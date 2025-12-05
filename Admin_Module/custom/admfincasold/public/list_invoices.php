<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - LISTADO DE FACTURAS (CORREGIDO 1:N)
// --------------------------------------------------------------------

// 1. CARGA DE DOLIBARR
if (!defined('NOLOGIN')) define('NOLOGIN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', '1');
if (!defined('NOIPCHECK')) define('NOIPCHECK', '1');

$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Error de núcleo.");

// 2. SEGURIDAD Y FUNCIONES
if (!session_id()) session_start();
if (!isset($_SESSION['portal_id']) || empty($_SESSION['portal_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'functions.php'; // Vital para encriptar

$my_admin_id = $_SESSION['portal_id'];

// 3. FILTROS RECIBIDOS
$f_date_start = GETPOST('date_start', 'alpha');
$f_date_end   = GETPOST('date_end', 'alpha');
$f_status     = GETPOST('status', 'alpha');
$f_socid      = GETPOST('socid', 'int');

// 4. OBTENER LISTA DE COMUNIDADES (Para el filtro desplegable)
$arr_societes = array();
// Consulta 1:N (Directa a societe)
$sql_list = "SELECT s.rowid, s.nom FROM " . MAIN_DB_PREFIX . "societe as s ";
$sql_list .= "WHERE s.fk_admfinca = " . $my_admin_id . " ORDER BY s.nom ASC";

$res_list = $db->query($sql_list);
if ($res_list) {
    while ($row = $db->fetch_object($res_list)) {
        $arr_societes[$row->rowid] = $row->nom;
    }
}

// 5. CONSULTA PRINCIPAL DE FACTURAS
$sql = "SELECT f.rowid, f.ref, f.datef, f.total_ttc, f.fk_statut, f.paye, ";
$sql .= "s.nom as comunidad_nombre, s.code_client ";
$sql .= "FROM " . MAIN_DB_PREFIX . "facture as f ";
$sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON f.fk_soc = s.rowid ";
// FILTRO 1:N (Sin tabla intermedia)
$sql .= "WHERE s.fk_admfinca = " . $my_admin_id . " ";

// Aplicar Filtros
if ($f_socid > 0) {
    $sql .= " AND f.fk_soc = " . $f_socid;
}
if ($f_date_start) {
    $sql .= " AND f.datef >= '" . $db->escape($f_date_start) . " 00:00:00'";
}
if ($f_date_end) {
    $sql .= " AND f.datef <= '" . $db->escape($f_date_end) . " 23:59:59'";
}
if ($f_status != '') {
    if ($f_status == 'paid') $sql .= " AND f.paye = 1";
    if ($f_status == 'unpaid') $sql .= " AND f.paye = 0 AND f.fk_statut = 1";
    if ($f_status == 'draft') $sql .= " AND f.fk_statut = 0";
}

$sql .= " ORDER BY f.datef DESC";

$resql = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Facturas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-page {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .filters-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .filters-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
            min-width: 150px;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-filter {
            background: #007bff;
            color: white;
            border: none;
            padding: 9px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .table-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }

        .status-draft {
            background: #e2e3e5;
            color: #383d41;
        }

        .btn-pdf {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <div class="header-page">
            <h2><i class="fa fa-file-invoice-dollar"></i> Facturas</h2>
            <a href="dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="filters-card">
            <form class="filters-form" method="GET">
                <div class="form-group" style="min-width: 250px;">
                    <label>Comunidad</label>
                    <select name="socid" class="form-control">
                        <option value="">-- Todas --</option>
                        <?php
                        foreach ($arr_societes as $id => $name) {
                            $selected = ($f_socid == $id) ? 'selected' : '';
                            echo "<option value='$id' $selected>$name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group"><label>Desde</label><input type="date" name="date_start" class="form-control" value="<?php echo $f_date_start; ?>"></div>
                <div class="form-group"><label>Hasta</label><input type="date" name="date_end" class="form-control" value="<?php echo $f_date_end; ?>"></div>
                <div class="form-group"><label>Estado</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="paid" <?php echo ($f_status == 'paid') ? 'selected' : ''; ?>>Pagadas</option>
                        <option value="unpaid" <?php echo ($f_status == 'unpaid') ? 'selected' : ''; ?>>Pendientes</option>
                    </select>
                </div>
                <button type="submit" class="btn-filter">Filtrar</button>
            </form>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Ref / Fecha</th>
                        <th>Comunidad</th>
                        <th>Importe</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Documento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resql && $db->num_rows($resql) > 0) {
                        while ($obj = $db->fetch_object($resql)) {

                            $status_class = 'status-draft';
                            $status_label = 'Borrador';
                            if ($obj->fk_statut == 1 && $obj->paye == 0) {
                                $status_class = 'status-unpaid';
                                $status_label = 'Pendiente';
                            }
                            if ($obj->paye == 1) {
                                $status_class = 'status-paid';
                                $status_label = 'Pagada';
                            }

                            echo '<tr>';
                            echo '<td><b style="color:#007bff;">' . $obj->ref . '</b><br><small style="color:#666;">' . date('d/m/Y', strtotime($obj->datef)) . '</small></td>';
                            echo '<td>' . $obj->comunidad_nombre . '</td>';
                            echo '<td><b>' . number_format($obj->total_ttc, 2, ',', '.') . ' €</b></td>';
                            echo '<td><span class="status-badge ' . $status_class . '">' . $status_label . '</span></td>';

                            // ENLACE SEGURO
                            $token = portal_encrypt($obj->rowid);
                            echo '<td style="text-align: right;"><a href="download.php?type=invoice&token=' . $token . '" class="btn-pdf" target="_blank"><i class="fa fa-file-pdf"></i> PDF</a></td>';

                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding: 30px;">No se encontraron facturas.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>