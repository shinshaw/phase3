<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_reject=false;
	
	$resid = clean_input($_POST['btnresid']);
	$incid = clean_input($_POST['btnincid']);	

	
	//echo "resource id is ".$resid ;
	//echo "incident id is ".$incid ;
	
	$query="UPDATE Request SET MyStatus='REJECTED', ApprovalDate=SYSDATE() WHERE Request.ResourceID=? and Request.IncidentID=? AND Request.MyStatus='SUBMITTED' ";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("ss", $resid,$incid);
	
	
	
	

	if(!$stmt->execute()) {
		$error_reject=true;
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	} 
	
	if(!$error_reject) {
		echo "request rejected";
	} else  {
		echo "request rejection failed";
	}

?>  
