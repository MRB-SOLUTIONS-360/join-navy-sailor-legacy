# Deep Dive — Frontend: Payment Gateway, My Applications, PDF/Verify/Download

Scope: `frontend/controllers/OnlinePaymentController.php`, `common/models/payment/SSLPayment.php`, `frontend/controllers/MyApplicationController.php`, and the PDF-generation/public-verification/download-form actions in `frontend/controllers/SailorCandidateController.php` and `frontend/controllers/DeSailorController.php` (`actionDownload`, `actionDownloadForm`, `actionVerifyCandidate`), plus their supporting views under `frontend/views/{sailor-candidate,de-sailor}/`.

This is a Yii2 2.0 advanced-template app, not Laravel — there is no `routes/web.php`; routing is `enablePrettyUrl => true` with an **empty `rules` array** (`frontend/config/main.php:59-66`), so every URL below is Yii2's default `/{controller-id}/{action-id}` mapping with remaining params as query string (e.g. `sailor-candidate/download?slug=...`), not a hand-declared route.

**Access-control mechanism, same pattern as the officer-legacy reference doc**: the `{slug}` query param is a reversible custom-cipher of the numeric row id — `common\static\StaticMethod::encryptPk()` / `decryptPk()` (`common/static/StaticMethod.php:486-526`), a digit-substitution + `mt_rand` padding + `chr()`-offset scheme, **not a cryptographic token**. `decryptPk()` is deterministic (same input always decrypts the same way regardless of the random padding baked in by `encryptPk()`), so it is fully invertible by anyone who studies the algorithm — this is security-by-obscurity, not real authorization.

---

## 1. Payment: the live SSLCommerz flow (`OnlinePaymentController.php`)

### 1.1 How a candidate reaches it

`SailorCandidateController::actionPayment($slug)` / `DeSailorController::actionPayment($slug)` (structurally identical) are the entry point, not `OnlinePaymentController` itself:

1. Guarded by `AccessControl` (`SailorCandidateController::behaviors()`, `only => ['payment', 'academic-info']`, `roles => ['@']`) — logged-in candidates only.
2. Loads the candidate's `Sailors`/`DeSailors` row via `findModel($slug)`, which **does** enforce `andWhere(['created_by' => Yii::$app->user->identity->id])` (`SailorCandidateController.php:1077-1088`) — ownership-scoped, unlike the download/verify actions below.
3. Resolves `payment_mode` (`live` vs `sandbox`) from the batch's `payment_mode` flag (`SailorBatchs::batchById()`).
4. **Retry path**: if the candidate is unpaid but has a prior `validation_id`, calls `SSLPayment::allRequestListByTranIds()` to re-poll SSLCommerz's validation API for already-completed transactions before generating a new one (`SailorCandidateController.php:110-136`).
5. Builds a `$payment_info` array (`tran_id`, `amount` from `batch_setting_info['payment_amount']`, `cus_*` fields from the logged-in `User` identity + `CanEligibilityCheckInfo`, `opt_a` = `'sailor'`/`'de_sailor'`, `opt_b` = `"r_{$sailor->id}#u_{$user->id}"` — row id and user id concatenated, this pair is how the SSL callback later re-identifies who to log back in), stores it in `Yii::$app->session->set('payment_info', $payment_info)`, saves the model, and redirects to `online-payment/payment-ssl`.

### 1.2 `OnlinePaymentController` actions

`beforeAction()` disables CSRF validation only for `ssl-success`, `ssl-cancel`, `ssl-fail` — correct, since these are server-to-server/browser-redirect gateway callbacks that can't carry the site's CSRF token.

