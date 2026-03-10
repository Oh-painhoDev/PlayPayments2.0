-- Adiciona campos para melhorar o rastreamento de transações
ALTER TABLE `vendas` 
ADD COLUMN `gateway_transaction_id` VARCHAR(255) NULL AFTER `adquirente_id`,
ADD COLUMN `external_ref` VARCHAR(255) NULL AFTER `gateway_transaction_id`,
ADD INDEX `idx_gateway_transaction_id` (`gateway_transaction_id`),
ADD INDEX `idx_external_ref` (`external_ref`);

-- Adiciona campo para salvar dados adicionais do gateway (JSON)
ALTER TABLE `vendas`
ADD COLUMN `gateway_data` TEXT NULL AFTER `external_ref`;

-- Adiciona campos úteis na tabela transacoes
ALTER TABLE `transacoes`
ADD COLUMN `gateway_transaction_id` VARCHAR(255) NULL AFTER `adquirente`,
ADD COLUMN `external_ref` VARCHAR(255) NULL AFTER `gateway_transaction_id`,
ADD COLUMN `venda_id` BIGINT(20) UNSIGNED NULL AFTER `external_ref`,
ADD INDEX `idx_gateway_transaction_id` (`gateway_transaction_id`),
ADD INDEX `idx_venda_id` (`venda_id`),
ADD CONSTRAINT `fk_transacoes_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE SET NULL;

