// ********************************************************************************************************
// Cliernte: ogAdmLnxClient
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmLnxClient.h
// Descripción :Este fichero implementa el cliente general del sistema
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
#include <sys/wait.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <signal.h>
#include "ogAdmLib.h"
// ________________________________________________________________________________________________________
// Variables globales
// ________________________________________________________________________________________________________
char idordenador[LONPRM];	 // Identificador del ordenador
char nombreordenador[LONPRM]; // Nombre del ordenador

char servidoradm[LONPRM]; // Dirección IP del servidor de administración
char puerto[LONPRM]; // Puerto de comunicación
char IPlocal[LONPRM]; // Path donde está la interface entre la administración y el módulo de clonación


typedef struct{  // Estructura usada para referenciar las funciones que procesan las tramas
	char nf[LONFUN]; // Nombre de la función
	BOOLEAN (*fptr)(TRAMA*); // Puntero a la función que procesa la trama
}MSGFUN;
MSGFUN tbfuncionesClient[MAXIMAS_FUNCIONES];
// ________________________________________________________________________________________________________
// Prototipo de funciones
// ________________________________________________________________________________________________________

void procesaComandos(TRAMA*);
BOOLEAN gestionaTrama(TRAMA *);
BOOLEAN InclusionClienteWinLnx();
BOOLEAN RESPUESTA_InclusionClienteWinLnx(TRAMA *);
BOOLEAN respuestaEjecucionComando(TRAMA *,int,char*);

BOOLEAN Apagar(TRAMA *);
BOOLEAN Reiniciar(TRAMA *);
BOOLEAN Sondeo(TRAMA *);

BOOLEAN enviaMensajeServidor(SOCKET *,TRAMA *,char);

