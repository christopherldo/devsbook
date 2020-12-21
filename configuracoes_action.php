<?php
require_once('./config.php');
require_once('./models/Auth.php');
require_once('./dao/UserDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$userDao = new UserDaoMysql($pdo);

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$birthdate = filter_input(INPUT_POST, 'birthdate');
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS);
$work = filter_input(INPUT_POST, 'work', FILTER_SANITIZE_SPECIAL_CHARS);
$lastPassword = filter_input(INPUT_POST, 'last-password');
$password = filter_input(INPUT_POST, 'password');
$passwordConfirmation = filter_input(INPUT_POST, 'password-confirmation');

if ($name && $email && $birthdate) {
  $userInfo->name = $name;
  $userInfo->city = $city;
  $userInfo->work = $work;

  // E-MAIL
  if ($userInfo->email !== $email) {
    if ($userDao->findByEmail($email) === false) {
      $userInfo->email = $email;
    } else {
      $_SESSION['flash'] = 'E-mail já cadastrado';

      header("Location: $base/configuracoes.php");
      exit;
    }
  }

  // BIRTHDATE
  $birthdate = explode('/', $birthdate);

  if (count($birthdate) !== 3) {
    $_SESSION['flash'] = 'Data de nascimento inválida';
    header("Location: $base/configuracoes.php");
    exit;
  }

  $birthdate = $birthdate[2] . '-' . $birthdate[1] . '-' . $birthdate[0];

  if (strtotime($birthdate) === false) {
    $_SESSION['flash'] = 'Data de nascimento inválida';

    header("Location: $base/configuracoes.php");
    exit;
  }

  if (strtotime($birthdate) >= (new DateTime())->modify('-13 years')->getTimestamp()) {
    $_SESSION['flash'] = 'Você precisa ter pelo menos 13 anos';

    header("Location: $base/configuracoes.php");
    exit;
  }

  $userInfo->birthdate = $birthdate;

  // PASSWORD
  if (empty($password) === false) {
    if (empty($lastPassword) === false) {
      $lasthash = hash('sha256', $lastPassword . $userInfo->salt);

      if ($lasthash === $userInfo->password) {
        if ($password === $passwordConfirmation) {
          $hash = hash('sha256', $password . $userInfo->salt);
          $userInfo->password = $hash;
        } else {
          $_SESSION['flash'] = 'Senhas não coincidem';

          header("Location: $base/configuracoes.php");
          exit;
        }
      } else {
        $_SESSION['flash'] = 'Senha antiga não coincide';

        header("Location: $base/configuracoes.php");
        exit;
      }
    } else {
      $_SESSION['flash'] = 'Preencha a senha antiga se quiser alterar a senha';

      header("Location: $base/configuracoes.php");
      exit;
    }
  }

  // AVATAR
  if (isset($_FILES['avatar']) && empty($_FILES['avatar']['tmp_name'] === false)) {
    $newAvatar = $_FILES['avatar'];

    if ($_FILES['avatar']['error'] === 0) {
      $acceptable = [
        'image/jpeg',
        'image/jpg',
        'image/png'
      ];

      if (in_array($newAvatar['type'], $acceptable)) {
        $avatarWidth = 200;
        $avatarHeight = 200;

        list($witdhOrig, $heightOrig) = getimagesize($newAvatar['tmp_name']);
        $ratio = $witdhOrig / $heightOrig;

        $newWidth = $avatarWidth;
        $newHeight = $newWidth / $ratio;

        if ($newHeight < $avatarHeight) {
          $newHeight = $avatarHeight;
          $newWidth = $newHeight * $ratio;
        }

        $x = ($avatarWidth - $newWidth) / 2;
        $y = ($avatarHeight - $newHeight) / 2;

        $finalImage = imagecreatetruecolor($avatarWidth, $avatarHeight);

        switch ($newAvatar['type']) {
          case 'image/jpeg':
          case 'image/jpg':
            $image = imagecreatefromjpeg($newAvatar['tmp_name']);
            break;
          case 'image/png':
            $image = imagecreatefrompng($newAvatar['tmp_name']);
            break;
        }

        imagecopyresampled(
          $finalImage,
          $image,
          $x,
          $y,
          0,
          0,
          $newWidth,
          $newHeight,
          $witdhOrig,
          $heightOrig
        );

        $avatarName = $userInfo->publicId . '.webp';

        imagewebp($finalImage, './media/avatars/' . $avatarName, 100);

        $userInfo->avatar = $avatarName;
      } else {
        $_SESSION['flash'] = 'Formato de imagem não aceita';

        header("Location: $base/configuracoes.php");
      }
    }
  }

  // COVER
  if (isset($_FILES['cover']) && empty($_FILES['cover']['tmp_name'] === false)) {
    $newCover = $_FILES['cover'];

    if ($_FILES['cover']['error'] === 0) {
      $acceptable = [
        'image/jpeg',
        'image/jpg',
        'image/png'
      ];

      if (in_array($newCover['type'], $acceptable)) {
        $coverWidth = 850;
        $coverHeight = 313;

        list($witdhOrig, $heightOrig) = getimagesize($newCover['tmp_name']);
        $ratio = $witdhOrig / $heightOrig;

        $newWidth = $coverWidth;
        $newHeight = $newWidth / $ratio;

        if ($newHeight < $coverHeight) {
          $newHeight = $coverHeight;
          $newWidth = $newHeight * $ratio;
        }

        $x = ($coverWidth - $newWidth) / 2;
        $y = ($coverHeight - $newHeight) / 2;

        $finalImage = imagecreatetruecolor($coverWidth, $coverHeight);

        switch ($newCover['type']) {
          case 'image/jpeg':
          case 'image/jpg':
            $image = imagecreatefromjpeg($newCover['tmp_name']);
            break;
          case 'image/png':
            $image = imagecreatefrompng($newCover['tmp_name']);
            break;
        }

        imagecopyresampled(
          $finalImage,
          $image,
          $x,
          $y,
          0,
          0,
          $newWidth,
          $newHeight,
          $witdhOrig,
          $heightOrig
        );

        $coverName = $userInfo->publicId . '.webp';

        imagewebp($finalImage, './media/covers/' . $coverName, 100);

        $userInfo->cover = $coverName;
      } else {
        $_SESSION['flash'] = 'Formato de imagem não aceita';

        header("Location: $base/configuracoes.php");
      }
    }
  }
  $userDao->update($userInfo);

  $_SESSION['flash'] = 'Configurações atualizadas com sucesso';
}

header("Location: $base/configuracoes.php");
exit;
