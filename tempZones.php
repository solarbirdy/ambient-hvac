<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Entry Status Array</title>
    <link rel="STYLESHEET" type="text/css" href="css/weatherstyle.css">
    <meta http-equiv="refresh" content="119">
<?php
// include common definitions in this PHP block too
include 'tempsCommonDefinitions.php';

if ($_SERVER['REMOTE_ADDR'] != $rgchOurIP) {
    print('    <meta http-equiv="refresh" content="0; URL=tempOverviewC.php">');
    } else {
    print('    <meta http-equiv="refresh" content="59">');
    }
?>
</head>
<body topmargin="0" style="padding-top: 0px; margin: 0px; color: #303030;">
<?php

// GLOBAL INITIALISATIONS
//
$rgchAllPossibleCapsUnits = "CFKR"; // all possible valid caps units

// default to C - this isn't for display on this page, but for which
// version of Overview should be hit on return
$chOurUnitCaps='C';

// if we get passed a legitimate unit, use that instead
if (isset($_GET["unit"])) {
    if (strpos($rgchAllPossibleCapsUnits, $_GET["unit"]))
        $chOurUnitCaps=$_GET["unit"];
    }

// this now works in all cases
$rgchOverviewPage="tempOverview" . $chOurUnitCaps . ".php";
$rgchStationsPage="temp" . $chOurUnitCaps . ".php";

// include common definitions
include 'tempsCommonDefinitions.php';
// include common functions code
include 'tempsCommonFunctions.php';

// for reduced code insanity
$rgchPortalOpen="<span class=portalOpen><span style='font-size: 17px; font-weight: bolder;'>open</span></span>";
$rgchPortalClosed="<span class=portalClosed><span style='font-size: 18px;'>closed</span></span>";
$rgchPortalStyle="<span class=portalLocation>%s</span>";
$rgchLogicalPortalOpen="<span class=portalOpen><span style='font-size: 20px; font-weight: bolder;'>open</span></span>";
$rgchLogicalPortalClosed="<span class=portalClosed><span style='font-size: 19px;'>closed</span></span>";

$rgchAllWindowsOpen="<span class=portalOpen>all windows open</span>";
$rgchAllWindowsClosed="<span class=portalClosed>all windows closed</span>";
$rgchSomeWindowsOpen="<span class=portalOpen><span style='font-size: 17px; font-weight: bolder'>some windows open</span></span>";

$rgchSomeDoorsOpen="<span class=portalOpen>some doors open</span>";
$rgchAllDoorsClosed="<span class=portalClosed>all doors closed</span>";

// main MAIN
//
// Primary code yep

// If we're not on the LAN, bail out. We'll refresh anyway.

if ($_SERVER['REMOTE_ADDR'] != $rgchOurIP) {
    exit;
    }

//
// I liked this but it doesn't work if you issue a change command to a
// logical zone :(
//
// If we don't have a referrer, save it; otherwise link back to stations page
// if we have that instead somehow. (Should only happen with bookmarks.)
//if (!isset($_SERVER['HTTP_REFERER'])) {
//    $rgchLinkbackTo="'" . $rgchStationsPage . "'";
//    } else {
//    $rgchLinkbackTo="'" . $_SERVER['HTTP_REFERER'] . "'";
//    }

// Get physical data
$rgchRealWindowsJ=rgchAcquireRealWindowStatus();

// If we don't get any physical data, bail
if ($rgchRealWindowsJ == NULL) {
    printf("<div class='nodata'><br/>no data</div>");
    exit;
    }
// We're good, decode it.
$rgchRealWindows=json_decode($rgchRealWindowsJ, true);

// Get virtual data for zones north and west up
$data = file_get_contents($fileSystemStatusCache);
$data = mb_substr($data, strpos($data, '{'));
$data = mb_substr($data, 0, -1);
// And decode it
$rgchVirtualWindows = json_decode($data, true);

