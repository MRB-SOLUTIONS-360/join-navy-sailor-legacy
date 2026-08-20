# Deep Dive — Admin: Users, Auth, Global Settings, Login Log

**Scope:** `backend/controllers/{UserController,SiteController,LogReportController}.php`, `backend/views/{user,site,log-report}/*.php`, `backend/views/layouts/{admin,top_bar,left_side_menu}.php` (+ the app-wide `backend/config/main.php` behaviors that gate them), `backend/assets/AppAdminAsset.php`, and every Model/Component they touch (`common/models/{User,LoginForm}.php`, `backend/models/UserSearch.php`, `common/components/R2Storage.php`).

**Generated:** 2026-08-20. No application code was modified to produce this document.

**Framework note:** this is a Yii2 2.0 advanced-template app, not Laravel. There is no `routes/web.php`, no `FormRequest`, no middleware-group syntax. The nearest Yii2 equivalents are used throughout this document and are exactly as mapped in `docs/00-inventories/controller_inventory.md` (top of file) and `docs/00-inventories/middleware_inventory.md` (§1): Laravel middleware groups → Yii2 **app-level behaviors** (`'as <name>' => [...]` in `backend/config/main.php`, which attach to the `yii\web\Application` object itself and therefore run on every controller/action in the app); Laravel `FormRequest` validation → each Model's `rules()` method.

---

## 0. Layout shell + the app-wide auth gate (used by every page below)

### `backend/views/layouts/admin.php` — authenticated admin shell
- **File:** `backend/views/layouts/admin.php` (69 lines). Registered as the app's default layout by `backend/config/main.php:17` (`'layout' => 'admin'`) — applies to every `backend/*` view except the login screen.
- **Includes:** `$this->render('top_bar')` (line 32), `$this->render('left_side_menu')` (line 33), `$content` (line 39).
- **Global JS/CSS loaded on every authenticated admin page**, via `backend\assets\AppAdminAsset` (line 9) plus two **hardcoded, non-asset-bundle `<script>` tags** (lines 25-26):
  ```php
  <script src="<?= Yii::getAlias('@web'); ?>/adminAsset/js/vendor.min.js"></script>
  <script src="<?= Yii::getAlias('@web'); ?>/adminAsset/js/hyper-config.js"></script>
  ```
  This is the **"Hyper" (Coderthemes) Bootstrap 5 admin theme** — same theme family as the officer repo's admin skin, confirmed by the layout's own `<meta name="description">`/`<meta name="author" content="Coderthemes">` tags (`admin.php:21-22`).

