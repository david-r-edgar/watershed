<?php
header('Content-type:"application/gpx+xml"; charset="UTF-8"');
header('Content-Disposition: inline; filename="route.gpx"');

require_once('coordConversion.php');

// usage: get 6,6 grid refs from two points on map
// first one (nearest Dunnet Head) is the 'from', later one is the 'to'
// then the URL should look like:
// https://loughrigg.org/watershed/map/gpx3.php?fromE=314527&fromN=614069&toE=317562&toN=611680
// all points en-route (inclusive of from and to) will be included

// works fine for <500 points (30 secs), after that, coord conversion takes too long

$fromE = 0;
$fromN = 0;
$toE = 0;
$toN = 0;

if (isset($_GET["fromE"])) $fromE = (int)$_GET["fromE"];
if (isset($_GET["fromN"])) $fromN = (int)$_GET["fromN"];
if (isset($_GET["toE"])) $toE = (int)$_GET["toE"];
if (isset($_GET["toN"])) $toN = (int)$_GET["toN"];

$resultArr = array();

$datafile = "fulldata.json";
$fulldata = file_get_contents ($datafile);
$fulljson = json_decode($fulldata, true);

echo "<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" version=\"1.1\" creator=\"http://www.loughrigg.org/watershed/gpx.php\">\n";
echo "<rte>\n";
echo "<name>watershed_" . $fromE . "_" . $fromN . "_" . $toE . "_" . $toN . "</name>\n";

$insideSelection = false;
foreach ($fulljson["mainWS"] as $point)
{
	// echo "checking point " . $point["E"] . ", " . $point["N"] . "\n";

	// do this check before outputting xml, so that the 'from' point is included
	if ($point["E"] == $fromE && $point["N"] == $fromN) {
		$insideSelection = true;
	}

	if ($insideSelection)
	{
		// $start = microtime(true);
		$ptWGS84 = NGR2LL_d($point["E"],$point["N"]);
		// $time_elapsed_secs = microtime(true) - $start;
		// echo "time_elapsed_secs " . $time_elapsed_secs . "\n";
		echo "<rtept lat=\"" . $ptWGS84[0] . "\" lon=\"" . $ptWGS84[1] . "\">\n";
		echo "<name>" . $point["Name"] . "</name>\n";
		echo "<desc>" . $point["Note"] . "</desc>\n";
		echo "</rtept>\n";
	}

	// do this check after outputting xml, so that we include the 'to' point too
	if ($point["E"] == $toE && $point["N"] == $toN) {
		$insideSelection = false;
	}
}

echo "</rte>\n</gpx>\n";

?>
