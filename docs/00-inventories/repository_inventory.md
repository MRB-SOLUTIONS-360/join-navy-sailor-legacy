# Repository Inventory — join-navy-sailor-legacy

**Generated:** 2026-08-20 | Scope excludes `vendor/`, `.git`. Framework: **Yii 2.0 "advanced" project template** (not Laravel) — three application tiers (`common`, `frontend`, `backend`) plus a `console` tier, sharing one codebase.

### Top-line summary

| Metric | Value |
|---|---|
| Total PHP files (in-scope) | 319 |
| Total in-scope files (all types) | 578 |
| Total in-scope size | ~25 MB (`backend` 18 MB, `frontend` 5.4 MB, `common` 568 KB, `console` 60 KB, `docs` 32 KB, `environments` 244 KB, `vagrant` 56 KB, `dummy` 8 KB, plus ~700 KB of root files) |
| `vendor/` (excluded from scope) | 298 MB |
| Controllers | 31 total (`backend` 21, `frontend` 10, `console` 0) |
| Models (AR + Search + Form + service) | 48 total (`common/models` 29 incl. 2 subfolders, `backend/models` 12, `frontend/models` 7) |
| Views | 116 total (`backend/views` 77, `frontend/views` 39) |
| Migrations | **2** — `console/migrations/m130524_201442_init.php`, `console/migrations/m190124_110200_add_verification_token_column_to_user_table.php` |
| Git history | 1 commit (`93617780 "migrate to mrb"`) — no historical trail, repo was imported as a snapshot |
| Key duplication/dead-code issues | 1 shadow controller (`OnlinePaymentController_shurjo_pay.php`, references non-existent classes); 2 dead config-file snapshots in both `backend/config` and `frontend/config`; 2 stray generated PDF/PNG artifacts committed to `frontend/web/`; only default Yii scaffold tests exist, no domain test coverage |
| **Migration/schema gap** | Only 2 migrations exist, yet ~24 real domain model classes imply a much larger live schema (`Sailors`, `DeSailors`, `SailorBatchs`, `SailorBatchConfiguration`, `Eligibility`, `CanDesignation`, `SailorCenters`, `SailorCentDistMapping`, `Districts`, `Upozilas`, `Unions`, `Subjects`, etc.). **The database was almost certainly built by direct SQL/import, not through migration history** — flag as a modernization-effort risk (see below). |

---

### Top-level layout

