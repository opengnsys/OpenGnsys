#!/bin/bash
################################################################
##################### SOFTWARE #####################
################################################################
OLDLANGUAGE=$LANGUAGE
OLDLC_ALL=$LC_ALL
OLDLANG=$LANG
export LANGUAGE=C
export LC_ALL=C
export LANG=C


source /opt/opengnsys/lib/engine/bin/ToolsGNU.c &>/dev/null
pushd /tmp

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

echo "busybox-static 1.17.1 en rootfs"
#echo "busybox-static 1.17.1 en ogLive rootfs permite reboot y poweroff
apt-get remove -y busybox-static
gdebi -n /var/cache/apt/archivesOG/busybox-static_1.17.1-10ubuntu1_i386.deb
cp /bin/busybox /bin/busyboxOLD
/bin/busyboxOLD
echo "busybox-static 1.18.5 en initrd"
apt-get install -y busybox-static
cp /bin/busybox /bin/busyboxNEW
/bin/busyboxNEW
# en scripts reboot y poweroff hacer llamada a busyboxOLD reboot|poweroff


#gpt
echo "gptfdisk"
apt-get install -y uuid-dev libicu-dev libpopt-dev libpopt0 ncurses-base libncurses5-dev
wget -O download.tgz http://sourceforge.net/projects/gptfdisk/files/gptfdisk/0.8.5/gptfdisk-0.8.5.tar.gz/download -O gptfdisk-0.8.5.tar.gz
tar xzvf gptfdisk-0.8.5.tar.gz
cd gptfdisk-0.8.5
make
cp -va sgdisk gdisk fixparts cgdisk /sbin
cd ..
rm -fr gptfdisk-0.8.5*
 
# Mach-O loader for Linux
echo "maloader"
wget https://github.com/shinh/maloader/archive/master.zip
unzip master.zip
cd maloader-master
perl -pi -le 'print "#include <unistd.h>" if $. == 45' ld-mac.cc
if [ "$(arch)" == "x86_64" ]; then
    ln -fs /lib/x86_64-linux-gnu/libcrypto.so.1.0.0 /lib/libcrypto.so 2>/dev/null
    make release
else
    ln -fs /lib/i386-linux-gnu/libcrypto.so.1.0.0 /lib/libcrypto.so 2>/dev/null
    make clean
    make all BITS=32
fi
cp -va ld-mac /usr/bin
cp -va libmac.so /usr/lib
cd ..
rm -fr master.zip maloader-master

popd
export LANGUAGE=$OLDLANGUAGE
export LC_ALL=$OLDLC_ALL
export LANG=$OLDLANG
 
history -c

