<?php

$datafile = "fulldata.json";
$fulldata = file_get_contents ($datafile);
$fulljson = json_decode($fulldata, true);

$lastPointE = 0;
$lastPointN = 0;

$cumulDist = 0;

$outArr = array();

function GetDistanceBetweenPoints($fromE, $fromN, $toE, $toN)
{
    $distE = $toE - $fromE;
    $distN = $toN - $fromN;
    $dist = sqrt($distE * $distE + $distN * $distN);
    return $dist;
}

foreach ($fulljson["mainWS"] as $point)
{
  if ($lastPointE != 0 && $lastPointN != 0)
  {
    $dist = GetDistanceBetweenPoints($lastPointE, $lastPointN, $point["E"], $point["N"]) / 1000;
    $cumulDist += $dist;
  }
  $key = $point["E"] . "," . $point["N"];

  $outArr[$key] = array(
    "distFromStart" => $cumulDist
  );

  $lastPointE = $point["E"];
  $lastPointN = $point["N"];
}

$totalDist = $cumulDist;

foreach ($outArr as $key => $info) {
  $outArr[$key]["distToFinish"] = $totalDist - $outArr[$key]["distFromStart"];
}

$file = fopen("mainWScache.json", "w");
fwrite($file, json_encode($outArr, JSON_PRETTY_PRINT));
fclose($file);

?>