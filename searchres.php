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
	$result=$DBconn->query($query_esf);
    if($result->num_rows > 0)  
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

	
	$query_inci="SELECT incidentid, Description from Incident WHERE Incident.Username=?";
	$stmt_inci= $DBconn->stmt_init();
	$stmt_inci->prepare($query_inci);
	$stmt_inci->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];
	$stmt_inci->execute();
	$result_inci=$stmt_inci->get_result();
	$rowcnt_inci=$result_inci->num_rows;
	    
     if($rowcnt_inci > 0) //IF there is at least one incident 
	{
		$incilist=[];
		while($row = $result_inci->fetch_assoc()) 
		{
		$incilist[]=$row;

		}


    }
    else
    {
		//empty incident list
		$incilist=array();
    }
	
	
	
	
	$error=false;
	// define variables and set to empty values
	$selinci  = "";
	$selinci_desc="";
	$slist_1=array();
	$slist_2=array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	

	//clean input;
	
	//validate input first
	if (empty($_POST["searchkey"])) {
		$searchkey="";
	} else {
		$searchkey = clean_input($_POST["searchkey"]);
	}	
	
	if (empty($_POST["selesf"])) {
		$selesf ="";
	} else {
		$selesf = clean_input($_POST["selesf"]);
	}
	
	if (empty($_POST["maxdist"])) {
		$maxdist=10000;
	} else {
		$maxdist = clean_input($_POST["maxdist"]);
	}
	
	if (empty($_POST["selinci"])) {
		$selinci="";
	} else {
		$selinci = clean_input($_POST["selinci"]);
	}

	
	if(empty($selinci)) { 
		$query_s2=" SELECT Resource.Resourceid as rid, Resource.MyName as resname, ERMSUser.MyName as Owner, ERMSUser.Username as resuser ,concat(format(Resource.CostAmount,2),'/', Resource.CostUnitType) as Cost, Resource.MyStatus as rstatus,Resource.NextAvailableDate,
		case when Resource.NextAvailableDate<sysdate() and Resource.MyStatus='IN USE' then 'Y' else 'N' end as pastdue_flag,
		CASE  WHEN Resource.ResourceID IN (SELECT ResourceId FROM Repair) then 'Y' Else 'N' END  As Repair_Flag,
		CASE  WHEN Resource.Username=? AND Resource.ResourceID NOT IN (SELECT ResourceId FROM Repair) then 'Y' Else 'N' END  As Repair_Enable
		FROM Resource , ERMSUser 
		WHERE  Resource.Username=ERMSUser.Username AND Resource.Resourceid in (
		SELECT Distinct Resource.Resourceid from Resource, Capability, AdditionalESF 
		WHERE (Resource.PrimeESFNum=?  OR (Resource.ResourceID=AdditionalESF.ResourceID AND AdditionalESF.ESFNum=?  ) OR ?='' )
		AND ( (Resource.ResourceID=Capability.ResourceID and Capability.Description LIKE concat('%',?,'%')  ) 
		OR  Resource.Model LIKE concat('%',?,'%') OR Resource.MyName LIKE  concat('%',?,'%')  OR ?='' ) ) ORDER BY  Resource.MyName ";
	$stmt_s2= $DBconn->stmt_init();
	$stmt_s2->prepare($query_s2);
	$stmt_s2->bind_param("ssssssss", $cln_username,$selesf,$selesf,$selesf,$searchkey,$searchkey,$searchkey,$searchkey);
	$cln_username =$_SESSION['sess_user'];
	$stmt_s2->execute();
	$result_s2=$stmt_s2->get_result();
	$rowcnt_s2=$result_s2->num_rows;
	    
     if($rowcnt_s2 > 0) //IF there is at least one incident 
	{
		$slist_2=[];
		while($row = $result_s2->fetch_assoc()) 
		{
		$slist_2[]=$row;

		}


    }
    else
    {
		//empty incident list
		$slist_2=array();
    }
	
	
	
	} //no incident selected 
	else {

	$query_s1="SELECT L.rid, L.MyName as resname, L.Owner, L.description, L.Cost, L.MyStatus as rstatus,  L.NextAvailableDate, L.distance, R.MyStatus as reqsts, L.Repair_Flag, L.Deploy_Flag,
case when L.NextAvailableDate<sysdate() and L.MyStatus='IN USE' then 'Y' else 'N' end as pastdue_flag,
case when L.Deploy_Flag='Y' AND L.MyStatus='AVAILABLE' AND R.MyStatus is NULL THEN 'Y' ELSE 'N' END AS Deploy_Enable, L. Repair_Enable,
case when R.MyStatus is NULL AND L.MyStatus!='IN REPAIR' AND L.Deploy_Flag!='Y' THEN 'Y' ELSE 'N' END  As Request_Enable	FROM 
(SELECT Resource.Resourceid as rid, Resource.MyName , ERMSUser.MyName as Owner, Incident.description, concat(format(Resource.CostAmount,2),'/',Resource.CostUnitType) as Cost, Resource.MyStatus, Resource.NextAvailableDate,  @d1 :=radians(resource.Latitude-Incident.Latitude), @d2 :=radians(Resource.longitude-Incident.Longitude), 
@A :=SIN(@d1/2)*SIN(@d1/2)+COS(radians(Resource.Latitude))*COS(radians(Incident.Latitude))*SIN(@d2/2)*SIN(@d2/2), 
@C :=2*ATAN2(SQRT(@A),SQRT(1-@A)), round(6371*@C,2) as distance, 
CASE  WHEN  Resource.Username=?  AND Incident.Username=? then 'Y' Else 'N' END  As Deploy_Flag, 
case when Resource.ResourceID IN (SELECT ResourceId FROM Repair) then 'Y' Else 'N' END  As Repair_Flag,
CASE  WHEN Resource.Username=? AND Resource.ResourceID NOT IN (SELECT ResourceId FROM Repair) then 'Y' Else 'N' END  As Repair_Enable
FROM Resource , ERMSUser ,  Incident 
WHERE Resource.Username=ERMSUser.Username AND Incident.IncidentID=?   AND 
Resource.ResourceID IN ( 
SELECT Distinct Resource.Resourceid from Resource, Capability, AdditionalESF 
WHERE (Resource.PrimeESFNum=?  OR (Resource.ResourceID=AdditionalESF.ResourceID AND AdditionalESF.ESFNum=?  ) OR ?='' )
 AND ( (Resource.ResourceID=Capability.ResourceID and Capability.Description LIKE concat('%',?,'%')  ) 
 OR  Resource.Model LIKE concat('%',?,'%') OR Resource.MyName LIKE  concat('%',?,'%') OR ?=''  ) ) ) L left join (select request.ResourceID, request.IncidentID, request.MyStatus from request where request.MyStatus!='REJECTED' and request.incidentid=? ) R  
 on L.rid=R.ResourceID  WHERE L.Distance<? ORDER BY L.distance, L.MyName  ";
	$stmt_s1= $DBconn->stmt_init();
	$stmt_s1->prepare($query_s1);
	$stmt_s1->bind_param("sssssssssssss", $cln_username,$cln_username,$cln_username,$selinci,$selesf,$selesf,$selesf,$searchkey,$searchkey,$searchkey,$searchkey,$selinci,$maxdist);
	$cln_username =$_SESSION['sess_user'];
	$stmt_s1->execute();
	$result_s1=$stmt_s1->get_result();
	$rowcnt_s1=$result_s1->num_rows;
	    
     if($rowcnt_s1 > 0) 
	{
		$slist_1=[];
		while($row = $result_s1->fetch_assoc()) 
		{
		$slist_1[]=$row;
		$selinci_desc=$row["description"];
		}
    }
    else
    {
		//empty incident list
		$slist_1=array();
    }
	} //incident selected 
	

	
}

	
?>

