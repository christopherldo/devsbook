<?php

require_once('./models/User.php');

class UserDaoMysql implements UserDAO
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  private function generateUser(string $array)
  {
    $user = new User();

    $user->publicId = $array['publicId'] ?? '';
    $user->email = $array['email'] ?? '';
    $user->name = $array['name'] ?? '';
    $user->birthdate = $array['birthdate'] ?? '';
    $user->city = $array['city'] ?? '';
    $user->work = $array['work'] ?? '';
    $user->avatar = $array['avatar'] ?? '';
    $user->cover = $array['cover'] ?? '';

    return $user;
  }

  public function findByToken(string $publicId)
  {
    if (empty($publicId) === false) {
      $sql = $this->pdo->prepare("SELECT * FROM users WHERE public_id = :public_id");
      $sql->bindValue('public_id', $publicId);
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
      $sql->bindValue('email', $email);
      $sql->execute();

      if ($sql->rowCount() > 0) {
        $data = $sql->fetch(PDO::FETCH_ASSOC);

        $user = $this->generateUser($data);

        return $user;
      }
    }
    return false;
  }
};
