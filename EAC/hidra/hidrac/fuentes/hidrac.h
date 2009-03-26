// ***************************************************************************************************************************************
// Aplicacin HIDRA (Gestin y Admistracin de aulas de informtica)
// Copyright 2003-2007 Jos Manuel Alonso. Todos los derechos reservados.
// Fichero: hidrax.h
//	Descripcin:
//		Fichero de cabecera de hidrax.cpp
// ***************************************************************************************************************************************
#include <sys/types.h>
#include <sys/wait.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <unistd.h>
#include <ctype.h>
#include <time.h>
#include <signal.h>

#define LEER		0
#define ESCRIBIR	1

#define LONGITUD_SCRIPTSALIDA 1024	// Longitud máima de la informacin devuelta por un script
#define LONGITUD_PARAMETROS 4048	// Longitud m?ima de la informacin de la trama (parametros)
#define LONGITUD_TRAMA LONGITUD_PARAMETROS+11	// Longitud m?ima de la trama completa
#define LONGITUD_CONFIGURACION 1024	// Longitud mxima de las configuraciones de particin
#define MAX_NUM_CSADDRS        20
#define MAX_INTERFACE_LIST     20
#define MAXCNX 5		// Mximos intentos de conexin al servidor HIDRA
#define MAXITEMS 100
#define MAXHTMLMNU 4000
#define MAXPARTICIONES 24
#define MAXINFOSO 5 // Numero máximo de nemonicos enla inforamción del S.O. de una partición 


#define PUERTOMINUSER 20000
#define PUERTOMAXUSER 60000

#define TRUE 1
#define FALSE 0

#define true 1
#define false 0

#define SOCKET_ERROR            (-1)
#define INVALID_SOCKET  (SOCKET)(~0)

typedef unsigned short  WORD;
typedef  int  BOOL;
typedef char  BYTE;
typedef  int  SOCKET;

typedef struct{		// EstructUra de la trama recibida
	char arroba;	// cabecera de la trama
	char identificador[9];	// identificador de la trama
	char ejecutor;	// ejecutor de la trama 1=el servidor rembo  2=el cliente rembo
	char parametros[LONGITUD_PARAMETROS]; // Contenido de la trama (par?etros)
}TRAMA;


char IPlocal[20];		// Ip local
char servidorhidra[20]; // IP servidor HIDRA
char Puerto[20]; 		// Puerto Unicode
int puerto;	// Puerto

char szPathFileCfg[128],szPathFileLog[128];

//___________________________________________________________________________________________________
// Variables y estructuras
//___________________________________________________________________________________________________

char cmdshell[512];
char msglog[512];
char filecmdshell[250];
char filemenu[250];
char fileitem[250];
char fileini[250];
char filecmd[250];

struct excepcion {
	int herror;
	char msg[250];
	char modulo[250];	
};
struct excepcion e;

// Nemnicos
int MsDos=1;
int Win98=2;
int Win2K=3;
int WinXP=4; 
int Linux=5;
	 	
BOOL PROCESO=true;			// Indicador de la actividad del proceso principal
BOOL OFFLINE;				// Indicador modo offline
BOOL ADMINISTRADO;			// Indicador modo administrado por el servidor HIDRA
BOOL CACHEEXISTS;			// Indica si existe cache
char HIDRACHERAIZ[250];		// Path al directorio de imgenes de HIDRA referido a la cach?
char HIDRASRVRAIZ[250];		// Path al directorio raiz HIDRA referido al servidor	
char HIDRACHEIMAGENES[250];	// Path del directorio hidra donde estn las imgenes (en la cach)
char HIDRASRVIMAGENES[250];	// Path del directorio hidra donde estn las imgenes (en el servidor)
char HIDRASRVCMD[250];		// Path del directorio hidra donde se depositan los comandos para el cliente rembo
char HIDRASCRIPTS[250];		// PAth al directorio donde estan los scripts
int HIDRAVER;				// Versin Hidra
int TPAR ;					// Tamao de la particin
	
