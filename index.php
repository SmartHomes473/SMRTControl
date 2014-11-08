<?php
	/* Function takes a dictionary of Device name => device php page */
	function create_navbar($devices)
	{
		foreach($devices as $key => $value)
		{
			echo '<a href="' . $value . '" target="content_iframe">' . $key . '</a>';
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<title>SMRTHAUS</title>
</head>


<body align="center">
	<div id="header">
		<h1> <IMG SRC="SMRTHAUSlogoV1.png" WIDTH=60 HEIGHT=60> SMRTControl for SMRTHAUS</h1>
	</div>

	<div id="nav">
	<?php
		/* Will eventually have a way to generate this table */
		$devices = [
			"Wall Weather Forecaster"	=> "wwf.php",
			"Roomba"					=> "roomba.php",
			"Automation Device 3"		=> "wwf.php",
			"Automation Device 4"		=> "wwf.php",
			"Automation Device 5"		=> "wwf.php",
			"Automation Device 6"		=> "wwf.php",
			"Automation Device 7"		=> "wwf.php",
			"Automation Device 8"		=> "wwf.php",
		];

		create_navbar($devices);	
	?>
		<!--
	   <a href="wwf.php" target="content_iframe">Wall Weather Forecaster</a>
	   <a href="roomba.php" target="content_iframe">Roomba</a>
	   <a href="wwf.php" target="content_iframe">Automation Device 3</a> 
	   <a href="wwf.php" target="content_iframe">Automation Device 4</a>
	   <a href="wwf.php" target="content_iframe">Automation Device 5</a> 
	   <a href="wwf.php" target="content_iframe">Automation Device 6</a> 
	   <a href="wwf.php" target="content_iframe">Automation Device 7</a>
	   <a href="wwf.php" target="content_iframe">Automation Device 8</a> 
		-->
	</div>

	<div id="section">
		<iframe src="wwf.php" scrolling="yes" frameBoarder="no" name="content_iframe"></iframe>
	</div>

	<div id="footer">
		<p>This be the foot</p>
	</div>

	<div id="postFooter">
		<p>This be the foot</p>
	</div>
</body>
</html>
