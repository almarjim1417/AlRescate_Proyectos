<?php
// --- AÑADIDO: Forzamos que se muestren los errores de PHP ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN ---

// Define la variable para indicar que el archivo está siendo cargado.
define('ISLOADEDBYSTEELSHEET', true);

// 1. Incluir el archivo principal de Dolibarr
require '../../main.inc.php';

// 2. Cargar librerías manualmente
require_once '../../core/lib/functions.lib.php';
require_once '../../core/lib/security.lib.php';

// 3. Comprobar si la BD está disponible
if (!isset($db) || !is_object($db)) {
    die("Error: El objeto de base de datos de Dolibarr no está disponible.");
}

// Cargar los archivos de idioma
$langs->loadLangs(array("main", "orders"));

// --- CONSULTA SQL CORREGIDA (CON 'special_code = 4') ---
$sql = "
    SELECT
        f.rowid AS fk_facture,
        f.note_public AS table_note, -- 'MESA: XX'
        f.datec,
        f.tms AS last_update_time, 
        fd.rowid AS rowid_line,
        p.label AS product_name,
        fd.qty,
        fd.fk_product,     -- (Para Comensal)
        fd.fk_parent_line, -- (Para Variantes)
        fe.servicio
    FROM
        vol_facturedet AS fd
    JOIN
        vol_facture AS f ON fd.fk_facture = f.rowid
    JOIN
        vol_facturedet_extrafields AS fe ON fe.fk_object = fd.rowid
    LEFT JOIN 
        vol_product AS p ON fd.fk_product = p.rowid
    WHERE
        f.fk_statut = 0          -- Ticket abierto (Borrador)
        AND fd.special_code = 4    -- <-- ¡¡EL DISPARADOR!! (Enviado a Cocina)
        AND fe.servicio != 99      -- Plato PENDIENTE (Cualquier servicio MENOS 99)
        
        -- === FILTRO DE BEBIDAS ===
        AND (fd.fk_product IS NULL OR fd.fk_product NOT IN (
            SELECT fk_product 
            FROM vol_categorie_product
            WHERE fk_categorie IN (
                SELECT rowid FROM vol_categorie 
                WHERE rowid IN (673, 675, 659, 680) -- IDs Maestros
                OR fk_parent IN (673, 675, 659, 680) -- IDs Hijos
            )
        ))
        -- === FIN FILTRO DE BEBIDAS ===

    ORDER BY
        f.datec ASC, fd.rowid ASC -- Ordenado por ID de línea
";

$resql = $db->query($sql);
if (!$resql) {
    die("Error fatal en la consulta SQL: " . $db->lasterror());
}

$num = $db->num_rows($resql);
$error = $db->lasterror();
?>
<!DOCTYPE html>
<html lang="<?php echo $langs->defaultlang; ?>">

