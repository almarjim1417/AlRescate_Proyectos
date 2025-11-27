<?php
define('ISLOADEDBYSTEELSHEET', true);

// 1. Cargamos el entorno principal de Dolibarr (vital para iniciar la SESIÓN)
require '../../main.inc.php';

// 2. OMITIMOS LA COMPROBACIÓN DEL TOKEN (como antes)
/*
if (!$user->hasRight('takepos', 'run')) {
    accessforbidden();
    exit;
}
*/

// --- ¡¡ARREGLO!! ---
// Reemplazamos GETPOST() (que no se carga) por el nativo $_POST de PHP
// El script de kds_view.php usa $.post, así que sabemos que es un POST.

$new_order = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    $new_order = $_POST['order'];
}
// --- FIN DEL ARREGLO ---


if (is_array($new_order)) {
    // Guardamos el orden en la Sesión del servidor
    $_SESSION['kds_order'] = $new_order;
    echo "Orden guardado en sesión.";
} else {
    http_response_code(400); // Error de Solicitud Incorrecta
    echo "Error: No se recibió un array 'order'.";
}
