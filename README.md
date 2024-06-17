# mpd_snap_alarm

  1. https://github.com/stroggprog/mpd_snap_alarm/blob/main/README.md#about
  2. https://github.com/stroggprog/mpd_snap_alarm/blob/main/README.md#requirements
  3. 
## About
I have a "whole-house" MPD/Snapcast music system, and decided to turn it into an alarm clock, so created these three scripts written in PHP to:

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
The first script, `alarm_set.php` is run just a few minutes before the alarm goes off, and fulfills the function of turning off all the speakers around the house while ensuring the bedroom speakers are turned on.

The second script `alarm.php` loads the playlist and starts it playing.

Both of the above scripts can be run on any machine that is running 24/7, but the two best choices would be either the bedroom device or the actual MPD/SnapcastServer machine. I use the bedroom machine.

The third script I run on my desktop machine. It's GNU/Linux Debian, and I power it down each night, so I run the script using the bootup macro in cron:
```@reboot /path/to/script/alarm_reset.php```

This way my speakers automagically come alive when I turn my computer on - and also turns off the bedroom speakers, so the spiders can go back to sleep.

## Additional Info
I wrote the scripts for my benefit, but have made them as generic as possible. Invoking a playlist called "Alarm" has the benefit that you can change the contents of the playlist without having to change the scripts.

The `alarm_set.php` script is called separately from `alarm.php` to guarantee (as much as that is possible) that it has turned off all the other speakers before the alarm plays.

This setup allows occupants of your dwelling to listen to music through the night, and nobody has to worry about making sure they turn speakers off before they go to sleep.

## Configuration
Yeah, I put configuration at the end of the readme, because I felt it necessary to understand how things worked before going into config options. First off, if you are running on a unix-like system, you'll need to set the scripts to executable:
```chmod +x alarm_set.php```
Repeat for the other two scripts, of course.

In `alarm_set.php` you'll find two constants you'll need to set as appropriate:
```
// set this to the name of the machine MPD/SnapServer are running on if not localhost
define("_MPD_HOST_", "127.0.0.1" );

// set this to the name of your alarm clock machine
// note that a hostname is required, you can't use localhost or 127.0.0.1
define("_ALARM_CLOCK_", "AlarmClock" );
```

`alarm.php` is called from cron like this (assuming you want to wake up at 6am every day):
```
0 6 * * * /path/to/alarm.php hostname
```
...where `hostname` is the name of the machine running MPD.

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
