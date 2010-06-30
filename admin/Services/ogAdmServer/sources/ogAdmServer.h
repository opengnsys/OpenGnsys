//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmServer.h
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
#include "ogAdmLib.h"

#define AUTOINCORPORACION_OFF	0x0000 // Los ordenadores no se pueden dar de alta autmáticamente
#define AUTOINCORPORACION_ONA	0x0001 // Los ordenadores se pueden dar de alta automáticamente si existe el aula
#define AUTOINCORPORACION_ONX	0x0002 // Los ordenadores se pueden dar de alta automáticamente y si no existe el aula la crea

char ecofile[512],msglog[512];
FILE *FLog,*Fconfig;
char AulaUp[2];
int aulaup;	// Switch para permitir  que un ordenador se de de alta automáticamente en un aula existenta
			// Valores:
			//	0: El ordenador No se da de alta automáticamente en un aula
			//	1: El ordenador se da de alta en un aula si existe
			//	2: El ordenador se da de alta en un aula si existe y si no existe la crea para darse de alta

char IPlocal[20]; // Ip local
char servidorhidra[20]; // IP servidor HIDRA
char Puerto[20]; // Puerto Unicode
int puerto; // Puerto
char usuario[20];
char pasguor[20];
char datasource[20];
char catalog[50];

int swcSocket;

struct s_socketCLRMB{ // Estructura usada para guardar información de los clientes
	char ip[16]; // IP del cliente
	char estado[4]; // Tipo de sistema Operativo del cliente "RMB"=rembo,"W98"=windows98,"W2K"=windows 2000, etc
	SOCKET sock; // Socket por el que se comunica
	char ipsrvdhcp[16]; // IP del servidor dhcp
	char ipsrvrmb[16]; // IP del servidor rembo
};
struct s_socketCLRMB tbsockets[MAXIMOS_SOCKETS];

struct s_socketSRVRMB{ // Estructura usada para guardar información de los servidores REMBO
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
int manda_trama_servidorrembo(char* ,char *,int);
SOCKET UDPConnect(char *);
int envia_comandos(SOCKET ,TRAMA* , char* ,int);
int hay_hueco(int *);
BOOLEAN cliente_existente(char *,int*);
int hay_huecoservidorrembo(int *);
BOOLEAN servidorrembo_existente(char *,int*);
char * corte_iph(char *);
char * escaparComillas(char*);
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
int Sondear(char *);
int EcoConsola(SOCKET ,char *);
int enviaEcoConsola(SOCKET ,const char *);
int Arrancar(char *);
int Actualizar(char *);
int FicheroOperador(char *);
int IconoItem(TRAMA*);
int Conmutar(char *);
int ConsolaRemota(char *);
int RenovarItems(char *);

void PurgarTablaSockets(char *);
int borra_entrada(int);
int RESPUESTA_Arrancar(SOCKET ,char *);
int RESPUESTA_Apagar(SOCKET ,char *);
int RESPUESTA_Reiniciar(SOCKET ,char *);
int RESPUESTA_IniciarSesion(SOCKET ,char *);
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
int Actualiza_ordenador_perfil(char *,char *, char*, Database);
int busca_comandos(char* ,char*,char *,int *);
int InsertaNotificaciones(int,int,int,char *,Database);
int comprueba_resultados(int ,Database );
int comprueba_finalizada(int ,char *,Database );

void EnviaServidoresRembo(char*,int);
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
int tomaIpRepoPort(char *,char *,char *);
void cambiacarac(char *,char , char );
int TomaConfiguracion(char* );

unsigned int TomaEnvio();
int recibeFichero(char *,char *,char *,char *);
