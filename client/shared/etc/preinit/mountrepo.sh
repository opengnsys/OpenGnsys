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
ROOTREPO=${ROOTREPO:-"$ROOTSERVER"}

# TODO Revisar proceso de arranque para no montar 2 veces el repositorio.
if [ $ogactiveadmin == "true" ]; then 
	export boot=admin	# ATENCIÓN: siempre en modo "admin".
	umount $OGIMG 2>/dev/null

	protocol=${ogprotocol:-"smb"}
	printf "$MSG_MOUNTREPO\n" "$protocol" "$boot"
	case "$ogprotocol" in
		nfs)	mount.nfs ${ROOTREPO}:$OGIMG $OGIMG -o rw,nolock ;;
		smb)	PASS=$(grep "^[ 	]*\(export \)\?OPTIONS=" /scripts/ogfunctions 2>&1 | \
				sed 's/\(.*\)pass=\(\w*\)\(.*\)/\2/')
			PASS=${PASS:-"og"}
			mount.cifs //${ROOTREPO}/ogimages $OGIMG -o rw,serverino,acl,username=opengnsys,password=$PASS
			;;
	esac
fi

