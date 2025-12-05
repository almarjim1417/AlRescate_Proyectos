<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - LISTADO DE PROYECTOS (V. FINAL)
// --------------------------------------------------------------------

// 1. CARGA DE DOLIBARR
if (!defined('NOLOGIN')) define('NOLOGIN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', '1');
if (!defined('NOIPCHECK')) define('NOIPCHECK', '1');

$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Error de núcleo.");

// 2. SEGURIDAD
if (!session_id()) session_start();
if (!isset($_SESSION['portal_id']) || empty($_SESSION['portal_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'functions.php'; // Incluimos por consistencia

$my_admin_id = $_SESSION['portal_id'];
$f_socid = GETPOST('socid', 'int');

// 3. LISTA COMUNIDADES (Para el desplegable del filtro)
$arr_societes = array();
// Consulta corregida 1:N
$sql_list = "SELECT s.rowid, s.nom FROM " . MAIN_DB_PREFIX . "societe as s WHERE s.fk_admfinca = " . $my_admin_id . " ORDER BY s.nom ASC";

$res_list = $db->query($sql_list);
if ($res_list) {
    while ($row = $db->fetch_object($res_list)) {
        $arr_societes[$row->rowid] = $row->nom;
    }
}

// 4. CONSULTA PROYECTOS (Principal)
$sql = "SELECT p.rowid, p.ref, p.title, p.dateo, p.datee, p.fk_statut, s.nom as comunidad_nombre ";
$sql .= "FROM " . MAIN_DB_PREFIX . "projet as p ";
$sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON p.fk_soc = s.rowid ";
// Filtro directo 1:N (Correcto)
$sql .= "WHERE s.fk_admfinca = " . $my_admin_id . " ";

if ($f_socid > 0) $sql .= " AND p.fk_soc = " . $f_socid;

$sql .= " ORDER BY p.dateo DESC";
$resql = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Proyectos</title>
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

        @media (max-width: 768px) {
            .desktop-only {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- BARRA NAVEGACIÓN UNIFICADA -->
    <?php include 'navbar.php'; ?>

    <div class="container">

        <div class="header-page">
            <h2><i class="fa fa-hard-hat"></i> Proyectos</h2>
            <a href="dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>

        <!-- LISTADO -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Ref / Título</th>
                        <th>Comunidad</th>
                        <th class="desktop-only">Fechas</th>
                        <th style="text-align: right;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resql && $db->num_rows($resql) > 0) {
                        while ($obj = $db->fetch_object($resql)) {
                            $st_class = 'status-draft';
                            $st_label = 'Borrador';
                            if ($obj->fk_statut == 1) {
                                $st_class = 'status-valid';
                                $st_label = 'Validado';
                            }
                            if ($obj->fk_statut == 2) {
                                $st_class = 'status-closed';
                                $st_label = 'Cerrado';
                            }

                            echo '<tr>';
                            echo '<td><b style="color:#6f42c1;">' . $obj->ref . '</b><div style="color:#666; font-size:0.9rem;">' . dol_trunc($obj->title, 50) . '</div></td>';
                            echo '<td>' . $obj->comunidad_nombre . '</td>';

                            echo '<td class="desktop-only" style="font-size:0.85rem;">';
                            if ($obj->dateo) echo 'Inicio: ' . date('d/m/Y', strtotime($obj->dateo)) . '<br>';
                            if ($obj->datee) echo 'Fin: ' . date('d/m/Y', strtotime($obj->datee));
                            echo '</td>';

                            echo '<td style="text-align: right;"><span class="status-badge ' . $st_class . '">' . $st_label . '</span></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center; padding:30px;">No hay proyectos.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>