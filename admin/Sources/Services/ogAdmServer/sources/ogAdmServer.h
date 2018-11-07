// ********************************************************************************************************
// Servicio: ogAdmServer
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmServer.h
// Descripción: Este fichero implementa el servicio de administración general del sistema
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
#include <stdbool.h>
#include </usr/include/mysql/mysql.h>
#include "Database.h"
#include "ogAdmLib.h"
// ________________________________________________________________________________________________________
// Variables globales
// ________________________________________________________________________________________________________
char servidoradm[LONPRM];	// Dirección IP del servidor de administración
char puerto[LONPRM];	// Puerto de comunicación
char usuario[LONPRM];	// Usuario de acceso a la base de datos
char pasguor[LONPRM];	// Password del usuario
char datasource[LONPRM];	// Dirección IP del gestor de base de datos
char catalog[LONPRM];	// Nombre de la base de datos
char aulaup[LONPRM];	// Conmutador para registro automático de clientes

typedef struct{ // Estructura usada para guardar información de los clientes
	char ip[LONIP]; // IP del cliente
	char estado[4]; // Tipo de Sistema Operativo en que se encuentra el cliente
	SOCKET sock; // Socket por el que se comunica
}SOCKETCL;
SOCKETCL tbsockets[MAXIMOS_CLIENTES];

bool swcSocket; // Switch para indicar si se debe cerrar el socket del cliente

// ________________________________________________________________________________________________________
// Prototipo de funciones
// ________________________________________________________________________________________________________

bool tomaConfiguracion(char*);
bool gestionaTrama(SOCKET*);
bool Sondeo(SOCKET*,TRAMA*);
bool respuestaSondeo(SOCKET *,TRAMA*);
bool InclusionClienteWinLnx(SOCKET*,TRAMA*);
bool InclusionCliente(SOCKET*,TRAMA*);
bool registraCliente(char *);

bool procesoInclusionClienteWinLnx(int socket, TRAMA*,int*,char*);
bool procesoInclusionCliente(int socket, TRAMA*);
bool clienteExistente(char *,int *);
bool clienteDisponible(char *,int *);
bool hayHueco(int *);
bool actualizaConfiguracion(Database , Table ,char* ,int);
bool AutoexecCliente(SOCKET *, TRAMA *);
bool recorreProcedimientos(Database ,char* ,FILE*,char*);

bool tomaRepositorio(Database ,Table ,char*,int*);
bool buscaComandos(char *,TRAMA *,int *);
bool DisponibilidadComandos(SOCKET*,TRAMA*);
bool respuestaEstandar(TRAMA *,char **,char **,char ** ,Database *,Table *);
bool respuestaConsola(int socket, TRAMA *,int);
bool enviaComando(TRAMA *ptrTrama,const char*);

bool Actualizar(SOCKET *, TRAMA* );
bool Purgar(SOCKET *, TRAMA* );

bool ConsolaRemota(SOCKET *,TRAMA*);
bool RESPUESTA_ConsolaRemota(SOCKET *,TRAMA*);
bool EcoConsola(SOCKET *,TRAMA*);

bool Comando(SOCKET *,TRAMA *);
bool RESPUESTA_Comando(SOCKET *,TRAMA *);

bool Arrancar(SOCKET *,TRAMA *);
bool Levanta(char*,char*,char*);
bool WakeUp(SOCKET *,char*,char*,char*);
void PasaHexBin(char *,char *);
bool RESPUESTA_Arrancar(SOCKET *,TRAMA*);
bool Apagar(SOCKET *,TRAMA *);
bool RESPUESTA_Apagar(SOCKET *,TRAMA *);
bool Reiniciar(SOCKET *,TRAMA *);
bool RESPUESTA_Reiniciar(SOCKET *,TRAMA *);
bool IniciarSesion(SOCKET *,TRAMA *);
bool RESPUESTA_IniciarSesion(SOCKET *,TRAMA *);
bool CrearImagen(SOCKET *,TRAMA *);
bool CrearImagenBasica(SOCKET *,TRAMA *);
bool CrearSoftIncremental(SOCKET *,TRAMA *);
bool RESPUESTA_CrearImagen(SOCKET *,TRAMA *);
bool RESPUESTA_CrearImagenBasica(SOCKET *,TRAMA *);
bool RESPUESTA_CrearSoftIncremental(SOCKET *,TRAMA *);
bool actualizaCreacionImagen(Database,Table,char*,char*,char*,char*,char*,char*);
bool actualizaCreacionSoftIncremental(Database,Table,char*,char*);
bool RestaurarImagen(SOCKET *,TRAMA *);
bool RestaurarImagenBasica(SOCKET *,TRAMA *);
bool RestaurarSoftIncremental(SOCKET *,TRAMA *);
bool RESPUESTA_RestaurarImagen(SOCKET *,TRAMA *);
bool RESPUESTA_RestaurarImagenBasica(SOCKET *,TRAMA *);
bool RESPUESTA_RestaurarSoftIncremental(SOCKET *,TRAMA *);
bool actualizaRestauracionImagen(Database,Table,char*,char*,char*,char*,char*);
bool Configurar(SOCKET *,TRAMA* );
bool RESPUESTA_Configurar(SOCKET *,TRAMA* );
bool actualizaConfigurar(Database , Table , char* );
bool InventarioHardware(SOCKET *,TRAMA *);
bool RESPUESTA_InventarioHardware(SOCKET *,TRAMA *);
bool actualizaHardware(Database, Table,char* ,char*,char*,char*);
bool cuestionPerfilHardware(Database,Table,char*,char*,int,char*,char*,int *,int);
bool actualizaSoftware(Database , Table , char* , char* , char*,char*,char*);
bool cuestionPerfilSoftware(Database, Table, char*, char*,int,int,char*,char*,char*,int *,int);

bool enviaArchivo(SOCKET *, TRAMA *);
bool recibeArchivo(SOCKET *, TRAMA *);
bool envioProgramacion(SOCKET *, TRAMA *);

int checkDato(Database,Table,char*,const char*,const char*,const char*);



