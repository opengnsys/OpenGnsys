// *************************************************************************************************************
// Aplicacin HIDRA
// Copyright 2003-2005 Jos Manuel Alonso. Todos los derechos reservados.
// Fichero: hidrapxedhcp.h
// 
//	Descripcin:
//	 Fichero de cabecera de hidrapxedhcp.cpp
// **************************************************************************************************************
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
// _____________________________________________________________________________________________________________

#define PUERTODHCPORIGEN 67
#define PUERTODHCPDESTINO 68

#define PUERTOBOOTPORIGEN 4011
#define PUERTOBOOTPDESTINO 68

#define PUERTOTFTPORIGEN 69

#define PUERTOMINUSER 20000
#define PUERTOMAXUSER 60000

#define MAX_INTERFACE_LIST     20
#define MAX_NUM_CSADDRS        20

// __________________________________________________________________________________________________________
typedef unsigned long DWORD;
typedef unsigned short  WORD;
typedef  int  BOOL;
typedef char  BYTE;
typedef  int  SOCKET;
typedef  void* LPVOID;

#define SOCKET_ERROR            (-1)
#define INVALID_SOCKET  (SOCKET)(~0)

#define TRUE 1
#define FALSE 0

#define true 1
#define false 0
// __________________________________________________________________________________________________________

char szPathFileCfg[128],szPathFileLog[128];
FILE *FLog,*Fconfig;
char mensaje[1000];
// _____________________________________________________________________________________________________________
#define DHCP_UDP_OVERHEAD	(20 + 8 ) // IP header + UDP header
#define DHCP_SNAME_LEN		64
#define DHCP_FILE_LEN		128
#define DHCP_FIXED_NON_UDP	236
#define DHCP_FIXED_LEN		(DHCP_FIXED_NON_UDP + DHCP_UDP_OVERHEAD) // Longitud de la trama sin las opciones
#define DHCP_MTU_MAX		1500
#define DHCP_OPTION_LEN		(DHCP_MTU_MAX - DHCP_FIXED_LEN)

#define BOOTP_MIN_LEN		300
#define DHCP_MIN_LEN        548

struct dhcp_packet {
	unsigned char	op;			//	Message opcode
	unsigned char	htype;		//	Hardware addr type 
	unsigned char	hlen;		//	Hardware addr length
	unsigned char	hops;		//	Number of relay agent hops from client
	unsigned long	xid;		//	Transaction ID 
	unsigned short	secs;		//	Seconds since client started looking
	unsigned short	flags;		//	Flag bits
	struct in_addr	ciaddr;		//	Client IP address
	struct in_addr	yiaddr;		//	Client IP address 
	struct in_addr	siaddr;		//	IP address of next server
	struct in_addr	giaddr;		//	DHCP relay agent IP address
	unsigned char	chaddr [16];//	Client hardware address 
	char sname[DHCP_SNAME_LEN];	//	Server name
	char file[DHCP_FILE_LEN];	//	Boot filename
	unsigned char magiccookie[4];
	unsigned char options [DHCP_OPTION_LEN-4];	// Optional parameters.
};

// Estructura genrica de una opcin DHCP
struct dhcp_opcion {
	unsigned char codop;
	unsigned char tam;
	unsigned char dato;
};

// Cdigo de las distintas opciones DHCP
#define DHCP_PAD							0
#define DHCP_SUBNET_MASK					1
#define DHCP_TIME_OFFSET					2
#define DHCP_ROUTERS						3
#define DHCP_TIME_SERVERS					4
#define DHCP_NAME_SERVERS					5
#define DHCP_DOMAIN_NAME_SERVERS			6
#define DHCP_LOG_SERVERS					7
#define DHCP_COOKIE_SERVERS					8
#define DHCP_LPR_SERVERS					9
#define DHCP_IMPRESS_SERVERS				10
#define DHCP_RESOURCE_LOCATION_SERVERS		11
#define DHCP_HOST_NAME						12
#define DHCP_BOOT_SIZE						13
#define DHCP_MERIT_DUMP						14
#define DHCP_DOMAIN_NAME					15
#define DHCP_SWAP_SERVER					16
#define DHCP_ROOT_PATH						17
#define DHCP_EXTENSIONS_PATH				18
#define DHCP_IP_FORWARDING					19
#define DHCP_NON_LOCAL_SOURCE_ROUTING		20
#define DHCP_POLICY_FILTER					21
#define DHCP_MAX_DGRAM_REASSEMBLY			22
#define DHCP_DEFAULT_IP_TTL					23
#define DHCP_PATH_MTU_AGING_TIMEOUT			24
#define DHCP_PATH_MTU_PLATEAU_TABLE			25
#define DHCP_INTERFACE_MTU					26
#define DHCP_ALL_SUBNETS_LOCAL				27
#define DHCP_BROADCAST_ADDRESS				28
#define DHCP_PERFORM_MASK_DISCOVERY			29
#define DHCP_MASK_SUPPLIER					30
#define DHCP_ROUTER_DISCOVERY				31
#define DHCP_ROUTER_SOLICITATION_ADDRESS	32
#define DHCP_STATIC_ROUTES					33
#define DHCP_TRAILER_ENCAPSULATION			34
#define DHCP_ARP_CACHE_TIMEOUT				35
#define DHCP_IEEE802_3_ENCAPSULATION		36
#define DHCP_DEFAULT_TCP_TTL				37
#define DHCP_TCP_KEEPALIVE_INTERVAL			38
#define DHCP_TCP_KEEPALIVE_GARBAGE			39
#define DHCP_NIS_DOMAIN						40
#define DHCP_NIS_SERVERS					41
#define DHCP_NTP_SERVERS					42
#define DHCP_VENDOR_ENCAPSULATED_OPTIONS	43
#define DHCP_NETBIOS_NAME_SERVERS			44
#define DHCP_NETBIOS_DD_SERVER				45
#define DHCP_NETBIOS_NODE_TYPE				46
#define DHCP_NETBIOS_SCOPE					47
#define DHCP_FONT_SERVERS					48
#define DHCP_X_DISPLAY_MANAGER				49
#define DHCP_REQUESTED_ADDRESS				50
#define DHCP_LEASE_TIME						51
#define DHCP_OPTION_OVERLOAD				52
#define DHCP_MESSAGE_TYPE					53
#define DHCP_SERVER_IDENTIFIER				54
#define DHCP_PARAMETER_REQUEST_LIST			55
#define DHCP_MESSAGE						56
#define DHCP_MAX_MESSAGE_SIZE				57
#define DHCP_RENEWAL_TIME					58
#define DHCP_REBINDING_TIME					59
#define DHCP_CLASS_IDENTIFIER				60
#define DHCP_CLIENT_IDENTIFIER				61
#define DHCP_USER_CLASS_ID					77
#define DHCP_END							255

