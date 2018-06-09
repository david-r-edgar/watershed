
<?php

function perpendicularDistance($ptX, $ptY, $l1x, $l1y, $l2x, $l2y)
{
	$result = 0;
	if ($l2x == $l1x)
	{
		//vertical lines - treat this case specially to avoid divide by zero
		$result = abs($ptX - $l2x);
	}
	else
	{
		$slope = (($l2y-$l1y) / ($l2x-$l1x));
		$passThroughY = (0-$l1x)*$slope + $l1y;
		$result = (abs(($slope * $ptX) - $ptY + $passThroughY)) / (sqrt($slope*$slope + 1));
	}
	return $result;
}

function RamerDouglasPeucker($pointList, $epsilon)
{
    // Find the point with the maximum distance
    $dmax = 0;
    $index = 0;
	$totalPoints = count($pointList);
    for ($i = 1; $i < ($totalPoints - 1); $i++)
	{		
        $d = perpendicularDistance($pointList[$i]["E"], $pointList[$i]["N"],
								   $pointList[0]["E"], $pointList[0]["N"],
								   $pointList[$totalPoints-1]["E"], $pointList[$totalPoints-1]["N"]);
			   
 	    if ($d > $dmax)
		{
            $index = $i;
            $dmax = $d;
		}
	}

	$resultList = array();
	
    // If max distance is greater than epsilon, recursively simplify
    if ($dmax >= $epsilon)
	{
        // Recursive call
		$recResults1 = RamerDouglasPeucker(array_slice($pointList, 0, $index + 1), $epsilon);
		$recResults2 = RamerDouglasPeucker(array_slice($pointList, $index, $totalPoints - $index), $epsilon);

        // Build the result list
		$resultList = array_merge(array_slice($recResults1, 0, count($recResults1) - 1),
								  array_slice($recResults2, 0, count($recResults2)));
	}
    else
	{
        $resultList = array($pointList[0], $pointList[$totalPoints-1]);
	}
    // Return the result
    return $resultList;
}

?>
