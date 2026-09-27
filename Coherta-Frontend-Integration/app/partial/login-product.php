<?php
declare(strict_types=1);

function coherta_login_product(array $query, array $post, string $redirect = ''): string
{
    $choice = $post['login_product'] ?? $query['product'] ?? '';
    if (is_string($choice) && in_array($choice, ['campaigns', 'introductions'], true)) return $choice;
    $path = parse_url($redirect, PHP_URL_PATH);
    if (in_array($path, ['/app/sales_insider', '/app/sales_insider.php', '/app/introduction-onboarding', '/app/introduction-onboarding.php'], true)) return 'introductions';
    return $redirect !== '' ? 'campaigns' : '';
}

function coherta_login_product_destination(string $product): string
{
    return $product === 'introductions' ? '/app/introduction-onboarding?resume=1' : '/app/campaign';
}
