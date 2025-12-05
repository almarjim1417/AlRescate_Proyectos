-- =====================================================
-- MIGRACIÓN v1.0 -> v2.0
-- Corrige la estructura de la tabla admfincas_admfinca
-- - Renombra columna nom a name
-- - Agrega columnas faltantes
-- =====================================================

-- Esta sentencia usa CHANGE COLUMN que es idempotente en estructura
-- Si ya existe 'name', se ignora el error sin afectar nada
ALTER TABLE IF EXISTS llx_admfincas_admfinca MODIFY COLUMN `name` varchar(128) NOT NULL;

-- Agregar columnas si no existen (usando ADD COLUMN IF NOT EXISTS en MariaDB 10.3+)
-- Para compatibilidad con versiones antiguas de MySQL, se usa una sentencia por columna
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `label` varchar(255) AFTER `ref`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `fk_soc` integer AFTER `town`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `fk_project` integer AFTER `fk_soc`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `description` text AFTER `url`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `last_main_doc` varchar(255) AFTER `fk_user_modif`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `model_pdf` varchar(255) AFTER `last_main_doc`;
