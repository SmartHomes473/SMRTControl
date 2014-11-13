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

	$commands = [
			"init"		=> array(0x00),
			"dock"		=> array(0x03),
			"test"		=> array(0x05),
			"default"	=> array(0x02, 0x00),
			"spot"		=> array(0x02, 0x01),
            "max"		=> array(0x02, 0x02),
            "song1"      => array(0x06, 0x01), 
            "song2"      => array(0x06, 0x02), 
            "song3"      => array(0x06, 0x03) 
		];

	$packet = array();
	$packet = $commands[$_POST["command"]];

	write_database(count($packet), $packet);
?>
