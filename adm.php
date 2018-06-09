<html>
<head>
</head>
<body>
<form method="post">
<input type="text" id="uptoE" name="doneuptoE" size=10 />
<input type="text" id="uptoN" name="doneuptoN" size=10 />
<input type="submit" value="Submit">
</form>
<?php

$doneuptoE = 0;
$doneuptoN = 0;
if (isset($_POST["doneuptoE"]))
{
    $doneuptoE = intval($_POST["doneuptoE"]);
}
if (isset($_POST["doneuptoN"]))
{
    $doneuptoN = intval($_POST["doneuptoN"]);
}
if ($doneuptoE > 0 && $doneuptoN > 0 && $doneuptoE < 1000000 && $doneuptoN < 1000000)
{
    list($valid, $doneDist, $todoDist) = checkValidPoint($doneuptoE, $doneuptoN);
	if ($valid)
	{
		echo ("updating file<br>");
		updateDoneupto($doneuptoE, $doneuptoN);
        echo ("done up to: " . $doneuptoE . ", " . $doneuptoN . "<br>");
        echo ("done: " . round($doneDist / 1000, 1) . " km<br>");
        echo ("to do: " . round($todoDist / 1000, 1) . " km<br>");
	}
    else
    {
        echo ("invalid point: " . $doneuptoE . ", " . $doneuptoN . "<br>");
    }
}
else
{
    list($de, $dn) = readDoneupto();
    list($name, $note) = GetDetailsForPoint($de, $dn);
    echo ("currently at: " . $de . ", " . $dn . " &nbsp;&nbsp;&nbsp; " . $name . " &nbsp;&nbsp;&nbsp; " . $note);
}

function GetDistanceBetweenPoints($fromE, $fromN, $toE, $toN)
{
    $distE = $toE - $fromE;
    $distN = $toN - $fromN;
    $dist = sqrt($distE * $distE + $distN * $distN);
    return $dist;
}

function GetDetailsForPoint($de, $dn)
{
	$datafile = "fulldata.json"/*"fulldataInitTest0.json"*/;
	$fulldata = file_get_contents ($datafile);
	$fulljson = json_decode($fulldata, true);

	foreach ($fulljson["mainWS"] as $point)
	{
    	if ($point["E"] == $de && $point["N"] == $dn)
		{
            return array($point["Name"], $point["Note"]);
        }
    }
    
    return array("", "");
}


function checkValidPoint($de, $dn)
{
	$datafile = "fulldata.json"/*"fulldataInitTest0.json"*/;
	$fulldata = file_get_contents ($datafile);
	$fulljson = json_decode($fulldata, true);
    $cumulDist = 0;
    $todoDist = 0;
    $found = false;
    $lastPointE = 0;
    $lastPointN = 0;

	foreach ($fulljson["mainWS"] as $point)
	{
        $dist = GetDistanceBetweenPoints($lastPointE, $lastPointN, $point["E"], $point["N"]);
        if ($lastPointE != 0 && $lastPointN != 0)
        {
            if ($found)
            {
                $todoDist += $dist;     
            }
            else
            {
                $cumulDist += $dist;
            }
        }

		if ($point["E"] == $de && $point["N"] == $dn)
		{
            $found = true;
		}

        $lastPointE = $point["E"];
        $lastPointN = $point["N"];
	}
	return array($found, $cumulDist, $todoDist);
}

function updateDoneupto($de, $dn)
{
	$ptStr = $de . "," . $dn;
	$fh = fopen("doneupto.txt", 'r+');
	fwrite($fh, $ptStr);
	fclose($fh);
	return 0;
}

function readDoneupto()
{
	$fh = fopen("doneupto.txt", 'r');
    $strRead = fread($fh, 14);
	fclose($fh);
    $pt = explode(",", $strRead, 2);
	return $pt;
}

?>
</body>
</html>