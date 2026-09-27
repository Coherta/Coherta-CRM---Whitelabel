<?php
require_once __DIR__ . '/base.php';
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
if (empty($_SESSION['introductions_csrf'])) $_SESSION['introductions_csrf'] = bin2hex(random_bytes(32));
$introSetupConfig = ['endpoint' => '/app/introduction-onboarding-api', 'csrf' => $_SESSION['introductions_csrf'], 'resume' => ($_GET['resume'] ?? '') === '1'];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Set up your 5 free qualified leads per week. Define your audience, connect LinkedIn and choose your preferred introducers.">
    <title>Get an Introduction — Free setup | Coherta</title>
    <link rel="icon" href="/uk/assets/img/favicon.png">
    <link rel="stylesheet" href="/app/assets/css/introduction-onboarding.css?v=<?= filemtime(__DIR__ . '/assets/css/introduction-onboarding.css') ?>">
    <script src="/app/assets/js/introduction-onboarding.js?v=<?= filemtime(__DIR__ . '/assets/js/introduction-onboarding.js') ?>" defer></script>
</head>
<body class="intro-setup-body">
    <header class="ios-header"><a class="ios-brand" href="/uk/get-an-introduction"><img src="/uk/assets/img/coherta-logo.png" alt="" width="29" height="29">Coherta <span>/ Get an Introduction</span></a><a href="/app/login?product=introductions">Already a member? <strong>Log in</strong></a></header>
    <main class="ios-layout" id="intro-setup">
        <aside class="ios-sidebar" aria-label="Your free introduction setup">
            <p class="ios-eyebrow">GOOD CONNECTIONS OPEN DOORS</p>
            <h1>Five good leads. <br><span>One trusted circle.</span></h1>
            <p>5 qualified leads every week, free of charge. An introduction made ready for each one.</p>
            <ol class="ios-progress" aria-label="Setup progress">
                <li data-progress="1" aria-current="step"><span>1</span><div><strong>Your ideal customers</strong><small>Tell us who you want to reach</small></div></li>
                <li data-progress="2"><span>2</span><div><strong>Connect your account</strong><small>Coherta, then LinkedIn</small></div></li>
                <li data-progress="3"><span>3</span><div><strong>Your 5 star contacts</strong><small>The people you trust to introduce you</small></div></li>
                <li data-progress="4"><span>4</span><div><strong>Let the scouting begin</strong><small>We start finding your next leads</small></div></li>
            </ol>
            <div class="ios-free-note"><span aria-hidden="true">✦</span><div><strong>Free means free.</strong><p>No card details.</p></div></div>
        </aside>
        <div class="ios-workspace">
            <p id="ios-status" class="ios-status" role="status" aria-live="polite">Loading your setup…</p>
            <p id="ios-error" class="ios-error" role="alert" hidden></p>
            <button id="ios-reload" class="ios-button ios-secondary" type="button" hidden>Retry loading setup</button>
            <noscript><p class="ios-error">Please enable JavaScript to securely connect your accounts and complete setup.</p></noscript>
            <section class="ios-step" data-step="1" aria-labelledby="ios-target-title">
                <p class="ios-eyebrow">STEP 1 OF 4 · YOUR IDEAL CUSTOMERS</p>
                <h2 id="ios-target-title" tabindex="-1">Who would you <br>love to meet?</h2>
                <p class="ios-description">Describe the people and businesses you want to reach. The more specific you are, the more relevant your introductions can be.</p>
                <form id="ios-target-form">
                    <label for="ios-description">Who do you want to reach?</label>
                    <textarea id="ios-description" name="description" rows="7" maxlength="8000" required placeholder="For example: Founders and heads of sales at B2B software companies in Denmark and Sweden with 20–200 employees. Ideally, they are growing their sales team. Exclude agencies and consultants." aria-describedby="ios-description-hint ios-character-count"></textarea>
                    <div class="ios-field-notes"><p id="ios-description-hint">Include roles, industries, locations and what makes someone a good fit.</p><output id="ios-character-count" for="ios-description">0 / 8,000</output></div>
                    <div class="ios-form-actions"><button class="ios-button" type="submit" disabled>Continue <span aria-hidden="true">→</span></button><span class="ios-save-note">Your description stays with you.</span></div>
                </form>
            </section>
            <section class="ios-step" data-step="2" aria-labelledby="ios-connect-title" hidden>
                <p class="ios-eyebrow">STEP 2 OF 4 · CONNECT YOUR ACCOUNT</p>
                <h2 id="ios-connect-title" tabindex="-1">Put a familiar face <br>behind the introduction.</h2>
                <p class="ios-description">Create or sign in to your free Coherta account, then connect LinkedIn to find introduction routes through your network.</p>
                <div id="ios-account-panel">
                    <fieldset class="ios-account-choice"><legend class="ios-sr-only">Your Coherta account</legend><label><input type="radio" name="account-mode" value="signup" checked><span>Create free account</span></label><label><input type="radio" name="account-mode" value="login"><span>Sign in to Coherta</span></label></fieldset>
                    <form id="ios-account-form">
                        <div id="ios-name-field"><label for="ios-name">Your name</label><input id="ios-name" name="name" autocomplete="name" maxlength="120" required></div>
                        <label for="ios-email">Email</label><input id="ios-email" name="email" type="email" autocomplete="username" maxlength="254" required>
                        <label for="ios-account-password">Create a Coherta password</label><div class="ios-password-field"><input id="ios-account-password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" required aria-describedby="ios-account-password-hint"><button type="button" data-toggle-password="ios-account-password" aria-label="Show Coherta password">Show</button></div>
                        <p id="ios-account-password-hint" class="ios-hint">At least 12 characters. Use a separate password for your Coherta account.</p>
                        <p id="ios-reset-link" class="ios-hint" hidden><a href="/app/forgot-password?url=%2Fapp%2Fintroduction-onboarding">Forgot your Coherta password?</a></p>
                        <p class="ios-terms">By creating an account, you agree to our <a href="/uk/forretningsbetingelser" target="_blank" rel="noopener">terms</a> and <a href="/uk/privacy-policy" target="_blank" rel="noopener">privacy policy</a>.</p>
                        <button id="ios-account-submit" class="ios-button" type="submit">Create free account <span aria-hidden="true">→</span></button>
                    </form>
                </div>
                <div id="ios-authenticated" class="ios-account-saved" hidden><span aria-hidden="true">✓</span><div><strong>Your Coherta account is ready</strong><p id="ios-account-name">You are signed in. Next, connect LinkedIn.</p></div></div>
                <div id="ios-linkedin-panel" hidden>
                    <form id="ios-linkedin-form">
                        <div class="ios-linkedin-label"><span aria-hidden="true">in</span><h3>Connect your LinkedIn account</h3></div>
                        <p class="ios-hint">Enter the username and password you use on LinkedIn. You may be asked to approve the login or enter a verification code.</p>
                        <p id="ios-linkedin-error" class="ios-error" role="alert" hidden></p>
                        <label for="ios-linkedin-username">LinkedIn username or email</label><input id="ios-linkedin-username" name="username" autocomplete="username" maxlength="254" required>
                        <label for="ios-linkedin-password">LinkedIn password</label><div class="ios-password-field"><input id="ios-linkedin-password" name="password" type="password" autocomplete="current-password" required maxlength="1000"><button type="button" data-toggle-password="ios-linkedin-password" aria-label="Show LinkedIn password">Show</button></div>
                        <button class="ios-button" type="submit">Connect LinkedIn <span aria-hidden="true">→</span></button>
                    </form>
                    <p id="ios-linkedin-status" class="ios-linkedin-status" role="status" aria-live="polite"></p>
                    <form id="ios-code-form" hidden><label for="ios-code">LinkedIn verification code</label><input id="ios-code" name="code" autocomplete="one-time-code" inputmode="numeric" maxlength="64" required><button class="ios-button" type="submit">Verify code</button></form>
                    <div id="ios-linkedin-ready" class="ios-account-saved" hidden><span aria-hidden="true">✓</span><div><strong>LinkedIn connected</strong><p>You are ready to choose your preferred introducers.</p></div></div>
                </div>
                <div class="ios-form-actions"><button class="ios-back" type="button" data-back="1">← Back</button><button id="ios-connect-next" class="ios-button" type="button" disabled>Choose my 5 star contacts <span aria-hidden="true">→</span></button></div>
            </section>
            <section class="ios-step" data-step="3" aria-labelledby="ios-stars-title" hidden>
                <p class="ios-eyebrow">STEP 3 OF 4 · YOUR PREFERRED INTRODUCERS</p>
                <h2 id="ios-stars-title" tabindex="-1">Who would happily <br>open a door for you?</h2>
                <p class="ios-description">Choose exactly 5 people you know and trust. <strong>These will be your preferred people to make introductions for you.</strong></p>
                <div class="ios-star-explanation"><span aria-hidden="true">★</span><p>Think of colleagues, customers, partners or friends who would feel comfortable introducing you. Adding them here does not send them a message.</p></div>
                <form id="ios-stars-form">
                    <?php for ($star = 1; $star <= 5; $star++): ?>
                    <div class="ios-star-field"><span aria-hidden="true"><?= $star ?></span><div><label for="ios-star-<?= $star ?>">Star contact <?= $star ?> — LinkedIn profile URL</label><input id="ios-star-<?= $star ?>" name="star_urls[]" type="url" inputmode="url" autocomplete="off" maxlength="2000" placeholder="https://www.linkedin.com/in/their-name" required></div></div>
                    <?php endfor; ?>
                    <p class="ios-hint">Open each person’s LinkedIn profile and copy the address from your browser. Choose 5 different people.</p>
                    <div class="ios-form-actions"><button class="ios-back" type="button" data-back="2">← Back</button><button class="ios-button" type="submit">Start scouting my free leads <span aria-hidden="true">→</span></button></div>
                </form>
            </section>
            <section class="ios-step ios-success" data-step="4" aria-labelledby="ios-success-title" hidden>
                <div class="ios-success-mark" aria-hidden="true">✓</div>
                <p class="ios-eyebrow">STEP 4 OF 4 · YOU’RE ALL SET</p>
                <h2 id="ios-success-title" tabindex="-1">We’ve started scouting <br>your next leads.</h2>
                <p class="ios-description">Your ideal customer profile and 5 preferred star contacts are saved. Scouting is now set up to find qualified leads and introduction routes through your network.</p>
                <div class="ios-success-offer"><strong>5 qualified leads. Every week. Free.</strong><p>An introduction made ready for each one. You choose the person and review the message before asking.</p></div>
                <a class="ios-button" href="/app/sales_insider">Open Get an Introduction <span aria-hidden="true">↗</span></a>
                <p class="ios-hint">You can return anytime through the Get an Introduction option on the login page.</p>
            </section>
        </div>
    </main>
    <?php require_once __DIR__ . '/partial/linkedin-approval.php'; ?>
    <footer class="ios-footer"><span>© <?= date('Y') ?> Coherta</span><a href="/uk/privacy-policy">Privacy policy</a><a href="/uk/kontakt-os.php">Need a hand?</a></footer>
    <script type="application/json" id="intro-setup-config"><?= json_encode($introSetupConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
</body>
</html>
