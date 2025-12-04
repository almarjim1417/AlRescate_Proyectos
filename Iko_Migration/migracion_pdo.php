<?php
// ==============================================================================
// MIGRACIÓN DOLIBARR v40.0 (FULL FIELDS + CONTACTO)
// ==============================================================================
// - Inserción de Teléfono y Email en la ficha del Sitio.
// - Inserción de Coordenadas GEO y Porcentajes en Extrafields del Sitio.
// - Ficha de Evento con las 29 columnas del Excel mapeadas.
// ==============================================================================

set_time_limit(0);
ini_set('memory_limit', '1024M');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) {
    ob_end_flush();
}
ob_implicit_flush(1);

$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'dol_ikonik';

$files_sites = [
    'sites_2015.csv',
    'sites_2016.csv',
    'sites_2017.csv',
    'sites_2018.csv',
    'sites_2019.csv',
    'sites_2020.csv',
    'sites_2021.csv'
];
$file_projects = 'import_proyectos.csv';
$id_user_creat = 1;
$secuencia_actual = 1774;
$sufijo_anual = "-25";

// --- MAPEO COMPLETO DE LAS 29 COLUMNAS ---
$etiquetas_excel = [
    0 => 'Nombre Proyecto',
    1 => 'Photo',
    2 => 'Nombre Marketing',
    3 => 'Ciudad',
    4 => 'PLZ (CP)',
    5 => 'Straße (Dirección)',
    6 => 'Standortart (Tipo)',
    7 => 'Propietario',
    8 => 'Persona Contacto',
    9 => 'Teléfono',
    10 => 'Email',
    11 => 'Ratecard',
    12 => 'Media',
    13 => 'Producción',
    14 => 'Forecasted Sales',
    15 => 'Gross Profit',
    16 => 'Magic 7',
    17 => 'Fecha Creación',
    18 => 'Último Cambio',
    19 => 'Estado',
    20 => 'Probabilidad',
    21 => 'Location Completion Chance',
    22 => 'Location Approval Chance',
    23 => 'Montaje',
    24 => 'Demontaje',
    25 => 'Resp. BU',
    26 => 'Note',
    27 => 'GEO Width (Lat)',
    28 => 'GEO Length (Long)'
];

echo "<h1>Migración v40.0 (Integridad Total)</h1>";

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "<p style='color:green'>Conexión OK.</p>";
} catch (\PDOException $e) {
    die("<h3 style='color:red'>Error BD: " . $e->getMessage() . "</h3>");
}

// --- FUNCIONES ---
function limpiar_moneda($valor)
{
    if (empty($valor)) return 0;
    $valor = str_replace(['€', ' '], '', $valor);
    if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif (strpos($valor, ',') !== false) {
        $valor = str_replace(',', '.', $valor);
    }
    return (float)$valor;
}
function limpiar_fecha_excel($fecha_str)
{
    if (empty($fecha_str)) return date('Y-m-d H:i:s');
    $time = strtotime(str_replace('/', '-', $fecha_str));
    return ($time) ? date('Y-m-d H:i:s', $time) : date('Y-m-d H:i:s');
}
function normalizar_texto($texto)
{
    if (empty($texto)) return "";
    $texto = mb_convert_encoding($texto, 'UTF-8', 'auto');
    $acentos = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'ñ' => 'n', 'Ñ' => 'n', 'ç' => 'c', 'Ç' => 'c'];
    $texto = strtr($texto, $acentos);
    return trim(mb_strtolower($texto, 'UTF-8'));
}
function get_status_site($texto)
{
    return (stripos($texto, 'Inactivo') !== false) ? 0 : 1;
}
function get_estado_proyecto($texto)
{
    $t = mb_strtolower(trim($texto), 'UTF-8');
    if (strpos($t, 'contrato firmado') !== false) return 1;
    if (strpos($t, 'oferta producida') !== false) return 1;
    if (strpos($t, 'en curso') !== false) return 1;
    if (strpos($t, 'reservado') !== false) return 1;
    if (strpos($t, 'perdido') !== false) return 2;
    if (strpos($t, 'competencia') !== false) return 2;
    if (strpos($t, 'interrumpida') !== false) return 2;
    if (strpos($t, 'cancelado') !== false) return 2;
    if (strpos($t, 'rechazado') !== false) return 2;
    return 0;
}
function buscar_mejor_coincidencia($nombre_buscado, $lista_bd)
{
    $mejor_id = null;
    $mayor_similitud = 0;
    $nombre_encontrado = "";
    $buscado_norm = normalizar_texto($nombre_buscado);
    foreach ($lista_bd as $nombre_bd_norm => $datos) {
        similar_text($buscado_norm, $nombre_bd_norm, $porcentaje);
        if ($porcentaje > $mayor_similitud) {
            $mayor_similitud = $porcentaje;
            $mejor_id = $datos['id'];
            $nombre_encontrado = $datos['nombre_real'];
        }
    }
    if ($mayor_similitud > 85) return ['id' => $mejor_id, 'nombre' => $nombre_encontrado];
    return null;
}

