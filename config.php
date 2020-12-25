<?php

session_start();

$base = 'http://localhost';

$db_name = 'devsbook';
$db_host = 'localhost';
$db_user = 'dev';
$db_pass = 'dev';

$pdo = new PDO("mysql:dbname=$db_name;host=$db_host", $db_user, $db_pass);