// Socket
SOCKET sock;

struct s_CabMnu {
	char resolucion[2];			 // Resolucin de pantalla
	char titulo[250];						// Titulo del menu
	char coorx[4];					// Coordenada x
	char coory[4];					// Coordenada y
	char modalidad[2];		// modalidad ( numero de items por linea )
	char scoorx[4];				// Coordenada x // Menu privado
	char scoory[4];				// Coordenada y
	char smodalidad[250];		// modalidad ( numero de items por linea )
	char htmmenupub[64];	// Nombre del fichero que contiene el html del menu (público)
	char htmmenupri[64];		// Nombre del fichero que contiene el html del menu (privado)
} CabMnu;  // Estructura con los datos de los menús
	
BOOL swmnu=false; // Indicador de menu asignado
	
struct s_Item{
	char idaccionmenu[16];		// Identificador del item a ejecutar
	char urlimg[64];						// Nombre de la imagen de fonfo del botn
	char literal[250];						// Literal del item
	char tipoitem[2];					// Tipo de otem ( público o privado)
	char tipoaccion[2];				// Tipo de accin que ejecuta el item
} ;
	
struct s_Propiedades {
	char idordenador[16];				 // Identificador del ordenador
	char nombreordenador[64];		 // Nombre del ordenador
	char idaula[16];								// Identificador del aula
	char servidorhidra[64];				// IP  del servidor HUDRA
	char puerto[16];								// Puerto
	char iprepo[16];								// Direción IP repositorio	
	char puertorepo[16];								// Puerto	
	char idperfilhard[16];					// Identificador del perfil hardware
	char IPlocal[16];						// Ip local
} Propiedades;	  // Estructura con los datos del odenador
	
struct s_Particiones{
	char tiposo[64];				// Tipo de sistema operativo 
	char tipopart[16];			// Tipo de particin
	char tamapart[16];  		// Tamao de la particin
	char numpart[5];  		// Nmero de la particin
	char nombreso[64];    // Nombre del S.O.
};
	
struct s_Hardware{
	char nemonico[4];				// Tipo de sistema operativo 
	char tipo[45];			// Tipo de hardware
	char codigovalor[256];  		// Codigo o descripcion
}
;	
struct tiposo {
  char *tipopart;
  char *tiposo;
  char *nombreso;  
};	
struct tiposo tiposos[] = {
		{"BIGDOS", "MsDos","MsDos"},
		{"NTFS","Windows NT Platafom","Windows 2000,XP,2003"},
		{"FAT32","Windows","Windos 98,SE,Millenium"},			
		{"EXT","Extendida","Extendida"},
		{"EXT3","Linux","Linux"},
		{"EXT2","Linux","Linux"},	
		{"VFAT","VFAT","VFAT"},	
		{"CACHE","CACHE","CACHE"},			
		{"UNKNOWN","UNKNOWN","UNKNOWN"},			
		{"EMPTY","Libre","Libre"},
		{"LINUX-SWAP","","Linux-swap"}};
		
int ntiposo = sizeof (tiposos) / sizeof (struct tiposo);

struct s_Item tbMenu[MAXITEMS];			// Tabla con los items del menu
int contitems;			// Contador items del menu
	
BOOL PRCCMD;		// Indicador de comandos interactivos
BOOL CMDPTES;	// Indicador de comandos pendientes
	
//char modulo[64];	// Nombre de la funcin donde se produce el error

BOOL aut = false; // Variable para controlar el acceso al menu de administracion


