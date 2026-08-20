# Filters, Behaviors & Access Control Inventory — join-navy-sailor-legacy

## Yii2 has no middleware layer — here's the actual equivalent surface

This is a Yii2 2.0 **advanced application template** (`common/`, `frontend/`, `backend/`, `console/`), not Laravel, so there is no `Kernel.php`/global-middleware-stack/route-alias system to inventory the way `join-navy-officer-legacy`'s `middleware_inventory.md` does. The closest equivalents, in descending order of how "global" they are:

1. **App-level behaviors** — a `'as <name>' => [...]` entry in `common/config/main.php` / `frontend/config/main.php` / `backend/config/main.php` attaches a `yii\base\Behavior` (most commonly `yii\filters\AccessControl`) directly to the `yii\web\Application` object. Because `Application` extends `Module`, and every controller action bubbles an `EVENT_BEFORE_ACTION` event up through `Controller → Module → Application`, an app-level behavior listening for that event runs on **every single controller/action in that app**, before the controller's own filters even matter. This is the nearest thing this codebase has to Laravel's global `$middleware` stack.
2. **Controller-level `behaviors()`** — the per-controller override, analogous to route-level `->middleware([...])` in Laravel. Standard filter classes used here: `yii\filters\AccessControl` (role/action gating) and `yii\filters\VerbFilter` (HTTP-verb gating). No custom filter subclasses exist anywhere in this repo (see §6).
3. **Manual in-action checks** — `if (Yii::$app->user->isGuest) { redirect... }` written directly inside `actionXxx()` bodies. This turns out to be how *most* of the frontend candidate-facing "must be logged in" gating is actually implemented — not via a filter at all.
4. **`beforeAction()` overrides** — a handful of controllers override `beforeAction()` solely to flip `$this->enableCsrfValidation = false` for specific AJAX/webhook actions. Not access control, just CSRF opt-out.

Cross-referenced against `frontend/controllers/`, `backend/controllers/`, `console/controllers/`, `common/components/`, `frontend/components/`, `backend/components/`, and all three apps' `config/main.php`.

**`console/controllers/` is an empty directory** — no console commands exist in this codebase (only `console/config/`, `console/migrations/`, `console/models/` have content), so there is nothing to inventory there.

---

## 1. App-level access control — `backend/config/main.php`

This file has **two** `AccessControl` blocks. Only one of them is live.

### 1a. `'as beforeRequest'` (lines 60-72) — **dead, commented out**

```php
// 'as beforeRequest' => [  //if guest user access site so, redirect to login page.
//     'class' => 'yii\filters\AccessControl',
//     'rules' => [
//         [
//             'actions' => ['login', 'error'],
//             'allow' => true,
//         ],
//         [
//             'allow' => true,
//             'roles' => ['@'],
//         ],
//     ],
// ],
```
Entirely commented PHP — never parsed, never attached. Confirms the task brief's suspicion: this specific block (the one "around line 60-65") is dead.

### 1b. `'as access'` (lines 132-144) — **live and active**

```php
'as access' => [  // Access control: redirect guest users to login
    'class' => 'yii\filters\AccessControl',
    'rules' => [
        [
            'actions' => ['login', 'error'], // allow these actions for everyone
            'allow' => true,
        ],
        [
            'allow' => true,
            'roles' => ['@'], // all other actions require login
        ],
    ],
],
```
This is a **near-duplicate of the dead block above**, just re-added under a different behavior name (`access` vs `beforeRequest`) further down the same file, and *not* commented out. Because it's attached at the application level, it runs on **every backend controller and action**, without needing each controller to declare its own `AccessControl`. Effect: any action whose `actionId` is not `login` or `error` requires an authenticated user (`Yii::$app->user->isGuest === false`); unauthenticated requests are redirected to the backend `user->loginUrl` (defaults to `site/login`, i.e. `SiteController::actionLogin()`).

