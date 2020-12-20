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
  public function getRelationsFrom(string $publicId);
}
