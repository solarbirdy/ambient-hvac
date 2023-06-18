<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Configuring...</title>
    <link rel="STYLESHEET" type="text/css" href="/css/weatherstyle.css">
    <meta http-equiv="Refresh" content="2; url='/tempC.php'" />
<?php	// **** HOLY GODS THIS NEEDS WORK ****

// Include and invoke common definitions
include 'tempsCommonDefinitions.php';

// Now include common functions
include 'tempsCommonFunctions.php';

// And the heartbeat function
include 'tempVentHeartbeatFn.php';

// if we didn't get both parametres we're execting, bail
if ((!isset($_GET["window"])) || (!isset($_GET["status"]))) {
    exit;
    }

// if we didn't get a referrer, go home to tempC, if we did, go back to
// where we came
if (!isset($_SERVER['HTTP_REFERER'])) {
    $rgchRefreshTo="'/tempC.php'";
    } else {
    $rgchRefreshTo="'" . $_SERVER['HTTP_REFERER'] . "'";
    }
printf('<meta http-equiv="Refresh" content="2; url=%s" />',$rgchRefreshTo);
?>

</head>
<body topmargin="0" style="padding-top: 0px; margin: 0px;">
<table border=0 cellpadding=0 cellspacing=10px
    align="center" valign="top" cols="3"
    width="100%" height="100%">
<?php

// twice why?
if ((!isset($_GET["window"])) || (!isset($_GET["status"]))) {
    exit;
    }

$rgchWindowSet=$_GET["window"];
$rgchStatusSet=$_GET["status"];

// absolute sanitisation of input
// oh gods there are so many more status that I forgot - blue 2023/4/30
if ( ($rgchStatusSet != "open" ) &&
     ($rgchStatusSet != "closed") &&
     ($rgchStatusSet != "summer") &&
     ($rgchStatusSet != "winter") &&
     ($rgchStatusSet != "smoke") &&
     ($rgchStatusSet != "spring") &&
     ($rgchStatusSet != "fall") &&
     ($rgchStatusSet != "disable") )
    exit;

// what the hell is season - blue 2023/4/30
if ( ($rgchWindowSet != $rgchStationNames[1] ) &&
     ($rgchWindowSet != $rgchStationNames[2] ) &&
     ($rgchWindowSet != $rgchStationNames[3] ) &&
     ($rgchWindowSet != $rgchStationNames[4] ) &&
     ($rgchWindowSet != "season"))
    exit;

// Get current status of all windows.
$data = file_get_contents($fileSystemStatusCache);
$data = mb_substr($data, strpos($data, '{'));
$data = mb_substr($data, 0, -1);
$windows = json_decode($data, true);

printf('<p><center><span class="location">%s was marked <b>%s</b>, ',$rgchWindowSet,$windows[$rgchWindowSet]);

// change json data based on what we get as params

if ( ($rgchStatusSet!="NONE") && ($rgchWindowSet!="NONE" ) ) {
    $windows[$rgchWindowSet]=$rgchStatusSet;
    printf("now being marked <b>%s</b>.</span></center></p>",$windows[$rgchWindowSet]);
    }

$rgchOurOutput=json_encode($windows);

if ($rgchOurOutput != "null") {		// because fuck you apache
    // update mode status, windows status (where still applicable)
    file_put_contents($fileSystemStatusCache,json_encode($windows) . PHP_EOL);
    // now that that's done, trigger a refresh on the air exchange system - blue 2023/4/30
    Heartbeat();
    }

?>
</body>
</html>
