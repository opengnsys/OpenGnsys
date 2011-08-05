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

echo  "echo partclone "
which partclone.ntfs || $(wget -O partclone_0.2.16_i386.deb http://downloads.sourceforge.net/project/partclone/stable/0.2.16/partclone_0.2.16_i386.deb?use_mirror=ovh &>/dev/null; gdebi -n partclone_0.2.16_i386.deb &>/dev/null) 

echo  "spartlnx"
which spartlnx.run || $(wget http://damien.guibouret.free.fr/savepart.zip &>/dev/null; unzip -o savepart.zip -d /sbin/)

history -c