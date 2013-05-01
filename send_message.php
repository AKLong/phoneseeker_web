<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (isset($_GET["imei"]) && isset($_GET["ip"]) && isset($_GET["id"])) {
    $imei = $_GET["imei"];
    $id = $_GET["id"];
    $ip = $_GET["ip"];
    include_once './GCM.php';
    
    $gcm = new GCM();
 
    include_once './db_functions.php';

    $db = new DB_Functions();

//    $db->setStatus($imei,"requesting");
//    $db->setIPAddress($id,$message);

    $gcm_regId = $db->getRegId($imei);

    $registration_ids = array($gcm_regId);
    $message = array("ip" => $ip,"id" => $id);

    $result = $gcm->send_notification($registration_ids, $message);

//echo (json_decode($result, true));

$obj = json_decode($result);
//print $obj->{'success'}; // 12345
//echo obj;
    echo  $obj->{'success'};

//echo "OK";
}
?>
