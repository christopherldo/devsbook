<?php

require_once('./config.php');
require_once('./models/Auth.php');

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password');
$passwordConfirmation = filter_input(INPUT_POST, 'password-confirmation');
$birthdate = filter_input(INPUT_POST, 'birthdate');

if ($name && $email && $password && $birthdate) {
  $auth = new Auth($pdo, $base);

  $birthdate = explode('/', $birthdate);

  if (count($birthdate) !== 3) {
    $_SESSION['flash'] = 'Data de nascimento inválida';
    header("Location: $base/signup.php");
    exit;
  }

  $birthdate = $birthdate[2] . '-' . $birthdate[1] . '-' . $birthdate[0];

  if (strtotime($birthdate) === false) {
    $_SESSION['flash'] = 'Data de nascimento inválida';

    header("Location: $base/signup.php");
    exit;
  }

  if (strtotime($birthdate) >= (new DateTime())->modify('-13 years')->getTimestamp()) {
    $_SESSION['flash'] = 'Você precisa ter pelo menos 13 anos para se inscrever';

    header("Location: $base/signup.php");
    exit;
  }

  if (strlen($password) < 8) {
    $_SESSION['flash'] = 'Sua senha precisa conter pelo menos 8 caracteres';

    header("Location: $base/signup.php");
    exit;
  }

  if ($password !== $passwordConfirmation) {
    $_SESSION['flash'] = 'As senhas não coincidem';

    header("Location: $base/signup.php");
    exit;
  }

  if ($auth->emailExists($email) === false) {
    $auth->registerUser($name, $email, $password, $birthdate);

    header("Location: $base");
    exit;
  } else {
    $_SESSION['flash'] = 'E-mail já cadastrado';

    header("Location: $base/signup.php");
    exit;
  }
}

$_SESSION['flash'] = 'Preencha todos os campos corretamente';
header("Location: $base/signup.php");
exit;
