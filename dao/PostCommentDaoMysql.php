<?php

require_once('./models/PostComment.php');
require_once('./dao/UserDaoMysql.php');

class PostCommentDaoMysql implements PostCommentDAO
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function addComment(PostComment $postComment)
  {
    $sql = $this->pdo->prepare("INSERT INTO post_comments (
        id_post, id_user, body, created_at
      ) VALUES (
        :id_post, :id_user, :body, :created_at
      )");
    $sql->bindValue(':id_post', $postComment->idPost);
    $sql->bindValue(':id_user', $postComment->idUser);
    $sql->bindValue(':body', $postComment->body);
    $sql->bindValue(':created_at', $postComment->createdAt);
    $sql->execute();
  }

  public function getComments(string $idPost)
  {
    $array = [];

    $sql = $this->pdo->prepare("SELECT * FROM post_comments WHERE id_post = :id_post");
    $sql->bindValue(':id_post', $idPost);
    $sql->execute();

    if ($sql->rowCount() > 0) {
      $data = $sql->fetchAll(PDO::FETCH_ASSOC);

      $userDao = new UserDaoMysql($this->pdo);

      foreach ($data as $item) {
        $commentItem = new PostComment();
        $commentItem->id = $item['id'];
        $commentItem->idPost = $item['id_post'];
        $commentItem->idUser = $item['id_user'];
        $commentItem->body = $item['body'];
        $commentItem->createdAt = $item['created_at'];
        $commentItem->user = $userDao->findById($commentItem->idUser);

        $array[] = $commentItem;
      }
    }

    return $array;
  }
}
