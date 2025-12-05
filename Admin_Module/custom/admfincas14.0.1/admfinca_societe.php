<?php
// Carga del entorno maestro
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

dol_include_once('/admfincas/class/admfinca.class.php');
dol_include_once('/admfincas/lib/admfincas_admfinca.lib.php');

$langs->loadLangs(array("admfincas@admfincas", "companies", "other"));

$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

// Parámetros lista
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
if (empty($page) || $page == -1) {
    $page = 0;
}
if (empty($sortfield)) {
    $sortfield = 's.nom';
}
if (empty($sortorder)) {
    $sortorder = 'ASC';
}
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$param = '&id=' . $id;

// Filtros
$search_nom = GETPOST('search_nom', 'alpha');
// ... (puedes añadir más filtros al param aquí)

if ($id <= 0)
    accessforbidden();

$object = new Admfinca($db);
$object->fetch($id);


// -------------------------------------------------------------------
// ACCIONES (Adaptadas a 1:N)
// -------------------------------------------------------------------

// 1. AÑADIR VINCULACIÓN (CON PROTECCIÓN Y CORRECCIÓN DE CAMPO)
if ($action == 'add_link') {
    $socid_to_add = GETPOST('socid_to_add', 'int');

    if ($socid_to_add > 0) {
        // PASO 1: Comprobar si ya tiene dueño
        // CORRECCIÓN: Usamos 'name' en lugar de 'nom'
        $sql_check = "SELECT s.fk_admfinca, adm.name as admin_name ";
        $sql_check .= "FROM " . MAIN_DB_PREFIX . "societe as s ";
        $sql_check .= "LEFT JOIN " . MAIN_DB_PREFIX . "admfincas_admfinca as adm ON s.fk_admfinca = adm.rowid ";
        $sql_check .= "WHERE s.rowid = " . $socid_to_add;

        $res_check = $db->query($sql_check);

        // Protección contra error fatal
        if (!$res_check) {
            setEventMessages("Error SQL al comprobar: " . $db->lasterror(), null, 'errors');
        } else {
            $obj_check = $db->fetch_object($res_check);

            // PASO 2: Evaluar
            if ($obj_check && $obj_check->fk_admfinca > 0 && $obj_check->fk_admfinca != $object->id) {
                // CASO OCUPADO
                $nombre_admin = $obj_check->admin_name ? $obj_check->admin_name : 'Otro Administrador';
                setEventMessages("Error: Esta comunidad ya está gestionada por '" . $nombre_admin . "'. Debe desvincularla primero.", null, 'errors');
            } else {
                // CASO LIBRE: Asignamos
                $sql = "UPDATE " . MAIN_DB_PREFIX . "societe SET fk_admfinca = " . $object->id . " WHERE rowid = " . $socid_to_add;

                if ($db->query($sql)) {
                    setEventMessages("Comunidad asignada correctamente.", null, 'mesgs');
                } else {
                    setEventMessages($db->error(), null, 'errors');
                }
            }
        }
    }
}

// 2. DESVINCULAR TERCERO (UPDATE a NULL)
if ($action == 'delete_link') {
    $socid_to_del = GETPOST('socid_to_del', 'int'); // Ahora recibimos el ID del tercero, no del link

    if ($socid_to_del > 0) {
        $sql = "UPDATE " . MAIN_DB_PREFIX . "societe SET fk_admfinca = NULL WHERE rowid = " . $socid_to_del;

        if ($db->query($sql)) {
            setEventMessages("Vinculación eliminada. El tercero ha quedado libre.", null, 'mesgs');
        } else {
            setEventMessages($db->error(), null, 'errors');
        }
    }
}

// -------------------------------------------------------------------
// VISTA
// -------------------------------------------------------------------

$title = "Terceros Gestionados - " . $object->name;
llxHeader('', $title);

if (function_exists('admfincaPrepareHead')) {
    $head = admfincaPrepareHead($object);
} else {
    $head = array();
}

print dol_get_fiche_head($head, 'societes', $langs->trans("Admfinca"), -1, $object->picto);

