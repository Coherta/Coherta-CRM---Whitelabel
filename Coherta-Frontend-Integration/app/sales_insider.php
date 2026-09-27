<?php
require_once __DIR__ . '/base.php';
$PageTitle = 'Get an Introduction';
$title = $PageTitle;
$cohertaFreeIntroductionPage = true;
include __DIR__ . '/partial/header.php';
$paid = true;
if (empty($_SESSION['introductions_csrf'])) $_SESSION['introductions_csrf'] = bin2hex(random_bytes(32));
$introductionConfig = ['endpoint' => '/app/introductions-api', 'csrf' => $_SESSION['introductions_csrf'], 'sender_name' => $user['full_name'] ?? ''];
?>
<link rel="stylesheet" href="/app/assets/css/get-introduction.css?v=<?= filemtime(__DIR__ . '/assets/css/get-introduction.css') ?>">
<?php include __DIR__ . '/partial/loader.php'; ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include __DIR__ . '/partial/topbar.php'; ?>
    <div class="page-body-wrapper">
        <?php include __DIR__ . '/partial/sidebar.php'; ?>
        <div class="page-body">
            <?php if ($paid): ?>
                <?php include __DIR__ . '/partial/get-introduction.php'; ?>
            <?php else: ?>
                <section class="intro-app intro-access">
                    <span class="intro-eyebrow">YOUR NETWORK, WORKING TOGETHER</span><h1>Get an Introduction</h1>
                    <p>Find people who fit your ideal customer profile and ask a trusted connection to introduce you.</p>
                    <p>Enable Get an Introduction to save your star connections and discover introduction opportunities.</p>
                    <?php if (($company_data['method'] ?? '') === 'stripe'): ?>
                        <a class="intro-btn intro-btn-primary" href="/app/upgrade.php?product=Tj6iuxGEvOIWQv">Enable Get an Introduction</a>
                    <?php else: ?><p>Contact your account manager to enable this feature.</p><?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
        <?php include __DIR__ . '/partial/footer.php'; ?>
    </div>
</div>
<?php include __DIR__ . '/partial/scripts.php'; ?>
<?php if ($paid): ?><script src="/app/assets/js/get-introduction.js?v=<?= filemtime(__DIR__ . '/assets/js/get-introduction.js') ?>" defer></script><?php endif; ?>
<?php include __DIR__ . '/partial/footer-end.php'; ?>
