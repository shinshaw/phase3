<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_rmvrpr=false;
	
	$resid = clean_input($_POST['btnresid']);
	$incid = clean_input($_POST['btnincid']);	

	
	//echo "resource id is ".$resid ;
	//echo "incident id is ".$incid ;
	
	$query="UPDATE Resource , Repair SET Resource.NextAvailableDate=Repair.StartDate WHERE Resource.ResourceID=Repair.ResourceID  AND Resource.ResourceID=? AND Resource.MyStatus='IN USE' ";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("s", $resid);
	
	$query_u="DELETE FROM Repair  WHERE Repair.ResourceID=? "; 
	$stmt_u= $DBconn->stmt_init();
	$stmt_u->prepare($query_u);
	$stmt_u->bind_param("s", $resid);
	
	
try {
	
			$DBconn->autocommit(FALSE); // i.e., start transaction
	if(!$stmt->execute()) {
		$error_rmvrpr=true;
		//echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		throw new Exception($DBconn->error);
	} 
	
	if(!$stmt_u->execute()) {
		$error_rmvrpr=true;
		//echo "Execute failed: (" . $stmt_u->errno . ") " . $stmt_u->error;
		throw new Exception($DBconn->error);
	} 
	
	// our SQL queries have been successful. commit them
    // and go back to non-transaction mode.

    $DBconn->commit();
    $DBconn->autocommit(TRUE); // i.e., end transaction
	
	echo "repair  cancel successs";
} catch ( Exception $e ) {

    // before rolling back the transaction, you'd want
    // to make sure that the exception was db-related
    $DBconn->rollback(); 
    $DBconn->autocommit(TRUE); // i.e., end transaction   
		echo "repair  cancel  failed";
	}

?>  
