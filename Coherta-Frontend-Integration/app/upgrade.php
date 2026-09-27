<?php
require_once __DIR__ . '/base.php';
$PageTitle = 'Upgrade subscription';
include __DIR__ . '/partial/header.php';
$payment_information_required = false;
?>
<link rel="stylesheet" href="/app/assets/css/product-upgrade.css?v=1">
<?php include __DIR__ . '/partial/loader.php'; ?>
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include __DIR__ . '/partial/topbar.php'; ?>
    <div class="page-body-wrapper">
        <?php include __DIR__ . '/partial/sidebar.php'; ?>
        <main class="page-body">
            <div class="container-fluid product-upgrade" id="product-upgrade" aria-busy="true">
                <a class="sc-back" href="/app/my-account">Back to settings</a>
                <div class="sc-heading"><div><p class="sc-eyebrow">ACCOUNT &amp; BILLING</p><h1>Upgrade your subscription</h1><p>Add a product to your account with Revolut.</p></div></div>
                <div id="upgrade-message" class="sc-message" role="status" hidden></div>
                <section class="card sc-subscription">
                    <h2 id="upgrade-title">Loading your offer…</h2>
                    <dl class="sc-details">
                        <div><dt>New subscription total</dt><dd id="upgrade-price">—</dd></div>
                        <div><dt>Billing period</dt><dd id="upgrade-period">—</dd></div>
                    </dl>
                    <p id="upgrade-terms"></p>
                    <button id="upgrade-confirm" class="btn sc-start" type="button" disabled>Continue with Revolut</button>
                    <button id="upgrade-refresh" class="btn sc-refresh" type="button">Refresh</button>
                </section>
            </div>
        </main>
        <?php include __DIR__ . '/partial/footer.php'; ?>
    </div>
</div>
<?php include __DIR__ . '/partial/scripts.php'; ?>
<script src="/app/assets/js/product-upgrade.js?v=1"></script>
<?php include __DIR__ . '/partial/footer-end.php'; ?>
