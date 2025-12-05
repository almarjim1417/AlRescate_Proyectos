<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - LISTADO DE COMUNIDADES (1:N)
// --------------------------------------------------------------------

if (!defined('NOLOGIN')) define('NOLOGIN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', '1');
if (!defined('NOIPCHECK')) define('NOIPCHECK', '1');

$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Error de núcleo.");

if (!session_id()) session_start();
if (!isset($_SESSION['portal_id'])) {
    header("Location: index.php");
    exit;
}

$my_admin_id = $_SESSION['portal_id'];
$search_text = GETPOST('s', 'alpha');

// CONSULTA CORREGIDA 1:N (Sin JOIN extra)
$sql = "SELECT s.rowid, s.nom as nombre, s.code_client, s.address, s.zip, s.town, s.email, s.phone, s.status ";
$sql .= "FROM " . MAIN_DB_PREFIX . "societe as s ";
$sql .= "WHERE s.fk_admfinca = " . $my_admin_id . " ";

if ($search_text) {
    $sql .= " AND (s.nom LIKE '%" . $db->escape($search_text) . "%' OR s.code_client LIKE '%" . $db->escape($search_text) . "%')";
}
$sql .= "ORDER BY s.nom ASC";
$resql = $db->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Comunidades</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            color: #333;
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

        .search-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 10px;
        }

        .form-control {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-search {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background-color: #f8f9fa;
            color: #666;
            font-weight: 600;
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .info-main {
            font-weight: 600;
            color: #007bff;
            font-size: 1.05rem;
            display: block;
        }

        .info-sub {
            font-size: 0.9rem;
            color: #666;
            margin-top: 4px;
            display: block;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: bold;
        }

        .badge-active {
            background: #d4edda;
            color: #155724;
        }

        .badge-closed {
            background: #e2e3e5;
            color: #383d41;
        }

        /* BOTONES DE ACCIÓN */
        .actions-grid {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.2s;
        }

        .btn-icon:hover {
            opacity: 0.8;
        }

        .btn-facturas {
            background-color: #28a745;
        }

        .btn-presupuestos {
            background-color: #fd7e14;
        }

        .btn-pedidos {
            background-color: #20c997;
        }

        .btn-proyectos {
            background-color: #6f42c1;
        }

        .btn-contratos {
            background-color: #001f3f;
        }

        @media (max-width: 768px) {
            .desktop-only {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="header-page">
            <h2><i class="fa fa-users"></i> Mis Comunidades</h2>
            <a href="dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
        <form method="GET" class="search-box">
            <input type="text" name="s" class="form-control" placeholder="Buscar por nombre o código..." value="<?php echo htmlspecialchars($search_text); ?>">
            <button type="submit" class="btn-search"><i class="fa fa-search"></i> Buscar</button>
        </form>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Comunidad</th>
                        <th class="desktop-only">Contacto</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones Rápidas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resql && $db->num_rows($resql) > 0) {
                        while ($obj = $db->fetch_object($resql)) {
                            $status_html = ($obj->status == 1) ? '<span class="badge badge-active">Activo</span>' : '<span class="badge badge-closed">Cerrado</span>';
                            echo '<tr>';
                            echo '<td><span class="info-main">' . $obj->nombre . '</span><span class="info-sub"><i class="fa fa-map-marker-alt"></i> ' . ($obj->address ? $obj->address : 'Sin dirección') . '</span></td>';
                            echo '<td class="desktop-only">';
                            if ($obj->email) echo '<div><i class="fa fa-envelope" style="width:20px; color:#666;"></i> ' . $obj->email . '</div>';
                            if ($obj->phone) echo '<div style="margin-top:5px;"><i class="fa fa-phone" style="width:20px; color:#666;"></i> ' . $obj->phone . '</div>';
                            echo '</td>';
                            echo '<td>' . $status_html . '</td>';
                            echo '<td style="text-align: right;"><div class="actions-grid">';
                            echo '<a href="list_invoices.php?socid=' . $obj->rowid . '" class="btn-icon btn-facturas" title="Ver Facturas"><i class="fa fa-file-invoice-dollar"></i></a>';
                            echo '<a href="list_proposals.php?socid=' . $obj->rowid . '" class="btn-icon btn-presupuestos" title="Ver Presupuestos"><i class="fa fa-file-signature"></i></a>';
                            echo '<a href="list_orders.php?socid=' . $obj->rowid . '" class="btn-icon btn-pedidos" title="Ver Pedidos"><i class="fa fa-shopping-cart"></i></a>';
                            echo '<a href="list_projects.php?socid=' . $obj->rowid . '" class="btn-icon btn-proyectos" title="Ver Proyectos"><i class="fa fa-hard-hat"></i></a>';
                            echo '<a href="list_contracts.php?socid=' . $obj->rowid . '" class="btn-icon btn-contratos" title="Ver Contratos"><i class="fa fa-file-contract"></i></a>';
                            echo '</div></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center; padding: 30px;">No hay comunidades.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>