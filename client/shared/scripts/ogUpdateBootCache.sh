#!/bin/bash
OGBTFTP="/opt/og2fs/tftpboot/ogclient/"
ogMountCache || exit 1
echo $OGCAC
[ -d $OGCAC/boot ] || mkdir -p $OGCAC/boot 
[ -f ${OGCAC}/boot/ogvmlinuz ] || cp ${OGBTFTP}ogvmlinuz ${OGCAC}/boot/ogvmlinuz
[ -f ${OGCAC}/boot/oginitrd.img ] || cp ${OGBTFTP}oginitrd.img ${OGCAC}/boot/oginitrd.img