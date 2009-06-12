#! /bin/bash
# Copyright 2007 Franklin Piat ; License GPL + LGPL
# Copyright 2009 Juan Carrera ; License GPL + LGPL
#Some local options
[ ! -d /srv/gnsys-live-helper ] && mkdir -p /srv/gnsys-live-helper
cd /srv/gnsys-live-helper || exit 1
MIRROROPTS=" \
--mirror-bootstrap http://ftp.es.debian.org/debian/ \
--mirror-chroot http://ftp.es.debian.org/debian/ \
--mirror-binary http://ftp.es.debian.org/debian/ \
--mirror-chroot-security http://security.eu.debian.org/ \
--mirror-binary-security http://security.eu.debian.org/ \
"


#Let's configure live-helper
lh_config \
--binary-images iso \
--
--iso-application gnsys-live \
--iso-publisher "SICUZ-OP Brutalix; http://sicuz.unizar.es/; carreraj@unizar.es" \
--iso-volume "BrtLive $(date +%Y%m%d-%H:%M)" \
--syslinux-timeout 5 \
--net-root-filesystem nfs \
#--net-root-mountoptions OPTIONS \
--net-root-path /mnt/almacen/nfs \
--net-root-server 10.3.18.1 \
#--net-cow-filesystem nfs|cfs \
#--net-cow-mountoptions OPTIONS \
#--net-cow-path PATH \
#--net-cow-server IP|HOSTNAME \
--language es \
--bootstrap-flavour minimal \
--packages-lists "standard 01-gnsys-packages" \
--apt apt \
--apt-recommends disabled \
--tasksel none \
--binary-indices disabled \
--distribution lenny \
--linux-flavours 686 \
--union-filesystem aufs \
--memtest none \
--bootappend-live "toram locale=es_ES.UTF-8 keyb=es" \
--hostname gnsyslive \
--username gnsys \
$MIRROROPTS
#algunos paquetes han cambiado en lenny y rompen el build. Ademas ya tenemos packages-lists!
#--packages "console-common console-tools  klogd netbase iputils-ping sysklogd update-inetd tcpd dhcp3-client debconf-i18n" \

#let's create some scripts

cat <<CHROOTLOCALHOOKS01 > config/chroot_local-hooks/01-removelocales.sh
#!/bin/sh
# Remove locales
# (this could remove other applications. add "-y" if your are confident)
#apt-get remove locales  --purge
apt-get remove dselect -y --purge
echo "don t fail on abort (set ERRORLEVEL to zero)" > /dev/null
CHROOTLOCALHOOKS01
chmod +x "config/chroot_local-hooks/01-removelocales.sh"

cat <<CHROOTLOCALHOOKS02 > config/chroot_local-hooks/02-purge-some-modules.sh
#!/bin/sh
# delete the modules we won't need (YMMMV)
rm -Rf /lib/modules/*/kernel/drivers/isdn
rm -Rf /lib/modules/*/kernel/drivers/media
rm -Rf /lib/modules/*/kernel/drivers/net/wireless
rm -Rf /lib/modules/*/kernel/sound
CHROOTLOCALHOOKS02
chmod +x "config/chroot_local-hooks/02-purge-some-modules.sh"

cat <<CHROOTLOCALHOOKS03 > config/chroot_local-hooks/03-purge-some-caches-and-docs.sh
#!/bin/sh
# delete apt-caches, docs, man pages, backups we won't need (YMMMV)
apt-get clean
rm  /var/cache/apt/*.bin      
rm -r /usr/share/doc/*
find /usr/share/man | grep .gz | awk '{ system ("rm " $1 )  }  '
find /usr/share/locale | grep .mo | awk '{ system ("rm " $1 )  }  '
rm -r /var/cache/debconf/*old
rm /boot/*bak
rm -Rf /var/lib/apt/lists/*
# we wan't to be able to update apt!
mkdir /var/lib/apt/lists/partial
CHROOTLOCALHOOKS03
chmod +x "config/chroot_local-hooks/03-purge-some-caches-and-docs.sh"

cat <<CHROOTLOCALPACKAGESLIST01 > config/chroot_local-packageslists/01-gnsys-packages
# Lista de paquetes adicionales en gnsys
openssh-client
nmap
ntfs-3g
ntfsprogs 
netcat
bzip2
gettext
dialog
ctorrent
parted
dosfstools
gpart
udpcast
pv
foremost
scalpel
wget
mawk
netbase
iputils-ping
update-inetd
tcpd
dhcp3-client
inetutils-tools
iproute
net-tools
ifupdown
util-linux
psmisc
ettercap
expect
ethtool
pciutils
chntpw
atl2-modules-$(uname -r)
vim-tiny

CHROOTLOCALPACKAGESLIST01


cat <<CHROOTLOCALHOOKS04 > config/chroot_local-hooks/04-install-brutalix-stuff.sh
#!/bin/sh
# delete apt-caches, docs, man pages, backups we won't need (YMMMV)
mkdir /mnt/win
wget "http://forja.rediris.es/snapshots.php?group_id=194" -O snap.tar.gz
tar -xvf snap.tar.gz
rm snap.tar.gz
cp  brutalixl*/scripts/* /usr/bin/
msgfmt brutalixl*/po/es.po
mkdir -p /usr/share/locale/es/LC_MESSAGES/
cp messages.mo /usr/share/locale/es/LC_MESSAGES/brutalix.mo
rm -Rf brutalixl*
CHROOTLOCALHOOKS04
chmod +x "config/chroot_local-hooks/04-install-brutalix-stuff.sh"


# We could exclude some unused  stuffs.
#export LH_BOOTSTRAP_EXCLUDE="vim-tiny,nano,ed"

# exclude some stuffs from the squashfs root
export MKSQUASHFS_OPTIONS="-e boot"

echo "Press <Enter> to continue to build..." &&read
nice ionice -c2 lh_build --debug 2>&1 |tee build.log

