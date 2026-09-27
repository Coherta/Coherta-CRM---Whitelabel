<?php
declare(strict_types=1);
// In-memory database and fake gateways only. The shared fake throws on every Stripe call.
$apiRoot = getenv('COHERTA_PAYMENT_TEST_API_ROOT') ?: dirname(__DIR__, 3) . '/api-crm/php';
require $apiRoot . '/tests/payment_providers_test.php';
require_once $apiRoot . '/lib/frontend_billing.php';
require_once __DIR__ . '/../partial/billing-access.php';
$initialChecks = $checks;
final class FrontendBillingHubSpotFake extends HubSpotClient
{
    public array $properties = ['stripe_subscription_id' => '', 'trial_days' => '14', 'dealname' => 'Current plan'];
    public function __construct() {}
    public function getDeal(string $id, array $properties): array { return ['id' => $id, 'properties' => $this->properties]; }
    public function getDealLineItems(string $id): array { return [['properties' => ['amount' => '100', 'hs_line_item_currency_code' => 'DKK', 'recurringbillingfrequency' => 'monthly']]]; }
    public function updateDeal(string $id, array $properties): array { $this->properties = array_replace($this->properties, $properties); return ['id' => $id, 'properties' => $this->properties]; }
}
$reset = static function () use ($pdo, &$gateway): void {
    foreach (['billing_checkouts', 'billing_subscriptions', 'billing_payments', 'subscription', 'crm_company_stripe'] as $table) $pdo->exec('DELETE FROM ' . $table);
    $gateway['revolut_subscriptions'] = $gateway['plans'] = $gateway['orders'] = $gateway['calls'] = [];
    unset($gateway['revolut_override']);
};
$reset();
$pdo->exec("CREATE TABLE plugins_price (plugin_id INTEGER, currency INTEGER, price NUMERIC);
    INSERT INTO plugins_price VALUES (3, 208, 20), (12, 208, 10), (14, 208, 5), (5, 208, 15);
    UPDATE crm_company SET hubspot_id = '100' WHERE id = 1");
$company = ['id' => 1, 'company_name' => 'Fixture', 'hubspot_id' => '100', 'email' => 'test@example.com'];
$hubspot = new FrontendBillingHubSpotFake();
$pdo->exec("INSERT INTO crm_company_stripe (company_id, stripe_subscription, next_renewal, last_state) VALUES (1, 'sub_trial', '2099-01-01', 'local_trial.started')");
payment_expect(false, local_company_billing($pdo, 1)['active'], 'local trial marker is not paid access');
$pdo->exec("DELETE FROM crm_company_stripe; INSERT INTO crm_company_stripe (company_id, method) VALUES (1, 'bankwire')");
payment_expect(true, local_company_billing($pdo, 1)['active'], 'saved bank-transfer access is preserved');
$pdo->exec('DELETE FROM crm_company_stripe');
payment_throws(static fn() => frontend_billing_quote($pdo, $hubspot, array_replace($company, ['hubspot_id' => '']), 'startups'), 409, 'Founders waits for its account billing record');
payment_expect(['prod_Tj6gIhPsQhHIq9', 'prod_Tj6iHmP3XyIEdS'], local_billing_products('["CRM & Connect",3,12]'), 'saved product aliases are normalised');
$quote = frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'Tj6iHmP3XyIEdS');
payment_expect(15000, $quote['amount_minor'], 'new total includes saved HubSpot base, saved addon, and DKK policy once');
payment_expect(14, $quote['trial_days'], 'new upgrade uses HubSpot trial');
payment_expect(false, isset(frontend_billing_public_quote($quote)['params']), 'private checkout parameters stay server-side');
payment_throws(static fn() => frontend_billing_begin($pdo, $hubspot, $company, ['flow' => 'upgrade', 'product' => 'Tj6iHmP3XyIEdS', 'quote_id' => 'tampered']), 409, 'stale or tampered quote rejected');
payment_expect([], $gateway['calls'], 'reading quotes makes no provider requests');
payment_throws(static fn() => frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'unknown'), 422, 'unknown products rejected');
$renewal = date('Y-m-d H:i:s', time() + 10 * 86400 + 3600);
$pdo->prepare("INSERT INTO crm_company_stripe (company_id, stripe_subscription, next_renewal) VALUES (1, 'sub_legacy', ?)")->execute([$renewal]);
$hubspot->properties['stripe_subscription_id'] = 'sub_legacy';
$legacy = frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'Tj6iHmP3XyIEdS');
payment_expect(11, $legacy['trial_days'], 'migration waits until saved renewal, rounded up');
payment_expect('sub_legacy', $legacy['transfer_id'], 'migration binds exact historical subscription');
payment_throws(static fn() => frontend_billing_quote($pdo, $hubspot, $company, 'startups'), 409, 'Founders does not duplicate existing legacy subscription');
$reset(); $hubspot->properties['stripe_subscription_id'] = '';
$founder = frontend_billing_quote($pdo, $hubspot, $company, 'startups');
payment_expect(179900, $founder['amount_minor'], 'Founders annual amount');
payment_expect('USD', $founder['currency'], 'Founders currency');
payment_expect('P1Y', $founder['duration'], 'Founders annual billing');
payment_expect(7, $founder['trial_days'], 'Founders trial remains seven days');
$request = ['flow' => 'startups', 'quote_id' => $founder['quote_id'], 'amount_minor' => 1, 'trial_days' => 730];
$gateway['lose_response'] = true;
payment_throws(static fn() => frontend_billing_begin($pdo, $hubspot, $company, $request), 502, 'lost creation response is recoverable');
$checkout = frontend_billing_begin($pdo, $hubspot, $company, $request)['checkout'];
payment_expect(1, count($gateway['revolut_subscriptions']), 'retry reuses subscription after a lost response');
payment_expect($checkout['checkout_reference'], frontend_billing_begin($pdo, $hubspot, $company, $request)['checkout']['checkout_reference'], 'repeat checkout reuses reference');
payment_expect('P7D', $gateway['revolut_subscriptions'][$checkout['subscription']]['trial_duration'], 'browser cannot override trial');
payment_expect(false, frontend_billing_status($pdo, $hubspot, $company, 'startups', $checkout['checkout_reference'])['paid'], 'pending checkout does not allow booking');
payment_throws(static fn() => frontend_billing_status($pdo, $hubspot, ['id' => 2], 'startups', $checkout['checkout_reference']), 404, 'another company cannot verify checkout');
payment_throws(static fn() => frontend_billing_status($pdo, $hubspot, $company, 'upgrade', $checkout['checkout_reference']), 404, 'return must match the flow');
payment_throws(static fn() => frontend_billing_status($pdo, $hubspot, $company, 'startups', 'cs_legacy'), 422, 'old checkout references cannot grant access');
$gateway['revolut_subscriptions'][$checkout['subscription']]['state'] = 'active';
$gateway['revolut_subscriptions'][$checkout['subscription']]['trial_end_date'] = gmdate('c', time() + 7 * 86400);
payment_expect(true, frontend_billing_status($pdo, $hubspot, $company, 'startups', $checkout['checkout_reference'])['paid'], 'verified Revolut trial permits booking');
payment_expect(true, in_array('prod_Tj6iHmP3XyIEdS', local_company_billing($pdo, 1)['products'], true), 'Extended trial grants website entitlement');
payment_throws(static fn() => frontend_billing_quote($pdo, $hubspot, $company, 'startups'), 409, 'active Revolut subscription cannot start Founders again');
$gateway['revolut_subscriptions'][$checkout['subscription']]['state'] = 'cancelled';
payment_expect(false, frontend_billing_status($pdo, $hubspot, $company, 'startups', $checkout['checkout_reference'])['paid'], 'cancelled trial cannot book');

