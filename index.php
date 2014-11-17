<?php
	/* Function takes a dictionary of Device name => device php page */
	function create_navbar($devices)
	{
		foreach($devices as $key => $value)
		{
			echo '<a href="' . $value . '" target="content_iframe" >' . $key . '</a>';
		}
	}
?>



<!DOCTYPE html>
<html>
<head>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<title>SMRTHAUS</title>
</head>


<body align="center" style ="font-size:large">
	<script type="text/javascript">
	function autoResizeContent(){
	    var newheight;
	    var newwidth;
		document.getElementById("content_iframe").height= (newheight) + "px";
	    document.getElementById("section").style.height = (newheight) + "px";

	    if(document.getElementById){
	        newheight=document.getElementById("content_iframe").contentWindow.document.body.scrollHeight;
	        newwidth=document.getElementById("content_iframe").contentWindow.document.body.scrollWidth;
	    }

	    document.getElementById("content_iframe").height= (newheight) + "px";
	    document.getElementById("section").style.height = (newheight) + "px";
	    document.getElementById("content_iframe").width= (newwidth) + "px";
	}
	</script>
	<div id="header">
		<h1> <IMG SRC="SMRTHAUSlogoV1.png" WIDTH=60 HEIGHT=60> <font size ="9" > SMRTControl for SMRTHAUS</font></h1>
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

		/* Store the current devices.txt modification time */
		echo '<script>var currentTS = ', (int)filemtime('devices.txt'), ';</script>';
	?>
	</div>
	
	<script type="text/javascript">
		function remove_device()
		{
			var url = document.getElementById("content_iframe").contentWindow.location.href;
			url = url.slice(url.lastIndexOf("/")+1, url.length);

			remove = confirm("Are you sure you wish to remove this device?");

			if (remove == true)
			{
				$.post("remove_device.php", 
					   { "device" : url },
					   function() { location.reload(); }
					  );
			}
			else
			{
				/* Do nothing */
			}
		}

		function add_device()
		{
			var url = prompt("What is the device URL?");
			$.post("add_device.php",
					   { "device" : url },
					   function() { location.reload(); }
					  );
		}

		function check_for_device_updates()
		{
			$.ajax({
					url: 'refresh_for_new_device.php',
					timeout: 2000, // don't wait too long
					success: 
						function(data) {
							data_parsed = JSON.parse(data);
							timestamp = data_parsed.ts;

							if (currentTS < timestamp) 
							{
								location.reload();
							}
						}
			});
		}
		setInterval(check_for_device_updates, 5000)
	</script>

	<div id="remove">
		<a href="javascript:;" onclick="remove_device();">
			<img src="remove.png">
		</a>
	</div>

	<div id="section">
		<iframe src="wwf/wwf.php" scrolling="yes" frameBoarder="no" id="content_iframe" name="content_iframe" onLoad="autoResizeContent();"></iframe>'
	</div>

	<div id="footer">
		<a href="javascript:;" onclick="add_device();">
			Add new device
		</a>
	</div>

	<div id="postFooter">
		<p>This be the foot</p>
	</div>
</body>
</html>
