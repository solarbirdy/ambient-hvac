<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Station Overview</title>
    <link rel="STYLESHEET" type="text/css" href="css/weatherstyle.css">
    <meta http-equiv="refresh" content="120">
</head>
<body topmargin="0" style="padding-top: 0px; margin: 0px;">
<table border=0 cellpadding=0 cellspacing=10px
    align="center" valign="top" cols="3"
    width="100%" height="100%">
<?php

// global definitions

// SET DISPLAY UNIT AND BASE PAGES HERE FIRST, BEFORE INCLUDING
// COMMON DEFINITIONS - some of them are calculated based on these.
//$chOurUnitCaps=substr(basename(__FILE__, '.php'),-1);
$chOurUnitCaps=substr(basename($_SERVER['REQUEST_URI'], '.php'),-1);
$chCelciusPage="tempOverviewC.php";
$chFarenheitPage="tempOverviewF.php";
$chKelvinPage="tempOverviewK.php";
$chRankinePage="tempOverviewC.php";	// THIS IS INTENTIONAL

// Now include and invoke common definitions
include 'tempsCommonDefinitions.php';

// Now include common functions
include 'tempsCommonFunctions.php';

// If filename somehow gets fucked up, default to Rankine so people know
// it's broken because honestly, who uses R? Nobody. And no one should.
if ( ($chOurUnitCaps != $chCelciusUnitCaps) &&
     ($chOurUnitCaps != $chFarenheitUnitCaps) &&
     ($chOurUnitCaps != $chKelvinUnitCaps) )
    $chOurUnitCaps=$chRankineUnitCaps;

//
// function to print the biggest temperature number in 4.1f or 2.0f
// depending upon which page version we are. code unification basically
// this is unique to the overview panel, so not in common code
//
function PrintLargestTemp($rgchStyleName, $fpDisplayTemp, $chUnit) {
    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    if ( ( $chOurUnitCaps == $chCelciusUnitCaps ) &&
         ( round($fpDisplayTemp) != intval($fpDisplayTemp) ) )
        {
        printf("<span class='%s'>%4.1f&deg;<span class='unit'>%s</span></span>",
            $rgchStyleName, $fpDisplayTemp, $chUnit);
        } else { // K, F, R, and C when not .5
        printf("<span class='%s'>%2.0f&deg;<span class='unit'>%s</span></span>",
            $rgchStyleName, $fpDisplayTemp, $chUnit);
        }
    return;
    }

//
// function to print dewpoint - also unique to the overview panel
//
function PrintDewpoint($fpDisplayTemp, $chUnit) {
    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    printf("<span class='humidex'>%2.0f&deg;%s</span>",
        $fpDisplayTemp, $chUnit);
    return;
    }

//
// function to print an outdoor temp/humidity/humidex brick,
// uses the above functions plus common code
//
function PrintOutdoorDataBrick($rgchTempColour, $fpTemp1, $fpUnit1, $fpHumidity, $fpHumidex, $fpDewpoint, $fpComposite, $fpHeatPillow, $fpUnit2, $iInPPM, $iOutPPM) {

    PrintLargestTemp($rgchTempColour . "Biggest", $fpTemp1, $fpUnit1);
    print("<br/>");
    print('<table border=0 cellpadding=0 cellspacing=10px align="center" valign="top" cols="5" width="70%">');
    print("<tr><td>");
    print("<small>composite<br/></small>");
    PrintDewpoint($fpComposite, $fpUnit2);
    print("</td><td>");
    print("<small>pillow<br/></small>");
    PrintDewpoint($fpHeatPillow, $fpUnit2);
    print("</td><td>");
    print("<small>dewpoint<br/></small>");
    PrintDewpoint($fpDewpoint, $fpUnit2);
    print("</td><td>");
    print("<small>humidity&nbsp;<br/></small>");
    PrintHumidity($fpHumidity);
    print("</td><td>");
    print("<small>humidex<br/></small>");
    PrintDewpoint($fpHumidex, $fpUnit2);
    print("</td></tr>");
    print("</table>");

    // Get our current operating season mode
    $rgchSeason = rgchGetCurrentSeasonMode();

    // If we are in SMOKE season - set up SMOKE!
    if ( $rgchSeason == "smoke" ) {
        print('<br><div class="temp"><span style="color: orange">smoke</span></div>');
        print('<span style="color: darkred">protocol is in effect</span>');
        } else {
        printf('<br><div class="temp" style="color: purple">%s</span></div>',
            $rgchSeason);
        print('protocol is in effect');
        }

    // display air quality
    print "<br>&nbsp;<br>air inside " . $iInPPM . " ppm | " . "outside " . $iOutPPM . " ppm";

    // Get vent status
    $rgchVentStatus = rgchAcquireVentStatus(0);
    if ( $rgchVentStatus )
        {
        print('<br>air intake vent is ' . $rgchVentStatus . '<br>');
        }
        else
        {
        print('<br><span style="color: red">air exchange system <b>not responding</b></span>');
        }
    

    return;
}

