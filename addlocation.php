<?php

// response json
$json = array();

/**
 * Registering a user device
 * Store reg id in users table
 */
if (isset($_POST["imei"]) && isset($_POST["ip"]) && isset($_POST["status"])) {
    $imei = $_POST["imei"];
    $status = $_POST["status"];
    $ip = $_POST["ip"];
    include_once './db_functions.php';

    $db = new DB_Functions();
    $res = $db->addLocation($imei, $status,$ip);

echo $res;
} else {
    // user details missing
}
?>
