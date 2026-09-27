<?php
$params = $_GET;
$params['manual_trial'] = '1';
$params['customer_type'] = 'private';

header('Location: /app/free_trial.php?' . http_build_query($params), true, 302);
exit;
