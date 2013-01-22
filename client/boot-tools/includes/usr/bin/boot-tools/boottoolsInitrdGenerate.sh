#!/bin/bash

OGCLIENTCFG=${OGCLIENTCFG:-/tmp/ogclient.cfg}
[ -f $OGCLIENTCFG ] && source $OGCLIENTCFG
OSRELEASE=${OSRELEASE:-$(uname -a | awk '{print $3}')}


cd /usr/lib/initramfs-tools/bin/
rm *
cp /bin/busybox ./
cd /tmp/
mkinitramfs -o /tmp/initrd.img-$OSRELEASE -v $OSRELEASE
cp /boot/vmlinuz-$OSRELEASE /tmp

history -c

