<?php
require_once __DIR__ . '/base.php';
require_once __DIR__ . '/partial/login-product.php';
header('Cache-Control: no-store');

function login_access_flag_enabled(?array $accessRights, string $key): bool
{
    return isset($accessRights[$key]) && (int)$accessRights[$key] === 1;
}

function login_default_destination(ApiClient $client): string
{
    $accessRights = $client->accessRights ?? $client->getAccessRights();

    $leadListsDeactivated = login_access_flag_enabled($accessRights, 'deactivate_lead_lists');
    if (!$leadListsDeactivated) {
        return '/app/index';
    }

    $campaignsDeactivated = login_access_flag_enabled($accessRights, 'deactivate_campaigns');
    $recruiterDeactivated = login_access_flag_enabled($accessRights, 'deactivate_recruiter_campaigns');

    if ($campaignsDeactivated && !$recruiterDeactivated) {
        return '/app/recruiters';
    }

    return '/app/campaign';
}

function login_introduction_confirmation_destination(string $requestedUrl): string
{
    $url = login_sanitize_redirect_url($requestedUrl);
    if ($url === '' || parse_url($url, PHP_URL_PATH) !== '/app/sales_insider') return '';
    parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
    if (($query['confirm_introduction'] ?? '') !== '1') return '';
    $choice = ['confirm_introduction' => 1];
    foreach (['icp_id', 'participant_id', 'introducer_id'] as $field) {
        $value = $query[$field] ?? null;
        if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/D', $value) || filter_var($value, FILTER_VALIDATE_INT) === false) return '';
        $choice[$field] = $value;
    }
    // Rebuild a local platform URL containing only the choice, never a login token.
    return '/app/sales_insider?' . http_build_query($choice, '', '&', PHP_QUERY_RFC3986);
}

function login_redirect_after_auth(ApiClient $client, ?array $user, string $requestedUrl = ''): void
{
    $requestedUrl = login_sanitize_redirect_url($requestedUrl);
    $portalReturnUrl = login_sanitize_redirect_url((string)($_SESSION['portal_login_return_url'] ?? ''));
    unset($_SESSION['portal_login_return_url']);
    $confirmationUrl = login_introduction_confirmation_destination($requestedUrl);
    if ($user && $confirmationUrl !== '') {
        unset($_SESSION['login_redirect_url']);
        header('Location: ' . $confirmationUrl);
        return;
    }
    if ($user && $requestedUrl !== '' && $requestedUrl === $portalReturnUrl) {
        unset($_SESSION['login_redirect_url']);
        header('Location: ' . login_session_handoff_url($requestedUrl, (string)($_SESSION['token'] ?? '')));
        return;
    }
    // A saved Campaigns return URL must not override an Introduction account's landing page.
    $onboarding = $user ? $client->get('/plugins__sales_insider__introductions.php?action=onboarding-status') : null;
    if (($onboarding['registered'] ?? false) === true) {
        unset($_SESSION['login_redirect_url']);
        header('Location: /app/sales_insider');
        return;
    }

    if ($requestedUrl !== '') {
        unset($_SESSION['login_redirect_url']);
        header("Location: " . login_session_handoff_url($requestedUrl, (string)($_SESSION['token'] ?? '')));
        return;
    }

    $linkedinConnected = isset($user['connectors']['linkedin']["status"]) && (int)$user['connectors']['linkedin']["status"] == 1;

    if (!$linkedinConnected) {
        header("Location: /app/connect_linkedin");
        return;
    }

    header("Location: " . login_default_destination($client));
}

function login_sanitize_redirect_url(string $requestedUrl = ''): string
{
    $requestedUrl = trim($requestedUrl);
    if ($requestedUrl === '' || strpbrk($requestedUrl, "\r\n") !== false) {
        return '';
    }

    for ($i = 0; $i < 8; $i++) {
        $path = parse_url($requestedUrl, PHP_URL_PATH) ?: '';
        if ($path !== '/app/login' && $path !== 'login' && $path !== '/login') {
            break;
        }

        parse_str((string)(parse_url($requestedUrl, PHP_URL_QUERY) ?: ''), $params);
        $nextUrl = isset($params['url']) ? (string)$params['url'] : '';
        if ($nextUrl === '' || $nextUrl === $requestedUrl) {
            return '';
        }
        $requestedUrl = $nextUrl;
    }

    $path = parse_url($requestedUrl, PHP_URL_PATH) ?: '';
    if ($path === '/app/login' || $path === 'login' || $path === '/login') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $requestedUrl)) {
        $host = strtolower((string)parse_url($requestedUrl, PHP_URL_HOST));
        if (!in_array($host, ['www.coherta.com', 'coherta.com', 'partner.coherta.com', 'www.partner.coherta.com', '127.0.0.1', 'localhost'], true)) {
            return '';
        }
    } elseif (strpos($requestedUrl, '//') === 0 || strpos($requestedUrl, '\\') === 0) {
        return '';
    } elseif ($requestedUrl[0] !== '/') {
        return '';
    }

    return $requestedUrl;
}

