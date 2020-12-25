<?php
require_once('./config.php');
require_once('./models/Auth.php');
require_once('./dao/PostDaoMysql.php');

$auth = new Auth($pdo, $base);
$userInfo = $auth->checkToken();
$activeMenu = 'friends';

$publicId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);

if ($publicId === null) {
  $publicId = $userInfo->publicId;
}

if ($publicId !== $userInfo->publicId) {
  $activeMenu = '';
}

$postDao = new PostDaoMysql($pdo);
$userDao = new UserDaoMysql($pdo);

$user = $userDao->findById($publicId, true);

if ($user === false) {
  header("Location: $base");
  exit;
}

$datefrom = new DateTime($user->birthdate);
$dateTo = new DateTime('today');

$user->ageYears = $datefrom->diff($dateTo)->y;

require_once('./partials/header.php');
require_once('./partials/menu.php');
?>

<section class="feed">
  <div class="row">
    <div class="box flex-1 border-top-flat">
      <div class="box-body">
        <div class="profile-cover" style="background-image: url('<?= $base ?>/media/covers/<?= $user->cover ?>');">
        </div>
        <div class="profile-info m-20 row">
          <div class="profile-info-avatar">
            <img src="<?= $base ?>/media/avatars/<?= $user->avatar ?>" />
          </div>
          <div class="profile-info-name">
            <div class="profile-info-name-text"><?= $user->name ?></div>
            <?php if (empty($user->city) === false) : ?>
              <div class="profile-info-location"><?= $user->city ?></div>
            <?php endif; ?>
          </div>
          <div class="profile-info-data row">
            <div class="profile-info-item m-width-20">
              <div class="profile-info-item-n"><?= count($user->followers) ?></div>
              <div class="profile-info-item-s">Seguidores</div>
            </div>
            <div class="profile-info-item m-width-20">
              <div class="profile-info-item-n"><?= count($user->following) ?></div>
              <div class="profile-info-item-s">Seguindo</div>
            </div>
            <div class="profile-info-item m-width-20">
              <div class="profile-info-item-n"><?= count($user->photos) ?></div>
              <div class="profile-info-item-s">Fotos</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="column">
      <div class="box">
        <div class="box-body">
          <div class="tabs">
            <?php if (count($user->followers) > 0) : ?>
              <div class="tab-item" data-for="followers">
                Seguidores
              </div>
            <?php endif ?>
            <?php if (count($user->following) > 0) : ?>
              <div class="tab-item active" data-for="following">
                Seguindo
              </div>
            <?php endif ?>
          </div>
          <div class="tab-content">
            <div class="tab-body" data-item="followers">
              <div class="full-friend-list">
                <?php foreach ($user->followers as $item) : ?>
                  <?php $friendFirstName = explode(' ', $item->name)[0]; ?>
                  <div class="friend-icon">
                    <a href="<?= $base ?>/perfil.php?id=<?= $item->publicId ?>">
                      <div class="friend-icon-avatar">
                        <img src="<?= $base ?>/media/avatars/<?= $item->avatar ?>" />
                      </div>
                      <div class="friend-icon-name">
                        <?= $friendFirstName ?>
                      </div>
                    </a>
                  </div>
                <?php endforeach ?>
              </div>
            </div>
            <div class="tab-body" data-item="following">
              <div class="full-friend-list">
                <?php foreach ($user->following as $item) : ?>
                  <?php $friendFirstName = explode(' ', $item->name)[0]; ?>
                  <div class="friend-icon">
                    <a href="<?= $base ?>/perfil.php?id=<?= $item->publicId ?>">
                      <div class="friend-icon-avatar">
                        <img src="<?= $base ?>/media/avatars/<?= $item->avatar ?>" />
                      </div>
                      <div class="friend-icon-name">
                        <?= $friendFirstName ?>
                      </div>
                    </a>
                  </div>
                <?php endforeach ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once('./partials/footer.php'); ?>