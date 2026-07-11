-- Script para criar a base de dados e as tabelas usadas pela aplicação
CREATE DATABASE IF NOT EXISTS `pap_app` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pap_app`;

CREATE TABLE IF NOT EXISTS `utilizadores` (
  `numero_processo` INT UNSIGNED NOT NULL,
  `nome` VARCHAR(150) NOT NULL,
  `palavra_passe` VARCHAR(255) NOT NULL,
  `tipo` ENUM('funcionario','professor','administrador') NOT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`numero_processo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `produtos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(255) NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 0,
  `descricao` TEXT,
  `devolvivel` TINYINT(1) NOT NULL DEFAULT 0,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `produto_id` INT DEFAULT NULL,
  `nome_produto` VARCHAR(255) NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  `pedido_por` INT NOT NULL,
  `estado` VARCHAR(40) NOT NULL DEFAULT 'pendente',
  `devolucao_obrigatoria` TINYINT(1) NOT NULL DEFAULT 0,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de salas disponíveis (administradores criam)
CREATE TABLE IF NOT EXISTS `salas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `capacidade` INT DEFAULT NULL,
  `localizacao` VARCHAR(255) DEFAULT NULL,
  `criado_por` INT DEFAULT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de horários para salas. Pode ser recorrente por dia da semana ou uma data específica.
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sala_id` INT NOT NULL,
  `dia_semana` ENUM('segunda','terca','quarta','quinta','sexta') DEFAULT NULL,
  `data_especifica` DATE DEFAULT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fim` TIME NOT NULL,
  `disponivel` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_por` INT DEFAULT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pedidos de reserva de sala efetuados por professores
CREATE TABLE IF NOT EXISTS `requisicao_sala` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sala_id` INT NOT NULL,
  `horario_id` INT NOT NULL,
  `pedido_por` INT NOT NULL,
  `estado` ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`horario_id`) REFERENCES `horarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
