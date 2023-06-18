<?php
//
// common functions library for functions used by multiple
// modules of the air-exchange hvac and weather monitoring
// system.
//

// FIRST include common definitions (do we actually need this?)

include 'tempsCommonDefinitions.php';

//
// rgchAcquireRealWindowStatus()
//
// query the ACTUAL WINDOWS AND DOORS for their open/closed status by
// talking to the arduino board hanging off the USB port as a virtual
// serial device this is so cool
//
// returns a string containing json data representing that status
// (and some extra shit for future expansion if we use the other
// digital pins someday)
//
function rgchAcquireRealWindowStatus() {

    // acquire, speed, and open USB serial to arduino board
    $usb='ttyACM0';
    $fSerial=fopen("/dev/$usb", "r+");

    if (!$fSerial) {
        // can't get the file handle? well fuck
        return NULL;
        }

    // wait .1 seconds, give the device lots of time to start
    usleep(100000);

    // set nonblocking because blocking I/O on serial is a nightmare
    stream_set_blocking($fSerial, false);

    // clearing anything we find in the serial buffer for some damn reason
    // don't know why this happens BUT it does
    do {
        $noise=fread($fSerial,1);
        } while ( $noise != NULL );

    // tell arduino to write its status to the serial port
    fwrite($fSerial,"a");	// any byte will do but ONLY one
    fflush($fSerial);	        // make sure it does

    // wait .05 seconds, give the device a moment to think
    usleep(50000);

    // because shit gets stupid sometimes let's do this one character
    // at a time. this way if we get some nulls due to some fuckery
    // delays we'll still get our json - but sanity check it at 300
    // characters because something's broken if it gets that long

    $chByteRead=' ';
    $rgchRealWindowsJSON='';
    while ( ($chByteRead != '}') && (strlen($rgchRealWindowsJSON) < 300 ) ) { 
        $chByteRead=fread($fSerial,1);
        $rgchRealWindowsJSON.=$chByteRead;
        }

    // close the file
    fclose($fSerial);

    if ($chByteRead != '}') {
        // this means we exited on too long a strong wtf
        return NULL;
    }

    return $rgchRealWindowsJSON;
    }

// rgchAcquireVentStatus
// Get the status of any given vent numbered 0-3
// return string "open" or "closed" if relay is running and talking
// returns null if can't be contacted or return is damaged

function rgchAcquireVentStatus(int $iVentNumber) {

    // initialise curl, set options, get response
    $curl = curl_init('http://192.168.1.230/api/relay');

    // set up some short timeouts - 3 seconds to connect, 3 to respond
    // neither should be more than 100ms irl
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3); 
    curl_setopt($curl, CURLOPT_TIMEOUT, 3);

    // set up for a clean GET and then GET
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    $rgchResponse=curl_exec($curl);

    // close curl
    curl_close($curl);

    // null response, pass that on
    if (!$rgchResponse) { return null; }

    // parse the data
    $data = json_decode($rgchResponse);

    // if we didn't get a valid answer from the box, return null
    if ( !$data ) { return null; }

    // now we have data yay
    if ( $data->relays[$iVentNumber] == 1 ) {
        return "open";
        } else {
        return "closed";
        };
}

// rgchGetCurrentSeasonMode
//
// function to get the current season in a string.
function rgchGetCurrentSeasonMode() {

    global $fileSystemStatusCache;

    $data = file_get_contents($fileSystemStatusCache);
    $data = mb_substr($data, strpos($data, '{'));
    $data = mb_substr($data, 0, -1);
    $windows = json_decode($data, true);
    return $windows["season"];
}

//
// fpConvertTemp(temperature in F)
//
// in C, converts F as presented to C, modifying the result to
// the nearest half a degree C.
//
// in F, R, and K, converts and modifies to nearest degree.
//
function fpConvertTemp($fpTempF) {
    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    if ( $chOurUnitCaps == $chCelciusUnitCaps )
        return (intval(($fpTempF-32)/1.8)+
           round(2*((($fpTempF-32)/1.8)-intval(($fpTempF-32)/1.8)))/2);
    // else
    if ( $chOurUnitCaps == $chKelvinUnitCaps )
        {
        return round((($fpTempF-32)/1.8)+273.15);
        }
    // else
    $RankineOffset=0;
    if ( $chOurUnitCaps == $chRankineUnitCaps )
        $RankineOffset=459.67;
    return (round(intval(($fpTempF+$RankineOffset)*10)/10));
}