### `AppAdminAsset` — double jQuery load (cite `css_inventory.md` / `javascript_inventory.md`)
- **File:** `backend/assets/AppAdminAsset.php` (38 lines):
  ```php
  public $css = [
      ['adminAsset/css/app.css', 'id' => 'app-style'],
      'adminAsset/css/icons.min.css',
  ];
  public $js = [
      //'adminAsset/js/vendor.min.js',                              // commented out here...
      'adminAsset/vendor/daterangepicker/moment.min.js',
      'adminAsset/vendor/daterangepicker/daterangepicker.js',
      'adminAsset/vendor/apexcharts/apexcharts.min.js',
      ///'adminAsset/js/pages/demo.dashboard-analytics.js',           // commented out, never loaded
      'adminAsset/js/app.min.js',
  ];
  public $depends = ['yii\web\YiiAsset'];
  ```
  `javascript_inventory.md` (lines 49-69) confirms the double-jQuery-load finding directly relevant to this scope: `vendor.min.js` — loaded as a raw hardcoded `<script>` tag in `admin.php:25`, **not** through this bundle (it's commented out of `$js` here) — itself bundles **jQuery 3.6.0 + Bootstrap 5.2.3 + SimpleBar**. `AppAdminAsset::$depends` separately pulls in `yii\web\YiiAsset`, which publishes `bower-asset/jquery`. **Net effect: jQuery loads twice on every single admin page** (login form, dashboard, user list/edit, site-activity report) — once bundled inside `vendor.min.js`, once via Yii2's own `JqueryAsset`.
  `css_inventory.md` (lines 18-25, 67-77, 107-109) additionally notes: `AppAdminAsset` ("Hyper" theme, ~839 KB) is the sole live CSS tree for the entire authenticated backend; `daterangepicker.css` is half-wired (its JS is registered, the matching CSS sitting in the same vendor folder never was); and `demo.dashboard-analytics.js` — which would normally drive the dashboard's ApexCharts — is explicitly commented out of `$js` (see §1 "Admin Dashboard" below for why the dashboard still renders charts anyway, via its own inline script).

### `backend/views/layouts/top_bar.php`
- **File:** `backend/views/layouts/top_bar.php` (68 lines).
- Shows a **static** avatar image (`adminAsset/images/users/avatar-1.jpg`, not the real user's photo — `component_inventory.md` §4 confirms this), the plain (undecrypted — this app has no AES-encryption layer over `User.username`, unlike the officer app) `Yii::$app->user->identity->username` (line 47), and a logout `<form method="post">` (lines 58-63) posting to `/site/logout`. **No "Change Password" link exists anywhere in the top bar or sidebar** — confirmed by grepping the whole scope for `change-pass`/`changePass`, zero hits outside vendor theme demo files. There is no self-service admin password-change screen in this app at all (contrast with the officer app's `admin-change-pass` page) — the only way to change any admin's password is `UserController::actionUpdate`, i.e. one admin editing another admin's (or their own) record through the User CRUD form (see §2).

### `backend/views/layouts/left_side_menu.php`
- **File:** `backend/views/layouts/left_side_menu.php` (183 lines). `component_inventory.md` §4 confirms it's a static Bootstrap-5-collapse nav tree: Sailor / DE Sailor / Sailor Setting / Sailor Report / DE Sailor Report / User.
- **"User" menu link (line 177-179) has no gating at all**:
  ```php
  <li class="side-nav-item">
      <?= Html::a(' <i class="uil-comments-alt"></i> <span> User </span>', Url::toRoute(['user/index']), ['class' => 'side-nav-link']) ?>
  </li>
  ```
  This is a meaningful contrast with the officer app's finding for the same area: there, the "Users" sidebar link was UI-gated to a hardcoded `Auth::id() in [1,2,3]` allowlist. Here, **every** authenticated admin sees and can click "User" — the only hardcoded-ID gate anywhere in this sidebar is on a *different, unrelated* link: `report/json-for-ls` ("JSON For LS", line 144), gated to `in_array(Yii::$app->user->id, [1])`. The User-management screen itself carries no ID-based restriction, UI-level or otherwise.
- **No link to the Site Activity Log screen exists anywhere in this file** (or anywhere else in the repo — confirmed via `grep -rln "site-activity\|log-report" backend/` returning only the view file that renders the report itself). `log-report/site-activity` is a fully orphaned admin screen, reachable only by an admin who knows/guesses the direct URL. This is a stronger version of the officer app's "unlinked-but-findable" pattern — the officer app's Login Log at least had a sidebar entry.

### The auth gate: no controller-level `AccessControl` needed — an **app-level** behavior does it
`UserController` and `LogReportController` (this doc's other two controllers) define **no `AccessControl` of their own** — `controller_inventory.md` flags this explicitly (line 483: *"No AccessControl — notable, since this controller manages admin user accounts and password hashes"*; line 328 for `LogReportController`: *"Not defined — no access control"*). Read together with `middleware_inventory.md` §1b, this is **not actually a gap**: `backend/config/main.php` attaches an application-level `AccessControl` behavior that runs on every backend controller/action regardless of what that controller declares locally:
```php
// backend/config/main.php:132-144
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
Because `Application` extends `Module` and every action's `EVENT_BEFORE_ACTION` bubbles up through `Controller → Module → Application`, this single block is the functional equivalent of the officer app's `['auth', 'backbuttoncheck', 'isAdmin']` middleware group — it is why `UserController`/`LogReportController` (and 9 other CRUD controllers, per `middleware_inventory.md` §1b/§3) are safely reachable only by authenticated users despite having no filters of their own. **Important gap `middleware_inventory.md` §1b calls out and this doc confirms applies directly to `UserController`:** the rule only checks role `@` (any authenticated `common\models\User`, i.e. `Yii::$app->user->isGuest === false`), **not** `user_type == 'admin'`. That distinction is checked exactly once, at login time, inside `LoginForm::validatePassword()` (see §1 "Admin Login" below) — there is no per-request re-check anywhere in `backend/`, unlike the officer app's `IsAdmin` middleware which re-derives and re-validates on every request.

A dead predecessor of this same rule sits earlier in the same file, commented out:
```php
// backend/config/main.php:60-72 ('as beforeRequest', fully commented out — dead code)
```
`middleware_inventory.md` §1a documents this as a near-duplicate later re-added under the different behavior name `access` (not `beforeRequest`) and left live — i.e. someone tried this exact filter once, commented it out, then re-added a copy of it further down the file rather than uncommenting the original.

**CSRF validation is disabled app-wide on the backend** (`backend/config/main.php:21`, `'enableCsrfValidation' => false` on the `request` component) — `middleware_inventory.md` line 117 confirms this is the identical app-wide disable pattern as the frontend. Every POST form in this scope (login, user edit, site-activity filter) submits without CSRF protection.

### The audit-log writer: `'on beforeRequest'` global event listener
Also attached at the application level (`backend/config/main.php:71-130`), and the source that feeds §4's "Site Activity Log" screen:
```php
'on beforeRequest' => function ($event) {
    Yii::$app->controllerMap = [];
    Yii::$app->on(\yii\base\Application::EVENT_BEFORE_ACTION, function($event) {
        $action = $event->action;
        $userId = Yii::$app->user->id ?? 'guest';
        // ...resolves route, method, user id/name/ip, POST/GET params...
        if ($method === 'POST') {
            if ($is_update && $update_id) {
                Yii::$app->r2Storage->actionLog(logData: $logData, logFile: 'action_log/'.$controller_id.'/update.ndjson');
            } else {
                Yii::$app->r2Storage->actionLog(logData: $logData, logFile: 'action_log/'.$controller_id.'/add.ndjson');
            }
        } else {
            Yii::$app->r2Storage->actionLog(logData: $logData, logFile: 'action_log/'.$controller_id.'/'.date('Y-m-d').'_'.strtolower($method).'.ndjson');
        }
    });
},
```
This listener fires on **every single backend controller/action, every request** (`middleware_inventory.md` §1c: *"Functionally the closest thing to Laravel global logging middleware"*). Unlike the officer app's equivalent (where the NDJSON *writer* calls were commented out and the report screen was built against a source that's never populated), **this writer is fully live** — it is the mechanism behind §4's "Site Activity Log" page. `common\components\R2Storage::actionLog()` (`common/components/R2Storage.php`) implements this by **downloading the entire existing NDJSON object from Cloudflare R2, decoding every line, linear-scanning for a matching `route`, prepending the new entry, and re-uploading the whole file** on every write — a full read-modify-write cycle against an ever-growing blob, on every POST/GET to every controller, with no locking against concurrent requests (two simultaneous admin actions writing to the same `action_log/<controller>/...ndjson` key can race and one write can silently clobber the other). `R2Storage`'s R2 client is also constructed with `'verify' => $this->verifySsl`, and `common/config/main.php:26` sets `'verifySsl' => false` for the `r2Storage` component — TLS certificate verification is disabled for **all** R2 traffic (log reads/writes and candidate file storage alike), the same class of finding the officer app had for its NDJSON fetch, but broader in scope here.

---

## 1. Page Inventory — Login, Dashboard, Logout

### Page: Admin Login
- **URL / Route:** `site/login` (GET renders form, POST submits) — `backend/controllers/SiteController.php::actionLogin()`
- **Portal:** Admin
- **Permission Required:** None (`allow => true` for `login` in both the app-level `'as access'` rule and `SiteController`'s own controller-level `AccessControl`, which duplicates it — `controller_inventory.md:422-436` confirms `SiteController` is "the only backend controller with a complete, role-gated AccessControl setup", layered on top of the already-global app-level gate).
- **View File:** `backend/views/site/login.php`
- **Layout Used:** `blank` (`$this->layout = 'blank';`, `SiteController.php:200`) — a separate, near-empty chrome (`backend/views/layouts/blank.php`, 33 lines: `<head>` + `$content` + `</body>`, no nav/footer), registering a **second**, distinct asset bundle `backend\assets\AppAsset` (stock Yii2 scaffold `css/site.css` + `yii\web\YiiAsset` + `yii\bootstrap5\BootstrapAsset`) — not `AppAdminAsset`. `css_inventory.md:107` confirms `AppAsset` survives *only* because this one action opts into it.
- **Purpose:** Admin username/password login with a math CAPTCHA (no email/SMS OTP flow at all in this app — a simpler auth model than the officer repo's IP/OTP branch).
- **Detailed Description:** `LoginForm` (`common/models/LoginForm.php`) is instantiated with `$model->user_type = 'admin'` hardcoded (`SiteController.php:203`) before validation, so the same form model that a candidate frontend login also uses is forced into "admin" mode here. On POST, the controller first checks the session-stored CAPTCHA answer *before* even attempting `$model->login()`:
  ```php
  // SiteController.php:205-213
  $session_captcha = Yii::$app->session['captcha'];
  if ($session_captcha['capture_value_result'] && time() > $session_captcha['capture_value_result_exp']) {
      $model->addError('captcha', 'Please refresh page and try again');
      return $this->render('login', ['model' => $model, 'captcha' => $this->captureValue()]);
  }
  if ($session_captcha['capture_value_result'] != $model->captcha) {
      $model->addError('captcha', 'Wrong result.');
      return $this->render('login', ['model' => $model, 'captcha' => $this->captureValue()]);
  }
  ```
  **Bug found by reading this exact condition:** the expiry check is gated by `$session_captcha['capture_value_result'] &&` — a **truthy check on the correct answer itself**, not just its existence. `captureValue()` (`SiteController.php:151-185`) can legitimately generate an equation whose correct result is `0` (e.g. `5 - 5`), and PHP's `0` is falsy — so on any captcha whose answer happens to be zero, the `&&` short-circuits and the expiry check is silently skipped entirely, regardless of how stale the session value is. A narrow but real logic bug in the expiry enforcement.
  If the CAPTCHA passes, `LoginForm::login()` (`common/models/LoginForm.php:79-91`) validates credentials via `validatePassword()` (compares `Yii::$app->security->validatePassword()` against `password_hash`, then checks `$user->user_type != $this->user_type` — this is the **one-time** admin/candidate distinction `middleware_inventory.md` line 322 references), then does something with a real reliability cost: `getLoginAddress($_SERVER['REMOTE_ADDR'])` (`LoginForm.php:94-104`) makes a **synchronous, unauthenticated, plain-HTTP (not HTTPS) blocking `file_get_contents()` call to a third-party geo-IP lookup service (`http://ipinfo.io/{ip}`), on every single successful login**, with errors suppressed via `@` and no timeout configured. If `ipinfo.io` is slow or unreachable, every admin login hangs on this call; it also silently sends every admin's real IP to a third party as a side effect of logging in.
- **User Actions Available:** Username text input (autofocus), password input (`passwordInput()`, correctly masked), a read-only equation string shown in plain text (`implode('', $captcha)`, e.g. `"5+3"`) with a single "Answer" text input bound to `captcha`, "Login" submit button. `rememberMe` is a `LoginForm` property defaulting to `true` (`LoginForm.php:15`) but **has no corresponding form field in `login.php`** — every successful login is therefore unconditionally "remember me" for 30 days (`3600 * 24 * 30`, `LoginForm.php:87`), with no way for the admin to opt out.
- **View Partials/Includes:** None.
- **JS files:** None page-specific (relies on the `blank` layout's `AppAsset` — stock Bootstrap/jQuery only, no admin-theme JS).
- **AJAX endpoints:** None — standard form POST.
- **Modals:** None.

### Page: Admin Dashboard
- **URL / Route:** `site/index` — `backend/controllers/SiteController.php::actionIndex()`. **This is the live default route** for the entire backend app: `backend/config/main.php:16`'s `'defaultRoute' => 'user/index'` override is commented out, so Yii's own default (`site/index`) governs — confirmed by `route_inventory.md:25`.
- **Portal:** Admin
- **Permission Required:** app-level `'as access'` (any authenticated user) + `SiteController`'s own `AccessControl` (`actions => ['logout', 'index'], roles => ['@']`).
- **View File:** `backend/views/site/index.php`
- **Layout Used:** `admin`
- **Purpose:** Post-login landing page with two live ApexCharts (roll-number generation over time, eligibility-check submissions over time).
- **Detailed Description:** Unlike the officer app's dashboard (a static placeholder with all real chart wiring commented out), **this dashboard does render real, live charts** — but with a confirmed dead-vs-live split worth documenting precisely, since `controller_inventory.md:448` only summarizes it as *"a large commented-out legacy chart-query block"*:
  - **Dead branch (chart 1, "No of Candidates Roll No Generating Date Wise"):** `SiteController.php:72-74` has a commented-out `Sailors::find()` query. `SiteController.php:77-79` then runs a **live, uncommented** `DeSailors::find()` query (`batch_id = 2`) and assigns it to `$sailor` — but the entire block that would translate `$sailor` into `$chart_data['have_value'] = 'yes'` (`SiteController.php:87-97`) is *also* commented out. Net effect: `$chart_data['have_value']` is hard-initialized to `'no'` (line 82) and **can never become `'yes'`** — yet the query itself still runs and its result is silently discarded on every dashboard load. `backend/views/site/index.php:60-67` correctly guards the `#sessions-overview` container `<div>` behind `if ($chart_data['have_value'] == 'yes')`, so that div is never rendered — but the inline `<script>` block below it (`index.php:87-130`) is **not** guarded by the same condition, and unconditionally calls `document.querySelector("#sessions-overview")` followed by `new ApexCharts(...)` / `chart.render()` against it. Since the container div is provably never in the DOM, this JS runs against a `null` element on every dashboard page load.
  - **Live branch (chart 2, "Eligibility Check Date Wise"):** `SiteController.php:100-118` runs a **second, identical** `DeSailors::find()` query (same `batch_id = 2`, same select/group/order as the dead `$sailor` query three lines above it) into `$desailor`, and this time the population block that follows *is* live — `$chart_data_3` is correctly built and rendered into `#sessions-overview_2`. This is a duplicated, redundant DB query (both `$sailor` and `$desailor` fetch the exact same rows) where only the second copy's result is ever used.
  - The dashboard's headline `CanEligibilityCheckInfo` chart is a **raw SQL string** (`SiteController.php:134`):
    ```php
    $s_sql = "SELECT COUNT(id) as total, DATE_FORMAT(created_dt, '%Y-%m-%d') as created_date FROM jnavy_can_eligibility_check_info WHERE DATE_FORMAT(created_dt, '%Y-%m-%d') > '2026-05-02' GROUP BY created_date";
    ```
    This is the exact "hardcoded date-filter finding" `controller_inventory.md:448` flags — the lower bound `'2026-05-02'` is a literal string, not a rolling/relative date, so this chart will silently stop showing new data as its own history grows past whatever range someone intended when this line was written, and needs manual periodic updating in the source file itself (no admin UI touches this value — see §3 below). A separate, fully commented-out earlier attempt at the same query survives at `SiteController.php:120-127` (dead exploration code).
  - `$total_generate_roll` (line 12 of the view, `"Total Complete Application with Roll No"`) is accumulated **only** inside the live `$desailor` loop (`SiteController.php:111`, `$total_generate_roll += $va['total'];`) — since the `$sailor`/`Sailors` branch is fully dead, this headline number reflects DE-Sailor roll generation only, despite the page having no label distinguishing that from regular Sailor rolls.
- **User Actions Available:** None functional — no form, no filters. (The officer-app-style commented-out date-range input exists here too, at `index.php:13-28`, fully HTML-commented.)
- **JS files:** Two inline `new ApexCharts(...)` blocks (`index.php:87-179`), using the globally-loaded `apexcharts.min.js` from `AppAdminAsset`. `demo.dashboard-analytics.js` — the theme's own dashboard demo script, explicitly commented out of `AppAdminAsset::$js` (`javascript_inventory.md:116`) — is **not needed** here since this page hand-rolls its own ApexCharts calls instead of relying on that file, unlike what the officer app's equivalent dead-code note might suggest.
- **AJAX endpoints:** None.
- **Modals:** None.

### Page: Logout
- **URL / Route:** `site/logout` (POST only, `VerbFilter`) — `backend/controllers/SiteController.php::actionLogout()`
- **Permission Required:** app-level `'as access'` + `SiteController`'s own `AccessControl` (`actions => ['logout', 'index'], roles => ['@']`).
- **View File:** None — no render, redirect only.
- **Detailed Description:** `Yii::$app->user->logout(); return $this->goHome();` (`SiteController.php:232-237`) — two lines, no session-row bookkeeping (contrast with the officer app's `UserLoginDetails` status flip and its associated null-pointer risk; there is no equivalent per-device login-tracking table in this app at all).
- **Triggered from:** `top_bar.php:58-63`, a POST `<form>` (not a plain link — required since CSRF is globally disabled but the route is still `VerbFilter`-restricted to POST).

---

## 2. Page Inventory — Users

### Page: User List
- **URL / Route:** `user/index` — `backend/controllers/UserController.php::actionIndex()`
- **Permission Required:** app-level `'as access'` only — `UserController` defines no `AccessControl` of its own (`controller_inventory.md:483`); see §0 for why this is still gated. **Sidebar entry-point is unrestricted** (§0) — every authenticated admin, not just a hardcoded allowlist, sees and can click "User".
- **View File:** `backend/views/user/index.php`
- **Layout Used:** `admin`
- **Controller / Method:** `UserController::actionIndex()` → `UserSearch::search()` (`backend/models/UserSearch.php`)
- **Purpose:** Search/browse all `User` records — both `admin` and `candidate` user types share this one table/list, same pattern as the officer app.
- **Detailed Description:** `UserSearch::search()` returns an `ActiveDataProvider` with `pageSize => 1000` (no default filter narrowing to admins only — a candidate-management admin browsing this screen sees every candidate account too, mixed in with real admin accounts). The grid's `phone_no` column is explicitly decrypted for display via `DataEncryption::dataDecrypt($data->phone_no)` (`index.php:96-99`), confirming `phone_no` is encrypted at rest (this app's encryption layer is `common\static\DataEncryption`, not AES-256-CTR-branded like the officer app, but functionally analogous). `email`/`username` are **not** decrypted in this grid because they are not encrypted at rest in this app at all — `common/models/User.php` has no `beforeSave`/`afterFind` encryption hooks for those fields.
  - **Security-relevant finding, confirmed by reading the exact `ActionColumn` button definition (`index.php:119-140`):** a `candidateLogin` action button, shown only when `$model->user_type == 'candidate'`, builds a raw impersonation URL that **embeds the account's bcrypt `password_hash` directly in the query string**:
    ```php
    // index.php:125-135
    'candidateLogin' => function ($url, $model) {
        if ($model->user_type == 'candidate') {
            $url = Yii::getAlias('@baseUrl') . '/candidate/auto-login?slug=' . StaticMethod::encryptPk($model->id) . '&encpas=' . $model->password_hash . '&uname=' . $model->username;
            return Html::a('Login', $url, ['title' => Yii::t('app', 'Candidate Login'), 'target' => '_blank', 'data-pjax' => '0']);
        } else return '';
    },
    ```
    `StaticMethod::encryptPk()` (`common/static/StaticMethod.php:486-501`) is a simple `mt_rand()`-seeded character-shift obfuscation, not real encryption. **Confirmed by grepping the entire `frontend/controllers/` directory (10 files) for `auto-login`/`autoLogin`/`encpas`: zero matches anywhere outside this one `href` string.** The target route `candidate/auto-login` does not exist — no such action is defined on `frontend\controllers\CandidateController` or any other controller. This "Login" button is dead/broken (clicking it 404s), but the `password_hash` value is still embedded in the rendered page's HTML `href` attribute on every load of this list for every candidate row — visible in page source, browser history, and any HTTP access/referrer logs, regardless of whether the link is ever clicked.
- **User Actions Available:** GridView with a Pjax-wrapped filter row (auto-generated per-column text filters from `UserSearch`'s `safe` attributes, plus explicit dropdown filters for `user_type` and `status`), pagination, per-row "Update" link, and the broken "Login" button described above for candidate rows. The "Add" button is present but fully HTML-commented (`index.php:42-43`) pointing at a `create` route that (see below) renders a missing view — same "commented-out Add button, no live Create UI" pattern the officer app has for its User List.
- **View Partials/Includes:** None. **AJAX endpoints:** Pjax grid refresh only (standard Yii2 GridView behavior, not a custom AJAX endpoint). **Modals:** None.

### Page: Create User — broken
- **URL / Route:** `user/create` — `UserController::actionCreate()`
- **Detailed Description:** `controller_inventory.md:34` confirms this directly: *"calls `$this->render('create', ...)` but `backend/views/user/create.php` does not exist (only `_form.php`, `index.php`, `view.php` are present in that directory)"*. Confirmed independently by `ls backend/views/user/` — only `_form.php`, `index.php`, `view.php`. Any admin who reaches this route (there is no live UI link to it — the only "Add" button is commented out, see above) hits a Yii2 `ViewNotFoundException` (HTTP 500-class error). This is a fully broken action, not merely unlinked.

### Page: View User
- **URL / Route:** `user/view` — `UserController::actionView($id)`
- **View File:** `backend/views/user/view.php`
- **Detailed Description:** `DetailView::widget()` (`view.php:29-52`) lists every `User` attribute, including **`password_hash` and `auth_key` in plain view on screen** (`view.php:38-39`) — any admin who opens a user's detail page sees that user's bcrypt hash and session auth key rendered as plain table cells. `phone_no` is listed **without** the `DataEncryption::dataDecrypt()` call the index grid applies (`view.php:37` vs. `index.php:96-99`) — an inconsistency: the same field displays decrypted on the list page and as raw ciphertext on the detail page.
  A "Delete" button is rendered (`view.php:20-26`, `Html::a('Delete', ['delete', ...], ['data' => ['confirm' => ..., 'method' => 'post']])`) pointing at `user/delete` — but `UserController::actionDelete($id)` is **fully commented out** (`UserController.php:120-125`; `controller_inventory.md:51` — *"`actionDelete($id)` (~line 120) is fully commented out — dead/unreachable. Admin users cannot be deleted via this controller"*). Clicking this live, rendered "Delete" button on every single user's detail page hits a route Yii2 cannot resolve to any action — a 400/404-class failure, not a working delete.

### Page: Edit User
- **URL / Route:** `user/update` (GET renders form, POST saves) — `UserController::actionUpdate($id)`
- **View File:** `backend/views/user/_form.php` (rendered directly by the controller — no separate `update.php`, same "controller renders `_form` directly" convention `component_inventory.md` §4 documents for 12 of the 13 backend CRUD modules).
- **Purpose:** Edit an existing user's group/type, contact info, status, and — via a single field literally named and labeled **"Password Hash"** — their password.
- **Detailed Description:** This is the password re-hash logic the task scope calls out, and it has a confirmed, non-obvious usability bug worth documenting in full. The controller:
  ```php
  // UserController.php:93-111
  public function actionUpdate($id)
  {
      $model = $this->findModel($id);
      if ($this->request->isPost && $model->load($this->request->post())) {
          if ($model->password_hash) {
              $model->password_hash = Yii::$app->security->generatePasswordHash($model->password_hash);
          }
          if ($model->validate() && $model->save())
              return $this->redirect(['view', 'id' => $model->id]);
      }
      $model->password_hash = '';
      return $this->render('_form', ['model' => $model]);
  }
  ```
  and the view (`_form.php:48`) binds this same field with a plain, unmasked `textInput()` — `$form->field($model, 'password_hash')->textInput(['maxlength' => true])` — labeled "Password Hash" (from `User::attributeLabels()`, `common/models/User.php:98`, since there is no override in this view). There is no separate `password`/`confirm password` pair, no confirm-password validation, and no length rule on it at all — `User::rules()` (`common/models/User.php:70`) only declares `[['username', 'email', 'auth_key', 'password_hash', 'created_at', 'updated_at'], 'required']` and `[[..., 'password_hash', ...], 'string', 'max' => 255]`.
  **The bug:** `$model->password_hash = '';` (line 107) runs unconditionally at the bottom of the method — on the initial GET *and* after a failed POST — so the form field is always blank when shown, which correctly prevents the real stored hash from ever being echoed back into the page (matches `controller_inventory.md:491`'s note). But because `password_hash` is marked `required` in `rules()`, and the controller never excludes it from validation when left blank, **submitting the edit form with the "Password Hash" field empty always fails validation** — there is no way to update a user's username, status, or type without also typing something new into the password field on every single save. Whatever is typed there — with no length/complexity constraint — becomes the new password (rehashed via `generatePasswordHash()` only `if ($model->password_hash)`, i.e. only on the POST path, so the raw input briefly occupies the `password_hash` attribute before being overwritten by its own hash).
  `UserSearch::rules()` (`backend/models/UserSearch.php:21`) additionally marks `password_hash` `safe`, meaning the grid's auto-filter row (§ User List above) accepts free-text search against the hash column too — not exploitable (search is a `LIKE` against a bcrypt string, effectively useless) but confirms no field was special-cased anywhere in this controller/search pair.
- **User Actions Available:** `user_group` dropdown (`super_admin`/`admin`/`register`, `_form.php:38`), `user_type` dropdown (`admin`/`candidate`, line 40), Username, Email, Phone No text inputs, the "Password Hash" text input described above, Status dropdown (`StaticMethod::statusDropDown()`), "Save" submit button.
- **JS files / AJAX / Modals:** None page-specific.

---

## 3. "Global Settings" — this screen does not exist in this app

Unlike the officer repo, **there is no `GlobalSetting` model, table, controller, or admin screen anywhere in this codebase** — confirmed by `grep -rln "GlobalSetting" --include="*.php" .` across the entire repo (zero hits outside this document). There is no singleton settings row, no admin-editable IP allowlist, no SMS on/off toggles, no admin-managed circular/notice visibility window.

The closest functional equivalents are two plain PHP config files, neither editable through any admin UI — an admin who wants to change any of these values needs a code deploy, not a form submission:

- **`common/config/params.php`** — application-wide static params (`Yii::$app->params[...]`):
  ```php
  return [
      'adminEmail' => 'admin@example.com',
      'supportEmail' => 'support@example.com',
      'senderEmail' => 'noreply@example.com',
      'senderName' => 'Example.com mailer',
      'user.passwordResetTokenExpire' => 3600,
      'user.passwordMinLength' => 8,
  ];
  ```
  (Note `user.passwordMinLength => 8` here is itself never actually enforced by `User::rules()` or `UserController::actionUpdate()` — see §2's Edit User finding; the admin-edit password field has no length rule at all, so this configured minimum only governs some other, out-of-scope password path, if any.)
- **`common/config/main.php`** — component-level settings, including the `r2Storage` component's credentials/bucket/`verifySsl` flag (§0) that stands in for what the officer app exposes as admin-editable IP/SMS toggles here — all hardcoded, not admin-editable.

There is no IP-allowlist or SMS-toggle mechanism of any kind in this app (confirmed by grepping the whole repo for `ip_white_list`/`check_ip`/`send_sms`/`white_list`/`whitelist` outside `vendor/` — the only hits are the unrelated `common/models/SendSms.php` table/model name). The officer app's Global-Setting-driven IP-restriction feature (§0 of the reference doc) has **no counterpart at all** here — this app's login has no IP-based access control path to configure in the first place.

---

## 4. Page Inventory — Site Activity Log (closest equivalent to "Login Log")

**Important framing, stated explicitly per the task scope:** `LogReportController::actionSiteActivity` is **not** a login log. It is a generic, whole-application audit-log viewer that reads back the app-level `'on beforeRequest'` action logger (§0) — every controller/action in the backend, not specifically logins. In fact its one login-specific behavior is the opposite of a "login log": it deliberately **excludes** `site/login` entries from the results (`LogReportController.php:45`, `$route != 'site/login'`). It is the audit-trail feature this app happens to have, but "Login Log" would be a misleading name for it.

### Page: Site Activity Log
- **URL / Route:** `log-report/site-activity` (GET renders, POST filters) — `LogReportController::actionSiteActivity()`
- **Permission Required:** app-level `'as access'` only — `LogReportController` defines no `behaviors()` at all (`controller_inventory.md:328`). **Not linked from the sidebar anywhere** (§0) — reachable only by direct URL.
- **View File:** `backend/views/log-report/report.php`
- **Layout Used:** `admin`
- **Purpose:** Search generic per-controller action-log NDJSON files (written by the global request logger) by controller + method, view a flat table of grouped update events, and drill into one event group's full field-level diff via a modal.
- **Detailed Description:** The filter form is a `DynamicModel(['data', 'method', 'controller'])` with `addRule(['method', 'controller'], 'required')` (`LogReportController.php:16-17`) — `data` (a date, despite the field name — the corresponding `DatePicker` widget UI for it is fully commented out in the view, see below) is **not** required. On POST, the branch actually reachable through the UI is `method == 'update'` (the only non-commented `dropDownList` option, see below), which builds `logFile = 'action_log/'.$model->controller.'/update.ndjson'` and pulls it via `Yii::$app->r2Storage->getLogFileContents()`, then groups entries by `update_id`, excluding `site/login`.
  **Two confirmed, distinct coverage gaps between what the global logger writes and what this one screen can show**, found by cross-referencing this controller against `backend/config/main.php`'s logger and `route_inventory.md`'s confirmed controller list:
  1. **The `controller` dropdown is a hand-maintained allowlist of 12 controllers, and it is out of sync with the real controller IDs the logger actually writes under.** `LogReportController.php:52-67`:
     ```php
     $controller_list = [
         'sailors' => 'Sailor', 'de-sailors' => 'DE-Sailor', 'sailor-batchs' => 'Sailor Batch',
         'sailor-batch-configuration' => 'Sailor Batch Configuration', 'districts' => 'District',
         'upozilas' => 'Upozila', 'unions' => 'Union', 'subjects' => 'Subject',
         'can-designations' => 'CAN Designation', 'eligibility' => 'Eligibility',
         'sailor-centers' => 'Sailor Center', 'sailor-cent-dist-mapping' => 'Sailor Center District Mapping',
         'report' => 'Report',
     ];
     ```
     `'can-designations'` (plural) is the dropdown's key for "CAN Designation" — but the real controller is `CanDesignationController`, whose actual Yii2 controller ID (and therefore the actual `action_log/<id>/...` path the global logger writes to, per `$controller_id = strtolower($event->action->controller->id)`) is `can-designation` (**singular** — confirmed in `route_inventory.md:104`, `can-designation/index → actionIndex()`, etc.). Selecting "CAN Designation" in this filter queries `action_log/can-designations/update.ndjson`, a key the logger never writes to (it writes `action_log/can-designation/update.ndjson`), so this one option **always returns zero results**, silently, with no error — a genuine bug, not just a coverage gap, and one not previously flagged in `controller_inventory.md`.
  2. **`UserController` — the controller this very document centers on — is not in the list at all**, nor is `SiteController`, `AjaxController`, `DeSailorReportController`, `ReportController`'s sibling `LogReportController` itself, or the two stub controllers. The global logger writes `action_log/user/update.ndjson`/`add.ndjson` (and dated GET logs) for every visit to the User pages documented in §2 — including every password-hash change — but there is **no dropdown option to select `user` as the controller**, so none of that activity can ever be surfaced through this screen. Reviewing "who changed which admin's password and when" — arguably the single most security-relevant use case this audit screen could serve — is not possible through its own UI.
  3. **The `method` dropdown offers only `UPDATE`** (`report.php:81-86`; `INSERT`, `GET`, `DELETE` options are present in the source but commented out) and the **date filter (`data` field) UI is entirely commented out** (`report.php:60-74`, a `DatePicker` widget block). Since the logger's *other* two write paths — `add.ndjson` (POST, non-update) and the dated `{Y-m-d}_{method}.ndjson` files (every GET/page-view) — are only reachable by posting `method=insert` or an arbitrary non-`update` string plus a `data` date, and the view provides no UI control to produce either, **the majority of what the global logger actually records (every plain page view, across every controller, forever) is structurally unreachable through this screen's own form.** Only `update.ndjson` files, for the 12 (13, minus the `can-designations` bug) listed controllers, are ever practically viewable.
- **User Actions Available:** Controller dropdown (13 options, one effectively broken per above), Method dropdown (locked to the single option "UPDATE"), "Submit" button, a results table (SL / Action(route) / Update ID / User / IP / Time / Method / "View" button per grouped update-event), and a Bootstrap modal + `fetch()`-driven detail view (see §6).
- **View Partials/Includes:** None. **JS files:** One inline `registerJs()` block (`report.php:207-234`) wiring the modal's fetch call — see §6.

### Page: Site Activity Log — Detail Modal (AJAX)
- **URL / Route:** `log-report/site-activity-view` (GET, `X-Requested-With: XMLHttpRequest`) — `LogReportController::actionSiteActivityView($date, $route, $method, $controller, $update_id)`
- **Detailed Description:** Re-runs the same NDJSON fetch/filter (independent of `actionSiteActivity`, not shared logic), then hand-builds an HTML `<table>` via `ob_start()`/`echo` (no view file — `controller_inventory.md:334` confirms this pattern) showing, per entry in the matched `update_id` group: timestamp, user, IP, the raw submitted `params` JSON (`<pre class="pre-json">`), and a field-level diff against the *next* chronological entry in the same group via `collectChanges()` (`LogReportController.php:222-264`).
  **Bug found by reading `collectChanges()` closely:** every branch that records a change deliberately swaps the `old`/`new` labels relative to its own inline comments:
  ```php
  // LogReportController.php:240-242
  if ($old[$key] !== $new[$key]) {
      // $changes[$subPath] = ['old' => $old[$key], 'new' => $new[$key]];   <- commented-out "correct" version
      $changes[$subPath] = ['new' => $old[$key], 'old' => $new[$key]];       <- live version, labels swapped
  }
  ```
  The same inversion repeats for the added-key, removed-key, and non-array-value branches (lines 248, 252, 261) — each has a commented-out line with `old`/`new` in the semantically correct order directly above the live line that swaps them. Whoever wrote this flipped the labels at some point and left the original as a comment rather than reverting — the modal's "Changed" column therefore shows every diff backwards: the value logged as `"new"` is actually the *older* of the two entries being compared, and vice versa.
- **User Actions Available:** None — read-only fetch-and-render into the modal body opened from the parent page's "View" button.
- **JS files (quoted, `report.php:209-233`):**
  ```js
  const params = new URLSearchParams({date: date, route: route, method: method, controller: controller, update_id: update_id});
  fetch('${detailUrl}?'+params.toString(), {headers: {'X-Requested-With':'XMLHttpRequest'}})
      .then(r => r.text())
      .then(html => { modalBody.innerHTML = html; })
      .catch(() => { modalBody.innerHTML = '<div class="text-danger">Failed to load details.</div>'; });
  ```
  Note the `controller` value passed through here is whatever the parent filter form submitted — so the `can-designations`/`can-designation` slug mismatch described above affects this modal's fetch identically.

---

## 5. Form Documentation

### Form 1 — Admin Login
- **Page:** Admin Login (`backend/views/site/login.php`)
- **Action URL:** `site/login` (self-POST)
- **Controller@Method:** `SiteController::actionLogin`
- **Validation source:** `LoginForm::rules()` (`common/models/LoginForm.php:27-38`), quoted in full:
  ```php
  return [
      ['username', 'trim'],
      [['username', 'password', 'captcha'], 'required'],
      ['rememberMe', 'boolean'],
      ['password', 'validatePassword'],
  ];
  ```

| Field | Label | Input Type | Required | Default | Validation | Dependencies |
|---|---|---|---|---|---|---|
| `username` | Username | `text`, autofocus | Required | none | `required`, trimmed | none |
| `password` | Password | `password` (masked) | Required | empty | `required`; custom `validatePassword()` compares against `Yii::$app->security->validatePassword()` **and** checks `user_type == 'admin'` in the same inline validator | Depends on the controller pre-setting `$model->user_type = 'admin'` before validation (`SiteController.php:203`) |
| `captcha` | "Answer" (label overridden, no field label shown, `_form`-style placeholder) | `text` | Required | none | `required`; correctness/expiry checked **imperatively in the controller**, not as a Yii model rule (see §1 "Admin Login" for the falsy-zero expiry bug) | Session-stored `capture_value_result`/`capture_value_result_exp`, regenerated on every failed attempt |

- **Conditional behavior:** None client-side. `rememberMe` has no UI control at all — always `true` by class default (§1).

---

### Form 2 — Edit User
- **Page:** Edit User (`backend/views/user/_form.php`)
- **Action URL:** `user/update`
- **Controller@Method:** `UserController::actionUpdate`
- **Validation source:** `User::rules()` (`common/models/User.php:63-82`), the relevant lines quoted:
  ```php
  [['username', 'email', 'auth_key', 'password_hash', 'created_at', 'updated_at'], 'required'],
  [['username', 'email', 'password_hash', 'password_reset_token', 'verification_token', 'login_zone'], 'string', 'max' => 255],
  [['username'], 'unique'],
  ```

| Field | Label | Input Type | Required/Optional | Default | Validation | Dependencies |
|---|---|---|---|---|---|---|
| `user_group` | User Group | `select` (`super_admin`/`admin`/`register`) | Not explicitly required by `rules()` (only `[['user_group','user_type'],'string']`) but no empty-string handling in the dropdown beyond an empty `prompt` | `old` value via model binding | `string` only | none |
| `user_type` | User Type | `select` (`admin`/`candidate`) | Same as above — `string` rule only | model value | `string` only | Drives §2's `candidateLogin` broken-URL button on the User List when `= candidate` |
| `username` | Username | `text` | Required | model value | `required`, `string max:255`, `unique` (excludes current record automatically, standard Yii2 ActiveRecord behavior) | none |
| `email` | Email | `text` | Required (per rules, though not enforced with a format check — no `email` validator applied, just `required` from the shared attribute-group rule) | model value | `required` (bundled into the same `required` rule as `username`/`auth_key`/etc.) | none |
| `phone_no` | Phone No | `text` | Optional (`safe`) | model value (raw ciphertext — **not** decrypted for display in this form, unlike the index grid) | none | none |
| `password_hash` | **"Password Hash"** (verbatim label, no override) | `text` (**not** `type="password"` — plaintext-visible on screen while typing) | **Required by model rules, but always blanked before every render** — see §2's full write-up of the resulting "must retype a password on every save" bug | Always empty string, both on GET and after a failed POST | `required`, `string max:255` — **no minimum length, no complexity rule, no confirm-password field at all** | None — contrast directly with the officer app's Form 3, which at least made the password field *optional* on edit |
| `status` | Status | `select` (`StaticMethod::statusDropDown()`) | Not required by `rules()` beyond `[['status'],'integer']` plus a `default` rule (`Status::INACTIVE`) | model value | `integer` | none |

- **Save-time behavior:** No AES/DataEncryption re-encryption happens for any field on save in this controller (unlike the officer app's `phone_no`/`email` re-encrypt-on-save pattern) — this app's `User` model stores `phone_no` encrypted only via whatever wrote it originally (candidate registration flow, out of scope here), and `UserController::actionUpdate` passes it straight through unmodified via `$model->load()`.

---

### Form 3 — Site Activity Log filter
- **Page:** Site Activity Log (`backend/views/log-report/report.php`)
- **Action URL:** `log-report/site-activity` (self-POST)
- **Controller@Method:** `LogReportController::actionSiteActivity`
- **Validation source:** `DynamicModel(['data', 'method', 'controller'])` with `addRule(['method', 'controller'], 'required')` (`LogReportController.php:16-17`) — no FormRequest/dedicated Model class, Yii2's `DynamicModel` is the direct equivalent of an inline Laravel `Validator::make()` call with no backing Eloquent-style model.

| Field | Label | Input Type | Required | Datasource | Dependencies |
|---|---|---|---|---|---|
| `controller` | (dropdown label from field name) | `select` | Required | Hardcoded 13-entry `$controller_list` array (§4) — **one entry (`can-designations`) has a confirmed slug mismatch against the real logged controller ID, always returning zero rows** | Determines which `action_log/<controller>/...ndjson` key is fetched |
| `method` | (dropdown label from field name) | `select`, but only one live option | Required | `['UPDATE' => 'UPDATE']` — `INSERT`/`GET`/`DELETE` options exist in source but are commented out | Only the `update.ndjson` read path is reachable via this form (§4) |
| `data` | (unused in the reachable UI path) | Would be a `DatePicker` widget, but that entire form-field block is HTML-commented out (`report.php:60-74`) | Not required by the `DynamicModel` rule | n/a | Only consumed by the `method != insert/update` branch, which nothing in this UI can trigger |

- **Conditional behavior:** None client-side beyond the modal-opening `fetch()` described in §4/§6.

---

## 6. Modal / AJAX Audit

Unlike the officer app's equivalent scope (Users / Global Setting / Login Log), which had **zero** modals or AJAX calls across all of it, this app's scope has exactly **one** live modal + AJAX pair, both confined to the Site Activity Log screen:

- **Modal:** `#logModal` (`report.php:190-205`), a standard Bootstrap 5 modal (`modal-xl modal-dialog-scrollable`), opened via `new bootstrap.Modal(modalEl)` + `.show()` from a delegated `document.addEventListener('click', ...)` handler matching `.btn-view` (`report.php:210-232`).
- **AJAX:** A native `fetch()` call (not jQuery `.ajax()`, despite jQuery being loaded twice globally per §0) to `log-report/site-activity-view`, passing `date`/`route`/`method`/`controller`/`update_id` as query-string params, with the response HTML injected directly into `modalBody.innerHTML` — no client-side sanitization of the returned markup (the server-side response does `htmlspecialchars()` its own dynamic cell values, `LogReportController.php:196-200`, so this is not an obviously-exploitable XSS path, but the client trusts the fetched HTML wholesale regardless).
- Every other page in this document's scope (Login, Dashboard, Logout, User List/View/Edit) is a plain server-rendered page navigation via standard `<form>` POST/GET or `<a href>` link — no modals, no AJAX, confirmed by the same `grep -rn "modal\|\.ajax(\|fetch("` sweep style the officer app's audit used, restricted here to `backend/views/user/`, `backend/views/site/`, `backend/views/log-report/`, `backend/views/layouts/`.

---

## 7. Cross-cutting observations (evidence-backed, non-code-changing notes)

1. **No `GlobalSetting` equivalent exists at all** — confirmed by a whole-repo grep for the model name (zero hits). The closest analogues are `common/config/params.php` (mailer/reset-token params) and `common/config/main.php` (component credentials, incl. `r2Storage.verifySsl`), both hardcoded PHP, neither admin-editable through any UI. See §3.
2. **`UserController`/`LogReportController` have no `AccessControl` of their own, but both are still fully gated** by the application-level `'as access'` behavior in `backend/config/main.php:132-144` — a real Laravel-middleware-group equivalent that `controller_inventory.md`'s per-controller-only view doesn't surface on its own. The gap that *is* real: that gate checks `roles => ['@']` (any authenticated user) only, never re-verifying `user_type == 'admin'` per request — that check happens exactly once, at login. Confirmed and already documented in `middleware_inventory.md` §1b.
3. **CSRF validation is disabled app-wide on the backend** (`backend/config/main.php:21`) — every form in this scope, including the login form and the password-changing Edit User form, submits without CSRF protection.
4. **The "User" sidebar link has no access restriction of any kind** (`left_side_menu.php:177-179`) — a meaningful contrast with the officer app's hardcoded `Auth::id() in [1,2,3]` UI gate on the same feature area. The only ID-hardcoded link in this sidebar guards an unrelated report (`report/json-for-ls`, restricted to user ID `1`).
5. **No self-service "Change Password" screen exists anywhere** — the only way to change any admin's password, including one's own, is `UserController::actionUpdate`, and that form requires re-typing a new password on *every* save regardless of intent (finding #6).
6. **Edit User's password field is misconfigured such that it's impossible to save any other change without also setting a new password.** `password_hash` is `required` in `User::rules()` and is unconditionally blanked before every render (`UserController.php:107`) — so leaving it empty on submit always fails validation. There is also no minimum length, complexity rule, or confirm-password field. Full write-up: §2 "Edit User".
7. **The `password_hash` value is embedded directly in an HTML `href` attribute** on the User List page's broken `candidateLogin` button (`backend/views/user/index.php:127`) — visible in page source for every candidate row on every list load, regardless of whether the (nonexistent, dead) target route is ever hit. Full write-up: §2 "User List".
8. **`user/view` displays `password_hash` and `auth_key` in plain DetailView cells** (`backend/views/user/view.php:38-39`) to any admin who opens any user's detail page.
9. **`user/view`'s "Delete" button targets a fully commented-out `actionDelete()`** (`UserController.php:120-125`) — a live, rendered button on every user's detail page that cannot successfully complete its action.
10. **`user/create` is broken** — renders a view file (`user/create.php`) that does not exist on disk. Not linked from any live UI (the "Add" button is commented out), but directly reachable by URL and always errors. Confirmed in `controller_inventory.md:34`.
11. **Site Activity Log (`log-report/site-activity`) is completely unlinked from any admin navigation** — reachable only by an admin who already knows the URL. Confirmed via repo-wide grep.
12. **Site Activity Log's `controller` filter dropdown has a slug mismatch for "CAN Designation"** (`can-designations` in the dropdown vs. the real `can-designation` controller ID the logger writes under) — that one filter option always returns zero results, silently. Not previously flagged in the existing inventories; found by cross-referencing `LogReportController.php`'s hardcoded list against `route_inventory.md`'s confirmed controller IDs.
13. **`UserController` (this very document's own subject) is absent from the Site Activity Log's controller list**, along with `SiteController`, `AjaxController`, and several others — meaning admin-account changes, including password re-hashes, cannot be reviewed through this app's one audit-log screen even though the underlying global logger records them.
14. **The Site Activity Log UI can only ever query `update.ndjson` files** — the `method` dropdown is hardcoded to a single live option (`UPDATE`), and the `date`-driven UI for the logger's other write paths (`add.ndjson`, dated GET logs — i.e. every plain page view across the whole admin app) is fully commented out of the view. The bulk of what the global logger actually records is unreachable through this screen's own form.
15. **`LogReportController::collectChanges()`'s diff output has its `old`/`new` labels inverted** relative to its own commented-out "correct" version left directly above each live line (`LogReportController.php:240-261`) — every field-level change shown in the Site Activity Log's detail modal is mislabeled backwards.
16. **The global action-log writer (`R2Storage::actionLog()`) does a full download-decode-reupload of the entire NDJSON object on every single backend request**, with no locking — a scalability and race-condition risk baked into the mechanism that both feeds and is the subject of the Site Activity Log screen. TLS verification is also disabled for all R2/Cloudflare traffic (`common/config/main.php:26`, `'verifySsl' => false`).
17. **Every admin login makes a blocking, unauthenticated, plain-HTTP third-party network call** (`http://ipinfo.io/{ip}` via `LoginForm::getLoginAddress()`) with no timeout and errors suppressed — a reliability and privacy-relevant dependency on every successful login.
18. **The login CAPTCHA's expiry check has a falsy-zero bug**: `$session_captcha['capture_value_result'] && time() > ...` skips the expiry check entirely whenever the correct answer happens to be `0` (e.g. `5 - 5`). `SiteController.php:206`.
19. **`rememberMe` defaults to `true` with no UI control to change it** — every admin login is a 30-day persistent session by default, unconditionally.
20. **jQuery loads twice on every admin page** (`vendor.min.js`'s bundled copy + Yii2's own `JqueryAsset` via `AppAdminAsset`'s `$depends`) — confirmed in `javascript_inventory.md:69`.
21. **The dashboard's first ApexCharts container div is permanently unrendered** (`$chart_data['have_value']` can never become `'yes'` — its only setter block is commented out) while the inline script that mounts a chart into it runs unconditionally on every dashboard load, targeting a `null` element. A duplicate, redundant `DeSailors::find()` query (identical to the one whose result is discarded) also runs immediately after it to feed the *second*, live chart. Full write-up: §1 "Admin Dashboard".
