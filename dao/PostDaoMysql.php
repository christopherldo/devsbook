<?php

require_once('./models/Post.php');

class PostDaoMysql implements PostDAO
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function insert(Post $post)
  {
    $sql = $this->pdo->prepare(
      "INSERT INTO posts (
        id_user, type, created_at, body
      ) VALUES (
        :id_user, :type, :created_at, :body
      )"
    );
    $sql->bindValue(':id_user', $post->idUser);
    $sql->bindValue(':type', $post->type);
    $sql->bindValue(':created_at', $post->createdAt);
    $sql->bindValue(':body', $post->body);
    $sql->execute();
  }
}
