<?php
require_once "./base.php";
$PageTitle = "Integration to CRM";
include('partial/header.php'); ?>

<link rel="stylesheet" type="text/css" href="assets/css/vendors/todo.css">

<?php include('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php'); ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php'); ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>
            <!-- Container-fluid starts-->
            <div class="container-fluid email-wrap bookmark-wrap todo-wrap">
                <div class="row">
                    <?php
                    $plugins = $client->get("plugins.php");

                    foreach ($plugins as $plugin) {
                        echo '<div class="col-xl-4 col-sm-6 col-xxl-3 col-ed-4 box-col-4">
                        <div class="card social-profile">
                            <div class="card-body">
                                <div class="social-img-wrap">
                                    <div class="social-img"><a href="plugin.php?id='.$plugin['id'].'" data-bs-original-title="" title=""><img src="'.$plugin['logo'].'" alt="profile"></a></div>
                                    <div class="edit-icon">
                                        <svg>
                                            <use href="assets/svg/icon-sprite.svg#profile-check"></use>
                                        </svg>
                                    </div>
                                </div>
                                <div class="social-details">
                                    <h5 class="mb-1"><a href="plugin.php?id='.$plugin['id'].'" data-bs-original-title="" title="">'.$plugin['title'].'</a></h5>
                                </div>
                            </div>
                        </div>
                    </div>';
                    }
                    ?>


                </div>
            </div>
            <!-- Container-fluid Ends-->
            <!-- Container-fluid ends                    -->
        </div>

        <?php include('partial/footer.php'); ?>
    </div>
</div>
<?php include('partial/scripts.php'); ?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    jQuery(function() {
        let currentAutocompleteRequest = null;
        jQuery(function() {
            jQuery("#customer-autocomplete").autocomplete({
                source: function(request, response) {
                    // Abort previous request if still running
                    if (currentAutocompleteRequest) {
                        currentAutocompleteRequest.abort();
                    }
                    currentAutocompleteRequest = $.ajax({
                        url: "https://api.coherta.com/pipeline/leads/search",
                        dataType: "json",
                        headers: {
                            'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
                            'Accept': 'application/json'
                        },
                        data: { company_name: request.term },
                        success: function(data) {
                            response($.map(data, function(item, key) {
                                return {
                                    label: item,
                                    value: key
                                };
                            }));
                        },
                        complete: function() {
                            currentAutocompleteRequest = null;
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    jQuery(this).val(ui.item.label);
                    jQuery(this).attr('data-id', ui.item.value);
                    return false;
                }
            });
        });

    });
</script>
<script src="assets/js/todo/todo.js"></script>
<script src="assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>