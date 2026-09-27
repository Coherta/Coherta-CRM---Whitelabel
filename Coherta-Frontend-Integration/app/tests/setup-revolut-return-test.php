<?php
declare(strict_types=1);

// Isolated database and fake payment gateways; no external requests.
$apiRoot = dirname(__DIR__, 3) . '/api-crm/php';
require $apiRoot . '/tests/payment_providers_test.php';
$initialChecks = $checks;

$baseUrl = 'https://www.coherta.com/da/setup-funnel?plan=standard&billing=monthly&payment_return=1&checkout_status=success';
$legacyUrl = $baseUrl . '&session_id={CHECKOUT_SESSION_ID}';
foreach (['session_id', '%73ession_id'] as $key) {
    $url = payment_redirect_url($baseUrl . '&' . $key . '=legacy&payment_provider=old&checkout_reference=old#payment', 'fixture');
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    payment_expect(false, array_key_exists('session_id', $query), 'no legacy session parameter reaches Revolut');
    payment_expect('revolut', $query['payment_provider'], 'provider is replaced without duplicates');
    payment_expect('fixture', $query['checkout_reference'], 'checkout reference is preserved');
    payment_expect('standard', $query['plan'], 'selected package survives the return');
    payment_expect('payment', parse_url($url, PHP_URL_FRAGMENT), 'return fragment survives');
}

$pdo->prepare('INSERT INTO crm_company (id) VALUES (?)')->execute([8701]);
$params = ['mode' => 'subscription', 'metadata[company_id]' => '8701', 'metadata[email]' => 'test@example.test',
    'success_url' => $legacyUrl, 'cancel_url' => 'https://www.coherta.com/app/setup-funnel?checkout_status=cancelled',
    'subscription_data[trial_period_days]' => 7, 'line_items[0][quantity]' => 1,
    'line_items[0][price_data][currency]' => 'dkk', 'line_items[0][price_data][unit_amount]' => 99900,
    'line_items[0][price_data][recurring][interval]' => 'month'];
$newParams = $params;
$newParams['success_url'] = $baseUrl;
payment_expect(payment_checkout_fingerprint(payment_expand_checkout($params)),
    payment_checkout_fingerprint(payment_expand_checkout($newParams)), 'removing session_id must not replace an existing checkout');
$changedTerms = $newParams;
$changedTerms['subscription_data[trial_period_days]'] = 0;
payment_expect(false, payment_checkout_fingerprint(payment_expand_checkout($newParams))
    === payment_checkout_fingerprint(payment_expand_checkout($changedTerms)), 'a changed trial still changes the checkout fingerprint');

$subscriptionCreates = 0;
$gateway['revolut_override'] = static function (string $method, string $path, array $body) use (&$subscriptionCreates): ?array {
    if ($method === 'POST' && $path === '/subscriptions') {
        parse_str(parse_url($body['setup_order_redirect_url'], PHP_URL_QUERY), $query);
        if (array_key_exists('session_id', $query)) throw new PaymentException('Revolut request failed (HTTP 403).', 502);
        payment_expect('P7D', $body['trial_duration'], 'the actual Revolut subscription receives seven free days');
        $subscriptionCreates++;
    }
    return null;
};
putenv('PAYMENTS_REVOLUT_ENABLED=true');
$checkout = subscription_checkout_create($pdo, $params, null, 'revolut');
payment_expect('revolut', $checkout['provider'], 'legacy saved return URL can open Revolut checkout');
payment_expect($checkout['id'], subscription_checkout_create($pdo, $newParams, null, 'revolut')['id'], 'retry reuses the same checkout');
payment_expect(1, $subscriptionCreates, 'retry creates no duplicate subscription');
unset($gateway['revolut_override']);

$endpoint = file_get_contents($apiRoot . '/billing__checkout.php');
payment_expect(false, str_contains($endpoint, 'session_id={CHECKOUT_SESSION_ID}'), 'new checkouts never add the obsolete session parameter');
echo 'PASS: ' . ($checks - $initialChecks) . " Revolut return URL, seven-day trial and retry assertions.\n";
