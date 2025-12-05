<?php
/**
 * Script para reinicializar el módulo admfincas
 * Ejecutar accediendo a: http://localhost/CONLIPRESS/htdocs/custom/admfincas/reinit_module.php
 */

// Incluir Dolibarr
require_once dirname(__FILE__) . '/../../main.inc.php';

// Verificar permisos
if (!$user->admin) {
    die("❌ Error: No tiene permisos de administrador");
}

$db = $GLOBALS['db'];

echo "<h2>Reinicializando módulo admfincas...</h2>";

// Paso 1: Limpiar índices duplicados (si existen)
echo "<p>1. Limpiando índices duplicados...</p>";
$cleanup_queries = array(
    "ALTER TABLE vol_admfincas_admfinca DROP INDEX IF EXISTS uk_admfinca_ref_dup",
    "ALTER TABLE vol_admfincas_admfinca DROP INDEX IF EXISTS uk_admfinca_ref_old",
);

foreach ($cleanup_queries as $sql) {
    $db->query($sql);
}

// Paso 2: Recrear índice único correcto
echo "<p>2. Recreando índice único...</p>";
$db->query("ALTER TABLE vol_admfincas_admfinca DROP INDEX IF EXISTS uk_admfinca_ref");
$db->query("ALTER TABLE vol_admfincas_admfinca ADD UNIQUE INDEX uk_admfinca_ref (ref, entity)");

// Paso 3: Cargar clase del módulo
echo "<p>3. Cargando clase del módulo...</p>";
require_once dirname(__FILE__) . '/core/modules/modAdmfincas.class.php';

// Paso 4: Crear instancia e inicializar
echo "<p>4. Inicializando módulo...</p>";
$module = new modAdmfincas($db);
$result = $module->init('');

if ($result > 0) {
    echo "<p style='color:green'><strong>✓ Módulo reinicializado correctamente</strong></p>";
    echo "<p>Ahora puede activarlo desde Administración > Módulos > admfincas</p>";
} else {
    echo "<p style='color:red'><strong>⚠ Advertencia durante inicialización (puede ser normal)</strong></p>";
    echo "<p>El módulo podría funcionar aún así. Intente activarlo.</p>";
}

echo "<p><a href='../../admin/modules.php?mainmenu=home&leftmenu=setup&idmenu=145'>← Ir a módulos</a></p>";
?>
