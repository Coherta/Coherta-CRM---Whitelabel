<?php
declare(strict_types=1);
// Reuse the existing isolated SQLite and fake-provider integration harness.
$apiRoot = getenv('COHERTA_PAYMENT_TEST_API_ROOT') ?: dirname(__DIR__, 3) . '/api-crm/php';
require $apiRoot . '/tests/payment_providers_test.php';
require_once $apiRoot . '/lib/account_start.php';
require_once __DIR__ . '/../partial/start-account-status.php';

// Account start also initializes the local Revolut billing association.
foreach (['method', 'next_renewal', 'created', 'last_updated', 'last_state'] as $column) {
    if (!in_array($column, array_column($pdo->query('PRAGMA table_info(crm_company_stripe)')->fetchAll(PDO::FETCH_ASSOC), 'name'), true)) {
        $pdo->exec('ALTER TABLE crm_company_stripe ADD COLUMN ' . $column . ' TEXT');
    }
}

class AccountStartHubSpotFake extends HubSpotClient
{
    public array $properties = ['stripe_subscription_id' => '', 'trial_days' => '14', 'dealname' => 'Agreed plan'];
    public array $items = [['properties' => ['amount' => '1499.50', 'hs_line_item_currency_code' => 'DKK', 'recurringbillingfrequency' => 'quarterly']]];
    public array $writes = [];
    public bool $failRead = false;
    public bool $failWrite = false;
    public function __construct() {}
    public function getDealProperties(): array
    {
        if ($this->failRead) throw new RuntimeException('HubSpot unavailable');
        return [['name' => 'stripe_subscription_id']];
    }
    public function getDeal(string $id, array $properties): array
    {
        if ($this->failRead) throw new RuntimeException('HubSpot unavailable');
        return ['id' => $id, 'properties' => $this->properties];
    }
    public function getDealLineItems(string $dealId): array { return $this->items; }
    public function updateDeal(string $id, array $props): array
    {
        if ($this->failWrite) throw new RuntimeException('HubSpot write failed');
        $this->writes[] = [$id, $props];
        $this->properties = array_merge($this->properties, $props);
        return ['id' => $id, 'properties' => $this->properties];
    }
}

$startChecks = $checks;
$hubspot = new AccountStartHubSpotFake();
$company = ['id' => 1, 'company_name' => 'Fixture', 'hubspot_id' => '100', 'email' => 'test@example.com', 'stripe_subscription' => 'stale'];
payment_expect(true, coherta_account_start_required(['source' => 'hubspot', 'subscription_id' => '']), 'empty HubSpot field opens modal');
payment_expect(true, coherta_account_start_required(['source' => 'hubspot', 'subscription_id' => '  ']), 'whitespace is empty');
payment_expect(false, coherta_account_start_required(['source' => 'hubspot', 'subscription_id' => 'sub_active']), 'nonempty HubSpot field hides modal');
payment_expect(true, coherta_account_start_required(null), 'failed status lookup does not grant access');
payment_expect(true, coherta_account_start_required('invalid'), 'malformed status cannot crash the page or hide the gate');
payment_expect(true, coherta_account_start_required(['source' => 'hubspot', 'subscription_id' => '', 'stripe_renewal_current' => true]), 'empty HubSpot field shows modal even with a future Stripe renewal');
foreach ([null, false, 'true', 1] as $renewalStatus) {
    payment_expect(true, coherta_account_start_required(['source' => 'hubspot', 'subscription_id' => '', 'stripe_renewal_current' => $renewalStatus]), 'renewal flags cannot override an empty HubSpot field');
}
payment_expect(true, coherta_account_start_required(['stripe_renewal_current' => true]), 'a renewal flag alone cannot override unknown subscription status');

