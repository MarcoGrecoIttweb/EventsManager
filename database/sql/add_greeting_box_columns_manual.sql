-- Box benvenuto evento: eseguire solo se php artisan migrate non riesce sul server.
-- Compatibile con tabella legacy `evento` (sql_mode disattivato per la sessione).

SET SESSION sql_mode = '';

ALTER TABLE `evento`
    ADD `greeting_box_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    ADD `greeting_box_duration` SMALLINT UNSIGNED NOT NULL DEFAULT 5,
    ADD `greeting_box_message` TEXT NULL,
    ADD `greeting_box_max_width` SMALLINT UNSIGNED NOT NULL DEFAULT 420,
    ADD `greeting_box_border_color` VARCHAR(7) NOT NULL DEFAULT '#198754',
    ADD `greeting_box_bg_color` VARCHAR(7) NOT NULL DEFAULT '#ffffff';

SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- Dopo l'SQL manuale, registra la migrazione Laravel (sostituisci il batch se necessario):
-- INSERT INTO migrations (migration, batch) VALUES ('2026_05_23_120000_add_greeting_box_columns_to_evento_table', (SELECT IFNULL(MAX(batch),0)+1 FROM migrations AS m));
