<?php
/** Account and product information has already been resolved by the company API. */
function coherta_company_has_product(array $company, string $productId, int $pluginId): bool
{
    $active = ($company['has_active_subscription'] ?? false) === true
        || ($company['trial_status'] ?? '') === 'active';
    if (!$active) return false;
    if (in_array($productId, $company['billing_products'] ?? [], true)) return true;
    foreach ($company['plugins'] ?? [] as $plugin) {
        if ((int)($plugin['plugin_id'] ?? 0) === $pluginId && !empty($plugin['installed'])) return true;
    }
    return false;
}
