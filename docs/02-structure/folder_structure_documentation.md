# Folder Structure Documentation — join-navy-sailor-legacy

Generated 2026-08-20. Complete folder-by-folder breakdown of the Yii 2.0 "advanced" project template ("Join Navy Sailor" application). Source material: `docs/00-inventories/repository_inventory.md`, `component_inventory.md`, `controller_inventory.md`, `model_inventory.md`, `view_inventory.md`, `css_inventory.md`, `javascript_inventory.md`, `middleware_inventory.md`, `route_inventory.md`, `service_inventory.md`, and direct filesystem reads (`find`/`ls`/`du`) performed for this document. `vendor/` is Composer-managed and noted in one line per the task scope; not analyzed further.

Architecture note: unlike the sibling `join-navy-officer-legacy` (Laravel/Blade, single app), this repo is a stock **Yii 2.0 advanced template** — three peer web/CLI application tiers (`common`, `frontend`, `backend`, `console`) sharing one codebase, each with its own `config/`, `web/` (or `runtime/`), and `tests/`. There is no single `app/` namespace; instead each tier has its own PSR-4 root (`common\`, `frontend\`, `backend\`, `console\`).

---

## Top level

### `common/`

**Folder Path:** `common/`
**Purpose:** Shared code layer used by both `frontend` and `backend` (and, for config, by `console`) — the Yii2-advanced-template's answer to Laravel's single `app/`.
**Responsibilities:** 568 KB, 57 PHP files (62 files total, `repository_inventory.md:53`). Houses every domain `ActiveRecord` model, mail view templates, static helper classes, one enum class, one service component, one widget, scaffold fixtures, and shared config consumed by every other tier.
**Dependencies:** Yii2 framework (`vendor/yiisoft/*`), consumed via `common\` PSR-4 namespace from both `frontend/config/main.php` and `backend/config/main.php` (config merge) and both tiers' `composer.json` autoload maps.
**Important Files:** None directly at this level — all content is in subdirectories below.
**Related Modules:** `frontend/` and `backend/` both merge `common/config/main.php` + `main-local.php` into their own config; `console/` merges `common/config/*` for CLI context.

### `frontend/`

**Folder Path:** `frontend/`
**Purpose:** Public-facing Yii web application — candidate/sailor self-service portal.
**Responsibilities:** 5.4 MB, 95 PHP files (146 files total, `repository_inventory.md:72`). Eligibility checker, Sailor/DE-Sailor multi-step application forms, online payment (SSLCommerz), candidate account auth, application status/history/download.
**Dependencies:** `common/models/*` (all domain AR models), `common/static/*`, `common/components/R2Storage.php`, its own `frontend/models/*` (form-only models), Yii2 framework.
**Important Files:** None directly at this level — see subfolders.
**Related Modules:** Root `.htaccess` routes the whole domain root here (`repository_inventory.md:45`) — this is the default-served tier; `backend/` is reached at its own subpath/subdomain via its own `web/` entry point.

### `backend/`

**Folder Path:** `backend/`
**Purpose:** Admin/back-office Yii web application.
**Responsibilities:** 18 MB, 131 PHP files (292 files total, `repository_inventory.md:85`) — by far the largest tier, dominated by 17 MB of vendored admin-theme assets in `web/adminAsset/`. CRUD + reporting for sailors, DE-sailors, batches, eligibility rules, exam centers, geography reference data (districts/unions/upozilas), subjects, users.
**Dependencies:** `common/models/*` (all real domain AR classes; `backend/models/*` contains only `*Search` filter models + 2 reference/report helper models, no real AR classes of its own), Yii2 framework, `backend/web/adminAsset/` (Hyper-style Bootstrap admin theme).
**Important Files:** None directly at this level — see subfolders.
**Related Modules:** `common/models/` (shared domain layer), `frontend/` (writes the records `backend/` administers).

### `console/`

**Folder Path:** `console/`
**Purpose:** Console (CLI) application tier.
**Responsibilities:** 60 KB, 6 PHP files (10 files total, `repository_inventory.md:97`). `controllers/` and `models/` subfolders are effectively empty (`.gitkeep` only, `repository_inventory.md:98`) — no custom Artisan-equivalent commands were ever built; all CLI usage is stock `yii migrate` etc.
**Dependencies:** `common/config/*` (merged for CLI context), Yii2 console framework.
**Important Files:** See `console/migrations/` below — only 2 migration files exist in the whole repo.
**Related Modules:** Root `yii` / `yii_test` scripts bootstrap this tier; `codeception.yml` writes test output to `console/runtime/output`.

### `vendor/`

**Folder Path:** `vendor/`
**Purpose:** Composer-managed third-party PHP package installation directory (Yii2 core + extensions).
**Responsibilities:** 298 MB — excluded from scope (`repository_inventory.md:12,31`).
**Dependencies:** Regenerated via `composer install` from `composer.json`/`composer.lock`.
**Important Files:** Not itemized — framework-managed.
**Related Modules:** Every tier's `config/bootstrap.php` and autoloading.

### `dummy/`

**Folder Path:** `dummy/`
**Purpose:** Leftover deployment/hosting smoke-test folder — 5 placeholder files (`1.txt`–`4.txt`, `index.html`) plus `.htaccess`.
**Responsibilities:** 8 KB. Not part of the application; not imported/referenced by any PHP code (`repository_inventory.md:32,113`).
**Dependencies:** None.
**Important Files:**
- `index.html`, `.htaccess`, `1.txt`–`4.txt` — static probe files, likely used once to verify the web server serves static content/PHP correctly on a new host.
**Related Modules:** None. Candidate for deletion once confirmed unused (`repository_inventory.md:128`).

### `environments/`

**Folder Path:** `environments/`
**Purpose:** Environment-config-switching source data consumed by the root `init` tool.
**Responsibilities:** 244 KB — `dev/` (140 KB) and `prod/` (96 KB) variants of `common/`, `backend/`, `console/`, `frontend/` config trees, selected via `environments/index.php`.
**Dependencies:** Read by `init` / `init.bat`.
**Important Files:**
- `index.php` — manifest of named environments (`Development` → `dev/`, `Production` → `prod/`), each declaring which paths to `setWritable` (runtime/assets dirs), `setExecutable` (`yii`, `yii_test`), and which config files need a generated `cookieValidationKey` (`repository_inventory.md:41`).
- `dev/{common,frontend,backend,console}/` — dev-environment config overrides copied over the live tier configs on `php init --env=Development`.
- `prod/{common,frontend,backend,console}/` — production-environment equivalents.
**Related Modules:** `common/config/`, `frontend/config/`, `backend/config/`, `console/config/` (all are overwritten by whichever environment's tree `init` copies in); `init`/`init.bat`.

### `vagrant/`

**Folder Path:** `vagrant/`
**Purpose:** Vagrant provisioning scripts, nginx vhost template, and local config example — for a local VM dev environment (separate from the `docker-compose.yml` path).
**Responsibilities:** 56 KB.
**Dependencies:** `Vagrantfile` (root) invokes these provisioning scripts; `vagrant-hostmanager` plugin for hostnames.
**Important Files:**
- `provision/once-as-root.sh`, `provision/once-as-vagrant.sh`, `provision/always-as-root.sh`, `provision/common.sh`, `provision/provision.awk` — shell provisioning scripts run at VM boot.
- `nginx/app.conf` — nginx vhost template for `y2aa-frontend.test` / `y2aa-backend.test`.
- `config/vagrant-local.example.yml` — example local override config (actual `vagrant-local.yml` is gitignored).
**Related Modules:** Root `Vagrantfile` (defines the VM using these files).

### Loose top-level files

**Folder Path:** repo root
**Purpose:** Composer/Docker/Vagrant/Yii bootstrapping and stock template metadata.
**Important Files:**
- `composer.json` / `composer.lock` — declares the project as `yiisoft/yii2-app-advanced`, description "Yii 2 Advanced Project Template" — **the stock template name/description, never renamed for this project** (`repository_inventory.md:43,115`).
- `docker-compose.yml` — defines 3 services: `frontend` (port 20080), `backend` (port 21080), both built from their own tier `Dockerfile`s with the whole repo bind-mounted; `mysql:5.7` (db `yii2advanced`); a commented-out `pgsql` alternative (`repository_inventory.md:39`).
- `Vagrantfile` — defines a local VM with `y2aa-frontend.test` / `y2aa-backend.test` hostnames via `vagrant-hostmanager`, provisioned by `vagrant/provision/*.sh` (`repository_inventory.md:40`).
- `init` / `init.bat` — environment-initialization tool (PHP CLI / Windows wrapper). Copies environment-specific config from `environments/{dev,prod}/` over the live tier configs, sets file permissions, generates cookie-validation keys. Interactive (`php init`) or scripted (`php init --env=Development --overwrite=n`) (`repository_inventory.md:36`).
- `yii` / `yii.bat` — console application bootstrap (`YII_ENV=dev` by default) — merges `common` + `console` configs and runs `yii\console\Application` (`repository_inventory.md:37`).
- `yii_test` / `yii_test.bat` — same as `yii`, but also merges `common/config/test.php` + `test-local.php` and `console/config/test.php` + `test-local.php`, for running the app under the `test` environment (used by Codeception) (`repository_inventory.md:38`).
- `requirements.php` — stock Yii2 server-requirements checker script (CLI or copy-to-webroot).
- `codeception.yml` — root Codeception config; includes `common`, `frontend`, `backend` suites, writes output to `console/runtime/output`.
- `.htaccess` — root Apache rewrite: routes everything to `frontend/web/` (front controller), making `frontend` the default-served tier at the domain root (`repository_inventory.md:45`).
- `LICENSE.md`, `README.md` — stock Yii2 advanced-template license/readme; README's directory-structure section is stale — it lists a `widgets/` dir under `frontend/` for this project, but `frontend/components/` is what's actually used instead (`repository_inventory.md:46`, confirmed via `view_inventory.md` and direct `find`).

---

## `common/` — Shared code

### `common/components/`

**Folder Path:** `common/components/`
**Purpose:** Yii2 `Component` subclasses registered as app-wide services in config.
**Responsibilities:** 1 file, 12 KB.
**Dependencies:** `Aws\S3\S3Client` (via `vendor/`), registered as the `r2Storage` app component in `common/config/main.php:19-27`, inherited by both `frontend` and `backend`.
**Important Files:**
- `R2Storage.php` (217 lines) — wraps Cloudflare R2 (S3-compatible) object storage for candidate photo/document uploads (`uploadFile()`/`fileExists()`/`deleteFile()`), plus a bolted-on NDJSON audit-log mechanism (`upsertCandidateLog()`/`actionLog()`/`getLogFileContents()`) writing `logs/logs.ndjson` to the same bucket — widely called across `frontend/controllers/` and `backend/controllers/` (`component_inventory.md` §2).
**Related Modules:** `common/config/main.php` (component registration), `frontend/controllers/*`, `backend/controllers/*` (via `Yii::$app->r2Storage`).

### `common/config/`

**Folder Path:** `common/config/`
**Purpose:** Centralized configuration shared by all three tiers.
**Responsibilities:** 40 KB, 9 files. No dead/duplicate config files here (unlike `backend/config/` and `frontend/config/` — see below).
**Dependencies:** Merged into `frontend/config/main.php`, `backend/config/main.php`, and `console/config/main.php` at bootstrap.
**Important Files:**
- `bootstrap.php` — class-alias/bootstrap definitions loaded first.
- `main.php` — shared component definitions (incl. `r2Storage`), common modules/behaviors.
- `main-local.php` — environment-specific overrides (DB credentials, etc.), gitignored/environment-supplied.
- `params.php` / `params-local.php` — shared application parameters.
- `test.php` / `test-local.php` / `codeception-local.php` — test-environment variants, loaded by `yii_test`/Codeception.
**Related Modules:** `frontend/config/main.php`, `backend/config/main.php`, `console/config/main.php` (all merge these files in).

### `common/enumClass/`

**Folder Path:** `common/enumClass/`
**Purpose:** PHP enum-style constant classes.
**Responsibilities:** 1 file, 8 KB.
**Dependencies:** Referenced from controllers/views wherever status codes need a name.
**Important Files:**
- `Status.php` — shared status-code constants (e.g. active/inactive, approval states) used across models and views.
**Related Modules:** `common/models/*` (status columns), `backend/views/*` (status badges/filters).

### `common/fixtures/`

**Folder Path:** `common/fixtures/`
**Purpose:** Codeception/Yii2 test data fixtures.
**Responsibilities:** 1 file, 8 KB — only the default Yii2 scaffold fixture; no domain fixtures exist for `Sailors`, `DeSailors`, `SailorBatchs`, etc. (`repository_inventory.md:63`).
**Dependencies:** `common/tests/unit/models/LoginFormTest.php` (the only real test that could use fixtures).
**Important Files:**
- `UserFixture.php` — stock scaffold fixture for the `user` table.
**Related Modules:** `common/tests/`, `common/models/User.php`.

### `common/mail/`

**Folder Path:** `common/mail/`
**Purpose:** HTML/text email view templates.
**Responsibilities:** 32 KB, 6 files — password-reset and email-verification templates, plus a shared layout pair.
**Dependencies:** Rendered via Yii2's `mailer` component (Symfony Mailer, per `composer.json`), invoked from `frontend/models/PasswordResetRequestForm.php`, `SignupForm.php`, `ResendVerificationEmailForm.php`, `VerifyEmailForm.php`.
**Important Files:**
- `passwordResetToken-html.php` / `passwordResetToken-text.php` — password-reset email pair.
- `emailVerify-html.php` / `emailVerify-text.php` — signup email-verification pair.
- `layouts/html.php` / `layouts/text.php` — shared mail layout wrappers.
**Related Modules:** `frontend/models/*Form.php` (trigger these emails), `frontend/controllers/SiteController.php` and `CandidateController.php` (auth/reset flows).

### `common/models/`

**Folder Path:** `common/models/`
**Purpose:** Core domain layer — every `yii\db\ActiveRecord` model shared by both `frontend` and `backend`, the direct analogue of the officer-repo's `app/Models/`.
**Responsibilities:** 352 KB, 29 files (27 top-level models + `payment/SSLPayment.php` + `scopeQuery/SailorBatchs.php`, `repository_inventory.md:57`, `model_inventory.md:1`). Represents the full domain: recruitment batches (`SailorBatchs`, `SailorBatchConfiguration(+ExamDate)`), candidates (`Sailors`, `DeSailors` + their `*Search` classes), designations (`CanDesignation`), eligibility (`Eligibility`, `CanEligibilityCheckInfo`), exam centers (`SailorCenters`, `SailorCentDistMapping`), geography lookups (`Districts`, `Unions`, `Upozilas`), academic reference (`Subjects`), users (`User`), plus account/form models (`LoginForm`, `ChangePassword`, `ResetPassword`, `SendSms`, `Session`, `DownloadDocuments`).
**Dependencies:** `yii\db\ActiveRecord` base class; no schema source of truth in-repo (see oddity below).
**Important Files:**
- `Sailors.php` / `SailorsQuery.php` / `SailorsSearch.php` — core general-sailor candidate application record and its query/search-filter companions.
- `DeSailors.php` / `DeSailorsSearch.php` — parallel Direct-Entry sailor candidate application record.
- `User.php` — admin/system authenticatable user model.
- `SailorBatchConfiguration.php` / `SailorBatchConfigurationQuery.php` / `SailorBatchConfigurationExamDate.php` — recruitment batch configuration + exam-date sub-record.
- `payment/SSLPayment.php` — the one payment-gateway model that actually exists; `frontend/controllers/OnlinePaymentController.php` also imports `AamarPay` and `ShurjoPayment` classes from this namespace that were **never created** (broken references, `controller_inventory.md`).
- `scopeQuery/SailorBatchs.php` — query-scope helper, oddly nested one level under `models/scopeQuery/` rather than alongside `SailorBatchs.php` itself.
**Oddity:** Only **1 of ~29 tables** implied by these models (`user`) has any migration history in `console/migrations/` — the schema for every other table was built via direct SQL/import, not Yii migrations (`repository_inventory.md:19,99`, `model_inventory.md:4`). Even `user` has drifted: application code reads/writes columns (`birth_registration_no`, `phone_no`, `dob`, `user_group`, `user_type`, `os`, `login_zone`, `created_dt`, `updated_dt`) that neither migration file creates.
**Related Modules:** `frontend/controllers/*` and `backend/controllers/*` (all CRUD flows against these models), `backend/models/*Search` (admin grid filters wrapping these), `console/migrations/` (incomplete schema history).

### `common/static/`

**Folder Path:** `common/static/`
**Purpose:** Static-method helper classes — Yii2-advanced-template's closest equivalent to the officer-repo's `app/Services/`.
**Responsibilities:** 44 KB, 5 files, used throughout both tiers' controllers.
**Dependencies:** External curl endpoints (`StaticMethod`), consumed via static calls (`ClassName::method()`), not DI.
**Important Files:**
- `StaticMethod.php` (639 lines) — largest helper class: miscellaneous shared utilities plus curl calls to an external `spgCurlPost`-style endpoint.
- `SendSms.php` — SMS-sending helper (paired with `common/models/SendSms.php`).
- `AES256CTR.php` — field-level AES-256-CTR encryption for sensitive candidate data.
- `DataEncryption.php` — additional encryption/decryption helper.
- `Constants.php` — application-wide constant definitions.
**Related Modules:** `frontend/controllers/*`, `backend/controllers/*` (both call into these statically), `common/models/SendSms.php`.

### `common/widgets/`

**Folder Path:** `common/widgets/`
**Purpose:** Reusable Yii2 `Widget` subclasses.
**Responsibilities:** 1 file, 8 KB.
**Dependencies:** `\yii\bootstrap5\Widget` / `\yii\bootstrap5\Alert`.
**Important Files:**
- `Alert.php` (76 lines) — stock Yii2-advanced-template flash-message widget, unmodified from scaffold. **Effectively dead in the live app**: only called from `backend/views/layouts/main.php`, which is itself an unused leftover layout — the live default layouts (`backend` admin shell, `frontend` `mainNavy`) never call `Alert::widget()`. All 21 `setFlash()` call sites across the app instead render via a hand-rolled inline `Yii::$app->session->hasFlash()`/`getFlash()` pattern repeated in 19 view files (`component_inventory.md` §1).
**Related Modules:** `backend/views/layouts/main.php` (only caller, itself dead), `frontend/views/layouts/mainNavy.php` (imports it but never invokes it).

### `common/tests/`

**Folder Path:** `common/tests/`
**Purpose:** Codeception unit tests for shared/common-layer classes.
**Responsibilities:** 52 KB. Only 1 real test file exists — stock scaffold coverage only, no domain-model tests (`repository_inventory.md:64`).
**Dependencies:** `common/fixtures/UserFixture.php`, Codeception `unit.suite.yml`.
**Important Files:**
- `unit/models/LoginFormTest.php` — the only real test in this folder, covers `common/models/LoginForm.php`.
- `_bootstrap.php`, `unit.suite.yml`, `_data/user.php`, `_support/UnitTester.php` — Codeception scaffold.
**Related Modules:** `common/models/LoginForm.php`, `common/fixtures/`; part of the root `codeception.yml` suite aggregation.

---

## `frontend/` — Public candidate-facing application

### `frontend/assets/`

**Folder Path:** `frontend/assets/`
**Purpose:** Yii2 `AssetBundle` definitions — declares which CSS/JS files get registered on each page and their dependency order.
**Responsibilities:** 12 KB, 2 files.
**Dependencies:** `frontend/web/navy/` (the theme files these bundles point at), `frontend/web/css/`.
**Important Files:**
- `AppAsset.php` — main frontend asset bundle (base CSS/JS).
- `AppNavyAsset.php` — "Navy" theme-specific asset bundle, registered by the live `mainNavy` layout.
**Related Modules:** `frontend/views/layouts/` (registers these bundles), `frontend/web/navy/`.

### `frontend/components/`

**Folder Path:** `frontend/components/`
**Purpose:** Despite the folder name, these are Yii2 `Widget` subclasses (`extends \yii\base\Widget`), not app `Component`s — a naming mismatch versus `common/components/` (`component_inventory.md` Summary).
**Responsibilities:** 24 KB, 4 files (2 widget classes + 2 partial views).
**Dependencies:** Rendered into `frontend/components/views/*` partials.
**Important Files:**
- `SupportNo.php` → renders `views/support_no.php` — displays support/helpline contact info.
- `StepAndSupport.php` → renders `views/step_and_support.php` — multi-step wizard progress indicator paired with support text.
**Related Modules:** `frontend/views/candidate/`, `de-sailor/`, `sailor-candidate/`, `check-eligibility/` (wizard views embedding these widgets).

### `frontend/config/`

**Folder Path:** `frontend/config/`
**Purpose:** Frontend-tier-specific configuration.
**Responsibilities:** 44 KB, 10 files — including 1 dead config snapshot.
**Dependencies:** Merges in `common/config/main.php` + `main-local.php`.
**Important Files:**
- `main.php` (77 lines, live) — defines the `mainNavy` default layout, frontend-specific components/modules; loaded by `frontend/web/index.php`.
- `main-local.php`, `params.php`, `params-local.php`, `bootstrap.php`, `test.php`, `test-local.php`, `codeception-local.php` — standard tier config set.
- **Dead file:** `_main.php` (71 lines) — underscore-prefixed, unreferenced by any `web/index*.php` entry point; diff vs. live `main.php` shows it's missing an `enableCsrfValidation => false` override and a commented-out `AccessControl` filter block (`repository_inventory.md:110`).
**Related Modules:** `frontend/web/index.php` / `index-test.php` (load `main.php` only), `common/config/`.

### `frontend/controllers/`

**Folder Path:** `frontend/controllers/`
**Purpose:** Request-handling layer for the public candidate portal.
**Responsibilities:** 224 KB, 10 files — candidate account auth, eligibility checker, Sailor/DE-Sailor application wizards, payment, ajax/api endpoints (`repository_inventory.md:73`, `controller_inventory.md`).
**Dependencies:** `common/models/*`, `frontend/models/*` (form models), `common/static/*`, `common/components/R2Storage.php`, `frontend/views/*`.
**Important Files:**
- `SailorCandidateController.php` (64,463 B / 1,195 lines) — the largest controller in the app; general Sailor candidate multi-step application wizard.
- `DeSailorController.php` (60,354 B / 1,118 lines) — parallel Direct-Entry Sailor application wizard.
- `CheckEligibilityController.php` (23,293 B) — pre-application eligibility-check wizard.
- `CandidateController.php` (19,474 B) — candidate account/dashboard actions.
- `OnlinePaymentController.php` (12,638 B) — live SSLCommerz payment integration (`ssl-*` actions); also imports non-existent `AamarPay`/`ShurjoPayment` classes for 3 unreachable actions (`actionPayment`, `actionPaymentResponseDeSailor`, `actionPaymentResponseSailor`) that throw a Fatal Error if ever hit (`controller_inventory.md`).
- `SiteController.php` — static/marketing pages, generic auth (signup, login, email verification, password reset).
- `MyApplicationController.php` — logged-in candidate's application status/history.
- `AjaxController.php` — AJAX lookups (districts/unions/upozilas cascades, etc.).
- **Dead duplicate:** `OnlinePaymentController_shurjo_pay.php` (16,007 B) — declares the identical `frontend\controllers\OnlinePaymentController` class name/namespace as the live file; Yii2's filename-convention autoloader never resolves to it, so it's unreachable dead code. References `common\models\payment\AamarPay` and `ShurjoPayment`, neither of which exists (`repository_inventory.md:107`, `controller_inventory.md`).
- **Inert stub:** `ApiController.php` (698 B) — extends `\yii\rest\Controller` but has zero `action*` methods and no `actions()` override; any request 404s.
**Related Modules:** `frontend/views/*`, `common/models/*`, `frontend/models/*`.

### `frontend/media/`

**Folder Path:** `frontend/media/`
**Purpose:** Small logo/media assets referenced from views (separate from the large `web/navy/` theme asset library).
**Responsibilities:** 20 KB, 3 files.
**Important Files:**
- `main_logo.png`, `main-logo.png` — two near-duplicate logo filenames (underscore vs. hyphen).
- `index.html` — stock Yii2 directory-listing-prevention stub.
**Related Modules:** `frontend/views/layouts/`.

### `frontend/models/`

**Folder Path:** `frontend/models/`
**Purpose:** Frontend-only form models (not `ActiveRecord`, no DB table) — account/contact-flow validation classes.
**Responsibilities:** 32 KB, 7 files. No frontend-only AR models; all domain data models live in `common/models/`.
**Dependencies:** `common/models/User.php` (auth), `common/mail/*` templates (via these forms' send-email methods).
**Important Files:**
- `SignupForm.php` — candidate account signup validation.
- `ResetPasswordForm.php`, `PasswordResetRequestForm.php` — password-reset flow.
- `VerifyEmailForm.php`, `ResendVerificationEmailForm.php` — email-verification flow.
- `ContactForm.php` — generic contact form (stock scaffold page).
- `RefundForm.php` — **0-byte empty file** (`repository_inventory.md`, `model_inventory.md:1`), a scaffold that was never implemented.
**Related Modules:** `frontend/controllers/SiteController.php`, `CandidateController.php`; `common/mail/`.

### `frontend/runtime/`

**Folder Path:** `frontend/runtime/`
**Purpose:** Runtime-writable cache/log directory (framework-managed, Yii2 standard).
**Responsibilities:** 8 KB — effectively empty in the repo snapshot, only `.gitignore` tracked.
**Important Files:** `.gitignore`.
**Related Modules:** `frontend/config/main.php` (log/cache component targets).

### `frontend/tests/`

**Folder Path:** `frontend/tests/`
**Purpose:** Codeception test suites for the frontend tier — `functional/`, `acceptance/`, `unit/`.
**Responsibilities:** 140 KB. All default Yii2 scaffold tests (`Login`, `Signup`, `VerifyEmail`, `ResendVerificationEmail`, `ResetPassword`, `Contact`, `Home`, `About` Cests/unit tests) — **zero tests** for `CheckEligibilityController`, `SailorCandidateController`, `DeSailorController`, or `OnlinePaymentController`, i.e. the entire real application logic is untested (`repository_inventory.md:78`).
**Dependencies:** `frontend/codeception.yml`, `frontend/tests/_support/`.
**Important Files:** Standard Codeception scaffold structure (`_bootstrap.php`, `_data/`, `_support/`, `_output/`).
**Related Modules:** Root `codeception.yml` (suite aggregation).

### `frontend/views/`

**Folder Path:** `frontend/views/`
**Purpose:** Plain-PHP view templates for the public portal (no Blade — `$this->render()`/`renderPartial()`, `ActiveForm`).
**Responsibilities:** 784 KB, 39 files across 8 subfolders (`view_inventory.md`).
**Dependencies:** `frontend/views/layouts/mainNavy.php` (shared shell, extended by most views), `frontend/controllers/*`.
**Important Files:**
- `sailor-candidate/` (9 files) and `de-sailor/` (10 files) — **near-parallel duplicate view trees**, same view set once per applicant type (general Sailor vs. Direct-Entry Sailor); a structural duplication pattern rather than accidental copy-paste (`repository_inventory.md:75`). Includes very large PDF/print-preview templates: `de-sailor/personal_info.php` (71.8 KB), `sailor-candidate/personal_info.php` (68.4 KB), `de-sailor/candidate/application_form_pdf.php` (55.8 KB), `sailor-candidate/candidate/application_form_pdf.php` (55.2 KB).
- `check-eligibility/` (3 files) — public eligibility-checker wizard, no login required.
- `candidate/` (4 files) — candidate account auth (login, signup, password reset/change).
- `my-application/` (1 file) — logged-in candidate's application list/status.
- `online-payment/` (1 file) — unmodified Yii2 scaffold stub.
- `site/` (10 files) — static/marketing pages, generic auth.
- `layouts/mainNavy.php` — shared public-site shell; imports `Breadcrumbs`, `Nav`, `NavBar`, and `common\widgets\Alert` but never calls any of them — dead scaffold imports left over from customizing the Yii2-basic-template default layout (`component_inventory.md` §1).
**Related Modules:** `frontend/controllers/*`, `frontend/components/views/` (2 partials embedded across these wizard views).

### `frontend/web/`

**Folder Path:** `frontend/web/`
**Purpose:** Web root — Yii2 front controller plus all publicly served static assets for this tier.
**Responsibilities:** 4.1 MB total; `navy/` theme subfolder alone is 3.7 MB.
**Dependencies:** `frontend/config/main.php` (via `index.php`).
**Important Files:**
- `index.php` — Yii2 front controller (production entry point).
- `index-test.php` — test-environment entry point (used by acceptance tests).
- `.htaccess`, `robots.txt`, `favicon.ico` — standard static site files.
- `navy/` (3.7 MB: `css/` 524 KB, `fonts/` 2.7 MB, `images/` 336 KB, `js/` 168 KB) — the single frontend theme, registered via `frontend/assets/AppNavyAsset.php`.
- `assets/`, `css/` — small additional Yii2-published-asset and custom-CSS folders.
**Oddity — stray committed files:** `2501001.pdf` (200,776 B), `filename.pdf` (200,776 B, different content from the former despite identical size), and `barcode.png` (172 B) sit directly in `web/` alongside the real assets. None are referenced by literal filename anywhere in the PHP source — they look like one-off generated PDFs/barcodes from a manual test of the application-form/roll-number PDF-generation feature, committed instead of `.gitignore`d (`repository_inventory.md:77,111`). Flagged as a low-risk cleanup/exposure item (`repository_inventory.md:125`).
**Related Modules:** `frontend/views/layouts/mainNavy.php` (asset() references into `navy/`), `frontend/config/main.php`.

---

## `backend/` — Admin/back-office application

### `backend/assets/`

**Folder Path:** `backend/assets/`
**Purpose:** Yii2 `AssetBundle` definitions for the admin panel.
**Responsibilities:** 12 KB, 2 files.
**Dependencies:** `backend/web/adminAsset/` (the theme files these bundles point at).
**Important Files:**
- `AppAsset.php` — base backend asset bundle.
- `AppAdminAsset.php` — "Hyper" admin-theme asset bundle, registered by the live admin layout.
**Related Modules:** `backend/views/layouts/` (registers these bundles), `backend/web/adminAsset/`.

### `backend/config/`

**Folder Path:** `backend/config/`
**Purpose:** Backend-tier-specific configuration.
**Responsibilities:** 52 KB, 11 files — including **2 dead config snapshots**, the most of any tier.
**Dependencies:** Merges in `common/config/main.php` + `main-local.php`.
**Important Files:**
- `main.php` (148 lines, live) — defines the admin default layout, an `on beforeRequest` global action-logging block, and a commented-out `AccessControl` filter block; loaded by `backend/web/index.php` / `index-test.php`.
- `main-local.php`, `params.php`, `params-local.php`, `bootstrap.php`, `test.php`, `test-local.php`, `codeception-local.php` — standard tier config set.
- **Dead file:** `09092025_main.php` (74 lines) — dated filename (9 Sep 2025 snapshot), missing the `beforeRequest` logging block and `AccessControl` comment block that live `main.php` has; not `require`d anywhere (`repository_inventory.md:108`).
- **Dead file:** `_main.php` (69 lines) — underscore-prefixed, additionally lacks `enableCsrfValidation => false` for a controller; not referenced anywhere (`repository_inventory.md:109`).
**Related Modules:** `backend/web/index.php` / `index-test.php` (load `main.php` only), `common/config/`.

### `backend/controllers/`

**Folder Path:** `backend/controllers/`
**Purpose:** Request-handling layer for the admin panel — largest controller set of the app.
**Responsibilities:** 248 KB, 21 files — CRUD controllers for nearly every domain entity plus reporting/logging/user-management controllers (`repository_inventory.md:86`).
**Dependencies:** `common/models/*`, `backend/models/*Search`, `backend/views/*`.
**Important Files:**
- `ReportController.php` (1,605 lines / 76.6 KB) — **the largest single PHP file in the repo**; general reporting (candidate filters, center/district candidate lists, monitoring, payment, exam-date checks) across 15 views.
- `SailorsController.php`, `DeSailorsController.php` — CRUD/review of general and Direct-Entry sailor candidates; both have fully commented-out `actionCreate()`/`actionDelete()` methods in places (`SailorsController` — create+delete unreachable; presumably records are created only via the frontend candidate flow).
- `UserController.php` — admin user CRUD; `actionCreate()` renders a nonexistent `create.php` view (broken reference); `actionDelete()` is fully commented out.
- `SailorBatchsController.php`, `SailorBatchConfigurationController.php`, `SailorCentersController.php`, `SailorCentDistMappingController.php`, `EligibilityController.php`, `CanDesignationController.php`, `SubjectsController.php`, `DistrictsController.php`, `UnionsController.php`, `UpozilasController.php` — one CRUD controller per reference-data entity.
- `DeSailorBranchController.php` — **non-functional as shipped**: `backend/views/de-sailor-branch/` does not exist anywhere in the repo, so all 4 of its `render()` calls are broken (`controller_inventory.md`).
- `DeSailorReportController.php`, `LogReportController.php` — reporting controllers.
- `AjaxController.php` — admin-side AJAX lookups.
- **Empty stubs:** `BulkCheckController.php` and `TeController.php` — both have empty class bodies with unused imports; `TeController.php`'s unused `PhpOffice\PhpWord` imports suggest an abandoned Word-doc export feature (the feature that did ship lives in `ReportController::actionReferenceCandidateWord()` instead) (`controller_inventory.md`).
**Related Modules:** `backend/views/*`, `backend/models/*Search`, `common/models/*`.

### `backend/models/`

**Folder Path:** `backend/models/`
**Purpose:** Backend-only filter/search and reporting-support models — no real AR classes; all domain data lives in `common/models/`.
**Responsibilities:** 64 KB, 12 files, all `*Search` filter models (wrapping `common/models/*` for `GridView`/`ActiveDataProvider` use) plus 2 reference/report helper models.
**Important Files:**
- `SailorsReference.php`, `DeSailorsReference.php` — reference-candidate lookup/report support models.
- `Report.php` — reporting query support model, backs `ReportController`'s 15 report views.
- `*Search` classes (`SailorBatchsSearch`, `SailorBatchConfigurationSearch`, `SailorCentersSearch`, `SailorCentDistMappingSearch`, `DistrictsSearch`, `EligibilitySearch`, `CanDesignationSearch`, `SubjectsSearch`, `UserSearch`) — one per admin CRUD grid. Note: `UnionsSearch` and `UpozilasSearch` are the two exceptions — they live in `common/models/`, not here.
**Related Modules:** `backend/controllers/*` (each `*Search` model is instantiated in its matching controller's `actionIndex()`), `common/models/*` (the AR classes being filtered).

### `backend/runtime/`

**Folder Path:** `backend/runtime/`
**Purpose:** Runtime-writable cache/log directory (framework-managed, Yii2 standard).
**Responsibilities:** 8 KB — effectively empty in the repo snapshot, only `.gitignore` tracked.
**Important Files:** `.gitignore`.
**Related Modules:** `backend/config/main.php` (log/cache component targets).

### `backend/tests/`

**Folder Path:** `backend/tests/`
**Purpose:** Codeception test suite for the backend tier.
**Responsibilities:** 68 KB. **Thinnest test presence of any tier** — only `functional/LoginCest.php` (stock scaffold), no domain coverage at all (`repository_inventory.md:90`).
**Important Files:**
- `functional/LoginCest.php` — admin login flow test.
- `_bootstrap.php`, `unit.suite.yml`, `functional.suite.yml`, `_data/login_data.php`, `_support/UnitTester.php`, `_support/FunctionalTester.php` — Codeception scaffold.
**Related Modules:** Root `codeception.yml`.

### `backend/views/`

**Folder Path:** `backend/views/`
**Purpose:** Plain-PHP view templates for the admin panel.
**Responsibilities:** 720 KB, 77 files across 17 subfolders + `layouts/` (`repository_inventory.md:88`, `view_inventory.md`).
**Dependencies:** `backend/views/layouts/` (shared admin shells), `backend/controllers/*`.
**Important Files:**
- `report/` (15 files) — largest view subfolder, backs `ReportController`.
- `de-sailors/` (8 files) and `sailors/` (6 files) — CRUD + reference-candidate sub-flow for each candidate type.
- `de-sailor-report/` (6 files) — reports scoped to DE-Sailor candidates.
- `layouts/` (5 files) — admin shell(s) + `include/` partials; the live default is the `admin`/`main` shell registered in `backend/config/main.php`, distinct from the dead unused `layouts/main.php` that only `common/widgets/Alert.php` calls.
- Remaining subfolders (`can-designation/`, `districts/`, `eligibility/`, `log-report/`, `sailor-batch-configuration/`, `sailor-batchs/`, `sailor-cent-dist-mapping/`, `sailor-centers/`, `site/`, `subjects/`, `unions/`, `upozilas/`, `user/`) — 3 files each in the standard `create/edit/index/view`-ish CRUD pattern.
- **Scaffold cruft:** `sailors/_form.php` retains an ~86-line commented-out block of unused `$form->field(...)` calls for dropped fields; `sailors/index.php` has ~29 consecutive commented-out `GridView` columns — leftover Gii-scaffold cruft, harmless but noisy (`repository_inventory.md:88,112`).
- **Broken references:** `backend/views/de-sailor-branch/` does not exist (breaks `DeSailorBranchController`); `user/create.php` and `de-sailors/create.php` don't exist despite their controllers' `actionCreate()` rendering them (`controller_inventory.md`).
**Related Modules:** `backend/controllers/*`.

### `backend/web/`

**Folder Path:** `backend/web/`
**Purpose:** Web root — Yii2 front controller plus all publicly served static assets for the admin tier.
**Responsibilities:** 17 MB — by far the largest single subfolder in the whole in-scope repo, almost entirely the vendored admin theme.
**Dependencies:** `backend/config/main.php` (via `index.php`).
**Important Files:**
- `index.php` — Yii2 front controller (production entry point).
- `index-test.php` — test-environment entry point.
- `.htaccess`, `robots.txt`, `favicon.ico` — standard static site files.
- `adminAsset/` — **17 MB "Hyper"-style Bootstrap admin theme** (`css/` 828 KB, `fonts/` 13 MB, `images/` 1000 KB, `js/` 1004 KB, `vendor/` 628 KB) — a single admin theme, registered via `backend/assets/AppAdminAsset.php`. Unlike the sibling officer repo (which carries two parallel admin themes, `admin_v1` + `adminAsset`), this repo has only one admin theme bundle, but its `fonts/` subfolder alone (13 MB, mostly icon-font families) accounts for the majority of the tier's total size.
- `assets/`, `css/` — small additional Yii2-published-asset and custom-CSS folders.
**Related Modules:** `backend/views/layouts/` (asset() references into `adminAsset/`), `backend/config/main.php`.

---

## `console/` — Console (CLI) application

### `console/config/`

**Folder Path:** `console/config/`
**Purpose:** Console-tier-specific configuration.
**Responsibilities:** Small — `bootstrap.php`, `main.php`, `params.php`, `test.php`, `.gitignore`. No dead/duplicate config files here.
**Dependencies:** Merges in `common/config/*`; loaded by the root `yii`/`yii_test` scripts.
**Important Files:**
- `main.php` — console application config (migration table namespace, console-only components).
**Related Modules:** Root `yii`/`yii_test` scripts, `common/config/`.

### `console/controllers/`

**Folder Path:** `console/controllers/`
**Purpose:** Custom Artisan-equivalent console commands.
**Responsibilities:** Effectively empty — only a `.gitkeep` file. No custom console commands were ever built; all CLI usage is stock `yii migrate` etc. (`repository_inventory.md:30,98`).
**Important Files:** `.gitkeep`.
**Related Modules:** None functional.

### `console/migrations/`

**Folder Path:** `console/migrations/`
**Purpose:** Versioned schema definitions (Yii2 migration framework).
**Responsibilities:** **Only 2 files** — the single biggest data-modeling risk flagged in the inventories (`repository_inventory.md:16,99`).
**Dependencies:** `yii\db\Migration` base class, run via `yii migrate`.
**Important Files:**
- `m130524_201442_init.php` — creates the stock Yii2 `user` table only (id, username, auth_key, password_hash, password_reset_token, email, status, created_at, updated_at).
- `m190124_110200_add_verification_token_column_to_user_table.php` — adds `verification_token` to `user`.
**Oddity:** Neither migration touches any of the ~24 domain tables implied by `common/models/` (`sailors`, `de_sailors`, `sailor_batchs`, `sailor_batch_configuration(_exam_date)`, `sailor_centers`, `sailor_cent_dist_mapping`, `eligibility`, `can_designation`, `can_eligibility_check_info`, `districts`, `upozilas`, `unions`, `subjects`, etc.). This is strong, direct evidence the production schema was built and evolved via direct SQL/DB import rather than through Yii migrations — anyone assessing modernization effort should not treat this folder as a source of truth for the schema, and should expect no scriptable/replayable schema history to work from (`repository_inventory.md:19,99`, `model_inventory.md:4`).
**Related Modules:** `common/models/*` (only `User.php` has any migration coverage).

### `console/models/`

**Folder Path:** `console/models/`
**Purpose:** Console-specific model classes (per Yii2-advanced-template convention).
**Responsibilities:** Effectively empty — only a `.gitkeep` file.
**Important Files:** `.gitkeep`.
**Related Modules:** None functional.

### `console/runtime/`

**Folder Path:** `console/runtime/`
**Purpose:** Runtime-writable directory, also the shared Codeception test-output target for the whole repo (`codeception.yml: paths.output: console/runtime/output`).
**Responsibilities:** Minimal — `.gitignore` tracked.
**Important Files:** `.gitignore`.
**Related Modules:** Root `codeception.yml` (all three tiers' test suites write here).

---

## Skipped per task scope (framework-managed, one-line notes only)

- **`vendor/`** — Composer-managed third-party PHP package installation directory (Yii2 core + extensions); 298 MB; regenerated via `composer install`.
- **`frontend/runtime/`, `backend/runtime/`, `console/runtime/`** — Yii2-managed cache/log/session runtime directories; regenerated automatically, only `.gitignore` tracked in the repo snapshot.
- **`*/tests/_output/`** — Codeception-managed test-run output directories; regenerated automatically.
