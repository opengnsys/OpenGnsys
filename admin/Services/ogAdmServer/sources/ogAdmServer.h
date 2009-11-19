//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmServer.cpp
//	Descripción:
//		Este módulo de la aplicación OpenGNSys implementa las comunicaciones con el Servidor.
// ****************************************************************************************************************************************************
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

#define LONGITUD_PARAMETROS 4000	// Longitud m�ima de la informaci� de la trama (parametros)
#define LONGITUD_TRAMA		LONGITUD_PARAMETROS+11	// Longitud m�ima de la trama completa
#define MAXCMD_PARAMETROS  200  // M�imo numero de par�etros de una trama de comandos
#define MAXIMOS_SOCKETS    4000 // M�imo numero de conexiones con ordenadores clientes
#define MAXIMOS_SRVRMB		200 // Maximo numero de servidores rembo
#define MAXLON_PARAMETROSIPH  3000 // M�ima longitud de un parametro iph

#define MAX_INTERFACE_LIST     20
#define MAX_NUM_CSADDRS        20

#define MAXHARDWARE 128 //	 mÁXIMOS ELEMENTOS HARDSWARE A DETECTAR
#define MAXSOFTWARE 2048 //	 mÁXIMOS ELEMENTOS SOFTWARE A DETECTAR

#define PUERTOMINUSER 40000
#define PUERTOMAXUSER 60000

#define LITAMBITO_CENTROS							"centros"
#define LITAMBITO_GRUPOSAULAS					"gruposaulas"
#define LITAMBITO_AULAS								"aulas"
#define LITAMBITO_GRUPOSORDENADORES	"gruposordenadores"
#define LITAMBITO_ORDENADORES				"ordenadores"

#define ACCION_EXITOSA			"1" // Finalizada con exito
#define ACCION_FALLIDA			"2" // Finalizada con errores
#define ACCION_TERMINADA	"3" // Finalizada manualmente con indicacion de exito 
#define ACCION_ABORTADA		"4" // Finalizada manualmente con indicacion de errores 
#define ACCION_SINERRORES	"5" // Activa y sin ningn error
#define ACCION_CONERRORES	"6" // Activa y con algn error

#define ACCION_DETENIDA		"0" // Acci� momentanemente parada
#define ACCION_INICIADA			"1" // Acci� activa
#define ACCION_FINALIZADA 	"2" // Accion finalizada

#define PROCESOS 0x01

#define EJECUCION_PROCEDIMIENTO	0x0000 // Accion Procedimiento
#define EJECUCION_COMANDO	0x0001 // Accion Comando
#define EJECUCION_TAREA		0x0002 // Accion Tarea
#define EJECUCION_TRABAJO		0x0003 // Accion Trabajo

#define EJECUTOR_servidorHIDRA	0x0001 // Ejecutor Servidor hidra
#define EJECUTOR_clienteREMBO	0x0002 // Ejecutor cliente rembo
#define EJECUTOR_servidorREMBO	0x0003 // Ejecutor Servidor rembo

#define CLIENTE_REMBO	"RMB" // Sistema operativo Rembo
#define CLIENTE_OCUPADO	"BSY" // Cliente ocupado
#define CLIENTE_APAGADO	"OFF" // Cliente apagado
#define CLIENTE_INICIANDO	"INI" // Cliente iniciando

#define AUTOINCORPORACION_OFF	0x0000 // Los ordenadores no se pueden dar de alta autm�icamente
#define AUTOINCORPORACION_ONA	0x0001 // Los ordenadores se pueden dar de alta autom�icamente si existe el aula
#define AUTOINCORPORACION_ONX	0x0002 // Los ordenadores se pueden dar de alta autom�icamente y si no existe el aula la crea

#define MAX_NUM_CSADDRS        20
#define MAX_INTERFACE_LIST     20

#define TRUE 1
#define FALSE 0

#define true 1
#define false 0

#define SOCKET_ERROR            (-1)
#define INVALID_SOCKET  (SOCKET)(~0)

// __________________________________________________________________________________________________________
typedef unsigned long DWORD;
typedef unsigned short  WORD;
typedef  int  BOOLEAN;
typedef char  BYTE;
typedef  int  SOCKET;
// __________________________________________________________________________________________________________

char szPathFileLog[128],szPathFileCfg[128],msglog[250];
FILE *FLog,*Fconfig;
char AulaUp[2];
int aulaup;	// Switch para permitir  que un ordenador se de de alta autom�icamente en un aula existenta
						// Valores:
						//	0: El ordenador No se da de alta autom�icamente en un aula
						//	1: El ordenador se da de alta en un aula si existe
						//	2: El ordenador se da de alta en un aula si existe y si no existe la crea para darse de alta

char IPlocal[20];		// Ip local
char servidorhidra[20]; 		// IP servidor HIDRA
char Puerto[20]; 		// Puerto Unicode
int puerto;	// Puerto
char usuario[20];
char pasguor[20];
char datasource[20];
char catalog[50];

typedef struct{		// Estructura de la trama recibida
		char arroba;	// Caracter arroba siempre
		char identificador[9];	// Identificador de la trama, siempre JMMLCAMDJ:
		char ejecutor;	// Identificador del encargado de ejecutar la funci� ( 1= Servidor  2=Cliente rembo:
		char parametros[LONGITUD_PARAMETROS]; // Contenido de la trama (par�etros)
}TRAMA;

