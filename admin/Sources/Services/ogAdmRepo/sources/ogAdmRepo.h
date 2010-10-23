// ********************************************************************************************************
// Servicio: ogAdmRepo
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmRepo.h
// Descripción: Este fichero implementa el servicio de repositorio de imágenes
// ********************************************************************************************************
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <errno.h>
#include <unistd.h>
#include <time.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include "ogAdmLib.h"
// ________________________________________________________________________________________________________
// Variables globales
// ________________________________________________________________________________________________________
char iplocal[LONPRM];	// Dirección IP del servidor de administración
char puerto[LONPRM];	// Puerto de comunicación

char servidoradm[LONIP]; // IP del servidor

typedef struct{  // Estructura usada para referenciar las funciones que procesan las tramas
	char nf[LONFUN]; // Nombre de la función
	BOOLEAN (*fptr)(SOCKET*,TRAMA*); // Puntero a la función que procesa la trama
}MSGFUN;

MSGFUN tbfuncionesRepo[MAXIMAS_FUNCIONES];

// ________________________________________________________________________________________________________
// Prototipo de funciones
// ________________________________________________________________________________________________________
BOOLEAN tomaConfiguracion(char*);
BOOLEAN gestionaTrama(SOCKET*);
BOOLEAN tomaConfiguracion(char*);
