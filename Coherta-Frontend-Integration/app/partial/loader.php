<?php
$url = $_SERVER['REQUEST_URI'];
$items = explode('/', $url);
$page = end($items);
?>
</head>
<?php
$sessionToken = htmlspecialchars((string)($_SESSION["token"] ?? ""), ENT_QUOTES, "UTF-8");
echo '<body session_id="' . $sessionToken . '">';
if(isset($_SESSION['return_to_agency'])) {
    echo '<div style="z-index:999999;position:fixed;top:0;left:0;width:100%;background:yellow;color:black;text-align:center;padding:10px;z-index:9999;font-weight:bold;">
        <a href="https://partner.coherta.com/?impersonate='.$_SESSION['return_to_agency'].'&s=r">Return to Agency Account</a>
    </div>';
}
global $payment_information_required, $payment_information_url, $payment_information_title, $payment_information_copy, $payment_information_button_label;
if (!empty($payment_information_required)) {
    $paymentInformationTitle = htmlspecialchars($payment_information_title ?? 'Start my account', ENT_QUOTES, 'UTF-8');
    $paymentInformationCopy = htmlspecialchars($payment_information_copy ?? 'Start my account to use Coherta.', ENT_QUOTES, 'UTF-8');
    $paymentInformationButtonLabel = htmlspecialchars($payment_information_button_label ?? 'Start my account', ENT_QUOTES, 'UTF-8');
    $paymentInformationCompanyId = (int)($company_data['id'] ?? 0);
    echo '<div id="account-start-modal" data-company-id="'.$paymentInformationCompanyId.'"><div style="z-index:999990;position:fixed;inset:0;background:rgba(15,23,42,0.58);backdrop-filter:grayscale(1) blur(2px);"></div>
    <div style="z-index:999998;position:fixed;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;">
        <div role="dialog" aria-modal="true" aria-labelledby="paymentInfoTitle" aria-describedby="paymentInfoCopy" style="width:min(520px,100%);background:#fff;border:1px solid rgba(15,23,42,0.12);border-radius:8px;box-shadow:0 24px 80px rgba(15,23,42,0.28);padding:32px;text-align:center;color:#1f2937;">
            <h2 id="paymentInfoTitle" style="font-size:24px;line-height:1.25;margin:0 0 14px;font-weight:700;">'.$paymentInformationTitle.'</h2>
            <p id="paymentInfoCopy" style="font-size:15px;line-height:1.55;margin:0 0 24px;color:#667085;">'.$paymentInformationCopy.'</p>
            <button type="button" id="paymentInfoStart" style="display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 22px;background:#0d6efd;color:#fff;border:0;border-radius:6px;font:inherit;font-weight:700;cursor:pointer;">'.$paymentInformationButtonLabel.'</button>
            <p id="paymentInfoError" role="status" hidden style="font-size:14px;line-height:1.5;margin:18px 0 0;color:#b42318;"></p>
            <noscript><p>Enable JavaScript to start your account.</p></noscript>
            <p style="font-size:14px;line-height:1.5;margin:18px 0 0;color:#667085;">Alternatively, <a href="/app/logout/" style="color:#0d6efd;font-weight:600;">sign out</a>.</p>
        </div>
    </div></div><script src="/app/assets/js/start-account.js?v=2" defer></script>';
}
?>
  <!-- loader starts-->
  <div class="loader-wrapper">
    <div class="loader-index"><span></span></div>
    <svg>
      <defs></defs>
      <filter id="goo">
        <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
        <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
      </filter>
    </svg>
  </div>
  <!-- loader ends-->
  <!-- tap on top starts-->
  <div class="tap-top"><i data-feather="chevrons-up"></i></div>
  <!-- tap on tap ends-->
