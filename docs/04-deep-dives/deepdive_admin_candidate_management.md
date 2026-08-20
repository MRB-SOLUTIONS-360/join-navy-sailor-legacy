# Deep Dive — Admin: Candidate Management (Sailors / DE Sailors / DE Sailor Branch)

Scope: `backend/controllers/{SailorsController,DeSailorsController,DeSailorBranchController}.php`, `backend/views/{sailors,de-sailors,de-sailor-branch}/*.php`, plus the `backend/controllers/AjaxController.php` endpoints these screens call.

All file paths below are repo-relative to `/home/bs-01692/Personal/MRB/Join Navy/Legacy/join-navy-sailor-legacy`. Every claim is anchored to a file/line read directly from the repository; no behavior is assumed. Where a claim was already established by the Phase-0 inventory agents, it is cited to `controller_inventory.md` / `model_inventory.md` / `component_inventory.md` / `view_inventory.md` / `middleware_inventory.md` / `route_inventory.md` rather than re-derived.

Architecture note (see `controller_inventory.md:1-10`): this is a Yii2 2.0 advanced-template app, not Laravel. There is no `FormRequest` layer — validation lives in each ActiveRecord model's `rules()`. There is no Blade — views are plain PHP (`$this->render()`), and list screens use the framework's own `yii\grid\GridView` widget instead of a hand-rolled DataTables/Blade partial.

---

## 0. Scope map — routes → controllers → views

Yii2's default URL rule is `{controller-id}/{action-id}` (confirmed live routes in `route_inventory.md:105,107,116`). There is no separate route file to read — the controller **is** the route table.

| Route | Controller@Action | View rendered | Portal nav label |
|---|---|---|---|
| `sailors/index` | `SailorsController::actionIndex()` | `sailors/index` | Sailor ▸ **Sailor** |
| `sailors/cancel-request` | `SailorsController::actionCancelRequest()` | `sailors/index` (same file, different query) | Sailor ▸ **Cancel Request** |
| `sailors/view` | `SailorsController::actionView($id)` | `sailors/view` | (row action, no nav entry) |
| `sailors/update` | `SailorsController::actionUpdate($id)` | `sailors/_form` | (row action, no nav entry) |
| `sailors/reference-candidate` | `SailorsController::actionReferenceCandidate()` | `sailors/reference/reference_candidate` | Sailor ▸ **Reference** |
| `sailors/add-reference-candidate` | `SailorsController::actionAddReferenceCandidate()` | `sailors/reference/add_reference_candidate` | (button on Reference list) |
| `sailors/reference-candidate-update` | `SailorsController::actionReferenceCandidateUpdate($id)` | `sailors/reference/update_reference_candidate` | (row action on Reference list) |
| `sailors/create` | *(dead — `actionCreate()` fully commented out)* | n/a | not linked anywhere |
| `sailors/delete` | *(dead — `actionDelete()` fully commented out)* | n/a | **still linked from `sailors/view.php:20`, see §7.1** |
| `de-sailors/index` | `DeSailorsController::actionIndex()` | `de-sailors/index` | DE Sailor ▸ **Sailor** |
| `de-sailors/view` | `DeSailorsController::actionView($id)` | `de-sailors/view` | (row action, no nav entry) |
| `de-sailors/create` | `DeSailorsController::actionCreate()` — **live action, broken render, see §7.2** | `de-sailors/create` (missing file) | not linked anywhere |
| `de-sailors/update` | `DeSailorsController::actionUpdate($id)` | `de-sailors/update` → `de-sailors/_form` | (row action, no nav entry) |
| `de-sailors/delete` | `DeSailorsController::actionDelete($id)` — **live, unlike Sailors** | n/a (redirects to index) | linked from `de-sailors/view.php:20` |
| `de-sailors/reference-candidate` | `DeSailorsController::actionReferenceCandidate()` | `de-sailors/reference/reference_candidate` | DE Sailor ▸ **Reference** |
| `de-sailors/add-reference-candidate` | `DeSailorsController::actionAddReferenceCandidate()` | `de-sailors/reference/add_reference_candidate` | (button on Reference list) |
| `de-sailors/reference-candidate-update` | `DeSailorsController::actionReferenceCandidateUpdate($id)` | `de-sailors/reference/update_reference_candidate` | (row action on Reference list) |
| `de-sailor-branch/index` \| `/view` \| `/create` \| `/update` \| `/delete` | `DeSailorBranchController` — **all 5 actions exist and are wired to routes, but every one renders a view file that does not exist on disk** | `backend/views/de-sailor-branch/*` — **directory absent entirely** | **not present anywhere in `left_side_menu.php`** — confirmed via `grep -n "de-sailor-branch\|Sailor Branch" backend/views/layouts/left_side_menu.php` → zero hits |

**Permission on every route above:** no controller in this scope declares its own `AccessControl` (`controller_inventory.md:267-268,295-296` and confirmed by reading all three controllers directly — each `behaviors()` only registers a `VerbFilter` for `delete`). Access control instead comes from **one application-level behavior**, `'as access'` in `backend/config/main.php:132-144`, which Yii2 fires on `EVENT_BEFORE_ACTION` for **every** action in the backend app before the controller's own filters run (`middleware_inventory.md:23-56`). Effect: any action other than `site/login`/`site/error` requires an authenticated Yii identity (`roles => ['@']`) — there is **no per-action role check for `user_type == 'admin'`** anywhere in this call path (`middleware_inventory.md:60`), so any authenticated `common\models\User` record, regardless of type, can reach every screen documented below.

**DE Sailor Branch — confirmed dead end (evidence).** `find backend/views/de-sailor-branch -type f` returns *"No such file or directory"* — the directory itself was never created, not just individual files (matches `controller_inventory.md:32`, re-verified directly here). All 5 of `DeSailorBranchController`'s actions (`actionIndex`, `actionView`, `actionCreate`, `actionUpdate`, `actionDelete`) call `$this->render(...)` against that missing directory and would throw a `yii\base\ViewNotFoundException` on the very first hit — `actionDelete()` is the only one of the five that would actually complete (it never renders a view, just deletes and redirects). Combined with the missing nav-menu entry, this feature is unreachable through normal navigation and would 500 immediately even if an admin guessed the URL directly.

---

## 1. Page Inventory

Every admin view here `extends` the shared `backend/views/layouts/admin.php` shell (breadcrumb + card wrapper, documented as the "Admin CRUD page shell" in `component_inventory.md:143-153`) via Yii2's implicit layout resolution — no `@extends` directive to point to, since Yii2 layouts are configured, not declared per-view. jQuery + Bootstrap 5 + the admin theme JS are loaded once via `backend/assets/AppAdminAsset.php`, which (unlike the frontend app) does declare `yii\web\YiiAsset` as a dependency (`component_inventory.md:169`), so Yii's own `ActiveForm` client validation and the `ActionColumn` `{delete}` JS confirm-dialog are live wherever used in this scope.

### 1.1 Sailor — Candidate List

