<?php
$outlet = $_POST['outlet'];
$name = $_POST['name'];

$db = new mysqli("localhost", "root", "smarthouse", "outlets");

$query = $db->prepare('UPDATE outlets SET name = ? WHERE id = ?');
$query->bind_param('si', $name, $outlet);
$query->execute();
?>
