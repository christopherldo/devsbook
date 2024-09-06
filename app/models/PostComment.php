<?php

class PostComment
{
  public int $id;
  public string $idPost;
  public string $idUser;
  public string $createdAt;
  public string $body;
}

interface PostCommentDAO
{
  public function addComment(PostComment $postComment);
  public function getComments(string $idPost);
  public function deleteFromPost(string $idPost);
}
