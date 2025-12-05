<?php
// Carga del entorno
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/contrat/class/contrat.class.php';

dol_include_once('/admfincas/class/admfinca.class.php');
dol_include_once('/admfincas/lib/admfincas_admfinca.lib.php');

$langs->loadLangs(array("contracts", "companies", "admfincas@admfincas"));

// Parámetros
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

if ($id <= 0)
    accessforbidden();

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
    $sortfield = 'c.date_contrat';
}
if (empty($sortorder)) {
    $sortorder = 'DESC';
}

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$param = '&id=' . $id;

// Filtros
$search_ref = GETPOST('search_ref', 'alpha');
$search_societe = GETPOST('search_societe', 'alpha');
$search_status = GETPOST('search_status', 'int');

if ($search_ref)
    $param .= '&search_ref=' . urlencode($search_ref);
if ($search_societe)
    $param .= '&search_societe=' . urlencode($search_societe);
if ($search_status != '' && $search_status != -1)
    $param .= '&search_status=' . urlencode($search_status);


// VISTA
$title = "Contratos Gestionados - " . $object->name;
llxHeader('', $title);

if (function_exists('admfincaPrepareHead')) {
    $head = admfincaPrepareHead($object);
} else {
    $head = array();
}

print dol_get_fiche_head($head, 'contracts', "Administrador de Fincas", -1, $object->picto);

print '<div class="fichecenter"><div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';
print '<tr><td class="titlefield">Referencia</td><td>' . $object->ref . '</td></tr>';
print '<tr><td>Nombre Comercial</td><td>' . $object->name . '</td></tr>';
print '</table></div>';
print dol_get_fiche_end();


// --- CONSULTA SQL (SIN total_ttc) ---
$sql = "SELECT c.rowid, c.ref, c.date_contrat, c.statut, ";
$sql .= "s.nom as societe_name, s.rowid as socid, s.code_client ";
$sql .= "FROM " . MAIN_DB_PREFIX . "contrat as c ";
$sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON c.fk_soc = s.rowid ";
// Filtro 1:N
$sql .= "WHERE s.fk_admfinca = " . $object->id . " ";

if ($search_ref)
    $sql .= natural_search('c.ref', $search_ref);
if ($search_societe)
    $sql .= natural_search('s.nom', $search_societe);
if ($search_status != '' && $search_status != -1)
    $sql .= " AND c.statut = " . $search_status;

$sql .= $db->order($sortfield, $sortorder);

// Paginación
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
    $resql_count = $db->query($sql);
    if ($resql_count)
        $nbtotalofrecords = $db->num_rows($resql_count);
}
$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);


// TABLA
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="id" value="' . $object->id . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="page" value="' . $page . '">';

print '<br>';
print_barre_liste("Listado de Contratos", $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'contract', 0, '', '', $limit);

print '<div class="div-table-responsive">';
print '<table class="tagtable liste">';

// Cabeceras
print '<tr class="liste_titre">';
print_liste_field_titre("Ref. Contrato", $_SERVER["PHP_SELF"], "c.ref", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Comunidad / Cliente", $_SERVER["PHP_SELF"], "s.nom", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Fecha Inicio", $_SERVER["PHP_SELF"], "c.date_contrat", "", $param, 'align="center"', $sortfield, $sortorder);
print_liste_field_titre("Estado", $_SERVER["PHP_SELF"], "c.statut", "", $param, 'align="right"', $sortfield, $sortorder);
print_liste_field_titre('', $_SERVER["PHP_SELF"], "", "", "", 'align="center"', $sortfield, $sortorder);
print '</tr>';

// Filtros
$form = new Form($db);
print '<tr class="liste_titre">';
print '<td class="liste_titre"><input type="text" class="flat" name="search_ref" value="' . $search_ref . '" size="10"></td>';
print '<td class="liste_titre"><input type="text" class="flat" name="search_societe" value="' . $search_societe . '"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre" align="right">';
print $form->selectarray('search_status', array('0' => 'Borrador', '1' => 'Validado', '2' => 'Cerrado'), $search_status, 1);
print '</td>';
print '<td class="liste_titre" align="center">';
print $form->showFilterButtons();
print '</td>';
print '</tr>';

// Bucle
if ($resql && $db->num_rows($resql) > 0) {
    $contratstatic = new Contrat($db);

    while ($row = $db->fetch_object($resql)) {
        $contratstatic->id = $row->rowid;
        $contratstatic->ref = $row->ref;
        $contratstatic->statut = $row->statut;

        print '<tr class="oddeven">';
        print '<td>' . $contratstatic->getNomUrl(1) . '</td>';
        print '<td><a href="' . DOL_URL_ROOT . '/societe/card.php?socid=' . $row->socid . '">' . $row->societe_name . '</a></td>';
        print '<td align="center">' . dol_print_date($db->jdate($row->date_contrat), 'day') . '</td>';
        print '<td align="right">' . $contratstatic->getLibStatut(5) . '</td>';
        print '<td></td>';
        print '</tr>';
    }
} else {
    print '<tr><td colspan="5" class="opacitymedium">No se encontraron contratos.</td></tr>';
}
print '</table>';
print '</div>';
print '</form>';

llxFooter();
$db->close();
?>