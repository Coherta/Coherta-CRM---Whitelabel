<?php
declare(strict_types=1);
require_once __DIR__ . '/base.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function intro_setup_response(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function intro_setup_upstream(string $path, string $method, ?array $body = null, bool $authenticated = true): array
{
    $headers = ['Accept: application/json'];
    if ($authenticated) $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    $curl = curl_init('https://api.coherta.com' . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 50, CURLOPT_CUSTOMREQUEST => $method]);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $raw = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $failed = curl_errno($curl) !== 0;
    curl_close($curl);
    $result = json_decode((string)$raw, true);
    if ($failed || !$status || !is_array($result) || $status >= 500) intro_setup_response(['detail' => 'The setup service is temporarily unavailable. Your progress is kept; please try again.'], 503);
    if ($status >= 400) {
        $detail = is_string($result['detail'] ?? null) ? $result['detail'] : 'We could not complete this step. Please check your details and try again.';
        // Never return an upstream response body: authentication endpoints can contain submitted fields.
        intro_setup_response(['detail' => mb_substr($detail, 0, 500)], $status);
    }
    return $result;
}

$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string)($_GET['action'] ?? 'state');
$allowed = ['state' => 'GET', 'target' => 'PUT', 'signup' => 'POST', 'login' => 'POST', 'connect' => 'POST', 'linkedin-status' => 'GET', 'linkedin-code' => 'PATCH', 'complete' => 'POST'];
if (!isset($allowed[$action])) intro_setup_response(['detail' => 'Unknown setup action.'], 404);
if ($allowed[$action] !== $method) { header('Allow: ' . $allowed[$action]); intro_setup_response(['detail' => 'Method not allowed.'], 405); }
$input = [];
if ($method !== 'GET') {
    $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($csrf) || empty($_SESSION['introductions_csrf']) || !hash_equals($_SESSION['introductions_csrf'], $csrf)) intro_setup_response(['detail' => 'Please refresh this page before continuing.'], 403);
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if ($raw === false || strlen($raw) > 65536) intro_setup_response(['detail' => 'Request too large.'], 413);
    $input = json_decode($raw, true);
    unset($raw);
    if (!is_array($input)) intro_setup_response(['detail' => 'A valid request is required.'], 422);
}
$authenticated = !empty($_SESSION['token']) && $client->isLoggedIn();
if ($action === 'target') {
    $description = $input['description'] ?? null;
    if (!is_string($description) || !mb_check_encoding($description, 'UTF-8') || trim($description) === '' || mb_strlen($description) > 8000 || strpos($description, "\0") !== false) intro_setup_response(['detail' => 'Describe who you want to reach in 1–8,000 characters.'], 422);
    $_SESSION['introduction_onboarding_description'] = trim($description);
    intro_setup_response(['saved' => true]);
}
if ($action === 'signup' || $action === 'login') {
    if ($authenticated) intro_setup_response(['authenticated' => true]);
    $attempts = array_values(array_filter($_SESSION['introduction_auth_attempts'] ?? [], static fn($at) => $at > time() - 900));
    if (count($attempts) >= 10) { header('Retry-After: 900'); intro_setup_response(['detail' => 'Too many attempts. Please try again in 15 minutes.'], 429); }
    $attempts[] = time(); $_SESSION['introduction_auth_attempts'] = $attempts;
    $email = is_string($input['email'] ?? null) ? trim($input['email']) : '';
    $password = $input['password'] ?? null;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || !is_string($password) || $password === '' || strlen($password) > 1000) intro_setup_response(['detail' => 'Enter your email and password.'], 422);
    if ($action === 'signup') {
        $result = intro_setup_upstream('/introductions__signup.php', 'POST', ['name' => $input['name'] ?? '', 'email' => $email, 'password' => $password], false);
        if (empty($result['token']) || !is_string($result['token'])) intro_setup_response(['detail' => 'Please sign in to continue setting up your account.'], 502);
        $client->set_access_token($result['token']);
    } else {
        if (!$client->fetchToken($email, $password)) intro_setup_response(['detail' => 'Your email or password is incorrect.'], 401);
    }
    unset($password, $input, $_SESSION['introduction_auth_attempts']);
    session_regenerate_id(true);
    intro_setup_response(['authenticated' => true]);
}
if ($action === 'state') {
    $state = ['authenticated' => $authenticated, 'description' => $_SESSION['introduction_onboarding_description'] ?? '', 'completed' => false, 'linkedin_connected' => false, 'stars' => []];
    if ($authenticated) {
        $saved = intro_setup_upstream('/plugins__sales_insider__introductions.php?action=onboarding', 'GET');
        $state = array_merge($state, array_intersect_key($saved, array_flip(['completed', 'linkedin_connected', 'description', 'stars', 'scouting_started_at'])));
        if (!$state['completed'] && !empty($_SESSION['introduction_onboarding_description'])) $state['description'] = $_SESSION['introduction_onboarding_description'];
        $state['name'] = $client->getCurrentUser()['full_name'] ?? '';
    }
    intro_setup_response($state);
}
if (!$authenticated) intro_setup_response(['detail' => 'Sign in to your Coherta account to continue.', 'requires_login' => true], 401);
if ($action === 'connect') {
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;
    if (!is_string($username) || trim($username) === '' || strlen($username) > 254 || !is_string($password) || $password === '' || strlen($password) > 1000) intro_setup_response(['detail' => 'Enter your LinkedIn username and password.'], 422);
    intro_setup_upstream('/apps/linkedin/connect', 'POST', ['username' => trim($username), 'password' => $password]);
    unset($password, $input);
    intro_setup_response(['status' => 0]);
}
if ($action === 'linkedin-status') {
    $result = intro_setup_upstream('/apps/linkedin/connect', 'GET');
    intro_setup_response(['status' => (int)($result['status'] ?? 0), 'code_submitted' => !empty($result['code'])]);
}
if ($action === 'linkedin-code') {
    $code = $input['code'] ?? null;
    if (!is_string($code) || !preg_match('/^[a-zA-Z0-9 -]{3,64}$/D', trim($code))) intro_setup_response(['detail' => 'Enter the verification code from LinkedIn.'], 422);
    intro_setup_upstream('/apps/linkedin/connect/sms', 'PATCH', ['sms_code' => trim($code)]);
    intro_setup_response(['status' => 0]);
}
if ($action === 'complete') {
    $description = $_SESSION['introduction_onboarding_description'] ?? '';
    if ($description === '') intro_setup_response(['detail' => 'Complete step 1 before starting scouting.'], 422);
    $result = intro_setup_upstream('/plugins__sales_insider__introductions.php?action=onboarding', 'POST', ['description' => $description, 'star_urls' => $input['star_urls'] ?? null]);
    if (empty($result['completed'])) intro_setup_response(['detail' => 'Setup is not complete yet. Please try again.'], 502);
    unset($_SESSION['introduction_onboarding_description']);
    intro_setup_response(['completed' => true, 'scouting_started_at' => $result['scouting_started_at'] ?? null]);
}
