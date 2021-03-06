<?php


$lat="";
$lon="";
$status="unknown";
/**
 * Registering a user device
 * Store reg id in users table
 */
include_once './db_functions.php';

$db = new DB_Functions();

$name = $_POST["name"];
$imei = $db->getIMEIfromName($name);

if (strcmp($imei,"")!=0) {

  $res = $db->getLastValidLocation($imei,0);
  $row = mysql_fetch_array($res);

  $lat=$row["lat"];
  $lon=$row["lon"];
  $status=$row["status"];

  $idRes = $db->getLastValidID($imei);
  $idRow = mysql_fetch_array($idRes);
  $id=$idRow["id"];
  if (strcmp($id,"")==0){
    $id="0";
    $lon="0";
    $lat="0";
  }
  $time=$row["time"];
   

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


var geocoder;
var map;
var circle;

var infowindow = new google.maps.InfoWindow();
var marker;

var statusDiv ;
var dateTimeDiv ;
var updateButton
var gpsPos;
var netPos;
var time="<?php echo $time ?>";

var positionReceived = false;
var waitingForUpdate = false

var dbUpdated = false;
var locationID=0;
var maxLocationID=<?php echo $id ?>;
var posArray = new Array();


//********************************************************************//
function position (type,m,lat,lon,accuracy) {
  this.type = type;
    this.lat=lat;
    this.lon=lon;
    this.accuracy=accuracy;
  
  this.col='#00AA00'
  if (this.type == 'network') { this.col = '#AA0000'}

  this.circle = new google.maps.Circle({
    map: m,
	radius: 1,                         
	fillColor:'#33FFFF',
	fillOpacity:0.05,
	strokeWeight:1,
	strokeOpacity:0.5,
	strokeColor:this.col

	});
  ;
   if (this.accuracy > 0 ){
      this.circle.setCenter(new google.maps.LatLng(this.lat,this.lon));
      this.circle.setRadius(this.accuracy);
      this.circle.setVisible(true);
    }

 this.remove = function() {
      this.circle.setVisible(false);
  };

}

//********************************************************************//

function getBestPos(){

  var i;
  if (posArray.length==0){ return ""}
  var pos = posArray[0];
  for(i=1;i<posArray.length;i++){
    if (pos.accuracy > 0 && posArray[i].accuracy >0){
      if (pos.accuracy > posArray[i].accuracy){
        pos = posArray[i];
      }
    }
  }
  return pos;
 }

//********************************************************************//
function sendPushNotification(id){

  //	lastLocationID=locationID;
  locationID=id;


  $.ajax({
    url: "send_message.php",
	type: 'GET',
	data: "imei=<?php echo $imei ?>&ip=<?php echo $ipaddress ?>&id=" + id,
	beforeSend: function() {
                        
      },
	success: function(data, textStatus, xhr) {
	if (data!="0"){
	  statusDiv.innerHTML =  "Request Accepted. Waiting for Position...";
	  update();
	}else{
	  alert("Phone is Not Registered");
	  map.controls[google.maps.ControlPosition.CENTER].pop(statusDiv);
	  map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(updateButton);
	}
      },
	error: function(xhr, textStatus, errorThrown) {
                        
      }
    });
  return false;
}


function clearCircles(){
	var i;
	for (i=0;i<posArray.length;i++){
		posArray[i].remove();	
	} 
	posArray=new Array();
 
}

//********************************************************************//
function addLocationEntry()
{
  statusDiv.innerHTML = "Sending Position Request...";
  map.controls[google.maps.ControlPosition.CENTER].push(statusDiv);
  map.controls[google.maps.ControlPosition.BOTTOM_CENTER].pop(updateButton);
  marker.setVisible(false);
  infowindow.close();
  dbUpdated=false;
  //gpsPos.circle.setVisible(false);
  //netPos.circle.setVisible(false);
  clearCircles();
  positionReceived=false;
  waitingForUpdate=true;

  $.ajax({
    url: "addlocation.php",
	type: 'POST',
	data: "imei=<?php echo $imei ?>&status=requesting&ip=<?php echo $ipaddress ?>",
	beforeSend: function() {
                        
      },
	success: function(data, textStatus, xhr) {

	sendPushNotification(data);
      },
	error: function(xhr, textStatus, errorThrown) {

      }
    });

}

//********************************************************************//
function initialize() {
  geocoder = new google.maps.Geocoder();



  var mapOptions = {
  zoom: 17,
  //          center: latlng,
  mapTypeId: 'roadmap'
  }
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

  //gpsPos = new position("gps",'#00AA00',map);
  //netPos = new position("network",'#AA0000',map);

//  gpsPos.lat=<?php echo $lat ?>;
//  gpsPos.lon=<?php echo $lon ?>;
//  netPos.lat=<?php echo $lat ?>;
//  netPos.lon=<?php echo $lon ?>;

  var latlng = new google.maps.LatLng(<?php echo $lat ?>,<?php echo $lon ?>);
  map.setCenter(latlng);
  marker = new google.maps.Marker({
    map:map
	});

  //var myTitle = document.createElement('h1');
  //myTitle.innerHTML = '';
  statusDiv = document.createElement('div');
  statusDiv.id = 'map-status';
  statusDiv.innerHTML = "Sending Position Request...";
  //myTextDiv.appendChild(myTitle);


  dateTimeDiv = document.createElement('div');
  dateTimeDiv.id = 'date-time';
  dateTimeDiv.innerHTML = time;

  map.controls[google.maps.ControlPosition.TOP_CENTER].push(dateTimeDiv);

  updateButton = document.createElement('input');
  updateButton.type = "button";
  updateButton.value = "Get New Position";
  updateButton.onclick = function (){
    //	sendPushNotification();
    addLocationEntry();

    ;}
  updateButton.id = 'update-button';
  map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(updateButton);

}

//********************************************************************//
function showMarker(pos){

  marker = new google.maps.Marker({
    position: new google.maps.LatLng(pos.lat,pos.lon),
	animation: google.maps.Animation.DROP,
	map: map
	});

  geocoder.geocode({'latLng': new google.maps.LatLng(pos.lat,pos.lon)}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
	if (results[1]) {
	  infowindow.setContent(results[1].formatted_address);
	  infowindow.open(map, marker);
	}
      }
    });
}

