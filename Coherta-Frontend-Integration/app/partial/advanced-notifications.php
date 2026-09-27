<?php
function advanced_notifications_is_installed($company_data) {
    return coherta_company_has_product($company_data, 'prod_Tj6gfRnDznSezk', 14);
}

function advanced_notifications_nav($active = 'feed') {
    $items = [
        'feed' => ['label' => 'Notifications', 'href' => '/app/wizard-notifications'],
        'setup' => ['label' => 'Search words', 'href' => '/app/wizard-notifications-setup'],
    ];
    echo '<div class="advanced-notifications-nav">';
    echo '<div><h2>Advanced Notifications</h2><p>Monitor relevant LinkedIn activity from saved search words and manage it in focused workspaces.</p></div>';
    echo '<nav aria-label="Advanced Notifications navigation">';
    foreach ($items as $key => $item) {
        echo '<a class="'.($active === $key ? 'active' : '').'" href="'.htmlspecialchars($item['href']).'">'.htmlspecialchars($item['label']).'</a>';
    }
    echo '</nav></div>';
}

function advanced_notifications_locked_card($company_data) {
    ?>
    <div class="card">
        <div class="card-header">
            <span>
                <strong>Advanced Notifications</strong> is a robust plugin that empowers you to efficiently manage and act on company pipeline notifications. Here are 10 ways you can use it:
                <ol>
                    <li>Instantly alert team members about important pipeline updates.</li>
                    <li>Filter notifications by status to focus on active or historical events.</li>
                    <li>Search for specific job titles or keywords to find relevant notifications.</li>
                    <li>Assign notifications to specific pipelines for better organization.</li>
                    <li>View detailed participant information, including LinkedIn profiles.</li>
                    <li>Mark notifications as seen to track progress and avoid duplicates.</li>
                    <li>Delete outdated or irrelevant notifications to keep your dashboard clean.</li>
                    <li>Update notification details directly from modal dialogs.</li>
                    <li>Stay informed about opportunities and changes in real time.</li>
                    <li>Improve collaboration and accountability within your company.</li>
                </ol>
                <br>
                <strong>Status:</strong> <span style="color: red;">Not installed</span> for your account.
                    <br>
                    <button class="btn btn-success" onclick="window.location.href='upgrade.php?product=Tj6gfRnDznSezk'">Upgrade to enable Advanced Notifications</button>
            </span>
        </div>
    </div>
    <?php
}
?>
<style>
    .advanced-notifications-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        padding: 18px 22px;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }
    .advanced-notifications-nav h2 {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
    }
    .advanced-notifications-nav p {
        margin: 5px 0 0;
        color: #667085;
        font-size: 13px;
        line-height: 1.45;
    }
    .advanced-notifications-nav nav {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .advanced-notifications-nav a {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 0 13px;
        border: 1px solid #e6e9f2;
        border-radius: 10px;
        color: #475467;
        background: #fff;
        font-size: 13px;
        font-weight: 700;
    }
    .advanced-notifications-nav a.active {
        color: #4f46e5;
        border-color: rgba(79, 70, 229, .28);
        background: rgba(79, 70, 229, .07);
    }
    .notification-management-card .card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 12px;
    }
    .notification-management-card h5 {
        margin: 0;
        font-size: 15px;
    }
    .notification-management-card p {
        margin: 4px 0 0;
        color: #667085;
        font-size: 12px;
        line-height: 1.45;
    }
    .list-of-notification-headers {
        max-height: 156px;
        overflow-y: auto;
        padding: 12px 14px !important;
        background: #fbfcff;
        border-top: 1px solid #edf0f7;
    }
    .list-of-notification-headers .notification-action-btn {
        margin: 4px !important;
        padding: 5px 9px;
        border-color: rgba(79, 70, 229, .35);
        color: #4f46e5;
        background: #fff;
        font-size: 12px;
        line-height: 1.3;
        opacity: .78;
    }
    .list-of-notification-headers .notification-action-btn:hover {
        opacity: 1;
        background: rgba(79, 70, 229, .06);
    }
    @media (max-width: 900px) {
        .advanced-notifications-nav {
            align-items: stretch;
            flex-direction: column;
        }
        .advanced-notifications-nav nav {
            justify-content: flex-start;
        }
    }
</style>
