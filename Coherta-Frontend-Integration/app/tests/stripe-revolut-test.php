<?php
declare(strict_types=1);
// Isolated SQLite and fake gateways only. No real account or payment is changed.
$apiRoot = getenv('COHERTA_PAYMENT_TEST_API_ROOT') ?: dirname(__DIR__, 3) . '/api-crm/php';
require $apiRoot . '/tests/payment_providers_test.php';
require_once $apiRoot . '/lib/stripe_revolut.php';

final class StripeRevolutHubSpotFake extends HubSpotClient
{
    public array $properties = ['stripe_subscription_id' => 'sub_source', 'trial_days' => '90', 'dealname' => 'Existing plan'];
    public array $writes = [];
    public function __construct() {}
    public function getDeal(string $id, array $properties): array { return ['id' => $id, 'properties' => $this->properties]; }
    public function getDealLineItems(string $id): array
    {
        return [['properties' => ['amount' => '100', 'hs_line_item_currency_code' => 'DKK', 'recurringbillingfrequency' => 'monthly']]];
    }
    public function updateDeal(string $id, array $properties): array
    {
        $this->writes[] = $properties;
        $this->properties = array_replace($this->properties, $properties);
        return ['id' => $id, 'properties' => $this->properties];
    }
}
$initialChecks = $checks;
foreach (['billing_checkouts', 'billing_subscriptions', 'billing_payments', 'subscription', 'crm_company_stripe'] as $table) $pdo->exec('DELETE FROM ' . $table);
foreach (['method', 'next_renewal', 'created', 'last_updated', 'last_state'] as $column) {
    if (!in_array($column, array_column($pdo->query('PRAGMA table_info(crm_company_stripe)')->fetchAll(PDO::FETCH_ASSOC), 'name'), true)) {
        $pdo->exec('ALTER TABLE crm_company_stripe ADD COLUMN ' . $column . ' TEXT');
    }
}
$pdo->exec("INSERT INTO crm_company_stripe (company_id, method, stripe_customer_id, stripe_subscription) VALUES (1, 'stripe', 'cus_test', 'sub_source')");
$pdo->exec("UPDATE crm_company SET hubspot_id = '100' WHERE id = 1");
$gateway['stripe_sessions'] = $gateway['revolut_subscriptions'] = $gateway['orders'] = $gateway['plans'] = [];
// Conflicting remote data must have no effect: Stripe is deprecated.
$gateway['stripe_subscriptions'] = [['id' => 'sub_unrelated', 'customer' => 'cus_other', 'status' => 'active']];
$gateway['calls'] = [];
$renewalAt = time() + 9 * 86400 + 43200;
$setRenewal = static function ($value) use ($pdo): void {
    $pdo->prepare('UPDATE crm_company_stripe SET next_renewal = ? WHERE company_id = 1')->execute([$value]);
};
$setRenewal(date('Y-m-d H:i:s', $renewalAt));
$hubspot = new StripeRevolutHubSpotFake();
$company = ['id' => 1, 'hubspot_id' => '100', 'company_name' => 'Fixture', 'email' => 'test@example.com'];
$overview = ['subscription_id' => 'sub_source'];
$source = stripe_revolut_source($pdo, 1, 'sub_source');
payment_expect($renewalAt, $source['renewal_at'], 'renewal comes from the database');
payment_expect(10, $source['remaining_trial_days'], 'partial days round up');
foreach ([9 * 86400 => 9, 1 => 1, 0 => 0, -86400 => 0] as $remaining => $days) {
    payment_expect($days, stripe_revolut_source($pdo, 1, 'sub_source', $renewalAt - $remaining)['remaining_trial_days'], 'remaining renewal time: ' . $remaining);
}
payment_throws(static fn() => stripe_revolut_source($pdo, 1, 'sub_source', $renewalAt - 731 * 86400), 422, 'unsupported trial duration is rejected');
payment_expect(strtotime('2026-10-01 00:00:00'), stripe_revolut_renewal_at('2026-10-01'), 'date-only renewal supported');
foreach ([null, '', 'not-a-date', '0000-00-00 00:00:00', '2026-02-30', '2026-01-01 25:00:00', true, 0] as $invalid) {
    payment_throws(static fn() => stripe_revolut_renewal_at($invalid), 409, 'invalid renewal never implies zero trial days');
}
$setRenewal(null);
payment_expect(['eligible' => false, 'unavailable' => true], stripe_revolut_offer($pdo, $company, $overview), 'missing renewal keeps an unavailable upgrade offer');
payment_throws(static fn() => stripe_revolut_checkout($pdo, $hubspot, $company), 409, 'missing renewal blocks creation without calling Stripe');
$setRenewal(date('Y-m-d H:i:s', $renewalAt));
payment_throws(static fn() => stripe_revolut_source($pdo, 2, 'sub_source'), 409, 'another company cannot use the renewal');
payment_throws(static fn() => stripe_revolut_source($pdo, 1, 'sub_other'), 409, 'a different subscription cannot borrow the renewal');
foreach ([null, '', '   '] as $clearedId) {
    $pdo->prepare('UPDATE crm_company_stripe SET stripe_subscription = ? WHERE company_id = 1')->execute([$clearedId]);
    payment_expect(10, stripe_revolut_source($pdo, 1, 'sub_source')['remaining_trial_days'], 'a previously cleared local ID can use its saved company renewal');
}
$pdo->exec("UPDATE crm_company_stripe SET stripe_subscription = 'sub_source' WHERE company_id = 1");
$pdo->exec("INSERT INTO crm_company_stripe (company_id, stripe_subscription, next_renewal) VALUES (1, 'sub_source', '2026-01-01')");
payment_throws(static fn() => stripe_revolut_source($pdo, 1, 'sub_source'), 409, 'conflicting saved renewal dates need reconciliation');
$pdo->exec("DELETE FROM crm_company_stripe WHERE next_renewal = '2026-01-01'");
foreach ([null, '', '   ', 'prod_old', 'rev_sub_1', 'prefix_sub_source'] as $id) {
    payment_expect(null, stripe_revolut_offer($pdo, $company, ['subscription_id' => $id]), 'only a sub_ ID offers upgrade');
}
foreach (['stripe', 'revolut', 'bankwire', null] as $method) {
    $pdo->prepare('UPDATE crm_company_stripe SET method = ?, stripe_customer_id = NULL')->execute([$method]);
    payment_expect(true, stripe_revolut_offer($pdo, $company, $overview)['eligible'], 'ID prefix controls upgrade regardless of historical method/customer');
}
$offer = stripe_revolut_offer($pdo, $company, $overview);
payment_expect(false, isset($offer['customer_id']), 'offer does not expose historical customer data');
payment_expect(false, stripe_revolut_offer($pdo, ['id' => 1, 'hubspot_id' => ''], $overview)['eligible'], 'a billing plan is required');
$params = account_start_checkout_params($hubspot, $company, $company['email'], '', 'subscription_center', $source);
payment_expect(10, $params['subscription_data']['trial_period_days'], 'saved renewal overrides the 90-day HubSpot offer');
payment_expect(12500, revolut_checkout_details($params)['amount_minor'], 'HubSpot price and DKK pricing policy are retained');
payment_expect(false, isset($params['customer']), 'upgrade needs no Stripe customer');
$hubspot->properties['stripe_subscription_id'] = 'sub_changed';
payment_throws(static fn() => account_start_checkout_params($hubspot, $company, $company['email'], '', 'subscription_center', $source), 409, 'concurrent HubSpot changes cannot be overwritten');
$hubspot->properties['stripe_subscription_id'] = 'sub_source';

