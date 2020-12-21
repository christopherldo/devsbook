<?php

require_once('./partials/feed-item-script.php');

$actionPhrase = '';

switch ($item->type) {
  case 'text':
    $actionPhrase = 'fez um post';
    break;
  case 'photo':
    $actionPhrase = 'postou uma foto';
    break;
}

$createdAt = date('n/d/Y H:i:m', strtotime($item->createdAt));
?>

<div class="box feed-item" data-id="<?= $item->id ?>">
  <div class="box-body">
    <div class="feed-item-head row mt-20 m-width-20">
      <div class="feed-item-head-photo">
        <a href="<?= $base ?>/perfil.php?id=<?= $item->user->publicId ?>">
          <img src="<?= $base ?>/media/avatars/<?= $item->user->avatar ?>" />
        </a>
      </div>
      <div class="feed-item-head-info">
        <a href="<?= $base ?>/perfil.php?id=<?= $item->user->publicId ?>">
          <span class="fidi-name"><?= $item->user->name ?>
          </span></a>
        <span class="fidi-action"><?= $actionPhrase ?></span>
        <br />
        <span class="fidi-date"><?= $createdAt ?></span>
      </div>
      <div class="feed-item-head-btn">
        <img src="<?= $base ?>/assets/images/more.png" />
      </div>
    </div>
    <div class="feed-item-body mt-10 m-width-20">
      <?= str_replace('&#13;&#10;', '<br>', $item->body) ?>
    </div>
    <div class="feed-item-buttons row mt-20 m-width-20">
      <div class="like-btn <?= $item->liked ? 'on' : '' ?>"><?= $item->likeCount ?></div>
      <div class="msg-btn"><?= count($item->comments) ?></div>
    </div>
    <div class="feed-item-comments">

      <!-- <div class="fic-item row m-height-10 m-width-20">
        <div class="fic-item-photo">
          <a href=""><img src="media/avatars/avatar.jpg" /></a>
        </div>
        <div class="fic-item-info">
          <a href="">Bonieky Lacerda</a>
          Comentando no meu próprio post
        </div>
      </div> -->

      <div class="fic-answer row m-height-10 m-width-20">
        <div class="fic-item-photo">
          <img src="<?= $base ?>/media/avatars/<?= $userInfo->avatar ?>" />
        </div>
        <input type="text" class="fic-item-field" placeholder="Escreva um comentário" />
      </div>

    </div>
  </div>
</div>