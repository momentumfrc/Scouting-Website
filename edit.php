<?php session_start(); ?>
<html>
<head>
	<title>Edit Team</title>
	<link rel="stylesheet" href="styles/edit.css">
	<!--favicon generated by http://realfavicongenerator.net/-->
	<link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
	<link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="/favicons/android-chrome-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="/favicons/manifest.json">
	<link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="/favicons/favicon.ico">
	<meta name="apple-mobile-web-app-title" content="Scouting">
	<meta name="application-name" content="Scouting">
	<meta name="msapplication-config" content="/favicons/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">
</head>
<body>
<?php
require 'functions.php';
$image_root = "photos/";
$acceptableFileTypes = array("jpg","png","jpeg","gif","bmp","svg");
#check if logged in and redirect if not
if ($_SESSION["loggedIn"]){
	$DB = new mysqli("localhost",$_SESSION["user"],$_SESSION["pass"],"frcteam4999");
} else {
	if(isset($_GET["team"])){
		header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=edit.php?team='.$_GET["team"]);
	} else {
		header( 'Location: https://frcteam4999.jordanpowers.net/login.php?redirect=edit.php');
	}
	exit();
}
#get columns into an associative array
$columnData = $DB->query('DESCRIBE robots;');
$columns = array();
while($row = $columnData->fetch_assoc()) {
	$columns[] = $row;
}
#handle submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	#check if team exists
	$data = formatAndQuery('SELECT Team FROM robots WHERE Team = %d;',$_POST["Team"]);
	if($data->num_rows == 0){ # add team if it doesn't exist yet
		formatAndQuery('INSERT INTO robots (Team) VALUES (%d);',$_POST["Team"]);
	}
	$update = 'UPDATE robots SET %s = %sv WHERE Team = %d;';
	foreach($columns as $column) {
		if($column["Field"] != "Team" and $column["Field"] != "Stored_Images"){
			formatAndQuery($update,$column["Field"],$_POST[$column["Field"]],$_POST["Team"]);
		}
	}
	#handle file submission
	for( $i = 0; $i < count($_FILES["uploadImages"]["tmp_name"]); $i++) {
		if (is_uploaded_file($_FILES["uploadImages"]["tmp_name"][$i])) {
			if (!file_exists($image_root)) {
				mkdir($image_root,0777,true);
			}
			$image_dir = $image_root . $_POST["Team"] ."/";
			if (!file_exists($image_dir)) {
				mkdir($image_dir,0777,true);
			}
			$files = scandir($image_dir);
			$biggestFile = 0;
			foreach( $files as $file ) {
				if (in_array(pathinfo(basename($file),PATHINFO_EXTENSION),$acceptableFileTypes)) {
					$base = basename($file, "." . pathinfo(basename($file),PATHINFO_EXTENSION));
					if ( $base > $biggestFile) {
						$biggestFile = $base;
					}
				}
			}
			$imgeFileExtension = pathinfo(basename($_FILES["uploadImages"]["name"][$i]),PATHINFO_EXTENSION);
			$imgeFileExtension = pathinfo(basename($_FILES["uploadImages"]["name"][$i]),PATHINFO_EXTENSION);
			$target_file_path = $image_dir . ($biggestFile + 1) .".". $imgeFileExtension;
			$continueUpload = TRUE;
			#check if is image
			if(getimagesize($_FILES["uploadImages"]["tmp_name"][$i]) == FALSE) {
				writeToLog("INVALID FILE","images");
				$continueUpload = FALSE;
			}
			#check if acceptable image (trusts extension)
			if(!in_array($imgeFileExtension,$acceptableFileTypes)) {
				writeToLog("INVALID FILE","images");
				$continueUpload = FALSE;
			}
			if($continueUpload) {
				if (!move_uploaded_file($_FILES["uploadImages"]["tmp_name"][$i], $target_file_path)) {
					writeToLog("ERROR MOVING UPLOADED FILE","images");
				}
			}
		}
	}
	#handle deletions
	$images = $_POST["images"];
	if(!empty($images)) {
		foreach($images as $image ) {
			$target_file_path = $image_root . $_POST["Team"] . "/" . $image;
			writeToLog("Will unset: ". $target_file_path,"images");
			unlink($target_file_path);
		}
	} else {
		writeToLog("Images was empty!","images");
	}
	header( 'Location: https://frcteam4999.jordanpowers.net/info.php?team='.$_POST["Team"]);
}
#check if creating a new entry, or editing an existing entry
#creates an associative array of the existing entry
if(isset($_GET["team"])){
	$data = formatAndQuery('SELECT * FROM robots WHERE Team = %d;',$_GET["team"]);
	if($data->num_rows > 0){
		$row = $data->fetch_assoc();
		echo('<h1>Team: '.$_GET["team"].'</h1>');
	}
}
#create the form
echo('<form id="edit" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post" autocomplete="off" enctype="multipart/form-data">');
foreach($columns as $column) {
	#remove underscores from column names
	$PrettyColumn = str_replace('_',' ',$column["Field"]);
	if(!($column["Field"] == "Team" and isset($_GET["team"]))){ #Check if creating a new team
		echo('<p>'.$PrettyColumn.':</p>');
		switch($column["Field"]) {
			#check all numbers
			case("Team"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9]*" required>');
				break;
			case("Width"):
			case("Depth"):
			case("Height"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9.]*"><span>inches</span>');
				break;
			case("Weight"):
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'" pattern="[0-9.]*"><span>lbs</span>');
				break;
			case("Drive_System"): #Create select menu with options for each type of drive system. The array $options can have new drive systems added to it to create more options
				echo('<select name="'.$column["Field"].'">');
				$options=array("West Coast","Mechanum","Tank","Swerve");
				foreach($options as $option) {
					if ($option == $row[$column["Field"]]) { #if the value is already set, set the option to that value
						echo('<option value="'.$option.'" selected>'.$option.'</option>');
					} else {
						echo('<option value="'.$option.'">'.$option.'</option>');
					}
				}
				echo('</select>');
				break;
			case("Can_pickup_gear_from_floor"):
			case("Can_place_gear_on_lift"):
			case("Can_catch_fuel_from_hoppers"):
			case("Can_pickup_fuel_from_floor"):
			case("Can_shoot_in_low_goal"):
			case("Can_shoot_in_high_goal"):
			case("Can_climb_rope"):
			case("Brought_own_rope"):
				echo('<select name="'.$column["Field"].'">');
				if ($row[$column["Field"]] == 0) {
					echo('<option value="0" selected>No</option>
					<option value="1">Yes</option>');
				} else {
					echo('<option value="0">No</option>
					<option value="1" selected>Yes</option>');
				}
				echo('</select>');
				break;
			case("Autonomous_capabilities"):
			case("Other_info"):
				echo('<textarea rows="4" cols="50" name="'.$column["Field"].'">'.$row[$column["Field"]].'</textarea>');
				break;
			default:
				echo('<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'">');
		}
		/*echo('<p>'.$PrettyColumn.':</p><br>
			<input type="text" name="'.$column["Field"].'" value="'.$row[$column["Field"]].'"><br>');*/
	} elseif ($column["Field"] == "Team" and isset($_GET["team"])) {#Set the team to a hidden value
		echo('<input type="hidden" name="Team" value="'.$row[$column["Field"]].'">');
	}
}
echo('<input type="file" name="uploadImages[]" accept="image/jpeg,image/png,image/gif,image/bmp,image/svg+xml" multiple>');
$image_dir = $image_root . $_GET["team"] . "/";
#writeToLog("Imagedir: " . $image_dir, "images");
if(file_exists($image_dir)){
	$files = scandir($image_dir);
	foreach( $files as $file ) {
		#writeToLog("File in image dir: " . $file, "images");
		if (in_array(pathinfo(basename($file),PATHINFO_EXTENSION),$acceptableFileTypes)) {
			echo('<label><input id="deletePix" type="checkbox" name="images[]" value="'.$file.'"><img src="'.$image_dir.$file.'" class="gallery"></label>');
		}
	}
}
echo('<input type="submit" value="Submit"></form>');
?>
	<p>All values are in lbs and inches</p>
<script src="scripts/universal.js"></script>
</body>
</html>
