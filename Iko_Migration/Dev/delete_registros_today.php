<?php
// ==============================================================================
// SCRIPT DE BORRADO DEFINITIVO (Usando Timestamp y Huellas)
// ==============================================================================

$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'dol_ikonik';

echo "<h1>Limpieza Definitiva (Por TMS y Huellas)</h1>";

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

function ejecutar_borrado($pdo, $sql, $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

// 1. DESACTIVAR PROTECCIÓN (Para borrar padres sin que se quejen los hijos)
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// ---------------------------------------------------------
// CRITERIO 1: BORRAR POR HUELLA DE TEXTO (Lo más seguro)
// ---------------------------------------------------------
echo "<h3>Fase 1: Borrado por Huella de Texto...</h3>";

// Borrar Eventos generados
$n = ejecutar_borrado($pdo, "DELETE FROM llx_actioncomm WHERE note LIKE '%--- DATOS ORIGINALES EXCEL ---%'");
echo "Eventos borrados: $n <br>";

// Borrar Extras de Proyectos (Vinculados a proyectos de la migración)
$n = ejecutar_borrado($pdo, "DELETE FROM llx_projet_extrafields WHERE fk_object IN (SELECT rowid FROM llx_projet WHERE description LIKE '%--- DATOS IMPORTADOS ---%')");
echo "Extras Proyectos borrados: $n <br>";

// Borrar Proyectos
$n = ejecutar_borrado($pdo, "DELETE FROM llx_projet WHERE description LIKE '%--- DATOS IMPORTADOS ---%'");
echo "Proyectos borrados: $n <br>";

// Borrar Extras de Sitios Auto-creados
$n = ejecutar_borrado($pdo, "DELETE FROM llx_socpeople_extrafields WHERE fk_object IN (SELECT rowid FROM llx_socpeople WHERE note_private = 'Generado Auto')");
echo "Extras Sitios borrados: $n <br>";

// Borrar Sitios Auto-creados
$n = ejecutar_borrado($pdo, "DELETE FROM llx_socpeople WHERE note_private = 'Generado Auto'");
echo "Sitios borrados: $n <br>";

// ---------------------------------------------------------
// CRITERIO 2: BORRAR POR TIMESTAMP DE HOY (Lo que se haya escapado)
// ---------------------------------------------------------
echo "<h3>Fase 2: Barrido por Fecha de Sistema (TMS)...</h3>";

// Borramos lo que se haya modificado/creado en el sistema en las últimas 24h
// NOTA: Usamos NOW() - INTERVAL 1 DAY para cubrir todo el día de hoy.

// Dinero (Tabla personalizada)
$n = ejecutar_borrado($pdo, "DELETE FROM presupuestos_indicadores WHERE fecha_registro >= CURDATE()");
echo "Datos Financieros borrados: $n <br>";

// Proyectos por TMS (Si se escapó alguno sin nota)
$n = ejecutar_borrado($pdo, "DELETE FROM llx_projet WHERE tms >= CURDATE()");
echo "Proyectos (por TMS) borrados: $n <br>";

// Clientes creados HOY (Estos sí usan datec correcto porque fue NOW())
$n = ejecutar_borrado($pdo, "DELETE FROM llx_societe WHERE datec >= CURDATE()");
echo "Clientes nuevos borrados: $n <br>";

// 3. REACTIVAR PROTECCIÓN
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "<hr><h2 style='color:green'>✅ Limpieza Completada.</h2>";
