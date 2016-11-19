<?php 
	include("mysql.php");
	$start = microtime(true); //start time

	//input
	$type=isset($_GET['type'])      ? $_GET['type'] 	   : "";
	$ubic=isset($_GET['ubication']) ? $_GET['ubication'] : "";

	//define a where clause (string) for the sql query
	if($type!="")              $where = "type='$type'";
	if($ubic!="")              $where = "ubication='$ubic'";
	if($type!="" && $ubic!="") $where = "(type='$type' AND ubication='$ubic')";
	if($type=="" && $ubic=="") $where = "1";

	//construct query: devices and number of readings
	$sql="SELECT id,name,plcPosition,type,unit,ubication,count
			FROM devices LEFT JOIN 
			(
				SELECT id_device,COUNT(*) as count
				FROM readings 
				GROUP BY id_device
			) cc ON devices.id=cc.id_device 
			WHERE $where";
?>
<!doctype html><html><head>
	<meta charset=utf-8>
	<link rel=stylesheet href="estils.css">
	<title><?php 
		//set title
		if($type!="")              $title = "<u>$type"."s</u> ";
		if($ubic!="")              $title = "Devices at <u>$ubic</u>";
		if($type!="" && $ubic!="") $title = "<u>$type"."s</u> at <u>$ubic</u>";
		if($type=="" && $ubic=="") $title = "All Devices";
		echo preg_replace("/\<\/?u\>/","",$title);
	?></title>
	<style>td{text-align:center}</style>
</head><body><center>
<!--NAVBAR-->	<?php include("navbar.php") ?>
<!--TITLE-->	<h2 onclick=window.location.reload() style=cursor:pointer>Devices &mdash; Viewing <?php echo $title ?></h2>

<div id=topOptions>
	<style>
		#topOptions * {margin:0;padding:0;}
		#topOptions button {padding:0.3em 1em}
		#topOptions > div {
			height:40px;font-size:14px;
			line-height:40px;
			vertical-align:middle;
			padding:0.1em 0.5em;
			margin-right:0.5em;
		}
		#topOptions #newDevice{cursor:pointer}
		#topOptions #newDevice:hover {background:lightgreen;transition:all 0.5s}
	</style>

	<!--NEW DEVICE-->
	<div class=inline style="border:1px solid blue" id=newDevice onclick=window.location='newDevice.php'>
		<b> + Create New Device </b>
	</div>

	<!--filter-->
	<div id=filter class=inline style='border:1px solid #ccc'>
		<form action=devices.php id=filter>
			<div class=inline><b>Search filter</b>&emsp;</div>
			<?php 
				//FIND ALL DIFFERENT DEVICE TYPES AND UBICATIONS
				$types=[];
				$ubics=[];
				/* e.g.:
					$types=['Sensor','Alarm','Equipment','Setpoint'];
					$ubics=['Influent','Bioreactor','Recirculation','Membranes','RO'];
				*/
				$res=mysql_query("SELECT DISTINCT(type) FROM devices") or die(mysql_error());
				while($row=mysql_fetch_array($res)) $types[]=$row['type'];
				$res=mysql_query("SELECT DISTINCT(ubication) FROM devices") or die(mysql_error());
				while($row=mysql_fetch_array($res)) $ubics[]=$row['ubication'];
			?>
			<div class=inline>
				Type 
				<select name=type>
					<option value=''>All
					<?php 
						foreach($types as $type) 
						{ 
							if(isset($_GET['type']) && $type==$_GET['type'])
								echo "<option selected>$type"; 
							else
								echo "<option>$type"; 
						} 
					?>
				</select>
			</div>
			&emsp;
			<div class=inline>
				Ubication
				<select name=ubication>
					<option value=''>All
					<?php 
						foreach($ubics as $ubic) 
						{ 
							if(isset($_GET['ubication']) && $ubic==$_GET['ubication'])
								echo "<option selected>$ubic"; 
							else
								echo "<option>$ubic"; 
						} 
					?>
				</select>
			</div>
			&emsp;
			<div class=inline><button>Filter</button></div>
		</form>
	</div>

	<!--direct input id-->
	<div class=inline style='border:1px solid #ccc;'>
		<form action=device.php method=GET>
			View device number 
			<input name=id placeholder="id" type=number style="width:40px"
				value="<?php 
					echo current(mysql_fetch_assoc(mysql_query("SELECT MIN(id) FROM devices")));
				?>"
			autocomplete=off> 
			<button>Go</button>
		</form>
	</div>
</div>

<!--QUERY & Nº of results-->
<div style=margin-top:2em><b><?php
	$res=mysql_query($sql) or die(mysql_error());
	echo mysql_num_rows($res)." devices found";
?></b></div>

<!--DEVICES-->
<table id=devices cellpadding=3>
	<style>
		#devices {width:80%}
	</style>
	<tr><th>Device Id<th>Name<th>Type<th>Ubication<th>Unit<th>PLC Position<th>Readings
	<?php
		while ($row=mysql_fetch_assoc($res))
		{
			$id			 	   = $row['id'];
			$name		 	   = $row['name'];
			$type		 	   = $row['type'];
			$ubication   = $row['ubication'];
			$unit		 	   = $row['unit'];
			$plcPosition = $row['plcPosition'];
			$readings    = $row['count'];

			if($readings==null)$readings=0;
			$colorRRR = $readings==0 ? "red" : ""; 		//red if number of readings is zero
			$colorPLC = $plcPosition=="" ? "red" : "";	//red if plc position is blank

			//display
			echo "<tr>
					<td>$id
					<td style=text-align:left><a href='device.php?id=$id'>$name</a>
					<td>$type
					<td>$ubication
					<td>$unit 
					<td style=background:$colorPLC>$plcPosition
					<td style=background:$colorRRR>$readings";
		}
		if(mysql_num_rows($res)==0)
		{
			echo "<tr><td colspan=7>~No devices created yet";
		}
	?>
</table>

<!--time--><?php printf("Page generated in %f seconds",microtime(true)-$start)?>
