<?php
require_once('./config.php');
require_once('./models/Auth.php');
require_once('./dao/PostDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$maxWidth = 600;
$maxHeight = 600;

$array = ['error' => ''];

$postDao = new PostDaoMysql($pdo);

if (isset($_FILES['photo']) && empty($_FILES['photo']['tmp_name']) === false) {
  $photo = $_FILES['photo'];

  $acceptable = [
    'image/jpeg',
    'image/jpg',
    'image/png'
  ];

  if (in_array($photo['type'], $acceptable)) {
    list($widthOrig, $heightOrig) = getimagesize($photo['tmp_name']);

    $ratio = $widthOrig / $heightOrig;
    $ratioMax = $maxWidth / $maxHeight;

    $newWidth = $maxWidth;
    $newHeight = $maxHeight;
    
    if($ratioMax > $ratio){
      $newWidth = $newHeight * $ratio;
    } else {
      $newHeight = $newWidth / $ratio;
    }

    $finalImage = imagecreatetruecolor($newWidth, $newHeight);

    switch ($photo['type']) {
      case 'image/jpeg':
      case 'image/jpg':
        $image = imagecreatefromjpeg($photo['tmp_name']);
        break;
      case 'image/png':
        $image = imagecreatefrompng($photo['tmp_name']);
        break;
    }

    imagecopyresampled(
      $finalImage,
      $image,
      0,
      0,
      0,
      0,
      $newWidth,
      $newHeight,
      $widthOrig,
      $heightOrig
    );

    $postId = $postDao->generateUuid();
    $photoName = $postId . '.webp';

    imagewebp($finalImage, "./media/uploads/$photoName");

    $newPost = new Post();
    $newPost->publicId = $postId;
    $newPost->idUser = $userInfo->publicId;
    $newPost->type = 'photo';
    $newPost->createdAt = gmdate('Y-m-d H:i:s');
    $newPost->body = $photoName;

    $postDao->insert($newPost);
  } else {
    $array['error'] = 'Extensão de arquivo não suportada.';
  }
} else {
  $array['error'] = 'A imagem não foi enviada.';
}

header("Content-Type: application/json");
echo json_encode($array);
exit;
