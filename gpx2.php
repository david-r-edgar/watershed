<?php
header('Content-type:"application/gpx+xml"; charset="UTF-8"');
header('Content-Disposition: inline; filename="route.gpx"');

require_once('coordConversion.php');

//get left, right, top, bottom bounds from args
$leftBound = 100000;
$rightBound = 700000;
$bottomBound = 100000;
$topBound = 1000000;

if (isset($_GET["xl"])) $leftBound = (int)$_GET["xl"];
if (isset($_GET["xr"])) $rightBound = (int)$_GET["xr"];
if (isset($_GET["yb"])) $bottomBound = (int)$_GET["yb"];
if (isset($_GET["yt"])) $topBound = (int)$_GET["yt"];

if (isset($_GET["start"])) $startIndex = (int)$_GET["start"];
if (isset($_GET["stop"])) $stopIndex = (int)$_GET["stop"];


$resultArr = array();

$datafile = "fulldata.json";
$fulldata = file_get_contents ($datafile);
$fulljson = json_decode($fulldata, true);

echo "<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" version=\"1.1\" creator=\"http://www.loughrigg.org/watershed/gpx.php\">\n";
echo "<rte>\n";
echo "<name>watershed" . $startIndex . "</name>\n";

$i = 0;
foreach ($fulljson["mainWS"] as $point)
{
	if (($startIndex <= $i) && ($stopIndex > $i))
	{
		$ptWGS84 = NGR2LL_d($point["E"],$point["N"]);
		echo "<rtept lat=\"" . $ptWGS84[0] . "\" lon=\"" . $ptWGS84[1] . "\">\n";
		echo "<name>" . $point["Name"] . "</name>\n";
		echo "<desc>" . $point["Note"] . "</desc>\n";
		echo "</rtept>\n";
	}

    $i++;
}


/*
foreach ($fulljson["mainWS"] as $point)
{
	if (($leftBound <= $point["E"]) && ($point["E"] <= $rightBound)
		&& ($bottomBound <= $point["N"]) && ($point["N"] <= $topBound))
	{
		$ptWGS84 = NGR2LL_d($point["E"],$point["N"]);
		echo "<rtept lat=\"" . $ptWGS84[0] . "\" lon=\"" . $ptWGS84[1] . "\">\n";
		echo "<name>" . $point["Name"] . "</name>\n";
		echo "<desc>" . $point["Note"] . "</desc>\n";
		echo "</rtept>\n";
	}
}
*/
echo "</rte>\n</gpx>\n";

?>
