<?php
	function write_database($len, $data)
	{
		$db = new mysqli("localhost", "root", "smarthouse", "roomba");

		if ($db->connect_errno > 0) {
			die("Error connection to database: " . $db->connect_error);
		}

		$query = $db->prepare("UPDATE `Communication` SET `Status` = 1, `ExStatusLength` = ?, `ExtendedStatus` = ?");

		$data_string = implode(array_map("chr", $data));
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
	<h2> Control a Roomba!!! </h2>

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
		$packet = array(0xFF);
		if (isset($_POST["init"]))
		{
			$packet = array(0x00);
			write_database(count($packet), $packet);
		}
		else if (isset($_POST["dock"]))
		{
			$packet = array(0x03);
			write_database(count($packet), $packet);
		}
		else if (isset($_POST["test"]))
		{
			$packet = array(0x05);
			write_database(count($packet), $packet);
		}
		else if (isset($_POST["default"]))
		{
			$packet = array(0x02, 0x00);
			write_database(count($packet), $packet);
		}
		else if (isset($_POST["spot"]))
		{
			$packet = array(0x02, 0x01);
			write_database(count($packet), $packet);
		}
		else if (isset($_POST["max"]))
		{
			$packet = array(0x02, 0x02);
			write_database(count($packet), $packet);
		}
	?>

	<br>
	<br>
	<a href="index.html">Go home!</a>

</body>
</html>