- **Page Name:** Sailor (list)
- **URL:** `/sailors/index`
- **Route:** `sailors/index`
- **Portal:** Backend (Admin), nav: Sailor ▸ Sailor
- **Permission:** app-level `'as access'` (any authenticated backend user — see §0)
- **View File:** `backend/views/sailors/index.php`
- **Controller/Method:** `SailorsController::actionIndex()` (`backend/controllers/SailorsController.php:45-56`)
- **Purpose:** Master searchable/paginated/sortable grid of every Sailor (general track) application, ordered `serial_no DESC`.
- **Detailed Description:** Builds an `ActiveDataProvider` from `SailorsSearch::search()` (`model_inventory.md:472-491`) and forces `orderBy('serial_no DESC')` after the fact (`SailorsController.php:49`). The district dropdown list (`all_district_session`) is cached in session on first hit, same pattern used by `de-sailors/index.php`. Two encrypted-phone columns are rendered per row: `permanent_phone_de` (AES256CTR, no filter attached — display-only) and `permanent_phone` (legacy `DataEncryption` cipher, `'filter' => false` so it can be viewed but not searched). A `Custom Filter` column (`list_custom_filter`) offers "Paid & complete" / "Paid & not complete", sourced from the same custom filter documented for `SailorsSearch` in `model_inventory.md:491` (`1` = paid & roll assigned, `2` = paid & roll not yet assigned).
- **User Actions Available:** GridView column filter row (14 filterable columns, see §2.1) with implicit Pjax auto-submit; per-row **Update** (pencil icon → `sailors/update`); per-row **Download Form** (SVG download icon → `/sailor-candidate/download-form?slug=...` on the **frontend** app, `target=_blank`, only rendered when `serial_no` is set — the row's roll number acts as the render guard, `index.php:400-410`); a commented-out **Add** button (`index.php:184`, dead markup — no `actionCreate` exists to point at anyway); a hidden **Decode Phone** link (`index.php:178`, `<!-- ... -->` HTML-commented out) whose click handler is nonetheless live in the page's own `<script>` block (`index.php:127-157`) — see §7.5.
- **Blade/View Partials:** none — layout only.
- **JS (page-specific, inline):** `index.php:124-158` (Decode Phone AJAX handler, dead trigger — §7.5); `index.php:427-473` (custom horizontal-scrollbar sync between a `.fake-scrollbar` div and the GridView table, re-bound on `pjax:end` so it survives Pjax partial reloads).
- **AJAX endpoints called:** `POST ajax/decode-phone` (reachable only via the dead trigger, §7.5).
- **Modals:** none (`grep -rn "modal\|Modal"` across `backend/views/sailors`, `backend/views/de-sailors` returns zero hits).

### 1.2 Sailor — Cancel Request List

- **Page Name:** Cancel Request
- **URL:** `/sailors/cancel-request`
- **Route:** `sailors/cancel-request`
- **Portal:** Backend (Admin), nav: Sailor ▸ Cancel Request
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/sailors/index.php` — **same physical file as §1.1**, not a dedicated view.
- **Controller/Method:** `SailorsController::actionCancelRequest()` (`SailorsController.php:62-77`)
- **Purpose:** List only the Sailor applications a candidate has requested to cancel (`request_for_cancel = 1`), for admin review/acknowledgement.
- **Detailed Description:** Runs the identical `SailorsSearch::search()` as §1.1, then bolts on `andWhere(['request_for_cancel' => 1])` and the same `serial_no DESC` order (`SailorsController.php:69-70`). The controller passes `'is_cancel_request' => true` into the shared `index` view specifically so the template could special-case this listing — **but `index.php` never reads that variable** (confirmed: `grep -n "is_cancel_request" backend/views/sailors/*.php` → zero matches). The only thing that visually distinguishes a cancel-request row from a normal one is the `app_unique_id` column's own row-level check (`index.php:232-237`, unconditional on both routes): when `request_for_cancel == 1 AND cancel_application_view == 1`, it appends a red **"Cancel Marked"** badge next to the application ID. In other words the "cancel request" page and the normal list page are functionally the same template with a different underlying WHERE clause, not a differentiated screen — see §7.3.
- **User Actions Available:** identical to §1.1 (same GridView, same Update/Download-Form row actions). The candidate's `Cancel Request Reason` (free text, `reason` column) and a **"Mark / Not Mark"** dropdown (`cancel_application_view`) to acknowledge the cancellation only become visible once the admin clicks through to the individual candidate's **Update** page (§1.3) — not on this list.
- **JS / AJAX / Modals:** identical to §1.1 (same file).

### 1.3 Sailor — Candidate Update

- **Page Name:** Update Candidate Information
- **URL:** `/sailors/update?id={id}`
- **Route:** `sailors/update`
- **Portal:** Backend (Admin)
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/sailors/_form.php`
- **Controller/Method:** `SailorsController::actionUpdate($id)` (`SailorsController.php:270-356`)
- **Purpose:** The **only** write path onto an existing Sailor record from the backend — a narrow, purpose-built subset of the full application (name/parent info, phone, photo, payment/status), not a general-purpose edit-everything form.
- **Detailed Description:**
  - **Decrypt-for-display / re-encrypt-on-save.** `actionUpdate()` decrypts every field in `$model->encryption_fields_for_personal_info` for display before rendering the form (`SailorsController.php:275-276`), then re-encrypts the same fields immediately before `save()` (lines 341-342) — same shape as the officer-portal's `Candidate::encryptFields()` pattern, just Yii2-flavored (a model property listing field names instead of a static method).
  - **Photo re-upload with R2 cleanup (`SailorsController.php:280-333`):** on a new `photo` upload, the controller (a) ensures a per-candidate local scratch directory `@rootDirFilUpload/media/sailor_candidate/{id}/` exists, (b) saves the new file there under a timestamped name, (c) uploads it to Cloudflare R2 under a batch-name-prefixed key via `Yii::$app->r2Storage->uploadFile()`, (d) deletes the **previous** local file if it still exists, (e) empties and `rmdir()`s the entire scratch directory (including the file it just wrote in step (b) — the local copy is treated as disposable once R2 has it), and (f) calls `$r2Storage->deleteFile($prevImage)` to remove the old R2 object. If no new file was uploaded, `$model->photo` is simply reset to `$prevImage` (line 333) — a no-op write.
  - **Redundant/broken re-save after cleanup (bug, see §7.4):** after `$model->save(false)` succeeds, line 347 unconditionally calls `if ($fileImage) $fileImage->saveAs($path);` a **second time** — but `$path`'s parent directory is the exact scratch directory that was just `rmdir()`'d in step (e) above, and the `UploadedFile` instance was already consumed by the first `saveAs()` call at line 305 (its underlying temp upload file no longer exists after a successful move). This second call is dead weight that silently fails (`saveAs()` returns `false` on failure, not checked) every time a photo is actually changed.
  - **Phase override on manual payment:** `if ($model->is_manula_paid == Constants::YES) $model->phase = Constants::SAILOR_PHASE_TWO;` (`SailorsController.php:337-338`) — ticking "manual paid = Yes" on this form unconditionally forces the candidate's application-phase forward to `SAILOR_PHASE_TWO`, regardless of what phase they were actually in or whether any other required step was completed. No confirmation, no audit note beyond the model's own `updated_by`/`updated_dt` stamping (`model_inventory.md:466`, `beforeSave()`).
  - **`$model->validate()` is called but the form only binds ~15 of the model's ~150 attributes** (see §2.2) — so `Sailors::rules()` only meaningfully validates the handful of fields actually present in `$_POST`; every other rule in the model is a no-op on this action since its field is never submitted.
- **User Actions Available:** single form (`_form.php:39-180`), Application ID / Payment Check ID / Designation / Center shown read-only as plain text at the top; ~15 editable fields (see §2.2) grouped under **Photo** and **Payment Block** headers; conditional **Cancel Request Reason** + **Mark/Not Mark** dropdown, rendered only `if ($model->request_for_cancel)` (`_form.php:171-175`); single **Save** submit button.
- **Blade/View Partials:** none.
- **JS (page-specific, inline):** none — no client-side preview/cascading script on this page (contrast with the officer-portal's `FileReader` photo preview; this form has no equivalent).
- **AJAX endpoints called:** none.
- **Modals:** none.
- **Dead code (evidence):** `_form.php:187-273` is a large HTML-comment block (`<?php /* ... */ ?>`) listing `$form->field()` calls for essentially every other model attribute (`current_village`, `ssc_institute`, `experience_one_institute`, `is_freedom_fighter`, `phase`, etc.) — a leftover from an earlier, fuller Gii-generated form that was deliberately pared down to the ~15 fields actually rendered above it.

### 1.4 Sailor — Candidate Detail View

- **Page Name:** *(title = candidate's name)*
- **URL:** `/sailors/view?id={id}`
- **Route:** `sailors/view`
- **Portal:** Backend (Admin)
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/sailors/view.php`
- **Controller/Method:** `SailorsController::actionView($id)` (`SailorsController.php:234-239`)
- **Purpose:** Full read-only dump of every field on the candidate record via `yii\widgets\DetailView` — the only place in the backend where an admin can see the ~100 fields (academic history, experience blocks, freedom-fighter/naval-lineage flags, full payment trail) that `_form.php` does not expose for editing.
- **User Actions Available:** **Update** button (→ §1.3); **Delete** button — **dead link, see §7.1**. `DetailView` lists ~100 attributes verbatim (`view.php:31-155`), including raw JSON columns rendered as `:ntext` (`ssc_edu_data`, `hsc_edu_data`, `ssc_teletalk_data`, `hsc_teletalk_data`, `reference_details`, `relationship`) — these render as unformatted JSON blobs, not parsed/tabulated (contrast with the Reference list's JSON-parsing behavior, §1.5).
- **JS / AJAX / Modals:** none.

### 1.5 Sailor — Reference Candidate List

- **Page Name:** Reference Candidate
- **URL:** `/sailors/reference-candidate`
- **Route:** `sailors/reference-candidate`
- **Portal:** Backend (Admin), nav: Sailor ▸ Reference
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/sailors/reference/reference_candidate.php`
- **Controller/Method:** `SailorsController::actionReferenceCandidate()` (`SailorsController.php:85-99`)
- **Purpose:** Grid of every Sailor candidate who has at least one recruiter reference attached (`have_reference = YES`), the entry point for both adding new references and correcting/removing existing ones.
- **Detailed Description:** Forces `$searchModel->have_reference = Constants::YES` server-side before running `SailorsSearch::search()`, then re-orders by `updated_dt DESC` (`SailorsController.php:88-90`) — most-recently-touched reference first, not most-recently-created candidate. The full query-string is session-stashed under `reference_candidate_query_param` (line 93) for the PDF/Excel/Word export actions on `ReportController`/`DeSailorReportController` (out of scope here, see `controller_inventory.md`'s `ReportController` section) to reuse as their own filter. **Export links on this page point at that separate `ReportController`**, not at this controller: Excel (`/report/all-reference-candidate-excel`), PDF (`/report/reference-candidate-pdf`), Word (`/report/reference-candidate-word`), and a second Excel variant (`/report/reference-candidate-excel`) — all four rendered unconditionally, all `target="_blank"` (`reference_candidate.php:67-70`).
- **User Actions Available:** GridView filter row (11 filterable columns, see §2.3); **Add** button (→ §1.6); per-row **Update** (pencil-style SVG → `sailors/reference-candidate-update`, §1.7); four export links (above) with no visibility guard (unlike the officer-portal's export links, which only render when a result set exists).
- **JS (page-specific, inline):** same horizontal-scrollbar-sync pattern as §1.1 (`reference_candidate.php:243-258`).
- **AJAX endpoints called:** none directly (Pjax handles filter/sort/paginate).
- **Modals:** none.

### 1.6 Sailor — Add Reference Candidate

- **Page Name:** Add Reference Candidate
- **URL:** `/sailors/add-reference-candidate`
- **Route:** `sailors/add-reference-candidate`
- **Portal:** Backend (Admin)
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/sailors/reference/add_reference_candidate.php`
- **Controller/Method:** `SailorsController::actionAddReferenceCandidate()` (`SailorsController.php:106-173`)
- **Purpose:** Look a Sailor candidate up by roll number (`serial_no`) and append one new reference entry (referrer name, relationship, free-text details) to that candidate's record — additive, not a replace.
- **Detailed Description — JSON-column-append logic (`SailorsController.php:106-173`):** Uses a standalone `backend\models\SailorsReference` ActiveRecord (points at the same `{{%sailors}}` table, scenario `ADD_REFERENCE`, only 3 required fields — `model_inventory.md:718-735`). On submit, the controller re-fetches the target `Sailors` row by `serial_no` (selecting only `id, eligibility_info_id, candidate_type, app_unique_id, referred_by, relationship, reference_details, have_reference, reference_add_on, last_reference_added`), then for **each** of `referred_by`, `relationship`, `reference_details`, `reference_add_on` independently: `json_decode()`s the column's current value if non-empty, **appends** the new value to that PHP array, and `json_encode()`s it back — i.e. four parallel JSON arrays are kept in lock-step by index, one array-slot per historical reference entry, rather than one JSON blob of `{referrer, relationship, details}` objects. `reference_details` falls back to the literal string `'empty'` if left blank (line 141/144) rather than `null`, and `reference_add_on` is stamped with the current timestamp on every append regardless of what the admin typed (lines 148-151). `have_reference` is force-set to `Constants::YES` and `last_reference_added` to `now()` on every successful append (lines 156-159). On success the form resets to a blank `SailorsReference` model so a new lookup can be typed immediately (line 162).
- **User Actions Available:** `serial_no` text input with a **blur-triggered AJAX lookup** (not a button) — see below; `referred_by` / `relationship` / `reference_details` textareas; **Save** button (`#save_btn`), disabled by JS when the looked-up application is cancelled.
- **JS (page-specific, inline):** `add_reference_candidate.php:15-71` — on blur of the roll-number field, `POST ajax/get-sailor-information-by-roll` with `{roll}`; on success, reveals a `#show_details` panel with the candidate's name/eligible-district/permanent-district, and if the candidate already has references, **client-side renders an HTML table** of all prior `referred_by`/`relationship`/`reference_details`/`reference_add_on` entries by zipping the four parallel JSON arrays returned from the server (lines 43-58) — this preview table is not persisted anywhere, purely informational before the admin appends a 5th/6th/etc. entry. If the looked-up candidate's `application_status` indicates a cancelled application, the panel shows an inline warning and **disables the Save button** client-side (lines 60-64) — this is a soft, JS-only guard; the server-side `actionAddReferenceCandidate()` does **not** independently re-check `application_status` before appending, so a direct POST (bypassing the disabled button) would still succeed.
- **AJAX endpoints called:** `POST ajax/get-sailor-information-by-roll` (`AjaxController::actionGetSailorInformationByRoll()`, `backend/controllers/AjaxController.php:29-46`).
- **Modals:** none.

### 1.7 Sailor — Update Reference Candidate

- **Page Name:** Update Reference Candidate
- **URL:** `/sailors/reference-candidate-update?id={id}`
- **Route:** `sailors/reference-candidate-update`
- **Portal:** Backend (Admin)
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/sailors/reference/update_reference_candidate.php`
- **Controller/Method:** `SailorsController::actionReferenceCandidateUpdate($id)` (`SailorsController.php:180-224`)
- **Purpose:** Edit or wholesale-clear the reference JSON arrays on one already-referenced candidate — the correction/removal counterpart to §1.6's additive flow.
- **Detailed Description:** Loads a `SailorsReference` row by `id` (not `serial_no`), pre-explodes the three parallel JSON arrays (`referred_by`, `relationship`, `reference_details`) into one editable `<textarea>` row per historical entry, indexed by array position (`update_reference_candidate.php:56-77`). On submit, the controller branches on **whether `$_POST['referred_by']` is present at all** (`SailorsController.php:196-210`): if present, all three (four, counting `have_reference`) fields are rebuilt from the posted arrays and `have_reference` stays/becomes `YES`; if **absent**, all reference fields are nulled out and `have_reference` is set to `NO` — this is how a candidate is fully un-referenced from this screen. Note `reference_add_on` and `last_reference_added` are **not** touched on a pure edit (only re-stamped on the §1.6 add flow) except that the "clear everything" branch also nulls `reference_add_on`/`last_reference_added` (lines 207-208).
- **User Actions Available:** read-only `serial_no` field (`textInput(['readonly' => true])`); one editable 3-column row (Referred By / Relationship / Reference Details) per historical entry, each with a **client-side-only "delete row" icon** (`.delete_row`, confirms via native `confirm()` then simply removes the `<tr>` from the DOM, `update_reference_candidate.php:92-100` — no server round-trip, the row is just excluded from the next form submission); **Update** submit button.
- **Edge case (evidence):** if an admin deletes **every** row client-side before submitting, `$_POST['referred_by']` is entirely absent from the request, which — per the controller branch above — triggers the "clear everything" path rather than "no-op save." This is very likely the intended UX (delete-all-rows = clear the reference), but it means the row-delete-then-save flow and the (nonexistent) explicit "clear reference" action are actually the same code path, undocumented as such in the UI.
- **JS / AJAX:** `update_reference_candidate.php:92-100` (client-side row removal only, no AJAX).
- **Modals:** none — the "are you sure" on row delete is a native `confirm()`, not a Bootstrap modal.

### 1.8 DE Sailor — Candidate List

- **Page Name:** Direct Entry Sailors
- **URL:** `/de-sailors/index`
- **Route:** `de-sailors/index`
- **Portal:** Backend (Admin), nav: DE Sailor ▸ Sailor
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/de-sailors/index.php`
- **Controller/Method:** `DeSailorsController::actionIndex()` (`DeSailorsController.php:46-58`)
- **Purpose:** Master grid of Direct-Entry (trade) Sailor applications — Artificer and Dockyard tracks — parallel to §1.1 but for the DE candidate pool.
- **Detailed Description:** Structurally identical to §1.1 (`DeSailorsSearch::search()`, forced `serial_no DESC`), with DE-specific differences: (a) a `candidate_type` filter column hard-restricted to the two DE constants — `Constants::CANDIDATE_DE_SAILOR` ("Direct Entry Artificer") and `CANDIDATE_DE_SAILOR_DOCKYARD` ("Direct Entry Dockyard") — rather than the general `StaticMethod::candidateType()` list (`index.php:117`, commented-out unrestricted version left in place directly above it at line 116); (b) a `diploma_trade_course` column/filter sourced from `Subjects::getAllSubject()` (passed in by the controller as `$all_subjects`, `DeSailorsController.php:51`) — a field with no equivalent on the general Sailor grid, reflecting DE candidates' trade-course background (`model_inventory.md`'s Sailors-vs-DeSailors section); (c) **no `permanent_phone_de` column** — DE Sailors only ever shows the legacy `DataEncryption`-decrypted `permanent_phone` (`index.php:184-190`), because the AES256CTR-encrypted search column (`permanent_phone_de`) only exists on the `Sailors`/`SailorsSearch` model, not `DeSailors` (confirmed absent from `model_inventory.md`'s `DeSailors` field list); (d) `candidate_designation`'s filter dropdown is **dynamically scoped to the currently-selected `candidate_type`** (`index.php:125`: `$searchModel->candidate_type ? CanDesignation::getAllDesignation(type: intval($searchModel->candidate_type)) : []` — empty until a type is chosen), unlike the Sailor grid's static single-type dropdown.
- **User Actions Available:** GridView filter row (10 filterable columns, see §2.5); per-row **Update** (→ `de-sailors/update`); per-row **Download Form** (→ `/de-sailor/download-form` on the frontend app, same `serial_no` render-guard as §1.1); commented-out **Add** button (`index.php:75`).
- **JS / AJAX / Modals:** same horizontal-scrollbar-sync pattern as §1.1 (no Decode Phone script on this page — that feature is Sailor-only).

### 1.9 DE Sailor — Candidate Detail View

- **Page Name:** *(title = candidate's name)*
- **URL:** `/de-sailors/view?id={id}`
- **Route:** `de-sailors/view`
- **Portal:** Backend (Admin)
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/de-sailors/view.php`
- **Controller/Method:** `DeSailorsController::actionView($id)` (`DeSailorsController.php:66-71`)
- **Purpose:** Same as §1.4, for a DE Sailor record.
- **Detailed Description:** Same `DetailView` pattern as §1.4, ~105 attributes, plus DE-only fields absent from the Sailor equivalent: `candidate_type`, `exam_group`, `serial_generate_date`, `is_departmental_candidate` (`view.php:36,41,42,127`) — consistent with `DeSailors` carrying a departmental-candidate age-ceiling distinction documented in `model_inventory.md`'s Sailors-vs-DeSailors section.
- **User Actions Available:** **Update** button (→ §1.10); **Delete** button — **this one is live** (unlike §1.4's dead equivalent), since `DeSailorsController::actionDelete($id)` is a real, uncommented action (`DeSailorsController.php:336-341`) that hard-deletes the row and redirects to the index.
- **JS / AJAX / Modals:** none.

### 1.10 DE Sailor — Candidate Create (broken) & Update

- **Page Name (Create):** *(none — broken route, never linked from any UI)*
- **URL:** `/de-sailors/create`
- **Route:** `de-sailors/create`
- **Controller/Method:** `DeSailorsController::actionCreate()` (`DeSailorsController.php:78-93`) — a completely ordinary Gii-generated create action (`$model = new DeSailors(); ... $this->render('create', ...)`), **not** commented out (contrast with `SailorsController::actionCreate()`, which is dead code — §7.2 vs. the Sailors dead-code entry in `controller_inventory.md:50`).
- **Why it's broken:** `backend/views/de-sailors/create.php` does not exist — only `_form.php`, `index.php`, `update.php`, `view.php`, `reference/` are present (`controller_inventory.md:36`, re-verified via `ls backend/views/de-sailors/`). Hitting this URL directly throws `yii\base\ViewNotFoundException` on both GET and a would-be successful POST-then-redirect-to-view path (the POST branch redirects to `view`, so it's only the GET/initial-render path that's reachable and broken). No `Add` button anywhere in `de-sailors/index.php` points at this route (it's commented out, `index.php:75`), so this is an orphaned, crash-on-access action with no discoverable entry point — functionally dead despite the controller code being live.
- **Page Name (Update):** Update De Sailors: {name}
- **URL:** `/de-sailors/update?id={id}`
- **Route:** `de-sailors/update`
- **View File:** `backend/views/de-sailors/update.php` (23-line wrapper that sets breadcrumbs/title and `$this->render('_form', ['model' => $model])`) → `backend/views/de-sailors/_form.php`
- **Controller/Method:** `DeSailorsController::actionUpdate($id)` (`DeSailorsController.php:102-190`)
- **Purpose:** DE Sailor's counterpart to §1.3 — same narrow editable-field set, same decrypt/re-encrypt, photo-to-R2, and phase-override behaviors.
- **Detailed Description — byte-for-byte parallel to §1.3, with one deliberate difference:** the redundant post-save `$fileImage->saveAs($path)` call that is live (and broken) in `SailorsController::actionUpdate()` is **explicitly commented out** here: `DeSailorsController.php:179`: `// if ($fileImage) $fileImage->saveAs($path);`. This is the clearest direct evidence that the Sailors-controller version (§7.4) is an unintentional leftover rather than deliberate behavior — the DE Sailors controller was evidently patched to remove it while the Sailors controller was not. Everything else in the upload/cleanup block (scratch-dir creation, R2 upload, old-file/old-dir cleanup, `$r2Storage->deleteFile($prevImage)`) is identical between the two controllers, including local media path naming (`/media/de_sailor_candidate/{id}/...` vs. `/media/sailor_candidate/{id}/...`).
- **User Actions Available / Fields:** identical shape to §1.3's ~15-field form (see §2.6) — Name/Father Name/Father NID/Father Occupation/Mother Name/Mother Occupation/Permanent Phone, Photo, Payment Type/Is Manual Paid/Payment Status/Ref ID/Validation ID/Card Type/Card No/Trans Date, Application Status. **No Cancel-Request block on this form** — `DeSailorsController` has no `actionCancelRequest()` counterpart, so there is nothing analogous to §1.2's cancel-marking workflow for DE candidates at all.
- **JS / AJAX / Modals:** none.
- **Dead code:** `_form.php` has the identical large commented-out full-field-list block as the Sailors version (same shape, `common\models\DeSailors` fields instead).

### 1.11 DE Sailor — Reference Candidate List

- **Page Name:** Reference Candidate
- **URL:** `/de-sailors/reference-candidate`
- **Route:** `de-sailors/reference-candidate`
- **Portal:** Backend (Admin), nav: DE Sailor ▸ Reference
- **Permission:** app-level `'as access'`
- **View File:** `backend/views/de-sailors/reference/reference_candidate.php`
- **Controller/Method:** `DeSailorsController::actionReferenceCandidate()` (`DeSailorsController.php:198-212`) — same shape as §1.5.
- **Purpose:** Same as §1.5, for DE Sailor candidates.
- **Detailed Description:** Same `have_reference = YES` force-filter + `updated_dt DESC` order + session-stash pattern as §1.5. **Only one export link** on this page — PDF via `/de-sailor-report/reference-de-candidate-pdf` (`reference_candidate.php:65`) — versus the four export links (Excel/PDF/Word/Excel) on the Sailor equivalent; there is no DE-specific Excel or Word reference export at all (`DeSailorReportController`'s own inventory entry confirms `actionReferenceCandidateExcel()` exists but is a near-stub placeholder — `controller_inventory.md`'s `DeSailorReportController` section — and there is no DE equivalent of `ReportController::actionReferenceCandidateWord()`).
- **User Actions Available:** GridView filter row (9 filterable columns, see §2.7); **Add** button (→ §1.12); per-row **Update** (→ §1.13). No `last_reference_added` column on this grid (contrast with §1.5) — because `DeSailorsReference` has no `reference_add_on`/`last_reference_added` fields at all (confirmed by its controller's narrower `select()` list, `DeSailorsController.php:234` and `:289`, versus Sailors' wider one, `SailorsController.php:120` and `:183`).
- **JS / AJAX / Modals:** same scrollbar-sync pattern; none.

### 1.12 DE Sailor — Add Reference Candidate

- **Page Name:** Add Reference Candidate
- **URL:** `/de-sailors/add-reference-candidate`
- **Route:** `de-sailors/add-reference-candidate`
- **View File:** `backend/views/de-sailors/reference/add_reference_candidate.php`
- **Controller/Method:** `DeSailorsController::actionAddReferenceCandidate()` (`DeSailorsController.php:220-277`)
- **Purpose:** DE Sailor counterpart to §1.6.
- **Detailed Description:** Same three-parallel-JSON-array append logic as §1.6 (`referred_by`, `relationship`, `reference_details`), minus the `reference_add_on` fourth array and the cancelled-application client-side guard (`add_reference_candidate.php` for DE Sailors has no `is_cancel_application` handling at all — compare `reference_candidate.php`'s JS at lines 30-40, which never reads `data.is_cancel_application`, versus the Sailor version's lines 60-64). `DeSailorsReference` labels the `serial_no` field **"Serial No"** rather than Sailors' **"Roll No"** — the one deliberate label difference between the two reference models, already flagged as a genuine (non-bug) naming nuance in `model_inventory.md:506`.
- **User Actions Available:** same as §1.6 minus the cancellation warning/disable behavior.
- **JS (page-specific, inline):** `add_reference_candidate.php:11-41` — blur-triggered `POST ajax/get-de-sailor-information-by-roll`.
- **AJAX endpoints called:** `POST ajax/get-de-sailor-information-by-roll` (`AjaxController::actionGetDeSailorInformationByRoll()`, `AjaxController.php:52-69`).
- **Modals:** none.

### 1.13 DE Sailor — Update Reference Candidate

- **Page Name:** Update Reference Candidate
- **URL:** `/de-sailors/reference-candidate-update?id={id}`
- **Route:** `de-sailors/reference-candidate-update`
- **View File:** `backend/views/de-sailors/reference/update_reference_candidate.php` — **byte-identical to `sailors/reference/update_reference_candidate.php`** (`view_inventory.md:181`), including its reference to `common\models\Sailors` in the doc-comment (a leftover from being copy-pasted, harmless since the view only ever touches `$model`'s generic properties).
- **Controller/Method:** `DeSailorsController::actionReferenceCandidateUpdate($id)` (`DeSailorsController.php:286-326`)
- **Purpose / Detailed Description:** Same three-array (not four — no `reference_add_on`) present/absent branch logic as §1.7.
- **User Actions / JS / AJAX / Modals:** identical to §1.7.

### 1.14 DE Sailor Branch — Index / View / Create / Update / Delete (all broken)

- **Page Name:** *(none reachable)*
- **URL/Routes:** `/de-sailor-branch/{index,view,create,update,delete}`
- **Controller:** `backend\controllers\DeSailorBranchController` (`backend/controllers/DeSailorBranchController.php`, full file read directly for this doc) — a completely ordinary 5-action Gii CRUD scaffold wrapping `common\models\DeSailorBranch` via `backend\models\DeSailorBranchSearch`, `VerbFilter` on `delete` only, no custom business logic of any kind (no photo upload, no JSON columns, no phase logic — unlike Sailors/DeSailors).
- **Why every screen is dead:** `backend/views/de-sailor-branch/` does not exist on disk (§0). `actionIndex()`, `actionView($id)`, `actionCreate()`, and `actionUpdate($id)` (`DeSailorBranchController.php:40-106`) all call `$this->render(...)` against that missing directory and will throw immediately. `actionUpdate()` additionally renders `'update'` rather than the usual `'_form'` (line 103) — matching the file-inventory's observation that this controller was scaffolded slightly differently from its siblings, but the distinction is moot since neither file exists. `actionDelete($id)` (`DeSailorBranchController.php:115-120`) is the **one** action that would actually complete, since it only calls `$this->findModel($id)->delete()` and redirects — no render involved.
- **Reachability:** zero. Not present in `backend/views/layouts/left_side_menu.php` (confirmed via direct grep — no "de-sailor-branch" or "Sailor Branch" string anywhere in that file), not linked from any other view in this scope, and not referenced by any other controller. The feature exists purely as an unshipped Gii scaffold: controller generated, model (`common\models\DeSailorBranch`, `backend\models\DeSailorBranchSearch`) generated and presumably functional at the DB layer, but the view-generation step was never completed and the resulting controller was never wired into the admin nav.

---

## 2. Form Documentation

### 2.1 Sailor — Candidate List: GridView filter row

- **Form Name:** Sailor list column filters
- **Page:** §1.1 (and §1.2, same markup)
- **Mechanism:** Yii2 `GridView` auto-generates one `<input>`/`<select>` per filterable column, wired directly to `$searchModel` (`SailorsSearch`), submitted via Pjax on change/enter — no separate `<form>` markup to read, no explicit "Search" button (Pjax auto-triggers on filter-row interaction, standard Yii2 GridView behavior).
- **Validation:** `SailorsSearch::rules()` loosens every field to `integer`/`safe`/`number` (`model_inventory.md:476-478`) and bypasses `scenarios()` — i.e. this is a permissive search filter, not a persisted-record form; there is nothing to reject.

| Column | Filter Type | Datasource | Notes |
|---|---|---|---|
| `app_unique_id` | *(display only, no filter widget)* | — | shows a "Cancel Marked" badge conditionally (§1.2) |
| `candidate_designation` | select | `CanDesignation::getAllDesignation(Constants::CANDIDATE_SAILOR)` | |
| `center_id` | select | `SailorCenters::getAllCenter()` | |
| `batch_id` | select | `SailorBatchs::getAllBatch(Constants::CANDIDATE_SAILOR)` | |
| `exam_date` | `yii\jui\DatePicker` | `dateFormat: yyyy-MM-dd`, year range ±3 from current year | |
| `exam_group` | select | `StaticMethod::sailorGroup()` | |
| `eligible_district` | select | session-cached `Districts::getAllDistrict()` | |
| `permanent_district` | select | same session-cached district list | |
| `permanent_phone_de` | *(display only)* | — | AES256CTR-decrypted, not filterable |
| `permanent_phone` | *(display only, `'filter' => false`)* | — | legacy-cipher-decrypted, explicitly not filterable |
| `gender` | select | `StaticMethod::gender()` | |
| `payment_status` | select | `StaticMethod::paymentStatus()` | |
| `list_custom_filter` | select | static `[1 => 'Paid & complete', 2 => 'Paid & not complete']` | maps to `SailorsSearch`'s custom join/subquery logic |
| `application_status` | select | `StaticMethod::isCanselApplication()` | |

### 2.2 Sailor — Candidate Update form

- **Form Name:** Update Candidate Information
- **Page:** §1.3
- **Action URL:** implicit self-post to `sailors/update?id={id}` (Yii2 `ActiveForm::begin([])` with no explicit `action`)
- **HTTP Method:** POST (`enctype` auto-switches to `multipart/form-data` because a file field is present)
- **Controller@Method:** `SailorsController::actionUpdate($id)`
- **Validation:** `Sailors::rules()` (full ~290-line ruleset per `model_inventory.md:460`), but only invoked against whatever `$model->load($this->request->post())` actually populated — i.e. only the fields below are ever exercised.

| Field | Type | Notes |
|---|---|---|
| `name` | text | |
| `father_name` | text | |
| `father_nid` | text | national ID — plaintext in this form, **not** in `$encryption_fields_for_personal_info` (only `father_nid, current_phone, permanent_phone, father_phone, mother_phone, guardian_phone` are — `model_inventory.md:471` — but note `father_nid` *is* listed there, so it is actually encrypted/decrypted like the phone fields, contrary to how it reads in isolation) |
| `father_occupation` | text | |
| `mother_name` | text | |
| `mother_occupation` | text | |
| `permanent_phone` | text | decrypted for display, re-encrypted on save |
| `photo` | file | see photo-reupload logic, §1.3; `Sailors` model rule requires png/jpg ≤500KB exactly 300×300px (`model_inventory.md:471`) |
| `payment_type` | select | `StaticMethod::paymentTypeAdmin()` |
| `is_manula_paid` | select | `StaticMethod::yesNo()` — **drives the phase-override side effect**, §1.3 |
| `payment_status` | select | `StaticMethod::yesNo()` (note: labeled "Payment Status" but uses the generic Yes/No list, not `StaticMethod::paymentStatus()` — a different static helper than the one used for this same column's grid *filter*, §2.1) |
| `ref_id` | text | |
| `validation_id` | text | |
| `card_type` | text | |
| `card_no` | text | |
| `trans_date` | `yii\jui\DatePicker`, `readonly` | |
| `application_status` | select | `StaticMethod::isCanselApplication()` |
| `cancel_application_view` | select, **conditional on `$model->request_for_cancel`** | static `[1 => 'Mark', 2 => 'Not Mark']` — only field on this form gated by data state |

### 2.3 Sailor — Reference Candidate List: GridView filter row

Same mechanism as §2.1. Filterable columns: `app_unique_id`, `candidate_designation` (`CanDesignation::getAllDesignation(Constants::CANDIDATE_SAILOR)`), `center_id`, `batch_id` (scoped to `Constants::CANDIDATE_SAILOR`), `exam_date` (DatePicker), `exam_group`, `eligible_district` (`Districts::getAllDistrict()` — **not** session-cached here, unlike §2.1/§1.1), `gender`, `marital_status` (`StaticMethod::maritalStatus()`). `permanent_phone_de`/`permanent_phone` are display-only (same pattern as §2.1). `last_reference_added` is display-only, no filter.

### 2.4 Sailor — Add Reference Candidate form

- **Form Name:** Add Reference Candidate
- **Page:** §1.6
- **Action URL:** implicit self-post to `sailors/add-reference-candidate`
- **HTTP Method:** POST (plus a live AJAX-validate branch on the same action when `Yii::$app->request->isAjax`, `SailorsController.php:111-114`)
- **Controller@Method:** `SailorsController::actionAddReferenceCandidate()`
- **Validation:** `SailorsReference::rules()`, scenario `ADD_REFERENCE`:
  ```php
  // backend/models/SailorsReference.php (per model_inventory.md:718-734)
  [['serial_no', 'referred_by', 'relationship'], 'required', 'on' => self::ADD_REFERENCE],
  ['serial_no', 'isSerialNoExist', 'on' => self::ADD_REFERENCE, 'skipOnError' => false, 'skipOnEmpty' => false],
  ['reference_details', 'safe'],
  ```
  `isSerialNoExist` is a custom validator that checks `self::find()->where(['serial_no' => ...])->count()` — i.e. it only confirms *a* row with that roll number exists in the `sailors` table; it does **not** independently re-check `application_status` server-side (that check is JS-only, §1.6).

| Field | Label | Type | Required | Notes |
|---|---|---|---|---|
| `serial_no` | *(model attribute label — "Roll No" on this model, `model_inventory.md:506`)* | text, `enableAjaxValidation: true` | Yes | blur triggers `ajax/get-sailor-information-by-roll` (client JS, not the Yii ActiveForm AJAX validation path) |
| `referred_by` | Referred By | textarea | Yes | appended to a JSON array on the target `Sailors` row |
| `relationship` | Relationship | textarea | Yes | appended to a parallel JSON array |
| `reference_details` | Reference Details | textarea | No (`safe`) | appended to a parallel JSON array; falls back to literal `'empty'` string if blank |

### 2.5 DE Sailor — Candidate List: GridView filter row

Same mechanism as §2.1. Filterable columns: `candidate_type` (restricted 2-option list — Artificer/Dockyard, §1.8), `candidate_designation` (dynamically scoped to selected `candidate_type`, empty until one is chosen), `diploma_trade_course` (`Subjects::getAllSubject()`), `center_id`, `batch_id` (scoped to both DE types), `exam_group`, `eligible_district` (session-cached), `permanent_district` (session-cached), `gender`, `payment_status`, `application_status`. `permanent_phone` display-only, `'filter' => false`; **no `permanent_phone_de` column at all** on this grid (§1.8).

### 2.6 DE Sailor — Candidate Update form

Identical field set to §2.2, same types/datasources, applied to `common\models\DeSailors` instead of `Sailors`. The one structural difference: **no `cancel_application_view` conditional field** — `DeSailors` has no `actionCancelRequest()` counterpart controller-side, so there's no cancel-marking UI path to gate on.

### 2.7 DE Sailor — Reference Candidate List: GridView filter row

Same mechanism as §2.1/§2.3. Filterable columns: `candidate_type` (restricted 2-option list), `candidate_designation` (dynamically scoped), `center_id`, `batch_id` (scoped to both DE types), `exam_group`, `eligible_district` (`Districts::getAllDistrict()`, not session-cached), `gender`, `marital_status`. `permanent_phone` display-only. No `last_reference_added` column (§1.11).

### 2.8 DE Sailor — Add/Update Reference Candidate forms

Same shape and validation as §2.4/§1.7, minus `reference_add_on` (field doesn't exist on `DeSailorsReference`) and minus the JS cancelled-application guard (§1.12). `serial_no` label is **"Serial No"** on this model, not "Roll No" (`model_inventory.md:506`).

### 2.9 DE Sailor Branch — Create/Update forms

**Not documented** — no form markup exists anywhere on disk for this resource (§0/§1.14). `common\models\DeSailorBranch`'s `rules()` were not inspected as part of this deep-dive since there is no reachable UI surface to validate against; see `model_inventory.md` if a field-level audit of the underlying table is needed for a future rebuild.

---

## 3. Frontend Business Logic — AJAX trigger summary (cross-page)

All AJAX in this scope is inline jQuery `$.ajax()` inside page `<script>` blocks — no dedicated `.js` asset files (consistent with the repo-wide pattern already established in `component_inventory.md`'s asset-usage notes).

| Trigger | Page(s) | Endpoint | Method | Auth |
|---|---|---|---|---|
| `blur` on roll/serial-no input | `sailors/reference/add_reference_candidate.php` | `ajax/get-sailor-information-by-roll` | POST | app-level `'as access'` (any authenticated backend user, §0) |
| `blur` on roll/serial-no input | `de-sailors/reference/add_reference_candidate.php` | `ajax/get-de-sailor-information-by-roll` | POST | same |
| `click` on `#decodePhone` (hidden trigger, §7.5) | `sailors/index.php` | `ajax/decode-phone` | POST | same |
| Pjax auto-submit on filter/sort/paginate | every GridView page in this scope | *(same route, `_pjax` param)* | GET | same |

**Every route above is a `backend\controllers\AjaxController` action.** Unlike the officer-portal reference doc's finding of specific unauthenticated routes, `AjaxController` here has **no route-level carve-out** — it sits inside the same `backend` app as every other controller in this doc, so the app-level `'as access'` behavior (§0) still gates it. The controller does, however, unconditionally disable CSRF validation for all of its own actions: `AjaxController::beforeAction()` sets `$this->enableCsrfValidation = false` before calling `parent::beforeAction()` (`AjaxController.php:19-22`) — meaning none of these three POST endpoints carry or check a CSRF token, relying entirely on session-cookie authentication (the app-level access-control gate) for protection. Given the `'as access'` gate only checks role `@` (any authenticated identity) and not `user_type == 'admin'` (`middleware_inventory.md:60`), any authenticated backend session — not just an admin — can call `ajax/decode-phone` and trigger the batch phone re-encryption described in §7.5.

---

## 4. Modal Audit

**Result: none.** `grep -rn "modal\|Modal" backend/views/sailors backend/views/de-sailors` (the `de-sailor-branch` view directory does not exist to grep) returns zero matches. Every create/edit/delete action in this scope is a full-page navigation or a bare AJAX call; the only "are you sure" prompts anywhere in this functional area are native browser `confirm()` dialogs — one hand-rolled in `sailors/index.php:130` (Decode Phone), one hand-rolled in both `update_reference_candidate.php` files (row delete, client-side only), and Yii2's own built-in `data-confirm` JS handler on the `{delete}` `ActionColumn` buttons in `sailors/view.php`/`de-sailors/view.php` (`yii.js`'s default confirm dialog, live because `AppAdminAsset` depends on `YiiAsset` — `component_inventory.md:169`).

---

## 5. Model / data-layer touchpoints (for traceability)

| Model | Relevant methods/fields used by this functional area |
|---|---|
| `common\models\Sailors` | `encryption_fields_for_personal_info` (decrypt-for-display/re-encrypt-on-save list), `rules()` (~290 lines, only ~15 fields exercised from the backend Update form), `beforeSave()` (manual `created_by`/`created_dt`/`updated_by`/`updated_dt` stamping), static `find()` → `SailorsQuery` |
| `common\models\SailorsQuery` | `activeApplication()` / `cancelApplication()` scopes — shared with `DeSailors` (`model_inventory.md:466`) |
| `common\models\SailorsSearch` | `search($params)` — exact-match + `like` filters backing every Sailor GridView in this scope; encrypted-phone `like` search against `permanent_phone_de` (AES-encrypt-then-`like`, `model_inventory.md:491`); custom `list_custom_filter` (paid/roll-assigned join logic) |
| `backend\models\SailorsReference` | standalone AR over the same `{{%sailors}}` table; scenario constants `ADD_REFERENCE`/`UPDATE_REFERENCE`; custom `isSerialNoExist()` validator; labels `serial_no` "Roll No" |
| `common\models\DeSailors` | DE-only fields absent from `Sailors`: `diploma_trade_institute/course/registration_roll/gpa`, four `experience_*` blocks, `is_departmental_candidate` (feeds `ageValidation()`'s dept-vs-general max-age branch, per `model_inventory.md`'s Sailors-vs-DeSailors section); same `encryption_fields_for_personal_info` pattern |
| `common\models\DeSailorsSearch` | same shape as `SailorsSearch`, no `permanent_phone_de` filter support (column doesn't exist on this table) |
| `backend\models\DeSailorsReference` | same shape as `SailorsReference` minus `reference_add_on`; labels `serial_no` "Serial No" |
| `common\models\DeSailorBranch` / `backend\models\DeSailorBranchSearch` | exist and are presumably functional at the DB layer, but have **no reachable view surface** in this repo (§1.14) — not otherwise inspected as part of this deep-dive |
| `common\models\CanDesignation` | `getAllDesignation()` / `getAllDesignationSession()` — designation dropdown datasource + session-cached label lookup, shared across every grid/filter in this scope |
| `common\models\SailorCenters`, `common\models\SailorBatchs`, `common\models\Districts` | `getAllCenter()`/`getAllCenterSession()`, `getAllBatch()`/`getAllBatchSession()`, `getAllDistrict()` — same session-cache-once-per-request-lifecycle pattern used for every dropdown datasource across these grids |
| `common\static\StaticMethod` | supplies every static dropdown used across this area: `paymentTypeAdmin`, `yesNo`, `isCanselApplication`, `gender`, `sailorGroup`, `maritalStatus`, `candidateType`, `encryptPk` |
| `common\static\DataEncryption` | legacy cipher used for `permanent_phone`/`father_nid`/etc. decrypt-for-display + re-encrypt-on-save on both Update forms |
| `common\static\AES256CTR` | second, separate cipher used only for the `permanent_phone_de` search-optimized column (Sailors only) and the `ajax/decode-phone` backfill job (§7.5) |
| `common\components\R2Storage` (`Yii::$app->r2Storage`) | `uploadFile()`, `deleteFile()`, `fileExists()`, `fileUrl` — Cloudflare R2 client used identically by both `SailorsController::actionUpdate()` and `DeSailorsController::actionUpdate()` for candidate-photo storage |

---

## 6. Notable findings / anomalies (flagged, not fixed — per task instructions no app code was modified)

1. **`sailors/view.php` renders a live "Delete" button pointing at a dead action.** `view.php:20` unconditionally emits `Html::a('Delete', ['delete', 'id' => $model->id], [...'confirm'...])`, but `SailorsController::actionDelete($id)` is fully commented out (`SailorsController.php:365-370`, already flagged at the controller level in `controller_inventory.md:50`). Clicking this button in a live admin session would hit a route with no matching action — Yii2 throws a `yii\web\NotFoundHttpException` (404), not a silent no-op. Contrast with `de-sailors/view.php`'s **identical** Delete button, which works, since `DeSailorsController::actionDelete($id)` is live (`DeSailorsController.php:336-341`) — the two view files are near-copies of each other but only one of the two matching controllers actually implements the action they both link to.
2. **`DeSailorsController::actionCreate()` is live code with a broken render target.** Unlike `SailorsController::actionCreate()` (dead/commented-out), this action is fully wired — `$model->load()`/`$model->save()`/redirect-to-view all work — but its GET/initial-render branch calls `$this->render('create', ...)` against a file that does not exist (`controller_inventory.md:36`, re-verified: `ls backend/views/de-sailors/` shows no `create.php`). It has no discoverable entry point in the UI (the only "Add" button that could link to it is commented out, `index.php:75`), so this is a crash-on-direct-access route with a live controller, not visible in normal use — a different failure shape from finding #3 below (which is fully broken including the model/search layer's own reachability) and from finding #1 (a working model backed by a missing action).
3. **DE Sailor Branch is a fully orphaned Gii scaffold.** All 5 actions on `DeSailorBranchController` exist and are individually reasonable (standard CRUD, no custom logic), but the entire `backend/views/de-sailor-branch/` directory was never created (§0/§1.14), and the feature is not linked from `left_side_menu.php` or anywhere else in the app. 4 of 5 actions (`index`, `view`, `create`, `update`) would throw `ViewNotFoundException` on first hit; only `delete` would actually execute. This reads as "scaffolded, migrated at the DB layer, never finished" rather than a regression — there's no evidence it ever worked.
4. **Redundant, silently-failing post-save file write in `SailorsController::actionUpdate()`.** After a successful photo change, the controller: saves the new file locally → uploads it to R2 → deletes the local scratch directory containing that same file (`rmdir()`, `SailorsController.php:322-329`) → then, after `$model->save(false)` succeeds, calls `$fileImage->saveAs($path)` a **second time** at line 347, targeting a path whose parent directory was just removed. This call will fail silently (`UploadedFile::saveAs()` returns `false` on failure; the return value isn't checked) on every photo-change update. The parallel `DeSailorsController::actionUpdate()` has the equivalent line explicitly commented out (`DeSailorsController.php:179`), which is strong evidence this is an unintentional leftover in the Sailors controller specifically, not a shared, deliberate pattern.
5. **`ajax/decode-phone` is a mislabeled, hardcoded, string-concatenated-SQL one-time backfill job left live behind a hidden UI trigger.** `AjaxController::actionDecodePhone()` (`AjaxController.php:93-113`) actually **encrypts** `permanent_phone` into the search-optimized `permanent_phone_de` column (the opposite of what "Decode Phone" suggests), is hardcoded to `WHERE batch_id = 1` with a `LIMIT 5000`, and executes a raw `UPDATE jnavy_sailors SET permanent_phone_de = "..." WHERE id = ...` string built via direct concatenation (`Yii::$app->db->createCommand($sql)`, not parameter binding) — the interpolated values are a base64/hex-shaped ciphertext and an integer primary key sourced from the DB itself, so this specific call site isn't directly attacker-controlled, but it's still a raw-SQL string-concatenation pattern in a codebase that otherwise uses Yii2's query builder/ActiveRecord throughout this scope. Its only UI trigger (`sailors/index.php:178`, an `<a id="decodePhone">`) is HTML-commented out, but the jQuery `click` handler bound to that same `id` is **not** commented out (`index.php:127-157`) — the route (`POST ajax/decode-phone`) remains live and reachable by any authenticated backend session (§3), just with no visible button pointing at it in the current UI.
6. **`actionCancelRequest()` passes a template flag the shared view never reads.** `SailorsController::actionCancelRequest()` sets `'is_cancel_request' => true` when rendering `sailors/index.php` (`SailorsController.php:75`), and `actionIndex()` sets the same key to `false` (`SailorsController.php:54`) — but `grep -n "is_cancel_request" backend/views/sailors/*.php` finds no reference to that variable anywhere in the view. The Cancel Request page (§1.2) is visually indistinguishable from the plain candidate list (§1.1) except for its underlying `WHERE request_for_cancel = 1` filter and the pre-existing, route-independent "Cancel Marked" badge logic (`index.php:232-237`, which fires on **either** route whenever a row happens to have both `request_for_cancel` and `cancel_application_view` set).
7. **Backend admin forms edit a small fraction of each candidate's data.** Both `sailors/_form.php` and `de-sailors/_form.php` expose only ~15 of each model's ~150 attributes for editing (§2.2/§2.6) — name/parentage/phone, photo, and the payment/status block. The remaining ~135 attributes (full academic history, all four experience blocks, freedom-fighter/naval-lineage flags, address fields) are visible only via the read-only `DetailView` on the View page (§1.4/§1.9) and are **not editable from the backend at all** once a candidate has submitted their application — consistent with both controllers' `actionUpdate()` only ever populating `$model` from whatever `$_POST` fields the trimmed form actually submits, and confirmed by the large dead/commented-out `$form->field()` block at the bottom of both `_form.php` files (a leftover from a fuller Gii-generated form that was deliberately cut down).
8. **No admin-role re-check anywhere in this functional area.** All three controllers rely entirely on the app-level `'as access'` behavior (`backend/config/main.php:132-144`), which only checks "is this Yii identity authenticated" (`roles => ['@']`), not `user_type == 'admin'` (`middleware_inventory.md:60`). Nothing controller-local in `SailorsController`, `DeSailorsController`, or `DeSailorBranchController` adds a stricter check — every action in this doc (candidate edit, reference add/update, photo replacement, phase override) is reachable by any authenticated backend-app identity, whatever its `user_type`.
