<?php
// function to do a status heartbeat on the whole-structure air-exchange system
// if the status is correct writing it again is harmless. if it is wrong, then it
// is fixed and set. resetting to the same value causes no hardware status change
// and so is also harmless to do it repeatedly.
// -- darako blue 2023/4/30
//
// all vents default to CLOSED

function Heartbeat() {

    global $fileAmbientServer;
    global $rgchVentControllerIP;

    $rgchSetVentStatus = array("closed", "closed", "closed", "closed");
    $chOurUnitCaps = 'C'; // doesn't actually matter but must be defined

    // what that means when talking to the relay server
    $rgchParamVerb = array( "closed" => "off", "open" => "on",);

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

    // $file = "https://api.ambientweather.net/v1/devices?applicationKey=1937589558be4416801ba9ce91c33d527b9db0be9cc9495a8d3871bd0290f104&apiKey=17b3b7b32ae54fe7b3c5102ebf10706493f792652940453390ba19d7267ffc68";
    $rgchPolledOrCached = "Polled";
    $dataTries=0;

    // Assume we _will_ have temperature data
    $fUseTemperature = true;
    // Assume inside air is _better_ than outside air
    $fInsideAirClean = true;
    $fOutsideAirBetter = false;
    // Assume we are NOT finding ourselves in a sudden smoke environment.
    $fSmokeOverride = false;

    do {
        $dataTries++;
        $data = file_get_contents($fileAmbientServer);
        if ($data) {
            $data = mb_substr($data, strpos($data, '{'));
            $data = mb_substr($data, 0, -1);
            $sensors = json_decode($data, true);
            } else {
            //if ($sensors['lastData']['humidity1'] == 0)
            usleep(500000);
            }
        } while (($sensors['lastData']['humidity1'] == 0) && ($dataTries < 3));

    // If we give up then we just have all zeros it's clearly wrong, so don't use temperature
    // or any other server data
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
echo "(air indoor " . $sensors['lastData']['pm25_in'] . ", ";
echo "air outdoor " . $sensors['lastData']['pm25'] . ") ";
        // Now check inside vs. outside air quality. If inside is bad, set $fInsideAirClean
        // to false
        if ( $sensors['lastData']['pm25_in'] > 9 ) {
echo "(fInsideAirClean false) ";
            $fInsideAirClean = false;
            }
        // Regardless of inside bad or not, if outside is better (with a buffer),
        // set $fOutsideAirBetter to true
        if ( ( $sensors['lastData']['pm25_in'] - 5 ) >= $sensors['lastData']['pm25'] ) {
echo "(fOutsideAirBetter true) ";
            $fOutsideAirBetter = true;
            }
        // SMOKE OVERRIDE - if it's smokey outside and we didn't know / set smoke mode,
        // this would override EVERYTHING else. This triggers when outside air is worse
        // than inside by 10 points
        if ( $sensors['lastData']['pm25'] >= ( $sensors['lastData']['pm25_in'] + 10 ) ) {
            $fSmokeOverride = true;
            }
        }

    // Get the local time, for calculating whether to open the vent.
    $arrTime=localtime(time(),true);

    // Decide whether the vent should be open.

    // winter mode
    if ( $rgchOperatingSeason == "winter" ) {
        // first: time of day
//echo "**** WINTER ****";
        if (( $arrTime['tm_hour'] == 7 ) || 			// open in 7am hour
            ( $arrTime['tm_hour'] > 10 ) && ($arrTime['tm_hour'] < 17  ) ||	// Open 11am-4:59pm
            ( $arrTime['tm_hour'] == 19 )) { 			// open 7pm hour
            $rgchSetVentStatus[0] = "open";
echo "(open time) ";
	    } // right time of day
        // second: even if closed by time, open if warmer outside by 2 degrees F than in
        // anyway (unlikely). This is to buffer for error range on indoor averages.
        // then ALSO check for bad air!
        if ( $fUseTemperature ) {
            if ( $Outside > ( $InsideAvg + 1.99 ) ) {
                $rgchSetVentStatus[0] = "open";
                echo "(open temp) ";
                }
            }
        // In the winter, if outside air is cleaner, just go with it regardless of anything else
        if ( $fOutsideAirBetter ) {
echo "(open ppm) ";
            $rgchSetVentStatus[0] = "open";
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
echo "(open time) ";
	    } // right time of day
        // second: even if closed by time, open if cooler outside than inside anyway by at
        // least two degrees F. This is to buffer for error range on indoor averages.
        if ( $fUseTemperature ) {
            if ( $InsideAvg > ( $Outside + 1.99 ) ) {
echo "(open temp) ";
                $rgchSetVentStatus[0] = "open";
                }
            }
        // in summer mode, if indoor air qualifies as clean, then even if outdoor is better,
        // keep it closed.
        if ( !$fInsideAirClean ) {
            if ( $fOutsideAirBetter ) {
echo "(open ppm) ";
                $rgchSetVentStatus[0] = "open";
                }
            }
        }

    // smoke mode
    if ( $rgchOperatingSeason == "smoke" ) {
        // Smoke means always closed
//echo "**** SMOKE ****";
        $rgchSetVentStatus[0] = "closed";
        }

    // All determinations have been made, now override them if smokey outside.
    if ( $fSmokeOverride ) {
        $rgchSetVentStatus[0] = "closed";
        echo "(PPM OVERRIDE) ";
        }

    // if we're in disable mode, we don't tell the relay anything one way or the other.
    if ( $rgchOperatingSeason != "disable" ) {
        // we're not in disable mode, tell the relay what to do
        curl_setopt($curl, CURLOPT_URL, 'http://' . $rgchVentControllerIP . '/api/relay/0/' . $rgchParamVerb[$rgchSetVentStatus[0]]);
        $rgchResponse=curl_exec($curl);
//echo "\n\r about to query vent status 0<br>\n\r";
//echo "vent " . rgchAcquireVentStatus(0) ;
//echo "\n\r";
        curl_close($curl);
        }
    }

?>
