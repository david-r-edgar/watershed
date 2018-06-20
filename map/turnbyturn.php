<html>
<head>
<title>
</title>
<style>

.eachline
{
	font-family: "Trebuchet MS",Helvetica,sans-serif;
    font-size: 0.9em;
    /*line-height: 1.2em;*/
}

.directions
{
    margin-left: 1em;
    font-size: 0.9em;
    vertical-align: baseline;
    position: relative;
    width: 14.3em;
    display: inline-block;
}

.mcoord,.oscoord,.name,.note
{
    margin: 0em 0.3em 0em 0.3em;
}

.mcoord,.oscoord
{
    font-family: "Lucida Console", Monaco, monospace;
    font-size: 0.9em;
    letter-spacing: 0em;
    display: inline-block;
}

.oscoord
{
    margin-left: 0.4em;
    margin-right: 1.0em;
}

.name
{
    font-weight: bold;
}

.note
{
    font-size: 0.84em;
}

.dist
{
    display: inline-block;
    width: 3.0em;
    text-align: right;
    padding-right: 0.5em;
}

.cumuldist
{
    display: inline-block;
    width: 4.8em;
    text-align: right;
    padding-right: 0.5em;
    padding-left: 0.2em;
    font-family: "Lucida Console", Monaco, monospace;
    font-size: 0.9em;
    color: #666;
}

.bearingdeg, .compasspoint
{
    margin-right: 1em;
}

.bearingdeg
{
    display: inline-block;
    text-align: right;
    width: 1.4em;
}

.compasspoint
{
    width: 1.8em;
    display: inline-block;
}

.i100k
{
    margin-left: 0.6em;
    font-size: 0.8em;
    color: #888;
}

.note
{
    display: inline-block;
}

</style>
</head>
<body>

<?php


/**
 * Converts this numeric grid reference to standard OS grid reference
 *
 * @param {Number} [digits=6] Precision of returned grid reference (6 digits = metres)
 * @return {String)           This grid reference in standard format
 */
function ConvertEastingAndNorthingToOSgridref($e, $n, $digits)
{
    if (null == $digits) $digits = 10;
    if ((!is_numeric($e)) || (!is_numeric($n)))
    {
        return "";
    }
    
    // get the 100km-grid indices
    $e100k = floor($e/100000);
    $n100k = floor($n/100000);

    if ($e100k < 0 || $e100k > 6 || $n100k < 0 || $n100k > 12) return "";

    // translate those into numeric equivalents of the grid letters
    $l1 = (19-$n100k) - (19-$n100k)%5 + floor(($e100k+10)/5);
    $l2 = (19-$n100k)*5%25 + $e100k%5;
    
    // compensate for skipped 'I' and build grid letter-pairs
    if ($l1 > 7) $l1++;
    if ($l2 > 7) $l2++;
    $letPair = chr($l1 + ord('A')) . chr($l2 + ord('A'));

    // strip 100km-grid indices from easting & northing, and reduce precision
    $e = floor(($e%100000)/pow(10,5-$digits/2));
    $n = floor(($n%100000)/pow(10,5-$digits/2));

    //$gridRef = $letPair . ' ' . padLz($e, ($digits/2)) . ' ' . padLz($n, ($digits/2));

    return array($letPair, $e100k, padLz($e, ($digits/2)), $n100k, padLz($n, ($digits/2)));
}

/** Pads a number with sufficient leading zeros to make it w chars wide */
function padLz($number, $digits)
{
    $n = strval($number);
    $l = strlen($n);
    for ($i=0; $i < $digits - $l; $i++) { $n = '0' . $n; }
    return $n;
}


function GetDistanceBetweenPoints($fromE, $fromN, $toE, $toN)
{
    $distE = $toE - $fromE;
    $distN = $toN - $fromN;
    $dist = sqrt($distE * $distE + $distN * $distN);
    return $dist;
}

function GetBearingToNext($distE, $distN, $dist)
{
    $nonReflexAngleFromN = rad2deg(acos($distN / $dist));
    $bearing = ($distE < 0) ? (360 - $nonReflexAngleFromN) : $nonReflexAngleFromN;
    return $bearing;
}

function GetCompassPointForBearing($deg)
{
    $numericCompassPoint = (floor(($deg/22.5)+0.5)) % 16;
    $compassPointAbbrev=array("N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE",
                              "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW");
    return $compassPointAbbrev[$numericCompassPoint];
}



//get left, right, top, bottom bounds from args
$leftBound = 200000;
$rightBound = 700000;
$bottomBound = 100000;
$topBound = 1000000;

if (isset($_GET["xl"])) $leftBound = (int)$_GET["xl"];
if (isset($_GET["xr"])) $rightBound = (int)$_GET["xr"];
if (isset($_GET["yb"])) $bottomBound = (int)$_GET["yb"];
if (isset($_GET["yt"])) $topBound = (int)$_GET["yt"];

$resultArr = array();

$datafile = "fulldata.json";
$fulldata = file_get_contents ($datafile);
$fulljson = json_decode($fulldata, true);

//echo "<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" version=\"1.1\" creator=\"http://www.loughrigg.org/watershed/gpx.php\">\n";
//echo "<rte>\n";
//echo "<name>watershed</name>\n";







$lastPointE = 0;
$lastPointN = 0;

$cumulDist = 0;

foreach ($fulljson["mainWS"] as $point)
{
    list($twoLetters, $e100k, $e5, $n100k, $n5)
        = ConvertEastingAndNorthingToOSgridref($point["E"], $point["N"], 10);
    
    //$gridRef = $letPair . ' ' . padLz($e, ($digits/2)) . ' ' . padLz($n, ($digits/2));

    //return array($letPair, $e100k, padLz($e, ($digits/2)), $n100k, padLz($n, ($digits/2)));
    
    
    $dist = GetDistanceBetweenPoints($lastPointE, $lastPointN, $point["E"], $point["N"]);
    $bearingDeg = GetBearingToNext($point["E"] - $lastPointE, $point["N"] - $lastPointN, $dist);
    $compassPoint = GetCompassPointForBearing($bearingDeg);

    echo "<div class=eachline>\n";
      
    echo "<span class=directions>\n";
    if ($lastPointE != 0 && $lastPointN != 0)
    {
        $cumulDist += $dist;
        echo "<span class=dist>" . round($dist, 0) . "m</span>";
        echo "<span class=compasspoint>" . $compassPoint . "</span>";
        echo "<span class=bearingdeg>" . round($bearingDeg, 0) . "</span>";
        echo "<span class=cumuldist>" . number_format($cumulDist / 1000, 1, ".", "") . "km</span>";
    }
    echo "</span>\n";

	//echo "<span class=mcoord>" . $point["E"] . "</span><span class=mcoord>" . $point["N"] . "</span>";

    echo "<span class=oscoord>";
    echo "<span>" . $twoLetters . "</span>";
    echo "<span class=i100k>" . $e100k . "</span>";
    echo "<span>" . $e5 . "</span>";
    echo "<span class=i100k>" . $n100k . "</span>";
    echo "<span>" . $n5 . "</span>";
    echo "</span>";

	echo "<span class=name>" . $point["Name"] . "</span>\n";
	echo "<span class=note>" . $point["Note"] . "</span>\n";
    echo "</div>\n";
    
    $lastPointE = $point["E"];
    $lastPointN = $point["N"];
}

?>

</body>
</html>