//
// function to print wind data block
//
function PrintWindBlock($fpSpeed, $fpDirection, $fpPeakToday) {
    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    // adjust to basic eight directions
    $fpDirectionAdjusted=intdiv($fpDirection+22.5,45);
    // adjust speeds to nearest 10th of whatever unit we're in
    if ( ( $chOurUnitCaps == $chCelciusUnitCaps ) ||
         ( $chOurUnitCaps == $chKelvinUnitCaps  ) )
        {
        $fpSpeedAdjusted=round($fpSpeed*16.09)/10;
        $fpPeakAdjusted=round($fpPeakToday*16.09)/10;
        $rgchWindUnit="kph";
        } else {
        $fpSpeedAdjusted=round($fpSpeed*10)/10;
        $fpPeakAdjusted=round($fpPeakToday*10)/10;
        $rgchWindUnit="mph";
        }

    print("<br/>");
    if ($fpSpeed < 4) print("<span class='temp' style='color: purple;'>calm</span>");
    if (($fpSpeed >= 4) && ($fpSpeed < 12 ))
        print("<span class='temp' style='color: darkgreen;'>light breeze</span>");
    if (($fpSpeed >= 12) && ($fpSpeed < 24 ))
        print("<span class='temp' style='color: green;'>breezy</span>");
    if (($fpSpeed >= 24) && ($fpSpeed < 32 ))
        print("<span class='temp' style='color: goldenrod;'>windy</span>");
    if (($fpSpeed >= 32) && ($fpSpeed < 46 ))
        print("<span class='temp' style='color: orange;'>gale</span>");
    if ($fpSpeed>=46) print("<span class='temp' style='color: red;'>STORM</span>");

    if ($fpSpeed >= 0.2) {
        printf("<br/><span class='wind'><span class='winddirection'>%4.1f %s ",
            $fpSpeedAdjusted, $rgchWindUnit);
        switch ($fpDirectionAdjusted) {
        case 0:
            print("northerly");
            break;
        case 1: 
            print("northeasterly");
            break;
        case 2: 
            print("easterly");
            break;
        case 3: 
            print("southeasterly");
            break;
        case 4:
            print("southerly");
            break;
        case 5:
            print("southwesterly");
            break;       
        case 6:
            print("westerly");
            break;
        case 7:
            print("northwesterly");
            break;
        default:
            print("ERROR");
            break;
            }
        print(" flow</span></span>");
        }
        else
        { print("<br/>"); }

    printf(" (%3.1f %s daily peak)", $fpPeakAdjusted, $rgchWindUnit);

    return;
}

//
// function to format and print a nice rain data block
//

