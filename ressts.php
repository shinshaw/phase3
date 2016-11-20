<?php
session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	require_once 'dbconnect.php';

	
	//populate status report
	

	
	//resource in use 
	$query_1="SELECT Request.ResourceID, Resource.MyName as resname, Incident.IncidentID,Incident.Description, ERMSUser.MyName as owner, Request.ApprovalDate as StartDate,  Request.ExpectedReturnDate ,
	case when Request.ExpectedReturnDate<sysdate() then 'Y' else 'N' end as pastdue_flag
	FROM Request, Resource, Incident, ERMSUser  WHERE Request.ResourceID=Resource.ResourceID AND Request.IncidentID=Incident.IncidentID  AND Incident.Username=? AND ERMSUser.Username=Resource.Username AND Resource.MyStatus='IN USE' AND Request.MyStatus='APPROVED'";
	$stmt_1= $DBconn->stmt_init();
	$stmt_1->prepare($query_1);
	$stmt_1->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];

	$stmt_1->execute();
	$result_1=$stmt_1->get_result();
	$rowcnt=$result_1->num_rows;
    if($result_1->num_rows > 0) //IF there are matching user 
	{
		$rsltlist1=[];
		while($row = $result_1->fetch_assoc()) 
		{
		$rsltlist1[]=$row;

		}

    }
    else
    {
		$rsltlist1=array();
 
    }
	
	
	
	//resource request by user
	$query_2="SELECT Request.ResourceID, Resource.MyName as resname, Incident.IncidentID,Incident.Description, ERMSUser.MyName as owner, Request.Mystatus, Request.ExpectedReturnDate , CASE WHEN Request.Mystatus='SUBMITTED'  THEN 'Y' ELSE 'N' END AS Cancel_Req_Enable FROM Request, Resource, Incident, ERMSUser  WHERE Request.ResourceID=Resource.ResourceID AND Request.IncidentID=Incident.IncidentID  AND Incident.Username=? AND ERMSUser.Username=Resource.Username AND Request.MyStatus IN ('SUBMITTED') AND Request.ApprovalDate is NULL ";
	$stmt_2= $DBconn->stmt_init();
	$stmt_2->prepare($query_2);
	$stmt_2->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];
	$stmt_2->execute();
	$result_2=$stmt_2->get_result();
	$rowcnt=$result_2->num_rows;
    if($result_2->num_rows > 0) //IF there are matching user 
	{
		$rsltlist2=[];
		while($row = $result_2->fetch_assoc()) 
		{
		$rsltlist2[]=$row;

		}

    }
    else
    {
		$rsltlist2=array();

    }


	//resource request received
	$query_3="SELECT Request.ResourceID, Resource.MyName as resname, Resource.MyStatus as rstatus, Incident.IncidentID,Incident.Description, ERMSUser.MyName as inciowner, Request.ExpectedReturnDate, CASE WHEN Resource.MyStatus='AVAILABLE' THEN 'Y' ELSE 'N' END AS Deploy_Enable, CASE WHEN Request.MyStatus='SUBMITTED'   then 'Y' ELSE 'N' END AS Reject_Enable FROM Request, Resource, Incident, ERMSUser  WHERE Request.ResourceID=Resource.ResourceID AND Request.IncidentID=Incident.IncidentID AND Resource.Username=? AND ERMSUser.Username=Incident.Username AND Request.MyStatus in ('SUBMITTED')  AND  Request.ApprovalDate is NULL ";
	$stmt_3= $DBconn->stmt_init();
	$stmt_3->prepare($query_3);
	$stmt_3->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];
	$stmt_3->execute();
	$result_3=$stmt_3->get_result();
	$rowcnt=$result_3->num_rows;
    if($result_3->num_rows > 0) //IF there are matching user 
	{
		$rsltlist3=[];
		while($row = $result_3->fetch_assoc()) 
		{
		$rsltlist3[]=$row;

		}

    }
    else
    {
		$rsltlist3= array();

    }
	
	//repair scheduled or in progress
	$query_4="SELECT Repair.ResourceID, Resource.MyName as resname, Resource.MyStatus as rstatus, Repair.StartDate as rstrtdt, 
	Repair.ReadyDate, CASE WHEN Repair.StartDate<=SYSDATE() AND  Resource.MyStatus='IN USE' then 'Y' else 'N' end as pastdue_flag,
	CASE WHEN Repair.StartDate>SYSDATE() OR Resource.MyStatus='IN USE' then 'Y' Else 'N' end as Cancel_Rpr_Enable FROM Repair, Resource  WHERE Repair.ResourceID=Resource.ResourceID AND Resource.Username=? ";
	$stmt_4= $DBconn->stmt_init();
	$stmt_4->prepare($query_4);
	$stmt_4->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];
	$stmt_4->execute();
	$result_4=$stmt_4->get_result();
	$rowcnt=$result_4->num_rows;
    if($result_4->num_rows > 0) //IF there are matching user 
	{
		$rsltlist4=[];
		while($row = $result_4->fetch_assoc()) 
		{
		$rsltlist4[]=$row;

		}

    }
    else
    {
		$rsltlist4= array();

    }
	
	//resource request by user rejected
	$query_5="select Y.* FROM