| Action | URL (default routing) | Status | What it does |
|---|---|---|---|
| `actionPaymentSsl()` | `/online-payment/payment-ssl` | **Live** | Reads `payment_info` from session, calls `SSLPayment::requestInit()`, redirects (`header("Location: ...")`) to the returned SSLCommerz `GatewayPageURL`. Throws/flashes+redirects-back on any failure (missing session, gateway error). |
| `actionSslSuccess()` | `/online-payment/ssl-success` | **Live** | SSLCommerz success callback. See §1.3. |
| `actionSslCancel()` | `/online-payment/ssl-cancel` | **Live** | Parses `$_REQUEST['value_b']` for the user id, logs that user in, flashes "Payment failed.", redirects to `my-application`. |
| `actionSslFail()` | `/online-payment/ssl-fail` | **Live** | Byte-identical to `actionSslCancel()` (same body, different action name — a copy-paste duplicate, not a distinct code path). |
| `actionPayment()` | `/online-payment/payment` | **BROKEN** | See §1.4. |
| `actionPaymentResponseDeSailor()` | `/online-payment/payment-response-de-sailor` | **BROKEN** | See §1.4. |
| `actionPaymentResponseSailor()` | `/online-payment/payment-response-sailor` | **BROKEN** | See §1.4. |

### 1.3 `actionSslSuccess()` in detail (`OnlinePaymentController.php:58-124`)

This is the actual money-received handler, and it is worth walking through because of its unauthenticated-identity-lookup pattern:

1. Reads the entire `$_REQUEST` superglobal (works whether SSLCommerz posts or redirects with a query string) and checks `strtolower($request['status']) == strtolower(SSLPayment::PAYMENT_VALID)` (i.e. `'valid'`).
2. Splits `$request['value_b']` on `#` back into `row_id` (`r_...`) and `user_id` (`u_...`) — this is the exact `opt_b` string the controller sent to SSLCommerz in step 1.1, echoed back unmodified by the gateway.
3. **`$identity = User::findOne(['id' => $user_id]); Yii::$app->user->login($identity);`** — logs a user in purely on the strength of an id value that arrived in an unauthenticated `$_REQUEST` payload. There is no signature/HMAC check on the callback beyond `status == 'VALID'`, and no re-verification against SSLCommerz's own validation API (`allRequestListByTranIds()`/`merchantTransIDvalidationAPI.php`) before trusting the values and logging the user in. In practice this means: knowing (or guessing) a `row_id#user_id` pair and hitting `/online-payment/ssl-success?status=VALID&value_b=r_123%23u_456&...` with plausible extra fields is enough to get session-authenticated as user 456 and flip candidate 123's row to `payment_status = PAID`.
4. Loads the matching `Sailors`/`DeSailors` row by `$row_id` (branching on `$request['value_a']`, `'sailor'` vs `'de_sailor'`), sets `card_type`, `trans_date`, `ref_id`, `card_no`, `payment_api` (raw `json_encode($request)`), `payment_status = Constants::PAYMENT_PAID`, `phase = Constants::SAILOR_PHASE_TWO`, `amount`, `store_amount = SSLPayment::STORE_AMOUNT`. Appends the full raw callback to `all_payment_response` (JSON array, growing log).
5. On save, redirects to `sailor-candidate/academic-info` or `de-sailor/academic-info` with the row's re-encrypted slug — advancing the candidate to the next application step. On any exception, flashes `application_close` and redirects to `my-application`.

### 1.4 The three broken/dead AamarPay & ShurjoPay actions

`OnlinePaymentController.php` imports `use common\models\payment\AamarPay;` and `use common\models\payment\ShurjoPayment;` at the top (lines 6-7), but **neither class exists anywhere in the repo** (`find . -iname "AamarPay*" -o -iname "ShurjoPayment*"`, excluding `vendor/`, returns zero results). Any request that reaches these three actions throws a PHP `Fatal Error: Class "common\models\payment\AamarPay" not found` (or `ShurjoPayment`), a 500, not a handled failure:

