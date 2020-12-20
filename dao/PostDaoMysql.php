<?php

require_once('./models/Post.php');
require_once('./dao/UserRelationDaoMysql.php');
require_once('./dao/UserDaoMysql.php');

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

  public function getHomeFeed(string $publicId)
  {
    $array = [];

    $userRelationDao = new UserRelationDaoMysql($this->pdo);
    $userList = $userRelationDao->getFollowing($publicId);

    $userList[] = $publicId;

    $sqlString = "SELECT * FROM posts WHERE id_user IN (";
    $counter = 1;
    $userListLenght = count($userList);

    foreach ($userList as $user) {
      if ($counter < $userListLenght) {
        $sqlString .= "'$user', ";
      } else {
        $sqlString .= "'$user'";
      }
      $counter++;
    }

    $sqlString .= ") ORDER BY created_at DESC";

    $sql = $this->pdo->query($sqlString);

    if ($sql && $sql->rowCount() > 0) {
      $data = $sql->fetchAll(PDO::FETCH_ASSOC);

      $array = $this->postListToObject($data, $publicId);
    }

    return $array;
  }

  public function getUserFeed(string $publicId)
  {
    $array = [];

    $sql = $this->pdo->prepare(
      "SELECT * FROM posts WHERE id_user = :id_user ORDER BY created_at DESC"
    );
    $sql->bindValue(':id_user', $publicId);
    $sql->execute();

    if ($sql && $sql->rowCount() > 0) {
      $data = $sql->fetchAll(PDO::FETCH_ASSOC);

      $array = $this->postListToObject($data, $publicId);
    }

    return $array;
  }

  public function getPhotosFrom(string $publicId)
  {
    $array = [];

    $sql = $this->pdo->prepare(
      "SELECT * FROM posts where id_user = :id_user AND type = 'photo'
      ORDER BY created_at DESC"
    );
    $sql->bindValue(':id_user', $publicId);
    $sql->execute();

    if($sql->rowCount() > 0){
      $data = $sql->fetchAll(PDO::FETCH_ASSOC);

      $array = $this->postListToObject($data, $publicId);
    }

    return $array;
  }

  private function postListToObject(array $postList, string $publicId)
  {
    $posts = [];
    $userDao = new UserDaoMysql($this->pdo);

    foreach ($postList as $postItem) {
      $newPost = new Post();
      $newPost->id = $postItem['id'];
      $newPost->type = $postItem['type'];
      $newPost->createdAt = $postItem['created_at'];
      $newPost->body = $postItem['body'];
      $newPost->mine = false;

      if ($postItem['id_user'] === $publicId) {
        $newPost->mine = true;
      }

      $newPost->user = $userDao->findById($postItem['id_user']);

      $newPost->likeCount = 0;
      $newPost->liked = false;

      $newPost->comments = [];

      $posts[] = $newPost;
    }

    return $posts;
  }
}
