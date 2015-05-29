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
if [ "$ogactiveadmin" == "true" ]; then 
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
		local)	# TODO: hacer funcion dentro de este script que monte smb
			# Comprobamos que estatus sea online.
			if [ "$ogstatus" == "offline" -o "$SERVER" == "" ]; then
			   # Si estatus es offline buscamos un dispositivo con etiqueta repo
			   # y si no existe montamos la cache como repo (si existe).
			   TYPE=$(blkid | grep REPO | awk -F"TYPE=" '{print $2}' | tr -d \")
			   if [ "$TYPE" == "" ]; then
				[ -d $OGCAC/$OGIMG ] && mount --bind  $OGCAC/$OGIMG $OGIMG
			   else
		           	mount -t $TYPE LABEL=REPO $OGIMG &>/dev/null
			   fi
			else
                           # Comprobamos que existe un servicio de samba.
                           smbclient -L $SERVER -N &>/dev/null
                           if [ $? -eq 0 ]; then
			   	PASS=$(grep "^[         ]*\(export \)\?OPTIONS=" /scripts/ogfunctions 2>&1 | \
			   	   sed 's/\(.*\)pass=\(\w*\)\(.*\)/\2/')
			   	PASS=${PASS:-"og"}
			   	mount.cifs //${ROOTREPO}/ogimages $OGIMG -o rw,serverino,acl,username=opengnsys,password=$PASS
			   fi
			   # TODO: buscar condicion para NFS
			fi
			;;
	esac
fi