struct s_socketCLRMB{ // Estructura usada para guardar informaci� de los clientes
	char ip[16]; // IP del cliente
	char estado[4]; // Tipo de sistema Operativo del cliente "RMB"=rembo,"W98"=windows98,"W2K"=windows 2000, etc
	SOCKET sock; // Socket por el que se comunica
	char ipsrvdhcp[16]; // IP del servidor dhcp
	char ipsrvrmb[16]; // IP del servidor rembo
};
struct s_socketCLRMB tbsockets[MAXIMOS_SOCKETS];

struct s_socketSRVRMB{ // Estructura usada para guardar informaci� de los servidores REMBO
	char ip[16]; // IP del servidor rembo
	int puertorepo;
	char ipes[MAXLON_PARAMETROSIPH]; // IP de los clientes rembo
	int swenv; // Switch de envio

};
struct s_socketSRVRMB tbsocketsSRVRMB[MAXIMOS_SRVRMB];

static pthread_mutex_t guardia; // Controla acceso exclusivo de hebras 

// Prototipo de funciones
void* GestionaConexion(void*);
void gestiona_comando(SOCKET s,TRAMA trama);
int manda_comando(SOCKET sock,char* parametros);
int manda_trama(SOCKET sock,TRAMA* trama);
int recibe_trama(SOCKET sock,TRAMA* trama);
int manda_trama_servidorrembo(char* ,char *,int);

SOCKET UDPConnect(char *);
int envia_comandos(SOCKET ,TRAMA* , char* ,int);


int hay_hueco(int *);
BOOLEAN cliente_existente(char *,int*);
int hay_huecoservidorrembo(int *);
BOOLEAN servidorrembo_existente(char *,int*);
BOOLEAN IgualIP(char*,char*);

void INTROaFINCAD(char* );
void FINCADaINTRO(char*,char*);

int cuenta_ipes(char*);
char * toma_parametro(const char*,char *);
char * corte_iph(char *);

int respuesta_cortesia(SOCKET );
int NoComandosPendientes(SOCKET);
int Coloca_estado(char *,const char *,SOCKET);
int actualiza_configuracion(Database , Table ,char* ,int,int ,char* );
int actualiza_hardware(Database , Table ,char* ,char* ,char*);
int actualiza_software(Database , Table ,char* ,char*,char*,char* ,char*);
int CuestionPerfilHardware(Database , Table ,int ,char* ,int *,int ,char*);
int CuestionPerfilSoftware(Database, Table ,int ,char* ,int *,int,char *,char*);

void TomaParticiones(char*, char* ,int );
int	Toma_menu(Database,Table,char*,int,int);
int RecuperaItem(SOCKET,char *);
int ComandosPendientes(SOCKET ,char *);
int procesaCOMANDOS(SOCKET ,char *);
int DisponibilidadComandos(SOCKET ,char *);

int InclusionCliente(SOCKET,char *);
int inclusion_srvRMB(char *,int);
int inclusion_REPO(SOCKET,char *);
int inclusion_cliWINLNX(SOCKET ,char *);

int Sondeo(SOCKET ,char *);
int Arrancar(char *);
int Actualizar(char *);
int FicheroOperador(char *);
int IconoItem(TRAMA*);
int Conmutar(char *);
int RenovarItems(char *);

SOCKET AbreConexion(char *,int);
void RegistraLog(const char *,int);

void PurgarTablaSockets(char *);
int borra_entrada(int);
int RESPUESTA_Arrancar(SOCKET ,char *);
int RESPUESTA_Apagar(SOCKET ,char *);
int RESPUESTA_RemboOffline(SOCKET ,char *);
int RESPUESTA_Reiniciar(SOCKET ,char *);
int RESPUESTA_Actualizar(SOCKET,char *);
int RESPUESTA_ExecShell(SOCKET ,char *);
int RespuestaEstandar(char *,char *,char *,char*,Database, Table);
int RESPUESTA_CrearPerfilSoftware(SOCKET ,char *);
int RESPUESTA_CrearSoftwareIncremental(SOCKET,char *);
int RESPUESTA_RestaurarImagen(SOCKET,char *);
int RESPUESTA_ParticionaryFormatear(SOCKET ,char *);
int RESPUESTA_Configurar(SOCKET ,char *);
int RESPUESTA_TomaConfiguracion(SOCKET ,char *);
int RESPUESTA_TomaHardware(SOCKET ,char *);
int RESPUESTA_TomaSoftware(SOCKET ,char *);

int	RESPUESTA_inclusionREPO(TRAMA*);

int Actualiza_ordenador_imagen(char *,const char *,char *,Database);

int busca_comandos(char* ,char*,char *,int *);
int InsertaNotificaciones(int,int,int,char *,Database);
int comprueba_resultados(int ,Database );
int comprueba_finalizada(int ,char *,Database );

void EnviaServidoresRembo(char*);
void DesmarcaServidoresRembo(void);
void MarcaServidoresRembo(char*,char*);

int EjecutarItem(SOCKET,char *);
BOOLEAN TomaIPServidorRembo(char*,int*);
 
void envia_tarea(char* );
int EjecutarTarea(int ,int ,int ,int , Database,char * );
int EjecutarTrabajo(int ,Database,char *  );
int cuestion_nuevoordenador(Database,Table ,int*,char *,char *,char *,char *,char*,char*,char*);
int alta_ordenador(Database db,Table tbl,int*,char *,char *,char*,int,int,int);
int Toma_idservidorres(Database ,Table ,char*,char*,int*,int*);

void cambiacarac(char *,char , char );
int TomaConfiguracion(char* );
int split_parametros(char **,char *, char *);
struct tm * TomaHora();
