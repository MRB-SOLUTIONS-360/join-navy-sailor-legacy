# Route Inventory — join-navy-sailor-legacy

## Important note: Yii2 routing is not Laravel routing

This is a Yii2 2.0 **advanced application template** (`common/`, `frontend/`, `backend/`, `console/`), not Laravel. There is no `routes/web.php` / `routes/api.php` equivalent to inventory — Yii2's `yii\web\UrlManager` resolves URLs to `controller/action` pairs either via explicit `rules` (none are defined here — see below) or, when a URL matches no rule, via its **default routing convention**: `/{controllerId}/{actionId}` → `{namespace}\{ControllerId}Controller::action{ActionId}()`.

Because both apps' `urlManager.rules` arrays are empty (`[]`), **every route in this app is implicit**, generated purely from controller/action naming. That makes the controller + action inventory below the de-facto route list, exactly as the reference `join-navy-officer-legacy` doc's `web.php`/`api.php` tables are for Laravel.

---

## How a URL maps to code

1. **Entry script** picks the app and merges config (see below).
2. `yii\web\Application` boots with `controllerNamespace` set per app (`frontend\controllers`, `backend\controllers`) and asks its `urlManager` component to parse the incoming request path into a route string.
3. `urlManager.rules` is `[]` in both `frontend/config/main.php` and `backend/config/main.php`, so `UrlRuleManager` falls straight through to **default parsing**: it splits the path on `/`, and (since `enableStrictParsing => false`) treats the first two segments as `controllerId/actionId`.
4. `controllerId` is converted from `dash-case` to `PascalCase` + `Controller` suffix (Yii's standard `Inflector::camelize` behavior) and resolved inside `controllerNamespace`. `actionId` is converted from `dash-case` to `camelCase` and prefixed with `action`.
5. Example: a request to **`/candidate/change-password`** on the frontend app resolves to controller id `candidate` → class `frontend\controllers\CandidateController` (file `frontend/controllers/CandidateController.php`), action id `change-password` → method `actionChangePassword()`. If no segments are given, or the controller/action segment is missing, Yii falls back to `defaultRoute`.
6. If the resolved controller or action doesn't exist, Yii throws `yii\web\NotFoundHttpException` (404) — there is no catch-all/fallback route defined anywhere in this app (unlike Laravel's explicit route table, Yii2 has no route list to "miss"; every syntactically valid `/controller/action` is reachable if the class/method exists, subject to `AccessControl` behaviors on the controller).

### `defaultRoute` (used when the URL has no controller/action segment, e.g. `/`)

| App | Config file | `defaultRoute` |
|---|---|---|
| frontend | `frontend/config/main.php:20` | `'check-eligibility/index'` → `frontend\controllers\CheckEligibilityController::actionIndex()` |
| backend | `backend/config/main.php:16` | commented out (`// 'defaultRoute' => 'user/index',`) → falls back to Yii's own default of `'site/index'` → `backend\controllers\SiteController::actionIndex()` |
| console | n/a | console apps use `defaultAction` per controller, not `defaultRoute`; no custom controllers exist to route to (see below) |

### `urlManager` config — quoted verbatim

**`frontend/config/main.php`:**
```php
'urlManager' => [
    'baseUrl' => $baseUrl,
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => false,
    'rules' => [],
],
```

**`backend/config/main.php`:**
```php
'urlManager' => [
    'class'=>'yii\web\UrlManager',
    'enablePrettyUrl'=>true,
    'showScriptName'=>false,
    'enableStrictParsing'=>false,
    'rules' => [
    ],
],
```

Both have pretty URLs on, script name hidden (requires web server rewrite — see `.htaccess` at repo root), and **zero explicit rules**. No custom URL rule classes, no REST `UrlRule` registrations, no versioned API prefix. This is the single biggest structural difference from the Laravel sibling app: there is nothing resembling `Route::prefix('admin')->group(...)` — "admin" is not a URL prefix here at all, it's simply a separate Yii application (`backend/`) served from its own entry script/subdomain/subfolder per deployment (see `backend/web/index.php`), with its own controller namespace.

