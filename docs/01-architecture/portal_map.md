# Portal Map — join-navy-sailor-legacy

Method: read `docs/00-inventories/route_inventory.md`, `controller_inventory.md`, and `middleware_inventory.md` in full (all already-verified Phase 0 inventories — cited by relative path throughout rather than re-derived), cross-referenced with `common/models/User.php` / `LoginForm.php` (via `model_inventory.md`), `view_inventory.md` for the rendered shells, and the three apps' `config/main.php` files directly for session/cookie/access-control config. Tone and structure follow the sibling Laravel doc, `../../../join-navy-officer-legacy/docs/01-architecture/portal_map.md`.

## Foundational fact: TWO physically separate applications, not one guard split by a column

This is the single biggest structural difference from `join-navy-officer-legacy`. The Laravel sibling is **one** application with **one** `web` session guard, where "Admin Portal" vs "Candidate Portal" is purely a `user_type` string check inside shared middleware. This app is a **Yii2 2.0 advanced template**: `frontend/` and `backend/` are two independent `yii\web\Application` instances, each with its **own entry script, own `webroot`, own session, own cookie names, and own `config/main.php`** (`route_inventory.md` §"Entry scripts"):

| | `frontend/` | `backend/` |
|---|---|---|
| Entry script | `frontend/web/index.php` | `backend/web/index.php` |
| Session cookie name | `join_bd_navy_front` | `join_navy-backend` |
| Identity cookie name | `_join_bd_navy_front` | `_join_navy-backend` |
| `defaultRoute` | `check-eligibility/index` (public wizard) | `site/index` (dashboard) |

(`middleware_inventory.md` §2, quoting both `config/main.php` files verbatim.)

**`common/models/User.php` is nonetheless one shared table** (`{{%user}}`) — both apps set `components.user.identityClass = 'common\models\User'` (`middleware_inventory.md` §2), so an admin row and a candidate row live side by side in the same table, exactly like the officer app's shared `users` table. But the two apps do **not** share a session the way Laravel's two `user_type` branches share one `web` guard — a login on `frontend` writes a `join_bd_navy_front` cookie; `backend` never reads that cookie name, so the practical isolation boundary is the **separate PHP application + separate cookie namespace**, not a runtime role check.

### Is `user_type` actually enforced per-app, or just descriptive metadata?

Confirmed via `middleware_inventory.md` §4 (its own dedicated section on exactly this question) and `model_inventory.md` §9 (`LoginForm.php`): **`user_type` is enforced exactly once, at login time, and nowhere else.**

- `common/models/LoginForm::validatePassword()` does `if ($user && $user->user_type != $this->user_type) { $this->addError(...); }` (`model_inventory.md` L284) — the calling controller sets `$model->user_type` *before* validating: `'admin'` in `backend/controllers/SiteController.php:203`, `'candidate'` in `frontend/controllers/CandidateController.php:177` (and in the orphaned `frontend/controllers/SiteController.php:103` — see login entry points below).
- There is **no per-request re-check**, unlike the officer app's `IsAdmin`/`IsCandidate` middleware which re-validates `user_type` on every protected request. Once logged in, `backend/`'s only ongoing gate is the app-level `'as access'` `AccessControl` behavior in `backend/config/main.php`, and it checks role `@` ("is *any* identity authenticated"), **not** `user_type == 'admin'` (`middleware_inventory.md` §1b, explicit "Important gap" callout).
- In principle, if a `user_type='candidate'` identity ever ended up authenticated inside a `backend`-namespaced session (there is no code path that does this today, but nothing at the filter layer would stop it), every `'as access'`-gated backend controller would let it through. The only thing standing in the way is that the two apps use different session/identity cookie names, so a frontend login cookie is simply never sent to/read by the backend app.
- **Verdict:** `user_type` is real (it's the only thing that stops a candidate's credentials from logging them into the admin login form, and vice versa), but it is a **one-time login-time gate**, not a standing per-request authorization boundary. The actual, load-bearing boundary is physical app separation — you cannot reach backend controllers from a frontend session at all, regardless of `user_type`, because it's a different PHP application with a different session cookie.

There is also no `Candidate` authenticatable model, mirroring the officer app: `Sailors`/`DeSailors` (see below) are *application records* a signed-in `User` fills out, not identities themselves.

---

## Portal 1 — Backend Admin Portal (staff/back-office)

