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

echo  "ms-sys "
which ms-sys || ms-sys install &>/dev/null

echo  "spartlnx"
which spartlnx.run || $(wget http://damien.guibouret.free.fr/savepart.zip &>/dev/null; unzip -o savepart.zip -d /sbin/)

# Mach-O loader for Linux
#echo "maloader"
#apt-get install -y uuid-dev lib64z1 lib32z1
#wget https://github.com/shinh/maloader/archive/master.zip
#unzip master.zip
#cd maloader-master
#perl -pi -le 'print "#include <unistd.h>" if $. == 45' ld-mac.cc
#if [ "$(arch)" == "x86_64" ]; then
#    ln -fs /lib/x86_64-linux-gnu/libcrypto.so.1.0.0 /lib/libcrypto.so 2>/dev/null
#    make release
#else
#    ln -fs /lib/i386-linux-gnu/libcrypto.so.1.0.0 /lib/libcrypto.so 2>/dev/null
#    make clean
#    make all BITS=32
#fi
#cp -va ld-mac /usr/bin
#cp -va libmac.so /usr/lib
#cd ..
#rm -fr master.zip maloader-master

popd
export LANGUAGE=$OLDLANGUAGE
export LC_ALL=$OLDLC_ALL
export LANG=$OLDLANG
 
history -c