function login_host_key(string $host): string
{
    $host = strtolower(trim($host));
    if (strpos($host, ':') !== false) {
        $host = (string)parse_url('https://' . $host, PHP_URL_HOST);
    }
    return preg_replace('/^www\./', '', $host) ?: $host;
}

function login_absolute_url_origin(string $url): string
{
    $scheme = strtolower((string)(parse_url($url, PHP_URL_SCHEME) ?: 'https'));
    $host = (string)parse_url($url, PHP_URL_HOST);
    $port = parse_url($url, PHP_URL_PORT);

    if ($host === '') {
        return '';
    }

    return $scheme . '://' . $host . ($port ? ':' . $port : '');
}

function login_session_handoff_url(string $requestedUrl, string $token): string
{
    $token = trim($token);
    if ($token === '' || !preg_match('/^https?:\/\//i', $requestedUrl)) {
        return $requestedUrl;
    }

    $targetHost = (string)parse_url($requestedUrl, PHP_URL_HOST);
    $currentHost = (string)($_SERVER['HTTP_HOST'] ?? 'www.coherta.com');
    if (login_host_key($targetHost) === login_host_key($currentHost)) {
        return $requestedUrl;
    }

    $targetOrigin = login_absolute_url_origin($requestedUrl);
    if ($targetOrigin === '') {
        return $requestedUrl;
    }

    return $targetOrigin . '/app/login?' . http_build_query([
        'old_session' => $token,
        'url' => $requestedUrl,
    ], '', '&', PHP_QUERY_RFC3986);
}

function login_token_from_params(array $params): string
{
    foreach (['old_session', 'session_id', 'sessionId', 'token', 'access_token', 'accessToken', 'bearer_token', 'bearerToken'] as $key) {
        if (!empty($params[$key]) && is_string($params[$key])) {
            return trim($params[$key]);
        }
    }

    return '';
}

function login_google_auth_url(string $requestedUrl = ''): string
{
    $returnUrl = 'https://www.coherta.com/app/login';
    $requestedUrl = login_sanitize_redirect_url($requestedUrl);
    if ($requestedUrl !== '') {
        $returnUrl .= '?url=' . rawurlencode($requestedUrl);
    }

    return 'https://api.coherta.com/auth/google?' . http_build_query([
        'return_url' => $returnUrl,
    ]);
}

function login_authenticate_with_token(ApiClient $client, string $token, string $requestedUrl = ''): bool
{
    $token = trim($token);
    if ($token === '') {
        return false;
    }

    $client->set_access_token($token);
    $user = $client->getCurrentUser();
    if (!$user) {
        unset($_SESSION['token']);
        $client->setToken('');
        return false;
    }

    login_redirect_after_auth($client, $user, $requestedUrl);
    return true;
}

$requestToken = login_token_from_params($_GET);
$rawRequestedRedirectUrl = (string)($_GET['url'] ?? '');
$requestedRedirectUrl = login_sanitize_redirect_url($rawRequestedRedirectUrl);
$loginProduct = coherta_login_product($_GET, $_POST, $requestedRedirectUrl);
if ($loginProduct !== '' && (isset($_GET['product']) || isset($_POST['login_product']) || $requestedRedirectUrl === '')) {
    $requestedRedirectUrl = coherta_login_product_destination($loginProduct);
    $rawRequestedRedirectUrl = $requestedRedirectUrl;
}
if ($requestedRedirectUrl !== '') {
    $_SESSION['login_redirect_url'] = $requestedRedirectUrl;
} elseif ($rawRequestedRedirectUrl === '' && $requestToken !== '') {
    $requestedRedirectUrl = login_sanitize_redirect_url((string)($_SESSION['login_redirect_url'] ?? ''));
} else {
    unset($_SESSION['login_redirect_url']);
}

$googleAuthUrl = login_google_auth_url($requestedRedirectUrl);
$login_error = false;
$submitted_username = '';
$clearStoredSession = false;

