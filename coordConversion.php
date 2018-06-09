<?
// Lat Long functions in PHP
//
// With thanks to Andy, G4JNT for inspiration in GEOG, and to the OSGB for their white paper on coordinate transformation
// describing the iterative method used
// thanks to the Ordnance survey of Ireland for details of the true and false origins of the Irish grid
//
// You may use and redistribute this code under the terms of the GPL see http://www.gnu.org/copyleft/gpl.html
//
//
// Written by Richard
// www.megalithia.com
// v0.something 27/2/2000
// v 1.01 28 June 2004
// v 1.02 6 Aug 2004 line 89 add "0" to chars in $ngr=stripcharsnotinbag Thx Andy
// v 1.03 9 Mar 2005 Jan (Klingon) added conversion to WGS84 map datum and removed limitation of digits of the grid ref
// v 1.04 10 Aug 2005 Richard correct error trapping (only manifest on malformed ngrs
//
// This code is predicated on the assumption that your are ONLY feeding it Irish or UK Grid references.
// It uses the single char prefix of Irish grid refs to tell the difference, UK grid refs have a two letter prefix.
// We would like an even number of digits for the rest of the grid ref.
// Anything in the NGR other than 0-9, A-Z, a-z is eliminated.
// WARNING this assumes there are no decimal points in your NGR components.
// (before v 1.03) you must have at least 4 numerical digits eg TM1234 and no more than eight eg TM12345678 otherwise
// you and your data are viewed with suspicion.
// NEW (Jan): at least the letter prefix of the Grid ref, no upper limit
//
// The transformation from OSGB36/Ireland 1965 to WGS84 is more precise than 5 m.
//
// The function is case insensitive
//
//call_user_func("NGR2LL",$ngr);
// Return value is array($country,$error,$lat,$long);
// you may test error = OK if blank, otherwise you will usually get a "malformed NGR"
//
// Removes all characters which do NOT appear in string $bag
// from string $s.
function stripCharsNotInBag($s,$bag)
{
$returnString="";
// Search through string's characters one by one.
// If character is in bag, append to returnString.
for($i=0;$i<strlen($s);$i++)
{
// Check that current character isn't whitespace.
$c=substr($s,$i,1);
if(strstr($bag,$c))
{
$returnString.=$c;
}
}
return $returnString;
}
// haversine distance computation.
// Haversines are good for small diffs in lat,long which is usually the case in terrestrial GPS calcs
// you can KO this function if not wanted - it is not part of the NGR2LL calc
function GCdist($lat1,$lon1,$lat2,$lon2)
{
// expects input in radians, give result in km
// mean radius of the earth in km
$earth_rad=6366.71;
// haversinehttp://mathforum.org/library/drmath/view/51879.html
$dlon=$lon2-$lon1;
$dlat=$lat2-$lat1;
// above all in degrees mind you
$a=pow(sin($dlat/2.0),2.0)+cos($lat1)*cos($lat2)*pow(sin($dlon/2.0),2.0);
$c=2*atan2(sqrt($a),sqrt(1.0-$a));
$d=$earth_rad*$c;
return $d;
// old non-haversine return acos(sin($lat1)*sin($lat2)+cos($lat1)*cos($lat2)*cos($lon1-$lon2))*$earth_rad;
}


function bearing($lat1,$lon1,$lat2,$lon2)
{
// Inspiration thanks to http://www.movable-type.co.uk/scripts/LatLong.html
$dlong=($lon2-$lon1)*M_PI/180.0;
$lat1=$lat1*M_PI/180.0;
$lat2=$lat2*M_PI/180.0;
return( 180/M_PI*atan2(sin($dlong)*cos($lat2),cos($lat1)*sin($lat2) - sin($lat1)*cos($lat2)*cos($dlong)) );
}


