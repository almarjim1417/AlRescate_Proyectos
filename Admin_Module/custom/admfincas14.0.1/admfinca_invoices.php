<?php
// Carga del entorno
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';

// Carga del módulo
dol_include_once('/admfincas/class/admfinca.class.php');
dol_include_once('/admfincas/lib/admfincas_admfinca.lib.php');

$langs->loadLangs(array("bills", "companies", "admfincas@admfincas"));

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
    $sortfield = 'f.datef';
} // Orden por defecto: Fecha
if (empty($sortorder)) {
    $sortorder = 'DESC';
}    // Descendente (las nuevas primero)

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;

// --- CORRECCIÓN CLAVE: Definir $param para mantener el ID al ordenar ---
$param = '&id=' . $id;
// -----------------------------------------------------------------------

// Parámetros de Búsqueda
$search_ref = GETPOST('search_ref', 'alpha');
$search_societe = GETPOST('search_societe', 'alpha');
$search_amount = GETPOST('search_amount', 'alpha');
$search_status = GETPOST('search_status', 'int'); // Importante: puede ser 0, así que ojo con empty()

// Añadimos filtros a $param
if ($search_ref)     $param .= '&search_ref=' . urlencode($search_ref);
if ($search_societe) $param .= '&search_societe=' . urlencode($search_societe);
if ($search_amount)  $param .= '&search_amount=' . urlencode($search_amount);
if ($search_status != '' && $search_status != -1) $param .= '&search_status=' . urlencode($search_status);


// -------------------------------------------------------------------
// VISTA
// -------------------------------------------------------------------
$title = "Facturas Gestionadas - " . $object->name;
llxHeader('', $title);

if (function_exists('admfincaPrepareHead')) {
    $head = admfincaPrepareHead($object);
} else {
    $head = array();
}

print dol_get_fiche_head($head, 'invoices', "Administrador de Fincas", -1, $object->picto);

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

$sql = "SELECT f.rowid, f.ref, f.datef, f.total_ttc, f.fk_statut, f.paye, f.type, ";
$sql .= "s.nom as societe_name, s.rowid as socid, s.code_client ";
$sql .= "FROM " . MAIN_DB_PREFIX . "facture as f ";
$sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON f.fk_soc = s.rowid ";
// IMPORTANTE: Eliminamos el JOIN a la tabla vieja y filtramos directo
$sql .= "WHERE s.fk_admfinca = " . $object->id . " ";

// Filtros
if ($search_ref)     $sql .= natural_search('f.ref', $search_ref);
if ($search_societe) $sql .= natural_search('s.nom', $search_societe);
if ($search_amount)  $sql .= natural_search('f.total_ttc', $search_amount);
if ($search_status != '' && $search_status != -1) $sql .= " AND f.fk_statut = " . $search_status;

// Orden
$sql .= $db->order($sortfield, $sortorder);

// Paginación - Conteo total
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
print '<input type="hidden" name="token" value="' . newToken() . '">'; // Token CSRF
print '<input type="hidden" name="id" value="' . $object->id . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="page" value="' . $page . '">';

print '<br>';
// Pasamos $param aquí para que las flechas de página funcionen
print_barre_liste("Listado de Facturas", $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_accountancy', 0, '', '', $limit);

print '<div class="div-table-responsive">';
print '<table class="tagtable liste">';

// CABECERAS (Pasamos $param aquí para que la ordenación funcione)
print '<tr class="liste_titre">';
print_liste_field_titre("Ref. Factura", $_SERVER["PHP_SELF"], "f.ref", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Comunidad / Cliente", $_SERVER["PHP_SELF"], "s.nom", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Fecha", $_SERVER["PHP_SELF"], "f.datef", "", $param, 'align="center"', $sortfield, $sortorder);
print_liste_field_titre("Importe (IVA inc)", $_SERVER["PHP_SELF"], "f.total_ttc", "", $param, 'align="right"', $sortfield, $sortorder);
print_liste_field_titre("Estado", $_SERVER["PHP_SELF"], "f.fk_statut", "", $param, 'align="right"', $sortfield, $sortorder);
print_liste_field_titre('', $_SERVER["PHP_SELF"], "", "", "", 'align="center"', $sortfield, $sortorder); // Botones filtro
print '</tr>';

// FILTROS DE BÚSQUEDA
$form = new Form($db);
print '<tr class="liste_titre">';
print '<td class="liste_titre"><input type="text" class="flat" name="search_ref" value="' . $search_ref . '" size="10"></td>';
print '<td class="liste_titre"><input type="text" class="flat" name="search_societe" value="' . $search_societe . '"></td>';
print '<td class="liste_titre"></td>'; // Fecha (dejamos vacío por simplicidad)
print '<td class="liste_titre" align="right"><input type="text" class="flat" name="search_amount" value="' . $search_amount . '" size="6"></td>';
print '<td class="liste_titre" align="right">';
// Select de estados de factura (0=Borrador, 1=Impagada, 2=Pagada)
print $form->selectarray('search_status', array('0' => 'Borrador', '1' => 'Impagada', '2' => 'Pagada'), $search_status, 1);
print '</td>';
print '<td class="liste_titre" align="center">';
print $form->showFilterButtons(); // Lupa y Cruz
print '</td>';
print '</tr>';

// BUCLE DE DATOS
if ($resql && $db->num_rows($resql) > 0) {
    $facturastatic = new Facture($db);

    while ($row = $db->fetch_object($resql)) {
        $facturastatic->id = $row->rowid;
        $facturastatic->ref = $row->ref;
        $facturastatic->statut = $row->fk_statut;
        $facturastatic->paye = $row->paye;
        $facturastatic->type = $row->type;

        print '<tr class="oddeven">';
        // Link Factura
        print '<td>' . $facturastatic->getNomUrl(1) . '</td>';

        // Link Tercero
        print '<td>';
        print '<a href="' . DOL_URL_ROOT . '/societe/card.php?socid=' . $row->socid . '">' . $row->societe_name . '</a>';
        print '</td>';

        // Fecha
        print '<td align="center">' . dol_print_date($db->jdate($row->datef), 'day') . '</td>';

        // Importe
        print '<td align="right">' . price($row->total_ttc) . '</td>';

        // Estado
        print '<td align="right">' . $facturastatic->getLibStatut(5) . '</td>';

        // Espacio vacío (acciones)
        print '<td></td>';
        print '</tr>';
    }
} else {
    print '<tr><td colspan="6" class="opacitymedium">No se encontraron facturas.</td></tr>';
}

print '</table>';
print '</div>';
print '</form>';

llxFooter();
$db->close();
