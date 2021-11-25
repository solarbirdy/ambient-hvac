<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Configuring...</title>
    <link rel="STYLESHEET" type="text/css" href="/css/weatherstyle.css">
    <meta http-equiv="Refresh" content="2; url='/tempC.php'" />
<?php

if ((!isset($_GET["window"])) || (!isset($_GET["status"]))) {
    exit;
    }

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

if ((!isset($_GET["window"])) || (!isset($_GET["status"]))) {
    exit;
    }

$rgchWindowSet=$_GET["window"];
$rgchStatusSet=$_GET["status"];

// absolute sanitisation of input
if ( ($rgchStatusSet != "open" ) &&
     ($rgchStatusSet != "closed") &&
     ($rgchStatusSet != "summer") &&
     ($rgchStatusSet != "winter") &&
     ($rgchStatusSet != "smoke") &&
     ($rgchStatusSet != "disable") )
    exit;

if ( ($rgchWindowSet != "east") &&
     ($rgchWindowSet != "west main") &&
     ($rgchWindowSet != "west up") &&
     ($rgchWindowSet != "north") &&
     ($rgchWindowSet != "season"))
    exit;

// Get current status of all windows.
$rgchWindowFile="tempWindows";
$data = file_get_contents($rgchWindowFile);
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

if ($rgchOurOutput != "null") {		// because apache
    file_put_contents($rgchWindowFile,json_encode($windows) . PHP_EOL);
    }

?>
</body>
</html>
