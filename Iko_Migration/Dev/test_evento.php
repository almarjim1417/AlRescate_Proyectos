<?php
// SCRIPT DE INYECCIÓN DE EVENTOS (SOLO EVENTOS)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'dol_ikonik';
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

echo "<h1>Inyectando Eventos a Proyectos de Hoy</h1>";

// 1. Buscar proyectos creados hoy que NO tengan evento
$sql = "SELECT rowid, ref, title, datec FROM llx_projet 
        WHERE datec >= CURDATE() 
        AND rowid NOT IN (SELECT fk_project FROM llx_actioncomm)";
$stmt = $pdo->query($sql);
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Encontrados " . count($proyectos) . " proyectos sin evento.<br>";

// 2. Preparar inserción
// AÑADIMOS TODOS LOS CAMPOS POSIBLES PARA EVITAR ERRORES
$sql_insert = "INSERT INTO llx_actioncomm (
    ref_ext, label, datep, datep2, percent, note, 
    fk_project, fk_user_author, fk_user_action, fk_soc, 
    entity, code, datec, tms, priority, fulldayevent, location
) VALUES (
    ?, ?, ?, ?, 100, ?, 
    ?, 1, 1, NULL, 
    1, 'AC_OTH', NOW(), NOW(), 0, 1, ''
)";
$stmt_insert = $pdo->prepare($sql_insert);

$creados = 0;
foreach ($proyectos as $p) {
    try {
        $ref_evt = 'EVT-' . $p['ref'];
        $titulo = "Ficha Técnica: " . $p['title'];
        $fecha = $p['datec']; // Usamos la misma fecha del proyecto
        $nota = "Datos importados automáticamente.";

        $stmt_insert->execute([
            $ref_evt,
            $titulo,
            $fecha,
            $fecha,
            $nota,
            $p['rowid']
        ]);
        $creados++;
        echo "Evento creado para: {$p['ref']}<br>";
    } catch (PDOException $e) {
        echo "<span style='color:red'>Error en {$p['ref']}: " . $e->getMessage() . "</span><br>";
    }
}

echo "<h3>Total eventos creados: $creados</h3>";