| Path | Purpose |
|---|---|
| `common/` | Shared code used by both `frontend` and `backend`: AR models, mail templates, static/helper classes, enum classes, widgets, fixtures, shared config. |
| `frontend/` | Public-facing Yii web application (candidate/sailor self-service: eligibility check, application forms, online payment, "my application"). |
| `backend/` | Admin/back-office Yii web application (CRUD for sailors, batches, centers, districts, unions, upozilas, subjects, eligibility, reports, users). |
| `console/` | Console (CLI) application tier — migrations, console controllers, console-only models. Controllers and models subfolders are effectively empty (`.gitkeep` only). |
| `vendor/` | Composer-managed third-party packages (Yii2 core + extensions). 298 MB, excluded from scope. |
| `dummy/` | 5 placeholder files (`1.txt`–`4.txt`, `index.html`, `.htaccess`) — looks like a leftover deployment/hosting smoke-test folder, not part of the application. |
| `environments/` | Environment-config-switching source data for the `init` tool — `dev/` and `prod/` variants of `common/backend/console/frontend` config + `yii`/`yii_test` scripts, selected via `environments/index.php`. |
| `vagrant/` | Vagrant provisioning scripts (`provision/*.sh`), nginx vhost templates, and local config example — for a local VM dev environment (separate from the `docker-compose.yml` path). |
| `tests` (no top-level dir) | Unlike some Yii setups, there is no root `tests/`; each tier owns its own `tests/` (`common/tests`, `frontend/tests`, `backend/tests`) run together via the root `codeception.yml`. |
| `init` / `init.bat` | Environment-initialization tool (PHP CLI / Windows wrapper). Copies environment-specific config from `environments/{dev,prod}/` over the live tier configs, sets file permissions, generates cookie-validation keys. Interactive (`php init`) or scripted (`php init --env=Development --overwrite=n`). |
| `yii` / `yii.bat` | Console application bootstrap (`YII_ENV=dev` by default) — merges `common` + `console` configs and runs `yii\console\Application`. |
| `yii_test` / `yii_test.bat` | Same as `yii`, but also merges `common/config/test.php` + `test-local.php` and `console/config/test.php` + `test-local.php`, for running the app under the `test` environment (used by Codeception). |
| `docker-compose.yml` | Defines 3 services: `frontend` (port 20080), `backend` (port 21080), both built from their own tier `Dockerfile`s with the whole repo bind-mounted; `mysql:5.7` (db `yii2advanced`); a commented-out `pgsql` alternative. |
| `Vagrantfile` | Defines a local VM with `y2aa-frontend.test` / `y2aa-backend.test` hostnames via `vagrant-hostmanager`, provisioned by `vagrant/provision/*.sh`. |
| `environments/` config-switching mechanism | `environments/index.php` returns a manifest of named environments (`Development` → `dev/`, `Production` → `prod/`) each declaring which paths to `setWritable` (runtime/assets dirs), `setExecutable` (`yii`, `yii_test`), and which config files need a generated `cookieValidationKey`. `init` reads this manifest and copies the matching `environments/{env}/**` tree over the live tier folders. |
| `codeception.yml` | Root Codeception config — includes `common`, `frontend`, `backend` suites, writes output to `console/runtime/output`. |
| `composer.json` / `composer.lock` | Declares this as `yiisoft/yii2-app-advanced` (stock template name/description — never renamed for this project). |
| `requirements.php` | Stock Yii2 server-requirements checker script (CLI or copy-to-webroot). |
| `.htaccess` | Root Apache rewrite: routes everything to `frontend/web/` (front controller), i.e. `frontend` is the default-served tier at the domain root; `backend` is reached at its own subpath/subdomain via its own `web/` entry point. |
| `LICENSE.md`, `README.md` | Stock Yii2 advanced-template license/readme; README directory-structure section still describes the generic template layout (e.g. lists a `widgets/` dir for `frontend` that doesn't exist here — `frontend/components/` is used instead), i.e. never customized for this project. |

---

### `common/` — Shared code

- **Purpose:** AR models, mail views, static helpers, enum classes, one widget, fixtures, shared config used by both `frontend` and `backend`.
- **Size:** 568 KB, 57 PHP files (62 files total).
- **Subdirectories:**
  | Dir | Files | Notes |
  |---|---|---|
  | `models/` | 29 (incl. `payment/SSLPayment.php`, `scopeQuery/SailorBatchs.php`) | Core domain AR models: `Sailors`, `DeSailors`, `SailorBatchs`, `SailorBatchConfiguration(+ExamDate)`, `SailorCenters`, `SailorCentDistMapping`, `Eligibility`, `CanDesignation`, `CanEligibilityCheckInfo`, `Districts`, `Upozilas`, `Unions`, `Subjects`, `User`, plus `*Search` classes and account/form models (`LoginForm`, `ChangePassword`, `ResetPassword`, `SendSms`, `Session`, `DownloadDocuments`). |
  | `static/` | 5 | `StaticMethod` (639 lines, misc helpers + curl calls to a `spgCurlPost`-style external endpoint), `SendSms`, `AES256CTR` (field-level encryption), `DataEncryption`, `Constants`. |
  | `components/` | 1 | `R2Storage.php` (Cloudflare R2 storage wrapper). |
  | `enumClass/` | 1 | `Status.php`. |
  | `widgets/` | 1 | `Alert.php` (stock Yii2 flash-message widget). |
  | `mail/` | 6 | HTML/text pairs for password-reset and email-verification, plus `layouts/html.php` / `layouts/text.php`. |
  | `fixtures/` | 1 | `UserFixture.php` (stock scaffold fixture, no domain fixtures). |
  | `tests/` | 2 real test files | `unit/models/LoginFormTest.php` only — stock scaffold test, no coverage for any domain model. |
- **Config:** `bootstrap.php`, `main.php`, `main-local.php`, `params.php`, `params-local.php`, `test.php`, `test-local.php`, `codeception-local.php` — no dead/duplicate config files here.

---

### `frontend/` — Public candidate-facing application

- **Purpose:** Candidate self-service — eligibility check, sailor/de-sailor application forms, online payment, application status/history.
- **Size:** 5.4 MB, 95 PHP files (146 files total).
- **Controllers (10):** `AjaxController`, `ApiController`, `CandidateController`, `CheckEligibilityController`, `DeSailorController` (1,118 lines / 60 KB), `MyApplicationController`, `OnlinePaymentController` (285 lines), `OnlinePaymentController_shurjo_pay.php` (see Dead Code below), `SailorCandidateController` (1,195 lines / 64 KB — the largest controller in the app), `SiteController`.
- **Models (7):** `ContactForm`, `RefundForm`, `PasswordResetRequestForm`, `ResendVerificationEmailForm`, `SignupForm`, `ResetPasswordForm`, `VerifyEmailForm` — all account/contact-flow forms; no frontend-only AR models (domain models live in `common/models`).
- **Views (39):** grouped under `candidate/`, `check-eligibility/`, `de-sailor/`, `my-application/`, `online-payment/`, `sailor-candidate/`, `site/`, `layouts/`. Several individual view files are very large PDF/print-preview templates (`de-sailor/personal_info.php` 71.8 KB, `sailor-candidate/personal_info.php` 68.4 KB, `de-sailor/candidate/application_form_pdf.php` 55.8 KB, `sailor-candidate/candidate/application_form_pdf.php` 55.2 KB) — the `sailor-candidate/` and `de-sailor/` view trees are near-parallel duplicates of each other (same view set, once per applicant type), a structural duplication pattern rather than accidental copy-paste.
- **Components:** `components/SupportNo.php`, `components/StepAndSupport.php` + their paired view partials in `components/views/` — small reusable widgets, not part of the README's documented `widgets/` folder (which doesn't exist in this tier; README is stale/generic here).
- **`web/`:** contains 3 files that look like accidentally-committed runtime output rather than source assets — `2501001.pdf` (200,776 B), `filename.pdf` (200,776 B, different content from the former despite identical size), `barcode.png` (172 B). None are referenced by literal filename anywhere in the PHP source, consistent with them being one-off generated PDFs/barcodes from a manual test run that got committed instead of `.gitignore`d.
- **Tests:** `tests/functional`, `tests/acceptance`, `tests/unit` — all default Yii2 scaffold tests (`Login`, `Signup`, `VerifyEmail`, `ResendVerificationEmail`, `ResetPassword`, `Contact`, `Home`, `About` Cests/unit tests). No tests for `CheckEligibilityController`, `SailorCandidateController`, `DeSailorController`, or `OnlinePaymentController` — the entire real application logic is untested.

