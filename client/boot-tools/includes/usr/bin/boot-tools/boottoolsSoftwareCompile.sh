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

popd
export LANGUAGE=$OLDLANGUAGE
export LC_ALL=$OLDLC_ALL
export LANG=$OLDLANG
 
history -c

