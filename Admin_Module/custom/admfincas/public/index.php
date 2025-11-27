<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - LOGIN (VERSIÓN FINAL & SEGURA)
// --------------------------------------------------------------------

// 1. CARGA MÍNIMA DE DOLIBARR
if (!defined('NOLOGIN')) define('NOLOGIN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', '1');
if (!defined('NOIPCHECK')) define('NOIPCHECK', '1');

$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Error: No se encuentra el núcleo de Dolibarr.");

$action = GETPOST('action', 'alpha');
$error = '';

// 2. LÓGICA DE LOGOUT (CERRAR SESIÓN)
if ($action == 'logout') {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit;
}

// 3. LÓGICA DE LOGIN
if ($action == 'login') {
    $u = GETPOST('username', 'alpha');
    $p = GETPOST('password', 'alpha');

    if ($u && $p) {
        // Búsqueda en tabla NATIVA (admfincas_admfinca)
        $sql = "SELECT rowid, portal_pass FROM " . MAIN_DB_PREFIX . "admfincas_admfinca ";
        $sql .= "WHERE portal_user = '" . $db->escape($u) . "'";

        $resql = $db->query($sql);

        if ($resql && $db->num_rows($resql) > 0) {
            $obj = $db->fetch_object($resql);

            if (password_verify($p, $obj->portal_pass)) {
                if (!session_id()) session_start();
                $_SESSION['portal_id'] = $obj->rowid; // Ahora usamos rowid directo
                $_SESSION['portal_user'] = $u;

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    } else {
        $error = "Introduzca usuario y contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administradores</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }

        .logo {
            font-size: 22px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            display: inline-block;
            padding-bottom: 10px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error-msg {
            background-color: #fee;
            color: #e74c3c;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #fcc;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo">Portal Administradores</div>
        <?php if ($error): ?><div class="error-msg"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="login">
            <input type="text" name="username" placeholder="Usuario" required autocomplete="off">
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Entrar</button>
        </form>

        <p style="margin-top: 20px; font-size: 12px; color: #999;">Al Rescate &copy; <?php echo date('Y'); ?></p>
    </div>
</body>

</html>