<?php
	$file = fopen("remove_log.txt", "w");
	fwrite($file, $_POST["device"]);
	fclose($file);

	echo exec('python remove_device.py ' . $_POST["device"]);
?>
