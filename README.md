# mpd_snap_alarm
v1.0.1

  1. [About](#about)
  2. [Requirements](#requirements)
  3. [Order of Play](#order-of-play)
  4. [Additional Info](#additional-info)
  5. [Configuration](#configuration)
  6. [Debugging](#debugging)

## About
I have a "whole-house" MPD/Snapcast music system, and decided to turn it into an alarm clock, so created these three scripts - two written in PHP, one a bash script (a Windows cmd version is also included), to:

  1. Turn off all speakers in the house except the bedroom
  2. Load and play a playlist named "Alarm"
  3. Turn off the bedroom speakers, turn on selected speakers around the house

## Requirements
All the scripts can run as a regular user, so no root privileges required. The requirements are simple enough:

  * PHP (7.4 or better, untested with earlier versions) with Curl enabled
  * A configured MPD/Snapcast multi-room audio system
  * An installed mpc (music player client)

Since the scripts are PHP, they are cross-platform.

## Order of Play
The first script, `alarm_set.php` is run when you want the alarm to go off, and fulfills the function of turning off all the speakers around the house while ensuring the bedroom speakers are turned on, then runs the alarm playlist.

The above script can be run on any machine that is running 24/7, but the two best choices would be either the bedroom device or the actual MPD/SnapcastServer machine. I use the bedroom machine.

The third script I run on my desktop machine. It's GNU/Linux Debian, and I power it down each night, so I run the script using the bootup macro in cron:
```@reboot /path/to/script/alarm_reset.php```
This script turns the bedroom speakers off (so the spiders can go back to sleep), and turns on any speakers defined in an array in the script.

## Additional Info
I wrote the scripts for myself, but have made them as generic as possible. Invoking a playlist called "Alarm" has the benefit that you can change the contents of the playlist without having to change the scripts.

This setup allows occupants of your dwelling to listen to music through the early hours of the night, and nobody has to worry about making sure they turn speakers off before they go to sleep.

## Configuration
Yeah, I put configuration at the end of the readme, because I felt it necessary to understand how things worked before going into config options. First off, if you are running on a unix-like system, you'll need to set the scripts to executable:
```chmod +x alarm_set.php```
Repeat for the other script, of course.

In the first line of each script is a hash-bang to the program required to run the script. For PHP:
```
#!/usr/bin/php
```
If PHP is not in the same location, please adjust accordingly.

**Windows**
To run the scripts, you'll either have to call them thus:
```
> /path/to/php scriptname.php
```
... or ensure PHP is on the PATH environment variable.

**end-of-windows-note**

In `alarm_set.php` you'll find three constants you'll need to set as appropriate:
```
// set this to the name of the machine MPD/SnapServer are running on if not localhost
define("_MPD_HOST_", "127.0.0.1" );

// set this to the name of your alarm clock machine
// note that a hostname is required, you can't use localhost or 127.0.0.1
define("_ALARM_CLOCK_", "AlarmClock" );

define("__ALARM_PLAYLIST__", "Alarm" );
```
The \_\_ALARM_PLAYLIST\_\_ allows you to choose another playlist. Once setup, rather than changing the name of the playlist, it would be easier to change the _contents_.

In `alarm_reset.php`, change these variables as required:
```
// set this to the name of the machine MPD/SnapServer are running on if not localhost
define("_MPD_HOST_", "127.0.0.1" );

// set this to the name of your alarm clock machine
// note that a hostname is required, you can't use localhost or 127.0.0.1
define("_ALARM_CLOCK_", "AlarmClock" );

// list the machines you want to mute, all others will be unmuted
$mutables = array( _ALARM_CLOCK_ /*, "another_machine" */ );
```
For those new to scripting PHP, the bit between `/*` and `*/` is a comment. Removing those two markers makes the code between them active.

That's all there is to it. Good luck.

## Debugging
I've left debugging code in the php scripts. Set this constant to true to output debugging data:
```
//define("__DEBUG__", false);
```
At any point in the code you can call `debug( "text to dump" );` to dump text to the screen - note you should invoke the script manually if you want to see the output. Alternately, you can change the `debug()` function to append to a file with `file_put_contents( filename, "$t\n", FILE_APPEND );`
