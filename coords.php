<?php
include "rdp.php";

$type = 0; //0 = all, 1 = completed, 2 = to do

if (isset($_GET["compl"])) $type = 1;
if (isset($_GET["todo"])) $type = 2;
if (isset($_GET["rdp"])) $rdpMetres = $_GET["rdp"];

$doneuptoFile = "doneupto.txt";
$doneuptoData = file_get_contents($doneuptoFile);
$doneuptoPoint = explode(",", $doneuptoData);
$doneuptoLon = (int)$doneuptoPoint[0];
$doneuptoLat = (int)$doneuptoPoint[1];

$datafile = "fulldata.json"/*"fulldataInitTest0.json"*/;
$fulldata = file_get_contents ($datafile);
$fulljson = json_decode($fulldata, true);
$foundDoneupto = false;

//var_dump($fulljson["mainWS"]);
//echo "points initially : " . count($fulljson["mainWS"]) . "\r\n";

$reducedFull = $fulljson["mainWS"];
if ($type == 1 || $type == 2)
{
	$index = 0;
	foreach ($reducedFull as $point)
	{
		if ((int)$point["E"] == $doneuptoLon && (int)$point["N"] == $doneuptoLat)
		{
            $foundDoneupto = true;
			break;
		}
		$index++;
	}
	//echo "index: " . $index . "\r\n";
	if (true != $foundDoneupto)
    {
        //if doneupto doesn't match, treat all points as remaining
        $index = 0;
    }

    if ($type == 1)
	{
		$reducedFull = array_slice($reducedFull, 0, $index + 1);
	}
    else
	{
		$remainingPoints = count($reducedFull) - $index;
		//echo "remainingPoints: " . $remainingPoints . "\r\n";
		$reducedFull = array_slice($reducedFull, $index, $remainingPoints);
	}
}
//echo "after todo/compl, points : " . count($reducedFull) . "\r\n";

if (isset($rdpMetres))
{
	$reducedFull = RamerDouglasPeucker($reducedFull, $rdpMetres);
}
//var_dump($rdpRes);
//echo "after RamerDouglasPeucker, points : " . count($reducedFull) . "\r\n";

foreach ($reducedFull as $point)
{
	echo ((string)$point["E"] . "," . (string)$point["N"] . ";");
}


/*
foreach ($fulljson["mainWS"] as $point)
{
	if ($type == 0)
	{
		echo ((string)$point["E"] . "," . (string)$point["N"] . ";");
	}
	else if ($type == 1)
	{
		echo ((string)$point["E"] . "," . (string)$point["N"] . ";");
		if ((int)$point["E"] == $doneuptoLon && (int)$point["N"] == $doneuptoLat)
		{
			break;
		}
	}
	else if ($type == 2)
	{
		if ((int)$point["E"] == $doneuptoLon && (int)$point["N"] == $doneuptoLat)
		{
			$foundDoneupto = true;
		}
		if (true == $foundDoneupto)
		{
			echo ((string)$point["E"] . "," . (string)$point["N"] . ";");
		}
	}
}
*/
?>
