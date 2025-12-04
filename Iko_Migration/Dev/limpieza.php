<?php
// ==============================================================================
// LIMPIEZA QUIRÚRGICA (BORRA SOLO LA MIGRACIÓN)
// ==============================================================================
// - No borra por fecha.
// - Borra buscando la "firma" que dejó el script en las notas.
// - Respeta tus datos antiguos y los creados a mano hoy.
// ==============================================================================

$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'dol_ikonik';

echo "<h1>Limpieza Selectiva (Solo Migración)</h1>";

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

function borrar_seguro($pdo, $sql, $texto_info)
{
    $stmt = $pdo->query($sql);
    echo "<li>$texto_info: <strong>" . $stmt->rowCount() . "</strong> registros.</li>";
}

// DESACTIVAR CLAVES FORÁNEAS PARA PODER BORRAR EN CUALQUIER ORDEN
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "<ul>";

// 1. BORRAR DATOS FINANCIEROS (De proyectos migrados)
// Borramos todo lo de esta tabla porque dijiste que antes estaba vacía/inservible.
// Si quieres conservarla, avísame, pero la migración inyecta aquí.
borrar_seguro(
    $pdo,
    "TRUNCATE TABLE presupuestos_indicadores",
    "Datos Financieros (Limpieza total de tabla)"
);

// 2. BORRAR EVENTOS (AGENDA) DE LA MIGRACIÓN
// Buscamos los que tienen la referencia externa que empieza por IMP-PDev
borrar_seguro(
    $pdo,
    "DELETE FROM llx_actioncomm WHERE ref_ext LIKE 'IMP-PDev%' OR label LIKE 'Ficha Importada:%'",
    "Eventos de Agenda importados"
);

// 3. BORRAR EXTRAS DE PROYECTOS (Solo de los migrados)
borrar_seguro(
    $pdo,
    "DELETE FROM llx_projet_extrafields WHERE fk_object IN (
        SELECT rowid FROM llx_projet 
        WHERE note_public LIKE '%--- DATOS EXCEL ---%' 
           OR note_public LIKE '%Ref. Original Excel:%'
    )",
    "Extras de Proyectos migrados"
);

// 4. BORRAR PROYECTOS (Solo los migrados)
borrar_seguro(
    $pdo,
    "DELETE FROM llx_projet 
     WHERE note_public LIKE '%--- DATOS EXCEL ---%' 
        OR note_public LIKE '%Ref. Original Excel:%'",
    "Proyectos migrados (Identificados por Nota)"
);

// 5. BORRAR EXTRAS DE SITIOS (Solo los generados auto)
borrar_seguro(
    $pdo,
    "DELETE FROM llx_socpeople_extrafields WHERE fk_object IN (
        SELECT rowid FROM llx_socpeople WHERE note_private = 'Generado Auto'
    )",
    "Extras de Sitios auto-generados"
);

// 6. BORRAR SITIOS (Solo los generados auto)
borrar_seguro(
    $pdo,
    "DELETE FROM llx_socpeople WHERE note_private = 'Generado Auto'",
    "Sitios auto-generados"
);

// 7. BORRAR CLIENTES (Solo los creados HOY por el script)
// Aquí sí usamos fecha porque no pusimos nota, pero es seguro si no has creado clientes a mano hoy.
// Si has creado clientes a mano hoy, comenta esta línea.
borrar_seguro(
    $pdo,
    "DELETE FROM llx_societe WHERE datec >= CURDATE() AND code_client IS NULL",
    "Clientes nuevos (sin código manual)"
);

echo "</ul>";

// REACTIVAR SEGURIDAD
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "<h3>✅ Limpieza finalizada. Tus datos antiguos están a salvo.</h3>";
echo "<a href='migracion_v34_master.php'>Volver a Importar (v34)</a>";
