<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Toggle Venting and Seasons</title>
    <link rel="STYLESHEET" type="text/css" href="css/weatherstyle.css">

<?php // If not on the LAN, bounce home.

    // $chOurUnitCaps = "C";

    // include common definitions, we'll need these throughout
    include 'tempsCommonDefinitions.php';
    // include common functions code, we'll need these throughout
    include 'tempsCommonFunctions.php';

    if ($_SERVER['REMOTE_ADDR'] != $rgchOurIP) {
        print('    <meta http-equiv="refresh" content="0; URL=tempOverviewC.php">');
        }
    ?>

</head>
<body topmargin="0" style="padding-top: 0px; margin: 0px;">

<?php // ACTUAL START OF CODE

// all vents default to CLOSED
$rgchSetVentStatus = array("closed", "closed", "closed", "closed");

// what that means when talking to the relay server
$rgchParamVerb = array(	"closed" => "off", "open" => "on",);

// initialise curl
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// get our OPERATING SEASON
$rgchOperatingSeason = rgchGetCurrentSeasonMode();

// GET TEMPERATURE DATA for decision making below

// Sometimes the server doesn't respond correctly, I test for that against
// humidity of zero. Since they throttle access to the server with free API
// keys, I pause half of a second whenever that happens before trying again.
// After 3 tries, I give up.

//$file = "https://api.ambientweather.net/v1/devices?applicationKey=1937589558be4416801ba9ce91c33d527b9db0be9cc9495a8d3871bd0290f104&apiKey=17b3b7b32ae54fe7b3c5102ebf10706493f792652940453390ba19d7267ffc68";
$rgchPolledOrCached = "Polled";
$dataTries=0;
// Assume we _will_ have temperature data
$fUseTemperature=true;

do {
    $dataTries++;
    $data = file_get_contents($fileAmbientServer);
    $data = mb_substr($data, strpos($data, '{'));
    $data = mb_substr($data, 0, -1);
    $sensors = json_decode($data, true);
    if ($sensors['lastData']['humidity1'] == 0) usleep(500000);
    } while (($sensors['lastData']['humidity1'] == 0) && ($dataTries < 3));

// If we give up then we just have all zeros it's clearly wrong, so don't use temperature.
if ($sensors['lastData']['humidity1'] == 0) {
    $fUseTemperature=false;
    } else {
    // we got some numbers, so make averages
    $InsideAvg=fpConvertTemp(
        fpAverageValidity(
           $sensors['lastData']['temp5f'], $sensors['lastData']['humidity5'],
           $sensors['lastData']['temp6f'], $sensors['lastData']['humidity6'],
           $sensors['lastData']['temp7f'], $sensors['lastData']['humidity7'],
           $sensors['lastData']['temp8f'], $sensors['lastData']['humidity8'],
           $sensors['lastData']['tempinf'], $sensors['lastData']['humidityin'])
        );
    // For cleanliness
    $Outside=fpConvertTemp($sensors['lastData']['tempf']);
    }

// Start setting up our page.

print '<span class="location">';
print '<style> a:link { color: maroon; font-variant: small-caps; font-style: normal; text-decoration: none; font-size: smaller; } a:visited { color: maroon; font-variant: small-caps; font-style: normal; text-decoration: none; font-size: smaller; } </style>';
print '<p><div style="line-height: 130%"><center>';

// Get the local time, for calculating whether to open the vent.
$arrTime=localtime(time(),true);

// Decide whether the vent should be open.

// winter mode
if ( $rgchOperatingSeason == "winter" ) {
    // first: time of day
//echo "**** WINTER ****";
    if (( $arrTime['tm_hour'] == 7 ) || 			// open in 7am hour
        ( $arrTime['tm_hour'] > 10 ) && ($arrTime['tm_hour'] < 17  ) ||	// Open 11am-4pm
        ( $arrTime['tm_hour'] == 19 )) { 			// open 7pm hour
        $rgchSetVentStatus[0] = "open";
	} // right time of day
    // second: even if closed by time, open if warmer outside by 2 degrees F than in
    // anyway (unlikely). This is to buffer for error range on indoor averages.
    if ( $fUseTemperature ) {
        if ( $Outside > ( $InsideAvg + 1.99 ) ) { $rgchSetVentStatus[0] = "open"; }
        }
    }

// summer mode
if ( $rgchOperatingSeason == "summer" ) {
//echo "**** SUMMER ****";
    // first: time of day
    if (( $arrTime['tm_hour'] == 8 ) ||			// open in 8am hour
        ( $arrTime['tm_hour'] == 19 ) ||			// open at 11pm hour
        ( $arrTime['tm_hour'] == 23 ) ||			// open at 7pm hour
        ( $arrTime['tm_hour'] < 6 )) {			// open midnight-5:59am
        $rgchSetVentStatus[0] = "open";
	} // right time of day
    // second: even if closed by time, open if cooler outside than inside anyway by at
    // least two degrees F. This is to buffer for error range on indoor averages.
    if ( $fUseTemperature ) {
        if ( $InsideAvg > ( $Outside + 1.99 ) ) { $rgchSetVentStatus[0] = "open"; }
        }
    }

// smoke mode
if ( $rgchOperatingSeason == "smoke" ) {
    // Smoke means always closed
//echo "**** SMOKE ****";
    $rgchSetVentStatus[0] = "closed";
    }

// if we're in disable mode, we don't tell the relay anything one way or the other.
if ( $rgchOperatingSeason != "disable" ) {
    // we're not in disable mode, tell the relay what to do
    curl_setopt($curl, CURLOPT_URL, 'http://192.168.1.230/api/relay/0/' . $rgchParamVerb[$rgchSetVentStatus[0]]);
    $rgchResponse=curl_exec($curl);
//echo "\n\r about to query vent status 0<br>\n\r";
echo "vent " . rgchAcquireVentStatus(0) ;
//echo "\n\r";
    curl_close($curl);
    }

// close page
print '</span></center>';
print '</p>';
print '</span>';
?>

</body>
</html>