// DHCP message types.
#define DHCPDISCOVER	1
#define DHCPOFFER		2
#define DHCPREQUEST		3
#define DHCPDECLINE		4
#define DHCPACK			5
#define DHCPNAK			6
#define DHCPRELEASE		7
#define DHCPINFORM		8

// Estructura para trabajar en cada hebra con el cliente en cuestion
struct  TramaDhcpBootp{
	SOCKET sck;
	struct sockaddr_in cliente;
	socklen_t sockaddrsize;
	struct dhcp_packet pckDchp;
	char bdIP[16];
};
// _____________________________________________________________________________________________________________

#define MAXBLOCK 4096


// TFTP Cdigos de operacin.
#define TFTPRRQ		1	// Read request.
#define TFTPWRQ		2	// Write request
#define TFTPDATA	3	// Read or write the next block of data.
#define TFTPACK		4	// Confirnacin de bloque procesado
#define TFTPERROR	5	// Error message
#define TFTPOACK	6	// Option acknowledgment 

// Paquete TFTP genrico
struct tftp_packet
{
	WORD opcode;
	char buffer[MAXBLOCK+2];
};
// Paquete TFTP tipo ACK
struct tftppacket_ack 
{
	WORD opcode;
	WORD block;
	char buffer[MAXBLOCK];
};
// Paquete TFTP tipo ERROR packet
struct tftppacket_error 
{
	WORD opcode;
	WORD errorcode;
	char errormessage[508];
};
// Estructura para trabajar en cada hebra con el cliente en cuestion
struct  TramaTftp{
	SOCKET sck;
	struct sockaddr_in cliente;
	socklen_t sockaddrsize;
	struct tftp_packet pckTftp;
	FILE * fileboot;
	int  bloquesize;
	int tsize;
	int interval;
	int numblock;
	unsigned short currentopCode;
};
//______________________________________________________
static pthread_mutex_t guardia; // Controla acceso exclusivo de hebras 
//______________________________________________________
char IPlocal[20];
char usuario[20];
char pasguor[20];
char datasource[20];
char catalog[50];

// Prototipo de funciones
void RegistraLog(char *,int);
int TomaParametrosReg();

LPVOID ServicioDHCP(LPVOID);
LPVOID ServicioBOOTP(LPVOID);
LPVOID ServicioTFTP(LPVOID);
LPVOID GestionaServicioDHCP(LPVOID);
LPVOID GestionaServicioBOOTP(LPVOID);
LPVOID GestionaServicioTFTP(LPVOID);

int ClienteExistente(struct TramaDhcpBootp *,char*,int);
int OpcionesPresentes(unsigned char *);
unsigned char * BuscaOpcion(dhcp_packet* ,unsigned char );

int OpcionPXEClient(dhcp_packet* );
void ProcesaTramaClientePXE(struct TramaDhcpBootp* trmInfo);
void ProcesaTramaClienteDHCP(struct TramaDhcpBootp* trmInfo);
void ProcesaTramaClienteBOOTP(struct TramaDhcpBootp* trmInfo);
void ProcesaTramaClienteTFTP(struct TramaTftp * trmInfo);

void RellenaIPCLiente(struct TramaDhcpBootp*);
void RellenaIPServidor(struct TramaDhcpBootp*);
void dhcpDISCOVER_PXE(struct TramaDhcpBootp*);
void dhcpREQUEST_PXE(struct TramaDhcpBootp*);
void bootpREQUEST_PXE(struct TramaDhcpBootp*);

int PeticionFichero(struct TramaTftp*);

void AdjDHCPOFFER(unsigned char**,int*);
void AdjDHCPACK(unsigned char**,int*);
void AdjROUTERS(unsigned char** ,int*);
void AdjSUBNETMASK(unsigned char**,int*);
void AdjCLASSIDENTIFIER(unsigned char** ,int*);
void AdjSERVERIDENTIFIER(unsigned char** ,int*);
void AdjLEASETIME(unsigned char** ,int*);
void AdjBOOTSIZE(unsigned char** ,int*);

SOCKET TomaSocketUser();
struct tm * TomaHora();
int split_parametros(char **,char *, char *);
void duerme(unsigned int );
