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
    $sql = $this->pdo->prepare("INSERT INTO user_relations (
      user_from, user_to
      ) VALUES (
      :user_from, :user_to
      )");
    $sql->bindValue(':user_from', $userRelation->userFrom);
    $sql->bindValue(':user_to', $userRelation->userTo);
    $sql->execute();
  }

  public function delete(UserRelation $userRelation)
  {
    $sql = $this->pdo->prepare(
      "DELETE FROM user_relations WHERE user_from = :user_from AND user_to = :user_to"
    );
    $sql->bindValue(':user_from', $userRelation->userFrom);
    $sql->bindValue(':user_to', $userRelation->userTo);
    $sql->execute();
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

  public function isFollowing(string $idOne, string $idTwo)
  {
    $sql = $this->pdo->prepare(
      "SELECT * FROM user_relations WHERE user_from = :user_from AND user_to = :user_to"
    );
    $sql->bindValue(':user_from', $idOne);
    $sql->bindValue(':user_to', $idTwo);
    $sql->execute();

    return $sql->rowCount() > 0 ? true : false;
  }
}
