<?php

require_once('../models/User.php');
require_once('../dao/UserRelationDaoMysql.php');

class UserDaoMysql implements UserDAO
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  private function generateUser(array $array, bool $full = false)
  {
    $user = new User();

    $user->publicId = $array['public_id'] ?? '';
    $user->email = $array['email'] ?? '';
    $user->password = $array['password'] ?? '';
    $user->salt = $array['salt'] ?? '';
    $user->name = $array['name'] ?? '';
    $user->birthdate = $array['birthdate'] ?? '';
    $user->city = $array['city'] ?? '';
    $user->work = $array['work'] ?? '';
    $user->avatar = $array['avatar'] ?? '';
    $user->cover = $array['cover'] ?? '';

    if ($full) {
      $userRelationDaoMysql = new UserRelationDaoMysql($this->pdo);
      $postDaoMysql = new PostDaoMysql($this->pdo);

      $user->followers = $userRelationDaoMysql->getFollowers($user->publicId);
      foreach ($user->followers as $key => $followerId) {
        $newUser = $this->findById($followerId);
        $user->followers[$key] = $newUser;
      }

      $user->following = $userRelationDaoMysql->getFollowing($user->publicId);
      foreach ($user->following as $key => $followerId) {
        $newUser = $this->findById($followerId);
        $user->following[$key] = $newUser;
      }

      $user->photos = $postDaoMysql->getPhotosFrom($user->publicId);
    }

    return $user;
  }

  public function findById(string $publicId, bool $full = false)
  {
    if (empty($publicId) === false) {
      $sql = $this->pdo->prepare("SELECT * FROM users WHERE public_id = :public_id");
      $sql->bindValue(':public_id', $publicId);
      $sql->execute();

      if ($sql->rowCount() > 0) {
        $data = $sql->fetch(PDO::FETCH_ASSOC);

        $user = $this->generateUser($data, $full);

        return $user;
      }
    }
    return false;
  }

  public function findByEmail(string $email)
  {
    if (empty($email) === false) {
      $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
      $sql->bindValue(':email', $email);
      $sql->execute();

      if ($sql->rowCount() > 0) {
        $data = $sql->fetch(PDO::FETCH_ASSOC);

        $user = $this->generateUser($data);

        return $user;
      }
    }
    return false;
  }

  public function findBySalt(string $salt)
  {
    if (empty($salt) === false) {
      $sql = $this->pdo->prepare("SELECT * FROM users WHERE salt = :salt");
      $sql->bindValue(':salt', $salt);
      $sql->execute();

      return $sql->rowCount() > 0 ? true : false;
    }
  }

  public function findByName(string $name)
  {
    $array = [];

    if (empty($name) === false) {
      $sql = $this->pdo->prepare("SELECT * FROM users WHERE name LIKE :name");
      $sql->bindValue(':name', '%' . $name . '%');
      $sql->execute();

      if ($sql->rowCount() > 0) {
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $item) {
          $array[] = $this->generateUser($item);
        }
      }
    }
    return $array;
  }

  public function insert(User $user)
  {
    $sql = $this->pdo->prepare(
      "INSERT INTO users (
        public_id, email, password, salt, name, birthdate
      ) VALUES (
        :public_id, :email, :password, :salt, :name, :birthdate
      )"
    );
    $sql->bindValue(':public_id', $user->publicId);
    $sql->bindValue(':email', $user->email);
    $sql->bindValue(':password', $user->password);
    $sql->bindValue(':salt', $user->salt);
    $sql->bindValue(':name', $user->name);
    $sql->bindValue(':birthdate', $user->birthdate);
    $sql->execute();
  }

  public function update(User $user)
  {
    $sql = $this->pdo->prepare(
      "UPDATE users SET email = :email, password = :password, name = :name,
      birthdate = :birthdate, city = :city, work = :work, avatar = :avatar, cover = :cover
      WHERE public_id = :public_id"
    );
    $sql->bindValue(':email', $user->email);
    $sql->bindValue(':password', $user->password);
    $sql->bindValue(':name', $user->name);
    $sql->bindValue(':birthdate', $user->birthdate);
    $sql->bindValue(':city', $user->city);
    $sql->bindValue(':work', $user->work);
    $sql->bindValue(':avatar', $user->avatar);
    $sql->bindValue(':cover', $user->cover);
    $sql->bindValue(':public_id', $user->publicId);
    $sql->execute();
  }
}
