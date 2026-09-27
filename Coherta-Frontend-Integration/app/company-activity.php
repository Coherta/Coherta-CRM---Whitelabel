<?php
require_once __DIR__ . '/base.php';
$PageTitle = 'Company activity';
include __DIR__ . '/partial/header.php';
?>
<link rel="stylesheet" href="/app/assets/css/company-activity.css?v=1">
<?php include __DIR__ . '/partial/loader.php'; ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include __DIR__ . '/partial/topbar.php'; ?>
    <div class="page-body-wrapper">
        <?php include __DIR__ . '/partial/sidebar.php'; ?>
        <main class="page-body">
            <?php include __DIR__ . '/partial/company-activity.php'; ?>
        </main>
        <?php include __DIR__ . '/partial/footer.php'; ?>
    </div>
</div>
<?php include __DIR__ . '/partial/scripts.php'; ?>
<script src="/app/assets/js/company-activity.js?v=1"></script>
<?php include __DIR__ . '/partial/footer-end.php'; ?>
