<?php
require_once "./base.php";
$PageTitle = "Users";

$users = $client->get("company/users");
$myAccessRights = is_array($client->accessRights ?? null) ? $client->accessRights : [];

include('partial/header.php');
include('partial/loader.php');
?>

    <style>
        .users-page-shell {
            display: grid;
            gap: 24px;
        }

        .users-hero-card,
        .users-table-card,
        .users-empty-state,
        .access-rights-modal .modal-content,
        #createUserModal .modal-content {
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            border-radius: 28px;
            overflow: hidden;
        }

        .users-hero-card {
            position: relative;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #2563eb 100%);
            color: #fff;
            padding: 32px;
        }

        .users-hero-card::after {
            content: "";
            position: absolute;
            inset: auto -100px -120px auto;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            filter: blur(8px);
        }

        .users-hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .users-hero-copy h1,
        .users-hero-copy h2,
        .users-hero-copy p {
            color: #fff;
            margin: 0;
        }

        .users-hero-copy h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .users-hero-copy p {
            max-width: 760px;
            color: rgba(255,255,255,0.82);
            font-size: 1rem;
            line-height: 1.65;
        }

        .users-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-end;
        }

        .users-hero-btn,
        .users-secondary-btn,
        .users-save-btn,
        .users-danger-btn,
        .users-ghost-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            font-weight: 600;
            border: 0;
            transition: all .18s ease;
            text-decoration: none;
        }

        .users-hero-btn {
            background: #fff;
            color: #0f172a;
        }

        .users-hero-btn:hover,
        .users-secondary-btn:hover,
        .users-save-btn:hover,
        .users-danger-btn:hover,
        .users-ghost-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        }

        .users-secondary-btn {
            background: rgba(255,255,255,0.14);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.18);
        }

        .users-stats-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 28px;
            position: relative;
            z-index: 1;
        }

        .users-stat {
            background: rgba(255,255,255,0.09);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 18px 20px;
            backdrop-filter: blur(10px);
        }

        .users-stat-label {
            display: block;
            font-size: .88rem;
            color: rgba(255,255,255,0.72);
            margin-bottom: 8px;
        }

        .users-stat-value {
            display: block;
            font-size: 1.7rem;
            line-height: 1;
            font-weight: 700;
            color: #fff;
        }

        .users-table-card {
            background: #fff;
        }

        .users-table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 28px 18px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.07);
        }

        .users-table-head h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
        }

        .users-table-head p {
            margin: 6px 0 0;
            color: #64748b;
        }

        .users-table-wrap {
            padding: 8px 22px 24px;
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            min-width: 1120px;
        }

        .users-table thead th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            font-weight: 700;
            padding: 0 16px 6px;
            border: 0;
            white-space: nowrap;
        }

        .users-table tbody tr {
            background: #f8fafc;
        }

        .users-table tbody td {
            padding: 18px 16px;
            vertical-align: middle;
            border-top: 1px solid rgba(148, 163, 184, 0.18);
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }

        .users-table tbody td:first-child {
            border-left: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 18px 0 0 18px;
        }

        .users-table tbody td:last-child {
            border-right: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 0 18px 18px 0;
        }

        .user-avatar-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.14);
            background: #e2e8f0;
        }

        .user-primary-name {
            font-weight: 700;
            color: #0f172a;
            display: block;
            line-height: 1.2;
        }

        .user-secondary-meta {
            color: #64748b;
            font-size: .92rem;
            display: block;
            margin-top: 3px;
        }

        .users-inline-actions,
        .users-inline-access {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .users-save-btn {
            background: #2563eb;
            color: #fff;
        }

        .users-danger-btn {
            background: #dc2626;
            color: #fff;
        }

        .users-ghost-btn {
            background: #fff;
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.10);
        }

        .users-ghost-btn:disabled,
        .users-save-btn:disabled,
        .users-danger-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .badge-soft,
        .badge-soft-success,
        .badge-soft-danger,
        .badge-soft-warning,
        .badge-soft-muted {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: .82rem;
            white-space: nowrap;
        }

        .badge-soft-success {
            background: rgba(22, 163, 74, 0.12);
            color: #15803d;
        }

        .badge-soft-danger {
            background: rgba(220, 38, 38, 0.12);
            color: #b91c1c;
        }

        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .badge-soft-muted {
            background: rgba(100, 116, 139, 0.12);
            color: #475569;
        }

        .users-empty-state {
            background: #fff;
            padding: 48px 28px;
            text-align: center;
            color: #64748b;
        }

        .users-empty-state h3 {
            color: #0f172a;
            margin-bottom: 8px;
        }

        .modal.access-rights-modal .modal-dialog,
        #createUserModal .modal-dialog {
            max-width: 720px;
        }

        .access-rights-modal .modal-content,
        #createUserModal .modal-content {
            border: 0;
        }

        .access-rights-modal .modal-header,
        .access-rights-modal .modal-footer,
        #createUserModal .modal-header,
        #createUserModal .modal-footer {
            border: 0;
            padding: 24px 28px;
        }

        .access-rights-modal .modal-body,
        #createUserModal .modal-body {
            padding: 0 28px 28px;
        }

        .access-rights-intro {
            color: #64748b;
            line-height: 1.65;
            margin: 8px 0 0;
        }

        .access-rights-grid {
            display: grid;
            gap: 14px;
            margin-top: 8px;
        }

        .access-rights-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: #f8fafc;
            border-radius: 18px;
        }

        .access-rights-item strong {
            color: #0f172a;
            display: block;
            margin-bottom: 4px;
        }

        .access-rights-item span {
            color: #64748b;
            font-size: .93rem;
            line-height: 1.5;
        }

        .access-rights-item.is-disabled-by-permission {
            opacity: 0.55;
            background: #f1f5f9;
        }

        .access-rights-item.is-disabled-by-permission .form-check-input {
            cursor: not-allowed;
        }

        .form-check.form-switch .form-check-input {
            width: 3rem;
            height: 1.6rem;
            cursor: pointer;
        }

        .users-toast {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 9999;
            min-width: 280px;
            max-width: 420px;
            padding: 14px 16px;
            border-radius: 16px;
            color: #fff;
            background: #0f172a;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
            opacity: 0;
            transform: translateY(10px);
            pointer-events: none;
            transition: .18s ease;
        }

        .users-toast.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .users-toast.is-error {
            background: #b91c1c;
        }

        .users-toast.is-success {
            background: #166534;
        }

        .users-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .users-form-field {
            display: grid;
            gap: 8px;
        }

        .users-form-field.full {
            grid-column: 1 / -1;
        }

        .users-form-field label {
            color: #0f172a;
            font-weight: 600;
        }

        .users-form-field input {
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 14px;
            min-height: 52px;
            padding: 0 16px;
            outline: none;
        }

        .users-form-field input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        @media (max-width: 991px) {
            .users-hero-top,
            .users-table-head {
                flex-direction: column;
                align-items: stretch;
            }

            .users-hero-actions {
                justify-content: flex-start;
            }

            .users-stats-row,
            .users-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <?php include('partial/topbar.php'); ?>
        <div class="page-body-wrapper">
            <?php include('partial/sidebar.php'); ?>
            <div class="page-body">
                <?php include('partial/breadcrumb.php'); ?>
                <div class="container-fluid">
                    <div class="users-page-shell">
                        <?php
                        $totalUsers = is_array($users) ? count($users) : 0;
                        $enabledUsers = 0;
                        $connectedUsers = 0;

                        if (!empty($users) && is_array($users)) {
                            foreach ($users as $user) {
                                if (empty($user['disabled'])) {
                                    $enabledUsers++;
                                }
                                if ($user['connect'] == 1) {
                                    $connectedUsers++;
                                }
                            }
                        }
                        ?>

                        <section class="users-hero-card">
                            <div class="users-hero-top">
                                <div class="users-hero-copy">
                                    <h2>Team access and permissions</h2>
                                    <p>Manage your company users, control platform access, and update employee permissions in one place. Open access rights for any employee to quickly deactivate selected modules across Coherta.</p>
                                </div>
                                <div class="users-hero-actions">
                                    <button class="users-hero-btn" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">Create new user</button>
                                </div>
                            </div>
                            <div class="users-stats-row">
                                <div class="users-stat">
                                    <span class="users-stat-label">Total users</span>
                                    <span class="users-stat-value" id="totalUsersCount"><?php echo (int) $totalUsers; ?></span>
                                </div>
                                <div class="users-stat">
                                    <span class="users-stat-label">Enabled users</span>
                                    <span class="users-stat-value" id="enabledUsersCount"><?php echo (int) $enabledUsers; ?></span>
                                </div>
                                <div class="users-stat">
                                    <span class="users-stat-label">LinkedIn connected</span>
                                    <span class="users-stat-value" id="connectedUsersCount"><?php echo (int) $connectedUsers; ?></span>
                                </div>
                            </div>
                        </section>

                        <section class="users-table-card">
                            <div class="users-table-head">
                                <div>
                                    <h3>Company users</h3>
                                    <p>Open access rights for each employee, or enable and disable user accounts directly from the table.</p>
                                </div>
                            </div>

                            <?php if (!empty($users) && is_array($users)) { ?>
                                <div class="users-table-wrap">
                                    <table id="user-table" class="users-table">
                                        <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Username</th>
                                            <th>Created</th>
                                            <th>LinkedIn</th>
                                            <th>Status</th>
                                            <th>Access Rights</th>
                                            <th>Account</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($users as $user) {
                                            $userId = htmlspecialchars((string) ($user['id'] ?? ''));
                                            $isConnected = $user['connect'] == 1;
                                            $isDisabled = !empty($user['disabled']);
                                            $userImage = htmlspecialchars($client->getColleagueImage($user['id'] ?? null));
                                            $safeUsername = htmlspecialchars($user['username'] ?? '');
                                            $safeFullName = htmlspecialchars($user['full_name'] ?? '');
                                            $safeEmail = htmlspecialchars($user['email'] ?? '');
                                            $safeCreated = htmlspecialchars($user['created'] ?? '');
                                            $userAccessRights = isset($user['access_rights']) && is_array($user['access_rights']) ? $user['access_rights'] : [];
                                            ?>
                                            <tr data-user-row="<?php echo $userId; ?>">
                                                <td>
                                                    <div class="user-avatar-wrap">
                                                        <img class="user-avatar" src="<?php echo $userImage; ?>" alt="<?php echo $safeFullName; ?>">
                                                        <div>
                                                            <span class="user-primary-name"><?php echo $safeFullName ?: 'Unnamed user'; ?></span>
                                                            <span class="user-secondary-meta">ID: <?php echo $userId; ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo $safeUsername ?: '<span class="user-secondary-meta">Not set</span>'; ?></td>
                                                <td><?php echo $safeCreated ?: '<span class="user-secondary-meta">Unknown</span>'; ?></td>
                                                <td id="linkedin-status-<?php echo $userId; ?>">
                                                    <?php if ($isConnected) { ?>
                                                        <span class="badge-soft-success">Connected</span>
                                                    <?php } else { ?>
                                                        <span class="badge-soft-warning">Disconnected</span>
                                                    <?php } ?>
                                                </td>
                                                <td id="account-status-<?php echo $userId; ?>">
                                                    <?php if ($isDisabled) { ?>
                                                        <span class="badge-soft-danger">Disabled</span>
                                                    <?php } else { ?>
                                                        <span class="badge-soft-success">Enabled</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <div class="users-inline-access">
                                                        <button
                                                                type="button"
                                                                class="users-ghost-btn"
                                                                onclick='openAccessRightsModal(<?php echo json_encode([
                                                                        "id" => $user['id'] ?? '',
                                                                        "full_name" => $user['full_name'] ?? '',
                                                                        "email" => $user['email'] ?? '',
                                                                        "access_rights" => $userAccessRights,
                                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>)'>
                                                            Access Rights
                                                        </button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="users-inline-actions">
                                                        <form method="post" action="javascript:void(0);" style="display:inline;" onsubmit="toggleUserStatus(event, '<?php echo $userId; ?>', <?php echo $isDisabled ? 'false' : 'true'; ?>)">
                                                            <button type="submit" class="<?php echo $isDisabled ? 'users-save-btn' : 'users-danger-btn'; ?>">
                                                                <?php echo $isDisabled ? 'Enable' : 'Disable'; ?>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div class="users-empty-state">
                                    <h3>No users found</h3>
                                    <p>Create your first employee to start managing access rights and team permissions.</p>
                                    <button class="users-hero-btn" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">Create new user</button>
                                </div>
                            <?php } ?>
                        </section>
                    </div>
                </div>
            </div>
            <?php include('partial/footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="createUserForm">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1" id="createUserModalLabel">Create new user</h5>
                            <p class="access-rights-intro">Add a colleague and invite them into your Coherta workspace.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="users-form-grid">
                            <div class="users-form-field full">
                                <label for="fullName">Full Name</label>
                                <input type="text" id="fullName" name="full_name" required>
                            </div>
                            <div class="users-form-field full">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="users-ghost-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="users-save-btn">Create user</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade access-rights-modal" id="accessRightsModal" tabindex="-1" aria-labelledby="accessRightsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="accessRightsForm">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1" id="accessRightsModalLabel">Access Rights</h5>
                            <p class="access-rights-intro" id="accessRightsModalIntro">Select which modules should be deactivated for this employee.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="accessRightsUserId" value="">
                        <div class="access-rights-grid">
                            <label class="access-rights-item" for="deactivate_all_access">
                                <div>
                                    <strong>Deactivate all access</strong>
                                    <span>Turn off every listed access right for this employee in one click.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="deactivate_all_access" name="deactivate_all_access">
                                </div>
                            </label>

                            <label class="access-rights-item" for="deactivate_crm_integration">
                                <div>
                                    <strong>Deactivate CRM Integration</strong>
                                    <span>Remove access to CRM integration related functionality.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input access-right-checkbox" type="checkbox" id="deactivate_crm_integration" name="deactivate_crm_integration">
                                </div>
                            </label>

                            <label class="access-rights-item" for="deactivate_advanced_notification">
                                <div>
                                    <strong>Deactivate Advanced Notification</strong>
                                    <span>Disable advanced notifications and related automation visibility.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input access-right-checkbox" type="checkbox" id="deactivate_advanced_notification" name="deactivate_advanced_notification">
                                </div>
                            </label>

                            <label class="access-rights-item" for="deactivate_sales_notification">
                                <div>
                                    <strong>Deactivate Sales Notification</strong>
                                    <span>Disable sales signal notifications for this employee.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input access-right-checkbox" type="checkbox" id="deactivate_sales_notification" name="deactivate_sales_notification">
                                </div>
                            </label>

                            <label class="access-rights-item" for="deactivate_campaigns">
                                <div>
                                    <strong>Deactivate Campaigns</strong>
                                    <span>Hide campaigns and prevent campaign actions for this employee.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input access-right-checkbox" type="checkbox" id="deactivate_campaigns" name="deactivate_campaigns">
                                </div>
                            </label>

                            <label class="access-rights-item" for="deactivate_recruiter_campaigns">
                                <div>
                                    <strong>Deactivate Recruiter Campaigns</strong>
                                    <span>Hide recruiter campaigns and prevent recruiter campaign actions for this employee.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input access-right-checkbox" type="checkbox" id="deactivate_recruiter_campaigns" name="deactivate_recruiter_campaigns">
                                </div>
                            </label>

                            <label class="access-rights-item" for="deactivate_gtm_templates">
                                <div>
                                    <strong>Deactivate GTM Templates</strong>
                                    <span>Restrict access to GTM templates and related setup flows.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input access-right-checkbox" type="checkbox" id="deactivate_gtm_templates" name="deactivate_gtm_templates">
                                </div>
                            </label>

                            <label class="access-rights-item" for="deactivate_lead_view">
                                <div>
                                    <strong>Deactivate Lead View</strong>
                                    <span>Prevent access to lead detail pages and lead view components.</span>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input access-right-checkbox" type="checkbox" id="deactivate_lead_view" name="deactivate_lead_view">
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="users-ghost-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="users-save-btn">Save access rights</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="users-toast" id="usersToast"></div>

    <script>
        window.__myAccessRights = <?php echo json_encode($myAccessRights, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        window.__initialUsers = <?php echo json_encode(array_map(function ($user) use ($client) {
            return [
                    'id' => $user['id'] ?? null,
                    'username' => $user['username'] ?? '',
                    'full_name' => $user['full_name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'created' => $user['created'] ?? '',
                    'connect' => !empty($user['connect']),
                    'disabled' => !empty($user['disabled']),
                    'image' => $client->getColleagueImage($user['id'] ?? null),
                    'access_rights' => isset($user['access_rights']) && is_array($user['access_rights']) ? $user['access_rights'] : []
            ];
        }, is_array($users) ? $users : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>

<?php include('partial/scripts.php'); ?>
    <script>
        const bearerToken = document.body.getAttribute('session_id');
        const accessRightsModalElement = document.getElementById('accessRightsModal');
        const accessRightsModal = accessRightsModalElement ? new bootstrap.Modal(accessRightsModalElement) : null;
        const createUserModalElement = document.getElementById('createUserModal');
        const accessRightCheckboxSelector = '.access-right-checkbox';
        const myAccessRights = window.__myAccessRights || {};

        const accessRightKeys = [
            'deactivate_all_access',
            'deactivate_crm_integration',
            'deactivate_advanced_notification',
            'deactivate_sales_notification',
            'deactivate_campaigns',
            'deactivate_recruiter_campaigns',
            'deactivate_gtm_templates',
            'deactivate_lead_lists',
            'deactivate_lead_view'
        ];

        function showToast(message, type = 'success') {
            const toast = document.getElementById('usersToast');
            if (!toast) return;

            toast.textContent = message;
            toast.className = 'users-toast is-visible ' + (type === 'error' ? 'is-error' : 'is-success');

            window.clearTimeout(window.__usersToastTimer);
            window.__usersToastTimer = window.setTimeout(() => {
                toast.className = 'users-toast';
            }, 2600);
        }

        function toBooleanFlag(value) {
            if (value === true || value === 1 || value === '1') return true;
            if (value === false || value === 0 || value === '0' || value === null || value === undefined || value === '') return false;

            if (typeof value === 'string') {
                const normalized = value.trim().toLowerCase();
                if (['true', 'yes', 'on'].includes(normalized)) return true;
                if (['false', 'no', 'off'].includes(normalized)) return false;
            }

            return Boolean(value);
        }

        function canManageAccessRight(key) {
            if (!key) return false;

            if (key === 'deactivate_all_access') {
                return accessRightKeys
                    .filter(item => item !== 'deactivate_all_access')
                    .some(item => canManageAccessRight(item));
            }

            return !toBooleanFlag(myAccessRights[key]);
        }

        function getAccessRightsPayload() {
            return {
                deactivate_all_access: document.getElementById('deactivate_all_access').checked,
                deactivate_crm_integration: document.getElementById('deactivate_crm_integration').checked,
                deactivate_advanced_notification: document.getElementById('deactivate_advanced_notification').checked,
                deactivate_sales_notification: document.getElementById('deactivate_sales_notification').checked,
                deactivate_campaigns: document.getElementById('deactivate_campaigns').checked,
                deactivate_recruiter_campaigns: document.getElementById('deactivate_recruiter_campaigns').checked,
                deactivate_gtm_templates: document.getElementById('deactivate_gtm_templates').checked,
                deactivate_lead_lists: document.getElementById('deactivate_lead_lists').checked,
                deactivate_lead_view: document.getElementById('deactivate_lead_view').checked
            };
        }

        function applyAccessRightsToForm(accessRights = {}) {
            const defaults = {
                deactivate_all_access: false,
                deactivate_crm_integration: false,
                deactivate_advanced_notification: false,
                deactivate_sales_notification: false,
                deactivate_campaigns: false,
                deactivate_recruiter_campaigns: false,
                deactivate_gtm_templates: false,
                deactivate_lead_lists: false,
                deactivate_lead_view: false
            };

            const state = Object.assign({}, defaults, accessRights || {});

            Object.keys(defaults).forEach((key) => {
                const field = document.getElementById(key);
                if (field) {
                    field.checked = !!state[key];
                }
            });
        }

        function syncAllAccessToggle() {
            const master = document.getElementById('deactivate_all_access');
            const boxes = Array.from(document.querySelectorAll(accessRightCheckboxSelector)).filter((checkbox) => !checkbox.disabled);
            if (!master) return;

            if (!boxes.length) {
                master.checked = false;
                return;
            }

            const allChecked = boxes.every((checkbox) => checkbox.checked);
            master.checked = allChecked;
        }

        function updateAccessRightsEditability() {
            accessRightKeys.forEach((key) => {
                const field = document.getElementById(key);
                if (!field) return;

                const allowed = canManageAccessRight(key);
                const item = field.closest('.access-rights-item');

                field.disabled = !allowed;

                if (item) {
                    item.classList.toggle('is-disabled-by-permission', !allowed);
                    item.title = !allowed ? 'You do not have permission to change this access right.' : '';
                }
            });
        }

        function sanitizeAccessRightsPayloadByPermission(payload) {
            const cleaned = { user_id: payload.user_id };

            accessRightKeys.forEach((key) => {
                if (canManageAccessRight(key) && key in payload) {
                    cleaned[key] = payload[key];
                }
            });

            return cleaned;
        }

        document.getElementById('deactivate_all_access').addEventListener('change', function () {
            const shouldCheckAll = this.checked;

            document.querySelectorAll(accessRightCheckboxSelector).forEach((checkbox) => {
                if (!checkbox.disabled) {
                    checkbox.checked = shouldCheckAll;
                }
            });
        });

        document.querySelectorAll(accessRightCheckboxSelector).forEach((checkbox) => {
            checkbox.addEventListener('change', syncAllAccessToggle);
        });

        function setAccessRightsFormLoading(isLoading) {
            const form = document.getElementById('accessRightsForm');
            if (!form) return;

            const controls = form.querySelectorAll('input, button');

            controls.forEach((control) => {
                if (control.id === 'accessRightsUserId') return;

                if (isLoading) {
                    control.dataset.originalDisabled = control.disabled ? '1' : '0';
                    control.disabled = true;
                } else {
                    const wasDisabled = control.dataset.originalDisabled === '1';
                    control.disabled = wasDisabled;
                    delete control.dataset.originalDisabled;
                }
            });
        }

        function normalizeAccessRightsRecord(record) {
            if (!record || typeof record !== 'object') return null;

            const recordUserId = record.user_id ?? record.userId ?? record.id ?? record.employee_id ?? record.company_user_id;
            if (recordUserId === undefined || recordUserId === null || recordUserId === '') {
                return null;
            }

            return {
                userId: String(recordUserId),
                deactivate_all_access: toBooleanFlag(record.deactivate_all_access ?? record.all_access_deactivated),
                deactivate_crm_integration: toBooleanFlag(record.deactivate_crm_integration),
                deactivate_advanced_notification: toBooleanFlag(record.deactivate_advanced_notification),
                deactivate_sales_notification: toBooleanFlag(record.deactivate_sales_notification),
                deactivate_campaigns: toBooleanFlag(record.deactivate_campaigns),
                deactivate_recruiter_campaigns: toBooleanFlag(record.deactivate_recruiter_campaigns),
                deactivate_gtm_templates: toBooleanFlag(record.deactivate_gtm_templates),
                deactivate_lead_lists: toBooleanFlag(record.deactivate_lead_lists),
                deactivate_lead_view: toBooleanFlag(record.deactivate_lead_view)
            };
        }

        function buildAccessRightsMap(payload) {
            const map = {};

            if (!payload) {
                return map;
            }

            const directRecord = normalizeAccessRightsRecord(payload);
            if (directRecord) {
                map[directRecord.userId] = directRecord;
                return map;
            }

            const candidates = Array.isArray(payload)
                ? payload
                : Array.isArray(payload.data)
                    ? payload.data
                    : Array.isArray(payload.users)
                        ? payload.users
                        : Array.isArray(payload.items)
                            ? payload.items
                            : [];

            candidates.forEach((item) => {
                const normalized = normalizeAccessRightsRecord(item);
                if (normalized) {
                    map[normalized.userId] = normalized;
                }
            });

            if (!candidates.length && typeof payload === 'object') {
                Object.keys(payload).forEach((key) => {
                    const value = payload[key];
                    if (value && typeof value === 'object' && !Array.isArray(value)) {
                        const normalized = normalizeAccessRightsRecord(
                            value.user_id !== undefined ? value : Object.assign({ user_id: key }, value)
                        );
                        if (normalized) {
                            map[normalized.userId] = normalized;
                        }
                    }
                });
            }

            return map;
        }

        function mergeUsersWithAccessRights(users, accessRightsMap) {
            if (!Array.isArray(users)) return [];

            return users.map((user) => {
                const userId = user && user.id !== undefined && user.id !== null ? String(user.id) : '';
                return Object.assign({}, user, {
                    access_rights: accessRightsMap && accessRightsMap[userId]
                        ? accessRightsMap[userId]
                        : (user.access_rights || {})
                });
            });
        }

        function fetchAccessRightsMap(userId) {
            return fetch('https://api.coherta.com/users/me/companies/access_rights?user_id=' + encodeURIComponent(userId), {
                headers: {
                    'Authorization': 'Bearer ' + bearerToken
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load access rights.');
                    }
                    return response.json();
                })
                .then(payload => buildAccessRightsMap(payload));
        }

        function getAccessRightsForUser(userId) {
            return fetchAccessRightsMap(userId).then((accessRightsMap) => {
                const normalizedUserId = userId !== undefined && userId !== null ? String(userId) : '';
                return accessRightsMap && accessRightsMap[normalizedUserId] ? accessRightsMap[normalizedUserId] : {};
            });
        }

        function openAccessRightsModal(user) {
            const userId = user && user.id ? user.id : '';
            document.getElementById('accessRightsUserId').value = userId;

            const intro = document.getElementById('accessRightsModalIntro');
            const safeName = user && user.full_name ? user.full_name : 'this employee';
            const safeEmail = user && user.email ? ' (' + user.email + ')' : '';

            intro.textContent = 'Loading access rights for ' + safeName + safeEmail + '...';

            applyAccessRightsToForm({});
            updateAccessRightsEditability();
            syncAllAccessToggle();

            if (accessRightsModal) {
                accessRightsModal.show();
            }

            setAccessRightsFormLoading(true);

            getAccessRightsForUser(userId)
                .then((accessRights) => {
                    applyAccessRightsToForm(accessRights || {});
                    updateAccessRightsEditability();
                    syncAllAccessToggle();
                    intro.textContent = 'Select which modules should be deactivated for ' + safeName + safeEmail + '.';
                })
                .catch(() => {
                    applyAccessRightsToForm(user && user.access_rights ? user.access_rights : {});
                    updateAccessRightsEditability();
                    syncAllAccessToggle();
                    intro.textContent = 'Select which modules should be deactivated for ' + safeName + safeEmail + '.';
                    showToast('Failed to load the latest access rights. Showing current table values instead.', 'error');
                })
                .finally(() => {
                    setAccessRightsFormLoading(false);
                    updateAccessRightsEditability();
                    syncAllAccessToggle();
                });
        }

        function updateStatsFromUsers(users) {
            const total = Array.isArray(users) ? users.length : 0;
            const enabled = Array.isArray(users) ? users.filter(user => !user.disabled).length : 0;
            const connected = Array.isArray(users) ? users.filter(user => !!user.connect).length : 0;

            const totalNode = document.getElementById('totalUsersCount');
            const enabledNode = document.getElementById('enabledUsersCount');
            const connectedNode = document.getElementById('connectedUsersCount');

            if (totalNode) totalNode.textContent = total;
            if (enabledNode) enabledNode.textContent = enabled;
            if (connectedNode) connectedNode.textContent = connected;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function toggleUserStatus(event, userId, enable) {
            event.preventDefault();

            fetch('https://api.coherta.com/company/users/' + userId, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + bearerToken
                },
                body: JSON.stringify({ disabled: !enable })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to update user status.');
                    }
                    showToast('User account updated successfully.');
                })
                .catch(() => showToast('Failed to update user status.', 'error'));
        }

        document.getElementById('createUserForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();

            fetch('https://api.coherta.com/company/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + bearerToken
                },
                body: JSON.stringify({ full_name: fullName, email: email })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to create user.');
                    }

                    return response.json();
                })
                .then(data => {
                    if (data && data.user_id) {
                        const modalInstance = bootstrap.Modal.getInstance(createUserModalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }

                        document.getElementById('createUserForm').reset();
                        showToast('User created successfully.');
                        window.setTimeout(() => {
                            window.location.reload();
                        }, 700);
                    } else {
                        throw new Error('Failed to create user.');
                    }
                })
                .catch(() => showToast('Failed to create user.', 'error'));
        });

        document.getElementById('accessRightsForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const userId = document.getElementById('accessRightsUserId').value;
            if (!userId) {
                showToast('No user selected for access rights.', 'error');
                return;
            }

            const rawPayload = Object.assign({ user_id: userId }, getAccessRightsPayload());
            const payload = sanitizeAccessRightsPayloadByPermission(rawPayload);

            fetch('https://api.coherta.com/users/me/companies/access_rights', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + bearerToken
                },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to save access rights.');
                    }

                    showToast('Access rights saved successfully.');

                    if (accessRightsModal) {
                        accessRightsModal.hide();
                    }
                })
                .catch(() => showToast('Failed to save access rights.', 'error'));
        });

        function renderUsersTable(users) {
            const card = document.querySelector('.users-table-card');
            if (!card) return;

            updateStatsFromUsers(users);

            const existingWrap = card.querySelector('.users-table-wrap, .users-empty-state');
            if (existingWrap) {
                existingWrap.remove();
            }

            if (!users || !users.length) {
                const empty = document.createElement('div');
                empty.className = 'users-empty-state';
                empty.innerHTML = `
                <h3>No users found</h3>
                <p>Create your first employee to start managing access rights and team permissions.</p>
                <button class="users-hero-btn" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">Create new user</button>
            `;
                card.appendChild(empty);
                return;
            }

            const wrap = document.createElement('div');
            wrap.className = 'users-table-wrap';
            wrap.innerHTML = `
            <table id="user-table" class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th>LinkedIn</th>
                        <th>Status</th>
                        <th>Access Rights</th>
                        <th>Account</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        `;

            const tbody = wrap.querySelector('tbody');

            users.forEach(user => {
                const row = document.createElement('tr');
                const image = escapeHtml(user.image || user.profile_image || user.avatar || '');
                const fullName = escapeHtml(user.full_name || 'Unnamed user');
                const username = user.username ? escapeHtml(user.username) : '<span class="user-secondary-meta">Not set</span>';
                const email = user.email ? escapeHtml(user.email) : '<span class="user-secondary-meta">Not set</span>';
                const created = user.created ? escapeHtml(user.created) : '<span class="user-secondary-meta">Unknown</span>';
                const connectedBadge = user.connect ? '<span class="badge-soft-success">Connected</span>' : '<span class="badge-soft-warning">Disconnected</span>';
                const statusBadge = user.disabled ? '<span class="badge-soft-danger">Disabled</span>' : '<span class="badge-soft-success">Enabled</span>';

                row.innerHTML = `
                <td>
                    <div class="user-avatar-wrap">
                        <img class="user-avatar" src="${image}" alt="${fullName}">
                        <div>
                            <span class="user-primary-name">${fullName}</span>
                            <span class="user-secondary-meta">ID: ${escapeHtml(user.id)}</span>
                        </div>
                    </div>
                </td>
                <td>${username}</td>
                <td>${email}</td>
                <td>${created}</td>
                <td>${connectedBadge}</td>
                <td>${statusBadge}</td>
                <td>
                    <div class="users-inline-access">
                        <button type="button" class="users-ghost-btn js-access-rights-btn">Access Rights</button>
                    </div>
                </td>
                <td>
                    <div class="users-inline-actions">
                        <form method="post" action="javascript:void(0);" style="display:inline;">
                            <button type="submit" class="${user.disabled ? 'users-save-btn' : 'users-danger-btn'}">${user.disabled ? 'Enable' : 'Disable'}</button>
                        </form>
                    </div>
                </td>
            `;

                row.querySelector('form').addEventListener('submit', function (event) {
                    toggleUserStatus(event, user.id, user.disabled ? false : true);
                });

                row.querySelector('.js-access-rights-btn').addEventListener('click', function () {
                    openAccessRightsModal({
                        id: user.id,
                        full_name: user.full_name || '',
                        email: user.email || '',
                        access_rights: user.access_rights || {}
                    });
                });

                tbody.appendChild(row);
            });

            card.appendChild(wrap);
        }

    </script>
<?php include('partial/footer-end.php'); ?>
