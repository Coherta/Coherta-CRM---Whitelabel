<?php
declare(strict_types=1);
// All provider calls and storage are isolated by the existing API test harness.
require dirname(__DIR__, 3) . '/api-crm/php/tests/payment_providers_test.php';
$initialChecks = $checks;
putenv('PAYMENTS_STRIPE_ENABLED=false');
putenv('PAYMENTS_REVOLUT_ENABLED=true');

$make = static function (string $live, string $cached = 'active') use ($pdo): array {
    global $gateway;
    static $companyId = 2000;
    $companyId++;
    $pdo->prepare('INSERT INTO crm_company (id) VALUES (?)')->execute([$companyId]);
    $params = ['mode' => 'subscription', 'customer' => 'cus_test', 'metadata[company_id]' => (string)$companyId,
        'metadata[email]' => 'test@example.com', 'success_url' => 'https://www.coherta.com/app/setup-funnel',
        'cancel_url' => 'https://www.coherta.com/app/setup-funnel',
        'line_items[0][price_data][currency]' => 'dkk', 'line_items[0][price_data][unit_amount]' => 99900,
        'line_items[0][price_data][recurring][interval]' => 'month', 'line_items[0][quantity]' => 1];
    $checkout = subscription_checkout_create($pdo, $params, null, 'revolut');
    $stmt = $pdo->prepare('SELECT * FROM billing_checkouts WHERE id = ?');
    $stmt->execute([$checkout['checkout_reference']]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
    $gateway['revolut_subscriptions'][$attempt['subscription_id']]['state'] = 'active';
    $gateway['revolut_subscriptions'][$attempt['subscription_id']]['current_cycle_id'] = 'cycle-1';
    $gateway['orders'][$attempt['checkout_id']]['state'] = 'completed';
    revolut_sync_checkout($pdo, $attempt, $gateway['orders'][$attempt['checkout_id']]);
    $gateway['revolut_subscriptions'][$attempt['subscription_id']]['state'] = $live;
    $pdo->prepare('UPDATE billing_subscriptions SET state = ? WHERE company_id = ?')->execute([$cached, $companyId]);
    $attempt['state'] = 'completed';
    return [$params, $attempt];
};
$newCount = static function (): int { global $gateway; return count($gateway['revolut_subscriptions']); };
$reload = static function (string $reference) use ($pdo): array {
    $stmt = $pdo->prepare('SELECT * FROM billing_checkouts WHERE id = ?');
    $stmt->execute([$reference]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
};

foreach (['cancelled', 'finished', 'paused', 'overdue'] as $live) {
    [$params, $old] = $make($live);
    $before = $newCount();
    $callsBefore = count($gateway['calls']);
    $replacement = subscription_checkout_create($pdo, $params, null, 'revolut');
    payment_expect($before + 1, $newCount(), $live . ': creates exactly one replacement');
    payment_expect(false, $replacement['subscription'] === $old['subscription_id'], $live . ': returns a new subscription');
    payment_expect(['revolut', 'GET', '/subscriptions/' . $old['subscription_id']], $gateway['calls'][$callsBefore], 'live state checked before new billing');
    payment_expect(in_array($live, ['paused', 'overdue'], true) ? 'cancelled' : $live,
        $gateway['revolut_subscriptions'][$old['subscription_id']]['state'], 'old subscription cannot resume billing');
    payment_expect($replacement['id'], subscription_checkout_create($pdo, $params, null, 'revolut')['id'], 'retry reuses the new checkout');
    payment_expect($before + 1, $newCount(), 'retry creates no duplicate');
    $fresh = $reload($replacement['checkout_reference']);
    payment_expect($old['id'], json_decode($fresh['details_json'], true)['replaces_checkout_reference'], 'replacement records its predecessor');
    // Force an exact timestamp tie, as checkout + webhook can happen within one second.
    $pdo->prepare('UPDATE billing_checkouts SET created_at = ? WHERE id = ?')->execute([$old['created_at'], $fresh['id']]);
    $fresh['created_at'] = $old['created_at'];
    $gateway['revolut_subscriptions'][$fresh['subscription_id']]['state'] = 'active';
    $gateway['revolut_subscriptions'][$fresh['subscription_id']]['current_cycle_id'] = 'cycle-1';
    revolut_sync_checkout($pdo, $fresh);
    payment_expect($fresh['subscription_id'], payment_company_subscription($pdo, (int)$old['company_id'])['subscription_id'], 'same-second replacement becomes current');
    revolut_sync_checkout($pdo, $old);
    payment_expect($fresh['subscription_id'], payment_company_subscription($pdo, (int)$old['company_id'])['subscription_id'], 'late old webhook cannot replace the new subscription');
    payment_expect(1, (int)$pdo->query('SELECT COUNT(*) FROM billing_payments WHERE company_id = ' . (int)$old['company_id'])->fetchColumn(), 'historical payment ledger preserved');
}

foreach (['active', 'cancelled', 'paused', 'overdue'] as $cached) {
    [$params, $old] = $make('active', $cached);
    $before = $newCount();
    $pdo->prepare("UPDATE billing_subscriptions SET current_period_end = '2000-01-01 00:00:00' WHERE company_id = ?")->execute([$old['company_id']]);
    payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 409, 'live active state blocks regardless of stale local state/dates');
    payment_expect($before, $newCount(), 'active subscriptions never create replacements');
    payment_expect('active', $gateway['revolut_subscriptions'][$old['subscription_id']]['state'], 'active subscription is not cancelled');
}
[$params, $old] = $make('active');
$pdo->prepare('DELETE FROM billing_subscriptions WHERE company_id = ?')->execute([$old['company_id']]);
$before = $newCount();
for ($i = 0; $i < 2; $i++) {
    payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 409, 'completed checkout without a snapshot still checks live subscription');
}
payment_expect($before, $newCount(), 'repeated active conflicts cannot bypass the guard');

