# ambient-hvac
Uses internet-connectable temperature sensors to provide cooling/heating assist for houses and other small buildings, as well as weather data display. Uses arduino to control status of air exchange vent based on air quality conditions, temperature, season, and preferences..

OVERVIEW

This system uses Ambient Weather and Arduino Leonardo hardware and software support to implement air-exchange cooling and heating assist, though it is mostly effective for cooling. Mostly written in PHP, it serves information display panels over the web, supporting metric, kelvin, and freedom weather units.

The basic HVAC functionality lives on the "Stations" display. Using pairs of temperature sensors, this display monitors temperatures inside and outside near air-exchange opportunities, such as windows or possibly vents, and display appropriate actions the user should take at any given time for optimal cooling, or, in theory, heating. In my case, this mostly consists of opening and closing windows and turning on and off the air-exchange vent on our primary HVAC system, but it is not in any way limited to that.

It has support for "winter," "summer," and "smoke" modes, along with a "disable" mode. These affect the recommendations the software makes. In the latter case, turning them off entirely.

In essense, this is smart window management. But at least in my case, the differences between actual temperatures and expected temperatures were between surprising and shocking, and - combined with other efforts - enough to dramatically reduce our cooling bills while improving comfort, despite a record hot summer.

The weather station display functionality lives on the "Overview" page, and is mostly self-explanitory. It allows access to your system's data from anywhere, even from extremely simple/primitive browsers, such as an original iPhone or iPad Mini. We are now using two obsolete tablets as system display/control panels. This is in contrast to the official ambientweather website, which cannot be viewed on these antique browsers at all.

The physical window/door sensor status display lives on the "Zones" page, and is similarly self-explanitory, and will work on similarly primative browsers. I will make obsolete hardware useful again by creating applications that support them.

Modes and windows/vents which cannot be automatically sensed can be changed on the "Modes" page, and in some cases, on the "Stations" page.

CURRENT STATE

Version 0.6 Alpha. This should not be seen as a simple package to install; it's not, at least not yet. It should be seen as base code which can be used to implement a similar solution specific to your location. I don't even consider this feature-complete, though all implemented functionality works as it should and most of it (everything except data cacheing) has some months of testing.

Version 0.6 adds a configuration file that actually works (on my machine lol), a substantial refactoring and consolidation of the code base (including a lot, and I do mean a lot, of redundant code elimination), the ability to name your air sensor pairs, and fixes a bunch of bugs. Basically, this is the long overdue "next drop" I promised early in the plague.

SYSTEM REQUIREMENTS

To use all functionality, you will need:

* Ambient Weather IPOBSERVER internet connectivity relay, to send data to the ambientweather servers
* Your own application and API keys from ambientweather to fetch your data back from the servers in JSON format; these are free
* Up to eight WS31E indoor/outdoor temperature humidity sensors (or any similar IPOBSERVER-compatible unit)
* Any IPOBSERVER-compatible weather station (I use the WS-2902-ARRAY)
* One additional "indoor" temperature/humidity sensor, paried with the weather station (I use model WH32B)
* Any web host supporting a reasonably recent version of PHP
* Ambient Weather 2.5nm particle sensors, one inside, one outside

To use window/vent status detection, you will need:

* One Arduino Leonardo, connected to appropriate magnetic switch sensors on its digital lines
* Physical access to your web server's serial port, for communication with the Arduino

To use active control over whatever air intake/exchange system you have, you will need:

* LinkSprite LinkNode R4 ESP-12f ESP8266 WiFi Relay Controller IoT Module or similar (cloud services not required or even used)

INSTALLATION

Copy all the .php files into an accessible directory on your webserver with typical permissions. Two additional files - tempWeatherDataCache and tempWindows - must be _writeable_ by the web server itself, and in the same directory. Put the css file into a subdirectory called "css" (or you could not, and edit the code to look for it in the same directory as everything else, that's okay too). The .ino file is for the arduino leonardo, so it can report physical vent/window status.

As of Version 0.6, there is a configuration file which dramatically reduces the need to edit the primary codebase. This file is tempsConfigDefinitions.php and is internally documented. While it is still not at any sort of "edit the config file and go" state, it is _dramatically closer to that_ in current implementation.

If you want to have automatic window/vent status sensing, you will also need to acquire and connect an Arduino Leonardo-compatable board, load the (fortunately very simple) code to drive it onto the board, then connect it to your window/door/vent sensors via the digital sense pins and also to a USB port on your webserver, where it will act as a serial device.

If you want to have active control over air intake/exchange, you'll need the LinkNode R4 mentioned above, and the firmware softer to give you direct (non cloud-involved) control over its relays via simple GET commands.

Okay that got kinda ugly. But you can skip that part and set vent/window status manually, through the web UI, with single clicks. Also, no separate arduino-sensing software has to live on the web server, it's all built into the core codebase.

CONFIGURATION

As of Version 0.6, this is still ugly, but less so. But if you're not comfortable editing source code, you probably shouldn't do this.

You'll need to edit tempsConfigDefinitions.php to define your own application and API keys. You can also define your sensor pair location names, your installation directory, your cache directory, the IP of your host server and that of the wireless board controlling your air exchange unit, whatever it might be. There's more, too, and it's all in there.

SUPPORT

I'll be surprised if anyone needs or wants any, but hey, you never know, right? That's why I'm putting it up. It made a big difference for us last summer. It won't help everywhere - like, places where you need cooling literally 24x7 - but if you're somewhere with air-exchange cooling possibilities, it'll work a treat.