// PASO 0: CARGA MEMORIA
echo "<h3>Paso 0: Cargando memoria...</h3>";
flush();
$mapa_sitios = [];
$stmt = $pdo->query("SELECT rowid, lastname FROM llx_socpeople");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $mapa_sitios[normalizar_texto($row['lastname'])] = ['id' => $row['rowid'], 'nombre_real' => $row['lastname']];

$mapa_proyectos_titulos = [];
$stmt = $pdo->query("SELECT title FROM llx_projet");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $mapa_proyectos_titulos[normalizar_texto($row['title'])] = true;

$mapa_clientes = [];
$stmt = $pdo->query("SELECT rowid, nom FROM llx_societe");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $mapa_clientes[normalizar_texto($row['nom'])] = ['id' => $row['rowid'], 'nombre_real' => $row['nom']];
echo "<p>Cargado.</p>";

// PASO 2: PROCESO
echo "<h3>Paso 2: Procesando Proyectos...</h3>";
echo "<div style='border:1px solid #ccc; padding:10px; max-height:400px; overflow-y:scroll;'>";

if (file_exists($file_projects)) {
    // PREPARAR SENTENCIAS
    $stmt_new_client = $pdo->prepare("INSERT INTO llx_societe (nom, client, datec, fk_user_creat, entity, status) VALUES (?, 1, NOW(), ?, 1, 1)");

    // CORRECCIÓN SITIOS: AÑADIDOS TELÉFONO Y EMAIL
    $stmt_new_site = $pdo->prepare("INSERT INTO llx_socpeople (datec, ref_ext, lastname, address, zip, town, phone, email, statut, note_private, fk_user_creat, entity) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, 1)");

    // CORRECCIÓN EXTRAS SITIO: AÑADIDOS LAT, LONG, CHANCES
    $stmt_site_extra = $pdo->prepare("INSERT INTO llx_socpeople_extrafields (fk_object, nom_marketing, fechamontaje, fechadesmontaje, lat, long_, location_completion_chance, probabilidaobtencionlicencia, photo_nombre) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // UPDATE SITIO
    $stmt_update_site_link = $pdo->prepare("UPDATE llx_socpeople_extrafields SET fk_projet = ?, templazamiento = ? WHERE fk_object = ?");
    // UPDATE SITIO DATOS (Si ya existía, le actualizamos el contacto)
    $stmt_update_site_contact = $pdo->prepare("UPDATE llx_socpeople SET phone = ?, email = ?, address = ?, zip = ?, town = ? WHERE rowid = ?");

    $stmt_proj = $pdo->prepare("INSERT INTO llx_projet (ref, title, fk_soc, dateo, datee, datec, note_public, description, fk_statut, opp_percent, fk_user_creat, entity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt_proj_extra = $pdo->prepare("INSERT INTO llx_projet_extrafields (fk_object, tipo, fk_emplazamiento, estado, estado_comercial, last_contact) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_money = $pdo->prepare("INSERT INTO presupuestos_indicadores (fk_emplazamiento, venta_prevista_vs, coste_previsto_vs, venta_presupuestada_vpr, costes_en_presupuesto_gpr) VALUES (?, ?, ?, ?, ?)");
    $stmt_event = $pdo->prepare("INSERT INTO llx_actioncomm (ref, ref_ext, label, datep, datep2, percent, note, fk_project, fk_user_author, entity, code, datec) VALUES (?, ?, ?, ?, ?, 100, ?, ?, ?, 1, 'AC_OTH', NOW())");

    $handle = fopen($file_projects, "r");
    fgetcsv($handle, 0, ",");

    $proy_nuevos = 0;
    $fila = 1;
    $proy_omitidos = 0;

    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        $fila++;
        if ($fila % 50 == 0) {
            echo ". ";
            flush();
        }

        $nombre_original = $data[0] ?? '';
        $title = $data[2] ?? '';
        if (empty($title)) continue;

        $clave_titulo = normalizar_texto($title);
        if (isset($mapa_proyectos_titulos[$clave_titulo])) {
            $proy_omitidos++;
            continue;
        }

        $ref_generada = "PDev" . str_pad($secuencia_actual, 4, "0", STR_PAD_LEFT) . $sufijo_anual;
        $secuencia_actual++;

        // Fechas
        $fecha_crea = limpiar_fecha_excel($data[17] ?? '');
        if (!$fecha_crea) $fecha_crea = date('Y-m-d H:i:s');
        $fecha_last = limpiar_fecha_excel($data[18] ?? '');
        $fecha_mont = limpiar_fecha_excel($data[23] ?? '');
        $fecha_desm = limpiar_fecha_excel($data[24] ?? '');

        // Datos Extra del Excel
        $ciudad_ex  = $data[3] ?? '';
        $cp_ex      = $data[4] ?? '';
        $dir_ex     = $data[5] ?? '';
        if (empty($dir_ex)) $dir_ex = $title;
        $contact_p  = $data[8] ?? '';
        $phone_ex   = $data[9] ?? '';
        $email_ex   = $data[10] ?? '';
        $photo_ex   = $data[1] ?? '';
        $comp_ch    = $data[21] ?? ''; // Completion Chance
        $appr_ch    = $data[22] ?? ''; // Approval Chance
        $geo_lat    = $data[27] ?? '';
        $geo_long   = $data[28] ?? '';

        // Nota Privada Sitio (Incluye contacto extra)
        $nota_sitio_priv = "Generado Auto. Contacto: $contact_p. Foto: $photo_ex";

        // 1. SITIO
        $clave_sitio = normalizar_texto($title);
        $fk_emplazamiento = null;

        if (isset($mapa_sitios[$clave_sitio])) {
            $fk_emplazamiento = $mapa_sitios[$clave_sitio]['id'];
            // Actualizamos datos de contacto en sitio existente
            $stmt_update_site_contact->execute([$phone_ex, $email_ex, $dir_ex, $cp_ex, $ciudad_ex, $fk_emplazamiento]);
        } else {
            $match = buscar_mejor_coincidencia($title, $mapa_sitios);
            if ($match) {
                $fk_emplazamiento = $match['id'];
                $stmt_update_site_contact->execute([$phone_ex, $email_ex, $dir_ex, $cp_ex, $ciudad_ex, $fk_emplazamiento]);
            } else {
                try {
                    // Crear Sitio con TELÉFONO y EMAIL
                    $stmt_new_site->execute(['GEN-' . $fila, $title, $dir_ex, $cp_ex, $ciudad_ex, $phone_ex, $email_ex, $nota_sitio_priv, $id_user_creat]);
                    $fk_emplazamiento = $pdo->lastInsertId();
                    // Extras con GEO y CHANCES
                    $stmt_site_extra->execute([$fk_emplazamiento, $title, $fecha_mont, $fecha_desm, $geo_lat, $geo_long, $comp_ch, $appr_ch, $photo_ex]);

                    $mapa_sitios[$clave_sitio] = ['id' => $fk_emplazamiento, 'nombre_real' => $title];
                    echo "<small style='color:purple'>[Sitio] $title</small>";
                } catch (PDOException $e) {
                }
            }
        }
        if (!$fk_emplazamiento) continue;

        // 2. CLIENTE
        $nombre_prop = trim($data[7] ?? '');
        if (empty($nombre_prop)) $nombre_prop = $nombre_original ?: $ref_generada;
        $fk_soc = 0;
        $clave_cli = normalizar_texto($nombre_prop);

        if (isset($mapa_clientes[$clave_cli])) $fk_soc = $mapa_clientes[$clave_cli]['id'];
        else {
            $match = buscar_mejor_coincidencia($nombre_prop, $mapa_clientes);
            if ($match) $fk_soc = $match['id'];
            else {
                try {
                    $stmt_new_client->execute([$nombre_prop, $id_user_creat]);
                    $fk_soc = $pdo->lastInsertId();
                    $mapa_clientes[$clave_cli] = ['id' => $fk_soc, 'nombre_real' => $nombre_prop];
                    echo "<small style='color:blue'>[Cliente] $nombre_prop</small>";
                } catch (PDOException $e) {
                }
            }
        }

        // 3. PROYECTO
        $dev_note = $data[26] ?? '';
        $fk_statut = get_estado_proyecto($data[19] ?? '');
        $opp_percent = (float)str_replace(['%', ','], ['', '.'], $data[20] ?? 0);
        $tipo_texto = $data[6] ?? '';
        $est_txt = $data[19] ?? '';

        // Descripción completa para el proyecto también
        $descripcion_full = "--- DATOS EXCEL ---\nRef Orig: $nombre_original\nContacto: $contact_p ($phone_ex / $email_ex)\nNota Dev: $dev_note";

        try {
            $stmt_proj->execute([$ref_generada, $title, $fk_soc, $fecha_crea, $fecha_desm, $fecha_crea, $dev_note, $descripcion_full, $fk_statut, $opp_percent, $id_user_creat]);
            $proj_id = $pdo->lastInsertId();
            $mapa_proyectos_titulos[$clave_titulo] = true;

            $stmt_proj_extra->execute([$proj_id, 1, $fk_emplazamiento, $est_txt, 'Importado', $fecha_last]);
            $stmt_update_site_link->execute([$proj_id, $tipo_texto, $fk_emplazamiento]);

            $venta = limpiar_moneda($data[14] ?? 0);
            $coste = limpiar_moneda($data[15] ?? 0);
            $rate  = limpiar_moneda($data[11] ?? 0);
            $prod  = limpiar_moneda($data[13] ?? 0);
            $stmt_money->execute([$fk_emplazamiento, $venta, $coste, $rate, $prod]);

            // 4. EVENTO HTML
            $contenido_nota = '<table class="table table-striped table-bordered" style="width:100%; border-collapse: collapse;">';
            $contenido_nota .= '<thead><tr style="background-color:#f0f0f0;"><th style="padding:8px; border:1px solid #ddd;">Campo</th><th style="padding:8px; border:1px solid #ddd;">Valor</th></tr></thead><tbody>';
            foreach ($etiquetas_excel as $idx => $label) {
                $val = $data[$idx] ?? '';
                if ($val !== '') $contenido_nota .= "<tr><td style='padding:5px; border:1px solid #ddd; font-weight:bold;'>$label</td><td style='padding:5px; border:1px solid #ddd;'>$val</td></tr>";
            }
            $contenido_nota .= '</tbody></table>';

            $ref_evento = 'EV-' . $ref_generada;
            $stmt_event->execute([$ref_evento, 'IMP-' . $ref_generada, "Ficha Importada: $title", $fecha_crea, $fecha_crea, $contenido_nota, $proj_id, $id_user_creat]);

            $proy_nuevos++;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] != 1062) echo "<small style='color:red'>Err: " . $e->getMessage() . "</small>";
        }
    }
    fclose($handle);
    echo "</div>";
    echo "<h3>¡Finalizado! $proy_nuevos creados.</h3>";
} else {
    echo "<h3>Falta CSV</h3>";
}
