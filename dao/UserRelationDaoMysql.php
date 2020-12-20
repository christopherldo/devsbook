<?php

require_once('./models/UserRelation.php');

class UserRelationDaoMysql implements UserRelationDAO
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function insert(UserRelation $userRelation)
  {
  }

  public function getFollowing(string $publicId)
  {
    $users = [];

    $sql = $this->pdo->prepare(
      "SELECT user_to FROM user_relations WHERE user_from = :user_from"
    );
    $sql->bindValue(':user_from', $publicId);
    $sql->execute();

    if ($sql->rowCount() > 0) {
      $data = $sql->fetchAll();

      foreach ($data as $item) {
        $users[] = $item['user_to'];
      }
    }

    return $users;
  }

  public function getFollowers(string $publicId)
  {
    $users = [];

    $sql = $this->pdo->prepare(
      "SELECT user_from FROM user_relations WHERE user_to = :user_to"
    );
    $sql->bindValue(':user_to', $publicId);
    $sql->execute();

    if ($sql->rowCount() > 0) {
      $data = $sql->fetchAll();

      foreach ($data as $item) {
        $users[] = $item['user_from'];
      }
    }

    return $users;
  }
}
