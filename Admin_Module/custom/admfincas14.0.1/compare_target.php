<?php
define('DOL_DOCUMENT_ROOT', 'd:/xampp/htdocs/CONLIPRESS/htdocs');
define('NOTOKENRENEWAL', 1);
require_once DOL_DOCUMENT_ROOT . '/master.inc.php';

// Polyfill
if (!function_exists('isModEnabled')) {
    function isModEnabled($modName) {
        global $conf;
        return !empty($conf->$modName->enabled);
    }
}

// Load Target Class
// We use dot_include_once to simulate real loading
// But to be sure we load the file we want, we direct include.
require_once DOL_DOCUMENT_ROOT . '/custom/admfincas/class/admfinca.class.php';
$target = new Admfinca($db);
$targetFields = $target->fields;

print json_encode(array_keys($targetFields));
