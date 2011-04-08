#!/bin/bash
export OSDISTRIB=$(lsb_release -i | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
#OSCODENAME=$(lsb_release -c | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
export OSCODENAME=$(cat /etc/lsb-release | grep CODENAME | awk -F= '{print $NF}')
export OSRELEASE=$(uname -a | awk '{print $3}')
uname -a | grep x86_64 > /dev/null  &&  export OSARCH=amd64 || export OSARCH=i386
export OSHTTP="http://es.archive.ubuntu.com/ubuntu/"



cd /usr/lib/initramfs-tools/bin/
rm *
cp /bin/busybox ./
cd /tmp/
mkinitramfs -o /tmp/initrd.img-$OSRELEASE -v $OSRELEASE
cp /boot/vmlinuz-$OSRELEASE /tmp