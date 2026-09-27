<?php
require_once "./base.php";
if(isset($_POST["login"])) {
    if ($client->fetchToken($_POST["login"]["username"], $_POST["login"]["password"])) {
        $user = $client->getCurrentUser();
        echo $_SESSION["token"];
        die();
    }
}
$publicPage = True;
$PageTitle = "Login";
include('partial/header.php');
?>
  <div class="container-fluid p-0">
    <div class="row m-0">
      <div class="col-12 p-0">    
        <div class="login-card">
          <div>
            <div><a class="logo" href="/"><img class="img-fluid for-light" src="/assets/img/logo/logo.png?w=300" alt="looginpage" width="3200" height="715">
                    <img class="img-fluid for-dark" src="/assets/img/logo/logo.png?w=300" alt="looginpage" width="3200" height="715"></a></div>
            <div class="login-main"> 
              <form class="theme-form" method="POST">
                <h4>Sign in to account</h4>
                <p>Enter your email & password to login</p>
                <div class="form-group">
                  <label class="col-form-label">Username</label>
                  <input class="form-control" type="text" required="" name="login[username]" placeholder="Test@gmail.com">
                </div>
                <div class="form-group">
                  <label class="col-form-label">Password</label>
                  <div class="form-input position-relative">
                    <input class="form-control" type="password" name="login[password]" required="" placeholder="*********">
                  </div>
                </div>
                <div class="form-group mb-0">
                  <div class="text-end mt-3">
                    <button class="btn btn-primary btn-block w-100" type="submit">Sign in</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include('partial/footer-end.php'); ?>