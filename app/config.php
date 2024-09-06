<?php

session_start();

$base = getenv("BASE_URL");

$db_name = getenv("DATABASE_USER");
$db_host = 'db';
$db_user = getenv("DATABASE_USER");
$db_pass = getenv("DATABASE_PASSWORD");

$pdo = new PDO("pgsql:dbname=$db_name;host=$db_host", $db_user, $db_pass);