// Historical Stripe checkout records, even unresolved ones, do not block Revolut.
$insertOld = $pdo->prepare("INSERT INTO billing_checkouts (id, company_id, provider, state, fingerprint, request_json, created_at, updated_at)
    VALUES (?, 1, 'stripe', ?, 'legacy', '{}', UTC_TIMESTAMP(), UTC_TIMESTAMP())");
foreach (['creating', 'open', 'attention'] as $state) $insertOld->execute(['legacy_' . $state, $state]);
putenv('PAYMENTS_STRIPE_ENABLED=true');
putenv('PAYMENTS_REVOLUT_ENABLED=false');
payment_throws(static fn() => stripe_revolut_checkout($pdo, $hubspot, $company), 503, 'disabled Revolut never falls back to Stripe');
putenv('PAYMENTS_REVOLUT_ENABLED=true');
$checkout = stripe_revolut_checkout($pdo, $hubspot, $company);
payment_expect('revolut', $checkout['provider'], 'upgrade creates Revolut');
payment_expect('P10D', $gateway['revolut_subscriptions'][$checkout['subscription']]['trial_duration'], 'created subscription waits for the saved renewal');
payment_expect($checkout['id'], stripe_revolut_checkout($pdo, $hubspot, $company)['id'], 'retries reuse the pending checkout');
payment_expect(1, count($gateway['revolut_subscriptions']), 'retries do not create duplicate subscriptions');
payment_expect([], $hubspot->writes, 'opening checkout does not activate HubSpot');
payment_expect('pending', account_start_return($pdo, $company, $checkout['checkout_reference'], $hubspot)['state'], 'pending checkout does not activate the account');
payment_throws(static fn() => account_start_return($pdo, ['id' => 2], $checkout['checkout_reference'], $hubspot), 404, 'checkout returns enforce account ownership');
$setRenewal(date('Y-m-d H:i:s', $renewalAt - 86400));
$shorter = stripe_revolut_checkout($pdo, $hubspot, $company);
payment_expect('P9D', $gateway['revolut_subscriptions'][$shorter['subscription']]['trial_duration'], 'changed database renewal replaces an unpaid checkout');
payment_expect('cancelled', $gateway['revolut_subscriptions'][$checkout['subscription']]['state'], 'outdated unpaid checkout is cancelled');
$setRenewal(date('Y-m-d H:i:s', time() - 86400));
$zero = stripe_revolut_checkout($pdo, $hubspot, $company);
payment_expect('P0D', $gateway['revolut_subscriptions'][$zero['subscription']]['trial_duration'], 'expired renewal charges immediately');
payment_expect('cancelled', $gateway['revolut_subscriptions'][$shorter['subscription']]['state'], 'expired renewal replaces the old trial');
$gateway['revolut_subscriptions'][$zero['subscription']]['state'] = 'active';
$gateway['revolut_subscriptions'][$zero['subscription']]['trial_end_date'] = gmdate('c', time() + 86400);
$hubspot->properties['stripe_subscription_id'] = 'changed_by_someone_else';
payment_throws(static fn() => account_start_return($pdo, $company, $zero['checkout_reference'], $hubspot), 409, 'activation refuses a changed HubSpot marker');
$hubspot->properties['stripe_subscription_id'] = 'sub_source';
payment_expect('active', account_start_return($pdo, $company, $zero['checkout_reference'], $hubspot)['state'], 'Revolut activation is verified');
payment_expect($zero['subscription'], $hubspot->properties['stripe_subscription_id'], 'activation replaces the original legacy marker');
$hubspot->properties['stripe_subscription_id'] = 'sub_source';
payment_throws(static fn() => stripe_revolut_checkout($pdo, $hubspot, $company), 409, 'active Revolut subscription still blocks duplicate billing');
foreach (['creating', 'open', 'attention'] as $state) {
    payment_expect($state, $pdo->query("SELECT state FROM billing_checkouts WHERE id = 'legacy_" . $state . "'")->fetchColumn(), 'historical Stripe checkout is untouched');
}

// Empty HubSpot IDs use HubSpot trial_days, even with a historical sub_ row and future renewal.
foreach ([null, '', '   '] as $id) {
    foreach (['billing_checkouts', 'billing_subscriptions', 'billing_payments'] as $table) $pdo->exec('DELETE FROM ' . $table);
    $setRenewal(date('Y-m-d H:i:s', $renewalAt));
    $hubspot->properties['stripe_subscription_id'] = $id;
    $hubspot->properties['trial_days'] = '14';
    payment_throws(static fn() => stripe_revolut_checkout($pdo, $hubspot, $company), 409, 'empty ID cannot take the upgrade path');
    $start = account_start_checkout($pdo, $hubspot, $company, 'subscription_center');
    payment_expect('P14D', $gateway['revolut_subscriptions'][$start['subscription']]['trial_duration'], 'empty ID uses HubSpot trial instead of the saved renewal');
    payment_expect($start['id'], account_start_checkout($pdo, $hubspot, $company, 'subscription_center')['id'], 'start retries reuse checkout');
}
payment_expect('sub_source', $pdo->query('SELECT stripe_subscription FROM crm_company_stripe WHERE company_id = 1')->fetchColumn(), 'stored legacy ID is preserved');
// Regression: the old loader erased the database ID but left the HubSpot ID and renewal.
foreach (['billing_checkouts', 'billing_subscriptions', 'billing_payments'] as $table) $pdo->exec('DELETE FROM ' . $table);
$pdo->exec('UPDATE crm_company_stripe SET stripe_subscription = NULL WHERE company_id = 1');
$hubspot->properties['stripe_subscription_id'] = 'sub_source';
$recovered = stripe_revolut_checkout($pdo, $hubspot, $company);
payment_expect('P10D', $gateway['revolut_subscriptions'][$recovered['subscription']]['trial_duration'], 'upgrade after a cleared local ID still uses the saved renewal');
payment_expect($recovered['id'], stripe_revolut_checkout($pdo, $hubspot, $company)['id'], 'cleared local ID upgrade retries reuse the same checkout');
payment_expect(null, $pdo->query('SELECT stripe_subscription FROM crm_company_stripe WHERE company_id = 1')->fetchColumn(), 'upgrade does not fabricate a local legacy ID');
payment_expect([], array_values(array_filter($gateway['calls'], static fn(array $call): bool => $call[0] === 'stripe')),
    'overview, upgrade, start, retries, replacement and activation make zero Stripe requests');
fwrite(STDOUT, 'PASS: ' . ($checks - $initialChecks) . ' database-renewal/Revolut assertions.' . PHP_EOL);
