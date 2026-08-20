# CSS Inventory — join-navy-sailor-legacy

**Generated:** 2026-08-20 | Scope: `frontend/assets/AppAsset.php`, `frontend/assets/AppNavyAsset.php`, `backend/assets/AppAsset.php`, `backend/assets/AppAdminAsset.php` (Yii2 AssetBundles), raw CSS/SCSS on disk under `frontend/web/`, `backend/web/`, `common/`, and inline `<style>` blocks in `backend/views/**/*.php` and `frontend/views/**/*.php`. Verified by reading every AssetBundle class, every layout (`admin.php`, `main.php`, `blank.php`, `mainNavy.php`), grepping all controllers for `$this->layout` overrides, and grepping all views for `<style` (no assumptions).

This is the Yii2-application equivalent of `join-navy-officer-legacy/docs/00-inventories/css_inventory.md` — same "candidate/public template vs. admin theme(s)" split exists here, but on a much smaller scale (14 CSS/SCSS files here vs. 54 there) since this app only has **two** competing admin layouts (not three) and no dead Vite pipeline.

---

## Summary

| Metric | Count |
|---|---|
| Total CSS/SCSS files in scope (excl. `vendor/`, published `assets/` cache) | **14** (4 backend + 10 frontend) |
| Vendor / theme library files | **10** |
| Custom application CSS files | **4** (`backend/web/css/site.css`, `frontend/web/css/site.css` — both unmodified Yii2 scaffold defaults; `frontend/web/navy/css/style.css` + `.scss` source — the real custom "NAVY" template) |
| Files present on disk but **never linked/registered anywhere** | **2 of 14** (`nice-select.css`, `vendor/daterangepicker/daterangepicker.css`) — plus `style.scss` which is a source file, not a browser-consumable asset |
| Blade/PHP views with inline `<style>` blocks | **23 files** (14 backend, 9 frontend) |
| Competing admin CSS trees | **2** — `backend/assets/AppAdminAsset.php` ("Hyper" theme, default layout for the entire backend) vs. `backend/assets/AppAsset.php` (Yii2-scaffold `site.css`, used only by the `blank` layout on the login page) |