if ($requestToken !== '') {
    if (login_authenticate_with_token($client, $requestToken, $requestedRedirectUrl)) {
        die();
    }
    $login_error = true;
    $clearStoredSession = true;
}
if(isset($_POST["login"])) {
    $submitted_username = trim((string)($_POST["login"]["username"] ?? ''));
    if ($client->fetchToken($submitted_username, $_POST["login"]["password"])) {
        $user = $client->getCurrentUser();
        session_regenerate_id(true);
        login_redirect_after_auth($client, $user, $loginProduct !== '' ? $requestedRedirectUrl : (string)($_POST['redirect_url'] ?? ''));
        die();
    }
    $login_error = true;
}
$publicPage = True;
$PageTitle = "B2B Login | Customers, Leads, and Sales Management";
$meta_description = "Log in to your Coherta account to manage your customers, leads, and sales. Access powerful CRM features and insights to grow your business.";
include('partial/header.php');
?>
  <style>
    .login-main .login-introduction-link { margin: 14px 0 0; text-align: center; font-size: 13px; line-height: 1.6; }
    .login-main .login-introduction-link a { font-size: inherit; letter-spacing: normal; }
    .login-error {
      margin: 18px 0 20px;
      border-radius: 12px;
      font-weight: 600;
      line-height: 1.45;
    }

    #login-password {
      padding-right: 72px;
    }

    .login-password-toggle {
      position: absolute;
      top: 50%;
      right: 14px;
      transform: translateY(-50%);
      border: 0;
      background: transparent;
      color: #2563eb;
      font-weight: 600;
      padding: 0;
      cursor: pointer;
    }
  </style>
  <div class="container-fluid p-0">
    <div class="row m-0">
      <div class="col-12 p-0">
        <div class="login-card">
          <div>
            <div><a class="logo" href="/"><img class="img-fluid for-light" src="/assets/img/logo/logo.png?w=300" alt="looginpage" width="300" height="67">
                    <img class="img-fluid for-dark" src="/assets/img/logo/logo.png?w=300" alt="looginpage" width="300" height="67"></a></div>
            <div class="login-main">
              <form class="theme-form" method="POST">
                  <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($requestedRedirectUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <h1>Login to account</h1>
                <p>Enter your email & password to login</p>
                <?php if ($login_error) { ?>
                    <div class="alert alert-danger login-error" role="alert">
                        Email or password is incorrect. Please try again or reset your password.
                    </div>
                <?php } ?>
                <a class="btn coherta-google-login w-100" href="<?php echo htmlspecialchars($googleAuthUrl, ENT_QUOTES, 'UTF-8'); ?>">
                  <i class="fa fa-google"></i>
                  <span>Continue with Google</span>
                </a>
                <div class="login-divider"></div>
                <div class="form-group">
                  <label class="col-form-label" for="login-username">Email</label>
                  <input class="form-control" id="login-username" type="email" required="" name="login[username]" placeholder="name@company.com" autocomplete="email" value="<?php echo htmlspecialchars($submitted_username, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                  <label class="col-form-label" for="login-password">Password</label>
                  <div class="form-input position-relative">
                    <input class="form-control" id="login-password" type="password" name="login[password]" autocomplete="current-password" required="" placeholder="*********">
                    <button class="login-password-toggle" type="button" id="toggleLoginPassword" aria-controls="login-password" aria-label="Show password">Show</button>
                  </div>
                </div>
                <div class="form-group mb-0">
                  <div class="text-end mt-3">
                    <button class="btn btn-primary btn-block w-100" type="submit">Login</button>
                  </div>
                </div>
                <p class="login-introduction-link"><a href="/app/introduction-onboarding">Or get started for free with 5 introductions per week</a></p>
                <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="setup-funnel">Create Account</a></p>
                <p class="mt-4 mb-0 text-center">Forgot your password?<a class="ms-2" href="/app/forgot-password?url=<?= rawurlencode($requestedRedirectUrl) ?>">Reset it here</a></p>
                  <p class="mt-4 mb-0 text-center"><a class="ms-2" href="/da/privacy-policy">Privacy Policy</a></p>
              </form>
                <script>
                    async function trackPartnerAdsSaleFromPending() {
                        const pendingSaleKey = 'coherta_partner_ads_pending_sale_v1';
                        let sale = null;
                        try {
                            sale = JSON.parse(localStorage.getItem(pendingSaleKey) || 'null');
                        } catch (e) {}

                        if (!sale || !sale.program_id || !sale.paid || !sale.pacid || !sale.order_id || !sale.revenue) return;

                        const sentKey = 'coherta_partner_ads_sale_sent_' + sale.order_id;
                        if (localStorage.getItem(sentKey) === '1') {
                            localStorage.removeItem(pendingSaleKey);
                            return;
                        }

                        const params = new URLSearchParams({
                            programid: sale.program_id,
                            type: 'lead',
                            partnerid: sale.paid,
                            pacid: sale.pacid,
                            ordreid: sale.order_id,
                            varenummer: 'x',
                            antal: '1',
                            omprsalg: String(sale.revenue).replace(',', '.')
                        });

                        await new Promise((resolve) => {
                            const pixel = new Image();
                            const done = () => resolve();
                            const timeout = setTimeout(done, 1000);
                            pixel.onload = pixel.onerror = function () {
                                clearTimeout(timeout);
                                done();
                            };
                            pixel.src = 'https://www.partner-ads.com/dk/leadtracks2s.php?' + params.toString();
                        });

                        localStorage.setItem(sentKey, '1');
                        localStorage.removeItem(pendingSaleKey);
                    }

                    (async function () {
                        if (new URLSearchParams(window.location.search).get('billing') === 'success') {
                            await trackPartnerAdsSaleFromPending();
                        }

                        const requestedRedirectUrl = <?php echo json_encode($requestedRedirectUrl, JSON_UNESCAPED_SLASHES); ?>;
                        const redirectStorageKey = 'coherta_login_redirect_url_v1';
                        const sessionHandoffGuardKey = 'coherta_session_handoff_guard_v1';
                        const sessionHandoffGuardTtl = 60 * 1000;
                        if (requestedRedirectUrl) {
                            localStorage.setItem(redirectStorageKey, requestedRedirectUrl);
                        }
                        const clearStoredSession = <?php echo json_encode($clearStoredSession); ?>;
                        const tokenKeys = ['old_session', 'session_id', 'sessionId', 'token', 'access_token', 'accessToken', 'bearer_token', 'bearerToken'];

                        if (clearStoredSession) {
                            localStorage.removeItem('session_id');
                        }

                        function tokenFromParams(rawParams) {
                            const raw = String(rawParams || '').replace(/^[#?]/, '');
                            if (!raw) return '';
                            const query = raw.includes('?') ? raw.slice(raw.indexOf('?') + 1) : raw;
                            const params = new URLSearchParams(query);
                            for (const key of tokenKeys) {
                                const value = params.get(key);
                                if (value) return value;
                            }
                            return '';
                        }

                        function continueWithSession(sessionToken, guardAutomaticHandoff = false) {
                            const token = String(sessionToken || '').trim();
                            if (!token || token === 'null') return;
                            const pendingRedirectUrl = requestedRedirectUrl || localStorage.getItem(redirectStorageKey) || '';

                            if (guardAutomaticHandoff && pendingRedirectUrl) {
                                let previousHandoff = null;
                                try {
                                    previousHandoff = JSON.parse(localStorage.getItem(sessionHandoffGuardKey) || 'null');
                                } catch {
                                    localStorage.removeItem(sessionHandoffGuardKey);
                                }

                                const repeatedHandoff = previousHandoff
                                    && previousHandoff.url === pendingRedirectUrl
                                    && Date.now() - Number(previousHandoff.timestamp || 0) < sessionHandoffGuardTtl;

                                if (repeatedHandoff) {
                                    localStorage.removeItem('session_id');
                                    localStorage.removeItem(redirectStorageKey);
                                    localStorage.removeItem(sessionHandoffGuardKey);
                                    return;
                                }

                                localStorage.setItem(sessionHandoffGuardKey, JSON.stringify({
                                    url: pendingRedirectUrl,
                                    timestamp: Date.now()
                                }));
                            }

                            localStorage.setItem('session_id', token);
                            document.body.innerHTML = '<div style="display:flex;justify-content:center;align-items:center;height:100vh;"><h2>Logging you in, one moment</h2></div>';
                            const loginUrl = new URL('login', window.location.href);
                            loginUrl.searchParams.set('old_session', token);
                            if (pendingRedirectUrl) {
                                loginUrl.searchParams.set('url', pendingRedirectUrl);
                                localStorage.removeItem(redirectStorageKey);
                            }
                            window.location.href = loginUrl.pathname + loginUrl.search;
                        }

                        continueWithSession(tokenFromParams(window.location.hash));

                        const sessionToken = localStorage.getItem('session_id');
                        if (sessionToken && sessionToken !== 'null') {
                            continueWithSession(sessionToken, true);
                        }
                    })();
                    document.addEventListener('DOMContentLoaded', function() {
                        const usernameInput = document.getElementById('login-username');
                        const passwordInput = document.getElementById('login-password');
                        const togglePassword = document.getElementById('toggleLoginPassword');
                        const form = document.querySelector('.theme-form');
                        function submitOnEnter(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                form.requestSubmit();
                            }
                        }
                        usernameInput.addEventListener('keydown', submitOnEnter);
                        passwordInput.addEventListener('keydown', submitOnEnter);
                        togglePassword.addEventListener('click', function () {
                            const isPassword = passwordInput.type === 'password';
                            passwordInput.type = isPassword ? 'text' : 'password';
                            togglePassword.textContent = isPassword ? 'Hide' : 'Show';
                            togglePassword.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                        });
                    });
                </script>
            </div>
              <div class="text-center mt-3">
                  <h3>Visit our Country Pages:</h3>
                  <a href="/da/" class="btn btn-link">Go to Denmark</a>
                  <a href="/uk/" class="btn btn-link">Go to United Kingdom</a>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include('partial/footer-end.php'); ?>
