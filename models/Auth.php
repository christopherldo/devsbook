<?php

require_once('./dao/UserDaoMysql.php');

class Auth
{
  const TOKEN_EXPIRED = 0;
  const TOKEN_VALID = 1;
  const TOKEN_INVALID_SIGNATURE = 2;

  protected $secret = 'zcA6Jaxh0wn94SJF5$IW4l9Fu4qPicG%qmTeJmC$8%Ia9d7QyC$EYEab9#j3sKkO';

  private PDO $pdo;
  private string $base;
  private UserDaoMysql $dao;

  public function __construct(PDO $pdo, string $base)
  {
    $this->pdo = $pdo;
    $this->base = $base;
    $this->dao = new UserDaoMysql($this->pdo);
  }

  public function checkToken()
  {
    if (isset($_SESSION['token'])) {
      $token = $_SESSION['token'];

      $tokenParts = explode('.', $token);

      if (count($tokenParts) === 3) {
        if ($this->validateToken($token) === self::TOKEN_VALID) {
          $payload = json_decode($this->getPayload($token));

          $publicId = $payload->publicId ?? null;

          if ($publicId) {
            $user = $this->dao->findById($publicId);

            if ($user) {
              return $user;
            }
          }
        }
      }
    }

    header("Location: $this->base/login.php");
    exit;
  }

  public function validateToken(string $token)
  {
    $tokenParts = explode('.', $token);

    $tokenHeader = base64_decode($tokenParts[0]);
    $tokenPayload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];
    $expiration = json_decode($tokenPayload)->exp ?? null;
    $publicId = json_decode($tokenPayload)->publicId ?? null;

    if ($expiration && $publicId) {
      if ($this->isTokenExpired(json_decode($tokenPayload)->exp)) {
        return self::TOKEN_EXPIRED;
      }
    } else {
      return self::TOKEN_EXPIRED;
    }

    $base64UrlHeader = $this->base64UrlEncode($tokenHeader);
    $base64UrlPayload = $this->base64UrlEncode($tokenPayload);
    $signature = $this->getSignature($base64UrlHeader, $base64UrlPayload);
    $base64UrlSignature = $this->base64UrlEncode($signature);

    if (($base64UrlSignature === $signatureProvided) === false) {
      return self::TOKEN_INVALID_SIGNATURE;
    }

    return self::TOKEN_VALID;
  }

  protected function isTokenExpired(int $expireTime = 0)
  {
    $now = (new DateTime('now'))->getTimestamp();
    return ($expireTime - $now < 0);
  }

  protected function getSecret()
  {
    return $this->secret;
  }

  protected function getSignature(string $header, string $payload)
  {
    return hash_hmac(
      'sha256',
      "{$header}.{$payload}",
      $this->getSecret(),
      true
    );
  }

  public function getPayload(string $token)
  {
    $tokenParts = explode('.', $token);
    $tokenPayload = base64_decode($tokenParts[1]);

    return $tokenPayload;
  }

  public function base64UrlEncode(string $text)
  {
    return str_replace(
      ['+', '/', '='],
      ['-', '_', ''],
      base64_encode($text)
    );
  }

  public function validateLogin(string $email, string $password)
  {
    $user = $this->dao->findByEmail($email);

    if ($user) {
      $salt = $user->salt;
      $hash = hash('sha256', $password . $salt);

      if ($hash === $user->password) {
        $token = $this->generateToken($user->publicId);

        $_SESSION['token'] = $token;

        return true;
      }
    }

    return false;
  }

  private function generateToken(string $publicId)
  {
    $payload = [
      'publicId' => $publicId,
      'exp' => ((new DateTime())->modify('+1 week')->getTimestamp())
    ];

    $base64UrlHeader = $this->base64UrlEncode($this->setHeader());
    $base64UrlPayload = $this->base64UrlEncode($this->setPayload($payload));
    $base64UrlSignature = $this->base64UrlEncode($this->getSignature($base64UrlHeader, $base64UrlPayload));

    $token = "{$base64UrlHeader}.{$base64UrlPayload}.{$base64UrlSignature}";
    return $token;
  }

  protected function setHeader()
  {
    return json_encode([
      'typ' => 'JWT',
      'alg' => 'HS256'
    ]);
  }

  protected function setPayload(array $payload)
  {
    return json_encode($payload);
  }

  public function emailExists(string $email)
  {
    return $this->dao->findByEmail($email) ? true : false;
  }

  public function registerUser(string $name, string $email, string $password, string $birthdate)
  {
    do {
      $salt = $this->generateSalt();
    } while ($this->dao->findBySalt($salt));

    do {
      $publicId = $this->generateUuid();
    } while ($this->dao->findById($publicId));

    $hash = hash('sha256', $password . $salt);

    $newUser = new User();
    $newUser->publicId = $publicId;
    $newUser->email = $email;
    $newUser->password = $hash;
    $newUser->salt = $salt;
    $newUser->name = $name;
    $newUser->birthdate = $birthdate;

    $this->dao->insert($newUser);

    $token = $this->generateToken($publicId);

    $_SESSION['token'] = $token;
  }

  private function generateSalt(int $length = 64)
  {
    $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . 'abcdefghijklmnopqrstuvwxyz' .
      '0123456789' . '`-=~!@#$%^&*()_+,./<>?;:[]{}\|';
    $salt = '';

    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
      $salt .= $chars[random_int(0, $max)];
    }

    return $salt;
  }

  private function generateUuid()
  {
    return sprintf(
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
  }
}