<!DOCTYPE html>
<head>
<meta charset="utf-8">
	 <link href="css/searchres.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">

	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>  
  <script src="modalbtn.js"></script>  
   <script src="sorttable.js"></script>
<title> Search Resource</title>
</head>
<body>

<section>
	<header>
		ERMS Search Resource
		<div class = "user_info">
			 <?php echo $_SESSION['usermyname']; ?>
		</div>
	</header>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
	<h1> Search Resource</h1>
	<hr>
	<div class="keyword-input">
		<label>Keyword</label>
		<input style="display: inline;" type="text" name='searchkey' title="Keyword " value="<?php  if (isset($_POST['searchkey'])) echo htmlspecialchars($_POST['searchkey']); ?>" />
	</div>

		<br/>

	<div class="ESF-input">
		<label>ESF</label>

		<select id="selesf" name="selesf" value=2 >
			<option label=" " value=0 >no selection</option>
			<?php
			foreach($esflist as $elist) {
				echo '<option  value = "'.$elist["ESFNum"].'"';
				if (isset($_POST['selesf']) && $elist["ESFNum"]==htmlspecialchars($_POST['selesf'])) echo "selected='selected'";
				echo  	'>(#'.$elist["ESFNum"].') '.$elist["Description"];
				echo '</option>';
			}
			?>

		</select>
		</div>

		<br/>
		<div class="location">
			<label id="location-label">Location</label>
			<div>
				Within<input style="display: inline;" type="text" name="maxdist" pattern="^(?=.*[1-9])\d*(?:\.\d{1,2})?$" title="positive number" value="<?php  if (isset($_POST['maxdist'])) echo htmlspecialchars($_POST['maxdist']); ?>"/>Kilometers of incident
			</div>
			</div>
		<br/>
		<div class="incident">
		<label id="incident-label">Incident</label>
		<select id="selinci" name="selinci"  >
			<option label=" " value=0 > no selection</option>
			<?php
			foreach($incilist as $ilist) {
				echo '<option value = "'.$ilist["incidentid"].'"';
				if (isset($_POST['selinci']) && $ilist["incidentid"]==htmlspecialchars($_POST['selinci'])) echo "selected='selected'";
				echo '>(#'.$ilist["incidentid"].') '.$ilist["Description"].'</option>';
			}
			?>
			</select>
		</div>
	<div class="buttons">
		<input  style="display: inline;"  type="button" name="Cancel" value="Cancel" onclick="window.location='home.php'" />
		<input style="display: inline;" type="submit" name="search" value="Search">
	</div>
