<?php
require_once('./config.php');
require_once('./models/Auth.php');
require_once('./dao/PostLikeDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (empty($id) === false) {
  $postLikeDao = new PostLikeDaoMysql($pdo);
  $postLikeDao->likeToggle($id, $userInfo->publicId);
}
