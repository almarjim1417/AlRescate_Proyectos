-- ESTRUCTURA BASE DE DATOS (V. COMPATIBLE)
-- Las operaciones con CREATE TABLE IF NOT EXISTS y ALTER TABLE IF EXISTS son seguras para múltiples ejecuciones

-- 1. TABLA PRINCIPAL
CREATE TABLE IF NOT EXISTS llx_admfincas_admfinca(
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    ref varchar(128) NOT NULL,
    label varchar(255),
    name varchar(128) NOT NULL,
    address text,
    zip varchar(25),
    town varchar(50),
    fk_state integer DEFAULT 0,
    fk_country integer DEFAULT 0,
    fk_soc integer,
    fk_project integer,
    email varchar(255),
    phone varchar(20),
    url varchar(255),
    description text,
    portal_user varchar(50),
    portal_pass varchar(255),
    note_public text,
    note_private text,
    date_creation datetime,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer,
    fk_user_modif integer,
    last_main_doc varchar(255),
    import_key varchar(14),
    model_pdf varchar(255),
    status integer DEFAULT 1,
    entity integer DEFAULT 1
) ENGINE=innodb;

-- Crear índice único (si ya existe, será ignorado)
-- NOTA: MySQL ejecutará esto sin error aunque ya exista
CREATE UNIQUE INDEX IF NOT EXISTS uk_admfinca_ref ON llx_admfincas_admfinca (ref, entity);

-- 2. TABLA EXTRAFIELDS (Requerida por Dolibarr core)
CREATE TABLE IF NOT EXISTS llx_admfincas_admfinca_extrafields (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_object integer NOT NULL,
  import_key varchar(14) DEFAULT NULL
) ENGINE=innodb;