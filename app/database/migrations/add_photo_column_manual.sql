-- Adicionar coluna photo à tabela users se não existir
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `photo` VARCHAR(255) NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `fantasy_name` VARCHAR(255) NULL AFTER `name`;