// The subscription ID and future renewal must belong to the same Stripe row and company.
$gatePdo = new PDO('sqlite::memory:');
$gatePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$gatePdo->exec('CREATE TABLE crm_company_stripe (company_id INTEGER, method TEXT, stripe_subscription TEXT, next_renewal TEXT, last_state TEXT)');
$gateInsert = $gatePdo->prepare('INSERT INTO crm_company_stripe (company_id, method, stripe_subscription, next_renewal) VALUES (?, ?, ?, ?)');
$gateNow = new DateTimeImmutable('2026-09-23 12:00:00', new DateTimeZone('Europe/Copenhagen'));
$inactiveGate = subscription_center_snapshot($hubspot, $company, 'stripe_subscription_id');
foreach ([
    ['future date', [[1, 'stripe', 'sub_paid', '2026-09-24']], false],
    ['future timestamp', [[1, 'stripe', 'sub_paid', '2026-10-01 09:00:00']], false],
    ['renewal today', [[1, 'stripe', 'sub_paid', '2026-09-23']], true],
    ['renewal later today', [[1, 'stripe', 'sub_paid', '2026-09-23 23:59:59']], true],
    ['past renewal', [[1, 'stripe', 'sub_paid', '2026-09-22']], true],
    ['missing renewal', [[1, 'stripe', 'sub_paid', null]], true],
    ['invalid renewal', [[1, 'stripe', 'sub_paid', 'invalid']], true],
    ['missing Stripe subscription', [[1, 'stripe', null, '2026-10-01']], true],
    ['empty Stripe subscription', [[1, 'stripe', '', '2026-10-01']], true],
    ['blank Stripe subscription', [[1, 'stripe', '   ', '2026-10-01']], true],
    ['historical method does not override paid renewal', [[1, 'revolut', 'sub_paid', '2026-10-01']], false],
    ['different company', [[2, 'stripe', 'sub_paid', '2026-10-01']], true],
    ['no Stripe record', [], true],
    ['fields on different rows', [[1, 'stripe', 'sub_old', '2026-09-22'], [1, 'stripe', '', '2026-10-01']], true],
    ['one current Stripe record', [[1, 'stripe', 'sub_old', '2026-09-22'], [1, 'stripe', 'sub_paid', '2026-10-01']], false],
] as [$case, $rows, $stripeFallbackRequired]) {
    $gatePdo->exec('DELETE FROM crm_company_stripe');
    foreach ($rows as $row) $gateInsert->execute($row);
    $gate = account_start_gate_snapshot($gatePdo, $inactiveGate, 1, $gateNow);
    payment_expect(!$stripeFallbackRequired, $gate['stripe_renewal_current'], $case . ': local renewal metadata');
    payment_expect(true, coherta_account_start_required($gate), $case . ': empty HubSpot field keeps modal visible');
    payment_expect(false, $gate['active'], $case . ': HubSpot status remains unchanged');
    payment_expect('', $gate['subscription_id'], $case . ': no fabricated subscription ID');
}
$gatePdo->exec('DELETE FROM crm_company_stripe');
$gateInsert->execute([1, 'stripe', 'sub_paid', '2026-09-24 09:00:00']);
$midnightCopenhagen = new DateTimeImmutable('2026-09-23 22:30:00', new DateTimeZone('UTC'));
payment_expect(false, account_start_gate_snapshot($gatePdo, $inactiveGate, 1, $midnightCopenhagen)['stripe_renewal_current'], 'after today uses Copenhagen calendar date, including across UTC midnight');
$activeGate = array_replace($inactiveGate, ['active' => true, 'subscription_id' => 'sub_active']);
payment_expect(false, coherta_account_start_required(account_start_gate_snapshot(new PDO('sqlite::memory:'), $activeGate, 1, $gateNow)), 'active HubSpot accounts do not need the Stripe fallback query');

