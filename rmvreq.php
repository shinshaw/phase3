<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_rmvreq=false;
	
	$resid = clean_input($_POST['btnresid']);
	$incid = clean_input($_POST['btnincid']);	

	
	//echo "resource id is ".$resid ;
	//echo "incident id is ".$incid ;
	
	$query="DELETE FROM  Request  WHERE Request.ResourceID=? and Request.IncidentID=? AND Request.MyStatus='SUBMITTED' ";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("ss", $resid,$incid);
	
	
	
	

	if(!$stmt->execute()) {
		$error_rmvreq=true;
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	} 
	
	if(!$error_rmvreq) {
		echo "request canceled";
	} else  {
		echo "request cancel failed";
	}

?>  
