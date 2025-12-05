<?php
// --------------------------------------------------------------------
// PESTAÑA CONTACTOS (VERSIÓN FINAL COMPATIBLE v14)
// --------------------------------------------------------------------

// 1. CARGA ROBUSTA
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res=@include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res=@include "../../../main.inc.php";
if (! $res) die("Error crítico: No se encuentra main.inc.php");

require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
dol_include_once('/admfincas/class/admfinca.class.php');
dol_include_once('/admfincas/lib/admfincas_admfinca.lib.php');

$langs->loadLangs(array("admfincas@admfincas", "companies", "other", "mails"));

// 2. PARÁMETROS
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'aZ09');

// 3. CARGA OBJETO
$object = new Admfinca($db);
if ($id > 0) {
    $object->fetch($id);
} else {
    accessforbidden("ID inválido");
}

// 4. PERMISOS (CORREGIDOS)
$usercanread = 0;
$usercanwrite = 0;

// Chequeo estándar
if (!empty($user->rights->admfincas->admfinca->read)) $usercanread = 1;
if (!empty($user->rights->admfincas->admfinca->write)) $usercanwrite = 1;

// Bypass para SuperAdmin (Esto soluciona tu problema)
if ($user->admin) {
    $usercanread = 1;
    $usercanwrite = 1;
}

if (!$usercanread) accessforbidden();


// -------------------------------------------------------------------
// ACCIONES
// -------------------------------------------------------------------

if ($action == 'addcontact' && $usercanwrite) {
    $contactid = (GETPOST('userid') ? GETPOSTINT('userid') : GETPOSTINT('contactid'));
    $typeid = (GETPOST('typecontact') ? GETPOST('typecontact') : GETPOST('type'));
    $source = GETPOST("source", 'aZ09');

    $result = $object->add_contact($contactid, $typeid, $source);

    if ($result >= 0) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id);
        exit;
    } else {
        if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
            setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
        } else {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    }
}

if ($action == 'deletecontact' && $usercanwrite) {
    $lineid = GETPOSTINT('lineid');
    $result = $object->delete_contact($lineid);

    if ($result >= 0) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $object->id);
        exit;
    } else {
        setEventMessages($object->error, $object->errors, 'errors');
    }
}

if ($action == 'swapstatut' && $usercanwrite) {
    $result = $object->swapContactStatus(GETPOSTINT('ligne'));
}


// -------------------------------------------------------------------
// VISTA
// -------------------------------------------------------------------

$title = "Contactos - " . $object->name;
llxHeader('', $title);

$form = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic = new Contact($db);
$userstatic = new User($db);

// Cabecera
if (function_exists('admfincaPrepareHead')) {
    $head = admfincaPrepareHead($object);
} else { $head = array(); }

print dol_get_fiche_head($head, 'contact', $langs->trans("Admfinca"), -1, $object->picto);

$linkback = '<a href="' . dol_buildpath('/admfincas/admfinca_list.php', 1) . '?restore_lastsearch_values=1">' . $langs->trans("BackToList") . '</a>';
dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', '', '', 0, '', '', 1);

print dol_get_fiche_end();
print '<br>';

// TABLA DE CONTACTOS
// Variables para la plantilla
$permissiontoadd = $usercanwrite; 
$modulepart = 'admfincas'; 

// Carga plantilla estándar
$dirtpls = array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
foreach ($dirtpls as $reldir) {
    $tpl_file = dol_buildpath($reldir . '/contacts.tpl.php');
    if (file_exists($tpl_file)) {
        include $tpl_file;
        break;
    }
}

llxFooter();
$db->close();
?>