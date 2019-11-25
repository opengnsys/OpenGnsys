#!/bin/bash
#
# Script:	opengnsys_compress.sh
# Descripción:	Programa para descargar y generar un fichero comprimido con los
#		datos de la última revisión de código del Proyecto OpenGnsys.
# Salida:	Datos del fichero comprimido generado.
# Versión:	1.0 - Incluido en OpenGnSys 1.0.1
# Autor:	Ramón Gómez, Universidad de Sevilla
# Fecha:	10/05/2011
# Versión:	1.1.1 - Descarga desde repositorio de GitHub
# Autor:	Ramón Gómez, Universidad de Sevilla
# Fecha:	27/05/2018


# Comprobaciones.
for PROG in jq unzip; do
    if ! which $PROG &>/dev/null; then
        echo "Please, install \"$PROG\" package."
        exit 1
    fi
done

# Variables.
BRANCH="master"
CODE_URL="https://codeload.github.com/opengnsys/OpenGnsys/zip/$BRANCH"
API_URL="https://api.github.com/repos/opengnsys/OpenGnsys/branches/$BRANCH"
REVISION=$(curl -s "$API_URL" | jq '"r" + (.commit.commit.committer.date | split("-") | join("")[:8]) + "." + (.commit.sha[:7])')

# Descargar del repositorio de código.
cd /tmp
rm -fr opengnsys
curl "$CODE_URL" -o opengnsys.zip && unzip opengnsys.zip && mv "OpenGnsys-$BRANCH" opengnsys

# Asisgnar propietario de los ficheros descargados.
chown -R root.root opengnsys
WARNING=$?

# Parchear datos de revisión del código.
jq ".release=$REVISION" opengnsys/doc/VERSION.json | sponge opengnsys/doc/VERSION.json

# Generar fichero comprimido.
VERSION=$(jq -r '.version+"-"+.release' opengnsys/doc/VERSION.json)
tar cvzf opengnsys-$VERSION.tar.gz opengnsys
rm -fr opengnsys opengnsys.zip

# Revisar salida.
[ $WARNING != 0 ] && echo "*** WARNING: cannot change owner of files to \"root\" user before compressing."
ls -lh $(readlink -e opengnsys-$VERSION.tar.gz)

