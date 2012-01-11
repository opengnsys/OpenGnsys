#!/bin/bash
################################################################
##################### SOFTWARE #####################
################################################################
export LANGUAGE=C
export LC_ALL=C
export LANG=C


source /opt/opengnsys/lib/engine/bin/ToolsGNU.c &>/dev/null
cd /tmp

echo  "ctorrent "
which ctorrent || ctorrent install &>/dev/null

echo  "udp-sender "
which udp-sender || udpcast install &>/dev/null

echo  "ms-sys "
which ms-sys || ms-sys install &>/dev/null


echo  "spartlnx"
which spartlnx.run || $(wget http://damien.guibouret.free.fr/savepart.zip &>/dev/null; unzip -o savepart.zip -d /sbin/)

echo "xvesa"
gdebi -n /var/cache/apt/archivesOG/xvesa.deb

echo "partclone"
gdebi -n /var/cache/apt/archivesOG/partclone_0.2.38_i386.deb

#echo "busybox"
#gdebi -n /var/cache/apt/archivesOG/busybox_1.17.1-10ubuntu1_i386.deb

echo "busybox-static"
gdebi -n /var/cache/apt/archivesOG/busybox-static_1.17.1-10ubuntu1_i386.deb


history -c