<?php

require_once('./models/Post.php');
require_once('./dao/UserRelationDaoMysql.php');
require_once('./dao/UserDaoMysql.php');
require_once('./dao/PostLikeDaoMysql.php');
require_once('./dao/PostCommentDaoMysql.php');

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
        public_id, id_user, type, created_at, body
      ) VALUES (
        :public_id, :id_user, :type, :created_at, :body
      )"
    );
    $sql->bindValue(':public_id', $post->publicId);
    $sql->bindValue(':id_user', $post->idUser);
    $sql->bindValue(':type', $post->type);
    $sql->bindValue(':created_at', $post->createdAt);
    $sql->bindValue(':body', $post->body);
    $sql->execute();
  }

  public function delete(string $postId, string $userId)
  {
    $postLikeDao = new PostLikeDaoMysql($this->pdo);
    $postCommentDao = new PostCommentDaoMysql($this->pdo);

    $post = $this->findById($postId);

    if ($post) {
      $sql = $this->pdo->prepare(
        "DELETE FROM posts WHERE public_id = :public_id AND id_user = :id_user"
      );
      $sql->bindValue(':public_id', $postId);
      $sql->bindValue(':id_user', $userId);
      $sql->execute();

      $postLikeDao->deleteFromPost($postId);
      $postCommentDao->deleteFromPost($postId);

      if ($post[0]->type === 'photo') {
        unlink('./media/uploads/' . $post[0]->body);
      }
    }
  }

  public function getHomeFeed(string $publicId)
  {
    $array = [];
    $perPage = 10;

    $page = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT);

    if ($page === null || $page < 1) {
      $page = 1;
    }

    $offset = ($page - 1) * $perPage;

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

    $sqlString .= ") ORDER BY created_at DESC LIMIT $offset,$perPage";

    $sql = $this->pdo->query($sqlString);

    if ($sql && $sql->rowCount() > 0) {
      $data = $sql->fetchAll(PDO::FETCH_ASSOC);

      $array['feed'] = $this->postListToObject($data, $publicId);
    }

    $sqlString = "SELECT COUNT(*) as c FROM posts WHERE id_user IN (";
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

    $sqlString .= ')';

    $sql = $this->pdo->query($sqlString);
    $totalData = $sql->fetch();
    $total = $totalData['c'];

    $array['pages'] = ceil($total / $perPage);
    $array['currentPage'] = $page;

    return $array;
  }

  public function getUserFeed(string $publicId)
  {
    $array = ['feed' => []];
    $perPage = 10;

    $page = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT);

    if ($page === null || $page < 1) {
      $page = 1;
    }

    $offset = ($page - 1) * $perPage;

    $sql = $this->pdo->prepare(
      "SELECT * FROM posts WHERE id_user = :id_user ORDER BY created_at DESC
      LIMIT $offset,$perPage"
    );
    $sql->bindValue(':id_user', $publicId);
    $sql->execute();

    if ($sql && $sql->rowCount() > 0) {
      $data = $sql->fetchAll(PDO::FETCH_ASSOC);

      $array['feed'] = $this->postListToObject($data, $publicId);
    }

    $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM posts WHERE id_user = :id_user");
    $sql->bindValue(':id_user', $publicId);
    $sql->execute();

    $totalData = $sql->fetch();
    $total = $totalData['c'];

    $array['pages'] = ceil($total / $perPage);
    $array['currentPage'] = $page;

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

    if ($sql->rowCount() > 0) {
      $data = $sql->fetchAll(PDO::FETCH_ASSOC);

      $array = $this->postListToObject($data, $publicId);
    }

    return $array;
  }

  private function postListToObject(array $postList, string $publicId)
  {
    $posts = [];
    $userDao = new UserDaoMysql($this->pdo);
    $postLikeDao = new PostLikeDaoMysql($this->pdo);
    $postCommentDaoMysql = new PostCommentDaoMysql($this->pdo);

    foreach ($postList as $postItem) {
      $newPost = new Post();
      $newPost->publicId = $postItem['public_id'];
      $newPost->type = $postItem['type'];
      $newPost->createdAt = $postItem['created_at'];
      $newPost->body = $postItem['body'];
      $newPost->mine = false;

      if ($postItem['id_user'] === $publicId) {
        $newPost->mine = true;
      }

      $newPost->user = $userDao->findById($postItem['id_user']);

      $newPost->likeCount = $postLikeDao->getLikeCount($newPost->publicId);
      $newPost->liked = $postLikeDao->isLiked($newPost->publicId, $publicId);

      $newPost->comments = $postCommentDaoMysql->getComments($newPost->publicId);

      $posts[] = $newPost;
    }

    return $posts;
  }

  public function findById(string $publicId)
  {
    if (empty($publicId) === false) {
      $sql = $this->pdo->prepare("SELECT * FROM posts WHERE public_id = :public_id");
      $sql->bindValue(':public_id', $publicId);
      $sql->execute();

      if ($sql->rowCount() > 0) {
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);

        $array = $this->postListToObject($data, $publicId);

        return $array;
      }
    }
    return false;
  }

  public function generateUuid()
  {
    do {
      $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
      );
    } while ($this->findById($uuid));

    return $uuid;
  }
}
