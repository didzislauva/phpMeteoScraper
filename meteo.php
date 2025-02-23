<?php

$creationStr="1947/01/01";
$today=new DateTime();

try {
    $creation = new DateTime($creationStr);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}


$interval = round(abs($today->format('U') - $creation->format('U')) / (60*60*24));



for ($i = 1; $i <= $interval; $i++) {

	//http://www.meteo.lv/meteorologijas-operativo-datu-grafiks/?id=RISA99PA&parameterId=100&fullMap=0&date=29.07.2012&time=10:00
	
	echo "<a href='http://www.meteo.lv/meteorologijas-operativo-datu-grafiks/?id=RISA99PA&parameterId=122&fullMap=0&date=".$creation->format("d.m.Y")."&time=24:00'>".$creation->format("d.m.Y")."</a><br />";
	
	$creation->modify("+1 day");
    //echo $creation->format("F j, Y")."<br />";
}




?>