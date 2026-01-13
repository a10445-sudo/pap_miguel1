-- create_db.sql
CREATE DATABASE IF NOT EXISTS `pap_app` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pap_app`;

CREATE TABLE IF NOT EXISTS `users` (
  `nrprocesso` INT UNSIGNED NOT NULL ,
  `name` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('funcionario','professor','administrador') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nrprocesso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