function PrintRainBlock($fpHourly, $fpDaily, $fpEvent, $rgchLastRain) {

    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    // we'll use these a couple of places, so move it up here
    $raindate = DateTime::createFromFormat('Y-m-d?H:i:s+',
                $rgchLastRain, new DateTimeZone("UTC"));
    $raindate->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $todaynow = new DateTime("now"); // for hours and minutes
    $diff = $todaynow->diff($raindate);
    $diffDays = (integer)$diff->format("%R%a"); // days from start of day
    $diffSeconds = $todaynow->getTimestamp() - $raindate->getTimestamp();
    $diffMinutes = round($diffSeconds/60);
    $diffHours = round($diffMinutes/6)/10;	// single decimal point hours

    // all the values say no rain _and_ it's been at least 12 hours
    // since rain since we don't honestly know how fpEvent works
    if ( ( ($fpHourly + $fpDaily + $fpEvent) == 0 ) && ($diffHours >= 12) ) {
        print("<br/><span class='temp' style='color: purple;'>dry</span><br/>");
        } else {
        if ($fpHourly == 0) { // no hourly rate, but still recent rain
            if ($diffHours < 4) { // it has not actually rained in four hours
                print("<br/><span class='temp' style='color: DarkSlateBlue;'>rainy</span><br/>");
                } else {
                // there's an ongoing event but not within 4 hours of now
                // if it's within 12 hours, say wet; if not, say dry again.
                // this is almost entirely for overnight rain which will dry
                // off by the afternoon
                if ($diffHours < 12 ) {
                    print("<br/><span class='temp' style='color: DarkSlateBlue;'>wet</span><br/>");
                    } else {
                    print("<br/><span class='temp' style='color: purple;'>dry</span><br/>");
                    }
                }
            } else { // $fpHourly is _not_ zero, it's raining
            print("<br/><span class='temp' style='color: #0000a6;'>rain</span><br/>");
            }
        }

    // not raining in the last hour(?), say when it last rained
    if ($fpHourly == 0) {
        switch ($diffDays) {
            case 0: {
                if ($diffMinutes > 119) {
                    printf('<center><span style="line-height: 17px;">last rain %s',
                        $raindate->format('g:ia'));
                    if ($fpDaily != $fpEvent) {
                        print("</span><br/>");
                        } else {
                        if ($fpDaily+$fpEvent != 0) print("; </span>");
                        }
                    }
                break;
                }
            case -1: {
                printf('<center><span style="line-height: 17px;">since yesterday at %s</span><br/>',
                    $raindate->format('g:ia'));
                break;
                }
            default:
                printf('<center><span style="line-height: 24px;">since %s, %s</span><br/>',
                    $raindate->format('g:ia'),
                    $raindate->format('l, F jS'));
            }
        }

    if (($fpHourly + $fpDaily + $fpEvent) != 0 ) {
        if ( ( $chOurUnitCaps == $chCelciusUnitCaps ) ||
             ( $chOurUnitCaps == $chKelvinUnitCaps  ) )
            {
            if ($fpHourly != 0 )
                printf("%4.1f mm/hour, ",$fpHourly*25.4);
            if ($fpDaily == 0)
                { printf("no rain today"); } else
                { printf("%4.1f mm today",$fpDaily*25.4); }
            if ($fpDaily != $fpEvent)
                printf(", %4.1f mm this event", $fpEvent*25.4);
            } else { // F, R
            if ($fpHourly != 0 ) 
                printf('%4.2f" per hour, ',$fpHourly);
            if ($fpDaily == 0) 
                { printf("no rain today"); } else
                { printf('%4.2f" today',$fpDaily); }
            if ($fpDaily != $fpEvent)
                printf(', %4.2f" this event', $fpEvent);
            }
        }

    return;
}

//
// function to print solar radiation information in a block
//
function PrintSolarBlock($fpUV, $fpRadiation) {

    switch ($fpUV) {
        case 0:
        case 1:
        case 2:
        if ($fpRadiation < 0.4 ) {
            print("<br/><span class='temp'>dark</span><br/>");
            } else {
            if ( ($fpRadiation >= 0.4 ) && ($fpRadiation <= 20 ) ) {
                print("<br/><span class='temp' style='color: purple;'>very low</span><br/>");
                } else {
                print("<br/><span class='temp' style='color: darkgreen;'>low</span><br/>");
                }
            }
        break;
        case 3:
	case 4:
	case 5:
        print("<br/><span class='temp' style='color: GoldenRod;'>moderate</span><br/>");
        break;
        case 6:
	case 7:
        print("<br/><span class='temp' style='color: orange;'>high</span><br/>");
        break;
        case 8:
	case 9:
	case 10:
        print("<br/><span class='temp' style='color: red;'>very high</span><br/>");
        break;
        default:
        print("<br/><span class='temp' style='color: violet;'>extreme</span><br/>");
        break;
        }

    print("UV index " . $fpUV . " | " );
    print($fpRadiation . " watts/m<small><sup>2</sup></small>");

}