**App:** `backend/` (separate Yii2 application, own webroot/session — not a URL prefix; see Foundational Fact above). 21 controller files, 118 live actions (`route_inventory.md` §"Backend controllers").

**Entry URLs (Yii2 default routing convention: `/{controller-id}/{action-id}`, no explicit `UrlManager` rules exist in either app — `route_inventory.md` §"How a URL maps to code"):**
- `GET /site/login` → `SiteController::actionLogin()` — login form, guest-allowed
- `POST /site/login` → same action, credential check
- `GET /site/index` → `SiteController::actionIndex()` — dashboard (also the live `defaultRoute`, since `backend/config/main.php`'s `defaultRoute` override is commented out and falls back to Yii's own `site/index` default)

**Authentication flow (cited):**
1. `SiteController::actionLogin()` (`backend/controllers/SiteController.php:194`) sets `$model->user_type = 'admin'` before validating a `LoginForm`, guarded by a math CAPTCHA (`captureValue()`) — this line is the actual enforcement point for "only admin-type users can log into the backend" (`middleware_inventory.md` §3.4).
2. `LoginForm::validatePassword()` (`common/models/LoginForm.php`) rejects the credentials if the matched `User.user_type` isn't `'admin'` (`model_inventory.md` §9).
3. On success, `Yii::$app->user->login()` — session cookie `join_navy-backend` / identity cookie `_join_navy-backend` (`middleware_inventory.md` §2).
4. Every subsequent backend request passes through the app-level `'as access'` `AccessControl` behavior in `backend/config/main.php` (lines 132-144, live) — allows `login`/`error` unauthenticated, requires role `@` (any authenticated identity) for everything else. **This is the Yii2 equivalent of the officer app's `IsAdmin` middleware, except it never re-checks `user_type`** (`middleware_inventory.md` §1b — flagged explicitly as a gap).
5. A second, near-identical `AccessControl` block (`'as beforeRequest'`, lines 60-72) is commented out — dead code, confirms only one of the two blocks is live (`middleware_inventory.md` §1a).
6. Logout: `POST /site/logout` → `SiteController::actionLogout()`, requires role `@`, `VerbFilter`-restricted to POST.

**Dashboard:** `SiteController::actionIndex()` builds three chart datasets from `DeSailors`/`CanEligibilityCheckInfo` counts; renders `backend/views/site/index.php` under layout `backend/views/layouts/admin.php` (`view_inventory.md` L42, L204). Login/logout use the chromeless `backend/views/layouts/blank.php` (`SiteController` sets `$this->layout = 'blank'` for login).

**Roles/Permissions:** No distinct role/permission table — the app-level filter's role `@` is a single flat "any authenticated backend user" gate, same flat-role shape as the officer app's `isAdmin`. `backend/controllers/UnionsController.php` and `UpozilasController.php` additionally declare their own controller-level `AccessControl` (role `@`, all actions) — confirmed **redundant**, not a gap, since it duplicates the app-level filter exactly (`middleware_inventory.md` §3.3).

**Which controllers require login vs which don't:**

| Coverage | Controllers | Source |
|---|---|---|
| Public (`login`, `error` only) | `SiteController` (login/error actions) | `middleware_inventory.md` §1b, §3.4 |
| Gated — app-level filter only, no controller `behaviors()` at all | `BulkCheckController` (inert stub), `DeSailorReportController`, `LogReportController`, `ReportController`, `TeController` (inert stub) | `middleware_inventory.md` §3.2 |
| Gated — app-level filter + `VerbFilter` (delete→POST) only | `CanDesignationController`, `DeSailorBranchController`, `DeSailorsController`, `DistrictsController`, `EligibilityController`, `SailorBatchConfigurationController`, `SailorBatchsController`, `SailorCentDistMappingController`, `SailorCentersController`, `SailorsController`, `SubjectsController`, `UserController` | `middleware_inventory.md` §3.1 |
| Gated — app-level filter + redundant controller-level `AccessControl` | `UnionsController`, `UpozilasController` | `middleware_inventory.md` §3.3 |
| Gated — app-level filter, own full `AccessControl`+`VerbFilter` | `SiteController` (all other actions) | `middleware_inventory.md` §3.4 |
| Gated — app-level filter, CSRF disabled only | `AjaxController` | `middleware_inventory.md` §3.5 |

