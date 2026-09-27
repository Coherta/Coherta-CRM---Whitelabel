<?php
declare(strict_types=1);
require_once __DIR__ . '/base.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function introduction_response(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (empty($_SESSION['token']) || !$client->isLoggedIn()) introduction_response(['detail' => 'Please log in again to manage your introductions.'], 401);
$action = (string)($_GET['action'] ?? '');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$allowed = ['icp' => ['GET', 'PUT'], 'icps' => ['GET', 'POST', 'PUT', 'DELETE'], 'overview' => ['GET'], 'opportunity' => ['GET'], 'contacts' => ['GET'], 'stars' => ['PUT'], 'schedule' => ['POST'], 'remove' => ['POST']];
if (!isset($allowed[$action])) introduction_response(['detail' => 'Unknown introduction action.'], 404);
if (!in_array($method, $allowed[$action], true)) {
    header('Allow: ' . implode(', ', $allowed[$action]));
    introduction_response(['detail' => 'Method not allowed.'], 405);
}
if ($method !== 'GET') {
    $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['introductions_csrf']) || !is_string($csrf) || !hash_equals($_SESSION['introductions_csrf'], $csrf)) introduction_response(['detail' => 'Your session has changed. Refresh this page and try again.'], 403);
}
$body = null;
if ($method !== 'GET') {
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if ($raw === false || strlen($raw) > 65536) introduction_response(['detail' => 'The request is too large.'], 413);
    $body = json_decode($raw, true);
    if (!is_array($body)) introduction_response(['detail' => 'A valid JSON request is required.'], 422);
}
if ($action === 'icp' && $method === 'PUT') {
    if (!is_string($body['name'] ?? null) || trim($body['name']) === '' || strlen($body['name']) > 800) introduction_response(['detail' => 'Enter an ICP name of up to 200 characters.'], 422);
    foreach (['job_titles', 'industries', 'locations', 'positive_signals', 'disqualifiers'] as $field) {
        if (!is_array($body[$field] ?? null) || count($body[$field]) > 100) introduction_response(['detail' => 'ICP fields must contain at most 100 items.'], 422);
        foreach ($body[$field] as $item) if (!is_string($item) || trim($item) === '' || strlen($item) > 1000) introduction_response(['detail' => 'Each ICP item must be non-empty and no longer than 1,000 bytes.'], 422);
    }
    foreach (['employee_min', 'employee_max'] as $field) {
        if (($body[$field] ?? null) !== null && (!is_int($body[$field]) || $body[$field] < 0 || $body[$field] > 2147483647)) introduction_response(['detail' => 'Employee counts must be non-negative whole numbers.'], 422);
    }
    if (($body['employee_min'] ?? null) !== null && ($body['employee_max'] ?? null) !== null && $body['employee_min'] > $body['employee_max']) introduction_response(['detail' => 'Minimum employees cannot be greater than maximum employees.'], 422);
    $body = array_intersect_key($body, array_flip(['name', 'job_titles', 'industries', 'locations', 'positive_signals', 'disqualifiers', 'employee_min', 'employee_max']));
}
// Call the PHP route shim directly: nginx rejects PUT to the physical directory URL.
$endpoint = $action === 'icp' ? '/extension/icp' : '/plugins__sales_insider__introductions.php?action=' . rawurlencode($action);
if ($action === 'contacts') $endpoint .= '&q=' . rawurlencode(substr((string)($_GET['q'] ?? ''), 0, 2001));
if ($action === 'opportunity') {
    foreach (['icp_id', 'participant_id', 'introducer_id'] as $field) {
        $value = $_GET[$field] ?? null;
        if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/D', $value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            introduction_response(['detail' => $field . ' must be a positive integer.'], 422);
        }
        $endpoint .= '&' . $field . '=' . rawurlencode($value);
    }
}
$token = $_SESSION['token'];
session_write_close();
$headers = ['Accept: application/json', 'Authorization: Bearer ' . $token];
$curl = curl_init('https://api.coherta.com' . $endpoint);
curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 28, CURLOPT_CUSTOMREQUEST => $method]);
if ($body !== null) {
    $headers[] = 'Content-Type: application/json';
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($curl);
$status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
$failed = curl_errno($curl) !== 0;
curl_close($curl);
if ($failed || $status === 0) introduction_response(['detail' => 'The introduction service did not respond. Please try again.'], 502);
$decoded = json_decode((string)$response, true);
if ($status >= 500 || !is_array($decoded)) introduction_response(['detail' => 'The introduction service is currently unavailable. Please try again shortly.'], 502);
if ($status === 404 && !in_array($action, ['icp', 'opportunity'], true)) introduction_response(['detail' => 'Get an Introduction is waiting for its service setup. Please contact your account manager.'], 503);
introduction_response($decoded, $status);