[$params, $old] = $make('pending');
$gateway['orders'][$old['checkout_id']]['state'] = 'pending';
$before = $newCount();
payment_expect($old['checkout_id'], subscription_checkout_create($pdo, $params, null, 'revolut')['id'], 'unfinished checkout is reusable');
payment_expect($before, $newCount(), 'pending retry is idempotent');

foreach (['unknown', 'ownership', 'lookup-failed', 'cancel-failed', 'cancel-unconfirmed', 'reactivated'] as $scenario) {
    [$params, $old] = $make('paused');
    $before = $newCount();
    $path = '/subscriptions/' . $old['subscription_id'];
    $reads = 0;
    if ($scenario === 'unknown') $gateway['revolut_subscriptions'][$old['subscription_id']]['state'] = 'unexpected';
    if ($scenario === 'ownership') $gateway['revolut_subscriptions'][$old['subscription_id']]['customer_id'] = 'another-customer';
    $gateway['revolut_override'] = static function ($method, $requestPath) use ($scenario, $path, $old, &$reads): ?array {
        global $gateway;
        if ($method === 'GET' && $requestPath === $path) {
            $reads++;
            if ($scenario === 'lookup-failed') throw new PaymentException('Simulated provider outage.', 502);
            if ($scenario === 'reactivated' && $reads === 2) $gateway['revolut_subscriptions'][$old['subscription_id']]['state'] = 'active';
        }
        if ($method === 'POST' && $requestPath === $path . '/cancel') {
            if ($scenario === 'cancel-failed') throw new PaymentException('Simulated cancellation failure.', 502);
            if ($scenario === 'cancel-unconfirmed') return [];
        }
        return null;
    };
    payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'),
        in_array($scenario, ['unknown', 'lookup-failed', 'cancel-failed'], true) ? 502 : 409,
        $scenario . ': uncertain status does not permit new billing');
    unset($gateway['revolut_override']);
    payment_expect($before, $newCount(), $scenario . ': no new subscription');
}