**Important gap:** the rule only checks role `@` (any authenticated Yii identity), not `user_type == 'admin'`. There is no per-request re-check anywhere in `backend/` that the logged-in identity's `user_type` is actually `'admin'` (see §5) — that check happens exactly once, at login time, inside `LoginForm::validatePassword()`. This app-level filter alone would let *any* authenticated `common\models\User` record (regardless of `user_type`) through to every backend controller/action it doesn't otherwise restrict, if that identity ever ended up authenticated in the backend app's session. In practice the frontend and backend apps use separate session-cookie names and separate `identityCookie` names (`join_bd_navy_front` / `_join_bd_navy_front` vs `join_navy-backend` / `_join_navy-backend`, see §3), so a frontend candidate login does not carry over into a backend session — but nothing at the filter layer itself enforces the admin/candidate distinction.

### 1c. `'on beforeRequest'` (lines 76-130) — global action-logging event listener, not a filter class

Also app-level and also runs on every backend request, but it's a raw event listener (`Yii::$app->on(\yii\base\Application::EVENT_BEFORE_ACTION, function($event) {...})`) registered from a config `on` hook, not a `Behavior`/filter class. Functionally the closest thing to Laravel global logging middleware: for every action it resolves `route`, `method`, `user id/name/ip`, and for `POST` requests writes an `add.ndjson`/`update.ndjson` entry (for GET, a date-stamped log) via `Yii::$app->r2Storage->actionLog(...)` (the `common\components\R2Storage` component, §3/§6) to `action_log/{controller_id}/...`. No equivalent listener exists in `frontend/config/main.php`.

**Frontend has no app-level `AccessControl` or `beforeRequest` equivalent at all** — confirmed by reading `frontend/config/main.php` in full (77 lines, reproduced in §3): there is no `'as ...'` key anywhere in it. Every "must be logged in" rule on the frontend is therefore either a controller-level `AccessControl` (rare — one controller, see §4.5) or a manual `Yii::$app->user->isGuest` check written into the action body (the norm — see §4.6-4.9).

---

## 2. App-level `bootstrap` / `components` — `common/`, `frontend/`, `backend/`

Each Yii2 application's `config/main.php` is merged in this order (per entry script, e.g. `frontend/web/index.php`, `backend/web/index.php`):
`common/config/main.php` → `common/config/main-local.php` → `{app}/config/main.php` → `{app}/config/main-local.php`.

`backend/config/09092025_main.php` and `backend/config/_main.php` (and their `frontend/config/_main.php` sibling) are **not referenced by any entry script** — dead/backup config files, not loaded at runtime.

### `common/config/main.php` (shared by all three apps)

```php
'components' => [
    'session' => [
        'class' => 'yii\web\Session',
        'cookieParams' => ['secure' => true],
    ],
    'cache' => [
        'class' => \yii\caching\FileCache::class,
    ],
    'r2Storage' => [
        'class' => 'common\components\R2Storage',
        'bucket' => 'sailor-images',
        'region' => 'auto',
        'verifySsl' => false,
        // accessKey/secretKey/endpoint/fileUrl left blank here — populated by main-local.php per environment
    ],
],
```
`r2Storage` is a custom S3-compatible storage service component (Cloudflare R2, based on `Aws\S3\S3Client`) used both for candidate-uploaded files and for the action-log writer described in §1c.

### `frontend/config/main.php`

| Key | Value | Note |
|---|---|---|
| `bootstrap` | `['log']` | `main-local.php` conditionally appends `'debug'`, `'gii'` when not `YII_ENV_TEST` (dev tooling) |
| `defaultRoute` | `'check-eligibility/index'` | public entry point, no auth |
| `components.request.enableCsrfValidation` | `false` | **CSRF validation is disabled app-wide on the frontend request component itself** — the per-controller `beforeAction()` CSRF opt-outs described in §4.9 are therefore redundant given this app-level setting (though harmless) |
| `components.user.identityClass` | `common\models\User` | shared identity class with backend |
| `components.user.loginUrl` | `['candidate/login']` | i.e. `frontend\controllers\CandidateController::actionLogin()`, **not** `SiteController::actionLogin()` (see §4.7 for why two login actions exist) |
| `components.user.identityCookie.name` | `_join_bd_navy_front` | |
| `components.session.name` | `join_bd_navy_front` | |
| `components.urlManager.rules` | `[]` | no explicit route rules — see `route_inventory.md` |

