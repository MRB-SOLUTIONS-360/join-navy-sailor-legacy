# JavaScript Inventory — join-navy-sailor-legacy

## Summary

| Metric | Count |
|---|---|
| Total `.js` files (outside `vendor/`, published `web/assets/`) | **68** |
| Third-party vendor / theme library files | **67** |
| Custom application code files | **1** (`frontend/web/navy/js/main.js` — template boilerplate, no project-specific logic) |
| Modern bundler (npm/webpack/Vite) present | **No** — no `package.json` or `vite.config`/`webpack.config` anywhere in the repo |
| Legacy Yii2 `AssetBundle` + `<script src>` tag loading in actual use | **Yes** — this is how 100% of the live app's JS loads |

Note: counts are based on `frontend/web/navy/js/` (4 files) and `backend/web/adminAsset/js/` + `backend/web/adminAsset/vendor/` (61 + 3 = 64 files). `frontend/web/assets/` and `backend/web/assets/` are Yii2's runtime-published-asset cache directories and are **empty in the repo** (populated on demand by `AssetManager` from `vendor/bower-asset/*` and framework asset bundles) — excluded from the file count since nothing is checked in there. `frontend/assets/AppAsset.php` and `frontend/web/css/site.css` exist but the bundle is dead code (see below).

---

## Yii2 asset pipeline: how bundles and legacy `<script>` tags coexist

This is a stock **Yii2 2.0 advanced-template** app. There is no Vite/webpack/npm build anywhere — confirmed by `find . -iname package.json` (zero hits, excluding `vendor/`) and no `vite.config.*`/`webpack.config.*` at any level. All client JS is delivered by one of two Yii2-native mechanisms:

1. **`yii\web\AssetBundle::register($this)`** calls in layout files, which resolve `$css`/`$js` arrays (published from `web/` folders or from composer `bower-asset` packages) into `<link>`/`<script>` tags via the Yii2 `View`/`AssetManager`.
2. **Hardcoded `<script src="...">` tags** written directly into layout/view PHP, bypassing the asset-bundle system entirely (used for two of the four Hyper-theme admin script files — see below).

**Four asset bundles exist, but only three are ever registered:**

