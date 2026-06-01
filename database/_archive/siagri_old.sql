CREATE DATABASE siagri;

USE siagri;

CREATE TABLE users (
  id INT(100) PRIMARY KEY,
  username VARCHAR(100),
  email VARCHAR(100),
  password VARCHAR(255)
);