| Bundle | Purpose | Files registered | On-disk size (registered files) | Layout that registers it | Live in current app? |
|---|---|---|---|---|---|
| `frontend/assets/AppAsset.php` | Yii2-scaffold default CSS | 1 (`css/site.css`) | 1.8 KB | *(none — no frontend layout registers it)* | **Dead** |
| `frontend/assets/AppNavyAsset.php` | Candidate-facing "NAVY" public template | 7 of 9 in `navy/css/` | ~436 KB | `frontend/views/layouts/mainNavy.php` (the app's only frontend layout) | **Yes** — every public/candidate page |
| `backend/assets/AppAsset.php` | Yii2-scaffold default CSS | 1 (`css/site.css`) | 1.6 KB | `backend/views/layouts/blank.php`, registered by `backend/views/layouts/main.php` (orphaned, never selected) | **Partially** — only via `blank` layout, and only for `SiteController::actionLogin()` (admin login page) |
| `backend/assets/AppAdminAsset.php` | "Hyper" admin theme | 2 of 4 in `adminAsset/` tree | ~839 KB | `backend/views/layouts/admin.php` — **the app-wide default layout** (`backend/config/main.php: 'layout' => 'admin'`) | **Yes** — every authenticated backend page |

---

## `frontend/assets/AppAsset.php` — dead scaffold bundle

| File | Type | Size | Status |
|---|---|---|---|
| `frontend/web/css/site.css` | Custom, unmodified Yii2-scaffold default (`.footer`, `.not-set`, GridView sort-icon rules) | 1.8 KB | Registered by `AppAsset` class, but `frontend/views/layouts/` contains **only one layout file** (`mainNavy.php`), which registers `AppNavyAsset` instead. `AppAsset::register()` is never called anywhere in `frontend/views`. **Dead.** |

---

## `frontend/assets/AppNavyAsset.php` — "NAVY" candidate-facing template (10 files in `frontend/web/navy/css/`)

Registered by `frontend/views/layouts/mainNavy.php`, the sole frontend layout — every public/candidate-facing page in this app goes through it (eligibility check, sign-up/login, application forms, etc.).

| File | Type | Purpose | Size | Registered in `AppNavyAsset::$css`? | Status |
|---|---|---|---|---|---|
| `frontend/web/navy/css/boxicons.min.css` | Vendor — Boxicons icon font | Icon font | 63.2 KB | Yes | **Used** |
| `frontend/web/navy/css/bootstrap-icons.css` | Vendor — Bootstrap Icons | Icon font | 65.7 KB | Yes | **Used** |
| `frontend/web/navy/css/bootstrap.min.css` | Vendor — **Bootstrap v5.0.0-beta3** (per file header) | Bootstrap framework, loaded as a raw asset, separate from `yiisoft/yii2-bootstrap5` | 154.9 KB | Yes | **Used** |
| `frontend/web/navy/css/animate.css` | Vendor — Animate.css | CSS animation utility classes | 81.4 KB | Yes | **Used** |
| `frontend/web/navy/css/style.css` | **Custom** — "NAVY - Join Bangladesh Navy" template stylesheet (header, hero, join/chief-staff/life-navy/gallery/benefits/equipment/footer sections, per its own table-of-contents comment) | Main site theme/branding CSS | 55.6 KB | Yes | **Used** |
| `frontend/web/navy/css/responsive.css` | **Custom** — responsive/breakpoint overrides | Mobile/tablet layout adjustments | 9.0 KB | Yes | **Used** |
| `frontend/web/navy/css/style_step.css` | **Custom** — multi-step form CSS (eligibility/registration step indicator) | Step-form UI | 6.7 KB | Yes | **Used** |
| `frontend/web/navy/css/nice-select.css` | Vendor — Nice Select plugin CSS | Custom select-dropdown styling | 4.0 KB | **No — commented out** (`AppNavyAsset.php:19`, paired with `jquery.nice-select.js` also commented out at line 27) | **Unused** — disabled in place, not deleted |
| `frontend/web/navy/css/style.scss` | **Custom** — SCSS source of `style.css` | Same "NAVY" template, source form with the identical section table-of-contents | 40.9 KB (source) | N/A — SCSS, not a browser-consumable stylesheet | **Not directly linkable** (source/dev artifact) |

**AppNavyAsset totals:** 9 CSS/SCSS files on disk, 7 registered/live, 1 disabled-in-place (`nice-select.css`), 1 source file (`style.scss`).

---

## `backend/assets/AppAsset.php` — Yii2-scaffold bundle (login page only)

| File | Type | Size | Registered from | Status |
|---|---|---|---|---|
| `backend/web/css/site.css` | Custom, unmodified Yii2-scaffold default (identical structure to the frontend copy, minus the `.container-fluid` selector) | 1.6 KB | `backend/views/layouts/blank.php:8` (`AppAsset::register($this)`) | **Used, but narrowly** — `blank` layout is selected only by `SiteController::actionLogin()` (`$this->layout = 'blank';`), i.e. the admin login screen. No other controller overrides the app-wide default layout (`'admin'`, set in `backend/config/main.php:17`). |

`backend/views/layouts/main.php` also registers this same `AppAsset`, but **no controller ever sets `layout = 'main'`** — it's an orphaned Yii2-scaffold layout left over from `yii2-app-advanced` and is never rendered.

---

## `backend/assets/AppAdminAsset.php` — "Hyper" admin theme (4 files in `backend/web/adminAsset/`)

Registered by `backend/views/layouts/admin.php`, which is the **app-wide default backend layout** (`backend/config/main.php: 'layout' => 'admin'`) — every authenticated backend/admin page uses this theme.

| File | Type | Purpose | Size | Registered in `AppAdminAsset::$css`? | Status |
|---|---|---|---|---|---|
| `backend/web/adminAsset/css/app.css` | Vendor — **"Hyper" Bootstrap 5 admin theme**, compiled (CSS custom properties `--ct-*`) | Theme's own core stylesheet (layout, sidebar, cards, components) | 394 KB | Yes (`['adminAsset/css/app.css', 'id' => 'app-style']`) | **Used** |
| `backend/web/adminAsset/css/icons.min.css` | Vendor — icon font CSS (Material Design Icons / Unicons / Remixicon, bundled) | Icon fonts used throughout the Hyper theme | 445 KB | Yes | **Used** |
| `backend/web/adminAsset/vendor/daterangepicker/daterangepicker.css` | Vendor — Daterangepicker plugin CSS | Date-range picker widget styling | 7.7 KB | **No** — only the two matching JS files (`moment.min.js`, `daterangepicker.js`) are registered; the CSS file is not in `$css` and not linked directly in any view | **Unused** — JS half of the plugin is wired up, CSS half was left out |

**AppAdminAsset totals:** 3 CSS files on disk in scope, 2 used / 1 unused.

---

## Inline `<style>` blocks in views (23 files)

Repo-wide grep of `backend/views/**/*.php` and `frontend/views/**/*.php` for `<style` found 23 files with embedded CSS — none are separate files this task counts as "CSS files" but are captured per the task's inline-block instruction.

| Area | Files |
|---|---|
| Backend PDF/report templates (mPDF-style standalone print CSS) | `backend/views/de-sailor-report/pdf/candidate_filter_pdf.php`, `backend/views/de-sailor-report/pdf/payment_report_pdf.php`, `backend/views/report/pdf/candidate_filter_pdf.php`, `backend/views/report/pdf/center_candidate_pdf.php`, `backend/views/report/pdf/district_candidate_pdf.php`, `backend/views/report/pdf/exam_date_center_candidate_pdf.php`, `backend/views/report/pdf/payment_report_pdf.php` |
| Backend grid/report/reference views | `backend/views/de-sailors/index.php`, `backend/views/de-sailors/reference/reference_candidate.php`, `backend/views/log-report/report.php`, `backend/views/report/candidate_filter.php`, `backend/views/sailors/index.php`, `backend/views/sailors/reference/add_reference_candidate.php`, `backend/views/sailors/reference/reference_candidate.php` |
| Frontend candidate-facing views | `frontend/views/candidate/login.php`, `frontend/views/candidate/sign_up.php`, `frontend/views/check-eligibility/personal_info.php`, `frontend/views/de-sailor/academic_info.php`, `frontend/views/sailor-candidate/academic_info.php` |
| Frontend PDF templates | `frontend/views/de-sailor/candidate/application_form_pdf.php`, `frontend/views/de-sailor/candidate/application_verification_pdf.php`, `frontend/views/sailor-candidate/candidate/application_form_pdf.php`, `frontend/views/sailor-candidate/candidate/application_verification_pdf.php` |

`de-sailor` and `sailor-candidate` view trees are near-duplicates of each other (same file names, same inline-style pattern) — same duplication shape as the PDF-template families documented in the officer repo's inventory.

---

## Bootstrap version

- `composer.json` declares **`yiisoft/yii2-bootstrap5: ~2.0.2`** only — no `yiisoft/yii2-bootstrap` (v3/4 wrapper) dependency present.
- Confirmed in active use: `use yii\bootstrap5\...` (Html, ActiveForm, Breadcrumbs, Nav, NavBar) appears across `backend/views/layouts/main.php`, `backend/views/layouts/left_side_menu.php`, and dozens of backend `_form.php`/report views plus frontend candidate/eligibility views. A repo-wide grep for the legacy `use yii\bootstrap\...` (no `5` suffix) namespace returned **zero matches** — no Bootstrap 3/4 Yii widgets remain.
- The two raw (non-Yii-widget) Bootstrap CSS copies vendored on disk are both **Bootstrap 5**: `frontend/web/navy/css/bootstrap.min.css` is explicitly versioned **v5.0.0-beta3** in its file header; `backend/web/adminAsset/css/app.css` (the "Hyper" theme) is a Bootstrap 5–based compiled theme (uses `--ct-*` CSS custom properties, no Bootstrap 3/4 markers). No legacy Bootstrap 3/4 CSS files exist anywhere in scope.

---

## Cross-cutting findings

1. **`frontend/assets/AppAsset.php` is dead** — its one file (`css/site.css`) is the unmodified Yii2-scaffold stylesheet, registered by a bundle class that no view ever calls; the app's single frontend layout (`mainNavy.php`) uses `AppNavyAsset` exclusively.
2. **Two competing backend CSS trees, but heavily lopsided (unlike the officer repo's three-way split):** `AppAdminAsset` ("Hyper" theme) is the default for the entire authenticated backend; `AppAsset` (scaffold `site.css`) survives only because `SiteController::actionLogin()` explicitly opts into the `blank` layout for the login screen. `backend/views/layouts/main.php` also wires up `AppAsset` but is itself orphaned — no controller ever selects it.
3. **`daterangepicker.css` is a half-wired vendor plugin** — its JS (`moment.min.js`, `daterangepicker.js`) is registered in `AppAdminAsset::$js`, but the matching CSS file sitting right next to it in the same `vendor/daterangepicker/` folder was never added to `$css`. Same "JS wired, CSS orphaned" pattern the officer repo's JS inventory calls out, just for one plugin instead of a whole tree.
4. **`nice-select.css` is disabled in place, not deleted** — commented out in `AppNavyAsset::$css` (line 19) in lockstep with its JS counterpart `jquery.nice-select.js` (line 27, also commented out) — a maintainer turned the whole plugin off without removing the files.
5. **Both `site.css` files (`frontend/web/css/site.css`, `backend/web/css/site.css`) are untouched Yii2 `app-advanced` scaffold defaults** — near-identical content (footer, `.not-set`, GridView sort-icon rules), confirming neither frontend nor backend teams customized the scaffold theme; all real branding lives in the "NAVY" template (`navy/css/style.css`) and the third-party "Hyper" admin theme (`adminAsset/css/app.css`).
6. **No legacy Bootstrap 3/4 remnants** — this app is cleanly on Bootstrap 5 both at the Yii widget layer (`yii\bootstrap5`) and in every raw vendored Bootstrap CSS file, unlike patterns sometimes seen in older Yii2 advanced-template forks.
7. **Scale is much smaller than the officer repo overall** (14 vs. 54 CSS/SCSS files, one admin theme "in rotation" plus one narrow scaffold fallback vs. three parallel admin/candidate trees there) — this codebase has comparatively little dead CSS weight; the only clearly orphaned/unused files are `nice-select.css` and `daterangepicker.css` (11 KB combined), versus hundreds of KB of orphaned theme-starter-kit CSS in the officer repo's `admin_v1/libs/`.
