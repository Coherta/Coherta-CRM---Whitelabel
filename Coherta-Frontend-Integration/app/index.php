<?php
header("Location: /app/campaign/campaigns-dashboard");
die();
require_once "./base.php";
$PageTitle = "Frontpage";
$dashboard = $client->get("dashboard/overview");
$yesterday = date('Y-m-d', strtotime('-1 day'));
$new_companies_count = 0;
$updated_companies_count = 0;
$linkedIn_leads = 0;
$won_deals_today = 0;
$lost_deals_today = 0;
$overall_lead_status = [];
foreach ($dashboard['companies'] as $company) {
    if ($company['day'] === $yesterday) {
        $new_companies_count = $company['new_companies_count'] + $new_companies_count;
        $updated_companies_count = $company['updated_companies_count'] + $updated_companies_count;
        $linkedIn_leads = $company['linkedIn_leads'] + $linkedIn_leads;
        $lost_deals_today = $company['lost_deals_today'] + $lost_deals_today;
        $overall_lead_status[] = $company['overall_lead_status'];
    }
    $won_deals_today = $company['won_deals_today'] + $won_deals_today;
    $latest_id = $company['id'];
}
include('partial/header.php');
include('partial/loader.php');

?>
<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <!-- Page Header Start-->
  <?php include('partial/topbar.php') ?>
  <!-- Page Header Ends-->
  <!-- Page Body Start-->
  <div class="page-body-wrapper">
    <!-- Page Sidebar Start-->
    <?php include('partial/sidebar.php') ?>
    <!-- Page Sidebar Ends-->
    <div class="page-body">
      <?php include('partial/breadcrumb.php') ?>
      <!-- Container-fluid starts-->

      <div class="container-fluid">
        <div class="row widget-grid">
          <div class="col-xxl-4 col-sm-6 box-col-6">
            <div class="card profile-box">
              <div class="card-body">
                <div class="media">
                  <div class="media-body">
                    <div class="greeting-user">
                      <h4 class="f-w-600">Welcome to Coherta</h4>
                      <div class="whatsnew-btn">
                          <p>&nbsp;<br />&nbsp;</p>
                      </div>
                    </div>
                  </div>
                  <div>
                    <div class="clockbox">
                      <svg id="clock" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600">
                        <g id="face">
                          <circle class="circle" cx="300" cy="300" r="253.9"></circle>
                          <path class="hour-marks" d="M300.5 94V61M506 300.5h32M300.5 506v33M94 300.5H60M411.3 107.8l7.9-13.8M493 190.2l13-7.4M492.1 411.4l16.5 9.5M411 492.3l8.9 15.3M189 492.3l-9.2 15.9M107.7 411L93 419.5M107.5 189.3l-17.1-9.9M188.1 108.2l-9-15.6"></path>
                          <circle class="mid-circle" cx="300" cy="300" r="16.2"></circle>
                        </g>
                        <g id="hour">
                          <path class="hour-hand" d="M300.5 298V142"></path>
                          <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                        </g>
                        <g id="minute">
                          <path class="minute-hand" d="M300.5 298V67"></path>
                          <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                        </g>
                        <g id="second">
                          <path class="second-hand" d="M300.5 350V55"></path>
                          <circle class="sizing-box" cx="300" cy="300" r="253.9"> </circle>
                        </g>
                      </svg>
                    </div>
                    <div class="badge f-10 p-0" id="txt"></div>
                  </div>
                </div>
                <div class="cartoon"><img class="img-fluid" src="assets/images/dashboard/cartoon.svg" alt="vector women with leptop" width="247" height="218"></div>
              </div>
            </div>
          </div>
          <div class="col-xxl-auto col-xl-12 col-sm-6 box-col-6">
            <div class="row">
              <div class="col-xxl-12 col-xl-6 box-col-12">
                <div class="card widget-1 widget-with-chart">
                  <div class="card-body">
                    <div>
                        <h4 class="mb-1"><?php echo $new_companies_count; ?></h4><span class="f-light">New Leads</span>
                    </div>
                    <div class="order-chart">
                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xxl-12 col-xl-6 box-col-12">
                <div class="card widget-1 widget-with-chart">
                  <div class="card-body">
                    <div>
                        <h4 class="mb-1"><?php echo $won_deals_today; ?></h4><span class="f-light">Won deals this month</span>
                    </div>
                    <div class="profit-chart">
                      
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
            <div class="col-xxl-auto col-xl-12 col-sm-6 box-col-6">
                <div class="row">
                    <div class="col-xxl-12 col-xl-6 box-col-12">
                        <div class="card widget-1 widget-with-chart">
                            <div class="card-body">
                                <div>
                                    <h4 class="mb-1"><?php echo $updated_companies_count; ?></h4><span class="f-light">Updated Leads</span>
                                </div>
                                <div class="order-chart">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12 col-xl-6 box-col-12">
                        <div class="card widget-1 widget-with-chart">
                            <div class="card-body">
                                <div>
                                    <h4 class="mb-1"><?php echo $lost_deals_today; ?></h4><span class="f-light">Lost deals this month</span>
                                </div>
                                <div class="profit-chart">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-auto col-xl-12 col-sm-6 box-col-6">
                <div class="row">
                    <div class="col-xxl-12 col-xl-6 box-col-12">
                        <div class="card widget-1 widget-with-chart">
                            <div class="card-body">
                                <div>
                                    <h4 class="mb-1"><?php echo $linkedIn_leads; ?></h4><span class="f-light">LinkedIn Leads</span>
                                </div>
                                <div class="order-chart">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-12 col-xl-6 box-col-12">
                        <div class="card widget-1 widget-with-chart">
                            <div class="card-body">
                                <div>
                                    <h4 class="mb-1">-</h4><span class="f-light">In negotiation right now</span>
                                </div>
                                <div class="profit-chart">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

          <div class="col-xxl-8 col-lg-12 box-col-12">
            <div class="card">
              <div class="card-header card-no-border">
                <h5>Overall</h5>
              </div>
              <div class="card-body pt-0">
                <div class="row m-0 overall-card">
                  <div class="col-xl-9 col-md-12 col-sm-7 p-0">
                    <div class="chart-right">
                      <div class="row">
                        <div class="col-xl-12">
                          <div class="card-body p-0">
                            <ul class="balance-data">
                                <li data-id="2"><span class="circle bg-primary"> </span><span class="f-light ms-1"><a href="leads.php?stage_id=2">Contacted</a></span></li>
                                <li data-id="3"><span class="circle bg-primary"> </span><span class="f-light ms-1"><a href="leads.php?stage_id=3">Qualified</a></span></li>
                                <li data-id="4"><span class="circle bg-primary"> </span><span class="f-light ms-1"><a href="leads.php?stage_id=4">Proposal</a></span></li>
                                <li data-id="5"><span class="circle bg-primary"> </span><span class="f-light ms-1"><a href="leads.php?stage_id=5">Negotiation</a></span></li>
                            </ul>
                            <div class="current-sale-container">
                              <div id="chart-currently"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-xl-3 col-md-12 col-sm-5 p-0">
                    <div class="row g-sm-4 g-2">
                      <div class="col-xl-12 col-md-4">
                        <div class="light-card balance-card widget-hover">
                          <div class="svg-box">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#income"></use>
                            </svg>
                          </div>
                          <div> <span class="f-light">Total Leads</span>
                            <h6 class="mt-1 mb-0" id="total_leads">0</h6>
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-12 col-md-4">
                        <div class="light-card balance-card widget-hover">
                          <div class="svg-box">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#expense"></use>
                            </svg>
                          </div>
                          <div> <span class="f-light">Initial</span>
                            <h6 class="mt-1 mb-0" id="total_initial">0</h6>
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-12 col-md-4">
                        <div class="light-card balance-card widget-hover">
                          <div class="svg-box">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#doller-return"></use>
                            </svg>
                          </div>
                          <div> <span class="f-light">Won leads</span>
                            <h6 class="mt-1 mb-0" id="total_won">0</h6>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-4 col-xl-7 col-md-6 col-sm-5 box-col-6">
            <div class="card height-equal">
              <div class="card-header card-no-border">
                <div class="header-top">
                  <h5>Open To-do</h5>
                </div>
              </div>
              <div class="card-body pt-0">
                  <div class="card-body pt-0">
                      <div class="appointment-table table-responsive">
                          <table class="table table-bordernone">
                              <tbody>
                              <?php
                              if (isset($dashboard["todo"]) && is_array($dashboard["todo"])) {
                                  $i = 0;
                                  foreach ($dashboard["todo"] as $todo) {
                                      echo '<tr>';
                                      echo '<td><img class="img-fluid img-40 rounded-circle" src="' . $client->getColleagueImage($todo['creator']) . '" alt="user"></td>';
                                      echo '<td class="img-content-box"><a class="d-block f-w-500" target="_blank" href="lead.php?id='.$todo['pipeline_company_id'].'">' . htmlspecialchars($todo["company_name"]) . '<br />' . htmlspecialchars($todo["body"]) . '</a><span class="f-light">' . htmlspecialchars($todo["deadline"]) . '</span></td>';
                                      echo '</tr>';

                                      if($i > 10) break;
                                      $i++;
                                  }
                              }
                              ?>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Container-fluid Ends-->
    </div>
    <!-- footer start-->
    <?php include('partial/footer.php') ?>
  </div>
</div>
<?php include('partial/scripts.php') ?>
<!-- Plugins JS start-->
<script src="assets/js/clock.js"></script>
<script src="assets/js/chart/apex-chart/moment.min.js"></script>
<script src="assets/js/notify/bootstrap-notify.min.js"></script>
<script src="assets/js/dashboard/default.js"></script>
<script src="assets/js/notify/index.js"></script>
<script src="assets/js/height-equal.js"></script>
<script src="assets/js/animation/wow/wow.min.js"></script>
<!-- Plugins JS Ends-->
<script>
  new WOW().init();
</script>
<?php include('partial/footer-end.php') ?>