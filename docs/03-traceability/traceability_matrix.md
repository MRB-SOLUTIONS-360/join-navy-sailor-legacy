# Traceability Matrix — join-navy-sailor-legacy

Route (implicit `controller-id/action-id`) → Controller::action → Model(s)/Service(s) touched → View rendered → Asset bundle/JS-CSS loaded, for every **live** action across all 31 controllers (10 frontend + 21 backend). Ground truth is `docs/00-inventories/controller_inventory.md` (541 lines, every action + view-existence check already verified against the actual controller/view source), cross-checked against `route_inventory.md`, `model_inventory.md`, `view_inventory.md`, `javascript_inventory.md`, `css_inventory.md`, `component_inventory.md`. Two model classes used by `DeSailorBranchController` (`common\models\DeSailorBranch`, `backend\models\DeSailorBranchSearch`) were not itemized in `model_inventory.md`'s per-file tables — confirmed directly via `grep` against the controller source for this doc.

This is a **Yii2 2.0 advanced-template** app, not Laravel — there is no `routes/web.php` to read a route list off. Both apps' `urlManager.rules` are `[]`, so every route is the framework's default `/{controller-id}/{action-id}` → `{Namespace}\{ControllerId}Controller::action{ActionId}()` convention (see `route_inventory.md`). The controller + action inventory **is** the route list. **171 live, routable actions** total (53 frontend + 118 backend) per `route_inventory.md`'s summary tally, plus 2 non-`action*` routes contributed by each app's `SiteController::actions()` override (`site/error`, `site/captcha` on the frontend app; `site/error` on the backend app) which that tally deliberately excludes from its 53/118 count but which are still real, reachable routes — both are included as their own rows below.

