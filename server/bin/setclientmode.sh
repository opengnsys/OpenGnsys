#!/bin/bash
# setclientmode.sh: Configura el archivo de arranque de PXE para los clientes, 
#	ya sea un equipo o un aula, generando enlaces a archivos usados como plantilla. 
# Nota: El archivo PXE por defecto "default" se deja en modo de ejecución "user" 
#	y se eliminan los enlaces para equipos con la plantilla por defecto.
# Uso: clienmode.sh NombrePlatilla { NombrePC | NombreAula }
# Autores: Irina Gomez y Ramon Gomez - Univ. Sevilla, noviembre 2010


# Variables.
PROG=$(basename $0)
OPENGNSYS=${OPENGNSYS:-"/opt/opengnsys"}
SERVERCONF=$OPENGNSYS/etc/ogAdmServer.cfg
PXEDIR=$OPENGNSYS/tftpboot/pxelinux.cfg
LOGFILE=$OPENGNSYS/log/opengnsys.log

# Control básico de errores.
if [ $# -ne 2 ]; then
	echo "$PROG: Error de ejecución"
	echo "Formato: $PROG Archivo_platilla [NOMBRE_PC|NOMBRE_AULA]"
	exit 1
fi
if [ ! -r $SERVERCONF ]; then
	echo "$PROG: Sin acceso a fichero de configuración"
	exit 2
fi
if [ ! -e $PXEDIR/"$1" ]; then
	echo "No existe archivo platilla: $PXEDIR/$1"
	exit
fi

# Obtener datos de acceso a la Base de datos.
source $SERVERCONF
# Comprobar si se recibe nombre de aula o de equipo.
IDAULA=$(mysql -u "$USUARIO" -p"$PASSWORD" -D "$CATALOG" -N -e \
		"SELECT idaula FROM aulas WHERE nombreaula=\"$2\";")

if [ -n "$IDAULA" ]; then
	# Aula encontrada
	ETHERNET=$(mysql -u "$USUARIO" -p"$PASSWORD" -D "$CATALOG" -N -e \
        	"SELECT mac FROM ordenadores WHERE idaula=\"$IDAULA\";")
else
	# Buscar ordenador
	ETHERNET=$(mysql -u "$USUARIO" -p"$PASSWORD" -D "$CATALOG" -N -e \
        	"SELECT mac FROM ordenadores WHERE nombreordenador=\"$2\";")
fi
if [ -z "$ETHERNET" ]; then
	date +"%b %d %T $PROG: No existe aula o equipo con el nombre \"$2\"" | tee -a $LOGFILE
	exit 1
fi

# Creamos los enlaces
# PXE no permite enlaces simbolicos y las letras han de ir en minuscula
date +"%b %d %T $PROG: Configurando \"$1\" en \"$2\"" | tee -a $LOGFILE
NPC=0
for AUX in $ETHERNET; do
	date +"%b %d %T $PROG: Detectada ethernet \"$AUX\" en \"$2\"" | tee -a $LOGFILE
	AUX=$(echo $AUX | awk '{print tolower($0)}')
	AUX="01-${AUX:0:2}-${AUX:2:2}-${AUX:4:2}-${AUX:6:2}-${AUX:8:2}-${AUX:10:2}"
	# Si existe anteriormente lo borra
	[ -e $PXEDIR/$AUX ] && rm $PXEDIR/$AUX
	if [ "$1" != "default" ]; then
		ln $PXEDIR/"$1" $PXEDIR/$AUX
	fi
	let NPC=NPC+1
done
date +"%b %d %T $PROG: $NPC equipo(s) configurado(s)" | tee -a $LOGFILE

