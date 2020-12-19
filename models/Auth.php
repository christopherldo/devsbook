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

  public function __construct(PDO $pdo, string $base)
  {
    $this->pdo = $pdo;
    $this->base = $base;
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
            $userDao = new UserDaoMysql($this->pdo);

            $user = $userDao->findByToken($publicId);

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
    $userDao = new UserDaoMysql($this->pdo);

    $user = $userDao->findByEmail($email);

    if ($user) {
      $salt = $user->salt;
      $hash = hash('sha256', $password . $salt);

      if ($hash === $user->password) {
        $token = $this->generateToken($user->public_id);

        $_SESSION['token'] = $token;

        $user->token = $token;

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
}