// Regression: company 534 is paid through October 12, even if HubSpot cannot be read.
$gatePdo->exec("CREATE TABLE crm_users (id INTEGER, username TEXT, email TEXT);
    CREATE TABLE crm_company (id INTEGER, company_name TEXT, hubspot_id TEXT);
    CREATE TABLE crm_user_company (user_id INTEGER, company_id INTEGER, disabled INTEGER);
    INSERT INTO crm_users VALUES (1, 'multiple-companies', 'fixture@example.com'), (2, 'unrelated', 'other@example.com');
    INSERT INTO crm_company VALUES (1, 'First company', '100'), (534, 'Paid company', '53400');
    INSERT INTO crm_user_company VALUES (1, 1, 0), (1, 534, 0), (2, 1, 0), (2, 534, 1)");
$gateCompany = account_start_company($gatePdo, 'multiple-companies', 534);
payment_expect(534, (int)$gateCompany['id'], 'gate selects the displayed company instead of the lowest company ID');
payment_expect(1, (int)account_start_company($gatePdo, 'multiple-companies')['id'], 'other existing requests preserve their default company');
payment_expect(null, account_start_company($gatePdo, 'unrelated', 534), 'disabled membership cannot inspect another company');
payment_expect(null, account_start_company($gatePdo, 'unknown-user', 534), 'unknown user cannot inspect company 534');
payment_expect(null, account_start_company($gatePdo, 'multiple-companies', 999), 'missing membership cannot inspect another company');
$gateInsert->execute([534, 'stripe', 'sub_company_534', '2026-10-12 19:19:05']);
$gateHubspot = new AccountStartHubSpotFake();
foreach (['', '   ', null] as $emptyHubSpotId) {
    $gateHubspot->properties['stripe_subscription_id'] = $emptyHubSpotId;
    $hubspotGate = account_start_gate_status($gatePdo, $gateHubspot, $gateCompany, $gateNow);
    payment_expect('hubspot', $hubspotGate['source'], 'HubSpot status takes precedence over a current local subscription');
    payment_expect('', $hubspotGate['subscription_id'], 'empty HubSpot ID is not replaced with a local subscription ID');
    payment_expect(true, coherta_account_start_required($hubspotGate, 534), 'empty HubSpot field shows start account despite a future local renewal');
}
$gateHubspot->properties['stripe_subscription_id'] = 'sub_hubspot';
$hubspotGate = account_start_gate_status(new PDO('sqlite::memory:'), $gateHubspot, $gateCompany, $gateNow);
payment_expect('hubspot', $hubspotGate['source'], 'a readable HubSpot status does not depend on the local billing table');
payment_expect(false, coherta_account_start_required($hubspotGate, 534), 'a populated HubSpot field hides start account');
$gateHubspot->properties['stripe_subscription_id'] = '';
$gateHubspot->failRead = true;
$paidGate = account_start_gate_status($gatePdo, $gateHubspot, $gateCompany, $gateNow);
payment_expect('crm_company_stripe', $paidGate['source'], 'paid-through access comes directly from the Stripe database record');
payment_expect(false, coherta_account_start_required($paidGate, 534), 'company 534 has no start modal even during a HubSpot outage');
payment_expect(true, coherta_account_start_required($paidGate, 1), 'a different company response cannot suppress the modal');
foreach ([['active' => false], ['subscription_id' => ''], ['stripe_renewal_current' => false], ['company_id' => null]] as $invalidGate) {
    payment_expect(true, coherta_account_start_required(array_replace($paidGate, $invalidGate), 534), 'malformed Stripe gate remains closed');
}
payment_expect([], $gateHubspot->writes, 'checking the gate does not change HubSpot');
$gatePdo->exec('DELETE FROM crm_company_stripe WHERE company_id = 534');
$gateHubspot->failRead = false;
$unpaidGate = account_start_gate_status($gatePdo, $gateHubspot, $gateCompany, $gateNow);
payment_expect(true, coherta_account_start_required($unpaidGate, 534), 'without paid-through Stripe or HubSpot status the modal remains');
$gateHubspot->properties['stripe_subscription_id'] = 'sub_hubspot';
payment_expect(false, coherta_account_start_required(account_start_gate_status($gatePdo, $gateHubspot, $gateCompany, $gateNow), 534), 'a registered HubSpot subscription still hides the modal');
$gateHubspot->failRead = true;
try { account_start_gate_status($gatePdo, $gateHubspot, $gateCompany, $gateNow); throw new LogicException('Expected failed HubSpot read'); }
catch (RuntimeException $error) { payment_expect('HubSpot unavailable', $error->getMessage(), 'unverified accounts do not gain access during an outage'); }

foreach ([null, '', ' ', 0, '0'] as $value) payment_expect(0, account_start_trial_days($value), 'blank or zero means no trial');
foreach ([1, 14, 90, 730] as $value) payment_expect($value, account_start_trial_days((string)$value), 'exact HubSpot trial length');
foreach ([-1, '1.5', 'invalid', 731, true, []] as $value) payment_throws(static fn() => account_start_trial_days($value), 422, 'invalid trials rejected');
$params = account_start_checkout_params($hubspot, $company, $company['email'], 'cus_test');
$centerParams = account_start_checkout_params($hubspot, $company, $company['email'], 'cus_test', 'subscription_center');
payment_expect('https://www.coherta.com/app/subscription_center', $centerParams['success_url'], 'subscription center checkout returns to the same page');
payment_expect($centerParams['success_url'], $centerParams['cancel_url'], 'same safe return destination');
payment_expect('https://coherta.com/app/', $params['success_url'], 'original account-start return is unchanged');
payment_throws(static fn() => account_start_checkout_params($hubspot, $company, $company['email'], 'cus_test', 'https://untrusted.example/'), 422, 'arbitrary return destinations rejected');
$details = revolut_checkout_details($params);
payment_expect(149950, $params['line_items'][0]['price_data']['unit_amount'], 'HubSpot base price remains unchanged');
payment_expect(187438, $details['amount_minor'], 'DKK HubSpot price includes 25%, rounded to the nearest ore');
payment_expect('P3M', $details['plan']['variations'][0]['phases'][0]['cycle_duration'], 'HubSpot quarterly period');
payment_expect('P14D', $details['trial_duration'], 'HubSpot trial transferred to Revolut');
payment_expect(['prod_UWswNDxveWxoUo'], $details['products'], 'preserve existing custom-price product mapping');
foreach (['monthly' => 'P1M', 'quarterly' => 'P3M', 'per_six_months' => 'P6M', 'annually' => 'P1Y'] as $frequency => $period) {
    $hubspot->items[0]['properties']['recurringbillingfrequency'] = $frequency;
    $mapped = revolut_checkout_details(account_start_checkout_params($hubspot, $company, $company['email'], 'cus_test'));
    payment_expect($period, $mapped['plan']['variations'][0]['phases'][0]['cycle_duration'], 'reuse Stripe billing frequency mapping');
}
$hubspot->properties['trial_days'] = '';
payment_expect('P0D', revolut_checkout_details(account_start_checkout_params($hubspot, $company, $company['email'], ''))['trial_duration'], 'no implicit seven-day trial');
$hubspot->properties['stripe_subscription_id'] = 'sub_existing';
payment_throws(static fn() => account_start_checkout_params($hubspot, $company, $company['email'], ''), 409, 'active HubSpot subscription blocks creation');
$hubspot->properties['stripe_subscription_id'] = '';
$hubspot->items = [];
payment_throws(static fn() => account_start_checkout_params($hubspot, $company, $company['email'], ''), 422, 'missing price never falls back to public plan');
$hubspot->items = [['properties' => ['price' => '250', 'quantity' => '2', 'hs_line_item_currency_code' => 'EUR']]];
payment_expect(50000, revolut_checkout_details(account_start_checkout_params($hubspot, $company, $company['email'], ''))['amount_minor'], 'reuse price times quantity fallback');

// Reset only this in-memory fixture database.
foreach (['billing_checkouts', 'billing_subscriptions', 'billing_payments', 'subscription', 'crm_company_stripe'] as $table) $pdo->exec('DELETE FROM ' . $table);
$pdo->exec("UPDATE crm_company SET hubspot_id = '100' WHERE id = 1");
$gateway['stripe_subscriptions'] = [];
$gateway['stripe_sessions'] = [];
$gateway['revolut_subscriptions'] = [];
$gateway['orders'] = [];
$gateway['plans'] = [];
putenv('PAYMENTS_STRIPE_ENABLED=true');
putenv('PAYMENTS_REVOLUT_ENABLED=false');
$calls = count($gateway['calls']);
payment_throws(static fn() => account_start_checkout($pdo, $hubspot, $company), 503, 'no Stripe fallback when Revolut disabled');
payment_expect($calls, count($gateway['calls']), 'disabled Revolut makes no provider requests');
putenv('PAYMENTS_REVOLUT_ENABLED=true');
$hubspot->properties['trial_days'] = '21';
$checkout = account_start_checkout($pdo, $hubspot, $company);
payment_expect('revolut', $checkout['provider'], 'Revolut chosen with both providers enabled');
payment_expect('P21D', $gateway['revolut_subscriptions'][$checkout['subscription']]['trial_duration'], 'subscription receives exact HubSpot trial');
payment_expect(50000, reset($gateway['plans'])['variations'][0]['phases'][0]['amount'], 'plan receives HubSpot amount');
payment_expect(0, count($gateway['stripe_sessions']), 'does not create a Stripe payment');
payment_expect($checkout['id'], account_start_checkout($pdo, $hubspot, $company)['id'], 'repeat clicks reuse same checkout');
payment_expect(1, count($gateway['revolut_subscriptions']), 'only one remote subscription');
payment_expect('pending', account_start_return($pdo, $company, $checkout['checkout_reference'], $hubspot)['state'], 'return URL does not prove payment');
payment_expect([], $hubspot->writes, 'pending subscription never activates HubSpot');
payment_throws(static fn() => account_start_return($pdo, array_merge($company, ['id' => 2]), $checkout['checkout_reference'], $hubspot), 404, 'other company cannot inspect checkout');
payment_throws(static fn() => account_start_return($pdo, $company, 'bad-reference', $hubspot), 422, 'invalid return reference rejected');
$gateway['revolut_subscriptions'][$checkout['subscription']]['state'] = 'active';
$gateway['revolut_subscriptions'][$checkout['subscription']]['trial_end_date'] = gmdate('c', time() + 21 * 86400);
$hubspot->failWrite = true;
try { account_start_return($pdo, $company, $checkout['checkout_reference'], $hubspot); throw new LogicException('Expected failed write'); }
catch (RuntimeException $error) { payment_expect('HubSpot write failed', $error->getMessage(), 'failed activation can be retried'); }
$hubspot->failWrite = false;
payment_expect('active', account_start_return($pdo, $company, $checkout['checkout_reference'], $hubspot)['state'], 'verified activation recovered');
payment_expect($checkout['subscription'], $hubspot->properties['stripe_subscription_id'], 'verified Revolut ID activates existing HubSpot status field');
payment_expect(false, coherta_account_start_required(subscription_center_snapshot($hubspot, $company, 'stripe_subscription_id')), 'modal hidden after activation');
$hubspot->properties['stripe_subscription_id'] = '';
account_start_return($pdo, $company, $checkout['checkout_reference'], $hubspot);
payment_expect('', $hubspot->properties['stripe_subscription_id'], 'later reconciliation does not undo a user stop');
payment_expect(1, count($hubspot->writes), 'activation published once');
payment_throws(static fn() => account_start_checkout($pdo, $hubspot, $company), 409, 'active Revolut billing blocks duplicate even when HubSpot field cleared');
$pdo->exec("INSERT INTO crm_company (id, hubspot_id) VALUES (2, '200')");
$pdo->exec("INSERT INTO crm_company_stripe (company_id, method, stripe_customer_id, stripe_subscription, next_renewal)
    VALUES (2, 'stripe', 'cus_test', 'sub_paid', '2999-10-12 19:19:05'), (2, 'stripe', 'cus_legacy', 'sub_legacy', NULL)");
$secondCompany = array_merge($company, ['id' => 2, 'hubspot_id' => '200']);
$gateway['stripe_subscriptions'] = [['id' => 'sub_paid', 'status' => 'active']];
// Neither old Stripe attempts nor multiple historical Stripe customer IDs may block start.
$legacyAttempt = $pdo->prepare("INSERT INTO billing_checkouts
    (id, company_id, provider, state, fingerprint, request_json, details_json, checkout_id, created_at, updated_at)
    VALUES (?, 2, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())");
foreach (['creating', 'open', 'attention'] as $state) {
    $legacyAttempt->execute(['legacy_stripe_' . $state, 'stripe', $state, 'legacy', '{}', null, 'cs_' . $state]);
}
// A Revolut checkout interrupted before customer creation can still contain Stripe-era data.
$legacyParams = account_start_checkout_params($hubspot, $secondCompany, $secondCompany['email'], 'cus_test', 'subscription_center');
$legacyAttempt->execute(['legacy_revolut_creating', 'revolut', 'creating', 'legacy', json_encode($legacyParams),
    json_encode(revolut_checkout_details($legacyParams)), null]);
$beforeStripeStart = count($gateway['calls']);
$secondStart = account_start_gate_begin($pdo, $hubspot, $secondCompany, 'subscription_center');
$secondCheckout = $secondStart['checkout'];
payment_expect('revolut', $secondCheckout['provider'], 'explicit start opens Revolut even with a paid-through Stripe subscription');
payment_expect(false, isset($secondStart['subscription']), 'explicit start does not return the GET paid-through status');
payment_expect('cancelled', $pdo->query("SELECT state FROM billing_checkouts WHERE id = 'legacy_revolut_creating'")->fetchColumn(), 'legacy Revolut attempt is reconciled before replacement');
foreach (['creating', 'open', 'attention'] as $state) {
    payment_expect($state, $pdo->query("SELECT state FROM billing_checkouts WHERE id = 'legacy_stripe_" . $state . "'")->fetchColumn(), 'start leaves existing Stripe attempts unchanged');
}
payment_expect($secondCheckout['id'], account_start_gate_begin($pdo, $hubspot, $secondCompany, 'subscription_center')['checkout']['id'], 'repeated start reuses the same Revolut subscription alongside Stripe');
$startRequest = json_decode($pdo->query("SELECT request_json FROM billing_checkouts WHERE id = '" . $secondCheckout['checkout_reference'] . "'")->fetchColumn(), true);
payment_expect(false, isset($startRequest['customer']), 'new Revolut request does not carry a Stripe customer');
$legacyAttempt->execute(['revolut_attention', 'revolut', 'attention', 'legacy', '{}', '{}', null]);
payment_throws(static fn() => account_start_checkout($pdo, $hubspot, $secondCompany), 409, 'Revolut reconciliation guard is still enforced');
$pdo->exec("DELETE FROM billing_checkouts WHERE id = 'revolut_attention'");
payment_expect([], array_values(array_filter(array_slice($gateway['calls'], $beforeStripeStart),
    static fn(array $call): bool => $call[0] === 'stripe')), 'creation, legacy recovery, retries and reconciliation make zero Stripe API calls');
$hubspot->failRead = true;
$before = count($gateway['calls']);
try { account_start_checkout($pdo, $hubspot, $secondCompany); throw new LogicException('Expected failed read'); }
catch (RuntimeException $error) { payment_expect('HubSpot unavailable', $error->getMessage(), 'HubSpot outage blocks checkout'); }
payment_expect($before, count($gateway['calls']), 'no provider calls after HubSpot outage');
$hubspot->failRead = false;
payment_expect($secondCheckout['id'], account_start_checkout($pdo, $hubspot, $secondCompany, 'subscription_center')['id'], 'HubSpot recovery reuses the existing checkout');
$hubspot->items[0]['properties']['price'] = '300';
$changedCheckout = account_start_checkout($pdo, $hubspot, $secondCompany, 'subscription_center');
payment_expect('https://www.coherta.com/app/subscription_center?payment_provider=revolut&checkout_reference=' . $changedCheckout['checkout_reference'],
    $gateway['revolut_subscriptions'][$changedCheckout['subscription']]['setup_order_redirect_url'], 'Revolut gets the subscription center return URL and verification reference');
payment_expect($changedCheckout['id'], account_start_checkout($pdo, $hubspot, $secondCompany, 'subscription_center')['id'], 'subscription center retries reuse checkout');
payment_expect('cancelled', $gateway['revolut_subscriptions'][$secondCheckout['subscription']]['state'], 'price change cancels old unpaid checkout');
payment_expect(60000, end($gateway['plans'])['variations'][0]['phases'][0]['amount'], 'price change uses fresh HubSpot amount');
$gateway['revolut_subscriptions'][$changedCheckout['subscription']]['state'] = 'active';
$gateway['revolut_subscriptions'][$changedCheckout['subscription']]['trial_end_date'] = gmdate('c', time() + 86400);
$hubspot->properties['stripe_subscription_id'] = 'different_subscription';
payment_throws(static fn() => account_start_return($pdo, $secondCompany, $changedCheckout['checkout_reference'], $hubspot), 409, 'activation never overwrites another HubSpot subscription');
payment_expect('different_subscription', $hubspot->properties['stripe_subscription_id'], 'conflicting HubSpot value preserved');
payment_expect([], array_values(array_filter(array_slice($gateway['calls'], $beforeStripeStart),
    static fn(array $call): bool => $call[0] === 'stripe')), 'price replacement and activation checks also avoid Stripe');
fwrite(STDOUT, 'PASS: ' . ($checks - $startChecks) . ' account-start assertions.' . PHP_EOL);
