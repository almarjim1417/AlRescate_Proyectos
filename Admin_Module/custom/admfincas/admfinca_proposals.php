<?php
// Carga del entorno
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';

// Carga del módulo
dol_include_once('/admfincas/class/admfinca.class.php');
dol_include_once('/admfincas/lib/admfincas_admfinca.lib.php');

$langs->loadLangs(array("propal", "companies", "admfincas@admfincas"));

// -------------------------------------------------------------------
// PARÁMETROS
// -------------------------------------------------------------------
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

// Seguridad
if ($id <= 0) accessforbidden();

$object = new Admfinca($db);
$object->fetch($id);

// Parámetros de lista
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
if (empty($page) || $page == -1) {
    $page = 0;
}
if (empty($sortfield)) {
    $sortfield = 'p.datep';
} // Orden por fecha
if (empty($sortorder)) {
    $sortorder = 'DESC';
}

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;

// --- VARIABLE PARAM (Para no perder el ID al ordenar) ---
$param = '&id=' . $id;

// Parámetros de Búsqueda
$search_ref = GETPOST('search_ref', 'alpha');
$search_societe = GETPOST('search_societe', 'alpha');
$search_amount = GETPOST('search_amount', 'alpha');
$search_status = GETPOST('search_status', 'int');

// Añadimos filtros a la URL de paginación
if ($search_ref)     $param .= '&search_ref=' . urlencode($search_ref);
if ($search_societe) $param .= '&search_societe=' . urlencode($search_societe);
if ($search_amount)  $param .= '&search_amount=' . urlencode($search_amount);
if ($search_status != '' && $search_status != -1) $param .= '&search_status=' . urlencode($search_status);


// -------------------------------------------------------------------
// VISTA
// -------------------------------------------------------------------
$title = "Presupuestos Gestionados - " . $object->name;
llxHeader('', $title);

if (function_exists('admfincaPrepareHead')) {
    $head = admfincaPrepareHead($object);
} else {
    $head = array();
}

print dol_get_fiche_head($head, 'proposals', "Administrador de Fincas", -1, $object->picto);

// Resumen
print '<div class="fichecenter"><div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';
print '<tr><td class="titlefield">Referencia</td><td>' . $object->ref . '</td></tr>';
print '<tr><td>Nombre Comercial</td><td>' . $object->name . '</td></tr>';
print '</table></div>';
print dol_get_fiche_end();


// -------------------------------------------------------------------
// CONSULTA SQL
// -------------------------------------------------------------------

// --- SQL ACTUALIZADO (1:N) ---
$sql = "SELECT p.rowid, p.ref, p.datep, p.total_ttc, p.fk_statut, ";
$sql .= "s.nom as societe_name, s.rowid as socid, s.code_client ";
$sql .= "FROM " . MAIN_DB_PREFIX . "propal as p ";
$sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON p.fk_soc = s.rowid ";
// SIN JOIN
$sql .= "WHERE s.fk_admfinca = " . $object->id . " "; // FILTRO DIRECT

// Filtros
if ($search_ref)     $sql .= natural_search('p.ref', $search_ref);
if ($search_societe) $sql .= natural_search('s.nom', $search_societe);
if ($search_amount)  $sql .= natural_search('p.total_ttc', $search_amount);
if ($search_status != '' && $search_status != -1) $sql .= " AND p.fk_statut = " . $search_status;

// Orden
$sql .= $db->order($sortfield, $sortorder);

// Paginación - Conteo
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
    $resql_count = $db->query($sql);
    $nbtotalofrecords = $db->num_rows($resql_count);
}

$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);


// -------------------------------------------------------------------
// FORMULARIO Y TABLA
// -------------------------------------------------------------------

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="id" value="' . $object->id . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="page" value="' . $page . '">';

print '<br>';
print_barre_liste("Listado de Presupuestos", $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_commercial', 0, '', '', $limit);

print '<div class="div-table-responsive">';
print '<table class="tagtable liste">';

// CABECERAS
print '<tr class="liste_titre">';
print_liste_field_titre("Ref. Presupuesto", $_SERVER["PHP_SELF"], "p.ref", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Comunidad / Cliente", $_SERVER["PHP_SELF"], "s.nom", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Fecha", $_SERVER["PHP_SELF"], "p.datep", "", $param, 'align="center"', $sortfield, $sortorder);
print_liste_field_titre("Importe (IVA inc)", $_SERVER["PHP_SELF"], "p.total_ttc", "", $param, 'align="right"', $sortfield, $sortorder);
print_liste_field_titre("Estado", $_SERVER["PHP_SELF"], "p.fk_statut", "", $param, 'align="right"', $sortfield, $sortorder);
print_liste_field_titre('', $_SERVER["PHP_SELF"], "", "", "", 'align="center"', $sortfield, $sortorder);
print '</tr>';

// FILTROS
$form = new Form($db);
print '<tr class="liste_titre">';
print '<td class="liste_titre"><input type="text" class="flat" name="search_ref" value="' . $search_ref . '" size="10"></td>';
print '<td class="liste_titre"><input type="text" class="flat" name="search_societe" value="' . $search_societe . '"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre" align="right"><input type="text" class="flat" name="search_amount" value="' . $search_amount . '" size="6"></td>';
print '<td class="liste_titre" align="right">';
// Estados de Presupuestos: 0=Borrador, 1=Abierto, 2=Firmado, 3=Rechazado, 4=Facturado
$status_array = array('0' => 'Borrador', '1' => 'Abierto', '2' => 'Firmado', '3' => 'Rechazado', '4' => 'Facturado');
print $form->selectarray('search_status', $status_array, $search_status, 1);
print '</td>';
print '<td class="liste_titre" align="center">';
print $form->showFilterButtons();
print '</td>';
print '</tr>';

// BUCLE
if ($resql && $db->num_rows($resql) > 0) {
    $propalstatic = new Propal($db);

    while ($row = $db->fetch_object($resql)) {
        $propalstatic->id = $row->rowid;
        $propalstatic->ref = $row->ref;
        $propalstatic->statut = $row->fk_statut;

        print '<tr class="oddeven">';
        // Link Presupuesto
        print '<td>' . $propalstatic->getNomUrl(1) . '</td>';

        // Link Tercero
        print '<td>';
        print '<a href="' . DOL_URL_ROOT . '/societe/card.php?socid=' . $row->socid . '">' . $row->societe_name . '</a>';
        print '</td>';

        // Fecha
        print '<td align="center">' . dol_print_date($db->jdate($row->datep), 'day') . '</td>';

        // Importe
        print '<td align="right">' . price($row->total_ttc) . '</td>';

        // Estado
        print '<td align="right">' . $propalstatic->getLibStatut(5) . '</td>';

        print '<td></td>';
        print '</tr>';
    }
} else {
    print '<tr><td colspan="6" class="opacitymedium">No se encontraron presupuestos.</td></tr>';
}

print '</table>';
print '</div>';
print '</form>';

llxFooter();
$db->close();
