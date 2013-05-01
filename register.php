<?php

// response json
$json = array();

/**
 * Registering a user device
 * Store reg id in users table
 */
if (isset($_POST["name"]) && isset($_POST["imei"]) && isset($_POST["regId"])) {
    $name = $_POST["name"];
    $imei = $_POST["imei"];
    $gcm_regid = $_POST["regId"]; // GCM Registration ID
    // Store user details in db
    include_once './db_functions.php';
    include_once './GCM.php';

    $db = new DB_Functions();
    $gcm = new GCM();

    $res = $db->storeUser($name, $imei, $gcm_regid);

//    $registatoin_ids = array($gcm_regid);
//    $message = array("product" => "shirt");

//    $result = $gcm->send_notification($registatoin_ids, $message);
//$result="OK";
//    echo $result;
echo $res;

} else {
    // user details missing
}
?>
