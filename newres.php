<?php
session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	require_once 'dbconnect.php';
	require_once 'clean_input.php';
	
	//populate the dropdown lists
	$query_esf="SELECT ESFNum, Description FROM ESF";
	$query_costunit="SELECT UnitType FROM CostPerUnit;";
	$result=$DBconn->query($query_esf);
    if($result->num_rows > 0) //IF there are matching user 
	{
		$esflist=[];
		while($row = $result->fetch_assoc()) 
		{
		$esflist[]=$row;
       //	echo "esf#".$row["ESFNum"]."Desc-".$row["Description"];
		}
		//foreach($esflist as $elist) { echo '(#'.$elist["ESFNum"].")".$elist['Description']."||";}

    }
    else
    {
        Print '<script>alert("no esf poplated something is wrong!");</script>'; // Prompts the user
        Print '<script>window.location.assign("logout.php");</script>'; // redirects to login.php
    }

	
	$result=$DBconn->query($query_costunit);
    if($result->num_rows > 0) 
	{
		$costunitlist=[];
		while($row = $result->fetch_assoc()) 
		{
       	$costunitlist[]= $row;
		}
       //foreach($costunitlist as $clist) {	echo '|'.$clist["UnitType"];}
    }
    else
    {
        Print '<script>alert("no esf poplated something is wrong!");</script>'; // Prompts the user
        Print '<script>window.location.assign("logout.php");</script>'; // redirects to login.php
    }
	
	$error=false;
	$error_res=false;
	
	//add new resource
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
	//validate input first
	if (empty($_POST["resname"])) {
		$error=true;
	} else {
		$resname = clean_input($_POST["resname"]);
	}	
	
	if (empty($_POST["primesf"])) {
		$error=true;
	} else {
		$primesf = clean_input($_POST["primesf"]);
	}	
	
	if (empty($_POST["model"])) {
		$error=true;
	} else {
		$model = clean_input($_POST["model"]);
	}		
	
	if (empty($_POST["reslat"])) {
		$error=true;
	} else {
		$reslat = clean_input($_POST["reslat"]);
		$fltlat=(double)$reslat;
		// check if lat is between -90 to 90
		if ($fltlat<-90.0 or $fltlat>90.0)  $error=true;
    
	}		
	
	if (empty($_POST["reslng"])) {
		$error=true;
	} else {
		$reslng = clean_input($_POST["reslng"]);
		$fltlng=(double)$reslng;
		// check if lat is between -180 to 180
		if ($fltlng<-180.0 or $fltlng>180.0)  $error=true;
    
	}
	
	if (empty($_POST["costamt"])) {
		$error=true;
	} else {
		$costamt = clean_input($_POST["costamt"]);
		$fltamt=(double)$costamt;
		// check if positive
		if ($fltamt<-0.0)  $error=true; 
	}
	
	if (empty($_POST["cstunt"])) {
		$error=true;
	} else {
		$cstunt = clean_input($_POST["cstunt"]);
		
	}
	
	if (!$error) {
		
		//echo "\r\n name->".$resname."\r\n";
		//echo "\r\n primesf->".$primesf."\r\n";
		//echo "\r\n addtional ESF-> \r\n";
		//foreach ($_POST['otheresf'] as $selesf) {echo $selesf."|";}
		//echo "\r\n model->".$model."\r\n";
		//echo "\r\n capability \r\n";
		//foreach ($_POST['capblty'] as $selcap) {echo $selcap."\n";}
		//echo "\r\n lat->".$reslat."\r\n";
		//echo "\r\n lng->".$reslng."\r\n";
		//echo "\r\n cost->".$costamt."\r\n";
		//echo "\r\n unit->".$cstunt."\r\n";

		
	
   try {
	   
	   $DBconn->autocommit(FALSE); // i.e., start transaction
	//insert new resource
	$query="INSERT INTO Resource(Username, MyName, PrimeESFNum, Model, MyStatus, NextAvailableDate, CostAmount, CostUnitType, Latitude, Longitude)
VALUES (?, ?, ?, ?, 'AVAILABLE', SYSDATE(), ?, ?, ?, ?)";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("ssssssss", $cln_username,$resname,$primesf,$model,$costamt,$cstunt,$reslat,$reslng);
	$cln_username =$_SESSION['sess_user'];
    if(!$stmt->execute()) {
		$error_res=true;
		//echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        throw new Exception($DBconn->error);
	}
	
	//get last insert  id
	$newid=$DBconn->insert_id;
	
	//insert addtional ESF
	$query_esf="INSERT INTO AdditionalESF (ResourceID,ESFNum) VALUES (?, ?)";
	$stmt_esf= $DBconn->stmt_init();
	$stmt_esf->prepare($query_esf);
	$stmt_esf->bind_param("ss", $newid,$esfnum);
	
	
	$query_cap="INSERT INTO Capability(ResourceID,Description) VALUES (?, ?)";
	$stmt_cap= $DBconn->stmt_init();
	$stmt_cap->prepare($query_cap);
	$stmt_cap->bind_param("ss", $newid,$capdesc);
	
	

	

	//insert addtional esf if selected
	if (empty($_POST["otheresf"])) {
		//echo "no addtional esfs" ;
	} else {
		foreach ($_POST['otheresf'] as $selesf) {
			$esfnum=clean_input($selesf);
			if(!$stmt_esf->execute()) {
				//echo "Execute failed: (" . $stmt_esf->errno . ") " . $stmt_esf->error;
				throw new Exception($DBconn->error);
			} 
		}
		
	}  //end insert addtional esf
	
	//insert capabilityes
	if (empty($_POST["capblty"])) {
		//echo "no capability" ;
	} else {
		foreach ($_POST['capblty'] as $capd) {
			$capdesc=clean_input($capd);
			if(!$stmt_cap->execute()) {
				//echo "Execute failed: (" . $stmt_cap->errno . ") " . $stmt_cap->error;
				throw new Exception($DBconn->error);
			} 
		}
		
	}  //end insert capability
	

	
	// our SQL queries have been successful. commit them
    // and go back to non-transaction mode.
	$DBconn->commit();
    $DBconn->autocommit(TRUE); // i.e., end transaction
	Print '<script>alert("resource added!");</script>'; // Prompts the user
        

   }
	catch ( Exception $e ) {


    $DBconn->rollback(); 
    $DBconn->autocommit(TRUE); // i.e., end transaction   
	Print '<script>alert("database error and resource not added!");</script>'; 
	Print '<script>window.location.assign("home.php");</script>'; 
	
	}
   
	} //end if no error	
	Print '<script>window.location.assign("home.php");</script>'; 
	} //end post 

	
