<?php
require_once __DIR__ . '/../base.php';

header('Content-Type: application/json; charset=utf-8');

function enreach_json_response($payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!$client->isLoggedIn()) {
    enreach_json_response(['error' => 'Not authenticated.'], 401);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($method !== 'POST') {
    enreach_json_response(['error' => 'Only POST is allowed.', 'method' => $method], 405);
}

$rawInput = file_get_contents('php://input');
$contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? ''));
$input = [];

if (strpos($contentType, 'application/json') !== false) {
    $decoded = json_decode($rawInput ?: '', true);
    $input = is_array($decoded) ? $decoded : [];
} elseif (!empty($_POST)) {
    $input = $_POST;
} else {
    parse_str($rawInput ?: '', $parsedInput);
    $input = is_array($parsedInput) ? $parsedInput : [];
}

if (!is_array($input) || empty($input)) {
    enreach_json_response(['error' => 'Invalid request body.'], 400);
}

$path = '/' . ltrim((string)($input['path'] ?? ''), '/');
$allowedPaths = ['/campaigns', '/leaddefinitions'];
if (!in_array($path, $allowedPaths, true)) {
    enreach_json_response(['error' => 'Unsupported Enreach endpoint.'], 400);
}

$username = trim((string)($input['username'] ?? ''));
$password = trim((string)($input['password'] ?? ''));
if ($username === '' || $password === '') {
    enreach_json_response(['error' => 'Username and password are required.'], 422);
}

if (!function_exists('curl_init')) {
    enreach_json_response(['error' => 'cURL is not available on this server.'], 500);
}

$url = 'https://ws01.enreachoutbound.com/api' . $path;
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => $username . ':' . $password,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
    ],
]);

$body = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($body === false || $curlError !== '') {
    enreach_json_response(['error' => 'Could not connect to Enreach.', 'detail' => $curlError], 502);
}

$decoded = json_decode((string)$body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    enreach_json_response([
        'error' => 'Enreach returned a non-JSON response.',
        'status' => $httpCode ?: 502,
    ], $httpCode >= 400 ? $httpCode : 502);
}

http_response_code($httpCode ?: 200);
echo json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);