char* tbErrores[]={"000-Se han generado errores. No se puede continuar la ejecucin de este mdulo",\
		"001-No hay memoria suficiente para el buffer",\
		"002-No se puede establecer conexin con el servidor Hidra",\
		"003-El fichero especificado no existe o bien no puede crearse o abrirse",\
		"004-Comando Error",\
		"005-El fichero est vacio",\
		"006-Error en la ejecucin del fichero autoexec",\
		"007-Error en la recuperacion del Menu principal",\
		"008-No hay espacio reservado para la cache en este disco",\
		"009-Ha ocurrido algn error generando el perfil software",\
		"010-IPlocal, NO se ha definido este parámetro",\
		"011-IPhidra, NO se ha definido este parámetro",\
		"012-Puerto, NO se ha definido este parámetro",\
		"013-NO existe fichero de configuración o contiene un error de sintaxis",\
		"014-Fallo de sintaxis en los parámetros: Debe especificar -f nombre_del_fichero_de_configuración_del_servicio",\
		"015-No existe Menu principal"};		
		

// Prototipos de funciones
void RaiseError(int,char*,char*);
int Nemonico(char*);
void LogError(char*, struct excepcion);
void Log(char*);
char * Buffer(int);
int Abre_conexion();
void Cierra_conexion();
int Cortesia();
int NoComandosPtes();
int inclusion_cliRMB();
int gestion_tramas(TRAMA *);
int envia_tramas(SOCKET,TRAMA *);
int recibe_tramas(SOCKET,TRAMA *);
int TCPWrite(SOCKET ,TRAMA* );
int TCPRead(SOCKET ,TRAMA* );
SOCKET TCPConnect(char *,char* );
BOOL RemoveFile(char *);
int BuildDiskImage(int , char *, char* ) ;

int CreateVirtualImage(char *, char* , char* ) ;
int Synchronize(char *, char* , char* ) ;
int FreeVirtualImage(char* ) ;

BOOL SacaMensaje(char* ,char* , int );

void INTROaFINCAD(char*);
char * toma_parametro(char* ,char *);

int Apagar(TRAMA*,TRAMA*);
int Reiniciar(TRAMA*,TRAMA*);
int Actualizar();
int RESPUESTA_inclusion_cliRMB(TRAMA*);
int RemboOffline(TRAMA*,TRAMA*);
int EjecutarScript(TRAMA*,TRAMA*);
int CrearPerfilSoftware(TRAMA*,TRAMA*);
int CrearPerfil(char* ,char* ,char* ,char* );
int Restaura_Imagen(char* ,char* ,char* ,char*);	
int RespuestaEjecucionComando(TRAMA* , TRAMA *, int );
int disponibilidadCOMANDOS(int);	
int CloseWindow(char *);
int Pantallazo(char* );
int PowerOff();
int  Muestra_Menu_Principal();
char* URLDecode(char* );
int CreateDir(char* );
int CrearIncremental(char* ,char* ,char* ,char*,char* );
int CrearSoftwareIncremental(TRAMA*,TRAMA*);
int RestaurarImagen(TRAMA*,TRAMA*);
int  reparticiona(int ,char* );
int cambiaFstab(char* ,char * ,char* );
int  DetectaConfiguracion();
int TomaConfiguracion(TRAMA *,TRAMA *);
int InventarioHardware(TRAMA *,TRAMA *);
int EjecutarItem(char* );
int GetCachePartitionSize(char*);
int CreateTree(char* );
int Arrancar(TRAMA *,TRAMA *);
SOCKET UDPConnect(char *);
int envia_comandos(SOCKET ,TRAMA* , char* ,char *);
char *recibe_comandos(SOCKET);
char* gestion_comandos(TRAMA*);
int ExisteFichero(char *);
char* Respuesta_ExisteFichero(TRAMA*);
char* Respuesta_EliminaFichero(TRAMA*);
char* Respuesta_LeeFicheroTexto(TRAMA*);
char * Desencriptar(char *);
char * Encriptar(char *);
int ejecutarscript (char *,char * ,char *);
int ExecShell(char*,char *);
int ParticionaryFormatear(TRAMA*,TRAMA*);
char* TomaNomSO(char*,int);
