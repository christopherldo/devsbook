<?php
require_once('./config.php');
require_once('./models/Auth.php');
require_once('./dao/UserDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$activeMenu = 'config';

$userDao = new UserDaoMysql($pdo);

require_once('./partials/header.php');
require_once('./partials/menu.php');
?>

<section class="feed mt-10">
  <h1>Configurações</h1>

  <?php if (empty($_SESSION['flash']) === false) : ?>
    <?= $_SESSION['flash'] ?>
    <?php $_SESSION['flash'] = '' ?>
  <?php endif; ?>

  <form method="POST" action="configuracoes_action.php" class="config-form" enctype="multipart/form-data">
    <label for="avatar">
      Novo avatar:<br>
      <input type="file" name="avatar" id="avatar">
      <br>
      <img class="mini" src="<?= $base ?>/media/avatars/<?= $userInfo->avatar ?>" alt="avatar">
    </label>
    <label for="cover">
      Nova capa:<br>
      <input type="file" name="cover" id="cover">
      <br>
      <img class="mini" src="<?= $base ?>/media/covers/<?= $userInfo->cover ?>" alt="cover">
    </label>

    <hr>

    <label for="name">
      Nome completo:*<br>
      <input type="text" name="name" id="name" value="<?= $userInfo->name ?>" minlength="2" maxlength="50" required>
    </label>

    <label for="email">
      E-mail:*<br>
      <input type="email" name="email" id="email" value="<?= $userInfo->email ?>" maxlength="64" required>
    </label>

    <label for="birthdate">
      Data de nascimento:*<br>
      <input type="text" name="birthdate" id="birthdate" value="<?= date("d/m/Y", strtotime($userInfo->birthdate)) ?>" required>
    </label>

    <label for="city">
      Cidade:<br>
      <input type="text" name="city" id="city" value="<?= $userInfo->city ?>" maxlength="50">
    </label>

    <label for="work">
      Trabalho:<br>
      <input type="text" name="work" id="work" value="<?= $userInfo->work ?>" maxlength="50">
    </label>

    <hr>
    <label for="last-password">
      Senha antiga:<br>
      <input type="password" name="last-password" id="last-password">
    </label>


    <label for="password">
      Nova senha:<br>
      <input type="password" name="password" id="password">
    </label>

    <label for="password-confirmation">
      Confirmar nova senha:<br>
      <input type="password" name="password-confirmation" id="password-confirmation">
    </label>

    *Campos obrigatórios<br>

    <br>

    <button class="button">Salvar</button>
  </form>
</section>

<script src="https://unpkg.com/imask"></script>
<script>
  IMask(
    document.getElementById('birthdate'), {
      mask: '00/00/0000'
    }
  );
</script>

<?php require_once('./partials/footer.php'); ?>