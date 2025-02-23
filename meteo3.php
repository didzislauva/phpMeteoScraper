<?php

include_once "meteoClass.php";

$mysqlUser="";
$mysqlPassw="";
$mysqlHost="";
$DB="";


$meteo=new meteoScraper(122,"RIGASLU");
//$meteo->createForCron();
$meteo->getStationAndParam();
$meteo->prepareWWWString();
$meteo->curlingScraping();
$meteo->connectDB($mysqlHost,$mysqlUser,$mysqlPassw,$DB);
$meteo->saveInDB();
//$meteo->returnParamID(122);
exit;




?>