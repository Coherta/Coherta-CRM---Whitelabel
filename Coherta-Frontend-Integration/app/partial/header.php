<?php
global $user, $user_image, $meta_description, $payment_information_required, $payment_information_url;
$uri = $_SERVER['REQUEST_URI'] ?? '/app/';
if (empty($publicPage)) {
    $user = !empty($_SESSION['token']) ? $client->getCurrentUser() : null;
    if (!$user) {
        // Keep the requested page ahead of product-specific landing pages after login.
        $_SESSION['portal_login_return_url'] = $uri;
        header('Cache-Control: no-store');
        header('Location: /app/login?url=' . rawurlencode($uri));
        exit;
    }
    if (!empty($user['img_key'])) {
        $user_image = 'https://api.coherta.com/images/' . $user['img_key'];
    }
}

$payment_information_required = false;
$payment_information_url = "/app/my-account";
$payment_information_title = "Start my account";
$payment_information_copy = "Start my account to use Coherta.";
$payment_information_button_label = "Start my account";
if (!function_exists('coherta_payment_info_first_string')) {
    function coherta_payment_info_first_string($source, array $keys): string
    {
        if (!is_array($source)) {
            return "";
        }
        foreach ($keys as $key) {
            if (isset($source[$key]) && is_scalar($source[$key])) {
                $value = trim((string)$source[$key]);
                if ($value !== "") {
                    return $value;
                }
            }
        }
        return "";
    }
}

if (!function_exists('coherta_payment_info_is_url')) {
    function coherta_payment_info_is_url(string $value): bool
    {
        $value = trim($value);
        return $value !== "" && (preg_match('/^https?:\/\//i', $value) || str_starts_with($value, '/'));
    }
}

if (!function_exists('coherta_payment_info_find_checkout_url')) {
    function coherta_payment_info_find_checkout_url($source, int $depth = 0, bool $allowGenericUrl = false): string
    {
        if (!is_array($source) || $depth > 5) {
            return "";
        }
        foreach (["stripe_checkout_url", "checkout_url", "billing_url", "payment_url", "payment_link", "payment_plan_url", "stripe_url"] as $key) {
            if (!array_key_exists($key, $source)) {
                continue;
            }
            $value = $source[$key];
            if (is_scalar($value) && coherta_payment_info_is_url((string)$value)) {
                return trim((string)$value);
            }
            if (is_array($value)) {
                $nested = coherta_payment_info_find_checkout_url($value, $depth + 1, true);
                if ($nested !== "") {
                    return $nested;
                }
            }
        }
        if ($allowGenericUrl && isset($source["url"]) && is_scalar($source["url"]) && coherta_payment_info_is_url((string)$source["url"])) {
            return trim((string)$source["url"]);
        }
        foreach (["stripe_checkout", "checkout", "billing", "payment", "payment_plan"] as $key) {
            if (isset($source[$key]) && is_array($source[$key])) {
                $nested = coherta_payment_info_find_checkout_url($source[$key], $depth + 1, true);
                if ($nested !== "") {
                    return $nested;
                }
            }
        }
        foreach ($source as $value) {
            if (is_array($value)) {
                $nested = coherta_payment_info_find_checkout_url($value, $depth + 1, false);
                if ($nested !== "") {
                    return $nested;
                }
            }
        }
        return "";
    }
}

if (!function_exists('coherta_payment_info_collect_plan_sources')) {
    function coherta_payment_info_collect_plan_sources($source, int $depth = 0): array
    {
        if (!is_array($source) || $depth > 4) {
            return [];
        }
        $planKeys = ["partner_payment_plan", "partner_plan", "payment_plan", "billing_plan", "subscription_plan", "subscription", "stripe_checkout", "checkout", "billing", "partner", "plan"];
        $signalKeys = ["stripe_price_id", "price_id", "stripe_product_id", "product_id", "monthly_payment", "monthly_price", "amount", "pricing", "currency", "products"];
        $sources = [];
        foreach (array_merge($planKeys, $signalKeys) as $key) {
            if (array_key_exists($key, $source)) {
                $sources[] = $source;
                break;
            }
        }
        foreach ($planKeys as $key) {
            if (!array_key_exists($key, $source)) {
                continue;
            }
            $value = $source[$key];
            if (is_array($value)) {
                $sources[] = $value;
                $sources = array_merge($sources, coherta_payment_info_collect_plan_sources($value, $depth + 1));
            } elseif (is_scalar($value) && trim((string)$value) !== "") {
                $sources[] = [$key => trim((string)$value)];
            }
        }
        foreach ($source as $value) {
            if (is_array($value)) {
                $sources = array_merge($sources, coherta_payment_info_collect_plan_sources($value, $depth + 1));
            }
        }
        return $sources;
    }
}