(SELECT Request.ResourceID, Resource.MyName as resname, Incident.IncidentID,Incident.Description, ERMSUser.MyName as owner, Request.Mystatus, Request.ExpectedReturnDate , Request.ApprovalDate as RejectDate FROM Request, Resource, Incident, ERMSUser  WHERE Request.ResourceID=Resource.ResourceID AND Request.IncidentID=Incident.IncidentID  AND Incident.Username=? AND ERMSUser.Username=Resource.Username AND Request.MyStatus IN ('REJECTED')) Y left join (SELECT Request.ResourceID, Request.IncidentID, Request.MyStatus FROM Request WHERE Request.MyStatus in ('APPROVED','RETURNED')) Z
on Y.ResourceID=Z.ResourceID AND Y.IncidentID=Z.IncidentID where Z.MyStatus is NULL";
	$stmt_5= $DBconn->stmt_init();
	$stmt_5->prepare($query_5);
	$stmt_5->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];
	$stmt_5->execute();
	$result_5=$stmt_5->get_result();
	$rowcnt=$result_5->num_rows;
    if($result_5->num_rows > 0) //IF there are matching user 
	{
		$rsltlist5=[];
		while($row = $result_5->fetch_assoc()) 
		{
		$rsltlist5[]=$row;

		}

    }
    else
    {
		$rsltlist5=array();

    }

	
	
?>

<!DOCTYPE html>
<head>
<meta charset="utf-8">
	<link href="css/ressts.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">

<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>   
 <script src="stsbtn.js"></script> 
  <script src="sorttable.js"></script>
<title> Resource Status</title>


</head>

<body>
<section>
	<header> ERMS Resource Status
		<div class = "user_info">
			<?php echo $_SESSION['usermyname']; ?>
		</div>
	</header>

<br/>

	<p  type="hidden" id="btnresid" name=""> </p>
	<p  type="hidden" id="btnincid" name=""> </p>
	<p id="suggestion">  </p>

<H2>  Resource IN USE </h2>

<Table class="sortable">

<?php
	 echo "<tr class='title'><th>ID</th><th>Resource Name</th><th>Incident</th><th>Owner</th><th>Start Date</th><th>Return By</th><th> Action </th></tr>";

		foreach($rsltlist1 as $list1) {
		echo "<tr><td>".$list1["ResourceID"]."</td><td>".$list1["resname"]."</td><td>".$list1["Description"]."</td><td>".$list1["owner"]."</td><td>".$list1["StartDate"]."</td><td>";
		
		echo $list1["ExpectedReturnDate"];
		if($list1["pastdue_flag"]=='Y') { echo "<span style='color:red;font-weight: bold;'> (PAST DUE)</span>";}
		
		echo "</td><td>";
		echo "<button class='returnbtn' value=".$list1['ResourceID']." name=".$list1['IncidentID'].">Return</button>";
		echo "</td></tr>";
		}



