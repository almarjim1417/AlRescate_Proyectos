<?php
$file = 'import_proyectos.csv';

if (!file_exists($file)) {
    die("❌ El archivo $file NO existe.");
}

echo "<h1>Diagnóstico del CSV</h1>";

// 1. Ver las primeras líneas en crudo
echo "<h3>Contenido Crudo (Primeros 500 caracteres):</h3>";
$content = file_get_contents($file, false, null, 0, 500);
echo "<textarea style='width:100%; height:100px;'>$content</textarea>";

// 2. Intentar leer con fgetcsv
echo "<h3>Intento de lectura PHP:</h3>";
$handle = fopen($file, "r");
$row_count = 0;

echo "<table border='1'>";
while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
    $row_count++;

    // Solo mostramos las 3 primeras filas
    if ($row_count <= 3) {
        echo "<tr>";
        echo "<td>Fila $row_count</td>";
        echo "<td>Columnas detectadas: <strong>" . count($data) . "</strong></td>";
        echo "<td>Columna 0: " . htmlspecialchars($data[0]) . "</td>";
        echo "</tr>";
    }
}
fclose($handle);
echo "</table>";

echo "<h3>Total de filas detectadas: <strong>$row_count</strong></h3>";

if ($row_count < 2) {
    echo "<h2 style='color:red'>⚠️ ALERTA: PHP solo ve 1 fila. El delimitador probablemente es incorrecto (¿Punto y coma?).</h2>";
} else {
    echo "<h2 style='color:green'>✅ El archivo parece correcto.</h2>";
}
