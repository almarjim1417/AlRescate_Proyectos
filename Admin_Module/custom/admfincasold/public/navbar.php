<?php
// --------------------------------------------------------------------
// BARRA DE NAVEGACIÓN COMÚN (Navbar + Color + Logo + Favicon JS)
// --------------------------------------------------------------------

// 1. CÁLCULO DEL COLOR
$default_color = '#2c3e50';
$brand_color = $default_color;
$raw_color_db = '';

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

if (!empty($raw_color_db)) {
    if (strpos($raw_color_db, ',') !== false && strpos($raw_color_db, 'rgb') === false) {
        $brand_color = 'rgb(' . $raw_color_db . ')';
    } elseif (strpos($raw_color_db, '#') === false && strpos($raw_color_db, 'rgb') === false) {
        $brand_color = '#' . $raw_color_db;
    } else {
        $brand_color = $raw_color_db;
    }
    if (strpos($brand_color, '255,255,255') !== false || strtolower($brand_color) == '#ffffff' || strtolower($brand_color) == '#fff') {
        $brand_color = $default_color;
    }
}

if (!isset($my_user_name) && isset($_SESSION['portal_user'])) {
    $my_user_name = $_SESSION['portal_user'];
}

// 2. LÓGICA DEL LOGO
$logo_url = '';
if (!empty($conf->global->MAIN_INFO_SOCIETE_LOGO)) {
    $logo_file = $conf->global->MAIN_INFO_SOCIETE_LOGO;
    $logo_url = DOL_URL_ROOT . '/viewimage.php?modulepart=mycompany&file=logos/' . urlencode($logo_file);
}
?>

<?php if ($logo_url): ?>
    <script>
        (function() {
            var link = document.querySelector("link[rel~='icon']");
            if (!link) {
                link = document.createElement('link');
                link.rel = 'icon';
                document.head.appendChild(link);
            }
            link.href = '<?php echo $logo_url; ?>';
        })();
    </script>
<?php endif; ?>


<style>
    .global-navbar {
        background-color: <?php echo $brand_color; ?> !important;
        color: white;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
    }

    .global-navbar-brand {
        color: white !important;
        text-decoration: none;
        font-size: 1.2rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .global-navbar-brand:hover {
        opacity: 0.9;
    }

    .brand-logo-img {
        max-height: 40px;
        width: auto;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.1);
        padding: 2px;
    }

    .global-logout-btn {
        color: rgba(255, 255, 255, 0.9) !important;
        text-decoration: none;
        font-size: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.5);
        padding: 5px 10px;
        border-radius: 4px;
        transition: 0.3s;
    }

    .global-logout-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white !important;
    }
</style>

<div class="global-navbar">
    <a href="dashboard.php" class="global-navbar-brand">
        <?php if ($logo_url): ?>
            <img src="<?php echo $logo_url; ?>" alt="Logo" class="brand-logo-img">
            <span>Portal Gestor</span>
        <?php else: ?>
            <i class="fa fa-building"></i> Portal Gestor
        <?php endif; ?>
    </a>

    <div style="display:flex; align-items:center;">
        <span style="margin-right: 15px; font-size: 0.9rem;">Hola, <?php echo htmlspecialchars($my_user_name); ?></span>
        <a href="index.php?action=logout" class="global-logout-btn">
            <i class="fa fa-sign-out-alt"></i> Salir
        </a>
    </div>
</div>