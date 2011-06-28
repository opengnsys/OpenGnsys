!#/bin/bash
#TODO Comprobar si esta los source.

#svn checkout http://www.opengnsys.es/svn/branches/version1.0/client /tmp/opengnsys_installer/opengnsys/client/;
#svn checkout http://www.opengnsys.es/svn/branches/version2/  /tmp/opengnsys_installer/opengnsys2
find /tmp/opengnsys_installer/ -name .svn -type d -exec rm -fr {} \; 2>/dev/null;

	
SVNCLIENTDIR=/tmp/opengnsys_installer/opengnsys/client/boot-tools
SVNCLIENTSTRUCTURE=/tmp/opengnsys_installer/opengnsys/client/shared
SVNCLIENTENGINE=/tmp/opengnsys_installer/opengnsys/client/engine
SVNOG2=/tmp/opengnsys_installer/opengnsys2
 
OGCLIENTMOUNT="/"

#ogClientOsInfo host
#ogClientOsInfo $1
#1 la salida de ogClientOsInfo
OSDISTRIB=$(lsb_release -i | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
#OSCODENAME=$(lsb_release -c | awk -F: '{sub(/\t/,""); print $2}') 2>/dev/null
OSCODENAME=$(cat /etc/lsb-release | grep CODENAME | awk -F= '{print $NF}')
OSRELEASE=$(uname -a | awk '{print $3}')
uname -a | grep x86_64 > /dev/null  &&  export OSARCH=amd64 || export OSARCH=i386
OSHTTP="http://es.archive.ubuntu.com/ubuntu/"
echo $OSDISTRIB:$OSCODENAME:$OSRELEASE:$OSARCH:$OSHTTP	

LERROR=TRUE

echo "$FUNCNAME: Iniciando la personalización con datos del SVN "
# comprobar que está montado  $OGCLIENTMOUNT
#mount | grep $OGCLIENTMOUNT || mount $OGCLIENTFILE $OGCLIENTMOUNT -o loop,offset=32256

# parseamos del apt.source
sed -e "s/OSCODENAME/$OSCODENAME/g" ${SVNCLIENTDIR}/clientstructure/etc/apt/sources.list.ubuntu > ${SVNCLIENTDIR}/clientstructure/etc/apt/sources.list
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Parsing apt.sources : ERROR"
	exit 1
fi

#parseamos el script de generación del initrd.
sed -e "s/OSRELEASE/$OSRELEASE/g" ${SVNCLIENTDIR}/clientstructure/root/GenerateInitrd.generic.sh > ${SVNCLIENTDIR}/clientstructure/root/GenerateInitrd.sh
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Parsing GenerateInitrd.sh : ERROR"
	exit 1
fi

#damos permiso al directorio de scripts 
chmod 775 ${SVNCLIENTDIR}/clientstructure/root/*

# los copiamos
cp -prv ${SVNCLIENTDIR}/clientstructure/* $OGCLIENTMOUNT
mkdir -p ${OGCLIENTMOUNT}/opt/opengnsys/
cp -prv ${SVNCLIENTSTRUCTURE}/* ${OGCLIENTMOUNT}/opt/opengnsys/
cp -prv ${SVNCLIENTENGINE}/* ${OGCLIENTMOUNT}/opt/opengnsys/lib/engine/bin/

if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying client data : ERROR"
	exit 1
fi

# copiamos algunas cosas del nfsexport

#### Tipos de letra para el Browser.
cp -pr ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/lib/fonts
#### Crear enlaces para compatibilidad con las distintas versiones del Browser.
mkdir -p $OGCLIENTMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/
mkdir -p $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.2/lib/
mkdir -p $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.3/lib/
#ln -fs $OGCLIENTMOUNT/usr/local/lib/fonts /usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts
#ln -fs $OGCLIENTMOUNT/usr/local/lib/fonts /usr/local/QtEmbedded-4.6.2/lib/fonts
#ln -fs $OGCLIENTMOUNT/usr/local/lib/fonts /usr/local/QtEmbedded-4.6.3/lib/fonts
cp -pr ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/Trolltech/QtEmbedded-4.5.1/lib/fonts 
cp -pr ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.2/lib/fonts 
cp -pr ${SVNCLIENTSTRUCTURE}/lib/fonts $OGCLIENTMOUNT/usr/local/QtEmbedded-4.6.3/lib/fonts




if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Linking Browser fonts : ERROR"
	exit 1
fi

# B ########################################################
cp -pr ${SVNCLIENTSTRUCTURE}/lib/pci.ids $OGCLIENTMOUNT/etc
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying pci.ids : ERROR"
	exit 1
fi

cp ${SVNCLIENTSTRUCTURE}/bin/browser $OGCLIENTMOUNT/bin
if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying Browser : ERROR"
	exit 1
fi

#plymoutyh
update-alternatives --install /lib/plymouth/themes/default.plymouth default.plymouth /lib/plymouth/themes/opengnsys/opengnsys.plymouth 100
update-alternatives --set default.plymouth /lib/plymouth/themes/opengnsys/opengnsys.plymouth 


#Compatiblidad con og2
cp -prv ${SVNOG2}/engine/2.0 en $OGCLIENTMOUNT/opt/opengnsys/lib/engine/2.0
cp -prv ${SVNOG2}/ogr/ogr $OGCLIENTMOUNT/opt/opengnsys/bin/
cp -prv ${SVNOG2}/ogr/libogr.py $OGCLIENTMOUNT/opt/opengnsys/bin/
cp -prv ${SVNOG2}/job_executer $OGCLIENTMOUNT/opt/opengnsys/bin/



cp ${SVNCLIENTSTRUCTURE}/bin/ogAdmClient  $OGCLIENTMOUNT/bin


if [ $? -ne 0 ]
then 
	echo "$FUNCNAME(): Copying ogAdmClient : ERROR"
	exit 1
else
	echo "$FUNCNAME: Finalizado: OK  "
	exit 0
fi


