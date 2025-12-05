<?php

/**
 * \file    admfincas/admin/setup.php
 * \ingroup admfincas
 * \brief   Página de configuración del módulo
 */

// 1. CARGA ROBUSTA DEL ENTORNO
$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res && preg_match('/\/custom\//', $_SERVER["SCRIPT_FILENAME"])) $res = @include "../../../main.inc.php";
if (! $res) die("Error de carga del núcleo.");

require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT . "/core/class/extrafields.class.php";
require_once '../lib/admfincas_admfinca.lib.php';

$langs->loadLangs(array("admin", "admfincas@admfincas"));

// Control de permisos
if (!$user->admin) accessforbidden();

$action = GETPOST('action', 'aZ09');
$value = GETPOST('value', 'alpha');
$modulepart = GETPOST('modulepart', 'aZ09');

/*
 * ACCIONES (Activar/Desactivar modelos de numeración)
 */
if ($action == 'setmod') {
	$constforval = 'ADMFINCAS_ADMFINCA_ADDON';
	dolibarr_set_const($db, $constforval, $value, 'chaine', 0, '', $conf->entity);
}

/*
 * VISTA
 */
$form = new Form($db);
$title = "Configuración Admfincas";
llxHeader('', $langs->trans($title));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans($title), $linkback, 'title_setup');

// PESTAÑAS (LIMPIAS: Solo General)
$head = array();
$h = 0;
$head[$h][0] = $_SERVER["PHP_SELF"];
$head[$h][1] = "General";
$head[$h][2] = 'general';
$h++;

// NOTA: Hemos ocultado la pestaña 'Atributos Adicionales' porque el archivo del núcleo no existe en esta instalación.
// Si en el futuro se repara Dolibarr, se puede descomentar esto:
/*
$head[$h][0] = '../../../../admin/extrafields.php?elementtype=admfincas_admfinca';
$head[$h][1] = "Atributos Adicionales";
$head[$h][2] = 'attributes';
$h++;
*/

print dol_get_fiche_head($head, 'settings', $langs->trans($title), -1, "admfincas@admfincas");

// --- ESTADO DEL MÓDULO ---
print '<div class="info-box">';
print '<span class="fa fa-check-circle" style="color: green;"></span> ';
print 'Módulo <b>Administradores de Fincas</b> instalado y operativo.<br>';
print 'Los campos de acceso al portal (Usuario/Contraseña) son nativos y están activos.';
print '</div>';
print '<br>';


// --- CONFIGURACIÓN DE NUMERACIÓN ---
$moduledir = 'admfincas';
$myTmpObjects = array();
$myTmpObjects['admfinca'] = array('label' => 'Administrador', 'includerefgeneration' => 1, 'includedocgeneration' => 0);

foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
	if (!empty($myTmpObjectArray['includerefgeneration'])) {

		print load_fiche_titre($langs->trans("NumberingModules", $myTmpObjectArray['label']), '', '');

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans("Name") . '</td>';
		print '<td>' . $langs->trans("Description") . '</td>';
		print '<td class="nowrap">' . $langs->trans("Example") . '</td>';
		print '<td class="center" width="60">' . $langs->trans("Status") . '</td>';
		print '</tr>' . "\n";

		$name = "mod_admfinca_standard";
		$desc = "Numeración automática secuencial (ADM-00001)";
		$example = "ADM-00001";

		print '<tr class="oddeven">';
		print '<td>' . $name . '</td>';
		print '<td>' . $desc . '</td>';
		print '<td>' . $example . '</td>';

		print '<td class="center">';
		print '<span class="badge badge-status4">Activado</span>';
		print '</td>';
		print '</tr>';

		print "</table>\n";
	}
}

print dol_get_fiche_end();
llxFooter();
