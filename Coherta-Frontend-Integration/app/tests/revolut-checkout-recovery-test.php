<?php
declare(strict_types=1);
$apiRoot = getenv('COHERTA_PAYMENT_TEST_API_ROOT') ?: dirname(__DIR__, 3) . '/api-crm/php';
require $apiRoot . '/tests/payment_providers_test.php';
require_once $apiRoot . '/lib/account_start.php';
require_once $apiRoot . '/lib/subscription_center_payments.php';
$initialChecks = $checks;
putenv('PAYMENTS_STRIPE_ENABLED=true');
putenv('PAYMENTS_REVOLUT_ENABLED=true');

final class RecoveryHubSpotFake extends HubSpotClient
{
    public string $amount = '100';
    public string $currency = 'DKK';
    public string $frequency = 'monthly';
    public string $trial = '0';
    public function __construct() {}
    public function getDeal(string $id, array $properties): array
    {
        return ['id' => $id, 'properties' => ['stripe_subscription_id' => '', 'trial_days' => $this->trial, 'dealname' => 'Recovery']];
    }
    public function getDealLineItems(string $id): array
    {
        return [['properties' => ['amount' => $this->amount, 'hs_line_item_currency_code' => $this->currency,
            'recurringbillingfrequency' => $this->frequency]]];
    }
}
$serial = 200;
$getAttempt = static function (string $id) use ($pdo): array {
    $stmt = $pdo->prepare('SELECT * FROM billing_checkouts WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
};
$makeFailed = static function (bool $lostResponse = false) use ($pdo, &$serial, &$gateway): array {
    $id = ++$serial;
    $pdo->prepare('INSERT INTO crm_company (id, hubspot_id) VALUES (?, ?)')->execute([$id, (string)$id]);
    $company = ['id' => $id, 'hubspot_id' => (string)$id, 'company_name' => 'Recovery fixture', 'email' => 'recovery@example.com'];
    $hubspot = new RecoveryHubSpotFake();
    if ($lostResponse) $gateway['lose_response'] = true;
    else $gateway['revolut_override'] = static function (string $method, string $path): ?array {
        if ($method === 'POST' && $path === '/subscriptions') throw new PaymentException('Simulated subscription creation failure.', 502);
        return null;
    };
    payment_throws(static fn() => account_start_checkout($pdo, $hubspot, $company), 502, 'creation failure is recoverable');
    unset($gateway['revolut_override']);
    $attempt = $pdo->query('SELECT * FROM billing_checkouts WHERE company_id = ' . $id)->fetch(PDO::FETCH_ASSOC);
    payment_expect('creating', $attempt['state'], 'fixture matches the unfinished checkout for company 848');
    payment_expect(null, $attempt['subscription_id'], 'subscription ID was not saved');
    return [$company, $hubspot, $attempt];
};
$postsSince = static fn(int $offset): array => array_values(array_filter(array_slice($gateway['calls'], $offset),
    static fn(array $call): bool => $call[1] === 'POST'));
$stripeBefore = count(array_filter($gateway['calls'], static fn(array $call): bool => $call[0] === 'stripe'));

[$company, $hubspot, $attempt] = $makeFailed();
$before = count($gateway['calls']);
$overview = subscription_center_overview($pdo, $hubspot, $company, 'stripe_subscription_id');
payment_expect(false, $overview['can_stop'], 'unfinished creation no longer blocks the overview');
payment_expect(false, $overview['can_resume'], 'an absent subscription offers Start');
payment_expect([], $postsSince($before), 'opening the page never creates a customer, plan or subscription');
payment_expect('creating', $getAttempt($attempt['id'])['state'], 'overview keeps the attempt for Start to recover');
$plans = count($gateway['plans']);
$customers = $gateway['customers'];
$resumed = account_start_gate_begin($pdo, $hubspot, $company)['checkout'];
payment_expect($attempt['id'], $resumed['checkout_reference'], 'Start account resumes the same operation');
payment_expect($attempt['customer_id'], $resumed['customer'], 'existing customer reused');
payment_expect($attempt['plan_variation_id'], $gateway['revolut_subscriptions'][$resumed['subscription']]['plan_variation_id'], 'live matching price reuses the saved plan');
payment_expect($plans, count($gateway['plans']), 'no unnecessary plan creation');
payment_expect($customers, $gateway['customers'], 'no unnecessary customer creation');
$subscriptions = count($gateway['revolut_subscriptions']);
payment_expect($resumed['id'], account_start_checkout($pdo, $hubspot, $company)['id'], 'repeated clicks reuse the checkout link');
payment_expect($subscriptions, count($gateway['revolut_subscriptions']), 'repeated clicks cannot duplicate subscriptions');

// Old snapshots saved only the variation ID: find it in the remote catalogue.
[$company, $hubspot, $attempt] = $makeFailed();
$details = json_decode($attempt['details_json'], true);
unset($details['revolut_plan_id']);
payment_checkout_save($pdo, $attempt['id'], ['details_json' => json_encode($details)]);
$plans = count($gateway['plans']);
payment_expect($attempt['id'], account_start_checkout($pdo, $hubspot, $company)['checkout_reference'], 'legacy variation-only attempt recovers');
payment_expect($plans, count($gateway['plans']), 'legacy recovery verifies and reuses the remote plan');

// A changed local price must never create a subscription on the old plan first.
[$company, $hubspot, $attempt] = $makeFailed();
$hubspot->amount = '120';
$plans = count($gateway['plans']);
$subscriptions = count($gateway['revolut_subscriptions']);
$new = account_start_checkout($pdo, $hubspot, $company, 'subscription_center');
payment_expect(false, $new['checkout_reference'] === $attempt['id'], 'changed price gets a new request reference');
payment_expect($plans + 1, count($gateway['plans']), 'changed price creates a correct plan');
payment_expect($subscriptions + 1, count($gateway['revolut_subscriptions']), 'no subscription is created on the obsolete plan');
$variation = $gateway['revolut_subscriptions'][$new['subscription']]['plan_variation_id'];
payment_expect(15000, $gateway['plans'][$variation]['variations'][0]['phases'][0]['amount'], 'new amount includes the existing DKK policy exactly once');
payment_expect('cancelled', $getAttempt($attempt['id'])['state'], 'obsolete attempt stops blocking the overview');
payment_expect(true, subscription_center_overview($pdo, $hubspot, $company, 'stripe_subscription_id')['can_resume'], 'pending replacement can continue from Subscription Center');

// Trust live Revolut price, not just our saved fingerprint, including currencies and periods.
foreach (['amount', 'currency', 'cycle_duration', 'archived', 'missing'] as $change) {
    [$company, $hubspot, $attempt] = $makeFailed();
    $variation = $attempt['plan_variation_id'];
    if ($change === 'archived') $gateway['plans'][$variation]['state'] = 'archived';
    elseif ($change === 'missing') $gateway['plans'][$variation]['state'] = 'deleted';
    else $gateway['plans'][$variation]['variations'][0]['phases'][0][$change] = match ($change) {
        'amount' => 10000, 'currency' => 'EUR', 'cycle_duration' => 'P1Y',
    };
    $plans = count($gateway['plans']);
    $new = account_start_checkout($pdo, $hubspot, $company);
    payment_expect($plans + 1, count($gateway['plans']), $change . ': remote mismatch creates a fresh plan');
    payment_expect(false, $variation === $gateway['revolut_subscriptions'][$new['subscription']]['plan_variation_id'], $change . ': old variation not reused');
}

// Provider outages cannot be mistaken for a missing plan or subscription.
foreach (['plans', 'subscriptions'] as $failure) {
    [$company, $hubspot, $attempt] = $makeFailed();
    $gateway['revolut_override'] = static function (string $method, string $path) use ($failure): ?array {
        if ($method === 'GET' && ($failure === 'plans' ? str_starts_with($path, '/subscription-plans') : $path === '/subscriptions')) {
            throw new PaymentException('Simulated read outage.', 502);
        }
        return null;
    };
    $before = count($gateway['calls']);
    payment_throws(static fn() => account_start_checkout($pdo, $hubspot, $company), 502, $failure . ': failed verification stops creation');
    payment_expect([], $postsSince($before), $failure . ': no provider mutations with unverified price or subscription');
    unset($gateway['revolut_override']);
}

// A subscription created before a lost response must be found, not duplicated.
[$company, $hubspot, $attempt] = $makeFailed(true);
$before = count($gateway['calls']);
$overview = subscription_center_overview($pdo, $hubspot, $company, 'stripe_subscription_id');
payment_expect('pending', $overview['payment_state'], 'overview discovers an unsaved remote subscription');
payment_expect(true, $overview['can_resume'], 'overview offers continuation after a lost response');
payment_expect([], $postsSince($before), 'discovery is read-only');
$subscriptions = count($gateway['revolut_subscriptions']);
$new = account_start_checkout($pdo, $hubspot, $company);
payment_expect($attempt['id'], $new['checkout_reference'], 'lost-response retry keeps the original reference');
payment_expect($subscriptions, count($gateway['revolut_subscriptions']), 'lost-response retry does not duplicate a subscription');

// An already-active subscription discovered through the reference must block another charge.
[$company, $hubspot, $attempt] = $makeFailed(true);
$remote = end($gateway['revolut_subscriptions']);
$gateway['revolut_subscriptions'][$remote['id']]['state'] = 'active';
$before = count($gateway['calls']);
payment_throws(static fn() => account_start_checkout($pdo, $hubspot, $company), 409, 'lost active subscription blocks duplicate creation');
payment_expect([], $postsSince($before), 'active recovery performs no payment mutations');

// A failed setup order needs a new subscription, but keeps a matching plan and customer.
[$company, $hubspot, $attempt] = $makeFailed(true);
$remote = end($gateway['revolut_subscriptions']);
$gateway['orders'][$remote['setup_order_id']]['state'] = 'failed';
$plans = count($gateway['plans']);
$new = account_start_checkout($pdo, $hubspot, $company);
payment_expect('cancelled', $gateway['revolut_subscriptions'][$remote['id']]['state'], 'failed old setup cannot be paid after replacement');
payment_expect($plans, count($gateway['plans']), 'failed setup reuses the matching plan');
payment_expect($attempt['customer_id'], $new['customer'], 'failed setup reuses the customer');
payment_expect(false, $new['subscription'] === $remote['id'], 'failed setup gets a new subscription and checkout');

// Old idempotency keys are retired only after proving there is no remote subscription.
[$company, $hubspot, $attempt] = $makeFailed();
$pdo->prepare('UPDATE billing_checkouts SET created_at = ? WHERE id = ?')->execute([gmdate('Y-m-d H:i:s', time() - 3 * 86400), $attempt['id']]);
$plans = count($gateway['plans']);
$new = account_start_checkout($pdo, $hubspot, $company);
payment_expect(false, $new['checkout_reference'] === $attempt['id'], 'expired key gets a fresh reference');
payment_expect($plans, count($gateway['plans']), 'expired request still reuses the matching plan');

foreach (['authorised', 'processing', 'completed'] as $state) {
    [$company, $hubspot, $attempt] = $makeFailed(true);
    $remote = end($gateway['revolut_subscriptions']);
    $gateway['orders'][$remote['setup_order_id']]['state'] = $state;
    $hubspot->amount = '130';
    $before = count($gateway['calls']);
    payment_throws(static fn() => account_start_checkout($pdo, $hubspot, $company), 409, $state . ': payment in flight blocks replacement');
    payment_expect([], $postsSince($before), $state . ': no cancellation or new subscription');
}
payment_expect($stripeBefore, count(array_filter($gateway['calls'], static fn(array $call): bool => $call[0] === 'stripe')), 'all direct account-start recovery scenarios make zero Stripe calls');
fwrite(STDOUT, 'PASS: ' . ($checks - $initialChecks) . ' checkout recovery assertions.' . PHP_EOL);
