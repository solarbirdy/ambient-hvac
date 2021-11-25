<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Sensor Array</title>
    <link rel="STYLESHEET" type="text/css" href="css/weatherstyle.css">
    <meta http-equiv="refresh" content="120">
</head>
<body topmargin="0" style="padding-top: 0px; margin: 0px;">
<table border=0 cellpadding=0 cellspacing=10px
    align="center" valign="top" cols="3"
    width="100%" height="100%">
<?php

// SET DISPLAY UNIT BEFORE INCLUDING COMMON DEFINITIONS
// Everything is dependant upon this
//$chOurUnitCaps=substr(basename(__FILE__, '.php'),-1);
$chOurUnitCaps=substr(basename($_SERVER['REQUEST_URI'], '.php'),-1);
$rgchOverviewPage="tempOverview" . $chOurUnitCaps. ".php";

// page-specific definitions affect common definitions
$chCelciusPage="tempC.php";
$chFarenheitPage="tempF.php";
$chKelvinPage="tempK.php";
$chRankinePage="tempC.php";     // THIS IS INTENTIONAL

// include standard definitions
include 'tempsCommonDefinitions.php';

// include common functions
include 'tempsCommonFunctions.php';

// If unit setting somehow gets fucked up, default to Rankine so people know
// it's broken because honestly, who uses R? Nobody. And no one should.
if ( ($chOurUnitCaps != $chCelciusUnitCaps) &&
     ($chOurUnitCaps != $chFarenheitUnitCaps) &&
     ($chOurUnitCaps != $chKelvinUnitCaps) )
    $chOurUnitCaps=$chRankineUnitCaps;

//
// function to print location, check window status, and return either
// status or needed change
//
function DisplayLocationStatus($rgchLocation, $fpTemp1, $fpTemp2) {

    global $rgchOperatingSeason;

    // Get current logical status of windows. May not match physical.
    $rgchWindowFile="tempWindows";
    $data = file_get_contents($rgchWindowFile);
    $data = mb_substr($data, strpos($data, '{'));
    $data = mb_substr($data, 0, -1);
    $windows = json_decode($data, true);

    // set our current window's status according to the logical data.
    $rgchWindowStatus=$windows[$rgchLocation];

    // Get current PHYSICAL status of windows. May not match logical!
    // only "north" and "west main" have physical sensors as of
    // 2021/8/18 so do this only if it's one of those
    if (($rgchLocation == "north") || ($rgchLocation == "west main")) {
        $rgchRealWindowsJ=rgchAcquireRealWindowStatus();
        if ($rgchRealWindowsJ != NULL) {
            $rgchRealWindows=json_decode($rgchRealWindowsJ, true);
            if ($rgchLocation == "north") {
                // north is laundry, or $rgchRealWindows["digitalpin2"]
                // "0" means open, "1" means closed.
                if ( $rgchRealWindows["digitalpin2"] == "0" ) {
                    $windows[$rgchLocation] = "open";
                    } else {
                    $windows[$rgchLocation] = "closed";
                    }
                } else {
                // west main, which means kitchen sink window, north
                // and south breakfast nook windows, digital pins 5, 6, 7.
                // If any are open, count the location as open.
                if ( ( $rgchRealWindows["digitalpin5"] == "0" ) ||
                     ( $rgchRealWindows["digitalpin6"] == "0" ) ||
                     ( $rgchRealWindows["digitalpin7"] == "0" ) )
                    {
                    $windows[$rgchLocation] = "open";
                    } else {
                    $windows[$rgchLocation] = "closed";
                    }
                }
            } // This is if we didn't get a serial read. No else needed.
              // Just fall back to old behaviour for this run.
        }

    // set our current window's status
    $rgchWindowStatus=$windows[$rgchLocation];

    // default "should be" to closed.
    $rgchShouldBe="closed";

    $rgchOperatingSeason=$windows["season"];
    // if difference is less than .5C, whatever we have now is fine.
    // "should be" is whatever is currently assigned.

    if ( abs($fpTemp1-$fpTemp2) <= 0.89 ) {
        $rgchShouldBe=$rgchWindowStatus;
        } else {
        // if it's more than .5C apart, set "should be" appropriately.
        // for winter and summer, test for warmer and cooler respectively
        if ($rgchOperatingSeason == "winter" ) { // in winter, warmer out
            if ($fpTemp1 > $fpTemp2) $rgchShouldBe="open";
            } else {                           // in summer, cooler out
            if ($fpTemp1 < $fpTemp2) $rgchShouldBe="open";
            }
        }

    // overrides in general - browser off LAN, DISABLE mode, or one or
    // both of our temperatures is 0F. That really shouldn't happen here
    // and means we didn't get data. But...
    // -- THIS IS A HACK --
    // ...this is the wrong way to do it - we should be checking for a
    // humidity of zero, but we don't have those numbers with us, so can't.
    if ( ($rgchOperatingSeason=="disable") ||		  // disabled
         ($_SERVER['REMOTE_ADDR'] != "173.160.243.41") || // disable off LAN
         (($fpTemp1 == 0) || ($fpTemp2 == 0)) )           // invalid temp(s)
        $rgchShouldBe=$rgchWindowStatus;
    if ($rgchOperatingSeason=="smoke") $rgchShouldBe="closed";

    $rgchLocationStyle="locationOK";
    $rgchActionStyle="locationactionOK";
    if ($rgchWindowStatus != $rgchShouldBe ) {
        $rgchLocationStyle="locationBAD";
        $rgchActionStyle="locationactionBAD";
        if ($rgchShouldBe=="open")		// closed, should be open
            {
            // that's more complicated now. If it's not a physically known
            // window, include a link. If it _is_ a physically known window,
            // do NOT include the link.
            if (($rgchLocation != "north") && ($rgchLocation != "west main"))
                {
                $rgchWindowStatus="closed, better " .
                    "<a href='tempWinToggle.php/?window=" .
                    $rgchLocation . "&status=open'>open</a>";
                } else {
                $rgchWindowStatus="closed, better open";
                }
            }
            else
            {
            // that's more complicated now. If it's not a physically known
            // window, include a link. If it _is_ a physically known window,
            // do NOT include the link.
            if (($rgchLocation != "north") && ($rgchLocation != "west main"))
                { 
                $rgchWindowStatus=
                    "open, better <a href='tempWinToggle.php/?window=" .
                    $rgchLocation . "&status=closed'>closed</a>";
                } else {
                $rgchWindowStatus="open, better closed";
                }
            }
        }

    // Print location (always), then possible action to take,
    // based on open/closed status of windows.
    printf("<span class='%s'>%s</span>",$rgchLocationStyle,$rgchLocation);
    if ( $rgchWindowStatus != $rgchShouldBe) printf("<span class='%s'><br/>%s</span>",$rgchActionStyle,$rgchWindowStatus);

    return;
}

