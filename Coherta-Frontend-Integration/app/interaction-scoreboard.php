<?php
require_once "./base.php";
$PageTitle = "Notifications - Interaction Scoreboard";

$interactors = $client->get("notifications/interactions/scoreboard");
include('partial/header.php');
include('partial/advanced-notifications.php');
$plugin_installed = advanced_notifications_is_installed($company_data);
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
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <?php advanced_notifications_nav('scoreboard'); ?>
<?php
if(!$plugin_installed) {
?>
                            <?php advanced_notifications_locked_card($company_data); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('partial/footer.php');?>
            <?php include('partial/scripts.php'); ?>
        </div>
    </div>
<?php
} else {
?>
                            <div class="card">
                                <div class="card-header">
                                    <h5>Interactions Scoreboard</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    if(count($interactors)>0) {
                                        ?>

                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Scrambled URL</th>
                                                <th>Interaction Count</th>
                                                <th>Subject Breakdown</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            foreach ($interactors as $notification) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($notification['rank']) . "</td>";
                                                if($notification['image'])
                                                    echo "<td><img src='https://api.coherta.com/images/" . htmlspecialchars($notification['image']) . "' alt='Image' class='rounded-circle me-3'  style='width:40px;height:40px;'></td>";
                                                else
                                                    echo "<td></td>";
                                                echo "<td>" . htmlspecialchars($notification['name']) . "</td>";
                                                echo "<td><a href='" . htmlspecialchars($notification['scrambled_url']) . "' target='_blank'>Profile</a></td>";
                                                echo "<td>" . htmlspecialchars($notification['likes_count']) . "</td>";
                                                echo "<td>" . htmlspecialchars($notification['subject_breakdown']) . "</td>";
                                                echo "<td><button class='btn btn-warning btn-sm ignore-btn' data-id='" . htmlspecialchars($notification['scrambled_id']) . "'>Ignore</button></td>";

                                                echo "</tr>";
                                                $i++;

                                                if($i >= 100) {
                                                    break;
                                                }
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                        <?php
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?php include('partial/footer.php');?>
        </div>
    </div>
<?php include('partial/scripts.php'); ?>
    <script>
        const bearerToken = document.body.getAttribute('session_id');

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.ignore-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const row = btn.closest('tr');
                    const participantId = btn.getAttribute('data-id');
                    fetch('https://api.coherta.com/notifications/interactions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + bearerToken
                        },
                        body: JSON.stringify({ scrambled_id: participantId, action: 'ignore' })
                    }).then(response => {
                        if(response.ok) {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
<?php } include('partial/footer-end.php'); ?>
