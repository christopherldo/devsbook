<?php

session_start();

$base = getenv("BASE_URL") . ":" . getenv("PORT");

$db_name = getenv("DATABASE_NAME");
$db_host = getenv("DATABASE_HOST");
$db_port = getenv("DATABASE_PORT");
$db_user = getenv("DATABASE_USER");
$db_pass = getenv("DATABASE_PASSWORD");

$pdo = new PDO("pgsql:dbname=$db_name;host=$db_host;port=$db_port", $db_user, $db_pass);