### `backend/config/main.php`

| Key | Value | Note |
|---|---|---|
| `bootstrap` | `['log']` | `main-local.php`'s debug/gii block is fully commented out — **backend never loads debug/gii tooling, even in dev**, unlike frontend |
| `components.request.enableCsrfValidation` | `false` | same app-wide CSRF disable as frontend |
| `components.user.identityClass` | `common\models\User` | |
| `components.user.identityCookie.name` | `_join_navy-backend` | separate cookie/session namespace from frontend, see §1b |
| `components.session.name` | `join_navy-backend` | |
| `components.errorHandler.errorAction` | `'site/error'` | |
| app-level behaviors | §1a/§1b/§1c | |

No custom `user` guard classes, no `AuthMethod` implementations, no API token/bearer auth components registered anywhere in `common`, `frontend`, or `backend` config.

---

## 3. Controller-by-controller `behaviors()` inventory

### 3.1 Backend admin CRUD controllers — `VerbFilter`-only pattern (12 controllers)

These all follow the identical pattern: `array_merge(parent::behaviors(), ['verbs' => [...]])`, where `parent::behaviors()` (they all `extend \yii\web\Controller` directly, no shared base controller exists) returns `[]`. So **the only controller-level filter is `VerbFilter` restricting `delete` to `POST`** — all access-control enforcement for these 12 controllers comes exclusively from the app-level `'as access'` behavior in §1b.

| Controller | File | `behaviors()` line |
|---|---|---|
| `CanDesignationController` | `backend/controllers/CanDesignationController.php` | 20 |
| `DeSailorBranchController` | `backend/controllers/DeSailorBranchController.php` | 20 |
| `DeSailorsController` | `backend/controllers/DeSailorsController.php` | 26 |
| `DistrictsController` | `backend/controllers/DistrictsController.php` | 20 |
| `EligibilityController` | `backend/controllers/EligibilityController.php` | 20 |
| `SailorBatchConfigurationController` | `backend/controllers/SailorBatchConfigurationController.php` | 24 |
| `SailorBatchsController` | `backend/controllers/SailorBatchsController.php` | 23 |
| `SailorCentDistMappingController` | `backend/controllers/SailorCentDistMappingController.php` | 20 |
| `SailorCentersController` | `backend/controllers/SailorCentersController.php` | 20 |
| `SailorsController` | `backend/controllers/SailorsController.php` | 25 |
| `SubjectsController` | `backend/controllers/SubjectsController.php` | 20 |
| `UserController` | `backend/controllers/UserController.php` | 20 |

Representative example (`backend/controllers/DistrictsController.php:20-32`):
```php
public function behaviors()
{
    return array_merge(
        parent::behaviors(),
        [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => ['delete' => ['POST']],
            ],
        ]
    );
}
```
`UserController` (backend admin-user management CRUD) is in this group too — creating/editing a backend `User` record does not set or constrain `user_type` in the controller at all (`grep -n "user_type" backend/controllers/UserController.php` returns nothing); it's a plain text field on the form, validated only as `'string'` in `common/models/User::rules()` (`user_type` has no enum/constant list anywhere).

### 3.2 Backend controllers with zero `behaviors()` override (5 controllers)

`BulkCheckController`, `DeSailorReportController`, `LogReportController`, `ReportController`, `TeController` define **no `behaviors()` method at all**. They rely entirely on the app-level `'as access'` filter (§1b) for gating — no `VerbFilter`, no `AccessControl` of their own. Functionally identical outcome to §3.1's controllers (any authenticated user, any HTTP verb, all actions), just without even the redundant `delete => POST` verb restriction.

