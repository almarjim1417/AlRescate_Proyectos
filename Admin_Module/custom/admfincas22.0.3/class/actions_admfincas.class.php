<?php

/**
 * Hook para mostrar el Administrador en la ficha del Tercero
 */
class ActionsAdmfincas
{
    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * Constructor
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Función que inyecta HTML en las fichas (Hook)
     */
    public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $langs;

        // Solo actuamos en la ficha del TERCERO
        if (!in_array('thirdpartycard', explode(':', $parameters['context']))) return 0;

        // --- CONSULTA SQL ACTUALIZADA (1:N) ---
        // Buscamos el administrador cuyo ID esté guardado en el campo fk_admfinca del tercero
        $sql = "SELECT adm.rowid, adm.name, adm.ref ";
        $sql .= "FROM " . MAIN_DB_PREFIX . "admfincas_admfinca as adm ";
        $sql .= "JOIN " . MAIN_DB_PREFIX . "societe as s ON s.fk_admfinca = adm.rowid ";
        $sql .= "WHERE s.rowid = " . ((int)$object->id);

        $resql = $this->db->query($sql);

        if ($resql && $this->db->num_rows($resql) > 0) {

            print '<tr>';

            // Etiqueta
            print '<td class="titlefield">';
            print '<i class="fa fa-user-tie" style="margin-right:5px; color:#555;"></i> ';
            print 'Administrador Fincas';
            print '</td>';

            // Valor
            print '<td colspan="3">';

            // Aunque ahora solo puede haber uno, el bucle sirve igual y es seguro
            while ($obj_adm = $this->db->fetch_object($resql)) {
                $url = dol_buildpath('/admfincas/admfinca_card.php', 1) . '?id=' . $obj_adm->rowid;

                print '<div style="margin-bottom: 4px;">';
                print '<a href="' . $url . '" style="font-weight: bold; color: #003366; display:inline-flex; align-items:center;">';
                print $obj_adm->ref . ' - ' . $obj_adm->name;
                print '</a>';
                print '</div>';
            }

            print '</td>';
            print '</tr>';
        }

        return 0;
    }
}
