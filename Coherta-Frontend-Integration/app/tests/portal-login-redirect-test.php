<?php
declare(strict_types=1);
namespace PortalLoginTests;

// Run with: php app/tests/portal-login-redirect-test.php
// Exercise the real header guard and login routing without contacting the authentication API.
final class ApiClient
{
    public ?array $accessRights = [];
    public ?array $user = ['id' => 11, 'img_key' => 'avatar', 'connectors' => ['linkedin' => ['status' => 1]]];
    public bool $introductionMember = false;
    public int $userReads = 0;
    public int $membershipReads = 0;
    public string $token = '';

    public function getCurrentUser(): ?array { $this->userReads++; return $this->user; }
    public function getAccessRights(): array { return $this->accessRights ?? []; }
    public function get(string $path): array
    {
        if ($path !== '/plugins__sales_insider__introductions.php?action=onboarding-status') {
            throw new \RuntimeException('Unexpected API call: ' . $path);
        }
        $this->membershipReads++;
        return ['registered' => $this->introductionMember];
    }
    public function set_access_token(string $token): void { $_SESSION['token'] = $this->token = $token; }
    public function setToken(string $token): void { $this->token = $token; }
}

final class Redirect extends \RuntimeException {}
function header(string $value): void
{
    $GLOBALS['portalTestHeaders'][] = $value;
    if (!empty($GLOBALS['portalTestStopAtRedirect']) && str_starts_with($value, 'Location: ')) {
        throw new Redirect($value);
    }
}

$headerSource = file_get_contents(__DIR__ . '/../partial/header.php');
$guardEnd = strpos($headerSource, '$payment_information_required = false;');
if ($guardEnd === false) throw new \RuntimeException('Could not load portal guard.');
$guardSource = substr($headerSource, strlen('<?php'), $guardEnd - strlen('<?php'));
$loginSource = file_get_contents(__DIR__ . '/../login.php');
$start = strpos($loginSource, 'function login_access_flag_enabled(');
$end = strpos($loginSource, '$requestToken = login_token_from_params($_GET);');
if ($start === false || $end === false) throw new \RuntimeException('Could not load login functions.');
eval('namespace ' . __NAMESPACE__ . ';' . substr($loginSource, $start, $end - $start));

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) throw new \RuntimeException($message);
    $checks++;
}
function runGuard(ApiClient $client, string $uri, bool $publicPage = false): bool
{
    global $guardSource;
    $_SERVER['REQUEST_URI'] = $uri;
    $GLOBALS['portalTestHeaders'] = [];
    $GLOBALS['portalTestStopAtRedirect'] = true;
    try {
        eval('namespace ' . __NAMESPACE__ . ';' . $guardSource);
        return false;
    } catch (Redirect $redirect) {
        return true;
    } finally {
        $GLOBALS['portalTestStopAtRedirect'] = false;
    }
}
function expectReturn(ApiClient $client, string $uri, bool $tokenLogin): void
{
    $GLOBALS['portalTestHeaders'] = [];
    $_SESSION['login_redirect_url'] = $uri;
    if ($tokenLogin) {
        check(login_authenticate_with_token($client, 'test-restored-session', $uri), 'Token authentication succeeds.');
    } else {
        $_SESSION['token'] = 'test-password-session';
        login_redirect_after_auth($client, $client->user, $uri);
    }
    check($GLOBALS['portalTestHeaders'] === ['Location: ' . $uri], 'Login returns to the exact requested portal URL.');
    check(!isset($_SESSION['portal_login_return_url']) && !isset($_SESSION['login_redirect_url']), 'The saved destination is consumed.');
    check($client->membershipReads === 0, 'A protected page takes priority over product landing pages.');
}

$_SERVER['HTTP_HOST'] = 'www.coherta.com';
$destinations = [
    '/app/',
    '/app/company-activity',
    '/app/company-activity.php',
    '/app/company-activity?billing=success&checkout_reference=fixture%2Breturn',
    '/app/campaign/campaigns-dashboard?embedded=1',
    '/app/my-account',
];
foreach ($destinations as $uri) {
    foreach ([false, true] as $introductionMember) {
        foreach ([false, true] as $tokenLogin) {
            $client = new ApiClient();
            $client->introductionMember = $introductionMember;
            $_SESSION = [];
            check(runGuard($client, $uri), 'A logged-out portal visit redirects before rendering.');
            check($GLOBALS['portalTestHeaders'] === ['Cache-Control: no-store', 'Location: /app/login?url=' . rawurlencode($uri)], 'Login receives the complete encoded return URL.');
            check($client->userReads === 0, 'A missing session does not call the user API.');
            expectReturn($client, $uri, $tokenLogin);
        }
    }
}

$client = new ApiClient();
$client->user = null;
$_SESSION = ['token' => 'expired-session'];
check(runGuard($client, '/app/company-activity'), 'An expired session redirects to login.');
check($client->userReads === 1, 'Existing tokens are validated.');
$client->user = ['id' => 11];
expectReturn($client, '/app/company-activity', true);

foreach (['/app/login', '/app/forgot-password', '/app/sign-up', '/app/setup-funnel', '/app/logout'] as $uri) {
    $client = new ApiClient();
    $_SESSION = [];
    check(!runGuard($client, $uri, true), 'Public pages remain accessible without login.');
    check($client->userReads === 0 && $GLOBALS['portalTestHeaders'] === [], 'Public pages do not run the guard.');
}
$client = new ApiClient();
$_SESSION = ['token' => 'valid-session'];
check(!runGuard($client, '/app/company-activity'), 'Authenticated visitors can load protected pages.');
check($GLOBALS['user']['id'] === 11 && $GLOBALS['user_image'] === 'https://api.coherta.com/images/avatar', 'The guard initializes the authenticated user and avatar.');

// A saved destination from an unrelated visit must not change normal product routing.
$client = new ApiClient();
$client->introductionMember = true;
$_SESSION = ['portal_login_return_url' => '/app/company-activity'];
$GLOBALS['portalTestHeaders'] = [];
login_redirect_after_auth($client, $client->user, '/app/campaign');
check($GLOBALS['portalTestHeaders'] === ['Location: /app/sales_insider'], 'Unrelated login destinations keep normal Introduction routing.');
check(!isset($_SESSION['portal_login_return_url']), 'Unrelated saved return URLs are cleared.');

foreach (['https://untrusted.example/path', '//untrusted.example/path', "/app/company-activity\r\nX-Test: injected"] as $unsafe) {
    $client = new ApiClient();
    $_SESSION = ['portal_login_return_url' => $unsafe];
    $GLOBALS['portalTestHeaders'] = [];
    login_redirect_after_auth($client, $client->user, $unsafe);
    check($GLOBALS['portalTestHeaders'] === ['Location: /app/index'], 'Unsafe return URLs use the default landing page.');
}

echo "Passed $checks portal login redirect checks; no live accounts used.\n";