//
// function that returns a stylesheet class based upon whether the
// first temperature is warmer or cooler or the same as the second.
// possible return values are "temp" "tempWarmer" and "tempCooler"
// all of which are defined in the stylesheet
//
function rgchSetFirstTempColour($temp1, $temp2) {

    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chOurUnitCaps;

    $tempColour="temp";    // default is black

    // If either temp shows as 0F, which just doesn't happen here,
    // return black. What's really happening is that some data is
    // not being fetched properly and the better way to test this is
    // via corresponding humidity of zero, but we don't have those
    // numbers with us. So just return black for no judgement made.
    // In short, -- THIS IS A HACK --
    if ( ($temp1 == 0) || ($temp2 == 0) ) return $tempColour;

    // perform set colour on raw data for C, on rounded data for F.
    // NOTE RAW DATA IS STILL IN F BECAUSE FUCK EVERYTHING. That's why
    // it's .89 below, because half a degree C is .9 of a degree F
    if ( $chOurUnitCaps == $chCelciusUnitCaps )
        {
        $fpModTemp1 = $temp1;
        $fpModTemp2 = $temp2;
        }
    else
        { // this is F, K, or R, I'm not putting in the work for Rod lol
        $fpModTemp1 = fpConvertTemp($temp1);
        $fpModTemp2 = fpConvertTemp($temp2);
        }

    // F, K, R same degree stays black because conversion, in C within 0.5C.
    if (($fpModTemp1 - $fpModTemp2) > 0.89 ) $tempColour="tempWarmer";
    if (($fpModTemp2 - $fpModTemp1) > 0.89 ) $tempColour="tempCooler";
    return $tempColour;
}

//
// function to print the big temperature number in 4.1f or 2.0f
// depending upon which page version we are.
//
// note this is not the biggEST temperature, this is the largest
// of the data elements in a standard temperature/humidity/humidex
// brick.
//
function PrintLargeTemp($rgchStyleName, $fpDisplayTemp, $chUnit) {
    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    if ( $chOurUnitCaps == $chCelciusUnitCaps )
        {
        printf("<span class='%s'>%4.1f&deg;<span class='unit'>%s</span></span>",
            $rgchStyleName, $fpDisplayTemp, $chUnit);
        } else { // K, F, R
        printf("<span class='%s'>%2.0f&deg;<span class='unit'>%s</span></span>",
            $rgchStyleName, $fpDisplayTemp, $chUnit);
        }
    return;
    }

//
// function to print the smallest temperature in 4.1f or 2.0f
// depending upon what our units are.
//
function PrintSmallTemp($fpDisplayTemp, $chUnit) {
    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    if ( $chOurUnitCaps == $chCelciusUnitCaps )
        {
        printf("<span class='humidex'>%4.1f&deg;%s</span>",
            $fpDisplayTemp, $chUnit);
        } else { // K, F, R
        printf("<span class='humidex'>%2.0f&deg;%s</span>",
            $fpDisplayTemp, $chUnit);
        }
    return;
    }

//
// function to print humidity just for completeness (and ease of changing later)
//
function PrintHumidity($fpHumidity) {

    printf("<span class='humidity'>%2.0f%%</span>",$fpHumidity);
    return;
    }

//
// function to print a temp/humidity/humidex brick, uses the above functions
//
function PrintDataBrick($rgchTempColour, $fpTemp1, $fpUnit1, $fpHumidity, $fpHumidex, $fpUnit2) {

    if ($fpHumidity != 0) {
        PrintLargeTemp($rgchTempColour, $fpTemp1, $fpUnit1);
        print("<br/>");
        PrintHumidity($fpHumidity);
        print("<span class='blockdivider'> | ");
        PrintSmallTemp($fpHumidex, $fpUnit2);
        } else {
        print("<span class='temp'>----</span>");
        print("<br/>");
	print("<span class='humidity'>--</span>");
        print("<span class='humidex'>--</span>");
        }
    return;
}

//
//
// fpAverageValidity (temperature and humidity pairs)
//
// Produces an average but only of the valid values in the list. Minimum
// two values, as many as ten. The stupid part is that it has to check
// the vailidity of each value (termined by humidity of 0%, impossible)
// because sometimes we don't get data back from the server.
//
function fpAverageValidity($fpTemp1, $fpHumid1, $fpTemp2, $fpHumid2,
                           $fpTemp3 = NULL, $fpHumid3 = 0,
                           $fpTemp4 = NULL, $fpHumid4 = 0,
                           $fpTemp5 = NULL, $fpHumid5 = 0,
                           $fpTemp6 = NULL, $fpHumid6 = 0,
                           $fpTemp7 = NULL, $fpHumid7 = 0,
                           $fpTemp8 = NULL, $fpHumid8 = 0,
                           $fpTemp9 = NULL, $fpHumid9 = 0,
                           $fpTemp10 = NULL, $fpHumid10 = 0)
{
    $iTotalValid=0;
    $iTotalTemps=0;
    if ($fpHumid1 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp1;
       }
    if ($fpHumid2 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp2;
       }
    if ($fpHumid3 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp3;
       }
    if ($fpHumid4 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp4;
       }
    if ($fpHumid5 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp5;
       }
    if ($fpHumid6 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp6;
       }
    if ($fpHumid7 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp7;
       }
    if ($fpHumid8 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp8;
       }
    if ($fpHumid9 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp9;
       }
    if ($fpHumid10 != 0) {
       $iTotalValid++;
       $iTotalTemps+=$fpTemp10;
       }

    // really this is an error but it needs a number back
    if ($iTotalValid == 0) return $pTemp1;
    return $iTotalTemps/$iTotalValid;
}


?>
