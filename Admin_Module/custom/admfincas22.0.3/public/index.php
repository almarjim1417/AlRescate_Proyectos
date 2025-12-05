<?php
// --------------------------------------------------------------------
// PORTAL EXTERNO - LOGIN CORPORATIVO (LOGO + COLORES)
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

// 2. LÓGICA DE LOGOUT
if ($action == 'logout') {
    if (!session_id()) session_start();
    session_destroy();
    header("Location: index.php");
    exit;
}

// 3. LÓGICA DE LOGIN
if ($action == 'login') {
    $u = GETPOST('username', 'alpha');
    $p = GETPOST('password', 'alpha');

    if ($u && $p) {
        $sql = "SELECT rowid, portal_pass FROM " . MAIN_DB_PREFIX . "admfincas_admfinca ";
        $sql .= "WHERE portal_user = '" . $db->escape($u) . "'";
        $resql = $db->query($sql);

        if ($resql && $db->num_rows($resql) > 0) {
            $obj = $db->fetch_object($resql);
            if (password_verify($p, $obj->portal_pass)) {
                if (!session_id()) session_start();
                $_SESSION['portal_id'] = $obj->rowid;
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
        $error = "Introduzca sus credenciales.";
    }
}

// 4. LÓGICA DE DISEÑO (COLOR Y LOGO)

// A) Logo
$logo_url = '';
if (!empty($conf->global->MAIN_INFO_SOCIETE_LOGO)) {
    $logo_file = $conf->global->MAIN_INFO_SOCIETE_LOGO;
    $logo_url = DOL_URL_ROOT . '/viewimage.php?modulepart=mycompany&file=logos/' . urlencode($logo_file);
}

// B) Color Corporativo (Lógica Robusta)
$default_color = '#2c3e50';
$brand_color = $default_color;
$raw_color_db = '';

// Consultamos directamente a la tabla de configuración para asegurar el dato
if (is_object($db)) {
    $sql_theme = "SELECT value FROM " . MAIN_DB_PREFIX . "const ";
    $sql_theme .= "WHERE name = 'THEME_ELDY_TOPMENU_BACK1' ";
    $sql_theme .= "AND entity IN (0, " . ((int)$conf->entity) . ") ";
    $sql_theme .= "ORDER BY entity DESC LIMIT 1";

    $res_theme = $db->query($sql_theme);
    if ($res_theme && $db->num_rows($res_theme) > 0) {
        $obj_theme = $db->fetch_object($res_theme);
        $raw_color_db = trim($obj_theme->value);
    }
}

// Procesamos el color (RGB o Hex)
if (!empty($raw_color_db)) {
    if (strpos($raw_color_db, ',') !== false && strpos($raw_color_db, 'rgb') === false) {
        $brand_color = 'rgb(' . $raw_color_db . ')';
    } elseif (strpos($raw_color_db, '#') === false && strpos($raw_color_db, 'rgb') === false) {
        $brand_color = '#' . $raw_color_db;
    } else {
        $brand_color = $raw_color_db;
    }
    // Evitar blancos que harían invisible el texto/botones
    if (strpos($brand_color, '255,255,255') !== false || strtolower($brand_color) == '#ffffff' || strtolower($brand_color) == '#fff') {
        $brand_color = $default_color;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Clientes</title>

    <?php if ($logo_url): ?>
        <link rel="icon" type="image/png" href="<?php echo $logo_url; ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        /* VARIABLES DINÁMICAS */
        :root {
            --brand-color: <?php echo $brand_color; ?>;
        }

        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            /* El fondo es un degradado desde TU color hacia un tono oscuro neutro */
            background: linear-gradient(135deg, var(--brand-color) 0%, #1a252f 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            text-align: center;
            position: relative;
        }

        .logo-img {
            max-height: 80px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        h2 {
            color: #333;
            font-weight: 400;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
            transition: border-color 0.3s;
            background-color: #fdfdfd;
        }

        /* Borde del input al hacer foco con tu color */
        .form-control:focus {
            border-color: var(--brand-color);
            outline: none;
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }

        /* Botón con tu color */
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--brand-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: filter 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            filter: brightness(0.85);
            /* Oscurece un poco al pasar el ratón */
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #f5c6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-copy {
            margin-top: 30px;
            color: #999;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>

    <div class="login-container">

        <?php if ($logo_url): ?>
            <img src="<?php echo $logo_url; ?>" alt="Logo Empresa" class="logo-img">
        <?php else: ?>
            <div style="font-size: 3rem; color: var(--brand-color); margin-bottom: 10px;">
                <i class="fa fa-building"></i>
            </div>
        <?php endif; ?>

        <h2>Área de Gestión</h2>

        <?php if ($error): ?>
            <div class="alert">
                <i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="login">

            <div class="input-group">
                <i class="fa fa-user"></i>
                <input type="text" name="username" class="form-control" placeholder="Usuario" required autocomplete="username">
            </div>

            <div class="input-group">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión <i class="fa fa-arrow-right" style="margin-left:5px;"></i></button>
        </form>

        <div class="footer-copy">
            &copy; <?php echo date('Y'); ?> - Acceso Seguro
        </div>
    </div>

</body>

</html>