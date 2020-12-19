<?php

require_once('./config.php');

$_SESSION['token'] = '';
header("Location: $base");
exit;
