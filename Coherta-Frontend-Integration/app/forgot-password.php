<?php
require_once "./base.php";
$password_reset = false;
if(isset($_POST["forgot"])) {
    if ($client->resetPassword($_POST["forgot"]["username"])) {
        $password_reset = true;
    }
}
$publicPage = True;
$PageTitle = "Forgot Password | Reset Your Password";
$meta_description = "Forgot your password? Enter your email and receive a password reset link to regain access to your account.";
include('partial/header.php');
?>
  <div class="container-fluid p-0">
    <div class="row m-0">
      <div class="col-12 p-0">    
        <div class="login-card">
          <div>
            <div><a class="logo" href="/"><img class="img-fluid for-light" src="/assets/img/logo/logo.png?w=300" alt="looginpage" width="300" height="67">
                    <img class="img-fluid for-dark" src="/assets/img/logo/logo.png?w=300" alt="looginpage" width="300" height="67"></a></div>
            <div class="login-main"> 
              <form class="theme-form" method="POST">
                  <?php if ($password_reset) { ?>
                      <div class="alert alert-success" role="alert">
                          If the email exists in our system, a password reset has been sent.
                      </div>
                  <?php } else { ?>
                <h1>Forgot your password?</h1>
                <p>Enter your email and receive a password reset link.</p>
                <a class="btn coherta-google-login w-100" href="https://api.coherta.com/auth/google">
                  <i class="fa fa-google"></i>
                  <span>Continue with Google</span>
                </a>
                <div class="login-divider"></div>
                <div class="form-group">
                  <label class="col-form-label">Email</label>
                  <input class="form-control" type="email" required="" name="forgot[username]" placeholder="name@company.com" autocomplete="email">
                </div>
                <div class="form-group mb-0">
                  <div class="text-end mt-3">
                    <button class="btn btn-primary btn-block w-100" type="submit">Get a new password</button>
                  </div>
                </div>
                <?php } ?>
                <p class="mt-4 mb-0 text-center">Go to <a class="ms-2" href="login">Log in</a></p>
                <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="sign-up">Create Account</a></p>
                  <p class="mt-4 mb-0 text-center"><a class="ms-2" href="/da/privacy-policy">Privacy Policy</a></p>
              </form>
            </div>
              <div class="text-center mt-3">
                  <h3>Visit our Country Pages:</h3>
                  <a href="/da/" class="btn btn-link">Go to Denmark</a>
                  <a href="/uk/" class="btn btn-link">Go to United Kingdom</a>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include('partial/footer-end.php'); ?>
