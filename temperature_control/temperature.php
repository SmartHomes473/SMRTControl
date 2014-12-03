<?php
	function write_comms($len, $data)
	{
		$db = new mysqli("localhost", "root", "smarthouse", "temperature");

		if ($db->connect_errno > 0) {
			die("Error connection to database: " . $db->connect_error);
		}

		$query = $db->prepare("UPDATE `Communication` SET `Status` = 1, `ExStatusLength` = ?, `ExtendedStatus` = ?");

		$data_string = implode(array_map("strval", $data));
		$query->bind_param("is", $len, $data_string);
		$query->execute();
	}

	function write_setpoint($temp, $unit)
	{
		$db = new mysqli("localhost", "root", "smarthouse", "temperature");

		if ($db->connect_errno > 0) {
			die("Error connection to database: " . $db->connect_error);
		}

		$query = $db->prepare("UPDATE `current_setpoint` SET `temperature` = ?, `units` = ? LIMIT 1") or trigger_error($db->error);
		$query->bind_param("ds", $temp, $unit);
		$query->execute();
	}

	function get_setpoint()
	{
		$db = new mysqli("localhost", "root", "smarthouse", "temperature");

		if ($db->connect_errno > 0) {
			die("Error connection to database: " . $db->connect_error);
		}
		$query = "SELECT `temperature`, `units` FROM `current_setpoint` LIMIT 1";
		$result = $db->query($query);
		$row = $result->fetch_array();
		return $row;
	}

	if (isset($_POST['submit']))
	{
		$temperature = $_POST['temperature'];
		$unit = $_POST['unit'];
		write_setpoint($temperature, $unit);

		$temp_arr = array($temperature);

		if ($unit == "Farenheit")
		{
			array_push($temp_arr, 1);
		}
		else if ($unit == "Kelvin")
		{
			array_push($temp_arr, 2);
		}
		else
		{
			array_push($temp_arr, 0);
		}

		if ($temperature > 999)
			write_comms(5, $temp_arr);
		else if ($temperature > 99)
			write_comms(4, $temp_arr);
		else if ($temperature > 9)
			write_comms(3, $temp_arr);
		else
			write_comms(2, $temp_arr);
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Temperature Control!</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<style>
	body, html {
		text-align: center;
	}
	form {
		display: inline-block;
		text-align: center;
	}
	</style>
</head>

<body>

	 <p style="text-align:center"><font size = 7 >Temperature Control</font></p>

	<script type="text/javascript">
	</script>

	<form method="post">
		<table>
			<tr>
				<td>
					Temperature:
				</td>
				<td>
					<?php
						$string = '<input type="number" name="temperature"';

						$setpoint = get_setpoint();

						$string = $string . " value=" . $setpoint['temperature'];
						$string = $string . " />";

						echo $string;
					?>
				</td>
			</tr>

			<tr>
				<td>
					Farenheit: 
				</td>
				<td>
					<?php
						$string = '<input type="radio" name="unit" value="Farenheit"';
						$setpoint = get_setpoint();

						if ($setpoint['units'] == "Farenheit")
						{
							$string = $string . " checked='checked'";
						}

						$string = $string . " />";
						echo $string;
					?>
				</td>
			</tr>
			<tr>
				<td>
					Celsius:
				</td> 
				<td>
					<?php
						$string = '<input type="radio" name="unit" value="Celsius"';
						$setpoint = get_setpoint();

						if ($setpoint['units'] == "Celsius")
						{
							$string = $string . " checked='checked'";
						}

						$string = $string . " />";
						echo $string;
					?>
				</td>
			</tr>
			<tr>
				<td>
					Kelvin: 
				</td>
				<td>
					<?php
						$string = '<input type="radio" name="unit" value="Kelvin"';
						$setpoint = get_setpoint();

						if ($setpoint['units'] == "Kelvin")
						{
							$string = $string . " checked='checked'";
						}

						$string = $string . " />";
						echo $string;
					?>
				</td>
			</tr>
		</table>
		<input type="submit" name="submit" value="Update">
	</form>

</body>
</html>