<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="15">
    <title>Monitor de Cocina KDS</title>
    <link rel="stylesheet" type="text/css" href="../../theme/<?php echo $conf->theme; ?>/style.css.php?v=<?php echo DOL_VERSION; ?>" />

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">

    <style>
        /* Estilos de la cocina (Naranja) */
        body {
            font-family: Arial, sans-serif;
            background-color: #e4e4e4ff;
            margin: 0;
            padding: 20px;
        }

        .kds-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start;
            /* Para que las tarjetas cortas no se estiren */
        }

        .comanda-card {
            background-color: #ffdeb9ff;
            border: 3px solid #ff9800;
            /* Borde Naranja */
            border-radius: 8px;
            padding: 15px;
            width: 300px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            cursor: grab;

            /* Scroll Interno Desactivado */
        }

        .comanda-card.ui-sortable-helper {
            cursor: grabbing;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            opacity: 0.9;
        }

        .comanda-placeholder {
            background-color: #f0e68c;
            border: 2px dashed #ccc;
            border-radius: 8px;
            width: 300px;
            height: 150px;
            visibility: visible !important;
        }

        .comanda-card h3 {
            color: #d32f2f;
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 5px;
            /* Espacio entre los elementos de la cabecera */
        }

        /* ESTILO PARA EL ICONO DE MESA */
        .table-icon-wrapper {
            background-color: #d32f2f;
            /* Mismo color rojo que el <h3> */
            color: #FFFFFF;
            /* Texto blanco */
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 1.1em;
            font-weight: bold;
            min-width: 60px;
            /* Ancho mínimo para que se vea bien */
            text-align: center;
            flex-shrink: 0;
            /* Evita que el icono se encoja */
        }


        .comanda-card h3 span.time {
            color: #555;
            font-size: 0.9em;
            font-weight: normal;
            flex-shrink: 0;
            /* Evita que la hora se encoja */
        }

        .comanda-card p {
            margin: 10px 0;
            font-size: 1.1em;
        }

        .comanda-card strong {
            font-size: 1.2em;
            color: #333;
        }

        /* ESTILO COMENSALES */
        .comanda-card h3 span.comensales {
            color: #0056b3;
            /* Azul */
            font-size: 0.9em;
            font-weight: bold;
            flex-grow: 1;
            /* Ocupa el espacio del medio */
            text-align: center;
            /* Centra el texto de comensales */
        }

        /* ESTILO HORA ACTUALIZACIÓN */
        .update-time {
            font-size: 0.9em;
            color: #d32f2f;
            /* Rojo, para que llame la atención */
            font-weight: bold;
            text-align: right;
            margin-bottom: 10px;
        }

        /* ESTILO PARA EL SEPARADOR HR */
        .header-divider {
            border: none;
            border-top: 1px solid #9e9e9eff;
            /* Gris más visible */
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .servicio-separator {
            background-color: #4A5568;
            /* Gris-azulado oscuro */
            color: #FFFFFF;
            /* Texto blanco */
            font-weight: bold;
            font-size: 0.9em;
            padding: 6px 10px;
            border-radius: 4px;
            margin-top: 80px;
            /* Tu margen personalizado */
            margin-bottom: 20px;
            /* Tu margen personalizado */
            text-transform: uppercase;
            border-top: none;
        }

        .comanda-card h4:first-of-type {
            margin-top: 10px;
        }

        /* ESTILO PARA LAS VARIANTES */
        .variant-line {
            margin-top: -5px;
            margin-left: 20px;
            font-size: 1.0em;
            color: #555;
            font-style: italic;
        }

        .variant-line strong {
            font-size: 1.0em;
            color: #555;
        }

        .btn-listo {
            display: block;
            margin-top: 10px;
            padding: 10px;
            background-color: #48bd4eff;
            /* Verde */
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            width: 100%;
            cursor: pointer;
            box-sizing: border-box;
        }

        .partial-form {
            margin-top: 10px;
        }

        .quantity-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .quantity-buttons button {
            flex-grow: 1;
            padding: 10px 5px;
            background-color: #ff9100ff;
            /* Azul (parcial) */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
        }

        .quantity-buttons button[name="action_send_all"] {
            background-color: #7c4200ff;
            /* Rojo (total) */
        }
    </style>
</head>

<body>

    <div class="kds-container">

        <?php
        if ($num > 0) {
            $tickets = [];
            while ($obj = $db->fetch_object($resql)) {
                $tickets[$obj->fk_facture]['datec'] = $obj->datec;
                $tickets[$obj->fk_facture]['last_update_time'] = $obj->last_update_time;
                $tickets[$obj->fk_facture]['table_note'] = $obj->table_note;
                $tickets[$obj->fk_facture]['lines'][] = $obj;
            }

            if (!empty($_SESSION['kds_order'])) {
                $saved_order = $_SESSION['kds_order'];
                $sorted_tickets = [];
                foreach ($saved_order as $facture_id) {
                    if (isset($tickets[$facture_id])) {
                        $sorted_tickets[$facture_id] = $tickets[$facture_id];
                        unset($tickets[$facture_id]);
                    }
                }
                $tickets = $sorted_tickets + $tickets;
            }

            $servicio_map = array(
                5 => 'PARA LLEVAR',
                1 => 'ENTRANTES',
                2 => 'PRIMEROS',
                3 => 'SEGUNDOS',
                4 => 'POSTRES',
                0 => 'NO DEFINIDO'
            );
            $print_order = array(5, 1, 2, 3, 4, 0);

            foreach ($tickets as $facture_id => $data) {

                // --- ¡¡INICIO DE LA CORRECCIÓN DE COMANDA VACÍA!! ---
                $comensales_qty = 0;
                $parent_lines_by_service = [];
                foreach ($servicio_map as $key => $value) {
                    $parent_lines_by_service[$key] = [];
                }
                $processed_lines = [];
                $child_lines = [];
                $food_lines_count = 0;

                // 1. Primera pasada
                foreach ($data['lines'] as $line) {
                    if ($line->fk_product == 3669) { // Es Comensal
                        $comensales_qty = $line->qty;
                    } elseif (empty($line->fk_parent_line) || $line->fk_parent_line == 0) { // Es Padre
                        $line->variants = [];
                        $processed_lines[$line->rowid_line] = $line;
                        $food_lines_count++;
                    } else { // Es Hijo
                        $child_lines[] = $line;
                        $food_lines_count++;
                    }
                }

                if ($food_lines_count == 0) {
                    continue;
                }

                // 2. Segunda pasada
                foreach ($child_lines as $child) {
                    if (isset($processed_lines[$child->fk_parent_line])) {
                        $processed_lines[$child->fk_parent_line]->variants[] = $child;
                    }
                }

                // 3. Tercera pasada
                foreach ($processed_lines as $line) {
                    $servicio_id = (int) $line->servicio;
                    if (!array_key_exists($servicio_id, $parent_lines_by_service)) {
                        $servicio_id = 0;
                    }
                    $parent_lines_by_service[$servicio_id][] = $line;
                }
                // --- FIN DE LA LÓGICA DE AGRUPACIÓN ---

                echo '<div class="comanda-card" data-facture-id="' . $facture_id . '">';

                $table_name = $data['table_note'];
                $table_name = str_ireplace('MESA: ', '', $table_name);
                $timestamp_creacion = strtotime($data['datec']);

                // --- CABECERA ACTUALIZADA (CON ICONO DE MESA) ---
                echo '<h3>';
                echo '<div class="table-icon-wrapper">' . $table_name . '</div>';

                if ($comensales_qty > 0) {
                    echo '<span class="comensales">' . (int)$comensales_qty . ' COMENSALES</span>';
                } else {
                    echo '<span class="comensales"></span>';
                }

                echo '<span class="time">' . date('H:i', $timestamp_creacion) . '</span>';
                echo '</h3>';
                // --- FIN CABECERA ---

                // --- MOSTRAR HORA DE ACTUALIZACIÓN ---
                $timestamp_update = strtotime($data['last_update_time']);
                if (($timestamp_update - $timestamp_creacion) > 60) {
                    echo '<div class="update-time">Últ. Act: ' . date('H:i', $timestamp_update) . '</div>';
                }

                // --- SEPARADOR HR ---
                echo '<hr class="header-divider">';

                $token = newToken();

                // 4. Imprimimos las cubetas
                foreach ($print_order as $servicio_id) {

                    if (!empty($parent_lines_by_service[$servicio_id])) {

                        $lines_in_group = $parent_lines_by_service[$servicio_id];

                        echo '<h4 class="servicio-separator">' . $servicio_map[$servicio_id] . '</h4>';

                        foreach ($lines_in_group as $line) { // $line es ahora un PADRE
                            echo '<p>';
                            echo '<strong>' . $line->qty . 'x ' . $line->product_name . '</strong>';
                            echo '</p>';

                            if (!empty($line->variants)) {
                                foreach ($line->variants as $variant) {
                                    echo '<p class="variant-line">';
                                    echo '<strong>- ' . $variant->product_name . '</strong>';
                                    echo '</p>';
                                }
                            }

                            $all_line_ids = [$line->rowid_line];
                            foreach ($line->variants as $variant) {
                                $all_line_ids[] = $variant->rowid_line;
                            }

                            if ($line->qty > 1) {
                                echo '<form action="kds_action.php" method="POST" class="partial-form">';
                                echo '<input type="hidden" name="token" value="' . $token . '">';
                                echo '<input type="hidden" name="line_id_partial" value="' . $line->rowid_line . '">';

                                // --- ¡¡AÑADIDO!! Enviamos el servicio actual ---
                                echo '<input type="hidden" name="current_servicio" value="' . $line->servicio . '">';

                                foreach ($all_line_ids as $id) {
                                    echo '<input type="hidden" name="line_ids[]" value="' . $id . '">';
                                }
                                echo '<div class="quantity-buttons">';
                                for ($i = 1; $i < $line->qty; $i++) {
                                    echo '<button type="submit" name="action_partial_qty" value="' . $i . '">' . $i . '</button>';
                                }
                                echo '<button type="submit" name="action_send_all" value="1">' . $line->qty . '</button>';
                                echo '</div>';
                                echo '</form>';
                            } else {
                                $url_params = 'send_all=1&token=' . $token;
                                foreach ($all_line_ids as $id) {
                                    $url_params .= '&line_ids[]=' . $id;
                                }
                                echo '<a class="btn-listo" href="kds_action.php?' . $url_params . '">Plato Listo</a>';
                            }
                        }
                    } // Fin del if (!empty)
                }

                echo '</div>'; // Cierra el contenedor de la comanda
            }
        } else {
            echo '<p style="color: green;">✅ No hay comandas pendientes.</p>';
        }

        if ($error) {
            echo '<p style."color: red;">Error en la consulta: ' . $error . '</p>';
        }
        ?>
    </div>

    <div id="scroll-indicator">HAY MÁS COMANDAS ABAJO &darr;</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function() {
            var dolibarr_token = "<?php echo newToken(); ?>";

            // --- SCRIPT DE INDICADOR DE SCROLL ---
            var $indicator = $("#scroll-indicator");
            var $window = $(window);

            function checkScroll() {
                var docHeight = $(document).height();
                var windowHeight = $window.height();
                var scrollTop = $window.scrollTop();
                if (docHeight > windowHeight) {
                    if ((windowHeight + scrollTop) < (docHeight - 30)) {
                        $indicator.fadeIn();
                    } else {
                        $indicator.fadeOut();
                    }
                } else {
                    $indicator.fadeOut();
                }
            }
            checkScroll();
            $window.on('scroll', checkScroll);

            // Script de "Arrastrar y Soltar"
            $(".kds-container").sortable({
                placeholder: "comanda-placeholder",
                opacity: 0.8,
                cancel: ".btn-listo, .partial-form, .quantity-buttons, button, .servicio-separator, .variant-line",
                tolerance: "pointer",
                stop: function(event, ui) {
                    var newOrder = $(this).sortable('toArray', {
                        attribute: 'data-facture-id'
                    });
                    $.post('kds_save_order.php', {
                        order: newOrder,
                        token: dolibarr_token
                    }, function(response) {
                        console.log('Orden guardado:', response);
                    });
                }
            });
        });
    </script>

</body>

</html>
<?php
$db->close();
?>