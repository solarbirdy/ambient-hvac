<?php
// This is a wrapper for the basic vent status updater, to be called by cron
// every five minutes or so

// not actually relevant, need to be defined though, because everythign that includes
// core definitions needs these also around because of derived values.
$chOurUnitCaps = 'C';
$chCelciusPage = "tempVentCronHeartbeat.php";
$chFarenheitPage = "tempVentCronHeartbeat.php";
$chKelvinPage = "tempVentCronHeartbeat.php";

// include common definitions, we'll need these throughout
include 'tempsCommonDefinitions.php';
// include common functions code, we'll need these throughout
include 'tempsCommonFunctions.php';
// include the actual heartbeat function (for now separate, will end up in CommonFunctions I think)
include 'tempVentHeartbeatFn.php';
// call the function
echo "Calling vent heartbeat, was " . rgchAcquireVentStatus(0) . " ";
Heartbeat();
echo "vent " . rgchAcquireVentStatus(0) . ".";
?>
