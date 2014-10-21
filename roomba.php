<?php
	function write_database($len, $data)
	{
		$db = new mysqli("localhost", "root", "smarthouse", "roomba");

		if ($db->connect_errno > 0) {
			die("Error connection to database: " . $db->connect_error);
		}

		$query = $db->prepare("UPDATE `Communication` SET `Status` = 1, `ExStatusLength` = ?, `ExtendedStatus` = ?");
		$query->bind_param("ii", $len, $data);
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
	<input type="submit" value="DEFAULT CLEAN" name="default">
	<input type="submit" value="MAX CLEAN" name="max">
	</form>

	<?php
		if (isset($_POST["default"]))
		{
			write_database(1, 1);
		}
		else if (isset($_POST["max"]))
		{
			write_database(5, 5);
		}
	?>

</body>
</html>



