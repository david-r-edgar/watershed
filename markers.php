<?php

//get left, right, top, bottom bounds from args
$leftBound = 100000;
$rightBound = 700000;
$bottomBound = 100000;
$topBound = 1000000;

if (isset($_GET["xl"])) $leftBound = (int)$_GET["xl"];
if (isset($_GET["xr"])) $rightBound = (int)$_GET["xr"];
if (isset($_GET["yb"])) $bottomBound = (int)$_GET["yb"];
if (isset($_GET["yt"])) $topBound = (int)$_GET["yt"];

$resultArr = array();

$datafile = "fulldata.json";
$filecontents = file_get_contents ($datafile);
$contentsJSON = json_decode($filecontents, true);
$firstAndLast = false;

if (isset($_GET["st_fi"])) { $firstAndLast = true; }

$datasetName = "mainWS";
if (isset($_GET["issues"])) { $datasetName = "issues"; }
else if (isset($_GET["watersources"])) { $datasetName = "watersources"; }

if ($firstAndLast)
{
	$datasetLen = count($contentsJSON[$datasetName]);
	array_push($resultArr, $contentsJSON[$datasetName][0]);
	array_push($resultArr, $contentsJSON[$datasetName][$datasetLen - 1]);
}
else
{
	if (array_key_exists($datasetName, $contentsJSON))
	{
		foreach ($contentsJSON[$datasetName] as $point)
		{
			//echo (string)$point["E"]. "," . (string)$point["N"] . "\r\n";

			if ($leftBound <= $point["E"] && $point["E"] <= $rightBound
				&& $bottomBound <= $point["N"] && $point["N"] <= $topBound)
			{
				array_push($resultArr, $point);
			}
		  //echo ((string)$point["E"] . "," . (string)$point["N"] . ";");
		}
	}
}

echo json_encode($resultArr);

?>
