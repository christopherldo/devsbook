<?php

class PostLike
{
  public int $id;
  public string $idPost;
  public string $idUser;
  public string $createdAt;
}

interface PostLikeDAO
{
  public function getLikeCount(string $idPost);
  public function isLiked(string $idPost, string $idUser);
  public function likeToggle(string $idPost, string $idUser);
}
