<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - DASHBOARD (LÓGICA 1:N ACTUALIZADA)
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

$my_admin_id = $_SESSION['portal_id'];

// 3. CONSULTAS DE DATOS (KPIs ACTUALIZADOS 1:N)

// A. Comunidades (Directo en tabla societe)
$sql = "SELECT count(*) as total FROM " . MAIN_DB_PREFIX . "societe WHERE fk_admfinca = " . $my_admin_id;
$res = $db->query($sql);
$num_soc = ($res) ? $db->fetch_object($res)->total : 0;

// B. Facturas (Join con societe)
$sql = "SELECT count(*) as total FROM " . MAIN_DB_PREFIX . "facture as f JOIN " . MAIN_DB_PREFIX . "societe as s ON f.fk_soc = s.rowid WHERE s.fk_admfinca = " . $my_admin_id;
$res = $db->query($sql);
$num_fac = ($res) ? $db->fetch_object($res)->total : 0;

// C. Presupuestos
$sql = "SELECT count(*) as total FROM " . MAIN_DB_PREFIX . "propal as p JOIN " . MAIN_DB_PREFIX . "societe as s ON p.fk_soc = s.rowid WHERE s.fk_admfinca = " . $my_admin_id;
$res = $db->query($sql);
$num_prop = ($res) ? $db->fetch_object($res)->total : 0;

// D. Pedidos
$sql = "SELECT count(*) as total FROM " . MAIN_DB_PREFIX . "commande as c JOIN " . MAIN_DB_PREFIX . "societe as s ON c.fk_soc = s.rowid WHERE s.fk_admfinca = " . $my_admin_id;
$res = $db->query($sql);
$num_ord = ($res) ? $db->fetch_object($res)->total : 0;

// E. Proyectos
$sql = "SELECT count(*) as total FROM " . MAIN_DB_PREFIX . "projet as p JOIN " . MAIN_DB_PREFIX . "societe as s ON p.fk_soc = s.rowid WHERE s.fk_admfinca = " . $my_admin_id;
$res = $db->query($sql);
$num_proj = ($res) ? $db->fetch_object($res)->total : 0;

// F. Contratos
$sql = "SELECT count(*) as total FROM " . MAIN_DB_PREFIX . "contrat as c JOIN " . MAIN_DB_PREFIX . "societe as s ON c.fk_soc = s.rowid WHERE s.fk_admfinca = " . $my_admin_id;
$res = $db->query($sql);
$num_contr = ($res) ? $db->fetch_object($res)->total : 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Fincas</title>
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

        .welcome {
            margin-bottom: 30px;
            color: #555;
        }

        /* GRID KPI */
        .grid-kpi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.2s;
            border-left: 4px solid transparent;
        }

        .card:hover {
            transform: translateY(-5px);
            border-left-color: #555;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
            color: white;
        }

        .bg-blue {
            background-color: #007bff;
        }

        .bg-green {
            background-color: #28a745;
        }

        .bg-orange {
            background-color: #fd7e14;
        }

        .bg-teal {
            background-color: #20c997;
        }

        .bg-purple {
            background-color: #6f42c1;
        }

        .bg-navy {
            background-color: #001f3f;
        }

        .card-info h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }

        .card-info p {
            margin: 0;
            color: #777;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* MENÚ RÁPIDO */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
        }

        .menu-item {
            background: white;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            color: #555;
            border-radius: 8px;
            border: 1px solid #eee;
            transition: 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .menu-item:hover {
            background: #f8faff;
            transform: translateY(-2px);
        }

        .menu-item i {
            font-size: 28px;
            margin-bottom: 12px;
            display: block;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <div class="welcome">
            <h2>Panel de Control</h2>
            <p>Resumen de actividad.</p>
        </div>

        <div class="grid-kpi">
            <div class="card">
                <div class="card-icon bg-blue"><i class="fa fa-users"></i></div>
                <div class="card-info">
                    <h3><?php echo $num_soc; ?></h3>
                    <p>Comunidades</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-green"><i class="fa fa-file-invoice-dollar"></i></div>
                <div class="card-info">
                    <h3><?php echo $num_fac; ?></h3>
                    <p>Facturas</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-orange"><i class="fa fa-file-signature"></i></div>
                <div class="card-info">
                    <h3><?php echo $num_prop; ?></h3>
                    <p>Presupuestos</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-teal"><i class="fa fa-shopping-cart"></i></div>
                <div class="card-info">
                    <h3><?php echo $num_ord; ?></h3>
                    <p>Pedidos</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-purple"><i class="fa fa-hard-hat"></i></div>
                <div class="card-info">
                    <h3><?php echo $num_proj; ?></h3>
                    <p>Proyectos</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon bg-navy"><i class="fa fa-file-contract"></i></div>
                <div class="card-info">
                    <h3><?php echo $num_contr; ?></h3>
                    <p>Contratos</p>
                </div>
            </div>
        </div>

        <h3>Accesos Directos</h3>
        <div class="menu-grid">
            <a href="list_societes.php" class="menu-item"><i class="fa fa-building"></i><br>Mis Comunidades</a>
            <a href="list_invoices.php" class="menu-item"><i class="fa fa-file-invoice-dollar"></i><br>Facturas</a>
            <a href="list_proposals.php" class="menu-item"><i class="fa fa-file-signature"></i><br>Presupuestos</a>
            <a href="list_orders.php" class="menu-item"><i class="fa fa-shopping-cart"></i><br>Pedidos</a>
            <a href="list_projects.php" class="menu-item"><i class="fa fa-hard-hat"></i><br>Proyectos</a>
            <a href="list_contracts.php" class="menu-item"><i class="fa fa-file-contract"></i><br>Contratos</a>
        </div>

    </div>

</body>

</html>