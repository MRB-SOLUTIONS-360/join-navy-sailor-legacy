# Deep Dive: Frontend — Candidate Authentication (Signup / Signin / Change Password / Reset Password)

Scope: `frontend/controllers/CandidateController.php` (`actionSignUp`, `actionLogin`, `actionLogout`, `actionChangePassword`, `actionRequestPasswordReset`), `frontend/controllers/SiteController.php` (`actionLogin`, `actionSignup`, `actionRequestPasswordReset`, `actionResetPassword`, `actionVerifyEmail`, `actionResendVerificationEmail`), views in `frontend/views/candidate/*.php` and `frontend/views/site/*.php`, form models `common/models/LoginForm.php`, `common/models/ChangePassword.php`, `common/models/ResetPassword.php`, `frontend/models/SignupForm.php`, plus the stock Yii2-scaffold models `frontend/models/PasswordResetRequestForm.php`, `ResetPasswordForm.php`, `VerifyEmailForm.php`, `ResendVerificationEmailForm.php`, and `common/models/User.php`.

This is a Yii2 2.0 advanced-template app, not Laravel — there is no `FormRequest` layer; validation lives in each form model's `rules()` method. Routes are Yii2 `controller/action-id` pairs (`enablePrettyUrl` is on, `rules' => []` in `frontend/config/main.php:65`, so the id maps directly to a URL segment, e.g. `CandidateController::actionSignUp()` → `/candidate/sign-up`). There is no separate Blade-style template language — views are plain PHP using `yii\bootstrap5\ActiveForm`.

**Two login/signup surfaces exist in this codebase, and only one is real.** `frontend/controllers/SiteController.php` carries a full, unmodified Yii2-advanced-template auth scaffold (`actionLogin`, `actionSignup`, `actionRequestPasswordReset`, `actionResetPassword`, `actionVerifyEmail`, `actionResendVerificationEmail`) with matching stock views in `frontend/views/site/`. `frontend/controllers/CandidateController.php` carries a second, hand-built candidate auth flow (`actionSignUp`, `actionLogin`, `actionLogout`, `actionChangePassword`, `actionRequestPasswordReset`) with navy-branded views in `frontend/views/candidate/`. Confirmed by direct inspection that **only the `CandidateController` flow is reachable by real users**:

- `frontend/config/main.php:38` sets `'user' => ['loginUrl' => ['candidate/login'], ...]` — Yii2's own "redirect unauthenticated user to log in" mechanism points at `CandidateController::actionLogin()`, not `SiteController::actionLogin()`.
- The site's only frontend layout, `frontend/views/layouts/mainNavy.php` (registered for every `frontend/*` view via `frontend/config/main.php:21`, `'layout' => 'mainNavy'`), links exclusively to `candidate/login`, `candidate/sign-up`, `candidate/change-password`, and `candidate/logout` (lines 90, 93, 98, 105) — see `mainNavy.php:86-114`. It contains **zero links** to `site/login`, `site/signup`, `site/request-password-reset`, or `site/resend-verification-email`.
- `frontend/views/candidate/sign_up.php:95` links to `/candidate/login` for the "already have an account" case, not `/site/login`.
- Grepping the entire `frontend/` and `common/` trees for `site/login`, `site/signup`, or any bracketed route array pointing at those actions returns **zero hits** outside `SiteController.php` itself and its own `site/login.php` view (which links to its sibling `site/*` actions in a closed loop) — plus one hit in `frontend/tests/functional/LoginCest.php:29`, a leftover Codeception scaffold test, not application code.
- `SiteController`'s own `AccessControl` (`behaviors()`, lines 30-45) only restricts `signup`/`logout` — `actionLogin()` has no guard beyond the generic `!Yii::$app->user->isGuest` check, so it 200s if hit directly, but nothing in the live UI ever links to it.

Two further facts confirm `SiteController`'s auth actions are not just unlinked but **functionally broken if ever reached directly by URL**:
1. `SiteController::actionSignup()` uses `frontend\models\SignupForm` — the *same* form model `CandidateController::actionSignUp()` uses (see §2.1) — whose `rules()` requires `dob`, `captcha`, and `birth_registration_no` (`SignupForm.php:34`). But `frontend/views/site/signup.php` only renders `username`, `email`, and `password` fields (lines 22-26) — there is no `dob`, `birth_registration_no`, `confirm_password`, or `captcha` input anywhere in that view. Submitting this form can **never** pass validation; `required` errors on the three missing fields fire every time.
2. `SiteController::actionLogin()` uses `common\models\LoginForm`, whose `rules()` requires `captcha` (`LoginForm.php:32`). `frontend/views/site/login.php` renders only `username`, `password`, and a `rememberMe` checkbox (lines 22-26) — no `captcha` field exists for a user to fill in, so this form also can never validate successfully.

Both `site/login.php` and `site/signup.php` are further unstyled relative to the rest of the app — they use the generic Yii2-scaffold markup (`<div class="site-login">`, no `data-form-area`/`form__body` wrapper classes) that the "NAVY" theme's CSS (`navy/css/style.css`, per `docs/00-inventories/css_inventory.md`) never targets, even though both still render inside the branded `mainNavy` layout (neither controller overrides `$this->layout`, so both fall through to the app-wide default set in `frontend/config/main.php:21`).

All page/route citations below are for the live `CandidateController` flow unless explicitly marked "(dead — SiteController)".

---

## 1. Page Inventory

### 1.1 Candidate Sign Up

- **Page Name**: Candidate Sign Up
- **URL**: `/candidate/sign-up` (optional `?ceci=` query param, an encrypted `CanEligibilityCheckInfo` id carried from the public eligibility-check flow)
- **Controller/Action**: `frontend\controllers\CandidateController::actionSignUp()` — `CandidateController.php:52-120`
- **Portal**: Frontend (candidate-facing), guest-only (`if (!Yii::$app->user->isGuest) return $this->goHome();`, line 54) — no `AccessControl`, the guard is inline.
- **View File**: `frontend/views/candidate/sign_up.php`
- **Layout Used**: `mainNavy` (app default, `frontend/config/main.php:21`)
- **Purpose**: Register a new candidate account and immediately log the user in.
- **Detailed Description**: On GET, if a `?ceci=` query param is present, the controller decrypts it via `StaticMethod::decryptPk($ceci)`, looks up `CanEligibilityCheckInfo::select(['id','dob'])` and pre-fills `$model->dob` from the prior eligibility-check answer (lines 110-114). It always calls `captureValue()` (§5.1) to generate a fresh arithmetic CAPTCHA and stash it in `Yii::$app->session['captcha']`. On POST, the controller first checks `Yii::$app->request->isAjax` — if the request is an AJAX request (Yii2's own `ActiveForm` client-validation submit, not a hand-rolled call — see §3), it loads the model and returns `\yii\widgets\ActiveForm::validate($model)` as JSON, short-circuiting before any CAPTCHA check or save (lines 62-65). On a real (non-AJAX) POST, it loads the model, then checks CAPTCHA expiry (`session['captcha']['capture_value_result_exp']` vs `time()`) and CAPTCHA-answer match against `$model->captcha` (lines 71-78) — **both checks run outside `SignupForm::rules()`**, directly in the controller, so a failed CAPTCHA check re-renders the form with a fresh CAPTCHA rather than going through normal Yii2 validation-error flow. If both pass, `$model->signup()` (§2.1) creates the `User` row and, on success, the controller re-looks-up the just-created `User` by username and calls `Yii::$app->user->login($identity)` directly (line 84) — it does **not** reuse the object `signup()` already returned, an extra redundant query. It then branches on the `?ceci=` query param via `haveCeci()` (§5.2) to redirect to a payment page, `/my-application`, or home; with no `ceci`, it redirects straight to `/my-application` (line 106) — the "please check inbox for verification email" flash message from the stock Yii2 flow is present in a comment (lines 104-105) but is dead/commented-out, never actually shown.
- **User Actions Available**: A two-stage form — Stage 1 (`#birth-form`, visible by default) has a single Birth Registration No. input and a "Validate & Continue" button that fires a hand-rolled AJAX call (§3) before revealing Stage 2. Stage 2 (`#signup-form`, hidden until Stage 1 passes) has: Date of Birth (jQuery UI `DatePicker` widget, readonly text input), Birth Registration No. (readonly, pre-filled from Stage 1), Username, Email, Password, Confirm Password, CAPTCHA "Answer" input, **Signup** submit button, "If you already have account? Login" link (→ `candidate/login`, carrying `ceci` forward).
- **Blade/partial-equivalent includes used**: None — no `renderPartial`/`Html::include`-style embeds in this view.
- **JS/CSS loaded (layout-global, every `frontend/*` page via `AppNavyAsset` on `mainNavy.php`)**: `jquery-3.6.0.min.js`, `bootstrap.min.js`, `wow.min.js`, `main.js` (template boilerplate only — preloader, sticky header, back-to-top, WOW init; no auth-specific logic — `docs/00-inventories/javascript_inventory.md`), plus CSS `boxicons.min.css`, `bootstrap-icons.css`, `bootstrap.min.css` (v5.0.0-beta3), `animate.css`, `style.css`, `responsive.css`, `style_step.css` (`docs/00-inventories/css_inventory.md`).
- **Page-specific JS**: An inline `<style>` block (lines 11-21) toggling `.birth-form`/`.signup-form`/`.loading` visibility, plus an inline `$this->registerJs(...)` block (lines 106-172, rendered as a `<script>` at the bottom of `<body>`) that wires the Stage-1 "Validate & Continue" click handler (see §3).
- **AJAX endpoints it calls**:
  1. Hand-rolled: `POST /candidate/validate-birth-registration` (see §1.1a, §3) — fired from the inline `registerJs` block above, independent of Yii2's `ActiveForm`.
  2. Yii2-native: the Stage-2 form itself is `ActiveForm::begin(['id' => 'form-signup', 'enableAjaxValidation' => true])` (line 66-69) — `enableAjaxValidation: true` plus the framework default `enableClientValidation: true` means the form's own submit is intercepted client-side by `yii.activeForm.js` and, before a real POST, fires an AJAX validation request back to the same URL; the server-side branch for this is the `Yii::$app->request->isAjax` check at the very top of `actionSignUp()` (lines 62-65). **This directly qualifies `docs/00-inventories/javascript_inventory.md`'s claim** that `AppNavyAsset`'s `$depends` is fully commented out so "the public site does not load `yii.js`/`yii.activeForm.js`" — that's true of the asset **bundle**, but `yii\widgets\ActiveForm::run()` registers `ActiveFormAsset` (which depends on `yii\web\YiiAsset`) independently of any bundle's `$depends`, purely because `$enableClientScript` defaults to `true` on the widget itself (`vendor/yiisoft/yii2/widgets/ActiveForm.php:134,227-229,239-245`). So **this specific page does load `yii.js`+`yii.activeForm.js`+jQuery-via-`YiiAsset`, on top of the `jquery-3.6.0.min.js` `AppNavyAsset` already loads directly** — i.e. jQuery is loaded twice, once from each source, mirroring the double-jQuery pattern the JS inventory already documents for the backend admin theme.
- **Modals on this page**: None.

#### 1.1a Birth Registration Validation (AJAX helper action, no view)

- **URL**: `POST /candidate/validate-birth-registration`
- **Controller/Action**: `CandidateController::actionValidateBirthRegistration()` — `CandidateController.php:27-47`
- **Purpose**: Hand-rolled AJAX pre-check called only from `sign_up.php`'s inline script, gating Stage 1 → Stage 2.
- **Detailed Description**: Reads `birth_registration_no` from POST, builds a **throwaway** `new SignupForm()`, sets only `$model->birth_registration_no`, and calls `$model->validateBirthRegistration('birth_registration_no', [])` directly (bypassing `rules()`/`validate()` entirely — this is a bare inline-validator method call, not a full model validation pass). Returns `{success: bool, message: string}` JSON. `SignupForm::validateBirthRegistration()` (`SignupForm.php:114-136`) checks: not empty, numeric-only (`preg_match('/^[0-9]+$/')`), length 10-17 digits, and a **separate** uniqueness check (`User::find()->where(['birth_registration_no' => $birth_reg_no])->exists()`) that duplicates — via a completely different code path — the `['birth_registration_no', 'unique', ...]` rule already declared in `SignupForm::rules()` (line 36). Both the AJAX pre-check and the real POST-time `rules()` validation independently query the DB for uniqueness on the same field.

### 1.2 Candidate Login

- **Page Name**: Candidate Login
- **URL**: `/candidate/login` (optional `?ceci=` query param)
- **Controller/Action**: `CandidateController::actionLogin()` — `CandidateController.php:162-226`
- **Portal**: Frontend, guest-only (inline `!Yii::$app->user->isGuest` check, no `AccessControl`)
- **View File**: `frontend/views/candidate/login.php`
- **Layout Used**: `mainNavy`
- **Purpose**: Authenticate an existing candidate by username + password + arithmetic CAPTCHA.
- **Detailed Description**: GET renders the form with a fresh `captureValue()` CAPTCHA (line 224). POST: `$model = new LoginForm(); $model->user_type = 'candidate';` (lines 176-177), then `$model->load(post)`. Unlike `actionSignUp()`, **this action has no AJAX-validation branch at all** — there is no `Yii::$app->request->isAjax` check anywhere in `actionLogin()`, and `login.php`'s `ActiveForm::begin(['id' => 'login-form'])` (line 35) does **not** set `enableAjaxValidation`, so it defaults to `false`. (Client-side validation via `yii.activeForm.js` still fires on submit because `enableClientValidation` defaults to `true` regardless — see the AJAX-asset note in §1.1 — but no AJAX round-trip happens; a real synchronous POST is what ultimately reaches the server.) After `load()`, the same CAPTCHA-expiry/CAPTCHA-mismatch checks as signup run inline against `Yii::$app->session['captcha']` (lines 183-191, identical shape to §5.1). If those pass, `$model->login()` runs `LoginForm::validate()` (which includes `LoginForm::validatePassword()`, checking both the password hash and `$user->user_type == $this->user_type`) and, on success, calls `Yii::$app->user->login(...)`. The controller then manually inserts a `common\models\Session` row (`user_id`, `session_id` = `Yii::$app->session->getId()`, `expire = 2`) — a custom app-level session-tracking table, separate from PHP's native session store (lines 195-199). It then branches on `?ceci=` via `haveCeci()` exactly as in signup (§5.2); with no `ceci`, it redirects to `/my-application` (line 215) — note the commented-out `//return $this->goBack();` on line 216, meaning "return to the page you came from" was deliberately abandoned in favor of always landing on `/my-application`.
- **User Actions Available**: Username input, Password input, CAPTCHA "Answer" input, **Login** submit button, "reset it" link (→ `candidate/request-password-reset`, line 49). A commented-out duplicate of the same forgot-password link sits directly above it (lines 40-43) — leftover markup, not rendered.
- **JS/CSS loaded**: Same layout-global `AppNavyAsset` set as §1.1, plus the same `ActiveFormAsset`/`yii.activeForm.js` registration triggered by `ActiveForm::begin()` (client validation only here, no AJAX submit — see above).
- **AJAX endpoints it calls**: None (form is a standard, non-AJAX synchronous POST — see the `enableAjaxValidation` note above).
- **Modals on this page**: None. A hardcoded Bangla warning banner ("সতর্কবার্তা: ইউজারনেম ও পাসওয়ার্ড নিরাপদে রাখুন...") sits above the form (lines 18-34), styled inline via a page-local `<style>` block (`.custom-warning`).

### 1.3 Candidate Logout (no view — action route)

- **URL**: `/candidate/logout` (POST, submitted via a plain `Html::beginForm(['/candidate/logout'], 'post', ...)` in `mainNavy.php:105-111` — the logout link in the nav)
- **Controller/Action**: `CandidateController::actionLogout()` — `CandidateController.php:309-314`
- **Detailed Description**: `Session::updateAll(['expire' => '1'], ['user_id' => Yii::$app->user->identity->id])` marks the app-level `Session` rows expired for the current user, then `Yii::$app->user->logout()`, then `goHome()`. No dedicated view.

### 1.4 Candidate Change Password

- **Page Name**: Change Password
- **URL**: `/candidate/change-password`
- **Controller/Action**: `CandidateController::actionChangePassword()` — `CandidateController.php:374-400`
- **Portal**: Frontend, **authenticated only** — inline `if (Yii::$app->user->isGuest) return $this->goHome();` (line 376). No `AccessControl`; nothing in the view/layout hides the nav link from guests either — it's purely the controller's inline check that gates access, and a guest hitting the URL directly is silently redirected home rather than shown a 403.
- **View File**: `frontend/views/candidate/change_password.php`
- **Layout Used**: `mainNavy`
- **Purpose**: Let a logged-in candidate set a new password **without confirming the old one**.
- **Detailed Description**: GET renders the form. POST: AJAX-validation branch first (`Yii::$app->request->isAjax && $model->load(post)` → `ActiveForm::validate($model)` JSON, lines 382-385) — same pattern as signup, and `change_password.php`'s `ActiveForm::begin(['id' => 'update-password'])` (line 28) doesn't set `enableAjaxValidation` explicitly, so (as with login) client-side validation still fires via the default `enableClientValidation: true`, but no AJAX round-trip occurs on real submit. On a validated non-AJAX POST, the controller re-fetches the `User` row by `Yii::$app->user->identity->id`, sets `password_hash = Yii::$app->security->generatePasswordHash($model->newpassword)` directly (bypassing `User::setPassword()`, though it does the same thing), and saves. **There is no `old_password`/current-password field anywhere in `ChangePassword`'s properties, `rules()`, or the view** — a logged-in session alone is sufficient to overwrite the password; nothing re-confirms the user is still the account owner (e.g. no re-auth, no old-password check). This is a materially weaker gate than the officer-repo equivalent (`old_password: required` + `Hash::check` against the current password).
- **User Actions Available**: New Password input, Retype Password input, **Update** submit button.
- **JS/CSS loaded**: Layout-global `AppNavyAsset` set + `ActiveFormAsset` (client validation, no AJAX submit).
- **AJAX endpoints it calls**: None on real submit (AJAX branch exists server-side per above, but the view's `ActiveForm` doesn't opt into `enableAjaxValidation`).
- **Modals on this page**: None. A success flash (`Yii::$app->session->getFlash('valid')`) renders as a plain Bootstrap alert above the form (lines 22-26).

### 1.5 Candidate Forgot Password (request reset)

- **Page Name**: Request Password Reset
- **URL**: `/candidate/request-password-reset`
- **Controller/Action**: `CandidateController::actionRequestPasswordReset()` — `CandidateController.php:406-441`
- **Portal**: Frontend, guest-only (`if (!Yii::$app->user->isGuest) return $this->goHome();`, line 408)
- **View File**: `frontend/views/candidate/request_password_reset.php`
- **Layout Used**: `mainNavy`
- **Purpose**: Verify a candidate's identity by username + date of birth, then reset their password to a **randomly generated plaintext value shown directly on screen** — no OTP, no email, no reset token, no intermediate confirmation step.
- **Detailed Description**: GET renders the blank form. POST: AJAX-validation branch first (same pattern as above, lines 413-416). On a validated non-AJAX POST, it looks up `User::find()->where('username=:username', [...])->andWhere('dob=:dob', [':dob' => date('Y-m-d', strtotime($model->dob))])->one()` (lines 420-423) — **note this is a plain equality match against the `username`/`dob` columns; unlike `SignupForm::signup()`, which stores `phone_no` AES-encrypted, `username`/`dob` on `User` are stored and matched in plaintext, so this lookup works as written**. If a matching user is found: `$new_pass = rand(11111, 99999)` (a bare 5-digit integer, not passed through any password-strength rule), `Yii::$app->security->generatePasswordHash($new_pass)` is saved to `password_hash`, and the **plaintext** `$new_pass` is embedded directly into a success flash message (line 430): `'Your new Password is :<strong> <span style="color:red">' . $new_pass . '</span><strong>'` — malformed HTML too (`<strong>` opened twice, `</strong>` never closed; the closing tag is a second `<strong>` typo). No email is sent, no SMS, no confirmation step, no rate limiting of any kind (no daily-attempt cap, no CAPTCHA on this specific form, no throttle) — a correct username+dob guess is immediate full account takeover, on-screen. If no match: `Yii::$app->session->setFlash('invalid', 'Sorry! no record found.')`. If the save fails: a generic "internal server error" flash — this branch is unreachable in practice since `generatePasswordHash()` cannot fail and `save()` on a freshly-fetched `ActiveRecord` with only `password_hash` changed essentially never fails validation (the model's `rules()` don't re-validate on a partial `save()` in a way that would trip here).
- **User Actions Available**: Username input, Date of Birth input (jQuery UI `DatePicker`), **Update** submit button.
- **JS/CSS loaded**: Layout-global `AppNavyAsset` set + `ActiveFormAsset` (client validation only, no `enableAjaxValidation` set in the view).
- **AJAX endpoints it calls**: None on real submit.
- **Modals on this page**: None. Success (`valid`) and failure (`invalid`) flashes render as Bootstrap alerts above the form (lines 20-30).

---

## 1.6–1.11 Dead/Vestigial `SiteController` Auth Actions (not reachable from any live UI)

Documented briefly for completeness since they live in the same controller family and the task calls them out explicitly; **none of these are linked from anywhere a real candidate can click**, per the analysis in the introduction.

| Action | Route | Form model | View | Status |
|---|---|---|---|---|
| `actionLogin()` | `/site/login` | `common\models\LoginForm` | `frontend/views/site/login.php` | Unreachable from nav; **broken even if hit directly** — `LoginForm::rules()` requires `captcha` (`LoginForm.php:32`) but the view has no CAPTCHA field, so the form can never pass validation. |
| `actionSignup()` | `/site/signup` (guest-only, per its `AccessControl`) | `frontend\models\SignupForm` (same class `CandidateController::actionSignUp()` uses) | `frontend/views/site/signup.php` | Unreachable from nav; **broken even if hit directly** — `SignupForm::rules()` requires `dob`, `birth_registration_no`, `captcha` (`SignupForm.php:34`) but the view only has `username`/`email`/`password` inputs. |
| `actionLogout()` | `/site/logout` (POST, authenticated-only per `AccessControl`) | — | — | Would technically work (`Yii::$app->user->logout(); goHome();`) but nothing links to it — the nav's logout button posts to `/candidate/logout` instead (`mainNavy.php:105`). |
| `actionRequestPasswordReset()` | `/site/request-password-reset` | `frontend\models\PasswordResetRequestForm` | `frontend/views/site/requestPasswordResetToken.php` | Unreachable from nav. Stock Yii2 flow: emails a `password_reset_token` link via `Yii::$app->mailer`. Schema-compatible (`User.password_reset_token` column exists and is used consistently, `common/models/User.php:21,79,171-181`) but entirely redundant with — and never cross-linked to — `CandidateController::actionRequestPasswordReset()`'s plaintext-reset flow (§1.5). Two independent, non-interoperating "forgot password" mechanisms exist in this codebase; only one is used. |
| `actionResetPassword($token)` | `/site/reset-password/{token}` | `frontend\models\ResetPasswordForm` | `frontend/views/site/resetPassword.php` | Companion to the action above — token-gated password-set form. Unreachable from nav (the only way to reach it is a link inside the email `actionRequestPasswordReset()` above would send, and nothing ever triggers that email in practice). |
| `actionVerifyEmail($token)` / `actionResendVerificationEmail()` | `/site/verify-email/{token}`, `/site/resend-verification-email` | `frontend\models\VerifyEmailForm` / `ResendVerificationEmailForm` | (no view for verify-email — redirects immediately) / `frontend/views/site/resendVerificationEmail.php` | Unreachable from nav. Moot regardless: `SignupForm::signup()` sets `status = User::STATUS_ACTIVE` immediately on registration (`SignupForm.php:164`) rather than `STATUS_INACTIVE` pending verification, and the `sendEmail($user)` call inside `signup()` is commented out (`model_inventory.md` §finding 6) — accounts are never actually put into an unverified state that this flow would need to resolve. |

`SiteController::actions()` also registers Yii2's built-in image-CAPTCHA action (`'captcha' => \yii\captcha\CaptchaAction`, `SiteController.php:64-67`) — grepping `frontend/views/` confirms it is used only by `frontend/views/site/contact.php` (`use yii\captcha\Captcha;`, out of this scope), never by any login/signup view. The actual candidate-auth CAPTCHA everywhere in scope is the fully custom arithmetic scheme in §5.1, not this built-in widget.

---

## 2. Form Documentation

### 2.1 Sign-Up Form

- **Form Name**: `form-signup` (id `#form-signup`, Stage 2 of `sign_up.php`; a separate `#form-birth-validation` covers Stage 1)
- **Page**: Candidate Sign Up (`sign_up.php`)
- **Action URL**: current URL (`ActiveForm::begin` defaults to `Yii::$app->request->url`) → `/candidate/sign-up`
- **HTTP Method**: POST
- **Controller@Method**: `CandidateController::actionSignUp()`
- **Form model**: `frontend\models\SignupForm`. Exact rules (`SignupForm.php:28-61`):
```php
return [
    ['username', 'trim'],
    [['username', 'dob', 'captcha', 'birth_registration_no'], 'required'],
    ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
    ['birth_registration_no', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This Birth Registration No has already been taken.'],
    ['username', 'usernameValidation'],
    ['email', 'trim'],
    ['email', 'email'],
    ['email', 'string', 'max' => 255],
    [['password', 'confirm_password'], 'required'],
    ['password', 'string', 'min' => 6, 'max' => 15],
    ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => "Passwords don't match"],
];
```
  Note `email` has no `required` rule (only `trim`/`email`/`max 255`) — an empty email is valid input, in which case `signup()` falls back to `$this->username . '@register.com'` as a synthetic address (`SignupForm.php:156`). `usernameValidation()` (lines 64-94, custom inline validator) additionally enforces: 6-15 characters, at least one letter (`preg_match('/[a-zA-Z]/')`), at least one digit (`preg_match('/\d/')`), and no whitespace (`preg_match('/^\S*$/')`) — a special-character requirement is present in a commented-out `elseif` (line 74) but not enforced. **`username` here is a free-form alphanumeric handle, not a phone number** — this is a genuine schema/UX difference from the officer-repo counterpart's phone-number-based login; do not assume parity between the two apps' auth models.

| Field | Label/Placeholder | Type | Required | Default | Validation | Datasource | Dependencies |
|---|---|---|---|---|---|---|---|
| `birth_registration_no` (Stage 1) | "Birth Registration No" | text | Required | none | Client: hand-rolled AJAX pre-check (§1.1a) — numeric, 10-17 digits, uniqueness. Server (on real submit): `required` + `unique` against `User.birth_registration_no` (both independently re-checked — two separate DB round-trips for the same fact) | N/A | Gates whether Stage 2 of the form is shown at all (client-side only; nothing stops a POST with a bad value being sent directly, bypassing Stage 1's gate, since the server-side `rules()` re-validate independently) |
| `dob` | "Date of Birth" | text (jQuery UI `DatePicker`, `readonly`, format `dd-MM-yyyy`) | Required | Pre-filled from `CanEligibilityCheckInfo.dob` when `?ceci=` present (`actionSignUp()`, lines 110-114), else empty | `required` (no `date`/`date_format` rule — format is enforced only by the read-only date-picker widget on the client, not re-validated server-side) | N/A | Value can arrive pre-filled from a prior eligibility-check session step |
| `username` | "Username" | text | Required | none | `trim`, `required`, `unique` (against `User`), custom `usernameValidation` (6-15 chars, ≥1 letter, ≥1 digit, no whitespace) | N/A | none |
| `email` | "Email" | text | **Not required** | none (synthesized as `{username}@register.com` in `signup()` if left blank) | `trim`, `email`, `max 255` — no `unique` check (commented out, `SignupForm.php:47`) | N/A | none |
| `password` | "Password" | password | Required | none | `required`, `string` min 6 / max 15 | N/A | none |
| `confirm_password` | "Confirm Password" | password | Required | none | `required`, `compare` against `password` | N/A | Must equal `password` |
| `captcha` | "Answer" | text | Required | none | `required` (client + server via `rules()`); server ALSO independently checks the value against `Yii::$app->session['captcha']['capture_value_result']`, outside `rules()`, in the controller (§5.1) | Server-generated in `captureValue()`; operands/operator echoed as plain text (`sign_up.php:91`, `implode('', $captcha)`) | Depends on the session CAPTCHA set at GET-render time; a >1-minute-old session triggers "Please refresh page and try again." regardless of the typed answer's correctness |

No conditional show/hide logic beyond the Stage-1/Stage-2 birth-registration gate described above; no track/designation-specific branching in the signup form itself (that happens later, in `haveCeci()`, §5.2).

### 2.2 Login Form

- **Form Name**: `login-form`
- **Page**: Candidate Login (`login.php`)
- **Action URL**: current URL → `/candidate/login`
- **HTTP Method**: POST
- **Controller@Method**: `CandidateController::actionLogin()`
- **Form model**: `common\models\LoginForm`. Exact rules (`LoginForm.php:27-38`):
```php
return [
    ['username', 'trim'],
    [['username', 'password', 'captcha'], 'required'],
    ['rememberMe', 'boolean'],
    ['password', 'validatePassword'],
];
```
  `validatePassword()` (lines 58-72) is a custom inline validator: looks up `User::findByUsername($this->username)` (which itself filters `status = STATUS_ACTIVE`, `User.php:160-163`), calls `$user->validatePassword($this->password)` (bcrypt-style hash check via `Yii::$app->security`), and separately checks `$user->user_type != $this->user_type` — the controller hardcodes `$model->user_type = 'candidate'` before `load()` (`CandidateController.php:177`), so this rejects credentials belonging to a non-candidate `user_type` (e.g. an admin account) with "You are not candidate user!".

| Field | Label/Placeholder | Type | Required | Default | Validation | Datasource | Dependencies |
|---|---|---|---|---|---|---|---|
| `username` | "Username" | text | Required | none | `trim`, `required` | N/A | Looked up via `User::findByUsername` (active-status users only) |
| `password` | "Password" | password | Required | none | `required`, `validatePassword` (hash check + `user_type` match) | N/A | none |
| `captcha` | "Answer" | text | Required | none | `required` (model), plus manual session-CAPTCHA expiry/match check in the controller (identical mechanism to signup, §5.1) | Server-generated via `captureValue()` on every GET | Depends on `Yii::$app->session['captcha']` set at GET time |

`rememberMe` is a model property (`public $rememberMe = true`, defaulted `true` in code) but **has no corresponding form field in `login.php`** — the view never renders a checkbox for it, so it's always `true` for every login regardless of user intent (`Yii::$app->user->login(...)`'s expire argument becomes `3600*24*30` unconditionally, `LoginForm.php:87`). No conditional show/hide logic on this form.

### 2.3 Change Password Form

- **Form Name**: `update-password`
- **Page**: Change Password (`change_password.php`)
- **Action URL**: current URL → `/candidate/change-password`
- **HTTP Method**: POST
- **Controller@Method**: `CandidateController::actionChangePassword()`
- **Form model**: `common\models\ChangePassword`. Exact rules (`ChangePassword.php:13-20`):
```php
return [
    [['newpassword', 'repeatnepassword'], 'string', 'min' => 6],
    [['newpassword', 'repeatnepassword'], 'required'],
    ['repeatnepassword', 'compare', 'compareAttribute' => 'newpassword'],
];
```

| Field | Label/Placeholder | Type | Required | Default | Validation | Datasource | Dependencies |
|---|---|---|---|---|---|---|---|
| `newpassword` | "New Password" | password | Required | none | `required`, `string` min 6 (**no max**, unlike signup's password field which caps at 15) | N/A | none |
| `repeatnepassword` | "Retype Password" | password | Required | none | `required`, `compare` against `newpassword` | N/A | Must equal `newpassword` |

**No `old_password`/current-password field exists on this model or in this view at all** — see the finding in §1.4. Gate is session-authentication only, no re-confirmation of account ownership. No conditional show/hide logic; no CAPTCHA on this form (only the guest-facing forms in this controller have one).

### 2.4 Forgot Password / Request Reset Form

- **Form Name**: `update-password` (id reused from the change-password template; functionally this is the forgot-password form)
- **Page**: Forgot Password (`request_password_reset.php`)
- **Action URL**: current URL → `/candidate/request-password-reset`
- **HTTP Method**: POST
- **Controller@Method**: `CandidateController::actionRequestPasswordReset()`
- **Form model**: `common\models\ResetPassword`. Exact rules (`ResetPassword.php:13-20`):
```php
return [
    [['username', 'dob'], 'required'],
];
```
  This model has **no `password`, `new_password`, or `otp`/token field of any kind** — by design, this endpoint's job is only to verify identity (username + dob), after which the controller generates and displays the new password itself (§1.5).

| Field | Label/Placeholder | Type | Required | Default | Validation | Datasource | Dependencies |
|---|---|---|---|---|---|---|---|
| `username` | "Username" | text | Required | none | `required` (no format constraint — any non-empty string is accepted by the model; matching against `User.username` happens as a raw equality lookup in the controller, not as a model rule) | N/A | Combined lookup key with `dob` against the `user` table |
| `dob` | "Date of Birth" | text (jQuery UI `DatePicker`) | Required | none | `required` | N/A | Must match the same `User` row's `dob` (plain-value equality, `strtotime`-normalized to `Y-m-d` before comparison) |

No CAPTCHA on this form (unlike signup/login). No conditional show/hide logic. No rate limiting of any kind on this endpoint — see §6.

---

## 3. Frontend Business Logic (JS / AJAX Triggers)

Across the five in-scope `CandidateController` views, JS/AJAX behavior is mixed — not uniformly "dead client validation, all hand-rolled AJAX" as the blanket statement in `docs/00-inventories/javascript_inventory.md` (written from the asset-bundle level, i.e. `AppNavyAsset::$depends` being commented out) would suggest at a glance. Reading the actual views shows two independent mechanisms coexist:

1. **Yii2's own `ActiveForm` client/AJAX validation is alive on every form in this scope.** Every `ActiveForm::begin()` call here (`login.php:35`, `sign_up.php:32,66`, `change_password.php:28`, `request_password_reset.php:32`) triggers `yii\widgets\ActiveForm::run()`, which (per `vendor/yiisoft/yii2/widgets/ActiveForm.php:134,227-229,239-245`) registers `ActiveFormAsset` — and therefore `yii.js`+`yii.activeForm.js`+jQuery-via-`YiiAsset` — **regardless of `AppNavyAsset`'s own `$depends` array**, because that registration is driven by the widget's own `$enableClientScript` default (`true`), not by any parent asset bundle's dependency graph. Only `sign_up.php`'s Stage-2 form additionally sets `'enableAjaxValidation' => true` (line 68), which is what makes `CandidateController::actionSignUp()`'s `Yii::$app->request->isAjax` branch (lines 62-65) actually reachable in practice — the other three forms' controllers have the identical `isAjax` branch pattern present in code, but their views never opt into `enableAjaxValidation`, so those branches are effectively dead at runtime (real users never trigger them; only a hand-crafted AJAX request would).
2. **A separate, fully hand-rolled jQuery `$.ajax()` call exists only on the signup page**, for the Birth-Registration pre-check (`sign_up.php:106-172`, via `$this->registerJs(...)`):
```js
$.ajax({
    url: '<?= Yii::$app->urlManager->createUrl(['candidate/validate-birth-registration']) ?>',
    type: 'POST',
    data: { birth_registration_no: birthRegNo, _csrf: yii.getCsrfToken() },
    success: function(response) { /* toggle #birth-form / #signup-form, prefill the field */ },
});
```
This is the pattern the JS inventory describes elsewhere in the app (custom endpoint, custom JSON contract, no Yii2 machinery) — it coexists on the same page as mechanism #1 above, targeting a different field. Note the `_csrf: yii.getCsrfToken()` token is sent, but `frontend/config/main.php:27` sets `'enableCsrfValidation' => false` for the whole frontend `request` component — **CSRF validation is disabled app-wide on the frontend**, so this token is inert cargo on every POST across all forms in this scope (signup, login, change-password, forgot-password all included).

No other inline `<script>` in scope beyond the layout-global mobile-menu toggle in `mainNavy.php:241-253` (unrelated to auth) and the Google Analytics snippet in `mainNavy.php:29-39`.

---

## 4. Modal Audit

**No modals exist anywhere in this scope.** None of the five `CandidateController` views (`sign_up`, `login`, `change_password`, `request_password_reset`) or the six dead `SiteController` views contain a `modal`-classed element, a `data-bs-toggle="modal"` trigger, or any JS-driven dialog. Every interaction is a full-page navigation (GET render / POST submit / redirect), aside from the two AJAX calls noted in §3 (neither of which opens a modal — both just toggle inline `<div>` visibility or return JSON consumed by `yii.activeForm.js`'s own inline error rendering).

---

## 5. Supporting Controller Logic (shared across multiple pages in scope)

### 5.1 `captureValue()` — arithmetic CAPTCHA (`CandidateController.php:122-156`, `protected`)

Used by `actionSignUp()` (GET) and `actionLogin()` (GET) — and re-invoked on validation-failure re-renders within either action's POST branch — to generate a 3-element array `[operand1, operator, operand2]` (`+`/`-`/`*`, two random 1-9 operands), compute the result, and store it in session:
```php
$session['captcha'] = [
    'capture_value_result' => $result,
    'capture_value_result_exp' => time() + (1 * 60),  // 1 min
];
```
The raw array is returned to the view and rendered as plain text via `implode('', $captcha)` (`sign_up.php:91`, `login.php:38`) — the candidate must type the arithmetic answer into the `captcha` field. The 1-minute expiry means a candidate who takes over a minute to fill either form gets "Please refresh page and try again." regardless of whether their answer was arithmetically correct. This CAPTCHA check happens **entirely outside** `SignupForm`/`LoginForm`'s `rules()` — it's a manual `session()` comparison inline in the controller (`CandidateController.php:71-78` for signup, `183-191` for login), structurally identical in both actions (copy-pasted, not shared via a common helper beyond `captureValue()` itself). The near-identical CAPTCHA mechanism is duplicated a third time in `backend/controllers/SiteController.php::captureValue()` for the admin login page (out of scope here, per `controller_inventory.md`).

### 5.2 `haveCeci($ceci)` — post-auth redirect / application-record bootstrap (`CandidateController.php:229-302`, public but not an `action*` method, not directly routable)

Invoked from both `actionSignUp()` and `actionLogin()` only when a `?ceci=` query param is present, i.e. only when the candidate arrived at signup/login from the public eligibility-check flow rather than directly. Logic:
1. Decrypt `$ceci` → look up the `CanEligibilityCheckInfo` row.
2. Determine `sailor_or_de_sailor` by checking whether `apply_department_type` is one of `Constants::CANDIDATE_DE_SAILOR` / `CANDIDATE_DE_SAILOR_DOCKYARD` — if so, `'de_sailor'`, else defaults to `'sailor'`.
3. Count prior `Sailors`/`DeSailors` rows for this `eligibility_info_id`. If any exist, short-circuit to `page = 'go-home'` (callers turn this into a flash "Please check eligibility again for apply" + redirect to `/my-application`).
4. Otherwise resolve `batch_and_config_id` via `SailorBatchConfiguration::batchIdAndCenterIdByApplyDistrictAndDepartment(...)` and check `Sailors::numberOfApplication()` / `DeSailors::numberOfApplication()` for that batch.
5. If not already applied to that batch, create a new `Sailors`/`DeSailors` row pre-populated from the eligibility-check answers — including "posso kotha" (child-of-naval-officer) and departmental-candidate branching (`Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA` / `_DEPARTMENTAL`, lines 278-286), each setting `naval_office_no`/`naval_rank` from the eligibility row. This is the only track-branching logic touched by this module — it happens entirely server-side after successful auth, invisible to the signup/login views themselves.
6. If already applied, `page = 'application-list'`.

The calling `actionSignUp()`/`actionLogin()` methods then branch on `$return['page']` to redirect to a sailor/de-sailor payment page, `/my-application`, or `/my-application` with a "check eligibility again" flash. This logic is duplicated verbatim (not shared via a common trait/base) between the two calling actions in the controller.

---

## 6. Cross-Cutting Notes / Anomalies Found

1. **Two independent, non-interoperating password-reset flows exist.** `CandidateController::actionRequestPasswordReset()` (live, linked from nav) resets to a random plaintext-shown password with zero rate limiting; `SiteController::actionRequestPasswordReset()`/`actionResetPassword()` (dead, unlinked) is the stock Yii2 email-token flow, schema-compatible but never reachable. Neither references the other.
2. **`CandidateController::actionRequestPasswordReset()` has no rate limiting whatsoever** — no daily-attempt cap, no CAPTCHA on this specific form, no throttle middleware. A correct `username`+`dob` guess is immediate, on-screen, plaintext account takeover; `dob` in particular is comparatively guessable/brute-forceable for a determined attacker with a known username.
3. **`CandidateController::actionChangePassword()` has no old-password confirmation.** `ChangePassword`'s `rules()` and view have no `old_password` field at all — session authentication alone is the only gate on changing a candidate's password.
4. **`SiteController`'s entire auth surface (`actionLogin`, `actionSignup`, `actionRequestPasswordReset`, `actionResetPassword`, `actionVerifyEmail`, `actionResendVerificationEmail`) is dead/vestigial Yii2 scaffold**, confirmed unreachable via `frontend/config/main.php:38`'s `loginUrl`, via every link in `mainNavy.php` (the app's only layout), and via a repo-wide grep for any route reference to `site/login`/`site/signup` outside the controller/its own views/a stale Codeception test. Two of its actions (`actionLogin`, `actionSignup`) are additionally **broken as shipped** — their views are missing form fields (`captcha`; `dob`/`birth_registration_no`/`confirm_password`/`captcha` respectively) that the shared form models require, so neither can ever pass server-side validation even if reached directly by URL guess.
5. **Frontend CSRF validation is globally disabled** (`frontend/config/main.php:27`, `'enableCsrfValidation' => false`) — every form in this scope (and every other frontend form in the app) submits without CSRF protection; the `_csrf: yii.getCsrfToken()` token sent by the hand-rolled birth-registration AJAX call (§3) is inert.
6. **`LoginForm::login()` makes a synchronous third-party HTTP call on every successful login** — `getLoginAddress($ip)` (`LoginForm.php:94-104`) does `file_get_contents("http://ipinfo.io/" . $ip)` (plain HTTP, no auth, no timeout, `@`-suppressed errors) purely to populate a `login_zone` display string; if `ipinfo.io` is slow or unreachable, every candidate login stalls on this call (flagged independently in `model_inventory.md` §5).
7. **The signup CAPTCHA field is double-validated for the same fact via two different code paths**: `SignupForm::rules()`'s `unique` rule against `birth_registration_no`, and the separate hand-rolled `validateBirthRegistration()` method invoked both by the AJAX pre-check (§1.1a) and — since `usernameValidation`/rules run again on the real POST — implicitly again at submit time. Same duplication pattern for `username` uniqueness (checked once in `rules()`, and the DB is hit again for the same `User::findByUsername` lookup inside `LoginForm::validatePassword()` on the login side, which is expected/necessary there but worth noting as a second, unrelated per-request User lookup pattern across this module).
8. **`CandidateController::actionSignUp()` re-queries the `User` it just created** instead of reusing the object `SignupForm::signup()` already returns — `$identity = User::findOne(['username' => $model['username']])` (`CandidateController.php:82`) immediately after `$model->signup()` succeeds, a redundant round-trip.
9. **`LoginForm::$rememberMe` defaults `true` and has no corresponding form field** in `candidate/login.php` — every login is treated as "remember me" (30-day cookie expiry) regardless of user intent, since there's no checkbox for the candidate to opt out.
10. **`SignupForm::signup()` activates the account immediately** (`status = User::STATUS_ACTIVE`) rather than leaving it pending email verification, even though the unreachable `SiteController::actionVerifyEmail()`/`actionResendVerificationEmail()` machinery exists for exactly that inactive-until-verified pattern — moot here since it's never wired up (see item 4 and `model_inventory.md` finding 6).
