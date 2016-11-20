<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_req=false;
	
	$query="INSERT INTO Request(ResourceID, IncidentID, MyStatus, ExpectedReturnDate, ApprovalDate) VALUES(?,?,'SUBMITTED', ?, NULL)";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("sss", $req_resid,$req_incid,$exprtndt);
	$req_resid = clean_input($_POST['req_resid']);
	$req_incid = clean_input($_POST['req_incid']);	
	$exprtndt = clean_input($_POST['exprtndt'])." 23:59:59";	

  
    if(!$stmt->execute()) {
		$error_req=true;
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;

	}
	else {

        echo "request submitted sucesss";
	}	
	
 
  

?>  
