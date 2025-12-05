<?php
// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

echo "<h1>Contract Data Diagnostic</h1>";

// 0. Check Module Status
if (!empty($conf->contrat->enabled)) {
    echo "<p style='color:green'>Module 'Contracts' is ENABLED.</p>";
} else {
    echo "<p style='color:red'>Module 'Contracts' is DISABLED. This is why you might not see data or features.</p>";
}

// 1. Check if column exists
$sql_check = "SHOW COLUMNS FROM " . MAIN_DB_PREFIX . "societe LIKE 'fk_admfinca'";
$res = $db->query($sql_check);
if ($res && $db->num_rows($res) > 0) {
    echo "<p style='color:green'>Column <code>fk_admfinca</code> exists in <code>llx_societe</code>.</p>";
} else {
    echo "<p style='color:red'>CRITICAL: Column <code>fk_admfinca</code> DOES NOT EXIST in <code>llx_societe</code>.</p>";
    exit;
}

// 1b. Check total contracts in system
$sql_all = "SELECT count(*) as cnt FROM " . MAIN_DB_PREFIX . "contrat";
$res_all = $db->query($sql_all);
$obj_all = $db->fetch_object($res_all);
echo "<p>Total Contracts in Database: <strong>" . $obj_all->cnt . "</strong></p>";

// 2. Check for companies linked to ANY admfinca
$sql_soc = "SELECT count(*) as cnt FROM " . MAIN_DB_PREFIX . "societe WHERE fk_admfinca > 0";
$res_soc = $db->query($sql_soc);
$obj_soc = $db->fetch_object($res_soc);
echo "<p>Companies with linked Admin: <strong>" . $obj_soc->cnt . "</strong></p>";

if ($obj_soc->cnt == 0) {
    echo "<p style='color:orange'>Warning: No companies are linked to any Admin Finca in the database.</p>";
}

// 3. Check for specific Admin ID (if passed)
$id = GETPOST('id', 'int');
if ($id > 0) {
    echo "<h2>Checking for Admin ID: " . $id . "</h2>";
    $sql_specific = "SELECT rowid, nom FROM " . MAIN_DB_PREFIX . "societe WHERE fk_admfinca = " . $id;
    $res_specific = $db->query($sql_specific);
    if ($db->num_rows($res_specific) > 0) {
        $found_contracts = 0;
        echo "<ul>";
        while ($row = $db->fetch_object($res_specific)) {
            echo "<li>Linked Company: " . $row->nom . " (ID: " . $row->rowid . ")";

            // Check contracts for this company
            $sql_con = "SELECT count(*) as cnt FROM " . MAIN_DB_PREFIX . "contrat WHERE fk_soc = " . $row->rowid;
            $res_con = $db->query($sql_con);
            $obj_con = $db->fetch_object($res_con);
            echo " -> Contracts: " . $obj_con->cnt . "</li>";
            $found_contracts += $obj_con->cnt;
        }
        echo "</ul>";

        if ($found_contracts == 0) {
            echo "<p style='color:red'>Result: Companies are linked, but NONE of them have any contracts.</p>";
        } else {
            echo "<p style='color:green'>Result: Found " . $found_contracts . " contracts linked to this Admin.</p>";
        }

    } else {
        echo "<p style='color:red'>No companies linked to Admin ID " . $id . "</p>";
    }
} else {
    echo "<p>Pass <code>?id=X</code> to check a specific Admin.</p>";
}