// A renewal can complete while cancelling an overdue subscription.
[$params, $old] = $make('overdue');
$before = $newCount();
$renewalId = 'order-9000';
$gateway['orders'][$renewalId] = array_replace($gateway['orders'][$old['checkout_id']], ['id' => $renewalId, 'state' => 'pending']);
$gateway['revolut_override'] = static function ($method, $path) use ($old, $renewalId): ?array {
    global $gateway;
    if ($method === 'GET' && $path === '/subscriptions/' . $old['subscription_id'] . '/cycles/cycle-1') {
        return ['id' => 'cycle-1', 'order_id' => $renewalId, 'end_date' => gmdate('c', time() + 2592000)];
    }
    if ($method === 'POST' && $path === '/subscriptions/' . $old['subscription_id'] . '/cancel') {
        $gateway['orders'][$renewalId]['state'] = 'completed';
    }
    return null;
};
payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 409, 'payment/cancellation race blocks a replacement');
unset($gateway['revolut_override']);
payment_expect('attention', $reload($old['id'])['state'], 'race is kept for reconciliation');
payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 409, 'retry cannot clear payment race');
payment_expect($before, $newCount(), 'race never creates another subscription');

// A lost cancellation response can be recovered from fresh provider state.
[$params, $old] = $make('paused');
$before = $newCount();
$gateway['revolut_override'] = static function ($method, $path) use ($old): ?array {
    global $gateway;
    if ($method === 'POST' && $path === '/subscriptions/' . $old['subscription_id'] . '/cancel') {
        $gateway['revolut_subscriptions'][$old['subscription_id']]['state'] = 'cancelled';
        throw new PaymentException('Simulated lost cancellation response.', 502);
    }
    return null;
};
payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 502, 'lost cancellation response stops this attempt');
unset($gateway['revolut_override']);
$replacement = subscription_checkout_create($pdo, $params, null, 'revolut');
payment_expect($before + 1, $newCount(), 'retry verifies cancellation and creates one replacement');
payment_expect($replacement['id'], subscription_checkout_create($pdo, $params, null, 'revolut')['id'], 'replacement retry is still idempotent');

// Persisted order snapshots must also catch a race after a lost cancel response.
[$params, $old] = $make('overdue');
$before = $newCount();
$gateway['orders'][$old['checkout_id']]['state'] = 'pending';
$gateway['revolut_override'] = static function ($method, $path) use ($old): ?array {
    global $gateway;
    if ($method === 'POST' && $path === '/subscriptions/' . $old['subscription_id'] . '/cancel') {
        $gateway['revolut_subscriptions'][$old['subscription_id']]['state'] = 'cancelled';
        $gateway['orders'][$old['checkout_id']]['state'] = 'completed';
        throw new PaymentException('Lost response after simultaneous payment.', 502);
    }
    return null;
};
payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 502, 'lost race response stops creation');
unset($gateway['revolut_override']);
payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 409, 'persisted baseline catches payment on retry');
payment_expect('attention', $reload($old['id'])['state'], 'lost response race requires reconciliation');
payment_expect($before, $newCount(), 'lost response race cannot charge twice');

foreach (['authorised', 'processing'] as $orderState) {
    [$params, $old] = $make('overdue');
    $before = $newCount();
    $gateway['orders'][$old['checkout_id']]['state'] = $orderState;
    payment_throws(static fn() => subscription_checkout_create($pdo, $params, null, 'revolut'), 409, 'in-flight payment must settle before replacement');
    payment_expect('overdue', $gateway['revolut_subscriptions'][$old['subscription_id']]['state'], 'in-flight payment is not cancelled');
    payment_expect($before, $newCount(), 'in-flight payment creates no replacement');
}

echo 'PASS: ' . ($checks - $initialChecks) . " inactive Revolut replacement assertions (fake gateways only).\n";
