<?php
declare(strict_types=1);

if (($argv[1] ?? '') === '--config') {
    $source = file_get_contents(dirname(__DIR__, 2) . '/setup-funnel.php');
    foreach (['query_value', 'normalize_account_type', 'normalize_plan', 'normalize_billing'] as $name) {
        if (!preg_match('/function coherta_setup_' . $name . '\([^\n]*\n\{.*?\n\}/s', $source, $match)) {
            throw new RuntimeException('Missing setup function: ' . $name);
        }
        eval($match[0]);
    }
    $start = strpos($source, '$talentAttractEnabled =');
    $end = strpos($source, '$setupGoogleReturnParams = []', $start);
    $selection = substr($source, $start, $end - $start);
    $configs = [];
    foreach (json_decode(file_get_contents('php://stdin'), true, 512, JSON_THROW_ON_ERROR) as $_GET) {
        eval($selection);
        $configs[] = ['selectedPlan' => $requestedPlan, 'selectedBilling' => $requestedBilling];
    }
    echo json_encode($configs, JSON_THROW_ON_ERROR);
    exit;
}

require_once dirname(__DIR__, 4) . '/api-crm/php/lib/hubspot_signup.php';

class SetupHubspotStatement extends PDOStatement
{
    public function execute(?array $params = null): bool { return true; }
}

class SetupHubspotPdo extends PDO
{
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new SetupHubspotStatement();
    }
}

class SetupHubspotClient extends HubSpotClient
{
    public array $deal = [];
    public array $lineItem = [];
    public function __construct() {}
    public function searchProductByProperty(string $property, string $value, array $properties = ['name', 'price', 'hs_sku']): ?array
    {
        // Existing catalogue IDs and DKK prices, verified with a read-only lookup.
        $products = [
            'Coherta Small Campaigns' => ['id' => '427467262189', 'price' => '999'],
            'Coherta Extended Campaigns' => ['id' => '427918554344', 'price' => '4219'],
        ];
        if ($property !== 'name' || !isset($products[$value])) {
            throw new RuntimeException('Unexpected HubSpot product lookup.');
        }
        $product = $products[$value];
        return ['id' => $product['id'], 'properties' => ['name' => $value,
            'price' => $product['price'], 'hs_price_dkk' => $product['price']]];
    }
    public function createDeal(array $props): array { $this->deal = $props; return ['id' => 'fixture_deal']; }
    public function createLineItem(array $props): array { $this->lineItem = $props; return ['id' => 'fixture_line']; }
    public function associateDefault(string $fromType, string $fromId, string $toType, string $toId): array { return []; }
}

$results = [];
foreach (json_decode(file_get_contents('php://stdin'), true, 512, JSON_THROW_ON_ERROR) as $payload) {
    $client = new SetupHubspotClient();
    hubspot_create_signup_deal(new SetupHubspotPdo(), 1, 'fixture_company', 'fixture_contact', $payload, $client);
    $results[] = ['deal' => $client->deal, 'lineItem' => $client->lineItem];
}
echo json_encode($results, JSON_THROW_ON_ERROR);