// START TABLE START BEGIN TABLE BEGIN
print("&nbsp;<br/>");
print('<table border=0 cellpadding=10px cellspacing=10px align="center" valign="top" cols="2" rows="4" width="90%" height="100%">');

// ROW 1, COLUMN 1
print("<tr><td style='vertical-align: top'>");

// FIRST ZONE (east in dev's install)
print("<div class='locationShorter'>" . $rgchStationNames[1] . "</div><br/>");
if ($rgchVirtualWindows[$rgchStationNames[1]]=="open") {
    printf('%s<br/><a href="tempWinToggle.php/?window=%s&status=closed"><span class=portalLocation>mark as closed</span></a></div>', $rgchLogicalPortalOpen, $rgchStationNames[1] );
    } else {
    printf('%s<br/><a href="tempWinToggle.php/?window=%s&status=open"><span class=portalLocation>mark as open</span></a>', $rgchLogicalPortalClosed, $rgchStationNames[1] );
    }

// ROW 1, COLUMN 2
print("</td><td style='vertical-align: top'>");

// MEDIA ROOM ZONE
print("<div class='locationShorter'>media</div><br/>");

if ($rgchRealWindows["digitalpin3"] == 1)
    { // both either open or closed - currently means just one, second broken
      // and that's a hardware issue IRL, needs fixed 2021/8/21.
    print($rgchAllWindowsClosed);
    } else {
    print($rgchAllWindowsOpen);
    }

print("</td>");
// END ROW 1

print("</tr><tr>");

// ROW 2, COLUMN 1
print("<td>");
//print("<td style='vertical-align: top'>");

// WEST MAIN ZONE
print("<div class='locationShorter'>" . $rgchStationNames[2] . "</div><br/>");

