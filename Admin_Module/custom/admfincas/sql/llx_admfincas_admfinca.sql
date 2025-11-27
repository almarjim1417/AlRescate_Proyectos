-- ==============================================================================
-- ESTRUCTURA DEL MÓDULO ADMINISTRADORES DE FINCAS (v2.0 - Relación 1:N)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 1. TABLA PRINCIPAL: Administradores
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS llx_admfincas_admfinca(
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    ref varchar(128) NOT NULL,
    nom varchar(128) NOT NULL,
    address text,
    zip varchar(25),
    town varchar(50),
    fk_state integer DEFAULT 0,
    fk_country integer DEFAULT 0,
    email varchar(255),
    phone varchar(20),
    url varchar(255),
    
    -- CAMPOS DEL PORTAL (NATIVOS)
    portal_user varchar(50),
    portal_pass varchar(255), 
    
    note_public text,
    note_private text,
    date_creation datetime,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer,
    fk_user_modif integer,
    import_key varchar(14),
    status integer DEFAULT 1,
    entity integer DEFAULT 1
) ENGINE=innodb;

-- Índices
ALTER TABLE llx_admfincas_admfinca ADD UNIQUE INDEX uk_admfincas_admfinca_ref (ref, entity);
ALTER TABLE llx_admfincas_admfinca ADD INDEX idx_admfincas_admfinca_status (status);
ALTER TABLE llx_admfincas_admfinca ADD INDEX idx_admfincas_admfinca_portal (portal_user);


-- ------------------------------------------------------------------------------
-- 2. MODIFICACIÓN DEL NÚCLEO (Relación 1:N)
-- Añadimos la columna fk_admfinca a la tabla de Terceros (llx_societe)
-- Nota: Usamos 'IGNORE' o sintaxis permisiva por si la columna ya existe
-- ------------------------------------------------------------------------------

-- Para MySQL/MariaDB: Si falla porque existe, no pasa nada crítico en la instalación
-- (Dolibarr suele gestionar esto bien, pero lo definimos explícito aquí)
ALTER TABLE llx_societe ADD COLUMN fk_admfinca INTEGER DEFAULT NULL;
ALTER TABLE llx_societe ADD INDEX idx_societe_fk_admfinca (fk_admfinca);


-- ------------------------------------------------------------------------------
-- 3. TABLA DE EXTRAFIELDS (Estándar de Dolibarr)
-- Necesaria aunque esté vacía para que el sistema de hooks funcione correctamente
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS llx_admfincas_admfinca_extrafields (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_object integer NOT NULL,
  import_key varchar(14) DEFAULT NULL
) ENGINE=innodb;

ALTER TABLE llx_admfincas_admfinca_extrafields ADD INDEX idx_admfincas_admfinca_extrafields (fk_object);