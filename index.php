<?php
require_once('./config.php');
require_once('./models/Auth.php');
require_once('./dao/PostDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();

$page = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT);

if ($page === null || $page < 1) {
  $page = 1;
}

$activeMenu = 'home';

$postDao = new PostDaoMysql($pdo);

$info = $postDao->getHomeFeed($userInfo->publicId, $page);

$feed = $info['feed'] ?? null;
$pages = $info['pages'];
$currentPage = $info['currentPage'];

require_once('./partials/header.php');
require_once('./partials/menu.php');
?>

<section class="feed mt-10">
  <div class="row">
    <div class="column pr-5">

      <?php require_once('./partials/feed-editor.php') ?>
      <?php if ($feed) : ?>
        <?php foreach ($feed as $item) : ?>
          <?php require('./partials/feed-item.php') ?>
        <?php endforeach ?>
      <?php endif ?>

      <?php if ($pages > 1) : ?>
        <div class="feed-pagination">
          <?php for ($q = 1; $q <= $pages; $q++) : ?>
            <a class="<?= $q == $currentPage ? 'active' : '' ?>" href="<?= $base ?><?= $q === 1 ? '' : '?p=' . $q ?>"><?= $q ?></a>
          <?php endfor ?>
        </div>
      <?php endif ?>

    </div>
    <div class="column side pl-5">
      <div class="box banners">
        <div class="box-header">
          <div class="box-header-text">Dica:</div>
          <div class="box-header-buttons">
          </div>
        </div>
        <div class="box-body">
          <a href="<?= $base ?>/configuracoes.php" style="color: #000; text-decoration: none;">Você pode trocar sua foto de capa e perfil clicando aqui</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once('./partials/footer.php'); ?>

<script>
  let updateDate = () => {
    let datesList = document.querySelectorAll('.fidi-date');
    for (let date of datesList) {
      dateInfo = date.innerHTML + ' UTC';
      let realDate = new Date(dateInfo);

      let day = realDate.getDate();

      if (day < 10) {
        day = '0' + day;
      };

      let month = realDate.getMonth() + 1;

      if (month < 10) {
        month = '0' + month;
      };

      let year = realDate.getFullYear();
      let hours = realDate.getHours();

      if (hours < 10) {
        hours = '0' + hours;
      };

      let minutes = realDate.getMinutes();

      if (minutes < 10) {
        minutes = '0' + minutes;
      };

      let newDateString = `${day}/${month}/${year} ${hours}:${minutes}`;

      date.innerHTML = newDateString;
    }
  };

  updateDate();
</script>