//convert E & N in numeric OSGB36 format (6 SF each, no two letter prefix) to WGS84 lat & lon
function NGR2LL_d($e, $n)
{	
	// USE BRitish values
	// okay up to here
	// now for the heavy stuff
	// deg2rad
	$dr=180.0/M_PI;
	// True Origin is 2 deg W
	$phi0uk=-2.0;
	// True Origin is 49 deg N
	$lambda0uk=49.0;
	// scale factor @ central meridian
	$F0uk=0.9996012717;
	// True origin in 400 km E of false origin
	$E0uk=400000.0;
	//True origin is 100 km S of false origin
	$N0uk=-100000.0;
	// semi-major axis (in line to equator) 0.996012717 is yer scale @ central meridian
	$auk=6377563.396*$F0uk;
	//semi-minor axis (in line to poles)
	$buk=6356256.91*$F0uk;
	// flatness=a1-b1/(a1+b1)
	$n1uk=0.00167322025032508731869331280635710896296;
	// first eccentricity squared=2*f-f^2where f=(a1-b1)/a1
	$e2uk=0.006670539761597529073698869358812557054558;
	// radius of earth
	//$re=6371.29;
	$k=($n-$N0uk)/$auk+$lambda0uk/$dr;
	$nextcounter=0;
	do
	{
		$nextcounter=$nextcounter+1;
		$k3=$k-$lambda0uk/$dr;
		$k4=$k+$lambda0uk/$dr;
		$j3=(1.0+$n1uk+1.25*pow($n1uk,2.0)+1.25*pow($n1uk,3.0))*$k3;
		$j4=(3.0*$n1uk+3.0*pow($n1uk,2.0)+2.625*pow($n1uk,3.0))*sin($k3)*cos($k4);
		$j5=(1.875*pow($n1uk,2.0)+1.875*pow($n1uk,3.0))*sin(2.0*$k3)*cos(2.0*$k4);
		$j6=35.0/24.0*pow($n1uk,3.0)*sin(3.0*$k3)*cos(3.0*$k4);
		$m=$buk*($j3-$j4+$j5-$j6);
		$k=$k+($n-$N0uk-$m)/$auk;
	}
	// Loop
	while((abs($n-$N0uk-$m)>0.000000000001)&&($nextcounter<10000));
	$v=$auk/sqrt(1.0-$e2uk*pow(sin($k),2.0));
	$r=$v*(1.0-$e2uk)/(1.0-$e2uk*pow(sin($k),2.0));
	$h2=$v/$r-1.0;
	$y1=$e-$E0uk;
	$j3=tan($k)/(2.0*$r*$v);
	$j4=tan($k)/(24.0*$r*pow($v,3.0))*(5.0+3.0*pow(tan($k),2.0)+$h2-9.0*pow(tan($k),2.0)*$h2);
	$j5=tan($k)/(720.0*$r*pow($v,5.0))*(61.0+90.0*pow(tan($k),2.0)+45.0*pow(tan($k),4.0));
	$k9=$k-$y1*$y1*$j3+pow($y1,4.0)*$j4-pow($y1,6.0)*$j5;
	$j6=1.0/(cos($k)*$v);
	$j7=1.0/(cos($k)*6.0*pow($v,3.0))*($v/$r+2.0*pow(tan($k),2.0));
	$j8=1.0/(cos($k)*120.0*pow($v,5.0))*(5.0+28.0*pow(tan($k),2.0)+24.0*pow(tan($k),4.0));
	$j9=1.0/(cos($k)*5040.0*pow($v,7.0));
	$j9=$j9*(61.0+662.0*pow(tan($k),2.0)+1320.0*pow(tan($k),4.0)+720.0*pow(tan($k),6.0));
	$long=$phi0uk+$dr*($y1*$j6-$y1*$y1*$y1*$j7+pow($y1,5.0)*$j8-pow($y1,7.0)*$j9);
	$lat=$k9*$dr;

    // convert long/lat to Cartesian coordinates
    $v=6377563.396/sqrt(1.0-$e2uk*pow(sin($k),2.0));
    $cartxa=$v*cos($k9)*cos($long/$dr);
    $cartya=$v*cos($k9)*sin($long/$dr);
    $cartza=(1.0-$e2uk)*$v*sin($k9);
    // Helmert-Transformation from OSGB36 to WGS84 map date
    $rotx=-0.1502/3600.0*M_PI/180.0;
    $roty=-0.2470/3600.0*M_PI/180.0;
    $rotz=-0.8421/3600.0*M_PI/180.0;
    $scale=-20.4894/1000000.0;
    $cartxb=446.448+(1.0+$scale)*$cartxa+$rotz*$cartya-$roty*$cartza;
    $cartyb=-125.157-$rotz*$cartxa+(1.0+$scale)*$cartya+$rotx*$cartza;
    $cartzb=542.06+$roty*$cartxa-$rotx*$cartya+(1.0+$scale)*$cartza;
    // convert Cartesian to long/lat
    $awgs84=6378137.0;
    $bwgs84=6356752.3141;
    $e2wgs84=0.00669438003551279089034150031998869922791;
    $lambdaradwgs84=atan($cartyb/$cartxb);
    $long=$lambdaradwgs84*180.0/M_PI;
    $pxy=sqrt(pow($cartxb,2.0)+pow($cartyb,2.0));
    $phiradwgs84=atan($cartzb/$pxy/(1.0-$e2wgs84));
    $nextcounter=0;
    do
    {
        $nextcounter=$nextcounter+1;
        $v=$awgs84/sqrt(1.0-$e2wgs84*pow(sin($phiradwgs84),2.0));
        $phinewwgs84=atan(($cartzb+$e2wgs84*$v*sin($phiradwgs84))/$pxy);
        $phiradwgs84=$phinewwgs84;
    }
    // Loop
    while((abs($phinewwgs84-$phiradwgs84)>0.000000000001)&&($nextcounter<10000));
    $lat=$phiradwgs84*180.0/M_PI;

	return array($lat,$long);
}