| Bundle | Registered by | Status |
|---|---|---|
| `frontend/assets/AppAsset.php` | *nobody* | **Dead code.** Stock Yii2 scaffolding (`css/site.css` + `yii\web\YiiAsset` + `yii\bootstrap5\BootstrapAsset`). `grep -r "AppAsset::register" frontend/views` finds zero hits — the only frontend layout, `mainNavy.php`, registers `AppNavyAsset` instead. |
| `frontend/assets/AppNavyAsset.php` | `frontend/views/layouts/mainNavy.php` (the site's only layout, set as `'layout' => 'mainNavy'` in `frontend/config/main.php`) | **Live** — every public page. |
| `backend/assets/AppAsset.php` | `backend/views/layouts/blank.php`, used by `SiteController::actionLogin()` (`$this->layout = 'blank'`) | **Live**, but only for the admin login screen. Stock scaffolding: `css/site.css` + `yii\web\YiiAsset` + `yii\bootstrap5\BootstrapAsset` (pulls jQuery/Bootstrap from `vendor/bower-asset/`, no custom JS). |
| `backend/assets/AppAdminAsset.php` | `backend/views/layouts/admin.php`, the backend's **default layout** (`'layout' => 'admin'` in `backend/config/main.php`) | **Live** — every authenticated admin page. |

**`AppNavyAsset` (frontend, live on every public page):**
```php
public $js = [
   ['navy/js/jquery-3.6.0.min.js','position' => \yii\web\View::POS_HEAD],
    'navy/js/bootstrap.min.js',
   // 'navy/js/jquery.nice-select.js',   // commented out, unused
    'navy/js/wow.min.js',
    'navy/js/main.js',
];
public $depends = [
    //'yii\web\YiiAsset',            // commented out — no Yii2 client-validation/yii.js on public pages
    //'yii\bootstrap5\BootstrapAsset',
];
```
Because `$depends` is fully commented out, the public site does **not** load `yii.js`/`yii.activeForm.js` (Yii2's client-side ActiveForm validation script). That's why every form-heavy public view (eligibility check, personal/academic info, etc.) hand-rolls its own jQuery `$.ajax()` validation/submit logic in inline `<script>` blocks instead of using Yii2's built-in client validation — see the inline-script section below.

**`AppAdminAsset` (backend, live on every admin page):**
```php
public $js = [
    //'adminAsset/js/vendor.min.js',                              // commented out here...
    'adminAsset/vendor/daterangepicker/moment.min.js',
    'adminAsset/vendor/daterangepicker/daterangepicker.js',
    'adminAsset/vendor/apexcharts/apexcharts.min.js',
   ///'adminAsset/js/pages/demo.dashboard-analytics.js',           // commented out, never loaded
     'adminAsset/js/app.min.js',
];
public $depends = [
     'yii\web\YiiAsset',            // pulls bower-asset jQuery separately
    // 'yii\bootstrap5\BootstrapAsset',
];
```
...but `vendor.min.js` and `hyper-config.js` are loaded anyway, as **raw `<script src>` tags hardcoded in `backend/views/layouts/admin.php`** (lines 25–26), completely outside the asset-bundle system:
```php
<script src="<?= Yii::getAlias('@web'); ?>/adminAsset/js/vendor.min.js"></script>
<script src="<?= Yii::getAlias('@web'); ?>/adminAsset/js/hyper-config.js"></script>
```
`vendor.min.js` itself bundles **jQuery 3.6.0 + Bootstrap 5.2.3 + SimpleBar** (confirmed by its banner comments/minified signatures), so combined with `AppAdminAsset`'s `yii\web\YiiAsset` dependency (which separately publishes `bower-asset/jquery`), **jQuery is loaded twice** on every admin page — once bundled inside `vendor.min.js`, once via Yii2's own `JqueryAsset`.

**Conclusion:** treat this as a pure legacy jQuery-era Yii2 app — no bundler was ever introduced, so there's no dead build pipeline to describe (unlike the officer repo's unused Vite setup). The only "unused code" here is dead/orphaned *asset bundles and theme files*, not a dead build tool.

---

## `vendor/bower-asset/` — Yii2/composer-managed vendor JS (framework-level, not project files)

Not part of the 68-file count above (lives under `vendor/`, published on demand into `web/assets/`), but this is the Yii2-equivalent of "npm-installed libs pulled in via a published asset dir" that the officer repo gets from its `node_modules`/Vite pipeline:

- `vendor/bower-asset/jquery` — pulled in by `yii\web\JqueryAsset`, a dependency of `yii\web\YiiAsset` (used on the backend login page and, via `AppAdminAsset`, every admin page).
- `vendor/bower-asset/bootstrap` — pulled in by `yii\bootstrap5\BootstrapAsset` (only actually depended-on by the two dead-or-login-only `AppAsset` bundles, frontend and backend).
- `vendor/bower-asset/jquery-ui`, `vendor/bower-asset/inputmask`, `vendor/bower-asset/punycode`, `vendor/bower-asset/yii2-pjax` — transitive framework/widget dependencies (Pjax, masked-input widgets), not directly wired into any bundle's `$js` array checked here.

---

## `frontend/web/navy/js/` (4 files) — public candidate-facing site

Loaded by `AppNavyAsset`, registered from the site's only layout (`frontend/views/layouts/mainNavy.php`).

Vendor:
- `jquery-3.6.0.min.js`
- `bootstrap.min.js`
- `wow.min.js` (scroll-reveal animation library)

Custom:

| File | Lines | Purpose |
|---|---|---|
| `main.js` | 62 | Fully custom but **pure template boilerplate**, no navy/candidate-specific logic: preloader fade-out, sticky header on scroll, back-to-top button show/hide + smooth scroll, `WOW().init()` wiring. All project-specific behavior (Google Analytics snippet, mobile menu toggle, per-page AJAX/validation) lives inline in `mainNavy.php` and individual view files instead — see below. |

`jquery.nice-select.js` is referenced in a commented-out line in `AppNavyAsset` but the file isn't even present in `navy/js/` — dead reference to a plugin that was removed.

---

## `backend/web/adminAsset/js/` + `backend/web/adminAsset/vendor/` (61 + 3 = 64 files)

This is the **"Hyper" Bootstrap admin theme** (Coderthemes — see the layout's own `<meta name="description">`/`<meta name="author">` tags), used by `backend/views/layouts/admin.php`, the backend's default layout. As with the officer repo's copy of the same theme family, almost the entire folder is unmodified third-party/demo code — confirmed by `grep -rli "navy\|candidate\|sailor\|eligibility"` across every file in the folder returning **zero hits**.

Vendor / theme libraries actually loaded (4 files, via the mix of `AppAdminAsset` and hardcoded `<script>` tags in `admin.php` described above):
- `js/vendor.min.js` — jQuery 3.6.0 + Bootstrap 5.2.3 + SimpleBar bundled together (hardcoded `<script>` tag, not via the bundle)
- `js/hyper-config.js` — theme's light/dark/layout config bootstrapper, localStorage-based (hardcoded `<script>` tag)
- `js/app.min.js` — theme's own compiled framework JS (sidebar toggling, generic UI wiring), via `AppAdminAsset`
- `vendor/daterangepicker/moment.min.js` + `vendor/daterangepicker/daterangepicker.js` + `vendor/apexcharts/apexcharts.min.js`, via `AppAdminAsset` (bundled in but **no view in `backend/views` references daterangepicker or apexcharts** — `grep -rn "vendor/quill\|vendor/daterangepicker\|vendor/apexcharts" backend/views` returns zero hits, so these three ship on every admin page for no active feature)

Everything else — confirmed unreferenced by any Blade-equivalent (`.php`) view via `grep -rn "demo\." backend/views` and `grep -rn "adminAsset/js/pages\|adminAsset/js/ui" backend/views`, both zero hits:
- `js/hyper-syntax.js` — theme's Prism-based code syntax highlighter
- `js/pages/demo.*.js` (52 files: `demo.apex-*`, `demo.chartjs-*`, `demo.crm-*`, `demo.dashboard*`, `demo.google-maps`, `demo.jstree`, `demo.quilljs`, `demo.sparkline`, `demo.toastr`, `demo.typehead`, `demo.vector-maps`, `demo.widgets`, etc.) — the theme's own demo/showcase pages. Note `demo.dashboard-analytics.js` (which would render the admin dashboard's ApexCharts) is explicitly present but **commented out** in `AppAdminAsset` (`///'adminAsset/js/pages/demo.dashboard-analytics.js',`), so the dashboard currently ships with no chart JS at all — unlike the officer repo, where the equivalent file is live (with unmodified dummy data).
- `js/ui/component.*.js` (5 files: dragula, fileupload, range-slider, rating, todo) — theme demo UI-component wrappers.

No custom application code in this folder; all admin-side custom logic lives inline in Blade-equivalent view files (see below), the same pattern observed in the officer repo.

---

## Inline `<script>` blocks in views

Counted via `grep -rl "<script"` (files) and `grep -ro "<script"` (tag occurrences) across each app's `views/` tree:

| App | Files containing `<script>` | Total `<script>` tag occurrences |
|---|---|---|
| `frontend/views/` | 11 | 16 |
| `backend/views/` | 15 | 22 |

This is where nearly all project-specific candidate/admin logic actually lives, not in `.js` files — consistent with `AppNavyAsset` not loading Yii2's own client-validation JS (see above), which pushes public-facing forms toward hand-written jQuery.

Frontend (candidate-facing) examples:
- `frontend/views/layouts/mainNavy.php` — Google Analytics (`gtag.js`) snippet in `<head>`; mobile sidebar-menu open/close toggle in `<body>`.
- `frontend/views/check-eligibility/personal_info.php` — inline jQuery: candidate-type change handler that shows/hides a "posso kotha / departmental" fields block and does an `$.ajax()` POST to `ajax/district-by-candidate-type` to repopulate the district `<select>`; plus a feet/inch → CM height-calculator on keyup.
- `frontend/views/check-eligibility/academic_info.php`, `eligible_department.php`, `de-sailor/personal_info.php`, `de-sailor/payment.php`, `de-sailor/candidate/application_form_download.php`, `sailor-candidate/personal_info.php`, `sailor-candidate/payment.php`, `sailor-candidate/candidate/application_form_download.php`, `my-application/index.php` — same pattern: form-flow-specific AJAX/UI wiring, no separate `.js` files.

Backend (admin) examples:
- `backend/views/layouts/admin.php` — one-line `document.write(new Date().getFullYear())` in the footer.
- `backend/views/log-report/report.php` — uses Yii2's `$this->registerJs(...)` PHP helper (not a raw `<script>` tag, but functionally the same: server-generated inline JS) for report-page logic.
- `backend/views/sailors/index.php`, `de-sailors/index.php`, `report/candidate_filter.php`, `report/exam_date_check_by_center_designation.php`, `eligibility/_form.php`, `sailor-batchs/_form.php`, `sailor-batch-configuration/_form.php`, `sailors/reference/*.php`, `de-sailors/reference/*.php`, `site/index.php` — CRUD-screen and reference-candidate form AJAX/validation handlers, same pattern as the officer repo's admin side: custom logic lives in the view, not in `public/adminAsset`-equivalent `.js` files.
