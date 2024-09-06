<?php
require_once('../config.php');
require_once('../models/Auth.php');
require_once('../dao/PostCommentDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$txt = filter_input(INPUT_POST, 'txt', FILTER_SANITIZE_SPECIAL_CHARS);

$array = [];

if ($id && $txt) {
  $postCommentDao = new PostCommentDaoMysql($pdo);

  $newComment = new PostComment();
  $newComment->idPost = $id;
  $newComment->idUser = $userInfo->publicId;
  $newComment->body = $txt;
  $newComment->createdAt = gmdate('Y-m-d H:i:s');

  $postCommentDao->addComment($newComment);

  $array = [
    'error' => '',
    'link' => "$base/perfil?id=$userInfo->publicId",
    'avatar' => "$base/media/avatars/$userInfo->avatar",
    'name' => $userInfo->name,
    'body' => $txt
  ];
}

header("Content-Type: application/json");
echo json_encode($array);
exit;
