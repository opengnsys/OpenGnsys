#!/bin/bash
#TODO Comprobar si esta los source.

#svn checkout http://www.opengnsys.es/svn/branches/version1.0/client /tmp/opengnsys_installer/opengnsys/client/;
#svn checkout http://www.opengnsys.es/svn/branches/version2/  /tmp/opengnsys_installer/opengnsys2
#find /tmp/opengnsys_installer/ -name .svn -type d -exec rm -fr {} \; 2>/dev/null;
#apt-get -y --force-yes install  subversion
#export SVNURL="http://opengnsys.es/svn/branches/version1.0/client/"
#VERSIONSVN=$(LANG=C svn info $SVNURL | awk '/Revision:/ {print "r"$2}')

VERSIONSVN=$(cat /tmp/versionsvn.txt)
VERSIONBOOTTOOLS=ogLive

NAMEISOCLIENTFILE="/tmp/opengnsys_info_rootfs" 
NAMEHOSTCLIENTFILE="/tmp/opengnsys_chroot"
	
SVNCLIENTDIR=/tmp/opengnsys_installer/opengnsys/client/boot-tools
SVNCLIENTSTRUCTURE=/tmp/opengnsys_installer/opengnsys/client/shared
SVNCLIENTENGINE=/tmp/opengnsys_installer/opengnsys/client/engine
SVNOG2=/tmp/opengnsys_installer/opengnsys2
 
OGCLIENTMOUNT=""

OSDISTRIB=${OSDISTRIB:-$(lsb_release -is)}
OSCODENAME=${OSCODENAME:-$(lsb_release -cs)}
OSRELEASE=${OSRELEASE:-$(uname -a | awk '{print $3}')}
if [ -z "$OSARCH" ]; then
	uname -a | grep x86_64 > /dev/null  &&  export OSARCH=amd64 || export OSARCH=i386
fi
OSHTTP=${OSHTTP:-"http://es.archive.ubuntu.com/ubuntu/"}

echo "$OSDISTRIB:$OSCODENAME:$OSRELEASE:$OSARCH:$OSHTTP"


LERROR=TRUE

echo "$FUNCNAME: Iniciando la personalización con datos del SVN "

# parseamos del apt.source
sed -e "s/OSCODENAME/$OSCODENAME/g" ${SVNCLIENTDIR}/includes/etc/apt/sources.list.ubuntu > ${SVNCLIENTDIR}/includes/etc/apt/sources.list
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Parsing apt.sources : ERROR"
	exit 1
fi



#damos permiso al directorio de scripts 
chmod -R 775 ${SVNCLIENTDIR}/includes/usr/bin/*

# los copiamos
cp -av ${SVNCLIENTDIR}/includes/* ${OGCLIENTMOUNT}/
mkdir -p ${OGCLIENTMOUNT}/opt/opengnsys/
cp -av ${SVNCLIENTSTRUCTURE}/* ${OGCLIENTMOUNT}/opt/opengnsys/
cp -av ${SVNCLIENTENGINE}/* ${OGCLIENTMOUNT}/opt/opengnsys/lib/engine/bin/

if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying client data : ERROR"
	exit 1
fi

# copiamos algunas cosas del nfsexport

#### Tipos de letra para el Browser.
cp -a ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/lib/fonts
#### Crear enlaces para compatibilidad con las distintas versiones del Browser.
mkdir -p $OGCLIENTMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/
mkdir -p $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.2/lib/
mkdir -p $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.3/lib/
cp -a ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts 
cp -a ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.2/lib/fonts 
cp -a ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.3/lib/fonts
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Linking Browser fonts : ERROR"
	exit 1
fi

#########################################################
cp -a ${SVNCLIENTSTRUCTURE}/lib/pci.ids $OGCLIENTMOUNT/etc
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying pci.ids : ERROR"
	exit 1
fi
####### Browsser
cp -av ${SVNCLIENTSTRUCTURE}/bin/browser $OGCLIENTMOUNT/bin
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying Browser : ERROR"
	exit 1
fi


#Compatiblidad con og2
cp -av ${SVNCLIENTSTRUCTURE}/bin/browser2 $OGCLIENTMOUNT/bin
cp -av ${SVNOG2}/ogr/ogr $OGCLIENTMOUNT/opt/opengnsys/bin/
cp -av ${SVNOG2}/ogr/libogr.py $OGCLIENTMOUNT/usr/lib/python2.7/libogr.py
cp -av ${SVNOG2}/ogr/libogr.py $OGCLIENTMOUNT/usr/lib/python2.6/libogr.py
cp -av ${SVNOG2}/ogr/libogr.py $OGCLIENTMOUNT/opt/opengnsys/lib/python

echo "mkdir -p /opt/opengnsys/lib/engine/"
mkdir -p /opt/opengnsys/engine/
echo "cp -prv ${SVNOG2}/engine/2.0/* $OGCLIENTMOUNT/opt/opengnsys/engine/" 
cp -av ${SVNOG2}/engine/2.0/* $OGCLIENTMOUNT/opt/opengnsys/engine/

cp -av ${SVNOG2}/job_executer $OGCLIENTMOUNT/opt/opengnsys/bin/


cp -av ${SVNCLIENTSTRUCTURE}/bin/ogAdmClient  $OGCLIENTMOUNT/bin


echo "${VERSIONBOOTTOOLS}-${OSCODENAME}-${OSRELEASE}-${VERSIONSVN}" > /$NAMEISOCLIENTFILE
echo "${VERSIONBOOTTOOLS}-${OSCODENAME}-${VERSIONSVN}" > $NAMEHOSTCLIENTFILE


history -c