### Entry scripts (equivalent of Laravel's `RouteServiceProvider` — these decide which config/app boots)

| Entry script | App | Config merged (in order) |
|---|---|---|
| `frontend/web/index.php` | `yii\web\Application` (frontend) | `common/config/main.php`, `common/config/main-local.php`, `frontend/config/main.php`, `frontend/config/main-local.php` |
| `frontend/web/index-test.php` | `yii\web\Application` (frontend, `YII_ENV=test`) | same as above **plus** `common/config/test.php`, `common/config/test-local.php`, `frontend/config/test.php`, `frontend/config/test-local.php`. Guarded: refuses to run unless `$_SERVER['REMOTE_ADDR']` is `127.0.0.1`/`::1`. |
| `backend/web/index.php` | `yii\web\Application` (backend) | `common/config/main.php`, `common/config/main-local.php`, `backend/config/main.php`, `backend/config/main-local.php` |
| `yii` (repo root, CLI) | `yii\console\Application` | `common/config/main.php`, `common/config/main-local.php`, `console/config/main.php`, `console/config/main-local.php` |

`frontend/config/main-local.php` conditionally registers the standard Yii2 dev-tooling modules (`debug`, `gii`) when not in the test environment — this is boilerplate, not app routing. `backend/config/main-local.php` has the identical block but it's entirely commented out, so `backend` never gets `debug`/`gii` even in dev.

---

## Module registration (`modules` config key)

| App | `modules` value | Notes |
|---|---|---|
| frontend | not set in `main.php`; `main-local.php` adds `debug` and `gii` (dev/gii tooling only, non-test env) | no app-level modules |
| backend | `'modules' => []` (`backend/config/main.php:14`) | empty; `main-local.php`'s debug/gii block is fully commented out |
| console | not set | n/a |

No custom Yii modules (e.g. an `api` module, an `admin` module) exist anywhere in this codebase — "admin" area = the separate `backend/` application, not a module.

---

## Frontend controllers (`frontend/controllers/`, namespace `frontend\controllers`)