?>

<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<link href="css/newres.css" rel="stylesheet">
	<title> Add New Resource</title>
<script type="text/javascript">
 function clearcap() {
	 localStorage.clear();
 }

//change addtional esf selection list based on primary esf selection 
 function primesfchg(selectObj) { 
 // get the index of the selected option 
 var idx = selectObj.selectedIndex; 
 // get the value of the selected option 
 var which = selectObj.options[idx].value;  
 
 var oSelect = document.getElementById("otheresf"); 
 // remove the current options from the addtional esf select 
 var len=oSelect.options.length; 
 while (oSelect.options.length > 0) { 
 oSelect.remove(0); 
 } 
  var newOption; 
 // create new options 
 for (var i=0; i<selectObj.options.length; i++) { 
 if (i!=idx && !selectObj.options[i].disabled) {
 newOption = document.createElement("option"); 
 newOption.value = selectObj.options[i].value;  // assumes option string and value are the same 
 newOption.text=selectObj.options[i].text; 
 // add the new option 
 try { 
 oSelect.add(newOption);  // this will fail in DOM browsers but is needed for IE 
 } 
 catch (e) { 
 oSelect.appendChild(newOption); 
 } 
 }
 } 
 
 } 

</script>
</head>

<body>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" onsubmit="clearcap()">
	<header> Add New Resource</header>

	<br>

	<div class="input-owner">
		<label id="owner-label"> Owner </label>
		<label><?php echo $_SESSION['usermyname']."<br/>" ; ?></label>
	</div>

	<br>

	<div class = "input-name">
		<label id="name-label">	Resource Name</label>
    	<input name="resname" title="name of resource" required />
	</div>

	<br/>

	<div class = "select-primaryESF">
		<label id="P_ESF-label">Primary ESF</label>
		<select id="primesf" name="primesf" onchange="primesfchg(this);" required>
		<option selected disabled>Choose Primary ESF</option>
		<?php
		foreach($esflist as $elist) {
			echo '<option value = "'.$elist["ESFNum"].'">(#'.$elist["ESFNum"].') '.$elist["Description"].'</option>';
		}
		?>
		</select>
		</div>

	<br/>

	<div class = "select-additionalESFs">
		<label id="A_ESF-label">Additional ESF</label>

		<select id="otheresf" name ="otheresf[]" multiple="multiple" >
		<?php
		foreach($esflist as $elist) {
			echo '<option value = "'.$elist["ESFNum"].'">(#'.$elist["ESFNum"].') '.$elist["Description"].'</option>';
		}
		?>
		</select>
		</div>

	<br/>

	<div class="input-model">
		<label id="model-label">Model</label>
  		<input name="model" title="model of resource" required />
		</div>

	<br/>

	<div class="input-cost">
		<label id = "cost-label"> Cost</label>
		<label id="dollar">$</label>
		<input type="text" name="costamt" pattern="^(?=.*[1-9])\d*(?:\.\d{1,2})?$" title=" positive numbers" required />
		<label id="per">Per</label>
		<select id="unittp" name="cstunt" required>
			<?php
			foreach($costunitlist as $clist) {
				echo '<option value = "'.$clist["UnitType"].'">'.$clist["UnitType"].'</option>';
			}
			?>
		</select>
		</div>

  	<br/>

	<div class="input-capabilities">
		<label id="capabilities-label">Capability</label>
		<input id="capin"><button type="button" id="addcaps" >Add </button>
		<div id="caps"></div>
		</div>


	<br/>

	<div class="location">
		<label id="home-location">Home Location</label>
		<br/>
		<div class="input-location">
			<label>Latitude</label>
			<input type="text" id="reslat" name="reslat" pattern="^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)" title="from -90.000000 to 90.000000" required />
			<br/>
			<label>Longitude</label>
			<input type="text" id="reslng" name="reslng" pattern="\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)" title="from -180.000000 to -180.000000" required />
			</div>
		<div id="map" style="width:500px;height:300px;"></div>
		</div>
	<br>
	<hr>
	<div class="buttons">
		<input type="button" name="Cancel" value="Cancel" onclick="clearcap();window.location='home.php'" />
		<input type="submit" name="savenewres" value="Save">
		</div>
</form>
<script src="addcap.js"></script>


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

	 document.getElementById('reslat').value = lat;
	 document.getElementById('reslng').value = lng;
	 
	  placeMarker(event.latLng);

});
}


</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmk_OAaoPdvbYtS-l83reLJ_WYHHRV3UI&callback=myMap"></script>		


</body>
</html>


