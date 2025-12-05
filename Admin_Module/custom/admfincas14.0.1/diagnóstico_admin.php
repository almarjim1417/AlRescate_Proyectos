<?php
/**
 * SOLUCIÓN PARA PÁGINA EN BLANCO EN CONFIGURACIÓN DEL MÓDULO ADMFINCAS
 * 
 * Este archivo contiene información sobre cómo diagnosticar y resolver
 * el problema de página en blanco al acceder a la configuración.
 */

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Diagnóstico - Admfincas Página en Blanco</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }";
echo "h1 { color: #333; border-bottom: 3px solid #0066cc; padding-bottom: 10px; }";
echo "h2 { color: #0066cc; margin-top: 30px; }";
echo ".error { background: #ffebee; border-left: 4px solid #c62828; padding: 10px; margin: 10px 0; }";
echo ".warning { background: #fff3e0; border-left: 4px solid #f57c00; padding: 10px; margin: 10px 0; }";
echo ".success { background: #e8f5e9; border-left: 4px solid #388e3c; padding: 10px; margin: 10px 0; }";
echo ".info { background: #e3f2fd; border-left: 4px solid #1976d2; padding: 10px; margin: 10px 0; }";
echo "code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; font-family: monospace; }";
echo "pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }";
echo ".solution { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

echo "<h1>🔧 Diagnóstico: Página en Blanco en Configuración de Admfincas</h1>";

// Intentar incluir archivos de configuración de Dolibarr
$mainIncPath = null;
$possiblePaths = array(
    dirname(__FILE__) . '/main.inc.php',
    dirname(__FILE__) . '/../main.inc.php',
    dirname(__FILE__) . '/../../main.inc.php',
    'D:/xampp/htdocs/CONLIPRESS/htdocs/main.inc.php',
);

foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $mainIncPath = $path;
        break;
    }
}

echo "<h2>📋 Estado del Sistema</h2>";

echo "<div class='info'>";
echo "<p><strong>Versión de PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Versión de Apache:</strong> " . (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'No disponible') . "</p>";
echo "<p><strong>Sistema Operativo:</strong> " . php_uname() . "</p>";
echo "</div>";

echo "<h2>🔍 Verificación de Archivos del Módulo</h2>";

$modulePath = 'D:/xampp/htdocs/CONLIPRESS/htdocs/custom/admfincas';
$criticalFiles = array(
    'core/modules/modAdmfincas.class.php' => 'Clase principal del módulo',
    'admin/setup.php' => 'Página de configuración',
    'lib/admfincas_admfinca.lib.php' => 'Librería del módulo',
    'class/admfinca.class.php' => 'Clase Admfinca',
);

$allFilesOk = true;
foreach ($criticalFiles as $file => $desc) {
    $fullPath = $modulePath . '/' . $file;
    $exists = file_exists($fullPath);
    $readable = is_readable($fullPath);
    
    if ($exists && $readable) {
        echo "<div class='success'>✓ <strong>$file</strong>: OK ($desc)</div>";
    } else {
        $allFilesOk = false;
        $msg = $exists ? 'No es legible' : 'No existe';
        echo "<div class='error'>✗ <strong>$file</strong>: $msg ($desc)</div>";
    }
}

echo "<h2>⚙️ Posibles Causas de Página en Blanco</h2>";

echo "<div class='solution'>";
echo "<h3>1. Error Silencioso (Silent PHP Error)</h3>";
echo "<p>La configuración de PHP podría estar ocultando los errores.</p>";
echo "<p><strong>Solución:</strong></p>";
echo "<pre>En D:\\xampp\\htdocs\\CONLIPRESS\\htdocs\\conf\\conf.php, añade al inicio:
error_reporting(E_ALL);
ini_set('display_errors', 1);</pre>";
echo "</div>";

echo "<div class='solution'>";
echo "<h3>2. Permisos de Archivos Insuficientes</h3>";
echo "<p>Los archivos del módulo podrían no tener permisos de lectura.</p>";
echo "<p><strong>Solución (PowerShell):</strong></p>";
echo "<pre>icacls 'D:\\xampp\\htdocs\\CONLIPRESS\\htdocs\\custom\\admfincas' /grant 'IUSR:(OI)(CI)F' /T</pre>";
echo "</div>";

