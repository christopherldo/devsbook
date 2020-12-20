<?php

require_once('./models/User.php');

class UserDaoMysql implements UserDAO
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  private function generateUser(array $array)
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

    return $user;
  }

  public function findById(string $publicId)
  {
    if (empty($publicId) === false) {
      $sql = $this->pdo->prepare("SELECT * FROM users WHERE public_id = :public_id");
      $sql->bindValue(':public_id', $publicId);
      $sql->execute();

      if ($sql->rowCount() > 0) {
        $data = $sql->fetch(PDO::FETCH_ASSOC);

        $user = $this->generateUser($data);

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
}
