<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - LISTADO DE PRESUPUESTOS (V. FINAL)
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

// 3. FILTROS
$f_socid = GETPOST('socid', 'int');
$f_status = GETPOST('status', 'alpha');

// 4. LISTA COMUNIDADES (CORREGIDA 1:N)
$arr_societes = array();
// Buscamos directamente en la tabla societe usando la columna nueva fk_admfinca
$sql_list = "SELECT s.rowid, s.nom FROM " . MAIN_DB_PREFIX . "societe as s WHERE s.fk_admfinca = " . $my_admin_id . " ORDER BY s.nom ASC";

$res_list = $db->query($sql_list);
if ($res_list) {
    while ($row = $db->fetch_object($res_list)) {
        $arr_societes[$row->rowid] = $row->nom;
    }
}

// 5. CONSULTA PRESUPUESTOS (CORREGIDA 1:N)
$sql = "SELECT p.rowid, p.ref, p.datep, p.total_ttc, p.fk_statut, s.nom as comunidad_nombre ";
$sql .= "FROM " . MAIN_DB_PREFIX . "propal as p ";
$sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON p.fk_soc = s.rowid ";
$sql .= "WHERE s.fk_admfinca = " . $my_admin_id . " ";

if ($f_socid > 0) $sql .= " AND p.fk_soc = " . $f_socid;
if ($f_status != '') $sql .= " AND p.fk_statut = " . $f_status;

$sql .= " ORDER BY p.datep DESC";
$resql = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Presupuestos</title>
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
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .form-control {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 200px;
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

        .status-0 {
            background: #e2e3e5;
            color: #383d41;
        }

        /* Borrador */
        .status-1 {
            background: #fff3cd;
            color: #856404;
        }

        /* Abierto */
        .status-2 {
            background: #d4edda;
            color: #155724;
        }

        /* Firmado */
        .status-3 {
            background: #f8d7da;
            color: #721c24;
        }

        /* Rechazado */
        .status-4 {
            background: #cce5ff;
            color: #004085;
        }

        /* Facturado */

        .btn-pdf {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <div class="header-page">
            <h2><i class="fa fa-file-signature"></i> Presupuestos</h2>
            <a href="dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="filters-card">
            <form class="filters-form" method="GET">
                <div>
                    <label style="display:block; font-size:0.8rem; margin-bottom:5px;">Comunidad</label>
                    <select name="socid" class="form-control">
                        <option value="">-- Todas --</option>
                        <?php foreach ($arr_societes as $id => $name) echo "<option value='$id' " . ($f_socid == $id ? 'selected' : '') . ">$name</option>"; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.8rem; margin-bottom:5px;">Estado</label>
                    <select name="status" class="form-control">
                        <option value="">-- Todos --</option>
                        <option value="1" <?php if ($f_status == '1') echo 'selected'; ?>>Abierto</option>
                        <option value="2" <?php if ($f_status == '2') echo 'selected'; ?>>Firmado</option>
                        <option value="3" <?php if ($f_status == '3') echo 'selected'; ?>>Rechazado</option>
                    </select>
                </div>
                <button type="submit" class="btn-filter"><i class="fa fa-search"></i> Filtrar</button>
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
                        <th style="text-align:right;">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $labels = [0 => 'Borrador', 1 => 'Abierto', 2 => 'Firmado', 3 => 'Rechazado', 4 => 'Facturado'];
                    if ($resql && $db->num_rows($resql) > 0) {
                        while ($obj = $db->fetch_object($resql)) {
                            $st_label = isset($labels[$obj->fk_statut]) ? $labels[$obj->fk_statut] : 'Desc.';
                            echo '<tr>';
                            echo '<td><b style="color:#007bff;">' . $obj->ref . '</b><div style="font-size:0.85rem; color:#666;">' . date('d/m/Y', strtotime($obj->datep)) . '</div></td>';
                            echo '<td>' . $obj->comunidad_nombre . '</td>';
                            echo '<td><b>' . number_format($obj->total_ttc, 2, ',', '.') . ' €</b></td>';
                            echo '<td><span class="status-badge status-' . $obj->fk_statut . '">' . $st_label . '</span></td>';

                            // ENCRIPTADO
                            $token = portal_encrypt($obj->rowid);
                            echo '<td style="text-align:right;"><a href="download.php?type=proposal&token=' . $token . '" class="btn-pdf" target="_blank"><i class="fa fa-file-pdf"></i> PDF</a></td>';

                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding:30px;">No hay presupuestos.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>