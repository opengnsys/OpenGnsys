

#define LONGITUD_PARAMETROS 4000	// Longitud mínima de la información de la trama (parametros)
#define LONGITUD_CABECERATRAMA	11 // Longitud mínima de la trama completa
#define LONGITUD_TRAMA LONGITUD_PARAMETROS+LONGITUD_CABECERATRAMA	// Longitud mínima de la trama completa

#define LEER		0
#define ESCRIBIR	1

#define TRUE 1
#define FALSE 0

#define true 1
#define false 0

#define SOCKET_ERROR            (-1)
#define INVALID_SOCKET  (SOCKET)(~0)
#define MAXCNX 5		// Mximos intentos de conexin al servidor HIDRA

#define PUERTOMINUSER 40000
#define PUERTOMAXUSER 60000

#define MAX_NUM_CSADDRS        20
#define MAX_INTERFACE_LIST     20

// Variables y estructuras

typedef struct{		// EstructUra de la trama recibida
	char arroba;	// cabecera de la trama
	char identificador[9];	// identificador de la trama
	char ejecutor;	// ejecutor de la trama 1=el servidor rembo  2=el cliente rembo
	char parametros[LONGITUD_PARAMETROS]; // Contenido de la trama (par?etros)
}TRAMA;

char szPathFileCfg[512];
char szPathFileLog[512];

typedef unsigned long DWORD;
typedef unsigned short  WORD;
typedef  int  BOOLEAN;
typedef char  BYTE;
typedef  int  SOCKET;

// Prototipos de funciones

void INTROaFINCAD(char* );
void FINCADaINTRO(char*,char*);
SOCKET AbreConexion(char *,int);
int cuenta_ipes(char*);
int IgualIP(char *,char *);
void RegistraLog(const char *,int);
struct tm * TomaHora();
char * toma_parametro(const char* ,char *);
int SplitParametros(char**,char*, char*);
int recibe_trama(SOCKET sock,TRAMA* trama);
char* Encriptar(char *);
char * Desencriptar(char *);

