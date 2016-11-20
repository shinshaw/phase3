<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_appv=false;
	
	$resid = clean_input($_POST['btnresid']);
	$incid = clean_input($_POST['btnincid']);	

	
	//echo "resource id is ".$resid ;
	//echo "incident id is ".$incid ;
	
	$query="UPDATE Request SET MyStatus='APPROVED',  ApprovalDate=SYSDATE()  WHERE Request .ResourceID=? and Request.IncidentID=? AND Request.MyStatus='SUBMITTED'";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("ss", $resid,$incid);
	
	$query_u="UPDATE Resource, Request  SET Resource.MyStatus='IN USE', NextAvailableDate=Request.ExpectedReturnDate  WHERE  Resource.ResourceID=Request.ResourceID AND Resource.ResourceID=? AND Request.IncidentID=? AND Request.MyStatus='APPROVED'"; 
	$stmt_u= $DBconn->stmt_init();
	$stmt_u->prepare($query_u);
	$stmt_u->bind_param("ss", $resid,$incid);
	
	
	try {
		
		$DBconn->autocommit(FALSE); // i.e., start transaction
		
	if(!$stmt->execute()) {
		$error_appv=true;
		//echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		throw new Exception($DBconn->error);
	} 
	
	if(!$stmt_u->execute()) {
		$error_appv=true;
		//echo "Execute failed: (" . $stmt_u->errno . ") " . $stmt_u->error;
		throw new Exception($DBconn->error);
	} 
	
	// our SQL queries have been successful. commit them
    // and go back to non-transaction mode.

    $DBconn->commit();
    $DBconn->autocommit(TRUE); // i.e., end transaction
	
	//echo "request approval successs";



	}
	catch ( Exception $e ) {

    // before rolling back the transaction, you'd want
    // to make sure that the exception was db-related
    $DBconn->rollback(); 
    $DBconn->autocommit(TRUE); // i.e., end transaction   
	//echo "request approval failed";
	}


?>  
