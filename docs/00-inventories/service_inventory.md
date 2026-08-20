# Service / Helper Class Inventory — join-navy-sailor-legacy

## Important note: Yii2 has no `app/Services`/`app/Classes`/`app/Jobs` equivalent

This is a Yii2 2.0 **advanced application template**, not Laravel. There is no single "services" directory. The functional equivalents of the Laravel `join-navy-officer-legacy` inventory's `app/Services/`, `app/Classes/`, and `app/Jobs/` are scattered across a few different Yii2 idioms:

| Laravel concept | Yii2 equivalent found in this repo |
|---|---|
| `app/Services/*Service.php` (DI-bound service classes) | `common/components/R2Storage.php` — a Yii **application component** (`Yii::$app->r2Storage`), registered in `common/config/main.php`, not a plain service class |
| `app/Classes/*Pay.php` (payment gateway classes) | `common/models/payment/SSLPayment.php` — despite the `models` path segment, this is a static gateway-integration class, not an ActiveRecord model |
| `app/Jobs/*.php` (queued jobs) | **None.** `console/controllers/` contains only a `.gitkeep`; `console/config/main.php` registers no custom `controllerMap` entries beyond the stock `yii\console\controllers\FixtureController`. There is no queue component configured (`grep -rl "yii\\queue"` across the repo returns nothing) and no cron-style background job class anywhere. Whatever the officer app does via `ProcessRollGeneration::dispatch()` happens synchronously inline in controllers here (out of scope for this doc — see `controller_inventory.md`) |
| `app/enumClass`/enum helpers | `common/enumClass/Status.php` — a native PHP 8.1 `enum`, unused (see §4) |

Scope of this document: `common/components/R2Storage.php`, `common/models/payment/SSLPayment.php`, `common/models/SendSms.php` + `common/static/SendSms.php`, `common/enumClass/Status.php`, `console/controllers/` (empty), and `common/mail/` templates. Models and Controllers proper are covered by the sibling `view_inventory.md`/`route_inventory.md` (no `model_inventory.md`/`controller_inventory.md` existed at the time of writing this doc; not duplicated here regardless).

All caller lists were produced by grepping `frontend/`, `backend/`, `common/`, `console/` (excluding `vendor/`) for the literal reference syntax (`Yii::$app->componentName->`, `ClassName::method(`, `use Namespace\ClassName`).

---

## 1. `common/components/R2Storage.php`

**Namespace:** `common\components`
**Type:** Yii2 application component (`extends yii\base\Component`), registered as `Yii::$app->r2Storage` in `common/config/main.php:19-27`.
**Purpose:** Cloudflare R2 storage wrapper — confirmed. Wraps `Aws\S3\S3Client` (AWS SDK for PHP, S3-compatible protocol) against Cloudflare R2's S3-compatible endpoint. Handles both file storage (candidate photos) and append/upsert JSON-lines ("ndjson") audit-log files, mirroring the officer app's `FileUploadService` + `LogService` combined into one component.

**Registration (`common/config/main.php:19-27`):**
```php
'r2Storage' => [
    'class' => 'common\components\R2Storage',
    'accessKey' => '',
    'secretKey' => '',
    'endpoint' => '',
    'fileUrl' => '',
    'bucket' => 'sailor-images',
    'region' => 'auto',
    'verifySsl' => false,
],
```
`accessKey`/`secretKey`/`endpoint`/`fileUrl` are blank literals in the checked-in config and are **not** overridden in `common/config/main-local.php` (that file only overrides `db` and `mailer`) — real R2 credentials must be injected some other way in production (not found in this repo checkout) or the component is presently non-functional for uploads in this environment. `verifySsl => false` disables TLS certificate verification for all R2 calls.

