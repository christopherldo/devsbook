<?php

require_once('./models/PostLike.php');

class PostLikeDaoMysql implements PostLikeDAO
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function getLikeCount(string $idPost)
  {
    $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM post_likes WHERE id_post = :id_post");
    $sql->bindValue(':id_post', $idPost);
    $sql->execute();

    $data = $sql->fetch();

    return $data['c'];
  }

  public function isLiked(string $idPost, string $idUser)
  {
    $sql = $this->pdo->prepare(
      "SELECT * FROM post_likes WHERE id_post = :id_post AND id_user = :id_user"
    );
    $sql->bindValue('id_post', $idPost);
    $sql->bindValue('id_user', $idUser);
    $sql->execute();

    if ($sql->rowCount() > 0) {
      return true;
    }

    return false;
  }

  public function likeToggle(string $idPost, string $idUser)
  {
    if ($this->isLiked($idPost, $idUser)) {
      $sql = $this->pdo->prepare(
        "DELETE FROM post_likes WHERE id_post = :id_post AND id_user = :id_user"
      );
      $sql->bindValue(':id_post', $idPost);
      $sql->bindValue(':id_user', $idUser);
      $sql->execute();
    } else {
      $sql = $this->pdo->prepare(
        "INSERT INTO post_likes (
        id_post, id_user, created_at
        ) VALUES (
          :id_post, :id_user, :created_at
        )"
      );
      $sql->bindValue(':created_at', gmdate('Y-m-d H:i:s'));
    }

    $sql->bindValue(':id_post', $idPost);
    $sql->bindValue(':id_user', $idUser);
    $sql->execute();
  }

  public function deleteFromPost(string $idPost)
  {
    $sql = $this->pdo->prepare("DELETE FROM post_likes WHERE id_post = :id_post");
    $sql->bindValue(':id_post', $idPost);
    $sql->execute();
  }
}
