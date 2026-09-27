<?php
global $PageTitle;
    $url = $_SERVER['REQUEST_URI'];
    $items = explode('/', $url);
?>
<div class="container-fluid">        
    <div class="page-title">
        <div class="row">
            <div class="col-6">
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><svg class="stroke-icon">
                      <use href="assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg></a></li>
                    <li class="breadcrumb-item"><?php echo $items[sizeof($items) - 2]; ?></li>
                    <li class="breadcrumb-item active"><?php echo ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME)); ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

