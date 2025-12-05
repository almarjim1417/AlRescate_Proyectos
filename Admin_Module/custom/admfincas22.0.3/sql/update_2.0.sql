-- =====================================================
-- Script de actualización de la tabla admfincas
-- Versión: 2.0 (Corrección de estructura)
-- =====================================================
-- Este script se ejecuta cuando se detecta una versión anterior

-- 1. Renombrar columna nom -> name si existe
ALTER TABLE llx_admfincas_admfinca CHANGE COLUMN `nom` `name` varchar(128) NOT NULL;

-- 2. Agregar columnas faltantes si no existen
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `label` varchar(255) AFTER `ref`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `fk_soc` integer AFTER `town`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `fk_project` integer AFTER `fk_soc`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `description` text AFTER `url`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `last_main_doc` varchar(255) AFTER `fk_user_modif`;
ALTER TABLE llx_admfincas_admfinca ADD COLUMN `model_pdf` varchar(255) AFTER `last_main_doc`;