**⚠ Notable gap — bigger deal here than in the officer app, because coverage is thinner overall:** `middleware_inventory.md`'s summary table (its final section) confirms only **5 of 31 controllers across both apps** define any `AccessControl` at all (`frontend/controllers/SiteController.php`, `frontend/controllers/SailorCandidateController.php` — partially, `backend/controllers/SiteController.php`, `UnionsController.php`, `UpozilasController.php`). Every other backend controller — **including `UserController.php`, which manages admin accounts and password hashes, and has no `AccessControl` at all** — relies entirely on the single app-level `'as access'` filter as its only gate. That filter is real and live (unlike the officer app's admin AJAX gap, there is no *routing-level* hole here), but it means a single config mistake in one file (`backend/config/main.php`'s `'as access'` block) is the entire backend's authorization boundary, for every one of its 21 controllers. `backend/controllers/AjaxController::actionDecodePhone()` (unauthenticated-if-that-filter-ever-broke, currently gated by it) performs raw-SQL PII re-encryption over up to 5,000 rows per call — flagged in `middleware_inventory.md` §3.5.

**Broken controllers (cite, don't re-derive — `controller_inventory.md`):** `DeSailorBranchController` (all 4 views missing — non-functional as shipped), `UserController::actionCreate()` and `DeSailorsController::actionCreate()` (both `render('create', ...)` against a missing view file), `SailorsController`/`UserController` have their `actionDelete()` fully commented out (no delete route exists for sailors or admin users via these controllers).

---

## Portal 2 — Frontend Candidate Portal (authenticated applicant-facing)

**App:** `frontend/` (separate Yii2 application; session cookie `join_bd_navy_front`). Candidate-authenticated actions are spread across `CandidateController` (real auth flow), `SailorCandidateController`, `DeSailorController`, `MyApplicationController`, `OnlinePaymentController` — there is no single `/candidate/*` prefix the way the officer app has a URL group; each is its own controller reached by Yii2's default `/{controller}/{action}` convention (`route_inventory.md`).

**Entry URLs:**
- `GET /candidate/login` → `CandidateController::actionLogin()` — **the real, in-use login form** (this is `components.user.loginUrl` in `frontend/config/main.php`, `middleware_inventory.md` §2)
- `POST /candidate/login` → same
- `GET /candidate/sign-up` → `CandidateController::actionSignUp()` — registration
- `GET /my-application/index` → `MyApplicationController::actionIndex()` — post-login landing/dashboard

**Authentication flow (cited):**
1. `CandidateController::actionLogin()` (`frontend/controllers/CandidateController.php:162`) — guest-only guard (`isGuest` check at line 172), sets `$model->user_type = 'candidate'` (line 177) before validating — the enforcement point mirroring `backend/controllers/SiteController::actionLogin`'s `'admin'` assignment (`middleware_inventory.md` §3.7).
2. Same `LoginForm::validatePassword()` shared with the backend flow rejects a non-`candidate` `user_type`.
3. `actionSignUp()` (line 52) creates a new `User` row (`user_type='candidate'`, via `frontend/models/SignupForm::signup()`), guest-only guarded, then logs the user in.
4. No `AccessControl` or app-level filter exists on `CandidateController` at all — every gate is a manual `if (Yii::$app->user->isGuest)` check inside 4 of its 7 actions (`actionSignUp`, `actionLogin`, `actionChangePassword`, `actionRequestPasswordReset`); the other 3 (`actionLogout`, `actionDownloadForm`, `actionValidateBirthRegistration`) are intentionally unguarded, low-risk (`middleware_inventory.md` §3.7).
5. Frontend has **no app-level `AccessControl` equivalent at all** — confirmed by reading the full 77-line `frontend/config/main.php` (`middleware_inventory.md` §1c). Every "must be logged in" rule on this whole app is either a controller-level `AccessControl` (one controller only, partially — see gap below) or a manual `isGuest` check written into individual action bodies. This is a materially thinner safety net than the backend's single app-level filter.
6. Logout: `CandidateController::actionLogout()`.

**Dashboard:** `MyApplicationController::actionIndex()` — the one frontend controller with a **clean, correctly-guarded** manual `isGuest` check (`middleware_inventory.md` §3.9) — queries both `Sailors` and `DeSailors` for the current user and renders `frontend/views/my-application/index.php` under the shared `frontend/views/layouts/mainNavy.php` (`view_inventory.md` L41, L101).

**Roles within this portal:** Single flat role, any `user_type='candidate'` user — same flat shape as the Laravel sibling's Candidate Portal.

**⚠ Notable gap — the sailor-app's version of the officer app's `CanContinueApplication` finding, but worse:** The two controllers that actually run the application wizard after login have **inconsistent, partial coverage**:
- `SailorCandidateController` declares an `AccessControl` scoped via `'only' => ['payment', 'academic-info']` — so only 2 of its 10 actions are covered at all. The other 8 (`personal-info`, `application-preview`, `complete-application`, `download`, `download-form`, `verify-candidate`, `refund-phone`, `cancel-application`) have **no access-control filter and no manual `isGuest` guard**, and dereference `Yii::$app->user->identity->...` directly — a guest request would fatal on a null identity rather than redirect cleanly. A third rule inside the same filter (`allow guests on 'verify-candidate'`) is dead — excluded by the `only` scope, can never fire (`middleware_inventory.md` §3.6, its own confirmed-gap callout).
- `DeSailorController` — the De-Sailor track's equivalent controller — has **zero `behaviors()` override and zero manual `isGuest` checks anywhere**, for all 8 of its actions. `middleware_inventory.md` §3.8 explicitly flags this as strictly worse than its `SailorCandidateController` sibling, which at least partially gates two actions.

**Primary features:**
- Multi-step Sailor application wizard: payment → academic info → personal info → preview → completion/download (`SailorCandidateController`)
- Multi-step De-Sailor (Direct Entry) application wizard: same shape (`DeSailorController`) — see track comparison below
- Post-login dashboard listing both application types (`MyApplicationController`)
- Online payment (SSLCommerz) initiation and gateway callbacks — `OnlinePaymentController` (callback actions intentionally unauthenticated/CSRF-exempt, expected for server-to-server gateway callbacks — `middleware_inventory.md` §3.11)
- Change password / password reset — `CandidateController`
- AJAX helpers scoped to registration/eligibility — `frontend/controllers/AjaxController` (district/upazila/union cascading lookups, GPA/subject lists) — fully public, no guard, no app-level backstop (`middleware_inventory.md` §3.13, noted as presumably-intentional public reference data, same pattern as the officer app's equivalent)

---

## The "Sailor" vs "De-Sailor" split — two parallel application pipelines

Cited from `docs/00-inventories/model_inventory.md`'s dedicated "Sailors vs. DeSailors" section (L496-506). This is **not an authentication distinction** — both tracks share the exact same `user_type='candidate'` identity (`middleware_inventory.md` §4, point 5) — it is a **data-driven branch inside the application logic**, decided once a candidate finishes the public eligibility check (see Portal 3 below).

- **`Sailors`** (`{{%sailors}}` table, `common/models/Sailors.php`, 1,315 lines — the largest model in the app) is the **general/standard sailor recruitment track**, covering the broad `can_designation` set (`candidate_type` 1 = Sailor).
- **`DeSailors`** (`{{%de_sailors}}` table, `common/models/DeSailors.php`) is the **"DE" (Direct Entry) track** — trade-entry recruitment for experienced/skilled candidates. `candidate_type` is 2=Artificer / 3=Dockyard, and the model carries DE-only fields absent from `Sailors` entirely: `diploma_trade_institute`, `diploma_trade_course`, `diploma_trade_registration_roll`, `diploma_trade_gpa`, plus four "experience" blocks capturing prior trade work/certifications. `is_departmental_candidate` additionally applies a different max-age ceiling for already-serving candidates.
- They are **two fully separate `ActiveRecord` models backed by two separate tables** — not single-table-inheritance or a subtype relationship — but structural twins otherwise: identical supporting infrastructure (`SailorsQuery` shared by both `find()` overrides, identically-named/signature static helpers like `numberOfApplication()`, `nextRollByBatchId()`, `generateLog()`), and each paired with a lightweight backend `*Reference` AR class (`SailorsReference` / `DeSailorsReference`) for the backend "add reference" form (same table, reference-columns-only view).
- Which track a candidate lands on is decided inside `frontend/controllers/CandidateController.php` (its `haveCeci()` helper, ~lines 233-267): it inspects the eligibility-check result (`sailor_or_de_sailor`, set to `'sailor'` or `'de_sailor'`) and routes into either `SailorCandidateController` or `DeSailorController` accordingly. There is no login-time or per-request filter deciding this — it is pure business logic, computed from the candidate's eligibility answers, not their identity.
- The two tracks' controllers (`SailorCandidateController` vs `DeSailorController`) mirror each other action-for-action (`payment`, `academic-info`, `personal-info`, `application-preview`, `complete-application`, `download`, `download-form`, `verify-candidate`) but — as noted in the gap above — have **asymmetric access control**: the Sailor controller partially gates 2 of 10 actions, the De-Sailor controller gates none of its 8.
- On the backend side, the same split repeats as parallel CRUD/report controller pairs: `SailorsController`/`DeSailorsController`, `ReportController`/`DeSailorReportController`, `SailorBatchsController`/`DeSailorBranchController` (DE's batch equivalent) — all following the same VerbFilter-only, app-level-filter-gated pattern from Portal 1.

---

## Portal 3 — Public Eligibility-Check / Landing surface (unauthenticated)

Functionally distinct from the Candidate Portal, exactly as in the officer app — no login required, and it is the literal site root/default route of the whole `frontend/` application.

**App / entry:** `frontend/` — `CheckEligibilityController` is the live `defaultRoute` (`check-eligibility/index`, `frontend/config/main.php`, quoted in `route_inventory.md` §"defaultRoute").

**Entry URLs:**
- `GET /` (and `GET /check-eligibility/index`) → `CheckEligibilityController::actionIndex()` — personal-info step, the literal homepage
- `GET /check-eligibility/academic-info/{slug}` → `actionAcademicInfo()`
- `GET /check-eligibility/eligible-department/{slug}` → `actionEligibleDepartment()`
- `GET /check-eligibility/apply-department/{slug}/{adpt}` → `actionApplyDepartment()` — hands off into `candidate/sign-up` or `candidate/login`

**Authentication flow:** none — `CheckEligibilityController` has no `behaviors()` override at all; intentionally public by design, since this is the pre-account eligibility wizard (`middleware_inventory.md` §3.10). `actionIndex()` has an `isGuest` branch, but it's a UX convenience (pre-filling a known DOB for already-logged-in visitors), not an access gate.

**Dashboard:** N/A — linear wizard. Renders under the same `frontend/views/layouts/mainNavy.php` shell used by the authenticated Candidate Portal (`view_inventory.md` — no separate "logged out" shell exists, same pattern as the officer app's shared `layouts/frontend/main.blade.php`).

**Primary features:** general/academic info eligibility questionnaire → eligible-department computation (queries `Eligibility` config rows + active `SailorBatchConfiguration` by district/gender) → hand-off into sign-up/sign-in carrying the eligibility record forward, exactly mirroring the officer app's `EligibilityController` chain.

---

## Portal 4 — Public/unauthenticated document & lookup surfaces (scattered, not consolidated)

Unlike the officer app, these don't sit behind a single unauthenticated URL prefix — they're individual unguarded actions on otherwise-authenticated-track controllers:

- **`SailorCandidateController::actionVerifyCandidate($slug)`** / **`DeSailorController::actionVerifyCandidate($slug)`** — public, no ownership check, slug-based lookup of an already-submitted application (same knowledge-based-lookup pattern as the officer app's Portal 4). Both have an additional ~17-18 lines of dead/unreachable code after their `return` — a second, never-executed mPDF block (`controller_inventory.md`).
- **`SailorCandidateController::actionDownloadForm($slug)`** / **`DeSailorController::actionDownloadForm($slug)`** — mPDF generation by direct slug lookup, no ownership check, differs from the controllers' own `findModel()` pattern used elsewhere.
- **`CandidateController::actionDownloadForm()`** — looks up a `Sailors` record by application ID or batch/serial/dob, no login required (intentionally, per `middleware_inventory.md` §3.7).
- **`frontend/controllers/AjaxController`** — fully public reference-data lookups (district/upazila/union cascading dropdowns), CSRF disabled, no app-level backstop unlike its backend namesake.
- **`OnlinePaymentController`**'s SSLCommerz gateway callback actions (`ssl-success`, `ssl-cancel`, `ssl-fail`) — unauthenticated by necessity (server-to-server callbacks can't carry a session), notably **log the user in from the callback payload** to resume the wizard.

**Verdict:** functionally the same "knowledge-of-a-slug-is-the-only-access-control" pattern as the officer app's Portals 4-6, just distributed across the authenticated-track controllers rather than living in dedicated public-facing controller classes.

---

## Notable cross-portal observations (flagging, not fixing)

- **The physical app split is the real security boundary, `user_type` is a login-time convenience.** A bug in the app-level backend filter or in `LoginForm::validatePassword()` is a bug in the entire authorization model — but even then, a frontend session literally cannot reach backend controllers because the cookie names don't match. This is architecturally the inverse of the officer app's risk profile: there, a bug in `IsAdmin`/`IsCandidate` is catastrophic because both portals share one session; here, the worse-case blast radius of an auth bug is contained by app separation, but *within* the backend app, the single app-level filter is a single point of failure for all 21 controllers (see Portal 1's gap).
- **Frontend has dramatically thinner coverage than backend.** Backend's app-level `'as access'` filter means every backend action is gated by default unless explicitly excepted. Frontend has no equivalent — coverage is opt-in, action by action, and inconsistent even within a single controller pair (`SailorCandidateController` vs `DeSailorController` — see the split section above).
- **A second, dead login path exists on the frontend**: `frontend/controllers/SiteController.php` is the **unmodified Yii2 "advanced app" template scaffold controller** — its `actionLogin()`/`actionSignup()` (also setting `user_type='candidate'`) are reachable by direct URL (`/site/login`, `/site/signup`) but not linked from the live layout (`grep` confirms only the two orphaned view files themselves reference them) — the real flow is `CandidateController`. Same `user_type`/validation, so not a security hole, but dead/confusing parallel surface area, directly analogous to the officer app's own dead-route findings (`middleware_inventory.md` §3.6).
- **Three stub/inert controllers**: `frontend/controllers/ApiController.php` (extends `yii\rest\Controller`, not `ActiveController`, zero actions — dead REST scaffold), `backend/controllers/BulkCheckController.php`, `backend/controllers/TeController.php` (both empty class bodies with unused imports) — none reachable, all flagged in `controller_inventory.md`.
- **No custom filter classes exist anywhere** in this codebase (`middleware_inventory.md` §5) — every access-control check uses the stock `yii\filters\AccessControl`/`yii\filters\VerbFilter`, or a manual `isGuest` check. No custom `AuthMethod`, no bearer/token auth, no rate limiter, no equivalent of the officer app's `XssValidation` middleware (the closest analog is a per-model `strip_tags` filter on exactly 4 models' name fields).
- **Broken payment classes**: `frontend/controllers/OnlinePaymentController.php` references `AamarPay`/`ShurjoPayment` classes that don't exist in the repo — three reachable, unauthenticated payment-callback actions (`actionPayment`, `actionPaymentResponseDeSailor`, `actionPaymentResponseSailor`) will hard Fatal Error if hit; the working live path is the `ssl-*`-prefixed actions alongside them (`controller_inventory.md`, item 1).

---

## Summary Table

| Portal | App | Auth Required | Guard mechanism | Layout | Primary Users |
|---|---|---|---|---|---|
| Backend Admin Portal | `backend/` (own webroot/session) | Yes — session + app-level `AccessControl` (role `@` only, no `user_type` re-check) | `common\models\User` (shared table), `join_navy-backend` session | `backend/views/layouts/admin.php` (login: `layouts/blank.php`) | Navy recruitment back-office staff |
| Frontend Candidate Portal | `frontend/` (own webroot/session) | Mostly — no app-level filter; per-action `AccessControl` (1 controller, partial) or manual `isGuest` checks; several actions ungated by omission | `common\models\User` (shared table), `join_bd_navy_front` session | `frontend/views/layouts/mainNavy.php` | Registered applicant candidates (Sailor + De-Sailor tracks) |
| Public Eligibility / Landing | `frontend/` | No | n/a (anonymous, intentional) | `frontend/views/layouts/mainNavy.php` | Prospective applicants pre-screening themselves |
| Public document/lookup surfaces (verify-candidate, download-form, AJAX, payment callbacks) | `frontend/` | No (knowledge of encrypted slug / application id, or server-to-server callback) | n/a | mixed — some standalone PDF templates, some `mainNavy.php` | Candidates re-fetching their own PDFs; payment gateway; anyone with a valid slug |
| *(dead/orphaned)* `frontend/controllers/SiteController` login/signup | `frontend/` | Guest-only rule exists but unreachable via live nav | n/a | `frontend/views/layouts/mainNavy.php` | Not a real portal — orphaned Yii2 scaffold, superseded by `CandidateController` |
| *(inert stubs)* `ApiController`, `BulkCheckController`, `TeController` | `frontend/` / `backend/` | N/A — zero actions defined | n/a | n/a | Not real portals — dead scaffolding |
