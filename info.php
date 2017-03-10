<?php session_start(); ?>
<?php
	if(!isset($_GET["team"])) {
		header( 'Location: https://frcteam4999.jordanpowers.net/index.php');
	}
?>
<html>
	<head>
		<link rel="stylesheet" href="styles/info.css">
		<title>Team: <?php echo(str_replace('_',' ',$_GET["team"])); ?></title>
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="no" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		<script>
			function setUrl(url) {
				document.location.href = url;
			}
			var team = <?php echo($_GET["team"]); ?>
		</script>
		
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
	<div id="info">
	<img src="images/back.png" id="back" onclick="setUrl('/scouting/')">
	<?php
	require 'functions.php';
	if (isset($_SESSION["loggedIn"])){
		$DB = new mysqli("localhost","momentu2_" . $_SESSION["user"],$_SESSION["pass"],"momentu2_frcteam4999");
	} else {
		$DB = new mysqli("localhost","momentu2_ro","aRza#p=XckDC","momentu2_frcteam4999");
	}
	$team = str_replace('_',' ',$_GET["team"]);
	$data = formatAndQuery('SELECT * FROM '.getCurrentDB().' WHERE Team = %d;',$team);
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			foreach($row as $key => $value) {
				$key = str_replace('_',' ',$key);
				switch($key) {
					case("Team"):
						echo('<h1>'.$key.': '.$value.'</h1>');
						break;
					case("Width"):
					case("Depth"):
					case("Height"):
						if(!empty($value)){
							echo('<p>'.$key.': '.$value.' inches</p>');
						}
						break;
					case("Weight"):
						if(!empty($value)){
							echo('<p>'.$key.': '.$value.' lbs</p>');
						}
						break;
					case("Can pickup gear from floor"):
					case("Can place gear on lift"):
					case("Can catch fuel from hoppers"):
					case("Can pickup fuel from floor"):
					case("Can shoot in low goal"):
					case("Can shoot in high goal"):
					case("Can climb rope"):
					case("Brought own rope"):
						if($value == 1) {
							echo('<p>'.$key.': Yes</p>');
						} else {
							echo('<p>'.$key.': No</p>');
						}
						break;
					case("Contributors"):
						if(!empty($value)) {
							echo('<p>Contributors to this information: ' . $value . '</p>');
						}
						break;
					default:
						if(!empty($value)){
							echo('<p>'.$key.': '.$value.'</p>');
						}
						break;
				}
			}
		}
	} else {
		echo('<p>No results!</p>');
	}
	$image_dir = $image_root . getCurrentDB() . '/' . $team . "/";
	#writeToLog("Imagedir: " . $image_dir, "images");
	if(file_exists($image_dir)){
		$files = scandir($image_dir);
		foreach( $files as $file ) {
			#writeToLog("File in image dir: " . $file, "images");
			if (in_array(pathinfo(basename($file),PATHINFO_EXTENSION),$acceptableFileTypes)) {
				echo('<a href="'.$image_dir.$file.'"><img src="'.$image_dir.$file.'" class="gallery"></a>');
			}
		}
	}
	echo('<br><a id="edit" href="edit.php?team='.$_GET["team"].'">Edit</a>');
	?>
	<hr><div id="TBAheading"><span>The Blue Alliance info</span></div>
	<div id="TBA">
	</div>
</div>
<script src="scripts/TBAIntegration.js"></script>
</body>
</html>
