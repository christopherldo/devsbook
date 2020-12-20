<?php

class Post
{
  public int $id;
  public string $idUser;
  public string $type;
  public string $createdAt;
  public string $body;
}

interface PostDAO
{
  public function insert(Post $post);
}
