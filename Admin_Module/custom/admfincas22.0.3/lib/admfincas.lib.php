<?php
/* Copyright (C) 2025		SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    admfincas/lib/admfincas.lib.php
 * \ingroup admfincas
 * \brief   Library files with common functions for Admfincas
 */

/**
 * Prepare array with list of tabs
 *
 * @param   Admfinca  $object  Object related to tabs
 * @return  array              Array of tabs to show
 */

function admfinca_prepare_head($object)
{
    global $db, $langs, $conf;

    $h = 0;
    $head = array();

    // Pestaña 1: Ficha principal (Card)
    // NOTA: Asegúrate de que 'card.php' es el nombre real de tu ficha.
    // El Module Builder a veces le llama 'admfinca_card.php' o simplemente 'card.php'.
    // Revisa en tu carpeta cómo se llama el archivo principal.
    $head[$h][0] = dol_buildpath("/admfincas/admfinca_card.php", 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Card");
    $head[$h][2] = 'card';
    $h++;

    // --- NUEVA PESTAÑA COMUNIDADES ---
    $head[$h][0] = dol_buildpath("/admfincas/admfinca_societe.php", 1).'?id='.$object->id;
    $head[$h][1] = 'Comunidades';
    $head[$h][2] = 'societes';
    $h++;
    // --------------------------------

    // Pestaña Documentos (si está activada)
    if (isset($object->fields['rowid'])) {
         require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
         require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
         $upload_dir = $conf->admfincas->dir_output . "/ref/" . dol_sanitizeFileName($object->ref);
         $nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
         
         $head[$h][0] = dol_buildpath("/admfincas/document.php", 1).'?id='.$object->id;
         $head[$h][1] = $langs->trans('Documents');
         if ($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
         $head[$h][2] = 'documents';
         $h++;
    }

    // Pestaña Agenda/Eventos (opcional, el builder suele ponerla)
    if (!empty($conf->agenda->enabled) && isModEnabled('agenda')) {
        $head[$h][0] = dol_buildpath("/admfincas/agenda.php", 1).'?id='.$object->id;
        $head[$h][1] = $langs->trans("Events");
        $head[$h][2] = 'agenda';
        $h++;
    }

    complete_head_from_modules($conf, $langs, $object, $head, $h, 'admfinca', 'add');

    return $head;
}
