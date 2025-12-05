<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - DESCARGA SEGURA DE DOCUMENTOS (V. FINAL)
// --------------------------------------------------------------------

// 1. CARGA DEL NÚCLEO
if (!defined('NOLOGIN')) define('NOLOGIN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', '1');
if (!defined('NOIPCHECK')) define('NOIPCHECK', '1');

$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Error system.");

require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/contrat/class/contrat.class.php';

// 2. SEGURIDAD Y FUNCIONES
if (!session_id()) session_start();
if (!isset($_SESSION['portal_id'])) die("Acceso denegado (Sesión).");

// IMPORTANTE: Incluir las funciones de desencriptado
require_once 'functions.php';

$my_admin_id = $_SESSION['portal_id'];
$token = GETPOST('token', 'alpha');
$type = GETPOST('type', 'alpha');

// 3. DESENCRIPTAR ID
$id = 0;
if (!empty($token)) {
    $id = (int) portal_decrypt($token);
}

if ($id <= 0) die("Enlace no válido o caducado.");

$allowed = false;
$file_path = '';
$filename = '';
$object = null;

// 4. LÓGICA POR TIPO DE DOCUMENTO

// --- FACTURAS ---
if ($type == 'invoice') {
    $object = new Facture($db);
    if ($object->fetch($id) > 0) {
        // Check seguridad 1:N (El cliente debe tener mi fk_admfinca)
        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE rowid = " . $object->socid . " AND fk_admfinca = " . $my_admin_id;
        if ($db->query($sql)->num_rows > 0) {
            $allowed = true;
            $ref = dol_sanitizeFileName($object->ref);
            $file_path = $conf->facture->dir_output . "/" . $ref . "/" . $ref . ".pdf";
            $filename = $ref . ".pdf";
        }
    }
}

// --- PRESUPUESTOS ---
if ($type == 'proposal') {
    $object = new Propal($db);
    if ($object->fetch($id) > 0) {
        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE rowid = " . $object->socid . " AND fk_admfinca = " . $my_admin_id;
        if ($db->query($sql)->num_rows > 0) {
            $allowed = true;
            $ref = dol_sanitizeFileName($object->ref);
            $file_path = $conf->propal->dir_output . "/" . $ref . "/" . $ref . ".pdf";
            $filename = $ref . ".pdf";
        }
    }
}

// --- PEDIDOS ---
if ($type == 'order') {
    $object = new Commande($db);
    if ($object->fetch($id) > 0) {
        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE rowid = " . $object->socid . " AND fk_admfinca = " . $my_admin_id;
        if ($db->query($sql)->num_rows > 0) {
            $allowed = true;
            $ref = dol_sanitizeFileName($object->ref);
            $file_path = $conf->commande->dir_output . "/" . $ref . "/" . $ref . ".pdf";
            $filename = $ref . ".pdf";
        }
    }
}

// --- CONTRATOS ---
if ($type == 'contract') {
    $object = new Contrat($db);
    if ($object->fetch($id) > 0) {
        // A veces el objeto contrato usa fk_soc o socid, aseguramos
        $soc_id_real = !empty($object->socid) ? $object->socid : $object->fk_soc;

        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE rowid = " . $soc_id_real . " AND fk_admfinca = " . $my_admin_id;
        if ($db->query($sql)->num_rows > 0) {
            $allowed = true;
            $ref = dol_sanitizeFileName($object->ref);
            $file_path = $conf->contrat->dir_output . "/" . $ref . "/" . $ref . ".pdf";
            $filename = $ref . ".pdf";
        }
    }
}


// 5. DESCARGA FINAL
if ($allowed) {
    if (file_exists($file_path)) {
        // Limpiar buffer de salida para no corromper el PDF
        if (ob_get_length()) ob_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
        exit;
    } else {
        die("<h1>Documento no disponible</h1><p>El archivo PDF (" . htmlspecialchars($filename) . ") no ha sido generado todavía en el sistema.</p>");
    }
} else {
    die("<h1>Acceso Denegado</h1><p>No tiene permisos para visualizar este documento o el enlace es incorrecto.</p>");
}
