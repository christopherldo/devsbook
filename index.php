<?php

require_once('config.php');
require_once('models/Auth.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

echo 'Index';
