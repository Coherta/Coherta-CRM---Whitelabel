<?php
// Keep new account creation open in the setup funnel.
$setupSignupsPaused = false;
// Require payment activation through Revolut in the setup funnel.
$setupPaymentEnabled = true;
$setupClearhausTestMode = isset($_GET['clearhaus'])
    && is_string($_GET['clearhaus'])
    && $_GET['clearhaus'] === '1';
// Test approval only advances this wizard; it must never enable real checkout.
$setupPaymentEnabled = $setupPaymentEnabled && !$setupClearhausTestMode;

require_once "./base.php";

$publicPage = true;
$PageTitle = "Setup Funnel";
$meta_description = "Complete your Coherta setup step by step, connect the required services and configure your workspace so your team can start working efficiently.";
$sidebar_close = true;
$setupHasAppSession = false;
$setupRenderAppShell = false;
$user = null;

if (!empty($_SESSION["token"])) {
    $sessionUser = $client->getCurrentUser();
    if (is_array($sessionUser)) {
        $user = $sessionUser;
        $setupHasAppSession = true;
        $setupRenderAppShell = true;
        if (!empty($user["img_key"])) {
            $user_image = "https://api.coherta.com/images/" . $user["img_key"];
        }
    }
}

include('partial/header.php');

// This page owns the activation step, so the global payment gate should not cover it.
$payment_information_required = false;

$company_data = isset($company_data) && is_array($company_data)
    ? $company_data
    : ($setupHasAppSession ? ($client->getCurrentCompanies()[0] ?? []) : []);

function coherta_setup_query_value(array $keys): string
{
    foreach ($keys as $key) {
        if (isset($_GET[$key]) && trim((string)$_GET[$key]) !== "") {
            return trim((string)$_GET[$key]);
        }
    }
    return "";
}

function coherta_setup_normalize_account_type(string $value): string
{
    $value = strtolower(trim($value));
    if (in_array($value, ["business", "company", "team", "teams", "b2b", "virksomhed", "firma"], true)) {
        return "business";
    }
    if (in_array($value, ["personal", "private", "person", "individual", "privat", "privatperson"], true)) {
        return "personal";
    }
    return "";
}

function coherta_setup_normalize_plan(string $value): string
{
    $value = strtolower(trim($value));
    if (in_array($value, ["personal", "private", "person", "individual", "privat", "privatperson"], true)) {
        return "personal";
    }
    if (in_array($value, ["extended", "scale", "scaling", "pro", "premium"], true)) {
        return "extended";
    }
    if (in_array($value, ["custom", "enterprise", "special"], true)) {
        return "custom";
    }
    return "standard";
}

function coherta_setup_normalize_billing(string $value): string
{
    $value = strtolower(trim($value));
    if (in_array($value, ["yearly", "annual", "annually", "year", "year_1", "aarlig", "årlig"], true)) {
        return "yearly";
    }
    return "monthly";
}

function coherta_setup_billing_period_key(string $billing): string
{
    if ($billing === "custom") {
        return "";
    }
    return $billing === "yearly" ? "year_1" : "month_1";
}

function coherta_setup_normalize_locale(string $value): string
{
    $key = strtolower(trim($value));
    $key = preg_replace('/[\s_]+/', '-', $key);
    $key = preg_replace('/[^a-z0-9-]/', '', $key ?? '');
    $map = [
        "da" => "da", "dk" => "da", "danmark" => "da", "denmark" => "da",
        "de" => "de", "germany" => "de", "deutschland" => "de",
        "es" => "es", "spain" => "es", "espana" => "es", "espaa" => "es",
        "fi" => "fi", "finland" => "fi",
        "fr" => "fr", "france" => "fr",
        "hr" => "hr", "croatia" => "hr", "hrvatska" => "hr",
        "is" => "is", "iceland" => "is", "island" => "is",
        "no" => "no", "nb" => "no", "norway" => "no", "norge" => "no",
        "pl" => "pl", "poland" => "pl", "polska" => "pl",
        "se" => "se", "sv" => "se", "sweden" => "se", "sverige" => "se",
        "uk" => "uk", "gb" => "uk", "en" => "uk", "en-gb" => "uk", "united-kingdom" => "uk", "unitedkingdom" => "uk", "great-britain" => "uk",
    ];
    return $map[$key] ?? "";
}

function coherta_setup_detect_locale(string $market): string
{
    foreach (["lang", "language", "locale", "country_code", "market"] as $key) {
        if (!empty($_GET[$key])) {
            $locale = coherta_setup_normalize_locale((string)$_GET[$key]);
            if ($locale !== "") {
                return $locale;
            }
        }
    }

    $path = parse_url((string)($_SERVER["REQUEST_URI"] ?? ""), PHP_URL_PATH) ?: "";
    $segments = array_values(array_filter(explode("/", $path)));
    if (!empty($segments[0])) {
        $locale = coherta_setup_normalize_locale((string)$segments[0]);
        if ($locale !== "") {
            return $locale;
        }
    }

    return coherta_setup_normalize_locale($market) ?: "da";
}

function coherta_setup_translation_map(string $locale): array
{
    if ($locale === "da") {
        return [];
    }
    $file = __DIR__ . "/setup-funnel-i18n.json";
    if (!is_file($file)) {
        return [];
    }
    $translations = json_decode((string)file_get_contents($file), true);
    if (!is_array($translations)) {
        return [];
    }
    $map = [];
    foreach ($translations as $source => $localized) {
        if (is_array($localized) && isset($localized[$locale]) && trim((string)$localized[$locale]) !== "") {
            $map[(string)$source] = (string)$localized[$locale];
        }
    }
    return $map;
}

function coherta_setup_language_options(): array
{
    return [
        "da" => ["label" => "Danmark", "countryCode" => "da", "htmlLang" => "da"],
        "uk" => ["label" => "United Kingdom", "countryCode" => "uk", "htmlLang" => "en"],
        "se" => ["label" => "Sverige", "countryCode" => "se", "htmlLang" => "sv"],
        "no" => ["label" => "Norge", "countryCode" => "no", "htmlLang" => "no"],
        "fi" => ["label" => "Suomi", "countryCode" => "fi", "htmlLang" => "fi"],
        "fr" => ["label" => "France", "countryCode" => "fr", "htmlLang" => "fr"],
        "de" => ["label" => "Deutschland", "countryCode" => "de", "htmlLang" => "de"],
        "es" => ["label" => "Espana", "countryCode" => "es", "htmlLang" => "es"],
        "is" => ["label" => "Iceland", "countryCode" => "is", "htmlLang" => "is"],
        "pl" => ["label" => "Polska", "countryCode" => "pl", "htmlLang" => "pl"],
        "hr" => ["label" => "Hrvatska", "countryCode" => "hr", "htmlLang" => "hr"],
    ];
}

function coherta_setup_flag_icon(string $locale): string
{
    $locale = coherta_setup_normalize_locale($locale) ?: "da";
    $flags = [
        "da" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="20" fill="#c8102e"></rect><rect x="8" width="3" height="20" fill="#fff"></rect><rect y="8.5" width="28" height="3" fill="#fff"></rect></svg>',
        "uk" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="20" fill="#012169"></rect><path d="M0 0l28 20M28 0L0 20" stroke="#fff" stroke-width="4"></path><path d="M0 0l28 20M28 0L0 20" stroke="#c8102e" stroke-width="2.4"></path><path d="M14 0v20M0 10h28" stroke="#fff" stroke-width="6"></path><path d="M14 0v20M0 10h28" stroke="#c8102e" stroke-width="3.4"></path></svg>',
        "se" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="20" fill="#006aa7"></rect><rect x="8" width="3.5" height="20" fill="#fecc00"></rect><rect y="8.25" width="28" height="3.5" fill="#fecc00"></rect></svg>',
        "no" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="20" fill="#ba0c2f"></rect><rect x="7" width="5" height="20" fill="#fff"></rect><rect y="7.5" width="28" height="5" fill="#fff"></rect><rect x="8.2" width="2.6" height="20" fill="#00205b"></rect><rect y="8.7" width="28" height="2.6" fill="#00205b"></rect></svg>',
        "fi" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="20" fill="#fff"></rect><rect x="7" width="4" height="20" fill="#002f6c"></rect><rect y="8" width="28" height="4" fill="#002f6c"></rect></svg>',
        "fr" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="9.33" height="20" fill="#0055a4"></rect><rect x="9.33" width="9.34" height="20" fill="#fff"></rect><rect x="18.67" width="9.33" height="20" fill="#ef4135"></rect></svg>',
        "de" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="6.67" fill="#000"></rect><rect y="6.67" width="28" height="6.66" fill="#dd0000"></rect><rect y="13.33" width="28" height="6.67" fill="#ffce00"></rect></svg>',
        "es" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="20" fill="#aa151b"></rect><rect y="5" width="28" height="10" fill="#f1bf00"></rect></svg>',
        "is" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="20" fill="#02529c"></rect><rect x="7" width="6" height="20" fill="#fff"></rect><rect y="7" width="28" height="6" fill="#fff"></rect><rect x="8.5" width="3" height="20" fill="#dc1e35"></rect><rect y="8.5" width="28" height="3" fill="#dc1e35"></rect></svg>',
        "pl" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="10" fill="#fff"></rect><rect y="10" width="28" height="10" fill="#dc143c"></rect></svg>',
        "hr" => '<svg width="28" height="20" viewBox="0 0 28 20"><rect width="28" height="6.67" fill="#f00"></rect><rect y="6.67" width="28" height="6.66" fill="#fff"></rect><rect y="13.33" width="28" height="6.67" fill="#171796"></rect><path d="M11.4 6.7h5.2v5.5c0 1.5-1.1 2.7-2.6 3.4-1.5-.7-2.6-1.9-2.6-3.4V6.7Z" fill="#f00" stroke="#fff" stroke-width=".6"></path><path d="M12.1 7.4h1.15v1.15H12.1V7.4Zm2.3 0h1.15v1.15H14.4V7.4Zm-1.15 1.15h1.15V9.7h-1.15V8.55Zm-1.15 1.15h1.15v1.15H12.1V9.7Zm2.3 0h1.15v1.15H14.4V9.7Z" fill="#fff"></path></svg>',
    ];
    return $flags[$locale] ?? $flags["da"];
}

function coherta_setup_price_month_copy(float $amount, string $currency, string $locale): string
{
    $formattedAmount = number_format($amount, 0, ",", ".");
    $currencyLabel = strtoupper($currency) === "DKK" ? "kr." : strtoupper($currency);
    $price = $formattedAmount . " " . $currencyLabel;
    $suffixes = [
        "da" => "/md. - 1 måned ad gangen.",
        "de" => "/Monat - monatlich.",
        "es" => "/mes - mes a mes.",
        "fi" => "/kk - kuukausi kerrallaan.",
        "fr" => "/mois - mois par mois.",
        "hr" => "/mj. - mjesec po mjesec.",
        "is" => "/mán. - einn mánuð í einu.",
        "no" => "/mnd. - 1 måned av gangen.",
        "pl" => "/mies. - miesiąc po miesiącu.",
        "se" => "/mån - 1 månad i taget.",
        "uk" => "/mo - month to month.",
    ];
    return $price . ($suffixes[$locale] ?? $suffixes["da"]);
}

$talentAttractEnabled = isset($_GET["TalentAttract"])
    && !is_array($_GET["TalentAttract"])
    && trim((string)$_GET["TalentAttract"]) === "1";
$requestedCustomerType = coherta_setup_normalize_account_type(coherta_setup_query_value(["customer_type"]));
$accountTypeLockedToCompany = true;
$requestedAccountType = coherta_setup_normalize_account_type(coherta_setup_query_value(["account_type", "customer_type", "use", "usage", "use_case"]));
if ($accountTypeLockedToCompany) {
    $requestedAccountType = "business";
}
$requestedPlan = coherta_setup_normalize_plan(coherta_setup_query_value(["plan", "model", "package"]));
$requestedBilling = coherta_setup_normalize_billing(coherta_setup_query_value(["billing", "billing_cycle", "payment"]));
if ($requestedPlan === "personal" && !$accountTypeLockedToCompany) {
    $requestedAccountType = "personal";
}
if ($requestedPlan === "personal" && $accountTypeLockedToCompany) {
    $requestedPlan = "standard";
}
if ($requestedAccountType === "personal") {
    $requestedPlan = "personal";
    $requestedBilling = "monthly";
}
if ($requestedPlan === "custom") {
    $requestedBilling = "custom";
}
if ($talentAttractEnabled) {
    $requestedAccountType = "business";
    $requestedPlan = "standard";
    $requestedBilling = "monthly";
}
$setupGoogleReturnParams = [];
foreach ($_GET as $key => $value) {
    if (!is_string($key) || !is_scalar($value)) continue;
    if (in_array($key, ["old_session", "session_id", "sessionId", "token", "access_token", "accessToken"], true)) continue;
    $setupGoogleReturnParams[$key] = (string)$value;
}
$setupGoogleReturnParams["account_type"] = "business";
$setupGoogleReturnParams["customer_type"] = "company";
$setupGoogleReturnParams["google_auth"] = "1";
$setupGoogleReturnPath = "/app/setup-funnel?" . http_build_query($setupGoogleReturnParams, "", "&", PHP_QUERY_RFC3986);
$setupGoogleLoginReturnUrl = "https://www.coherta.com/app/login?url=" . rawurlencode($setupGoogleReturnPath);
$setupGoogleAuthUrl = "https://api.coherta.com/auth/google?" . http_build_query([
    "return_url" => $setupGoogleLoginReturnUrl,
    "source" => "setup-funnel",
    // Keep the legacy automatic Stripe signup trial disabled.
    "stripe_enabled" => "0",
], "", "&", PHP_QUERY_RFC3986);
$pricingRequestPresent = $talentAttractEnabled
    || coherta_setup_query_value(["plan", "model", "package", "billing", "billing_cycle", "payment"]) !== "";
$selectedMarket = coherta_setup_query_value(["country_code", "market", "locale"]);
if ($selectedMarket === "") {
    $selectedMarket = coherta_company_market($company_data) ?: "Denmark";
}
$setupLocale = coherta_setup_detect_locale($selectedMarket);
$setupTranslations = coherta_setup_translation_map($setupLocale);
$setupLanguageOptions = coherta_setup_language_options();

$personalPricingProduct = coherta_get_product_pricing($selectedMarket, "private", "standard");
$standardPricingProduct = coherta_get_product_pricing($selectedMarket, "company", "standard");
$extendedPricingProduct = coherta_get_product_pricing($selectedMarket, "company", "extended");
$setupSharedStripeProductKey = "Tj6iHmP3XyIEdS";
$setupSharedStripeProductId = "prod_UWswNDxveWxoUo";
$setupSharedStripePriceId = "price_1TXpB3LliR0gAZeqpwEpkUCN";
$setupPrivateStripeProductId = "prod_UTgr8MlNvkYiwU";
$setupTalentAttractProductId = "prod_UTh12jd4lY49SA";
$setupTalentAttractSubscriptionProductId = "prod_Use1kp5nPpRN5y";
$setupTalentAttractSubscriptionPriceId = "price_1TssjILliR0gAZeqpDNGS0qs";
$setupTalentAttractUpfrontPrice = 750;
$setupTalentAttractMonthlyPrice = 999;
$setupTalentAttractTrialDays = 365;
$setupAffiliateId = coherta_setup_query_value(["bo_affiliate"]) !== "" ? "41" : coherta_setup_query_value(["affiliate"]);
$setupTrialDays = $talentAttractEnabled ? $setupTalentAttractTrialDays : ($setupAffiliateId === "114" ? 30 : 7);
$setupTrialCopy = strtr($setupTranslations['Inkl. {days} dages gratis trial.'] ?? 'Inkl. {days} dages gratis trial.', ['{days}' => (string)$setupTrialDays]);

$setupPersonalMonthlyPrice = (float)($personalPricingProduct["monthly_price"] ?? 299);
$setupPersonalAnnualMonthlyPrice = (float)($personalPricingProduct["annual_monthly_price"] ?? $setupPersonalMonthlyPrice);
$setupPersonalCurrency = (string)($personalPricingProduct["currency"] ?? "DKK");
$setupStandardMonthlyPrice = (float)($standardPricingProduct["monthly_price"] ?? 999);
$setupStandardAnnualMonthlyPrice = (float)($standardPricingProduct["annual_monthly_price"] ?? $setupStandardMonthlyPrice);
$setupStandardCurrency = (string)($standardPricingProduct["currency"] ?? "DKK");
$setupExtendedMonthlyPrice = (float)($extendedPricingProduct["monthly_price"] ?? 4198);
$setupExtendedAnnualMonthlyPrice = (float)($extendedPricingProduct["annual_monthly_price"] ?? $setupExtendedMonthlyPrice);
$setupExtendedCurrency = (string)($extendedPricingProduct["currency"] ?? $setupStandardCurrency);
if ($talentAttractEnabled) {
    $setupStandardMonthlyPrice = $setupTalentAttractMonthlyPrice;
    $setupStandardAnnualMonthlyPrice = $setupTalentAttractMonthlyPrice;
    $setupStandardCurrency = "DKK";
}
$setupPersonalPriceCopy = coherta_setup_price_month_copy($setupPersonalMonthlyPrice, $setupPersonalCurrency, $setupLocale);

$setupPricingModels = [
    "personal" => [
        "key" => "personal",
        "label" => "Coherta Privatperson",
        "monthlyPrice" => $setupPersonalMonthlyPrice,
        "annualMonthlyPrice" => $setupPersonalAnnualMonthlyPrice,
        "currency" => $setupPersonalCurrency,
        "stripeProductKey" => (string)($personalPricingProduct["product_key"] ?? $personalPricingProduct["product_id"] ?? $setupPrivateStripeProductId),
        "stripeProductId" => (string)($personalPricingProduct["product_id"] ?? $setupPrivateStripeProductId),
        "stripePriceId" => (string)($personalPricingProduct["price_id"] ?? ""),
    ],
    "standard" => [
        "key" => "standard",
        "label" => "Coherta Standard",
        "monthlyPrice" => $setupStandardMonthlyPrice,
        "annualMonthlyPrice" => $setupStandardAnnualMonthlyPrice,
        "currency" => $setupStandardCurrency,
        "stripeProductKey" => $talentAttractEnabled
            ? $setupTalentAttractSubscriptionProductId
            : (string)($standardPricingProduct["product_key"] ?? $setupSharedStripeProductKey),
        "stripeProductId" => $talentAttractEnabled
            ? $setupTalentAttractSubscriptionProductId
            : (string)($standardPricingProduct["product_id"] ?? $setupSharedStripeProductId),
        "stripePriceId" => $talentAttractEnabled
            ? $setupTalentAttractSubscriptionPriceId
            : (string)($standardPricingProduct["price_id"] ?? $setupSharedStripePriceId),
    ],
    "extended" => [
        "key" => "extended",
        "label" => "Coherta Extended",
        "monthlyPrice" => $setupExtendedMonthlyPrice,
        "annualMonthlyPrice" => $setupExtendedAnnualMonthlyPrice,
        "currency" => $setupExtendedCurrency,
        "stripeProductKey" => (string)($extendedPricingProduct["product_key"] ?? $setupSharedStripeProductKey),
        "stripeProductId" => (string)($extendedPricingProduct["product_id"] ?? $setupSharedStripeProductId),
        "stripePriceId" => (string)($extendedPricingProduct["price_id"] ?? $setupSharedStripePriceId),
    ],
    "custom" => [
        "key" => "custom",
        "label" => "Coherta Custom",
        "monthlyPrice" => 0,
        "annualMonthlyPrice" => 0,
        "currency" => "DKK",
        "stripeProductKey" => "",
        "stripeProductId" => "",
        "stripePriceId" => "",
    ],
];

$selectedPricingModel = $setupPricingModels[$requestedPlan] ?? $setupPricingModels["standard"];
$selectedBillingPeriodKey = coherta_setup_billing_period_key($requestedBilling);

$stripeSubscriptionId = trim((string)($company_data["stripe_subscription"] ?? ""));
$hasActiveSubscription = $stripeSubscriptionId !== "" || !empty($company_data["payment_subscription_active"]);
$paymentRequired = $setupClearhausTestMode || ($setupPaymentEnabled && !$hasActiveSubscription);
if ($requestedPlan === "custom") {
    $paymentActivationUrl = "/da/kontakt-os.php?source=setup-funnel&plan=custom";
} else {
    $paymentActivationUrl = "#payment-checkout";
}

$fullName = trim((string)($user["full_name"] ?? ""));
$nameParts = preg_split('/\s+/', $fullName);
$firstName = $nameParts && $nameParts[0] !== "" ? $nameParts[0] : "there";
$companyName = (string)($company_data["company_name"] ?? "");
$userEmail = (string)($user["email"] ?? $user["username"] ?? "");
$userId = (string)($user["id"] ?? $user["user_id"] ?? "");
$userLoggedIn = $userId !== "" || !empty($_SESSION["token"] ?? "");
$setupSignupBlocked = $setupSignupsPaused && !$setupHasAppSession;
$setupSignupNotice = 'Vi kan i øjeblikket ikke tage imod nye kunder på grund af stor efterspørgsel.';
$companyId = (string)($company_data["id"] ?? $company_data["company_id"] ?? "");
$connectors = isset($user["connectors"]) && is_array($user["connectors"]) ? $user["connectors"] : [];
$linkedinConnector = isset($connectors["linkedin"]) && is_array($connectors["linkedin"]) ? $connectors["linkedin"] : [];
$linkedinStatus = null;

if (array_key_exists("status", $linkedinConnector)) {
    $linkedinStatus = $linkedinConnector["status"];
} elseif (isset($linkedinConnector[0]) && is_array($linkedinConnector[0]) && array_key_exists("status", $linkedinConnector[0])) {
    $linkedinStatus = $linkedinConnector[0]["status"];
}

$linkedinConnected = (int)$linkedinStatus === 1;
$chromeExtensionConnected = ($connectors["chrome_extension"] ?? false) === true
    || ($connectors["chrome_extension"] ?? false) === 1
    || ($connectors["chrome_extension"] ?? false) === "1"
    || ($connectors["chrome_extension"] ?? false) === "true";
$chromePluginUrl = "https://chromewebstore.google.com/search/Coherta";

$setupConfig = [
    "signupBlocked" => $setupSignupBlocked,
    "signupNotice" => $setupSignupNotice,
    "clearhausTestMode" => $setupClearhausTestMode,
    "clearhausTestDate" => (new DateTimeImmutable('now', new DateTimeZone('Europe/Copenhagen')))->format('Y-m-d'),
    "paymentEnabled" => $setupPaymentEnabled,
    "paymentRequired" => $paymentRequired,
    "hasActiveSubscription" => $hasActiveSubscription,
    "paymentActivationUrl" => $paymentActivationUrl,
    "paymentCheckoutEndpoint" => "https://api.coherta.com/billing/checkout",
    "trialDays" => $setupClearhausTestMode ? 0 : $setupTrialDays,
    "sessionId" => (string)($_SESSION["token"] ?? ""),
    "firstName" => $firstName,
    "fullName" => $fullName,
    "userEmail" => $userEmail,
    "userId" => $userId,
    "userLoggedIn" => $userLoggedIn,
    "companyName" => $companyName,
    "companyId" => $companyId,
    "linkedinConnected" => $linkedinConnected,
    "linkedinStatus" => $linkedinStatus,
    "chromePluginInstalled" => $chromeExtensionConnected,
    "chromePluginAuthenticated" => $chromeExtensionConnected,
    "chromePluginUrl" => $chromePluginUrl,
    "prefilledAccountType" => $requestedAccountType,
    "selectedPlan" => $requestedPlan,
    "selectedBilling" => $requestedBilling,
    "selectedBillingPeriod" => $selectedBillingPeriodKey,
    "selectedMarket" => $selectedMarket,
    "selectedPricingModel" => $selectedPricingModel,
    "pricingModels" => $setupPricingModels,
    "locale" => $setupLocale,
    "translations" => $setupTranslations,
    "languageOptions" => $setupLanguageOptions,
    "pricingRequestPresent" => $pricingRequestPresent,
    "accountTypeLockedToCompany" => $accountTypeLockedToCompany,
    "talentAttract" => [
        "enabled" => $talentAttractEnabled,
        "promotionCode" => "TalentAttract",
        "upfrontProductId" => $setupTalentAttractProductId,
        "upfrontPrice" => $setupTalentAttractUpfrontPrice,
        "subscriptionProductId" => $setupTalentAttractSubscriptionProductId,
        "subscriptionPriceId" => $setupTalentAttractSubscriptionPriceId,
        "monthlyPrice" => $setupTalentAttractMonthlyPrice,
        "currency" => "DKK",
        "trialDays" => $setupClearhausTestMode ? 0 : $setupTalentAttractTrialDays,
    ],
];

