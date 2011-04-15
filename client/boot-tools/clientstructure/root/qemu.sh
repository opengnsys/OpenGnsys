#/bin/bash

mount -o rw,remount /
mount proc /proc -t proc
export PATH=$PATH dpkg -i *.deb
modprobe 8139too
modprobe 8139cp
dhclient
/etc/init.d/ssh restart