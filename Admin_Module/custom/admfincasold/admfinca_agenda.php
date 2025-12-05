<?php
// --------------------------------------------------------------------
// PESTAÑA EVENTOS (AGENDA CONSOLIDADA - VERSIÓN FINAL)
// --------------------------------------------------------------------

// 1. CARGA ROBUSTA DEL NÚCLEO (CORREGIDO)
$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php"; // Si está en custom
if (! $res) die("Error: No se encuentra main.inc.php");

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

// Carga del módulo
dol_include_once('/admfincas/class/admfinca.class.php');
dol_include_once('/admfincas/lib/admfincas_admfinca.lib.php');

$langs->loadLangs(array("admfincas@admfincas", "agenda", "companies", "commercial"));

$id = GETPOST('id', 'int');
if ($id <= 0) accessforbidden();

$object = new Admfinca($db);
$object->fetch($id);

// Vista
$title = "Eventos - " . $object->name;
llxHeader('', $title);

if (function_exists('admfincaPrepareHead')) {
	$head = admfincaPrepareHead($object);
} else {
	$head = array();
}

print dol_get_fiche_head($head, 'agenda', $langs->trans("Admfinca"), -1, $object->picto);

// 1. OBTENER IDs DE COMUNIDADES
$societe_ids = array();
// Consulta corregida 1:N
$sql_soc = "SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE fk_admfinca = " . $object->id;
$res_soc = $db->query($sql_soc);
if ($res_soc) {
	while ($row = $db->fetch_object($res_soc)) {
		$societe_ids[] = $row->rowid;
	}
}
if (empty($societe_ids)) {
	$societe_ids[] = 0;
}
$lista_ids = implode(',', $societe_ids);


// 2. CONSULTA DE EVENTOS (SQL PURO PARA EVITAR ERRORES 500)
// Seleccionamos eventos relacionados con las comunidades O con el propio admin
$sql = "SELECT a.id, a.label, a.datep, a.percent, a.fk_user_author, ";
$sql .= "s.nom as soc_name, s.rowid as soc_id, ";
$sql .= "c.code as type_code, ";
$sql .= "u.login as user_login ";
$sql .= "FROM " . MAIN_DB_PREFIX . "actioncomm as a ";
$sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON a.fk_soc = s.rowid ";
$sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "c_actioncomm as c ON a.fk_action = c.id ";
$sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "user as u ON a.fk_user_author = u.rowid ";
// Filtro: O es de mis comunidades, O es mío directo
$sql .= "WHERE (a.fk_soc IN (" . $lista_ids . ") OR (a.elementtype = 'admfinca' AND a.fk_element = " . $object->id . ")) ";
$sql .= "ORDER BY a.datep DESC, a.id DESC ";
$sql .= "LIMIT 50";

$resql = $db->query($sql);

// 3. PINTAR LA TABLA
print '<div class="div-table-responsive">';
print '<table class="tagtable liste">';

print '<tr class="liste_titre">';
print '<td>ID</td>';
print '<td>Tipo</td>';
print '<td>Descripción</td>';
print '<td>Comunidad</td>';
print '<td align="center">Fecha</td>';
print '<td align="center">Usuario</td>';
print '<td align="right">%</td>';
print '</tr>';

if ($resql && $db->num_rows($resql) > 0) {
	while ($obj = $db->fetch_object($resql)) {
		print '<tr class="oddeven">';

		// ID con enlace
		$link = DOL_URL_ROOT . '/comm/action/card.php?id=' . $obj->id;
		print '<td><a href="' . $link . '">#' . $obj->id . '</a></td>';

		// Tipo
		print '<td><span class="badge">' . ($obj->type_code ? $obj->type_code : 'Evento') . '</span></td>';

		// Etiqueta
		print '<td>' . dol_trunc($obj->label, 60) . '</td>';

		// Comunidad
		print '<td>';
		if ($obj->soc_id > 0) {
			print '<a href="' . DOL_URL_ROOT . '/societe/card.php?socid=' . $obj->soc_id . '">' . $obj->soc_name . '</a>';
		} else {
			print '-';
		}
		print '</td>';

		// Fecha
		print '<td align="center">' . dol_print_date($db->jdate($obj->datep), 'dayhour') . '</td>';

		// Usuario
		print '<td align="center">' . $obj->user_login . '</td>';

		// Porcentaje
		print '<td align="right">' . $obj->percent . '%</td>';

		print '</tr>';
	}
} else {
	print '<tr><td colspan="7" class="opacitymedium">No hay eventos recientes.</td></tr>';
}

print '</table>';
print '</div>';

print dol_get_fiche_end();
llxFooter();
$db->close();
