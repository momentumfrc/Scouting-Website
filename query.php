<?php session_start(); ?>
<?php
echo($_SESSION["DB"]);
if ($_SESSION["loggedIn"]){
	$data = $_SESSION["DB"]->query("SELECT * FROM robots;");
	if($data->num_rows > 0){
		while($row = $data->fetch_assoc()) {
			echo('<div class="infoRow">');
				echo('<p>Team: ' . $row["TeamNum"] . '</p>');
				echo('<p>Drive System: ' . $row["DrvSys"] . '</p>');
		}
	} else {
		echo('<p>No results!</p>');
	}
}
?>