if (!function_exists('coherta_payment_info_find_matching_string')) {
    function coherta_payment_info_find_matching_string($source, array $keys, callable $matches, int $depth = 0): string
    {
        if (!is_array($source) || $depth > 4) {
            return "";
        }
        foreach ($keys as $key) {
            if (isset($source[$key]) && is_scalar($source[$key])) {
                $value = trim((string)$source[$key]);
                if ($value !== "" && $matches($value)) {
                    return $value;
                }
            }
        }
        foreach ($source as $value) {
            if (is_array($value)) {
                $matched = coherta_payment_info_find_matching_string($value, $keys, $matches, $depth + 1);
                if ($matched !== "") {
                    return $matched;
                }
            }
        }
        return "";
    }
}

if (!function_exists('coherta_payment_info_amount')) {
    function coherta_payment_info_amount($source): float
    {
        if (!is_array($source)) {
            return 0;
        }
        foreach (["monthly_payment", "monthly_price", "pricepoint", "price_point", "amount", "pricing", "price"] as $key) {
            if (!isset($source[$key]) || !is_scalar($source[$key])) {
                continue;
            }
            $value = trim((string)$source[$key]);
            if ($value === "") {
                continue;
            }
            if (is_numeric($value)) {
                return (float)$value;
            }
            $normalized = preg_replace('/[^\d,.\-]/', '', $value);
            if ($normalized === "" || $normalized === null) {
                continue;
            }
            if (str_contains($normalized, ",") && str_contains($normalized, ".")) {
                $normalized = str_replace(".", "", $normalized);
            }
            $normalized = str_replace(",", ".", $normalized);
            if (is_numeric($normalized)) {
                return (float)$normalized;
            }
        }
        return 0;
    }
}

if (!function_exists('coherta_payment_info_currency')) {
    function coherta_payment_info_currency($source): string
    {
        $currency = strtoupper(coherta_payment_info_first_string($source, ["currency", "currency_code", "currencyCode", "plan_currency"]));
        $numericMap = ["208" => "DKK", "752" => "SEK", "578" => "NOK", "826" => "GBP", "840" => "USD", "978" => "EUR", "985" => "PLN", "352" => "ISK"];
        if (isset($numericMap[$currency])) {
            return $numericMap[$currency];
        }
        return preg_match('/^[A-Z]{3}$/', $currency) ? $currency : "";
    }
}

if (!function_exists('coherta_payment_info_billing_frequency')) {
    function coherta_payment_info_billing_frequency($source): string
    {
        $frequency = strtolower(coherta_payment_info_first_string($source, [
            "billing_frequency", "billingFrequency", "frequency", "billing_cycle", "billingCycle",
        ]));
        return match ($frequency) {
            "year", "annual", "annually", "yearly" => "yearly",
            "quarter", "quarterly" => "quarterly",
            "semiannual", "semi_annually", "per_six_months" => "semiannual",
            default => "monthly",
        };
    }
}

if (!function_exists('coherta_payment_info_plan_key')) {
    function coherta_payment_info_plan_key($source): string
    {
        $plan = strtolower(coherta_payment_info_first_string($source, ["plan", "subscription_plan", "payment_plan", "partner_plan", "package", "package_name", "plan_label", "title", "name"]));
        if ($plan === "") {
            return "";
        }
        if (str_contains($plan, "extended")) {
            return "extended";
        }
        if (str_contains($plan, "standard") || str_contains($plan, "growth") || str_contains($plan, "connect") || str_contains($plan, "crm")) {
            return "standard";
        }
        return "";
    }
}

