<?php
session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}

	require_once 'dbconnect.php';
	require_once 'clean_input.php';
	$error=false;
	// define variables and set to empty values
	$incidtErr = $lngErr = $latErr = $descErr = "";
	$incidt = $incidesc = $incilat = $incilng = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["incidt"])) {
    $incidtErr = "Date of Incident is required";
	$error=true;
  } else {
    $incidt = clean_input($_POST["incidt"]);
     
  }
  
  if (empty($_POST["incidesc"])) {
    $descErr = "short description is required";
	$error=true;
  } else {
    $incidesc = clean_input($_POST["incidesc"]);
   
  }
    
  if (empty($_POST["incilat"])) {
    $latErr = "Latitude of Incident is required";
	$error=true;
  } else {
    $incilat = clean_input($_POST["incilat"]);
	$fltlat=(double)$incilat;
    // check if lat is between -90 to 90
  if ($fltlat<-90.0 or $fltlat>90.0) {
      $latErr = "Invalid latitude "; 
	  $error=true;
    }
	
  }

  if (empty($_POST["incilng"])) {
    $lngErr = "Longitude of Incident is required";
  } else {
    $incilng = clean_input($_POST["incilng"]);
	$fltlng=(double)$incilng;
    // check if lng is between -180 to 180
	
    if ($fltlng<-180.0 or $incilat>180.0) {
      $lngErr = "Invalid longitude "; 
	  $error=true;
    }
  }
    //insert new incident
	if(!$error) {
	
	
	
	
	$query="INSERT INTO Incident(Username, IncidentDate, Description, Latitude, Longitude) VALUES (?,?,?,?,?)";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("sssss", $cln_username,$incidt,$incidesc,$incilat,$incilng);
	$cln_username =$_SESSION['sess_user'];
    if(!$stmt->execute()) {
		//echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		Print '<script>alert("Database error and Incident not added!");</script>'; // Prompts the user
		Print '<script>window.location.assign("home.php");</script>'; // redirects to home.php
	}
	else {
		Print '<script>alert("Incident added!");</script>'; // Prompts the user
        Print '<script>window.location.assign("home.php");</script>'; // redirects to home.php
	}	
		
		
	}

}

	
?>

<!DOCTYPE html>
<head>

<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  <script>
  $(document).ready(function() {
    $("#incidatepicker").datepicker({ dateFormat: "yy-mm-dd" }).val();
  });
  </script>
    <meta charset="UTF-8">
    <link href="css/newinci.css" rel="stylesheet">
<title> Add New Resource</title>
</head>
<body>


<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <header>
        Add New Incident
    </header>
    <br/>

    <div class = "input-owner">
        <label id="owner-label">Reproted by</label>
        <label><?php echo $_SESSION['usermyname']."<br/>" ; ?></label>
        </div>
    <br>

    <div class="input-date">
        <label id="date-label">Incident Date</label>
        <input id="incidatepicker"  name="incidt" title="YYYY-MM-DD" pattern="(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))" required />
	    <span class="error"> <?php echo $incidtErr;?></span>
        </div>

    <br/>
    <div class="input-description">
        <label id="description-label">Description</label>
        <input type="text" name='incidesc' title="short description" required />
        <span class="error"> <?php echo $descErr;?></span>
        </div>
        <br/>

    <div class="location">
        <label id="location-label">Location</label>
        <br>
        <div class="input-location">
            <label>Latitude</label>
            <input type="text" id="incilat" name="incilat" pattern="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)" title="from -90.000000 to 90.00000" required />
            <span class="error"> <?php echo $latErr;?></span>
            <br>
            <label>Longitude</label>
            <input type="text" id="incilng" name="incilng" pattern="\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)" title="from -180.000000 to -180.000000" required />
            <span class="error"> <?php echo $lngErr;?></span>
            </div>
        <div id="map" style="width:400px;height:300px;"></div>
        </div>
        <hr>
    <div class="buttons">
        <input type="button" name="Cancel" value="Cancel" onclick="window.location='home.php'" />
        <input type="submit" name="savenewinci" value="Save">
        </div>
</form>



<script>

function myMap() {
  var mapCanvas = document.getElementById("map");
  var myCenter=new google.maps.LatLng(43.508742,-80.120850);
  var mapOptions = {center: myCenter, zoom: 5};
  var map = new google.maps.Map(mapCanvas, mapOptions);
 var marker;

function placeMarker(location) {
  if ( marker ) {
    marker.setPosition(location);
  } else {
    marker = new google.maps.Marker({
      position: location,
      map: map
    });
  }
	
}

google.maps.event.addListener(map, 'click', function(event) {
	 var lat = event.latLng.lat().toFixed(6).toString();
	 var lng = event.latLng.lng().toFixed(6).toString();

	 document.getElementById('incilat').value = lat;
	 document.getElementById('incilng').value = lng;
	 
	  placeMarker(event.latLng);

});
}


</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmk_OAaoPdvbYtS-l83reLJ_WYHHRV3UI&callback=myMap"></script>		
	

</body>
</html>


