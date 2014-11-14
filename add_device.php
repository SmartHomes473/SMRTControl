<?php
	/* Because I'm too lazy to figure out how to configure Apache to execute python files directly */
	exec('python add_new_device.py ' . $_POST["device"]);
?>