---

### `backend/` — Admin/back-office application

- **Purpose:** CRUD + reporting for the recruitment pipeline: sailors, batches, eligibility rules, exam centers, geography reference data (districts/upozilas/unions), users.
- **Size:** 18 MB, 131 PHP files (292 files total) — by far the largest in-scope tier, dominated by `web/adminAsset/` (17 MB of admin theme JS/CSS/vendor assets: `vendor/`, `fonts/`, `images/`, `js/`, `css/`).
- **Controllers (21):** `AjaxController`, `BulkCheckController`, `CanDesignationController`, `DeSailorBranchController`, `DeSailorReportController`, `DeSailorsController`, `DistrictsController`, `EligibilityController`, `LogReportController`, `ReportController` (1,605 lines / 76.6 KB — the largest single PHP file in the repo), `SailorBatchConfigurationController`, `SailorBatchsController`, `SailorCentDistMappingController`, `SailorCentersController`, `SailorsController`, `SiteController`, `SubjectsController`, `TeController`, `UnionsController`, `UpozilasController`, `UserController`.
- **Models (12):** all `*Search` filter models (`SailorCentDistMappingSearch`, `DistrictsSearch`, `SailorBatchConfigurationSearch`, `UserSearch`, `SubjectsSearch`, `CanDesignationSearch`, `SailorBatchsSearch`, `EligibilitySearch`, `SailorCentersSearch`) plus `DeSailorsReference`, `SailorsReference`, `Report`. No backend-only AR models; all real domain AR classes live in `common/models`.
- **Views (77)** across `can-designation/`, `de-sailor-report/`, `de-sailors/`, `districts/`, `eligibility/`, `log-report/`, `report/`, `sailor-batch-configuration/`, `sailor-batchs/`, `sailor-cent-dist-mapping/`, `sailor-centers/`, `sailors/`, `site/`, `subjects/`, `unions/`, `upozilas/`, `user/`, `layouts/`. Several views retain large blocks of Gii-scaffold commented-out field lists (e.g. `views/sailors/_form.php` has an ~86-line `/* ... */` block of unused `$form->field(...)` calls for fields dropped from the live form; `views/sailors/index.php` has ~29 consecutive commented-out GridView columns) — scaffold cruft rather than deliberate dead features, low-priority cleanup.
- **Config:** `bootstrap.php`, `main.php` (148 lines, live), `main-local.php`, `params.php`, `params-local.php`, `test.php`, `test-local.php`, `codeception-local.php`, **plus 2 dead config snapshots** — see Dead Code below.
- **Tests:** only `tests/functional/LoginCest.php` (stock scaffold) — no domain coverage at all, the thinnest test presence of any tier.

