-- Script para criar a base de dados e as tabelas usadas pela aplicação
CREATE DATABASE IF NOT EXISTS `pap_app` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pap_app`;

CREATE TABLE IF NOT EXISTS `users` (
  `nrprocesso` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('funcionario','professor','administrador') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nrprocesso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `description` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `requester_id` INT NOT NULL,
  `status` VARCHAR(40) NOT NULL DEFAULT 'pendente',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de salas disponíveis (administradores criam)
CREATE TABLE IF NOT EXISTS `salas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `capacidade` INT DEFAULT NULL,
  `localizacao` VARCHAR(255) DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de horários para salas. Pode ser recorrente por dia da semana ou uma data específica.
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sala_id` INT NOT NULL,
  `dia_semana` ENUM('segunda','terca','quarta','quinta','sexta','sabado','domingo') DEFAULT NULL,
  `data_especifica` DATE DEFAULT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fim` TIME NOT NULL,
  `disponivel` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pedidos de reserva de sala efetuados por professores
CREATE TABLE IF NOT EXISTS `room_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sala_id` INT NOT NULL,
  `horario_id` INT NOT NULL,
  `requester_id` INT NOT NULL,
  `status` ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`horario_id`) REFERENCES `horarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