Dead/inert files are **excluded** from the trace entirely (no row) even though they are physically present on disk: `OnlinePaymentController_shurjo_pay.php` (dead file — same FQCN as the live `OnlinePaymentController`, never autoloaded), `ApiController.php`, `BulkCheckController.php`, `TeController.php` (all three: zero `action*` methods, unreachable), and the commented-out `SailorsController::actionCreate()`/`actionDelete()` and `UserController::actionDelete()` (unreachable — no route resolves to a commented-out method). The 3 AamarPay/ShurjoPayment-dependent actions in the **live** `OnlinePaymentController.php` (`actionPayment`, `actionPaymentResponseDeSailor`, `actionPaymentResponseSailor`) are routable but reference two classes (`AamarPay`, `ShurjoPayment`) that do not exist anywhere in the repo — these are listed once in the dead-code table below, not given full trace rows, since hitting them is a guaranteed PHP Fatal Error, not a functioning code path. Three further routable-but-broken actions (`DeSailorBranchController`'s CRUD actions, `UserController::actionCreate()`, `DeSailorsController::actionCreate()`) reference `render()` targets whose view files do not exist on disk — these **are** given full trace rows, flagged **BROKEN**, per the difference between "unreachable" (excluded) and "reachable but crashes" (traced-and-flagged).

---

## Legend — asset bundle shorthand

No bundler (no `package.json`, no Vite/webpack) anywhere in this repo — 100% of JS/CSS is delivered via Yii2's native `AssetBundle::register()` mechanism plus two hardcoded `<script src>` tags in `backend/views/layouts/admin.php`. See `javascript_inventory.md` / `css_inventory.md` for full detail.

| Code | Meaning |
|---|---|
| **FE** | `frontend/assets/AppNavyAsset.php`, registered by `frontend/views/layouts/mainNavy.php` (the frontend app's only layout, default for every `frontend/*` view). JS: `navy/js/{jquery-3.6.0.min.js, bootstrap.min.js, wow.min.js, main.js}`. CSS: `navy/css/{boxicons.min.css, bootstrap-icons.css, bootstrap.min.css, animate.css, style.css, responsive.css, style_step.css}`. `$depends` is fully commented out, so `yii.js`/`yii.activeForm.js` (Yii2's client-side ActiveForm validation) is **never loaded** on any public page — this is why almost every form-heavy public view hand-rolls its own jQuery AJAX validation inline instead (see FE+INLINE). |
| **FE+INLINE** | FE **+** a page-specific inline `<script>` block hand-written into that view (AJAX submit/validation, dependent-dropdown population, height calculators, etc. — see `javascript_inventory.md`'s "Inline `<script>` blocks in views" section, 11 frontend files total incl. the layout's own GA/mobile-menu script). |
| **ADM** | `backend/assets/AppAdminAsset.php`, registered by `backend/views/layouts/admin.php` (the backend app's default layout, `'layout' => 'admin'`). "Hyper" Bootstrap 5 admin theme (Coderthemes). CSS: `adminAsset/css/{app.css, icons.min.css}`. JS: `adminAsset/js/{vendor.min.js, hyper-config.js}` (both hardcoded `<script src>` tags in `admin.php`, **outside** the asset-bundle system — `vendor.min.js` bundles jQuery 3.6.0 + Bootstrap 5.2.3 + SimpleBar) **+** `adminAsset/js/app.min.js`, `vendor/daterangepicker/{moment.min.js,daterangepicker.js}`, `vendor/apexcharts/apexcharts.min.js` via the bundle's own `$js` array. The daterangepicker/apexcharts JS ships on every admin page but is dead weight — no view in `backend/views` references either plugin. `AppAdminAsset`'s `$depends` also pulls `yii\web\YiiAsset` (bower jQuery), so **jQuery loads twice** on every admin page. |
| **ADM+INLINE** | ADM **+** a page-specific inline `<script>`/`$this->registerJs(...)` block (CRUD-grid AJAX, reference-candidate form logic, dashboard chart wiring — 15 backend files total incl. the layout's one-line footer-year script; see `javascript_inventory.md`). |
| **ADM-LOGIN** | `backend/assets/AppAsset.php`, registered by `backend/views/layouts/blank.php` — used **only** by `SiteController::actionLogin()` (`$this->layout = 'blank'`). Unmodified Yii2-scaffold `css/site.css` (1.6 KB) **+** `yii\web\YiiAsset` (bower jQuery) **+** `yii\bootstrap5\BootstrapAsset` (bower Bootstrap). |
| **PDF** | No JS/CSS bundle — standalone full-HTML document (own `<!doctype html>`, own inline `<style>` block only) rendered outside any layout, captured to a PDF stream by `\Mpdf\Mpdf` (`curlAllowUnsafeSslRequests = true`). |
| **—(redirect)** | Action only calls `$this->redirect(...)`/`goHome()`/`goBack()`; no view rendered by this action. |
| **—(JSON)** | Returns `Yii::$app->response->format = Response::FORMAT_JSON` or `json_encode(...)` directly — AJAX endpoint, no PHP view, no asset bundle. |
| **—(raw echo)** | Dumps `echo`/`print_r` output directly, or a debug `die()` block — no view, no asset bundle. |
| **—(binary download)** | Streams a generated `.xlsx` (`PhpOffice\PhpSpreadsheet`), `.docx` (`PhpOffice\PhpWord`), or `.csv` file with a `Content-Disposition` header — no HTML view, no asset bundle. |

**Access control**: **Backend app** wires a **global** `AccessControl` filter at the application level (`'as access' => [...]` in `backend/config/main.php`, per `route_inventory.md`), allowing only `site/login` and `site/error` unauthenticated — **every other backend route below requires an authenticated backend user regardless of that controller's own `behaviors()`.** Only `backend/controllers/SiteController.php`, `UnionsController.php`, and `UpozilasController.php` additionally define their own `AccessControl` (redundant with the global filter); every other backend controller relies on `VerbFilter` only (or nothing at all — `AjaxController`, `ReportController`, `DeSailorReportController`, `LogReportController` have zero access control of any kind beyond the app-level filter, which still applies since it's app-wide). **Frontend app has no equivalent global filter** — access is per-controller: `SiteController` (`only=>['logout','signup']`), `SailorCandidateController` (`only=>['payment','academic-info']`, roles `@`), and ad-hoc `Yii::$app->user->isGuest`/`identity->id` checks inside `CandidateController`, `CheckEligibilityController`, `DeSailorController` action bodies (not a clean 403 — would fatal for guests on some actions). `AjaxController` (both apps) explicitly disables CSRF in `beforeAction()` and has no access control at all.

---

## Dead / excluded / broken code (not fully traced below)

| Item | Why | Disposition |
|---|---|---|
| `frontend/controllers/OnlinePaymentController_shurjo_pay.php` | Dead file — declares the exact same FQCN as the live `OnlinePaymentController.php`; Yii2's `@frontend`-alias autoloader only ever resolves that class to `OnlinePaymentController.php`. Zero references anywhere in the repo. | **Excluded** — no rows |
| `frontend/controllers/OnlinePaymentController.php::actionPayment()` | References `common\models\payment\AamarPay::requestInit()` — class does not exist anywhere in the repo | **Excluded from full trace, flagged dead** — route `online-payment/payment` resolves to this method; hitting it is a guaranteed PHP Fatal Error |
| `frontend/controllers/OnlinePaymentController.php::actionPaymentResponseDeSailor()` | References `AamarPay::AAMAR_PAY_SUCCESS` + `ShurjoPayment::STORE_AMOUNT` — neither class exists | **Excluded from full trace, flagged dead** — route `online-payment/payment-response-de-sailor` |
| `frontend/controllers/OnlinePaymentController.php::actionPaymentResponseSailor()` | Same missing-class references | **Excluded from full trace, flagged dead** — route `online-payment/payment-response-sailor` |
| `frontend/controllers/ApiController.php` | Extends `yii\rest\Controller` (not `ActiveController`), zero `action*` methods, no `actions()` override | **Excluded** — every request 404s |
| `backend/controllers/BulkCheckController.php` | Empty class body, zero actions | **Excluded** — every request 404s |
| `backend/controllers/TeController.php` | Empty class body, zero actions | **Excluded** — every request 404s |
| `backend/controllers/SailorsController.php::actionCreate()` / `::actionDelete()` | Fully commented out in source | **Excluded** — not reachable, no route resolves to a commented-out method |
| `backend/controllers/UserController.php::actionDelete()` | Fully commented out in source | **Excluded** — `user/delete` not reachable |

**Broken but routable — traced below with a BROKEN flag** (view file missing, everything else about the action works): `backend/controllers/DeSailorBranchController.php` (all 4 render-producing actions — the entire `backend/views/de-sailor-branch/` directory is absent from the repo), `backend/controllers/UserController.php::actionCreate()` (`backend/views/user/create.php` missing), `backend/controllers/DeSailorsController.php::actionCreate()` (`backend/views/de-sailors/create.php` missing).

---

# Part A — Frontend (`frontend/controllers/`, namespace `frontend\controllers`)

9 live controllers (of 10 files), 53 live `action*` methods + 2 framework sub-actions (`site/error`, `site/captcha`).

## A1. `AjaxController` — `frontend/controllers/AjaxController.php`

No `behaviors()`. `beforeAction()` unconditionally disables CSRF. **No access control at all — every action below is public.**

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `ajax/district-by-candidate-type` | `AjaxController::actionDistrictByCandidateType()` | `Districts` | —(JSON) | n/a | Special-cases "posso kota" candidate type to Navy-child districts only |
| `ajax/hsc-diploma-ac-group` | `AjaxController::actionHscDiplomaAcGroup()` | `Subjects`; Svc: `StaticMethod::academicGroupHsc()` | —(JSON) | n/a | Branches on `ac_type === "NO"` |
| `ajax/upazial-by-district` | `AjaxController::actionUpazialByDistrict()` | `Districts` (`getIdBySlug`), `Upozilas` (`getUpazilaListCandidate`) | —(JSON) | n/a | |
| `ajax/union-by-upazial` | `AjaxController::actionUnionByUpazial()` | `Unions` (`getUnionsListForCandidate`) | —(JSON) | n/a | |

## A2. `CandidateController` — `frontend/controllers/CandidateController.php`

No `behaviors()`; access done manually inline via `Yii::$app->user->isGuest`/logged-in checks per action.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `candidate/validate-birth-registration` | `CandidateController::actionValidateBirthRegistration()` | `SignupForm` (throwaway instance) | —(JSON) | n/a | AJAX field validator only |
| `candidate/sign-up` | `CandidateController::actionSignUp()` | `SignupForm`, `User` (via `signup()`); session math CAPTCHA | `candidate/sign_up` | FE | Guest-only. Branches via `haveCeci()` on encrypted `ceci` param to redirect to payment/my-application/home |
| `candidate/login` | `CandidateController::actionLogin()` | `LoginForm` (user_type=candidate), `Session` (row created on success) | `candidate/login` | FE | Guest-only. Same CAPTCHA check + `haveCeci()` branch |
| `candidate/logout` | `CandidateController::actionLogout()` | `Session` (marks rows expired) | —(redirect → home) | n/a | |
| `candidate/download-form` | `CandidateController::actionDownloadForm()` | `DownloadDocuments`, `Sailors` | `/sailor-candidate/candidate/application_form_download` (cross-controller absolute view path) | FE+INLINE | Looks up by app ID or batch/serial/dob |
| `candidate/change-password` | `CandidateController::actionChangePassword()` | `ChangePassword`, `User`; `Yii::$app->security->generatePasswordHash` | `candidate/change_password` | FE | Logged-in only |
| `candidate/request-password-reset` | `CandidateController::actionRequestPasswordReset()` | `ResetPassword`, `User` | `candidate/request_password_reset` | FE | Guest-only. Generates a random 5-digit password and flashes it **in plaintext** to the user (security flag) |

## A3. `CheckEligibilityController` — `frontend/controllers/CheckEligibilityController.php`

No `behaviors()`. `beforeAction()` disables CSRF only for `get-description`. This is the frontend `defaultRoute` (`check-eligibility/index`).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `check-eligibility/index` | `CheckEligibilityController::actionIndex()` | `CanEligibilityCheckInfo` (scenario `SCENARIO_PERSONAL_INFO`), `Session` | `check-eligibility/personal_info` | FE+INLINE | Default route target. Forces logout if the session row is invalid |
| `check-eligibility/academic-info` | `CheckEligibilityController::actionAcademicInfo($slug)` | `CanEligibilityCheckInfo` (scenario `SCENARIO_ACADEMIC_INFO`) | `check-eligibility/academic_info` | FE+INLINE | Debug `print_r()`+`die()` on validation failure (production debug leftover, not fixed) |
| `check-eligibility/eligible-department` | `CheckEligibilityController::actionEligibleDepartment($slug)` | `SailorBatchs`, `Eligibility`, `SailorBatchConfiguration` | `check-eligibility/eligible_department` | FE+INLINE | Computes age vs. active batch circular dates; cross-references config by district/gender |
| `check-eligibility/apply-department` | `CheckEligibilityController::actionApplyDepartment($slug, $adpt)` | `Sailors`, `DeSailors` (new-row creation, one-per-batch check); session-stored eligible list | —(redirect → sign-up w/ ceci, or payment) | n/a | Guest → sign-up; logged-in → creates application row. Debug `echo/print_r/die()` on save failure |
| `check-eligibility/get-description` | `CheckEligibilityController::actionGetDescription()` | `CanDesignation` | —(raw `json_encode`) | n/a | AJAX-only, CSRF-exempt. Unreachable `exit()` statement after `return` |

## A4. `DeSailorController` — `frontend/controllers/DeSailorController.php`

No `behaviors()` / no `AccessControl` — relies on internal `Yii::$app->user->identity->id` checks (fatals for guests rather than a clean 403).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `de-sailor/payment` | `DeSailorController::actionPayment($slug)` | `DeSailors`, `SailorBatchs` (live/sandbox mode); Svc: `SSLPayment::allRequestListByTranIds()` (retry support) | `de-sailor/payment` | FE+INLINE | Session-stores `payment_info`, redirects to `online-payment/payment-ssl` |
| `de-sailor/academic-info` | `DeSailorController::actionAcademicInfo($slug)` | `DeSailors`; Svc: `StaticMethod::educationBoardResult()` (external SSC/HSC API, unless `skipTeleTalkValidation`), `Eligibility::eligibilityBySession()` | `de-sailor/academic_info` | FE | Advances phase to `THREE`, redirects to `de-sailor/personal-info` |
| `de-sailor/personal-info` | `DeSailorController::actionPersonalInfo($slug)` | `DeSailors`, `User` (`dob` update); Svc: `R2Storage` (photo upload/cleanup), `DataEncryption` (PII encrypt) | `de-sailor/personal_info` | FE+INLINE | Contains a large commented-out QR-code generation block |
| `de-sailor/application-preview` | `DeSailorController::actionApplicationPreview($slug)` | `DeSailors`; Svc: `DataEncryption` (PII decrypt for display) | `de-sailor/application_preview` | FE | Redirects to my-application if `serial_no` already set |
| `de-sailor/complete-application` | `DeSailorController::actionCompleteApplication($slug)` | `DeSailors`, `SailorBatchConfiguration`, `SailorBatchConfigurationExamDate`; Svc: `R2Storage::upsertCandidateLog()`, `DeSailors::generateLog()` | —(redirect → `de-sailor/download`) | n/a | POST-only (throws otherwise). Roll-number/exam-date allocation, deactivates exhausted configs |
| `de-sailor/download` | `DeSailorController::actionDownload($slug)` | `DeSailors` | `de-sailor/candidate/application_form_pdf.php` (via `renderPartial` → `Mpdf\Mpdf`) | PDF | Inline output, `exit()` |
| `de-sailor/download-form` | `DeSailorController::actionDownloadForm($slug)` | `DeSailors` (**no ownership check** — direct lookup by decrypted slug) | `de-sailor/candidate/application_form_pdf.php` | PDF | Same PDF generation, different lookup path than `actionDownload()` |
| `de-sailor/verify-candidate` | `DeSailorController::actionVerifyCandidate($slug)` | `DeSailors` (public, no ownership check) | `de-sailor/application_verify_preview` | FE | ~18 lines of unreachable dead code after `return` — a second, dead PDF-generation block referencing `application_verification_pdf.php` |

## A5. `MyApplicationController` — `frontend/controllers/MyApplicationController.php`

No `behaviors()`.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `my-application/index` | `MyApplicationController::actionIndex()` | `Sailors`, `DeSailors` (both queried by `created_by = current user`) | `my-application/index` | FE+INLINE | Redirects guests to `candidate/login` |

## A6. `OnlinePaymentController` — `frontend/controllers/OnlinePaymentController.php`

No `behaviors()`. `beforeAction()` disables CSRF for `ssl-success`/`ssl-cancel`/`ssl-fail` (gateway callback endpoints — correctly exempted, since the gateway can't supply a Yii CSRF token). **4 live/working actions below; the other 3 declared on this class (`actionPayment`, `actionPaymentResponseDeSailor`, `actionPaymentResponseSailor`) are the AamarPay/ShurjoPayment-broken ones — see the dead-code table above, not traced here.**

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `online-payment/payment-ssl` | `OnlinePaymentController::actionPaymentSsl()` | Svc: `SSLPayment::requestInit()` (reads session `payment_info`) | —(redirect → gateway URL, or flash+back on error) | n/a | The live, working payment-initiation path |
| `online-payment/ssl-success` | `OnlinePaymentController::actionSslSuccess()` | `Sailors`, `DeSailors` (payment fields updated), `User` (**unauthenticated identity lookup/login from callback payload** — notable) | —(redirect → academic-info step) | n/a | CSRF-exempt gateway callback. Parses `$_REQUEST`, decodes `value_b` |
| `online-payment/ssl-cancel` | `OnlinePaymentController::actionSslCancel()` | `Sailors`/`DeSailors` (via callback login) | —(redirect → my-application) | n/a | CSRF-exempt. Flashes "Payment failed" |
| `online-payment/ssl-fail` | `OnlinePaymentController::actionSslFail()` | Same pattern as `ssl-cancel` | —(redirect → my-application) | n/a | CSRF-exempt |

## A7. `SailorCandidateController` — `frontend/controllers/SailorCandidateController.php`

**The only frontend controller besides `SiteController` with real `AccessControl`** — `only => ['payment', 'academic-info']`, both requiring `roles => ['@']`; a third declared rule (`verify-candidate`, roles `?`) is dead configuration since it's outside `only`. All other actions below have no `AccessControl` coverage.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `sailor-candidate/payment` | `SailorCandidateController::actionPayment($slug)` | `Sailors`, `SailorBatchs`; Svc: `SSLPayment` | `sailor-candidate/payment` | FE+INLINE | **AccessControl-gated** (`@`) |
| `sailor-candidate/academic-info` | `SailorCandidateController::actionAcademicInfo($slug)` | `Sailors`; Svc: `StaticMethod::educationBoardResult()` (no skip branch, unlike DeSailor's), `Eligibility::eligibilityBySession()` | `sailor-candidate/academic_info` | FE | **AccessControl-gated** (`@`) |
| `sailor-candidate/personal-info` | `SailorCandidateController::actionPersonalInfo($slug)` | `Sailors`, `User`; Svc: `R2Storage`, `DataEncryption` | `sailor-candidate/personal_info` | FE+INLINE | Not gated (relies on internal checks) |
| `sailor-candidate/application-preview` | `SailorCandidateController::actionApplicationPreview($slug)` | `Sailors`; Svc: `DataEncryption` | `sailor-candidate/application_preview` | FE | Not gated |
| `sailor-candidate/complete-application` | `SailorCandidateController::actionCompleteApplication($slug)` | `Sailors`, `SailorBatchConfiguration`, `SailorBatchConfigurationExamDate` | —(redirect → `sailor-candidate/download`) | n/a | POST-only. Has commented-out `SendSms::sendSms` call |
| `sailor-candidate/download` | `SailorCandidateController::actionDownload($slug)` | `Sailors` | `sailor-candidate/candidate/application_form_pdf.php` | PDF | Inline output, `exit()` |
| `sailor-candidate/download-form` | `SailorCandidateController::actionDownloadForm($slug)` | `Sailors` (no ownership check) | `sailor-candidate/candidate/application_form_pdf.php` | PDF | Prepends `+88` to phone numbers |
| `sailor-candidate/verify-candidate` | `SailorCandidateController::actionVerifyCandidate($slug)` | `Sailors` (no ownership check) | `sailor-candidate/application_verify_preview` | FE | ~17 lines of unreachable dead code after `return` (second mPDF block) |
| `sailor-candidate/refund-phone` | `SailorCandidateController::actionRefundPhone()` | `Sailors` (`refund_phone`, scoped to `created_by`); Svc: `DataEncryption` | —(JSON) | n/a | AJAX-only, logged-in only. Validates BD phone regex |
| `sailor-candidate/cancel-application` | `SailorCandidateController::actionCancelApplication()` | `Sailors` (`request_for_cancel`/`reason`, scoped to `created_by`) | —(JSON) | n/a | AJAX-only, reads raw JSON body |

## A8. `SiteController` — `frontend/controllers/SiteController.php`

```php
'access' => ['only' => ['logout', 'signup'], 'rules' => [
    ['actions' => ['signup'], 'allow' => true, 'roles' => ['?']],
    ['actions' => ['logout'], 'allow' => true, 'roles' => ['@']],
]],
'verbs' => ['actions' => ['logout' => ['post']]],
```
All other actions below (outside `only`) are unrestricted/public. `actions()` override contributes `error` (`yii\web\ErrorAction`) and `captcha` (`yii\captcha\CaptchaAction`).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `site/index` | `SiteController::actionIndex()` | (none) | `site/index` | FE | Public homepage — title still the Yii2-default `'My Yii Application'` (cosmetic bug, not fixed) |
| `site/form` | `SiteController::actionForm()` | (none) | `site/form_c` | FE | Static, unwired demo form (`action="#"`) — looks like leftover scaffolding |
| `site/login` | `SiteController::actionLogin()` | `LoginForm` (user_type=candidate) | `site/login` | FE | Guest-only, `goBack()` on success. Generic Yii2-scaffold login, separate from `candidate/login` |
| `site/logout` | `SiteController::actionLogout()` | (none — `Auth::logout()`) | —(redirect → home) | n/a | POST-only, requires auth |
| `site/contact` | `SiteController::actionContact()` | `ContactForm` (`sendEmail()`) | `site/contact` | FE | Sends mail via `Yii::$app->mailer` |
| `site/about` | `SiteController::actionAbout()` | (none) | `site/about` | FE | Static page |
| `site/signup` | `SiteController::actionSignup()` | `SignupForm` (`signup()`) | `site/signup` | FE | Guest-only. Generic scaffold signup, parallel to `candidate/sign-up` |
| `site/request-password-reset` | `SiteController::actionRequestPasswordReset()` | `PasswordResetRequestForm` (`sendEmail()`) | `site/requestPasswordResetToken` | FE | Email side effect |
| `site/reset-password` | `SiteController::actionResetPassword($token)` | `ResetPasswordForm`, `User` | `site/resetPassword` | FE | Throws on invalid token |
| `site/verify-email` | `SiteController::actionVerifyEmail($token)` | `VerifyEmailForm`, `User` | —(redirect → home, after login) | n/a | |
| `site/resend-verification-email` | `SiteController::actionResendVerificationEmail()` | `ResendVerificationEmailForm` (`sendEmail()`) | `site/resendVerificationEmail` | FE | Email side effect |
| `site/error` | `yii\web\ErrorAction` (via `actions()`) | (none) | `site/error` | FE | Also wired as `errorHandler.errorAction`. Not counted in `route_inventory.md`'s 53-action tally (not an `action*` method) |
| `site/captcha` | `yii\captcha\CaptchaAction` (via `actions()`) | (none) | —(image stream) | n/a | Same — framework sub-action, not counted in the 53 |

---

# Part B — Backend (`backend/controllers/`, namespace `backend\controllers`)

19 live controllers (of 21 files), 118 live `action*` methods + 1 framework sub-action (`site/error`). No shared custom base controller — every controller extends `\yii\web\Controller` directly. **Every route below except `site/login`/`site/error` requires an authenticated backend user via the app-level global `AccessControl` filter** (see Legend section above) regardless of the per-controller notes.

## B1. `AjaxController` — `backend/controllers/AjaxController.php`

No `behaviors()` beyond CSRF-disable in `beforeAction()`. **No `VerbFilter`, no per-controller access control** — protected only by the app-level global filter.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `ajax/get-sailor-information-by-roll` | `AjaxController::actionGetSailorInformationByRoll()` | `Sailors`, `Districts` (slug→name resolve) | —(JSON) | n/a | |
| `ajax/get-de-sailor-information-by-roll` | `AjaxController::actionGetDeSailorInformationByRoll()` | `DeSailors`, `Districts` | —(JSON) | n/a | |
| `ajax/get-candesignation-by-cantype` | `AjaxController::actionGetCandesignationByCantype()` | `CanDesignation` | —(JSON) | n/a | |
| `ajax/get-all-assigned-district-by-center` | `AjaxController::actionGetAllAssignedDistrictByCenter()` | `SailorCentDistMapping` | —(JSON) | n/a | |
| `ajax/decode-phone` | `AjaxController::actionDecodePhone()` | `Sailors` (raw SQL `UPDATE`, up to 5000 rows/call); Svc: `DataEncryption` (decrypt), `AES256CTR` (re-encrypt) | —(JSON) | n/a | **Batch PII re-encryption/data-migration endpoint** — protected only by the app-level auth filter, no role/permission check of its own |

## B2. `CanDesignationController` — `backend/controllers/CanDesignationController.php`

`VerbFilter` only (`delete` → POST).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `can-designation/index` | `CanDesignationController::actionIndex()` | `CanDesignation`, `CanDesignationSearch` | `can-designation/index` | ADM | |
| `can-designation/view` | `CanDesignationController::actionView($id)` | `CanDesignation` | `can-designation/view` | ADM | |
| `can-designation/create` | `CanDesignationController::actionCreate()` | `CanDesignation` | `can-designation/_form` | ADM | Flash on success |
| `can-designation/update` | `CanDesignationController::actionUpdate($id)` | `CanDesignation` | `can-designation/_form` | ADM | Flash on success |
| `can-designation/delete` | `CanDesignationController::actionDelete($id)` | `CanDesignation` | —(redirect → index) | n/a | POST-only |

## B3. `DeSailorBranchController` — `backend/controllers/DeSailorBranchController.php` — BROKEN

`VerbFilter` only. **The entire `backend/views/de-sailor-branch/` directory does not exist in the repo** — all 4 render-producing actions crash. Confirmed non-functional as shipped.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `de-sailor-branch/index` | `DeSailorBranchController::actionIndex()` | `DeSailorBranch`, `DeSailorBranchSearch` | `de-sailor-branch/index` — **MISSING** | n/a | **BROKEN** — `Error: view file does not exist` |
| `de-sailor-branch/view` | `DeSailorBranchController::actionView($id)` | `DeSailorBranch` | `de-sailor-branch/view` — **MISSING** | n/a | **BROKEN** |
| `de-sailor-branch/create` | `DeSailorBranchController::actionCreate()` | `DeSailorBranch` | `de-sailor-branch/create` — **MISSING** | n/a | **BROKEN** |
| `de-sailor-branch/update` | `DeSailorBranchController::actionUpdate($id)` | `DeSailorBranch` | `de-sailor-branch/update` (renders `update`, not `_form`, unlike every sibling CRUD controller) — **MISSING** | n/a | **BROKEN** |
| `de-sailor-branch/delete` | `DeSailorBranchController::actionDelete($id)` | `DeSailorBranch` (`findModel()->delete()`) | —(redirect → index) | n/a | POST-only. Technically executes (no `render()` call) but practically unreachable in a real admin session — you can't navigate to a delete button without first loading the broken `index`/`view` |

## B4. `DeSailorReportController` — `backend/controllers/DeSailorReportController.php`

No `behaviors()` — **no access control on any report/export action** beyond the app-level global filter. Uses `PhpOffice\PhpSpreadsheet\{Spreadsheet, Writer\Xlsx}`, `\Mpdf\Mpdf`.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `de-sailor-report/payment` | `DeSailorReportController::actionPayment()` | `DeSailors` (filtered by batch/payment_type/is_paid); session-stored results+filters | `de-sailor-report/payment_report` | ADM | |
| `de-sailor-report/payment-pdf` | `DeSailorReportController::actionPaymentPdf()` | Session-stored payment report; Svc: `Mpdf` | `de-sailor-report/pdf/payment_report_pdf` | PDF | Byte-identical to `report/pdf/payment_report_pdf.php` |
| `de-sailor-report/payment-excel` | `DeSailorReportController::actionPaymentExcel()` | Svc: `PhpSpreadsheet`/`Xlsx` | —(binary download) | n/a | **Stub** — writes only a literal `"Hello World !"` placeholder cell |
| `de-sailor-report/candidate-filter` | `DeSailorReportController::actionCandidateFilter()` | `DeSailors` (batch/center/district/designation/exam_date/gender); session-stored | `de-sailor-report/candidate_filter` | ADM | |
| `de-sailor-report/candidate-filter-pdf` | `DeSailorReportController::actionCandidateFilterPdf()` | Session-stored filtered list; Svc: `Mpdf` | `de-sailor-report/pdf/candidate_filter_pdf` | PDF | Byte-identical to `report/pdf/candidate_filter_pdf.php` |
| `de-sailor-report/candidate-filter-excel` | `DeSailorReportController::actionCandidateFilterExcel()` | `DeSailors`; Svc: `PhpSpreadsheet`/`Xlsx`, `R2Storage` (photo embed), `DataEncryption` (phone decrypt) | —(binary download) | n/a | Per-row photo embedding |
| `de-sailor-report/monitoring-application` | `DeSailorReportController::actionMonitoringApplication()` | `DeSailors` (missing-exam-photo check via file-existence against upload dir) | `de-sailor-report/monitoring_application` | ADM | |
| `de-sailor-report/reference-candidate-pdf` | `DeSailorReportController::actionReferenceCandidatePdf()` | Session-stored `SailorsSearch` params; Svc: `Mpdf` | `de-sailor-report/pdf/reference_candidate_pdf` | PDF | `die()`s with an error string if batch+district not both set |
| `de-sailor-report/reference-de-candidate-pdf` | `DeSailorReportController::actionReferenceDeCandidatePdf()` | Session-stored `DeSailorsSearch` params; Svc: `Mpdf` | `de-sailor-report/pdf/reference_candidate_pdf` (shared with the action above) | PDF | |
| `de-sailor-report/reference-candidate-excel` | `DeSailorReportController::actionReferenceCandidateExcel()` | Svc: `PhpSpreadsheet`/`Xlsx` | —(binary download) | n/a | **Near-stub** — writes only literal `"We are working"` placeholder text plus real query/filename logic |

## B5. `DeSailorsController` — `backend/controllers/DeSailorsController.php`

`VerbFilter` only (`delete` → POST).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `de-sailors/index` | `DeSailorsController::actionIndex()` | `DeSailors` (ordered `serial_no DESC`), `Subjects::getAllSubject()` (filter dropdown) | `de-sailors/index` | ADM+INLINE | |
| `de-sailors/view` | `DeSailorsController::actionView($id)` | `DeSailors` | `de-sailors/view` | ADM | |
| `de-sailors/create` | `DeSailorsController::actionCreate()` | `DeSailors` | `de-sailors/create` — **MISSING** | n/a | **BROKEN** — `backend/views/de-sailors/create.php` does not exist |
| `de-sailors/update` | `DeSailorsController::actionUpdate($id)` | `DeSailors`; Svc: `DataEncryption` (decrypt for display / re-encrypt before save), `R2Storage` (photo upload + old-file cleanup) | `de-sailors/_form` | ADM | Sets `phase = SAILOR_PHASE_TWO` on manual payment |
| `de-sailors/reference-candidate` | `DeSailorsController::actionReferenceCandidate()` | `DeSailors` (`have_reference = YES`); session-stored query params | `de-sailors/reference/reference_candidate` | ADM+INLINE | |
| `de-sailors/add-reference-candidate` | `DeSailorsController::actionAddReferenceCandidate()` | `DeSailorsReference` (JSON-column append, looked up by `serial_no`) | `de-sailors/reference/add_reference_candidate` | ADM | AJAX-validate + POST |
| `de-sailors/reference-candidate-update` | `DeSailorsController::actionReferenceCandidateUpdate($id)` | `DeSailorsReference` (edits/clears reference JSON columns) | `de-sailors/reference/update_reference_candidate` | ADM | Byte-identical view to `sailors/reference/update_reference_candidate.php` |
| `de-sailors/delete` | `DeSailorsController::actionDelete($id)` | `DeSailors` | —(redirect → index) | n/a | POST-only |

## B6. `DistrictsController` — `backend/controllers/DistrictsController.php`

`VerbFilter` only.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `districts/index` | `DistrictsController::actionIndex()` | `Districts`, `DistrictsSearch` | `districts/index` | ADM | |
| `districts/view` | `DistrictsController::actionView($id)` | `Districts` | `districts/view` | ADM | |
| `districts/create` | `DistrictsController::actionCreate()` | `Districts` | `districts/_form` | ADM | |
| `districts/update` | `DistrictsController::actionUpdate($id)` | `Districts` | `districts/_form` | ADM | |
| `districts/delete` | `DistrictsController::actionDelete($id)` | `Districts` | —(redirect → index) | n/a | POST-only |

## B7. `EligibilityController` — `backend/controllers/EligibilityController.php`

`VerbFilter` only.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `eligibility/index` | `EligibilityController::actionIndex()` | `Eligibility`, `EligibilitySearch` | `eligibility/index` | ADM | |
| `eligibility/view` | `EligibilityController::actionView($id)` | `Eligibility` | `eligibility/view` | ADM | |
| `eligibility/create` | `EligibilityController::actionCreate()` | `Eligibility` | `eligibility/_form` | ADM+INLINE | |
| `eligibility/update` | `EligibilityController::actionUpdate($id)` | `Eligibility` (`implodeFieldList()` re-explodes comma-separated multi-select fields for the form) | `eligibility/_form` | ADM+INLINE | |
| `eligibility/delete` | `EligibilityController::actionDelete($id)` | `Eligibility` | —(redirect → index) | n/a | POST-only |

## B8. `LogReportController` — `backend/controllers/LogReportController.php`

No `behaviors()` — no access control beyond the app-level global filter.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `log-report/site-activity` | `LogReportController::actionSiteActivity()` | `DynamicModel(['data','method','controller'])`; Svc: `R2Storage::getLogFileContents()` (reads `action_log/<controller>/add\|update.ndjson`) | `log-report/report` | ADM+INLINE | Uses `$this->registerJs(...)`. Excludes `site/login` entries |
| `log-report/site-activity-view` | `LogReportController::actionSiteActivityView($date,$route,$method,$controller,$update_id)` | Same ndjson source, filtered to one route/update_id | —(raw echo — hand-built HTML `<table>` via `ob_start()`, not a `render()` call) | n/a | Modal detail view, no dedicated view file |

## B9. `ReportController` — `backend/controllers/ReportController.php` (1,605 lines — largest controller in the repo)

No `behaviors()` — **no access control on any action**, including the hardcoded-user-ID gate on `actionJsonForLs()`. Uses `PhpOffice\PhpSpreadsheet\{Spreadsheet, Writer\Xlsx, Style\Alignment, Style\Color, RichText\RichText, RichText\Run}`, `PhpOffice\PhpWord\{PhpWord, IOFactory}`, `\Mpdf\Mpdf`.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `report/payment` | `ReportController::actionPayment()` | `Sailors` (batch/payment_type/is_paid); session-stored | `report/payment_report` | ADM | |
| `report/payment-pdf` | `ReportController::actionPaymentPdf()` | Session-stored payment report; Svc: `Mpdf` | `report/pdf/payment_report_pdf` | PDF | |
| `report/payment-excel` | `ReportController::actionPaymentExcel()` | Svc: `PhpSpreadsheet`/`Xlsx` | —(binary download) | n/a | **Stub** — literal `"Hello World !"` placeholder cell only |
| `report/candidate-filter` | `ReportController::actionCandidateFilter()` | `Sailors` (batch/center/district/designation/exam_date/gender/ssc_group/father_occupation/serial_no); session-stored | `report/candidate_filter` | ADM+INLINE | Main candidate filter/search screen |
| `report/candidate-filter-pdf` | `ReportController::actionCandidateFilterPdf()` | Session-stored filtered candidates; Svc: `Mpdf` | `report/pdf/candidate_filter_pdf` | PDF | |
| `report/candidate-filter-excel` | `ReportController::actionCandidateFilterExcel()` | `Sailors`; Svc: `PhpSpreadsheet`/`Xlsx`, `R2Storage` (photo embed), `DataEncryption` (phone decrypt) | —(binary download) | n/a | Logo, SSC subject-grade breakdown, per-row photo embedding |
| `report/monitoring-application` | `ReportController::actionMonitoringApplication()` | `Sailors` (missing-exam-photo check via `R2Storage`, raw SQL flag update `is_image_exist_check`) | `report/monitoring_application` | ADM | |
| `report/exam-date-check` | `ReportController::actionExamDateCheck()` | (none — form-render only) | `report/exam_date_check_by_center_designation` | ADM+INLINE | **Incomplete/stub** — no POST handling or query logic in the action itself |
| `report/all-reference-candidate-excel` | `ReportController::actionAllReferenceCandidateExcel()` | `Sailors` (reference candidates for the last search's center); Svc: `PhpSpreadsheet`/`Xlsx` | —(binary download) | n/a | Branch/designation summary header |
| `report/reference-candidate-pdf` | `ReportController::actionReferenceCandidatePdf()` | Session-stored search params (requires center+exam_date); Svc: `Mpdf` | `report/pdf/reference_candidate_pdf` | PDF | |
| `report/reference-de-candidate-pdf` | `ReportController::actionReferenceDeCandidatePdf()` | Session-stored `DeSailorsSearch` params; Svc: `Mpdf` | `report/pdf/reference_candidate_pdf` (shared) | PDF | |
| `report/reference-candidate-excel` | `ReportController::actionReferenceCandidateExcel()` | `Sailors`; Svc: `PhpSpreadsheet`/`Xlsx`, `R2Storage` (photo URL) | —(binary download) | n/a | Center/date summary header |
| `report/district-candidate` | `ReportController::actionDistrictCandidate()` | `Sailors` (grouped by `candidate_designation` count, batch/center/district-filtered); session-stored | `report/district_candidate` | ADM | Paid/active only |
| `report/center-date-candidate` | `ReportController::actionCenterDateCandidate()` | `Sailors` (same grouping, batch/center/exam_date-filtered); session-stored | `report/center_date_candidate` | ADM | |
| `report/exam-date-center-candidate-pdf` | `ReportController::actionExamDateCenterCandidatePdf()` | Session-stored center/date candidate counts; Svc: `Mpdf` | `report/pdf/exam_date_center_candidate_pdf` | PDF | |
| `report/district-candidate-pdf` | `ReportController::actionDistrictCandidatePdf()` | Session-stored district candidate counts; Svc: `Mpdf` | `report/pdf/district_candidate_pdf` | PDF | |
| `report/district-candidate-excel` | `ReportController::actionDistrictCandidateExcel()` | Svc: `PhpSpreadsheet`/`Xlsx` | —(binary download) | n/a | Logo + batch/district header, designation counts + total row |
| `report/reference-candidate-word` | `ReportController::actionReferenceCandidateWord()` | `Sailors` (reference candidates); Svc: `PhpWord`/`IOFactory` | —(binary download, `.docx`) | n/a | Landscape table (roll/mobile/district/description/reference/relationship/subject/photo) — only DOCX export in the codebase |
| `report/center-candidate` | `ReportController::actionCenterCandidate()` | `Sailors` (grouped by designation, batch/center/exam_date-filtered); session-stored | `report/center_candidate` | ADM | Paid/active only |
| `report/center-candidate-pdf` | `ReportController::actionCenterCandidatePdf()` | Session-stored center candidate counts; Svc: `Mpdf` | `report/pdf/center_candidate_pdf` | PDF | |
| `report/center-candidate-excel` | `ReportController::actionCenterCandidateExcel()` | Svc: `PhpSpreadsheet`/`Xlsx` | —(binary download) | n/a | Logo + batch/center header, counts + total row |
| `report/same-academic-info` | `ReportController::actionSameAcademicInfo()` | (none in the action body — filtering presumably client-side/AJAX) | `report/same_academic_info` | ADM | Title mislabeled "Center candidate" (copy-paste bug) |
| `report/json-for-ls` | `ReportController::actionJsonForLs()` | (none — renders empty data arrays) | `report/json_for_ls` | ADM | **Gated to `Yii::$app->user->id == 1`** — hardcoded single-user check, not role-based |

## B10. `SailorBatchConfigurationController` — `backend/controllers/SailorBatchConfigurationController.php`

`VerbFilter` only.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `sailor-batch-configuration/index` | `SailorBatchConfigurationController::actionIndex()` | `SailorBatchConfiguration`, `SailorBatchConfigurationSearch` | `sailor-batch-configuration/index` | ADM | |
| `sailor-batch-configuration/view` | `SailorBatchConfigurationController::actionView($id)` | `SailorBatchConfiguration` | `sailor-batch-configuration/view` | ADM | |
| `sailor-batch-configuration/create` | `SailorBatchConfigurationController::actionCreate()` | `SailorBatchConfiguration`, `SailorBatchConfigurationExamDate` (nested rows via `batchConfigurationExamDate()`) | `sailor-batch-configuration/_form` | ADM+INLINE | AJAX-validate branch |
| `sailor-batch-configuration/update` | `SailorBatchConfigurationController::actionUpdate($id)` | `SailorBatchConfiguration`, `SailorBatchConfigurationExamDate` (re-explodes multi-select fields) | `sailor-batch-configuration/_form` | ADM+INLINE | |
| `sailor-batch-configuration/delete` | `SailorBatchConfigurationController::actionDelete($id)` | `SailorBatchConfiguration` | —(redirect → index) | n/a | POST-only |
| `sailor-batch-configuration/delete-exam-date` | `SailorBatchConfigurationController::actionDeleteExamDate()` | `SailorBatchConfigurationExamDate` | —(JSON) | n/a | AJAX-only, deletes one exam-date sub-row |

## B11. `SailorBatchsController` — `backend/controllers/SailorBatchsController.php`

`VerbFilter` only.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `sailor-batchs/index` | `SailorBatchsController::actionIndex()` | `SailorBatchs`, `SailorBatchsSearch` | `sailor-batchs/index` | ADM | |
| `sailor-batchs/view` | `SailorBatchsController::actionView($id)` | `SailorBatchs` | `sailor-batchs/view` | ADM | |
| `sailor-batchs/create` | `SailorBatchsController::actionCreate()` | `SailorBatchs` (`circular_media` file upload to `/media/sailor_circular/`) | `sailor-batchs/_form` | ADM+INLINE | `$prevImage` referenced but only ever defined in `actionUpdate()` — latent undefined-variable reference, silently guarded by `!empty()` |
| `sailor-batchs/update` | `SailorBatchsController::actionUpdate($id)` | `SailorBatchs` (same upload handling, formats circular dates) | `sailor-batchs/_form` | ADM+INLINE | |
| `sailor-batchs/delete` | `SailorBatchsController::actionDelete($id)` | `SailorBatchs` | —(redirect → index) | n/a | POST-only |

## B12. `SailorCentDistMappingController` — `backend/controllers/SailorCentDistMappingController.php`

`VerbFilter` only.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `sailor-cent-dist-mapping/index` | `SailorCentDistMappingController::actionIndex()` | `SailorCentDistMapping`, `SailorCentDistMappingSearch` | `sailor-cent-dist-mapping/index` | ADM | |
| `sailor-cent-dist-mapping/view` | `SailorCentDistMappingController::actionView($id)` | `SailorCentDistMapping` | `sailor-cent-dist-mapping/view` | ADM | |
| `sailor-cent-dist-mapping/create` | `SailorCentDistMappingController::actionCreate()` | `SailorCentDistMapping` | `sailor-cent-dist-mapping/_form` | ADM | |
| `sailor-cent-dist-mapping/update` | `SailorCentDistMappingController::actionUpdate($id)` | `SailorCentDistMapping` (explodes `district_slug` CSV into array for the form) | `sailor-cent-dist-mapping/_form` | ADM | |
| `sailor-cent-dist-mapping/delete` | `SailorCentDistMappingController::actionDelete($id)` | `SailorCentDistMapping` | —(redirect → index) | n/a | POST-only |

## B13. `SailorCentersController` — `backend/controllers/SailorCentersController.php`

`VerbFilter` only.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `sailor-centers/index` | `SailorCentersController::actionIndex()` | `SailorCenters`, `SailorCentersSearch` | `sailor-centers/index` | ADM | |
| `sailor-centers/view` | `SailorCentersController::actionView($id)` | `SailorCenters` | `sailor-centers/view` | ADM | |
| `sailor-centers/create` | `SailorCentersController::actionCreate()` | `SailorCenters` | `sailor-centers/_form` | ADM | |
| `sailor-centers/update` | `SailorCentersController::actionUpdate($id)` | `SailorCenters` | `sailor-centers/_form` | ADM | |
| `sailor-centers/delete` | `SailorCentersController::actionDelete($id)` | `SailorCenters` | —(redirect → index) | n/a | POST-only |

## B14. `SailorsController` — `backend/controllers/SailorsController.php`

`VerbFilter` only. **`actionCreate()`/`actionDelete()` are commented out** — see dead-code table above; only 7 of the 9 methods declared in this file are live routes.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `sailors/index` | `SailorsController::actionIndex()` | `Sailors`, `SailorsSearch` (ordered `serial_no DESC`) | `sailors/index` | ADM+INLINE | |
| `sailors/cancel-request` | `SailorsController::actionCancelRequest()` | `Sailors` (`request_for_cancel = 1`) | `sailors/index` (reused, with `is_cancel_request = true`) | ADM+INLINE | |
| `sailors/reference-candidate` | `SailorsController::actionReferenceCandidate()` | `Sailors` (has reference); session-stored query params | `sailors/reference/reference_candidate` | ADM+INLINE | |
| `sailors/add-reference-candidate` | `SailorsController::actionAddReferenceCandidate()` | `SailorsReference` (reference/relationship/details/reference_add_on JSON arrays, by `serial_no`), tracks `last_reference_added` | `sailors/reference/add_reference_candidate` | ADM | AJAX-validate + POST |
| `sailors/reference-candidate-update` | `SailorsController::actionReferenceCandidateUpdate($id)` | `SailorsReference` (edits/clears reference JSON fields) | `sailors/reference/update_reference_candidate` | ADM | Byte-identical view to `de-sailors/reference/update_reference_candidate.php` |
| `sailors/view` | `SailorsController::actionView($id)` | `Sailors` | `sailors/view` | ADM | |
| `sailors/update` | `SailorsController::actionUpdate($id)` | `Sailors`; Svc: `DataEncryption` (decrypt/re-encrypt), `R2Storage` (photo upload + cleanup) | `sailors/_form` | ADM | Sets `phase` on manual payment |

## B15. `SiteController` (backend) — `backend/controllers/SiteController.php`

```php
'access' => ['rules' => [
    ['actions' => ['login', 'error'], 'allow' => true],
    ['actions' => ['logout', 'index'], 'allow' => true, 'roles' => ['@']],
]],
'verbs' => ['actions' => ['logout' => ['post']]],
```
The only backend controller with a complete, role-gated `AccessControl` of its own (redundant with — but consistent with — the app-level global filter). `actions()` override contributes `error` (`yii\web\ErrorAction`).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `site/index` | `SiteController::actionIndex()` | `DeSailors` (counts grouped by `serial_generate_date`), `CanEligibilityCheckInfo` (raw SQL, hardcoded date filter `'2026-05-02'`) | `site/index` | ADM+INLINE | Dashboard — 3 chart datasets. Large commented-out legacy chart-query block left in place |
| `site/login` | `SiteController::actionLogin()` | `LoginForm`; session math CAPTCHA | `site/login` | **ADM-LOGIN** | `$this->layout = 'blank'` — the one action that overrides the app-wide `admin` layout |
| `site/logout` | `SiteController::actionLogout()` | (none) | —(redirect → home) | n/a | POST-only, requires auth |
| `site/error` | `yii\web\ErrorAction` (via `actions()`) | (none) | `site/error` | ADM | Also wired as `errorHandler.errorAction`. Not counted in `route_inventory.md`'s 118-action tally |

## B16. `SubjectsController` — `backend/controllers/SubjectsController.php`

`VerbFilter` only.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `subjects/index` | `SubjectsController::actionIndex()` | `Subjects`, `SubjectsSearch` | `subjects/index` | ADM | |
| `subjects/view` | `SubjectsController::actionView($id)` | `Subjects` | `subjects/view` | ADM | |
| `subjects/create` | `SubjectsController::actionCreate()` | `Subjects` | `subjects/_form` | ADM | |
| `subjects/update` | `SubjectsController::actionUpdate($id)` | `Subjects` | `subjects/_form` | ADM | |
| `subjects/delete` | `SubjectsController::actionDelete($id)` | `Subjects` | —(redirect → index) | n/a | POST-only |

## B17. `UnionsController` — `backend/controllers/UnionsController.php`

```php
'access' => ['rules' => [['allow' => true, 'roles' => ['@']], ['allow' => false]]],
'verbs' => ['delete' => ['POST']],
```
One of only two backend CRUD controllers with an explicit `AccessControl` (redundant fallback given the app-level global filter already requires `@`).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `unions/index` | `UnionsController::actionIndex()` | `Unions`, `UnionsSearch` | `unions/index` | ADM | |
| `unions/view` | `UnionsController::actionView($id)` | `Unions` | `unions/view` | ADM | |
| `unions/create` | `UnionsController::actionCreate()` | `Unions` | `unions/_form` | ADM | |
| `unions/update` | `UnionsController::actionUpdate($id)` | `Unions` | `unions/_form` | ADM | Does **not** reload `$model` after save, unlike sibling controllers |
| `unions/delete` | `UnionsController::actionDelete($id)` | `Unions` | —(redirect → index) | n/a | POST-only |

## B18. `UpozilasController` — `backend/controllers/UpozilasController.php`

Same `AccessControl` pattern as `UnionsController` plus `VerbFilter` (`delete` → POST).

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `upozilas/index` | `UpozilasController::actionIndex()` | `Upozilas`, `UpozilasSearch` | `upozilas/index` | ADM | |
| `upozilas/view` | `UpozilasController::actionView($id)` | `Upozilas` | `upozilas/view` | ADM | |
| `upozilas/create` | `UpozilasController::actionCreate()` | `Upozilas` | `upozilas/_form` | ADM | |
| `upozilas/update` | `UpozilasController::actionUpdate($id)` | `Upozilas` | `upozilas/_form` | ADM | |
| `upozilas/delete` | `UpozilasController::actionDelete($id)` | `Upozilas` | —(redirect → index) | n/a | POST-only |

## B19. `UserController` — `backend/controllers/UserController.php`

`VerbFilter` only (`delete` → POST). **No `AccessControl` of its own** — notable, since this controller manages admin accounts/password hashes (protected only by the app-level global filter). `actionDelete()` is commented out — see dead-code table above.

| Route | Controller::Action | Model(s) / Service(s) | View | Asset Bundle | Notes |
|---|---|---|---|---|---|
| `user/index` | `UserController::actionIndex()` | `User`, `UserSearch` | `user/index` | ADM | `UserSearch::search()` exposes `like`-filtering on `auth_key`/`password_hash`/`password_reset_token` (security flag, see `model_inventory.md`) |
| `user/view` | `UserController::actionView($id)` | `User` | `user/view` | ADM | |
| `user/create` | `UserController::actionCreate()` | `User` | `user/create` — **MISSING** | n/a | **BROKEN** — `backend/views/user/create.php` does not exist. Redirects to `view` on success, but can never render the initial form |
| `user/update` | `UserController::actionUpdate($id)` | `User`; `Yii::$app->security->generatePasswordHash()` | `user/_form` | ADM | Re-hashes password if posted; clears `password_hash` before render so it's never echoed back |

---

## Cross-cutting notes

- **Two parallel candidate tracks, traced in full above as separate controllers**: `DeSailorController`/`frontend/views/de-sailor/*` (Direct-Entry Sailor) vs. `SailorCandidateController`/`frontend/views/sailor-candidate/*` (general Sailor). Same split repeats on the backend: `SailorsController` vs. `DeSailorsController`, `ReportController` vs. `DeSailorReportController`. Several backend PDF partials are byte-identical across the two trees (`report/pdf/candidate_filter_pdf.php` ≡ `de-sailor-report/pdf/candidate_filter_pdf.php`; `report/pdf/payment_report_pdf.php` ≡ `de-sailor-report/pdf/payment_report_pdf.php`; `sailors/reference/update_reference_candidate.php` ≡ `de-sailors/reference/update_reference_candidate.php`) — copy-pasted rather than shared, per `view_inventory.md`.
- **11 admin CRUD controllers follow the unmodified Gii 5-action `index/view/create/update/delete` template** (`CanDesignationController`, `DistrictsController`, `EligibilityController`, `SailorBatchsController`, `SailorCentDistMappingController`, `SailorCentersController`, `SubjectsController`, `UnionsController`, `UpozilasController`, and mostly `SailorsController`/`DeSailorsController`/`UserController` with commented-out `create`/`delete` variants) — all render `ADM` with a shared `_form.php` partial for create+update, and all POST to the same route for both.
- **No queue/job layer exists** — `AjaxController::actionDecodePhone()` (backend) runs its up-to-5000-row PII batch re-encryption synchronously inside a single web request, not as a queued job (`console/controllers/` is empty; no `yii\queue` usage anywhere in the repo).
- **`frontend/assets/AppAsset.php` and `backend/views/layouts/main.php` are both dead** (confirmed in `css_inventory.md`/`view_inventory.md`) — no controller/view in the live trace above ever reaches either.
