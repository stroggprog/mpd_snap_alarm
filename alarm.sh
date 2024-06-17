#!/usr/bin/bash
/usr/bin/mpc -h $1 clear
/usr/bin/mpc -h $1 load "Alarm"
/usr/bin/mpc -h $1 play
