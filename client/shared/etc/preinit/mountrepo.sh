#!/bin/bash
#/**
#@file    mountrepo.sh
#@brief   Script para montar el repositorio de datos remoto.
#@warning License: GNU GPLv3+
#@version 1.0
#@author  Ramon Gomez, ETSII Universidad de Sevilla
#@date    2011-03-17
#*/

OGIMG=${OGIMG:-/opt/opengnsys/images}

# TODO Revisar proceso de arranque para no montar 2 veces el repositorio.
if [ $ogactiveadmin == "true" ]; then 
	export boot=admin
	umount $OGIMG 2>/dev/null

	protocol=${potocol:-"smb"}
	printf "$MSG_MOUNTREPO\n" "$protocol" "$boot"
	case "$protocol" in
		nfs)	mount.nfs ${ROOTSERVER}:$OGIMG $OGIMG -o rw,nolock ;;
		smb)	mount.cifs //${ROOTSERVER}/ogimages $OGIMG -o rw,relatime,serverino,acl,username=opengnsys,password=og ;;
	esac
fi

