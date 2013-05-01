<?php

// response json
$json = array();

/**
 * Registering a user device
 * Store reg id in users table
 */
if (isset($_POST["id"]) && isset($_POST["accuracy"]) && isset($_POST["provider"]) && isset($_POST["imei"]) && isset($_POST["lat"]) && isset($_POST["lon"]) && isset($_POST["status"])) {
    $imei = $_POST["imei"];
    $lat = $_POST["lat"];
    $lon = $_POST["lon"];
    $status = $_POST["status"];
    $id = $_POST["id"];
    $accuracy = $_POST["accuracy"];
    $provider = $_POST["provider"];
    include_once './db_functions.php';

    $db = new DB_Functions();

//    $res = $db->setLocation($imei, $lat, $lon, $status);
    $res = $db->setLocation($id, $imei, $lat, $lon, $accuracy, $provider, $status);
echo $res;
} else {
    // user details missing
echo "incorrect params";}
?>
