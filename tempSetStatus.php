<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Toggle Venting and Seasons</title>
    <link rel="STYLESHEET" type="text/css" href="css/weatherstyle.css">
<?php // If not on the LAN, bounce home.
// we need this in each block :/
include 'tempsCommonDefinitions.php';
if ($_SERVER['REMOTE_ADDR'] != $rgchOurIP) {
    print('    <meta http-equiv="refresh" content="0; URL=tempOverviewC.php">');
    }
?>

</head>
<body topmargin="0" style="padding-top: 0px; margin: 0px;">
<?php

// include common definitions - needed for our canonical okay IP address
include 'tempsCommonDefinitions.php';
// include common functions code - not needed but might be useful someday
include 'tempsCommonFunctions.php';

if ( $_SERVER['REMOTE_ADDR'] == $rgchOurIP ) { // on the lan, welcome to panel
    print '<span class="location">';
    print '<style> a:link { color: maroon; font-variant: small-caps; font-style: normal; text-decoration: none; font-size: smaller; } a:visited { color: maroon; font-variant: small-caps; font-style: normal; text-decoration: none; font-size: smaller; } </style>';
    print '<p><div style="line-height: 130%"><center>';
    print '<a href="tempWinToggle.php/?window=' . $rgchStationNames[1] . '&status=closed">close</a> &nbsp; ' . $rgchStationNames[1] . ' &nbsp; '; // east
    print '<a href="tempWinToggle.php/?window=' . $rgchStationNames[1] . '&status=open">open</a><br/>'; // east
    print '<a href="tempWinToggle.php/?window=' . $rgchStationNames[3] . '&status=closed">close</a> &nbsp; ' . $rgchStationNames[3] . ' &nbsp; '; // west up
    print '<a href="tempWinToggle.php/?window=' . $rgchStationNames[3] . '&status=open">open</a><br/>'; // west up
    print '<p>';
    print '<center>select mode<br/><a href="tempWinToggle.php/?window=season&status=summer">summer</a> &nbsp; ';
    print '<a href="tempWinToggle.php/?window=season&status=winter">winter</a> &nbsp; ';
    print '<a href="tempWinToggle.php/?window=season&status=smoke">smoke</a>';
    print '</p></center>';
    print '<p><center>';
    print '<a href="tempWinToggle.php/?window=season&status=disable">disable all modes</a></center>';
    print '</p>';
    print '<p style="margin-top: -14px;"><center>';
    print '<span style="font-size: medium">';
    print '<a style="font-variant-caps: normal; font-size: medium; color: #551A8B;" href="tempOverviewC.php">Overview</a> | ';
    print '<a style="font-variant-caps: normal; font-size: medium; color: #551A8B;" href="tempC.php">Stations</a> | ';
    print '<a style="font-variant-caps: normal; font-size: medium; color: #551A8B;" href="tempZones.php">Zones</a> | ';
    print '<a style="font-variant-caps: normal; font-size: medium; color: #551A8B;" href="https://ambientweather.net/dashboard/c70a0f8a27d52e86df72303460554462">Graphs</a>';
    print '</span></center>';
    print '</p>';
    print '</span>';
    }
?>
</body>
</html>