print '<div class="fichecenter"><div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';
print '<tr><td class="titlefield">Referencia</td><td>' . $object->ref . '</td></tr>';
print '<tr><td>Nombre Comercial</td><td>' . $object->name . '</td></tr>';
print '</table></div>';
print dol_get_fiche_end();


// --- AÑADIR NUEVO (BUSCADOR) ---
print '<br>';
print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="add_link">';
print '<div class="box"><table class="noborder centpercent"><tr class="liste_titre"><td>Asignar Tercero a este Administrador</td></tr><tr><td>';
$form = new Form($db);
// Filtro opcional: Mostrar solo terceros "libres" (fk_admfinca IS NULL)? 
// De momento mostramos todos.
print $form->select_company('', 'socid_to_add', '', 1);
print '<input type="submit" class="button" value="Asignar" style="margin-left:10px;">';
print '</td></tr></table></div></form>';


// --- LISTADO ---

// QUERY ACTUALIZADA
$sql = "SELECT s.rowid, s.nom as name, s.code_client, s.email, s.phone, s.siren, s.town, s.zip, s.status, s.client ";
$sql .= "FROM " . MAIN_DB_PREFIX . "societe as s ";
$sql .= "WHERE s.fk_admfinca = " . $object->id . " "; // Relación directa

if ($search_nom)
    $sql .= natural_search('s.nom', $search_nom);
// ... resto filtros ...

$sql .= $db->order($sortfield, $sortorder);

// Paginación...
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
    $resql_count = $db->query($sql);
    $nbtotalofrecords = $db->num_rows($resql_count);
}
$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);
$num = 0;
if ($resql) {
    $num = $db->num_rows($resql);
}

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="id" value="' . $object->id . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="page" value="' . $page . '">';

print '<br>';
print_barre_liste("Cartera de Clientes", $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_companies', 0, '', '', $limit);

print '<div class="div-table-responsive">';
print '<table class="tagtable liste">';

// Cabeceras (Igual que antes)
print '<tr class="liste_titre">';
print_liste_field_titre("Tercero", $_SERVER["PHP_SELF"], "s.nom", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Código", $_SERVER["PHP_SELF"], "s.code_client", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("NIF/CIF", $_SERVER["PHP_SELF"], "s.siren", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Ciudad", $_SERVER["PHP_SELF"], "s.town", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Email", $_SERVER["PHP_SELF"], "s.email", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Estado", $_SERVER["PHP_SELF"], "s.status", "", $param, 'align="center"', $sortfield, $sortorder);
print_liste_field_titre('', $_SERVER["PHP_SELF"], "", "", "", 'align="center"', $sortfield, $sortorder);
print '</tr>';

// Filtros visuales (Igual que antes)...
print '<tr class="liste_titre">...</tr>'; // (Resumido para no alargar, mantén tus filtros)

if ($resql && $db->num_rows($resql) > 0) {
    $soc_static = new Societe($db);

    while ($row = $db->fetch_object($resql)) {
        $soc_static->id = $row->rowid;
        $soc_static->name = $row->name;
        $soc_static->email = $row->email;
        $soc_static->client = $row->client;
        $soc_static->status = $row->status;

        print '<tr class="oddeven">';
        print '<td>' . $soc_static->getNomUrl(1) . '</td>';
        print '<td>' . $row->code_client . '</td>';
        print '<td>' . $row->siren . '</td>';
        print '<td>' . $row->zip . ' ' . $row->town . '</td>';
        print '<td>' . dol_print_email($row->email, 0, 0, 1, 0, -1) . '</td>';
        print '<td align="center">' . $soc_static->getLibStatut(5) . '</td>';

        // BOTÓN DESVINCULAR (Actualizado)
        // Pasamos socid_to_del (el ID del tercero) en lugar de link_id
        print '<td align="center">';
        print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=delete_link&socid_to_del=' . $row->rowid . '&token=' . newToken() . '">';
        print img_delete("Desvincular (Liberar)");
        print '</a>';
        print '</td>';
        print '</tr>';
    }
} else {
    print '<tr><td colspan="7" class="opacitymedium">No hay comunidades asignadas.</td></tr>';
}

print '</table>';
print '</div>';
print '</form>';

llxFooter();
$db->close();
