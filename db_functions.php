<?php

class DB_Functions {

    private $db;

    //put your code here
    // constructor
    function __construct() {
        include_once './db_connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->db->connect();
    }

    // destructor
    function __destruct() {
        
    }

    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($name, $imei, $gcm_regid) {
        // insert user into database
	if ($this->userExists($imei)){
              $result = mysql_query("update gcm_users SET name='$name', gcm_regid='$gcm_regid'  WHERE imei=$imei");
  	}else{
        	$result = mysql_query("INSERT INTO gcm_users(name, imei, gcm_regid, created_at) VALUES('$name', '$imei', '$gcm_regid', NOW())");
        }
	// check for successful store
        if ($result) {
            // get user details
            $id = mysql_insert_id(); // last inserted id
            $result = mysql_query("SELECT * FROM gcm_users WHERE id = $id") or die(mysql_error());
            // return user details
            if (mysql_num_rows($result) > 0) {
                return mysql_fetch_array($result);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function removeUser($imei) {
        // insert user into database
        if ($this->userExists($imei)){
              $result = mysql_query("update gcm_users SET gcm_regid=''  WHERE imei=$imei");
        }
    }                                                             

    /**
     * Get user by email and password
     */
    public function getUserByEmail($email) {
        $result = mysql_query("SELECT * FROM gcm_users WHERE email = '$email' LIMIT 1");
        return $result;
    }

    /**
     * Getting all users
     */
    public function getAllUsers() {
        $result = mysql_query("select * FROM gcm_users");
        return $result;
    }

    /**
     * Check user is existed or not
     */
    public function userExists($imei) {
        $result = mysql_query("SELECT imei from gcm_users WHERE imei = '$imei'");
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) {
            // user existed
            return true;
        } else {
            // user not existed
            return false;
        }
    }

   public function getIMEIfromName($name) {
        $result = mysql_query("SELECT imei from gcm_users WHERE name = '$name'");
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) {
 	      $row = mysql_fetch_array($result);
              return $row["imei"];
        } else {
            // user not existed
            return "";
        }
    }


    public function addLocation($imei,$status,$ip) {
        // insert user into database
        $result = mysql_query("INSERT INTO position_data (imei, time) VALUES('$imei', NOW())");
        $id = mysql_insert_id(); // last inserted id
        // check for successful store
        if ($result) {
            $this->setStatus($id, $imei,$status);
            $this->setIPAddress($id,$ip);
	    return  $id;
        } else {
	    $this->setStatus($id,$imei,"error");
                return "";
    	}
  	}
    public function setLocation($id, $imei, $lat, $lon, $accuracy, $provider, $status) {
        // insert user into database
        $result = mysql_query("update gcm_users SET status='$status' WHERE imei=$imei");

//	if (strcmp($provider,"gps")==0){
//	        $result = mysql_query("update position_data SET gps_lat='$lat', gps_lon='$lon',status='$status', gps_accuracy='$accuracy', provider='$provider' WHERE id=$id");
//       	}else{
//	        $result = mysql_query("update position_data SET net_lat='$lat', net_lon='$lon',status='$status', net_accuracy='$accuracy', provider='$provider' WHERE id=$id");
//	}
	// check for successful store

        $result = mysql_query("update position_data SET status='$status' WHERE id=$id");

	$result = mysql_query("INSERT INTO gps_data (lat, lon, accuracy, provider, request_id) VALUES($lat, $lon, $accuracy, '$provider',$id)");

        return $result;
    }

    public function setStatus($id, $imei, $status) {
        // insert user into database
        $result = mysql_query("update gcm_users SET status='$status' WHERE imei=$imei");
        if (strcmp($id,"0") != 0){
        	$result = mysql_query("update position_data SET status='$status' WHERE id=$id");
	}
        // check for successful store
        return $result;
    }

    public function setIPAddress($id, $address) {
        // insert user into database
        $result = mysql_query("update position_data SET ipaddress='$address' WHERE id=$id");
        // check for successful store
        return $result;
    }

    public function getLastLocation($imei,$id) {

//        $result = mysql_query("select lat ,lon, status FROM  gcm_users WHERE imei=$imei");
	if (strcmp($id,"0")==0){
		$result = mysql_query("select *  FROM  position_data WHERE imei=$imei ORDER BY id DESC LIMIT 1");
	}else{
//        $result = mysql_query("SELECT lat, lon, accuracy, provider FROM gps_data WHERE request_id = '$id'  ORDER BY accuracy ASC LIMIT 1");
	$result = mysql_query("SELECT gps_data.lat, gps_data.lon, gps_data.accuracy, gps_data.provider,  position_data.status FROM position_data LEFT JOIN gps_data on position_data.id = gps_data.request_id WHERE position_data.id=$id ORDER BY id DESC LIMIT 1");

//		$result = mysql_query("select *  FROM  gps_data WHERE requset_id=$id");
	}       
	return $result;
    }

    public function getLastValidLocation($imei) {

	$result = mysql_query("SELECT gps_data.lat, gps_data.lon, gps_data.accuracy, position_data.id FROM position_data, gps_data
	WHERE position_data.id = gps_data.request_id AND  position_data.imei=$imei ORDER BY id DESC LIMIT 1");

//	$result = mysql_query("select *  FROM  position_data WHERE imei=$imei AND gps_lat != 0 ORDER BY id DESC LIMIT 1");
//	$result = mysql_query("select *  FROM  position_data WHERE imei=$imei AND gps_lat != 0 ORDER BY id DESC LIMIT 1");
	return $result;
    }

    public function getLastValidID($imei) {
	$result = mysql_query("select id  FROM  position_data WHERE imei=$imei ORDER BY id DESC LIMIT 1");
	return $result;
    }

    public function getStatus($imei) {
        $result = mysql_query("SELECT status FROM  gcm_users WHERE imei=$imei");
	$row = mysql_fetch_array($result);
        return $row["status"];
    }

    public function getRegId($imei) {
        $result = mysql_query("select gcm_regid FROM  gcm_users WHERE imei=$imei");
	    
while ($row = mysql_fetch_array($result)){
    return $row["gcm_regid"];
}

    }

}

?>
