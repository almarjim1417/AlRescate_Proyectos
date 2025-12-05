<?php
define('DOL_DOCUMENT_ROOT', 'd:/xampp/htdocs/CONLIPRESS/htdocs');
define('NOTOKENRENEWAL', 1); // Avoid token issues
require_once DOL_DOCUMENT_ROOT . '/master.inc.php';

// Polyfill
if (!function_exists('isModEnabled')) {
    function isModEnabled($modName)
    {
        global $conf;
        return !empty($conf->$modName->enabled);
    }
}

// Load Source Class
require_once DOL_DOCUMENT_ROOT . '/admfincas2.0/class/admfinca.class.php';
$source = new Admfinca($db);
$sourceFields = $source->fields;
$sourceProps = get_object_vars($source);

// Reset Class (cannot redeclare class with same name in same run easily if names matched, 
// oh wait, they have the SAME CLASS NAME 'Admfinca'. PHP will fatal error if I load both.)
// I have to do this in two separate executions or use reflection on file content.
// Since I can't load both, I will output the source fields, then run another script for target.

print json_encode(array_keys($sourceFields));
