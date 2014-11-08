<?php
	function write_database($len, $data)
	{
		$db = new mysqli("localhost", "root", "smarthouse", "roomba");

		if ($db->connect_errno > 0) {
			die("Error connection to database: " . $db->connect_error);
		}

		$query = $db->prepare("UPDATE `Communication` SET `Status` = 1, `ExStatusLength` = ?, `ExtendedStatus` = ?");

		$data_string = implode(array_map("strval", $data));
		$query->bind_param("is", $len, $data_string);
		$query->execute();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Roomba Control!</title>
	<link href="style_buttons.css" rel="stylesheet" type="text/css" media="screen" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
</head>

<body>
	<h1> Control a Roomba!!! </h1>

	<script type="text/javascript">
		function button_clicked(button)
		{
			$.post("roomba.php", button);
		}
	</script>


	<a href="#0" onclick=button_clicked("clean"); class="button clean"></a>
	<a href="#0" onclick=button_clicked("spot"); class="button spot"></a>
	<a href="#0" onclick=button_clicked("max"); class="button max"></a>
	<a href="#0" onclick=button_clicked("dock"); class="button dock"></a>

	

	<?php
		$commands = [
			"init"		=> array(0x00),
			"dock"		=> array(0x03),
			"test"		=> array(0x05),
			"default"	=> array(0x02, 0x00),
			"spot"		=> array(0x02, 0x01),
			"max"		=> array(0x02, 0x02)
		];

		$packet = array();
		foreach($_POST as $key => $value)
		{
			if(isset($_POST[$key]))
			{
				$packet = $commands[$key];
			}
		}
		write_database(count($packet), $packet);
	?>
</body>
</html>
