<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_dply=false;
	
	$dply_resid = clean_input($_POST['dply_resid']);
	$dply_incid = clean_input($_POST['dply_incid']);	
	$exprtndt_d = clean_input($_POST['exprtndt_d'])." 23:59:59";	
	
	//echo "resource id is ".$dply_resid ;
	//echo "incident id is ".$dply_incid ;
	//echo "exprtndt  is ".$exprtndt_d ;

	//check if the incident owner is same as resource owner
	
	$query_c="SELECT Incident.Username FROM Incident  WHERE Incident.IncidentID=? AND Incident.Username IN (SELECT Resource.Username FROM Resource WHERE Resource.ResourceID=?) ";
	$stmt_c= $DBconn->stmt_init();
	$stmt_c->prepare($query_c);
	$stmt_c->bind_param("ss", $dply_incid,$dply_resid );


	
	if(!$stmt_c->execute()) {
		$error_dply=true;
		echo "Execute failed: (" . $stmt_c->errno . ") " . $stmt_c->error;

	}
	else {

	$result=$stmt_c->get_result();
	$rowcnt=$result->num_rows;
	
	try {
		
		$DBconn->autocommit(FALSE); // i.e., start transaction
		
	 if($rowcnt > 0) //owners are same, submit request first
	{		
		//echo "submit request first";
		$query_h="INSERT INTO Request(ResourceID, IncidentID, MyStatus, ExpectedReturnDate, ApprovalDate) VALUES(?,?,'SUBMITTED', ?, NULL)";
		$stmt_h= $DBconn->stmt_init();
		$stmt_h->prepare($query_h);
		$stmt_h->bind_param("sss", $dply_resid,$dply_incid ,$exprtndt_d);
	
		if(!$stmt_h->execute()) {
			$error_dply=true;
			//echo "Execute failed: (" . $stmt_h->errno . ") " . $stmt_h->error;
			throw new Exception($DBconn->error);
		}		
	}
	
	// deply th resource

		
		$query_u1="UPDATE Request SET MyStatus='APPROVED',  ApprovalDate=SYSDATE()  WHERE Request .ResourceID=? and Request.IncidentID=?";
		$stmt_u1= $DBconn->stmt_init();
		$stmt_u1->prepare($query_u1);
		$stmt_u1->bind_param("ss",$dply_resid, $dply_incid);
		
		$query_u2="UPDATE Resource, Request  SET Resource.MyStatus='IN USE', NextAvailableDate=Request.ExpectedReturnDate  WHERE  Resource.ResourceID=Request.ResourceID AND Resource.ResourceID=?";
		$stmt_u2= $DBconn->stmt_init();
		$stmt_u2->prepare($query_u2);
		$stmt_u2->bind_param("s",$dply_resid);
		
		if(!$stmt_u1->execute()) {
			$error_dply=true;
			//echo "Execute failed: (" . $stmt_u1->errno . ") " . $stmt_u1->error;
			throw new Exception($DBconn->error);
		} 
		if (!$stmt_u2->execute()) {
			$error_dply=true;
			//echo "Execute failed: (" . $stmt_u2->errno . ") " . $stmt_u->error;
			throw new Exception($DBconn->error);
		}	
		
	// our SQL queries have been successful. commit them
    // and go back to non-transaction mode.

    $DBconn->commit();
    $DBconn->autocommit(TRUE); // i.e., end transaction
	
	echo "deployed";
	
	} catch ( Exception $e ) {

    // before rolling back the transaction, you'd want
    // to make sure that the exception was db-related
    $DBconn->rollback(); 
    $DBconn->autocommit(TRUE); // i.e., end transaction   
	echo "deploy failed";
	}


	
	}

?>  