echo "<div class='solution'>";
echo "<h3>3. Falta de Configuración del Módulo</h3>";
echo "<p>El módulo podría no estar completamente instalado.</p>";
echo "<p><strong>Solución:</strong></p>";
echo "<ol>";
echo "<li>Ve a Administración > Módulos</li>";
echo "<li>Busca 'admfincas'</li>";
echo "<li>Haz clic en 'Activar' (si no está activo)</li>";
echo "<li>Si ya está activo, haz clic en 'Desactivar' y luego 'Activar'</li>";
echo "</ol>";
echo "</div>";

echo "<div class='solution'>";
echo "<h3>4. Error en la Conexión a la Base de Datos</h3>";
echo "<p>El módulo podría estar intentando ejecutar queries que fallan.</p>";
echo "<p><strong>Verificación:</strong></p>";
echo "<pre>Revisa D:\\xampp\\apache\\logs\\error.log para mensajes de error</pre>";
echo "</div>";

echo "<div class='solution'>";
echo "<h3>5. Clase DolibarrModules no Encontrada</h3>";
echo "<p>El archivo de la clase principal podría tener referencias rotas.</p>";
echo "<p><strong>Solución:</strong></p>";
echo "<pre>Verifica que en modAdmfincas.class.php existe:
include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';</pre>";
echo "</div>";

echo "<h2>🛠️ Pasos de Diagnóstico Paso a Paso</h2>";

echo "<div class='info'>";
echo "<ol>";
echo "<li><strong>Verifica los logs de Apache:</strong><br>";
echo "<code>D:\\xampp\\apache\\logs\\error.log</code></li>";
echo "<li><strong>Activa el debug en Dolibarr:</strong><br>";
echo "En <code>conf/conf.php</code> establece:<br>";
echo "<code>\$dolibarr_main_prod = '0';</code></li>";
echo "<li><strong>Crea un archivo de prueba:</strong><br>";
echo "En el directorio raíz con:<br>";
echo "<pre>&lt;?php\n";
echo "error_reporting(E_ALL);\n";
echo "ini_set('display_errors', 1);\n";
echo "require_once 'main.inc.php';\n";
echo "if (isModEnabled('admfincas')) {\n";
echo "    echo 'Módulo admfincas está activo';\n";
echo "} else {\n";
echo "    echo 'Módulo admfincas NO está activo';\n";
echo "}\n";
echo "?&gt;</pre>";
echo "</li>";
echo "<li><strong>Prueba manualmente la clase:</strong><br>";
echo "<pre>&lt;?php\n";
echo "require_once 'main.inc.php';\n";
echo "require_once 'custom/admfincas/core/modules/modAdmfincas.class.php';\n";
echo "if (class_exists('modAdmfincas')) {\n";
echo "    echo 'Clase cargada correctamente';\n";
echo "    \$mod = new modAdmfincas(\$db);\n";
echo "    echo 'Módulo instanciado correctamente';\n";
echo "} else {\n";
echo "    echo 'Error: Clase no encontrada';\n";
echo "}\n";
echo "?&gt;</pre>";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<h2>📞 Información Técnica del Módulo</h2>";

echo "<div class='info'>";
echo "<p><strong>Número del Módulo:</strong> 500000</p>";
echo "<p><strong>Versión:</strong> 2.0</p>";
echo "<p><strong>Clase Principal:</strong> modAdmfincas extends DolibarrModules</p>";
echo "<p><strong>Archivo Configuración:</strong> /custom/admfincas/admin/setup.php</p>";
echo "<p><strong>Triggers Habilitados:</strong> Sí</p>";
echo "<p><strong>Hooks Activos:</strong> thirdpartycard</p>";
echo "<p><strong>Dolibarr Mínimo Requerido:</strong> 12.0.0</p>";
echo "</div>";

echo "<h2>💡 Consejos Adicionales</h2>";

echo "<div class='warning'>";
echo "<p><strong>Si la página se queda en blanco pero ves la barra superior y menú lateral:</strong></p>";
echo "<ol>";
echo "<li>Significa que Dolibarr cargó correctamente</li>";
echo "<li>El error está en el contenido de la página de configuración</li>";
echo "<li>Es probablemente un error de PHP no capturado en admin/setup.php</li>";
echo "<li>Verifica que la función <code>admfincaPrepareHead()</code> existe</li>";
echo "<li>Revisa que todos los <code>require_once</code> están correctos</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top: 40px; text-align: center; color: #999; font-size: 0.9em;'>";
echo "Diagnóstico generado: " . date('Y-m-d H:i:s') . " | ";
echo "Dolibarr en: " . $mainIncPath . " | ";
echo "Módulo en: " . $modulePath;
echo "</p>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
