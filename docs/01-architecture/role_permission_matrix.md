# Role & Permission Matrix — join-navy-sailor-legacy

Analysis only — no application code was modified while producing this file. Structure/tone follows the sibling Laravel doc, `../../../join-navy-officer-legacy/docs/01-architecture/role_permission_matrix.md`; findings are cited from this repo's own Phase 0 inventories (`docs/00-inventories/controller_inventory.md`, `docs/00-inventories/middleware_inventory.md`, `docs/00-inventories/model_inventory.md`) and the Phase 1 `docs/01-architecture/portal_map.md`, not re-derived from scratch.

## 0. Authorization mechanism (no package, home-grown)

`composer.json` `require`/`require-dev` list only framework and tooling packages — `yiisoft/yii2`, `yiisoft/yii2-bootstrap5`, `yiisoft/yii2-symfonymailer`, `skeeks/yii2-slug-behavior`, `yiisoft/yii2-jui`, `kartik-v/yii2-widget-select2`, `2amigos/yii2-tinymce-widget`, `yiisoft/yii2-debug`, `yiisoft/yii2-gii`, `yiisoft/yii2-faker`, `codeception/module-yii2`. There is no `yii2-authclient`, no RBAC (`yii\rbac\*` / `authManager`) component configured in any of `common/config/main.php`, `frontend/config/main.php`, or `backend/config/main.php`, and no `spatie`-style permission package (this is a Yii2 app, not Laravel, so that specific package doesn't apply, but the absence of *any* ACL/RBAC package is the same shape of finding as the officer repo).

`common/models/User.php` implements `yii\web\IdentityInterface` (`getId()`, `getAuthKey()`, `validateAuthKey()`) — the minimum Yii2 requires for session-based login — and nothing else role-related. No `getAuthAssignments()`, no `can()`/`checkAccess()` override, no relation to an `auth_item`/`auth_assignment` table. The only role-shaped data on the model is two plain string columns, `user_type` and `user_group` (`common/models/User.php:15-16`, validated at line 69 only as `[['user_group', 'user_type'], 'string']` — no enum, no constants list defined anywhere for either column, confirmed by `model_inventory.md` L647/L661/L665).

Authorization is entirely home-grown, built from three ingredients — the Yii2 equivalents of the officer app's Laravel middleware, per `middleware_inventory.md` §0:

1. **Physical app separation** — `frontend/` and `backend/` are two independent `yii\web\Application` instances with separate entry scripts, session cookie names (`join_bd_navy_front` vs `join_navy-backend`) and identity cookie names (`_join_bd_navy_front` vs `_join_navy-backend`), sharing only the one `common\models\User` table (`middleware_inventory.md` §2, `portal_map.md` "Foundational fact"). This is the load-bearing boundary — not a role check.
2. **`user_type` string column**, checked exactly once, at login time, inside `common\models\LoginForm::validatePassword()` (`common/models/LoginForm.php`, quoted in full below, §1).
3. **`yii\filters\AccessControl`**, used sparingly — an app-level instance in `backend/config/main.php` (live on every backend controller), plus controller-level instances on exactly 5 of the 31 controllers (§4).

There is no `Gate::`-equivalent, no `Yii::$app->user->can()`, no policy class anywhere in `frontend/controllers`, `backend/controllers`, or `console/controllers` — confirmed by `middleware_inventory.md` §5 ("Custom filter classes — none exist").

---

## 1. `user_type` vs `user_group`: one is enforced, the other is vestigial

The `User` model carries **two** unrelated-looking role columns. Reading `common/models/User.php` and every write-site across the repo (`grep -rn "user_type\s*="` / `grep -rn "user_group"`, excluding vendor) resolves what each actually does:

### 1a. `user_type` — the real, enforced discriminator

Two literal values are ever written: `'admin'` and `'candidate'`.

- `backend/controllers/SiteController.php:203` (`actionLogin()`) sets `$model->user_type = 'admin'` before validating.
- `frontend/controllers/CandidateController.php:177` (`actionLogin()`, the real candidate login flow) sets `$model->user_type = 'candidate'`.
- `frontend/controllers/SiteController.php:103` (`actionLogin()`, the orphaned Yii2 scaffold controller, not linked from live nav) also sets `'candidate'`.
- `frontend/models/SignupForm.php:163` (`signup()`) sets `'candidate'`.

Enforcement happens in `common/models/LoginForm::validatePassword()`:

```php
// common/models/LoginForm.php:58-66
if (!$this->hasErrors()) {
    $user = $this->getUser();
    if (!$user || !$user->validatePassword($this->password)) {
        $this->addError($attribute, 'Incorrect username or password.');
    }
    // check user type
    // is candidate or admin user
    if ($user && $user->user_type != $this->user_type) {
        $this->addError($attribute, 'You are not ' . $this->user_type . ' user!');
    }
}
```

The calling controller sets `LoginForm::$user_type` to the value it expects (`'admin'` for `backend/SiteController`, `'candidate'` for `frontend/CandidateController`) **before** validating, so a candidate's credentials are rejected on the admin login form and vice versa. This is a **one-time, login-time gate**, not a per-request check — confirmed by `middleware_inventory.md` §4 and `portal_map.md` "Is `user_type` actually enforced per-app, or just descriptive metadata?": there is no Yii2 equivalent of the officer app's `IsAdmin`/`IsCandidate` middleware re-validating `user_type` on every subsequent request. Once logged in, `backend/`'s only ongoing gate is the app-level `AccessControl` (§3), which checks role `@` ("any authenticated identity in this app's session"), not `user_type == 'admin'` specifically. In practice the two apps' separate session/identity cookie names mean a frontend-authenticated candidate session is never even presented to the backend app, so this gap has no live exploit path today — but it is a config-only boundary, not a code-enforced one.

### 1b. `user_group` — a dropdown that is never checked by anything

`user_group` looks like a finer-grained role at first glance — the backend admin-user create/edit form offers a **three-value dropdown**:

```php
// backend/views/user/_form.php:38
<?= $form->field($model, 'user_group')->dropDownList(['super_admin' => 'Super admin', 'admin' => 'Admin', 'register' => 'Register',], ['prompt' => '']) ?>
```

But grepping the whole repo (`common`, `frontend`, `backend`, excluding vendor) for `user_group` turns up exactly four hits, none of them an authorization check:

- `common/models/User.php` — the `@property`, the `'string'` validation rule, and the `attributeLabels()` entry (schema/display only).
- `frontend/models/SignupForm.php:162` — `$user->user_group = 'register';`, set unconditionally on every candidate signup (not conditional on anything, so it's constant, not a real branch).
- `backend/models/UserSearch.php:21,73` — a search-form `safe` attribute + `andFilterWhere(['like', 'user_group', ...])`, i.e. it's filterable in the admin user grid, nothing more.
- `backend/views/user/_form.php:38` and `backend/views/user/view.php:33` — the create/edit dropdown and the read-only detail view.

Notably, `backend/views/user/index.php:76` even has the grid column **commented out** (`///  'user_group',`) — the admin user *list* doesn't display it at all, only the single-record create/edit/view screens do. The only column the index grid actually surfaces and filters on for role purposes is `user_type` (`backend/views/user/index.php:78-83`, dropdown restricted to `admin`/`candidate` only — `super_admin` isn't even an option there).

**No controller, `AccessControl` rule, or model method anywhere reads `user_group` to make an authorization decision.** `grep -rn "user_group" "$REPO" | grep -i "access\|role\|permission\|can("` returns nothing. This is structurally identical to the officer repo's `super_admin` enum finding (`role_permission_matrix.md` §1 there): a three-value privilege-looking field (`super_admin`/`admin`/`register`) exists in the schema/UI but is **never wired into any access-control decision**. Unlike the officer app (where `super_admin` was in the *enforced* column and simply unreachable), here the vestigial field sits in the **unenforced** column (`user_group`) while a *different*, plainer column (`user_type`, just `admin`/`candidate`) does the real work.

**Conclusion: there is exactly one enforced role discriminator, `user_type`, with two functional values (`admin`, `candidate`) plus unauthenticated/guest. `user_group`'s `super_admin`/`admin`/`register` values are decorative metadata — settable and visible in one admin-user form, never read by any authorization path.**

---

## 2. No backend admin sub-roles

Direct answer to "do backend admin users have any sub-roles": **no.** Every `user_type == 'admin'` identity that clears the backend app's login and app-level `AccessControl` filter (§3) has identical access to all 21 backend controllers — there is no "read-only admin", "reports-only admin", or "super admin" distinction enforced anywhere in the code. The only column that *looks* like it could carry such a distinction is `user_group` (§1b), and it is never checked. `backend/controllers/UserController.php` — which creates/edits admin accounts — does not set or constrain `user_type`/`user_group` based on the creating user's own role either (`grep -n "user_type" backend/controllers/UserController.php` returns nothing; it's a plain form field, same as every other attribute). Any existing admin can create another admin with any `user_group` value via that dropdown, and it will have zero effect on what that new account can do.

One narrow exception exists, but it is a hardcoded single-user check, not a role: `backend/controllers/ReportController.php::actionJsonForLs()` is gated with `if (Yii::$app->user->id == 1)` (`controller_inventory.md`, `ReportController.php` row) — a literal user-ID comparison, not `user_type`/`user_group`-based, and not documented or named as a "role" anywhere. It is the closest thing to a sub-role in the entire codebase and it is a one-off, not a pattern.

A separate, tangential finding while confirming this: `backend/views/user/index.php:126-134` builds a "candidateLogin" action-grid button, visible only for `user_type == 'candidate'` rows, linking to `/candidate/auto-login?slug=...&encpas={password_hash}&uname={username}` — passing the target candidate's password hash as a URL query parameter. `frontend/controllers/CandidateController.php`'s action list (`controller_inventory.md`) has no `actionAutoLogin()`, so this link 404s as shipped — a broken/dead admin-impersonation feature, not a live privilege-escalation path. Flagged for completeness since it's adjacent to the "does admin have extra powers over candidates" question, but out of scope to fix here.

---

## 3. Yii2 access-control building blocks actually used (recap, cited from `middleware_inventory.md`)

Per `middleware_inventory.md` §0/§1/§5, this app has no middleware layer; the closest equivalents, most-global first:

1. **App-level behavior** — `backend/config/main.php`'s live `'as access'` block (lines 132-144) attaches `yii\filters\AccessControl` to the whole `backend` `Application` object, so it runs on **every backend controller/action** before any controller-level filter. Rule: `login`/`error` public, everything else requires role `@` (any authenticated identity — **not** re-checked against `user_type`). A near-duplicate block, `'as beforeRequest'` (lines 60-72), is commented out — dead code. **Frontend has no app-level access-control equivalent at all** (`middleware_inventory.md` §1c, confirmed by reading the full 77-line `frontend/config/main.php`).
2. **Controller-level `behaviors()`** — `yii\filters\AccessControl` (role gating) and `yii\filters\VerbFilter` (HTTP-verb gating, e.g. `delete` → `POST`). Used on only 5 of the 31 controllers for `AccessControl` (§4).
3. **Manual in-action checks** — `if (Yii::$app->user->isGuest) { ... }` written directly into `actionXxx()` bodies. This is how most frontend "must be logged in" gating actually happens, not via a filter.
4. **No custom filter classes exist anywhere** in the repo (`middleware_inventory.md` §5) — every check is stock `AccessControl`/`VerbFilter` or a manual `isGuest` branch.

Because there is no fine-grained role system, the "roles" reachable at each controller collapse to exactly four buckets, matching the task brief:

- **Guest** — unauthenticated request.
- **Any-authenticated-frontend-user** — a session with role `@` inside the `frontend` app (in practice always `user_type == 'candidate'`, since that's the only identity type that ever logs in through `frontend/`, per §1a).
- **Any-authenticated-backend-user** — a session with role `@` inside the `backend` app (in practice always `user_type == 'admin'`, same reasoning).
- **N/A** — dead/inert controller, unreachable regardless of role.

---

## 4. Controller-by-controller role matrix (all 31 controllers)

Cited from `controller_inventory.md` (per-controller `behaviors()`/action findings) and `middleware_inventory.md` §3 (the full behaviors inventory, including its own summary table). "AccessControl?" marks the **5 controllers** that define their own `yii\filters\AccessControl` — everything else relies solely on the backend's app-level filter (§3.1) or a manual/absent guard (frontend).

### 4a. `frontend/` controllers (10 files — session `join_bd_navy_front`, no app-level filter)

| Controller | `AccessControl`? | Who can reach it | Notes |
|---|---|---|---|
| `AjaxController` | No | **Guest** (all 4 actions) | Fully public reference-data lookups (district/upazila/union cascades); CSRF disabled; no app-level backstop exists on this app (`middleware_inventory.md` §3.13). |
| `ApiController` | No | **N/A — dead/inert** | Extends `yii\rest\Controller`, zero `action*` methods defined; unreachable regardless of role (`controller_inventory.md`). |
| `CandidateController` | No (manual `isGuest`, 4 of 7 actions) | **Mixed, per-action**: `actionSignUp`/`actionLogin` = guest-only (blocks already-logged-in users); `actionChangePassword`/`actionRequestPasswordReset` = guest-only for reset, logged-in-only for change-password; `actionLogout`/`actionDownloadForm`/`actionValidateBirthRegistration` = **unguarded, effectively guest-reachable** | The real, in-use candidate auth flow (`components.user.loginUrl`). Sets `user_type='candidate'` at line 177 before validating (§1a). |
| `CheckEligibilityController` | No | **Guest** (all 5 actions) | Intentional — this is the live `defaultRoute` (`check-eligibility/index`), the public pre-account eligibility wizard. No `behaviors()` override at all. |
| `DeSailorController` | No | **Guest** (all 8 actions — **zero guard of any kind**) | Confirmed gap: no `behaviors()`, no `isGuest` check anywhere in the file (`middleware_inventory.md` §3.8). Every action dereferences `Yii::$app->user->identity->...` directly, so an actual guest request would fatal rather than being cleanly redirected — but structurally the role that can *attempt* to reach it is "anyone." |
| `MyApplicationController` | No (manual `isGuest`, correct) | **Any-authenticated-frontend-user** | Its one action (`actionIndex`) is cleanly guarded — redirects guests to `candidate/login` (`middleware_inventory.md` §3.9). |
| `OnlinePaymentController` | No | **Guest** (all live actions) | Intentional — SSLCommerz gateway server-to-server callbacks can't carry a session; CSRF disabled for `ssl-success`/`ssl-cancel`/`ssl-fail`. 3 of its actions (`actionPayment`, `actionPaymentResponseDeSailor`, `actionPaymentResponseSailor`) are additionally **broken** (missing `AamarPay`/`ShurjoPayment` classes — Fatal Error if hit), reachable by the same "guest" role regardless. |
| `OnlinePaymentController_shurjo_pay.php` | N/A | **N/A — dead file** | PSR-4/autoloader name collision with the live `OnlinePaymentController`; never resolved by any route (`controller_inventory.md`). |
| `SailorCandidateController` | **Yes** — `only => ['payment', 'academic-info']` | `payment`/`academic-info` = **any-authenticated-frontend-user** (`roles => ['@']`); the other 8 actions (`personal-info`, `application-preview`, `complete-application`, `download`, `download-form`, `verify-candidate`, `refund-phone`, `cancel-application`) = **guest-reachable by omission** (no filter, no manual guard) | A third rule in the same filter (`allow guests on 'verify-candidate'`, `roles=>['?']`) is dead — excluded by `only`, can never fire (`middleware_inventory.md` §3.6). |
| `SiteController` | **Yes** — `only => ['logout', 'signup']` | `signup` = **guest**; `logout` = **any-authenticated-frontend-user**; all other actions (`index`, `form`, `login`, `contact`, `about`, `request-password-reset`, `reset-password`, `verify-email`, `resend-verification-email`) = **guest** (outside `only`, unrestricted) | Orphaned Yii2 scaffold controller — reachable by direct URL (`/site/login`, `/site/signup`) but not linked from the live layout; superseded by `CandidateController`. Also sets `user_type='candidate'` at line 103 (a second, unused enforcement point). |

### 4b. `backend/` controllers (21 files — session `join_navy-backend`, app-level `AccessControl` live on every action except `login`/`error`)

| Controller | Own `AccessControl`? | Who can reach it | Notes |
|---|---|---|---|
| `AjaxController` | No | **Any-authenticated-backend-user** (app-level filter only) | No own `behaviors()`, only CSRF opt-out. Includes `actionDecodePhone()` — raw-SQL batch PII re-encryption over up to 5,000 rows, gated *only* by the single app-level filter (`middleware_inventory.md` §3.5). |
| `BulkCheckController` | No | **N/A — inert stub, zero actions** | Empty class body; app-level filter is moot since nothing is reachable (`controller_inventory.md`). |
| `CanDesignationController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard Gii CRUD. |
| `DeSailorBranchController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | All 4 `render()` targets are broken — non-functional as shipped regardless of who reaches it (`controller_inventory.md` item 2). |
| `DeSailorReportController` | No | **Any-authenticated-backend-user** | Bulk PII export/reporting (xlsx/PDF), no controller-level filter at all — app-level filter is the sole gate. |
| `DeSailorsController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | `actionCreate()` renders a missing view (broken, item 4). |
| `DistrictsController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard CRUD. |
| `EligibilityController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard CRUD + custom multi-select handling. |
| `LogReportController` | No | **Any-authenticated-backend-user** | Audit-log viewer (reads action-log ndjson from R2), no controller-level filter — app-level filter only. |
| `ReportController` | No | **Any-authenticated-backend-user** | Largest controller (1,605 lines), 23 actions, bulk PII export/reporting; **`actionJsonForLs()` additionally requires `Yii::$app->user->id == 1`** (§2) on top of the app-level filter — the one hardcoded exception in the whole app. |
| `SailorBatchConfigurationController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard CRUD + nested exam-date rows. |
| `SailorBatchsController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard CRUD + file upload. |
| `SailorCentDistMappingController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard CRUD. |
| `SailorCentersController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard CRUD. |
| `SailorsController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | `actionCreate()`/`actionDelete()` fully commented out — dead, not reachable by any role. |
| `SiteController` | **Yes** — full rule set | `login`/`error` = **guest**; `logout`/`index` = **any-authenticated-backend-user** (redundant with app-level filter) | The only backend controller with a complete, self-contained `AccessControl`. `actionLogin()` sets `user_type='admin'` at line 203 (§1a) — the actual admin-vs-candidate enforcement point. |
| `SubjectsController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | Standard CRUD. |
| `TeController` | No | **N/A — inert stub, zero actions** | Empty class body (`controller_inventory.md`). |
| `UnionsController` | **Yes** — `roles=>['@']`, all actions, + trailing `allow=>false` fallback | **Any-authenticated-backend-user** | Redundant with the app-level filter, not a gap — same rule, just duplicated (`middleware_inventory.md` §3.3). |
| `UpozilasController` | **Yes** — same pattern as `UnionsController` | **Any-authenticated-backend-user** | Same redundancy note. |
| `UserController` | No (`VerbFilter` only) | **Any-authenticated-backend-user** | **Notable gap**: manages admin accounts and password hashes, yet has no controller-level `AccessControl` of its own — relies entirely on the app-level filter, same as every low-sensitivity CRUD controller in this list. `actionCreate()` renders a missing view (broken, item 3); `actionDelete()` fully commented out. |

**Count check:** 5 controllers total define their own `AccessControl` — `frontend/SailorCandidateController`, `frontend/SiteController`, `backend/SiteController`, `backend/UnionsController`, `backend/UpozilasController` — matching `middleware_inventory.md`'s own summary table exactly. The remaining 26 controllers (5 frontend + 21 backend, two of which — `ApiController`, `BulkCheckController`, `TeController` — are inert stubs and one — `OnlinePaymentController_shurjo_pay.php` — is a dead file) have **no `AccessControl` at all**, relying either on the backend's single app-level filter (backend controllers — real coverage, just centralized) or on nothing/manual `isGuest` checks (frontend controllers — genuinely thin, per `portal_map.md`'s "Frontend has dramatically thinner coverage than backend").

---

## 5. Sailor track vs De-Sailor track: not a permission distinction

Direct answer to "is there any distinction between 'sailor track' and 'de-sailor track' users at the permission level": **no.** Both tracks share the identical `user_type='candidate'` identity (`middleware_inventory.md` §4 point 5) — there is no `user_type` value, `user_group` value, `AccessControl` rule, or login-time check anywhere that differs between a Sailor applicant and a De-Sailor applicant. This is confirmed as **data-driven, not auth-driven** by `model_inventory.md`'s dedicated "Sailors vs. DeSailors" section and restated in `portal_map.md`'s "Sailor vs De-Sailor" section:

- `Sailors` (`{{%sailors}}`) and `DeSailors` (`{{%de_sailors}}`) are two separate `ActiveRecord` models/tables — the general recruitment track vs. the Direct-Entry/trade track — not a subtype or single-table-inheritance relationship.
- Which track a signed-in candidate lands on is decided by `frontend/controllers/CandidateController.php`'s `haveCeci()` helper (~lines 233-267), which inspects the eligibility-check result (`sailor_or_de_sailor`, set to `'sailor'` or `'de_sailor'`) and routes accordingly into `SailorCandidateController` or `DeSailorController` — pure business logic computed from the candidate's own eligibility answers, evaluated **after** authentication, not a role assigned **at** authentication.
- The two tracks' controllers happen to have **asymmetric access control** (§4a: `SailorCandidateController` partially gates 2 of 10 actions, `DeSailorController` gates none of its 8) — but that asymmetry is a coverage gap in the same flat `any-authenticated-frontend-user` bucket, not evidence of two different roles. A candidate authenticated for the Sailor track and one authenticated for the De-Sailor track are running through the exact same `user_type='candidate'` gate; only the *data* (which model/table their application row lives in) differs.

---

## 6. Permission Matrix (summary)

| Role | Reachable via | Key actions allowed | Key actions denied | Notes |
|---|---|---|---|---|
| **Public / Unauthenticated** | `frontend/` public actions (most of `CandidateController`, all of `CheckEligibilityController`, `AjaxController`, `OnlinePaymentController`, most of `DeSailorController`, 8 of 10 `SailorCandidateController` actions, orphaned `SiteController`); `backend/` `login`/`error` only | Eligibility check wizard, candidate sign-up/sign-in, district/upazila/union AJAX lookups, payment-gateway callbacks, **and — by omission, not by design — most of the De-Sailor and much of the Sailor application wizard once a `slug` is known** (§4a) | Anything behind `roles=>['@']` on either app; all `backend/` actions except `login`/`error` | Frontend has no app-level backstop (§3), so "public by omission" is a real, much larger surface here than the deliberately-public buckets. |
| **Any-authenticated-frontend-user** (`user_type='candidate'`, session `join_bd_navy_front`) | `MyApplicationController`; `payment`/`academic-info` on `SailorCandidateController`; `logout` on `frontend/SiteController`; guarded actions of `CandidateController` | View own applications (`Sailors`/`DeSailors`, scoped by `created_by` where checked — ownership scoping is out of this doc's scope, see model/controller docs), pay, progress the Sailor wizard's gated steps | Any `backend/*` route (blocked by physical app separation, §0, not by a role check) | No `admin` privileges leak to this role; session cookie is entirely separate from backend's. |
| **Any-authenticated-backend-user** (`user_type='admin'`, session `join_navy-backend`) | All 21 backend controllers via the app-level `'as access'` filter (§3), regardless of whether the controller defines its own `AccessControl` | Full CRUD on all reference/config data (districts/upazilas/unions/subjects/can-designation/eligibility/sailor-centers/batches), candidate listing/edit/reporting, bulk PII export, admin **user** account management (create/edit — not delete, commented out), audit-log viewing, raw-SQL PII re-encryption (`AjaxController::actionDecodePhone`) | Nothing is denied to an authenticated backend user beyond the one hardcoded `user->id == 1` check on `ReportController::actionJsonForLs()` (§2) | No sub-roles (§2) — every `admin` account is fully equivalent once past login, regardless of the cosmetic `user_group` value on its record. |
| **`user_group` values other than what backs `user_type`** (`super_admin`, or `register` as distinct from `admin`/`candidate`) | *None reachable as a role.* | *(none — `user_group` is never read by any `AccessControl` rule, controller check, or model method, §1b)* | Everything, functionally — but not because it's blocked; because nothing checks it | Present only as a display/filter field in the backend admin-user CRUD screens. Treat as decorative metadata, not a privilege tier — the same conclusion the officer app reached about its dead `super_admin` enum value, reached here by a different mechanism (unused column vs. unreachable enum). |

---

## 7. Source files read in full or in relevant part for this analysis

- `composer.json`
- `common/models/User.php`
- `common/models/LoginForm.php`
- `frontend/models/SignupForm.php` (`signup()`)
- `backend/views/user/_form.php`, `backend/views/user/index.php`, `backend/views/user/view.php`
- `backend/models/UserSearch.php`
- Cited without re-reading (already-verified Phase 0/Phase 1 docs, per task instructions): `docs/00-inventories/controller_inventory.md`, `docs/00-inventories/middleware_inventory.md`, `docs/00-inventories/model_inventory.md`, `docs/01-architecture/portal_map.md`