---

### `console/` — CLI application

- **Purpose:** Migrations + console commands/models.
- **Size:** 60 KB, 6 PHP files (10 files total).
- **`controllers/`, `models/`:** empty except `.gitkeep` — no custom console commands were ever built; all CLI usage is the stock `yii migrate` etc.
- **`migrations/`:** **only 2 files** — `m130524_201442_init.php` (creates the stock Yii2 `user` table only — id, username, auth_key, password_hash, password_reset_token, email, status, created_at, updated_at) and `m190124_110200_add_verification_token_column_to_user_table.php` (adds `verification_token` to `user`). Neither touches any of the ~24 domain tables implied by `common/models` (`sailors`, `de_sailors`, `sailor_batchs`, `sailor_batch_configuration(_exam_date)`, `sailor_centers`, `sailor_cent_dist_mapping`, `eligibility`, `can_designation`, `can_eligibility_check_info`, `districts`, `upozilas`, `unions`, `subjects`, etc.). **This is strong, direct evidence that the production schema for this app was built and evolved via direct SQL / DB import rather than through Yii migrations** — anyone assessing modernization effort should not treat `console/migrations/` as a source of truth for the schema, and should expect no scriptable/replayable schema history to work from; the schema will need to be reverse-engineered from a live DB dump or from the AR model `@property` docblocks instead.

---

### Dead code / duplicates / stale scaffolding