if (!function_exists('coherta_payment_info_source_has_specific_plan')) {
    function coherta_payment_info_source_has_specific_plan(array $source): bool
    {
        if (coherta_payment_info_find_checkout_url($source) !== "") {
            return true;
        }
        if (coherta_payment_info_amount($source) > 0) {
            return true;
        }
        if (coherta_payment_info_first_string($source, ["partner_payment_plan", "partner_plan", "payment_plan", "billing_plan"]) !== "") {
            return true;
        }
        $priceId = coherta_payment_info_find_matching_string($source, ["stripe_price_id", "stripePriceId", "price_id", "priceId"], function (string $value): bool {
            return str_starts_with($value, "price_");
        });
        if ($priceId !== "") {
            return true;
        }
        $productId = coherta_payment_info_find_matching_string($source, ["stripe_product_id", "stripeProductId", "product_id", "productId"], function (string $value): bool {
            return str_starts_with($value, "prod_");
        });
        return $productId !== "";
    }
}
if (!function_exists('coherta_payment_info_customer_matches')) {
    function coherta_payment_info_customer_matches(array $customer, array $company, ?array $user): bool
    {
        $companyIds = [];
        foreach (["company_uid", "company_id", "companyId", "uid", "id", "account_id", "accountId"] as $key) {
            if (isset($company[$key]) && is_scalar($company[$key])) {
                $value = trim((string)$company[$key]);
                if ($value !== "") {
                    $companyIds[$value] = true;
                }
            }
        }

        foreach (["company_uid", "company_id", "companyId", "customer_company_id", "account_id", "accountId", "company", "uid", "id"] as $key) {
            if (isset($customer[$key]) && is_scalar($customer[$key])) {
                $value = trim((string)$customer[$key]);
                if ($value !== "" && isset($companyIds[$value])) {
                    return true;
                }
            }
        }

        $companyName = strtolower(trim((string)($company["company_name"] ?? $company["name"] ?? "")));
        $customerCompanyName = strtolower(trim((string)($customer["company_name"] ?? $customer["customer_name"] ?? $customer["name"] ?? "")));
        if ($companyName !== "" && $customerCompanyName !== "" && $companyName === $customerCompanyName) {
            return true;
        }
        $userEmail = strtolower(trim((string)($user["email"] ?? $user["username"] ?? "")));
        $customerEmail = strtolower(trim((string)($customer["email"] ?? $customer["customer_email"] ?? $customer["username"] ?? "")));
        return $userEmail !== "" && $customerEmail !== "" && $userEmail === $customerEmail;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="Coherta">
  <meta name="keywords" content="Coherta, CRM, Customer Relationship Management">
  <meta name="description" content="<?php echo $meta_description ?>">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://coherta.com<?php echo htmlspecialchars($uri, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="icon" href="/app/assets/images/favicon.png" type="image/x-icon">
  <link rel="shortcut icon" href="/app/assets/images/favicon.png" type="image/x-icon">
  <meta name="facebook-domain-verification" content="72ic6skc3hleii59giomj6fahx86s1" />
  <title>Coherta - <?php echo $PageTitle; ?></title>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17610635410"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-17610635410');
    </script>
<?php include('style.php'); ?>
<?php
if(isset($user)) {
    $company_data = !empty($cohertaFreeIntroductionPage)
        ? ($client->get('/plugins__sales_insider__introductions.php?action=account')['company'] ?? [])
        : ($client->getCurrentCompanies()[0] ?? []);
    if (!empty($company_data) && empty($cohertaFreeIntroductionPage) && ($setupStripeEnabled ?? true)) {
        $isPartnerSession = !empty($_SESSION["return_to_agency"]);
        if (!$isPartnerSession) {
            require_once __DIR__ . '/start-account-status.php';
            $accountCompanyId = (int)$company_data['id'];
            $accountSubscription = $client->get('/users/me/subscription_center', ['view' => 'gate', 'company_id' => $accountCompanyId])['subscription'] ?? null;
            $payment_information_required = coherta_account_start_required($accountSubscription, $accountCompanyId);
        }

        if ($payment_information_required) {
            $payment_information_title = "Start my account";
            $payment_information_copy = "Start my account to use Coherta.";
            $payment_information_button_label = "Start my account";
            $payment_information_url = "/app/my-account";
        }
    }

    if(empty($cohertaFreeIntroductionPage) && ($company_data["method"] ?? "") == "stripe" && ($company_data['stripe_subscription'] ?? "") == "") {

        $expiry = new DateTime($company_data["trial_expiry"] ?? "now");
        $today  = new DateTime();
        $diff = $today->diff($expiry);
        $days_left = (int)$diff->format('%r%a');
        $trial_message = "";
        if ($days_left > 0) {
            echo "<script>

                function showDemoButton() {
                    if (document.getElementById('demo-fab')) return;
                    var fab = document.createElement('button');
                    fab.id = 'demo-fab';
                    fab.innerText = 'Schedule walkthrough today';
                    fab.innerHTML = '<img src=\"/assets/img/people/simone-chimera.png\" alt=\"Simone Chimera\" style=\"width:32px;height:32px;border-radius:50%;vertical-align:middle;margin-right:8px;\" /> Schedule Onboarding';
                    fab.style.position = 'fixed';
                    fab.style.right = '24px';
                    fab.style.bottom = '24px';
                    fab.style.background = '#28a745';
                    fab.style.color = '#fff';
                    fab.style.border = 'none';
                    fab.style.borderRadius = '24px';
                    fab.style.padding = '0.75rem 1.5rem';
                    fab.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
                    fab.style.zIndex = '1002';
                    fab.style.cursor = 'pointer';
                    fab.onclick = function() {
                        window.open('https://outlook.office.com/book/Coherta@venturecapitals.dk/s/QTR0Oy_OQU6VDuPmyS5Usw2?ismsaljsauthenabled', '_blank');
                    };
                    document.body.appendChild(fab);
                }
            </script>";
        }
    }
}
