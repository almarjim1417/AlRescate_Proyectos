<?php

// Usamos la ruta relativa que sí funcionó
require_once __DIR__ . '/../../../../core/modules/DolibarrModules.class.php';

class modKitchen extends DolibarrModules
{
    /**
     * Constructor.
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;
        $this->numero = 500001;
        $this->rights_class = 'kitchen';
        $this->name = 'kitchen';
        $this->description = 'KDS Monitor de Cocina Personalizado';
        $this->version = '1.0';
        $this->const_name = 'MAIN_MODULE_KITCHEN';
        $this->family = 'products';
        $this->module_parts = array('css' => 1);

        $this->dirs = array("/kitchen/core/modules");

        // --- PERMISOS DESACTIVADOS (Causan el Error 500) ---
        // $this->rights = array();

        // Dependencias
        $this->depends = array('modTakepos');
        $this->requiredby = array();

        // --- MENÚ REACTIVADO ---
        $this->menu = array();
    }

    /**
     * Función de activación del módulo
     * (Versión "vacía" que sí funcionaba)
     */
    public function init($options = '')
    {
        $sql = array(); // Array de SQL vacío
        return $this->_init($sql, $options);
    }

    /**
     * Función de desactivación
     * (Versión "vacía" que sí funcionaba)
     */
    public function remove($options = '')
    {
        $sql = array(); // Array de SQL vacío
        return $this->_remove($sql, $options);
    }

    /**
     * Añadir menús (¡¡CON PERMISOS ELIMINADOS!!)
     */
    public function loadMenu()
    {
        // --- PRUEBA 1: Intentar "pegarse" a TPV (sin 'langs' y sin 'perms') ---
        $this->menu[] = array(
            'fk_menu' => 0,
            'type' => 'top',
            'titre' => 'Monitor KDS', // Título del botón
            'mainmenu' => 'takepos',
            'leftmenu' => '',
            'url' => '/custom/kitchen/kds_view.php',
            //'langs' => 'mylangfile@kitchen', // (Eliminado)
            'position' => 100,
            'enabled' => 1,
            //'perms' => '$user->rights->kitchen->UsarKDS', // <-- ¡¡ELIMINADO!!
            'target' => '_blank',
            'user' => 2
        );

        // --- PRUEBA 2: Crear un menú de Nivel Superior (sin 'perms') ---
        $this->menu[] = array(
            'fk_menu' => 0,
            'type' => 'top',
            'titre' => 'KDS Cocina', // Título del botón
            'mainmenu' => 'kitchen', // <-- Su propio menú
            'leftmenu' => 'kitchen',
            'url' => '/custom/kitchen/kds_view.php',
            'position' => 101,
            'enabled' => 1,
            //'perms' => '$user->rights->kitchen->UsarKDS', // <-- ¡¡ELIMINADO!!
            'target' => '_blank',
            'user' => 2
        );
    }
}
