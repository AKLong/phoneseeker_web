<?php

// response json
$json = array();

/**
 * Registering a user device
 * Store reg id in users table
 */
if (isset($_POST["imei"])) {
    $imei = $_POST["imei"];
    // Store user details in db
    include_once './db_functions.php';

    $db = new DB_Functions();

    $res = $db->removeUser($imei);

echo $res;

} else {
    // user details missing
}
?>
