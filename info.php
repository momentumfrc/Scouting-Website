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
	<img src="images/back.png" id="back" onclick="setUrl('<?php require 'specificvars.php'; echo($appdir); ?>')">
	<?php
	require 'functions.php';

	function getPhotoDiv($id) {
		global $appdir, $image_root,$acceptableFileTypes;
		$out = '<div id="photogallery">';
		$image_dir = $image_root . $id.'/full/';
		$thumb_dir = $image_root . $id.'/thumb/';
		if(file_exists($image_dir)) {
			$files = scandir($image_dir);
			foreach($files as $file) {
				if(in_array(pathinfo(basename($file),PATHINFO_EXTENSION),$acceptableFileTypes)) {
					$out .= '<a href="'.$image_dir.$file.'"><img src="'.$thumb_dir.$file.'" class="gallery"></a>';
				}
			}
		}
		$out .= '</div>';
		return $out;
	}

	function getAccordion($title,$content) {
		return '<div class="accordion">
			<button class="accordionbutton">'.$title.'</button>
			<div class="accordioncontent">'.$content.'</div>
			</div>';
	}
	function formatContent($ids, $schema,$table) {
		global $RobotDataTable, $EventDataTable;
		$out = "";
		foreach($ids as $index=>$id) {
			$data = retrieveKeys($table, $id, $schema);

			if($table == $RobotDataTable) {
				if(isset($data["name"]["data_value"]) && !empty($data["name"]["data_value"])) {
					$title = $data["name"]["data_value"];
				} else {
					$title = "Robot #" . ($index + 1);
				}
			} elseif ($table == $EventDataTable) {
				// event_name and match_num are special
				if(isset($data["event_name"]["data_value"]) && isset($data["match_num"]["data_value"]) &&!empty($data["event_name"]["data_value"]) && !empty($data["match_num"]["data_value"])) {
					$title = $data["event_name"]["data_value"]." Match #".$data["match_num"]["data_value"];
				} else {
					$title = "Match #" . ($index + 1);
				}
			} else {
				$title = $index + 1;
			}

			$content = "";
			foreach($data as $key=>$value) {
				if(isset($value["data_value"]) && !empty($value["data_value"])) {
					if(($table == $RobotDataTable && $key == "name") || ($table == $EventDataTable && ($key == "event_name" || $key == "match_num"))) {
						continue;
					}
					if($value["type"] == "boolean") {
						if($value["data_value"] == "true") {
							$content .= '<p><span class="key">'.$value["display_name"].':</span> Yes</p>';
						} else {
							$content .= '<p><span class="key">'.$value["display_name"].':</span> No</p>';
						}
					} else {
						$content .= '<p><span class="key">'.$value["display_name"].':</span> '.$value["data_value"].'</p>';
					}
				}
			}
			$content .= getPhotoDiv($id);
			$out .= getAccordion($title, $content);
		}
		return $out;
	}

	$DB = createDBObject();
	$team = clean($_GET["team"]);
	$results = formatAndQuery("SELECT robotids,eventids FROM %s WHERE number = %d;",$TeamDataTable,$team);
	if($results->num_rows <= 0) {
		$robotids = array();
		$eventids = array();
	} else {
		$result = $results->fetch_assoc();
		$robotids = explode($explodeseparator,$result["robotids"]);
		$eventids = explode($explodeseparator,$result["eventids"]);
	}

	if(file_exists("schema.json")) {
		$json = json_decode(file_get_contents("schema.json"), True);
		$year = getYearData($json, getDefaultYear())[1];
		if($year === false) {
			echo("<p>Schema for this year does not exist</p>");
			exit();
		}
	} else {
		echo("<p>schema.json does not exist!</p>");
		exit();
	}

	$robotids = getIdsForYear($RobotDataTable, $year["year"], $robotids);
	$eventids = getIdsForYear($EventDataTable, $year["year"], $eventids);

	echo('<p class="category">Robots:</p>');
	if(count($robotids) > 0) {
		echo(formatContent($robotids,$year["robotdata"],$RobotDataTable));
	} else {
		echo("<p>No data!</p>");
	}

	echo('<p class="category">Matches:</p>');
	if(count($eventids) > 0 ) {
		echo(formatContent($eventids,$year["matchdata"],$EventDataTable));
	} else {
		echo("<p>No data!</p>");
	}


	?>
	<a id="edit" href="edit.php?team=<?php echo($team); ?>">Edit</a>
	<hr><div id="TBAheading"><span>The Blue Alliance info</span></div>
	<div id="TBA">
	</div>
</div>
<script src="scripts/jquery-3.1.1.min.js"></script>
<script src="scripts/info.js"></script>
<script src="scripts/TBAIntegration.js"></script>
</body>
</html>
