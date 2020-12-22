<?php

class Post
{
  public string $publicId;
  public string $idUser;
  public string $type;
  public string $createdAt;
  public string $body;
}

interface PostDAO
{
  public function insert(Post $post);
  public function delete(string $postId, string $userId);
  public function getHomeFeed(string $publicId);
  public function getUserFeed(string $publicId);
  public function getPhotosFrom(string $publicId);
  public function findById(string $publicId);
  public function generateUuid();
}
