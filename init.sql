CREATE TABLE posts (
  id SERIAL PRIMARY KEY,
  public_id varchar(36) UNIQUE NOT NULL,
  id_user varchar(36) NOT NULL,
  type varchar(25) NOT NULL,
  created_at timestamp NOT NULL,
  body text NOT NULL
);

CREATE TABLE post_comments (
  id SERIAL PRIMARY KEY,
  id_post varchar(36) NOT NULL,
  id_user varchar(36) NOT NULL,
  created_at timestamp NOT NULL,
  body text NOT NULL
);

CREATE TABLE post_likes (
  id SERIAL PRIMARY KEY,
  id_post varchar(36) NOT NULL,
  id_user varchar(36) NOT NULL,
  created_at timestamp NOT NULL
);

CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  public_id varchar(36) UNIQUE NOT NULL,
  email varchar(50) UNIQUE NOT NULL,
  password varchar(64) NOT NULL,
  salt varchar(64) UNIQUE NOT NULL,
  name varchar(50) NOT NULL,
  birthdate date NOT NULL,
  city varchar(50) DEFAULT NULL,
  work varchar(50) DEFAULT NULL,
  avatar varchar(100) NOT NULL DEFAULT 'default.jpg',
  cover varchar(100) NOT NULL DEFAULT 'cover.jpg'
);

CREATE TABLE user_relations (
  id SERIAL PRIMARY KEY,
  user_from varchar(36) NOT NULL,
  user_to varchar(36) NOT NULL
);
