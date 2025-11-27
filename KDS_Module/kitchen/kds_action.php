<?php
define('ISLOADEDBYSTEELSHEET', true);

// 1. Cargamos el entorno principal de Dolibarr
require '../../main.inc.php';

// 2. Comprobar si la BD está disponible
if (!isset($db) || !is_object($db)) {
    die("Error: El objeto de base de datos de Dolibarr no está disponible.");
}

// 3. OMITIMOS LA COMPROBACIÓN DEL TOKEN (como en la versión anterior)

// 4. OBTENER VARIABLES
$line_ids_array = GETPOST('line_ids', 'array');
$line_id_partial = GETPOSTINT('line_id_partial');
$qty_to_send = GETPOSTINT('action_partial_qty');
$action_send_all = GETPOST('action_send_all');
$send_all_link = GETPOST('send_all');
$current_servicio = GETPOST('current_servicio', 'int'); // (el que enviamos desde v47)

// OPCIÓN A: Se pulsó "Mandar Todos" o "Plato Listo"
if ($action_send_all || $send_all_link) {

    if (!empty($line_ids_array)) {
        foreach ($line_ids_array as $line_id) {
            $line_id = (int) $line_id;
            if ($line_id > 0) {
                // Marcamos el plato como "listo" (99)
                $sql = "UPDATE vol_facturedet_extrafields 
                        SET servicio = 99 -- 99 = Hecho
                        WHERE fk_object = " . $line_id;

                // --- ¡¡ARREGLO!! Desactivamos los hooks/triggers ---
                $db->query($sql, 0, 'write', 1);
            }
        }
    }

    // OPCIÓN B: Se pulsó un botón numérico parcial (ej: "1", "2")
} elseif ($line_id_partial > 0 && $qty_to_send > 0) {

    $sql_get = "SELECT qty FROM vol_facturedet WHERE rowid = " . $line_id_partial;
    $res = $db->query($sql_get); // No necesitamos desactivar hooks en un SELECT
    $obj = $db->fetch_object($res);
    $original_qty = $obj->qty;

    if ($qty_to_send < $original_qty) {

        // 1. Restamos la cantidad
        $remaining_qty = $original_qty - $qty_to_send;
        $sql_update = "UPDATE vol_facturedet 
                       SET qty = " . $remaining_qty . " 
                       WHERE rowid = " . $line_id_partial;

        // --- ¡¡ARREGLO!! Desactivamos los hooks/triggers ---
        $db->query($sql_update, 0, 'write', 1);

        // 2. Forzamos al 'servicio' a volver a su valor original
        $sql_fix_servicio = "UPDATE vol_facturedet_extrafields
                             SET servicio = " . $current_servicio . "
                             WHERE fk_object = " . $line_id_partial;

        // --- ¡¡ARREGLO!! Desactivamos los hooks/triggers ---
        $db->query($sql_fix_servicio, 0, 'write', 1);
    } else {
        // Si la cantidad a enviar es igual (o mayor), marcamos todas las líneas
        if (!empty($line_ids_array)) {
            foreach ($line_ids_array as $line_id) {
                $line_id = (int) $line_id;
                if ($line_id > 0) {
                    $sql_all = "UPDATE vol_facturedet_extrafields 
                                SET servicio = 99 
                                WHERE fk_object = " . $line_id;
                    // --- ¡¡ARREGLO!! Desactivamos los hooks/triggers ---
                    $db->query($sql_all, 0, 'write', 1);
                }
            }
        }
    }
}

// 5. REDIRIGIR DE VUELTA AL KDS
header('Location: kds_view.php');
exit;
