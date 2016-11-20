<?php  

session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	
	require_once 'dbconnect.php'; 
	require_once 'clean_input.php';
	$error_rpr=false;
	
	$rpr_resid = clean_input($_POST['rpr_resid']);
	$rpr_dur = clean_input($_POST['duration']);	
	 
	//echo "resource id is ".$rpr_resid ;
	//echo "iduration is ".$rpr_dur ;
	
	$query="SELECT Resource.MyStatus as sts FROM Resource WHERE Resource.ResourceID=? ";
	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("s", $rpr_resid);



  if(!$stmt->execute()) {
		$error_rpr=true;
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;

	}
	else {

	$result=$stmt->get_result();
	$rowcnt=$result->num_rows;
	    
     if($rowcnt > 0) 
	{
		// get current status of resource
		while($row = $result->fetch_assoc()) 
		{
		$res_sts=$row["sts"];
		}

		//insert repair
		$query_r="INSERT INTO  Repair(ResourceID, StartDate, ReadyDate) SELECT Resource.ResourceID, Greatest(Resource.NextAvailableDate,SYSDATE()), Date_Add(Greatest(Resource.NextAvailableDate,SYSDATE()), interval ? day) FROM Resource WHERE Resource.ResourceID=?  ";
		$stmt_r= $DBconn->stmt_init();
		$stmt_r->prepare($query_r);
		$stmt_r->bind_param("ss", $rpr_dur,$rpr_resid);
		
		
		try {
			$DBconn->autocommit(FALSE); // i.e., start transaction
			
		if(!$stmt_r->execute()) {
				$error_rpr=true;
			//echo "Execute failed: (" . $stmt_r->errno . ") " . $stmt_r->error;
			throw new Exception($DBconn->error);
			}
		
		
		//update resource based on status
		if ($res_sts=="IN USE") {
						 //echo "resource status is ".$res_sts;
						 
						 
						 $query_u="UPDATE Resource, Repair SET  NextAvailableDate=Repair.ReadyDate WHERE Resource.ResourceID=Repair.ResourceID  AND Resource.ResourceID=? AND Resource.MyStatus='IN USE'";
						$stmt_u= $DBconn->stmt_init();
						$stmt_u->prepare($query_u);
						$stmt_u->bind_param("s", $rpr_resid);
						if(!$stmt_u->execute()) {
							$error_rpr=true;
							//echo "Execute failed: (" . $stmt_u->errno . ") " . $stmt_u->error;
							throw new Exception($DBconn->error);
							}
										
			}
			else  if ($res_sts=="AVAILABLE") {
						 //echo "resource status is".$res_sts;
						 $query_u="UPDATE Resource, Repair SET  Resource.MyStatus='IN REPAIR',  NextAvailableDate=date_add(sysdate(), interval datediff(Repair.readydate,Repair.startdate) day) WHERE Resource.ResourceID=Repair.ResourceID  AND Resource.ResourceID=? And Resource.MyStatus='AVAILABLE'";
						$stmt_u= $DBconn->stmt_init();
						$stmt_u->prepare($query_u);
						$stmt_u->bind_param("s", $rpr_resid);
						if(!$stmt_u->execute()) {
							$error_rpr=true;
							//echo "Execute failed: (" . $stmt_u->errno . ") " . $stmt_u->error;
							throw new Exception($DBconn->error);
							}				 
			}
			else {
						 echo "resource is in currently repair";
			}
			
		
	// our SQL queries have been successful. commit them
    // and go back to non-transaction mode.

		$DBconn->commit();
		$DBconn->autocommit(TRUE); // i.e., end transaction
	
		echo "repair schedule successs";
		
		}
	catch ( Exception $e ) {

    // before rolling back the transaction, you'd want
    // to make sure that the exception was db-related
    $DBconn->rollback(); 
    $DBconn->autocommit(TRUE); // i.e., end transaction   
	echo "repair schedule failed";
	}

	
    }  //if find matching resource
    else
    {
       echo "no resource found";
    }
	
	

	} 
  
   
  
?>  