</form>

 <p id="suggestion">  </p>
<hr/> 

<?php 

if (empty($selinci)) {
	echo "<h2 >  Search Result: </h2>";
	echo "<input id='selinciid' type='hidden' name=''>";
	
	echo "<Table class='sortable'>";
	echo "<tr id='title'><th>ID</th><th>Name</th><th>Owner</th><th>COST</th><th>Status</th><th>Next Available Date</th><th> Repair </th></tr>";

		foreach($slist_2 as $lst2) {
		echo "<tr><td>".$lst2["rid"]."</td><td>".$lst2["resname"]."</td><td>".$lst2["Owner"]."</td><td>".$lst2["Cost"]."</td><td>".$lst2["rstatus"]."</td><td>";
		
		if ($lst2["rstatus"]=='AVAILABLE') { echo "NOW";
		} else {		
			echo $lst2["NextAvailableDate"];
			if($lst2["pastdue_flag"]=='Y') { echo "<span style='color:red;font-weight: bold;'> (PAST DUE)</span>";}
		}
		echo "</td><td>";
		
		if ($lst2["Repair_Enable"]=='Y') {
			echo "<button class='repairbtn' value=".$lst2["rid"]." >Repair</button>";
		} else {
			 if ($lst2["rstatus"]=='IN REPAIR') {echo "IN REPAIR";
			 }
			   else {
				   
				   if ($lst2["Repair_Flag"]=='Y') {echo "Repair Scheduled";}
				   else {  echo "Not Owner";}
			 };
		}
		echo "</td></tr>";
		
		}//foreach end
		
echo "</Table>";
}
else {
	echo "<h2>  Search Result for Incident: </h2> ";
	
	echo "<h2>".$selinci_desc."(".$selinci.")</h2>";
	echo "<input id='selinciid' type='hidden' name=".$selinci.">";

 
echo "<Table class='sortable'>";
	 echo "<tr id='title'><th>ID</th><th>Name</th><th>Owner</th><th>COST</th><th>Status</th><th>Next Available Date</th><th> Distance</th><th>Request </th><th>Repair </th><th> Deploy </th></tr>";

		foreach($slist_1 as $lst1) {
		echo "<tr><td>".$lst1["rid"]."</td><td>".$lst1["resname"]."</td><td>".$lst1["Owner"]."</td><td>".$lst1["Cost"]."</td><td>".$lst1["rstatus"]."</td><td>";
		
		if ($lst1["rstatus"]=='AVAILABLE') { echo "NOW";
		} else {		
			echo $lst1["NextAvailableDate"];
			if($lst1["pastdue_flag"]=='Y') { echo "<span style='color:red;font-weight: bold;'> (PAST DUE)</span>";}

		}
		echo "</td><td>";
		
		
		echo $lst1["distance"]."</td><td>";
				
		
		
		if ($lst1["Request_Enable"]=='Y') {
			echo "<button class='requestbtn' value=".$lst1["rid"]." name=".$lst1["NextAvailableDate"]." >Request</button>";
		} else 
		{
			if ($lst1["Deploy_Flag"]=='Y') {
				echo "Owner N/A";
			} else 
			{    if ($lst1["rstatus"]=='IN REPAIR') { echo "IN REPAIR";}
				else { 
				if ($lst1["reqsts"]=='RETURNED' ){
				 echo "Deployed Before";
				}
				
				else {echo $lst1["reqsts"]; }
				}
			}
					 
			
		} 
			 
		 
		
		
		echo "</td><td>";
		

		
		if ($lst1["Repair_Enable"]=='Y') {
			echo "<button class='repairbtn' value=".$lst1["rid"]." >Repair</button>";
		} else { 
			if ($lst1["rstatus"]=='IN REPAIR') {echo "IN REPAIR";
			 }
			   else {
				   
				   if ($lst1["Repair_Flag"]=='Y') {echo "Scheduled";}
				   else {  echo "Not Owner";}
			 };
		
		}
		echo "</td><td>";
		
		if ($lst1["Deploy_Enable"]=='Y') {
			echo "<button class='deploybtn' value=".$lst1["rid"]." name=".$lst1["NextAvailableDate"]." >Deploy</button>";
		}  else {
			if ($lst1["rstatus"]!='AVAILABLE') {echo $lst1["rstatus"];} 
			
			else {
				if ($lst1["Deploy_Flag"]=='Y' && $lst1["reqsts"]=='RETURNED' ){
				 echo "Deployed Before";
				}
							
				else {echo "not owner";}		 
			}
			
			
		}
		
		echo "</td></tr>";
		
		}//end foreach
		
echo "</Table>";
}
?>