//
// function to print barometic pressure because completeness
//
function PrintBarometricBlock($fpPressure) {

    global $chCelciusUnitCaps, $chFarenheitUnitCaps, $chKelvinUnitCaps,
           $chRankineUnitCaps, $chOurUnitCaps;

    if ( ($chOurUnitCaps == $chCelciusUnitCaps ) ||
         ($chOurUnitCaps == $chKelvinUnitCaps ) ) {
        printf("<br/><span class='temp' style='color: purple;'>%5.1f<span class='unit'> kPa</span></span><br/>", $fpPressure*3.386);
        } else {
        printf("<br/><span class='temp' style='color: purple;'>%4.1f<span class='unit'> inHg</span></span><br/>", $fpPressure); 
        }
    printf("relative barometic pressure");
    return;
}

// main MAIN

// Sometimes the server doesn't respond correctly, I test for that against
// humidity of zero. Since they throttle access to the server with free API
// keys, I pause half of a second whenever that happens before trying again.
// After 3 tries, I give up.

// $file = "https://api.ambientweather.net/v1/devices?applicationKey=1937589558be4416801ba9ce91c33d527b9db0be9cc9495a8d3871bd0290f104&apiKey=17b3b7b32ae54fe7b3c5102ebf10706493f792652940453390ba19d7267ffc68";
$rgchPolledOrCached = "Polled";
$dataTries=0;

do {
    $dataTries++;
    $data = file_get_contents($fileAmbientServer);
    $data = mb_substr($data, strpos($data, '{'));
    $data = mb_substr($data, 0, -1);
    $sensors = json_decode($data, true);
    if ($sensors['lastData']['humidity1'] == 0) usleep(500000);
    } while (($sensors['lastData']['humidity1'] == 0) && ($dataTries < 3));

// If I give up then we just have all zeros and it's clearly wrong so that's a
// thing, pull the cached data instead

if (!$sensors) {
    // instead of "no data" pull the cached version
    $rgchPolledOrCached =
        '<span style="background-color: maroon; color: white; font-variant: small-caps; word-spacing: 10px; font-weight: bold;">&nbsp;cached&nbsp;</span>';
    $data = file_get_contents($fileWeatherDataCache);
    $data = mb_substr($data, strpos($data, '{'));
    $data = mb_substr($data, 0, -1);
    $sensors = json_decode($data, true);
    // if there's no cache file either, _then_ go "no data"
    if (!$sensors) {
        print("<span class='nodata'><br/><center>no data</center></span>");
        exit;
        }
    } else {
    // Write latest data polling to the cache file, assuming it exists
    if (json_encode($sensors)) {
        file_put_contents($fileWeatherDataCache, json_encode($sensors).PHP_EOL);
        }
    }

// print the header row
    print("<tr><td width=30% class='headingrow'><span class='headingtext'>Outdoors&nbsp;</span></td><td width=35% class='headingrow'><span class='headingtext'>Environment</span></td><td width=35% class='headingrow'><span class='headingtext'>Indoors</span></td></tr>");

