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
		/* Generate the list of devices */
		$device_list = file("devices.txt");

		/* Organization of each line in devices.txt:
		 * folder name, displayed name, homepage, rxpage, database name
		*/

		$devices = array();
		foreach ($device_list as $line)
		{
			$device_entry = explode(",", $line);
			$devices[$device_entry[1]] = $device_entry[0] . "/" . $device_entry[2];
		}

		create_navbar($devices);	
	?>
	</div>

	<div id="section">
		<iframe src="wwf/wwf.php" scrolling="yes" frameBoarder="no" name="content_iframe"></iframe>
	</div>

	<div id="footer">
		<p>This be the foot</p>
	</div>

	<div id="postFooter">
		<p>This be the foot</p>
	</div>
</body>
</html>
