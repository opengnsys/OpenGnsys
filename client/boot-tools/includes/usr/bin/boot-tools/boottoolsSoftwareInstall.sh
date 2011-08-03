#!/bin/bash
################################################################
##################### SOFTWARE #####################
################################################################
export LANGUAGE=C
export LC_ALL=C
export LANG=C

export OSDISTRIB=$(lsb_release -i | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
#OSCODENAME=$(lsb_release -c | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
export OSCODENAME=$(cat /etc/lsb-release | grep CODENAME | awk -F= '{print $NF}')
export OSRELEASE=$(uname -a | awk '{print $3}')
uname -a | grep x86_64 > /dev/null  &&  export OSARCH=amd64 || export OSARCH=i386
export OSHTTP="http://es.archive.ubuntu.com/ubuntu/"


dpkg-divert --local --rename --add /sbin/initctl 
ln -s /bin/true /sbin/initctl


apt-get clean
apt-get -y update

 
# software system
apt-get -y --force-yes install linux-image-${OSRELEASE} linux-headers-${OSRELEASE} linux-image-$RELEASE wget dialog man-db htop fbset gdebi-core busybox-static

apt-get -y --force-yes install console-data locales

# sofware networking
apt-get -y --force-yes install netpipes nfs-common sshfs smbfs smbclient davfs2 unionfs-fuse open-iscsi nmap tcpdump arping dnsutils tftp

apt-get clean
# software services
apt-get -y --force-yes install openssh-server bittornado trickle iptraf screen schroot grub lighttpd 

# software disk and filesystem
apt-get -y --force-yes install drbl-ntfsprogs ntfsprogs parted ntfs-3g dosfstools ncdu
apt-get -y --force-yes install dmraid dmsetup lvm2 e2fsprogs jfsutils reiserfsprogs xfsprogs unionfs-fuse mhddfs squashfs-tools
apt-get -y --force-yes install  hfsplus hfsprogs hfsutils nilfs-tools reiser4progs ufsutils

#btrfs-tools

# software cloning
apt-get -y --force-yes install drbl-partimage fsarchiver pv kexec-tools
apt-get -y --force-yes install mbuffer

#monitor
apt-get -y --force-yes install bwbar bmon iftop ifstat  dstat  hdparm sdparm blktool testdisk ssmping mii-diag

## software postconf
apt-get -y --force-yes install drbl-chntpw chntpw ethtool lshw gawk subversion

# software compressor
apt-get -y --force-yes install lzma zip unzip gzip lzop drbl-lzop pigz pbzip2 lbzip2 rzip p7zip-full unzip


#compatibilidad og2
apt-get -y --force-yes install python-openssl python



apt-get -y --force-yes remove busybox
apt-get -y --force-yes install busybox-static  bash-static 


#apt-get -y --force-yes install xorg-dev xorg lxde roxterm

apt-get clean
#
####################################################################
###################### Reconfigurando paquetes ######################
###################################################################


#dpkg-reconfigure console-data
#dpkg-reconfigure console-setup
#dpkg-reconfigure locales
apt-get clean
##TODO################# Borrar algunos binarios del mkinitramfs