</table>

	<br/>
	<hr/>
	<div>
		<input id="close-button" type="button" name="Cancel" value="Close" onclick="window.location='home.php'" />
	</div>
</section>

<div id="repair-form" title="Schedule Repair">
  <p class="validateTips">Please enter number of days in repair.</p>
   <form>
    <fieldset>
	  <label for="duration">Repair Duration in Days</label>
      <input type="text" name="duration" id="duration" value="" class="text ui-widget-content ui-corner-all">
	  <input type="hidden" id="rpr_rid" name="">
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div>


<div id="request-form"  title="Request">
  <p class="validateTips">Please enter the expected return date</p>
   <form>
    <fieldset>
	  <label for="exprtndt">Expected Return Date</label>
	  <input type="hidden" id="req_rid" name="">
	  <input type="hidden" id="req_nad" name="">
      <input name="exprtndt" id="exprtndt" value="" class="text ui-widget-content ui-corner-all">
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div>

<div id="deploy-form"  title="Request">
  <p class="validateTips">Please enter the expected return date</p>
   <form>
    <fieldset>
	  <label for="exprtndt">Expected Return Date</label>
	  <input type="hidden" id="dply_rid" name="">
	  <input type="hidden" id="dply_nad" name="">
      <input name="exprtndt_d" id="exprtndt_d" value="" class="text ui-widget-content ui-corner-all">
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div> 



</body>
</html>