$reset(); $hubspot->properties['stripe_subscription_id'] = '';
$quote = frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'Tj6iHmP3XyIEdS');
$checkout = frontend_billing_begin($pdo, $hubspot, $company, ['flow' => 'upgrade', 'product' => 'Tj6iHmP3XyIEdS', 'quote_id' => $quote['quote_id']])['checkout'];
$gateway['revolut_subscriptions'][$checkout['subscription']]['state'] = 'active';
$gateway['revolut_subscriptions'][$checkout['subscription']]['current_cycle_id'] = 'cycle-1';
frontend_billing_status($pdo, $hubspot, $company, 'upgrade', $checkout['checkout_reference']);
payment_expect('included', frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'Tj6iHmP3XyIEdS')['mode'], 'already owned product is not charged again');
$quote = frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'Tj6gfRnDznSezk');
payment_expect('change', $quote['mode'], 'active Revolut subscription uses change-plan');
payment_expect(15625, $quote['amount_minor'], 'existing gross amount is not taxed twice');
$saved = json_decode(payment_company_subscription($pdo, 1)['details_json'], true);
$admin = $saved;
$admin['admin_plan']['phases'] = $admin['plan']['variations'][0]['phases'];
unset($admin['plan']);
$pdo->prepare('UPDATE billing_subscriptions SET details_json = ? WHERE company_id = 1')->execute([json_encode($admin)]);
payment_expect(15625, frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'Tj6gfRnDznSezk')['amount_minor'], 'existing admin-created Revolut plans support upgrades');
$pdo->prepare('UPDATE billing_subscriptions SET details_json = ? WHERE company_id = 1')->execute([json_encode($saved)]);
$changes = 0; $target = '';
$gateway['revolut_override'] = static function ($method, $path, $params) use (&$changes, &$target): ?array {
    if ($method === 'POST' && str_ends_with($path, '/change-plan')) {
        payment_expect('at_cycle_end', $params['scheduled'], 'plan takes effect next cycle');
        $target = $params['plan_variation_id']; $changes++;
        return [];
    }
    return null;
};
$request = ['flow' => 'upgrade', 'product' => 'Tj6gfRnDznSezk', 'quote_id' => $quote['quote_id']];
payment_expect(true, frontend_billing_begin($pdo, $hubspot, $company, $request)['scheduled'], 'upgrade scheduled');
payment_expect(false, in_array('prod_Tj6gfRnDznSezk', local_company_billing($pdo, 1)['products'], true), 'scheduled product remains locked before next cycle');
frontend_billing_begin($pdo, $hubspot, $company, $request);
payment_expect(1, $changes, 'retry does not reschedule a confirmed change');
$other = frontend_billing_quote($pdo, $hubspot, $company, 'upgrade', 'Tj6iuxGEvOIWQv');
payment_throws(static fn() => frontend_billing_begin($pdo, $hubspot, $company, ['flow' => 'upgrade', 'product' => 'Tj6iuxGEvOIWQv', 'quote_id' => $other['quote_id']]), 409, 'conflicting scheduled change rejected');
$gateway['revolut_subscriptions'][$checkout['subscription']]['plan_variation_id'] = $target;
frontend_billing_status($pdo, $hubspot, $company, 'upgrade', $checkout['checkout_reference']);
payment_expect(true, in_array('prod_Tj6gfRnDznSezk', local_company_billing($pdo, 1)['products'], true), 'product unlocks after confirmed plan change');
payment_expect(15625, (int)payment_company_subscription($pdo, 1)['amount_minor'], 'new price applied after confirmed change');
payment_expect(false, coherta_company_has_product(['has_active_subscription' => false, 'billing_products' => ['prod_Tj6gfRnDznSezk']], 'prod_Tj6gfRnDznSezk', 14), 'inactive saved products grant no access');
payment_expect(true, coherta_company_has_product(['has_active_subscription' => true, 'billing_products' => ['prod_Tj6gfRnDznSezk']], 'prod_Tj6gfRnDznSezk', 14), 'active saved products grant access without Stripe');
echo 'PASS: ' . ($checks - $initialChecks) . " frontend billing assertions.\n";
