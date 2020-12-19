<?php
require_once('./config.php');
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <title>Login - Devsbook</title>
  <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1" />
  <link rel="stylesheet" href="<?= $base ?>/assets/css/login.css" />
</head>

<body>
  <header>
    <div class="container">
      <a href="<?= $base ?>"><img src="<?= $base ?>/assets/images/devsbook_logo.png" /></a>
    </div>
  </header>
  <section class="container main">
    <form method="POST" action="<?= $base ?>/signup_action.php">
      <?php if (empty($_SESSION['flash']) === false) : ?>
        <?= $_SESSION['flash'] ?>
        <?php $_SESSION['flash'] = '' ?>
      <?php endif; ?>

      <input placeholder="Digite seu nome completo" class="input" type="text" name="name" id="name" required/>

      <input placeholder="Digite seu e-mail" class="input" type="email" name="email" id="email" required/>

      <input placeholder="Digite sua senha" class="input" type="password" name="password" id="password" required/>

      <input placeholder="Digite sua data de nascimento" class="input" type="text" name="birthdate" id="birthdate" required/>

      <input class="button" type="submit" value="Fazer cadastro" id="submit"/>

      <a href="<?= $base ?>/login.php">Já possui uma conta? Faça login</a>
    </form>
  </section>

  <script src="https://unpkg.com/imask"></script>
  <script>
    IMask(
      document.getElementById('birthdate'),
      {mask: '00/00/0000'}
    );
  </script>
</body>

</html>