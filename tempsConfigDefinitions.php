<?php

// this is the installation configuration file. hopefully you'll only need to edit this.

// This defines your Ambient Weather API key.
global $fileAmbientServer;
$lclAppKey = ""; // <------ Your application key here
$lclAPIKey = ""; // <------ Your API key here
//
$fileAmbientServer = "https://api.ambientweather.net/v1/devices?applicationKey=" .
    $lclAppKey . "&apiKey=" . $lclAPIKey;

// this is the path to your installation directory, including the directory itself
global $rgchInstallPath;
$rgchInstallPath = "//";  // <--------------- installation path goes here

// this is your working directory, including the directory itself. defaults to the same
// as the install path; override it if you like. data files go here.
$rgchWorkingPath = $rgchInstallPath;
//$rgchWorkingPath = "";                        // <--------------- Data files directory goes here

// derived values from InstallPath, WorkingPath
global $fileWeatherDataCache;
global $fileSystemStatusCache;
$fileWeatherDataCache = $rgchWorkingPath . "tempWeatherDataCache";
$fileSystemStatusCache = $rgchWorkingPath . "tempWindows";

// this is the number of paired stations you have, only tested with 4 lol
global $cNumberOfStations;
$cNumberOfStations = 4;        // <--------------- number of stations goes here

// this are the names of the stations you have, 1 through 4,
// along with whether their status is set manually or sensed
global $rgchStationNames;
$rgchStationNames = array(
    1 => "east", 'east' => "manual",             // <--------------- station 1
    2 => "west main", 'west main' => "sensed",   // <--------------- station 2
    3 => "west up", 'west up' => "manual",       // <--------------- station 3
    4 => "north", 'north' => "sensed"            // <--------------- station 4
    );

// default operating season at very first initial startup
$rgchOperatingSeason="summer";                   // <--------------- must be spring, summer, smoke, fall, winter, disable

// this is the IP address of your server
global $rgchOurIP;
$rgchOurIP = "192.168.1.1";                     // <--------------- IP goes here

// this is the IP address of your relay board for vent control, if you have one
global $rgchVentControllerIP;
$rgchVentControllerIP = "192.168.1.1";         // <--------------- IP goes here

?>
