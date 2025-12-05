<?php
/* Copyright (C) 2025       SuperAdmin
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
 * \file    lib/admfincas_admfinca.lib.php
 * \ingroup admfincas
 * \brief   Library files with common functions for Admfinca
 */

/**
 * Prepare array of tabs for Admfinca
 *
 * @param   Admfinca    $object                 Admfinca
 * @return  array<array{string,string,string}>  Array of tabs
 */
function admfincaPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("admfincas@admfincas");
	$langs->load("companies"); // Cargamos traducción de Terceros

	$showtabofpagecontact = 1;
	$showtabofpagenote = 1;
	$showtabofpagedocument = 1;
	$showtabofpageagenda = 1;

	$h = 0;
	$head = array();

	// 1. Pestaña Principal (Ficha)
	$head[$h][0] = dol_buildpath("/admfincas/admfinca_card.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Admfinca");
	$head[$h][2] = 'card';
	$h++;

	// --- NUEVAS PESTAÑAS (Centralizadas) ---

	// 2. Terceros (Antes Comunidades) - CAMBIO SOLICITADO
	$head[$h][0] = dol_buildpath("/admfincas/admfinca_societe.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("ThirdParties"); // Usamos la traducción oficial "Terceros"
	$head[$h][2] = 'societes';
	$h++;

	// 3. Facturas
	$head[$h][0] = dol_buildpath("/admfincas/admfinca_invoices.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Facturas';
	$head[$h][2] = 'invoices';
	$h++;

	// 4. Presupuestos
	$head[$h][0] = dol_buildpath("/admfincas/admfinca_proposals.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Presupuestos';
	$head[$h][2] = 'proposals';
	$h++;

	// 5. Pedidos
	$head[$h][0] = dol_buildpath("/admfincas/admfinca_orders.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Pedidos';
	$head[$h][2] = 'orders';
	$h++;

	// 6. Proyectos (AÑADIDO QUE FALTABA)
	$head[$h][0] = dol_buildpath("/admfincas/admfinca_projects.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Proyectos';
	$head[$h][2] = 'projects';
	$h++;

	// 7. Contratos
    $head[$h][0] = dol_buildpath("/admfincas/admfinca_contracts.php", 1).'?id='.$object->id;
    $head[$h][1] = 'Contratos';
    $head[$h][2] = 'contracts';
    $h++;

	// ---------------------------------------

	// Pestaña Contactos
	if ($showtabofpagecontact) {
		$head[$h][0] = dol_buildpath("/admfincas/admfinca_contact.php", 1) . '?id=' . $object->id;
		$head[$h][1] = $langs->trans("Contacts");
		$head[$h][2] = 'contact';
		$h++;
	}

	// Pestaña Notas
	if ($showtabofpagenote) {
		if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
			$nbNote = 0;
			if (!empty($object->note_private)) {
				$nbNote++;
			}
			if (!empty($object->note_public)) {
				$nbNote++;
			}
			$head[$h][0] = dol_buildpath('/admfincas/admfinca_note.php', 1) . '?id=' . $object->id;
			$head[$h][1] = $langs->trans('Notes');
			if ($nbNote > 0) {
				$head[$h][1] .= (!getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER') ? '<span class="badge marginleftonlyshort">' . $nbNote . '</span>' : '');
			}
			$head[$h][2] = 'note';
			$h++;
		}
	}

	// Pestaña Documentos
	if ($showtabofpagedocument) {
		require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
		$upload_dir = $conf->admfincas->dir_output . "/admfinca/" . dol_sanitizeFileName($object->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $object->element, $object->id);
		$head[$h][0] = dol_buildpath("/admfincas/admfinca_document.php", 1) . '?id=' . $object->id;
		$head[$h][1] = $langs->trans('Documents');
		if (($nbFiles + $nbLinks) > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">' . ($nbFiles + $nbLinks) . '</span>';
		}
		$head[$h][2] = 'document';
		$h++;
	}

	// Pestaña Agenda
	if ($showtabofpageagenda) {
		$head[$h][0] = dol_buildpath("/admfincas/admfinca_agenda.php", 1) . '?id=' . $object->id;
		$head[$h][1] = $langs->trans("Events");
		$head[$h][2] = 'agenda';
		$h++;
	}

	// Show more tabs from modules
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'admfinca@admfincas');
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'admfinca@admfincas', 'remove');

	return $head;
}