?>

</table>

<hr/> 
<H2>  Resource requested by me </h2>
<table class="sortable">
<?php
	 echo "<tr class='title'><th>ID</th><th>Resource Name</th><th>Incident</th><th>Owner</th><th>Return By</th><th> Action </th></tr>";

	 foreach($rsltlist2 as $list2) {
		echo "<tr><td>".$list2["ResourceID"]."</td><td>".$list2["resname"]."</td><td>".$list2["Description"]."</td><td>".$list2["owner"]."</td><td>".$list2["ExpectedReturnDate"]."</td><td>";
		if ($list2["Cancel_Req_Enable"]=='Y') {
		echo "<button class='rmvreqbtn' value=".$list2["ResourceID"]." name=".$list2['IncidentID']." >Cancel</button>";
		} else {
			
			echo $list2["Mystatus"];
		}
			
		
		echo "</td></tr>";
		} 	 


?>

</table>

<hr/> 
<H2>  Resource requested Received By me </h2>
<table class="sortable">
<?php
	 echo "<tr class='title'><th>ID</th><th>Resource Name</th><th>Incident</th><th>Requested By</th><th>Return By</th><th> Action</th></tr>";

		foreach($rsltlist3 as $list3) {
			echo "<tr><td>".$list3["ResourceID"]."</td><td>".$list3["resname"]."</td><td>".$list3["Description"]."</td><td>".$list3["inciowner"]."</td><td>".$list3["ExpectedReturnDate"]."</td><td>";
		
		if ($list3["Deploy_Enable"]=='Y') {
			echo "<button class='appvreqbtn' value=".$list3["ResourceID"]." name=".$list3['IncidentID'].">Deploy</button>";
		} else {
			echo $list3["rstatus"];
		} 
		
		
		if ($list3["Reject_Enable"]=='Y') {
			echo "<button class='rjtreqbtn' value=".$list3["ResourceID"]." name=".$list3['IncidentID'].">Reject</button>";
		}
		
		echo "</td></tr>";		
			
			
		}


		 
?>

</table>

<hr/> 
<H2>  Repair Scheduled/In Progress </h2>
<table class="sortable">
<?php
	 echo "<tr class='title'><th>ID</th><th>Resource Name</th><th>Resource Status</th><th>Start On</th><th>Ready By</th><th> Action</th></tr>";
		foreach($rsltlist4 as $list4) {
			echo "<tr><td>".$list4["ResourceID"]."</td><td>".$list4["resname"]."</td><td>".$list4["rstatus"]."</td><td>";
			
			echo $list4["rstrtdt"];
			
			if($list4["pastdue_flag"]=='Y') { echo "<span style='color:red;font-weight: bold;'> (PAST DUE)</span>";}
			
			echo "</td><td>".$list4["ReadyDate"]."</td><td>";
			
		if ($list4["Cancel_Rpr_Enable"]=='Y') {
			echo "<button class='rmvrprbtn' value=".$list4["ResourceID"]." >Cancel</button>";
				
		} else {
			echo $list4["rstatus"];
		}
		
		
		echo "</td></tr>";
		}
		 
?>

</table>

<hr/> 
<H2>  Request From Me With Lastest Status As REJECTED </h2>
<table class="sortable">
<?php
	 echo "<tr class='title'><th>ID</th><th>Resource Name</th><th>Incident</th><th>Owner</th><th>Return By</th><th> Status </th><th> REJECTED ON </th></tr>";

	 foreach($rsltlist5 as $list5) {
		echo "<tr><td>".$list5["ResourceID"]."</td><td>".$list5["resname"]."</td><td>".$list5["Description"]."</td><td>".$list5["owner"]."</td><td>".$list5["ExpectedReturnDate"]."</td><td>".$list5["Mystatus"]."</td><td>".$list5["RejectDate"]."</td></tr>";
		} 	 

?>

</table>

<br/>
<div class="buttons">
<input type="button" name="Cancel" value="Close" onclick="window.location='home.php'" />
</div>

</section>

</body>
</html>


