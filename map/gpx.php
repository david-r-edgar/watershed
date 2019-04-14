<?php

$base = "http://" . $_SERVER['SERVER_NAME'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], basename(__FILE__)));

header('Content-type:"application/gpx+xml"; charset="UTF-8"');
header('Content-Disposition: inline; filename="route.gpx"');

require 'coordConversion.php';

// ideally want to select start and end point from list, then pick all between
// list could pass in coords of both points
// so api should accept x1,y1 & x2,y2, then find all intermediates inclusive

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

$fulldata = file_get_contents($base . "api/waypoints/markers?xl=" . $leftBound . "&xr=" . $rightBound . "&yb=" . $bottomBound . "&yt=" . $topBound);
$fulljson = json_decode($fulldata, true);

echo "<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" version=\"1.1\" creator=\"http://www.loughrigg.org/watershed/gpx.php\">\n";
echo "<rte>\n";
echo "<name>watershed</name>\n";

foreach ($fulljson as $point)
{
	$ptWGS84 = NGR2LL_d($point["E"],$point["N"]);
	echo "<rtept lat=\"" . $ptWGS84[0] . "\" lon=\"" . $ptWGS84[1] . "\">\n";
	echo "<name>" . $point["Name"] . "</name>\n";
	echo "<desc>" . $point["Note"] . "</desc>\n";
	echo "</rtept>\n";
}
echo "</rte>\n</gpx>\n";

?>
