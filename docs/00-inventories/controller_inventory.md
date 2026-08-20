# Controller Inventory — join-navy-sailor-legacy

Yii2 2.0 advanced template (`common/`, `frontend/`, `backend/`, `console/`). Concepts adapted from the Laravel-flavored reference doc (`join-navy-officer-legacy`):

- **Controllers** → `frontend/controllers/*Controller.php`, `backend/controllers/*Controller.php`, `console/controllers/*Controller.php`.
- **Requests** → Yii2 has no `FormRequest` layer. Validation lives in each Model's `rules()` method instead — there is no separate class to inventory here, this is a real architectural difference from Laravel, not a gap.
- **Models** → counted/listed only; detailed model doc is `model_inventory.md` (separate agent's scope).
- **Services** → Yii2 has no dedicated Services layer here. Closest equivalents are `common/components/`, `common/widgets/`, `common/enumClass/` (and `common/static/`, a static-helper-class layer used throughout the controllers below) — counted/listed only; detailed doc is `component_inventory.md` / `service_inventory.md` (separate agent's scope).
- **Jobs** → no queue mechanism and no custom console commands exist in this repo (see Jobs section).

## Summary

| Layer | Path | File count | Notes |
|---|---|---|---|
| Controllers | `frontend/controllers/` | 10 files | 1 dead duplicate (`OnlinePaymentController_shurjo_pay.php`), 1 inert stub (`ApiController.php`) |
| Controllers | `backend/controllers/` | 21 files | 2 empty stubs (`BulkCheckController.php`, `TeController.php`) |
| Controllers | `console/controllers/` | 0 files | directory holds only `.gitkeep`; no custom console controllers exist |
| Requests | N/A — Yii2 pattern | — | validation lives in Model `rules()`, not a separate Request class layer |
| Models | `common/models/` (27), `frontend/models/` (7), `backend/models/` (12), `console/models/` (0) | 46 total | detailed doc: `model_inventory.md` |
| Services (closest equivalent) | `common/components/` (1), `common/widgets/` (1), `common/enumClass/` (1), `common/static/` (5) | 8 total | detailed doc: `component_inventory.md` / `service_inventory.md` |
| Jobs | — | 0 | no `yii\queue` usage anywhere in the repo (grep-verified), no cron-style console commands |

**Broken references found (confirmed by direct read + grep, not just static analysis):**

1. **`frontend/controllers/OnlinePaymentController.php`** — imports `common\models\payment\AamarPay` and `common\models\payment\ShurjoPayment`, but neither class exists anywhere in the repo (only `common/models/payment/SSLPayment.php` exists; verified via `find . -iname "AamarPay*" -o -iname "ShurjoPayment*"`, excluding vendor — zero results). Three live, reachable actions reference them and will throw a PHP Fatal Error ("Class not found") if hit:
   - `actionPayment()` — line 176 (`AamarPay::requestInit`)
   - `actionPaymentResponseDeSailor()` — lines 188 (`AamarPay::AAMAR_PAY_SUCCESS`), 208 (`ShurjoPayment::STORE_AMOUNT`)
   - `actionPaymentResponseSailor()` — lines 233 (`AamarPay::AAMAR_PAY_SUCCESS`), 268 (`ShurjoPayment::STORE_AMOUNT`)

   The `ssl-*`-prefixed actions in the same controller are unaffected and are the working/live payment path (`SSLPayment` exists and is used correctly). This reads as a legacy AamarPay/ShurjoPay integration left half-migrated to SSLPayment.

2. **`backend/controllers/DeSailorBranchController.php`** — the entire `backend/views/de-sailor-branch/` directory does not exist anywhere in the repo (verified via `find`). All 4 of its `render()` calls (`index`, `view`, `_form`, `update`) are broken. The controller is non-functional as shipped.

3. **`backend/controllers/UserController.php::actionCreate()`** — calls `$this->render('create', ...)` but `backend/views/user/create.php` does not exist (only `_form.php`, `index.php`, `view.php` are present in that directory — verified via `ls`).

4. **`backend/controllers/DeSailorsController.php::actionCreate()`** — calls `$this->render('create', ...)` but `backend/views/de-sailors/create.php` does not exist (only `_form.php`, `index.php`, `update.php`, `view.php`, `reference/` are present — verified via `ls`).

All other `render()`/`renderPartial()`/`renderAjax()` targets across all 31 controllers resolved successfully on disk.

---

## Dead/Duplicate/Inert Code (confirmed)

| File | Why dead/inert |
|---|---|
| `frontend/controllers/OnlinePaymentController_shurjo_pay.php` | Declares `namespace frontend\controllers; class OnlinePaymentController` — **identical FQCN** to the live `frontend/controllers/OnlinePaymentController.php`. Yii2's `@frontend`-alias autoloader resolves `frontend\controllers\OnlinePaymentController` exclusively to the file `OnlinePaymentController.php` (filename derived from short class name); a second file declaring the same class is never autoloaded. Grepped the whole repo (excluding vendor) for the literal filename as a `require`/`include` target — zero matches. This is the Yii2-autoloader equivalent of the Laravel repo's PSR-4-collision `_BK` files. Contains an earlier ShurjoPay-based implementation with debug-only bodies (`echo '01'; print_r($request); die();`) and large commented-out blocks. Safe to archive/delete. |
| `frontend/controllers/ApiController.php` | Extends `\yii\rest\Controller` (not `ActiveController`, so no CRUD actions materialize automatically) and declares `$modelClass`, `$token` (hardcoded), `$allowed_ip`, `$allowed_domain` properties, but has **zero `action*` methods and no `actions()` override**. None of those properties are referenced anywhere in the (empty) class body. Effectively an inert shell — any request to this controller 404s. |
| `backend/controllers/BulkCheckController.php` | Empty class body. Imports `SailorBatchConfiguration`, `SailorBatchConfigurationExamDate`, `SailorBatchs`, `Sailors`, `Constants` — all unused. Zero actions; scaffold left in place. |
| `backend/controllers/TeController.php` | Empty class body. Imports `PhpOffice\PhpWord\{PhpWord, IOFactory}` plus several model/static-helper classes — all unused. The PhpWord imports suggest an abandoned Word-doc export feature (see `backend/controllers/ReportController.php::actionReferenceCandidateWord()` for the feature that *did* ship, via `ReportController` instead). Zero actions. |
| `backend/controllers/SailorsController.php` | `actionCreate()` (~line 246) and `actionDelete()` (~line 365) are **fully commented out** — dead/unreachable. Sailors records cannot be created or deleted via this controller; presumably handled elsewhere (frontend candidate flow creates the row; deletion path is not exposed anywhere in `backend/`). |
| `backend/controllers/UserController.php` | `actionDelete($id)` (~line 120) is **fully commented out** — dead/unreachable. Admin users cannot be deleted via this controller. |
| `backend/controllers/ReportController.php` | `folderTurncate()` protected helper is defined but never called anywhere in the class. |

---

## Controllers

### `frontend/controllers/`

#### `AjaxController.php`
- **Class:** `frontend\controllers\AjaxController extends \yii\web\Controller`
- **behaviors():** Not defined. `beforeAction()` unconditionally sets `enableCsrfValidation = false` for all actions. **No AccessControl — every action is public.**
- **Actions:**

| Action | Description |
|---|---|
| `actionDistrictByCandidateType()` | AJAX-only. Reads POST `candidate_type`, queries `Districts` (special-cases the "posso kota" candidate type to Navy-child districts only), returns JSON. |
| `actionHscDiplomaAcGroup()` | AJAX-only. Reads POST `ac_type`; if `"NO"` pulls active diploma subjects for DE_SAILOR type, else calls `StaticMethod::academicGroupHsc()`. Returns JSON. |
| `actionUpazialByDistrict()` | AJAX-only. Resolves district id via `Districts::getIdBySlug`, returns upazila list via `Upozilas::getUpazilaListCandidate` as JSON. |
| `actionUnionByUpazial()` | AJAX-only. Returns unions via `Unions::getUnionsListForCandidate` as JSON. |

- **Views:** None — all JSON responses.

#### `ApiController.php`
- **Class:** `frontend\controllers\ApiController extends \yii\rest\Controller`
- **behaviors():** Not defined.
- **Actions:** None. **Inert stub** — see Dead/Duplicate/Inert Code above.
- **Views:** None.

#### `CandidateController.php`
- **Class:** `frontend\controllers\CandidateController extends \yii\web\Controller`
- **behaviors():** Not defined. Access control done manually inline via `Yii::$app->user->isGuest` checks per action.
- **Actions:**

| Action | Description |
|---|---|
| `actionValidateBirthRegistration()` | AJAX JSON validator for a throwaway `SignupForm`'s `birth_registration_no` field. |
| `actionSignUp()` | Guest-only. Loads `SignupForm`, AJAX-validates, on POST checks a manual math CAPTCHA (`session['captcha']`), calls `$model->signup()`, logs the user in, branches via `haveCeci()` on an encrypted `ceci` query param to redirect to payment/my-application/home. Renders `sign_up`. |
| `actionLogin()` | Guest-only. Loads `LoginForm` (user_type=candidate), same CAPTCHA check, creates a `Session` row on success, branches via `haveCeci()`. Renders `login`. |
| `actionLogout()` | Marks `Session` rows expired for the user, logs out, redirects home. |
| `actionDownloadForm()` | Builds `DownloadDocuments`, AJAX-validates, on POST looks up a `Sailors` record by application ID or batch/serial/dob, renders `/sailor-candidate/candidate/application_form_download` (cross-controller absolute view path). |
| `actionChangePassword()` | Logged-in only. AJAX-validates `ChangePassword`, re-hashes password via `Yii::$app->security->generatePasswordHash`, saves `User`. Renders `change_password`. |
| `actionRequestPasswordReset()` | Guest-only. AJAX-validates `ResetPassword`, looks up `User` by username+dob, generates a random 5-digit password, saves it and flashes the **plaintext** new password to the user. Renders `request_password_reset`. |

- **Non-action helpers:** `captureValue()` (protected, builds arithmetic CAPTCHA), `haveCeci($ceci)` (public but not an `action*` method — decrypts eligibility id, resolves sailor vs. de_sailor, creates a `Sailors`/`DeSailors` row if none applied yet, returns routing hints).
- **Views:** `sign_up`, `login`, `change_password`, `request_password_reset` (all in `frontend/views/candidate/`) and `frontend/views/sailor-candidate/candidate/application_form_download.php` — all confirmed present.
- **Flags:** Debug leftovers (`echo/print_r/die()`) on some validation-failure paths — not dead code, but production debug scaffolding.

#### `CheckEligibilityController.php`
- **Class:** `frontend\controllers\CheckEligibilityController extends \yii\web\Controller`
- **behaviors():** Not defined. `beforeAction()` disables CSRF only for action id `get-description`.
- **Actions:**

| Action | Description |
|---|---|
| `actionIndex()` | Personal-info step. Forces logout if the logged-in user's `Session` row is invalid. Loads `CanEligibilityCheckInfo` (scenario `SCENARIO_PERSONAL_INFO`), converts height ft/in→cm, formats dob, saves, redirects to `check-eligibility/academic-info`. Renders `personal_info`. |
| `actionAcademicInfo($slug)` | Loads model (scenario `SCENARIO_ACADEMIC_INFO`), saves, redirects to `check-eligibility/eligible-department`; on validation failure dumps `print_r($model->attributes)` + `die()` (debug leftover). Renders `academic_info`. |
| `actionEligibleDepartment($slug)` | Computes candidate age against active `SailorBatchs` circular dates, queries `Eligibility` config rows by gender/marital/height/age/jsc result, cross-references active `SailorBatchConfiguration` by district/gender to build eligible-department options. Renders `eligible_department`. |
| `actionApplyDepartment($slug=null, $adpt=null)` | Validates the chosen department against a session-stored eligible list, resolves batch/center config, saves. Guest → redirect to sign-up with `ceci`; logged-in → creates a new `Sailors`/`DeSailors` row (one-application-per-batch check), redirects to payment. Debug `echo/print_r/die()` block on save failure. |
| `actionGetDescription()` | AJAX-only (CSRF-exempt). Returns a `CanDesignation` description as raw `json_encode`. Has an `exit();` after `return` — unreachable statement. |

- **Views:** `personal_info`, `academic_info`, `eligible_department` — all confirmed present in `frontend/views/check-eligibility/`.
- **Flags:** Unreachable `exit()` after `return` in `actionGetDescription()`. Debug dumps in `actionAcademicInfo`/`actionApplyDepartment`.

#### `DeSailorController.php`
- **Class:** `frontend\controllers\DeSailorController extends \yii\web\Controller`
- **behaviors():** Not defined. **No explicit access control** — relies on internal `Yii::$app->user->identity->id` checks (would fatal for guests, not a clean 403).
- **Actions:**

| Action | Description |
|---|---|
| `actionPayment($slug=null)` | Payment step 1. Computes live/sandbox payment mode from batch settings; supports retry via `SSLPayment::allRequestListByTranIds`; builds `payment_info`, session-stores it, redirects to `online-payment/payment-ssl`. Renders `payment`. |
| `actionAcademicInfo($slug=null)` | Loads course/trade dropdowns. Unless `skipTeleTalkValidation`, calls external `StaticMethod::educationBoardResult()` for SSC/HSC and validates GPA/group against `Eligibility::eligibilityBySession`. Computes age, advances phase to THREE, redirects to `de-sailor/personal-info`. Renders `academic_info`. |
| `actionPersonalInfo($slug=null)` | Photo upload → local temp → Cloudflare R2 via `Yii::$app->r2Storage`, deletes stale local/remote files. Encrypts PII via `DataEncryption::dataEncrypt`, updates `User.dob`, redirects to `de-sailor/application-preview`. Renders `personal_info`. Contains a large commented-out QR-code generation block (~lines 432–453). |
| `actionApplicationPreview($slug=null)` | Redirects to my-application if already has `serial_no`. Decrypts PII for display. Renders `application_preview`. |
| `actionCompleteApplication($slug=null)` | POST-only (`BadRequestHttpException` otherwise). Roll-number/exam-date allocation across `SailorBatchConfiguration`/`SailorBatchConfigurationExamDate`, deactivates exhausted configs, logs via `r2Storage->upsertCandidateLog` and `DeSailors::generateLog`, redirects to `de-sailor/download`. |
| `actionDownload($slug=null)` | Generates PDF via `Mpdf\Mpdf` from `renderPartial('candidate/application_form_pdf', ...)`, outputs inline, `exit()`. |
| `actionDownloadForm($slug=null)` | Same PDF generation, but looks up the model directly by decrypted slug with **no ownership check** (differs from `findModel()`). |
| `actionVerifyCandidate($slug=null)` | Public verification page, no ownership check. Renders `application_verify_preview`. Has ~18 lines of unreachable code after `return` (~1075–1092): a second, dead PDF-generation block. |

- **Views:** `payment`, `academic_info`, `personal_info`, `application_preview`, `application_verify_preview` (in `frontend/views/de-sailor/`), `candidate/application_form_pdf.php` / `candidate/application_verification_pdf.php` (in `frontend/views/de-sailor/candidate/`) — all confirmed present (the verification-PDF one is dead code so moot despite existing).
- **Notable side effects:** File upload + R2 cloud storage, mPDF generation with `curlAllowUnsafeSslRequests = true`, external SSC/HSC result API calls, cloud log upserts.
- **Flags:** Unreachable dead code block in `actionVerifyCandidate()`; commented-out QR generation in `actionPersonalInfo()`.

#### `MyApplicationController.php`
- **Class:** `frontend\controllers\MyApplicationController extends \yii\web\Controller`
- **behaviors():** Not defined.
- **Actions:**

| Action | Description |
|---|---|
| `actionIndex()` | Redirects guests to `candidate/login`. Else queries both `Sailors` and `DeSailors` for `created_by = current user`. Renders `index` with `model`/`model_de`. |

- **Views:** `index` → `frontend/views/my-application/index.php` — confirmed present.

#### `OnlinePaymentController.php`
- **Class:** `frontend\controllers\OnlinePaymentController extends \yii\web\Controller` — **the live file**.
- **behaviors():** Not defined. `beforeAction()` disables CSRF for `ssl-success`, `ssl-cancel`, `ssl-fail` (gateway callback endpoints, correctly exempted).
- **Actions:**

| Action | Description |
|---|---|
| `actionPaymentSsl()` | Reads session `payment_info`, calls `SSLPayment::requestInit()`, redirects to gateway URL or flashes error. Safe — working live path. |
| `actionSslSuccess()` | SSL gateway success callback. Parses `$_REQUEST`, decodes `value_b`, logs the user in from the callback payload (unauthenticated identity lookup — notable), updates `Sailors`/`DeSailors` payment fields, redirects to academic-info step. Safe. |
| `actionSslCancel()` | Logs user in from callback, flashes "Payment failed", redirects to my-application. Safe. |
| `actionSslFail()` | Same pattern as `actionSslCancel()`. Safe. |
| `actionPayment()` | **BROKEN** — calls `AamarPay::requestInit()` (line 176); `AamarPay` class does not exist. Fatal Error if hit. |
| `actionPaymentResponseDeSailor()` | **BROKEN** — references `AamarPay::AAMAR_PAY_SUCCESS` (line 188) and `ShurjoPayment::STORE_AMOUNT` (line 208); neither class exists. Fatal Error if hit. |
| `actionPaymentResponseSailor()` | **BROKEN** — same missing-class references (lines 233, 268). Fatal Error if hit. |

- **Views:** None (redirects / raw `header()` calls only).
- **Flags:** See "Broken references found" above (item 1) for full detail.

#### `OnlinePaymentController_shurjo_pay.php` — **DEAD FILE**
See Dead/Duplicate/Inert Code table above. Not counted as a live controller.

#### `SailorCandidateController.php`
- **Class:** `frontend\controllers\SailorCandidateController extends \yii\web\Controller`
- **behaviors():** **Defined** — the only frontend controller besides `SiteController` with real `AccessControl`:
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
  The filter's `only` scope is `['payment', 'academic-info']`, so the `verify-candidate` rule is declared but never actually enforced (dead configuration — `verify-candidate` ends up unrestricted/public by default anyway since it's outside `only`). All other actions (`personal-info`, `application-preview`, `complete-application`, `download`, `download-form`, `refund-phone`, `cancel-application`) have **no AccessControl coverage** at all.
- **Actions:**

| Action | Description |
|---|---|
| `actionPayment($slug=null)` | Sailor equivalent of `DeSailorController::actionPayment()`. Same pattern. Renders `payment`. |
| `actionAcademicInfo($slug=null)` | SSC/HSC teletalk-result validation (no skip branch, unlike DeSailor's). Redirects to `sailor-candidate/personal-info`. Renders `academic_info`. |
| `actionPersonalInfo($slug=null)` | Photo upload to R2, PII encryption, `User.dob` update, redirects to `sailor-candidate/application-preview`. Renders `personal_info`. |
| `actionApplicationPreview($slug=null)` | Decrypts PII, renders `application_preview`. |
| `actionCompleteApplication($slug=null)` | POST-only. Roll-number/exam-date allocation (near-identical to DeSailor's, with additional `next_start_roll` override logic). Has a commented-out SMS call (`SendSms::sendSms`). Redirects to `sailor-candidate/download`. |
| `actionDownload($slug=null)` | mPDF generation, inline output, `exit()`. |
| `actionDownloadForm($slug=null)` | Same PDF generation, direct lookup (no ownership check), prepends `+88` to phone numbers. |
| `actionVerifyCandidate($slug=null)` | Public verification, no ownership check. Renders `application_verify_preview`. Has ~17 lines of unreachable dead code after `return` (~1051–1067) — a second mPDF block. |
| `actionRefundPhone()` | AJAX-only, logged-in only. Validates BD phone regex, encrypts, saves to `Sailors.refund_phone` scoped to `created_by`. JSON response. |
| `actionCancelApplication()` | AJAX-only. Reads raw JSON body, sets `request_for_cancel=1`/`reason` on the ownership-scoped `Sailors` row. JSON response. |

- **Non-action helper:** `findModel($id)` (protected).
- **Views:** `payment`, `academic_info`, `personal_info`, `application_preview`, `application_verify_preview` (in `frontend/views/sailor-candidate/`), `candidate/application_form_pdf.php` / `candidate/application_verification_pdf.php` — all confirmed present (verification-PDF call is dead code).
- **Flags:** Unreachable PDF block in `actionVerifyCandidate()`; dead `verify-candidate` AccessControl rule.

#### `SiteController.php`
- **Class:** `frontend\controllers\SiteController extends \yii\web\Controller`
- **behaviors():**
  ```php
  'access' => [
      'class' => AccessControl::class,
      'only' => ['logout', 'signup'],
      'rules' => [
          ['actions' => ['signup'], 'allow' => true, 'roles' => ['?']],
          ['actions' => ['logout'], 'allow' => true, 'roles' => ['@']],
      ],
  ],
  'verbs' => [
      'class' => VerbFilter::class,
      'actions' => ['logout' => ['post']],
  ],
  ```
  All other actions (`index`, `form`, `login`, `contact`, `about`, `request-password-reset`, `reset-password`, `verify-email`, `resend-verification-email`) are outside `only` → unrestricted/public.
- **actions() overrides:** `error` (`\yii\web\ErrorAction`), `captcha` (`\yii\captcha\CaptchaAction`).
- **Actions:**

| Action | Description |
|---|---|
| `actionIndex()` | Renders homepage `index`. |
| `actionForm()` | Renders `form_c`. |
| `actionLogin()` | Guest-only. `LoginForm` (user_type=candidate), `goBack()` on success. Renders `login`. |
| `actionLogout()` | Logs out, `goHome()`. |
| `actionContact()` | `ContactForm`, sends email via `$model->sendEmail(...)`, flashes, refreshes. Renders `contact`. |
| `actionAbout()` | Renders `about`. |
| `actionSignup()` | `SignupForm::signup()`, flashes, `goHome()`. Renders `signup`. |
| `actionRequestPasswordReset()` | `PasswordResetRequestForm::sendEmail()` (email side effect), flashes, `goHome()`. Renders `requestPasswordResetToken`. |
| `actionResetPassword($token)` | `ResetPasswordForm($token)` (throws on invalid token), `resetPassword()`, flashes, `goHome()`. Renders `resetPassword`. |
| `actionVerifyEmail($token)` | `VerifyEmailForm($token)::verifyEmail()` + login, flashes, `goHome()`. |
| `actionResendVerificationEmail()` | `ResendVerificationEmailForm::sendEmail()` (email side effect), flashes, `goHome()`. Renders `resendVerificationEmail`. |

- **Views:** All 9 render targets confirmed present in `frontend/views/site/`.

---

### `backend/controllers/`

No shared custom base controller exists — every controller extends `\yii\web\Controller` directly.

#### `AjaxController.php`
- **Class:** `backend\controllers\AjaxController extends \yii\web\Controller`
- **behaviors():** Not defined. Only `beforeAction()` disabling CSRF. **No access control, no verb filter, on any action.**
- **Actions:**

| Action | Description |
|---|---|
| `actionGetSailorInformationByRoll()` | AJAX lookup, returns JSON Sailors data by `serial_no`, resolves district slugs to names. |
| `actionGetDeSailorInformationByRoll()` | Same, for DeSailors. |
| `actionGetCandesignationByCantype()` | Returns active CAN designations for a candidate type. |
| `actionGetAllAssignedDistrictByCenter()` | Returns districts mapped to a center. |
| `actionDecodePhone()` | Batch data-migration: pulls up to 5000 Sailors rows with encrypted phone but no `permanent_phone_de`, decrypts via `DataEncryption::dataDecrypt`, re-encrypts via `AES256CTR::dataEncrypt`, writes back via raw SQL `UPDATE` per row. **Unauthenticated batch-mutation endpoint over PII.** |

- **Views:** None — JSON responses.
- **Flags:** No `behaviors()` at all — every action including the raw-SQL PII re-encryption job is unauthenticated.

#### `BulkCheckController.php` — **inert stub**, see Dead/Duplicate/Inert Code above.

#### `CanDesignationController.php`
- **Class:** `backend\controllers\CanDesignationController extends Controller`
- **behaviors():** `VerbFilter` only (`delete` → POST). No AccessControl.
- **Actions:** Standard Gii CRUD — `actionIndex()`, `actionView($id)`, `actionCreate()` (flash on success), `actionUpdate($id)` (flash on success), `actionDelete($id)`. Plus `findModel($id)` (protected, not an action).
- **Views:** `index`, `view`, `_form` (create+update) — all confirmed present in `backend/views/can-designation/`.

#### `DeSailorBranchController.php`
- **Class:** `backend\controllers\DeSailorBranchController extends Controller`
- **behaviors():** `VerbFilter` only (`delete` → POST). No AccessControl.
- **Actions:** Standard CRUD — `actionIndex()`, `actionView($id)`, `actionCreate()`, `actionUpdate($id)` (renders `update`, not `_form`), `actionDelete($id)`, `findModel($id)`.
- **Views:** **All 4 render targets broken** — see "Broken references found" item 2 above.

#### `DeSailorReportController.php`
- **Class:** `backend\controllers\DeSailorReportController extends \yii\web\Controller`
- **behaviors():** Not defined. **No access control on any report/export action.**
- **Uses:** `PhpOffice\PhpSpreadsheet\{Spreadsheet, Writer\Xlsx}`, `\Mpdf\Mpdf` (fully-qualified).
- **Actions:**

| Action | Description |
|---|---|
| `actionPayment()` | Filters `DeSailors` by batch/payment_type/is_paid, session-stores results+filters. Renders `payment_report`. |
| `actionPaymentPdf()` | mPDF export of session-stored payment report → `pdf/payment_report_pdf`. |
| `actionPaymentExcel()` | **Stub** — xlsx export writes only a literal `"Hello World !"` placeholder cell, then streams+deletes the file. |
| `actionCandidateFilter()` | Filters `DeSailors` by batch/center/district/designation/exam_date/gender, session-stores results+filters. Renders `candidate_filter`. |
| `actionCandidateFilterPdf()` | mPDF export of the filtered list → `pdf/candidate_filter_pdf`. |
| `actionCandidateFilterExcel()` | xlsx export with photo embedding per row, decrypted phone, gender/district formatting. |
| `actionMonitoringApplication()` | Finds `DeSailors` candidates missing exam photos for a batch/create_date (file-existence check against upload dir). Renders `monitoring_application`. |
| `actionReferenceCandidatePdf()` | mPDF export of reference candidates filtered by session-stored `SailorsSearch` params (requires batch+district or `die()`s with an error string). |
| `actionReferenceDeCandidatePdf()` | Same as above but sourced from session-stored `DeSailorsSearch` params. |
| `actionReferenceCandidateExcel()` | **Near-stub** — xlsx export writes only `"We are working"` placeholder text plus real query/filename logic. |

- **Views:** `payment_report`, `candidate_filter`, `monitoring_application` and `pdf/payment_report_pdf`, `pdf/candidate_filter_pdf`, `pdf/reference_candidate_pdf` (used 2x, shared by both `actionReferenceCandidatePdf` and `actionReferenceDeCandidatePdf`) — all confirmed present in `backend/views/de-sailor-report/`.
- **Flags:** `actionPaymentExcel()` and `actionReferenceCandidateExcel()` are placeholder/incomplete exports (literal placeholder text instead of real spreadsheet data). No access control on any action, including bulk PII export.

#### `DeSailorsController.php`
- **Class:** `backend\controllers\DeSailorsController extends Controller`
- **behaviors():** `VerbFilter` only (`delete` → POST). No AccessControl.
- **Actions:**

| Action | Description |
|---|---|
| `actionIndex()` | CRUD list ordered by `serial_no DESC`, loads `Subjects::getAllSubject()` for a filter dropdown. |
| `actionView($id)` | Standard. |
| `actionCreate()` | Standard create. **Renders `create` — broken, see item 4 above.** |
| `actionUpdate($id)` | Custom: decrypts PII for display, photo upload to R2 with old-file cleanup, sets `phase` to `SAILOR_PHASE_TWO` on manual payment, re-encrypts PII before save. |
| `actionReferenceCandidate()` | Lists DeSailors with `have_reference = YES`, session-stores query params for export actions. |
| `actionAddReferenceCandidate()` | AJAX-validate + POST appends reference/relationship/details entries to JSON-encoded columns (looked up by `serial_no`). |
| `actionReferenceCandidateUpdate($id)` | Edits/clears reference JSON columns on a `DeSailorsReference` record. |
| `actionDelete($id)` | Standard. |

- **Non-action helper:** `findModel($id)` (protected).
- **Views:** `index`, `view` ✓; **`create` MISSING (broken)**; `_form` (update) ✓; `reference/reference_candidate`, `reference/add_reference_candidate`, `reference/update_reference_candidate` ✓. Note: `backend/views/de-sailors/reference/02092025_reference_candidate.php` also exists but is unreferenced by this controller — a dated leftover/backup view file.

#### `DistrictsController.php`
- **Class:** `backend\controllers\DistrictsController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:** Standard CRUD — `actionIndex`, `actionView($id)`, `actionCreate()`, `actionUpdate($id)`, `actionDelete($id)`, `findModel($id)`.
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/districts/`.

#### `EligibilityController.php`
- **Class:** `backend\controllers\EligibilityController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:** Standard CRUD plus `actionUpdate($id)` has custom logic (`$model::implodeFieldList()` re-explodes comma-separated multi-select fields for the form).
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/eligibility/`.

#### `LogReportController.php`
- **Class:** `backend\controllers\LogReportController extends \yii\web\Controller`
- **behaviors():** Not defined — no access control.
- **Actions:**

| Action | Description |
|---|---|
| `actionSiteActivity()` | Audit-log report. Builds a `DynamicModel(['data','method','controller'])`; on POST resolves an ndjson log path (`action_log/<controller>/add\|update.ndjson` or dated), reads it via `Yii::$app->r2Storage->getLogFileContents()`, parses/groups entries by `update_id`, excludes `site/login`. Renders `report`. |
| `actionSiteActivityView(...)` | Re-reads the same ndjson source filtered to one route/update_id, manually builds an HTML `<table>` via `ob_start()`/`echo` (modal detail view — not a `render()` call, no view file expected). |

- **Non-action helper:** `collectChanges($old, $new, $path)` (private, recursive JSON-diff helper).
- **Views:** `report` → `backend/views/log-report/report.php` — confirmed present.

#### `ReportController.php` (1,605 lines — largest controller in the repo)
- **Class:** `backend\controllers\ReportController extends \yii\web\Controller`
- **behaviors():** Not defined — **no access control on any report/export action**, including the hardcoded-user-ID gate described below.
- **Uses:** `PhpOffice\PhpSpreadsheet\{Spreadsheet, Writer\Xlsx, Style\Alignment, Style\Color, RichText\RichText, RichText\Run}`, `PhpOffice\PhpWord\{PhpWord, IOFactory}`, `\Mpdf\Mpdf`.
- **Actions (23 total):**

| Action | Description |
|---|---|
| `actionPayment()` | Filters Sailors by batch/payment_type/is_paid, session-stores. Renders form+results. |
| `actionPaymentPdf()` | mPDF export of the session-stored payment report. |
| `actionPaymentExcel()` | **Stub** — writes only `"Hello World !"` placeholder cell. |
| `actionCandidateFilter()` | Main candidate filter/search (batch/center/district/designation/exam_date/gender/ssc_group/father_occupation/serial_no); session-stores. |
| `actionCandidateFilterPdf()` | mPDF export of filtered candidates. |
| `actionCandidateFilterExcel()` | xlsx export with logo, SSC subject-grade breakdown, per-row photo embedding, decrypted phone. |
| `actionMonitoringApplication()` | Finds candidates missing exam photos (checks R2 existence per record, flags `is_image_exist_check` via raw SQL) for a batch/create_date. |
| `actionExamDateCheck()` | **Renders a filter form only — no POST handling or query logic.** Incomplete/stub action. |
| `actionAllReferenceCandidateExcel()` | xlsx export of all reference candidates for the last search's center, with branch/designation summary header. |
| `actionReferenceCandidatePdf()` | mPDF export of reference candidates filtered by session-stored search params (requires center+exam_date). |
| `actionReferenceDeCandidatePdf()` | Same, sourced from `DeSailors`/`DeSailorsSearch` session params. |
| `actionReferenceCandidateExcel()` | xlsx export of reference candidates with center/date summary header, photo embedding via R2 URL. |
| `actionDistrictCandidate()` | Groups paid/active Sailors by `candidate_designation` count, filtered by batch/center/district; session-stores. |
| `actionCenterDateCandidate()` | Same grouping, filtered by batch/center/exam_date. |
| `actionExamDateCenterCandidatePdf()` | mPDF export of session-stored center/date candidate counts. |
| `actionDistrictCandidatePdf()` | mPDF export of session-stored district candidate counts. |
| `actionDistrictCandidateExcel()` | xlsx export with logo + batch/district header, designation counts + total row. |
| `actionReferenceCandidateWord()` | **DOCX export** via PhpWord — landscape table of reference candidates (roll/mobile/district/description/reference/relationship/subject/photo), streamed as `.docx`. |
| `actionCenterCandidate()` | Groups paid/active Sailors by designation for a batch/center/exam_date; session-stores. |
| `actionCenterCandidatePdf()` | mPDF export of session-stored center candidate counts. |
| `actionCenterCandidateExcel()` | xlsx export with logo + batch/center header, counts + total row. |
| `actionSameAcademicInfo()` | Renders a form (`same_academic_info`) with today's date pre-filled — **no server-side query logic in the action** (filtering presumably client-side/AJAX, not visible in this file). |
| `actionJsonForLs()` | **Gated to `Yii::$app->user->id == 1` only** (hardcoded single-user check, not role-based). Renders `json_for_ls` with empty data arrays — logic appears to live client-side. |

- **Non-action helpers:** `getBranch()`, `getDescription()`, `getReference()`, `getRelation()` (formatting helpers reused across exports), `folderTurncate()` (**defined, never called — dead code**).
- **Views:** `payment_report`, `candidate_filter`, `monitoring_application`, `exam_date_check_by_center_designation`, `district_candidate`, `center_date_candidate`, `center_candidate`, `same_academic_info`, `json_for_ls` and `pdf/payment_report_pdf`, `pdf/candidate_filter_pdf`, `pdf/reference_candidate_pdf` (x2), `pdf/exam_date_center_candidate_pdf`, `pdf/district_candidate_pdf`, `pdf/center_candidate_pdf` — all confirmed present in `backend/views/report/` and `backend/views/report/pdf/`.
- **Flags:** `actionPaymentExcel()` is a non-functional stub. `actionExamDateCheck()` and parts of `actionSameAcademicInfo()`/`actionJsonForLs()` are render-only/incomplete. `folderTurncate()` unused. No access control anywhere, including the hardcoded-user-ID gate on `actionJsonForLs()`.

#### `SailorBatchConfigurationController.php`
- **Class:** `backend\controllers\SailorBatchConfigurationController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:** `actionIndex()`, `actionView($id)`, `actionCreate()` (AJAX-validate branch, then processes nested `SailorBatchConfigurationExamDate` rows via `batchConfigurationExamDate()`), `actionUpdate($id)` (same, plus re-explodes multi-select fields), `actionDelete($id)`, `actionDeleteExamDate()` (AJAX JSON endpoint deleting one exam-date sub-row).
- **Non-action helpers:** `batchConfigurationExamDate($post, $batch_configuration_id)` (protected), `findModel($id)` (protected).
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/sailor-batch-configuration/`.

#### `SailorBatchsController.php`
- **Class:** `backend\controllers\SailorBatchsController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:** `actionIndex()`, `actionView($id)`, `actionCreate()` (handles `circular_media` file upload to `/media/sailor_circular/`), `actionUpdate($id)` (same upload handling, formats circular dates), `actionDelete($id)`.
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/sailor-batchs/`.
- **Flags:** `$prevImage` is referenced in `actionCreate()`'s upload-cleanup branch but only ever defined in `actionUpdate()` — a latent undefined-variable reference, silently guarded by `!empty($prevImage)` so it just skips cleanup on create rather than erroring.

#### `SailorCentDistMappingController.php`
- **Class:** `backend\controllers\SailorCentDistMappingController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:** Standard CRUD; `actionUpdate($id)` explodes `district_slug` CSV into an array for the form.
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/sailor-cent-dist-mapping/`.

#### `SailorCentersController.php`
- **Class:** `backend\controllers\SailorCentersController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:** Fully standard CRUD, no custom logic beyond flash messages.
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/sailor-centers/`.

#### `SailorsController.php`
- **Class:** `backend\controllers\SailorsController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:**

| Action | Description |
|---|---|
| `actionIndex()` | List ordered by `serial_no DESC`. |
| `actionCancelRequest()` | Lists Sailors with `request_for_cancel = 1`, reuses `index` view with `is_cancel_request = true`. |
| `actionReferenceCandidate()` | Lists Sailors with reference, session-stores query params. |
| `actionAddReferenceCandidate()` | AJAX-validate + POST appends reference/relationship/details/reference_add_on JSON arrays (by `serial_no`), tracks `last_reference_added` timestamp. |
| `actionReferenceCandidateUpdate($id)` | Edits/clears reference JSON fields on a `SailorsReference` record. |
| `actionView($id)` | Standard. |
| `actionUpdate($id)` | Decrypts PII, photo upload to R2 with cleanup, sets `phase` on manual payment, re-encrypts before save. |

- **Non-action helper:** `findModel($id)` (protected).
- **Views:** `index` (index + cancel-request), `reference/reference_candidate`, `reference/add_reference_candidate`, `view`, `_form` (update), `reference/update_reference_candidate` — all confirmed present.
- **Flags:** `actionCreate()` and `actionDelete()` are fully commented out — see Dead/Duplicate/Inert Code above.

#### `SiteController.php`
- **Class:** `backend\controllers\SiteController extends Controller`
- **behaviors():**
  ```php
  'access' => [
      'class' => AccessControl::class,
      'rules' => [
          ['actions' => ['login', 'error'], 'allow' => true],
          ['actions' => ['logout', 'index'], 'allow' => true, 'roles' => ['@']],
      ],
  ],
  'verbs' => [
      'class' => VerbFilter::class,
      'actions' => ['logout' => ['post']],
  ],
  ```
  The only backend controller with a complete, role-gated AccessControl setup.
- **actions() override:** `error` → `\yii\web\ErrorAction`.
- **Actions:**

| Action | Description |
|---|---|
| `actionIndex()` | Dashboard — builds chart data from `DeSailors` counts grouped by `serial_generate_date`, and from `CanEligibilityCheckInfo` via raw SQL (hardcoded date filter `'2026-05-02'` in the WHERE clause — will need periodic updating). Passes 3 chart datasets to the view. |
| `actionLogin()` | Custom login with a math CAPTCHA plus `LoginForm`. |
| `actionLogout()` | Logout + redirect home. |

- **Non-action helper:** `captureValue()` (protected, CAPTCHA generator).
- **Views:** `index`, `login` (confirmed present in `backend/views/site/`); `error` view exists, used implicitly by `ErrorAction`.
- **Flags:** `actionIndex()` has a large commented-out legacy chart-query block (~lines 72–97) and a commented-out `CanEligibilityCheckInfo` query attempt (~lines 120–127) — leftover exploration code.

#### `SubjectsController.php`
- **Class:** `backend\controllers\SubjectsController extends Controller`
- **behaviors():** `VerbFilter` only. No AccessControl.
- **Actions:** Fully standard CRUD, no custom logic beyond flash messages.
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/subjects/`.

#### `TeController.php` — **inert stub**, see Dead/Duplicate/Inert Code above.

#### `UnionsController.php`
- **Class:** `backend\controllers\UnionsController extends Controller`
- **behaviors():**
  ```php
  'access' => [
      'class' => AccessControl::class,
      'rules' => [
          ['allow' => true, 'roles' => ['@']],
          ['allow' => false],
      ],
  ],
  'verbs' => ['delete' => ['POST']],
  ```
  One of only two backend CRUD controllers with an explicit AccessControl (the trailing `allow => false` is a redundant fallback given the prior allow-all-authenticated rule).
- **Actions:** Fully standard CRUD. Note: `actionUpdate` does not reload `$model` after save (unlike sibling controllers), otherwise standard.
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/unions/`.

#### `UpozilasController.php`
- **Class:** `backend\controllers\UpozilasController extends Controller`
- **behaviors():** Same AccessControl pattern as `UnionsController` (allow-authenticated + redundant deny-all fallback) plus `VerbFilter` (`delete` → POST).
- **Actions:** Fully standard CRUD, no custom logic beyond flash messages.
- **Views:** `index`, `view`, `_form` — all confirmed present in `backend/views/upozilas/`.

#### `UserController.php`
- **Class:** `backend\controllers\UserController extends Controller`
- **behaviors():** `VerbFilter` only (`delete` → POST). **No AccessControl — notable, since this controller manages admin user accounts and password hashes.**
- **Actions:**

| Action | Description |
|---|---|
| `actionIndex()` | Standard list. |
| `actionView($id)` | Standard. |
| `actionCreate()` | Standard create. **Renders `create` — broken, see item 3 above.** Redirects to `view` on success. |
| `actionUpdate($id)` | Custom: if `password_hash` is posted, re-hashes via `Yii::$app->security->generatePasswordHash()` before save; clears `password_hash` before render so it's never echoed back into the form. |

- **Non-action helper:** `findModel($id)` (protected).
- **Views:** `index`, `view` ✓; **`create` MISSING (broken)**; `_form` (update) ✓.
- **Flags:** `actionDelete($id)` fully commented out (see Dead/Duplicate/Inert Code). No AccessControl on an account/password-management controller.

---

### `console/controllers/`

Directory contains only a `.gitkeep` file — **no custom console controllers exist**. `console/config/main.php` maps a single `fixture` command to Yii's built-in `\yii\console\controllers\FixtureController`; nothing else is registered. No cron-style commands, no data-migration console commands beyond the framework default.

---

## Models

| Layer | Path | Count |
|---|---|---|
| Common | `common/models/` | 27 (incl. `common/models/scopeQuery/SailorBatchs.php`, `common/models/payment/SSLPayment.php` in subfolders) |
| Frontend | `frontend/models/` | 7 |
| Backend | `backend/models/` | 12 |
| Console | `console/models/` | 0 |
| **Total** | | **46** |

Detailed per-model documentation is out of scope here — see `model_inventory.md`.

## Services (closest Yii2 equivalents)

Yii2 has no dedicated Services layer in this codebase. The closest equivalents, by directory:

| Path | Count | Contents |
|---|---|---|
| `common/components/` | 1 | `R2Storage.php` — Cloudflare R2 (S3-compatible) storage component, registered as `Yii::$app->r2Storage`, used throughout the candidate-facing controllers for photo upload/delete and audit-log upserts. |
| `common/widgets/` | 1 | `Alert.php` — renders session flash messages. |
| `common/enumClass/` | 1 | `Status.php` — a PHP 8.1 enum (`ACTIVE`/`INACTIVE`) with display helpers. |
| `common/static/` | 5 | Static-helper-class layer heavily used across almost every controller above: `Constants.php`, `StaticMethod.php`, `AES256CTR.php`, `DataEncryption.php`, `SendSms.php`. Functions as the de facto "services" layer for this app (encryption, SMS, misc constants/helpers) even though it's not named that way. |

Detailed per-component documentation is out of scope here — see `component_inventory.md` / `service_inventory.md`.

## Jobs

**None found.** Grepped the whole repo (excluding `vendor/`) for `yii\queue` usage — zero matches. `console/controllers/` has no custom commands (see above). There is no cron-style background-job mechanism in this codebase; batch/bulk operations that exist (e.g. `AjaxController::actionDecodePhone()` in `backend/`) run synchronously as a web request, not as a queued job.

---

## Notes / follow-ups worth flagging separately

- **Access-control inconsistency:** Across all 31 controllers, only `frontend/controllers/SiteController.php`, `frontend/controllers/SailorCandidateController.php` (partial — one dead rule), `backend/controllers/SiteController.php`, `backend/controllers/UnionsController.php`, and `backend/controllers/UpozilasController.php` define any `AccessControl`. Every other controller — including `backend/controllers/UserController.php` (manages admin accounts/password hashes), `backend/controllers/AjaxController.php` (unauthenticated raw-SQL PII batch mutation), and all of `ReportController.php` / `DeSailorReportController.php` / `LogReportController.php` (bulk PII export/reporting) — has no `AccessControl` at all, relying only on `VerbFilter` or nothing. This may be covered by a global filter/module config outside the controller files themselves (not reviewed as part of this inventory) — worth confirming with whoever owns deployment/security config.
- Three "stub" controllers exist purely as scaffolding with zero implemented actions: `frontend/controllers/ApiController.php`, `backend/controllers/BulkCheckController.php`, `backend/controllers/TeController.php`.
- Recommend deleting `frontend/controllers/OnlinePaymentController_shurjo_pay.php` (confirmed dead/unreachable via Yii2's `@frontend`-alias autoloader) after a version-control safety check — this repo is under git, so it's already recoverable via history.
- The `AamarPay`/`ShurjoPayment` broken references in the live `OnlinePaymentController.php` are the most severe finding: three reachable, unauthenticated payment-callback actions will hard-fail with a PHP Fatal Error if a real AamarPay/ShurjoPay transaction ever routes through them. Given the working SSL-prefixed actions exist alongside them, this reads as an incomplete migration away from AamarPay/ShurjoPay to SSLCommerz — worth confirming whether these three actions are still linked from any live payment-gateway configuration (IPN/webhook URLs) or are truly orphaned.