- **`actionPayment()`** (`OnlinePaymentController.php:172-182`) — clearly meant to be the AamarPay counterpart of `actionPaymentSsl()`: reads the same `session('payment_info')`, calls `AamarPay::requestInit(dataArray: $payment_info)`, and on success `header("Location: ...")`-redirects to `$response['payment_url']`. A commented-out line even shows the intended verification call: `// $req = AamarPay::paymentVerify(validationId:'231127121629426317');`.
- **`actionPaymentResponseDeSailor()`** (`:185-218`) — the AamarPay success-callback handler, structurally the AamarPay twin of `actionSslSuccess()`: checks `$request['status_code'] == AamarPay::AAMAR_PAY_SUCCESS`, logs in `User::findOne(['id' => $request['opt_d']])` (same unauthenticated-login-from-callback pattern as the SSL path, one field name different — `opt_d` instead of parsing `value_b`), loads the `DeSailors` row from `$request['opt_b']`, sets the same payment fields (`card_type`, `trans_date` from `pay_time`, `ref_id` from `pg_txnid`, `card_no` from `epw_txnid`, `amount` from `amount_original`), and — tellingly — sets `$sailor->store_amount = ShurjoPayment::STORE_AMOUNT` (line 208), pulling a constant from the *other* missing gateway class inside the *AamarPay* handler. This cross-reference is a strong tell that AamarPay and ShurjoPay were two competing/sequential integrations mid-migration, not fully separated even in the broken code.
- **`actionPaymentResponseSailor()`** (`:230-284`) — same shape again, this time for `Sailors` (general track): parses `opt_b` as `row_id#user_id` (matching the SSL controller's convention exactly), same `AamarPay::AAMAR_PAY_SUCCESS` / `ShurjoPayment::STORE_AMOUNT` missing-class references (lines 233, 268). Ends with a commented-out AamarPay verification API URL (`http://sandbox.aamarpay.com/api/v1/trxcheck/request.php?...`) as a developer note.

**What these were clearly meant to do**: provide a fallback/alternate payment gateway (AamarPay) with a further-alternate one (ShurjoPay) layered underneath it, mirroring the exact same "collect payment → gateway redirect → callback → log candidate in → flip `payment_status`/`phase` → redirect to academic-info" shape as the working SSL path — just never finished. The live SSL-prefixed actions (§1.2-1.3) are the actual, working, currently-used payment path; these three are dead weight that will hard-crash if ever linked from a live gateway config, but are otherwise inert since nothing in this codebase's views/controllers routes a candidate toward them (confirmed: no `Html::a`/`createUrl` call anywhere targets `online-payment/payment`, `payment-response-de-sailor`, or `payment-response-sailor`).

A dead duplicate file, `frontend/controllers/OnlinePaymentController_shurjo_pay.php`, sits alongside the live controller declaring the identical FQCN `frontend\controllers\OnlinePaymentController` — Yii2's `@frontend`-alias autoloader resolves that class name exclusively to the correctly-named file, so this second file is never loaded (same shape as a PSR-4 collision in Laravel). It contains an earlier ShurjoPay-based implementation with debug-only bodies (`echo '01'; print_r($request); die();`).

---

## 2. `common/models/payment/SSLPayment.php` — security findings (flagged prominently, not buried)

`SSLPayment` is a plain static-method PHP class (not a Yii `Model`/`ActiveRecord`) — the de facto payment-gateway service for the live checkout flow above. Two findings here are materially more severe than typical legacy-code smells because they concern **live money movement**:

### 2.1 Hardcoded live merchant credentials, committed to source control

```php
// common/models/payment/SSLPayment.php:16-28
const SANDBOX_STORE_ID = 'unloc67b319d54a54e';
const SANDBOX_PASSWORD = 'unloc67b319d54a54e@ssl';
const SANDBOX_URL = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
...
const LIVE_STORE_ID = 'joinnavynavymilbd0live';
const LIVE_PASSWORD = '67D29D06BA5D147902';
const LIVE_URL = 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
```

Both the **sandbox and the production/live** SSLCommerz `store_id`/`store_passwd` pair are plaintext class constants, permanently in git history for anyone with repo access (any past or present developer, any leaked clone). These are the credentials that authorize this merchant account to initiate and receive payments through SSLCommerz — not a low-stakes API key. There is no `.env`/config-based injection anywhere in this file; environment selection is entirely in-code (see §2.3).

### 2.2 TLS certificate/host verification explicitly disabled on every outbound gateway call

```php
// requestInit(), line 100-101
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// allRequestListByTranIds(), line 182-183 (same pattern, inside the per-transaction loop)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
```

Both cURL calls to SSLCommerz — the one that *initiates* a payment session (`requestInit()`) and the one that *validates completed transactions* (`allRequestListByTranIds()`, called both from the payment-retry path in `SailorCandidateController::actionPayment()` and potentially anywhere else needing transaction reconciliation) — disable TLS peer and host verification. This removes protection against a man-in-the-middle intercepting or forging responses from the payment gateway on the network path, for both the request that hands over transaction amounts/customer PII and the response that tells the app whether a transaction is genuinely paid. Combined with §2.1 (the same credentials are sent over these unverified connections), this is a materially worse pairing than a typical "disabled cert check" finding — it's the transport for live merchant credentials and payment validation both.

### 2.3 Environment switching by comment/uncomment, not configuration

```php
// SSLPayment.php:28-31
const HOST = 'https://www.joinnavysailor.org';
// const HOST = 'http://joinnavysailor.local';
// const HOST = 'http://localhost/NAVY/joinnavy-sailor-V2';
//CONST HOST = "https://joinnavy.navy.mil.bd/application";
```

Three alternate `HOST` values (local dev, an older localhost path, and what looks like an abandoned domain, `joinnavy.navy.mil.bd`) are left commented out rather than driven by environment config (`.env`, `params.php`, etc.) — evidence this has historically been toggled by hand-editing source before each deploy, a workflow that risks accidentally shipping a dev `HOST` to production (or vice versa) and is itself a process smell layered on top of the credential/TLS findings above.

### 2.4 What the code otherwise does (for completeness)

- `requestInit($dataArray)` builds the full SSLCommerz payload (`store_id`, `store_passwd`, `tran_id`, `success_url`/`fail_url`/`cancel_url` all pointed at `HOST . '/online-payment/ssl-{success,cancel,fail}'`, customer fields, and four opaque passthrough values `value_a`..`value_d` sourced from the caller's `opt_a`..`opt_d`), POSTs it to SSLCommerz, and on a `status == 'success'` JSON response with a non-empty `GatewayPageURL`, additionally — **only for `opt_a == 'sailor'`** (the `DeSailors` branch is not mirrored here) — appends the transaction id and full request payload to the `Sailors` row's `all_requested_tran_id`/`all_payment_request` JSON columns via `$sailor->save(false)` (validation explicitly skipped).
- `allRequestListByTranIds($app_type, $payment_mode, $tranIds)` loops the given transaction ids against SSLCommerz's `merchantTransIDvalidationAPI.php`, collecting every `VALIDATED` result and returning the first one found plus the full list of paid transaction ids — this is the reconciliation path `SailorCandidateController::actionPayment()` calls when a candidate returns to a payment step with an existing `validation_id` (§1.1 step 4), letting the app recover from a lost callback without re-charging the candidate.
- Live/sandbox selection: `$dataArray['payment_type'] == 'sandbox'` switches both the credential pair and the endpoint URL; otherwise it defaults to live. The `opt_a == 'de_sailor' && payment_type != 'sandbox'` branch (lines 45-48, and mirrored at 159-162 in `allRequestListByTranIds`) sets the exact same live-credential values the `else` path already would — dead/redundant conditional, not a functional difference.

---

## 3. My Applications listing (`MyApplicationController.php`)

Small, single-action controller (32 lines total) — the candidate's cross-track dashboard:

- **URL**: `/my-application` (or `/my-application/index`)
- **`actionIndex()`**: guest → redirect to `candidate/login`. Logged-in → runs two separate queries scoped to `created_by = current identity`, one against `Sailors::find()` and one against `DeSailors::find()`, each selecting `id, candidate_designation, eligibility_info_id, app_unique_id, name, serial_no, batch_id, phase, payment_status, application_status`. Renders `index` with both result sets as `model` (Sailors) / `model_de` (DeSailors).
- **View** (`frontend/views/my-application/index.php`): a single table iterating `$model` (the `DeSailors` set, `$model_de`, is passed into the view but the visible table in this file only walks `$model`/Sailors — worth flagging as a likely rendering gap: a DE-track candidate's applications are fetched but, from what's iterated here, not obviously surfaced in the same table markup shown to the user). Per row, `SailorBatchs::isCandidateContinueApplication()` determines if the batch is still open, and a chain of `if/elseif` on `phase`/`payment_status`/`serial_no` picks one action link: **Continue Payment** (phase 1 / unpaid → `/sailor-candidate/payment/{slug}`), **Continue** (phase 2/3/4 → `/sailor-candidate/academic-info/`, `/personal-info/`, or `/application-preview/` respectively), or **Download Slip** (paid + `serial_no` assigned → `/sailor-candidate/download/{slug}`, `target="_blank"`) — plus a **Cancel Application** button that opens a modal calling `SailorCandidateController::actionCancelApplication()` (AJAX, ownership-scoped by `created_by`). All slugs are freshly re-encrypted per request via `StaticMethod::encryptPk($value['id'])`, not stored.

---

## 4. PDF generation, public verification & self-service download-form (`SailorCandidateController` / `DeSailorController`)

Both controllers implement the same three actions with near-identical bodies (`DeSailorController`'s versions additionally resolve `diploma_trade_course` via `Subjects::subjectFindById()` and pass it into the view, since the DE track has trade/diploma fields the general Sailor track doesn't) — the two tracks are documented together, deltas called out inline.

### 4.1 `actionDownload($slug)` — owner-only PDF

- **URL**: `/sailor-candidate/download?slug=...` / `/de-sailor/download?slug=...`
- **The only one of the three actions that checks ownership**: `$sailor = $this->findModel($slug)` calls the `protected findModel()` helper (`SailorCandidateController.php:1077-1088`), which does `Sailors::find()->where(['id' => decryptPk($slug)])->andWhere(['created_by' => Yii::$app->user->identity->id])->one()` — so this specific action requires the requester to be logged in as the candidate who owns the row (accessing `Yii::$app->user->identity->id` on a guest would itself fatal/throw, since there's no explicit `AccessControl` covering this action — see §5).
- Decrypts the row's PII fields (`father_nid`, `current_phone`, `permanent_phone`, `father_phone`, `mother_phone`, `guardian_phone` — the model's `encryption_fields_for_personal_info` list) via `DataEncryption::dataDecrypt()`; resolves `current_thana`/`permanent_thana` names via `Upozilas::upazilaNameById()`.
- Generates the PDF (see §4.4) from `renderPartial('candidate/application_form_pdf', ...)` and streams it inline, named `{name}({serial_no}).pdf`.

### 4.2 `actionDownloadForm($slug)` — public, no ownership check

- **URL**: `/sailor-candidate/download-form?slug=...` / `/de-sailor/download-form?slug=...`
- Deliberately bypasses `findModel()` and instead does a bare `Sailors::find()->where(['id' => StaticMethod::decryptPk($slug)])->one()` (`SailorCandidateController.php:975-977`) — **no `created_by` check at all**. Anyone who can produce or guess a valid slug can pull any candidate's full application PDF.
- This is the endpoint the self-service **Download Documents** flow links to (see §4.5) — by design it must work for a candidate who isn't logged in (they looked their application up by application-ID/batch+roll+DOB, not by session), which explains the missing `AccessControl`, but the URL itself is not scoped to that flow — anyone with the slug can hit it directly, logged in or not.
- Same decrypt/thana-resolve/PDF steps as `actionDownload()`, plus one extra: `$sailor->current_phone = '+88' . $sailor->current_phone;` / same for `permanent_phone` — prepends the country code before rendering (not applied to `actionDownload()`'s output).

### 4.3 `actionVerifyCandidate($slug)` — public read-only HTML page, no ownership check

- **URL**: `/sailor-candidate/verify-candidate?slug=...` / `/de-sailor/verify-candidate?slug=...`
- Same bare `Sailors::find()->where(['id' => decryptPk($slug)])->one()` lookup, no ownership scoping.
- **`SailorCandidateController` is the only one of the two with a `behaviors()`/`AccessControl` block at all** (`SailorCandidateController.php:39-59`):
  ```php
  'access' => [
      'class' => AccessControl::class,
      'only' => ['payment', 'academic-info'],
      'rules' => [
          ['allow' => true, 'actions' => ['verify-candidate'], 'roles' => ['?']],
          ['allow' => true, 'actions' => ['payment', 'academic-info'], 'roles' => ['@']],
      ],
  ],
  ```
  The `only => ['payment', 'academic-info']` scope means the filter **only ever runs for those two actions** — the `verify-candidate` rule (`roles => ['?']`, guest-allowed) is declared but never enforced, because Yii2's `AccessControl::only` gates which actions the whole behavior even looks at. `verify-candidate` ends up public by simple absence of any check, not because the guest-allow rule did anything — dead configuration that happens to produce the same practical outcome (public access) as if it worked.
- Decrypts PII, resolves thana names, then `return $this->render('application_verify_preview', ['model' => $sailor])` — unlike the PDF actions, this one renders through the normal `mainNavy` frontend layout (per `view_inventory.md`), not a standalone document.
- **Dead code after the `return`**: both controllers have ~17-18 unreachable lines immediately following the `render()` call — a second, never-executed mPDF-generation block targeting `candidate/application_verification_pdf` (`SailorCandidateController.php:1051-1067`, `DeSailorController.php:1075-1091`). The `application_verification_pdf.php` views exist on disk (confirmed in `view_inventory.md`) but are never actually rendered by any reachable code path in either controller — this looks like an earlier "verification as PDF" design that was replaced by "verification as HTML page" without deleting the old branch.

### 4.4 mPDF generation pattern (shared by `actionDownload`/`actionDownloadForm`)

Identical shape in both controllers, both tracks:

```php
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'margin_top' => 2,           // 5 in SailorCandidateController::actionDownloadForm()
    'margin_left' => 8,
    'margin_right' => 8,
    'margin_bottom' => 0,
    'mirrorMargins' => true,
]);
$mpdf->curlAllowUnsafeSslRequests = true;   // present in every one of the 4 call sites
$mpdf->debug = true;                        // debug mode left on in production code
$mpdf->WriteHTML($this->renderPartial('candidate/application_form_pdf', ['model' => $sailor, /* + diploma_trade_course for DE */], true, false));
$mpdf->Output(str_replace(' ', '_', $sailor->name) . '(' . $sailor->serial_no . ')' . '.pdf', 'I');
exit();
```

`renderPartial(..., true, false)` renders the standalone `candidate/application_form_pdf.php` view (its own full `<!doctype html>` document, not wrapped in `mainNavy` — confirmed by `view_inventory.md`'s note that all four `candidate/application_*_pdf.php` files are standalone HTML captured to PDF) into a string, which `Mpdf::WriteHTML()` then converts. `Output(..., 'I')` streams the PDF inline (browser-rendered, not force-downloaded) and the action `exit()`s immediately after — no further Yii2 response lifecycle runs.

`$mpdf->curlAllowUnsafeSslRequests = true` is set unconditionally at every mPDF call site in both controllers — mPDF uses cURL internally to fetch any remote image referenced in the HTML being rendered (e.g. a photo/QR code hosted on Cloudflare R2), and this flag disables SSL verification on those internal fetches, same category of finding as §2.2 in `SSLPayment` — a second place in this codebase where TLS verification is turned off for outbound HTTP, this time for asset-fetching rather than the payment gateway itself.

### 4.5 Self-service Download Documents lookup form (`CandidateController::actionDownloadForm`)

Distinct from §4.2 — this is the actual public-facing lookup UI a candidate without a saved link would use to get to a `download-form` slug in the first place:

- **URL**: `/candidate/download-form` (GET renders the form; POST — regular full-page submit, not pure AJAX — processes it)
- **Controller**: `frontend\controllers\CandidateController::actionDownloadForm()` (`CandidateController.php:321-368`) — note this lives on a *third* controller, separate from both `SailorCandidateController` and `DeSailorController`.
- **Model**: `common\models\DownloadDocuments` (form model, no table) — two lookup modes selected by `download_by`:

| Field | Label | Required when | Validation |
|---|---|---|---|
| `download_by` | "Document Download By" | Always | `required`; `1` = "Using Application ID", `2` = "Using Information" |
| `application_id` | "Application ID" | `download_by == 1` | `applicationIdValidation` — custom inline validator, `addError` if empty *and* `download_by == 1` (`common/models/DownloadDocuments.php:35-39`) |
| `batch` | "Batch" | `download_by == 2` | `batchSerialValidation` — same pattern, gated on `download_by == 2`; datasource `SailorBatchs::getAllBatch()` |
| `serial_no` | "Serial No" | `download_by == 2` | `batchSerialValidation` |
| `dob` | "Date of Birth" | `download_by == 2` | `batchSerialValidation`; rendered via `yii\jui\DatePicker`, format `dd-MM-yyyy` |

- **Lookup logic** (`CandidateController.php:334-360`): only queries `Sailors` — **there is no `DeSailors` branch**, despite the inline comment `/// have de sailor table again` appearing twice (once per `download_by` mode) marking this as a known, unaddressed gap. A DE-track candidate cannot use this self-service form to retrieve their documents.
  - Mode 1 (`download_by == 1`): `Sailors::find()->where(['app_unique_id' => $application_id])->andWhere(['not', ['serial_no' => null]])` — requires a roll number already assigned.
  - Mode 2 (`download_by == 2`): `Sailors::find()->where(['batch_id' => $batch])->andWhere(['serial_no' => $serial_no])->andWhere(['dob' => $dob])`.
  - On a match, builds `$url = baseUrl . '/sailor-candidate/download-form/?slug=' . StaticMethod::encryptPk($sailor['id'])` and flashes success; the view (`sailor-candidate/candidate/application_form_download.php`) renders a "Download" link (`target="_blank"`) pointing at that URL — i.e. this form is the on-ramp to the no-ownership-check §4.2 endpoint.
  - On no match, flashes `application_close` = "No record found" (HTTP 200, same page re-rendered — no distinct error state).
- AJAX validation is enabled (`enableAjaxValidation: true` on the `ActiveForm`) for inline field errors, but the actual search submit is a normal full-page POST, not fetch/AJAX — different pattern from the officer-legacy reference doc's fully-AJAX download-form flow.

---

## 5. Access-control summary for this functional area

| Action | Controller | AccessControl? | Ownership check? | Effective exposure |
|---|---|---|---|---|
| `payment`, `academic-info` | `SailorCandidateController` | Yes (`roles => ['@']`) | Yes (`findModel()`) | Logged-in owner only |
| `download` | `SailorCandidateController` / `DeSailorController` | **No** (not in `only`) | Yes (`findModel()`) | Requires a session, but no formal gate — `Yii::$app->user->identity->id` access on a guest would itself error rather than cleanly 403 |
| `download-form` | `SailorCandidateController` / `DeSailorController` | **No** | **No** | Fully public — any valid slug works, logged in or not |
| `verify-candidate` | `SailorCandidateController` | Declared (`roles => ['?']`) but **outside `only`, never enforced** | **No** | Fully public by absence of any check |
| `verify-candidate`, `download`, `download-form` | `DeSailorController` | **No `behaviors()` at all** in this controller | Mixed (see above) | Same practical exposure as the Sailor track, with even less declared intent |
| `download-form` (lookup form) | `CandidateController` | Not applicable (public lookup by design) | N/A | Public by design — but hands out slugs to the no-ownership-check `download-form` PDF endpoint |
| `payment-ssl`, `ssl-success`, `ssl-cancel`, `ssl-fail` | `OnlinePaymentController` | **No `behaviors()`/AccessControl** (only CSRF exemption) | N/A (identity resolved from callback payload, see §1.3) | Public by necessity (gateway callbacks), but `ssl-success` trusts an unauthenticated `$_REQUEST` payload to both log a user in and mark a row paid |
| `payment`, `payment-response-de-sailor`, `payment-response-sailor` | `OnlinePaymentController` | **No** | N/A | Dead — fatal on missing classes, no live link to these actions exists in the codebase |

---

## 6. Cross-cutting observations (evidence-backed, informational only)

1. **`SSLPayment.php` is the single most severe security finding in this functional area** — live SSLCommerz merchant credentials hardcoded in source control, plus TLS verification disabled on every outbound call to the payment gateway (§2.1-2.2). Any repo access (past or present contributor, leaked clone) exposes the ability to interact with the live merchant account; the disabled TLS check additionally weakens the transport those credentials and payment confirmations travel over.
2. **`OnlinePaymentController::actionSslSuccess()` logs a user in and marks a row paid based on an unauthenticated callback payload**, with no signature/HMAC verification beyond a `status == 'VALID'` string check (§1.3). This is the same trust model SSLCommerz's IPN pattern generally relies on server-side validation for — here the "validation" is just trusting whatever arrived in `$_REQUEST`.
3. **Three payment actions (`actionPayment`, `actionPaymentResponseDeSailor`, `actionPaymentResponseSailor`) reference two classes that don't exist anywhere in the repo** (`AamarPay`, `ShurjoPayment`) and will hard-crash with a PHP Fatal Error if ever hit — dead/broken code from an incomplete gateway migration, not currently linked from anywhere reachable, but a landmine if old gateway callback URLs are still configured externally.
4. **Only `download`/`findModel()` in `SailorCandidateController` (and its `DeSailorController` twin) enforces ownership** — `download-form` and `verify-candidate`, on both tracks, resolve the target candidate purely from a decrypted slug with no session/ownership check at all, exposing full application PII (decrypted phone numbers, address, academic history) to anyone who can produce a valid slug.
5. **`$mpdf->curlAllowUnsafeSslRequests = true` and `$mpdf->debug = true` are set unconditionally at every one of the four mPDF call sites** across both controllers (§4.4) — the SSL-bypass flag is a second, independent instance of the same "disable TLS verification for convenience" pattern found in `SSLPayment.php`, this time for mPDF's internal remote-image fetches rather than payment-gateway calls.
6. **`CandidateController::actionDownloadForm()`'s self-service lookup form only ever queries `Sailors`**, never `DeSailors`, despite two identical `/// have de sailor table again` comments marking the gap explicitly — a Direct-Entry-track candidate cannot use this form to retrieve their own documents by application ID or batch/roll/DOB.
7. **`SailorCandidateController`'s `AccessControl` has a dead `verify-candidate` rule** — declared inside `rules` but outside the behavior's `only` scope, so it is never evaluated; the action ends up public regardless, but for a reason unrelated to the rule that appears to grant it.
8. **Both controllers carry ~17-18 lines of unreachable dead code immediately after `actionVerifyCandidate()`'s `return`** — a second, never-executed mPDF-based verification-PDF path (`candidate/application_verification_pdf.php`), superseded by the current HTML-page implementation but never deleted.
9. **`actionSslCancel()` and `actionSslFail()` are byte-for-byte identical method bodies** under two different action names — not a meaningful distinction between "user cancelled" and "gateway reported failure," just duplicated code.
10. **Environment (sandbox vs. live `HOST`) in `SSLPayment.php` has historically been switched by hand-commenting `const HOST` lines** (three dead alternates left in source, §2.3) rather than through environment-specific configuration — a process risk layered on top of the credential/TLS findings.
