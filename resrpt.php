<?php
session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	
	require_once 'dbconnect.php';

	
	//populate status report
	

	
	//resource summary by primary esf
	$query_1="SELECT ESF.ESFNum,ESF.Description , IFNULL(S.TotalResource,0) as TotalResource, IFNULL(S.ResourceInUse,0) as ResourceInUse  from ESF LEFT JOIN 
	(SELECT PrimeESFNum, count(*) as TotalResource, SUM(CASE WHEN Resource.MyStatus='IN USE' then 1 else 0 end) as ResourceInUse FROM Resource WHERE Resource.Username=? GROUP BY PrimeESFNum ) S 
	ON S.PrimeESFNum=ESF.ESFNum ORDER BY ESF.ESFNum";
	$stmt_1= $DBconn->stmt_init();
	$stmt_1->prepare($query_1);
	$stmt_1->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];

	$stmt_1->execute();
	$result_1=$stmt_1->get_result();
	$rowcnt=$result_1->num_rows;
    if($result_1->num_rows > 0) 
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
	
	
	
	//resource total
	$query_2="SELECT count(*) as Totalres, SUM(CASE WHEN Resource.MyStatus='IN USE' then 1 else 0 end) as totInUse FROM Resource, ESF WHERE Resource.Username=? and Resource.PrimeESFNum=ESF.ESFNum ";
	$stmt_2= $DBconn->stmt_init();
	$stmt_2->prepare($query_2);
	$stmt_2->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];
	$stmt_2->execute();
	$result_2=$stmt_2->get_result();
	$rowcnt=$result_2->num_rows;
    if($result_2->num_rows > 0) //IF there are matching user 
	{
		
		while($row = $result_2->fetch_assoc()) 
		{
		$totalres=$row["Totalres"];
		$totinuse=$row["totInUse"];

		}

    }
    else
    {
		$totalres=0;
		$totinuse=0;
		
        Print '<script>alert("no resouce request by me  poplated something is wrong!");</script>'; // Prompts the user
    }



	
	
	
?>

<!DOCTYPE html>
<head>

<title> Resource Report</title>
	<meta charset="utf-8">
	<link href="css/resrpt.css" rel="stylesheet">

</head>

<body>
<section>
<header>  Resource Report by Primary Emergency Support Function
	<div class = "user_info">
		<?php echo $_SESSION['usermyname']; ?>
	</div>
</header>
<br>

<Table>

<?php
	 echo "<tr class='title'><th>#</th><th>Primary Emergency Support Function</th><th>Total Resources</th><th>Resource in Use </th></tr>";

	foreach($rsltlist1 as $list1) {
		echo "<tr><td>".$list1["ESFNum"]."</td><td>".$list1["Description"]."</td><td>".$list1["TotalResource"]."</td><td>".$list1["ResourceInUse"]."</td></tr>";
		}
     echo "<tr class='title'><td></td><td>TOTAL</td><td>".$totalres."</td><td>".$totinuse."</td></tr>";


?>

</table>



<br/>
	<div class="buttons">
<input type="button" name="Cancel" value="Close" onclick="window.location='home.php'" /> 
</div>
</section>
</body>
</html>


