<?php
#122 temperatыra
#100 vзjр
#102 mitrums
#103 spiediens
#101 nokriртi
#104 redzamоb



#RIGASLU - Rоga
#RIDO99MS - Dobele
#RISA99PA - Saldus
#RUCAVA - Rucava
#RILP99PA - Liepвja
#RIKO99PA - Kolka
#RISI99PA - Skrоveri
#RIDM99MS - Daugavpils
#RIAL99MS - Alыksne


$creationStr="2011/01/01";
$today=new DateTime();

try {
    $creation = new DateTime($creationStr);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}

$hp="http://www.meteo.lv/meteorologijas-operativo-datu-grafiks/?id=RISA99PA&parameterId=122&fullMap=0&date=".$creation->format("d.m.Y")."&time=23:00";

$ch=curl_init();
curl_setopt($ch, CURLOPT_URL, $hp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt ($ch, CURLOPT_COOKIEJAR, "ck"); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$contents = curl_exec($ch);

//echo $contents;
curl_close($ch);


$contents=str_replace(" ","",$contents);
$contents=str_replace("\t","",$contents);
$contents=str_replace("\n","",$contents);
$contents=str_replace("],[","];[",$contents);

$sakums= strpos($contents,"data.addRows")+14;
$beigas= strpos($contents,"varoptions")-3;

$contents= substr($contents,$sakums,$beigas-$sakums);

//echo $contents;
$datumasivs=explode(";",$contents);

//var_dump($datumasivs);

foreach ($datumasivs as $value){
	$pair=explode(",",$value);
	$pair[0]=str_replace("[","",$pair[0]);
	$pair[0]=str_replace("'","",$pair[0]);
	$pair[1]=str_replace("]","",$pair[1]);
	echo $pair[0]." ".$pair[1]."\n";
	}

?>