switch ($rgchRealWindows["digitalpin5"]+
        $rgchRealWindows["digitalpin6"]+
        $rgchRealWindows["digitalpin7"]) {
    case 0:
        print($rgchAllWindowsOpen);
        break;
    case 3:
        print($rgchAllWindowsClosed);
        break;
    default:
        // mixed window situation
        printf($rgchPortalStyle, "kitchen: ");
        if ($rgchRealWindows["digitalpin5"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
        printf($rgchPortalStyle, "dining nook, north: ");
        if ($rgchRealWindows["digitalpin6"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
        printf($rgchPortalStyle, "dining nook, south: ");
        if ($rgchRealWindows["digitalpin7"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
        break;
    }

// ROW 2, COLUMN 2
//print("</td><td style='vertical-align: top'>");
print("</td><td>");

// LIVING ROOM ZONE
print("<div class='locationShorter'>living</div><br/>");

switch ($rgchRealWindows["digitalpin8"]+
        $rgchRealWindows["digitalpin9"]+
        $rgchRealWindows["digitalpin10"]+
        $rgchRealWindows["digitalpin11"]) {
    case 0:
        print($rgchAllWindowsOpen);
        break;
    case 4:
        print($rgchAllWindowsClosed);
        break;
    default:
        printf($rgchPortalStyle, "rear, north: ");
        if ($rgchRealWindows["digitalpin8"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
        printf($rgchPortalStyle, "rear, south: ");
        if ($rgchRealWindows["digitalpin9"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
        printf($rgchPortalStyle, "fireplace, west: ");
        if ($rgchRealWindows["digitalpin10"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
        printf($rgchPortalStyle, "fireplace, east: ");
        if ($rgchRealWindows["digitalpin11"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
        break;
    }

print("</td></tr>");
// END ROW 2

// ROW 3, COLUMN 1
print("<tr><td style='vertical-align: top'>");
//print("<tr><td>");

// WEST UP ZONE
print("<div class='locationShorter'>" . $rgchStationNames[3] . "</div><br/>");
if ($rgchVirtualWindows["west up"]=="open") {
    printf('%s<br/><a href="tempWinToggle.php/?window=west up&status=closed"><span class=portalLocation>mark as closed</span></a></div>', $rgchLogicalPortalOpen);
    } else {
    printf('%s<br/><a href="tempWinToggle.php/?window=west up&status=open"><span class=portalLocation>mark as open</span></a>', $rgchLogicalPortalClosed);
    }

// ROW 3, COLUMN 2
print("</td><td style='vertical-align: top'>");
//print("</td><td>");

// GROUND ZONE
print("<div class='locationShorter'>ground</div><br/>");

if ($rgchRealWindows["digitalpin12"] == $rgchRealWindows["digitalpin13"])
    { // both either open or closed
    if ($rgchRealWindows["digitalpin12"] == 1) {
       print($rgchAllWindowsClosed);
       } else {
       print($rgchAllWindowsOpen);
       }
    }
    else // mixed window situation
    { 
    printf($rgchPortalStyle, "south: ");
    if ($rgchRealWindows["digitalpin12"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
    printf($rgchPortalStyle, "north: ");
    if ($rgchRealWindows["digitalpin13"] == 0) {
            printf("%s<br/>",$rgchPortalOpen);
            } else {
            printf("%s<br/>",$rgchPortalClosed); }
    }

// END ROW 3
print("</td></tr>");

// ROW 4, COLUMN 1
//print("<tr><td style='vertical-align: top'>");
print("<tr><td>");

// NORTH ZONE
print("<div class='locationShorter'>" . $rgchStationNames[4] . "</div><br/>");

if ($rgchRealWindows["digitalpin2"] == 0) {
   print($rgchAllWindowsOpen); } else { print($rgchAllWindowsClosed); }

// ROW 4, COLUMN 2
//print("</td><td style='vertical-align: top'>");
print("</td><td>");

print("<div class='locationShorter'>summary</div><br/>");

$iAllWindows=$rgchRealWindows["digitalpin2"]+
    $rgchRealWindows["digitalpin3"]+
    $rgchRealWindows["digitalpin5"]+
    $rgchRealWindows["digitalpin6"]+
    $rgchRealWindows["digitalpin7"]+
    $rgchRealWindows["digitalpin8"]+
    $rgchRealWindows["digitalpin9"]+
    $rgchRealWindows["digitalpin10"]+
    $rgchRealWindows["digitalpin11"]+
    $rgchRealWindows["digitalpin12"];

if ($rgchVirtualWindows["west up"]=="closed") $iAllWindows++;
if ($rgchVirtualWindows[ $rgchStationNames[1] ]=="closed") $iAllWindows++;

if (($iAllWindows == 12) && ($rgchRealWindows["digitalpin4"] == 1))
    {
    printf("<div class='shuttered'>shuttered</div>%s",$rgchAllDoorsClosed);
    } else {
    // windows
    switch ($iAllWindows) {
        case 0:
        printf("%s<br/>",$rgchAllWindowsOpen);
        break;
        case 12:
        printf("%s<br/>",$rgchAllWindowsClosed);
        break;
        default:
        printf("%s<br/>",$rgchSomeWindowsOpen);
        }
    // doors
    if ($rgchRealWindows["digitalpin4"] == 1) {
        print($rgchAllDoorsClosed);
        } else {
        print($rgchSomeDoorsOpen);
        }
    }

// END ROW 4
print("</td></tr>");

// ROW 5, COLUMNS 1 & 2
print("<tr><td colspan=2>");

// And print our time/date and links
date_default_timezone_set('America/Los_Angeles');
printf("<center>Polled %s, %s | ",date('g:ia', time()), date('l, F jS', time()));
printf("<a href='%s'>Overview</a> | <a href=%s>Stations</a> | <a href='tempSetStatus.php'>Modes</a> | <a href='https://ambientweather.net/dashboard/c70a0f8a27d52e86df72303460554462'>Graphs</a>&nbsp;</center>",
    $rgchOverviewPage,
    $rgchStationsPage);

// END OF TABLE
print("</td></tr></table>");

?>
</body>
</html>
