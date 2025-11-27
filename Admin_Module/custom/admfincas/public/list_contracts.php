<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - LISTADO DE CONTRATOS (V. FINAL)
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
require_once 'functions.php'; // Vital para encriptar el ID del PDF

$my_admin_id = $_SESSION['portal_id'];

// 3. FILTROS
$f_socid  = GETPOST('socid', 'int');
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

// 5. CONSULTA CONTRATOS (CORREGIDA 1:N)
$sql = "SELECT c.rowid, c.ref, c.date_contrat, c.statut, c.total_ttc, s.nom as comunidad_nombre ";
$sql .= "FROM " . MAIN_DB_PREFIX . "contrat as c ";
$sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON c.fk_soc = s.rowid ";
// JOIN ELIMINADO
$sql .= "WHERE s.fk_admfinca = " . $my_admin_id . " ";

if ($f_socid > 0) $sql .= " AND c.fk_soc = " . $f_socid;
if ($f_status != '') $sql .= " AND c.statut = " . $f_status;

$sql .= " ORDER BY c.date_contrat DESC";
$resql = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Contratos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- ESTILOS LIMPIOS (El resto viene de navbar.php/style.php) -->
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

        .status-draft {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-valid {
            background: #d4edda;
            color: #155724;
        }

        .status-closed {
            background: #cce5ff;
            color: #004085;
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

    <!-- BARRA NAVEGACIÓN -->
    <?php include 'navbar.php'; ?>

    <div class="container">

        <div class="header-page">
            <h2><i class="fa fa-file-contract"></i> Contratos</h2>
            <a href="dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <div class="filters-card">
            <form method="GET" class="filters-form">
                <div class="form-group" style="min-width: 250px;">
                    <label style="display:block; font-size:0.8rem; margin-bottom:5px;">Comunidad</label>
                    <select name="socid" class="form-control">
                        <option value="">-- Todas --</option>
                        <?php foreach ($arr_societes as $id => $name) echo "<option value='$id' " . ($f_socid == $id ? 'selected' : '') . ">$name</option>"; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:block; font-size:0.8rem; margin-bottom:5px;">Estado</label>
                    <select name="status" class="form-control">
                        <option value="">-- Todos --</option>
                        <option value="0" <?php if ($f_status == '0') echo 'selected'; ?>>Borrador</option>
                        <option value="1" <?php if ($f_status == '1') echo 'selected'; ?>>Activo</option>
                        <option value="2" <?php if ($f_status == '2') echo 'selected'; ?>>Cerrado</option>
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
                        <th style="text-align:right;">Documento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resql && $db->num_rows($resql) > 0) {
                        while ($obj = $db->fetch_object($resql)) {
                            $st_class = 'status-draft';
                            $st_label = 'Borrador';
                            if ($obj->statut == 1) {
                                $st_class = 'status-valid';
                                $st_label = 'Activo';
                            }
                            if ($obj->statut == 2) {
                                $st_class = 'status-closed';
                                $st_label = 'Cerrado';
                            }

                            echo '<tr>';
                            echo '<td><b style="color:#001f3f;">' . $obj->ref . '</b><div style="font-size:0.85rem; color:#666;">' . date('d/m/Y', strtotime($obj->date_contrat)) . '</div></td>';
                            echo '<td>' . $obj->comunidad_nombre . '</td>';
                            echo '<td><b>' . number_format($obj->total_ttc, 2, ',', '.') . ' €</b></td>';
                            echo '<td><span class="status-badge ' . $st_class . '">' . $st_label . '</span></td>';

                            // ENLACE PDF ENCRIPTADO
                            $token = portal_encrypt($obj->rowid);
                            echo '<td style="text-align:right;"><a href="download.php?type=contract&token=' . $token . '" class="btn-pdf" target="_blank"><i class="fa fa-file-pdf"></i> PDF</a></td>';

                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; padding:30px;">No hay contratos.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>