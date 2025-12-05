<?php
/* Copyright (C) 2004-2018	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019	Nicolas ZABOURI				<info@inovea-conseil.com>
 * Copyright (C) 2019-2024	Frédéric France				<frederic.france@free.fr>
 * Copyright (C) 2025		SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   admfincas     Module Admfincas
 *  \brief      Admfincas module descriptor.
 *
 *  \file       htdocs/admfincas/core/modules/modAdmfincas.class.php
 *  \ingroup    admfincas
 *  \brief      Description and activation file for module Admfincas
 */
include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module Admfincas
 */
class modAdmfincas extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 500000; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'admfincas';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "other";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleAdmfincasName' not found (Admfincas is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// DESCRIPTION_FLAG
		// Module description, used if translation string 'ModuleAdmfincasDesc' not found (Admfincas is name of module).
		$this->description = "AdmfincasDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "AdmfincasDescription";

		// Author
		$this->editor_name = 'Fincadmin';
		$this->editor_url = '';		// Must be an external online web site
		$this->editor_squarred_logo = '';					// Must be image filename into the module/img directory followed with @modulename. Example: 'myimage.png@admfincas'

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
		$this->version = '2.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where ADMFINCAS is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'fa-file';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/admfincas/css/admfincas.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/admfincas/js/admfincas.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			/* BEGIN MODULEBUILDER HOOKSCONTEXTS */
			'hooks' => array(
                'data' => array(
                    'thirdpartycard',  // <--- ESTE ES EL IMPORTANTE (Ficha del tercero)
                ),
            
				//   'entity' => '0',
			),
			/* END MODULEBUILDER HOOKSCONTEXTS */
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
			// Set this to 1 if the module provides a website template into doctemplates/websites/website_template-mytemplate
			'websitetemplates' => 0,
			// Set this to 1 if the module provides a captcha driver
			'captcha' => 0
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/admfincas/temp","/admfincas/subdir");
		$this->dirs = array("/admfincas/temp");

		// Config pages. Put here list of php page, stored into admfincas/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@admfincas");

		// Dependencies
		// A condition to hide module
		$this->hidden = getDolGlobalInt('MODULE_ADMFINCAS_DISABLED'); // A condition to disable module;
		// List of module class names that must be enabled if this module is enabled. Example: array('always'=>array('modModuleToEnable1','modModuleToEnable2'), 'FR'=>array('modModuleToEnableFR')...)
		$this->depends = array();
		// List of module class names to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->requiredby = array();
		// List of module class names this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array();

		// The language file dedicated to your module
		$this->langfiles = array("admfincas@admfincas");

		// Prerequisites
		$this->phpmin = array(7, 1); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(12, -3); // Minimum version of Dolibarr required by module
		$this->need_javascript_ajax = 0;

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'AdmfincasWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('ADMFINCAS_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('ADMFINCAS_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		// Initialize module configuration object
		if (!isset($conf->admfincas)) {
			$conf->admfincas = new stdClass();
			$conf->admfincas->enabled = 0;
		}

		// Array to add new pages in new tabs
		/* BEGIN MODULEBUILDER TABS */
		$this->tabs = array();
		/* END MODULEBUILDER TABS */

		// Dictionaries
		/* BEGIN MODULEBUILDER DICTIONARIES */
		$this->dictionaries = array();
		/* END MODULEBUILDER DICTIONARIES */

		// Boxes/Widgets
		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array();
		/* END MODULEBUILDER WIDGETS */

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array();
		/* END MODULEBUILDER CRON */

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 0 + 1);
		$this->rights[$r][1] = 'Read Admfinca object of Admfincas';
		$this->rights[$r][4] = 'admfinca';
		$this->rights[$r][5] = 'read';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 1 + 1);
		$this->rights[$r][1] = 'Create/Update Admfinca object of Admfincas';
		$this->rights[$r][4] = 'admfinca';
		$this->rights[$r][5] = 'write';
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf('%02d', (0 * 10) + 2 + 1);
		$this->rights[$r][1] = 'Delete Admfinca object of Admfincas';
		$this->rights[$r][4] = 'admfinca';
		$this->rights[$r][5] = 'delete';
		$r++;

		/* END MODULEBUILDER PERMISSIONS */


		// Main menu entries to add
		$this->menu = array();
		$r = 0;

		// --- 1. CABECERA PRINCIPAL (Carpeta) ---
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=companies',  // Cuelga de la raíz de Terceros
			'type' => 'left',
			'titre' => 'Administradores Fincas',
			'mainmenu' => 'companies',
			'leftmenu' => 'admfincas',             // ID de la carpeta
			'prefix' => img_picto('', 'fa-user-tie', 'class="pictofixedwidth"'),
			'url' => '/admfincas/admfinca_list.php',
			'langs' => 'admfincas@admfincas',
			'position' => 2000,
			'enabled' => '$conf->admfincas->enabled',
			'target' => '',
			'user' => 2,
		);

		// --- 2. SUBMENÚ: LISTADO (Hijo) ---
		$this->menu[$r++] = array(
			// Cuelga de Terceros (companies) Y de la carpeta 'admfincas'
			'fk_menu' => 'fk_mainmenu=companies,fk_leftmenu=admfincas',
			'type' => 'left',
			'titre' => 'Listado',
			'mainmenu' => 'companies',
			'leftmenu' => 'admfincas_list',
			'url' => '/admfincas/admfinca_list.php', // Misma URL que la cabecera
			'langs' => 'admfincas@admfincas',
			'position' => 2001,
			'enabled' => '$conf->admfincas->enabled',
			'target' => '',
			'user' => 2,
		);

		// --- 3. SUBMENÚ: NUEVO (Hijo) ---
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=companies,fk_leftmenu=admfincas',
			'type' => 'left',
			'titre' => 'Nuevo Administrador',
			'mainmenu' => 'companies',
			'leftmenu' => 'admfincas_new',
			'url' => '/admfincas/admfinca_card.php?action=create',
			'langs' => 'admfincas@admfincas',
			'position' => 2002,
			'enabled' => '$conf->admfincas->enabled',
			'target' => '',
			'user' => 2,
		);

		// Exports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int<-1,1>          	1 if OK, <=0 if KO
	 */
    public function init($options = '')
    {
        global $conf, $langs;

        // 1. Cargar Tablas SQL (Esto crea las columnas nativas)
        // El archivo SQL está optimizado para ser idempotente
        $result = $this->_load_tables('/admfincas/sql/');
        if ($result < 0) {
            dol_syslog("Error loading tables for admfincas module", LOG_ERR);
            // No retornamos -1 aquí porque las tablas ya podrían existir
            // return -1;
        }

        // 2. Permisos y Configuración estándar
        $this->remove($options);

        $sql = array();

        // Document templates
        $moduledir = dol_sanitizeFileName('admfincas');
        $myTmpObjects = array();
        $myTmpObjects['Admfinca'] = array('includerefgeneration' => 1, 'includedocgeneration' => 1);

        foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
            if ($myTmpObjectArray['includerefgeneration']) {
                 // Templates will be created by framework
            }
        }

        return $this->_init($sql, $options);
    }

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted
	 *
	 *	@param	string		$options	Options when enabling module ('', 'noboxes')
	 *	@return	int<-1,1>				1 if OK, <=0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