include('partial/loader.php');
?>
<style>
    .setup-funnel-page,
    .setup-funnel-page * {
        box-sizing: border-box;
        letter-spacing: 0;
    }

    body.setup-funnel-open {
        overflow: hidden;
    }

    .setup-funnel-page {
        --setup-bg: #f6f8fb;
        --setup-surface: #ffffff;
        --setup-ink: #111827;
        --setup-muted: #667085;
        --setup-line: #d8e1ec;
        --setup-soft: #eef6ff;
        --setup-blue: #2478f2;
        --setup-green: #15915f;
        --setup-red: #c81e1e;
        --setup-amber: #a15c00;
        position: fixed;
        inset: 0;
        z-index: 999900;
        width: 100vw;
        height: 100vh;
        height: 100dvh;
        display: grid;
        place-items: center;
        padding: 22px;
        overflow: auto;
        background: rgba(247, 249, 252, .42);
        backdrop-filter: blur(8px);
        color: var(--setup-ink);
    }

    .setup-shell {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        width: calc(100vw - 44px);
        height: calc(100vh - 44px);
        height: calc(100dvh - 44px);
        background: var(--setup-surface);
        border: 1px solid var(--setup-line);
        border-radius: 26px;
        overflow: hidden;
        box-shadow: 0 30px 100px rgba(15, 23, 42, .18);
        transition: width .45s ease, height .45s ease, border-radius .45s ease;
    }

    .setup-shell.workflow-mode {
        grid-template-columns: minmax(0, 1fr);
        border-radius: 22px;
    }

    .setup-shell.workflow-mode .setup-rail {
        display: none;
    }

    .setup-rail {
        border-right: 1px solid var(--setup-line);
        background: #fbfcff;
        padding: 24px 18px;
    }

    .setup-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        font-size: 15px;
        margin-bottom: 24px;
    }

    .setup-brand img {
        width: 26px;
        height: 26px;
        object-fit: contain;
    }

    .setup-progress {
        height: 7px;
        background: #e8eef6;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 22px;
    }

    .setup-progress span {
        display: block;
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, var(--setup-blue), #52a3ff);
        border-radius: 999px;
        transition: width .2s ease;
    }

    .setup-steps {
        display: grid;
        gap: 8px;
    }

    .setup-step {
        width: 100%;
        display: grid;
        grid-template-columns: 28px 1fr;
        gap: 11px;
        border: 0;
        background: transparent;
        text-align: left;
        padding: 10px;
        color: var(--setup-muted);
        border-radius: 8px;
    }

    .setup-step:hover,
    .setup-step.active {
        background: var(--setup-soft);
        color: #1f2a44;
    }

    .setup-step.done .setup-step-num {
        background: var(--setup-green);
        color: #fff;
        border-color: var(--setup-green);
    }

    .setup-step.active .setup-step-num {
        background: var(--setup-blue);
        color: #fff;
        border-color: var(--setup-blue);
    }

    .setup-step-num {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 1px solid var(--setup-line);
        border-radius: 999px;
        background: #fff;
        font-weight: 800;
        font-size: 12px;
        line-height: 1;
        margin-top: 0;
        text-align: center;
    }

    .setup-step b {
        display: block;
        color: inherit;
        font-size: 14px;
        line-height: 1.25;
    }

    .setup-step > span:not(.setup-step-num),
    .setup-step > span:not(.setup-step-num) > span {
        display: block;
        margin-top: 2px;
        font-size: 12px;
        line-height: 1.35;
    }

    .setup-stage {
        display: grid;
        grid-template-rows: 1fr auto;
        min-width: 0;
        min-height: 0;
        background: #fff;
    }

    .setup-screen-wrap {
        position: relative;
        min-height: 0;
        overflow: hidden;
    }

    .setup-screen {
        position: absolute;
        inset: 0;
        display: block;
        padding: 34px;
        overflow: auto;
        opacity: 0;
        pointer-events: none;
        transform: translateX(38px);
        transition: opacity .28s ease, transform .34s cubic-bezier(.2,.9,.2,1);
        will-change: transform, opacity;
    }

    .setup-screen.active {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0);
        z-index: 2;
    }

    .setup-screen.enter-from-left {
        opacity: 0;
        transform: translateX(-38px);
    }

    .setup-screen.enter-from-right {
        opacity: 0;
        transform: translateX(38px);
    }

    .setup-screen.active.exit-to-left {
        opacity: 0;
        pointer-events: none;
        transform: translateX(-38px);
        z-index: 1;
    }

    .setup-screen.active.exit-to-right {
        opacity: 0;
        pointer-events: none;
        transform: translateX(38px);
        z-index: 1;
    }

    .setup-kicker {
        color: var(--setup-blue);
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .setup-title {
        margin: 0;
        max-width: 760px;
        font-size: 32px;
        line-height: 1.12;
        font-weight: 800;
        color: var(--setup-ink);
    }

    .setup-lead {
        max-width: 780px;
        margin: 12px 0 24px;
        color: var(--setup-muted);
        font-size: 15px;
        line-height: 1.55;
    }

    .setup-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        max-width: 860px;
    }

    .setup-field {
        display: grid;
        gap: 7px;
    }

    .setup-field.full {
        grid-column: 1 / -1;
    }

    .setup-field label {
        margin: 0;
        color: #344054;
        font-size: 13px;
        font-weight: 800;
    }

    .setup-field input,
    .setup-field select,
    .setup-field textarea {
        width: 100%;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        padding: 13px 14px;
        background: #fff;
        color: var(--setup-ink);
        outline: none;
    }

    .setup-field textarea {
        min-height: 120px;
        resize: vertical;
    }

    .setup-field input:focus,
    .setup-field select:focus,
    .setup-field textarea:focus {
        border-color: #8bbcff;
        box-shadow: 0 0 0 4px rgba(36, 120, 242, .10);
    }

    .setup-choice-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        max-width: 860px;
        margin-top: 18px;
    }

    .setup-source-choice-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        max-width: 980px;
    }

    .setup-playbook-choice-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        max-width: 980px;
    }

    .setup-choice {
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background: #fff;
        padding: 16px;
        text-align: left;
        min-height: 116px;
    }

    .setup-choice:not(:disabled):hover,
    .setup-choice.selected {
        border-color: #86b7ff;
        background: #f7fbff;
        box-shadow: 0 14px 34px rgba(36, 120, 242, .08);
    }

    .setup-choice:disabled,
    .setup-choice.is-disabled {
        cursor: not-allowed;
        opacity: .46;
        background: #f3f5f8;
        color: #98a2b3;
        box-shadow: none;
    }

    .setup-choice:disabled h3,
    .setup-choice:disabled p,
    .setup-choice.is-disabled h3,
    .setup-choice.is-disabled p {
        color: #98a2b3;
    }

    .setup-choice h3 {
        margin: 0 0 8px;
        font-size: 16px;
        color: var(--setup-ink);
    }

    .setup-choice p {
        margin: 0;
        color: var(--setup-muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .setup-source-panel {
        max-width: 860px;
        margin-top: 16px;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        padding: 18px;
        background: #fbfcff;
    }

    .setup-source-panel[hidden] {
        display: none;
    }

    .setup-auth-options { display: grid; gap: 12px; margin: 14px 0 18px; }
    .setup-google-auth { align-items: center; background: #fff; border: 1px solid #d8e0ee; border-radius: 12px; color: #1f2937; display: flex; font-weight: 700; gap: 10px; justify-content: center; padding: 12px 16px; text-decoration: none; }
    .setup-google-auth:hover { border-color: #9db5e3; color: #164ca1; }
    .setup-google-auth:disabled { cursor: not-allowed; opacity: .55; width: 100%; }
    .setup-signup-notice {
        background: #fff8d6;
        border: 1px solid #e8bc35;
        border-left: 5px solid #e8bc35;
        border-radius: 12px;
        color: #664d03;
        line-height: 1.5;
        margin: 0 0 20px;
        padding: 14px 16px;
    }
    .setup-auth-divider { align-items: center; color: #718096; display: flex; font-size: 12px; font-weight: 700; gap: 10px; text-transform: uppercase; }
    .setup-auth-divider::before,
    .setup-auth-divider::after { background: #d8e0ee; content: ""; flex: 1; height: 1px; }
    .setup-source-group-title {
        margin: 0 0 12px;
        color: var(--setup-ink);
        font-size: 15px;
        font-weight: 900;
    }

    .setup-source-mode-divider {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 12px;
        margin: 22px 0;
        color: var(--setup-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
    }

    .setup-source-mode-divider hr {
        width: 100%;
        border: 0;
        border-top: 1px solid var(--setup-line);
        margin: 0;
    }

    .setup-field input:disabled,
    .setup-field select:disabled,
    .setup-field textarea:disabled {
        background: #eef2f7;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .setup-builder {
        display: grid;
        grid-template-columns: 250px minmax(0, 1fr) 280px;
        gap: 14px;
        min-height: 640px;
    }

    .setup-builder-panel,
    .setup-inspector {
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background: #fff;
        padding: 16px;
    }

    .setup-builder-panel h3,
    .setup-inspector h3 {
        margin: 0 0 10px;
        font-size: 15px;
        font-weight: 800;
    }

    .setup-palette {
        display: grid;
        gap: 8px;
    }

    .setup-node-add {
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        padding: 10px;
        background: #fff;
        color: #1f2937;
        text-align: left;
        font-weight: 800;
        min-height: 48px;
    }

    .setup-node-add span {
        display: block;
        margin-top: 2px;
        color: var(--setup-muted);
        font-weight: 500;
        font-size: 12px;
        line-height: 1.3;
    }

    .setup-node-add:hover {
        border-color: #86b7ff;
        background: #f7fbff;
    }

    .setup-canvas-wrap {
        position: relative;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background-color: #f8fbff;
        background-image: radial-gradient(#d8e8ff 1px, transparent 1px);
        background-size: 22px 22px;
        overflow: auto;
        min-height: 640px;
    }

    .setup-canvas {
        position: relative;
        min-width: 980px;
        min-height: 980px;
    }

    .setup-edges {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .setup-workflow-node {
        position: absolute;
        z-index: 2;
        width: 224px;
        min-height: 118px;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background: rgba(255,255,255,.98);
        box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
        padding: 13px;
        cursor: pointer;
    }

    .setup-workflow-node.selected {
        border-color: var(--setup-blue);
        box-shadow: 0 14px 34px rgba(36, 120, 242, .18);
    }

    .setup-workflow-node.needs-edit {
        border-color: #f59e0b;
        box-shadow: 0 14px 34px rgba(245, 158, 11, .16);
    }

    .setup-workflow-node.decision {
        background: linear-gradient(180deg, #fff, #f4f8ff);
    }

    .setup-node-type {
        font-size: 11px;
        color: var(--setup-muted);
        text-transform: uppercase;
        font-weight: 800;
    }

    .setup-node-title {
        margin-top: 5px;
        color: #172033;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.25;
    }

    .setup-node-body {
        margin-top: 8px;
        color: var(--setup-muted);
        font-size: 12px;
        line-height: 1.4;
        white-space: pre-line;
    }

    .setup-edge-label {
        fill: #536079;
        font-size: 12px;
        font-family: Arial, sans-serif;
        font-weight: 700;
    }

    .setup-inspector-body {
        color: var(--setup-muted);
        font-size: 13px;
        line-height: 1.5;
        min-height: 160px;
    }

    .setup-inspector-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .setup-summary {
        display: grid;
        gap: 10px;
        max-width: 780px;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        padding: 18px;
        background: #fbfcff;
    }

    .setup-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e8eef6;
        color: #52637a;
    }

    .setup-summary-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .setup-alert {
        max-width: 780px;
        border: 1px solid #dbeafe;
        background: #f8fbff;
        border-radius: 8px;
        padding: 16px;
        color: #41516b;
        line-height: 1.55;
    }

    .setup-payment-trust {
        display: grid;
        gap: 14px;
        max-width: 980px;
        margin-top: 16px;
    }

    .setup-trial-card {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 16px;
        align-items: center;
        border: 1px solid #cfe3ff;
        border-radius: 8px;
        background: #f7fbff;
        padding: 18px;
        color: #27364f;
    }

    .setup-trial-days {
        width: 74px;
        height: 74px;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: var(--setup-blue);
        color: #fff;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    .setup-trial-eyebrow {
        margin-bottom: 4px;
        color: var(--setup-blue);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .setup-trial-card h2 {
        margin: 0 0 6px;
        color: var(--setup-ink);
        font-size: 20px;
        line-height: 1.25;
        font-weight: 900;
    }

    .setup-trial-card p {
        margin: 0;
        color: #46556f;
        font-size: 14px;
        line-height: 1.5;
    }

    .setup-review-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .setup-review-card {
        display: flex;
        min-height: 158px;
        flex-direction: column;
        gap: 10px;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background: #fff;
        padding: 16px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .05);
    }

    .setup-review-stars {
        color: #f59e0b;
        font-size: 14px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .setup-review-stars span {
        display: block;
        margin-top: 5px;
        color: #7a8599;
        font-size: 11px;
        font-weight: 800;
    }

    .setup-review-card p {
        margin: 0;
        color: #41516b;
        font-size: 13px;
        line-height: 1.48;
    }

    .setup-review-card strong {
        margin-top: auto;
        color: var(--setup-ink);
        font-size: 13px;
        font-weight: 900;
    }

    .setup-connect-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        max-width: 980px;
        margin-top: 20px;
    }

    .setup-connect-card {
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background: #fff;
        padding: 18px;
    }

    .setup-connect-card.disabled {
        background: #f5f7fb;
        color: #8a97aa;
    }

    .setup-connect-card.coming-soon {
        border-color: #d5dbe7;
        background: #f8fafc;
    }

    .setup-connect-card.coming-soon h3,
    .setup-connect-card.coming-soon p {
        color: #68758a;
    }

    .setup-card-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .setup-card-title-row h3 {
        margin-bottom: 0;
    }

    .setup-coming-soon-badge {
        flex: 0 0 auto;
        padding: 5px 9px;
        border: 1px solid #cfd8e5;
        border-radius: 999px;
        background: #eef3f8;
        color: #536174;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .setup-connect-card.connected {
        border-color: rgba(21, 145, 95, .32);
        background: #f0fdf7;
        box-shadow: 0 18px 42px rgba(21, 145, 95, .12);
    }

    .setup-connect-card.connected h3,
    .setup-connect-card.connected p {
        color: #126044;
    }

    .setup-connected-state {
        display: flex;
        align-items: center;
        gap: 10px;
        width: fit-content;
        max-width: 100%;
        margin: 0 0 14px;
        padding: 11px 13px;
        border-radius: 8px;
        background: rgba(21, 145, 95, .12);
        border: 1px solid rgba(21, 145, 95, .22);
        color: #126044;
        font-weight: 900;
    }

    .setup-connected-state[hidden] {
        display: none;
    }

    .setup-connected-mark {
        width: 24px;
        height: 24px;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: var(--setup-green);
        color: #fff;
        font-size: 13px;
        line-height: 1;
        flex: 0 0 auto;
    }

    .setup-extension-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .setup-connect-card h3 {
        margin: 0 0 8px;
        color: var(--setup-ink);
        font-size: 17px;
        font-weight: 900;
    }

    .setup-connect-card p {
        margin: 0 0 16px;
        color: var(--setup-muted);
        line-height: 1.5;
    }

    .setup-status {
        min-height: 22px;
        margin-top: 12px;
        color: var(--setup-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .setup-status.success {
        color: var(--setup-green);
    }

    .setup-status.error {
        color: var(--setup-red);
    }

    .setup-two-factor {
        display: none;
        max-width: 520px;
        margin-top: 14px;
    }

    .setup-two-factor.show {
        display: block;
    }

    .setup-success {
        max-width: 720px;
        margin: 70px auto;
        text-align: center;
    }

    .setup-success-mark {
        width: 70px;
        height: 70px;
        display: grid;
        place-items: center;
        margin: 0 auto 22px;
        border-radius: 999px;
        background: var(--setup-green);
        color: #fff;
        font-size: 34px;
        font-weight: 800;
    }

    .setup-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 22px;
        border-top: 1px solid var(--setup-line);
        background: #fff;
    }

    .setup-shell.workflow-mode .setup-screen[data-step-id="workflow"] {
        padding: 18px;
    }

    .setup-shell.workflow-mode .setup-screen[data-step-id="workflow"] .setup-kicker,
    .setup-shell.workflow-mode .setup-screen[data-step-id="workflow"] .setup-title,
    .setup-shell.workflow-mode .setup-screen[data-step-id="workflow"] .setup-lead {
        display: none;
    }

    .setup-shell.workflow-mode .setup-builder {
        height: 100%;
        min-height: 0;
    }

    .setup-shell.workflow-mode .setup-builder-panel,
    .setup-shell.workflow-mode .setup-inspector,
    .setup-shell.workflow-mode .setup-canvas-wrap {
        min-height: 0;
        overflow: auto;
    }

    .setup-footer-left,
    .setup-footer-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .setup-btn {
        min-height: 42px;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background: #fff;
        color: #1f2937;
        padding: 0 16px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .setup-btn.primary {
        border-color: var(--setup-blue);
        background: var(--setup-blue);
        color: #fff;
    }

    .setup-btn.danger {
        border-color: #f0b3ae;
        color: var(--setup-red);
        background: #fff6f5;
    }

    .setup-btn.soft {
        background: #f4f7fb;
    }

    .setup-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .setup-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .46);
        padding: 22px;
    }

    .setup-modal-backdrop.open {
        display: flex;
    }

    .setup-modal {
        width: min(720px, 100%);
        max-height: calc(100vh - 44px);
        overflow: auto;
        border: 1px solid var(--setup-line);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 28px 70px rgba(15, 23, 42, .28);
        padding: 20px;
    }

    .setup-modal-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .setup-modal h2 {
        margin: 0 0 6px;
        font-size: 22px;
        font-weight: 800;
    }

    .setup-modal p {
        margin: 0;
        color: var(--setup-muted);
        line-height: 1.45;
    }

    .setup-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 14px;
    }

    .setup-hidden {
        display: none !important;
    }

    /* Sample-aligned shell structure */
    .setup-shell {
        grid-template-columns: none;
        grid-template-rows: 98px minmax(0, 1fr) 82px;
        width: min(1220px, calc(100vw - 44px));
        height: min(830px, calc(100vh - 44px));
        height: min(830px, calc(100dvh - 44px));
        border-color: #dde6f2;
        border-radius: 34px;
    }

    .setup-shell.workflow-mode {
        width: calc(100vw - 44px);
        height: calc(100vh - 44px);
        height: calc(100dvh - 44px);
        grid-template-columns: none;
        grid-template-rows: 70px minmax(0, 1fr) 64px;
        border-radius: 28px;
    }

    .setup-shell.workflow-mode .setup-footer {
        height: 64px;
    }

    .setup-head {
        min-height: 0;
        border-bottom: 1px solid #e5ebf3;
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 0 34px;
        overflow: visible;
        background: #fff;
    }

    .setup-head-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
        flex: 0 0 auto;
        font-size: 17px;
        font-weight: 760;
        color: #111827;
    }

    .setup-head-brand img {
        width: 24px;
        height: 24px;
        object-fit: contain;
        flex: 0 0 24px;
    }

    .setup-head-actions {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .setup-language-switcher {
        position: relative;
        display: flex;
        align-items: center;
        flex: 0 0 auto;
    }

    .setup-language-toggle,
    .setup-language-option {
        width: 42px;
        height: 34px;
        border: 1px solid #d7e0ec;
        border-radius: 8px;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(15, 23, 42, .05);
    }

    .setup-language-toggle:hover,
    .setup-language-toggle[aria-expanded="true"],
    .setup-language-option:hover,
    .setup-language-option.is-active {
        border-color: var(--setup-blue);
        background: var(--setup-soft);
    }

    .setup-language-flag {
        width: 28px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 0 0 1px rgba(15, 23, 42, .12);
    }

    .setup-language-flag svg {
        display: block;
        width: 28px;
        height: 20px;
    }

    .setup-language-menu {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        z-index: 20;
        width: 214px;
        display: none;
        grid-template-columns: repeat(4, 42px);
        justify-content: center;
        gap: 8px;
        padding: 10px;
        border: 1px solid #dce5f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 18px 44px rgba(15, 23, 42, .16);
    }

    .setup-language-menu.open {
        display: grid;
    }

    .setup-body {
        display: grid;
        grid-template-columns: 310px minmax(0, 1fr);
        min-height: 0;
    }

    .setup-shell.workflow-mode .setup-body {
        grid-template-columns: minmax(0, 1fr);
    }

    .setup-rail {
        padding: 26px 22px;
        background: linear-gradient(180deg, #fff, #fbfcff);
        overflow-y: auto;
        min-height: 0;
    }

    .setup-rail h3 {
        margin: 0 0 20px;
        color: #7b8498;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .setup-step {
        display: flex;
        align-items: center;
        grid-template-columns: none;
        gap: 13px;
        padding: 12px 10px;
        border-radius: 16px;
        margin-bottom: 6px;
        cursor: pointer;
    }

    .setup-step-num {
        flex: 0 0 28px;
        display: grid;
        place-items: center;
        align-self: center;
        width: 28px;
        height: 28px;
        margin-top: 0;
        border-color: #d7e0ec;
        color: #68748a;
    }

    .setup-step b {
        font-size: 15px;
        color: #2e384e;
    }

    .setup-stage {
        display: block;
        position: relative;
        overflow: hidden;
    }

    .setup-screen-wrap {
        width: 100%;
        height: 100%;
    }

    .setup-screen {
        padding: 34px 42px;
    }

    .setup-field input,
    .setup-field select,
    .setup-field textarea {
        border-radius: 16px;
        padding: 15px 16px;
    }

    .setup-choice {
        border-radius: 20px;
        padding: 20px;
        min-height: 135px;
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease, background .16s ease;
    }

    .setup-choice:hover,
    .setup-choice.selected {
        transform: translateY(-1px);
        box-shadow: 0 18px 46px rgba(31, 122, 255, .10);
    }

    .setup-source-panel,
    .setup-summary,
    .setup-alert {
        border-radius: 22px;
    }

    .setup-builder-panel,
    .setup-inspector {
        border-radius: 22px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .04);
    }

    .setup-canvas-wrap {
        border-radius: 24px;
    }

    .setup-workflow-node {
        border-radius: 20px;
    }

    .setup-footer {
        height: 82px;
        padding: 0 34px;
        position: relative;
        z-index: 5;
    }

    .setup-footer .setup-progress {
        flex: 1;
        margin: 0;
        background: #edf2f7;
    }

    .setup-btn {
        min-height: 44px;
        border-radius: 999px;
        padding: 0 20px;
    }

    @media (max-width: 1200px) {
        .setup-builder {
            grid-template-columns: 1fr;
        }

        .setup-builder-panel,
        .setup-inspector {
            min-height: auto;
        }

        .setup-palette {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .setup-funnel-page {
            padding: 12px;
        }

        .setup-shell {
            width: calc(100vw - 24px);
            height: calc(100vh - 24px);
            height: calc(100dvh - 24px);
            grid-template-rows: 84px minmax(0, 1fr) 74px;
            border-radius: 18px;
        }

        .setup-head {
            padding: 0 18px;
            gap: 12px;
        }

        .setup-head-brand {
            min-width: 0;
        }

        .setup-body {
            grid-template-columns: 1fr;
        }

        .setup-rail {
            display: none;
        }

        .setup-screen {
            padding: 22px;
        }

        .setup-form-grid,
        .setup-choice-grid,
        .setup-connect-grid,
        .setup-review-grid,
        .setup-trial-card {
            grid-template-columns: 1fr;
        }

        .setup-trial-days {
            width: 64px;
            height: 64px;
            font-size: 24px;
        }

        .setup-footer {
            height: 74px;
            padding: 0 18px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .setup-screen,
        .setup-shell {
            transition: none;
        }
    }
</style>
<!-- page-wrapper Start-->
<?php if ($setupRenderAppShell): ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <?php include('partial/topbar.php') ?>
  <div class="page-body-wrapper">
    <?php include('partial/sidebar.php') ?>
    <div class="page-body">
      <?php include('partial/breadcrumb.php') ?>
<?php else: ?>
<div class="setup-public-page">
<?php endif; ?>
      <div class="setup-funnel-page">
        <div class="setup-shell" id="setupShell">
          <header class="setup-head">
            <div class="setup-head-brand">
              <img src="/app/assets/images/logo/logo-icon.png" alt="Coherta" width="35" height="35">
            <span>Første opsætning</span>
            </div>
            <div class="setup-head-actions">
              <a class="setup-btn soft" href="/app/index">Spring over</a>
              <div class="setup-language-switcher" id="setupLanguageSwitcher">
                <button class="setup-language-toggle" id="setupLanguageToggle" type="button" aria-haspopup="true" aria-expanded="false" aria-label="Change language" title="Change language">
                  <span class="setup-language-flag" aria-hidden="true"><?php echo coherta_setup_flag_icon($setupLocale); ?></span>
                </button>
                <div class="setup-language-menu" id="setupLanguageMenu" role="menu" aria-label="Setup language">
                  <?php foreach ($setupLanguageOptions as $languageLocale => $languageOption): ?>
                  <button class="setup-language-option<?php echo $languageLocale === $setupLocale ? " is-active" : ""; ?>" type="button" data-setup-language="<?php echo htmlspecialchars($languageLocale, ENT_QUOTES, 'UTF-8'); ?>" data-country-code="<?php echo htmlspecialchars((string)$languageOption["countryCode"], ENT_QUOTES, 'UTF-8'); ?>" data-html-lang="<?php echo htmlspecialchars((string)$languageOption["htmlLang"], ENT_QUOTES, 'UTF-8'); ?>" role="menuitemradio" aria-checked="<?php echo $languageLocale === $setupLocale ? "true" : "false"; ?>" aria-label="<?php echo htmlspecialchars((string)$languageOption["label"], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars((string)$languageOption["label"], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="setup-language-flag" aria-hidden="true"><?php echo coherta_setup_flag_icon($languageLocale); ?></span>
                  </button>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </header>
          <div class="setup-body">
            <aside class="setup-rail">
              <h3>Setup flow</h3>
              <div class="setup-steps" id="setupSteps"></div>
            </aside>
            <main class="setup-stage">
              <div class="setup-screen-wrap" id="setupScreenWrap">
              <section class="setup-screen active" data-step-id="account-type" data-step-title="Opret din konto" data-step-copy="Google eller e-mail">
                <div class="setup-kicker">Step 1</div>
                <h1 class="setup-title">Opret din konto</h1>
                <p class="setup-lead">Opret med din Google-konto, eller brug navn og e-mail.</p>
                <?php if ($setupSignupBlocked): ?>
                <div class="setup-signup-notice" id="setupSignupNotice" role="status"><?php echo htmlspecialchars($setupTranslations[$setupSignupNotice] ?? $setupSignupNotice, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <div class="setup-source-panel" id="setupIdentityFields">
                  <div class="setup-source-group-title"><?php echo $userLoggedIn ? "Opdater din bruger" : "Opret din konto"; ?></div>
                  <?php if (!$userLoggedIn): ?>
                  <div class="setup-auth-options">
                    <?php if ($setupSignupBlocked): ?>
                    <button class="setup-google-auth" type="button" disabled aria-disabled="true" aria-describedby="setupSignupNotice">
                      <i class="fa fa-google" aria-hidden="true"></i>
                      <span>Opret med Google-konto</span>
                    </button>
                    <?php else: ?>
                    <a class="setup-google-auth" href="<?php echo htmlspecialchars($setupGoogleAuthUrl, ENT_QUOTES, 'UTF-8'); ?>">
                      <i class="fa fa-google" aria-hidden="true"></i>
                      <span>Opret med Google-konto</span>
                    </a>
                    <?php endif; ?>
                    <div class="setup-auth-divider"><span>Eller opret med brugernavn og kodeord</span></div>
                  </div>
                  <?php endif; ?>
                  <div class="setup-form-grid">
                    <div class="setup-field">
                      <label for="setupUserName">Navn</label>
                      <input id="setupUserName" type="text" autocomplete="name" value="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Dit fulde navn">
                    </div>
                    <div class="setup-field">
                      <label for="setupUserEmail">Email</label>
                      <input id="setupUserEmail" type="email" autocomplete="email" value="<?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?>" placeholder="din@email.dk">
                    </div>
                  </div>
                  <p class="setup-status" id="setupIdentityStatus" aria-live="polite"></p>
                </div>
              </section>

              <section class="setup-screen" data-step-id="campaign" data-step-title="Mål" data-step-copy="Campaign type">
                <div class="setup-kicker">Campaign mål</div>
                <h2 class="setup-title">Hvad vil du opnå?</h2>
                <p class="setup-lead">Vælg samme campaign-type som i New Campaign.</p>
                <div class="setup-form-grid">
                  <div class="setup-field full" hidden>
                    <label for="campaignName">Campaign-navn</label>
                    <input id="campaignName" type="text" value="" placeholder="CEO'er i København">
                  </div>
                  <input id="campaignGoal" type="hidden" value="">
                </div>
                <div class="setup-choice-grid setup-playbook-choice-grid" data-choice-group="campaignMode">
                  <button class="setup-choice" type="button" data-value="connections">
                    <h3>More Connections</h3>
                    <p>Grow an employee's LinkedIn network with relevant people.</p>
                  </button>
                  <button class="setup-choice" type="button" data-value="sales">
                    <h3>More Sales</h3>
                    <p>Find, qualify, connect with, and message new sales leads.</p>
                  </button>
                  <button class="setup-choice" type="button" data-value="advanced">
                    <h3>Advanced Setup</h3>
                    <p>Choose a lead source and build the workflow yourself.</p>
                  </button>
                </div>
                <p class="setup-status" id="setupCampaignStatus" aria-live="polite"></p>
              </section>

              <section class="setup-screen" data-step-id="workflow" data-step-title="Audience" data-step-copy="Title & country">
                <div class="setup-kicker">Audience</div>
                <h2 class="setup-title">Define the people you want to reach.</h2>
                <p class="setup-lead">Use the same audience fields and generated campaign preview as New Campaign.</p>
                <div class="setup-form-grid" id="setupAudienceFields"><div class="setup-field"><label for="sourceTitle" id="setupDecisionMakerLabel">Who do you want to connect with?</label><input id="sourceTitle" type="text" placeholder="Example: CFO"></div><div class="setup-field"><label for="sourceCountry">Country</label><select id="sourceCountry"><option value="">Select a country</option><option value="dk">Denmark</option><option value="se">Sweden</option><option value="no">Norway</option><option value="gb">United Kingdom</option><option value="de">Germany</option><option value="nl">Netherlands</option></select></div><div class="setup-field full" id="setupQualificationField" hidden><label for="setupLeadQualification">What qualifies a lead as good?</label><textarea id="setupLeadQualification" placeholder="Describe the company, seniority, responsibilities, or other signals that make someone a good sales lead."></textarea></div></div>
                <div class="setup-source-panel" style="margin-top:18px"><div class="setup-source-group-title">Title of Campaign</div><strong id="setupGeneratedCampaignTitle">Generated automatically</strong><div id="setupCampaignWorkflowPreview" style="margin-top:10px;color:var(--setup-muted)"></div></div>
                <div class="setup-builder" hidden>
                  <aside class="setup-builder-panel">
                    <h3>Elementer</h3>
                    <div class="setup-palette" id="workflowPalette"></div>
                    <div class="setup-inspector-actions">
                      <button class="setup-btn soft" type="button" id="resetWorkflowBtn">Nulstil</button>
                      <button class="setup-btn primary" type="button" id="saveWorkflowBtn">Gem workflow</button>
                    </div>
                  </aside>
                  <div class="setup-canvas-wrap" id="workflowCanvasWrap">
                    <div class="setup-canvas" id="workflowCanvas">
                      <svg class="setup-edges" id="workflowEdges">
                        <defs>
                          <marker id="setupArrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse">
                            <path d="M 0 0 L 10 5 L 0 10 z" fill="#9db5e3"></path>
                          </marker>
                        </defs>
                      </svg>
                      <div id="workflowNodes"></div>
                    </div>
                  </div>
                  <aside class="setup-inspector">
                    <h3>Valgt node</h3>
                    <div class="setup-inspector-body" id="workflowInspector">Vælg en node for at redigere den eller tilføje næste element.</div>
                    <div class="setup-inspector-actions">
                      <button class="setup-btn" type="button" id="editNodeBtn" disabled>Rediger</button>
                      <button class="setup-btn danger" type="button" id="deleteNodeBtn" disabled>Slet</button>
                    </div>
                  </aside>
                </div>
                <p class="setup-status" id="setupWorkflowStatus" aria-live="polite"></p>
              </section>

              <?php if ($paymentRequired): ?>
              <?php if ($setupClearhausTestMode): ?>
              <?php include __DIR__ . '/partial/setup-clearhaus-test.php'; ?>
              <?php else: ?>
              <section class="setup-screen" data-step-id="payment" data-step-title="Aktivering" data-step-copy="Revolut">
                <div class="setup-kicker">Aktivering</div>
                <h2 class="setup-title">Aktiver med Revolut.</h2>
                <?php if ($talentAttractEnabled): ?>
                <p class="setup-lead">Aktiver TalentAttract for 750 kr. nu. Coherta-abonnementet på 999 kr. om måneden starter efter 12 måneder.</p>
                <?php else: ?>
                <p class="setup-lead"><?php echo htmlspecialchars($setupTrialCopy, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <div class="setup-summary">
                  <div class="setup-summary-row"><strong>Pakke</strong><span id="summaryPlan"><?php echo $talentAttractEnabled ? "TalentAttract - 750 kr. nu" : htmlspecialchars((string)$selectedPricingModel["label"], ENT_QUOTES, 'UTF-8'); ?></span></div>
                  <div class="setup-summary-row"><strong>Betaling</strong><span id="summaryBilling"><?php echo $talentAttractEnabled ? "999 kr./md. efter 12 måneder" : ($requestedBilling === "custom" ? "Aftales individuelt" : ($requestedBilling === "yearly" ? "Årlig betaling" : "Månedlig betaling")); ?></span></div>
                </div>
                <div class="setup-alert" style="margin-top:16px;">
                  <?php if ($talentAttractEnabled): ?>
                  Revolut opkræver 750 kr. nu. Det månedlige abonnement på 999 kr. starter først efter 12 måneder.
                  <?php else: ?>
                  Første betaling sker efter prøveperioden.
                  <?php endif; ?>
                </div>
                <div class="setup-payment-trust" aria-label="Tryghed før Revolut">
                  <section class="setup-trial-card">
                    <div class="setup-trial-days" aria-hidden="true"><?php echo $talentAttractEnabled ? '12' : (string)$setupTrialDays; ?></div>
                    <div>
                      <?php if ($talentAttractEnabled): ?>
                      <div class="setup-trial-eyebrow">TalentAttract</div>
                      <h2>12 måneder inkluderet for 750 kr.</h2>
                      <p>Du betaler 750 kr. ved aktivering. Efter 12 måneder fortsætter Coherta til 999 kr. om måneden, indtil abonnementet opsiges.</p>
                      <?php else: ?>
                      <h2>Gratis trial</h2>
                      <p><?php echo htmlspecialchars($setupTrialCopy, ENT_QUOTES, 'UTF-8'); ?></p>
                      <?php endif; ?>
                    </div>
                  </section>
                  <div class="setup-review-grid" aria-label="5-stjernede Coherta reviews">
                    <article class="setup-review-card">
                      <div class="setup-review-stars" aria-label="5 ud af 5 stjerner">&#9733;&#9733;&#9733;&#9733;&#9733;<span>5 ud af 5 stjerner</span></div>
                      <p>Coherta gjorde vores outreach konkret på få minutter. Vi kunne se målgruppen, flowet og næste step uden støj.</p>
                      <strong>Sales lead</strong>
                    </article>
                    <article class="setup-review-card">
                      <div class="setup-review-stars" aria-label="5 ud af 5 stjerner">&#9733;&#9733;&#9733;&#9733;&#9733;<span>5 ud af 5 stjerner</span></div>
                      <p>Det føles som et praktisk salgsworkspace: leads, workflow og opfølgning samlet samme sted.</p>
                      <strong>B2B agency</strong>
                    </article>
                  </div>
                </div>
                <p class="setup-status" id="setupPaymentStatus" aria-live="polite"></p>
              </section>
              <?php endif; ?>
              <?php endif; ?>

              <section class="setup-screen" data-step-id="linkedin" data-step-title="LinkedIn" data-step-copy="Connect">
                <div class="setup-kicker">LinkedIn</div>
                <h2 class="setup-title">Forbind LinkedIn.</h2>
                <p class="setup-lead">Vælg hvordan Coherta skal forbinde LinkedIn. Direkte login er klar nu, og Chrome Extension kommer snart.</p>
                <div class="setup-connect-grid">
                  <div class="setup-connect-card" id="setupLinkedInLoginCard">
                    <h3>Forbind via LinkedIn email/adgangskode</h3>
                    <div class="setup-connected-state" id="setupLinkedInLoginConnectedState" hidden>
                      <span class="setup-connected-mark">✓</span>
                      <span>LinkedIn er forbundet</span>
                    </div>
                    <p>Brug din LinkedIn email sammen med adgangskoden til at forbinde kontoen direkte fra Coherta.</p>
                    <p class="setup-status error" id="setupLinkedInError" role="alert" hidden></p>
                    <div class="setup-form-grid">
                      <div class="setup-field">
                        <label for="setupLinkedInUsername">LinkedIn email</label>
                        <input id="setupLinkedInUsername" type="email" autocomplete="username" value="<?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="setup-field">
                        <label for="setupLinkedInPassword">LinkedIn adgangskode</label>
                        <input id="setupLinkedInPassword" type="password" autocomplete="current-password">
                      </div>
                    </div>
                    <button class="setup-btn primary" type="button" id="setupLinkedInLoginBtn">Forbind LinkedIn</button>
                    <div class="setup-two-factor" id="setupLinkedInTwoFactor">
                      <div class="setup-field">
                        <label for="setupLinkedInCode">SMS / email kode</label>
                        <input id="setupLinkedInCode" type="text" inputmode="numeric" autocomplete="one-time-code">
                      </div>
                      <button class="setup-btn" type="button" id="setupLinkedInCodeBtn">Send kode</button>
                    </div>
                    <p class="setup-status" id="setupLinkedInStatus" aria-live="polite"></p>
                  </div>
                  <div class="setup-connect-card coming-soon disabled" id="setupChromePluginCard" aria-disabled="true">
                    <div class="setup-card-title-row">
                      <h3>Forbind via Chrome Extension</h3>
                      <span class="setup-coming-soon-badge">Coming soon</span>
                    </div>
                    <div class="setup-connected-state" id="setupChromePluginConnectedState" hidden>
                      <span class="setup-connected-mark">✓</span>
                      <span>LinkedIn er forbundet</span>
                    </div>
                    <p>Chrome Extension-forbindelsen kommer snart. Brug LinkedIn email/adgangskode ovenfor indtil den er klar.</p>
                    <div class="setup-extension-actions">
                      <button class="setup-btn soft" id="cohertaPluginLogin" type="button" hidden>Log plugin ind</button>
                      <button class="setup-btn primary" id="cohertaLinkedInConnect" type="button" hidden>Forbind LinkedIn</button>
                      <button class="setup-btn primary" type="button" id="setupChromePluginBtn" disabled aria-disabled="true">Coming soon</button>
                    </div>
                    <p class="setup-status" id="cohertaPluginStatus" aria-live="polite"></p>
                  </div>
                </div>
              </section>

              <section class="setup-screen" data-step-id="ai-tool" data-step-title="AI" data-step-copy="Værktøj">
                <div class="setup-kicker">AI workflow</div>
                <h2 class="setup-title">Bruger du ChatGPT eller Claude til dagligt?</h2>
                <p class="setup-lead">Vælg det værktøj, du primært bruger, så resten af setup-flowet kan tage højde for din hverdag.</p>
                <div class="setup-choice-grid setup-source-choice-grid" data-choice-group="aiTool">
                  <button class="setup-choice" type="button" data-value="chatgpt">
                    <h3>ChatGPT</h3>
                    <p>Jeg bruger ChatGPT som mit primære AI-værktøj.</p>
                  </button>
                  <button class="setup-choice" type="button" data-value="claude">
                    <h3>Claude</h3>
                    <p>Jeg bruger Claude som mit primære AI-værktøj.</p>
                  </button>
                  <button class="setup-choice" type="button" data-value="none">
                    <h3>Intet</h3>
                    <p>Jeg bruger ikke et AI-værktøj fast i hverdagen.</p>
                  </button>
                </div>
                <p class="setup-status" id="setupAiToolStatus" aria-live="polite"></p>
              </section>

              <section class="setup-screen" data-step-id="done" data-step-title="Klar" data-step-copy="Færdig">
                <div class="setup-success">
                  <div class="setup-success-mark">✓</div>
                  <div class="setup-kicker">Setup gennemført</div>
                  <h2 class="setup-title">Kampagnen er klar.</h2>
                  <p class="setup-lead" id="doneLead">Workflowet er sat op, og campaignen er klar til at blive brugt.</p>
                  <a class="setup-btn primary" href="/app/login">Gå til forsiden</a>
                </div>
              </section>
              </div>
            </main>
          </div>
          <footer class="setup-footer">
            <button class="setup-btn" type="button" id="setupBackBtn">Tilbage</button>
            <div class="setup-progress" aria-hidden="true"><span id="setupProgressBar"></span></div>
            <button class="setup-btn primary" type="button" id="setupNextBtn"<?php if ($setupSignupBlocked): ?> disabled aria-disabled="true" aria-describedby="setupSignupNotice"<?php endif; ?>><?php echo $setupSignupBlocked ? htmlspecialchars($setupTranslations['Opret din konto'] ?? 'Opret din konto', ENT_QUOTES, 'UTF-8') : 'Fortsæt'; ?></button>
          </footer>
        </div>
      </div>
<?php if ($setupRenderAppShell): ?>
      <?php include('partial/footer.php') ?>
    </div>
  </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<div class="setup-modal-backdrop" id="workflowModalBackdrop">
  <div class="setup-modal" role="dialog" aria-modal="true" aria-labelledby="workflowModalTitle">
    <div class="setup-modal-head">
      <div>
        <h2 id="workflowModalTitle">Rediger node</h2>
        <p id="workflowModalCopy">Opdater indstillingerne for den valgte node.</p>
      </div>
      <button class="setup-btn" type="button" id="closeWorkflowModal">Luk</button>
    </div>
    <div id="workflowModalBody"></div>
    <div class="setup-modal-actions">
      <button class="setup-btn" type="button" id="cancelWorkflowModal">Annuller</button>
      <button class="setup-btn primary" type="button" id="saveWorkflowModal">Gem</button>
    </div>
  </div>
</div>

<?php
// Keep the funnel usable when the optional approval dialog has not been deployed yet.
if (is_file(__DIR__ . '/partial/linkedin-approval.php')
    && is_file(__DIR__ . '/assets/js/linkedin-approval.js')
    && is_file(__DIR__ . '/assets/css/linkedin-approval.css')) {
    require_once __DIR__ . '/partial/linkedin-approval.php';
}
?>
<?php if ($setupRenderAppShell) include('partial/scripts.php') ?>
<?php if ($setupClearhausTestMode): ?>
<link rel="stylesheet" href="/app/assets/css/setup-clearhaus-test.css?v=2">
<script src="/app/assets/js/setup-clearhaus-test.js?v=4"></script>
<?php endif; ?>
<script>
    window.COHERTA_SETUP_FUNNEL = <?php echo json_encode($setupConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>
<script>
    (function () {
        document.body.classList.add('setup-funnel-open');
        const loaderWrapper = document.querySelector('.loader-wrapper');
        if (loaderWrapper) loaderWrapper.remove();
        const config = window.COHERTA_SETUP_FUNNEL || {};
        const signupBlocked = config.signupBlocked === true;
        const clearhausTestMode = config.clearhausTestMode === true;
        const paymentEnabled = config.paymentEnabled === true;
        const talentAttractConfig = config.talentAttract && typeof config.talentAttract === 'object' ? config.talentAttract : {};
        const talentAttractEnabled = talentAttractConfig.enabled === true || talentAttractConfig.enabled === 1
            || talentAttractConfig.enabled === '1' || talentAttractConfig.enabled === 'true';
        const setupTranslations = config.translations && typeof config.translations === 'object' ? config.translations : {};
        const setupLocale = String(config.locale || 'da').trim().toLowerCase();
        const setupIntlLocales = {da: 'da-DK', de: 'de-DE', es: 'es-ES', fi: 'fi-FI', fr: 'fr-FR', hr: 'hr-HR', is: 'is-IS', no: 'nb-NO', pl: 'pl-PL', se: 'sv-SE', uk: 'en-GB'};
        const setupIntlLocale = setupIntlLocales[setupLocale] || 'da-DK';
        const setupTranslationAttrs = ['placeholder', 'title', 'aria-label', 'data-step-title', 'data-step-copy'];
        const setupTranslationSkipTags = new Set(['SCRIPT', 'STYLE', 'TEXTAREA', 'INPUT', 'SELECT']);

        function translateSetupText(value, vars) {
            const original = String(value ?? '');
            if (!original) return original;
            const leading = original.match(/^\s*/)?.[0] || '';
            const trailing = original.match(/\s*$/)?.[0] || '';
            const key = original.trim();
            let translated = setupTranslations[key] || key;
            if (vars && typeof vars === 'object') {
                Object.entries(vars).forEach(([name, replacement]) => {
                    translated = translated.replaceAll('{' + name + '}', String(replacement));
                });
            }
            return leading + translated + trailing;
        }

        function localizeSetupText(root) {
            if (!root || !Object.keys(setupTranslations).length) return;
            const rootNode = root.nodeType ? root : document;
            const translateAttributes = (element) => {
                if (!element || element.nodeType !== 1) return;
                setupTranslationAttrs.forEach((attr) => {
                    if (!element.hasAttribute(attr)) return;
                    const current = element.getAttribute(attr);
                    const translated = translateSetupText(current);
                    if (translated !== current) element.setAttribute(attr, translated);
                });
            };

            if (rootNode.nodeType === 1) translateAttributes(rootNode);
            const queryRoot = rootNode.nodeType === 9 ? rootNode : (rootNode.querySelectorAll ? rootNode : null);
            if (queryRoot) {
                queryRoot.querySelectorAll(setupTranslationAttrs.map((attr) => '[' + attr + ']').join(',')).forEach(translateAttributes);
            }

            const walkerRoot = rootNode.nodeType === 9 ? rootNode.body : rootNode;
            if (!walkerRoot) return;
            const walker = document.createTreeWalker(walkerRoot, NodeFilter.SHOW_TEXT, {
                acceptNode(node) {
                    const parent = node.parentElement;
                    if (!parent || setupTranslationSkipTags.has(parent.tagName)) return NodeFilter.FILTER_REJECT;
                    return node.nodeValue.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                }
            });
            const nodes = [];
            while (walker.nextNode()) nodes.push(walker.currentNode);
            nodes.forEach((node) => {
                const translated = translateSetupText(node.nodeValue);
                if (translated !== node.nodeValue) node.nodeValue = translated;
            });
        }

        function setLocalizedText(element, text) {
            if (!element) return;
            element.textContent = translateSetupText(text || '');
        }

        const screens = Array.from(document.querySelectorAll('.setup-screen'));
        const stepsWrap = document.getElementById('setupSteps');
        const progressBar = document.getElementById('setupProgressBar');
        const backBtn = document.getElementById('setupBackBtn');
        const nextBtn = document.getElementById('setupNextBtn');
        const shell = document.getElementById('setupShell');
        const sourcePanel = document.getElementById('sourcePanel');
        const campaignName = document.getElementById('campaignName');
        const campaignGoal = document.getElementById('campaignGoal');
        const setupEmployeeQuestion = document.getElementById('setupEmployeeQuestion');
        const setupDecisionMakerLabel = document.getElementById('setupDecisionMakerLabel');
        const setupQualificationField = document.getElementById('setupQualificationField');
        const setupGeneratedCampaignTitle = document.getElementById('setupGeneratedCampaignTitle');
        const setupCampaignWorkflowPreview = document.getElementById('setupCampaignWorkflowPreview');
        const campaignStatus = document.getElementById('setupCampaignStatus');
        const sourceStatus = document.getElementById('setupSourceStatus');
        const workflowStatus = document.getElementById('setupWorkflowStatus');
        const paymentStatus = document.getElementById('setupPaymentStatus');
        const clearhausAcceptTerms = document.getElementById('clearhausAcceptTerms');
        const doneLead = document.getElementById('doneLead');
        const setupIdentityFields = document.getElementById('setupIdentityFields');
        const setupUserName = document.getElementById('setupUserName');
        const setupUserEmail = document.getElementById('setupUserEmail');
        const setupIdentityStatus = document.getElementById('setupIdentityStatus');
        const directLinkedInCard = document.getElementById('setupLinkedInLoginCard');
        const linkedInUsername = document.getElementById('setupLinkedInUsername');
        const linkedInPassword = document.getElementById('setupLinkedInPassword');
        const directLinkedInLoginBtn = document.getElementById('setupLinkedInLoginBtn');
        const linkedInCode = document.getElementById('setupLinkedInCode');
        const linkedInCodeBtn = document.getElementById('setupLinkedInCodeBtn');
        const linkedInTwoFactor = document.getElementById('setupLinkedInTwoFactor');
        const linkedInStatus = document.getElementById('setupLinkedInStatus');
        const linkedInError = document.getElementById('setupLinkedInError');
        const linkedInApproval = window.CohertaLinkedInApproval || {
            update() {},
            messages: {
                invalidCredentials: 'LinkedIn could not verify your username or password. Check your details and try again.',
                approvalTimeout: "You didn't approve the request within the timeframe. Please try again"
            }
        };
        const directLinkedInConnectedState = document.getElementById('setupLinkedInLoginConnectedState');
        const pluginLoginBtn = document.getElementById('cohertaPluginLogin');
        const extensionLinkedInConnectBtn = document.getElementById('cohertaLinkedInConnect');
        const pluginStatus = document.getElementById('cohertaPluginStatus');
        const chromePluginConnectedState = document.getElementById('setupChromePluginConnectedState');
        const chromePluginBtn = document.getElementById('setupChromePluginBtn');
        const chromePluginCard = document.getElementById('setupChromePluginCard');
        const chromePluginStatus = pluginStatus;
        const aiToolStatus = document.getElementById('setupAiToolStatus');
        const setupLanguageSwitcher = document.getElementById('setupLanguageSwitcher');
        const setupLanguageToggle = document.getElementById('setupLanguageToggle');
        const setupLanguageMenu = document.getElementById('setupLanguageMenu');
        const SETUP_PROGRESS_STORAGE = clearhausTestMode || talentAttractEnabled ? window.sessionStorage : window.localStorage;
        const SETUP_PROGRESS_KEY = clearhausTestMode
            ? 'coherta_setup_funnel_progress_clearhaus_test'
            : (talentAttractEnabled ? 'coherta_setup_funnel_progress_talent_attract' : 'coherta_setup_funnel_progress');
        const SETUP_DRAFT_KEY = clearhausTestMode ? 'coherta_setup_funnel_draft_clearhaus_test' : 'coherta_setup_funnel_draft';
        let bearerToken = document.body.getAttribute('session_id') || config.sessionId || '';
        if (bearerToken) {
            try {
                window.localStorage.setItem('session_id', bearerToken);
                window.localStorage.setItem('access_token', bearerToken);
                window.localStorage.setItem('coherta_auth_session_id', bearerToken);
                window.sessionStorage.setItem('access_token', bearerToken);
            } catch (error) {}
        }
        const LINKEDIN_CONNECT_ENDPOINT = 'https://api.coherta.com/apps/linkedin/connect';
        const LINKEDIN_SMS_ENDPOINT = 'https://api.coherta.com/apps/linkedin/connect/sms';
        const EXTENSION_BRIDGE_CHANNEL = 'COHERTA_EXTENSION_BRIDGE';
        const EXTENSION_PAGE_SOURCE = 'coherta-website';
        const EXTENSION_SOURCE = 'coherta-extension';
        const CHROME_EXTENSION_COMING_SOON = true;
        let extensionBridgeSeq = 0;
        const extensionBridgePending = new Map();
        let activeIndex = 0;
        let transitionTimer = null;
        let chromePluginCheckInProgress = false;
        let linkedInPolling = false;
        let storedSetupProgress = {};

        try {
            storedSetupProgress = JSON.parse(SETUP_PROGRESS_STORAGE.getItem(SETUP_PROGRESS_KEY) || '{}') || {};
        } catch (error) {
            storedSetupProgress = {};
        }

        {
            const configUserId = Number(config.userId || 0);
            const storedUserId = Number(storedSetupProgress.userId || 0);
            const configEmail = String(config.userEmail || '').trim().toLowerCase();
            const storedEmail = String(storedSetupProgress.userEmail || '').trim().toLowerCase();
            const identityMismatch =
                (configUserId > 0 && storedUserId > 0 && configUserId !== storedUserId)
                || (configEmail && storedEmail && configEmail !== storedEmail);
            if (identityMismatch) {
                storedSetupProgress = {};
                try { SETUP_PROGRESS_STORAGE.removeItem(SETUP_PROGRESS_KEY); } catch (error) {}
            }
            storedSetupProgress.paymentConfirmed = false;
            if (talentAttractEnabled) {
                try { window.localStorage.removeItem(SETUP_PROGRESS_KEY); } catch (error) {}
            }
        }

        function truthy(value) {
            return value === true || value === 1 || value === '1' || value === 'true';
        }

        function getAffiliateAttribution() {
            const queryPartnerId = <?php echo json_encode($setupAffiliateId); ?>;
            try {
                const partnerId = queryPartnerId || String(window.localStorage.getItem('affiliate') || '').trim();
                const transactionId = String(window.localStorage.getItem('transaction_id') || '').trim();
                const bo_affiliateId = String(window.localStorage.getItem('bo_affiliate') || '').trim();
                return {partnerId: partnerId, transactionId: transactionId, bo_affiliateId: bo_affiliateId};
            } catch (error) {
                return {partnerId: queryPartnerId, transactionId: '', bo_affiliateId: ''};
            }
        }

        const configChromePluginAuthenticated = truthy(config.chromePluginAuthenticated);
        const configChromePluginInstalled = truthy(config.chromePluginInstalled) || configChromePluginAuthenticated;
        const pricingRequestPresent = truthy(config.pricingRequestPresent);
        const pricingModels = config.pricingModels || {};
        const prefilledAccountType = String(config.prefilledAccountType || '').trim();
        const accountTypeLockedToCompany = truthy(config.accountTypeLockedToCompany);

        function normalizeBillingCycle(value) {
            const clean = String(value || '').trim().toLowerCase();
            if (clean === 'custom') return 'custom';
            return ['yearly', 'annual', 'annually', 'year', 'year_1', 'aarlig', 'årlig'].includes(clean) ? 'yearly' : 'monthly';
        }

        function normalizePricingPlan(value) {
            const clean = String(value || '').trim().toLowerCase();
            if (['personal', 'private', 'person', 'individual', 'privat', 'privatperson'].includes(clean)) return 'personal';
            if (['extended', 'scale', 'scaling', 'pro', 'premium'].includes(clean)) return 'extended';
            if (['custom', 'enterprise', 'special'].includes(clean)) return 'custom';
            return 'standard';
        }

        function billingPeriodKey(value) {
            if (normalizeBillingCycle(value) === 'custom') return '';
            return normalizeBillingCycle(value) === 'yearly' ? 'year_1' : 'month_1';
        }

        function getPricingModel(plan) {
            const planKey = normalizePricingPlan(plan);
            const selectedModel = config.selectedPricingModel || null;
            const selectedModelKey = selectedModel ? normalizePricingPlan(selectedModel.key || selectedModel.plan || selectedModel.pricing_model || '') : '';
            if (pricingModels[planKey]) return pricingModels[planKey];
            if (selectedModel && selectedModelKey === planKey) return selectedModel;
            return {
                key: planKey,
                label: planKey === 'personal' ? 'Coherta Privatperson' : (planKey === 'extended' ? 'Coherta Extended' : (planKey === 'custom' ? 'Coherta Custom' : 'Coherta Standard')),
                monthlyPrice: planKey === 'personal' ? 299 : (planKey === 'extended' ? 4198 : (planKey === 'custom' ? 0 : 999)),
                annualMonthlyPrice: planKey === 'personal' ? 299 : (planKey === 'extended' ? 4198 : (planKey === 'custom' ? 0 : 999)),
                currency: 'DKK',
                stripeProductKey: planKey === 'custom' ? '' : 'Tj6iHmP3XyIEdS',
                stripeProductId: planKey === 'custom' ? '' : 'prod_UWswNDxveWxoUo',
                stripePriceId: planKey === 'custom' ? '' : 'price_1TXpB3LliR0gAZeqpwEpkUCN'
            };
        }

        function getBillingMonthlyPrice(model, billing) {
            const cleanBilling = normalizeBillingCycle(billing);
            if (cleanBilling === 'yearly' && Number(model?.annualMonthlyPrice || 0) > 0) {
                return Number(model.annualMonthlyPrice);
            }
            return Number(model?.monthlyPrice || 0);
        }

        // Use the current link's plan, including Standard for the plain setup URL.
        const initialPricingPlan = normalizePricingPlan(config.selectedPlan || 'standard');
        const initialBillingCycle = normalizeBillingCycle(config.selectedBilling || 'monthly');

        function buildPaymentActivationUrl(plan, billing) {
            const planKey = normalizePricingPlan(plan);
            if (planKey === 'custom') return '/da/kontakt-os.php?source=setup-funnel&plan=custom';
            return '#payment-checkout';
        }

        function isRevolutCheckoutUrl(value) {
            try {
                const url = new URL(value);
                return url.protocol === 'https:' && !url.username && !url.password
                    && (url.hostname === 'revolut.com' || url.hostname.endsWith('.revolut.com'));
            } catch (error) {
                return false;
            }
        }

        const initialPaymentActivationUrl = pricingRequestPresent || storedSetupProgress.pricingPlan
            ? buildPaymentActivationUrl(initialPricingPlan, initialBillingCycle)
            : (config.paymentActivationUrl || buildPaymentActivationUrl(initialPricingPlan, initialBillingCycle));

        const state = {
            campaignMode: storedSetupProgress.campaignMode || '',
            playbook: storedSetupProgress.playbook || 'network',
            accountType: 'business',
            pricingPlan: initialPricingPlan,
            businessPricingPlan: initialPricingPlan === 'personal' ? 'standard' : initialPricingPlan,
            billingCycle: initialBillingCycle,
            billingPeriodKey: billingPeriodKey(initialBillingCycle),
            pricingModel: getPricingModel(initialPricingPlan),
            paymentActivationUrl: initialPaymentActivationUrl,
            source: storedSetupProgress.source || 'url',
            workflowSaved: false,
            linkedinConnected: !!config.linkedinConnected,
            linkedinStatus: config.linkedinConnected ? 'connected' : 'idle',
            chromePluginInstalled: configChromePluginInstalled,
            chromePluginAuthenticated: configChromePluginAuthenticated,
            aiTool: storedSetupProgress.aiTool || '',
            userName: storedSetupProgress.userName || config.fullName || '',
            userEmail: storedSetupProgress.userEmail || config.userEmail || '',
            userId: Number(storedSetupProgress.userId || config.userId || 0),
            userSaved: !!config.userId || !!storedSetupProgress.userSaved,
            userSaving: false,
            campaignId: Number(storedSetupProgress.campaignId || 0),
            campaignSaving: false,
            companySaving: false,
            // Browser storage and return parameters never prove payment.
            paymentConfirmed: !!config.hasActiveSubscription,
            clearhausTestPayment: clearhausTestMode ? storedSetupProgress.clearhausTestPayment || null : null,
            paymentPolling: false,
            checkoutOpening: false,
            paymentCheckoutWindow: null,
            pendingPaymentCheckout: paymentEnabled && !!storedSetupProgress.pendingPaymentCheckout,
            pendingPaymentTargetIndex: paymentEnabled ? (Number(storedSetupProgress.pendingPaymentTargetIndex || 0) || null) : null,
            csvItemCount: Number(storedSetupProgress.csvItemCount || 0),
            csvLinkedInUrlCount: Number(storedSetupProgress.csvLinkedInUrlCount || 0),
            csvLinkedInUrls: [],
            csvFileName: storedSetupProgress.csvFileName || ''
        };

        function isPersonalAccountType(accountType) {
            return String(accountType || '').trim().toLowerCase() === 'personal';
        }

        function applyAccountTypePricingRules() {
            if (talentAttractEnabled) {
                state.accountType = 'business';
                state.pricingPlan = 'standard';
                state.businessPricingPlan = 'standard';
                state.billingCycle = 'monthly';
                state.billingPeriodKey = billingPeriodKey('monthly');
                state.pricingModel = getPricingModel('standard');
                state.paymentActivationUrl = buildPaymentActivationUrl('standard', 'monthly');
                return;
            }

            if (isPersonalAccountType(state.accountType)) {
                const currentPlan = normalizePricingPlan(state.pricingPlan);
                if (currentPlan !== 'personal') state.businessPricingPlan = currentPlan;
                state.pricingPlan = 'personal';
                state.billingCycle = 'monthly';
            } else {
                let nextPlan = normalizePricingPlan(state.pricingPlan);
                if (nextPlan === 'personal') {
                    nextPlan = normalizePricingPlan(state.businessPricingPlan || config.selectedPlan || storedSetupProgress.pricingPlan || 'standard');
                    if (nextPlan === 'personal') nextPlan = 'standard';
                }
                state.pricingPlan = nextPlan;
                state.businessPricingPlan = nextPlan === 'personal' ? 'standard' : nextPlan;
                state.billingCycle = normalizeBillingCycle(state.billingCycle);
                if (state.pricingPlan === 'custom') state.billingCycle = 'custom';
                if (state.pricingPlan !== 'custom' && state.billingCycle === 'custom') state.billingCycle = 'monthly';
            }
            state.billingPeriodKey = billingPeriodKey(state.billingCycle);
            state.pricingModel = getPricingModel(state.pricingPlan);
            state.paymentActivationUrl = buildPaymentActivationUrl(state.pricingPlan, state.billingCycle);
        }

        applyAccountTypePricingRules();

        const CHROME_PLUGIN_READY_TYPES = new Set([
            'COHERTA_PLUGIN_READY',
            'COHERTA_PLUGIN_PONG',
            'COHERTA_CHROME_PLUGIN_READY',
            'COHERTA_CHROME_PLUGIN_PONG',
            'coherta-plugin-ready',
            'coherta-plugin-pong',
            'coherta:plugin:ready',
            'coherta:plugin:pong'
        ]);

        const sourceLabels = {
            url: 'LinkedIn Search URL',
            csv: 'CSV upload',
            manual: 'Manuel liste'
        };

        const SETUP_COUNTRY_CODES = 'AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR BS BT BV BW BY BZ CA CC CD CF CG CH CI CK CL CM CN CO CR CU CV CW CX CY CZ DE DJ DK DM DO DZ EC EE EG EH ER ES ET FI FJ FK FM FO FR GA GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT GU GW GY HK HM HN HR HT HU ID IE IL IM IN IO IQ IR IS IT JE JM JO JP KE KG KH KI KM KN KP KR KW KY KZ LA LB LC LI LK LR LS LT LU LV LY MA MC MD ME MF MG MH MK ML MM MN MO MP MQ MR MS MT MU MV MW MX MY MZ NA NC NE NF NG NI NL NO NP NR NU NZ OM PA PE PF PG PH PK PL PM PN PR PS PT PW PY QA RE RO RS RU RW SA SB SC SD SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX SY SZ TC TD TF TG TH TJ TK TL TM TN TO TR TT TV TW TZ UA UG UM US UY UZ VA VC VE VG VI VN VU WF WS YE YT ZA ZM ZW'.split(' ');

        function populateCountrySelect(select) {
            if (!select) return;
            const selectedValue = String(select.value || '').trim().toLowerCase();
            const placeholder = select.dataset.placeholder || select.options[0]?.textContent || 'Select a country';
            const locale = setupLocale || document.documentElement.lang || 'en';
            let displayNames = null;
            try {
                displayNames = new Intl.DisplayNames([locale], {type: 'region'});
            } catch (error) {}

            const countries = SETUP_COUNTRY_CODES.map((code) => ({
                code: code.toLowerCase(),
                name: displayNames?.of(code) || code
            })).sort((a, b) => a.name.localeCompare(b.name, locale, {sensitivity: 'base'}));

            select.replaceChildren(
                new Option(placeholder, ''),
                ...countries.map((country) => new Option(country.name, country.code))
            );
            if (selectedValue && countries.some((country) => country.code === selectedValue)) {
                select.value = selectedValue;
            }
        }

        function setupCountryFullName(countryCode) {
            const code = String(countryCode || '').trim().toUpperCase();
            if (!code) return '';
            try {
                return new Intl.DisplayNames(['en'], {type: 'region'}).of(code) || code;
            } catch (error) {
                return code;
            }
        }

        populateCountrySelect(document.getElementById('sourceCountry'));

        const sourcePanels = {
            url: '<div class="setup-source-group-title">Titel og Land</div><div class="setup-form-grid"><div class="setup-field"><label for="sourceTitle">Titel</label><input id="sourceTitle" type="text" placeholder="CEO, Founder eller Head of Sales"></div><div class="setup-field"><label for="sourceCountry">Land</label><select id="sourceCountry"><option value="">Vælg land</option></select></div></div><div class="setup-source-mode-divider"><hr><span>ELLER</span><hr></div><div class="setup-form-grid"><div class="setup-field full"><label for="sourceUrl">Søge-URL</label><input id="sourceUrl" type="url" placeholder="https://www.linkedin.com/search/results/people/..."></div></div>',
            csv: '<div class="setup-form-grid"><div class="setup-field full"><label for="sourceCsv">CSV-fil</label><input id="sourceCsv" type="file" accept=".csv,text/csv"></div></div><p class="setup-status" id="setupCsvStatus" aria-live="polite">Upload en CSV-fil, så viser vi hvor mange emner der lægges ind i kampagnen.</p>',
            manual: '<div class="setup-form-grid"><div class="setup-field full"><label for="manualLeads">Leads</label><textarea id="manualLeads" placeholder="Indsæt en lead pr. linje"></textarea></div></div>'
        };

        function renderSteps() {
            stepsWrap.innerHTML = screens.map((screen, index) => {
                const title = screen.dataset.stepTitle || ('Step ' + (index + 1));
                const copy = screen.dataset.stepCopy || '';
                return '<button class="setup-step" type="button" data-step-index="' + index + '"><span class="setup-step-num">' + (index + 1) + '</span><span><b>' + escapeHtml(title) + '</b><span>' + escapeHtml(copy) + '</span></span></button>';
            }).join('');
            localizeSetupText(stepsWrap);
            stepsWrap.querySelectorAll('.setup-step').forEach((button) => {
                button.addEventListener('click', () => goToStep(Number(button.dataset.stepIndex)));
            });
        }

        function applyAccountTypeLock() {
            if (!accountTypeLockedToCompany) return;

            state.accountType = 'business';
            applyAccountTypePricingRules();
            const accountTypeGroup = document.querySelector('[data-choice-group="accountType"]');
            if (!accountTypeGroup) return;

            const personalChoice = accountTypeGroup.querySelector('.setup-choice[data-value="personal"]');
            const businessChoice = accountTypeGroup.querySelector('.setup-choice[data-value="business"]');
            if (personalChoice) {
                personalChoice.disabled = true;
                personalChoice.setAttribute('aria-disabled', 'true');
                personalChoice.classList.add('is-disabled');
                personalChoice.classList.remove('selected');
            }
            if (businessChoice) {
                businessChoice.disabled = false;
                businessChoice.classList.add('selected');
            }
        }

        function findStepIndexById(stepId) {
            return screens.findIndex((screen) => screen.dataset.stepId === stepId);
        }

        function paymentStepCanContinue() {
            if (clearhausTestMode) {
                return window.CohertaClearhausTest.isApproved(getClearhausTestOffer(), state.clearhausTestPayment);
            }
            return !paymentEnabled || !config.paymentRequired || !!config.hasActiveSubscription || !!state.paymentConfirmed;
        }

        function beginPaymentActivation(targetIndex) {
            if (clearhausTestMode) {
                approveClearhausTestPayment(Number.isInteger(targetIndex) ? targetIndex : activeIndex + 1);
                return;
            }
            openPaymentCheckout(null, {
                openInNewWindow: true,
                continueAfterPayment: true,
                targetIndex: Number.isInteger(targetIndex) ? targetIndex : activeIndex + 1
            });
        }

        function updateStepChrome() {
            if (clearhausTestMode) updateSummary();
            stepsWrap.querySelectorAll('.setup-step').forEach((step, stepIndex) => {
                const done = stepIndex < activeIndex;
                const number = step.querySelector('.setup-step-num');
                step.classList.toggle('active', stepIndex === activeIndex);
                step.classList.toggle('done', done);
                if (number) number.textContent = done ? '✓' : String(stepIndex + 1);
            });
            const progress = screens.length > 0 ? ((activeIndex + 1) / screens.length) * 100 : 100;
            progressBar.style.width = progress + '%';
            backBtn.disabled = activeIndex === 0;
            backBtn.classList.toggle('setup-hidden', activeIndex === 0);
            const activeStepId = screens[activeIndex].dataset.stepId;
            const isWorkflowStep = false;
            const needsPaymentActivation = activeStepId === 'payment' && !paymentStepCanContinue();
            const needsRequiredWorkflowEdit = false;
            const needsAccountDetails = activeStepId === 'account-type' && !accountStepCanContinue();
            const needsCampaignChoice = activeStepId === 'campaign' && !state.campaignMode;
            const needsCampaignCreation = activeStepId === 'workflow' && !state.campaignId;
            const stepIsSaving = state.campaignSaving || state.companySaving || state.checkoutOpening || state.paymentPolling;
            nextBtn.disabled = needsAccountDetails || needsCampaignChoice || stepIsSaving;
            const nextLabel = needsRequiredWorkflowEdit
                ? 'Ret besked først'
                : (needsPaymentActivation
                    ? (state.paymentPolling ? 'Venter på betaling...' : (state.checkoutOpening ? 'Åbner Revolut...' : 'Fortsæt med Revolut'))
                    : (stepIsSaving ? 'Gemmer...' : (needsAccountDetails ? (state.accountType ? 'Udfyld navn og email' : 'Vælg en mulighed') : (needsCampaignCreation ? 'Opret campaign' : (activeIndex === screens.length - 1 ? 'Færdig' : 'Fortsæt')))));
            nextBtn.textContent = translateSetupText(nextLabel);
            if (signupBlocked && activeStepId === 'account-type') {
                nextBtn.disabled = true;
                nextBtn.textContent = translateSetupText('Opret din konto');
            }
            if (clearhausTestMode && needsPaymentActivation) {
                nextBtn.textContent = 'Godkend betaling som test';
                nextBtn.disabled = stepIsSaving || !getClearhausTestOffer().valid || !clearhausAcceptTerms?.checked;
            }
            shell.classList.toggle('workflow-mode', isWorkflowStep);
            if (!clearhausTestMode) updateSummary();
        }

        function goToStep(index) {
            if (signupBlocked && index > 0) return;
            let nextIndex = Math.max(0, Math.min(index, screens.length - 1));
            if (nextIndex > activeIndex && screens[activeIndex]?.dataset.stepId === 'account-type') {
                if (!accountStepCanContinue()) {
                    updateStepChrome();
                    return;
                }
                if (!state.userSaved || !currentUserId()) {
                    setIdentityStatus('Klik Fortsæt for at oprette brugeren før du går videre.', 'error');
                    updateStepChrome();
                    return;
                }
            }
            const campaignIndex = findStepIndexById('workflow');
            if (nextIndex > activeIndex && campaignIndex >= 0 && activeIndex <= campaignIndex && nextIndex > campaignIndex && !state.campaignId) {
                if (activeIndex === campaignIndex) {
                    setCampaignStatus('Klik Fortsæt for at oprette campaignen før du går videre.', 'error');
                    updateStepChrome();
                    return;
                }
                nextIndex = campaignIndex;
            }
            const paymentIndex = findStepIndexById('payment');
            if (nextIndex > activeIndex && paymentIndex >= 0 && !paymentStepCanContinue()) {
                if (activeIndex === paymentIndex && nextIndex > paymentIndex) {
                    if (clearhausTestMode) {
                        setPaymentStatus('Acceptér abonnementsvilkårene og klik på “Godkend betaling som test” for at fortsætte.', 'error');
                    } else {
                        beginPaymentActivation(nextIndex);
                    }
                    updateStepChrome();
                    return;
                }
                if (activeIndex < paymentIndex && nextIndex > paymentIndex) {
                    nextIndex = paymentIndex;
                }
            }
            if (nextIndex === activeIndex) {
                updateStepChrome();
                return;
            }

            const previousIndex = activeIndex;
            const previousScreen = screens[previousIndex];
            const nextScreen = screens[nextIndex];
            const isForward = nextIndex > previousIndex;

            saveSetupProgress(previousScreen.dataset.stepId);
            window.clearTimeout(transitionTimer);
            screens.forEach((screen) => {
                if (screen !== previousScreen && screen !== nextScreen) {
                    screen.classList.remove('active', 'enter-from-left', 'enter-from-right', 'exit-to-left', 'exit-to-right');
                }
            });

            nextScreen.classList.remove('exit-to-left', 'exit-to-right', 'enter-from-left', 'enter-from-right');
            previousScreen.classList.remove('enter-from-left', 'enter-from-right', 'exit-to-left', 'exit-to-right');

            nextScreen.classList.add('active', isForward ? 'enter-from-right' : 'enter-from-left');
            previousScreen.classList.add(isForward ? 'exit-to-left' : 'exit-to-right');

            activeIndex = nextIndex;
            if (nextScreen.dataset.stepId !== 'linkedin') linkedInApproval.update(false);
            saveSetupProgress(nextScreen.dataset.stepId);
            updateStepChrome();
            if (nextScreen.dataset.stepId === 'workflow') {
                renderWorkflow();
            }

            window.requestAnimationFrame(() => {
                nextScreen.classList.remove('enter-from-left', 'enter-from-right');
            });

            transitionTimer = window.setTimeout(() => {
                previousScreen.classList.remove('active', 'exit-to-left', 'exit-to-right');
            }, 360);
        }

        function stepIndexFromId(stepId) {
            const index = findStepIndexById(stepId);
            return index >= 0 ? index : -1;
        }

        function resolveInitialStepIndex() {
            if (signupBlocked) return 0;
            const params = new URLSearchParams(window.location.search);
            const paymentIndex = findStepIndexById('payment');
            const status = paymentReturnStatus();
            const requestedStepId = String(params.get('return_step') || params.get('step') || '').trim();
            let resolvedIndex = 0;

            if ((status || params.get('payment_return') === '1' || state.pendingPaymentCheckout) && paymentIndex >= 0) {
                resolvedIndex = paymentIndex;
            } else if (requestedStepId && stepIndexFromId(requestedStepId) >= 0) {
                resolvedIndex = stepIndexFromId(requestedStepId);
            } else if (storedSetupProgress.currentStepId && stepIndexFromId(storedSetupProgress.currentStepId) >= 0) {
                resolvedIndex = stepIndexFromId(storedSetupProgress.currentStepId);
            } else if (Number.isInteger(Number(storedSetupProgress.currentStepIndex)) && Number(storedSetupProgress.currentStepIndex) >= 0) {
                resolvedIndex = Number(storedSetupProgress.currentStepIndex);
            }

            resolvedIndex = Math.max(0, Math.min(resolvedIndex, screens.length - 1));
            if (paymentIndex >= 0 && resolvedIndex > paymentIndex && !paymentStepCanContinue()) {
                return paymentIndex;
            }
            return resolvedIndex;
        }

        function showInitialStep(index) {
            activeIndex = Math.max(0, Math.min(index, screens.length - 1));
            screens.forEach((screen, screenIndex) => {
                screen.classList.toggle('active', screenIndex === activeIndex);
                screen.classList.remove('enter-from-left', 'enter-from-right', 'exit-to-left', 'exit-to-right');
            });
            updateStepChrome();
            if (screens[activeIndex]?.dataset.stepId === 'workflow') {
                renderWorkflow();
            }
        }

        function escapeHtml(value) {
            return String(value || '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
        }

        function isValidEmail(value) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
        }

        function setIdentityStatus(message, type) {
            if (!setupIdentityStatus) return;
            setupIdentityStatus.textContent = translateSetupText(message || '');
            setupIdentityStatus.hidden = !message;
            setupIdentityStatus.classList.toggle('success', type === 'success');
            setupIdentityStatus.classList.toggle('error', type === 'error');
        }

        function setStepStatus(element, message, type) {
            if (!element) return;
            element.textContent = translateSetupText(message || '');
            element.hidden = !message;
            element.classList.toggle('success', type === 'success');
            element.classList.toggle('error', type === 'error');
        }

        function setCampaignStatus(message, type) {
            setStepStatus(campaignStatus, message, type);
        }

        function setSourceStatus(message, type) {
            setStepStatus(sourceStatus, message, type);
        }

        function setWorkflowStatus(message, type) {
            setStepStatus(workflowStatus, message, type);
        }

        function syncIdentityPanel() {
            if (setupIdentityFields) setupIdentityFields.hidden = !state.accountType;
        }

        function accountStepCanContinue() {
            if (signupBlocked) return false;
            const name = setupUserName ? setupUserName.value.trim() : '';
            const email = setupUserEmail ? setupUserEmail.value.trim() : '';
            return !!state.accountType && name.length > 1 && isValidEmail(email) && !state.userSaving;
        }

        function splitName(fullName) {
            const parts = String(fullName || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return {first_name: '', last_name: ''};
            return {
                first_name: parts[0],
                last_name: parts.slice(1).join(' ')
            };
        }

        function extractSessionId(data) {
            if (!data || typeof data !== 'object') return '';
            const directKeys = ['access_token', 'accessToken', 'session_id', 'sessionId', 'token', 'bearer_token', 'bearerToken'];
            for (const key of directKeys) {
                if (data[key]) return String(data[key]);
            }
            for (const key of ['data', 'user', 'session', 'auth']) {
                const nestedSessionId = extractSessionId(data[key]);
                if (nestedSessionId) return nestedSessionId;
            }
            return '';
        }

        function extractUserId(data) {
            if (!data || typeof data !== 'object') return 0;
            const directUserId = Number(data.user_id || data.id || data.uid || 0);
            if (directUserId) return directUserId;
            for (const key of ['data', 'user', 'session', 'auth']) {
                const nestedUserId = extractUserId(data[key]);
                if (nestedUserId) return nestedUserId;
            }
            return 0;
        }

        function persistSessionId(sessionId) {
            if (!sessionId) return;
            bearerToken = sessionId;
            config.sessionId = sessionId;
            if (document.body) document.body.setAttribute('session_id', sessionId);
            try {
                window.localStorage.setItem('session_id', sessionId);
                window.localStorage.setItem('access_token', sessionId);
                window.localStorage.setItem('coherta_auth_session_id', sessionId);
                window.sessionStorage.setItem('session_id', sessionId);
                window.sessionStorage.setItem('access_token', sessionId);
                window.sessionStorage.setItem('coherta_auth_session_id', sessionId);
            } catch (error) {}
        }

        function persistUserId(userId) {
            const cleanUserId = String(userId || '').trim();
            if (!cleanUserId || cleanUserId === '0') return;
            const numericUserId = Number(cleanUserId);
            if (numericUserId) state.userId = numericUserId;
            config.userId = cleanUserId;
            try {
                window.localStorage.setItem('coherta_setup_user_id', cleanUserId);
                window.sessionStorage.setItem('coherta_setup_user_id', cleanUserId);
            } catch (error) {}
        }

        function currentUserId() {
            return Number(state.userId || config.userId || storedSetupProgress.userId || 0);
        }

        function getSourceProgress() {
            const sourceTitle = document.getElementById('sourceTitle');
            const sourceCountry = document.getElementById('sourceCountry');
            const sourceUrl = document.getElementById('sourceUrl');
            const manualLeads = document.getElementById('manualLeads');
            return {
                sourceTitle: sourceTitle ? sourceTitle.value.trim() : (storedSetupProgress.sourceTitle || ''),
                sourceCountry: sourceCountry ? sourceCountry.value : (storedSetupProgress.sourceCountry || ''),
                sourceUrl: sourceUrl ? sourceUrl.value.trim() : (storedSetupProgress.sourceUrl || ''),
                manualLeads: manualLeads ? manualLeads.value.trim() : (storedSetupProgress.manualLeads || '')
            };
        }

        function getWorkflowProgress() {
            try {
                return workflow || null;
            } catch (error) {
                return storedSetupProgress.workflow || null;
            }
        }

        function buildSetupProgress(stepId) {
            applyAccountTypePricingRules();
            const progressStepId = stepId || (screens[activeIndex]?.dataset.stepId || '');
            return Object.assign({}, storedSetupProgress, getSourceProgress(), {
                savedAt: new Date().toISOString(),
                currentStepId: progressStepId,
                currentStepIndex: findStepIndexById(progressStepId),
                talentAttract: talentAttractEnabled,
                accountType: state.accountType,
                pricingPlan: state.pricingPlan,
                billingCycle: state.billingCycle,
                billingPeriodKey: state.billingPeriodKey,
                pricingModel: state.pricingModel,
                paymentActivationUrl: state.paymentActivationUrl,
                stripeProductKey: state.pricingModel?.stripeProductKey || '',
                stripeProductId: state.pricingModel?.stripeProductId || '',
                stripePriceId: state.pricingModel?.stripePriceId || '',
                monthlyPrice: Number(state.pricingModel?.monthlyPrice || 0),
                currency: state.pricingModel?.currency || 'DKK',
                userId: state.userId || 0,
                userName: setupUserName ? setupUserName.value.trim() : state.userName,
                userEmail: setupUserEmail ? setupUserEmail.value.trim() : state.userEmail,
                userSaved: !!state.userSaved,
                campaignName: campaignName ? campaignName.value.trim() : '',
                campaignGoal: campaignGoal ? campaignGoal.value : '',
                campaignMode: state.campaignMode,
                playbook: state.playbook,
                source: state.source,
                csvItemCount: state.csvItemCount,
                csvLinkedInUrlCount: state.csvLinkedInUrlCount,
                csvFileName: state.csvFileName,
                campaignId: state.campaignId || 0,
                workflowSaved: !!state.workflowSaved,
                workflow: getWorkflowProgress(),
                paymentConfirmed: !!state.paymentConfirmed,
                ...(clearhausTestMode ? {clearhausTestPayment: state.clearhausTestPayment} : {}),
                pendingPaymentCheckout: !!state.pendingPaymentCheckout,
                pendingPaymentTargetIndex: state.pendingPaymentTargetIndex || null,
                linkedinConnected: !!state.linkedinConnected,
                linkedinStatus: state.linkedinStatus,
                chromePluginInstalled: !!state.chromePluginInstalled,
                chromePluginAuthenticated: !!state.chromePluginAuthenticated,
                aiTool: state.aiTool
            });
        }

        function saveSetupProgress(stepId, extra) {
            storedSetupProgress = Object.assign(buildSetupProgress(stepId), extra || {});
            try {
                SETUP_PROGRESS_STORAGE.setItem(SETUP_PROGRESS_KEY, JSON.stringify(storedSetupProgress));
                SETUP_PROGRESS_STORAGE.setItem(SETUP_DRAFT_KEY, JSON.stringify(storedSetupProgress));
            } catch (error) {}
            return storedSetupProgress;
        }

        function setSetupLanguageMenuOpen(isOpen) {
            if (!setupLanguageMenu || !setupLanguageToggle) return;
            setupLanguageMenu.classList.toggle('open', !!isOpen);
            setupLanguageToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function switchSetupLanguage(language, countryCode) {
            const nextLanguage = String(language || '').trim().toLowerCase();
            if (!nextLanguage) return;
            const currentStepId = screens[activeIndex]?.dataset.stepId || storedSetupProgress.currentStepId || '';
            saveSetupProgress(currentStepId, {locale: nextLanguage});
            try {
                window.localStorage.setItem('coherta_setup_funnel_locale', nextLanguage);
            } catch (error) {}

            const url = new URL(window.location.href);
            url.searchParams.delete('lang');
            url.searchParams.delete('language');
            url.searchParams.set('locale', nextLanguage);
            url.searchParams.set('country_code', String(countryCode || nextLanguage).trim().toLowerCase() || nextLanguage);
            if (currentStepId) url.searchParams.set('step', currentStepId);
            window.location.href = url.toString();
        }

        function bindSetupLanguageSwitcher() {
            if (!setupLanguageSwitcher || !setupLanguageToggle || !setupLanguageMenu) return;
            document.documentElement.lang = setupLanguageMenu.querySelector('.setup-language-option.is-active')?.dataset.htmlLang || setupLocale || 'da';

            setupLanguageToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                setSetupLanguageMenuOpen(!setupLanguageMenu.classList.contains('open'));
            });

            setupLanguageMenu.querySelectorAll('[data-setup-language]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const language = button.dataset.setupLanguage || '';
                    if (language === setupLocale) {
                        setSetupLanguageMenuOpen(false);
                        return;
                    }
                    switchSetupLanguage(language, button.dataset.countryCode || language);
                });
            });

            document.addEventListener('click', (event) => {
                if (!setupLanguageSwitcher.contains(event.target)) setSetupLanguageMenuOpen(false);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setSetupLanguageMenuOpen(false);
            });
        }

        function syncChoiceSelection(groupName, value) {
            const group = document.querySelector('[data-choice-group="' + groupName + '"]');
            if (!group) return;
            group.querySelectorAll('.setup-choice').forEach((item) => {
                item.classList.toggle('selected', !!value && item.dataset.value === value);
            });
        }

        function hydrateSetupProgress() {
            applyAccountTypePricingRules();
            if (setupUserName && state.userName) setupUserName.value = state.userName;
            if (setupUserEmail && state.userEmail) setupUserEmail.value = state.userEmail;
            if (campaignName && storedSetupProgress.campaignName) campaignName.value = storedSetupProgress.campaignName;
            if (campaignGoal && storedSetupProgress.campaignGoal) campaignGoal.value = storedSetupProgress.campaignGoal;
            syncChoiceSelection('accountType', state.accountType);
            applyAccountTypeLock();
            applyAccountTypePricingRules();
            syncChoiceSelection('playbook', state.playbook);
            syncChoiceSelection('campaignMode', state.campaignMode);
            syncChoiceSelection('aiTool', state.aiTool);
            syncIdentityPanel();
        }

        function parseCsvRows(text) {
            const rows = [];
            let row = [];
            let cell = '';
            let inQuotes = false;
            const source = String(text || '').replace(/^\uFEFF/, '');

            for (let index = 0; index < source.length; index++) {
                const char = source[index];
                const next = source[index + 1];

                if (char === '"') {
                    if (inQuotes && next === '"') {
                        cell += '"';
                        index++;
                    } else {
                        inQuotes = !inQuotes;
                    }
                    continue;
                }

                if (char === ',' && !inQuotes) {
                    row.push(cell);
                    cell = '';
                    continue;
                }

                if ((char === '\n' || char === '\r') && !inQuotes) {
                    if (char === '\r' && next === '\n') index++;
                    row.push(cell);
                    rows.push(row);
                    row = [];
                    cell = '';
                    continue;
                }

                cell += char;
            }

            row.push(cell);
            rows.push(row);
            return rows.filter((items) => items.some((item) => String(item || '').trim() !== ''));
        }

        function csvLooksLikeHeader(row) {
            const text = row.map((item) => String(item || '').trim().toLowerCase()).join(' ');
            if (!text || text.includes('linkedin.com/')) return false;
            return /\b(name|navn|email|e-mail|linkedin|url|company|firma|title|titel)\b/.test(text);
        }

        function summarizeCsv(text, fileName) {
            const rows = parseCsvRows(text);
            const hasHeader = rows.length > 1 && csvLooksLikeHeader(rows[0]);
            const itemRows = hasHeader ? rows.slice(1) : rows;
            const linkedinUrls = Array.from(String(text || '').matchAll(/https?:\/\/(?:www\.)?linkedin\.com\/(?:in|pub)\/[A-Za-z0-9\-_%/]+/gi)).map((item) => item[0]);
            const uniqueLinkedInUrls = Array.from(new Set(linkedinUrls));
            return {
                fileName: fileName || '',
                itemCount: itemRows.length,
                linkedinUrlCount: uniqueLinkedInUrls.length,
                linkedinUrls: uniqueLinkedInUrls,
                hasHeader: hasHeader
            };
        }

        function updateCsvStatus(summary) {
            const csvStatus = document.getElementById('setupCsvStatus');
            if (!csvStatus) return;
            const itemCount = Number(summary?.itemCount || state.csvItemCount || 0);
            const linkedInCount = Number(summary?.linkedinUrlCount || state.csvLinkedInUrlCount || 0);
            const fileName = summary?.fileName || state.csvFileName || '';
            csvStatus.classList.toggle('success', itemCount > 0);
            csvStatus.classList.toggle('error', itemCount === 0 && !!fileName);

            if (!fileName) {
                csvStatus.textContent = translateSetupText('Upload en CSV-fil, så viser vi hvor mange emner der lægges ind i kampagnen.');
                return;
            }

            if (!itemCount) {
                csvStatus.textContent = translateSetupText('Vi fandt ingen emner i CSV-filen.');
                return;
            }

            const itemLabel = translateSetupText(itemCount === 1 ? 'emne' : 'emner');
            const csvReadyText = translateSetupText('{count} {itemLabel} fundet i CSV-filen og klar til kampagnen.', {count: itemCount, itemLabel: itemLabel});
            const linkedInCopy = linkedInCount ? ' ' + translateSetupText('{count} LinkedIn-profiler fundet.', {count: linkedInCount}) : '';
            csvStatus.textContent = csvReadyText + linkedInCopy;
        }

        function bindCsvUpload() {
            const sourceCsv = document.getElementById('sourceCsv');
            if (!sourceCsv) {
                updateCsvStatus(null);
                return;
            }

            updateCsvStatus({
                fileName: state.csvFileName,
                itemCount: state.csvItemCount,
                linkedinUrlCount: state.csvLinkedInUrlCount
            });

            sourceCsv.addEventListener('change', async () => {
                const file = sourceCsv.files && sourceCsv.files[0];
                if (!file) return;

                try {
                    const summary = summarizeCsv(await file.text(), file.name);
                    state.csvFileName = summary.fileName;
                    state.csvItemCount = summary.itemCount;
                    state.csvLinkedInUrlCount = summary.linkedinUrlCount;
                    state.csvLinkedInUrls = summary.linkedinUrls || [];
                    updateCsvStatus(summary);
                    saveSetupProgress('source');
                    updateSummary();
                } catch (error) {
                    state.csvFileName = file.name;
                    state.csvItemCount = 0;
                    state.csvLinkedInUrlCount = 0;
                    state.csvLinkedInUrls = [];
                    updateCsvStatus({fileName: file.name, itemCount: 0, linkedinUrlCount: 0});
                    saveSetupProgress('source');
                }
            });
        }

        function bindSourcePersistence() {
            sourcePanel.querySelectorAll('input, select, textarea').forEach((field) => {
                field.addEventListener('input', () => saveSetupProgress('source'));
                field.addEventListener('change', () => saveSetupProgress('source'));
            });
        }

        function updateSummary() {
            applyAccountTypePricingRules();
            if (clearhausTestMode) updateClearhausTestSummary();
            const summaryPlan = document.getElementById('summaryPlan');
            const summaryBilling = document.getElementById('summaryBilling');
            const summaryCampaign = document.getElementById('summaryCampaign');
            const summarySource = document.getElementById('summarySource');
            const summaryWorkflow = document.getElementById('summaryWorkflow');
            const paymentActivationLink = document.getElementById('setupPaymentActivationLink');
            const monthlyPrice = getBillingMonthlyPrice(state.pricingModel, state.billingCycle);
            const currency = state.pricingModel?.currency || 'DKK';
            const planLabel = translateSetupText(state.pricingModel?.label || 'Coherta Standard');
            const priceCopy = monthlyPrice > 0
                ? ' - ' + (normalizeBillingCycle(state.billingCycle) === 'yearly'
                    ? annualMonthlyPriceDisplay(monthlyPrice, currency)
                    : monthlyPriceDisplay(monthlyPrice, currency))
                : '';
            if (talentAttractEnabled) {
                const upfrontPrice = Number(talentAttractConfig.upfrontPrice || 750);
                const upfrontCurrency = talentAttractConfig.currency || currency;
                const upfrontDisplay = formatCheckoutPrice(upfrontPrice) + ' ' + checkoutCurrencyLabel(upfrontCurrency);
                const monthlyDisplay = formatCheckoutPrice(monthlyPrice) + ' ' + checkoutCurrencyLabel(currency);
                if (summaryPlan) summaryPlan.textContent = translateSetupText('TalentAttract - {price} nu', {price: upfrontDisplay});
                if (summaryBilling) summaryBilling.textContent = translateSetupText('{price} / md. efter 12 måneder', {price: monthlyDisplay});
            } else {
                if (summaryPlan) summaryPlan.textContent = planLabel + priceCopy;
                if (summaryBilling) summaryBilling.textContent = translateSetupText(state.billingCycle === 'custom' ? 'Aftales individuelt' : (state.billingCycle === 'yearly' ? 'Årlig betaling' : 'Månedlig betaling'));
            }
            if (summaryCampaign) summaryCampaign.textContent = campaignName.value.trim() || translateSetupText('Ikke navngivet endnu');
            if (summarySource) {
                const csvSuffix = state.source === 'csv' && state.csvItemCount ? ' (' + translateSetupText('{count} emner', {count: state.csvItemCount}) + ')' : '';
                summarySource.textContent = translateSetupText(sourceLabels[state.source] || 'Ikke valgt') + csvSuffix;
            }
            if (summaryWorkflow) summaryWorkflow.textContent = translateSetupText('{count} nodes', {count: workflow.nodes.length});
            if (paymentActivationLink) {
                paymentActivationLink.href = state.pricingPlan === 'custom'
                    ? buildPaymentActivationUrl(state.pricingPlan, state.billingCycle)
                    : '#payment-checkout';
                paymentActivationLink.textContent = translateSetupText(state.pricingPlan === 'custom' ? 'Tal med os om Custom' : 'Gå til Revolut');
            }
            if (doneLead) {
                doneLead.textContent = translateSetupText(paymentEnabled && config.hasActiveSubscription
                    ? 'Workflowet er sat op. Vi fandt en eksisterende subscription, så Revolut-trinnet blev sprunget over.'
                    : 'Workflowet er sat op, og campaignen er klar til aktivering.');
            }
        }

        function setPaymentStatus(message, type) {
            if (!paymentStatus) return;
            paymentStatus.textContent = translateSetupText(message || '');
            paymentStatus.classList.toggle('success', type === 'success');
            paymentStatus.classList.toggle('error', type === 'error');
        }

        function buildSetupReturnUrl(status) {
            const returnLocale = /^[a-z]{2}$/.test(setupLocale) ? setupLocale : '';
            const returnPath = returnLocale ? '/' + returnLocale + '/setup-funnel' : '/app/setup-funnel';
            const url = new URL(returnPath, window.location.origin);
            const returnAccountType = state.accountType || prefilledAccountType || 'business';
            url.searchParams.set('account_type', returnAccountType);
            url.searchParams.set('customer_type', returnAccountType === 'personal' ? 'personal' : 'company');
            url.searchParams.set('use', returnAccountType === 'personal' ? 'personal' : 'business');
            url.searchParams.set('plan', state.pricingPlan);
            url.searchParams.set('model', state.pricingPlan);
            url.searchParams.set('billing', state.billingCycle);
            if (talentAttractEnabled) url.searchParams.set('TalentAttract', '1');
            url.searchParams.set('country_code', normalizeCheckoutCountryCode(config.selectedMarket || 'DK').toLowerCase());
            url.searchParams.set('source', 'pricing');
            url.searchParams.set('payment_return', '1');
            url.searchParams.set('return_step', 'payment');
            if (status) url.searchParams.set('checkout_status', status);
            return url.toString();
        }

        function normalizeCheckoutCountryCode(country) {
            const value = String(country || '').trim().toLowerCase();
            if (!value || value === 'da' || value === 'dk' || value === 'danmark' || value === 'denmark') return 'DK';
            return value.slice(0, 2).toUpperCase();
        }

        function checkoutApiPlan(plan) {
            const planKey = normalizePricingPlan(plan);
            return planKey === 'extended' ? 'extended' : 'growth';
        }

        function getClearhausTestOffer() {
            return window.CohertaClearhausTest.buildOffer({
                plan: state.pricingPlan,
                label: state.pricingModel?.label || 'Coherta Standard',
                billingCycle: state.billingCycle,
                monthlyPrice: getBillingMonthlyPrice(state.pricingModel, state.billingCycle),
                currency: state.pricingModel?.currency || 'DKK',
                identity: [config.userId || state.userId || 0, config.userEmail || state.userEmail || ''],
                startDate: state.clearhausTestPayment?.startDate || config.clearhausTestDate
            });
        }

        function updateClearhausTestSummary() {
            let offer = getClearhausTestOffer();
            const approved = window.CohertaClearhausTest.isApproved(offer, state.clearhausTestPayment);
            if (state.clearhausTestPayment && !approved) {
                state.clearhausTestPayment = null;
                clearhausAcceptTerms.checked = false;
                offer = getClearhausTestOffer();
            }
            const fields = {
                clearhausPlan: offer.plan,
                clearhausInitialPrice: offer.initialPriceText,
                clearhausRecurringPrice: offer.recurringText,
                clearhausStartDate: offer.startDateText,
                clearhausFirstBillingDate: offer.firstBillingDateText,
                clearhausFrequency: offer.frequencyText,
                clearhausDuration: offer.durationText
            };
            Object.entries(fields).forEach(([id, value]) => { document.getElementById(id).textContent = value; });
            document.getElementById('clearhausCustomNotice').hidden = offer.valid;
            const standardUrl = new URL(window.location.href);
            standardUrl.searchParams.set('plan', 'standard');
            standardUrl.searchParams.set('billing', 'monthly');
            standardUrl.searchParams.set('step', 'payment');
            document.getElementById('clearhausStandardLink').href = standardUrl.toString();
            clearhausAcceptTerms.disabled = !offer.valid || approved;
            if (approved) {
                clearhausAcceptTerms.checked = true;
                setPaymentStatus('Testbetalingen er godkendt. Ingen penge er trukket.', 'success');
            }
        }

        function approveClearhausTestPayment(targetIndex) {
            if (!clearhausTestMode) return;
            if (paymentStepCanContinue()) {
                goToStep(targetIndex);
                return;
            }
            const receipt = window.CohertaClearhausTest.approve(getClearhausTestOffer(), clearhausAcceptTerms?.checked === true, new Date());
            if (!receipt) {
                setPaymentStatus('Acceptér abonnementsvilkårene og vælg en pakke med fast pris for at godkende testbetalingen.', 'error');
                clearhausAcceptTerms?.focus();
                return;
            }
            state.clearhausTestPayment = receipt;
            saveSetupProgress('payment');
            setPaymentStatus('Testbetalingen er godkendt. Ingen penge er trukket.', 'success');
            goToStep(targetIndex);
        }

        function checkoutCurrencyLabel(currency) {
            return String(currency || '').trim().toUpperCase() === 'DKK' ? 'kr.' : String(currency || 'DKK');
        }

        function formatCheckoutPrice(amount) {
            return new Intl.NumberFormat(setupIntlLocale, {maximumFractionDigits: 0}).format(Number(amount || 0));
        }

        function monthlyPriceDisplay(monthlyPrice, currency) {
            return translateSetupText('{price} / md.', {
                price: formatCheckoutPrice(monthlyPrice) + ' ' + checkoutCurrencyLabel(currency)
            });
        }

        function annualMonthlyPriceDisplay(monthlyPrice, currency) {
            return translateSetupText('{price} pr. måned x {quantity} stk.', {
                price: formatCheckoutPrice(monthlyPrice) + ' ' + checkoutCurrencyLabel(currency),
                quantity: 12
            });
        }

        function buildPaymentCheckoutPayload() {
            applyAccountTypePricingRules();
            const model = state.pricingModel || getPricingModel(state.pricingPlan);
            const billingCycle = normalizeBillingCycle(state.billingCycle);
            const isYearly = billingCycle === 'yearly';
            const monthlyPrice = getBillingMonthlyPrice(model, billingCycle);
            const displayedAnnualQuantity = isYearly ? 12 : 1;
            const checkoutQuantity = 1;
            const checkoutUnitPrice = monthlyPrice;
            const checkoutCustomPrice = isYearly ? checkoutUnitPrice * displayedAnnualQuantity : checkoutUnitPrice;
            const currency = model?.currency || 'DKK';
            const planKey = normalizePricingPlan(state.pricingPlan);
            const productKey = String(talentAttractEnabled
                ? talentAttractConfig.subscriptionProductId
                : (model?.stripeProductKey || (planKey === 'custom' ? '' : 'Tj6iHmP3XyIEdS'))).trim();
            const productId = String(talentAttractEnabled
                ? talentAttractConfig.subscriptionProductId
                : (model?.stripeProductId || (planKey === 'custom' ? '' : 'prod_UWswNDxveWxoUo'))).trim();
            const priceId = String(talentAttractEnabled
                ? talentAttractConfig.subscriptionPriceId
                : (model?.stripePriceId || '')).trim();
            const email = state.userEmail || (setupUserEmail ? setupUserEmail.value.trim() : config.userEmail || '');
            const checkoutAccountType = state.accountType || prefilledAccountType || 'business';
            const checkoutCustomerType = checkoutAccountType === 'personal' ? 'personal' : 'company';
            const companyName = config.companyName || (checkoutAccountType === 'personal' ? (state.userName || email) : 'Default Company');
            const countryCode = normalizeCheckoutCountryCode(config.selectedMarket || 'DK');
            const apiPlan = checkoutApiPlan(planKey);
            const planLabel = talentAttractEnabled ? 'TalentAttract' : translateSetupText(model?.label || 'Coherta Standard');
            const priceFormula = isYearly
                ? annualMonthlyPriceDisplay(checkoutUnitPrice, currency)
                : monthlyPriceDisplay(checkoutUnitPrice, currency);
            const checkoutLabel = isYearly ? planLabel + ' - ' + priceFormula : planLabel;
            const trialDays = Math.max(0, Number(talentAttractEnabled ? talentAttractConfig.trialDays : (config.trialDays ?? 7)) || 0);
            const checkoutDescription = talentAttractEnabled
                ? translateSetupText('TalentAttract koster {upfront} nu. Abonnementet på {monthly} om måneden starter efter 12 måneder.', {
                    upfront: formatCheckoutPrice(Number(talentAttractConfig.upfrontPrice || 750)) + ' ' + checkoutCurrencyLabel(talentAttractConfig.currency || currency),
                    monthly: formatCheckoutPrice(checkoutUnitPrice) + ' ' + checkoutCurrencyLabel(currency)
                })
                : (isYearly
                ? translateSetupText('{plan} - valgt som samlet årsbetaling på {total} ({monthly} pr. måned x {quantity}). Næste afregning om 365 dage.', {
                    plan: planLabel,
                    total: formatCheckoutPrice(checkoutCustomPrice) + ' ' + checkoutCurrencyLabel(currency),
                    monthly: formatCheckoutPrice(checkoutUnitPrice) + ' ' + checkoutCurrencyLabel(currency),
                    quantity: displayedAnnualQuantity
                })
                : planLabel + ' - ' + priceFormula);

            const payload = {
                user_id: state.userId || config.userId || null,
                email: email,
                plan: apiPlan,
                plan_key: planKey,
                subscription_plan: apiPlan,
                pricing_model: planKey,
                coherta_pricing_model: planKey,
                plan_label: checkoutLabel,
                product_label: checkoutLabel,
                line_item_name: checkoutLabel,
                line_item_description: checkoutDescription,
                description: checkoutDescription,
                checkout_description: checkoutDescription,
                billing_cycle: billingCycle,
                billing_interval: isYearly ? 'year' : 'month',
                interval: isYearly ? 'year' : 'month',
                recurring_interval: isYearly ? 'year' : 'month',
                interval_count: 1,
                billing_interval_count: 1,
                billing_period: state.billingPeriodKey || billingPeriodKey(billingCycle),
                pricing: checkoutCustomPrice,
                monthly_price: checkoutCustomPrice,
                unit_price: checkoutCustomPrice,
                unit_amount: checkoutCustomPrice,
                custom_monthly_price: checkoutCustomPrice,
                custom_unit_amount: checkoutCustomPrice,
                custom_price_unit_amount: checkoutCustomPrice,
                monthly_unit_price: checkoutUnitPrice,
                monthly_reference_price: checkoutUnitPrice,
                custom_monthly_reference_price: checkoutUnitPrice,
                quantity: checkoutQuantity,
                line_item_quantity: checkoutQuantity,
                custom_quantity: checkoutQuantity,
                custom_price_quantity: checkoutQuantity,
                custom_monthly_price_quantity: checkoutQuantity,
                price_quantity: checkoutQuantity,
                stripe_quantity: checkoutQuantity,
                subscription_quantity: checkoutQuantity,
                custom_price_display: priceFormula,
                custom_price_description: checkoutDescription,
                custom_monthly_price_display: isYearly
                    ? translateSetupText('{price} årligt', {price: formatCheckoutPrice(checkoutCustomPrice) + ' ' + checkoutCurrencyLabel(currency)})
                    : formatCheckoutPrice(checkoutCustomPrice) + ' ' + checkoutCurrencyLabel(currency),
                quantity_display: checkoutQuantity + ' stk.',
                custom_monthly_price_currency: currency,
                currency: currency,
                company_name: companyName,
                customer_type: checkoutCustomerType,
                account_type: checkoutAccountType,
                country: countryCode,
                country_code: countryCode,
                billing_country: countryCode,
                customer_country: countryCode,
                address_country: countryCode,
                market: countryCode === 'DK' ? 'Denmark' : countryCode,
                market_code: countryCode,
                source: 'setup-funnel',
                success_url: buildSetupReturnUrl('success'),
                cancel_url: buildSetupReturnUrl('cancelled'),
                trial_days: trialDays,
                trial_period_days: trialDays,
                trialPeriodDays: trialDays,
                free_trial_days: trialDays
            };

            if (isYearly) {
                payload.annual_monthly_price_display = priceFormula;
                payload.annual_quantity = displayedAnnualQuantity;
                payload.annual_price = checkoutCustomPrice;
                payload.annual_total_price = checkoutCustomPrice;
                payload.custom_annual_price = checkoutCustomPrice;
                payload.custom_total_amount = checkoutCustomPrice;
                payload.total_price = checkoutCustomPrice;
                payload.next_billing_days = 365;
                payload.next_billing_in_days = 365;
                payload.next_billing_label = translateSetupText('Næste afregning om 365 dage');
                payload.renewal_period_days = 365;
            }

            if (productId) {
                const productLineItem = {
                    product: productId,
                    product_key: productKey,
                    product_id: productId,
                    stripe_product_key: productKey,
                    stripe_product_id: productId,
                    plan: apiPlan,
                    plan_key: planKey,
                    name: checkoutLabel,
                    label: checkoutLabel,
                    description: checkoutDescription,
                    quantity: checkoutQuantity,
                    line_item_quantity: checkoutQuantity,
                    price_quantity: checkoutQuantity,
                    stripe_quantity: checkoutQuantity,
                    unit_price: checkoutCustomPrice,
                    unit_amount: checkoutCustomPrice,
                    monthly_price: checkoutCustomPrice,
                    custom_monthly_price: checkoutCustomPrice,
                    custom_unit_amount: checkoutCustomPrice,
                    custom_price_unit_amount: checkoutCustomPrice,
                    monthly_unit_price: checkoutUnitPrice,
                    monthly_reference_price: checkoutUnitPrice,
                    custom_monthly_reference_price: checkoutUnitPrice,
                    currency: currency,
                    billing_cycle: billingCycle,
                    billing_interval: isYearly ? 'year' : 'month',
                    interval: isYearly ? 'year' : 'month',
                    recurring_interval: isYearly ? 'year' : 'month',
                    interval_count: 1,
                    billing_interval_count: 1,
                    next_billing_days: isYearly ? 365 : null,
                    trial_period_days: trialDays
                };
                payload.products = [productId];
                payload.product_key = productKey;
                payload.product_id = productId;
                payload.stripe_product_key = productKey;
                payload.stripe_product_id = productId;
                payload.product_quantity = checkoutQuantity;
                payload.product_quantities = productKey
                    ? {[productId]: checkoutQuantity, [productKey]: checkoutQuantity}
                    : {[productId]: checkoutQuantity};
                payload.line_items = [productLineItem];
                payload.items = [productLineItem];
                payload.product_items = [productLineItem];
                payload.checkout_line_items = [productLineItem];
                payload.stripe_line_items = [productLineItem];
            }

            if (talentAttractEnabled) {
                const upfrontProductId = String(talentAttractConfig.upfrontProductId || 'prod_UTh12jd4lY49SA').trim();
                const promotionCode = String(talentAttractConfig.promotionCode || 'TalentAttract').trim();
                const upfrontPrice = Number(talentAttractConfig.upfrontPrice || 750);

                payload.coupon = promotionCode;
                payload.offer_type = 'talent_attract';
                payload.talent_attract = 1;
                payload.products = upfrontProductId ? [upfrontProductId] : [];
                payload.product_quantities = upfrontProductId ? {[upfrontProductId]: checkoutQuantity} : {};
                payload.product_id = productId;
                payload.stripe_product_id = productId;
                payload.subscription_product_id = productId;
                payload.price_id = priceId;
                payload.stripe_price_id = priceId;
                payload.priceId = priceId;
                payload.reference_price_id = priceId;
                payload.subscription_price_id = priceId;
                payload.upfront_product_id = upfrontProductId;
                payload.upfront_price_display = formatCheckoutPrice(upfrontPrice) + ' ' + checkoutCurrencyLabel(talentAttractConfig.currency || currency);
                payload.subscription_monthly_price = checkoutUnitPrice;
                payload.next_billing_days = trialDays;
                payload.next_billing_in_days = trialDays;
                payload.next_billing_label = translateSetupText('Abonnementet starter efter 12 måneder');

                if (payload.line_items?.[0]) {
                    payload.line_items[0].price = priceId;
                    payload.line_items[0].price_id = priceId;
                    payload.line_items[0].stripe_price_id = priceId;
                }
            }

            return payload;
        }

        async function createPaymentCheckout(payload) {
            if (!getSessionId()) throw new Error('Log ind igen for at fortsætte betalingen.');
            const response = await fetch(config.paymentCheckoutEndpoint || 'https://api.coherta.com/billing/checkout', {
                method: 'POST',
                headers: headersJson(true),
                body: JSON.stringify(payload)
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                const error = new Error(result.message || result.error || 'Revolut checkout kunne ikke åbnes lige nu.');
                error.status = response.status;
                error.result = result;
                throw error;
            }
            return result;
        }

        function isLikelyMobileCheckout() {
            const ua = navigator.userAgent || '';
            return /Mobi|Android|iPhone|iPad|iPod|CriOS|FxiOS/i.test(ua)
                || (window.matchMedia && window.matchMedia('(max-width: 760px)').matches);
        }

        function savePendingPaymentCheckout(targetIndex) {
            const paymentIndex = findStepIndexById('payment');
            const safeTargetIndex = Number.isInteger(targetIndex)
                ? Math.max(0, Math.min(targetIndex, screens.length - 1))
                : nextStepAfterPaymentIndex();
            state.pendingPaymentCheckout = true;
            state.pendingPaymentTargetIndex = safeTargetIndex;
            saveSetupProgress('payment', {
                currentStepId: 'payment',
                currentStepIndex: paymentIndex >= 0 ? paymentIndex : activeIndex,
                pendingPaymentCheckout: true,
                pendingPaymentTargetIndex: safeTargetIndex,
                pendingStripeStartedAt: new Date().toISOString()
            });
        }

        function clearPendingPaymentCheckout(extra) {
            state.pendingPaymentCheckout = false;
            state.pendingPaymentTargetIndex = null;
            saveSetupProgress(screens[activeIndex]?.dataset.stepId || 'payment', Object.assign({
                pendingPaymentCheckout: false,
                pendingPaymentTargetIndex: null
            }, extra || {}));
        }

        function setPaymentControlsBusy(isBusy, label) {
            const paymentActivationLink = document.getElementById('setupPaymentActivationLink');
            const paymentContinueButton = document.getElementById('setupStripeContinueButton');
            if (paymentActivationLink) {
                paymentActivationLink.setAttribute('aria-disabled', isBusy ? 'true' : 'false');
                paymentActivationLink.textContent = translateSetupText(isBusy ? (label || 'Åbner Revolut...') : (state.pricingPlan === 'custom' ? 'Tal med os om Custom' : 'Gå til Revolut'));
            }
            if (paymentContinueButton) {
                paymentContinueButton.disabled = isBusy;
                paymentContinueButton.textContent = translateSetupText(isBusy ? (label || 'Åbner Revolut...') : 'Fortsæt med Revolut');
            }
        }

        function openPaymentWindowShell() {
            const checkoutWindow = window.open('', 'cohertaPaymentCheckout', 'width=1120,height=860,resizable=yes,scrollbars=yes');
            if (!checkoutWindow) return null;
            try {
                const paymentShellTitle = translateSetupText('Åbner Revolut...');
                const paymentShellCopy = translateSetupText('Du kan blive på Coherta-siden. Flowet fortsætter automatisk, når abonnementet er registreret.');
                const paymentShellLang = setupLocale === 'se' ? 'sv' : (setupLocale === 'uk' ? 'en-GB' : (setupLocale || 'da'));
                checkoutWindow.document.write('<!doctype html><html lang="' + escapeHtml(paymentShellLang) + '"><head><meta charset="utf-8"><title>Coherta Revolut</title><style>body{font-family:Inter,Arial,sans-serif;margin:0;min-height:100vh;display:grid;place-items:center;background:#f7f9ff;color:#071633}.box{max-width:420px;padding:32px;text-align:center}.spinner{width:34px;height:34px;border:3px solid #dce5ff;border-top-color:#315ff4;border-radius:50%;margin:0 auto 18px;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}</style></head><body><div class="box"><div class="spinner"></div><h1>' + escapeHtml(paymentShellTitle) + '</h1><p>' + escapeHtml(paymentShellCopy) + '</p></div></body></html>');
                checkoutWindow.document.close();
                checkoutWindow.focus();
            } catch (error) {}
            return checkoutWindow;
        }

        function subscriptionValueIsActive(value) {
            if (!value || value === false) return false;
            const subscriptionId = String(value).trim();
            return subscriptionId !== ''
                && subscriptionId !== '0'
                && subscriptionId.toLowerCase() !== 'null'
                && subscriptionId.toLowerCase() !== 'undefined';
        }

        function payloadHasActiveSubscription(payload, depth) {
            if (!payload || depth > 5) return false;
            if (Array.isArray(payload)) {
                return payload.some((item) => payloadHasActiveSubscription(item, (depth || 0) + 1));
            }
            if (typeof payload !== 'object') return false;

            // Only the current company can satisfy this funnel's payment gate.
            const companyId = String(payload.company_id || payload.id || '');
            const matchesCompany = !config.companyId || companyId === String(config.companyId);
            if (matchesCompany) {
                if (payload.payment_subscription_active === true) return true;
                for (const key of ['stripe_subscription', 'stripe_subscription_id']) {
                    if (subscriptionValueIsActive(payload[key])) return true;
                }
            }

            for (const key of ['data', 'company', 'companies', 'crm_company', 'current_company', 'user']) {
                if (payloadHasActiveSubscription(payload[key], (depth || 0) + 1)) return true;
            }

            return false;
        }

        async function waitForSubscriptionActivation(settings) {
            const options = settings || {};
            if (state.paymentPolling) return false;

            const targetIndex = Number.isInteger(options.targetIndex)
                ? Math.max(0, Math.min(options.targetIndex, screens.length - 1))
                : Math.max(0, Math.min(findStepIndexById('payment') + 1, screens.length - 1));
            const checkoutWindow = options.checkoutWindow || null;
            const maxAttempts = 90;
            let attempt = 0;
            let lastError = null;

            state.paymentPolling = true;
            state.checkoutOpening = false;
            setPaymentControlsBusy(true, 'Venter på betaling...');
            updateStepChrome();

            try {
                while (attempt < maxAttempts) {
                    attempt += 1;
                    try {
                        const companyPayload = await cohertaApi('/users/me/companies', {method: 'GET'});
                        if (payloadHasActiveSubscription(companyPayload)) {
                            state.paymentConfirmed = true;
                            state.pendingPaymentCheckout = false;
                            state.pendingPaymentTargetIndex = null;
                            config.hasActiveSubscription = true;
                            config.paymentRequired = false;
                            saveSetupProgress('payment', {paymentConfirmed: true, pendingPaymentCheckout: false, pendingPaymentTargetIndex: null});
                            setPaymentStatus('Abonnementet er registreret. Vi fortsætter flowet...', 'success');
                            if (checkoutWindow && !checkoutWindow.closed) {
                                try { checkoutWindow.close(); } catch (error) {}
                            }
                            state.paymentPolling = false;
                            setPaymentControlsBusy(false);
                            updateStepChrome();
                            window.setTimeout(() => goToStep(targetIndex), 350);
                            return true;
                        }
                    } catch (error) {
                        lastError = error;
                    }

                    if (checkoutWindow && checkoutWindow.closed && attempt >= 6) {
                        setPaymentStatus('Revolut-vinduet er lukket, og vi kan endnu ikke se et aktivt abonnement. Åbn Revolut igen, når du er klar.', 'error');
                        return false;
                    }

                    if (attempt === 1) {
                        setPaymentStatus('Revolut er åbnet i et nyt vindue. Bliv på denne side - vi fortsætter automatisk, når abonnementet er aktivt.', '');
                    }
                    await wait(4000);
                }

                setPaymentStatus(lastError?.message || 'Vi kan ikke se et aktivt abonnement endnu. Prøv at åbne Revolut igen eller genindlæs siden efter betaling.', 'error');
                return false;
            } finally {
                state.paymentPolling = false;
                setPaymentControlsBusy(false);
                updateStepChrome();
            }
        }

        async function openPaymentCheckout(event, options) {
            if (event && typeof event.preventDefault === 'function') event.preventDefault();
            if (clearhausTestMode) return false;
            const settings = options || {};
            const targetIndex = Number.isInteger(settings.targetIndex)
                ? Math.max(0, Math.min(settings.targetIndex, screens.length - 1))
                : Math.max(0, Math.min(activeIndex + 1, screens.length - 1));

            if (paymentStepCanContinue()) {
                if (settings.continueAfterPayment) goToStep(targetIndex);
                return true;
            }

            if (state.checkoutOpening || state.paymentPolling) return false;

            if (state.pricingPlan === 'custom') {
                const contactUrl = buildPaymentActivationUrl(state.pricingPlan, state.billingCycle);
                const contactWindow = settings.openInNewWindow === false ? null : window.open(contactUrl, 'cohertaCustomPlan');
                if (!contactWindow) window.location.href = contactUrl;
                setPaymentStatus('Custom kræver en aftale, før flowet kan fortsætte.', 'error');
                return false;
            }

            const shouldOpenCheckoutWindow = settings.openInNewWindow !== false && !isLikelyMobileCheckout();
            const checkoutWindow = shouldOpenCheckoutWindow ? openPaymentWindowShell() : null;
            if (shouldOpenCheckoutWindow && !checkoutWindow) {
                setPaymentStatus('Tillad popups for denne side, så kan vi åbne Revolut i et nyt vindue.', 'error');
                return false;
            }

            state.checkoutOpening = true;
            state.pendingPaymentTargetIndex = targetIndex;
            setPaymentControlsBusy(true, 'Åbner Revolut...');
            updateStepChrome();
            setPaymentStatus('Forbereder sikker Revolut checkout...', '');

            try {
                if (!state.userSaved || !currentUserId()) {
                    const saved = await saveAccountIdentityStep();
                    if (!saved) {
                        throw new Error('Opret bruger i første step, før du åbner Revolut.');
                    }
                }

                savePendingPaymentCheckout(targetIndex);

                const checkout = await createPaymentCheckout(buildPaymentCheckoutPayload());
                const checkoutUrl = checkout.url;
                if (checkout.provider !== 'revolut' || !isRevolutCheckoutUrl(checkoutUrl)) {
                    throw new Error('Revolut returnerede ikke en checkout URL.');
                }

                if (checkoutWindow && !checkoutWindow.closed) {
                    checkoutWindow.location.href = checkoutUrl;
                    state.paymentCheckoutWindow = checkoutWindow;
                    waitForSubscriptionActivation({targetIndex: targetIndex, checkoutWindow: checkoutWindow});
                } else {
                    window.location.href = checkoutUrl;
                }
                return true;
            } catch (error) {
                state.checkoutOpening = false;
                state.pendingPaymentCheckout = false;
                state.pendingPaymentTargetIndex = null;
                saveSetupProgress(screens[activeIndex]?.dataset.stepId || 'payment', {pendingPaymentCheckout: false, pendingPaymentTargetIndex: null});
                if (checkoutWindow && !checkoutWindow.closed) {
                    try { checkoutWindow.close(); } catch (closeError) {}
                }

                if (error.status === 409) {
                    // Conflicts include pending/paused checkouts as well as active subscriptions.
                    // Re-read the authenticated company instead of trusting the error text.
                    try {
                        const companies = await cohertaApi('/users/me/companies', {method: 'GET'});
                        if (payloadHasActiveSubscription(companies)) {
                            config.hasActiveSubscription = true;
                            state.paymentConfirmed = true;
                            config.paymentRequired = false;
                            setPaymentStatus('Vi fandt dit aktive abonnement. Vi fortsætter flowet...', 'success');
                            setPaymentControlsBusy(false);
                            updateStepChrome();
                            if (settings.continueAfterPayment) window.setTimeout(() => goToStep(targetIndex), 350);
                            return true;
                        }
                    } catch (verificationError) {}
                }
                setPaymentStatus(error.message || 'Revolut checkout kunne ikke åbnes lige nu.', 'error');
                setPaymentControlsBusy(false);
                updateStepChrome();
                return false;
            }
        }

        function bindPaymentActivationLink() {
            const paymentActivationLink = document.getElementById('setupPaymentActivationLink');
            if (!paymentActivationLink || paymentActivationLink.dataset.paymentBound === 'true') return;
            paymentActivationLink.dataset.paymentBound = 'true';
            paymentActivationLink.addEventListener('click', (event) => openPaymentCheckout(event, {
                openInNewWindow: true,
                continueAfterPayment: true,
                targetIndex: activeIndex + 1
            }));
            document.querySelectorAll('[data-payment-continue]').forEach((button) => {
                if (button.dataset.paymentBound === 'true') return;
                button.dataset.paymentBound = 'true';
                button.addEventListener('click', (event) => openPaymentCheckout(event, {
                    openInNewWindow: true,
                    continueAfterPayment: true,
                    targetIndex: activeIndex + 1
                }));
            });
        }

        function normalizePaymentReturnStatus(value) {
            const clean = String(value || '').trim().toLowerCase();
            if (!clean) return '';
            if (['success', 'succeeded', 'complete', 'completed', 'paid', 'active', 'trialing'].includes(clean)) return 'success';
            if (['cancel', 'cancelled', 'canceled'].includes(clean)) return 'cancelled';
            if (['failed', 'failure', 'error', 'expired'].includes(clean)) return 'failed';
            return '';
        }

        function paymentReturnStatus() {
            if (!paymentEnabled) return '';
            const params = new URLSearchParams(window.location.search);
            if (params.get('payment_provider') === 'revolut' && params.has('checkout_reference')) {
                return 'success';
            }
            for (const key of ['stripe', 'checkout', 'checkout_status', 'payment', 'payment_status', 'billing', 'billing_status', 'redirect_status', 'setup_status', 'status']) {
                const status = normalizePaymentReturnStatus(params.get(key));
                if (status) return status;
            }
            const hasCheckoutIdentifier = ['session_id', 'checkout_session_id', 'checkoutSessionId', 'setup_intent', 'payment_intent'].some((key) => params.has(key));
            if (hasCheckoutIdentifier && (params.get('payment_return') === '1' || state.pendingPaymentCheckout || storedSetupProgress.pendingPaymentCheckout)) {
                return 'success';
            }
            return '';
        }

        function nextStepAfterPaymentIndex() {
            const paymentIndex = findStepIndexById('payment');
            const storedTargetIndex = Number(state.pendingPaymentTargetIndex || storedSetupProgress.pendingPaymentTargetIndex || 0);
            // PHP omits the payment screen once active; saved numeric indices then shift.
            if (paymentIndex >= 0 && storedTargetIndex > 0) return Math.max(0, Math.min(storedTargetIndex, screens.length - 1));
            if (paymentIndex >= 0) return Math.max(0, Math.min(paymentIndex + 1, screens.length - 1));
            const linkedinIndex = findStepIndexById('linkedin');
            return linkedinIndex >= 0 ? linkedinIndex : 0;
        }

        function notifyOpenerAboutPaymentReturn() {
            const status = paymentReturnStatus();
            if (!status || !window.opener || window.opener === window) return false;
            try {
                window.opener.postMessage({
                    type: 'COHERTA_PAYMENT_CHECKOUT_RETURN',
                    status: status
                }, window.location.origin);
            } catch (error) {}
            if (status === 'success') {
                setPaymentStatus('Betalingen er sendt tilbage til Coherta. Hovedvinduet fortsætter, når abonnementet er registreret.', 'success');
            } else if (status === 'cancelled' || status === 'canceled' || status === 'cancel') {
                setPaymentStatus('Betalingen blev afbrudt. Gå tilbage til Coherta-vinduet for at prøve igen.', 'error');
            }
            return true;
        }

        function handlePaymentCheckoutReturnMessage(event) {
            if (!paymentEnabled) return;
            if (event.origin !== window.location.origin) return;
            if (!state.paymentCheckoutWindow || event.source !== state.paymentCheckoutWindow) return;
            const data = event.data || {};
            if (data.type !== 'COHERTA_PAYMENT_CHECKOUT_RETURN') return;

            const status = normalizePaymentReturnStatus(data.status);
            if (status === 'success') {
                setPaymentStatus('Revolut er gennemført. Vi bekræfter abonnementet hos Coherta...', '');
                waitForSubscriptionActivation({
                    targetIndex: state.pendingPaymentTargetIndex || nextStepAfterPaymentIndex(),
                    checkoutWindow: state.paymentCheckoutWindow
                });
            } else if (status === 'cancelled') {
                clearPendingPaymentCheckout({currentStepId: 'payment'});
                setPaymentStatus('Betalingen blev afbrudt. Åbn Revolut igen for at fortsætte flowet.', 'error');
            } else if (status === 'failed') {
                clearPendingPaymentCheckout({currentStepId: 'payment'});
                setPaymentStatus('Revolut kunne ikke gennemføre betalingen. Åbn Revolut igen for at fortsætte flowet.', 'error');
            }
        }

        function handlePaymentReturnOnLoad() {
            const status = paymentReturnStatus();
            if (!status) return;

            if (notifyOpenerAboutPaymentReturn() && !isLikelyMobileCheckout()) return;

            const targetIndex = nextStepAfterPaymentIndex();

            const paymentIndex = findStepIndexById('payment');
            if (paymentIndex >= 0 && activeIndex !== paymentIndex) {
                goToStep(paymentIndex);
            }

            if (status === 'success') {
                setPaymentStatus('Revolut er gennemført. Vi bekræfter abonnementet hos Coherta...', '');
                waitForSubscriptionActivation({targetIndex: targetIndex});
            } else if (status === 'cancelled') {
                clearPendingPaymentCheckout({currentStepId: 'payment'});
                setPaymentStatus('Betalingen blev afbrudt. Åbn Revolut igen for at fortsætte flowet.', 'error');
            } else if (status === 'failed') {
                clearPendingPaymentCheckout({currentStepId: 'payment'});
                setPaymentStatus('Revolut kunne ikke gennemføre betalingen. Åbn Revolut igen for at fortsætte flowet.', 'error');
            }
        }

        function renderSourcePanel() {
            sourcePanel.innerHTML = sourcePanels[state.source] || sourcePanels.url;
            localizeSetupText(sourcePanel);
            const sourceTitle = document.getElementById('sourceTitle');
            const sourceCountry = document.getElementById('sourceCountry');
            const sourceUrl = document.getElementById('sourceUrl');
            const manualLeads = document.getElementById('manualLeads');
            populateCountrySelect(sourceCountry);
            if (sourceTitle && storedSetupProgress.sourceTitle) sourceTitle.value = storedSetupProgress.sourceTitle;
            if (sourceCountry && storedSetupProgress.sourceCountry) sourceCountry.value = storedSetupProgress.sourceCountry;
            if (sourceUrl && storedSetupProgress.sourceUrl) sourceUrl.value = storedSetupProgress.sourceUrl;
            if (manualLeads && storedSetupProgress.manualLeads) manualLeads.value = storedSetupProgress.manualLeads;
            bindLinkedInSourceMode();
            bindCsvUpload();
            bindSourcePersistence();
            updateSummary();
        }

        function bindLinkedInSourceMode() {
            const sourceTitle = document.getElementById('sourceTitle');
            const sourceCountry = document.getElementById('sourceCountry');
            const sourceUrl = document.getElementById('sourceUrl');
            if (!sourceTitle || !sourceCountry || !sourceUrl) return;

            const syncSourceMode = () => {
                const profileUsed = sourceTitle.value.trim() !== '' || sourceCountry.value !== '';
                const urlUsed = sourceUrl.value.trim() !== '';
                sourceUrl.disabled = profileUsed && !urlUsed;
                sourceTitle.disabled = urlUsed;
                sourceCountry.disabled = urlUsed;
            };

            sourceTitle.addEventListener('input', syncSourceMode);
            sourceCountry.addEventListener('change', syncSourceMode);
            sourceUrl.addEventListener('input', syncSourceMode);
            syncSourceMode();
        }

        function wait(ms) {
            return new Promise((resolve) => window.setTimeout(resolve, ms));
        }

        function headersJson(useAuth) {
            const headers = {'Content-Type': 'application/json'};
            const token = useAuth ? getSessionId() : '';
            if (token) headers.Authorization = 'Bearer ' + token;
            return headers;
        }

        async function saveAccountIdentityStep() {
            if (signupBlocked) {
                setIdentityStatus(config.signupNotice, '');
                return false;
            }
            if (!state.accountType) {
                setIdentityStatus('Vælg først om du bruger Coherta som privatperson eller virksomhed.', 'error');
                return false;
            }

            applyAccountTypePricingRules();

            const name = setupUserName ? setupUserName.value.trim() : '';
            const email = setupUserEmail ? setupUserEmail.value.trim() : '';

            if (name.length <= 1) {
                setIdentityStatus('Indtast dit navn før du fortsætter.', 'error');
                return false;
            }
            if (!isValidEmail(email)) {
                setIdentityStatus('Indtast en gyldig email før du fortsætter.', 'error');
                return false;
            }

            const identityUnchanged = state.userSaved
                && currentUserId() > 0
                && name === state.userName
                && email === state.userEmail
                && storedSetupProgress.accountTypeSaved === state.accountType
                && storedSetupProgress.pricingPlan === state.pricingPlan
                && storedSetupProgress.billingCycle === state.billingCycle;

            if (identityUnchanged) {
                saveSetupProgress('account-type');
                setIdentityStatus('', '');
                return true;
            }

            state.userSaving = true;
            updateStepChrome();
            setIdentityStatus('Gemmer bruger...', '');
            saveSetupProgress('account-type');

            try {
                const sessionId = getSessionId();
                const shouldCreateUser = !truthy(config.userLoggedIn) || !currentUserId();
                const countryCode = normalizeCheckoutCountryCode(config.selectedMarket || 'DK');
                const selectedMonthlyPrice = getBillingMonthlyPrice(state.pricingModel, state.billingCycle);
                const apiPlan = checkoutApiPlan(state.pricingPlan);
                if (sessionId && !shouldCreateUser) {
                    const response = await fetch('https://api.coherta.com/users/me', {
                        method: 'PATCH',
                        headers: headersJson(true),
                        body: JSON.stringify({
                            full_name: name,
                            email: email,
                            account_type: state.accountType,
                            setup_account_type: state.accountType,
                            plan_key: state.pricingPlan,
                            subscription_plan: apiPlan,
                            billing_cycle: state.billingCycle,
                            billing_period: state.billingPeriodKey,
                            pricing_model: state.pricingPlan,
                            coherta_pricing_model: state.pricingPlan,
                            monthly_price: selectedMonthlyPrice,
                            custom_pricing: true,
                            custom_monthly_price: selectedMonthlyPrice,
                            currency: state.pricingModel?.currency || 'DKK',
                            country: countryCode,
                            country_code: countryCode,
                            stripe_product_key: state.pricingModel?.stripeProductKey || '',
                            stripe_product_id: state.pricingModel?.stripeProductId || ''
                        })
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(data.message || data.error || 'Brugeren kunne ikke gemmes i Coherta lige nu.');
                    state.userId = Number(state.userId || config.userId || extractUserId(data) || 0);
                    persistUserId(state.userId || config.userId);
                    await saveCompanyAccountType(state.accountType);
                } else {
                    const nameParts = splitName(name);
                    const companyName = state.accountType === 'personal' ? name : (config.companyName || 'Default Company');
                    const affiliateAttribution = getAffiliateAttribution();
                    const affiliatePayload = {};
                    if (affiliateAttribution.partnerId) {
                        affiliatePayload.partner_id = affiliateAttribution.partnerId;
                    }
                    if (affiliateAttribution.transactionId) {
                        affiliatePayload.other_data = {
                            transaction_id: affiliateAttribution.transactionId,
                            affiliate_id: affiliateAttribution.bo_affiliateId
                        };
                    }
                    const response = await fetch('https://api.coherta.com/users/create', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            phone: '',
                            source: 'setup-funnel',
                            stripe_enabled: false,
                            username: email,
                            password: true,
                            email: email,
                            full_name: name,
                            first_name: nameParts.first_name,
                            last_name: nameParts.last_name,
                            company_name: companyName,
                            use_case: 'Setup funnel: ' + state.accountType,
                            account_type: state.accountType,
                            setup_account_type: state.accountType,
                            plan: apiPlan,
                            plan_key: state.pricingPlan,
                            subscription_plan: apiPlan,
                            billing_cycle: state.billingCycle,
                            billing_period: state.billingPeriodKey,
                            pricing_model: state.pricingPlan,
                            coherta_pricing_model: state.pricingPlan,
                            monthly_price: selectedMonthlyPrice,
                            custom_pricing: true,
                            custom_monthly_price: selectedMonthlyPrice,
                            currency: state.pricingModel?.currency || 'DKK',
                            country: countryCode,
                            country_code: countryCode,
                            stripe_product_key: state.pricingModel?.stripeProductKey || '',
                            stripe_product_id: state.pricingModel?.stripeProductId || '',
                            ...affiliatePayload,
                            crm_company: {
                                account_type: state.accountType,
                                company_name: companyName,
                                plan_key: state.pricingPlan,
                                subscription_plan: apiPlan,
                                billing_cycle: state.billingCycle,
                                billing_period: state.billingPeriodKey,
                                pricing_model: state.pricingPlan,
                                coherta_pricing_model: state.pricingPlan,
                                monthly_price: selectedMonthlyPrice,
                                custom_pricing: true,
                                custom_monthly_price: selectedMonthlyPrice,
                                currency: state.pricingModel?.currency || 'DKK',
                                country: countryCode,
                                country_code: countryCode,
                                stripe_product_key: state.pricingModel?.stripeProductKey || '',
                                stripe_product_id: state.pricingModel?.stripeProductId || ''
                            },
                            token: true,
                            session_id: true,
                            return_session: true
                        })
                    });
                    const data = await response.json().catch(() => ({}));
                    const newSessionId = extractSessionId(data);
                    const newUserId = extractUserId(data);
                    if (!response.ok && !newSessionId && !newUserId) throw new Error(data.message || data.error || 'Brugeren kunne ikke oprettes lige nu.');
                    persistSessionId(newSessionId);
                    if (newUserId) {
                        state.userId = newUserId;
                        persistUserId(newUserId);
                    }
                    if (!currentUserId()) throw new Error(data.message || data.error || 'Brugeren blev oprettet, men Coherta returnerede ikke et user_id.');
                    if (!getSessionId()) throw new Error(data.message || data.error || 'Brugeren blev oprettet, men Coherta returnerede ikke en session.');
                }

                state.userName = name;
                state.userEmail = email;
                state.userSaved = true;
                saveSetupProgress('account-type', {accountTypeSaved: state.accountType});
                setIdentityStatus('', '');
                return true;
            } catch (error) {
                if (getSessionId() && currentUserId()) {
                    state.userName = name;
                    state.userEmail = email;
                    state.userSaved = true;
                    persistUserId(currentUserId());
                    saveSetupProgress('account-type', {userSaveWarning: error.message || 'Kun gemt lokalt.'});
                    setIdentityStatus('Vi gemmer valget lokalt og fortsætter setup.', 'success');
                    return true;
                }

                setIdentityStatus(error.message || 'Brugeren kunne ikke oprettes. Prøv igen.', 'error');
                return false;
            } finally {
                state.userSaving = false;
                updateStepChrome();
            }
        }

        async function saveCompanyAccountType(accountType) {
            applyAccountTypePricingRules();
            const cleanAccountType = String(accountType || '').trim();
            if (!cleanAccountType || !getSessionId()) return null;
            const countryCode = normalizeCheckoutCountryCode(config.selectedMarket || 'DK');
            const selectedMonthlyPrice = getBillingMonthlyPrice(state.pricingModel, state.billingCycle);
            const apiPlan = checkoutApiPlan(state.pricingPlan);

            const response = await fetch('https://api.coherta.com/users/me/companies', {
                method: 'PATCH',
                headers: headersJson(true),
                body: JSON.stringify({
                    account_type: cleanAccountType,
                    setup_account_type: cleanAccountType,
                    plan_key: state.pricingPlan,
                    subscription_plan: apiPlan,
                    billing_cycle: state.billingCycle,
                    billing_period: state.billingPeriodKey,
                    pricing_model: state.pricingPlan,
                    coherta_pricing_model: state.pricingPlan,
                    monthly_price: selectedMonthlyPrice,
                    custom_pricing: true,
                    custom_monthly_price: selectedMonthlyPrice,
                    currency: state.pricingModel?.currency || 'DKK',
                    country: countryCode,
                    country_code: countryCode,
                    stripe_product_key: state.pricingModel?.stripeProductKey || '',
                    stripe_product_id: state.pricingModel?.stripeProductId || ''
                })
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || data.error || data.detail || 'Company account type kunne ikke gemmes.');
            return data;
        }

        async function saveCompanyAiTool(aiTool) {
            const cleanAiTool = String(aiTool || '').trim();
            if (!cleanAiTool || !getSessionId()) {
                throw new Error('Coherta session mangler. Genindlæs siden og prøv igen.');
            }

            const response = await fetch('https://api.coherta.com/users/me/companies', {
                method: 'PATCH',
                headers: headersJson(true),
                body: JSON.stringify({
                    ai_tools: cleanAiTool,
                    ai_tool: cleanAiTool,
                    setup_ai_tool: cleanAiTool,
                    crm_company: {
                        ai_tools: cleanAiTool
                    }
                })
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || data.error || data.detail || 'AI-værktøj kunne ikke gemmes.');
            return data;
        }

        async function cohertaApi(path, options) {
            const settings = options || {};
            const response = await fetch('https://api.coherta.com' + path, {
                method: settings.method || 'GET',
                headers: headersJson(settings.auth !== false),
                body: settings.body === undefined ? undefined : JSON.stringify(settings.body)
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || data.error || data.detail || 'Coherta kunne ikke gemme ændringen lige nu.');
            }
            return data;
        }

        function campaignTitleValue() {
            const cleanTitle = campaignName ? campaignName.value.trim() : '';
            if (cleanTitle) return cleanTitle;
            const fallback = translateSetupText('Min første campaign');
            if (campaignName) campaignName.value = fallback;
            return fallback;
        }

        function syncCampaignWizard() {
            const mode = state.campaignMode || '';
            if (campaignGoal) campaignGoal.value = mode;
            if (!mode) {
                if (campaignName) campaignName.value = '';
                if (setupGeneratedCampaignTitle) setupGeneratedCampaignTitle.textContent = 'Generated automatically';
                if (setupCampaignWorkflowPreview) setupCampaignWorkflowPreview.innerHTML = '';
                if (setupEmployeeQuestion) setupEmployeeQuestion.textContent = 'Which employee will run this campaign?';
                if (setupDecisionMakerLabel) setupDecisionMakerLabel.textContent = 'Who do you want to reach?';
                if (setupQualificationField) setupQualificationField.hidden = true;
                return;
            }
            state.playbook = mode === 'sales' ? 'dialogue' : (mode === 'advanced' ? 'blank' : 'network');
            state.source = 'url';
            const titleInput = document.getElementById('sourceTitle');
            const countryInput = document.getElementById('sourceCountry');
            const countryName = countryInput?.value ? (countryInput.options[countryInput.selectedIndex]?.textContent || '') : '';
            const employeeName = String(state.userName || setupUserName?.value || '').trim();
            const labels = {connections:'More Connections', sales:'More Sales', advanced:'Advanced Setup'};
            const generated = [labels[mode] || 'Campaign', employeeName, mode === 'advanced' ? '' : titleInput?.value.trim(), mode === 'advanced' ? '' : countryName].filter(Boolean).join(' - ');
            if (campaignName) campaignName.value = generated;
            if (setupGeneratedCampaignTitle) setupGeneratedCampaignTitle.textContent = generated || 'Generated automatically';
            if (setupEmployeeQuestion) setupEmployeeQuestion.textContent = mode === 'connections' ? 'Which employee needs more connections?' : 'Which employee should we get more sales leads for?';
            if (setupDecisionMakerLabel) setupDecisionMakerLabel.textContent = mode === 'connections' ? 'Which job title do you want to connect with?' : 'Job title of the decision maker';
            if (setupQualificationField) setupQualificationField.hidden = mode !== 'sales';
            const audience = document.getElementById('setupAudienceFields');
            if (audience) audience.hidden = mode === 'advanced';
            if (setupCampaignWorkflowPreview) setupCampaignWorkflowPreview.innerHTML = mode === 'connections'
                ? '<strong>Workflow created automatically</strong><br>Connect → If not accepted → Wait 35 days → Delete Connection Request'
                : (mode === 'sales' ? '<strong>Workflow created automatically</strong><br>Automated Screening → Connect → accepted/not accepted paths' : '<strong>No workflow will be created</strong><br>You can build the campaign workflow after creation.');
            applyPlaybook(state.playbook);
        }

        function extractCampaignId(data) {
            return Number(data?.id || data?.campaign_id || data?.campaign?.id || data?.data?.id || 0);
        }

        async function createCampaignRecord(title) {
            return await cohertaApi('/campaigns', {
                method: 'POST',
                body: {
                    title: title,
                    mode: 'scratch'
                }
            });
        }

        async function saveCampaignNameStep(options) {
            const settings = options || {};
            if (!getSessionId()) {
                setCampaignStatus('Coherta session mangler. Opret eller opdater brugeren først.', 'error');
                return false;
            }

            state.campaignSaving = true;
            updateStepChrome();
            const creatingCampaign = settings.forceCreate || !state.campaignId;
            if (!settings.silent) {
                setCampaignStatus(creatingCampaign ? 'Opretter campaign...' : 'Opdaterer campaign...', '');
            }
            if (settings.mirrorStatus === 'source') {
                setSourceStatus(creatingCampaign ? 'Opretter campaign...' : 'Opdaterer campaign...', '');
            }
            if (settings.mirrorStatus === 'workflow') {
                setWorkflowStatus(creatingCampaign ? 'Opretter campaign...' : 'Opdaterer campaign...', '');
            }

            try {
                const title = campaignTitleValue();
                let data = {};
                if (settings.forceCreate || !state.campaignId) {
                    data = await createCampaignRecord(title);
                    state.campaignId = extractCampaignId(data);
                } else {
                    data = await cohertaApi('/campaigns', {
                        method: 'PATCH',
                        body: {
                            id: state.campaignId,
                            title: title
                        }
                    });
                }

                if (!state.campaignId) {
                    throw new Error('Campaign blev oprettet, men ID mangler i API-svaret.');
                }

                saveSetupProgress('campaign', {
                    campaignId: state.campaignId,
                    campaignSaved: true
                });
                if (!settings.silent) setCampaignStatus('', '');
                return true;
            } catch (error) {
                const message = error.message || 'Campaign kunne ikke gemmes. Prøv igen.';
                setCampaignStatus(message, 'error');
                if (settings.mirrorStatus === 'source') setSourceStatus(message, 'error');
                if (settings.mirrorStatus === 'workflow') setWorkflowStatus(message, 'error');
                return false;
            } finally {
                state.campaignSaving = false;
                updateStepChrome();
            }
        }

        async function ensureCampaign(mirrorStatus) {
            if (state.campaignId) return state.campaignId;
            const saved = await saveCampaignNameStep({silent: true, mirrorStatus: mirrorStatus});
            return saved ? state.campaignId : 0;
        }

        function buildCampaignSourcePayload(campaignId) {
            const sourceProgress = getSourceProgress();
            const base = {
                action: 'add_leads',
                campaign_id: campaignId
            };

            if (state.source === 'csv') {
                return Object.assign(base, {
                    source_type: 'csv',
                    csv_filename: state.csvFileName || null,
                    linkedin_urls: state.csvLinkedInUrls || [],
                    setup_item_count: state.csvItemCount
                });
            }

            if (state.source === 'manual') {
                return Object.assign(base, {
                    source_type: 'manual',
                    manual_leads: sourceProgress.manualLeads || ''
                });
            }

            if (sourceProgress.sourceUrl) {
                return Object.assign(base, {
                    source_type: 'linkedin_search_url',
                    linkedin_search_url: sourceProgress.sourceUrl
                });
            }

            if (sourceProgress.sourceTitle || sourceProgress.sourceCountry) {
                return Object.assign(base, {
                    source_type: 'employee_title_location',
                    employee_title: sourceProgress.sourceTitle || '',
                    country: setupCountryFullName(sourceProgress.sourceCountry) || null
                });
            }

            return null;
        }

        async function saveCampaignSourceStep() {
            const campaignId = await ensureCampaign('source');
            if (!campaignId) return false;

            const payload = buildCampaignSourcePayload(campaignId);
            if (!payload) {
                state.campaignSaving = true;
                updateStepChrome();
                setSourceStatus('Opdaterer campaign...', '');

                try {
                    await cohertaApi('/campaigns', {
                        method: 'PATCH',
                        body: {
                            id: campaignId,
                            title: campaignTitleValue()
                        }
                    });
                    saveSetupProgress('source', {
                        campaignId: campaignId,
                        campaignSourceSaved: false
                    });
                    setSourceStatus('', '');
                    return true;
                } catch (error) {
                    setSourceStatus(error.message || 'Campaignen kunne ikke opdateres.', 'error');
                    return false;
                } finally {
                    state.campaignSaving = false;
                    updateStepChrome();
                }
            }

            state.campaignSaving = true;
            updateStepChrome();
            setSourceStatus('Gemmer kilde på campaign...', '');

            try {
                await cohertaApi('/campaigns', {
                    method: 'POST',
                    body: payload
                });
                saveSetupProgress('source', {
                    campaignId: campaignId,
                    campaignSourceSaved: true
                });
                setSourceStatus('', '');
                return true;
            } catch (error) {
                setSourceStatus(error.message || 'Kilden kunne ikke gemmes på campaignen.', 'error');
                return false;
            } finally {
                state.campaignSaving = false;
                updateStepChrome();
            }
        }

        function buildCampaignWorkflowPayload() {
            const workflowPayload = JSON.parse(JSON.stringify(workflow));
            workflowPayload.setup = {
                playbook: state.playbook,
                source: state.source,
                sourceDetails: getSourceProgress(),
                csvItemCount: state.csvItemCount,
                csvLinkedInUrlCount: state.csvLinkedInUrlCount,
                csvFileName: state.csvFileName,
                accountType: state.accountType,
                pricingPlan: state.pricingPlan,
                billingCycle: state.billingCycle,
                billingPeriodKey: state.billingPeriodKey,
                pricingModel: state.pricingModel,
                campaignGoal: campaignGoal ? campaignGoal.value : ''
            };
            return workflowPayload;
        }

        async function saveCampaignWorkflowStep() {
            const campaignId = await ensureCampaign('workflow');
            if (!campaignId) return false;

            state.campaignSaving = true;
            updateStepChrome();
            setWorkflowStatus('Gemmer workflow på campaign...', '');

            try {
                await cohertaApi('/campaigns', {
                    method: 'PATCH',
                    body: {
                        id: campaignId,
                        title: campaignTitleValue(),
                        workflow: JSON.stringify(buildCampaignWorkflowPayload())
                    }
                });
                state.workflowSaved = true;
                saveSetupProgress('workflow', {
                    campaignId: campaignId,
                    workflowSaved: true
                });
                setWorkflowStatus('', '');
                return true;
            } catch (error) {
                setWorkflowStatus(error.message || 'Workflowet kunne ikke gemmes på campaignen.', 'error');
                return false;
            } finally {
                state.campaignSaving = false;
                updateStepChrome();
            }
        }

        async function connectCurrentEmployeeToCampaign() {
            const campaignId = await ensureCampaign('linkedin');
            if (!campaignId) return false;
            const employeeId = Number(state.userId || config.userId || storedSetupProgress.userId || 0);

            state.campaignSaving = true;
            updateStepChrome();
            setLinkedInStatus('Kobler din employee til campaign...', '');

            try {
                const payload = {
                    id: campaignId,
                    connect_current_employee: true
                };
                if (employeeId) {
                    payload.employee_ids = [employeeId];
                }

                await cohertaApi('/campaigns', {
                    method: 'PATCH',
                    body: payload
                });
                saveSetupProgress('linkedin', {
                    campaignId: campaignId,
                    campaignEmployeeId: employeeId || null,
                    campaignEmployeeConnected: true
                });
                setLinkedInStatus('', '');
                return true;
            } catch (error) {
                setLinkedInStatus(error.message || 'Employee kunne ikke kobles til campaignen.', 'error');
                return false;
            } finally {
                state.campaignSaving = false;
                updateStepChrome();
            }
        }

        function setLinkedInStatus(message, type) {
            if (!linkedInStatus) return;
            const isError = type === 'error';
            linkedInError.textContent = isError ? translateSetupText(message || '') : '';
            linkedInError.hidden = !isError || !message;
            linkedInStatus.textContent = isError ? '' : translateSetupText(message || '');
            linkedInStatus.hidden = isError || !message;
            linkedInStatus.classList.toggle('success', type === 'success');
            linkedInStatus.classList.toggle('error', type === 'error');
        }

        function setAiToolStatus(message, type) {
            if (!aiToolStatus) return;
            aiToolStatus.textContent = translateSetupText(message || '');
            aiToolStatus.classList.toggle('success', type === 'success');
            aiToolStatus.classList.toggle('error', type === 'error');
        }

        function setLinkedInBusy(isBusy, label) {
            if (!directLinkedInLoginBtn) return;
            directLinkedInLoginBtn.disabled = isBusy;
            directLinkedInLoginBtn.textContent = translateSetupText(isBusy ? (label || 'Forbinder...') : 'Forbind LinkedIn');
        }

        function setExtensionLinkedInBusy(isBusy, label) {
            if (!extensionLinkedInConnectBtn) return;
            extensionLinkedInConnectBtn.disabled = isBusy;
            extensionLinkedInConnectBtn.textContent = translateSetupText(isBusy ? (label || 'Forbinder...') : 'Forbind LinkedIn');
        }

        function setPluginLoginBusy(isBusy) {
            if (!pluginLoginBtn) return;
            pluginLoginBtn.disabled = isBusy;
            pluginLoginBtn.textContent = translateSetupText(isBusy ? 'Logger plugin ind...' : 'Log plugin ind');
        }

        function setTwoFactorVisible(isVisible) {
            if (linkedInTwoFactor) linkedInTwoFactor.classList.toggle('show', isVisible);
        }

        function isChromeBrowser() {
            const ua = navigator.userAgent || '';
            return /(Chrome|CriOS)\//.test(ua) && !/(Edg|OPR|Opera|Firefox|SamsungBrowser)\//.test(ua);
        }

        function isStoredUserIdSession(value) {
            const cleanValue = String(value || '').trim();
            if (!cleanValue) return false;
            const setupUserId = String(currentUserId() || window.localStorage.getItem('coherta_setup_user_id') || '').trim();
            return (!!setupUserId && cleanValue === setupUserId) || /^[0-9]+$/.test(cleanValue);
        }

        function getSessionId() {
            try {
                const candidates = [
                    bearerToken,
                    document.body.getAttribute('session_id'),
                    window.localStorage.getItem('access_token'),
                    window.sessionStorage.getItem('access_token'),
                    window.localStorage.getItem('coherta_auth_session_id'),
                    window.sessionStorage.getItem('coherta_auth_session_id'),
                    window.sessionStorage.getItem('session_id'),
                    window.localStorage.getItem('session_id')
                ];
                for (const candidate of candidates) {
                    const clean = String(candidate || '').trim();
                    if (clean && !isStoredUserIdSession(clean)) return clean;
                }
                return '';
            } catch (error) {
                const fallback = bearerToken || document.body.getAttribute('session_id') || '';
                return isStoredUserIdSession(fallback) ? '' : fallback;
            }
        }

        function requestCohertaExtension(action, payload, timeoutMs) {
            const requestId = 'coherta-' + Date.now() + '-' + (++extensionBridgeSeq);
            const requestPayload = payload && typeof payload === 'object' ? payload : {};
            const waitMs = Number(timeoutMs) > 0 ? Number(timeoutMs) : 15000;

            return new Promise((resolve, reject) => {
                const timer = window.setTimeout(() => {
                    extensionBridgePending.delete(requestId);
                    reject(new Error('Coherta plugin svarede ikke. Er det installeret?'));
                }, waitMs);

                extensionBridgePending.set(requestId, {resolve: resolve, reject: reject, timer: timer});

                window.postMessage({
                    channel: EXTENSION_BRIDGE_CHANNEL,
                    source: EXTENSION_PAGE_SOURCE,
                    requestId: requestId,
                    action: action,
                    payload: requestPayload
                }, window.location.origin);
            });
        }

        function handleCohertaExtensionMessage(event) {
            if (event.source !== window || event.origin !== window.location.origin) return;

            const message = event.data;
            if (!message || message.channel !== EXTENSION_BRIDGE_CHANNEL || message.source !== EXTENSION_SOURCE) return;

            const waiter = extensionBridgePending.get(message.requestId);
            if (!waiter) return;

            window.clearTimeout(waiter.timer);
            extensionBridgePending.delete(message.requestId);

            if (message.ok) waiter.resolve(message);
            else waiter.reject(new Error(message.error || 'Coherta plugin-fejl.'));
        }

        function isLinkedInConnectedFromExtensionStatus(status) {
            return !!(
                status?.linkedIn?.connected
                || status?.linkedin?.connected
                || status?.linkedinConnected
                || status?.linkedInConnected
            );
        }

        function markLinkedInConnected(message) {
            linkedInApproval.update(false);
            state.linkedinConnected = true;
            state.linkedinStatus = 'connected';
            setLinkedInBusy(false);
            setExtensionLinkedInBusy(false);
            setPluginLoginBusy(false);
            setTwoFactorVisible(false);

            if (directLinkedInLoginBtn) {
                directLinkedInLoginBtn.hidden = true;
                directLinkedInLoginBtn.disabled = true;
            }
            if (linkedInCodeBtn) {
                linkedInCodeBtn.disabled = true;
            }
            if (pluginLoginBtn) {
                pluginLoginBtn.hidden = true;
                pluginLoginBtn.disabled = true;
            }
            if (extensionLinkedInConnectBtn) {
                extensionLinkedInConnectBtn.hidden = true;
                extensionLinkedInConnectBtn.disabled = true;
            }
            if (chromePluginBtn) {
                if (CHROME_EXTENSION_COMING_SOON) {
                    chromePluginBtn.hidden = false;
                    chromePluginBtn.disabled = true;
                    chromePluginBtn.setAttribute('aria-disabled', 'true');
                    chromePluginBtn.textContent = translateSetupText('Coming soon');
                } else {
                    chromePluginBtn.hidden = true;
                }
            }
            if (linkedInUsername) linkedInUsername.disabled = true;
            if (linkedInPassword) linkedInPassword.disabled = true;
            if (linkedInCode) linkedInCode.disabled = true;
            if (directLinkedInCard) {
                directLinkedInCard.classList.remove('disabled');
                directLinkedInCard.classList.add('connected');
            }
            if (chromePluginCard) {
                if (CHROME_EXTENSION_COMING_SOON) {
                    chromePluginCard.classList.remove('connected');
                    chromePluginCard.classList.add('coming-soon', 'disabled');
                    chromePluginCard.setAttribute('aria-disabled', 'true');
                } else {
                    chromePluginCard.classList.remove('disabled');
                    chromePluginCard.classList.add('connected');
                }
            }
            if (directLinkedInConnectedState) directLinkedInConnectedState.hidden = false;
            if (chromePluginConnectedState) chromePluginConnectedState.hidden = CHROME_EXTENSION_COMING_SOON;
            if (linkedInStatus) {
                linkedInStatus.textContent = '';
                linkedInStatus.hidden = true;
                linkedInStatus.classList.remove('success', 'error');
            }
            if (chromePluginStatus) {
                chromePluginStatus.textContent = '';
                chromePluginStatus.hidden = true;
                chromePluginStatus.classList.remove('success', 'error');
            }

            saveSetupProgress('linkedin');
            updateStepChrome();
        }

        function syncExtensionStatus(status) {
            state.chromePluginInstalled = true;
            const authenticated = !!status?.authenticated;
            const linkedInConnected = isLinkedInConnectedFromExtensionStatus(status);
            state.chromePluginAuthenticated = authenticated;

            if (chromePluginCard) chromePluginCard.classList.remove('disabled');
            if (chromePluginBtn) chromePluginBtn.hidden = true;

            if (linkedInConnected) {
                markLinkedInConnected('LinkedIn er forbundet via Coherta plugin.');
                return;
            }

            if (chromePluginCard) chromePluginCard.classList.remove('connected');
            if (chromePluginConnectedState) chromePluginConnectedState.hidden = true;
            if (pluginLoginBtn) {
                pluginLoginBtn.hidden = authenticated;
                pluginLoginBtn.disabled = false;
            }
            if (extensionLinkedInConnectBtn) {
                extensionLinkedInConnectBtn.hidden = !authenticated;
                extensionLinkedInConnectBtn.disabled = false;
            }

            setChromePluginStatus(
                authenticated
                    ? 'Plugin er logget ind. Du kan nu forbinde LinkedIn.'
                    : 'Plugin er installeret, men ikke logget ind.',
                authenticated ? 'success' : ''
            );
        }

        async function refreshPluginStatus(options) {
            const settings = options || {};

            if (applyChromeExtensionComingSoonState()) {
                return false;
            }

            if (state.chromePluginAuthenticated && !settings.forceBridge) {
                syncExtensionStatus({
                    authenticated: true,
                    linkedIn: {connected: state.linkedinConnected}
                });
                return true;
            }

            if (!isChromeBrowser()) {
                if (state.linkedinConnected) {
                    markLinkedInConnected('LinkedIn er allerede forbundet.');
                    return false;
                }
                state.chromePluginInstalled = false;
                if (pluginLoginBtn) pluginLoginBtn.hidden = true;
                if (extensionLinkedInConnectBtn) extensionLinkedInConnectBtn.hidden = true;
                if (chromePluginBtn) {
                    chromePluginBtn.hidden = false;
                    chromePluginBtn.disabled = true;
                }
                if (chromePluginConnectedState) chromePluginConnectedState.hidden = true;
                if (chromePluginCard) chromePluginCard.classList.add('disabled');
                setChromePluginStatus('Denne mulighed kræver Chrome og er deaktiveret i den nuværende browser.', 'error');
                return false;
            }

            try {
                await requestCohertaExtension('PING', {}, 1200);
                const status = await requestCohertaExtension('GET_STATUS');
                syncExtensionStatus(status);
                return true;
            } catch (error) {
                if (state.linkedinConnected) {
                    markLinkedInConnected('LinkedIn er allerede forbundet.');
                    return false;
                }
                state.chromePluginInstalled = false;
                if (pluginLoginBtn) pluginLoginBtn.hidden = true;
                if (extensionLinkedInConnectBtn) extensionLinkedInConnectBtn.hidden = true;
                if (chromePluginBtn) {
                    chromePluginBtn.hidden = false;
                    chromePluginBtn.disabled = false;
                    chromePluginBtn.textContent = translateSetupText('Hent plug-in');
                }
                if (chromePluginConnectedState) chromePluginConnectedState.hidden = true;
                if (chromePluginCard) {
                    chromePluginCard.classList.remove('connected');
                    chromePluginCard.classList.remove('disabled');
                }
                if (!settings.silent) {
                    setChromePluginStatus('Coherta plugin er ikke installeret eller ikke aktivt.', 'error');
                }
                return false;
            }
        }

        function setChromePluginStatus(message, type) {
            if (!chromePluginStatus) return;
            chromePluginStatus.textContent = translateSetupText(message || '');
            chromePluginStatus.hidden = !message;
            chromePluginStatus.classList.toggle('success', type === 'success');
            chromePluginStatus.classList.toggle('error', type === 'error');
        }

        function applyChromeExtensionComingSoonState() {
            if (!CHROME_EXTENSION_COMING_SOON || state.linkedinConnected) return false;

            state.chromePluginInstalled = false;
            state.chromePluginAuthenticated = false;

            if (chromePluginCard) {
                chromePluginCard.classList.remove('connected');
                chromePluginCard.classList.add('coming-soon', 'disabled');
                chromePluginCard.setAttribute('aria-disabled', 'true');
            }
            if (chromePluginConnectedState) chromePluginConnectedState.hidden = true;
            if (pluginLoginBtn) {
                pluginLoginBtn.hidden = true;
                pluginLoginBtn.disabled = true;
            }
            if (extensionLinkedInConnectBtn) {
                extensionLinkedInConnectBtn.hidden = true;
                extensionLinkedInConnectBtn.disabled = true;
            }
            if (chromePluginBtn) {
                chromePluginBtn.hidden = false;
                chromePluginBtn.disabled = true;
                chromePluginBtn.setAttribute('aria-disabled', 'true');
                chromePluginBtn.textContent = translateSetupText('Coming soon');
            }
            setChromePluginStatus('', '');
            return true;
        }

        function hasInstalledPluginDataset(element) {
            if (!element || !element.dataset) return false;
            return element.dataset.cohertaPlugin === 'installed'
                || element.dataset.cohertaPluginInstalled === 'true'
                || element.dataset.cohertaChromePluginInstalled === 'true';
        }

        function hasCohertaPluginSignal() {
            const globals = [
                'CohertaPlugin',
                'CohertaChromePlugin',
                'cohertaPlugin',
                'cohertaChromePlugin',
                '__COHERTA_PLUGIN_INSTALLED__',
                '__COHERTA_CHROME_PLUGIN_INSTALLED__'
            ];

            if (globals.some((key) => !!window[key])) return true;
            if (window.Coherta && (window.Coherta.pluginInstalled || window.Coherta.chromePluginInstalled)) return true;
            if (hasInstalledPluginDataset(document.documentElement) || hasInstalledPluginDataset(document.body)) return true;
            if (document.querySelector('[data-coherta-plugin-installed="true"], [data-coherta-chrome-plugin-installed="true"], [data-coherta-plugin="installed"]')) return true;

            const installedMeta = document.querySelector('meta[name="coherta-plugin-installed"], meta[name="coherta-chrome-plugin-installed"]');
            return !!installedMeta && /^(true|installed|1)$/i.test(installedMeta.getAttribute('content') || '');
        }

        function isCohertaPluginMessage(data) {
            if (!data) return false;
            if (typeof data === 'string') return CHROME_PLUGIN_READY_TYPES.has(data);
            if (typeof data !== 'object') return false;

            const messageKeys = [data.type, data.event, data.action, data.name]
                .filter(Boolean)
                .map((value) => String(value));
            if (messageKeys.some((value) => CHROME_PLUGIN_READY_TYPES.has(value))) return true;
            if (data.cohertaPluginInstalled === true || data.cohertaChromePluginInstalled === true || data.cohertaPlugin === 'installed') return true;

            const source = String(data.source || data.origin || '').toLowerCase();
            const status = String(data.status || '').toLowerCase();
            return (source === 'coherta-plugin' || source === 'coherta-extension' || source === 'coherta-chrome-plugin')
                && (data.installed === true || data.ready === true || status === 'ready' || status === 'installed');
        }

        function markChromePluginInstalled() {
            if (applyChromeExtensionComingSoonState()) return;
            if (!chromePluginBtn || !chromePluginStatus) return;
            state.chromePluginInstalled = true;
            chromePluginBtn.disabled = true;
            chromePluginBtn.textContent = translateSetupText('Plug-in installeret');
            if (chromePluginCard) chromePluginCard.classList.remove('disabled');
            setChromePluginStatus('Chrome plug-in er allerede installeret.', 'success');
        }

        function requestChromePluginPresence() {
            return new Promise((resolve) => {
                if (hasCohertaPluginSignal()) {
                    markChromePluginInstalled();
                    resolve(true);
                    return;
                }

                let resolved = false;
                let timeoutId = 0;
                const finish = (installed) => {
                    if (resolved) return;
                    resolved = true;
                    window.clearTimeout(timeoutId);
                    window.removeEventListener('message', onMessage);
                    document.removeEventListener('coherta:plugin:ready', onPluginEvent);
                    document.removeEventListener('coherta:plugin:pong', onPluginEvent);
                    document.removeEventListener('coherta-plugin-ready', onPluginEvent);
                    document.removeEventListener('coherta-plugin-pong', onPluginEvent);
                    resolve(installed);
                };
                const onPluginEvent = () => {
                    markChromePluginInstalled();
                    finish(true);
                };
                const onMessage = (event) => {
                    if (event.source && event.source !== window) return;
                    if (!isCohertaPluginMessage(event.data)) return;
                    markChromePluginInstalled();
                    finish(true);
                };

                window.addEventListener('message', onMessage);
                document.addEventListener('coherta:plugin:ready', onPluginEvent);
                document.addEventListener('coherta:plugin:pong', onPluginEvent);
                document.addEventListener('coherta-plugin-ready', onPluginEvent);
                document.addEventListener('coherta-plugin-pong', onPluginEvent);

                try {
                    const targetOrigin = window.location.origin && window.location.origin !== 'null' ? window.location.origin : '*';
                    window.postMessage({type: 'COHERTA_PLUGIN_PING', source: 'coherta-setup-funnel'}, targetOrigin);
                } catch (error) {}

                try {
                    document.dispatchEvent(new CustomEvent('coherta:plugin:ping', {detail: {source: 'coherta-setup-funnel'}}));
                    document.dispatchEvent(new CustomEvent('coherta-plugin-ping', {detail: {source: 'coherta-setup-funnel'}}));
                } catch (error) {}

                timeoutId = window.setTimeout(() => {
                    const installed = hasCohertaPluginSignal();
                    if (installed) markChromePluginInstalled();
                    finish(installed);
                }, 800);
            });
        }

        async function setupChromePluginAvailability(options) {
            if (applyChromeExtensionComingSoonState()) return false;
            if (chromePluginCheckInProgress) return false;
            chromePluginCheckInProgress = true;

            try {
                return await refreshPluginStatus(options);
            } finally {
                chromePluginCheckInProgress = false;
            }
        }

        function handleChromePluginMessage(event) {
            if (applyChromeExtensionComingSoonState()) return;
            if (event.source && event.source !== window) return;
            if (isCohertaPluginMessage(event.data)) markChromePluginInstalled();
        }

        function recheckChromePluginAfterFocus() {
            if (applyChromeExtensionComingSoonState()) return;
            if (document.hidden || !isChromeBrowser() || state.chromePluginInstalled) return;
            setupChromePluginAvailability({silent: true});
        }

        function hydrateLinkedInConnectUi() {
            if (state.linkedinConnected) {
                markLinkedInConnected('LinkedIn er allerede forbundet.');
            }
        }

        async function loginPluginFromSetup() {
            if (applyChromeExtensionComingSoonState()) return;
            if (!pluginLoginBtn || pluginLoginBtn.hidden) return;

            const sessionId = getSessionId();
            if (!sessionId) {
                setChromePluginStatus('Coherta session mangler. Genindlæs siden og prøv igen.', 'error');
                return;
            }

            setPluginLoginBusy(true);
            setChromePluginStatus('Logger plugin ind...', '');

            try {
                await requestCohertaExtension('LOGIN_WITH_SESSION', {sessionId: sessionId});
                await refreshPluginStatus({silent: true});
            } catch (error) {
                setChromePluginStatus(error.message || 'Plugin kunne ikke logges ind.', 'error');
            } finally {
                setPluginLoginBusy(false);
            }
        }

        async function connectLinkedInFromSetup() {
            if (state.linkedinConnected || state.linkedinStatus === 'submitting' || linkedInPolling) return;

            const username = linkedInUsername ? linkedInUsername.value.trim() : '';
            const password = linkedInPassword ? linkedInPassword.value : '';

            if (!getSessionId()) {
                setLinkedInStatus('Coherta session mangler. Genindlæs siden og prøv igen.', 'error');
                return;
            }
            if (!username || !password) {
                setLinkedInStatus('Udfyld LinkedIn email og password først.', 'error');
                return;
            }

            state.linkedinStatus = 'submitting';
            setLinkedInBusy(true, 'Forbinder...');
            setTwoFactorVisible(false);
            setLinkedInStatus('Vi forbinder LinkedIn. Godkend eventuelle prompts i LinkedIn appen.', '');

            try {
                const response = await fetch(LINKEDIN_CONNECT_ENDPOINT, {
                    method: 'POST',
                    headers: headersJson(true),
                    body: JSON.stringify({username: username, password: password})
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || result.error || 'LinkedIn kunne ikke forbindes.');

                state.linkedinStatus = 'waiting';
                setLinkedInStatus('Vi tjekker LinkedIn status...', '');
                await pollLinkedInStatus(90);
            } catch (error) {
                state.linkedinStatus = 'idle';
                setLinkedInStatus(error.message || 'LinkedIn kunne ikke forbindes. Prøv igen.', 'error');
            } finally {
                if (!state.linkedinConnected && !['sms', 'authenticator', 'app-approval'].includes(state.linkedinStatus)) {
                    setLinkedInBusy(false);
                }
            }
        }

        async function submitLinkedInCodeFromSetup() {
            if (state.linkedinConnected || linkedInPolling) return;

            const smsCode = linkedInCode ? linkedInCode.value.trim() : '';
            if (!smsCode) {
                setLinkedInStatus('Indtast koden før du fortsætter.', 'error');
                return;
            }
            if (!getSessionId()) {
                setLinkedInStatus('Coherta session mangler. Genindlæs siden og prøv igen.', 'error');
                return;
            }

            state.linkedinStatus = 'waiting';
            if (linkedInCodeBtn) {
                linkedInCodeBtn.disabled = true;
                linkedInCodeBtn.textContent = translateSetupText('Validerer...');
            }
            setLinkedInStatus('Vi validerer koden.', '');

            try {
                const response = await fetch(LINKEDIN_SMS_ENDPOINT, {
                    method: 'PATCH',
                    headers: headersJson(true),
                    body: JSON.stringify({sms_code: smsCode})
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || result.error || 'Koden kunne ikke valideres.');
                await pollLinkedInStatus(90);
            } catch (error) {
                state.linkedinStatus = 'sms';
                setLinkedInStatus(error.message || 'Koden kunne ikke valideres. Prøv igen.', 'error');
            } finally {
                if (!state.linkedinConnected && linkedInCodeBtn) {
                    linkedInCodeBtn.disabled = false;
                    linkedInCodeBtn.textContent = translateSetupText('Send kode');
                }
            }
        }

        async function pollLinkedInStatus(maxAttempts) {
            if (linkedInPolling) return false;
            linkedInPolling = true;
            const attempts = Number(maxAttempts) > 0 ? Number(maxAttempts) : 30;
            setLinkedInBusy(true);

            try {
                for (let i = 0; i < attempts; i++) {
                    const response = await fetch(LINKEDIN_CONNECT_ENDPOINT, {method: 'GET', headers: headersJson(true)});
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(data.message || data.error || 'LinkedIn status kunne ikke tjekkes. Prøv igen.');
                    const status = Number(data.status);
                    linkedInApproval.update(status === 7 && screens[activeIndex]?.dataset.stepId === 'linkedin');

                    if (status === 1) {
                        markLinkedInConnected('LinkedIn er forbundet.');
                        return true;
                    }

                    if (status === 2 || status === 3) {
                        state.linkedinStatus = 'idle';
                        setTwoFactorVisible(false);
                        const messages = linkedInApproval.messages;
                        setLinkedInStatus(status === 2 ? messages.invalidCredentials : messages.approvalTimeout, 'error');
                        return false;
                    }

                    if (status === 8) {
                        state.linkedinStatus = 'authenticator';
                        setTwoFactorVisible(true);
                        setLinkedInStatus(
                            data.code === null
                                ? 'Indtast koden fra Microsoft Authenticator.'
                                : 'Vi validerer din Authenticator-godkendelse.',
                            ''
                        );
                        return false;
                    }

                    if (status === 9) {
                        state.linkedinStatus = 'sms';
                        setTwoFactorVisible(true);
                        setLinkedInStatus('Indtast SMS- eller emailkoden, du lige har modtaget.', '');
                        return false;
                    }

                    if (status === 7 || status === 10) {
                        state.linkedinStatus = 'app-approval';
                        setTwoFactorVisible(false);
                        setLinkedInStatus('Godkend login-anmodningen i din LinkedIn app.', '');
                    } else {
                        state.linkedinStatus = 'waiting';
                        setLinkedInStatus('Venter på LinkedIn godkendelse...', '');
                    }

                    await wait(2000);
                }

                state.linkedinStatus = 'idle';
                setLinkedInStatus(linkedInApproval.messages.approvalTimeout, 'error');
                return false;
            } finally {
                linkedInApproval.update(false);
                linkedInPolling = false;
                if (!state.linkedinConnected) setLinkedInBusy(false);
            }
        }

        async function connectLinkedInWithExtensionFromSetup() {
            if (applyChromeExtensionComingSoonState()) return;
            if (state.linkedinConnected || state.linkedinStatus === 'extension-submitting') return;

            state.linkedinStatus = 'extension-submitting';
            setExtensionLinkedInBusy(true, 'Forbinder...');
            setChromePluginStatus('Forbinder LinkedIn via Coherta plugin...', '');

            try {
                await requestCohertaExtension('CONNECT_LINKEDIN', {
                    active: true,
                    closeTab: true,
                    timeoutMs: 30000
                }, 45000);
                await refreshPluginStatus({silent: true});
                markLinkedInConnected('LinkedIn er forbundet via Coherta plugin.');
            } catch (error) {
                state.linkedinStatus = 'idle';
                const message = error.message || 'LinkedIn kunne ikke forbindes. Prøv igen.';
                setChromePluginStatus(message, 'error');
            } finally {
                if (!state.linkedinConnected) setExtensionLinkedInBusy(false);
            }
        }

        function openChromePlugin() {
            if (applyChromeExtensionComingSoonState()) return;
            if (!chromePluginBtn || chromePluginBtn.disabled) return;
            window.open('https://chromewebstore.google.com/detail/coherta/npcgakepgigijgkhidhopkmbignnkmgc', '_blank', 'noopener');
            setChromePluginStatus('Chrome plug-in er åbnet i en ny fane. Vi tjekker igen, når du vender tilbage.', 'success');
            window.setTimeout(() => setupChromePluginAvailability({silent: true}), 1200);
        }

        async function submitAiToolChoice() {
            if (!state.aiTool) {
                setAiToolStatus('Vælg ChatGPT, Claude eller Intet før du fortsætter.', 'error');
                return false;
            }

            state.companySaving = true;
            updateStepChrome();
            setAiToolStatus('Gemmer AI-værktøj...', '');

            try {
                await saveCompanyAiTool(state.aiTool);
                saveSetupProgress('ai-tool', {aiToolSaved: state.aiTool});
                setAiToolStatus('', '');
                return true;
            } catch (error) {
                setAiToolStatus(error.message || 'AI-værktøj kunne ikke gemmes. Prøv igen.', 'error');
                return false;
            } finally {
                state.companySaving = false;
                updateStepChrome();
            }
        }

        document.querySelectorAll('[data-next]').forEach((button) => {
            button.addEventListener('click', () => {
                if (screens[activeIndex]?.dataset.stepId === 'payment') {
                    beginPaymentActivation(activeIndex + 1);
                    return;
                }
                saveSetupProgress(screens[activeIndex]?.dataset.stepId || '');
                goToStep(activeIndex + 1);
            });
        });

        document.querySelectorAll('[data-choice-group]').forEach((group) => {
            group.addEventListener('click', (event) => {
                const choice = event.target.closest('.setup-choice');
                if (!choice) return;
                if (choice.disabled || choice.classList.contains('is-disabled')) return;
                if (group.dataset.choiceGroup === 'accountType' && accountTypeLockedToCompany && choice.dataset.value !== 'business') return;
                group.querySelectorAll('.setup-choice').forEach((item) => item.classList.remove('selected'));
                choice.classList.add('selected');
                const groupName = group.dataset.choiceGroup;
                state[groupName] = choice.dataset.value;
                if (groupName === 'campaignMode') syncCampaignWizard();
                if (groupName === 'accountType') {
                    state.userSaved = false;
                    applyAccountTypePricingRules();
                    syncIdentityPanel();
                    updateSummary();
                }
                if (groupName === 'playbook') applyPlaybook(state.playbook);
                if (groupName === 'source') renderSourcePanel();
                if (groupName === 'aiTool') {
                    setAiToolStatus('', '');
                }
                saveSetupProgress(screens[activeIndex]?.dataset.stepId || groupName);
                updateStepChrome();
            });
        });

        backBtn.addEventListener('click', () => goToStep(activeIndex - 1));
        nextBtn.addEventListener('click', async () => {
            const activeStepId = screens[activeIndex].dataset.stepId;

            if (activeStepId === 'account-type') {
                const saved = await saveAccountIdentityStep();
                if (!saved) return;
            }
            if (activeStepId === 'campaign' && !state.campaignMode) return;
            if (activeStepId === 'workflow') {
                syncCampaignWizard();
                if (state.campaignMode !== 'advanced') {
                    const audienceTitle = document.getElementById('sourceTitle')?.value.trim() || '';
                    const audienceCountry = document.getElementById('sourceCountry')?.value || '';
                    if (!audienceTitle || !audienceCountry) {
                        setWorkflowStatus(!audienceTitle ? 'Enter the job title you want to reach.' : 'Select a country.', 'error');
                        return;
                    }
                }
                const campaignSaved = await saveCampaignNameStep({forceCreate: true});
                if (!campaignSaved) return;
                if (state.campaignMode !== 'advanced') {
                    const sourceSaved = await saveCampaignSourceStep();
                    if (!sourceSaved) return;
                }
                const workflowSaved = await saveCampaignWorkflowStep();
                if (!workflowSaved) return;
            }
            if (activeStepId === 'payment') {
                beginPaymentActivation(activeIndex + 1);
                return;
            }
            if (activeStepId === 'linkedin') {
                const connected = await connectCurrentEmployeeToCampaign();
                if (!connected) return;
            }
            if (activeStepId === 'ai-tool') {
                const sent = await submitAiToolChoice();
                if (!sent) return;
            }
            saveSetupProgress(activeStepId);
            if (activeIndex >= screens.length - 1) {
                if (talentAttractEnabled) {
                    try { SETUP_PROGRESS_STORAGE.removeItem(SETUP_PROGRESS_KEY); } catch (error) {}
                    try { SETUP_PROGRESS_STORAGE.removeItem(SETUP_DRAFT_KEY); } catch (error) {}
                }
                window.location.href = '/app/login';
                return;
            }
            goToStep(activeIndex + 1);
        });

        if (setupUserName) {
            setupUserName.addEventListener('input', () => {
                state.userSaved = false;
                setIdentityStatus('', '');
                saveSetupProgress('account-type');
                updateStepChrome();
            });
        }
        if (setupUserEmail) {
            setupUserEmail.addEventListener('input', () => {
                state.userSaved = false;
                setIdentityStatus('', '');
                saveSetupProgress('account-type');
                updateStepChrome();
            });
        }

        campaignName.addEventListener('input', () => {
            updateSummary();
            saveSetupProgress('campaign');
        });
        campaignGoal.addEventListener('change', () => {
            updateSummary();
            saveSetupProgress('campaign');
        });

        const NODE_TYPES = {
            opportunity: {label: 'Opportunity', description: 'Starting decision in the workflow.', kind: 'decision'},
            connect: {label: 'Connect', description: 'Initiate a connection action.', kind: 'decision'},
            if_accepted: {label: 'If Accepted', description: 'Branch for accepted connection requests.', kind: 'decision'},
            not_accepted: {label: 'Not Accepted', description: 'Branch for connection requests that were not accepted.', kind: 'decision'},
            visit: {label: 'Visit Person', description: 'Open or review the person profile.', kind: 'action'},
            write: {label: 'Write to person', description: 'Send a templated message to the person.', kind: 'action'},
            like: {label: 'Like Post', description: "Like one of the person's posts.", kind: 'action'},
            wait: {label: 'Wait', description: 'Pause the flow before the next action.', kind: 'action'},
            delete_connection_request: {label: 'Delete Connection Request', description: 'Remove the pending connection request.', kind: 'action'}
        };

        const PALETTE = [
            'connect',
            'visit',
            'write',
            'like',
            'wait'
        ];

        const DIALOGUE_SECOND_WRITE_TEMPLATE = translateSetupText('Jeg rækker ud til dig fordi ...');

        const palette = document.getElementById('workflowPalette');
        const nodesLayer = document.getElementById('workflowNodes');
        const edgesSvg = document.getElementById('workflowEdges');
        const canvas = document.getElementById('workflowCanvas');
        const canvasWrap = document.getElementById('workflowCanvasWrap');
        const inspector = document.getElementById('workflowInspector');
        const editNodeBtn = document.getElementById('editNodeBtn');
        const deleteNodeBtn = document.getElementById('deleteNodeBtn');
        const resetWorkflowBtn = document.getElementById('resetWorkflowBtn');
        const saveWorkflowBtn = document.getElementById('saveWorkflowBtn');
        const modalBackdrop = document.getElementById('workflowModalBackdrop');
        const modalTitle = document.getElementById('workflowModalTitle');
        const modalCopy = document.getElementById('workflowModalCopy');
        const modalBody = document.getElementById('workflowModalBody');
        const closeWorkflowModal = document.getElementById('closeWorkflowModal');
        const cancelWorkflowModal = document.getElementById('cancelWorkflowModal');
        const saveWorkflowModal = document.getElementById('saveWorkflowModal');
        let selectedNodeId = null;
        let editingNodeId = null;
        let workflow = createNetworkWorkflow();
        const WORKFLOW_NODE_WIDTH = 224;
        const WORKFLOW_NODE_HEIGHT = 118;

        function uid() {
            return 'n_' + Math.random().toString(36).slice(2, 11);
        }

        function createNotAcceptedCleanup(notAcceptedId, x, y) {
            const waitId = uid();
            const cleanupId = uid();
            return {
                nodes: [
                    {id: waitId, type: 'wait', title: 'Wait 35 days', x: x, y: y, config: {durationValue: 35, durationUnit: 'days', locked: true}},
                    {id: cleanupId, type: 'delete_connection_request', title: 'Delete Connection Request', x: x, y: y + 180, config: {locked: true}}
                ],
                edges: [
                    {id: uid(), from: notAcceptedId, to: waitId, label: ''},
                    {id: uid(), from: waitId, to: cleanupId, label: ''}
                ]
            };
        }

        function createNetworkWorkflow() {
            const opportunityId = uid();
            const visitId = uid();
            const waitBeforeConnectId = uid();
            const connectId = uid();
            const acceptedId = uid();
            const notAcceptedId = uid();
            const notAcceptedCleanup = createNotAcceptedCleanup(notAcceptedId, 590, 990);
            return {
                version: 1,
                nodes: [
                    {id: opportunityId, type: 'opportunity', title: 'Opportunity', x: 380, y: 70, config: {}},
                    {id: visitId, type: 'visit', title: 'Visit Person', x: 380, y: 250, config: {}},
                    {id: waitBeforeConnectId, type: 'wait', title: 'Wait 1 day', x: 380, y: 430, config: {durationValue: 1, durationUnit: 'days'}},
                    {id: connectId, type: 'connect', title: 'Connect', x: 380, y: 610, config: {connectMessage: translateSetupText('Hi {lead.first_name}, I came across {lead.company_name} and thought it would be relevant to connect.')}},
                    {id: acceptedId, type: 'if_accepted', title: 'If Accepted', x: 170, y: 810, config: {locked: true}},
                    {id: notAcceptedId, type: 'not_accepted', title: 'Not Accepted', x: 590, y: 810, config: {locked: true}},
                    ...notAcceptedCleanup.nodes
                ],
                edges: [
                    {id: uid(), from: opportunityId, to: visitId, label: ''},
                    {id: uid(), from: visitId, to: waitBeforeConnectId, label: ''},
                    {id: uid(), from: waitBeforeConnectId, to: connectId, label: ''},
                    {id: uid(), from: connectId, to: acceptedId, label: 'If Accepted'},
                    {id: uid(), from: connectId, to: notAcceptedId, label: 'Not Accepted'},
                    ...notAcceptedCleanup.edges
                ]
            };
        }

        function createDialogueWorkflow() {
            const opportunityId = uid();
            const visitId = uid();
            const waitBeforeConnectId = uid();
            const connectId = uid();
            const acceptedId = uid();
            const notAcceptedId = uid();
            const firstWriteId = uid();
            const waitBeforeFollowUpId = uid();
            const secondWriteId = uid();
            const notAcceptedCleanup = createNotAcceptedCleanup(notAcceptedId, 590, 990);
            return {
                version: 1,
                nodes: [
                    {id: opportunityId, type: 'opportunity', title: 'Opportunity', x: 380, y: 70, config: {}},
                    {id: visitId, type: 'visit', title: 'Visit Person', x: 380, y: 250, config: {}},
                    {id: waitBeforeConnectId, type: 'wait', title: 'Wait 1 day', x: 380, y: 430, config: {durationValue: 1, durationUnit: 'days'}},
                    {id: connectId, type: 'connect', title: 'Connect', x: 380, y: 610, config: {connectMessage: translateSetupText('Hi {lead.first_name}, I came across {lead.company_name} and thought it would be relevant to connect.')}},
                    {id: acceptedId, type: 'if_accepted', title: 'If Accepted', x: 170, y: 810, config: {locked: true}},
                    {id: notAcceptedId, type: 'not_accepted', title: 'Not Accepted', x: 590, y: 810, config: {locked: true}},
                    {id: firstWriteId, type: 'write', title: 'Write: Tak for connection', x: 170, y: 990, config: {template: translateSetupText('Tak for connection, {lead.first_name}.'), systemPrompt: translateSetupText('Hold beskeden kort, personlig og naturlig.'), manualApproval: false}},
                    {id: waitBeforeFollowUpId, type: 'wait', title: 'Wait 3 days', x: 170, y: 1170, config: {durationValue: 3, durationUnit: 'days'}},
                    {id: secondWriteId, type: 'write', title: 'Write: Opfølgning', x: 170, y: 1350, config: {template: DIALOGUE_SECOND_WRITE_TEMPLATE, systemPrompt: translateSetupText('Tilpas beskeden til leadets rolle, virksomhed og campaignens mål.'), manualApproval: false, requiresEdit: true, originalTemplate: DIALOGUE_SECOND_WRITE_TEMPLATE, edited: false}},
                    ...notAcceptedCleanup.nodes
                ],
                edges: [
                    {id: uid(), from: opportunityId, to: visitId, label: ''},
                    {id: uid(), from: visitId, to: waitBeforeConnectId, label: ''},
                    {id: uid(), from: waitBeforeConnectId, to: connectId, label: ''},
                    {id: uid(), from: connectId, to: acceptedId, label: 'If Accepted'},
                    {id: uid(), from: connectId, to: notAcceptedId, label: 'Not Accepted'},
                    {id: uid(), from: acceptedId, to: firstWriteId, label: ''},
                    {id: uid(), from: firstWriteId, to: waitBeforeFollowUpId, label: ''},
                    {id: uid(), from: waitBeforeFollowUpId, to: secondWriteId, label: ''},
                    ...notAcceptedCleanup.edges
                ]
            };
        }

        function createBlankWorkflow() {
            const opportunityId = uid();
            return {
                version: 1,
                nodes: [{id: opportunityId, type: 'opportunity', title: 'Opportunity', x: 380, y: 80, config: {}}],
                edges: []
            };
        }

        function applyPlaybook(playbook) {
            if (playbook === 'blank') workflow = createBlankWorkflow();
            if (playbook === 'network') workflow = createNetworkWorkflow();
            if (playbook === 'dialogue') workflow = createDialogueWorkflow();
            state.workflowSaved = false;
            selectedNodeId = getRequiredWorkflowEditNode()?.id || workflow.nodes[0]?.id || null;
            renderWorkflow();
            updateStepChrome();
        }

        function renderPalette() {
            palette.innerHTML = PALETTE.map((type) => {
                const nodeType = NODE_TYPES[type];
                return '<button class="setup-node-add" type="button" data-node-type="' + type + '">' + escapeHtml(nodeType.label) + '<span>' + escapeHtml(nodeType.description) + '</span></button>';
            }).join('');
            localizeSetupText(palette);
            palette.querySelectorAll('[data-node-type]').forEach((button) => {
                button.addEventListener('click', () => addNode(button.dataset.nodeType));
            });
        }

        function getNode(id) {
            return workflow.nodes.find((node) => node.id === id) || null;
        }

        function getOutgoingWorkflowEdges(id) {
            return workflow.edges.filter((edge) => edge.from === id);
        }

        function getConnectBranchNodeIds(connectId) {
            return getOutgoingWorkflowEdges(connectId)
                .map((edge) => getNode(edge.to))
                .filter((node) => node && ['if_accepted', 'not_accepted'].includes(node.type))
                .map((node) => node.id);
        }

        function maxNodeY() {
            return workflow.nodes.reduce((max, node) => Math.max(max, node.y), 80);
        }

        function addNode(type) {
            const parent = getNode(selectedNodeId) || workflow.nodes[workflow.nodes.length - 1];
            if (!parent) return;
            const y = maxNodeY() + 190;
            const x = parent.x;
            const node = {
                id: uid(),
                type: type,
                title: NODE_TYPES[type].label,
                x: x,
                y: y,
                config: defaultConfig(type)
            };
            workflow.nodes.push(node);
            workflow.edges.push({id: uid(), from: parent.id, to: node.id, label: ''});

            if (type === 'connect') {
                const accepted = {id: uid(), type: 'if_accepted', title: 'If Accepted', x: x - 210, y: y + 190, config: {locked: true}};
                const notAccepted = {id: uid(), type: 'not_accepted', title: 'Not Accepted', x: x + 210, y: y + 190, config: {locked: true}};
                const notAcceptedCleanup = createNotAcceptedCleanup(notAccepted.id, x + 210, y + 380);
                workflow.nodes.push(accepted, notAccepted, ...notAcceptedCleanup.nodes);
                workflow.edges.push(
                    {id: uid(), from: node.id, to: accepted.id, label: 'If Accepted'},
                    {id: uid(), from: node.id, to: notAccepted.id, label: 'Not Accepted'},
                    ...notAcceptedCleanup.edges
                );
            }

            selectedNodeId = node.id;
            renderWorkflow();
            saveSetupProgress('workflow');
        }

        function defaultConfig(type) {
            if (type === 'wait') return {durationValue: 1, durationUnit: 'days'};
            if (type === 'connect') return {connectMessage: ''};
            if (type === 'write') return {template: '', systemPrompt: '', manualApproval: false};
            return {};
        }

        function localizedDurationUnit(unit) {
            return translateSetupText(String(unit || 'days'));
        }

        function nodeTitle(node) {
            if (!node) return '';
            if (node.type === 'wait') {
                return translateSetupText('Wait {value} {unit}', {
                    value: node.config.durationValue || 1,
                    unit: localizedDurationUnit(node.config.durationUnit || 'days')
                });
            }
            return translateSetupText(node.title || NODE_TYPES[node.type]?.label || '');
        }

        function nodeBody(node) {
            if (!node) return '';
            if (node.type === 'write') {
                const status = node.config.requiresEdit && !node.config.edited ? 'Skal rettes før du kan fortsætte' : (node.config.template ? 'Template saved' : 'No template yet');
                const sendMode = node.config.manualApproval ? 'Manual approval enabled' : 'Automatic send';
                return translateSetupText(status) + '\n' + translateSetupText(sendMode);
            }
            if (node.type === 'connect') return translateSetupText(node.config.connectMessage ? 'Connect message saved' : 'No connect message');
            if (node.type === 'wait') {
                return translateSetupText('Wait {value} {unit}', {
                    value: node.config.durationValue || 1,
                    unit: localizedDurationUnit(node.config.durationUnit || 'days')
                });
            }
            if (node.type === 'delete_connection_request') return translateSetupText('Delete pending connection request');
            return translateSetupText(NODE_TYPES[node.type]?.description || '');
        }

        function getRequiredWorkflowEditNode() {
            return workflow.nodes.find((node) => node.type === 'write' && node.config.requiresEdit && !node.config.edited) || null;
        }

        function workflowCanContinue() {
            return !getRequiredWorkflowEditNode();
        }

        function renderWorkflow() {
            if (!selectedNodeId && workflow.nodes[0]) selectedNodeId = workflow.nodes[0].id;
            const contentWidth = workflow.nodes.reduce((max, node) => Math.max(max, node.x + WORKFLOW_NODE_WIDTH + 76), 980);
            const contentHeight = workflow.nodes.reduce((max, node) => Math.max(max, node.y + WORKFLOW_NODE_HEIGHT + 102), 980);
            const canvasWidth = Math.ceil(Math.max(contentWidth, canvasWrap ? canvasWrap.clientWidth : 0));
            const canvasHeight = Math.ceil(Math.max(contentHeight, canvasWrap ? canvasWrap.clientHeight : 0));
            canvas.style.width = canvasWidth + 'px';
            canvas.style.height = canvasHeight + 'px';
            canvas.style.minWidth = canvasWidth + 'px';
            canvas.style.minHeight = canvasHeight + 'px';
            edgesSvg.setAttribute('width', canvasWidth);
            edgesSvg.setAttribute('height', canvasHeight);
            edgesSvg.setAttribute('viewBox', '0 0 ' + canvasWidth + ' ' + canvasHeight);

            nodesLayer.innerHTML = workflow.nodes.map((node) => {
                const kind = NODE_TYPES[node.type]?.kind || 'action';
                const needsEdit = node.type === 'write' && node.config.requiresEdit && !node.config.edited;
                return '<button class="setup-workflow-node ' + (kind === 'decision' ? 'decision ' : '') + (needsEdit ? 'needs-edit ' : '') + (node.id === selectedNodeId ? 'selected' : '') + '" type="button" data-node-id="' + node.id + '" style="left:' + node.x + 'px;top:' + node.y + 'px"><div class="setup-node-type">' + escapeHtml(kind) + '</div><div class="setup-node-title">' + escapeHtml(node.title) + '</div><div class="setup-node-body">' + escapeHtml(nodeBody(node)) + '</div></button>';
            }).join('');
            localizeSetupText(nodesLayer);
            nodesLayer.querySelectorAll('[data-node-id]').forEach((button) => {
                button.addEventListener('click', () => {
                    selectedNodeId = button.dataset.nodeId;
                    renderWorkflow();
                });
            });
            renderEdges();
            renderInspector();
            updateSummary();
        }

        function workflowNodeBox(node) {
            const element = nodesLayer.querySelector('[data-node-id="' + node.id + '"]');
            const width = element ? element.offsetWidth : WORKFLOW_NODE_WIDTH;
            const height = element ? element.offsetHeight : WORKFLOW_NODE_HEIGHT;
            return {
                x: node.x,
                y: node.y,
                width: width,
                height: height,
                centerX: node.x + width / 2,
                topY: node.y,
                bottomY: node.y + height
            };
        }

        function renderEdges() {
            const defs = edgesSvg.querySelector('defs').outerHTML;
            edgesSvg.innerHTML = defs;
            workflow.edges.forEach((edge) => {
                const from = getNode(edge.from);
                const to = getNode(edge.to);
                if (!from || !to) return;
                const fromBox = workflowNodeBox(from);
                const toBox = workflowNodeBox(to);
                const startX = Math.round(fromBox.centerX);
                const startY = Math.round(fromBox.bottomY);
                const endX = Math.round(toBox.centerX);
                const endY = Math.round(toBox.topY);
                const midY = Math.round((startY + endY) / 2);
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', 'M ' + startX + ' ' + startY + ' C ' + startX + ' ' + midY + ', ' + endX + ' ' + midY + ', ' + endX + ' ' + endY);
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', '#9db5e3');
                path.setAttribute('stroke-width', '3');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('marker-end', 'url(#setupArrow)');
                edgesSvg.appendChild(path);
                if (edge.label) {
                    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    text.setAttribute('x', String((startX + endX) / 2));
                    text.setAttribute('y', String(midY - 8));
                    text.setAttribute('text-anchor', 'middle');
                    text.setAttribute('class', 'setup-edge-label');
                    text.textContent = translateSetupText(edge.label);
                    edgesSvg.appendChild(text);
                }
            });
        }

        function renderInspector() {
            const node = getNode(selectedNodeId);
            if (!node) {
                inspector.textContent = translateSetupText('Vælg en node for at redigere den eller tilføje næste element.');
                editNodeBtn.disabled = true;
                deleteNodeBtn.disabled = true;
                return;
            }
            inspector.innerHTML = '<strong style="display:block;color:#111827;margin-bottom:8px;">' + escapeHtml(nodeTitle(node)) + '</strong><span style="display:block;margin-bottom:8px;">' + escapeHtml(translateSetupText('Type: {type}', {type: node.type})) + '</span><span>' + escapeHtml(nodeBody(node)).replaceAll('\n', '<br>') + '</span>';
            localizeSetupText(inspector);
            editNodeBtn.disabled = !!node.config.locked || ['opportunity', 'if_accepted', 'not_accepted', 'like', 'visit', 'delete_connection_request'].includes(node.type);
            deleteNodeBtn.disabled = node.type === 'opportunity' || !!node.config.locked;
        }

        function deleteSelectedNode() {
            const node = getNode(selectedNodeId);
            if (!node || node.type === 'opportunity' || node.config.locked) return;
            const id = node.id;
            const nodesToDelete = new Set([id]);
            if (node.type === 'connect') {
                getConnectBranchNodeIds(id).forEach((branchId) => nodesToDelete.add(branchId));
            }
            workflow.nodes = workflow.nodes.filter((item) => !nodesToDelete.has(item.id));
            workflow.edges = workflow.edges.filter((edge) => !nodesToDelete.has(edge.from) && !nodesToDelete.has(edge.to));
            selectedNodeId = workflow.nodes[0]?.id || null;
            renderWorkflow();
            saveSetupProgress('workflow');
        }

        function openModal() {
            const node = getNode(selectedNodeId);
            if (!node) return;
            editingNodeId = node.id;
            modalTitle.textContent = translateSetupText('Rediger') + ' ' + nodeTitle(node);
            modalCopy.textContent = translateSetupText(NODE_TYPES[node.type]?.description || 'Opdater node-indstillinger.');
            modalBody.innerHTML = modalFields(node);
            localizeSetupText(modalBody);
            modalBackdrop.classList.add('open');
        }

        function closeModal() {
            modalBackdrop.classList.remove('open');
            editingNodeId = null;
        }

        function modalFields(node) {
            if (node.type === 'connect') {
                return '<div class="setup-field"><label for="modalConnectMessage">Connect message</label><textarea id="modalConnectMessage" placeholder="Leave empty to send without a message.">' + escapeHtml(node.config.connectMessage || '') + '</textarea></div>';
            }
            if (node.type === 'write') {
                const notice = node.config.requiresEdit && !node.config.edited ? '<div class="setup-alert">Ret denne anden Write-besked før du fortsætter fra workflowet.</div>' : '';
                return notice + '<div class="setup-field"><label for="modalWriteTemplate">Template</label><textarea id="modalWriteTemplate">' + escapeHtml(node.config.template || '') + '</textarea></div><div class="setup-field"><label for="modalWriteSystemPrompt">System Prompt</label><textarea id="modalWriteSystemPrompt">' + escapeHtml(node.config.systemPrompt || '') + '</textarea></div><label style="display:flex;gap:8px;align-items:center;"><input id="modalManualApproval" type="checkbox" ' + (node.config.manualApproval ? 'checked' : '') + '> Do not send automatically, place all messages in Message Outbox for manual approval</label>';
            }
            if (node.type === 'wait') {
                return '<div class="setup-form-grid"><div class="setup-field"><label for="modalWaitValue">Duration</label><input id="modalWaitValue" type="number" min="1" step="1" value="' + escapeHtml(node.config.durationValue || 1) + '"></div><div class="setup-field"><label for="modalWaitUnit">Unit</label><select id="modalWaitUnit"><option value="minutes">Minutes</option><option value="hours">Hours</option><option value="days">Days</option><option value="weeks">Weeks</option></select></div></div>';
            }
            return '<div class="setup-alert">Denne node har ingen ekstra indstillinger.</div>';
        }

        function saveModal() {
            const node = getNode(editingNodeId);
            if (!node) return;
            if (node.type === 'connect') {
                node.config.connectMessage = document.getElementById('modalConnectMessage').value.trim();
            }
            if (node.type === 'write') {
                node.config.template = document.getElementById('modalWriteTemplate').value;
                node.config.systemPrompt = document.getElementById('modalWriteSystemPrompt').value;
                node.config.manualApproval = document.getElementById('modalManualApproval').checked;
                if (node.config.requiresEdit) {
                    const originalTemplate = String(node.config.originalTemplate || '').trim();
                    node.config.edited = node.config.template.trim() !== '' && node.config.template.trim() !== originalTemplate;
                }
            }
            if (node.type === 'wait') {
                node.config.durationValue = Math.max(1, Number(document.getElementById('modalWaitValue').value || 1));
                node.config.durationUnit = document.getElementById('modalWaitUnit').value;
                node.title = 'Wait ' + node.config.durationValue + ' ' + node.config.durationUnit;
            }
            closeModal();
            renderWorkflow();
            saveSetupProgress('workflow');
            updateStepChrome();
        }

        async function saveWorkflowState() {
            const requiredNode = getRequiredWorkflowEditNode();
            if (requiredNode) {
                selectedNodeId = requiredNode.id;
                renderWorkflow();
                openModal();
                return;
            }
            const payload = {
                campaignName: campaignName.value.trim(),
                goal: campaignGoal.value,
                accountType: state.accountType,
                pricingPlan: state.pricingPlan,
                billingCycle: state.billingCycle,
                billingPeriodKey: state.billingPeriodKey,
                pricingModel: state.pricingModel,
                userName: setupUserName ? setupUserName.value.trim() : state.userName,
                userEmail: setupUserEmail ? setupUserEmail.value.trim() : state.userEmail,
                source: state.source,
                csvItemCount: state.csvItemCount,
                csvLinkedInUrlCount: state.csvLinkedInUrlCount,
                csvFileName: state.csvFileName,
                workflow: workflow
            };
            try {
                SETUP_PROGRESS_STORAGE.setItem('coherta_setup_funnel_draft', JSON.stringify(payload));
            } catch (error) {}
            const saved = await saveCampaignWorkflowStep();
            if (!saved) return;
            saveWorkflowBtn.textContent = translateSetupText('Gemt');
            setTimeout(() => { saveWorkflowBtn.textContent = translateSetupText('Gem workflow'); }, 1600);
            updateSummary();
        }

        editNodeBtn.addEventListener('click', openModal);
        deleteNodeBtn.addEventListener('click', deleteSelectedNode);
        resetWorkflowBtn.addEventListener('click', () => {
            applyPlaybook(state.playbook || 'network');
            saveSetupProgress('workflow');
        });
        saveWorkflowBtn.addEventListener('click', saveWorkflowState);
        closeWorkflowModal.addEventListener('click', closeModal);
        cancelWorkflowModal.addEventListener('click', closeModal);
        saveWorkflowModal.addEventListener('click', saveModal);
        window.CohertaExtension = {
            request: requestCohertaExtension,
            refresh: refreshPluginStatus,
            login: () => requestCohertaExtension('LOGIN_WITH_SESSION', {sessionId: getSessionId()}),
            connectLinkedIn: () => requestCohertaExtension('CONNECT_LINKEDIN', {active: true, closeTab: true}, 45000)
        };
        if (pluginLoginBtn) pluginLoginBtn.addEventListener('click', loginPluginFromSetup);
        if (directLinkedInLoginBtn) directLinkedInLoginBtn.addEventListener('click', connectLinkedInFromSetup);
        if (linkedInCodeBtn) linkedInCodeBtn.addEventListener('click', submitLinkedInCodeFromSetup);
        if (extensionLinkedInConnectBtn) extensionLinkedInConnectBtn.addEventListener('click', connectLinkedInWithExtensionFromSetup);
        if (chromePluginBtn) chromePluginBtn.addEventListener('click', openChromePlugin);
        modalBackdrop.addEventListener('click', (event) => {
            if (event.target === modalBackdrop) closeModal();
        });
        window.addEventListener('resize', () => {
            if (screens[activeIndex]?.dataset.stepId === 'workflow') renderWorkflow();
        });
        window.addEventListener('message', handlePaymentCheckoutReturnMessage);
        window.addEventListener('message', handleCohertaExtensionMessage);
        window.addEventListener('message', handleChromePluginMessage);
        window.addEventListener('focus', recheckChromePluginAfterFocus);
        document.addEventListener('visibilitychange', recheckChromePluginAfterFocus);
        document.addEventListener('coherta:plugin:ready', markChromePluginInstalled);
        document.addEventListener('coherta:plugin:pong', markChromePluginInstalled);
        document.addEventListener('coherta-plugin-ready', markChromePluginInstalled);
        document.addEventListener('coherta-plugin-pong', markChromePluginInstalled);

        hydrateSetupProgress();
        if (clearhausAcceptTerms) clearhausAcceptTerms.addEventListener('change', () => {
            setPaymentStatus('', '');
            updateStepChrome();
        });
        bindSetupLanguageSwitcher();
        localizeSetupText(document);
        renderSteps();
        if (document.getElementById('sourceTitle') && storedSetupProgress.sourceTitle) document.getElementById('sourceTitle').value = storedSetupProgress.sourceTitle;
        if (document.getElementById('sourceCountry') && storedSetupProgress.sourceCountry) document.getElementById('sourceCountry').value = storedSetupProgress.sourceCountry;
        if (document.getElementById('setupLeadQualification') && storedSetupProgress.leadQualification) document.getElementById('setupLeadQualification').value = storedSetupProgress.leadQualification;
        syncCampaignWizard();
        ['sourceTitle', 'sourceCountry', 'setupLeadQualification'].forEach((id) => {
            const field = document.getElementById(id);
            if (!field) return;
            field.addEventListener(field.tagName === 'SELECT' ? 'change' : 'input', () => {
                syncCampaignWizard();
                saveSetupProgress('workflow');
                updateStepChrome();
            });
        });
        renderPalette();
        if (storedSetupProgress.workflow && Array.isArray(storedSetupProgress.workflow.nodes)) {
            workflow = storedSetupProgress.workflow;
            state.workflowSaved = !!storedSetupProgress.workflowSaved;
        } else if (state.playbook === 'blank') {
            workflow = createBlankWorkflow();
        } else if (state.playbook === 'dialogue') {
            workflow = createDialogueWorkflow();
        }
        selectedNodeId = workflow.nodes[0]?.id || null;
        renderWorkflow();
        hydrateLinkedInConnectUi();
        bindPaymentActivationLink();
        setupChromePluginAvailability();
        showInitialStep(resolveInitialStepIndex());
        if (!paymentReturnStatus()) {
            saveSetupProgress(screens[activeIndex]?.dataset.stepId || '');
        }
        handlePaymentReturnOnLoad();
    })();
</script>

<?php include('partial/footer-end.php') ?>
