<?php


$lat="";
$lon="";
$status="unknown";
/**
 * Registering a user device
 * Store reg id in users table
 */
if (isset($_GET["imei"])) {
    $imei = $_GET["imei"];
    include_once './db_functions.php';

    $db = new DB_Functions();

    $res = $db->getLastLocation($imei);

    while ($row = mysql_fetch_array($res)){
    $lat=$row["lat"];
    $lon=$row["lon"];
    $status=$row["status"];
    $id=$row["id"];
    }

    $ipaddress = $_SERVER["REMOTE_ADDR"];


} else {
    // user details missing
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Location of <?php echo $imei?></title>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script>

            $(document).ready(function(){
               
            });


//********************************************************************//
            function sendPushNotification(){
                $.ajax({
                    url: "send_message.php",
                    type: 'GET',
                    data: "imei=<?php echo $imei ?>&message=<?php echo $ipaddress ?>",
                    beforeSend: function() {
                        
                    },
                    success: function(data, textStatus, xhr) {
                          update();
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        
                    }
                });
                return false;
            }




      var geocoder;
      var map;
	var circle;
      var infowindow = new google.maps.InfoWindow();
      var marker;

	var statusDiv ;
	var lat=<?php echo $lat ?>;
	var lon=<?php echo $lon ?>;
	var accuracy = 0;
	var provider = "";
	var positionReceived = false;

	var dbUpdated = false;
	var locationID="0";

//********************************************************************//
      function initialize() {
        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(<?php echo $lat, "," , $lon ?>);
        var mapOptions = {
          zoom: 17,
          center: latlng,
          mapTypeId: 'roadmap'
        }
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);


circle = new google.maps.Circle({
  map: map,
  radius: 1,                         
  fillColor: '#AA0000',
strokeWeight:1
});

//var myTitle = document.createElement('h1');
//myTitle.innerHTML = '';
statusDiv = document.createElement('div');
statusDiv.id = 'map-status';
statusDiv.innerHTML = "Sending Position Request...";
//myTextDiv.appendChild(myTitle);

map.controls[google.maps.ControlPosition.CENTER].push(statusDiv);


	sendPushNotification();
    }

//********************************************************************//
     function showMarker(latlng){

              marker = new google.maps.Marker({
                  position: latlng,
          animation: google.maps.Animation.DROP,
                  map: map
              });

        geocoder.geocode({'latLng': latlng}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
              infowindow.setContent(results[1].formatted_address);
              infowindow.open(map, marker);
            }
          }
        });
      }

 function  showAccuracy(latlng){
	if (provider=="gps"){
		circle.setOptions({fillColor:'#00AA00'});
	}
	circle.setCenter(latlng);
	circle.setRadius(accuracy);
	circle.setVisible(true);

}
//********************************************************************//
function updateMap(){
        var latlng = new google.maps.LatLng(lat,lon);
        map.panTo(latlng);

	showAccuracy(latlng);

	if (positionReceived){
	        map.controls[google.maps.ControlPosition.CENTER].pop(statusDiv);
        	showMarker(latlng);
	}
}


//********************************************************************//
//function updatePos(){
//	loadData("","lat");
//	loadData("","lon");
//}


//********************************************************************//
function update(){
	if (positionReceived==false){
		if (dbUpdated==true){
			loadData("status","status");

			setTimeout("update()",5000);
		}else{
			loadData("","id");
		}
	}
}

//********************************************************************//
function loadData()
{

	var xmlhttp;
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
  		xmlhttp=new XMLHttpRequest();
  	}
	else
  	{// code for IE6, IE5
  		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  	}
	xmlhttp.onreadystatechange=function()
  	{
  		if (xmlhttp.readyState==4 && xmlhttp.status==200)
    		{

        		var resp = xmlhttp.responseText.replace(/[\r\n]/g,"");
			if (resp != ""){

				var obj = jQuery.parseJSON(resp);


				if (dbUpdated==true){
					lat = parseFloat(obj.lat);
					lon = parseFloat(obj.lon);
					accuracy = Math.round(parseFloat(obj.accuracy));
					provider=obj.provider;
					if (obj.status=="requesting"){
						statusDiv.innerHTML =  "Reqesting Position from phone...";
                                        }else if (obj.status=="completed"){ 
                                                statusDiv.innerHTML =  "Loading Position...";
                                                positionReceived=true;
					updateMap();
//                                                updatePos();
                                        }else if (obj.status=="improvingAccuracy"){ 
                                                statusDiv.innerHTML =  "Improving Accuracy...";
					updateMap();
//                                                updatePos();
					}else if (obj.status=="waitingForSatellite"){ 
						statusDiv.innerHTML =  "Request Accepted. Waiting for Position...";
					}else if (obj.status=="noPositionAvailable"){ 
						statusDiv.innerHTML =  "Position Data Not Available";
					}else{ 
						statusDiv.innerHTML = obj.status;
					}
				}else{
					if (obj.id != <?php echo $id ?>){
						dbUpdated=true;
						locationID = obj.id;
					}
					setTimeout("update()",5000);
				}
			}

//	        	if (resp == "") resp="No Data"
//			if (elementName != ""){
//      				if (document.getElementById(elementName).innerHTML != resp){
//            				document.getElementById(elementName).innerHTML=resp;
//        			}
//			}
	    	}
	}	
	xmlhttp.open("GET","getupdate.php?imei=<?php echo $imei?>&id=" + locationID,true);
	xmlhttp.send();
}

    </script>

<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
}

#map-canvas {
    height: auto !important; /* ie6 ignores !important, so this will be overridden below */
    min-height: 100%; /* ie6 ignores min-height completely */
    height: 100%;
}

#map-status{
    opacity: 0.9;
    filter: alpha(opacity=90);
color:black;
background-color:white;
font-size:20px;
padding:30px;
width:100%;
text-align:center;
border:1px solid black;
}
</style>

  </head>
  <body onload="initialize()">
 <div id='map-canvas'></div>
 </body>
</html>
