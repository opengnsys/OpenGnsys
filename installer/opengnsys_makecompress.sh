#!/bin/bash
#
# Script:	opengnsys_compress.sh
# Descripción:	Programa para descargar y generar un fichero comprimido con los
#		datos de la última revisión de código del Proyecto OpenGnSys.
# Salida:	Datos del fichero comprimido generado.
# Versión:	1.0 - Incluido en OpenGnSys 1.0.1
# Autor:	Ramón Gómez, Universidad de Sevilla
# Fecha:	10/05/2011


# Variables
SVNURL="http://www.opengnsys.es/svn/trunk/"
SVNREV=$(LANG=C svn info $SVNURL | awk '/Last Changed Rev:/ {print "r"$4}')

# Descargar repositorio SVN
cd /tmp
rm -fr opengnsys
svn export $SVNURL opengnsys || exit 1

# Asisgnar propietario de los ficheros descargados.
chown -R root.root opengnsys
WARNING=$?

# Parchear datos de revisión del código.
perl -pi -e "s/$/ $SVNREV/" opengnsys/doc/VERSION.txt

# Generar fichero comprimido.
VERSION=$(awk '{print $2"-"$3}' opengnsys/doc/VERSION.txt)
tar cvzf opengnsys-$VERSION.tar.gz opengnsys
rm -fr opengnsys

# Revisar salida.
[ $WARNING != 0 ] && echo "*** WARNING: cannot change owner of files to \"root\" user before compressing."
ls -lh $(readlink -e opengnsys-$VERSION.tar.gz)

