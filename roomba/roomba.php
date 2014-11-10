
<!DOCTYPE html>
<html>
<head>
	<title>Roomba Control!</title>
	<link href="style_buttons.css" rel="stylesheet" type="text/css" media="screen" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
</head>

<body>
	<h1> ROOMBA CONTROL</h1>

	<script type="text/javascript">
		function button_clicked(button)
		{
			$.post("roomba_update_db.php", { command : button });
			return false;
		}
	</script>


	<div id="buttons">
		<a href="#0" onclick="button_clicked('default');" class="button clean"></a>
		<a href="#0" onclick="button_clicked('spot');" class="button spot"></a>
		<a href="#0" onclick="button_clicked('max');" class="button max"></a>
		<a href="#0" onclick="button_clicked('dock');" class="button dock"></a>
	</div>

</body>
</html>
