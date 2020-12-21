<?php

class PostLike
{
  public int $id;
  public int $idPost;
  public string $idUser;
  public string $createdAt;
}

interface PostLikeDAO
{
  public function getLikeCount(int $idPost);
  public function isLiked(int $idPost, string $idUser);
  public function likeToggle(int $idPost, string $idUser);
}
