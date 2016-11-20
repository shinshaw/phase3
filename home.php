<?php
session_start();
if (isset($_SESSION['sess_user'])!="") {
 }
else  {
	header("location:index.php");
	}
	//get the user info to display
	require_once 'dbconnect.php';
	$query="SELECT A.MyName, B.displayinfo FROM ERMSUser as A, 
(SELECT Username, '' as displayinfo FROM Individual UNION 
SELECT Username, Concat('Population Size:  ', format(PopulationSize,0)) as displayinfo FROM Municipality UNION
SELECT Username, concat('Head Quarter: ' ,Headquarter) as displayinfo FROM Company UNION
SELECT Username, concat('Jurisdiction: ', Jurisdiction) as displayinfo FROM GovernmentAgency) as B
WHERE A.username=B.username and A.username=?";


	$stmt= $DBconn->stmt_init();
	$stmt->prepare($query);
	$stmt->bind_param("s", $cln_username);
	$cln_username =$_SESSION['sess_user'];
    $stmt->execute();
	$result=$stmt->get_result();
	$rowcnt=$result->num_rows;
    if($rowcnt > 0) 
    {
		while($row = $result->fetch_assoc()) 
		{
       	$_SESSION['userinfo'] = $row['displayinfo']; 
		$_SESSION['usermyname']=$row['MyName'];
		}
       
    }
    else
    {
        Print '<script>alert("User not exist , something is wrong!");</script>'; // Prompts the user
        Print '<script>window.location.assign("logout.php");</script>'; // redirects to login.php
    }

	?>

<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<link href="css/home.css" rel="stylesheet">
	<title> ERMS Menu </title>
</head>
<body>
<section class="menu_list">
	<header>
		ERMS Main Menu
		<div class = "user_info">
			<h2> <?php echo $_SESSION['usermyname']; ?> </h2>
			<h3><?php echo $_SESSION['userinfo']; ?></h3>
		</div>
		</header>
	<nav class="navigation">
		<ul class="menu">
			<li> <a href="newres.php"> Add Resource </a> </li>
			<li> <a href="newinci.php"> Add Emergency Incident </a> </li>
			<li> <a href="searchres.php"> Search Resource </a> </li>
			<li> <a href="ressts.php"> Resource Status </a> </li>
			<li> <a href="resrpt.php"> Resource Report </a> </li>
			<li> <a href="logout.php"> Exit </a> </li>
		</ul>
		</nav>
	</section>
</body>
</html>


