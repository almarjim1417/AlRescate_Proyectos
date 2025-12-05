<?php
// Define minimal context
define('DOL_DOCUMENT_ROOT', 'd:/xampp/htdocs/CONLIPRESS/htdocs');
require_once DOL_DOCUMENT_ROOT . '/master.inc.php';


print "Testing dol_include_once...\n";
$res = dol_include_once('/admfincas/class/admfinca.class.php');
if ($res) {
    print "dol_include_once success.\n";
} else {
    print "dol_include_once FAILED.\n";
}

print "Instantiating class...\n";
if (class_exists('Admfinca')) {
    $object = new Admfinca($db);
    print "Class instantiated.\n";
} else {
    print "Class Admfinca NOT FOUND.\n";
}

print "Done.\n";
