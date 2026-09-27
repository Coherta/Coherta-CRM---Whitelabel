<?php
global $sidebar_close, $payment_information_url, $user, $user_image;
if (empty($user['id'])) {
    $user = $client->getCurrentUser() ?? [];
}
if (empty($company_data['id'])) {
    $company_data = $client->getCurrentCompanies()[0] ?? [];
}
$sidebar_close = False;
$showPartnerPaymentButton = !empty($_SESSION['return_to_agency'])
    && empty($company_data['stripe_subscription']);
$partnerPaymentUrl = htmlspecialchars($payment_information_url ?? '/app/my-account', ENT_QUOTES, 'UTF-8');
$sidebarUserName = trim((string)($user['full_name'] ?? $user['name'] ?? $user['email'] ?? 'User'));
$sidebarAvatarSources = [$user];
foreach (($user['colleagues'] ?? []) as $colleague) {
    if (is_array($colleague) && (string)($colleague['id'] ?? '') === (string)($user['id'] ?? '')) {
        $sidebarAvatarSources[] = $colleague;
        break;
    }
}
$sidebarUserImage = '';
foreach ($sidebarAvatarSources as $avatarSource) {
    $imageKey = trim((string)($avatarSource['img_key'] ?? ''));
    if ($imageKey !== '') {
        $sidebarUserImage = 'https://api.coherta.com/images/' . rawurlencode($imageKey);
        break;
    }
}
foreach ($sidebarAvatarSources as $avatarSource) {
    if ($sidebarUserImage !== '') {
        break;
    }
    foreach (['image', 'profile_image', 'profile_image_url', 'avatar', 'avatar_url', 'picture', 'picture_url', 'photo', 'photo_url'] as $imageKey) {
        $candidate = trim((string)($avatarSource[$imageKey] ?? ''));
        if ($candidate !== '' && (preg_match('/^https?:\/\//i', $candidate) || str_starts_with($candidate, '/'))) {
            $sidebarUserImage = $candidate;
            break 2;
        }
    }
}
if ($sidebarUserImage === '') {
    $sidebarUserImage = (string)($user_image ?? '/app/assets/images/user/user.png');
}
?>
<div class="sidebar-wrapper <?php if($sidebar_close) { echo "close_icon"; } ?>" sidebar-layout="stroke-svg">
  <div>
    <div class="logo-wrapper"><a href="/app/index"><img class="img-fluid for-light" src="/assets/img/logo/logo.png?w=150" alt=""><img class="img-fluid for-dark" src="/app/assets/images/logo/logo_dark.png" alt="" width="294" height="70"></a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="index"><img class="img-fluid" src="/app/assets/images/logo/logo-icon.png" alt="" width="35" height="35"></a></div>
    <a class="sidebar-personal-profile" href="/app/my-account" aria-label="Open profile for <?php echo htmlspecialchars($sidebarUserName, ENT_QUOTES, 'UTF-8'); ?>">
      <img src="<?php echo htmlspecialchars($sidebarUserImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($sidebarUserName, ENT_QUOTES, 'UTF-8'); ?>" onerror="this.onerror=null;this.src='/app/assets/images/user/user.png';">
      <span>
        <small>Signed in as</small>
        <strong><?php echo htmlspecialchars($sidebarUserName, ENT_QUOTES, 'UTF-8'); ?></strong>
      </span>
    </a>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn"><a href="/app/index"><img class="img-fluid" src="/app/assets/images/logo/logo-icon.png" alt="" width="35" height="35"></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
            <?php
            if(!isset($client->accessRights['deactivate_campaigns']) || $client->accessRights['deactivate_campaigns'] !== 1) {
            ?>
            <li class="sidebar-list">
                <a class="sidebar-link sidebar-title" href="/app/campaign">
                    <svg class="stroke-icon" viewBox="0 0 18 18" aria-hidden="true">
                        <rect width="18" height="18" rx="3" fill="#0A66C2" stroke="none"></rect>
                        <text x="9" y="12" text-anchor="middle" fill="#ffffff" stroke="none" font-family="Arial, Helvetica, sans-serif" font-size="8" font-weight="700">IN</text>
                    </svg>
                    <svg class="fill-icon" viewBox="0 0 18 18" aria-hidden="true">
                        <rect width="18" height="18" rx="3" fill="#0A66C2" stroke="none"></rect>
                        <text x="9" y="12" text-anchor="middle" fill="#ffffff" stroke="none" font-family="Arial, Helvetica, sans-serif" font-size="8" font-weight="700">IN</text>
                    </svg><span class="lan-3">Campaigns</span>
                </a>
            </li>
            <?php
            }
            if(!isset($client->accessRights['deactivate_recruiter_campaigns']) || $client->accessRights['deactivate_recruiter_campaigns'] !== 1) {
                ?>
                <li class="sidebar-list">
                    <a class="sidebar-link sidebar-title" href="/app/recruiters">
                        <svg class="stroke-icon">
                            <use href="/app/assets/svg/icon-sprite.svg#stroke-job-search"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="/app/assets/svg/icon-sprite.svg#fill-job-search"></use>
                        </svg><span class="lan-3">Recruiter Campaigns</span>
                    </a>
                </li>
                <?php
            }
            if(!isset($client->accessRights['deactivate_crm_integration']) || $client->accessRights['deactivate_crm_integration'] !== 1) {
                ?>
                <li class="sidebar-list">
                    <a class="sidebar-link sidebar-title" href="/app/plugins">
                        <svg class="stroke-icon">
                            <use href="/app/assets/svg/icon-sprite.svg#stroke-widget"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="/app/assets/svg/icon-sprite.svg#fill-widget"></use>
                        </svg><span class="lan-3">Integration to CRM</span>
                    </a>
                </li>
                <?php
            }
            if(!isset($client->accessRights['deactivate_advanced_notification']) || $client->accessRights['deactivate_advanced_notification'] !== 1) {
                echo '<li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="/app/wizard-notifications">
                            <svg class="stroke-icon">
                                <use href="/app/assets/svg/icon-sprite.svg#stroke-email"></use>
                            </svg>
                            <svg class="fill-icon">
                                <use href="/app/assets/svg/icon-sprite.svg#fill-email"></use>
                            </svg><span class="lan-3">Advanced Notifications</span>
                        </a>
                    </li>';
            }
            // The free Introduction product is available independently of Campaigns billing.
            {
                echo '<li class="sidebar-list">
                <a class="sidebar-link sidebar-title" href="/app/sales_insider">
                    <svg class="stroke-icon">
                        <use href="/app/assets/svg/icon-sprite.svg#stroke-charts"></use>
                    </svg>
                    <svg class="fill-icon">
                        <use href="/app/assets/svg/icon-sprite.svg#fill-charts"></use>
                    </svg><span class="lan-3">Get an Introduction</span>
                </a>
            </li>';
            }
            if (!empty($company_data['plugins']) && is_array($company_data['plugins'])) {
                foreach ($company_data['plugins'] as $plugin) {
                    if (isset($plugin['plugin_id'], $plugin['installed']) && $plugin['plugin_id'] == 8 && $plugin['installed']) {
                        echo '<li class="sidebar-list">
                                <a class="sidebar-link sidebar-title" href="/app/revision">
                                    <svg class="stroke-icon">
                                        <use href="/app/assets/svg/icon-sprite.svg#stroke-file"></use>
                                    </svg>
                                    <svg class="fill-icon">
                                        <use href="/app/assets/svg/icon-sprite.svg#fill-file"></use>
                                    </svg><span class="lan-3">Tax Flows</span>
                                </a>
                                <ul class="sidebar-submenu">
                                    <li><a href="/app/revision?type=tax">Skattekonto</a></li>
                                    <li><a href="/app/revision?type=vat">Momskvitteringer</a></li>
                                    <li><a href="/app/revision?type=company_registration">Registeringsbevis</a></li>
                                    <li><a href="/app/revision?type=e_indkomst">E-indkomst</a></li>
                                </ul>
                            </li>';
                    }
                }
            }
            ?>
            <li class="sidebar-list">
                <a class="sidebar-link sidebar-title" href="/app/stakeholder-monitoring">
                    <svg class="stroke-icon">
                        <use href="/app/assets/svg/icon-sprite.svg#stroke-user"></use>
                    </svg>
                    <svg class="fill-icon">
                        <use href="/app/assets/svg/icon-sprite.svg#fill-user"></use>
                    </svg><span class="lan-3">Lead Monitoring</span>
                </a>
            </li>
            <?php
            if(!isset($client->accessRights['deactivate_campaigns']) || $client->accessRights['deactivate_campaigns'] !== 1) {
                ?>
                <?php
                foreach($client->getCurrentPipelines() as $pipelines) {
                    if($pipelines['people_list'] == 0)
                        continue;
                    $peopleLeadTitle = ((int)$pipelines['id'] === 1563) ? 'People Leads' : $pipelines['title'];
                    echo '                <li class="sidebar-list">
                    <a class="sidebar-link sidebar-title" href="/app/leads_people?pipeline_id=' . $pipelines['id'] . '">
                        <svg class="fill-icon">
                            <use href="/app/assets/svg/icon-sprite.svg#fill-user"></use>
                        </svg><span class="lan-3">' . htmlspecialchars($peopleLeadTitle) . '</span>
                    </a>
                </li>';
                }
            }
        ?>
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
    <?php if ($showPartnerPaymentButton): ?>
      <a class="sidebar-subscription-action" href="<?php echo $partnerPaymentUrl; ?>">
        <i data-feather="credit-card" aria-hidden="true"></i>
        <span>My Subscription</span>
      </a>
    <?php endif; ?>
    <small class="sidebar-account-ids">Company ID: <?php echo htmlspecialchars((string)($company_data['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> | User ID: <?php echo htmlspecialchars((string)($user['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
  </div>
</div>