function NGR2LL($ngr)
{
// returns a error,lat,long list
$country="";
$ngr=stripCharsNotInBag(strtoupper($ngr),"0123456789ABCDEFGHJKLMNOPQRSTUVWXYZ");
$lett=stripCharsNotInBag($ngr,"ABCDEFGHJKLMNOPQRSTUVWXYZ");

if(strlen($lett)==1)
{
$error="";
$lat=0.0;
$long=0.0;
$num=stripCharsNotInBag($ngr,"01232456789");
$le=strlen($num);
$country="Irish";
if($le%2==1)
    {
    // bust odd numerical parts
    $error="Malformed numerical part of NGR";
    $lat=0.0;
    $long=0.0;
    }
else
{
    $pr=$le/2;
    // split into northings
    $n=substr($num,$pr);
    // and eastings
    $e=substr($num,0,$pr);
    $pr=pow(10.0,(5.0-$pr));
    $T1=ord(substr($lett,0,1))-65;
    if($T1>8)
    {
        $T1=$T1-1;
    }
    $e=100000.0*($T1%5.0)+$e*$pr;
    $n=$n*$pr+100000.0*(4.0-floor($T1/5.0));
    // USE IRISH values
    // okay up to here
    // now for the heavy stuff
    // deg2rad
    $dr=180.0/M_PI;
    // True Origin is 8 deg W
    $phi0ir=-8.0;
    // True Origin is 53.5 deg N
    $lambda0ir=53.5;
    // scale factor @ central meridian
    $F0ir=1.000035;
    // True origin in 200 km E of false origin
    $E0ir=200000.0;
    //True origin is 250km N of false origin
    $N0ir=250000.0;
    // semi-major axis (in line to equator) 1.000035 is yer scale @ central meridian
    $air=6377340.189*$F0ir;
    //semi-minor axis (in line to poles)
    $bir=6356034.447*$F0ir;
    // flatness=a1-b1/(a1 + b1)
    $n1ir=0.001673220384152058651484728058385228837777;
    // first eccentricity squared=2*f-f^2 where f=(a1-b1)/a1
    $e2ir=0.006670540293336110419293763349975612794125;
    // radius of earth
    //$re=6371.29;
    $k=($n-$N0ir)/$air+$lambda0ir/$dr;
    $nextcounter=0;
    do
    {
    $nextcounter=$nextcounter+1;
    $k3=$k-$lambda0ir/$dr;
    $k4=$k+$lambda0ir/$dr;
    $j3=(1.0+$n1ir+1.25*pow($n1ir,2.0)+1.25*pow($n1ir,3.0))*$k3;
    $j4=(3.0*$n1ir+3.0*pow($n1ir,2.0)+2.625*pow($n1ir,3.0))*sin($k3)*cos($k4);
    $j5=(1.875*pow($n1ir,2.0)+1.875*pow($n1ir,3.0))*sin(2.0*$k3)*cos(2.0*$k4);
    $j6=35.0/24.0*pow($n1ir,3.0)*sin(3.0*$k3)*cos(3.0*$k4);
    $m=$bir*($j3-$j4+$j5-$j6);
    $k=$k+($n-$N0ir-$m)/$air;
    }
    // Loop
    while((abs($n-$N0ir-$m)>0.000000000001)&&($nextcounter<10000));
    $v=$air/sqrt(1.0-$e2ir*pow(sin($k),2.0));
    $r=$v*(1.0-$e2ir)/(1.0-$e2ir*pow(sin($k),2.0));
    $h2=$v/$r-1.0;
    $y1=$e-$E0ir;
    $j3=tan($k)/(2.0*$r*$v);
    $j4=tan($k)/(24.0*$r*pow($v,3.0))*(5.0+3.0*pow(tan($k),2.0)+$h2-9.0*pow(tan($k),2.0)*$h2);
    $j5=tan($k)/(720.0*$r*pow($v,5.0))*(61.0+90.0*pow(tan($k),2.0)+45.0*pow(tan($k),4.0));
    $k9=$k-$y1*$y1*$j3+pow($y1,4.0)*$j4-pow($y1,6.0)*$j5;
    $j6=1.0/(cos($k)*$v);
    $j7=1.0/(cos($k)*6.0*pow($v,3.0))*($v/$r+2.0*pow(tan($k),2.0));
    $j8=1.0/(cos($k)*120.0*pow($v,5.0))*(5.0+28.0*pow(tan($k),2.0)+24.0*pow(tan($k),4.0));
    $j9=1.0/(cos($k)*5040.0*pow($v,7.0));
    $j9=$j9*(61.0+662.0*pow(tan($k),2.0)+1320.0*pow(tan($k),4.0)+720.0*pow(tan($k),6.0));
    $long=$phi0ir+$dr*($y1*$j6-$y1*$y1*$y1*$j7+pow($y1,5.0)*$j8-pow($y1,7.0)*$j9);
    $lat=$k9*$dr;

    // v1.04 this bracket moved to just before elsif // }
    // convert long/lat to Cartesian coordinates
    $v=6377340.189/sqrt(1.0-$e2ir*pow(sin($k),2.0));
    $cartxa=$v*cos($k9)*cos($long/$dr);
    $cartya=$v*cos($k9)*sin($long/$dr);
    $cartza=(1.0-$e2ir)*$v*sin($k9);
    // Helmert-Transformation from Ireland 1965 to WGS84 map date
    $rotx=1.042/3600.0*M_PI/180.0;
    $roty=0.214/3600.0*M_PI/180.0;
    $rotz=0.631/3600.0*M_PI/180.0;
    $scale=8.15/1000000.0;
    $cartxb=482.53+(1.0+$scale)*$cartxa+$rotz*$cartya-$roty*$cartza;
    $cartyb=-130.596-$rotz*$cartxa+(1.0+$scale)*$cartya+$rotx*$cartza;
    $cartzb=564.557+$roty*$cartxa-$rotx*$cartya+(1.0+$scale)*$cartza;
    // convert Cartesian to long/lat
    $awgs84=6378137.0;
    $bwgs84=6356752.3141;
    $e2wgs84=0.00669438003551279089034150031998869922791;
    $lambdaradwgs84=atan($cartyb/$cartxb);
    $long=$lambdaradwgs84*180.0/M_PI;
    $pxy=sqrt(pow($cartxb,2.0)+pow($cartyb,2.0));
    $phiradwgs84=atan($cartzb/$pxy/(1.0-$e2wgs84));
    $nextcounter=0;
    do
    {
    $nextcounter=$nextcounter+1;
    $v=$awgs84/sqrt(1.0-$e2wgs84*pow(sin($phiradwgs84),2.0));
    $phinewwgs84=atan(($cartzb+$e2wgs84*$v*sin($phiradwgs84))/$pxy);
    $phiradwgs84=$phinewwgs84;
    }
    // Loop
    while((abs($phinewwgs84-$phiradwgs84)>0.000000000001)&&($nextcounter<10000));
    $lat=$phiradwgs84*180.0/M_PI;
    }
        } // v 1.04 mod
elseif(strlen($lett)==2)
{
    // British
    // first caclulate e,n
    $country="UK";
    $num=stripCharsNotInBag($ngr,"0123456789");
    $le=strlen($num);

    if($le%2==1)
    {
    // bust odd numerical parts
        $error="Malformed numerical part of NGR";
        $lat=0.0;
        $long=0.0;
    }
    else
        {
        $pr=$le/2;
        // split into northings
        $n=substr($num,$pr);
        // and eastings
        $e=substr($num,0,$pr);
        $pr=pow(10.0,(5.0-$pr));
        $T1=ord(substr($lett,0,1))-65;
        if($T1>8)
            {
            $T1=$T1-1;
            }
        $T2=ord(substr($lett,1,1))-65;
        if($T2>8)
            {
            $T2=$T2-1;
            }
        $e=500000.0*($T1%5.0)+100000.0*($T2%5.0)-1000000.0+$e*$pr;
        $n=1900000.0-500000.0*floor($T1/5.0)-100000.0*floor($T2/5.0)+$n*$pr;
		
		echo "coord conv " . $e . "<br>\r\n";
		echo "coord conv " . $n . "<br>\r\n";
		
        // USE BRitish values
        // okay up to here
        // now for the heavy stuff
        // deg2rad
        $dr=180.0/M_PI;
        // True Origin is 2 deg W
        $phi0uk=-2.0;
        // True Origin is 49 deg N
        $lambda0uk=49.0;
        // scale factor @ central meridian
        $F0uk=0.9996012717;
        // True origin in 400 km E of false origin
        $E0uk=400000.0;
        //True origin is 100 km S of false origin
        $N0uk=-100000.0;
        // semi-major axis (in line to equator) 0.996012717 is yer scale @ central meridian
        $auk=6377563.396*$F0uk;
        //semi-minor axis (in line to poles)
        $buk=6356256.91*$F0uk;
        // flatness=a1-b1/(a1+b1)
        $n1uk=0.00167322025032508731869331280635710896296;
        // first eccentricity squared=2*f-f^2where f=(a1-b1)/a1
        $e2uk=0.006670539761597529073698869358812557054558;
        // radius of earth
        //$re=6371.29;
        $k=($n-$N0uk)/$auk+$lambda0uk/$dr;
        $nextcounter=0;
        do
        {
            $nextcounter=$nextcounter+1;
            $k3=$k-$lambda0uk/$dr;
            $k4=$k+$lambda0uk/$dr;
            $j3=(1.0+$n1uk+1.25*pow($n1uk,2.0)+1.25*pow($n1uk,3.0))*$k3;
            $j4=(3.0*$n1uk+3.0*pow($n1uk,2.0)+2.625*pow($n1uk,3.0))*sin($k3)*cos($k4);
            $j5=(1.875*pow($n1uk,2.0)+1.875*pow($n1uk,3.0))*sin(2.0*$k3)*cos(2.0*$k4);
            $j6=35.0/24.0*pow($n1uk,3.0)*sin(3.0*$k3)*cos(3.0*$k4);
            $m=$buk*($j3-$j4+$j5-$j6);
            $k=$k+($n-$N0uk-$m)/$auk;
        }
        // Loop
        while((abs($n-$N0uk-$m)>0.000000000001)&&($nextcounter<10000));
        $v=$auk/sqrt(1.0-$e2uk*pow(sin($k),2.0));
        $r=$v*(1.0-$e2uk)/(1.0-$e2uk*pow(sin($k),2.0));
        $h2=$v/$r-1.0;
        $y1=$e-$E0uk;
        $j3=tan($k)/(2.0*$r*$v);
        $j4=tan($k)/(24.0*$r*pow($v,3.0))*(5.0+3.0*pow(tan($k),2.0)+$h2-9.0*pow(tan($k),2.0)*$h2);
        $j5=tan($k)/(720.0*$r*pow($v,5.0))*(61.0+90.0*pow(tan($k),2.0)+45.0*pow(tan($k),4.0));
        $k9=$k-$y1*$y1*$j3+pow($y1,4.0)*$j4-pow($y1,6.0)*$j5;
        $j6=1.0/(cos($k)*$v);
        $j7=1.0/(cos($k)*6.0*pow($v,3.0))*($v/$r+2.0*pow(tan($k),2.0));
        $j8=1.0/(cos($k)*120.0*pow($v,5.0))*(5.0+28.0*pow(tan($k),2.0)+24.0*pow(tan($k),4.0));
        $j9=1.0/(cos($k)*5040.0*pow($v,7.0));
        $j9=$j9*(61.0+662.0*pow(tan($k),2.0)+1320.0*pow(tan($k),4.0)+720.0*pow(tan($k),6.0));
        $long=$phi0uk+$dr*($y1*$j6-$y1*$y1*$y1*$j7+pow($y1,5.0)*$j8-pow($y1,7.0)*$j9);
        $lat=$k9*$dr;
        // v1.04 this bracket moved to just before elsif // }
    // convert long/lat to Cartesian coordinates
    $v=6377563.396/sqrt(1.0-$e2uk*pow(sin($k),2.0));
    $cartxa=$v*cos($k9)*cos($long/$dr);
    $cartya=$v*cos($k9)*sin($long/$dr);
    $cartza=(1.0-$e2uk)*$v*sin($k9);
    // Helmert-Transformation from OSGB36 to WGS84 map date
    $rotx=-0.1502/3600.0*M_PI/180.0;
    $roty=-0.2470/3600.0*M_PI/180.0;
    $rotz=-0.8421/3600.0*M_PI/180.0;
    $scale=-20.4894/1000000.0;
    $cartxb=446.448+(1.0+$scale)*$cartxa+$rotz*$cartya-$roty*$cartza;
    $cartyb=-125.157-$rotz*$cartxa+(1.0+$scale)*$cartya+$rotx*$cartza;
    $cartzb=542.06+$roty*$cartxa-$rotx*$cartya+(1.0+$scale)*$cartza;
    // convert Cartesian to long/lat
    $awgs84=6378137.0;
    $bwgs84=6356752.3141;
    $e2wgs84=0.00669438003551279089034150031998869922791;
    $lambdaradwgs84=atan($cartyb/$cartxb);
    $long=$lambdaradwgs84*180.0/M_PI;
    $pxy=sqrt(pow($cartxb,2.0)+pow($cartyb,2.0));
    $phiradwgs84=atan($cartzb/$pxy/(1.0-$e2wgs84));
    $nextcounter=0;
    do
    {
        $nextcounter=$nextcounter+1;
        $v=$awgs84/sqrt(1.0-$e2wgs84*pow(sin($phiradwgs84),2.0));
        $phinewwgs84=atan(($cartzb+$e2wgs84*$v*sin($phiradwgs84))/$pxy);
        $phiradwgs84=$phinewwgs84;
    }
    // Loop
    while((abs($phinewwgs84-$phiradwgs84)>0.000000000001)&&($nextcounter<10000));
    $lat=$phiradwgs84*180.0/M_PI;
        }   // v 1.04 mod
}
else
{
    $error="Malformed NGR";
    $lat=0.0;
    $long=0.0;
}
return array($country,$error,$lat,$long);
}
?>