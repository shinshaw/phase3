<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_rtn=false;
	
	$resid = clean_input($_POST['btnresid']);
	$incid = clean_input($_POST['btnincid']);	

	
	//echo "resource id is ".$resid ;
	//echo "incident id is ".$incid ;
	
	$query="UPDATE Request SET MyStatus='RETURNED' WHERE Request.ResourceID=? and Request.IncidentID=? AND MyStatus='APPROVED' ";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("ss", $resid,$incid);
	
	$query_u1="UPDATE Resource SET MyStatus='AVAILABLE', NextAvailableDate=SYSDATE() WHERE Resource.ResourceID=?";
	$stmt_u1= $DBconn->stmt_init();
	$stmt_u1->prepare($query_u1);
	$stmt_u1->bind_param("s", $resid);
	
	$query_u2="UPDATE Repair SET ReadyDate=date_add(sysdate(), interval datediff(readydate,startdate) day),  StartDate=SYSDATE() Where Repair.ResourceID=? ";
	$stmt_u2= $DBconn->stmt_init();
	$stmt_u2->prepare($query_u2);
	$stmt_u2->bind_param("s", $resid);
	
	$query_u3="UPDATE Resource, Repair SET  Resource.MyStatus='IN REPAIR',  NextAvailableDate=Repair.ReadyDate WHERE Resource.ResourceID=Repair.ResourceID  AND Resource.ResourceID=? AND Resource.MyStatus='AVAILABLE'";
	$stmt_u3= $DBconn->stmt_init();
	$stmt_u3->prepare($query_u3);
	$stmt_u3->bind_param("s", $resid);
	
	
try {
		$DBconn->autocommit(FALSE); // i.e., start transaction
	
	if(!$stmt->execute()) {
		$error_rtn=true;
		//echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		throw new Exception($DBconn->error);
	} 
	
	if(!$stmt_u1->execute()) {
		$error_rtn=true;
		//echo "Execute failed: (" . $stmt_u1->errno . ") " . $stmt_u1->error;
		throw new Exception($DBconn->error);
	} 

	if(!$stmt_u2->execute()) {
		$error_rtn=true;
		//echo "Execute failed: (" . $stmt_u2->errno . ") " . $stmt_u2->error;
		throw new Exception($DBconn->error);
	} 

	if(!$stmt_u3->execute()) {
		$error_rtn=true;
		//echo "Execute failed: (" . $stmt_u3->errno . ") " . $stmt_u3->error;
		throw new Exception($DBconn->error);
	} 
	
	// our SQL queries have been successful. commit them
    // and go back to non-transaction mode.

    $DBconn->commit();
    $DBconn->autocommit(TRUE); // i.e., end transaction
	
	echo "return successs";
}

catch ( Exception $e ) {

    // before rolling back the transaction, you'd want
    // to make sure that the exception was db-related
    $DBconn->rollback(); 
    $DBconn->autocommit(TRUE); // i.e., end transaction   
	echo "return failed";
	}

	
	

?>  