//********************************************************************//
function panTo(pos){
  if (pos != ""){
    var latlng = new google.maps.LatLng(pos.lat,pos.lon);
    map.panTo(latlng);
  }
 
}

//********************************************************************//
function updateMap(lat,lon){

//  panTo(comparePos(gpsPos,netPos));
  panTo(getBestPos());

  dateTimeDiv.innerHTML = time;

  if (positionReceived){
    map.controls[google.maps.ControlPosition.CENTER].pop(statusDiv);
    showMarker(getBestPos());
  }
}


//********************************************************************//
//function updatePos(){
//	loadData("","lat");
//	loadData("","lon");
//}


//********************************************************************//
function update(){
  if (waitingForUpdate==true){
    loadData();

    setTimeout("update()",5000);
  }else{
    map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(updateButton);
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


	    var json = jQuery.parseJSON(resp);
	    var obj = null
	    $.each(json, function() {
  
               posArray.push(new position(this.provider,map,parseFloat(this.lat),parseFloat(this.lon),Math.round(parseFloat(this.accuracy))));
               obj = this;
	     })
	    
	if (obj != null){	
	    time = obj.time;
	    if (obj.status=="requesting"){
	      statusDiv.innerHTML =  "Reqesting Position from phone...";
	    }else if (obj.status=="completed"){ 
	      statusDiv.innerHTML =  "Loading Position...";
	      positionReceived=true;
	      waitingForUpdate=false;
	      updateMap();
	      //                                              updatePos();
	    }else if (obj.status=="improvingAccuracy"){ 
	      statusDiv.innerHTML =  "Improving Accuracy...";
	      updateMap();
	      //                                              updatePos();
	    }else if (obj.status=="waitingForSatellite"){ 
	      statusDiv.innerHTML =  "Request Accepted. Waiting for Position...";
	    }else if (obj.status=="noPositionAvailable"){ 
	      statusDiv.innerHTML =  "Position Data Not Available";
	      waitingForUpdate=false;
	    }else{ 
	      statusDiv.innerHTML = obj.status;
	    }
	  }
	}

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
  font-family:arial;
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

#update-button{
font-size:30px;
margin-bottom:10px;
//height:50px;
-webkit-appearance: button;
}

#date-time{
opacity: 0.9;
filter: alpha(opacity=90);
color:black;
background-color:white;
font-size:12px;
padding:5px;
margin-top:5px;
//width:100%;
text-align:center;
border:1px solid #888888;
}

.container{
 width: 900px;
 margin: 0 auto;
 padding: 0;
 }
h1{
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  font-size: 24px;
 color: #777;
}
div.clear{
 clear: both;
}

</style>

</head>

<?php if (strcmp($imei,"")!=0) { ?>
  <body onload="initialize()">
      <div id='map-canvas'></div>

      <?php }else{ ?>
  <body>
  
    <div class="container">
    <h1>Select Device</h1>
    <hr/>

    <form name="" method="post" action="index.php">
    <label>Name</label><input type="text" name="name"/>
    <input type="submit"/>
    </form>

    </div>
    <?php } ?>


    </body>
    </html>
