<?php include('partial/header.php');?>         

<link rel="stylesheet" type="text/css" href="assets/css/vendors/tree.css">

<?php include('partial/loader.php');?>

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
          <div class="col-sm-12 col-xl-6">
            <div class="card">
              <div class="card-header">
                <h5>Basic Tree</h5>
              </div>
              <div class="card-body">
                <div id="treeBasic">
                  <ul>
                    <li>Admin
                      <ul>
                        <li data-jstree="{&quot;opened&quot;:true}">Assets
                          <ul>
                            <li data-jstree="{&quot;opened&quot;:false}">Css
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Css one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Css two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Js
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:true,&quot;type&quot;:&quot;file&quot;}">Js one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Js two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Scss
                              <ul>
                                <li data-jstree="{&quot;opened&quot;:false}">Sub Child
                                  <ul>
                                    <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                  </ul>
                                </li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Scss two</li>
                              </ul>
                            </li>
                          </ul>
                        </li>
                        <li data-jstree="{&quot;opened&quot;:true}">Default
                          <ul>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Dashboard</li>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Typography</li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">index file</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-xl-6">
            <div class="card">
              <div class="card-header">
                <h5>Checkbox Tree</h5>
              </div>
              <div class="card-body">
                <div id="treecheckbox">
                  <ul>
                    <li>Admin
                      <ul>
                        <li data-jstree="{&quot;opened&quot;:true}">Assets
                          <ul>
                            <li data-jstree="{&quot;opened&quot;:false}">Css
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Css one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Css two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Js
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:true,&quot;type&quot;:&quot;file&quot;}">Js one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Js two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Scss
                              <ul>
                                <li data-jstree="{&quot;opened&quot;:false}">Sub Child
                                  <ul>
                                    <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                  </ul>
                                </li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Scss two</li>
                              </ul>
                            </li>
                          </ul>
                        </li>
                        <li data-jstree="{&quot;opened&quot;:true}">Default
                          <ul>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Dashboard</li>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Typography</li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">index file</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-xl-6">
            <div class="card">
              <div class="card-header">
                <h5>Drag Tree</h5>
              </div>
              <div class="card-body">
                <div id="dragTree">
                  <ul>
                    <li>Admin
                      <ul>
                        <li data-jstree="{&quot;opened&quot;:true}">Assets
                          <ul>
                            <li data-jstree="{&quot;opened&quot;:false}">Css
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Css one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Css two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Js
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:true,&quot;type&quot;:&quot;file&quot;}">Js one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Js two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Scss
                              <ul>
                                <li data-jstree="{&quot;opened&quot;:false}">Sub Child
                                  <ul>
                                    <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                  </ul>
                                </li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Scss two</li>
                              </ul>
                            </li>
                          </ul>
                        </li>
                        <li data-jstree="{&quot;opened&quot;:true}">Default
                          <ul>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Dashboard</li>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Typography</li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">index file</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-xl-6">
            <div class="card">
              <div class="card-header">
                <h5>Contextmenu Tree</h5>
              </div>
              <div class="card-body">
                <div id="contextmenu">
                  <ul>
                    <li>Admin
                      <ul>
                        <li data-jstree="{&quot;opened&quot;:true}">Assets
                          <ul>
                            <li data-jstree="{&quot;opened&quot;:false}">Css
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Css one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Css two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Js
                              <ul>
                                <li data-jstree="{&quot;selected&quot;:true,&quot;type&quot;:&quot;file&quot;}">Js one</li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Js two</li>
                              </ul>
                            </li>
                            <li data-jstree="{&quot;opened&quot;:true}">Scss
                              <ul>
                                <li data-jstree="{&quot;opened&quot;:false}">Sub Child
                                  <ul>
                                    <li data-jstree="{&quot;selected&quot;:false,&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Sub File</li>
                                  </ul>
                                </li>
                                <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Scss two</li>
                              </ul>
                            </li>
                          </ul>
                        </li>
                        <li data-jstree="{&quot;opened&quot;:true}">Default
                          <ul>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Dashboard</li>
                            <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">Typography</li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                    <li data-jstree="{&quot;type&quot;:&quot;file&quot;}">index file</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Container-fluid Ends-->
    </div>
    
    <?php include('partial/footer.php');?>
  </div>
</div>
<?php include('partial/scripts.php'); ?>
<script src="assets/js/tree/jstree.min.js"></script>
<script src="assets/js/tree/tree.js"></script>
<?php include('partial/footer-end.php'); ?>