<?php
    session_start();
	require_once 'dbconnect.php';
	require_once 'clean_input.php';
	$error_login="";
	if ($_SERVER["REQUEST_METHOD"] == "POST") {	
		$query="SELECT * FROM ERMSUser WHERE username=? AND Mypassword=?";
		$stmt= $DBconn->stmt_init();
		$stmt->prepare($query);
		$stmt->bind_param("ss", $cln_username,$hash);
		$cln_username =clean_input($_POST['loginname']) ;
		$cln_pwd = clean_input($_POST['loginpwd']);
		//use username as salt
		$hash=MD5($cln_username.$cln_pwd);
	
		$stmt->execute();
		$result=$stmt->get_result();
		$rowcnt=$result->num_rows;
	    
		if($rowcnt > 0) //IF there are matching user and password
		{
			$_SESSION['sess_user'] = $cln_username; //set the username in a session. This serves as a global variable
			header("location: home.php");  //then redirects the user to the authenticated home page   
		}  else    {
		$error_login="Incorrect username or password! Please try again.";
		}
	}
?>

<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<link href="css/index.css" rel="stylesheet">
	<title> Login to ERMS </title>
</head>
<body>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<header>Emergency Resource Management System </header>

	<label>Username</label>
	<div class="input-username">
		<input type="text" pattern="[A-Za-z0-9_]+" title="only letters and numbers" name="loginname" placeholder="Enter Username" required />
	</div>

	<label>Password</label>
	<div class="input-password">
		<input type="password" pattern=".{5,}" title="five or more characters" name="loginpwd" placeholder="Enter Password" required />
	</div>

<p id="suggestions"> <?php echo $error_login ?> </p>
	<input id="submit_button" type="submit" value="Login"/>

</form>

</body>
</html>