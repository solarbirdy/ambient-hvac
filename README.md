# ambient-hvac
Uses internet-connectable temperature sensors to provide cooling/heating assist for houses and other small buildings, as well as weather data display.

OVERVIEW

This system uses Ambient Weather and Arduino Leonardo hardware and software support to implement air-exchange cooling and heating assist, though it is mostly effective for cooling. Mostly written in PHP, it serves information display panels over the web, supporting metric, kelvin, and freedom weather units.

The basic HVAC functionality lives on the "Stations" display. Using pairs of temperature sensors, this display monitors temperatures inside and outside near air-exchange opportunities, such as windows or possibly vents, and display appropriate actions the user should take at any given time for optimal cooling, or, in theory, heating. In my case, this mostly consists of opening and closing windows and turning on and off the air-exchange vent on our primary HVAC system, but it is not in any way limited to that.

It has support for "winter," "summer," and "smoke" modes, along with a "disable" mode. These affect the recommendations the software makes. In the latter case, turning them off entirely.

In essense, this is smart window management. But at least in my case, the differences between actual temperatures and expected temperatures were between surprising and shocking, and - combined with other efforts - enough to dramatically reduce our cooling bills while improving comfort, despite a record hot summer.

The weather station display functionality lives on the "Overview" page, and is mostly self-explanitory. It allows access to your system's data from anywhere, even from extremely simple/primitive browsers, such as an original iPhone or iPad Mini. We are now using two obsolete tablets as system display/control panels. This is in contrast to the official ambientweather website, which cannot be viewed on these antique browsers at all.

The physical window/door sensor status display lives on the "Zones" page, and is similarly self-explanitory, and will work on similarly primative browsers. I will make obsolete hardware useful again by creating applications that support them.

Modes and windows/vents which cannot be automatically sensed can be changed on the "Modes" page, and in some cases, on the "Stations" page.

SYSTEM REQUIREMENTS

To use all functionality, you will need:

* Ambient Weather IPOBSERVER internet connectivity relay, to send data to the ambientweather servers
* Your own application and API keys from ambientweather to fetch your data back from the servers in JSON format; these are free
* Up to eight WS31E indoor/outdoor temperature humidity sensors (or any similar IPOBSERVER-compatible unit)
* Any IPOBSERVER-compatible weather station (I use the WS-2902-ARRAY)
* One additional "indoor" temperature/humidity sensor, paried with the weather station (I use model WH32B)
* Any web host supporting a reasonably recent version of PHP

To use window/vent status detection, you will need:

* One Arduino Leonardo, connected to appropriate magnetic switch sensors on its digital lines
* Physical access to your web server's serial port, for communication with the Arduino

Eventually I hope to add at least some degree of vent automation, which will add a requirement for some sort of Raspberry Pi devices with switching capability.

INSTALLATION

It's not that ugly, but it's not exactly a script either.

Copy all the .php files into an accessible directory on your webserver with typical permissions. Two additional files - tempWeatherDataCache and tempWindows - must be _writeable_ by the web server itself, and in the same directory.

That's not so bad, right?

If you want to have automatic window/vent status sensing, you will also need to acquire and connect an Arduino Leonardo-compatable board, load the (fortunately very simple) code to drive it onto the board, then connect it to your window/door/vent sensors via the digital sense pins and also to a USB port on your webserver, where it will act as a serial device.

Okay that got kinda ugly. But you can skip that part and set vent/window status manually, through the web UI, with single clicks. Also, no separate arduino-sensing software has to live on the web server, it's all built into the core codebase.

CONFIGURATION

This... yeah, this is the very ugly part.

For Version 0.3, if you're not comfortable editing source code, you probably shouldn't do this.

You'll need to edit the PHP code to define your own application and API keys. You'll then need to define your own sensor pair locations _also_ by editing source code. I hope to fix that soon, sorry. Finally, the Overview page assumes a three-level building, sorting sensors appropriately to _my_ house. At the moment, that's also only editable by editing actual code. Sorry, and again, I hope to get to that soon.

SUPPORT

I'll be surprised if anyone needs or wants any, but hey, you never know, right? That's why I'm putting it up. It made a big difference for us last summer. It won't help everywhere - like, places where you need cooling literally 24x7 - but if you're somewhere with air-exchange cooling possibilities, it'll work a treat.
