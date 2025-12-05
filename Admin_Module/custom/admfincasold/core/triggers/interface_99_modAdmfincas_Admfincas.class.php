<?php
/* Copyright (C) 2025 SuperAdmin */

require_once DOL_DOCUMENT_ROOT . '/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';

/**
 * Trigger para Auditoría: Registra cambios del Administrador en la Agenda
 */
class interface_99_modAdmfincas_Admfincas extends DolibarrTriggers
{
    public $family = 'admfincas';
    public $description = "Trigger for audit logs (Agenda)";
    public $version = '1.0.0';

    public function __construct($db)
    {
        $this->db = $db;
        $this->name = preg_replace('/^interface_99_modAdmfincas_/i', '', get_class($this));
    }

    /**
     * Función runTrigger (CamelCase para v21)
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {

        
        // Filtramos solo las acciones de nuestro módulo
        if ($action == 'ADMFINCA_CREATE' || $action == 'ADMFINCA_MODIFY' || $action == 'ADMFINCA_DELETE') {

            $log_label = "";
            $log_code  = "AC_OTH"; // Código genérico "Otros"

            if ($action == 'ADMFINCA_CREATE') {
                $log_label = "Creación de Administrador: " . $object->ref;
            } elseif ($action == 'ADMFINCA_MODIFY') {
                $log_label = "Modificación de datos: " . $object->ref;
            } elseif ($action == 'ADMFINCA_DELETE') {
                $log_label = "Eliminación de Administrador: " . $object->ref;
            }

            // Creamos el evento en la Agenda
            $event = new ActionComm($this->db);

            $event->type_code   = $log_code;
            $event->label       = $log_label;
            $event->note        = "Acción realizada por el usuario " . $user->login . " sobre el gestor " . $object->nom; // Usamos 'nom' o 'name' según tu clase
            $event->datep       = dol_now();
            $event->percentage  = 100; // Completado

            // Vinculación con el usuario que hace la acción
            $event->userownerid    = $user->id;
            $event->fk_user_author = $user->id;

            // VINCULACIÓN CLAVE: Unir el evento al objeto Administrador
            // (Menos si es DELETE, porque el objeto dejará de existir)
            if ($action != 'ADMFINCA_DELETE') {
                $event->elementtype = 'admfinca';
                $event->fk_element  = $object->id;
            }

            // Guardar el evento en la base de datos
            $event->create($user);
        }

        return 0;
    }
}