**Public methods:**
| Signature | Description |
|---|---|
| `public function init()` | Component lifecycle hook; builds the internal `S3Client` from the public properties above (`region`, `endpoint`, `credentials.key/secret`, `http.verify`). |
| `public function uploadFile($fileName, $path)` | `putObject` of a local temp file to the bucket at key `$fileName`. Returns `true` if the SDK response contains an `ObjectURL`, else `false`; swallows `AwsException` and returns `false` (no logging — the `Yii::error()`/`throw` lines are commented out, lines 64-65). |
| `public function fileExists($fileName)` | `headObject` existence check; returns `true`/`false`, swallows `AwsException`. |
| `public function deleteFile($fileName)` | Calls `fileExists()` first, then `deleteObject` if present; returns `false` both when the file doesn't exist and on any `AwsException`. |
| `public function upsertCandidateLog(array $model, $logFile = null)` | Reads the current `$logFile` (or the default `logs.ndjson`) via `getLogFileContents()`, scans line-by-line for an entry whose `id` matches `$model['id']`. If found, `array_merge`s the new `data` block (candidate snapshot + acting `Yii::$app->user->identity` id/username/timestamp) into the existing entry's `data` array. If not found, appends a new ndjson line. Writes the whole file back via `putObject` to key `logs/{logFile}`. Returns `true`/`false` (swallows `\Exception`). |
| `public function getLogFileContents($logFile = null)` | `getObject` for key `logs/{logFile}`; returns body as string, or `''` on any exception. |
| `public function actionLog(array $logData, $logFile = null)` | Different merge shape from `upsertCandidateLog`: each ndjson line holds a JSON **array** of log entries (not a single object). Matches on `$existingEntry[0]['route'] == $logData['route']` and `array_unshift`s the new entry onto the front of that array (most-recent-first) if a match is found for the same route; otherwise appends a new line wrapping `$logData` in a single-element array. Used for admin action auditing (see caller below). |

