// ********************************************************************************************************
// Servicio: ogAdmAgent
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmAgent.h
// Descripción: Este fichero implementa el servicio agente del sistema. Revisa a intervalos
//				regulares la base de datos para comprobar si existen acciones programadas.
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
#include </usr/include/mysql/mysql.h>
#include "Database.h"
#include "ogAdmLib.h"

// ________________________________________________________________________________________________________
//
//	Valores hexadecimales para consultas
// ________________________________________________________________________________________________________

BYTE HEX_annos[]={0,0x01,0x02,0x04,0x08,0x10,0x20,0x40,0x80};
WORD HEX_meses[]={0,0x0001,0x0002,0x0004,0x0008,0x0010,0x0020,0x0040,0x0080,0x0100,0x0200,0x0400,0x0800};
int	HEX_dias[]={0,0x00000001,0x00000002,0x00000004,0x00000008,0x00000010,0x00000020,0x00000040,0x00000080,0x00000100,0x00000200,
		0x00000400,0x00000800,0x00001000,0x00002000,0x00004000,0x00008000,0x00010000,0x00020000,0x00040000,0x00080000,
		0x00100000,0x00200000,0x00400000,0x00800000,0x01000000,0x02000000,0x04000000,0x08000000,0x10000000,0x20000000,0x40000000};
WORD	HEX_horas[]={0x0001,0x0002,0x0004,0x0008,0x0010,0x0020,0x0040,0x0080,0x0100,0x0200,0x0400,0x0800 };
BYTE	HEX_diasemana[]={0,0x01,0x02,0x04,0x08,0x10,0x20,0x40};
BYTE	HEX_semanas[]={0,0x01,0x02,0x04,0x08,0x10,0x20};
WORD	dias_meses[]={0,31,28,31,30,31,30,31,31,30,31,30,31};

// ________________________________________________________________________________________________________
//
// Variables globales
// ________________________________________________________________________________________________________

char servidoradm[LONPRM];	// Dirección IP del servidor de administración
char puerto[LONPRM];	// Puerto de comunicación
char usuario[LONPRM];	// Usuario de acceso a la base de datos
char pasguor[LONPRM];	// Password del usuario
char datasource[LONPRM];	// Dirección IP del gestor de base de datos
char catalog[LONPRM];	// Nombre de la base de datos

int idprogramacion;
int tipoaccion,idtipoaccion;
char descriaccion[250];
char *cadenaid;
char *cadenaip;
char *cadenamac;
int concli;
int sesion;
int idcentro;

// ________________________________________________________________________________________________________
// Prototipo de funciones
// ________________________________________________________________________________________________________
BOOLEAN tomaConfiguracion(char*);
int diadelaSemana(WORD,WORD,WORD);
BOOLEAN bisiesto(WORD);
BOOLEAN buscaAccion(Database,WORD,WORD,WORD,WORD,WORD,WORD);
BOOLEAN ejecutarComando(Database,int,int );
BOOLEAN ejecutarProcedimiento(Database,int,int,int,char*);
BOOLEAN ejecutarTarea(Database,int, int);
BOOLEAN insertaComando(Database,int,char*,int,int,int,char*);
BOOLEAN EjecutarReserva(int,Database);
BOOLEAN enviaPeticion(int);
BOOLEAN RecopilaIpesMacs(Database,int,int,char *);
BOOLEAN RecorreCentro(Database, char*);
BOOLEAN RecorreGruposAulas(Database, char*);
BOOLEAN RecorreAulas(Database, char*);
BOOLEAN RecorreGruposOrdenadores(Database, char*);
BOOLEAN RecorreOrdenadores(Database, char*);

