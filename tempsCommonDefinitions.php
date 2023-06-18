<?php

// not all pages have a default (or any) unit, make sure one gets defined

if ( !isset($chOurUnitCaps) ) $chOurUnitCaps = "C";

// include configuration options
include 'tempsConfigDefinitions.php';

$chCelciusUnitCaps="C";
$chCelciusUnitLowercase="c";
$chFarenheitUnitCaps="F";
$chFarenheitUnitLowercase="f";
$chKelvinUnitCaps="K";
$chKelvinUnitLowercase="k";
$chRankineUnitCaps="R";
$chRankineUnitLowercase="r";

$chOurUnitLowercase = "BROKEN";
$chOtherUnit1 = "BROKEN";
$chOtherUnit2 = "BROKEN";

if ($chOurUnitCaps == $chCelciusUnitCaps) {
    $chOurUnitLowercase=$chCelciusUnitLowercase;
    $chOtherUnit1=$chFarenheitUnitCaps;
    $chOtherUnitPage1=$chFarenheitPage;
    $chOtherUnit2=$chKelvinUnitCaps;
    $chOtherUnitPage2=$chKelvinPage;
    }

if ($chOurUnitCaps == $chFarenheitUnitCaps) {
    $chOurUnitLowercase=$chFarenheitUnitLowercase;
    $chOtherUnit1=$chCelciusUnitCaps;
    $chOtherUnitPage1=$chCelciusPage;
    $chOtherUnit2=$chKelvinUnitCaps;
    $chOtherUnitPage2=$chKelvinPage;
    }

if ($chOurUnitCaps == $chKelvinUnitCaps) {
    $chOurUnitLowercase=$chKelvinUnitLowercase;
    $chOtherUnit1=$chCelciusUnitCaps;
    $chOtherUnitPage1=$chCelciusPage;
    $chOtherUnit2=$chFarenheitUnitCaps;
    $chOtherUnitPage2=$chFarenheitPage;
    }

if ($chOurUnitCaps == $chRankineUnitCaps) {
    $chOurUnitLowercase=$chRankineUnitLowercase;
    $chOtherUnit1=$chCelciusUnitCaps;
    $chOtherUnitPage1=$chCelciusPage;
    $chOtherUnit2=$chFarenheitUnitCaps;
    $chOtherUnitPage2=$chFarenheitPage;
    }

?>