// ROW 1 OF DATA
// row 1 of data 
print("<tr>");
    // outdoor summation data brick, all four rows of column 1.
    print("<td rowspan=4 class='locationcol'>");
    // need the inside average for display colour setting
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
    PrintOutdoorDataBrick(
        rgchSetFirstTempColour($Outside,$InsideAvg),
        $Outside,
        "<span style='font-size: 50px;'>" . $chOurUnitCaps . "</span>",
        $sensors['lastData']['humidity'],
        fpConvertTemp($sensors['lastData']['feelsLike']),
        fpConvertTemp($sensors['lastData']['dewPoint']),
        // composite outside average, north, west up, outside station
        fpConvertTemp(
           fpAverageValidity(
             $sensors['lastData']['temp3f'], $sensors['lastData']['humidity3'],
             $sensors['lastData']['temp4f'], $sensors['lastData']['humidity4'],
             $sensors['lastData']['tempf'], $sensors['lastData']['humidity'])
           ),
        fpConvertTemp($sensors['lastData']['temp1f']),
        $chOurUnitLowercase,
        $sensors['lastData']['pm25_in'], $sensors['lastData']['pm25']);
    print("</td>");

    // column 2, row 1, wind
    print('<td style="vertical-align: center;">');
    PrintWindBlock($sensors['lastData']['windspeedmph'], $sensors['lastData']['winddir'], $sensors['lastData']['maxdailygust']);
    print("</td>");

    // column 3, row 1, whole house
    print("<td>");
        print("<small>combined average</small><br/>");
        $InsideAvg=fpAverageValidity(
           $sensors['lastData']['temp5f'], $sensors['lastData']['humidity5'],
           $sensors['lastData']['temp6f'], $sensors['lastData']['humidity6'],
           $sensors['lastData']['temp7f'], $sensors['lastData']['humidity7'],
           $sensors['lastData']['temp8f'], $sensors['lastData']['humidity8'],
           $sensors['lastData']['tempinf'], $sensors['lastData']['humidityin']);
        $OutsideAvg=$sensors['lastData']['tempf'];
        $InsideHumidityAvg=fpAverageValidity(
           $sensors['lastData']['humidity5'], $sensors['lastData']['humidity5'],
           $sensors['lastData']['humidity6'], $sensors['lastData']['humidity6'],
           $sensors['lastData']['humidity7'], $sensors['lastData']['humidity7'],
           $sensors['lastData']['humidity8'], $sensors['lastData']['humidity8'],
           $sensors['lastData']['humidityin'],
             $sensors['lastData']['humidityin']
           );
        $InsideFeelslikeAvg=fpAverageValidity(
           $sensors['lastData']['feelsLike5'],
             $sensors['lastData']['humidity5'],
           $sensors['lastData']['feelsLike6'],
             $sensors['lastData']['humidity6'],
           $sensors['lastData']['feelsLike7'],
             $sensors['lastData']['humidity7'],
           $sensors['lastData']['feelsLike8'],
             $sensors['lastData']['humidity8'],
           $sensors['lastData']['feelsLikein'],
             $sensors['lastData']['humidityin']);
        PrintDataBrick(
            rgchSetFirstTempColour($InsideAvg,$OutsideAvg),
            fpConvertTemp($InsideAvg),
            $chOurUnitCaps,
            $InsideHumidityAvg,
            fpConvertTemp($InsideFeelslikeAvg),
            $chOurUnitLowercase);
    print("</td>");
print("</tr>");

// ROW 2 OF DATA
// row 2 of data
print("<tr>");
    // no column 1

    // column 2, row 2, rain
    print("<td>");
    PrintRainBlock($sensors['lastData']['hourlyrainin'],
                   $sensors['lastData']['dailyrainin'],
                   $sensors['lastData']['eventrainin'],
                   $sensors['lastData']['lastRain']);
    print("</td>");

    // column 2, row 2, top level inside averages
    print("<td>");
        print("<small>top level</small><br/>");
        $InsideAvg=fpAverageValidity(
           $sensors['lastData']['temp5f'], $sensors['lastData']['humidity5'],
           $sensors['lastData']['temp7f'], $sensors['lastData']['humidity7']);
        $OutsideAvg=fpAverageValidity(
           $sensors['lastData']['temp1f'], $sensors['lastData']['humidity1'],
           $sensors['lastData']['temp2f'], $sensors['lastData']['humidity2']);
        $InsideHumidityAvg=fpAverageValidity(
           $sensors['lastData']['humidity5'], $sensors['lastData']['humidity5'],
           $sensors['lastData']['humidity7'], $sensors['lastData']['humidity7']
           );
        $InsideFeelslikeAvg=fpAverageValidity(
           $sensors['lastData']['feelsLike5'],
             $sensors['lastData']['humidity5'],
           $sensors['lastData']['feelsLike7'],
             $sensors['lastData']['humidity7']);
        PrintDataBrick(
            rgchSetFirstTempColour($InsideAvg,$OutsideAvg),
            fpConvertTemp($InsideAvg),
            $chOurUnitCaps,
            $InsideHumidityAvg,
            fpConvertTemp($InsideFeelslikeAvg),
            $chOurUnitLowercase);
    print("</td>");