//
// main MAIN
//
// Sometimes the server doesn't respond correctly, I test for that against
// humidity of zero. Since they throttle access to the server with free API
// keys, I pause half of a second whenever that happens before trying again.
// After 3 tries, I give up.

$file = "https://api.ambientweather.net/v1/devices?applicationKey=__YOUR_APPLICATION_KEY__apiKey=__YOUR_API_KEY__";
$fileWeatherDataCache = "tempWeatherDataCache";
$rgchPolledOrCached = "Polled";
$dataTries=0;

do {
    $dataTries++;
    $data = file_get_contents($file);
    $data = mb_substr($data, strpos($data, '{'));
    $data = mb_substr($data, 0, -1);
    $sensors = json_decode($data, true);
    if ($sensors['lastData']['humidity1'] == 0) usleep(500000);
    } while (($sensors['lastData']['humidity1'] == 0) && ($dataTries < 3));

// If I give up then we just have all zeros and it's clearly wrong so
// pull the cache

if (!$sensors) {
    // instead of "no data" pull the cached version
    $data = file_get_contents($fileWeatherDataCache);
    $data = mb_substr($data, strpos($data, '{'));
    $data = mb_substr($data, 0, -1);
    $sensors = json_decode($data, true);
    $rgchPolledOrCached =
        '<span style="background-color: maroon; color: white; font-variant: small-caps; word-spacing: 10px; font-weight: bold;">&nbsp;cached&nbsp;</span>';
    // if there's no cache file either, then go "no data"
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
    print("<tr><td width=30% class='headingrow'><span class='headingtext'>Station</span></td><td width=35% class='headingrow'><span class='headingtext'>Outdoor</span></td><td width=35% class='headingrow'><span class='headingtext'>Indoor</span></td></tr>");

// four rows of temperatures
print("<tr>");
    print("<td class='locationcol'>");
    DisplayLocationStatus("east",
        $sensors['lastData']['temp1f'],
        $sensors['lastData']['temp5f']);
    print("</td>");
    print("<td>");
    PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp1f'],
            $sensors['lastData']['temp5f']),
        fpConvertTemp($sensors['lastData']['temp1f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity1'],
        fpConvertTemp($sensors['lastData']['feelsLike1']),
        $chOurUnitLowercase);
    print("</td>");
    print("<td>");
        PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp5f'],
            $sensors['lastData']['temp1f']),
        fpConvertTemp($sensors['lastData']['temp5f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity5'],
        fpConvertTemp($sensors['lastData']['feelsLike5']),
        $chOurUnitLowercase);
    print("</td>");
