<?php
require_once "./base.php";
$PageTitle = "Connect your Office365 Email with Coherta";
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
                                    <h5>Connect your Office365</h5>
                                </div>
                                <div class="card-body">
                                        <div>
                                            <div class="text-center btn-mb">
                                                <form id="microsoftConnectForm" action="https://api.coherta.com/email/microsoft/login.php"
                                                      method="POST" class="d-inline">
                                                    <input type="hidden" name="session_id" id="ms_session_id" value="<?php
                                                     echo $_SESSION["token"];
                                                    ?>">
                                                    <?php
                                                    if(!isset($user['email_provider']["microsoft"])) {
                                                        ?>
                                                        <button type="submit" class="btn btn-primary" id="microsoftConnectBtn">
                                                            <i class="fab fa-microsoft"></i> Connect now
                                                        </button>
                                                        <?php
                                                    } elseif($user['email_provider']["microsoft"] == 1) {
                                                        echo "You are already connected to Office365";
                                                    }
                                                    ?>
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
<div class="modal fade" id="linkedinModal" tabindex="-1" aria-labelledby="linkedinModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="linkedinConnectForm" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="linkedinModalLabel">Connect your LinkedIn</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetLinkedInModal()"></button>
        </div>
        <div class="modal-body" id="linkedinModalBody">
          <div class="mb-3">
            <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
            <input type="email" class="form-control" id="linkedinUsername" name="username" required>
          </div>
          <div class="mb-3">
            <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
            <input type="password" class="form-control" id="linkedinPassword" name="password" required>
          </div>
        </div>
        <div class="modal-footer" id="linkedinModalFooter">
          <button type="submit" class="btn btn-primary">
            <i class="fab fa-linkedin"></i> Connect now
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
let linkedinModal = null;
const bearerToken = document.body.getAttribute('session_id');
document.addEventListener('DOMContentLoaded', function() {
    linkedinModal = new bootstrap.Modal(document.getElementById('linkedinModal'));

    document.getElementById('linkedinConnectForm').onsubmit = async function(e) {
        e.preventDefault();
        const username = document.getElementById('linkedinUsername').value;
        const password = document.getElementById('linkedinPassword').value;
        document.getElementById('linkedinModalBody').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>We are currently connecting to your LinkedIn Account.<br>
                You might receive a notification in your LinkedIn App, to approve our connection request.</p>
            </div>
        `;
        document.getElementById('linkedinModalFooter').innerHTML = '';
        try {
            await fetch('https://api.coherta.com/apps/linkedin/connect', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + bearerToken },
                body: JSON.stringify({ username, password })
            });
            // Poll for connection status every 5 seconds, up to 10 minutes (120 attempts)

            let connected = false;
            for (let i = 0; i < 120; i++) {
                await new Promise(res => setTimeout(res, 5000));
                try {
                    const res = await fetch(`https://api.coherta.com/apps/linkedin/connect`, {
                        method: 'GET',
                        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + bearerToken }
                    });
                    const data = await res.json();
                    if (data.status === 1) {
                        connected = true;
                        break;
                    }
                    else if (data.status === 10) {
                        document.getElementById('linkedinModalBody').innerHTML = `
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>You have received a notification in your LinkedIn App. Please open LinkedIn on your mobile and approve our login request</p>
                            </div>
                        `;
                        document.getElementById('linkedinModalFooter').innerHTML = '';
                    }
                    else if (data.status === 2) {
                        document.getElementById('linkedinModalBody').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            LinkedIn could not validate your email and/or password. Please try again.
                        </div>
                        <div class="mb-3">
                            <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
                            <input type="email" class="form-control" id="linkedinUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
                            <input type="password" class="form-control" id="linkedinPassword" name="password" required>
                        </div>
                    `;
                                        document.getElementById('linkedinModalFooter').innerHTML = `
                        <button type="submit" class="btn btn-primary">
                            <i class="fab fa-linkedin"></i> Connect now
                        </button>
                    `;
                                        break;
                    }
                } catch (err) {
                    // Optionally handle error
                }
            }
            if (connected) {
                document.getElementById('linkedinModalBody').innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success" style="font-size:3rem;"></i>
                        <h5 class="mt-3">Your LinkedIn account is now connected</h5>
                    </div>
                `;
            } else {
                // Optionally handle timeout or failure
            }
        } catch (err) {
            // Optionally handle error
        }
    };
});
function openLinkedInModal() {
    resetLinkedInModal();
    linkedinModal.show();
}
function resetLinkedInModal() {
    document.getElementById('linkedinModalBody').innerHTML = `
      <div class="mb-3">
        <label for="linkedinUsername" class="form-label">LinkedIn Email</label>
        <input type="email" class="form-control" id="linkedinUsername" name="username" required>
      </div>
      <div class="mb-3">
        <label for="linkedinPassword" class="form-label">LinkedIn Password</label>
        <input type="password" class="form-control" id="linkedinPassword" name="password" required>
      </div>
    `;
    document.getElementById('linkedinModalFooter').innerHTML = `
      <button type="submit" class="btn btn-primary">
        <i class="fab fa-linkedin"></i> Connect now
      </button>
    `;
}
</script>
<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>
