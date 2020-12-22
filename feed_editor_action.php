<?php
require_once('./config.php');
require_once('./models/Auth.php');
require_once('./dao/PostDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$body = filter_input(INPUT_POST, 'body', FILTER_SANITIZE_SPECIAL_CHARS);

if ($body) {
  $postDao = new PostDaoMysql($pdo);

  $newPost = new Post();
  $newPost->publicId = $postDao->generateUuid();
  $newPost->idUser = $userInfo->publicId;
  $newPost->type = 'text';
  $newPost->createdAt = gmdate('Y-m-d H:i:s');
  $newPost->body = $body;

  $postDao->insert($newPost);
}

header("Location: $base");
exit;
