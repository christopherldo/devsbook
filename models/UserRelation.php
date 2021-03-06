<?php

class UserRelation
{
  public int $id;
  public string $userFrom;
  public string $userTo;
}

interface UserRelationDAO
{
  public function insert(UserRelation $userRelation);
  public function delete(UserRelation $userRelation);
  public function getFollowing(string $publicId);
  public function getFollowers(string $publicId);
  public function isFollowing(string $me, string $id);
}
