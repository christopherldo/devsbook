<?php

class User
{
  private int $id;
  public string $publicId;
  public string $email;
  public string $password;
  public string $name;
  public string $birthdate;
  public string $city;
  public string $work;
  public string $avatar;
  public string $cover;
}

interface UserDAO
{
  public function findByToken(string $publicId);
  public function findByEmail(string $email);
}
