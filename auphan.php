<?php

// Config file for misc and senstitive information
include 'config.php';

session_start();

class Application {
	// Used for handling database and misc data
	public function __construct($config, $server) {
		$this->__buildConfig($config);
		$this->__buildServer($server);
	}
	private function __buildConfig($config) {
		// Initializes Misc App data
		$this->name = $config['name'];
		$this->author = $config['author'];
	}
	private function __buildServer($server) {
		// Initializes database connection
		try {
			// "serves" the database (haha get it)
			$this->serve = new PDO("mysql:host=" . $server['host'] . ";dbname=" . $server['database'], $server['username'], $server['password']);
			$this->serve->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
}
class Authentication {
	// Used for handling user sessions and data
	public function __construct($serve) {
		$this->serve = $serve;
	}

	private function query_user($username) {
		// Queries the database to find User by Username
		// Returns queried user for further use
		try {
			$query = $this->serve->prepare('SELECT * FROM users WHERE username = :username');
			$query->bindValue(':username', $username);
			$query->execute();
			$user = $query->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			echo $query . "<br>" . $e->getMessage();
		}
		return $user;
	}

	public function create_user($username, $password) {
		// Not functionally employed in the endpoint of this project
		// This function was created to create the initial user data
		// As well as for demonstrating my capability in doing so

		$pw_hash = password_hash($password, PASSWORD_DEFAULT);
		if(empty($this->query_user($username))) {
			try {
				$sql = "INSERT INTO users (`username`, `password`) VALUES ('" .$username ."', '" .$pw_hash ."')";
				$query = $this->serve->exec($sql);
			} catch(PDOException $e) {
				echo $query . "<br>" . $e->getMessage();
			}
		} else {
			echo "Username already exists";
		}
	}
	public function verify_user($username, $password) {
		// Verifies user input given
		// If the username doesn't exist, return false
		// If the username exists but the password is incorrect, return false
		// If the username exists and the password is correct, return true
		$user = $this->query_user($username);
		if(!empty($user)) {
			if (password_verify($password, $user['password'])) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	public function login_user($username) {
		// Sets current session
		$_SESSION['user'] = $username;
	}
	public function current_user() {
		// Used for checking and manipulating current SESSION data
		return $_SESSION['user'];
	}
}

$app = new Application($config, $server); // Initializes Application class
$auth = new Authentication($app->serve); // Initializes Authentication class

// I don't particularly like the method I used here, but I believe it works under the limited scope of this project.

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(!$auth->current_user()) {
		$username = $_POST["username"];
		$password = $_POST["password"];

		if($auth->verify_user($username, $password)) {
			echo "Successfully logged in";
			$auth->login_user($username);
		} else {
			echo "Incorrect username or password";
		}
	} else {
		echo "Successfully logged out";
		session_destroy();
	}
}
?>

<!---

This Application was built upon the following guidlines given:
https://github.com/KirtusJ/auphan_application

1. Has a place for the username, password and a login button.
2. The username, password and login button are placed inside a box and centered horizontally and vertically in the page.   
3. Use CSS classes, no use of style attributes.
4. Uses ajax to submit the username and password for verification.
5. If the username submitted is {redacted} and password is {redacted}, 
   then show a message saying "Login Successful." 
   otherwise, flash a message showing "Incorrect Username/Password".
6. If the username is not an email prevent the user from pressing login.
7. The page should look decent.  (Perhaps rounded corners, background image/color, drop shadows... it's up to you.)
8. Be in one file. (Easier for us to review your work.)

!-->

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $app->name ?></title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<!-- Objectively the best font (only joking but it's nice) !-->
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

	<script type="text/javascript">
		$(document).ready(function() {
			// init var_form
			var form = $('.auth_form');

			// on var_form submit
			$(form).submit(function(e) {

				// init var_form ajax
				$.ajax({
					type: form.attr('method'),	// Acquires form method
					url: form.attr('url'),		// Acquires form url
					data: form.serialize(),		// Acquires and Serialzies form data
					success: function(result) {
						if (result.startsWith("Successfully logged in") || result.startsWith("Successfully logged out")) {
							location.reload();
						} else if (result.startsWith("Incorrect username or password")) {
							alert("Username or password is incorrect");
						}
					}
				})
				e.preventDefault();
				return false;
			});
		});
	</script>
	<style type="text/css">
		/*

		I employ pretty hacky methods here. I apologize for any applicable method that is used incorrectly.
		Please let me know what I can improve on. 

		*/
		html {
			position: relative;
   			min-height: 100%;
		}
		body {
			background-color: #efefef;
			margin: 0 0 50px;
			font-family: roboto;
		}
		.container {
			background-color: white;
			position: absolute;
 			left: 50%;
 			top: 50%;
 			-webkit-transform: translate(-50%, -50%);
 			transform: translate(-50%, -50%);
 			border: 1px solid lightgrey;
 			border-radius: 5px;
 			width: 320px;
 			height: 140px;
			text-align: center;
		}
		.auth_form input {
			margin: 10px;
			width: 300px;
			height: 25px;
			border: 1px solid lightgrey;
			border-radius: 2px;
		}
		.auth_form input[type=submit] {
		    padding:5px 15px; 
		    background: #1a73e8; 
		    border:0 none;
		    cursor:pointer;
		    font-size: 15px;
		    color: white;
		}
		#header {
			width: 100%;
			background-color: white;
			left: 0;
			right: 0;
			top: 0;
			height: 50px;
			position: absolute;
			text-align: center;
			border-bottom: 1px solid lightgrey;
		}

		#header span{
			position: absolute;
			left: 50%;
			top: 50%;
			-webkit-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
		}

		#footer {
   			position: absolute;
    		left: 0;
    		bottom: 0;
    		height: 50px;
    		width: 100%;
    		background-color: white;
    		border-top: 1px solid lightgrey;
		}
		#footer span{
			position: absolute;
			left: 50%;
			top: 50%;
			-webkit-transform: translate(-50%, -50%);
			transform: translate(-50%, -50%);
		}
		a:link{
			color: black;
			text-decoration: none;
		}
		a:visited {
			color: black;
			text-decoration: none;
		}
		a:hover {
			text-decoration: underline;
		}
	</style>
</head>
<body>
	<div id="header">
		<!-- I sort of wanted to add more pages such as a User Profile but I respected the wishes of a single page project !-->
		<span>
			<a href="index.php"><?php echo $app->name ?></a>
		</span>
	</div>

	<?php if($auth->current_user()) : ?>
		<!--- 
		This area will render if there is a current session
 		!-->
		<div class="container">
			<p>Logged in</p>
			<p>Username: <?php echo $auth->current_user() ?></p>
			<form class="auth_form" method="POST">
				<input type="submit" name="submit" value="Logout">
			</form>
		</div>
	<?php else : ?>
		<!--
		This area will render if there is no current session
		!-->
		<div class="container">
			<form class="auth_form" method="POST">
				<input type="email" name="username" placeholder="Username">
				<input type="password" name="password" placeholder="Password">
				<input type="submit" name="submit" value="Sign in">
			</form>
		</div>
	<?php endif; ?>

	<!-- My github! (This project will be uploaded there) !-->
	<div id="footer">
		<span>
			<a href="http://www.<?php echo $app->author ?>"><?php echo $app->author ?></a>
		</span>
	</div>
</body>
</html>


<!-- 

Thank you checking out my application! Here's some information you can use for contacting me if you so choose:

$kirtus = {
	"full_name" => "Kirtus Justus",
	"github" => "github.com/KirtusJ",
	"email" => "kirtuswork@gmail.com"
}

$this->github("https://github.com/KirtusJ/auphan_application");

!-->