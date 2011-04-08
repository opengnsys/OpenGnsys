#!/bin/bash
################################################################
##################### SOFTWARE #####################
################################################################
export LANGUAGE=C
export LC_ALL=C
export LANG=C


dpkg-divert --local --rename --add /sbin/initctl 
ln -s /bin/true /sbin/initctl

apt-get clean
apt-get -y update

 
# software system
apt-get -y --force-yes install linux-image-${OSRELEASE} linux-headers-${OSRELEASE} linux-image-$RELEASE wget dialog man-db htop fbset gdebi-core busybox-static

apt-get -y --force-yes install console-data locales

# sofware networking
apt-get -y --force-yes install netpipes nfs-common sshfs smbfs smbclient davfs2 unionfs-fuse open-iscsi nmap tcpdump arping dnsutils

apt-get clean
# software services
apt-get -y --force-yes install openssh-server bittornado trickle iptraf screen schroot grub

# software disk and filesystem
apt-get -y --force-yes install drbl-ntfsprogs ntfsprogs parted ntfs-3g dosfstools
apt-get -y --force-yes install dmraid dmsetup  lvm2 e2fsprogs jfsutils reiserfsprogs xfsprogs unionfs-fuse mhddfs squashfs-tools
apt-get -y --force-yes install btrfs-tools hfsplus hfsprogs hfsutils

# software cloning
apt-get -y --force-yes install drbl-partimage fsarchiver pv kexec-tools
apt-get -y --force-yes install mbuffer


apt-get clean
## software postconf
apt-get -y --force-yes install drbl-chntpw chntpw ethtool lshw gawk

# software compressor
apt-get -y --force-yes install lzma zip unzip gzip lzop drbl-lzop pigz pbzip2 lbzip2 rzip p7zip-full unzip

apt-get -y --force-yes remove busybox
apt-get -y --force-yes install busybox-static  bash-static 
apt-get clean
#apt-get -y --force-yes xorg-dev xorg lxde roxterm
#
####################################################################
###################### Reconfigurando paquetes ######################
###################################################################


#dpkg-reconfigure console-data
#dpkg-reconfigure console-setup
#dpkg-reconfigure locales
apt-get clean
##TODO################# Borrar algunos binarios del mkinitramfs


