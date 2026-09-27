<?php include('partial/header.php'); ?>
<div class="container-fluid p-0">
  <!-- Unlock page start-->
  <div class="authentication-main mt-0">
    <div class="row">
      <div class="col-12">
        <div class="login-card">
          <div>
            <div><a class="logo" href="index.html"><img class="img-fluid for-light" src="assets/images/logo/login.png" alt="looginpage"><img class="img-fluid for-dark" src="../assets/images/logo/logo_dark.png" alt="looginpage" width="294" height="70"></a></div>
            <div class="login-main">
              <form class="theme-form">
                <h4>Unlock </h4>
                <div class="form-group">
                  <label class="col-form-label">Enter your Password</label>
                  <div class="form-input position-relative">
                    <input class="form-control" type="password" name="login[password]" required="" placeholder="*********">
                    <div class="show-hide"><span class="show"> </span></div>
                  </div>
                </div>
                <div class="form-group mb-0">
                  <div class="checkbox p-0">
                    <input id="checkbox1" type="checkbox">
                    <label class="text-muted" for="checkbox1">Remember password</label>
                  </div>
                  <button class="btn btn-primary btn-block w-100" type="submit">Unlock</button>
                </div>
                <p class="mt-4 mb-0">Already Have an account?<a class="ms-2" href="login.html">Sign in</a></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('partial/scripts.php'); ?>  
<?php include('partial/footer-end.php'); ?>