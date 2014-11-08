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
</head>

<body>
	<h1> Control a Roomba!!! </h1>

	<form method="post" action="roomba.php">
	<table>
	<tr>
		<td>
			<input type="submit" value="INITIALIZE ROOMBA" name="init">
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" value="SEND HOME" name="dock">
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" value="TEST MOVE" name="test">
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" value="DEFAULT CLEAN" name="default">
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" value="SPOT CLEAN" name="spot">
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit" value="MAX CLEAN" name="max">
		</td>
	</tr>
	</table>
	</form>

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
