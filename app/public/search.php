<?php
require_once('../config.php');
require_once('../models/Auth.php');
require_once('../dao/UserDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$activeMenu = '';

$userDao = new UserDaoMysql($pdo);

$searchTerm = filter_input(INPUT_GET, 's', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($searchTerm)) {
  header("Location: $base");
  exit;
}

$userList = $userDao->findByName($searchTerm);

require_once('../partials/header.php');
require_once('../partials/menu.php');
?>

<section class="feed mt-10">
  <div class="row">
    <div class="column pr-5">
      <div class="full-friend-list">
        <?php foreach ($userList as $userItem) : ?>
          <?php $friendFirstName = explode(' ', $userItem->name)[0]; ?>
          <div class="friend-icon">
            <a href="<?= $base ?>/perfil?id=<?= $userItem->publicId ?>">
              <div class="friend-icon-avatar">
                <img src="<?= $base ?>/media/avatars/<?= $userItem->avatar ?>" />
              </div>
              <div class="friend-icon-name">
                <?= $friendFirstName ?>
              </div>
            </a>
          </div>
        <?php endforeach ?>
      </div>
    </div>
    <div class="column side pl-5">
      <div class="box banners">
        <div class="box-header">
          <div class="box-header-text">Patrocínios</div>
          <div class="box-header-buttons">
          </div>
        </div>
        <div class="box-body">
          <a href=""><img src="<?=$base?>/assets/images/php.jpg" /></a>
          <a href=""><img src="<?=$base?>/assets/images/laravel.jpg" /></a>
        </div>
      </div>
      <div class="box">
        <div class="box-body m-10">
          Criado com ❤️ por <a href="https://github.com/christopherldo" target="_blank" rel="noreferrer">@christopherldo</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once('../partials/footer.php'); ?>