| File | Duplicate of / issue | Verdict |
|---|---|---|
| `frontend/controllers/OnlinePaymentController_shurjo_pay.php` | `frontend/controllers/OnlinePaymentController.php` | **Confirmed dead.** Declares `namespace frontend\controllers; class OnlinePaymentController` — the *identical* class name and namespace as the live file. Yii2's controller resolution maps `OnlinePaymentController` → `OnlinePaymentController.php` by filename convention, so this file is never autoloaded/routed to; it can only ever be reached by an explicit, non-existent `require`. Confirmed no `require`/`include` of it anywhere in the tree. It also actively references `common\models\payment\AamarPay` and `common\models\payment\ShurjoPayment`, **neither of which exists** in `common/models/payment/` (only `SSLPayment.php` is present) — the file wouldn't even parse-execute successfully if it were ever invoked. This is the sailor-app equivalent of the officer-repo's `shurjo_pay_*` alternate-payment-gateway variants: an abandoned mid-migration attempt to add a second payment gateway (ShurjoPay/AamarPay) alongside the live SSLCommerz (`SSLPayment`) integration, left in the tree. 332 lines, 16 KB. Safe to delete once confirmed unreferenced by any deploy script. |
| `backend/config/09092025_main.php` | `backend/config/main.php` | **Confirmed dead.** Dated filename (`09092025` = 9 Sep 2025), 74 lines vs. live `main.php`'s 148 lines. Diff shows it's a pre-request-logging snapshot — missing the `on beforeRequest` global action-logging block and a commented-out `AccessControl` filter block that the live file has. Not `require`d anywhere (only `backend/config/main.php` is loaded, via `backend/web/index.php` / `backend/web/index-test.php`). Same dated-backup-file pattern as the officer repo's `routes/07_01_25_web.php` etc. |
| `backend/config/_main.php` | `backend/config/main.php` | **Confirmed dead.** Underscore-prefixed, 69 lines. Diff shows it additionally lacks `enableCsrfValidation => false` for a controller and the commented `AccessControl` block. Not referenced anywhere. |
| `frontend/config/_main.php` | `frontend/config/main.php` | **Confirmed dead.** Underscore-prefixed, 71 lines vs. live 77. Same pattern — missing `enableCsrfValidation => false` override and the `AccessControl` block. Not referenced anywhere. |
| `frontend/web/2501001.pdf`, `frontend/web/filename.pdf`, `frontend/web/barcode.png` | Accidentally committed generated output | Not referenced by filename anywhere in PHP source; look like one-off manual-test artifacts (application PDF export, roll-number barcode) from the admit-card/PDF-generation feature, dumped into the public web root and committed instead of gitignored. Low risk but easy accidental-exposure/clutter cleanup. |
| Gii-scaffold commented field/column blocks (`backend/views/sailors/_form.php`, `backend/views/sailors/index.php`, and similar) | N/A — not file duplication, just leftover scaffold cruft | Large blocks (~29–86 lines) of commented-out `$form->field(...)` / GridView column definitions for model fields that were later dropped from the live UI. Not wired to anything; harmless but noisy. |
| `dummy/` | Root-level `1.txt`–`4.txt`, `index.html`, `.htaccess` | Looks like a hosting/deployment smoke-test folder (verify the web server serves static files / PHP correctly), unrelated to the application. Not imported/referenced by any PHP code. |
| Test suites (`common/tests`, `frontend/tests`, `backend/tests`) | N/A | Only default Yii2-template scaffold tests exist (Login/Signup/Contact/Home/About). Zero tests for any real domain logic — `CheckEligibilityController`, `SailorCandidateController`, `DeSailorController`, `OnlinePaymentController`, batch/eligibility/reporting logic, etc. are all untested. |
| `composer.json` name/description | Still `yiisoft/yii2-app-advanced` / "Yii 2 Advanced Project Template" | Project was never renamed from the stock template identity — same "never customized the boilerplate" pattern as the officer repo's Laravel README. |

**Not found:** no `.bak` files, no `_old`/`_copy`/`" copy"`-suffixed files, no `~`/`.orig` backup files, and — unlike the officer repo — no parallel/duplicate admin-theme asset libraries (`backend/web/adminAsset` is the single admin theme, 17 MB; `frontend/web/navy` is the single frontend theme, 3.7 MB).

---

### Priority cleanup list (by confidence/impact)

1. **`frontend/controllers/OnlinePaymentController_shurjo_pay.php`** — delete; confirmed dead, unroutable (class-name collision with the live controller), and references nonexistent classes.
2. **`backend/config/09092025_main.php`, `backend/config/_main.php`, `frontend/config/_main.php`** — delete; confirmed unreferenced by any `web/index*.php` entry point, highest risk of an editor accidentally opening/editing the wrong config file.
3. **`frontend/web/2501001.pdf`, `filename.pdf`, `barcode.png`** — delete and add a `.gitignore` rule for generated PDFs/barcodes in `web/` to prevent recurrence.
4. **`console/migrations/` schema gap** — before any modernization effort, generate a fresh migration set (or authoritative schema dump) from the live database; the existing 2 migrations cover only the stock `user` table and cannot be trusted to reconstruct the ~24-table domain schema.
5. **Gii-scaffold commented blocks** (`backend/views/sailors/_form.php`, `index.php`, etc.) — low priority, cosmetic cleanup.
6. **`dummy/`** — confirm it's unused server-config-test scaffolding, then delete.
7. **Zero domain test coverage** across all three tiers — not a deletion item, but the single biggest risk factor for any refactor/modernization: there is no automated safety net for `SailorCandidateController`, `DeSailorController`, `CheckEligibilityController`, or `OnlinePaymentController`.
