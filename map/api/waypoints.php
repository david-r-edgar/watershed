<?php

class Waypoints {

  public static function getDone() {
    $markersArr = array();
    $filecontents = file_get_contents ("../fulldata.json");
    $contentsJSON = json_decode($filecontents, true);

    $doneuptoFile = "../doneupto.txt";
    $doneuptoData = file_get_contents($doneuptoFile);
    $doneuptoPoint = explode(",", $doneuptoData);

    foreach ($contentsJSON["mainWS"] as $point)
    {
      array_push($markersArr, $point);
      if ((int)$point["E"] == (int)$doneuptoPoint[0] &&
        (int)$point["N"] == (int)$doneuptoPoint[1])
      {
        break;
      }
    }

    return $markersArr;
  }

  public static function getToDo() {
    $markersArr = array();
    $filecontents = file_get_contents ("../fulldata.json");
    $contentsJSON = json_decode($filecontents, true);

    $doneuptoFile = "../doneupto.txt";
    $doneuptoData = file_get_contents($doneuptoFile);
    $doneuptoPoint = explode(",", $doneuptoData);

    $foundDoneupto = false;
    foreach ($contentsJSON["mainWS"] as $point)
    {
      if (!$foundDoneupto &&
        (int)$point["E"] == (int)$doneuptoPoint[0] &&
        (int)$point["N"] == (int)$doneuptoPoint[1])
      {
        $foundDoneupto = true;
      }
      if ($foundDoneupto)
      {
        array_push($markersArr, $point);
      }
    }

    return $markersArr;
  }

  public static function getWaypointInfo($e, $n) {
    $wsCache = file_get_contents ("../mainWScache.json");
    $wptInfoArr = json_decode($wsCache, true);
    return $wptInfoArr[$e . "," . $n];
  }

  public static function getMarkers($xl, $xr, $yb, $yt) {
    $markersArr = array();

    $mainWithDistances = Waypoints::getMainWithDistances();

    foreach ($mainWithDistances as $point)
    {
      if ($xl <= $point["E"] && $point["E"] <= $xr
        && $yb <= $point["N"] && $point["N"] <= $yt)
      {
        array_push($markersArr, $point);
      }
    }

    return $markersArr;
  }

  public static function getMajorMarkers() {
    $majorMarkersArr = array();
    $filecontents = file_get_contents ("../fulldata.json");
    $contentsJSON = json_decode($filecontents, true);
    $datasetLen = count($contentsJSON["mainWS"]);

    $start = $contentsJSON["mainWS"][0];
    $wptInfo = Waypoints::getWaypointInfo($start["E"], $start["N"]);
    $start = array_merge($start, $wptInfo);
    array_push($majorMarkersArr, $start);

    $finish = $contentsJSON["mainWS"][$datasetLen - 1];
    $wptInfo = Waypoints::getWaypointInfo($finish["E"], $finish["N"]);
    $finish = array_merge($finish, $wptInfo);
    array_push($majorMarkersArr, $finish);

    return $majorMarkersArr;
  }

  public static function getIssues() {
    $filecontents = file_get_contents ("../fulldata.json");
    $contentsJSON = json_decode($filecontents, true);
    return $contentsJSON["issues"];
  }

  public static function getWaterSources() {
    $filecontents = file_get_contents ("../fulldata.json");
    $contentsJSON = json_decode($filecontents, true);
    return $contentsJSON["watersources"];
  }

  public static function getCurrentPosition() {

    $doneuptoFile = "../doneupto.txt";
    $doneuptoData = file_get_contents($doneuptoFile);
    $doneuptoPoint = explode(",", $doneuptoData);
    $doneuptoLon = (int)$doneuptoPoint[0];
    $doneuptoLat = (int)$doneuptoPoint[1];

    $datafile = "../fulldata.json";
    $fulldata = file_get_contents ($datafile);
    $fulljson = json_decode($fulldata, true);
    $mainWS = $fulljson["mainWS"];

    $reducedFull = array_slice($mainWS, 1, count($mainWS) - 2);

    foreach ($reducedFull as $point)
    {
      if ((int)$point["E"] == $doneuptoLon && (int)$point["N"] == $doneuptoLat)
      {
        break;
      }
    }

    $wptInfo = Waypoints::getWaypointInfo($point["E"], $point["N"]);
    $point = array_merge($point, $wptInfo);

    return $point;
  }

  public static function setCurrentPosition($doneuptoE, $doneuptoN) {

    list($valid, $doneDist, $todoDist) = Waypoints::checkValidPoint($doneuptoE, $doneuptoN);
    if ($valid)
    {
      Waypoints::updateDoneupto($doneuptoE, $doneuptoN);
    }

    return [$valid, $doneDist, $todoDist];
  }

  private static function getMainWithDistances()
  {
    $mainWithDistanceSoFar = array();

    $filecontents = file_get_contents ("../fulldata.json");
    $contentsJSON = json_decode($filecontents, true);

    $mainWithDistanceSoFar = array();
    $lastPoint = null;
    $totalDistance = 0;
    foreach ($contentsJSON["mainWS"] as $point)
    {
      if (!$lastPoint) {
        $point['distFromLast'] = 0;
        $point['distSoFar'] = 0;
      }
      else {
        $point['distFromLast'] = Waypoints::distanceBetweenPoints($lastPoint["E"], $lastPoint["N"],
          $point["E"], $point["N"]);
        $point['distSoFar'] = $lastPoint['distSoFar'] + $point['distFromLast'];
      }
      $lastPoint = $point;

      array_push($mainWithDistanceSoFar, $point);
    }

    $totalDistance = $lastPoint['distSoFar'];

    $pointWithDistRemaining = function($point) use ($totalDistance) {
      $point['distRemaining'] = $totalDistance - $point['distSoFar'];
      return $point;
    };
    $mainWithDistances = array_map($pointWithDistRemaining, $mainWithDistanceSoFar);

    return $mainWithDistances;
  }

  // TODO move to a utility class?
  private static function distanceBetweenPoints($fromE, $fromN, $toE, $toN)
  {
    $distE = $toE - $fromE;
    $distN = $toN - $fromN;
    $dist = sqrt($distE * $distE + $distN * $distN);
    return $dist;
  }

  // TODO rewrite this using getMainWithDistances???
  private static function checkValidPoint($de, $dn)
  {
    $filecontents = file_get_contents ("../fulldata.json");
    $fulljson = json_decode($filecontents, true);
    $cumulDist = 0;
    $todoDist = 0;
    $found = false;
    $lastPointE = 0;
    $lastPointN = 0;

    foreach ($fulljson["mainWS"] as $point)
    {

      $dist = Waypoints::distanceBetweenPoints($lastPointE, $lastPointN, $point["E"], $point["N"]);
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

  private static function updateDoneupto($de, $dn)
  {
    $ptStr = $de . "," . $dn;
    $fh = fopen("../doneupto.txt", 'r+');
    fwrite($fh, $ptStr);
    fclose($fh);
    return 0;
  }

}

?>
