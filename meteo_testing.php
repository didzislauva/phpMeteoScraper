<?php

class meteoScraper {
private $parameter;
private $parameterID;
private $station;
private $stationID;
private $yesterday;
public $contents;
public $hp;
public $dati;


	function __construct($parameter,$station) {
		$this->parameter=$parameter;
		$this->station=$station;
		}
	function prepareWWWString() {
		$date=$this->prepareDate();
		$this->hp="http://www.meteo.lv/meteorologijas-operativo-datu-grafiks/?id=".$this->station."&parameterId=".$this->parameter."&fullMap=0&date=".$date."&time=23:00";
		print $this->hp;
		}
		
	function prepareDate() {
		$today=new DateTime();
		$today->modify('-1 day');
		$this->yesterday=$today;
		return $this->yesterday->format("d.m.Y");
		}
		
	function curlingScraping(){
		
		try {
			$ch=curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->hp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_COOKIEJAR, "ck"); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$this->contents = curl_exec($ch);
			curl_close($ch);
			try{
				$this->scraping();
			}
			catch (exception $e){
				print "scraping impossible @ Scraping";
			}	
		}
		catch (Exception $e) {
				print "curling and scraping impossible @ curlingScraping";
		}
	}
	
	function scraping(){
		
		$this->contents=str_replace(" ","",$this->contents);
		$this->contents=str_replace("\t","",$this->contents);
		
		$this->contents=str_replace("\n","",$this->contents);
		$this->contents=str_replace("],[","];[",$this->contents);
		
		$sakums= strpos($this->contents,"data.addRows")+14;
		$beigas= strpos($this->contents,"varoptions")-3;
		
		$this->contents= substr($this->contents,$sakums,$beigas-$sakums);
		$datumasivs=explode(";",$this->contents);
		$masivs=array();
		$i=0;
		foreach ($datumasivs as $value){
			$pair=explode(",",$value);
			$pair[0]=str_replace("[","",$pair[0]);
			$pair[0]=str_replace("'","",$pair[0]);
			$pair[1]=str_replace("]","",$pair[1]);
			$masivs[$i][0]=$this->yesterday->format("Y.m.d")." ".$pair[0].":00";
			$masivs[$i][1]=$pair[1];
			$i=$i+1;
			}
		$this->dati=$masivs;
		
		if (count($this->dati)<>24){
			throw new Exception('Scraping impossible @ scraping. Data count - '.count($this->dati));
		}
		
	}
	function returnParamId(){
		try{
			print $this->parameter;
			$query="SELECT id from Z2_meteo_parameterID where parameter=".$this->parameter;
			$result=mysql_query($query);
			
			
			$row = mysql_fetch_row($result);
			$this->parameterID= $row[0];	
		}
		catch (Exception $e){
			print "unable to query parameter db";
			die;
		}
	}
		
	function returnStationId(){
		try{
			$query="SELECT id from Z2_meteo_stationID where stationName='".$this->station."'";
			$result=mysql_query($query);
			$row = mysql_fetch_row($result);
			$this->stationID= $row[0];	
		}
		catch (Exception $e){
			print "unable to query station db";
			die;
		}	
		
		
	}
	function connectDB($ip,$usern,$passw,$db){
		try {
			$link = @mysql_connect($ip, $usern,$passw);
			if (!$link) {
				throw new Exception('Error Connecting to host');
			}
			
			$db_selected = mysql_select_db($db, $link);
			if (!$db_selected) {
			    throw new Exception('Error select database');
			}
		}
		catch (Exception $e)
		{
			echo "unable to connect mysql. ".$e->getMessage();
			die;
		}
	}
	
	function saveInDB(){

			$queryArray=array();
			$this->returnParamID();
			$this->returnStationID();
			
			foreach ($this->dati as $element) {

				$queryelement="('".$element[0]."',".$element[1].",".$this->parameterID.",".$this->stationID.")";
				array_push($queryArray, $queryelement);
				
			}
			
			$queryString=implode(",",$queryArray);
			
			
			$query="INSERT INTO Z2_meteo_values (date, value, paramID, stationID) VALUES ".$queryString;
			#print $query;
			mysql_query($query);
			
		}


	function createForCron(){
		
		#RIGASLU - Rîga
		#RIDO99MS - Dobele
		#RISA99PA - Saldus
		#RUCAVA - Rucava
		#RILP99PA - Liepâja
		#RIKO99PA - Kolka
		#RISI99PA - Skrîveri
		#RIDM99MS - Daugavpils
		#RIAL99MS - Alûksne
		
		#122 temperatûra
		#100 vçjð
		#102 mitrums
		#103 spiediens
		#101 nokriðòi
		#104 redzamîb
		
		$stacijas=array("RIGASLU", "RIDO99MS","RISA99PA","RUCAVA","RILP99PA","RIKO99PA","RISI99PA","RIDM99MS","RIAL99MS");
		$parametri=array(122,100,102,103,101,104);
		$masivs=array();
		$p=0;
		foreach ($stacijas as $stacija){
			foreach ($parametri as $parametrs){
				 array_push($masivs, $stacija."\t".$parametrs);
					    
			}
			
		}
		for ($i==0;$i<=5; $i++){
			array_push($masivs,"NaN");
		}
		file_put_contents('file.txt', implode("\n",$masivs));	
	}
	function getStationAndParam(){
		$farray=file("file.txt");
		$isNan=$farray[0];
		array_push($farray,$farray[0]);
		array_shift($farray);
		file_put_contents('file.txt', implode("",$farray));
		
		
		$StationAndParam=explode("\t",$isNan);
		if (trim($StationAndParam[0])=="NaN") {
			exit;
		}
		
		$this->parameter=$StationAndParam[1];
		$this->station=$StationAndParam[0];
		
	
		//var_dump ($farray);
		
	}
}
$mysqlUser="";
$mysqlPassw="";
$mysqlHost="";
$DB="";


$meteo=new meteoScraper(122,"RIGASLU");
//$meteo->createForCron();
//$meteo->getStationAndParam();

$meteo->prepareWWWString();
$meteo->curlingScraping();
echo $meteo->contents;
$meteo->connectDB($mysqlHost,$mysqlUser,$mysqlPassw,$DB);
//$meteo->saveInDB();
//$meteo->returnParamID(122);
exit;




?>