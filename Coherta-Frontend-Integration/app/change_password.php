<?php
require_once "./base.php";
$PageTitle = "Change password";
include('partial/header.php');
?>
    <link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
<?php include('partial/loader.php') ?>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <?php include('partial/topbar.php');?>
        <!-- Page Header Ends -->
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <?php include('partial/sidebar.php');?>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                <?php include('partial/breadcrumb.php'); ?>
                <!-- Container-fluid starts-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Change password</h5>
                                </div>
                                <div class="card-body">
                                        <div>
                                            <div class="text-center btn-mb">
                                                <form id="changePasswordForm" method="POST" action="#" class="mx-auto" style="max-width:400px;">
                                                    <div class="mb-3">
                                                        <label for="newPassword" class="form-label">New Password</label>
                                                        <input type="password" class="form-control" id="newPassword" name="new_password" required autocomplete="new-password">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required  autocomplete="new-password">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary w-100">Change Password</button>
                                                </form>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('partial/footer.php');?>
        </div>
    </div>
    <script>

        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (newPassword === confirmPassword) {
                try {
                    const response = await fetch('https://api.coherta.com/users/me', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer <?php echo $_SESSION["token"]; ?>'
                        },
                        body: JSON.stringify({ password: newPassword })
                    });
                    if (response.ok) {
                        alert('Password changed successfully.');
                    } else {
                        alert('Failed to change password.');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            } else {
                alert('Passwords do not match.');
            }
        });
    </script>

<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>