<?php
require_once __DIR__ . '/../pricing.php';

$PageTitle = "Sign Up | Create Your Account";
$publicPage = true;
$meta_description = "Join Coherta today and start building your lead engine. Create your account in three simple steps and unlock powerful tools for lead generation.";
include('partial/header.php');
$linkedinAccessToken = "";
?>

<div class="signup-page">
    <div class="signup-aside d-none d-lg-flex">
        <div class="signup-aside-inner">
            <a class="signup-logo" href="/">
                <img class="img-fluid for-light" src="/assets/img/logo/logo.png?w=300" alt="Coherta" width="3200" height="715">
                <img class="img-fluid for-dark" src="/assets/img/logo/logo.png?w=300" alt="Coherta" width="3200" height="715">
            </a>

            <div class="signup-aside-copy">
                
                <h1>Build your lead engine today.</h1>
            </div>

            <div class="signup-benefits">
                <div class="benefit-card active" data-aside-step="1">
                    <div class="benefit-icon-wrap">
                        <div class="benefit-icon">1</div>
                        <div class="benefit-line"></div>
                    </div>
                    <div>
                        <span class="benefit-status">Current step</span>
                        <strong>Create your workspace</strong>
                        <p>Add the essentials so your account is ready immediately.</p>
                    </div>
                </div>
                <div class="benefit-card" data-aside-step="2">
                    <div class="benefit-icon-wrap">
                        <div class="benefit-icon">2</div>
                        <div class="benefit-line"></div>
                    </div>
                    <div>
                        <span class="benefit-status">Up next</span>
                        <strong>Set your company profile</strong>
                        <p>We can tailor the experience to your team, market, and goals.</p>
                    </div>
                </div>
                <div class="benefit-card" data-aside-step="3">
                    <div class="benefit-icon-wrap">
                        <div class="benefit-icon">3</div>
                    </div>
                    <div>
                        <span class="benefit-status">Final step</span>
                        <strong>Complete billing securely</strong>
                        <p>Revolut handles payment details so activation feels fast and trustworthy.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="signup-main-area">
        <div class="container-fluid p-0 signup-shell">
            <div class="row g-0">
                <div class="col-lg-5 d-none d-lg-block"></div>

                <div class="col-12 col-lg-7 signup-main-wrap">
                    <div class="signup-main">
                        <div class="signup-mobile-header d-lg-none">
                            <a class="signup-logo" href="/">
                                <img class="img-fluid for-light" src="/assets/img/logo/logo.png?w=220" alt="Coherta" width="3200" height="715">
                                <img class="img-fluid for-dark" src="/assets/img/logo/logo.png?w=220" alt="Coherta" width="3200" height="715">
                            </a>
                        </div>

                        <div class="signup-card">
                            <div class="signup-card-head">
                                <div>
                                    <h2>Create your account</h2>
                                    <p>Three short steps and you are ready to activate your workspace.</p>
                                </div>
                                <div class="signup-steps-meta">
                                    <span id="currentStepLabel">Step 1 of 3</span>
                                </div>
                            </div>

                            <div class="signup-progress">
                                <div class="signup-progress-track">
                                    <div class="signup-progress-bar" id="signupProgressBar"></div>
                                </div>
                                <div class="signup-step-dots">
                                    <span class="step-dot active" data-step-dot="1"></span>
                                    <span class="step-dot" data-step-dot="2"></span>
                                    <span class="step-dot" data-step-dot="3"></span>
                                </div>
                            </div>

                            <form id="signupForm" class="theme-form signup-form" novalidate>
                                <section class="signup-step active" data-step="1">
                                    <div class="step-title-wrap">
                                        <h3>Your account details</h3>
                                        <p>Only the basics needed to create your login and workspace owner profile.</p>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">First name</label>
                                            <input class="form-control" type="text" name="first_name" required placeholder="Jens">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Last name</label>
                                            <input class="form-control" type="text" name="last_name" required placeholder="Andersen">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Work email</label>
                                            <input class="form-control" type="email" name="email" required placeholder="name@company.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input class="form-control" type="tel" name="phone" required placeholder="+45 12 34 56 78">
                                        </div>
                                        <div class="col-md-6" style="display:none">
                                            <label class="form-label">Work Phone</label>
                                            <input class="form-control" type="tel" name="work_phone" placeholder="+45 12 34 56 78">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Password</label>
                                            <div class="position-relative">
                                                <input class="form-control" type="password" name="password" id="passwordField" required minlength="8" placeholder="Minimum 8 characters">
                                                <button type="button" class="password-toggle" id="togglePassword">Show</button>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="signup-step" data-step="2">
                                    <div class="step-title-wrap">
                                        <h3>Tell us about your company</h3>
                                        <p>This helps tailor onboarding, defaults, and recommendations from the start.</p>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label">Company name</label>
                                            <input class="form-control" type="text" name="company_name" required placeholder="Coherta ApS">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Team size</label>
                                            <select class="form-control" name="team_size" required>
                                                <option value="">Select size</option>
                                                <option value="1">Just me</option>
                                                <option value="2-10">2-10</option>
                                                <option value="11-25">11-25</option>
                                                <option value="26-50">26-50</option>
                                                <option value="51-200">51-200</option>
                                                <option value="200+">200+</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Company website</label>
                                            <input class="form-control" type="text" name="website" inputmode="url" placeholder="company.com">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Country / market</label>
                                            <select class="form-control" name="country" required>
                                                <option value="">Select market</option>
                                                <option value="DK">Denmark</option>
                                                <option value="SE">Sweden</option>
                                                <option value="NO">Norway</option>
                                                <option value="GB">United Kingdom</option>
                                                <option value="EU">Europe</option>
                                                <option value="OTHER">Other</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">CVR / VAT number</label>
                                            <input class="form-control" type="text" name="vat_code" placeholder="Optional">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Primary use case</label>
                                            <select class="form-control" name="use_case" required>
                                                <option value="">Select use case</option>
                                                <option value="lead_generation">Lead generation</option>
                                                <option value="outreach">Sales outreach</option>
                                                <option value="recruitment">Recruitment</option>
                                                <option value="enrichment">CRM enrichment</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">What do you want to achieve first?</label>
                                            <textarea class="form-control" name="goal" rows="4" placeholder="Example: Build a qualified list of Danish ecommerce companies and start outreach within a week."></textarea>
                                        </div>
                                    </div>
                                </section>

                                <section class="signup-step" data-step="3">
                                    <div class="step-title-wrap">
                                        <h3>Billing details</h3>
                                        <p>Choose your plan and continue to secure checkout through Revolut.</p>
                                    </div>

                                    <div class="billing-toggle">
                                        <button type="button" class="billing-switch" data-billing-cycle="monthly">Monthly</button>
                                        <button type="button" class="billing-switch active" data-billing-cycle="annual">Annual <span>Save 20%</span></button>
                                    </div>

                                    <input type="hidden" name="billing_cycle" id="billingCycle" value="annual">
                                    <input type="hidden" name="plan" id="selectedPlan" value="growth">

                                    <div class="row g-3 plan-grid">
                                        <div class="col-md-6">
                                            <button type="button" class="plan-card active" data-plan="growth">
                                                <div class="plan-card-top">
                                                    <h4>Standard</h4>
                                                    <span class="plan-badge featured">Most popular</span>
                                                </div>
                                                <div class="plan-price">
                                                    <strong data-plan-price="growth" data-monthly="999" data-annual="799" data-currency-prefix="" data-currency-suffix=" DKK">999 DKK</strong>
                                                    <span>/month</span>
                                                </div>
                                                <ul>
                                                    <li>Advanced workflows</li>
                                                    <li>Better onboarding defaults</li>
                                                    <li>Faster team rollout</li>
                                                </ul>
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="plan-card" data-plan="extended">
                                                <div class="plan-card-top">
                                                    <h4>Extended</h4>
                                                </div>
                                                <div class="plan-price">
                                                    <strong data-plan-price="extended" data-monthly="4198" data-annual="3499" data-currency-prefix="" data-currency-suffix=" DKK">4198 DKK</strong>
                                                    <span>/month</span>
                                                </div>
                                                <ul>
                                                    <li>Advanced workflows</li>
                                                    <li>Better onboarding defaults</li>
                                                    <li>Faster team rollout</li>
                                                </ul>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="stripe-note">
                                        <div class="stripe-note-icon">🔒</div>
                                        <div>
                                            <strong>Secure billing with Revolut</strong>
                                            <p>When you click continue, we create your account and redirect for payment details securely.</p>
                                        </div>
                                    </div>
                                </section>

                                <div class="signup-alert" id="signupAlert" hidden></div>

                                <div class="signup-actions">
                                    <button type="button" class="btn btn-light" id="prevStepBtn" hidden>Back</button>
                                    <button type="button" class="btn btn-primary" id="nextStepBtn">Continue</button>
                                    <button type="submit" class="btn btn-primary" id="submitSignupBtn" hidden>Create Account</button>
                                </div>

                                <div class="signup-footer-note">
                                    <div class="checkbox p-0 terms-consent">
                                        <input id="termsCheckbox" type="checkbox" required autocomplete="off">
                                        <label class="text-muted" for="termsCheckbox">
                                            I agree to the <a class="ms-1" href="/da/forretningsbetingelser">Terms</a> and
                                            <a class="ms-1" href="/da/privacy-policy">Privacy Policy</a>
                                        </label>
                                    </div>
                                    <p class="mb-0">Already have an account? <a href="login">Sign in</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .signup-page {
        min-height: 100vh;
        background:
                radial-gradient(circle at top left, rgba(79, 70, 229, 0.08), transparent 28%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.10), transparent 24%),
                #f8fafc;
    }

    .signup-shell {
        min-height: 100vh;
        background: transparent;
    }

    .signup-aside {
        position: fixed;
        top: 0;
        left: 0;
        width: 41.66666667%;
        height: 100vh;
        background: linear-gradient(160deg, #0f172a 0%, #111827 45%, #1d4ed8 100%);
        color: #fff;
        overflow: hidden;
        z-index: 2;
    }

    .signup-aside::before,
    .signup-aside::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        filter: blur(24px);
        opacity: 0.45;
    }

    .signup-aside::before {
        width: 260px;
        height: 260px;
        background: rgba(59, 130, 246, 0.35);
        top: -80px;
        right: -50px;
    }

    .signup-aside::after {
        width: 220px;
        height: 220px;
        background: rgba(168, 85, 247, 0.28);
        bottom: -70px;
        left: -40px;
    }

    .signup-aside-inner {
        position: relative;
        z-index: 1;
        padding: 56px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 40px;
    }

    .signup-logo img {
        max-width: 220px;
    }

    .signup-aside-copy h1 {
        color: #fff;
        font-size: 42px;
        line-height: 1.1;
        margin-bottom: 0;
    }

    .signup-benefits {
        display: grid;
        gap: 16px;
    }

    .benefit-card {
        display: grid;
        grid-template-columns: 52px 1fr;
        gap: 14px;
        padding: 18px;
        border-radius: 24px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.12);
        backdrop-filter: blur(8px);
        transition: all .25s ease;
    }

    .benefit-card.active {
        background: rgba(255,255,255,.16);
        border-color: rgba(255,255,255,.28);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        transform: translateX(6px);
    }

    .benefit-card.completed {
        background: rgba(34, 197, 94, .12);
        border-color: rgba(134, 239, 172, .28);
    }

    .benefit-icon-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .benefit-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.12);
        font-weight: 700;
        font-size: 18px;
        transition: all .25s ease;
    }

    .benefit-card.active .benefit-icon {
        background: #fff;
        color: #1d4ed8;
        transform: scale(1.04);
    }

    .benefit-card.completed .benefit-icon {
        background: rgba(134, 239, 172, .18);
        color: #dcfce7;
    }

    .benefit-line {
        width: 2px;
        flex: 1;
        min-height: 28px;
        background: rgba(255,255,255,.18);
        border-radius: 999px;
    }

    .benefit-card:last-child .benefit-line {
        display: none;
    }

    .benefit-status {
        display: inline-flex;
        align-items: center;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255,255,255,.62);
    }

    .benefit-card.active .benefit-status {
        color: #bfdbfe;
    }

    .benefit-card.completed .benefit-status {
        color: #bbf7d0;
    }

    .benefit-card strong {
        display: block;
        font-size: 16px;
        margin-bottom: 4px;
        color: #fff;
    }

    .benefit-card p {
        margin: 0;
        color: rgba(255,255,255,.72);
        font-size: 14px;
    }

    .benefit-icon {
        position: relative;
        overflow: hidden;
    }

    .benefit-icon-number,
    .benefit-icon-check {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        transition: opacity .2s ease, transform .2s ease;
    }

    .benefit-icon-number {
        opacity: 1;
        transform: scale(1);
    }

    .benefit-icon-check {
        position: absolute;
        inset: 0;
        opacity: 0;
        transform: scale(.7);
    }

    .benefit-card {
        transition: border-color .2s ease, background .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .benefit-card.is-current {
        background: rgba(255,255,255,.14);
        border-color: rgba(255,255,255,.34);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        transform: translateX(8px);
    }

    .benefit-card.is-current .benefit-icon {
        background: rgba(255,255,255,.22);
    }

    .benefit-card.is-complete .benefit-icon {
        background: #22c55e;
        color: #fff;
    }

    .benefit-card.is-complete .benefit-icon-number {
        opacity: 0;
        transform: scale(.7);
    }

    .benefit-card.is-complete .benefit-icon-check {
        opacity: 1;
        transform: scale(1);
    }

    .benefit-card.is-complete strong {
        color: #fff;
    }

    .signup-main-area {
        min-height: 100vh;
    }

    .signup-main-wrap {
        min-height: 100vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 32px 24px;
    }

    .signup-main {
        width: 100%;
        max-width: 920px;
    }

    .signup-mobile-header {
        margin-bottom: 20px;
        text-align: center;
    }

    .signup-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 30px;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.10);
        padding: 34px;
    }

    .signup-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
    }

    .signup-card-head h2 {
        margin: 0 0 8px;
        font-size: 30px;
        line-height: 1.1;
        color: #0f172a;
    }

    .signup-card-head p,
    .signup-steps-meta span {
        margin: 0;
        color: #64748b;
    }

    .signup-progress {
        margin-bottom: 28px;
    }

    .signup-progress-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .signup-progress-bar {
        height: 100%;
        width: 33.333%;
        border-radius: 999px;
        background: linear-gradient(90deg, #2563eb, #7c3aed);
        transition: width .25s ease;
    }

    .signup-step-dots {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .step-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: all .2s ease;
    }

    .step-dot.active {
        width: 28px;
        background: #2563eb;
    }

    .signup-step {
        display: none;
    }

    .signup-step.active {
        display: block;
    }

    .step-title-wrap {
        margin-bottom: 18px;
    }

    .step-title-wrap h3 {
        margin: 0 0 6px;
        font-size: 22px;
        color: #0f172a;
    }

    .step-title-wrap p {
        margin: 0;
        color: #64748b;
    }

    .signup-form .form-label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #0f172a;
    }

    .signup-form .form-control {
        min-height: 52px;
        border-radius: 16px;
        border: 1px solid #dbe2ea;
        box-shadow: none;
        padding: 12px 16px;
        background: #fff;
    }

    .signup-form textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .signup-form .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #2563eb;
        font-weight: 600;
        padding: 0;
    }

    .billing-toggle {
        display: inline-flex;
        padding: 5px;
        border-radius: 18px;
        background: #f1f5f9;
        gap: 6px;
        margin-bottom: 18px;
    }

    .billing-switch {
        border: 0;
        min-height: 42px;
        border-radius: 14px;
        padding: 0 16px;
        background: transparent;
        color: #334155;
        font-weight: 600;
    }

    .billing-switch span {
        font-size: 12px;
        color: #16a34a;
        margin-left: 6px;
    }

    .billing-switch.active {
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08);
        color: #0f172a;
    }

    .plan-card {
        width: 100%;
        text-align: left;
        border: 1px solid #dbe2ea;
        background: #fff;
        border-radius: 24px;
        padding: 22px;
        transition: all .2s ease;
    }

    .plan-card.active {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
    }

    .plan-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .plan-card h4 {
        margin: 0;
        font-size: 20px;
        color: #0f172a;
    }

    .plan-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .plan-badge.featured {
        background: #eef2ff;
        color: #6d28d9;
    }

    .plan-price {
        display: flex;
        align-items: flex-end;
        gap: 6px;
        margin-bottom: 14px;
    }

    .plan-price strong {
        font-size: 32px;
        line-height: 1;
        color: #0f172a;
    }

    .plan-price span {
        color: #64748b;
        margin-bottom: 3px;
    }

    .plan-card ul {
        margin: 0;
        padding-left: 18px;
        color: #475569;
    }

    .plan-card ul li + li {
        margin-top: 6px;
    }

    .stripe-note {
        margin-top: 22px;
        padding: 18px;
        border-radius: 22px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        display: grid;
        grid-template-columns: 42px 1fr;
        gap: 14px;
    }

    .stripe-note-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        font-size: 18px;
    }

    .stripe-note strong {
        display: block;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .stripe-note p {
        margin: 0;
        color: #64748b;
    }

    .signup-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 28px;
    }

    .signup-actions .btn {
        min-height: 52px;
        border-radius: 16px;
        padding: 0 22px;
        font-weight: 700;
    }

    .signup-actions .btn-primary {
        min-width: 200px;
        margin-left: auto;
    }

    .signup-footer-note {
        margin-top: 18px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .terms-consent {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .terms-consent input[type="checkbox"] {
        position: static;
        opacity: 1;
        flex: 0 0 auto;
        width: 22px;
        height: 22px;
        margin: 1px 0 0;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .terms-consent label {
        position: static;
        padding-left: 0;
        line-height: 1.5;
        cursor: pointer;
    }

    .terms-consent label::before,
    .terms-consent label::after {
        display: none;
    }

    .terms-consent input[type="checkbox"]:focus-visible {
        outline: 3px solid rgba(37, 99, 235, 0.24);
        outline-offset: 3px;
    }

    .signup-alert {
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 16px;
        font-weight: 600;
    }

    .signup-alert.error {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .signup-alert.success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    @media (max-width: 991.98px) {
        .signup-aside {
            display: none !important;
        }

        .signup-main-wrap {
            min-height: 100vh;
            padding: 24px 16px;
        }

        .signup-card {
            padding: 24px;
            border-radius: 24px;
        }

        .signup-card-head {
            flex-direction: column;
        }

        .signup-actions {
            flex-direction: column;
        }

        .signup-actions .btn-primary,
        .signup-actions .btn {
            width: 100%;
            margin-left: 0;
        }

        .signup-footer-note {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script>
    (function () {
        const form = document.getElementById('signupForm');
        const steps = Array.from(document.querySelectorAll('.signup-step'));
        const asideCards = Array.from(document.querySelectorAll('.benefit-card[data-aside-step]'));
        const nextBtn = document.getElementById('nextStepBtn');
        const prevBtn = document.getElementById('prevStepBtn');
        const submitBtn = document.getElementById('submitSignupBtn');
        const progressBar = document.getElementById('signupProgressBar');
        const stepLabel = document.getElementById('currentStepLabel');
        const alertBox = document.getElementById('signupAlert');
        const passwordField = document.getElementById('passwordField');
        const togglePassword = document.getElementById('togglePassword');
        const planCards = Array.from(document.querySelectorAll('.plan-card'));
        const selectedPlanInput = document.getElementById('selectedPlan');
        const billingCycleInput = document.getElementById('billingCycle');
        const billingSwitches = Array.from(document.querySelectorAll('.billing-switch'));
        const termsCheckbox = document.getElementById('termsCheckbox');
        const sideStepCards = Array.from(document.querySelectorAll('[data-side-step]'));
        const storageKey = 'coherta_signup_draft_v1';
        const partnerAdsPendingSaleKey = 'coherta_partner_ads_pending_sale_v1';
        // Set to the Partner-Ads program id before enabling sale tracking.
        const partnerAdsProgramId = '12682';
        const signupCountryCode = new URLSearchParams(window.location.search).get('country_code') || '';
        const normalizedCountryCode = signupCountryCode.trim().toLowerCase();
        const marketPricing = <?php echo json_encode(coherta_market_pricing_js_map(), JSON_UNESCAPED_SLASHES); ?>;
        const companyProductId = <?php echo json_encode((coherta_get_product_pricing('Denmark', 'company', 'standard')['product_id'] ?? 'prod_UWswNDxveWxoUo')); ?>;

        let currentStep = 1;
        let submitting = false;

        function normalizeMarketKey(value) {
            return String(value || '').trim().toLowerCase().replace(/[\s_]+/g, '-').replace(/[^a-z0-9-]/g, '');
        }

        function getSelectedMarketPricing() {
            return marketPricing[normalizeMarketKey(normalizedCountryCode || form.country.value)] || null;
        }

        function getCompanyPlanPricing(pricing, plan = selectedPlanInput.value) {
            if (!pricing) return null;
            const planKey = plan === 'extended' ? 'extended' : 'standard';
            return pricing?.company?.[planKey] || pricing?.company?.standard || null;
        }

        function extractSessionId(payload) {
            if (!payload || typeof payload !== 'object') return '';
            const directKeys = ['session_id', 'sessionId', 'token', 'access_token', 'accessToken', 'bearer_token', 'bearerToken'];
            for (const key of directKeys) {
                if (payload[key]) return String(payload[key]);
            }
            for (const key of ['data', 'user', 'session', 'auth']) {
                const nested = extractSessionId(payload[key]);
                if (nested) return nested;
            }
            return '';
        }

        function unwrapCompanyPayload(payload) {
            if (!payload || typeof payload !== 'object') return null;
            if (Array.isArray(payload)) return payload[0] || null;
            if (payload.company && typeof payload.company === 'object') return payload.company;
            if (payload.data && typeof payload.data === 'object') return unwrapCompanyPayload(payload.data);
            if (payload.id || payload.uid || payload.market || payload.country || payload.country_code || payload.company_name) return payload;
            return null;
        }

        function extractCompanyMarket(company) {
            if (!company || typeof company !== 'object') return '';
            for (const key of ['market', 'company_market', 'billing_market', 'country', 'country_code', 'countryCode', 'company_country', 'geography']) {
                if (company[key]) return String(company[key]);
            }
            return '';
        }

        async function fetchCompanyMarketPricing(createdUser) {
            const sessionId = extractSessionId(createdUser);
            if (!sessionId) return null;

            const endpoints = [
                'https://api.coherta.com/users/me/company',
                'https://api.coherta.com/users/me/companies'
            ];

            for (const endpoint of endpoints) {
                try {
                    const response = await fetch(endpoint, {
                        method: 'GET',
                        headers: {
                            Accept: 'application/json',
                            Authorization: 'Bearer ' + sessionId
                        }
                    });
                    if (!response.ok) continue;
                    const payload = await response.json().catch(() => null);
                    const company = unwrapCompanyPayload(payload);
                    const pricing = marketPricing[normalizeMarketKey(extractCompanyMarket(company))] || null;
                    if (pricing) return pricing;
                } catch (e) {}
            }

            return null;
        }

        function applyCountryPricing() {
            const pricing = getSelectedMarketPricing();
            if (!pricing) return;

            const growthCard = document.querySelector('.plan-card[data-plan="growth"]');
            const extendedCard = document.querySelector('.plan-card[data-plan="extended"]');
            const growthPrice = document.querySelector('[data-plan-price="growth"]');
            const extendedPrice = document.querySelector('[data-plan-price="extended"]');
            const isUnitedKingdom = pricing.market_key === 'united-kingdom';

            if (growthCard) {
                const heading = growthCard.querySelector('h4');
                if (heading) heading.textContent = isUnitedKingdom ? '1 User' : 'Standard';
            }

            if (extendedCard) {
                const heading = extendedCard.querySelector('h4');
                if (heading) heading.textContent = isUnitedKingdom ? 'Unlimited Users' : 'Extended';
            }

            if (growthPrice) {
                const standardPricing = getCompanyPlanPricing(pricing, 'standard');
                growthPrice.dataset.monthly = String(standardPricing?.monthly_price || pricing.monthly_price);
                growthPrice.dataset.annual = String(standardPricing?.annual_monthly_price || pricing.annual_monthly_price || standardPricing?.monthly_price || pricing.monthly_price);
                growthPrice.dataset.currencyPrefix = pricing.currency === 'GBP' ? '£' : (pricing.currency === 'EUR' ? '€' : '');
                growthPrice.dataset.currencySuffix = ['GBP', 'EUR'].includes(pricing.currency) ? '' : ' ' + pricing.currency;
            }

            if (extendedPrice) {
                const extendedPricing = getCompanyPlanPricing(pricing, 'extended');
                extendedPrice.dataset.monthly = String(extendedPricing?.monthly_price || 4198);
                extendedPrice.dataset.annual = String(extendedPricing?.annual_monthly_price || extendedPricing?.monthly_price || 3499);
                extendedPrice.dataset.currencyPrefix = pricing.currency === 'GBP' ? '£' : (pricing.currency === 'EUR' ? '€' : '');
                extendedPrice.dataset.currencySuffix = ['GBP', 'EUR'].includes(pricing.currency) ? '' : ' ' + pricing.currency;
            }

            if (isUnitedKingdom && !form.country.value) {
                form.country.value = 'GB';
            }

            syncBillingCycleUI();
        }

        function formatPlanPrice(priceEl, value) {
            return (priceEl.dataset.currencyPrefix || '') + value + (priceEl.dataset.currencySuffix || '');
        }

        function getSelectedPlanAmount() {
            const priceEl = document.querySelector('[data-plan-price="' + selectedPlanInput.value + '"]');
            if (!priceEl) return '';
            return billingCycleInput.value === 'annual' ? priceEl.dataset.annual : priceEl.dataset.monthly;
        }

        function getPlanAmountFromPricing(pricing) {
            if (!pricing) return getSelectedPlanAmount();
            const planPricing = getCompanyPlanPricing(pricing);
            if (!planPricing) return getSelectedPlanAmount();
            const isAnnual = billingCycleInput.value === 'annual';
            return String(isAnnual
                ? (planPricing.annual_monthly_price || planPricing.monthly_price)
                : planPricing.monthly_price);
        }

        function showAlert(message, type = 'error') {
            alertBox.hidden = false;
            alertBox.className = 'signup-alert ' + type;
            alertBox.textContent = message;
        }

        function hideAlert() {
            alertBox.hidden = true;
            alertBox.textContent = '';
            alertBox.className = 'signup-alert';
        }

        function updateAsideSteps() {
            asideCards.forEach(card => {
                const step = Number(card.dataset.asideStep);
                const status = card.querySelector('.benefit-status');

                card.classList.remove('active', 'completed');

                if (step < currentStep) {
                    card.classList.add('completed');
                    if (status) status.textContent = 'Completed';
                } else if (step === currentStep) {
                    card.classList.add('active');
                    if (status) status.textContent = 'Current step';
                } else if (step === steps.length) {
                    if (status) status.textContent = 'Final step';
                } else {
                    if (status) status.textContent = 'Up next';
                }
            });
        }

        function updateUI() {
            steps.forEach(step => {
                step.classList.toggle('active', Number(step.dataset.step) === currentStep);
            });

            document.querySelectorAll('.step-dot').forEach(dot => {
                dot.classList.toggle('active', Number(dot.dataset.stepDot) <= currentStep);
            });

            progressBar.style.width = ((currentStep / steps.length) * 100) + '%';
            stepLabel.textContent = 'Step ' + currentStep + ' of ' + steps.length;

            prevBtn.hidden = currentStep === 1;
            nextBtn.hidden = currentStep === steps.length;
            submitBtn.hidden = currentStep !== steps.length;

            updateAsideSteps();
        }

        function validateStep(stepNumber) {
            hideAlert();

            const step = document.querySelector('.signup-step[data-step="' + stepNumber + '"]');
            const fields = Array.from(step.querySelectorAll('input, select, textarea')).filter(field => {
                if (field.type === 'hidden' || field.type === 'button') return false;
                return true;
            });

            for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return false;
                }
            }

            if (stepNumber === 1 && passwordField.value.trim().length < 8) {
                showAlert('Password must be at least 8 characters.');
                passwordField.focus();
                return false;
            }

            return true;
        }

        function normalizeWebsiteUrl(value) {
            const website = value.trim();
            if (!website) return '';
            if (/^[a-z][a-z0-9+.-]*:\/\//i.test(website)) return website;
            return 'https://' + website;
        }

        function goToNextStep() {
            if (!validateStep(currentStep)) return;
            if (currentStep >= steps.length) return;
            currentStep += 1;
            saveDraft();
            updateUI();
        }

        function saveDraft() {
            const formData = new FormData(form);
            const draft = {};
            formData.forEach((value, key) => {
                draft[key] = value;
            });
            draft.currentStep = currentStep;
            localStorage.setItem(storageKey, JSON.stringify(draft));
        }

        function restoreDraft() {
            try {
                const draft = JSON.parse(localStorage.getItem(storageKey) || '{}');

                Object.keys(draft).forEach(key => {
                    const field = form.elements[key];
                    if (!field) return;

                    if (field.type === 'checkbox') {
                        field.checked = Boolean(draft[key]);
                    } else {
                        field.value = draft[key];
                    }
                });

                if (draft.plan) {
                    selectedPlanInput.value = draft.plan;
                }

                if (draft.billing_cycle) {
                    billingCycleInput.value = draft.billing_cycle;
                }

                if (draft.currentStep && Number(draft.currentStep) >= 1 && Number(draft.currentStep) <= steps.length) {
                    currentStep = Number(draft.currentStep);
                }

                syncPlanUI();
                syncBillingCycleUI();
            } catch (e) {}
        }

        function clearDraft() {
            localStorage.removeItem(storageKey);
        }

        function syncPlanUI() {
            const selectedPlan = selectedPlanInput.value;
            planCards.forEach(card => {
                card.classList.toggle('active', card.dataset.plan === selectedPlan);
            });
        }

        function syncBillingCycleUI() {
            const cycle = billingCycleInput.value;

            billingSwitches.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.billingCycle === cycle);
            });

            document.querySelectorAll('.plan-price strong').forEach(priceEl => {
                const value = cycle === 'annual' ? priceEl.dataset.annual : priceEl.dataset.monthly;
                priceEl.textContent = formatPlanPrice(priceEl, value);
            });
        }

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            togglePassword.textContent = isPassword ? 'Hide' : 'Show';
        });

        nextBtn.addEventListener('click', function () {
            goToNextStep();
        });

        prevBtn.addEventListener('click', function () {
            hideAlert();
            currentStep -= 1;
            saveDraft();
            updateUI();
        });

        form.addEventListener('input', saveDraft);
        form.addEventListener('change', function (event) {
            saveDraft();
            if (event.target && event.target.name === 'country') {
                applyCountryPricing();
            }
        });

        form.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            if (e.shiftKey) return;

            const target = e.target;
            const tagName = target.tagName.toLowerCase();

            if (tagName === 'textarea') return;
            if (target.type === 'submit' || target.type === 'button') return;

            if (currentStep < steps.length) {
                e.preventDefault();
                goToNextStep();
            }
        });

        planCards.forEach(card => {
            card.addEventListener('click', function () {
                selectedPlanInput.value = card.dataset.plan;
                saveDraft();
                syncPlanUI();
            });
        });

        billingSwitches.forEach(btn => {
            btn.addEventListener('click', function () {
                billingCycleInput.value = btn.dataset.billingCycle;
                saveDraft();
                syncBillingCycleUI();
            });
        });

        function getStoredPaymentContext() {
            const context = {};
            try {
                const paid = localStorage.getItem('paid');
                const pacid = localStorage.getItem('pacid');
                if (paid !== null && paid !== '') context.paid = paid;
                if (pacid !== null && pacid !== '') context.pacid = pacid;
            } catch (e) {}
            return context;
        }

        function storePendingPartnerAdsSale(sale) {
            const paymentContext = getStoredPaymentContext();
            if (!paymentContext.paid || !paymentContext.pacid) return;
            localStorage.setItem(partnerAdsPendingSaleKey, JSON.stringify({
                ...sale,
                program_id: partnerAdsProgramId,
                paid: paymentContext.paid,
                pacid: paymentContext.pacid
            }));
        }

        function getPartnerAdsCheckoutContext() {
            const paymentContext = getStoredPaymentContext();
            if (!partnerAdsProgramId || !paymentContext.paid || !paymentContext.pacid) return null;
            return {
                program_id: partnerAdsProgramId,
                paid: paymentContext.paid,
                pacid: paymentContext.pacid
            };
        }

        async function createUser(payload) {
            const response = await fetch('https://api.coherta.com/users/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...payload, ...getStoredPaymentContext() })
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || 'Unable to create account.');
            }

            return result;
        }

        async function createStripeCheckout(payload) {
            const response = await fetch('https://api.coherta.com/billing/checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || 'Unable to start Revolut checkout.');
            }

            return result;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            hideAlert();

            if (submitting) return;
            if (!validateStep(3)) return;

            if (!termsCheckbox.checked) {
                showAlert('You must accept the terms and privacy policy to continue.');
                return;
            }

            submitting = true;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Preparing Revolut checkout...';

            try {
                const payload = {
                    first_name: form.first_name.value.trim(),
                    last_name: form.last_name.value.trim(),
                    email: form.email.value.trim(),
                    password: form.password.value,
                    phone: form.phone.value.trim(),
                    work_phone: form.work_phone.value.trim(),
                    company_name: form.company_name.value.trim(),
                    website: normalizeWebsiteUrl(form.website.value),
                    country: form.country.value,
                    vat_code: form.vat_code.value.trim(),
                    team_size: form.team_size.value,
                    use_case: form.use_case.value,
                    goal: form.goal.value.trim(),
                    plan: selectedPlanInput.value,
                    billing_cycle: billingCycleInput.value
                };

                const createdUser = await createUser(payload);

                const selectedMarketPricing = await fetchCompanyMarketPricing(createdUser) || getSelectedMarketPricing();
                const selectedCompanyPlanPricing = getCompanyPlanPricing(selectedMarketPricing);
                const checkoutMonthlyPrice = getPlanAmountFromPricing(selectedMarketPricing);
                const checkoutPayload = {
                    user_id: createdUser.user_id || createdUser.id || null,
                    email: payload.email,
                    plan: payload.plan,
                    billing_cycle: payload.billing_cycle,
                    pricing: checkoutMonthlyPrice,
                    monthly_price: checkoutMonthlyPrice,
                    custom_monthly_price: checkoutMonthlyPrice,
                    custom_monthly_price_currency: selectedMarketPricing?.currency || 'DKK',
                    currency: selectedMarketPricing?.currency || 'DKK',
                    company_name: payload.company_name,
                    country: payload.country,
                    market: selectedMarketPricing?.label || payload.country,
                    vat_code: payload.vat_code,
                    success_url: window.location.origin + '/app/login?account=created&billing=success',
                    cancel_url: window.location.origin + '/app/sign-up?billing=cancelled' + (normalizedCountryCode ? '&country_code=' + encodeURIComponent(normalizedCountryCode) : '')
                };

                if (normalizedCountryCode) {
                    checkoutPayload.country_code = normalizedCountryCode;
                }

                if (selectedCompanyPlanPricing?.product_id) {
                    checkoutPayload.products = [selectedCompanyPlanPricing.product_id];
                    checkoutPayload.product_id = selectedCompanyPlanPricing.product_id;
                    checkoutPayload.stripe_product_id = selectedCompanyPlanPricing.product_id;
                    if (selectedCompanyPlanPricing.price_id) checkoutPayload.reference_price_id = selectedCompanyPlanPricing.price_id;
                } else {
                    checkoutPayload.products = [companyProductId];
                    checkoutPayload.product_id = companyProductId;
                    checkoutPayload.stripe_product_id = companyProductId;
                }

                const partnerAdsContext = getPartnerAdsCheckoutContext();
                if (partnerAdsContext) checkoutPayload.partner_ads = partnerAdsContext;

                const checkout = await createStripeCheckout(checkoutPayload);

                if (checkout.url) {
                    storePendingPartnerAdsSale({
                        order_id: checkout.id || checkout.session_id || checkout.checkout_session_id || createdUser.user_id || createdUser.id || payload.email,
                        revenue: checkoutMonthlyPrice
                    });
                    clearDraft();
                    window.location.href = checkout.url;
                    return;
                }

                throw new Error('Revolut checkout URL was not returned.');
            } catch (error) {
                showAlert(error.message || 'Something went wrong. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
                submitting = false;
            }
        });

        restoreDraft();
        termsCheckbox.checked = false;
        applyCountryPricing();
        updateUI();
        syncPlanUI();
        syncBillingCycleUI();
    })();
</script>
<script type="text/javascript">
    _linkedin_partner_id = "9993001";
    window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
    window._linkedin_data_partner_ids.push(_linkedin_partner_id);
</script><script type="text/javascript">
    (function(l) {
        if (!l){window.lintrk = function(a,b){window.lintrk.q.push([a,b])};
            window.lintrk.q=[]}
        var s = document.getElementsByTagName("script")[0];
        var b = document.createElement("script");
        b.type = "text/javascript";b.async = true;
        b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js";
        s.parentNode.insertBefore(b, s);})(window.lintrk);
</script>
<noscript>
    <img height="1" width="1" style="display:none;" alt="" src="https://px.ads.linkedin.com/collect/?pid=9993001&fmt=gif" />
</noscript>
<?php include('partial/footer-end.php'); ?>