| Controller | File | Public `action*` methods (route → method) |
|---|---|---|
| AjaxController | `frontend/controllers/AjaxController.php` | `ajax/district-by-candidate-type` → `actionDistrictByCandidateType()` (L27); `ajax/hsc-diploma-ac-group` → `actionHscDiplomaAcGroup()` (L49); `ajax/upazial-by-district` → `actionUpazialByDistrict()` (L69); `ajax/union-by-upazial` → `actionUnionByUpazial()` (L88) |
| ApiController | `frontend/controllers/ApiController.php` | none — extends `yii\rest\Controller` with `$modelClass = 'common\models\Eligibility'` but declares **no `action*` methods and no `actions()` override**; see "REST controller" note below |
| CandidateController | `frontend/controllers/CandidateController.php` | `candidate/validate-birth-registration` → `actionValidateBirthRegistration()` (L27); `candidate/sign-up` → `actionSignUp()` (L52); `candidate/login` → `actionLogin()` (L162); `candidate/logout` → `actionLogout()` (L309); `candidate/download-form` → `actionDownloadForm()` (L321); `candidate/change-password` → `actionChangePassword()` (L374); `candidate/request-password-reset` → `actionRequestPasswordReset()` (L406) |
| CheckEligibilityController | `frontend/controllers/CheckEligibilityController.php` | `check-eligibility/index` → `actionIndex()` (L36, **default route target**); `check-eligibility/academic-info` → `actionAcademicInfo($slug)` (L91); `check-eligibility/eligible-department` → `actionEligibleDepartment($slug)` (L130); `check-eligibility/apply-department` → `actionApplyDepartment($slug=null, $adpt=null)` (L372); `check-eligibility/get-description` → `actionGetDescription()` (L463) |
| DeSailorController | `frontend/controllers/DeSailorController.php` | `de-sailor/payment` → `actionPayment($slug=null)` (L36); `de-sailor/academic-info` → `actionAcademicInfo($slug=null)` (L158); `de-sailor/personal-info` → `actionPersonalInfo($slug=null)` (L377); `de-sailor/application-preview` → `actionApplicationPreview($slug=null)` (L667); `de-sailor/complete-application` → `actionCompleteApplication($slug=null)` (L708); `de-sailor/download` → `actionDownload($slug=null)` (L951); `de-sailor/download-form` → `actionDownloadForm($slug=null)` (L996); `de-sailor/verify-candidate` → `actionVerifyCandidate($slug=null)` (L1045) |
| MyApplicationController | `frontend/controllers/MyApplicationController.php` | `my-application/index` → `actionIndex()` (L11) |
| OnlinePaymentController | `frontend/controllers/OnlinePaymentController.php` | `online-payment/payment-ssl` → `actionPaymentSsl()` (L33); `online-payment/ssl-success` → `actionSslSuccess()` (L58); `online-payment/ssl-cancel` → `actionSslCancel()` (L127); `online-payment/ssl-fail` → `actionSslFail()` (L146); `online-payment/payment` → `actionPayment()` (L172); `online-payment/payment-response-de-sailor` → `actionPaymentResponseDeSailor()` (L185); `online-payment/payment-response-sailor` → `actionPaymentResponseSailor()` (L230) |
| ~~OnlinePaymentController~~ (dup class, dead file) | `frontend/controllers/OnlinePaymentController_shurjo_pay.php` | **dead code — see below**; would-be actions: `actionPayment()` (L126, one earlier copy commented out at L26), `actionPaymentResponseDeSailor()` (L141), `actionPaymentResponseSailor()` (L258, one earlier copy commented out at L184) |
| SailorCandidateController | `frontend/controllers/SailorCandidateController.php` | `sailor-candidate/payment` → `actionPayment($slug=null)` (L76); `sailor-candidate/academic-info` → `actionAcademicInfo($slug=null)` (L199); `sailor-candidate/personal-info` → `actionPersonalInfo($slug=null)` (L406); `sailor-candidate/application-preview` → `actionApplicationPreview($slug=null)` (L647); `sailor-candidate/complete-application` → `actionCompleteApplication($slug=null)` (L681); `sailor-candidate/download` → `actionDownload($slug=null)` (L927); `sailor-candidate/download-form` → `actionDownloadForm($slug=null)` (L972); `sailor-candidate/verify-candidate` → `actionVerifyCandidate($slug=null)` (L1021); `sailor-candidate/refund-phone` → `actionRefundPhone()` (L1090); `sailor-candidate/cancel-application` → `actionCancelApplication()` (L1142) |
| SiteController | `frontend/controllers/SiteController.php` | plus `actions()` (L58) contributing `site/error` → `yii\web\ErrorAction` (also wired as `errorHandler.errorAction` in `frontend/config/main.php`) and `site/captcha` → `yii\captcha\CaptchaAction`; `site/index` → `actionIndex()` (L76); `site/form` → `actionForm()` (L86); `site/login` → `actionLogin()` (L96); `site/logout` → `actionLogout()` (L121, POST-only via `VerbFilter`); `site/contact` → `actionContact()` (L133); `site/about` → `actionAbout()` (L156); `site/signup` → `actionSignup()` (L166, guests only via `AccessControl`); `site/request-password-reset` → `actionRequestPasswordReset()` (L184); `site/reset-password` → `actionResetPassword($token)` (L209); `site/verify-email` → `actionVerifyEmail($token)` (L235); `site/resend-verification-email` → `actionResendVerificationEmail()` (L256) |

**10 controller files present, 9 live controllers** (one is dead — see below).

## Backend controllers (`backend/controllers/`, namespace `backend\controllers`)