### 3.3 Backend controllers with their own `AccessControl` (`UnionsController`, `UpozilasController`)

Both add an explicit `'access'` key on top of `array_merge(parent::behaviors(), [...])` (`backend/controllers/UnionsController.php:20-47`, `backend/controllers/UpozilasController.php:20-48`):
```php
'access' => [
    'class' => \yii\filters\AccessControl::className(),
    'rules' => [
        [
            'allow' => true,
            // 'actions' => ['delete'],  // Specify the actions you want to restrict
            'roles' => ['@'],  // Only authenticated users (@) can access this action
        ],
        [
            'allow' => false,  // Deny access to other users
            // 'actions' => ['delete'],
        ],
    ],
],
```
No `'only'`/`'actions'` scoping, so this applies to every action on the controller — but it enforces the exact same rule (`role @`, i.e. any authenticated user) that the app-level filter in §1b already enforces for every backend controller. **Purely redundant, not a gap** — belt-and-suspenders duplication left over from whoever scaffolded these two controllers, likely via Gii, before the app-level filter existed or was noticed.

### 3.4 `backend/controllers/SiteController.php` — admin login/logout

`behaviors()` at lines 26-50:
```php
'access' => [
    'class' => AccessControl::class,
    'rules' => [
        ['actions' => ['login', 'error'], 'allow' => true],
        ['actions' => ['logout', 'index'], 'allow' => true, 'roles' => ['@']],
    ],
],
'verbs' => ['class' => VerbFilter::class, 'actions' => ['logout' => ['post']]],
```
`actionLogin()` (line 194) sets `$model->user_type = 'admin'` (line 203) before validating against `LoginForm` — this is the actual enforcement point for "only admin-type users can log into the backend" (see §5). Guarded additionally by a math-captcha stored in session (`captureValue()`, lines 151-185).

### 3.5 `backend/controllers/AjaxController.php` — CSRF-exempt AJAX lookups, still gated

No `behaviors()` override; only overrides `beforeAction()` (lines 19-23) to set `$this->enableCsrfValidation = false`. Because it's a backend controller, it's still covered by the app-level `'as access'` filter (§1b) — these AJAX endpoints (`actionGetSailorInformationByRoll`, etc.) require an authenticated backend session even though CSRF checking is off for them.

---

### 3.6 Frontend controllers with `AccessControl`

**`frontend/controllers/SailorCandidateController.php`** (`behaviors()`, lines 39-59):
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
**Confirmed gaps:**
- The `only` key restricts the *entire filter* to the `payment` and `academic-info` actions. The `verify-candidate` rule (allow guests, `roles => ['?']`) is therefore **dead — it can never fire**, since `verify-candidate` isn't in `only` and the filter never runs for it at all.
- The controller has 10 actions total (`actionPayment`, `actionAcademicInfo`, `actionPersonalInfo`, `actionApplicationPreview`, `actionCompleteApplication`, `actionDownload`, `actionDownloadForm`, `actionVerifyCandidate`, `actionRefundPhone`, `actionCancelApplication` — `grep -n "public function action" frontend/controllers/SailorCandidateController.php`). Only `payment` and `academic-info` are covered by `AccessControl`. The other 8 — including `personal-info`, `application-preview`, `complete-application`, `download`, `refund-phone`, `cancel-application` — have **no access-control filter and no manual `Yii::$app->user->isGuest` guard** (`grep -n "isGuest" frontend/controllers/SailorCandidateController.php` returns nothing). They dereference `Yii::$app->user->identity->...` directly (e.g. line 49, `$sailor->birth_registration_no = Yii::$app->user->identity->birth_registration_no;`), which would fatal on a null identity for a guest request rather than cleanly redirect. This is the sailor-app's version of the officer repo's `CanContinueApplication` "not applied to every step" gap.

