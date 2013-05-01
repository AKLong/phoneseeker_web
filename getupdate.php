<?php
if (isset($_GET["imei"]) && isset($_GET["id"])) {
    $imei = $_GET["imei"];
    $id = $_GET["id"];
//    $attribute = $_GET["attribute"];
    include_once './db_functions.php';
              
    $db = new DB_Functions();

//    if (strcmp($attribute,"status")==0){

//       $res= $db->getStatus($imei);
 //      print $res;
//    }else{

       $res = $db->getLastLocation($imei,$id);
       $row = mysql_fetch_array($res);
//       print trim($row[$attribute]);
echo json_encode($row);
 //   }



} else {
echo "error";    // user details missing
}
?>

