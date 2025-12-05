<?php
/**
 * VERIFICADOR DIRECTO DE CONFIGURACIÓN DEL MÓDULO ADMFINCAS
 * 
 * Este script simula lo que hace Dolibarr al intentar cargar la página de configuración
 * del módulo y muestra errores específicos.
 */

// Forzar visualización de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head>";
echo "<meta charset='UTF-8'>";
echo "<title>Test Configuración Admfincas</title>";
echo "<style>";
echo "body { font-family: 'Courier New', monospace; margin: 20px; background: #1e1e1e; color: #d4d4d4; }";
echo ".success { color: #4ec9b0; }";
echo ".error { color: #f48771; }";
echo ".warning { color: #ce9178; }";
echo ".log { background: #252526; padding: 10px; margin: 5px 0; border-left: 3px solid #0e639c; }";
echo "pre { background: #252526; padding: 10px; overflow-x: auto; }";
echo "</style>";
echo "</head><body>";

echo "<h1>🧪 Test Directo de Configuración de Admfincas</h1>";

// Step 1: Verificar archivos
echo "<h2>Paso 1: Verificación de Archivos</h2>";

$moduleDir = 'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas';
$files = array(
    'core/modules/modAdmfincas.class.php',
    'admin/setup.php',
    'lib/admfincas_admfinca.lib.php',
    'class/admfinca.class.php',
);

foreach ($files as $file) {
    $path = $moduleDir . '/' . $file;
    if (file_exists($path)) {
        echo "<div class='log'><span class='success'>✓</span> $file</div>";
    } else {
        echo "<div class='log'><span class='error'>✗ FALTA:</span> $file</div>";
    }
}

// Step 2: Intentar cargar la clase del módulo
echo "<h2>Paso 2: Cargar Clase del Módulo</h2>";

try {
    // Simular la carga que haría Dolibarr
    $path = 'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas/core/modules/modAdmfincas.class.php';
    
    echo "<div class='log'>Intentando incluir: $path</div>";
    
    // Primero verificar que existe
    if (!file_exists($path)) {
        throw new Exception("Archivo no existe");
    }
    
    if (!is_readable($path)) {
        throw new Exception("Archivo no es legible");
    }
    
    // Intentar incluir
    if (@require_once($path)) {
        echo "<div class='log'><span class='success'>✓ Archivo incluido correctamente</span></div>";
        
        // Verificar si la clase existe
        if (class_exists('modAdmfincas')) {
            echo "<div class='log'><span class='success'>✓ Clase modAdmfincas encontrada</span></div>";
        } else {
            echo "<div class='log'><span class='error'>✗ Clase modAdmfincas NO encontrada en el archivo</span></div>";
        }
    } else {
        echo "<div class='log'><span class='error'>✗ Error al incluir el archivo</span></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='log'><span class='error'>✗ Excepción:</span> " . $e->getMessage() . "</div>";
}

// Step 3: Verificar dependencias de la clase
echo "<h2>Paso 3: Verificar Dependencias</h2>";

$required_classes = array(
    'DolibarrModules' => 'core/modules/DolibarrModules.class.php',
);

foreach ($required_classes as $class => $file) {
    if (class_exists($class)) {
        echo "<div class='log'><span class='success'>✓ Clase $class disponible</span></div>";
    } else {
        echo "<div class='log'><span class='warning'>⚠ Clase $class podría no estar disponible (normal si Dolibarr no está cargado)</span></div>";
    }
}

// Step 4: Simular lo que hace admin/setup.php
echo "<h2>Paso 4: Verificar Librería de Admin</h2>";

$libPath = 'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas/lib/admfincas_admfinca.lib.php';

if (file_exists($libPath) && is_readable($libPath)) {
    echo "<div class='log'><span class='success'>✓ Librería existe y es legible</span></div>";
    
    // Intentar incluir
    try {
        @require_once($libPath);
        if (function_exists('admfincaPrepareHead')) {
            echo "<div class='log'><span class='success'>✓ Función admfincaPrepareHead está disponible</span></div>";
        } else {
            echo "<div class='log'><span class='warning'>⚠ Función admfincaPrepareHead no se definió</span></div>";
        }
    } catch (Exception $e) {
        echo "<div class='log'><span class='error'>✗ Error al cargar librería: " . $e->getMessage() . "</span></div>";
    }
} else {
    echo "<div class='log'><span class='error'>✗ Librería no existe o no es legible</span></div>";
}

// Step 5: Verificar permisos
echo "<h2>Paso 5: Verificación de Permisos</h2>";

$permissionsToCheck = array(
    'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas',
    'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas/admin',
    'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas/class',
    'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas/lib',
);

foreach ($permissionsToCheck as $dir) {
    if (is_dir($dir) && is_readable($dir)) {
        echo "<div class='log'><span class='success'>✓</span> Directorio legible: " . basename($dir) . "</div>";
    } else {
        echo "<div class='log'><span class='error'>✗ Directorio no legible:</span> " . basename($dir) . "</div>";
    }
}

// Step 6: Información del servidor
echo "<h2>Paso 6: Información del Servidor</h2>";

echo "<div class='log'>PHP Version: " . phpversion() . "</div>";
echo "<div class='log'>Servidor: " . (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Desconocido') . "</div>";
echo "<div class='log'>SO: " . php_uname() . "</div>";
echo "<div class='log'>Usuario de PHP: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : getenv('USERNAME')) . "</div>";

// Step 7: Resumen
echo "<h2>📊 Resumen</h2>";

echo "<div class='log'>";
echo "<p><span class='success'>✓ COMPLETADO</span></p>";
echo "<p>Si todos los pasos muestran éxito, el módulo está correctamente instalado.</p>";
echo "<p>Si hay errores, revisa los logs:</p>";
echo "<ul>";
echo "<li>D:\\xampp\\apache\\logs\\error.log</li>";
echo "<li>D:\\xampp\\apache\\logs\\access.log</li>";
echo "<li>D:\\xampp\\mysql\\data\\mysql_error.log</li>";
echo "</ul>";
echo "</div>";

echo "<p style='margin-top: 30px; color: #888;'>Test ejecutado: " . date('Y-m-d H:i:s') . "</p>";

echo "</body></html>";
?>
