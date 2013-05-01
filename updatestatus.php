<?php

// response json
$json = array();

/**
 * Registering a user device
 * Store reg id in users table
 */
if (isset($_POST["id"]) && isset($_POST["imei"]) && isset($_POST["status"])) {
    $id = $_POST["id"];
    $imei = $_POST["imei"];
    $status = $_POST["status"];
    include_once './db_functions.php';

    $db = new DB_Functions();

    $res = $db->setStatus($id, $imei, $status);

echo $res;

} else {
    // user details missing
}
?>
