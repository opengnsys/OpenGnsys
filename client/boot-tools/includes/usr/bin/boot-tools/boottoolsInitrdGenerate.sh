#!/bin/bash

OGCLIENTCFG=${OGCLIENTCFG:-/tmp/ogclient.cfg}
[ -f $OGCLIENTCFG ] && source $OGCLIENTCFG
OSRELEASE=${OSRELEASE:-$(uname -r)}

rm -f /usr/lib/initramfs-tools/bin/*
cp /bin/busybox /usr/lib/initramfs-tools/bin
cd /tmp
mkinitramfs -o /tmp/initrd.img-$OSRELEASE -v $OSRELEASE
cp -v /boot/vmlinuz-$OSRELEASE.efi.signed /tmp

history -c