**`frontend/controllers/SiteController.php`** (lines 27-53):
```php
'access' => [
    'class' => AccessControl::class,
    'only' => ['logout', 'signup'],
    'rules' => [
        ['actions' => ['signup'], 'allow' => true, 'roles' => ['?']],
        ['actions' => ['logout'], 'allow' => true, 'roles' => ['@']],
    ],
],
'verbs' => ['class' => VerbFilter::class, 'actions' => ['logout' => ['post']]],
```
This `SiteController` is the **unmodified Yii2 "advanced app" template scaffold controller** (login/signup/contact/about/password-reset/verify-email actions with the framework's default flash messages — e.g. `actionLogin()` at line 96 also sets `$model->user_type = 'candidate'`). Its `login`/`signup` views exist (`frontend/views/site/login.php`, `frontend/views/site/signup.php`) but are **not linked from the live layout** (`frontend/views/layouts/mainNavy.php` links only to `candidate/login`; `grep -rl "site/login\|site/signup" frontend/views` returns only the two orphaned view files themselves). The real, in-use candidate auth flow is `frontend/controllers/CandidateController.php` (§3.7). `SiteController::actionLogin`/`actionSignup` remain reachable by direct URL (`/site/login`, `/site/signup`) — a second, parallel, effectively-dead login path with its own `user_type = 'candidate'` write, not a security hole per se (same user model, same validation) but dead/confusing surface area.

### 3.7 `frontend/controllers/CandidateController.php` — the real candidate auth flow, no filter at all

**No `behaviors()` override.** All gating is manual, inline, per action (`grep -n "isGuest"`):
- `actionSignUp()` (line 52): `if (!Yii::$app->user->isGuest) { ... }` — blocks already-logged-in users from re-signing-up.
- `actionLogin()` (line 162): same guest-only guard (line 172), sets `$model->user_type = 'candidate'` (line 177) — the actual enforcement point for "only candidate-type users can log in here" (mirrors `backend/controllers/SiteController::actionLogin`'s `'admin'` assignment).
- `actionChangePassword()` (line 374): `if (Yii::$app->user->isGuest)` — blocks guests.
- `actionRequestPasswordReset()` (line 406): guest-only guard.

That's 4 of the controller's 7 actions (`grep -n "public function action" frontend/controllers/CandidateController.php`: `actionValidateBirthRegistration`, `actionSignUp`, `actionLogin`, `actionLogout`, `actionDownloadForm`, `actionChangePassword`, `actionRequestPasswordReset`). The other 3 — `actionLogout()`, `actionDownloadForm()`, `actionValidateBirthRegistration()` — have **no guard at all** (not flagged as a real gap: logout is idempotent for a guest, download-form serves a public PDF, and birth-registration validation is a pre-signup public lookup — but noted for completeness since they diverge from the controller's otherwise-consistent guard pattern).

This controller is also where "sailor vs de-sailor" is decided (§5) — it is **not** an authentication distinction, it's a data-driven branch.

### 3.8 `frontend/controllers/DeSailorController.php` — zero behaviors, zero manual guards

**No `behaviors()` override and no `isGuest` checks anywhere** (`grep -n "isGuest\|behaviors" frontend/controllers/DeSailorController.php` — no matches for either). This is the de-sailor-track sibling of `SailorCandidateController` (§3.6) — same action set (`actionPayment`, `actionAcademicInfo`, `actionPersonalInfo`, `actionApplicationPreview`, `actionCompleteApplication`, `actionDownload`, `actionDownloadForm`, `actionVerifyCandidate`), all of which read `Yii::$app->user->identity->...` directly. **Confirmed gap, and an asymmetry with §3.6**: `SailorCandidateController` at least gates `payment`/`academic-info` with `AccessControl`; `DeSailorController`'s equivalent actions have no gate whatsoever — not even the partial coverage its sibling has.

### 3.9 `frontend/controllers/MyApplicationController.php` — manual guard, single action

Only action, `actionIndex()` (lines 11-31):
```php
if (Yii::$app->user->isGuest) {
    return $this->redirect(['candidate/login']);
} else {
    $userIdentity = Yii::$app->user->identity->id;
    ...
}
```
Clean, correctly-guarded manual check — the pattern the other frontend controllers in §3.7/§3.8 are missing in places.

### 3.10 `frontend/controllers/CheckEligibilityController.php` — intentionally public

No `behaviors()` override. This is the frontend `defaultRoute` (`check-eligibility/index`, §2) — the public eligibility-check wizard candidates use before any account exists, so the absence of auth gating is by design. Only override is `beforeAction()` (lines not reproduced) disabling CSRF for `get-description`. `actionIndex()` (line 39) has an `if (!Yii::$app->user->isGuest)` branch, but it's a UX convenience (pre-fill known DOB) not an access gate.

### 3.11 `frontend/controllers/OnlinePaymentController.php` — payment-gateway callbacks

No `behaviors()` override; only `beforeAction()` disabling CSRF for `ssl-success`, `ssl-cancel`, `ssl-fail` (lines ~14-24) — expected, since these are server-to-server SSLCommerz gateway callbacks that can't carry a session CSRF token, same rationale the officer repo's `payment-response`/`payment-fail` CSRF exemptions document. No access control on any action, including `actionPaymentResponseDeSailor`/`actionPaymentResponseSailor` — also expected for gateway callbacks.

### 3.12 `frontend/controllers/OnlinePaymentController_shurjo_pay.php` — dead file, PSR-4 name collision

```php
namespace frontend\controllers;
...
class OnlinePaymentController extends \yii\web\Controller
```
The class is named `OnlinePaymentController` — identical to the class already defined in `frontend/controllers/OnlinePaymentController.php`. Under Composer's PSR-4 autoloading (which this Yii2 app uses), the class `frontend\controllers\OnlinePaymentController` can only ever resolve to the file literally named `OnlinePaymentController.php`; a file named `OnlinePaymentController_shurjo_pay.php` is never looked up by the autoloader for that class name. **This entire file is dead code, unreachable by any route** — directly analogous to the officer repo's `CandidateController_BK.php` finding. Its own `beforeAction()` CSRF-exemption for `payment-response-sailor` never executes.

### 3.13 `frontend/controllers/AjaxController.php` — fully public, no app-level backstop

No `behaviors()`; only `beforeAction()` disabling CSRF (lines 17-21). Unlike the backend `AjaxController` (§3.5), **there is no app-level `AccessControl` on the frontend** (§1), so these lookup endpoints (`actionDistrictByCandidateType`, etc.) are reachable by anyone, unauthenticated, with CSRF off. This mirrors the officer repo's "public reference-data AJAX endpoints" pattern and is presumably intentional (they only return public district/union/upozila lists), but it's worth flagging that the *absence* of a filter here is doing real work, not an oversight elsewhere compensating for it.

### 3.14 `frontend/controllers/ApiController.php` — dead REST scaffold

```php
class ApiController extends \yii\rest\Controller
{
    public $modelClass = 'common\models\Eligibility';
    public $token = 'a2bc0cb09a28beci86sdbbs94adb9b407e1321122023';
    private $allowed_ip = ['127.0.0.1', '::1'];
    private $allowed_domain = ['127.0.0.1', 'localhost', 'http://joinnavysailor.local/'];
}
```
Extends `yii\rest\Controller` (which brings its own default `behaviors()` — `contentNegotiator`, `verbFilter`, `authenticator`, `rateLimiter` — none configured beyond the framework defaults here) but **defines zero `actionXxx()` methods** (`grep -c "public function action"` → `0`). The `$token`/`$allowed_ip`/`$allowed_domain` properties are declared but never referenced anywhere else in the class or checked by any filter. Dead scaffolding — no live route can ever reach a real action on this controller (default REST routing would only expose CRUD actions if this extended `yii\rest\ActiveController`, which it doesn't).

---

## 4. Auth/session state: candidate vs admin vs de-sailor

There is no Yii2 equivalent of the officer repo's `isAdmin`/`isCandidate` middleware aliases applied declaratively at the route level. Instead:

1. **Single shared identity model**: both apps set `components.user.identityClass = 'common\models\User'` (§2) — one `User` table backs both admin and candidate logins.
2. **`user_type` string column** (`common/models/User.php`, `@property string $user_type`, validated only as `[['user_group', 'user_type'], 'string']` at line 69 — no enum, no constants defined for its values anywhere in the codebase) is the sole discriminator. Observed literal values, from every write-site (`grep -rn "user_type" common backend frontend`):
   - `'admin'` — written once, `backend/controllers/SiteController.php:203` (`actionLogin`).
   - `'candidate'` — written at `frontend/controllers/SiteController.php:103` (`actionLogin`, the orphaned scaffold, §3.6), `frontend/controllers/CandidateController.php:177` (`actionLogin`, the real flow, §3.7), and `frontend/models/SignupForm.php:163` (`signup()`).
3. **Enforcement is at login time only**, via `common/models/LoginForm::validatePassword()` (lines 58-72):
   ```php
   if ($user && $user->user_type != $this->user_type) {
       $this->addError($attribute, 'You are not ' . $this->user_type . ' user!');
   }
   ```
   The calling controller sets `$model->user_type` before validating (`'admin'` in `backend/controllers/SiteController.php:203`, `'candidate'` in `frontend/controllers/CandidateController.php:177`). This is a **one-time gate at authentication**, not a per-request middleware check — there is no equivalent of the officer repo's `IsAdmin`/`IsCandidate` middleware re-validating `user_type` on every subsequent request. Post-login, the backend app's own protection is entirely the app-level `roles => ['@']` check (§1b), which only asks "is *any* user authenticated", not "is this specifically an admin".
4. **Session/cookie isolation is the practical backstop**: backend (`join_navy-backend` session / `_join_navy-backend` identity cookie) and frontend (`join_bd_navy_front` session / `_join_bd_navy_front` identity cookie) use different cookie names entirely (§2), so a candidate authenticated on the frontend does not carry an authenticated session into the backend app, and vice versa — even though both point at the same `identityClass`/user table.
5. **"Sailor" vs "de-sailor" is not a `user_type` value and not an auth distinction at all.** Both tracks share the same `user_type = 'candidate'` identity. Which track a candidate is on is decided by **data**, inside `frontend/controllers/CandidateController.php` (e.g. lines 233-267): it inspects the candidate's eligibility-check result (`$return['sailor_or_de_sailor']`, set to `'sailor'` or `'de_sailor'`) and branches into either the `Sailors` or `DeSailors` model/controller pair accordingly (`SailorCandidateController` vs `DeSailorController`, §3.6/§3.8). There is no login-time or per-request filter analogous to a hypothetical "IsDeSailor" — it's pure application/business logic, not access control.

---

## 5. Custom filter classes — none exist

Checked `common/components/`, `frontend/components/`, `backend/components/` (backend has no `components/` directory at all) for anything extending `yii\base\ActionFilter`/`yii\filters\AccessControl`/`yii\filters\AuthMethod` or similar:

| File | Class | Extends | Role |
|---|---|---|---|
| `common/components/R2Storage.php` | `R2Storage` | `yii\base\Component` | S3-compatible (Cloudflare R2) file storage + action-log writer (§1c, §2). A plain service component, registered in `common/config/main.php`, **not** a filter — nothing about it runs on the request lifecycle automatically. |
| `frontend/components/StepAndSupport.php` | `StepAndSupport` | `yii\base\Widget` | View-rendering widget (application-wizard step indicator), not a filter. |
| `frontend/components/SupportNo.php` | `SupportNo` | `yii\base\Widget` | View-rendering widget (support contact number display), not a filter. |

Every access-control filter used anywhere in this codebase is the stock `yii\filters\AccessControl` or `yii\filters\VerbFilter` — no custom `AuthMethod`, no bearer/token auth filter, no rate limiter, no custom CORS filter, no equivalent of the officer repo's `XssValidation` middleware. The closest analog to that last one is a **model-level** (not request-level) input filter — `[['name_en', 'name_bn'], 'filter', 'filter' => 'strip_tags']` — present in exactly four models: `common/models/Districts.php:68`, `common/models/Subjects.php:40`, `common/models/CanDesignation.php:42`, `common/models/SailorCenters.php:39`. It strips tags from two specific attributes on save, scoped per-model, not an app-wide request sanitizer.

---

## Summary table

| # | Controller / config | Filter(s) | Enforced by | Confirmed gap |
|---|---|---|---|---|
| 1 | `backend/config/main.php` `'as beforeRequest'` | `AccessControl` | Nowhere — commented out (lines 60-72) | Dead code |
| 2 | `backend/config/main.php` `'as access'` | `AccessControl` | App-level, every backend controller/action except `login`/`error` (lines 132-144) | Only checks role `@`, not `user_type=='admin'` |
| 3 | `backend/config/main.php` `'on beforeRequest'` | event listener (not a filter class) | App-level, every backend request (lines 76-130) | None — logging only |
| 4 | 12 backend CRUD controllers (§3.1) | `VerbFilter` only | `delete` action, `POST` only | Access control comes solely from app-level filter #2 |
| 5 | 5 backend controllers, zero behaviors (§3.2) | none | App-level filter #2 only | No verb restriction at all |
| 6 | `UnionsController`, `UpozilasController` (§3.3) | `AccessControl` (role `@`, all actions) + `VerbFilter` | Controller-level, redundant with #2 | None — just duplicated |
| 7 | `backend/controllers/SiteController.php` (§3.4) | `AccessControl` + `VerbFilter` | `login`/`error` public, `logout`/`index` require `@` | None |
| 8 | `backend/controllers/AjaxController.php` (§3.5) | none (CSRF disabled in `beforeAction`) | App-level filter #2 | None |
| 9 | `frontend/controllers/SailorCandidateController.php` (§3.6) | `AccessControl`, `only`=>`['payment','academic-info']` | Those 2 of 10 actions only | 8 actions ungated; `verify-candidate` guest rule is dead (excluded by `only`) |
| 10 | `frontend/controllers/SiteController.php` (§3.6) | `AccessControl`, `only`=>`['logout','signup']` | `signup` public, `logout` requires `@` | Orphaned scaffold, not linked from live layout; parallel dead login path |
| 11 | `frontend/controllers/CandidateController.php` (§3.7) | none — manual `isGuest` checks | 4 of 7 actions manually guarded | `actionLogout`, `actionDownloadForm`, `actionValidateBirthRegistration` unguarded (low risk) |
| 12 | `frontend/controllers/DeSailorController.php` (§3.8) | none | Nothing | All 8 actions (payment, academic-info, personal-info, etc.) completely ungated — worse than its `SailorCandidateController` sibling |
| 13 | `frontend/controllers/MyApplicationController.php` (§3.9) | none — manual `isGuest` check | Its 1 action, correctly | None |
| 14 | `frontend/controllers/CheckEligibilityController.php` (§3.10) | none | Public by design (defaultRoute) | None — intentional |
| 15 | `frontend/controllers/OnlinePaymentController.php` (§3.11) | none (CSRF disabled for gateway callbacks) | Public by design | None — intentional |
| 16 | `frontend/controllers/OnlinePaymentController_shurjo_pay.php` (§3.12) | n/a | Nowhere — unreachable | Dead file (PSR-4 class-name collision) |
| 17 | `frontend/controllers/AjaxController.php` (§3.13) | none (CSRF disabled) | Nothing — fully public | None — same pattern as backend's but with no app-level backstop; appears intentional (public reference data) |
| 18 | `frontend/controllers/ApiController.php` (§3.14) | `yii\rest\Controller` defaults, unconfigured | N/A — no actions defined | Dead scaffold, unreachable |
| 19 | Custom filter classes (§5) | — | — | None exist; only stock `AccessControl`/`VerbFilter` used anywhere |
