<?php
/* DELETE a device from devices */
include("mysql.php");

//input: device id
$id=$_GET['id'];

//delete device
$sql="DELETE FROM devices WHERE id=$id";
$mysqli->query($sql) or die($mysqli->error());

//delete device's readings
$sql="DELETE FROM readings WHERE id_device=$id";
$mysqli->query($sql) or die($mysqli->error());

?>
<!doctype html><html><head>
	<meta charset=utf-8>
	<link rel=stylesheet href=estils.css>
</head><body><center>
<!--NAVBAR--><?php include("navbar.php")?>
<?php
	die("<div><h2 style=color:red>Device id $id removed correctly</h2></div>");
?>
