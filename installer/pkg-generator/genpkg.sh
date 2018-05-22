#!/bin/bash
#####################################################################
####### This script downloads svn repo and generates a debian package
####### Autor: Fredy <aluque@soleta.eu>      2018 Q1
####### First attempt just launches the opengnsys_installer
#####################################################################

# Needs root priviledges
if [ "$(whoami)" != 'root' ]; then
        echo "ERROR: this program must run under root privileges!!"
        exit 1
fi

VERSION="1.1"
SVNURL=https://opengnsys.es/svn/branches/version$VERSION
PKG_GEN_PATH=/root/debian-pkg
#DESTDIR=$ROOTDIR/opt/opengnsys
TMPDIR=/tmp/opengnsys_installer

# Registro de incidencias.
OGLOGFILE=$TMPDIR/log/${PROGRAMNAME%.sh}.log 
LOG_FILE=/tmp/$(basename $OGLOGFILE) 

BRANCH="master"
CODE_URL="https://codeload.github.com/opengnsys/OpenGnsys/zip/$BRANCH"
API_URL="https://api.github.com/repos/opengnsys/OpenGnsys/branches/$BRANCH"
RAW_URL="https://raw.githubusercontent.com/opengnsys/OpenGnsys/$BRANCH"

DEV_BRANCH="debian-pkg"


function help()
{
read -r -d '' HELP <<- EOM
########################################################################
#  This script creates debian ".deb" packages for the great            #
#           Opengnsys Deployment Software                              #
#  - Select which type of package you would like to generate           #
#  - You will find your ".deb" file inside /root/debian-pkg folder     #
#  - Send the ".deb" file to your destination machine and install it:  #
#  - apt install ./opengnsys-*.deb   (use apt instead apt-get or dpkg) #
#  The script has been tested on Ubuntu Xenial 16.04 LTS               #
########################################################################
EOM
echo "$HELP"
}

function getDateTime()
{
	date "+%Y%m%d-%H%M%S"
}

# Escribe a fichero y muestra por pantalla
function echoAndLog()
{
	echo $1
	DATETIME=`getDateTime`
	echo "$DATETIME;$SSH_CLIENT;$1" >> $LOG_FILE
}

function errorAndLog()
{
	echo "ERROR: $1"
	DATETIME=`getDateTime`
	echo "$DATETIME;$SSH_CLIENT;ERROR: $1" >> $LOG_FILE
}

function createControlFile()
{
cat > $ROOTDIR/DEBIAN/control << EOF
Package: $PKG_NAME
Priority: optional
Section: misc
Maintainer: info@opengnsys.es
Architecture: all
Version: $VERSION
$DEPENDS
Description: Opengnsys Deploy Generator
Homepage: https://opengnsys.es
EOF
}

function downloadCode()
{
	if [ $# -ne 1 ]; then
		errorAndLog "${FUNCNAME}(): invalid number of parameters"
		exit 1
	fi

	local url="$1"

	echoAndLog "${FUNCNAME}(): downloading code..."

	curl "${url}" -o opengnsys.zip && unzip opengnsys.zip && mv "OpenGnsys-$BRANCH" opengnsys
	if [ $? -ne 0 ]; then
		errorAndLog "${FUNCNAME}(): error getting code from ${url}, verify your user and password"
		return 1
	fi
	rm -f opengnsys.zip
	echoAndLog "${FUNCNAME}(): code was downloaded"
	return 0
}

function createFullPackage()
{
PKG_NAME="opengnsys-full"
ROOTDIR=$PKG_GEN_PATH/$PKG_NAME
mkdir -p $DESTDIR $TMPDIR $ROOTDIR
#downloadCode $CODE_URL
# for testing and development:
git clone gituser@opengnsys.es:/git/opengnsys -b $DEV_BRANCH $TMPDIR
mkdir -p $ROOTDIR/DEBIAN $ROOTDIR/tmp
ln -s $TMPDIR/ $TMPDIR/opengnsys
DEPENDS="Depends: subversion, apache2, php, php-ldap, libapache2-mod-php, isc-dhcp-server, bittorrent, tftp-hpa, tftpd-hpa, xinetd, build-essential, g++-multilib, libmysqlclient-dev, wget, curl, doxygen, graphviz, bittornado, ctorrent, samba, rsync, unzip, netpipes, debootstrap, schroot, squashfs-tools, btrfs-tools, procps, arp-scan, realpath, php-curl, gettext ,moreutils, jq, wakeonlan, mysql-server, php-mysql, udpcast"

# Copy installer to postinst
#cp $TMPDIR/installer/opengnsys_git_installer.sh $ROOTDIR/DEBIAN/postinst
cp -a $TMPDIR/installer/pkg-generator/DEBIAN $ROOTDIR/

createControlFile

# Ejemplo de modificacion del postinst al vuelo
# sed -i 's/wget --spider -q/wget --spider -q --no-check-certificate/g' $ROOTDIR/DEBIAN/postinst

# deactivate svn function
#sed -i '/function svnExportCode/{N;s/$/\nreturn 0/}' $ROOTDIR/DEBIAN/postinst

# copy repo structure inside .deb package and delete .git dir
cp -r $TMPDIR $ROOTDIR/tmp
rm -Rf $ROOTDIR/tmp/opengnsys_installer/.git

# Finally Generate package
cd $PKG_GEN_PATH
dpkg --build $PKG_NAME .
}


function createClientPackage()
{
PKG_NAME="opengnsys-client"
ROOTDIR=$PKG_GEN_PATH/$PKG_NAME
mkdir -p $TMPDIR/opengnsys/client/
mkdir -p $ROOTDIR/tmp/client
svn checkout $SVNURL/client $TMPDIR/opengnsys/client/
mkdir -p $ROOTDIR/DEBIAN
DEPENDS="Depends: debootstrap, subversion, schroot, squashfs-tools, syslinux, genisoimage, ipxe, qemu, lsof"
createControlFile
# Copy installer to postinst
cp $TMPDIR/opengnsys/client/boot-tools/boottoolsgenerator.sh $ROOTDIR/DEBIAN/postinst
# Modify installer
sed -i 's/apt-get -y --force-yes install/#apt-get -y --force-yes install/g' $ROOTDIR/DEBIAN/postinst
# Copy repo to package
cp -a $TMPDIR $ROOTDIR/tmp
# Generate package
cd $PKG_GEN_PATH
dpkg --build $PKG_NAME .
}

# Start the Menu
echo "Main Menu"

# Define the choices to present to the user.
choices=( 'help' "Create full package" "Client package (testing)" 'exit')

while [ "$menu" != 1 ]; do
# Present the choices.
# The user chooses by entering the *number* before the desired choice.
	select choice in "${choices[@]}"; do

		# Examine the choice.
		case $choice in
		help)
		  echo "Generate Package Help"
		  help

		  ;;
		"Create full package")
			echo "Creating new full package..."
			createFullPackage
			exit 0
		  ;;
		"Client package (testing)")
			echo "Creating Client package..."
			createClientPackage
			exit 0
		  ;;		  
		exit)
		  echo "Exiting. "
		  exit 0
		  ;;
		*)
		  echo "Wrong choice!"
		  exit 1
		esac
		break

	done
done

echo "End of the script"
exit



