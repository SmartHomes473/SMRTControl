<!DOCTYPE html>
<html>
<head>
	<title>Roomba Control!</title>
	<link href="style_buttons.css" rel="stylesheet" type="text/css" media="screen" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
</head>

<body>
	<p style="text-align:center"><font size = 7 >Roomba Control</font></p>

	<script type="text/javascript">
		function button_clicked(button)
		{
			$.post("roomba_update_db.php", { command : button });
			return false;
		}
	</script>


	<div id="buttons">
		<a href="javascript:;" onclick="button_clicked('default');" class="button clean"></a>
		<a href="javascript:;" onclick="button_clicked('spot');" class="button spot"></a>
		<a href="javascript:;" onclick="button_clicked('max');" class="button max"></a>
		<a href="javascript:;" onclick="button_clicked('dock');" class="button dock"></a>
	</div>
    
	<br>
	<div id="music">
		<a href="javascript:;" onclick="button_clicked('song1');"><img src="music.PNG" /></a>
		<a href="javascript:;" onclick="button_clicked('song2');"><img src="music.PNG" /></a>
		<a href="javascript:;" onclick="button_clicked('song3');"><img src="music.PNG" /></a>
		<a href="javascript:;" onclick="button_clicked('song4');"><img src="music.PNG" /></a>
	</div>

</body>
</html>
