<?php
declare(strict_types=1);

// Use the real checkout/webhook code with SQLite and fake Revolut/HubSpot clients.
$apiRoot = dirname(__DIR__, 3) . '/api-crm/php';
require $apiRoot . '/tests/payment_providers_test.php';
$initialChecks = $checks;

$setupAttempt = static function (int $companyId, string $returnPath = '/da/setup-funnel') use ($pdo, $params): array {
    $pdo->prepare('INSERT INTO crm_company (id, hubspot_id) VALUES (?, ?)')->execute([$companyId, 'deal-' . $companyId]);
    $checkoutParams = $params;
    $checkoutParams['metadata[company_id]'] = (string)$companyId;
    $checkoutParams['success_url'] = 'https://www.coherta.com' . $returnPath . '?payment_return=1&checkout_status=success';
    $checkoutParams['subscription_data[trial_period_days]'] = 7;
    $checkoutParams['line_items[0][price_data][product]'] = 'prod_UWswNDxveWxoUo';
    $checkout = subscription_checkout_create($pdo, $checkoutParams, null, 'revolut');
    $stmt = $pdo->prepare('SELECT * FROM billing_checkouts WHERE id = ?');
    $stmt->execute([$checkout['checkout_reference']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
};

$setupHubspot = new PaymentTestHubSpot();
$setup = $setupAttempt(8801);
revolut_sync_checkout($pdo, $setup, null, $setupHubspot);
payment_expect([], $setupHubspot->updates, 'pending checkout does not activate the HubSpot subscription');
$activate($setup);
$gateway['revolut_subscriptions'][$setup['subscription_id']]['trial_end_date'] = gmdate('c', time() + 7 * 86400);
revolut_sync_checkout($pdo, $setup, null, $setupHubspot);
payment_expect($setup['subscription_id'], $setupHubspot->deals['deal-8801'] ?? '', 'active setup trial sends its Revolut subscription ID to HubSpot before the first payment');
payment_expect(0, (int)$pdo->query('SELECT COUNT(*) FROM billing_payments WHERE company_id = 8801')->fetchColumn(), 'trial sync requires no paid order');

$gateway['orders'][$setup['checkout_id']]['state'] = 'completed';
$gateway['orders'][$setup['checkout_id']]['amount'] = 0;
$setupEvent = ['event' => 'ORDER_COMPLETED', 'order_id' => $setup['checkout_id']];
revolut_handle_event($pdo, $setupEvent, $setupHubspot);
revolut_handle_event($pdo, $setupEvent, $setupHubspot);
payment_expect(1, count($setupHubspot->updates), 'zero-value setup and duplicate webhooks do not repeat the sync');
$gateway['orders']['order-8801'] = array_replace($gateway['orders'][$setup['checkout_id']], ['id' => 'order-8801', 'amount' => 12375]);
revolut_handle_event($pdo, ['event' => 'ORDER_COMPLETED', 'order_id' => 'order-8801'], $setupHubspot);
payment_expect($setup['subscription_id'], $setupHubspot->deals['deal-8801'], 'the first paid renewal preserves the subscription ID instead of replacing it with a product ID');
payment_expect(1, count($setupHubspot->updates), 'paid renewal does not rewrite an already synced setup subscription');
$setupHubspot->deals['deal-8801'] = '';
revolut_sync_checkout($pdo, $setup, null, $setupHubspot);
payment_expect('', $setupHubspot->deals['deal-8801'], 'reconciliation does not undo a later HubSpot stop');

$retry = $setupAttempt(8802, '/app/setup-funnel');
$activate($retry);
$setupHubspot->nextResponse = 'unconfirmed';
payment_throws(static fn() => revolut_sync_checkout($pdo, $retry, null, $setupHubspot), 502, 'unconfirmed subscription sync is retryable');
payment_expect('active', payment_company_subscription($pdo, 8802)['state'], 'HubSpot failure preserves verified subscription activation');
payment_expect(false, isset($readDetails($retry)['hubspot_subscription_synced']), 'failed sync is not marked complete');
revolut_sync_checkout($pdo, $retry, null, $setupHubspot);
payment_expect($retry['subscription_id'], $setupHubspot->deals['deal-8802'], 'reconciliation retries a failed trial sync');

$lost = $setupAttempt(8803, '/uk/setup-funnel.php');
$activate($lost);
$setupHubspot->nextResponse = 'lost';
payment_throws(static fn() => revolut_sync_checkout($pdo, $lost, null, $setupHubspot), 502, 'lost HubSpot response remains retryable');
$writes = count($setupHubspot->updates);
revolut_sync_checkout($pdo, $lost, null, $setupHubspot);
payment_expect($writes, count($setupHubspot->updates), 'lost response is recovered without repeating the update');
payment_expect($lost['subscription_id'], $readDetails($lost)['hubspot_subscription_synced']['subscription_id'], 'recovered sync is persisted');

$legacy = $setupAttempt(8804);
$activate($legacy);
$legacyDetails = $readDetails($legacy);
$legacyDetails['hubspot_products_synced'] = ['deal_id' => 'deal-8804', 'product_id' => 'prod_UWswNDxveWxoUo'];
payment_checkout_save($pdo, $legacy['id'], ['details_json' => json_encode($legacyDetails, JSON_THROW_ON_ERROR)]);
$setupHubspot->deals['deal-8804'] = 'prod_UWswNDxveWxoUo';
revolut_sync_checkout($pdo, $legacy, null, $setupHubspot);
payment_expect($legacy['subscription_id'], $setupHubspot->deals['deal-8804'], 'previous setup checkouts are repaired even when the old product sync was complete');

$unlinked = $setupAttempt(8805);
$activate($unlinked);
$pdo->exec("UPDATE crm_company SET hubspot_id = NULL WHERE id = 8805");
revolut_sync_checkout($pdo, $unlinked, null, $setupHubspot);
payment_expect(false, isset($readDetails($unlinked)['hubspot_subscription_synced']), 'missing deal does not discard a pending sync');
$pdo->exec("UPDATE crm_company SET hubspot_id = 'deal-8805' WHERE id = 8805");
revolut_sync_checkout($pdo, $unlinked, null, $setupHubspot);
payment_expect($unlinked['subscription_id'], $setupHubspot->deals['deal-8805'], 'subscription sync recovers when the signup deal becomes available');

$ended = $setupAttempt(8806);
$gateway['revolut_subscriptions'][$ended['subscription_id']]['state'] = 'cancelled';
$writes = count($setupHubspot->updates);
revolut_sync_checkout($pdo, $ended, null, $setupHubspot);
payment_expect($writes, count($setupHubspot->updates), 'cancelled setup checkout cannot activate HubSpot');
$pdo->exec("UPDATE billing_subscriptions SET state = 'replaced' WHERE company_id = 8806");
$activate($ended);
revolut_sync_checkout($pdo, $ended, null, $setupHubspot);
payment_expect($writes, count($setupHubspot->updates), 'replaced setup checkout cannot overwrite HubSpot');

echo 'PASS: ' . ($checks - $initialChecks) . " setup subscription HubSpot assertions. No live requests made.\n";
