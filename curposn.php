<?php

$resultArr = array();

$doneuptoFile = "doneupto.txt";
$doneuptoData = file_get_contents($doneuptoFile);
$doneuptoPoint = explode(",", $doneuptoData);
$doneuptoLon = (int)$doneuptoPoint[0];
$doneuptoLat = (int)$doneuptoPoint[1];

$datafile = "fulldata.json"/*"fulldataInitTest0.json"*/;
$fulldata = file_get_contents ($datafile);
$fulljson = json_decode($fulldata, true);
$mainWS = $fulljson["mainWS"];

$reducedFull = array_slice($mainWS, 1, count($mainWS) - 2);

foreach ($reducedFull as $point)
{
	if ((int)$point["E"] == $doneuptoLon && (int)$point["N"] == $doneuptoLat)
	{
        array_push($resultArr, $point);
		break;
	}
}
echo json_encode($resultArr);

?>