print("</tr>");

print("<tr>");
    print("<td class='locationcol'>");
    DisplayLocationStatus("west main",
        $sensors['lastData']['temp2f'],
        $sensors['lastData']['temp6f']);
    print("</td>");
    print("</td>");
    print("<td>");
        PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp2f'],
            $sensors['lastData']['temp6f']),
        fpConvertTemp($sensors['lastData']['temp2f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity2'],
        fpConvertTemp($sensors['lastData']['feelsLike2']),
        $chOurUnitLowercase);
    print("</td>");
    print("<td>");
    PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp6f'],
            $sensors['lastData']['temp2f']),
        fpConvertTemp($sensors['lastData']['temp6f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity6'],
        fpConvertTemp($sensors['lastData']['feelsLike6']),
        $chOurUnitLowercase);
    print("</td>");
print("</tr>");

print("<tr>");
    print("<td class='locationcol'>");
    DisplayLocationStatus("west up",
        $sensors['lastData']['temp3f'],
        $sensors['lastData']['temp7f']);
    print("</td>");
    print("</td>");
    print("<td>");
    PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp3f'],
            $sensors['lastData']['temp7f']),
        fpConvertTemp($sensors['lastData']['temp3f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity3'],
        fpConvertTemp($sensors['lastData']['feelsLike3']),
        $chOurUnitLowercase);
    print("</td>");
    print("<td>");
    PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp7f'],
            $sensors['lastData']['temp3f']),
        fpConvertTemp($sensors['lastData']['temp7f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity7'],
        fpConvertTemp($sensors['lastData']['feelsLike7']),
        $chOurUnitLowercase);
    print("</td>");
print("</tr>");

print("<tr>");
    print("<td class='locationcol'>");
    DisplayLocationStatus("north",
        $sensors['lastData']['temp4f'],
        $sensors['lastData']['temp8f']);
    print("</td>");
    print("</td>");
    print("<td>");
    PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp4f'],
            $sensors['lastData']['temp8f']),
        fpConvertTemp($sensors['lastData']['temp4f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity4'],
        fpConvertTemp($sensors['lastData']['feelsLike4']),
        $chOurUnitLowercase);
    print("</td>");
    print("<td>");
    PrintDataBrick(
        rgchSetFirstTempColour(
            $sensors['lastData']['temp8f'],
            $sensors['lastData']['temp4f']),
        fpConvertTemp($sensors['lastData']['temp8f']),
        $chOurUnitCaps,
        $sensors['lastData']['humidity8'],
        fpConvertTemp($sensors['lastData']['feelsLike8']),
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

if ($_SERVER['REMOTE_ADDR'] != "173.160.243.41") // disable if not LAN
    {
    $rgchModeLink=$rgchOperatingSeason;
    } else {
    $rgchModeLink="<a href='/tempSetStatus.php'>" .
        $rgchOperatingSeason . "</a>";
    }
    
// And print the time the data was polled formatted nicely.
printf("<center><br/>%s %s (%s mode)<br/>",
    $rgchPolledOrCached,
    $datareaddate->format('g:ia, l, F jS'),
    $rgchModeLink);
printf("<a href='%s'>Overview</a> | ", $rgchOverviewPage);
if ($_SERVER['REMOTE_ADDR'] == "173.160.243.41") {
    // pass our unit so if they go to overview from zones they keep their unit
    printf('<a href="tempZones.php?unit=%s">Zones</a> | ',$chOurUnitCaps);
    }
printf("<a href='https://ambientweather.net/dashboard/c70a0f8a27d52e86df72303460554462'>Graphs</a> | <a href='%s'>Show in &deg;%s</a>, &deg;<a href='%s'>%s</a></center>",
    $chOtherUnitPage1,
    $chOtherUnit1,
    $chOtherUnitPage2,
    $chOtherUnit2);

?>
</body>
</html>
