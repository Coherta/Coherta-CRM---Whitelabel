<?php
declare(strict_types=1);

// Reuse the isolated in-memory database and fake gateways; no external calls.
$apiRoot = dirname(__DIR__, 3) . '/api-crm/php';
require $apiRoot . '/tests/payment_providers_test.php';

// Exercise the actual endpoint's dispatch with both providers enabled.
$endpoint = file_get_contents($apiRoot . '/billing__checkout.php');
if (!preg_match('/    \$requiredProvider = .*?\n    \$session = subscription_checkout_create\([^;]+;/s', $endpoint, $match)) {
    throw new RuntimeException('Setup checkout provider dispatch is missing.');
}
$dispatch = $match[0];
$run = static function (array $input) use ($pdo, $dispatch): array {
    $checkoutParams = ['mode' => 'subscription', 'customer' => 'cus_test', 'metadata[company_id]' => '1',
        'metadata[email]' => 'test@example.com', 'success_url' => 'https://www.coherta.com/app/setup-funnel?checkout_status=success',
        'cancel_url' => 'https://www.coherta.com/app/setup-funnel?checkout_status=cancelled',
        'line_items[0][price_data][currency]' => 'dkk', 'line_items[0][price_data][unit_amount]' => 99900,
        'line_items[0][price_data][recurring][interval]' => 'month', 'line_items[0][quantity]' => 1];
    $idempotencyKey = 'fixture';
    eval($dispatch);
    return $session;
};
$reset = static function () use ($pdo): void {
    global $gateway;
    foreach (['billing_checkouts', 'billing_subscriptions', 'billing_payments', 'subscription'] as $table) {
        $pdo->exec('DELETE FROM ' . $table);
    }
    $gateway['stripe_sessions'] = $gateway['stripe_subscriptions'] = $gateway['revolut_subscriptions'] = [];
    $gateway['orders'] = $gateway['plans'] = $gateway['calls'] = [];
    $gateway['lose_response'] = false;
};
$reset();
putenv('PAYMENTS_STRIPE_ENABLED=true');
putenv('PAYMENTS_REVOLUT_ENABLED=true');
$checkout = $run(['source' => 'setup-funnel']);
payment_expect('revolut', $checkout['provider'], 'setup uses Revolut even with Stripe enabled');
payment_expect($checkout['id'], $run(['source' => 'setup-funnel'])['id'], 'setup retries reuse the checkout');
payment_expect(0, count($gateway['stripe_sessions']), 'setup never creates a Stripe checkout');
putenv('PAYMENTS_REVOLUT_ENABLED=false');
$calls = count($gateway['calls']);
payment_throws(static fn() => $run(['source' => 'setup-funnel']), 503, 'disabled Revolut does not fall back to Stripe');
payment_expect($calls, count($gateway['calls']), 'disabled Revolut makes no provider calls');
$reset();
payment_throws(static fn() => $run(['source' => 'upgrade']), 503, 'disabled Revolut does not fall back to Stripe for other callers');

// The PHP-rendered gate is enabled by default, with Clearhaus kept isolated.
$page = file_get_contents(__DIR__ . '/../setup-funnel.php');
$flags = substr($page, 5, strpos($page, 'require_once "./base.php"') - 5);
$flagCheck = static function (array $query) use ($flags): array {
    $_GET = $query;
    eval($flags);
    return [$setupPaymentEnabled, $setupClearhausTestMode, $setupSignupsPaused];
};
payment_expect([true, false, false], $flagCheck([]), 'Revolut and new signups are enabled');
payment_expect([false, true, false], $flagCheck(['clearhaus' => '1']), 'Clearhaus never enables live checkout');
preg_match('/\$stripeSubscriptionId = .*?\$paymentRequired = [^;]+;/s', $page, $gate);
$gateCheck = static function (array $company_data) use ($gate): bool {
    $setupPaymentEnabled = true;
    $setupClearhausTestMode = false;
    eval($gate[0]);
    return $paymentRequired;
};
payment_expect(true, $gateCheck([]), 'inactive account needs payment');
payment_expect(false, $gateCheck(['payment_subscription_active' => true]), 'active Revolut skips payment');
payment_expect(false, $gateCheck(['stripe_subscription' => 'sub_existing']), 'existing Stripe subscription remains recognized');
payment_expect(true, $gateCheck(['payment_subscription_id' => 'rev_pending', 'payment_subscription_active' => false]), 'pending subscription is not sufficient');
echo "Setup Revolut routing and rendered payment-gate tests passed.\n";
