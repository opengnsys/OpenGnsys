#!/bin/bash
#/**
# * @mainpage Proyecto OpenGnSys
# *
# * Documentación de la API de funciones del motor de clonación de OpenGnSys.
# *
# *
# * @file    ToolsGNU.c
# * @brief   Librería o clase Tools GNU used by OpenGNSys
# * @class   Tools
# * @brief   Herramientas gnu utilizadas por opengnsys.
# * @version 0.9
# * @warning License: GNU GPLv3+
# */

function install ()
{
	[ $# = 0 ] && echo pasar url del tar.gz && return
	cd /tmp
	wget -O download.tgz $1
	mkdir download || directorio no creado
	tar xzvf download.tgz -C download
	for i in `ls download`
	do
	  cd download/$i
	  [ -e "configure" ] && ./configure
	  make && make install
	  cd - && rm -fr download*
	done
}

function mbuffer ()
{
	if [ "$1" = install ]
	then
		install http://www.maier-komor.de/software/mbuffer/mbuffer-20091122.tgz
	else
		return
	fi
}

function ms-sys ()
{
	if [ "$1" = install ]
	then
		install http://downloads.sourceforge.net/project/ms-sys/ms-sys%20development/2.1.4/ms-sys-2.1.4.tar.gz
	else
		return
	fi
}

function ctorrent ()
{
	if [ "$1" = install ]
	then
		install http://sourceforge.net/projects/dtorrent/files/dtorrent/3.3.2/ctorrent-dnh3.3.2.tar.gz/download
	else
		return
	fi
}

function udpcast ()
{
	if [ "$1" = install ]
	then
		install http://udpcast.linux.lu/download/udpcast-20091031.tar.gz
	else
		return
	fi
}

function ntfs-3g ()
{
if [ "$1" = install ]
     then
		 install http://tuxera.com/opensource/ntfs-3g-2010.3.6.tgz
     else
    	 return
fi

}

function partitionsaving ()
{
echo  http://damien.guibouret.free.fr/savepart.zip

}

function awk ()
{
}

function chntpw ()
{
}

function ctorrent ()
{
}

function fdisk ()
{
}

function fsck ()
{
}

function kexec ()
{
}

function lshw ()
{
}

function mkfs ()
{
}

function mount ()
{
}



function parted ()
{
}

function partimage ()
{
}

function partprobe ()
{
}

function sfdisk ()
{
}

function umount ()
{
}