| Controller | File | Public `action*` methods (route → method) |
|---|---|---|
| AjaxController | `backend/controllers/AjaxController.php` | `ajax/get-sailor-information-by-roll` → `actionGetSailorInformationByRoll()` (L29); `ajax/get-de-sailor-information-by-roll` → `actionGetDeSailorInformationByRoll()` (L52); `ajax/get-candesignation-by-cantype` → `actionGetCandesignationByCantype()` (L71); `ajax/get-all-assigned-district-by-center` → `actionGetAllAssignedDistrictByCenter()` (L79); `ajax/decode-phone` → `actionDecodePhone()` (L94) |
| BulkCheckController | `backend/controllers/BulkCheckController.php` | none — empty controller body, no `action*` methods defined at all; unreachable except for the base `yii\web\Controller` behavior (no default action exists to hit) |
| CanDesignationController | `backend/controllers/CanDesignationController.php` | `can-designation/index` → `actionIndex()` (L40); `can-designation/view` → `actionView($id)` (L57); `can-designation/create` → `actionCreate()` (L69); `can-designation/update` → `actionUpdate($id)` (L94); `can-designation/delete` → `actionDelete($id)` (L115) |
| DeSailorBranchController | `backend/controllers/DeSailorBranchController.php` | `de-sailor-branch/index` → `actionIndex()` (L40); `de-sailor-branch/view` → `actionView($id)` (L57); `de-sailor-branch/create` → `actionCreate()` (L69); `de-sailor-branch/update` → `actionUpdate($id)` (L94); `de-sailor-branch/delete` → `actionDelete($id)` (L115) |
| DeSailorReportController | `backend/controllers/DeSailorReportController.php` | `de-sailor-report/payment` → `actionPayment()` (L26); `de-sailor-report/payment-pdf` → `actionPaymentPdf()` (L50); `de-sailor-report/payment-excel` → `actionPaymentExcel()` (L78); `de-sailor-report/candidate-filter` → `actionCandidateFilter()` (L114); `de-sailor-report/candidate-filter-pdf` → `actionCandidateFilterPdf()` (L142); `de-sailor-report/candidate-filter-excel` → `actionCandidateFilterExcel()` (L171); `de-sailor-report/monitoring-application` → `actionMonitoringApplication()` (L274); `de-sailor-report/reference-candidate-pdf` → `actionReferenceCandidatePdf()` (L307); `de-sailor-report/reference-de-candidate-pdf` → `actionReferenceDeCandidatePdf()` (L355); `de-sailor-report/reference-candidate-excel` → `actionReferenceCandidateExcel()` (L404) |
| DeSailorsController | `backend/controllers/DeSailorsController.php` | `de-sailors/index` → `actionIndex()` (L46); `de-sailors/view` → `actionView($id)` (L66); `de-sailors/create` → `actionCreate()` (L78); `de-sailors/update` → `actionUpdate($id)` (L102); `de-sailors/reference-candidate` → `actionReferenceCandidate()` (L198); `de-sailors/add-reference-candidate` → `actionAddReferenceCandidate()` (L220); `de-sailors/reference-candidate-update` → `actionReferenceCandidateUpdate($id)` (L286); `de-sailors/delete` → `actionDelete($id)` (L336) |
| DistrictsController | `backend/controllers/DistrictsController.php` | `districts/index` → `actionIndex()` (L40); `districts/view` → `actionView($id)` (L57); `districts/create` → `actionCreate()` (L69); `districts/update` → `actionUpdate($id)` (L94); `districts/delete` → `actionDelete($id)` (L115) |
| EligibilityController | `backend/controllers/EligibilityController.php` | `eligibility/index` → `actionIndex()` (L40); `eligibility/view` → `actionView($id)` (L57); `eligibility/create` → `actionCreate()` (L69); `eligibility/update` → `actionUpdate($id)` (L94); `eligibility/delete` → `actionDelete($id)` (L120) |
| LogReportController | `backend/controllers/LogReportController.php` | `log-report/site-activity` → `actionSiteActivity()` (L14); `log-report/site-activity-view` → `actionSiteActivityView($date, $route, $method, $controller, $update_id)` (L83) — reads the `r2Storage` action-log NDJSON files written by the `on beforeRequest` global logging hook in `backend/config/main.php` |
| ReportController | `backend/controllers/ReportController.php` | `report/payment` → `actionPayment()` (L36); `report/payment-pdf` → `actionPaymentPdf()` (L60); `report/payment-excel` → `actionPaymentExcel()` (L88); `report/candidate-filter` → `actionCandidateFilter()` (L124); `report/candidate-filter-pdf` → `actionCandidateFilterPdf()` (L169); `report/candidate-filter-excel` → `actionCandidateFilterExcel()` (L203); `report/monitoring-application` → `actionMonitoringApplication()` (L389); `report/exam-date-check` → `actionExamDateCheck()` (L435); `report/all-reference-candidate-excel` → `actionAllReferenceCandidateExcel()` (L451); `report/reference-candidate-pdf` → `actionReferenceCandidatePdf()` (L579); `report/reference-de-candidate-pdf` → `actionReferenceDeCandidatePdf()` (L647); `report/reference-candidate-excel` → `actionReferenceCandidateExcel()` (L744); `report/district-candidate` → `actionDistrictCandidate()` (L920); `report/center-date-candidate` → `actionCenterDateCandidate()` (L952); `report/exam-date-center-candidate-pdf` → `actionExamDateCenterCandidatePdf()` (L988); `report/district-candidate-pdf` → `actionDistrictCandidatePdf()` (L1012); `report/district-candidate-excel` → `actionDistrictCandidateExcel()` (L1037); `report/reference-candidate-word` → `actionReferenceCandidateWord()` (L1125); `report/center-candidate` → `actionCenterCandidate()` (L1428); `report/center-candidate-pdf` → `actionCenterCandidatePdf()` (L1456); `report/center-candidate-excel` → `actionCenterCandidateExcel()` (L1482); `report/same-academic-info` → `actionSameAcademicInfo()` (L1566); `report/json-for-ls` → `actionJsonForLs()` (L1589) — by far the largest controller in the app (76 KB, 22 actions) |
| SailorBatchConfigurationController | `backend/controllers/SailorBatchConfigurationController.php` | `sailor-batch-configuration/index` → `actionIndex()` (L44); `.../view` → `actionView($id)` (L61); `.../create` → `actionCreate()` (L73); `.../update` → `actionUpdate($id)` (L112); `.../delete` → `actionDelete($id)` (L177); `.../delete-exam-date` → `actionDeleteExamDate()` (L188) |
| SailorBatchsController | `backend/controllers/SailorBatchsController.php` | `sailor-batchs/index` → `actionIndex()` (L43); `.../view` → `actionView($id)` (L60); `.../create` → `actionCreate()` (L72); `.../update` → `actionUpdate($id)` (L117); `.../delete` → `actionDelete($id)` (L161) |
| SailorCentDistMappingController | `backend/controllers/SailorCentDistMappingController.php` | `sailor-cent-dist-mapping/index` → `actionIndex()` (L40); `.../view` → `actionView($id)` (L57); `.../create` → `actionCreate()` (L69); `.../update` → `actionUpdate($id)` (L94); `.../delete` → `actionDelete($id)` (L119) |
| SailorCentersController | `backend/controllers/SailorCentersController.php` | `sailor-centers/index` → `actionIndex()` (L40); `.../view` → `actionView($id)` (L57); `.../create` → `actionCreate()` (L69); `.../update` → `actionUpdate($id)` (L94); `.../delete` → `actionDelete($id)` (L115) |
| SailorsController | `backend/controllers/SailorsController.php` | `sailors/index` → `actionIndex()` (L45); `sailors/cancel-request` → `actionCancelRequest()` (L62); `sailors/reference-candidate` → `actionReferenceCandidate()` (L85); `sailors/add-reference-candidate` → `actionAddReferenceCandidate()` (L106); `sailors/reference-candidate-update` → `actionReferenceCandidateUpdate($id)` (L180); `sailors/view` → `actionView($id)` (L234); `sailors/update` → `actionUpdate($id)` (L270); `actionCreate()` (L246) and `actionDelete($id)` (L365) are **commented out** — `sailors/create` and `sailors/delete` are not reachable routes |
| SiteController | `backend/controllers/SiteController.php` | plus `actions()` (L55) contributing `site/error` → `yii\web\ErrorAction` (also wired as `errorHandler.errorAction`); `site/index` → `actionIndex()` (L69, dashboard/chart data — **live default route target** since `backend/config/main.php`'s `defaultRoute` override is commented out); `site/login` → `actionLogin()` (L194, allowed for guests via `AccessControl`); `site/logout` → `actionLogout()` (L232, POST-only, requires auth) |
| SubjectsController | `backend/controllers/SubjectsController.php` | `subjects/index` → `actionIndex()` (L40); `.../view` → `actionView($id)` (L57); `.../create` → `actionCreate()` (L69); `.../update` → `actionUpdate($id)` (L94); `.../delete` → `actionDelete($id)` (L115) |
| TeController | `backend/controllers/TeController.php` | none — empty controller body, no `action*` methods; imports `PhpOffice\PhpWord` and encryption/JSON helpers but nothing is wired up (unreachable, likely scaffolding for a "TE" — Technical Education? — feature that was never finished) |
| UnionsController | `backend/controllers/UnionsController.php` | `unions/index` → `actionIndex()` (L54); `.../view` → `actionView($id)` (L71); `.../create` → `actionCreate()` (L83); `.../update` → `actionUpdate($id)` (L108); `.../delete` → `actionDelete($id)` (L128) |
| UpozilasController | `backend/controllers/UpozilasController.php` | `upozilas/index` → `actionIndex()` (L55); `.../view` → `actionView($id)` (L72); `.../create` → `actionCreate()` (L84); `.../update` → `actionUpdate($id)` (L107); `.../delete` → `actionDelete($id)` (L127) |
| UserController | `backend/controllers/UserController.php` | `user/index` → `actionIndex()` (L40); `user/view` → `actionView($id)` (L57); `user/create` → `actionCreate()` (L69); `user/update` → `actionUpdate($id)` (L93); `actionDelete($id)` (L120) is **commented out** — `user/delete` is not a reachable route |

**21 controller files, all 21 mapped into `controllerNamespace`**, but 2 (`BulkCheckController`, `TeController`) expose zero actions — see "notable observations."

All CRUD-shaped backend controllers (`CanDesignationController`, `DeSailorBranchController`, `DistrictsController`, `EligibilityController`, `SailorBatchsController`, `SailorCentDistMappingController`, `SailorCentersController`, `SubjectsController`, `UnionsController`, `UpozilasController`, `UserController`, and mostly `SailorsController`/`DeSailorsController`) follow the same 5-action `index/view/create/update/delete` shape — this is Yii Gii's default CRUD generator template, unmodified in structure.

Because `backend/config/main.php` wires a global `AccessControl` filter at the **application level** (`'as access' => [...]`, allowing only `login`/`error` unauthenticated, everything else requires `roles => ['@']`), every route above except `site/login` and `site/error` requires an authenticated backend user regardless of per-controller `behaviors()`.

---

## Console controllers (`console/controllers/`)

```
$ ls -la console/controllers
total 8
drwxrwxr-x 2 ... .
drwxrwxr-x 7 ... ..
-rw-rw-r-- 1 ... .gitkeep
```

**The directory is empty** — it contains only the placeholder `.gitkeep` file, no `.php` controller files at all. There are **zero custom console commands** in this app.

`console/config/main.php` (`controllerNamespace => 'console\controllers'`) declares one built-in mapping via `controllerMap`:

| Command id | Maps to | Purpose |
|---|---|---|
| `fixture` | `yii\console\controllers\FixtureController` (framework built-in), `namespace => 'common\fixtures'` | Loads/unloads DB fixtures for tests; the only fixture present is `common/fixtures/UserFixture.php` |

Additionally, `console/migrations/` holds Yii's standard DB migration files, run via the framework's built-in `yii migrate` command (`yii\console\controllers\MigrateController`, auto-registered by the framework, not a project controller):
- `m130524_201442_init.php`
- `m190124_110200_add_verification_token_column_to_user_table.php`

So "console routes" in this project reduce to framework defaults (`yii migrate`, `yii migrate/*`, `yii fixture/*`, `yii cache/*`, `yii help`, etc.) — there is no project-authored console automation.

---

## Dead / unused code

### `frontend/controllers/OnlinePaymentController_shurjo_pay.php` — confirmed dead

- Declares `class OnlinePaymentController` in `namespace frontend\controllers` (`OnlinePaymentController_shurjo_pay.php:14`) — **the exact same fully-qualified class name** as the live `frontend/controllers/OnlinePaymentController.php:15`.
- `grep -rn "OnlinePaymentController_shurjo_pay\|shurjo_pay" frontend backend common console` (excluding vendor) returns **zero matches** — nothing in the app ever requires this file by name, references it via `controllerMap`, or otherwise loads it.
- Yii2's class autoloading resolves `frontend\controllers\OnlinePaymentController` to the file whose name matches the class (`OnlinePaymentController.php`), via the `@frontend` path alias set in `common/config/bootstrap.php`. A file named `OnlinePaymentController_shurjo_pay.php` is never looked up by the autoloader for that class, so it is unreachable through normal Yii routing.
- If it were ever manually `require`d alongside the real file, PHP would fatal with "Cannot redeclare class frontend\controllers\OnlinePaymentController" — so the two files are mutually exclusive by construction, not just by convention.
- Conclusion: this is a leftover alternate implementation (ShurjoPay payment gateway variant, vs. the live file's SSL/aamarpay-style actions) — same pattern as the sibling Laravel project's `shurjo_pay_web.php` dead route file. Safe to delete/archive if this is intentional cleanup territory; not currently exercised by any live route.

### `backend/controllers/BulkCheckController.php` — dead/stub

Empty class body (`backend/controllers/BulkCheckController.php:12-15`), no `action*` methods. Imports several models (`SailorBatchConfiguration`, `SailorBatchs`, `Sailors`, etc.) suggesting planned functionality that was never implemented. No route currently resolves through it (any `/bulk-check/*` request 404s).

### `backend/controllers/TeController.php` — dead/stub

Same pattern: empty class body (`backend/controllers/TeController.php:21-26`), only `use` imports (`PhpOffice\PhpWord`, `AES256CTR`, `DataEncryption`, etc.), no actions. Every `/te/*` request 404s. Note: the sibling Laravel `join-navy-officer-legacy` app has a **live** `TeController` with `ApiCheck`/`upozila-uni` routes (`/admin/api-check`, `/admin/te/upozila-uni`) — this Yii2 sailor app appears to have scaffolded an equivalent controller but never finished wiring it.

### Dead config snapshot files (same pattern as the sibling app's dead route files)

Neither app's entry script (`frontend/web/index.php`, `backend/web/index.php`) references these; only `main.php`/`main-local.php` are merged. They are inert backups, not live config:

| File | App | Notes |
|---|---|---|
| `frontend/config/_main.php` | frontend | earlier snapshot of `main.php` (same `defaultRoute`, same empty `urlManager.rules`) |
| `backend/config/_main.php` | backend | earlier snapshot of `main.php` |
| `backend/config/09092025_main.php` | backend | dated snapshot, same shape as `main.php` |

### Commented-out actions (present in source, unreachable as routes)

| File | Method | Route that would exist if uncommented |
|---|---|---|
| `backend/controllers/SailorsController.php:246` | `actionCreate()` | `sailors/create` |
| `backend/controllers/SailorsController.php:365` | `actionDelete($id)` | `sailors/delete` |
| `backend/controllers/UserController.php:120` | `actionDelete($id)` | `user/delete` |

---

## REST controller: `frontend/controllers/ApiController.php`

```php
class ApiController extends \yii\rest\Controller
{
    public $modelClass = 'common\models\Eligibility';
    public $token = 'a2bc0cb09a28beci86sdbbs94adb9b407e1321122023';
    private $allowed_ip = ['127.0.0.1', '::1'];
    private $allowed_domain = ['127.0.0.1', 'localhost', 'http://joinnavysailor.local/'];
}
```

Extends `yii\rest\Controller` (the Yii2 REST base class) and sets `$modelClass = 'common\models\Eligibility'`, but it **does not extend `yii\rest\ActiveController`** (the class that actually auto-generates `index`/`view`/`create`/`update`/`delete` REST actions from `$modelClass` via its own `actions()` override) and does **not** override `actions()` itself. `yii\rest\Controller` alone contributes no CRUD actions — it only adds content-negotiation/rate-limiting/authentication behaviors. Net effect: **`ApiController` currently exposes zero callable actions.** The `$token` and `$allowed_ip`/`$allowed_domain` properties are declared but unused (no `behaviors()` override reads them, no filter class consumes them) — this looks like an abandoned custom-auth REST endpoint. Combined with the empty `urlManager.rules`, there is also no REST-style pretty-URL wiring (e.g. `['class' => 'yii\rest\UrlRule', 'controller' => 'api']`) anywhere, so even if actions existed they'd only be reachable at `/api/{action}` under the default route convention, not proper REST verb dispatch.

---

## Notable observations (flagging, not fixing)

- **Zero explicit `UrlManager` rules in both apps** — this is the single most consequential fact for anyone porting this app: there is no authoritative route list to read off a config file; the entire route surface is implicit in controller/action naming (109 live frontend+backend actions total, tallied below). Any controller/action rename is a silent route rename.
- **Class-name collision as dead-code marker**: `OnlinePaymentController_shurjo_pay.php` reusing the live class's exact FQCN is a strong signal it was deliberately disabled by renaming the file (rather than deleting it) — worth confirming with whoever did the ShurjoPay→SSL/aamarpay payment gateway switch before deleting.
- **Backend has no page-level route protection beyond the blanket `roles => ['@']` gate** — unlike the Laravel sibling app's finding that admin AJAX routes bypass the `isAdmin` group, here the opposite risk applies: the app-level `'as access'` filter in `backend/config/main.php` protects *everything* except `login`/`error` by default, so there's no equivalent "forgotten public AJAX route" class of bug possible at the routing layer (individual controllers' own `behaviors()`, e.g. `SiteController`'s `only => ['logout','signup']` on frontend, could still narrow/override this, but none do so backend-side beyond `SiteController` itself).
- **`console/controllers/` is empty** — no project Artisan/Yii console commands exist; all console usage is framework-default (`migrate`, `fixture`, etc.). Anyone expecting scheduled jobs, data-import scripts, or CLI tooling comparable to Laravel `artisan` commands will find none here.
- **Two stub controllers with imported-but-unused dependencies** (`BulkCheckController`, `TeController`) suggest abandoned features; worth a decision to finish or delete rather than leaving them as silent 404s.
- **Dead config snapshots** (`_main.php` × 2, `09092025_main.php`) mirror the Laravel sibling's dead dated route files — same "keep a timestamped copy before editing" habit, same risk of someone editing the wrong file.

---

## Summary — live, reachable action counts

Verified via `grep -rnE "^\s*public function action" <dir>/controllers/*.php` (anchored, so commented-out `// public function action...()` lines are correctly excluded; the `actions()` array-returning method, present in both `SiteController`s, is subtracted out below since it defines extra sub-actions like `error`/`captcha` rather than being a route itself):

| App | Controller files | Controllers with 0 actions | Live `action*` methods |
|---|---|---|---|
| frontend | 10 | 2 (`ApiController`, and the dead `OnlinePaymentController_shurjo_pay.php` which never autoloads) | **53** |
| backend | 21 | 2 (`BulkCheckController`, `TeController`) | **118** |
| console | 0 project controllers | — | 0 (framework-default `migrate`/`fixture` only) |

Total live, routable actions across both web apps: **171**. Treat the per-controller tables above as the source of truth for which route maps to which method/line.