**External dependencies:** AWS SDK for PHP (`Aws\S3\S3Client`, `Aws\Exception\AwsException`) against a Cloudflare R2 bucket (`sailor-images`), S3-compatible protocol. No `env()` calls inside the class itself — all config is injected via the DI component array in `common/config/main.php` (and, in Yii2 fashion, could be overridden per-environment in `main-local.php`, though it currently isn't).

**Callers (grep `Yii::$app->r2Storage->` across `frontend/`, `backend/`; excludes the component's own definition/registration):**

*File uploads (`uploadFile`/`deleteFile`):*
- `frontend/controllers/DeSailorController.php:418,441,485,502`
- `frontend/controllers/SailorCandidateController.php:460,472,498,515`
- `backend/controllers/DeSailorsController.php:145-146,164`
- `backend/controllers/SailorsController.php:313-314,332`

*Existence check + URL building (`fileExists`/`fileUrl`) — mostly view-layer image display:*
- `frontend/views/sailor-candidate/personal_info.php:901-902`
- `frontend/views/sailor-candidate/application_preview.php:31-32,55-56`
- `frontend/views/sailor-candidate/application_verify_preview.php:48-49` (QR-photo variant commented out, lines 25-26)
- `frontend/views/sailor-candidate/candidate/application_form_pdf.php:97-98` (QR-photo variant commented out, lines 67-68)
- `frontend/views/de-sailor/personal_info.php:950-951`
- `frontend/views/de-sailor/application_preview.php:31-32,55-56`
- `frontend/views/de-sailor/application_verify_preview.php:46-47`
- `frontend/views/de-sailor/candidate/application_form_pdf.php:96-97` (QR-photo variant commented out, lines 66-67)
- `backend/views/sailors/_form.php:113-114`
- `backend/views/de-sailors/_form.php:113-114`
- `backend/controllers/ReportController.php:404,418,877,881,1395,1397` (photo existence check + path for PDF/Excel roll/candidate reports)

*Audit logging:*
- `frontend/controllers/DeSailorController.php:921` — `upsertCandidateLog(...)` to `{batch_name}.ndjson`
- `frontend/controllers/SailorCandidateController.php:895` — `upsertCandidateLog(...)` to `{batch_name}.ndjson`
- `backend/config/main.php:120,122,126` — global `beforeAction`-style hook (module bootstrap) calling `actionLog(...)` to `action_log/{controller_id}/{add|update|<method>_<date>}.ndjson` for every backend controller action, i.e. this is the admin-panel audit trail
- `backend/controllers/LogReportController.php:34,98` — `getLogFileContents(...)` to **read back** the ndjson logs for display (the admin "log viewer" screen)

**Note:** the officer app's equivalent (`FileUploadService` + `LogService`) is two separate classes with `LogService` reading `CLOUDFLARE_*` env vars directly; here both responsibilities are merged into one Yii component whose credentials are DI-configured (and currently blank in the checked-in config, per above).

---

## 2. `common/models/payment/SSLPayment.php` — payment gateway wiring

**Namespace:** `common\models\payment`
**Purpose:** SSLCommerz payment-gateway integration. Confirmed as the **only functioning** payment gateway class that actually exists in this repo, despite two controllers referencing two other gateway names.

**Notable constants (all hard-coded, no `env()`/`config()`):**
- `PAYMENT_VALID = 'VALID'`, `PAYMENT_VALIDATED = 'VALIDATED'`, `STORE_AMOUNT = 275`
- Sandbox: `SANDBOX_STORE_ID = 'unloc67b319d54a54e'`, `SANDBOX_PASSWORD = 'unloc67b319d54a54e@ssl'`, `SANDBOX_URL`, `SANDBOX_PAYMENT_CHECK` (both under `sandbox.sslcommerz.com`)
- Live: `LIVE_STORE_ID = 'joinnavynavymilbd0live'`, `LIVE_PASSWORD = '67D29D06BA5D147902'` (plaintext), `LIVE_URL`, `LIVE_PAYMENT_CHECK` (both under `securepay.sslcommerz.com`)
- `HOST = 'https://www.joinnavysailor.org'` (two commented-out local/dev alternates left above it, lines 29-31), `SUCCESS_URL_SAILOR`/`CANCEL_URL_SAILOR`/`FAIL_URL_SAILOR` built from it

**Public methods:**
| Signature | Description |
|---|---|
| `public static function requestInit($dataArray = array())` | Defaults to live store id/password; the `opt_a == 'de_sailor'` branch (lines 45-48) is a no-op copy-paste artifact — it reassigns the same live credentials that were already set, i.e. de-sailor and sailor share one live SSLCommerz merchant account (unlike the officer app, which has separate OC/DEO live credential sets). Switches to sandbox credentials/URL when `$dataArray['payment_type'] == 'sandbox'`. Builds the SSLCommerz init payload (customer info, amount, currency `BDT`, success/fail/cancel URLs, `value_a..value_d` opt fields) and POSTs via raw `curl` with `CURLOPT_SSL_VERIFYPEER`/`VERIFYHOST` both disabled. On a JSON response with `status == 'success'` and non-empty `GatewayPageURL`: returns `['success' => true, 'url' => ...]`. As a side effect, when `opt_a == 'sailor'`, parses `opt_b` (format `r_{id}#...`) to find the `Sailors` row id, loads it, appends the `tran_id` to `all_requested_tran_id` (JSON column) and a request snapshot `{time, gatewayPageURL, data}` to `all_payment_request` (JSON column), and calls `$sailor->save(false)` (validation skipped). **Note:** this save-side-effect branch only fires for `opt_a == 'sailor'` — a `de_sailor` payment-init call never records `all_requested_tran_id`/`all_payment_request` on the `DeSailors` row, unlike the sailor flow. Throws `\Exception` if the curl call itself fails. |
| `public static function allRequestListByTranIds($app_type, $payment_mode = 'sandbox', $tranIds = array())` | Same live/sandbox credential-selection logic as `requestInit` (with the same no-op `de_sailor` branch). For each `$tranIds` entry, GETs SSLCommerz's `merchantTransIDvalidationAPI.php` validator (SSL verification disabled). Collects matching `PAYMENT_VALIDATED` responses into `paid_tran_ids`, captures the first as `first_paid_tran_id`/`details_paid_tran_id`. Returns all three. |

**External dependencies:** raw PHP `curl_*` against SSLCommerz sandbox/live REST endpoints; directly reads/writes `common\models\Sailors` (`all_requested_tran_id`, `all_payment_request` JSON columns) via a `use common\models\Sailors;` import. No `env()`/`config()` — all credentials/URLs are class constants.

**Callers (grep `SSLPayment::` across `frontend/controllers`):**
- `frontend/controllers/OnlinePaymentController.php` — `actionPaymentSsl()` calls `SSLPayment::requestInit()`; `actionSslSuccess()` reads `SSLPayment::PAYMENT_VALID`/`STORE_AMOUNT`

### Payment-gateway flow investigation — both other gateways are effectively dead in this repo

`frontend/controllers/OnlinePaymentController.php` (the live, routable controller — class `frontend\controllers\OnlinePaymentController`, matching its filename) imports **three** gateway namespaces:
```php
use common\models\payment\AamarPay;
use common\models\payment\ShurjoPayment;
use common\models\payment\SSLPayment;
```
But `common/models/payment/` on disk contains **only** `SSLPayment.php` — `AamarPay.php` and `ShurjoPayment.php` do not exist anywhere in this repository (`find . -iname 'AamarPay.php' -o -iname 'ShurjoPayment.php'`, excluding `vendor/`, returns zero results). Consequences:
- **`SSLPayment`-backed actions are live and functional as PHP code:** `actionPaymentSsl()`, `actionSslSuccess()`, `actionSslCancel()`, `actionSslFail()`.
- **`AamarPay`-backed actions (`actionPayment()`, `actionPaymentResponseDeSailor()`, `actionPaymentResponseSailor()`) and the `ShurjoPayment::STORE_AMOUNT` reference inside `actionPaymentResponseDeSailor()`/`actionPaymentResponseSailor()` will fatal-error (`Class "common\models\payment\AamarPay" not found` / `...ShurjoPayment...`) if ever invoked**, because PHP only resolves a `use`-imported class lazily, at first actual reference — and that referenced class simply isn't in the codebase.
- `frontend/controllers/OnlinePaymentController_shurjo_pay.php` also declares `class OnlinePaymentController` (same class name, different filename) with a further, mostly-commented-out ShurjoPay flow, plus a live-but-broken `actionPayment()`/`actionPaymentResponseDeSailor()` duplicating the AamarPay calls above, and an `actionPaymentResponseSailor()` whose live code path is a hard `die()` immediately after dumping `$_REQUEST` (line ~261-265) — everything below the `die()`, including the real ShurjoPay verification logic, is unreachable dead code. **This file is not routable at all**: Yii2's autoloader resolves `frontend\controllers\OnlinePaymentController` to `frontend/controllers/OnlinePaymentController.php` by class name (the file that already exists and is shown above), never to a file named `OnlinePaymentController_shurjo_pay.php`. It is an orphaned scratch/backup file with no live entry point, analogous to the `_BK` backup-controller convention documented in the officer-app inventory.

**Conclusion: only SSLCommerz (`SSLPayment`) is a genuinely live, callable payment gateway in this repo.** AamarPay and ShurjoPay are referenced by name/constant throughout the payment controllers (suggesting they were ported from the officer app or an earlier iteration) but their implementation classes were never added to `common/models/payment/`, so those code paths are non-functional dead branches, not alternate live gateways.

---

## 3. `common/models/SendSms.php` + `common/static/SendSms.php` — SMS integration

Two distinct classes share the name `SendSms` in different namespaces — worth flagging since it's easy to conflate them.

### 3a. `common\models\SendSms` (`common/models/SendSms.php`)
Standard `yii\db\ActiveRecord` model for table `{{%send_sms}}` (columns: `application_type`, `serial_no`, `phone_no`, `sms_body`, `created_by`/`updated_by`/`created_dt`/`updated_dt`). This is the **persistence log** of sent SMS, not the sender itself. `beforeSave()` auto-stamps `created_by`/`created_dt` or `updated_by`/`updated_dt` from `Yii::$app->user->identity->id` (falling back to hard-coded `1`/`2` if no identity). Out of scope for deep documentation (it's an ActiveRecord model, covered by the models bucket), noted here only because the sending class below depends on it.

### 3b. `common\static\SendSms` (`common/static/SendSms.php`) — the actual SMS sender
**Purpose:** SMS sending integration via **BoomCast** (`boom-cast.com`), a Bangladesh bulk-SMS gateway — confirmed by the hard-coded endpoint.

**Notable constant:** `SMS_API_URL_BOOM_CAST = "http://api.boom-cast.com/boomcast/WebFramework/boomCastWebService/externalApiSendTextMessage.php?masking=JOIN NAVY&userName=Unlocklive&password=&MsgType=TEXT&receiver=Number&message=Your Message"` — note the `password=` query param is **empty** in the hard-coded URL (no BoomCast account password is baked in, unlike the SSLCommerz/live credentials elsewhere in this repo), and it's plain `http://`, not `https://`.

**Public methods:**
| Signature | Description |
|---|---|
| `public static function sendSms($mobile, string $smsBody, string $application_type, string $serial_no)` | Strips non-digits from `$mobile`, prefixes with country code `88` if not already present. Builds the GET URL from `SMS_API_URL_BOOM_CAST` with `receiver`/`message` overridden via query-string concatenation (not proper URL templating — the base constant's own `receiver=Number&message=Your Message` placeholders are left in place and simply followed by real `&receiver=...&message=...` params, relying on the gateway using the *last* occurrence of a repeated query key). `curl_exec`s a GET (`CURLOPT_SSL_VERIFYPEER => false`, 120s connect/response timeouts, follows redirects). On a curl error: `print_r`s the error and calls `exit()` (script-terminating, matching the `die`/`exit` failure pattern seen in the officer app's gateway classes). On success, persists a `common\models\SendSms` row (`application_type`, `serial_no`, `phone_no`, `sms_body`) via `$model->save(false)` (validation skipped) and returns the literal string `'_success'` or `'_failure'`. |

**External dependencies:** BoomCast bulk-SMS HTTP API (`api.boom-cast.com`), raw `curl_*`, no `env()`/`config()` — URL and (missing) credentials are a class constant.

**Callers:** `grep -rn "SendSms::sendSms\|use common\\static\\SendSms"` across `frontend/`, `backend/`, `common/`, `console/` finds exactly **one** reference, and it is commented out:
- `frontend/controllers/SailorCandidateController.php:29` (`use common\static\SendSms;`) and `:902` (`// SendSms::sendSms(mobile: $sailor->permanent_phone, smsBody: $smsBody, serial_no: $sailor->serial_no, application_type: 'sailor');`)

**SMS sending is presently disabled in this codebase** — the only call site is commented out, unlike the officer app where the roll-generation job actively sends a congratulations SMS via `Sms::sendSms()`. No `App\Jobs`-equivalent exists here to have carried that call anyway (see the top-of-doc table — this repo has no job/queue layer).

---

## 4. `common/enumClass/Status.php`

**Namespace:** `common\enumClass`
**Type:** Native PHP 8.1 backed-less `enum` (not a class) — `enum Status { case ACTIVE; case INACTIVE; ... }`.
**Purpose:** Generic active/inactive status enum with two instance methods:
| Method | Description |
|---|---|
| `public function selected(): string` | `match($this)` → `'Active'` / `'Inactive'`. |
| `public function dropDown()` | Returns `[Status::ACTIVE => 'Active', Status::INACTIVE => 'Inactive']` — note the array keys are enum *cases* (objects), not scalars, which is unusual for a "dropdown" helper meant to feed an HTML `<select>` (Yii's `Html::activeDropDownList`/`ArrayHelper` normally expect scalar keys); this shape suggests the method was never actually wired into a view. |

Below the enum, lines 30-37 contain a commented-out `abstract class DaysOfWeek` scaffold (dead/never-completed code, left in place with a PHP 8.2 changelog link comment).

**Callers:** `grep -rn "enumClass\\\\Status\|Status::ACTIVE\|Status::INACTIVE"` across `frontend/`, `backend/`, `common/`, `console/` (excluding `vendor/`) returns **zero references outside the file's own definition**. This enum is entirely unused — the codebase's actual active/inactive dropdowns are handled elsewhere (likely via `common\static\StaticMethod`-style constant arrays, matching the officer app's `StaticMethod::statusDropDown()` pattern, though that class is out of scope for this doc).

---

## 5. `console/controllers/` — no custom console commands exist

`console/controllers/` contains only a `.gitkeep` file — **zero** custom console command classes. `console/config/main.php` registers a single `controllerMap` entry, `'fixture' => yii\console\controllers\FixtureController::class` (Yii2's own built-in test-fixture-loading command, not application-specific), plus the stock `log` component/target. No `yii\queue` component is configured anywhere in the repo (`grep -rl "yii\\queue"` across `console/`, `common/`, `frontend/`, `backend/` returns nothing), and no cron/scheduler config was found.

**This is the one place where this repo has strictly less than the officer-app reference** — the officer app's `app/Jobs/ProcessRollGeneration.php` (queued roll-number generation + SMS + audit log) has no counterpart here at all. Whatever roll-generation logic exists in this sailor app runs synchronously inside a controller action (out of scope for this doc; see `route_inventory.md`/`view_inventory.md` for the candidate-flow controllers, e.g. `SailorCandidateController.php`/`DeSailorController.php`, which are the likely home of that logic given they're also the `r2Storage->upsertCandidateLog()` callers above).

---

## 6. `common/mail/` templates

Standard Yii2 advanced-template scaffold mail views (`viewPath => '@common/mail'` set on the `mailer` component in `common/config/main-local.php`), rendered by `Yii::$app->mailer->compose([...])`. All four content templates are **unmodified from the Yii2 `yii2-app-advanced` starter** — same body copy, same `Html::encode($user->username)` pattern, same absolute-URL construction via `Yii::$app->urlManager->createAbsoluteUrl(...)`.

| Template | Purpose | Rendered together with |
|---|---|---|
| `common/mail/emailVerify-html.php` / `emailVerify-text.php` | Account email-verification link (`site/verify-email?token=...`) | HTML+text pair passed as one `compose(['html' => 'emailVerify-html', 'text' => 'emailVerify-text'], ...)` call |
| `common/mail/passwordResetToken-html.php` / `passwordResetToken-text.php` | Password-reset link (`site/reset-password?token=...`) | HTML+text pair, same `compose()` pattern |
| `common/mail/layouts/html.php` | Outer HTML document wrapper (`<!DOCTYPE html>...<body>{content}</body>`) applied to all HTML mail views | n/a — layout, not content |
| `common/mail/layouts/text.php` | Plain-text layout wrapper (just `beginPage`/`beginBody`/`content`/`endBody`/`endPage`, no markup) | n/a — layout, not content |

**Trigger points (grep `->mailer->compose(` / `emailVerify` / `passwordResetToken` across `frontend/`, excluding `vendor/`):**
| Template pair | Triggered from | Reached via controller action |
|---|---|---|
| `emailVerify-*` | `frontend/models/SignupForm.php:179-183` (`sendEmail()`, called at the end of `signup()`) | `frontend/controllers/SiteController.php` → `actionSignup()` (line 166) |
| `emailVerify-*` | `frontend/models/ResendVerificationEmailForm.php:51-55` | `frontend/controllers/SiteController.php` → `actionResendVerificationEmail()` (line 256) |
| `passwordResetToken-*` | `frontend/models/PasswordResetRequestForm.php:59-63` | `frontend/controllers/SiteController.php` → `actionRequestPasswordReset()` (line 184) |

A fifth mailer call exists but doesn't use a template file: `frontend/models/ContactForm.php:53` calls `Yii::$app->mailer->compose()` with no arguments (plain-text body set directly via `->setTextBody(...)`, not shown here as it's outside this doc's `common/mail/` scope) for the `site/contact` form (`SiteController::actionContact()`, line 133).

**Local dev config note:** `common/config/main-local.php` sets `'useFileTransport' => true` on the `mailer` component with no `transport` block configured — in this checked-out environment, mail is written to local files rather than actually sent (standard Yii2-advanced dev default; production presumably overrides this, but no such override was found in this repo checkout).

---

## Summary table

| File | Type | Live/active? | Callers (files) | Notes |
|---|---|---|---|---|
| `common/components/R2Storage.php` | App component (`Yii::$app->r2Storage`) | Yes | 17+ (controllers + views + backend audit hook) | Credentials blank in checked-in config; `verifySsl => false` |
| `common/models/payment/SSLPayment.php` | Payment gateway | Yes | 1 (`OnlinePaymentController.php`) | Only working gateway class in this repo; SSL verify disabled |
| `common\models\payment\AamarPay` (referenced, not present) | Payment gateway | **No — class file missing entirely** | 2 controllers reference it, both would fatal-error if invoked | Dead/broken import |
| `common\models\payment\ShurjoPayment` (referenced, not present) | Payment gateway | **No — class file missing entirely** | 2 controllers reference it, both would fatal-error if invoked | Dead/broken import |
| `frontend/controllers/OnlinePaymentController_shurjo_pay.php` | Duplicate-named controller file | **No — not autoloadable/routable** (filename ≠ class name) | 0 | Orphaned scratch/backup file |
| `common/models/SendSms.php` | ActiveRecord (SMS send log) | Yes (as a log table) | via `common\static\SendSms` only | Out-of-scope model, documented for context |
| `common/static/SendSms.php` | SMS sender (BoomCast API) | **No — only call site is commented out** | 1 (commented) | SSL verify disabled; API password blank in hard-coded URL |
| `common/enumClass/Status.php` | PHP 8.1 native enum | **No — zero callers found** | 0 | Dead code; `dropDown()` keyed by enum objects, not scalars |
| `console/controllers/` | Console commands | **N/A — empty, no custom commands exist** | n/a | No queue/cron layer anywhere in the repo; no `ProcessRollGeneration`-equivalent job |
| `common/mail/emailVerify-{html,text}.php` | Mail template pair | Yes | `SignupForm`, `ResendVerificationEmailForm` | Unmodified Yii2 scaffold template |
| `common/mail/passwordResetToken-{html,text}.php` | Mail template pair | Yes | `PasswordResetRequestForm` | Unmodified Yii2 scaffold template |
| `common/mail/layouts/{html,text}.php` | Mail layout wrappers | Yes | applies to all `common/mail/*` content views | Unmodified Yii2 scaffold layout |