print("</tr>");

// ROW 3 OF DATA
// row 3 of data
print("<tr>");
    // no column 1

    // column 2, row 3, UV and solar radiation
    print("<td>");
    PrintSolarBlock($sensors['lastData']['uv'],
                    $sensors['lastData']['solarradiation']);
    print("</td>");

    // column 2, row 3, main level temperature
    print("<td>");
        print("<small>main level</small><br/>");
        $InsideAvg=fpAverageValidity(
           $sensors['lastData']['temp6f'], $sensors['lastData']['humidity6'],
           $sensors['lastData']['temp8f'], $sensors['lastData']['humidity8']);
        $OutsideAvg=fpAverageValidity(
           $sensors['lastData']['temp2f'], $sensors['lastData']['humidity2'],
           $sensors['lastData']['temp4f'], $sensors['lastData']['humidity4']);
        $InsideHumidityAvg=fpAverageValidity(
           $sensors['lastData']['humidity6'],
             $sensors['lastData']['humidity6'],
           $sensors['lastData']['humidity8'],
             $sensors['lastData']['humidity8']);
        $InsideFeelslikeAvg=fpAverageValidity(
           $sensors['lastData']['feelsLike6'],
             $sensors['lastData']['humidity6'],
           $sensors['lastData']['feelsLike8'],
             $sensors['lastData']['humidity8']);
        PrintDataBrick(
            rgchSetFirstTempColour($InsideAvg,$OutsideAvg),
            fpConvertTemp($InsideAvg),
            $chOurUnitCaps,
            $InsideHumidityAvg,
            fpConvertTemp($InsideFeelslikeAvg),
            $chOurUnitLowercase);
    print("</td>");
print("</tr>");

// ROW 4 OF DATA
// row 4 of data
print("<tr>");
    // no column 1

    // column 2, row 4, barometic pressure
    print("<td>");
    PrintBarometricBlock($sensors['lastData']['baromrelin']);
    //print($sensors['lastData']['baromrelin'] . " inHg<br/>(relative)");
    print("</td>");

    // column 3, row 4, ground level
    print("<td>");
    print("<small>ground level</small><br/>");
    PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['tempinf'],
            $sensors['lastData']['tempf']),
        fpConvertTemp($sensors['lastData']['tempinf']),
        $chOurUnitCaps,
        $sensors['lastData']['humidityin'],
        fpConvertTemp($sensors['lastData']['feelsLikein']),
        $chOurUnitLowercase);
    print("</td>");

print("</tr>");

print("</table>");

//
// print the data collection timestamp in friendly fashion
// and link to other unit page
//

// Divide dateutc by 1000 to obtain the actual timestamp
$ts = intval($sensors['lastData']['dateutc'] / 1000);

// Parse into a DateTime object
$datareaddate = DateTime::createFromFormat('U', $ts);

// Set time zone
$datareaddate->setTimezone(new DateTimeZone('America/Los_Angeles'));

if ($_SERVER['REMOTE_ADDR'] != $rgchOurIP) // disable if not LAN
    {
    $rgchModeLink=$rgchOperatingSeason;
    } else {
    $rgchModeLink="<a href='/tempSetStatus.php'>" .
        $rgchOperatingSeason . "</a>";
    }
    
// And print the time the data was polled formatted nicely.
printf("<br/><center>%s %s | <a href='temp%s.php'>Stations</a> | <a href='https://ambientweather.net/dashboard/c70a0f8a27d52e86df72303460554462'>Graphs</a> | <a href='%s'>Show in &deg;%s</a>, &deg;<a href='%s'>%s</a></center>",
    $rgchPolledOrCached,
    $datareaddate->format('g:ia, l, F jS'),
    $chOurUnitCaps,
    $chOtherUnitPage1,
    $chOtherUnit1,
    $chOtherUnitPage2,
    $chOtherUnit2);

?>
</body>
</html>
