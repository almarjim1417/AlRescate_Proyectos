<?php
/**
 * Script para desactivar y limpiar módulo admfincas
 * ADVERTENCIA: Este script limpia índices y restricciones duplicadas
 */

// Incluir Dolibarr
require_once dirname(__FILE__) . '/../../htdocs/main.inc.php';

if (!$user->admin) {
    die("¡No tiene permisos de administrador!");
}

$db = $GLOBALS['db'];

// Paso 1: Limpiar índices y restricciones duplicadas
$sql_queries = array(
    // Eliminar índice duplicado en tabla admfinca
    "ALTER TABLE llx_admfincas_admfinca DROP INDEX IF EXISTS uk_admfinca_ref",
    
    // Eliminar índice duplicado en vol_societe si existe
    "ALTER TABLE llx_societe DROP INDEX IF EXISTS idx_societe_fk_admfinca_dup",
    
    // Recrear el índice único correcto
    "ALTER TABLE llx_admfincas_admfinca ADD UNIQUE INDEX uk_admfinca_ref (ref, entity)",
);

foreach ($sql_queries as $sql) {
    if (!$db->query($sql)) {
        // Log pero continúa - algunos índices podrían no existir
        error_log("Query opcional: " . $sql);
    }
}

// Paso 2: Dejar el módulo sin activar
$GLOBALS['conf']->admfincas = new stdClass();
$GLOBALS['conf']->admfincas->enabled = 0;

echo "✓ Módulo limpiado. Puede intentar activarlo nuevamente en Administración > Módulos";
