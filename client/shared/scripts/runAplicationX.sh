#/bin/bash
/usr/X11R6/bin/Xvesa :0 -ac -shadow -screen 1024x768x24 -br -mouse /dev/input/mice &
/bin/sleep 0.1
export DISPLAY=:0
#/usr/bin/lxde-logout
#/usr/bin/openbox
/usr/bin/roxterm
#/usr/sbin/gparted
