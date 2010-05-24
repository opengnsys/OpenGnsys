// *************************************************************************
// Aplicación: OPENGNSYS
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: ogAdmServer.php
// Descripción : 
//		Este fichero aporta las funciones de localización y ejecución de tareas, trabajos o reservas. 
//		Necesita el servicio ogAdmServer para su envio a la red.
// *********************************************************************************************************************************************************
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <unistd.h>
#include <time.h>
#include <ctype.h>
#include </usr/include/mysql/mysql.h>
#include <pthread.h>
#include "Database.h"
#include "ogAdmLib.h"



#define SERVIDORHIDRA	"127.0.0.1" // Servidor Hidra
#define PUERTO_DEFAULT	2003	// Puerto por defecto
#define USUARIO "usuhidra" // Usuario por defecto
#define PASGUOR "passusuhidra" // Password por defecto
#define DATASOURCE	"127.0.0.1" // Servidor de base de datos
#define CATALOG	"BDHidra" // Base de datos
#define CADENACONEXION "Provider=SQLOLEDB.1;Initial Catalog=%s;Data Source=%s;Packet Size=4096;"

#define LONGITUD_PARAMETROS 4000	// Longitud máxima de la información de la trama (parametros)

#define PROCESOS 0x01

#define ACCION_EXITOSA			"1" // Finalizada con exito
#define ACCION_FALLIDA			"2" // Finalizada con errores
#define ACCION_TERMINADA		"3" // Finalizada manualmente con indicacion de exito 
#define ACCION_ABORTADA		"4" // Finalizada manualmente con indicacion de errores 
#define ACCION_SINERRORES	"5" // Activa y sin ningún error
#define ACCION_CONERRORES	"6" // Activa y con algún error

#define ACCION_DETENIDA		"0" // Acción momentanemente parada
#define ACCION_INICIADA		"1" // Acción activa
#define ACCION_FINALIZADA	 	"2" // Accion finalizada

#define EJECUCION_COMANDO	0x0001 // Accion Comando
#define EJECUCION_TAREA		0x0002 // Accion Tarea
#define EJECUCION_TRABAJO	0x0003 // Accion Trabajo
#define EJECUCION_RESERVA	0x0004 // Reserva de aulas

#define CHKREGISTRY(f) if (!(f)) { return 0;}
#define HIVE HKEY_LOCAL_MACHINE				// Rama del registro donde estarán los parametros de conexión
#define BASEKEY "SOFTWARE\\Alonsoft"	// Key del registro para parametros de conexión
#define BASE "SOFTWARE\\Alonsoft\\Hidra"	// SubKey del registro para parametros de conexión

char servidorhidra[20];
int puerto;	// Puerto
char usuario[20];
char pasguor[20];
char datasource[20];
char catalog[50];
char cadenaconexion[1024];

FILE *FLog; // Fichero de log

BYTE	HEX_annos[9];
WORD	HEX_meses[13];
LONG	HEX_dias[32];
WORD	HEX_horas[13];
BYTE	HEX_diasemana[8];
BYTE	HEX_semanas[7]; 

WORD	dias_meses[13];

typedef struct{		// Estructura de la trama recibida
		char arroba;	// Caracter arroba siempre
		char identificador[9];	// Identificador de la trama, siempre JMMLCAMDJ:
		char ejecutor;	// Identificador del encargado de ejecutar la función ( 1= Servidor  2=Cliente rembo:
		char parametros[LONGITUD_PARAMETROS]; // Contenido de la trama (parámetros)
}TRAMA;

// Prototipo de funciones
void inicializa(void);
void Pausa(int);
int GestionaProgramacion(SYSTEMTIME);
int busca_accion(WORD ,WORD ,WORD ,WORD ,WORD,WORD );
int busca_reserva(WORD ,WORD ,WORD ,WORD ,WORD,WORD );

int DiadelaSemana(WORD ,WORD ,WORD );
bool bisiesto(WORD );
int SemanadelMes(int ,int );
int EjecutarTrabajo(int ,Database,char* );
int EjecutarTarea(int ,int ,int ,int ,Database,char* );
int EjecutarReserva(int,Database,char*);

int envia_comando(char*);
SOCKET AbreConexion(char*,int);
int	TomaConfiguracion(void);
int manda_trama(SOCKET ,TRAMA* );
