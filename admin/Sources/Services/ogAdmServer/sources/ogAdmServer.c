// *******************************************************************************************************
// Servicio: ogAdmServer
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Marzo-2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ogAdmServer.cpp
// Descripción :Este fichero implementa el servicio de administración general del sistema
// *******************************************************************************************************
#include "ogAdmServer.h"
#include "ogAdmLib.c"
#include "dbi.h"
#include "list.h"
#include "schedule.h"
#include <ev.h>
#include <syslog.h>
#include <sys/ioctl.h>
#include <ifaddrs.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <jansson.h>
#include <time.h>

static char usuario[LONPRM]; // Usuario de acceso a la base de datos
static char pasguor[LONPRM]; // Password del usuario
static char datasource[LONPRM]; // Dirección IP del gestor de base de datos
static char catalog[LONPRM]; // Nombre de la base de datos
static char interface[LONPRM]; // Interface name
static char auth_token[LONPRM]; // API token

static struct og_dbi_config dbi_config = {
	.user		= usuario,
	.passwd		= pasguor,
	.host		= datasource,
	.database	= catalog,
};

//________________________________________________________________________________________________________
//	Función: tomaConfiguracion
//
//	Descripción:
//		Lee el fichero de configuración del servicio
//	Parámetros:
//		filecfg : Ruta completa al fichero de configuración
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error 
//________________________________________________________________________________________________________
static bool tomaConfiguracion(const char *filecfg)
{
	char buf[1024], *line;
	char *key, *value;
	FILE *fcfg;

	if (filecfg == NULL || strlen(filecfg) == 0) {
		syslog(LOG_ERR, "No configuration file has been specified\n");
		return false;
	}

	fcfg = fopen(filecfg, "rt");
	if (fcfg == NULL) {
		syslog(LOG_ERR, "Cannot open configuration file `%s'\n",
		       filecfg);
		return false;
	}

	servidoradm[0] = '\0'; //inicializar variables globales

	line = fgets(buf, sizeof(buf), fcfg);
	while (line != NULL) {
		const char *delim = "=";

		line[strlen(line) - 1] = '\0';

		key = strtok(line, delim);
		value = strtok(NULL, delim);

		if (!strcmp(StrToUpper(key), "SERVIDORADM"))
			snprintf(servidoradm, sizeof(servidoradm), "%s", value);
		else if (!strcmp(StrToUpper(key), "PUERTO"))
			snprintf(puerto, sizeof(puerto), "%s", value);
		else if (!strcmp(StrToUpper(key), "USUARIO"))
			snprintf(usuario, sizeof(usuario), "%s", value);
		else if (!strcmp(StrToUpper(key), "PASSWORD"))
			snprintf(pasguor, sizeof(pasguor), "%s", value);
		else if (!strcmp(StrToUpper(key), "DATASOURCE"))
			snprintf(datasource, sizeof(datasource), "%s", value);
		else if (!strcmp(StrToUpper(key), "CATALOG"))
			snprintf(catalog, sizeof(catalog), "%s", value);
		else if (!strcmp(StrToUpper(key), "INTERFACE"))
			snprintf(interface, sizeof(interface), "%s", value);
		else if (!strcmp(StrToUpper(key), "APITOKEN"))
			snprintf(auth_token, sizeof(auth_token), "%s", value);

		line = fgets(buf, sizeof(buf), fcfg);
	}

	fclose(fcfg);

	if (!servidoradm[0]) {
		syslog(LOG_ERR, "Missing SERVIDORADM in configuration file\n");
		return false;
	}
	if (!puerto[0]) {
		syslog(LOG_ERR, "Missing PUERTO in configuration file\n");
		return false;
	}
	if (!usuario[0]) {
		syslog(LOG_ERR, "Missing USUARIO in configuration file\n");
		return false;
	}
	if (!pasguor[0]) {
		syslog(LOG_ERR, "Missing PASSWORD in configuration file\n");
		return false;
	}
	if (!datasource[0]) {
		syslog(LOG_ERR, "Missing DATASOURCE in configuration file\n");
		return false;
	}
	if (!catalog[0]) {
		syslog(LOG_ERR, "Missing CATALOG in configuration file\n");
		return false;
	}
	if (!interface[0])
		syslog(LOG_ERR, "Missing INTERFACE in configuration file\n");

	return true;
}

enum og_client_state {
	OG_CLIENT_RECEIVING_HEADER	= 0,
	OG_CLIENT_RECEIVING_PAYLOAD,
	OG_CLIENT_PROCESSING_REQUEST,
};

#define OG_MSG_REQUEST_MAXLEN	65536
#define OG_CMD_MAXLEN		64

/* Shut down connection if there is no complete message after 10 seconds. */
#define OG_CLIENT_TIMEOUT	10

/* Agent client operation might take longer, shut down after 30 seconds. */
#define OG_AGENT_CLIENT_TIMEOUT	30

enum og_cmd_type {
	OG_CMD_UNSPEC,
	OG_CMD_WOL,
	OG_CMD_PROBE,
	OG_CMD_SHELL_RUN,
	OG_CMD_SESSION,
	OG_CMD_POWEROFF,
	OG_CMD_REFRESH,
	OG_CMD_REBOOT,
	OG_CMD_STOP,
	OG_CMD_HARDWARE,
	OG_CMD_SOFTWARE,
	OG_CMD_IMAGE_CREATE,
	OG_CMD_IMAGE_RESTORE,
	OG_CMD_SETUP,
	OG_CMD_RUN_SCHEDULE,
	OG_CMD_MAX
};

static LIST_HEAD(client_list);

enum og_client_status {
	OG_CLIENT_STATUS_OGLIVE,
	OG_CLIENT_STATUS_BUSY,
};

struct og_client {
	struct list_head	list;
	struct ev_io		io;
	struct ev_timer		timer;
	struct sockaddr_in	addr;
	enum og_client_state	state;
	char			buf[OG_MSG_REQUEST_MAXLEN];
	unsigned int		buf_len;
	unsigned int		msg_len;
	int			keepalive_idx;
	bool			rest;
	bool			agent;
	int			content_length;
	char			auth_token[64];
	enum og_client_status	status;
	enum og_cmd_type	last_cmd;
	unsigned int		last_cmd_id;
};

static inline int og_client_socket(const struct og_client *cli)
{
	return cli->io.fd;
}

static inline const char *og_client_status(const struct og_client *cli)
{
	if (cli->last_cmd != OG_CMD_UNSPEC)
		return "BSY";

	switch (cli->status) {
	case OG_CLIENT_STATUS_BUSY:
		return "BSY";
	case OG_CLIENT_STATUS_OGLIVE:
		return "OPG";
	default:
		return "OFF";
	}
}

// ________________________________________________________________________________________________________
// Función: clienteDisponible
//
//	Descripción:
//		Comprueba la disponibilidad del cliente para recibir comandos interactivos
//	Parametros:
//		- ip : La ip del cliente a buscar
//		- idx: (Salida)  Indice que ocupa el cliente, de estar ya registrado
//	Devuelve:
//		true: Si el cliente está disponible
//		false: En caso contrario
// ________________________________________________________________________________________________________
bool clienteDisponible(char *ip, int* idx)
{
	int estado;

	if (clienteExistente(ip, idx)) {
		estado = strcmp(tbsockets[*idx].estado, CLIENTE_OCUPADO); // Cliente ocupado
		if (estado == 0)
			return false;

		estado = strcmp(tbsockets[*idx].estado, CLIENTE_APAGADO); // Cliente apagado
		if (estado == 0)
			return false;

		estado = strcmp(tbsockets[*idx].estado, CLIENTE_INICIANDO); // Cliente en proceso de inclusión
		if (estado == 0)
			return false;

		return true; // En caso contrario el cliente está disponible
	}
	return false; // Cliente no está registrado en el sistema
}
// ________________________________________________________________________________________________________
// Función: clienteExistente
//
//	Descripción:
//		Comprueba si el cliente está registrado en la tabla de socket del sistema
//	Parametros:
//		- ip : La ip del cliente a buscar
//		- idx:(Salida)  Indice que ocupa el cliente, de estar ya registrado
//	Devuelve:
//		true: Si el cliente está registrado
//		false: En caso contrario
// ________________________________________________________________________________________________________
bool clienteExistente(char *ip, int* idx)
{
	int i;
	for (i = 0; i < MAXIMOS_CLIENTES; i++) {
		if (contieneIP(ip, tbsockets[i].ip)) { // Si existe la IP en la cadena
			*idx = i;
			return true;
		}
	}
	return false;
}
// ________________________________________________________________________________________________________
// Función: actualizaConfiguracion
//
//	Descripción:
//		Esta función actualiza la base de datos con la configuracion de particiones de un cliente
//	Parámetros:
//		- db: Objeto base de datos (ya operativo)
//		- tbl: Objeto tabla
//		- cfg: cadena con una Configuración
//		- ido: Identificador del ordenador cliente
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
//	Especificaciones:
//		Los parametros de la configuración son:
//			par= Número de partición
//			cpt= Codigo o tipo de partición
//			sfi= Sistema de ficheros que está implementado en la partición
//			soi= Nombre del sistema de ficheros instalado en la partición
//			tam= Tamaño de la partición
// ________________________________________________________________________________________________________
bool actualizaConfiguracion(struct og_dbi *dbi, char *cfg, int ido)
{
	int lon, p, c,i, dato, swu, idsoi, idsfi,k;
	char *ptrPar[MAXPAR], *ptrCfg[7], *ptrDual[2], tbPar[LONSTD];
	char *ser, *disk, *par, *cpt, *sfi, *soi, *tam, *uso; // Parametros de configuración.
	dbi_result result, result_update;
	const char *msglog;

	lon = 0;
	p = splitCadena(ptrPar, cfg, '\n');
	for (i = 0; i < p; i++) {
		c = splitCadena(ptrCfg, ptrPar[i], '\t');

		// Si la 1ª línea solo incluye el número de serie del equipo; actualizar BD.
		if (i == 0 && c == 1) {
			splitCadena(ptrDual, ptrCfg[0], '=');
			ser = ptrDual[1];
			if (strlen(ser) > 0) {
				// Solo actualizar si número de serie no existía.
				result = dbi_conn_queryf(dbi->conn,
						"UPDATE ordenadores SET numserie='%s'"
						" WHERE idordenador=%d AND numserie IS NULL",
						ser, ido);
				if (!result) {
					dbi_conn_error(dbi->conn, &msglog);
					syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
					       __func__, __LINE__, msglog);
					return false;
				}
				dbi_result_free(result);
			}
			continue;
		}

		// Distribución de particionado.
		disk = par = cpt = sfi = soi = tam = uso = NULL;

		splitCadena(ptrDual, ptrCfg[0], '=');
		disk = ptrDual[1]; // Número de disco

		splitCadena(ptrDual, ptrCfg[1], '=');
		par = ptrDual[1]; // Número de partición

		k=splitCadena(ptrDual, ptrCfg[2], '=');
		if(k==2){
			cpt = ptrDual[1]; // Código de partición
		}else{
			cpt = (char*)"0";
		}

		k=splitCadena(ptrDual, ptrCfg[3], '=');
		if(k==2){
			sfi = ptrDual[1]; // Sistema de ficheros
			/* Comprueba existencia del s0xistema de ficheros instalado */
			idsfi = checkDato(dbi, sfi, "sistemasficheros", "descripcion","idsistemafichero");
		}
		else
			idsfi=0;

		k=splitCadena(ptrDual, ptrCfg[4], '=');
		if(k==2){ // Sistema operativo detecdtado
			soi = ptrDual[1]; // Nombre del S.O. instalado
			/* Comprueba existencia del sistema operativo instalado */
			idsoi = checkDato(dbi, soi, "nombresos", "nombreso", "idnombreso");
		}
		else
			idsoi=0;

		splitCadena(ptrDual, ptrCfg[5], '=');
		tam = ptrDual[1]; // Tamaño de la partición

		splitCadena(ptrDual, ptrCfg[6], '=');
		uso = ptrDual[1]; // Porcentaje de uso del S.F.

		lon += sprintf(tbPar + lon, "(%s, %s),", disk, par);

		result = dbi_conn_queryf(dbi->conn,
				"SELECT numdisk, numpar, tamano, uso, idsistemafichero, idnombreso"
				"  FROM ordenadores_particiones"
				" WHERE idordenador=%d AND numdisk=%s AND numpar=%s",
				ido, disk, par);
		if (!result) {
			dbi_conn_error(dbi->conn, &msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}
		if (!dbi_result_next_row(result)) {
			result_update = dbi_conn_queryf(dbi->conn,
					"INSERT INTO ordenadores_particiones(idordenador,numdisk,numpar,codpar,tamano,uso,idsistemafichero,idnombreso,idimagen)"
					" VALUES(%d,%s,%s,0x%s,%s,%s,%d,%d,0)",
					ido, disk, par, cpt, tam, uso, idsfi, idsoi);
			if (!result_update) {
				dbi_conn_error(dbi->conn, &msglog);
				syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
				       __func__, __LINE__, msglog);
				return false;
			}
			dbi_result_free(result_update);

		} else { // Existe el registro
			swu = true; // Se supone que algún dato ha cambiado

			dato = dbi_result_get_uint(result, "tamano");
			if (atoi(tam) == dato) {// Parámetro tamaño igual al almacenado
				dato = dbi_result_get_uint(result, "idsistemafichero");
				if (idsfi == dato) {// Parámetro sistema de fichero igual al almacenado
					dato = dbi_result_get_uint(result, "idnombreso");
					if (idsoi == dato) {// Parámetro sistema de fichero distinto al almacenado
						swu = false; // Todos los parámetros de la partición son iguales, no se actualiza
					}
				}
			}
			if (swu) { // Hay que actualizar los parámetros de la partición
				result_update = dbi_conn_queryf(dbi->conn,
					"UPDATE ordenadores_particiones SET "
					" codpar=0x%s,"
					" tamano=%s,"
					" uso=%s,"
					" idsistemafichero=%d,"
					" idnombreso=%d,"
					" idimagen=0,"
					" idperfilsoft=0,"
					" fechadespliegue=NULL"
					" WHERE idordenador=%d AND numdisk=%s AND numpar=%s",
					cpt, tam, uso, idsfi, idsoi, ido, disk, par);
			} else {  // Actualizar porcentaje de uso.
				result_update = dbi_conn_queryf(dbi->conn,
					"UPDATE ordenadores_particiones SET "
					" codpar=0x%s,"
					" uso=%s"
					" WHERE idordenador=%d AND numdisk=%s AND numpar=%s",
					cpt, uso, ido, disk, par);
			}
			if (!result_update) {
				dbi_conn_error(dbi->conn, &msglog);
				syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
				       __func__, __LINE__, msglog);
				return false;
			}

			dbi_result_free(result_update);
		}
		dbi_result_free(result);
	}
	lon += sprintf(tbPar + lon, "(0,0)");
	// Eliminar particiones almacenadas que ya no existen
	result_update = dbi_conn_queryf(dbi->conn,
		"DELETE FROM ordenadores_particiones WHERE idordenador=%d AND (numdisk, numpar) NOT IN (%s)",
			ido, tbPar);
	if (!result_update) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	dbi_result_free(result_update);

	return true;
}
// ________________________________________________________________________________________________________
// Función: checkDato
//
//	Descripción:
//		 Esta función comprueba si existe un dato en una tabla y si no es así lo incluye. devuelve en
//		cualquier caso el identificador del registro existenet o del insertado
//	Parámetros:
//		- db: Objeto base de datos (ya operativo)
//		- tbl: Objeto tabla
//		- dato: Dato
//		- tabla: Nombre de la tabla
//		- nomdato: Nombre del dato en la tabla
//		- nomidentificador: Nombre del identificador en la tabla
//	Devuelve:
//		El identificador del registro existente o el del insertado
//
//	Especificaciones:
//		En caso de producirse algún error se devuelve el valor 0
// ________________________________________________________________________________________________________

int checkDato(struct og_dbi *dbi, char *dato, const char *tabla,
		     const char *nomdato, const char *nomidentificador)
{
	const char *msglog;
	int identificador;
	dbi_result result;

	if (strlen(dato) == 0)
		return (0); // EL dato no tiene valor
	result = dbi_conn_queryf(dbi->conn,
			"SELECT %s FROM %s WHERE %s ='%s'", nomidentificador,
			tabla, nomdato, dato);

	// Ejecuta consulta
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return (0);
	}
	if (!dbi_result_next_row(result)) { //  Software NO existente
		dbi_result_free(result);

		result = dbi_conn_queryf(dbi->conn,
				"INSERT INTO %s (%s) VALUES('%s')", tabla, nomdato, dato);
		if (!result) {
			dbi_conn_error(dbi->conn, &msglog);
			og_info((char *)msglog);
			return (0);
		}
		// Recupera el identificador del software
		identificador = dbi_conn_sequence_last(dbi->conn, NULL);
	} else {
		identificador = dbi_result_get_uint(result, nomidentificador);
	}
	dbi_result_free(result);

	return (identificador);
}

struct og_task {
	uint32_t	task_id;
	uint32_t	procedure_id;
	uint32_t	command_id;
	uint32_t	center_id;
	uint32_t	schedule_id;
	uint32_t	type_scope;
	uint32_t	scope;
	const char	*filtered_scope;
	const char	*params;
};

static TRAMA *og_msg_alloc(char *data, unsigned int len);
static void og_msg_free(TRAMA *ptrTrama);

static bool og_send_cmd(char *ips_array[], int ips_array_len,
			const char *state, TRAMA *ptrTrama)
{
	int i, idx;

	for (i = 0; i < ips_array_len; i++) {
		if (clienteDisponible(ips_array[i], &idx)) { // Si el cliente puede recibir comandos
			int sock = tbsockets[idx].cli ? tbsockets[idx].cli->io.fd : -1;

			strcpy(tbsockets[idx].estado, state); // Actualiza el estado del cliente
			if (sock >= 0 && !mandaTrama(&sock, ptrTrama)) {
				syslog(LOG_ERR, "failed to send response to %s:%s\n",
				       ips_array[i], strerror(errno));
			}
		}
	}
	return true;
}

// ________________________________________________________________________________________________________
// Función: Levanta
//
//	Descripción:
//		Enciende ordenadores a través de la red cuyas macs se pasan como parámetro
//	Parámetros:
//		- iph: Cadena de direcciones ip separadas por ";"
//		- mac: Cadena de direcciones mac separadas por ";"
//		- mar: Método de arranque (1=Broadcast, 2=Unicast)
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________

bool Levanta(char *ptrIP[], char *ptrMacs[], int lon, char *mar)
{
	unsigned int on = 1;
	struct sockaddr_in local;
	int i, res;
	int s;

	/* Creación de socket para envío de magig packet */
	s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP);
	if (s < 0) {
		syslog(LOG_ERR, "cannot create socket for magic packet\n");
		return false;
	}
	res = setsockopt(s, SOL_SOCKET, SO_BROADCAST, (unsigned int *) &on,
			 sizeof(on));
	if (res < 0) {
		syslog(LOG_ERR, "cannot set broadcast socket\n");
		return false;
	}
	memset(&local, 0, sizeof(local));
	local.sin_family = AF_INET;
	local.sin_port = htons(PUERTO_WAKEUP);
	local.sin_addr.s_addr = htonl(INADDR_ANY);

	for (i = 0; i < lon; i++) {
		if (!WakeUp(s, ptrIP[i], ptrMacs[i], mar)) {
			syslog(LOG_ERR, "problem sending magic packet\n");
			close(s);
			return false;
		}
	}
	close(s);
	return true;
}

#define OG_WOL_SEQUENCE		6
#define OG_WOL_MACADDR_LEN	6
#define OG_WOL_REPEAT		16

struct wol_msg {
	char secuencia_FF[OG_WOL_SEQUENCE];
	char macbin[OG_WOL_REPEAT][OG_WOL_MACADDR_LEN];
};

static bool wake_up_broadcast(int sd, struct sockaddr_in *client,
			      const struct wol_msg *msg)
{
	struct sockaddr_in *broadcast_addr;
	struct ifaddrs *ifaddr, *ifa;
	int ret;

	if (getifaddrs(&ifaddr) < 0) {
		syslog(LOG_ERR, "cannot get list of addresses\n");
		return false;
	}

	client->sin_addr.s_addr = htonl(INADDR_BROADCAST);

	for (ifa = ifaddr; ifa != NULL; ifa = ifa->ifa_next) {
		if (ifa->ifa_addr == NULL ||
		    ifa->ifa_addr->sa_family != AF_INET ||
		    strcmp(ifa->ifa_name, interface) != 0)
			continue;

		broadcast_addr =
			(struct sockaddr_in *)ifa->ifa_ifu.ifu_broadaddr;
		client->sin_addr.s_addr = broadcast_addr->sin_addr.s_addr;
		break;
	}
	freeifaddrs(ifaddr);

	ret = sendto(sd, msg, sizeof(*msg), 0,
		     (struct sockaddr *)client, sizeof(*client));
	if (ret < 0) {
		syslog(LOG_ERR, "failed to send broadcast wol\n");
		return false;
	}

	return true;
}

static bool wake_up_unicast(int sd, struct sockaddr_in *client,
			    const struct wol_msg *msg,
			    const struct in_addr *addr)
{
	int ret;

	client->sin_addr.s_addr = addr->s_addr;

	ret = sendto(sd, msg, sizeof(*msg), 0,
		     (struct sockaddr *)client, sizeof(*client));
	if (ret < 0) {
		syslog(LOG_ERR, "failed to send unicast wol\n");
		return false;
	}

	return true;
}

enum wol_delivery_type {
	OG_WOL_BROADCAST = 1,
	OG_WOL_UNICAST = 2
};

//_____________________________________________________________________________________________________________
// Función: WakeUp
//
//	 Descripción:
//		Enciende el ordenador cuya MAC se pasa como parámetro
//	Parámetros:
//		- s : Socket para enviar trama magic packet
//		- iph : Cadena con la dirección ip
//		- mac : Cadena con la dirección mac en formato XXXXXXXXXXXX
//		- mar: Método de arranque (1=Broadcast, 2=Unicast)
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
//_____________________________________________________________________________________________________________
//
bool WakeUp(int s, char* iph, char *mac, char *mar)
{
	unsigned int macaddr[OG_WOL_MACADDR_LEN];
	char HDaddress_bin[OG_WOL_MACADDR_LEN];
	struct sockaddr_in WakeUpCliente;
	struct wol_msg Trama_WakeUp;
	struct in_addr addr;
	bool ret;
	int i;

	for (i = 0; i < 6; i++) // Primera secuencia de la trama Wake Up (0xFFFFFFFFFFFF)
		Trama_WakeUp.secuencia_FF[i] = 0xFF;

	sscanf(mac, "%02x%02x%02x%02x%02x%02x",
	       &macaddr[0], &macaddr[1], &macaddr[2],
	       &macaddr[3], &macaddr[4], &macaddr[5]);

	for (i = 0; i < 6; i++)
		HDaddress_bin[i] = (uint8_t)macaddr[i];

	for (i = 0; i < 16; i++) // Segunda secuencia de la trama Wake Up , repetir 16 veces su la MAC
		memcpy(&Trama_WakeUp.macbin[i][0], &HDaddress_bin, 6);

	/* Creación de socket del cliente que recibe la trama magic packet */
	WakeUpCliente.sin_family = AF_INET;
	WakeUpCliente.sin_port = htons((short) PUERTO_WAKEUP);

	switch (atoi(mar)) {
	case OG_WOL_BROADCAST:
		ret = wake_up_broadcast(s, &WakeUpCliente, &Trama_WakeUp);
		break;
	case OG_WOL_UNICAST:
		if (inet_aton(iph, &addr) < 0) {
			syslog(LOG_ERR, "bad IP address for unicast wol\n");
			ret = false;
			break;
		}
		ret = wake_up_unicast(s, &WakeUpCliente, &Trama_WakeUp, &addr);
		break;
	default:
		syslog(LOG_ERR, "unknown wol type\n");
		ret = false;
		break;
	}
	return ret;
}

// ________________________________________________________________________________________________________
// Función: actualizaCreacionImagen
//
//	Descripción:
//		Esta función actualiza la base de datos con el resultado de la creación de una imagen
//	Parámetros:
//		- db: Objeto base de datos (ya operativo)
//		- tbl: Objeto tabla
//		- idi: Identificador de la imagen
//		- dsk: Disco de donde se creó
//		- par: Partición de donde se creó
//		- cpt: Código de partición
//		- ipr: Ip del repositorio
//		- ido: Identificador del ordenador modelo
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool actualizaCreacionImagen(struct og_dbi *dbi, char *idi, char *dsk,
			     char *par, char *cpt, char *ipr, char *ido)
{
	const char *msglog;
	dbi_result result;
	int idr,ifs;

	/* Toma identificador del repositorio correspondiente al ordenador modelo */
	result = dbi_conn_queryf(dbi->conn,
			"SELECT repositorios.idrepositorio"
			"  FROM repositorios"
			"  LEFT JOIN ordenadores USING (idrepositorio)"
			" WHERE repositorios.ip='%s' AND ordenadores.idordenador=%s", ipr, ido);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!dbi_result_next_row(result)) {
		syslog(LOG_ERR,
		       "repository does not exist in database (%s:%d)\n",
		       __func__, __LINE__);
		dbi_result_free(result);
		return false;
	}
	idr = dbi_result_get_uint(result, "idrepositorio");
	dbi_result_free(result);

	/* Toma identificador del perfilsoftware */
	result = dbi_conn_queryf(dbi->conn,
			"SELECT idperfilsoft"
			"  FROM ordenadores_particiones"
			" WHERE idordenador=%s AND numdisk=%s AND numpar=%s", ido, dsk, par);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!dbi_result_next_row(result)) {
		syslog(LOG_ERR,
		       "software profile does not exist in database (%s:%d)\n",
		       __func__, __LINE__);
		dbi_result_free(result);
		return false;
	}
	ifs = dbi_result_get_uint(result, "idperfilsoft");
	dbi_result_free(result);

	/* Actualizar los datos de la imagen */
	result = dbi_conn_queryf(dbi->conn,
		"UPDATE imagenes"
		"   SET idordenador=%s, numdisk=%s, numpar=%s, codpar=%s,"
		"       idperfilsoft=%d, idrepositorio=%d,"
		"       fechacreacion=NOW(), revision=revision+1"
		" WHERE idimagen=%s", ido, dsk, par, cpt, ifs, idr, idi);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	dbi_result_free(result);

	/* Actualizar los datos en el cliente */
	result = dbi_conn_queryf(dbi->conn,
		"UPDATE ordenadores_particiones"
		"   SET idimagen=%s, revision=(SELECT revision FROM imagenes WHERE idimagen=%s),"
		"       fechadespliegue=NOW()"
		" WHERE idordenador=%s AND numdisk=%s AND numpar=%s",
		idi, idi, ido, dsk, par);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	dbi_result_free(result);

	return true;
}

// ________________________________________________________________________________________________________
// Función: actualizaRestauracionImagen
//
//	Descripción:
//		Esta función actualiza la base de datos con el resultado de la restauración de una imagen
//	Parámetros:
//		- db: Objeto base de datos (ya operativo)
//		- tbl: Objeto tabla
//		- idi: Identificador de la imagen
//		- dsk: Disco de donde se restauró
//		- par: Partición de donde se restauró
//		- ido: Identificador del cliente donde se restauró
//		- ifs: Identificador del perfil software contenido	en la imagen
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool actualizaRestauracionImagen(struct og_dbi *dbi, char *idi,
				 char *dsk, char *par, char *ido, char *ifs)
{
	const char *msglog;
	dbi_result result;

	/* Actualizar los datos de la imagen */
	result = dbi_conn_queryf(dbi->conn,
			"UPDATE ordenadores_particiones"
			"   SET idimagen=%s, idperfilsoft=%s, fechadespliegue=NOW(),"
			"       revision=(SELECT revision FROM imagenes WHERE idimagen=%s),"
			"       idnombreso=IFNULL((SELECT idnombreso FROM perfilessoft WHERE idperfilsoft=%s),0)"
			" WHERE idordenador=%s AND numdisk=%s AND numpar=%s", idi, ifs, idi, ifs, ido, dsk, par);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	dbi_result_free(result);

	return true;
}
// ________________________________________________________________________________________________________
// Función: actualizaHardware
//
//		Descripción:
//			Actualiza la base de datos con la configuracion hardware del cliente
//		Parámetros:
//			- db: Objeto base de datos (ya operativo)
//			- tbl: Objeto tabla
//			- hrd: cadena con el inventario hardware
//			- ido: Identificador del ordenador
//			- npc: Nombre del ordenador
//			- idc: Identificador del centro o Unidad organizativa
// ________________________________________________________________________________________________________
//
bool actualizaHardware(struct og_dbi *dbi, char *hrd, char *ido, char *npc,
		       char *idc)
{
	const char *msglog;
	int idtipohardware, idperfilhard;
	int lon, i, j, aux;
	bool retval;
	char *whard;
	int tbidhardware[MAXHARDWARE];
	char *tbHardware[MAXHARDWARE],*dualHardware[2], strInt[LONINT], *idhardwares;
	dbi_result result;

	/* Toma Centro (Unidad Organizativa) */
	result = dbi_conn_queryf(dbi->conn,
				 "SELECT idperfilhard FROM ordenadores WHERE idordenador=%s",
				 ido);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!dbi_result_next_row(result)) {
		syslog(LOG_ERR, "client does not exist in database (%s:%d)\n",
		       __func__, __LINE__);
		dbi_result_free(result);
		return false;
	}
	idperfilhard = dbi_result_get_uint(result, "idperfilhard");
	dbi_result_free(result);

	whard=escaparCadena(hrd); // Codificar comillas simples
	if(!whard)
		return false;
	/* Recorre componentes hardware*/
	lon = splitCadena(tbHardware, whard, '\n');
	if (lon > MAXHARDWARE)
		lon = MAXHARDWARE; // Limita el número de componentes hardware
	/*
	 for (i=0;i<lon;i++){
	 sprintf(msglog,"Linea de inventario: %s",tbHardware[i]);
	 RegistraLog(msglog,false);
	 }
	 */
	for (i = 0; i < lon; i++) {
		splitCadena(dualHardware, rTrim(tbHardware[i]), '=');
		//sprintf(msglog,"nemonico: %s",dualHardware[0]);
		//RegistraLog(msglog,false);
		//sprintf(msglog,"valor: %s",dualHardware[1]);
		//RegistraLog(msglog,false);
		result = dbi_conn_queryf(dbi->conn,
					 "SELECT idtipohardware,descripcion FROM tipohardwares WHERE nemonico='%s'",
					 dualHardware[0]);
		if (!result) {
			dbi_conn_error(dbi->conn, &msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}
		if (!dbi_result_next_row(result)) { //	Tipo de Hardware NO existente
			dbi_result_free(result);
			return false;
		} else { //  Tipo de Hardware Existe
			idtipohardware = dbi_result_get_uint(result, "idtipohardware");
			dbi_result_free(result);

			result = dbi_conn_queryf(dbi->conn,
						 "SELECT idhardware FROM hardwares WHERE idtipohardware=%d AND descripcion='%s'",
						 idtipohardware, dualHardware[1]);

			if (!result) {
				dbi_conn_error(dbi->conn, &msglog);
				syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
				       __func__, __LINE__, msglog);
				return false;
			}

			if (!dbi_result_next_row(result)) { //	Hardware NO existente
				dbi_result_free(result);
				result = dbi_conn_queryf(dbi->conn,
							"INSERT hardwares (idtipohardware,descripcion,idcentro,grupoid) "
							" VALUES(%d,'%s',%s,0)", idtipohardware,
						dualHardware[1], idc);
				if (!result) {
					dbi_conn_error(dbi->conn, &msglog);
					syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
					       __func__, __LINE__, msglog);
					return false;
				}

				// Recupera el identificador del hardware
				tbidhardware[i] = dbi_conn_sequence_last(dbi->conn, NULL);
			} else {
				tbidhardware[i] = dbi_result_get_uint(result, "idhardware");
			}
			dbi_result_free(result);
		}
	}
	// Ordena tabla de identificadores para cosultar si existe un pefil con esas especificaciones

	for (i = 0; i < lon - 1; i++) {
		for (j = i + 1; j < lon; j++) {
			if (tbidhardware[i] > tbidhardware[j]) {
				aux = tbidhardware[i];
				tbidhardware[i] = tbidhardware[j];
				tbidhardware[j] = aux;
			}
		}
	}
	/* Crea cadena de identificadores de componentes hardware separados por coma */
	sprintf(strInt, "%d", tbidhardware[lon - 1]); // Pasa a cadena el último identificador que es de mayor longitud
	aux = strlen(strInt); // Calcula longitud de cadena para reservar espacio a todos los perfiles
	idhardwares = reservaMemoria(sizeof(aux) * lon + lon);
	if (idhardwares == NULL) {
		syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
		return false;
	}
	aux = sprintf(idhardwares, "%d", tbidhardware[0]);
	for (i = 1; i < lon; i++)
		aux += sprintf(idhardwares + aux, ",%d", tbidhardware[i]);

	if (!cuestionPerfilHardware(dbi, idc, ido, idperfilhard, idhardwares,
			npc, tbidhardware, lon)) {
		syslog(LOG_ERR, "Problem updating client hardware\n");
		retval=false;
	}
	else {
		retval=true;
	}
	liberaMemoria(whard);
	liberaMemoria(idhardwares);
	return (retval);
}
// ________________________________________________________________________________________________________
// Función: cuestionPerfilHardware
//
//		Descripción:
//			Comprueba existencia de perfil hardware y actualización de éste para el ordenador
//		Parámetros:
//			- db: Objeto base de datos (ya operativo)
//			- tbl: Objeto tabla
//			- idc: Identificador de la Unidad organizativa donde se encuentra el cliente
//			- ido: Identificador del ordenador
//			- tbidhardware: Identificador del tipo de hardware
//			- con: Número de componentes detectados para configurar un el perfil hardware
//			- npc: Nombre del cliente
// ________________________________________________________________________________________________________
bool cuestionPerfilHardware(struct og_dbi *dbi, char *idc, char *ido,
		int idperfilhardware, char *idhardwares, char *npc, int *tbidhardware,
		int lon)
{
	const char *msglog;
	dbi_result result;
	int i;
	int nwidperfilhard;

	// Busca perfil hard del ordenador que contenga todos los componentes hardware encontrados
	result = dbi_conn_queryf(dbi->conn,
		"SELECT idperfilhard FROM"
		" (SELECT perfileshard_hardwares.idperfilhard as idperfilhard,"
		"	group_concat(cast(perfileshard_hardwares.idhardware AS char( 11) )"
		"	ORDER BY perfileshard_hardwares.idhardware SEPARATOR ',' ) AS idhardwares"
		" FROM	perfileshard_hardwares"
		" GROUP BY perfileshard_hardwares.idperfilhard) AS temp"
		" WHERE idhardwares LIKE '%s'", idhardwares);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!dbi_result_next_row(result)) {
		// No existe un perfil hardware con esos componentes de componentes hardware, lo crea
		dbi_result_free(result);
		result = dbi_conn_queryf(dbi->conn,
				"INSERT perfileshard  (descripcion,idcentro,grupoid)"
				" VALUES('Perfil hardware (%s) ',%s,0)", npc, idc);
		if (!result) {
			dbi_conn_error(dbi->conn, &msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}
		dbi_result_free(result);

		// Recupera el identificador del nuevo perfil hardware
		nwidperfilhard = dbi_conn_sequence_last(dbi->conn, NULL);

		// Crea la relación entre perfiles y componenetes hardware
		for (i = 0; i < lon; i++) {
			result = dbi_conn_queryf(dbi->conn,
					"INSERT perfileshard_hardwares  (idperfilhard,idhardware)"
						" VALUES(%d,%d)", nwidperfilhard, tbidhardware[i]);
			if (!result) {
				dbi_conn_error(dbi->conn, &msglog);
				syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
				       __func__, __LINE__, msglog);
				return false;
			}
			dbi_result_free(result);
		}
	} else { // Existe un perfil con todos esos componentes
		nwidperfilhard = dbi_result_get_uint(result, "idperfilhard");
		dbi_result_free(result);
	}
	if (idperfilhardware != nwidperfilhard) { // No coinciden los perfiles
		// Actualiza el identificador del perfil hardware del ordenador
		result = dbi_conn_queryf(dbi->conn,
			"UPDATE ordenadores SET idperfilhard=%d"
			" WHERE idordenador=%s", nwidperfilhard, ido);
		if (!result) {
			dbi_conn_error(dbi->conn, &msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}
		dbi_result_free(result);
	}
	/* Eliminar Relación de hardwares con Perfiles hardware que quedan húerfanos */
	result = dbi_conn_queryf(dbi->conn,
		"DELETE FROM perfileshard_hardwares WHERE idperfilhard IN "
		" (SELECT idperfilhard FROM perfileshard WHERE idperfilhard NOT IN"
		" (SELECT DISTINCT idperfilhard from ordenadores))");
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	dbi_result_free(result);

	/* Eliminar Perfiles hardware que quedan húerfanos */
	result = dbi_conn_queryf(dbi->conn,
			"DELETE FROM perfileshard WHERE idperfilhard NOT IN"
			" (SELECT DISTINCT idperfilhard FROM ordenadores)");
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	dbi_result_free(result);

	/* Eliminar Relación de hardwares con Perfiles hardware que quedan húerfanos */
	result = dbi_conn_queryf(dbi->conn,
			"DELETE FROM perfileshard_hardwares WHERE idperfilhard NOT IN"
			" (SELECT idperfilhard FROM perfileshard)");
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	dbi_result_free(result);

	return true;
}
// ________________________________________________________________________________________________________
// Función: actualizaSoftware
//
//	Descripción:
//		Actualiza la base de datos con la configuración software del cliente
//	Parámetros:
//		- db: Objeto base de datos (ya operativo)
//		- tbl: Objeto tabla
//		- sft: cadena con el inventario software
//		- par: Número de la partición
//		- ido: Identificador del ordenador del cliente en la tabla
//		- npc: Nombre del ordenador
//		- idc: Identificador del centro o Unidad organizativa
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
//
//	Versión 1.1.0: Se incluye el sistema operativo. Autora: Irina Gómez - ETSII Universidad Sevilla
// ________________________________________________________________________________________________________
bool actualizaSoftware(struct og_dbi *dbi, char *sft, char *par,char *ido,
		       char *npc, char *idc)
{
	int i, j, lon, aux, idperfilsoft, idnombreso;
	bool retval;
	char *wsft;
	int tbidsoftware[MAXSOFTWARE];
	char *tbSoftware[MAXSOFTWARE], strInt[LONINT], *idsoftwares;
	const char *msglog;
	dbi_result result;

	/* Toma Centro (Unidad Organizativa) y perfil software */
	result = dbi_conn_queryf(dbi->conn,
		"SELECT idperfilsoft,numpar"
		" FROM ordenadores_particiones"
		" WHERE idordenador=%s", ido);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	idperfilsoft = 0; // Por defecto se supone que el ordenador no tiene aún detectado el perfil software
	while (dbi_result_next_row(result)) {
		aux = dbi_result_get_uint(result, "numpar");
		if (aux == atoi(par)) { // Se encuentra la partición
			idperfilsoft = dbi_result_get_uint(result, "idperfilsoft");
			break;
		}
	}
	dbi_result_free(result);
	wsft=escaparCadena(sft); // Codificar comillas simples
	if(!wsft)
		return false;

	/* Recorre componentes software*/
	lon = splitCadena(tbSoftware, wsft, '\n');

	if (lon == 0)
		return true; // No hay lineas que procesar
	if (lon > MAXSOFTWARE)
		lon = MAXSOFTWARE; // Limita el número de componentes software

	idnombreso = 0;
	for (i = 0; i < lon; i++) {
		// Primera línea es el sistema operativo: se obtiene identificador
		if (i == 0) {
			idnombreso = checkDato(dbi, rTrim(tbSoftware[i]), "nombresos", "nombreso", "idnombreso");
			continue;
		}

		result = dbi_conn_queryf(dbi->conn,
				"SELECT idsoftware FROM softwares WHERE descripcion ='%s'",
				rTrim(tbSoftware[i]));
		if (!result) {
			dbi_conn_error(dbi->conn, &msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}

		if (!dbi_result_next_row(result)) {
			dbi_result_free(result);
			result = dbi_conn_queryf(dbi->conn,
						"INSERT INTO softwares (idtiposoftware,descripcion,idcentro,grupoid)"
						" VALUES(2,'%s',%s,0)", tbSoftware[i], idc);
			if (!result) { // Error al insertar
				dbi_conn_error(dbi->conn, &msglog);
				syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
				       __func__, __LINE__, msglog);
				return false;
			}

			// Recupera el identificador del software
			tbidsoftware[i] = dbi_conn_sequence_last(dbi->conn, NULL);
		} else {
			tbidsoftware[i] = dbi_result_get_uint(result, "idsoftware");
		}
		dbi_result_free(result);
	}

	// Ordena tabla de identificadores para cosultar si existe un pefil con esas especificaciones

	for (i = 0; i < lon - 1; i++) {
		for (j = i + 1; j < lon; j++) {
			if (tbidsoftware[i] > tbidsoftware[j]) {
				aux = tbidsoftware[i];
				tbidsoftware[i] = tbidsoftware[j];
				tbidsoftware[j] = aux;
			}
		}
	}
	/* Crea cadena de identificadores de componentes software separados por coma */
	sprintf(strInt, "%d", tbidsoftware[lon - 1]); // Pasa a cadena el último identificador que es de mayor longitud
	aux = strlen(strInt); // Calcula longitud de cadena para reservar espacio a todos los perfiles
	idsoftwares = reservaMemoria((sizeof(aux)+1) * lon + lon);
	if (idsoftwares == NULL) {
		syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
		return false;
	}
	aux = sprintf(idsoftwares, "%d", tbidsoftware[0]);
	for (i = 1; i < lon; i++)
		aux += sprintf(idsoftwares + aux, ",%d", tbidsoftware[i]);

	// Comprueba existencia de perfil software y actualización de éste para el ordenador
	if (!cuestionPerfilSoftware(dbi, idc, ido, idperfilsoft, idnombreso, idsoftwares,
			npc, par, tbidsoftware, lon)) {
		syslog(LOG_ERR, "cannot update software\n");
		og_info((char *)msglog);
		retval=false;
	}
	else {
		retval=true;
	}
	liberaMemoria(wsft);
	liberaMemoria(idsoftwares);
	return (retval);
}
// ________________________________________________________________________________________________________
// Función: CuestionPerfilSoftware
//
//	Parámetros:
//		- db: Objeto base de datos (ya operativo)
//		- tbl: Objeto tabla
//		- idcentro: Identificador del centro en la tabla
//		- ido: Identificador del ordenador del cliente en la tabla
//		- idnombreso: Identificador del sistema operativo
//		- idsoftwares: Cadena con los identificadores de componentes software separados por comas
//		- npc: Nombre del ordenador del cliente
//		- particion: Número de la partición
//		- tbidsoftware: Array con los identificadores de componentes software
//		- lon: Número de componentes
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
//
//	Versión 1.1.0: Se incluye el sistema operativo. Autora: Irina Gómez - ETSII Universidad Sevilla
//_________________________________________________________________________________________________________
bool cuestionPerfilSoftware(struct og_dbi *dbi, char *idc, char *ido,
			    int idperfilsoftware, int idnombreso,
			    char *idsoftwares, char *npc, char *par,
			    int *tbidsoftware, int lon)
{
	int i, nwidperfilsoft;
	const char *msglog;
	dbi_result result;

	// Busca perfil soft del ordenador que contenga todos los componentes software encontrados
	result = dbi_conn_queryf(dbi->conn,
		"SELECT idperfilsoft FROM"
		" (SELECT perfilessoft_softwares.idperfilsoft as idperfilsoft,"
		"	group_concat(cast(perfilessoft_softwares.idsoftware AS char( 11) )"
		"	ORDER BY perfilessoft_softwares.idsoftware SEPARATOR ',' ) AS idsoftwares"
		" FROM	perfilessoft_softwares"
		" GROUP BY perfilessoft_softwares.idperfilsoft) AS temp"
		" WHERE idsoftwares LIKE '%s'", idsoftwares);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!dbi_result_next_row(result)) { // No existe un perfil software con esos componentes de componentes software, lo crea
		dbi_result_free(result);
		result = dbi_conn_queryf(dbi->conn,
				"INSERT perfilessoft  (descripcion, idcentro, grupoid, idnombreso)"
				" VALUES('Perfil Software (%s, Part:%s) ',%s,0,%i)", npc, par, idc,idnombreso);
		if (!result) {
			dbi_conn_error(dbi->conn, &msglog);
			og_info((char *)msglog);
			return false;
		}

		dbi_result_free(result);
		// Recupera el identificador del nuevo perfil software
		nwidperfilsoft = dbi_conn_sequence_last(dbi->conn, NULL);

		// Crea la relación entre perfiles y componenetes software
		for (i = 0; i < lon; i++) {
			result = dbi_conn_queryf(dbi->conn,
						"INSERT perfilessoft_softwares (idperfilsoft,idsoftware)"
						" VALUES(%d,%d)", nwidperfilsoft, tbidsoftware[i]);
			if (!result) {
				dbi_conn_error(dbi->conn, &msglog);
				og_info((char *)msglog);
				return false;
			}
			dbi_result_free(result);
		}
	} else { // Existe un perfil con todos esos componentes
		nwidperfilsoft = dbi_result_get_uint(result, "idperfilsoft");
		dbi_result_free(result);
	}

	if (idperfilsoftware != nwidperfilsoft) { // No coinciden los perfiles
		// Actualiza el identificador del perfil software del ordenador
		result = dbi_conn_queryf(dbi->conn,
				"UPDATE ordenadores_particiones SET idperfilsoft=%d,idimagen=0"
				" WHERE idordenador=%s AND numpar=%s", nwidperfilsoft, ido, par);
		if (!result) { // Error al insertar
			dbi_conn_error(dbi->conn, &msglog);
			og_info((char *)msglog);
			return false;
		}
		dbi_result_free(result);
	}

	/* DEPURACIÓN DE PERFILES SOFTWARE */

	 /* Eliminar Relación de softwares con Perfiles software que quedan húerfanos */
	result = dbi_conn_queryf(dbi->conn,
		"DELETE FROM perfilessoft_softwares WHERE idperfilsoft IN "\
		" (SELECT idperfilsoft FROM perfilessoft WHERE idperfilsoft NOT IN"\
		" (SELECT DISTINCT idperfilsoft from ordenadores_particiones) AND idperfilsoft NOT IN"\
		" (SELECT DISTINCT idperfilsoft from imagenes))");
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		og_info((char *)msglog);
		return false;
	}
	dbi_result_free(result),
	/* Eliminar Perfiles software que quedan húerfanos */
	result = dbi_conn_queryf(dbi->conn,
		"DELETE FROM perfilessoft WHERE idperfilsoft NOT IN"
		" (SELECT DISTINCT idperfilsoft from ordenadores_particiones)"\
		" AND  idperfilsoft NOT IN"\
		" (SELECT DISTINCT idperfilsoft from imagenes)");
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		og_info((char *)msglog);
		return false;
	}
	dbi_result_free(result),

	/* Eliminar Relación de softwares con Perfiles software que quedan húerfanos */
	result = dbi_conn_queryf(dbi->conn,
			"DELETE FROM perfilessoft_softwares WHERE idperfilsoft NOT IN"
			" (SELECT idperfilsoft from perfilessoft)");
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		og_info((char *)msglog);
		return false;
	}
	dbi_result_free(result);

	return true;
}

static void og_client_release(struct ev_loop *loop, struct og_client *cli)
{
	if (cli->keepalive_idx >= 0) {
		syslog(LOG_DEBUG, "closing keepalive connection for %s:%hu in slot %d\n",
		       inet_ntoa(cli->addr.sin_addr),
		       ntohs(cli->addr.sin_port), cli->keepalive_idx);
		tbsockets[cli->keepalive_idx].cli = NULL;
	}

	list_del(&cli->list);
	ev_io_stop(loop, &cli->io);
	close(cli->io.fd);
	free(cli);
}

static void og_client_keepalive(struct ev_loop *loop, struct og_client *cli)
{
	struct og_client *old_cli;

	old_cli = tbsockets[cli->keepalive_idx].cli;
	if (old_cli && old_cli != cli) {
		syslog(LOG_DEBUG, "closing old keepalive connection for %s:%hu\n",
		       inet_ntoa(old_cli->addr.sin_addr),
		       ntohs(old_cli->addr.sin_port));

		og_client_release(loop, old_cli);
	}
	tbsockets[cli->keepalive_idx].cli = cli;
}

static void og_client_reset_state(struct og_client *cli)
{
	cli->state = OG_CLIENT_RECEIVING_HEADER;
	cli->buf_len = 0;
}

static TRAMA *og_msg_alloc(char *data, unsigned int len)
{
	TRAMA *ptrTrama;

	ptrTrama = (TRAMA *)reservaMemoria(sizeof(TRAMA));
	if (!ptrTrama) {
		syslog(LOG_ERR, "OOM\n");
		return NULL;
	}

	initParametros(ptrTrama, len);
	memcpy(ptrTrama, "@JMMLCAMDJ_MCDJ", LONGITUD_CABECERATRAMA);
	memcpy(ptrTrama->parametros, data, len);
	ptrTrama->lonprm = len;

	return ptrTrama;
}

static void og_msg_free(TRAMA *ptrTrama)
{
	liberaMemoria(ptrTrama->parametros);
	liberaMemoria(ptrTrama);
}

#define OG_CLIENTS_MAX	4096
#define OG_PARTITION_MAX 4

struct og_partition {
	const char	*disk;
	const char	*number;
	const char	*code;
	const char	*size;
	const char	*filesystem;
	const char	*format;
	const char	*os;
	const char	*used_size;
};

struct og_sync_params {
	const char	*sync;
	const char	*diff;
	const char	*remove;
	const char	*compress;
	const char	*cleanup;
	const char	*cache;
	const char	*cleanup_cache;
	const char	*remove_dst;
	const char	*diff_id;
	const char	*diff_name;
	const char	*path;
	const char	*method;
};

struct og_msg_params {
	const char	*ips_array[OG_CLIENTS_MAX];
	const char	*mac_array[OG_CLIENTS_MAX];
	unsigned int	ips_array_len;
	const char	*wol_type;
	char		run_cmd[4096];
	const char	*disk;
	const char	*partition;
	const char	*repository;
	const char	*name;
	const char	*id;
	const char	*code;
	const char	*type;
	const char	*profile;
	const char	*cache;
	const char	*cache_size;
	bool		echo;
	struct og_partition	partition_setup[OG_PARTITION_MAX];
	struct og_sync_params sync_setup;
	struct og_schedule_time time;
	const char	*task_id;
	uint64_t	flags;
};

#define OG_COMPUTER_NAME_MAXLEN	100

struct og_computer {
	unsigned int	id;
	unsigned int	center;
	unsigned int	room;
	char		name[OG_COMPUTER_NAME_MAXLEN + 1];
};

#define OG_REST_PARAM_ADDR			(1UL << 0)
#define OG_REST_PARAM_MAC			(1UL << 1)
#define OG_REST_PARAM_WOL_TYPE			(1UL << 2)
#define OG_REST_PARAM_RUN_CMD			(1UL << 3)
#define OG_REST_PARAM_DISK			(1UL << 4)
#define OG_REST_PARAM_PARTITION			(1UL << 5)
#define OG_REST_PARAM_REPO			(1UL << 6)
#define OG_REST_PARAM_NAME			(1UL << 7)
#define OG_REST_PARAM_ID			(1UL << 8)
#define OG_REST_PARAM_CODE			(1UL << 9)
#define OG_REST_PARAM_TYPE			(1UL << 10)
#define OG_REST_PARAM_PROFILE			(1UL << 11)
#define OG_REST_PARAM_CACHE			(1UL << 12)
#define OG_REST_PARAM_CACHE_SIZE		(1UL << 13)
#define OG_REST_PARAM_PART_0			(1UL << 14)
#define OG_REST_PARAM_PART_1			(1UL << 15)
#define OG_REST_PARAM_PART_2			(1UL << 16)
#define OG_REST_PARAM_PART_3			(1UL << 17)
#define OG_REST_PARAM_SYNC_SYNC			(1UL << 18)
#define OG_REST_PARAM_SYNC_DIFF			(1UL << 19)
#define OG_REST_PARAM_SYNC_REMOVE		(1UL << 20)
#define OG_REST_PARAM_SYNC_COMPRESS		(1UL << 21)
#define OG_REST_PARAM_SYNC_CLEANUP		(1UL << 22)
#define OG_REST_PARAM_SYNC_CACHE		(1UL << 23)
#define OG_REST_PARAM_SYNC_CLEANUP_CACHE	(1UL << 24)
#define OG_REST_PARAM_SYNC_REMOVE_DST		(1UL << 25)
#define OG_REST_PARAM_SYNC_DIFF_ID		(1UL << 26)
#define OG_REST_PARAM_SYNC_DIFF_NAME		(1UL << 27)
#define OG_REST_PARAM_SYNC_PATH			(1UL << 28)
#define OG_REST_PARAM_SYNC_METHOD		(1UL << 29)
#define OG_REST_PARAM_ECHO			(1UL << 30)
#define OG_REST_PARAM_TASK			(1UL << 31)
#define OG_REST_PARAM_TIME_YEARS		(1UL << 32)
#define OG_REST_PARAM_TIME_MONTHS		(1UL << 33)
#define OG_REST_PARAM_TIME_WEEKS		(1UL << 34)
#define OG_REST_PARAM_TIME_WEEK_DAYS		(1UL << 35)
#define OG_REST_PARAM_TIME_DAYS			(1UL << 36)
#define OG_REST_PARAM_TIME_HOURS		(1UL << 37)
#define OG_REST_PARAM_TIME_AM_PM		(1UL << 38)
#define OG_REST_PARAM_TIME_MINUTES		(1UL << 39)

enum og_rest_method {
	OG_METHOD_GET	= 0,
	OG_METHOD_POST,
	OG_METHOD_NO_HTTP
};

static struct og_client *og_client_find(const char *ip)
{
	struct og_client *client;
	struct in_addr addr;
	int res;

	res = inet_aton(ip, &addr);
	if (!res) {
		syslog(LOG_ERR, "Invalid IP string: %s\n", ip);
		return NULL;
	}

	list_for_each_entry(client, &client_list, list) {
		if (client->addr.sin_addr.s_addr == addr.s_addr && client->agent) {
			return client;
		}
	}

	return NULL;
}

static bool og_msg_params_validate(const struct og_msg_params *params,
				   const uint64_t flags)
{
	return (params->flags & flags) == flags;
}

static int og_json_parse_clients(json_t *element, struct og_msg_params *params)
{
	unsigned int i;
	json_t *k;

	if (json_typeof(element) != JSON_ARRAY)
		return -1;

	for (i = 0; i < json_array_size(element); i++) {
		k = json_array_get(element, i);
		if (json_typeof(k) != JSON_STRING)
			return -1;

		params->ips_array[params->ips_array_len++] =
			json_string_value(k);

		params->flags |= OG_REST_PARAM_ADDR;
	}

	return 0;
}

static int og_json_parse_string(json_t *element, const char **str)
{
	if (json_typeof(element) != JSON_STRING)
		return -1;

	*str = json_string_value(element);
	return 0;
}

static int og_json_parse_uint(json_t *element, uint32_t *integer)
{
	if (json_typeof(element) != JSON_INTEGER)
		return -1;

	*integer = json_integer_value(element);
	return 0;
}

static int og_json_parse_bool(json_t *element, bool *value)
{
	if (json_typeof(element) == JSON_TRUE)
		*value = true;
	else if (json_typeof(element) == JSON_FALSE)
		*value = false;
	else
		return -1;

	return 0;
}

static int og_json_parse_sync_params(json_t *element,
                                     struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "sync")) {
			err = og_json_parse_string(value, &params->sync_setup.sync);
			params->flags |= OG_REST_PARAM_SYNC_SYNC;
		} else if (!strcmp(key, "diff")) {
			err = og_json_parse_string(value, &params->sync_setup.diff);
			params->flags |= OG_REST_PARAM_SYNC_DIFF;
		} else if (!strcmp(key, "remove")) {
			err = og_json_parse_string(value, &params->sync_setup.remove);
			params->flags |= OG_REST_PARAM_SYNC_REMOVE;
		} else if (!strcmp(key, "compress")) {
			err = og_json_parse_string(value, &params->sync_setup.compress);
			params->flags |= OG_REST_PARAM_SYNC_COMPRESS;
		} else if (!strcmp(key, "cleanup")) {
			err = og_json_parse_string(value, &params->sync_setup.cleanup);
			params->flags |= OG_REST_PARAM_SYNC_CLEANUP;
		} else if (!strcmp(key, "cache")) {
			err = og_json_parse_string(value, &params->sync_setup.cache);
			params->flags |= OG_REST_PARAM_SYNC_CACHE;
		} else if (!strcmp(key, "cleanup_cache")) {
			err = og_json_parse_string(value, &params->sync_setup.cleanup_cache);
			params->flags |= OG_REST_PARAM_SYNC_CLEANUP_CACHE;
		} else if (!strcmp(key, "remove_dst")) {
			err = og_json_parse_string(value, &params->sync_setup.remove_dst);
			params->flags |= OG_REST_PARAM_SYNC_REMOVE_DST;
		} else if (!strcmp(key, "diff_id")) {
			err = og_json_parse_string(value, &params->sync_setup.diff_id);
			params->flags |= OG_REST_PARAM_SYNC_DIFF_ID;
		} else if (!strcmp(key, "diff_name")) {
			err = og_json_parse_string(value, &params->sync_setup.diff_name);
			params->flags |= OG_REST_PARAM_SYNC_DIFF_NAME;
		} else if (!strcmp(key, "path")) {
			err = og_json_parse_string(value, &params->sync_setup.path);
			params->flags |= OG_REST_PARAM_SYNC_PATH;
		} else if (!strcmp(key, "method")) {
			err = og_json_parse_string(value, &params->sync_setup.method);
			params->flags |= OG_REST_PARAM_SYNC_METHOD;
		}

		if (err != 0)
			return err;
	}
	return err;
}

#define OG_PARAM_PART_NUMBER			(1UL << 0)
#define OG_PARAM_PART_CODE			(1UL << 1)
#define OG_PARAM_PART_FILESYSTEM		(1UL << 2)
#define OG_PARAM_PART_SIZE			(1UL << 3)
#define OG_PARAM_PART_FORMAT			(1UL << 4)
#define OG_PARAM_PART_DISK			(1UL << 5)
#define OG_PARAM_PART_OS			(1UL << 6)
#define OG_PARAM_PART_USED_SIZE			(1UL << 7)

static int og_json_parse_partition(json_t *element,
				   struct og_partition *part,
				   uint64_t required_flags)
{
	uint64_t flags = 0UL;
	const char *key;
	json_t *value;
	int err = 0;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &part->number);
			flags |= OG_PARAM_PART_NUMBER;
		} else if (!strcmp(key, "code")) {
			err = og_json_parse_string(value, &part->code);
			flags |= OG_PARAM_PART_CODE;
		} else if (!strcmp(key, "filesystem")) {
			err = og_json_parse_string(value, &part->filesystem);
			flags |= OG_PARAM_PART_FILESYSTEM;
		} else if (!strcmp(key, "size")) {
			err = og_json_parse_string(value, &part->size);
			flags |= OG_PARAM_PART_SIZE;
		} else if (!strcmp(key, "format")) {
			err = og_json_parse_string(value, &part->format);
			flags |= OG_PARAM_PART_FORMAT;
		} else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &part->disk);
			flags |= OG_PARAM_PART_DISK;
		} else if (!strcmp(key, "os")) {
			err = og_json_parse_string(value, &part->os);
			flags |= OG_PARAM_PART_OS;
		} else if (!strcmp(key, "used_size")) {
			err = og_json_parse_string(value, &part->used_size);
			flags |= OG_PARAM_PART_USED_SIZE;
		}

		if (err < 0)
			return err;
	}

	if (flags != required_flags)
		return -1;

	return err;
}

static int og_json_parse_partition_setup(json_t *element,
					 struct og_msg_params *params)
{
	unsigned int i;
	json_t *k;

	if (json_typeof(element) != JSON_ARRAY)
		return -1;

	for (i = 0; i < json_array_size(element) && i < OG_PARTITION_MAX; ++i) {
		k = json_array_get(element, i);

		if (json_typeof(k) != JSON_OBJECT)
			return -1;

		if (og_json_parse_partition(k, &params->partition_setup[i],
					    OG_PARAM_PART_NUMBER |
					    OG_PARAM_PART_CODE |
					    OG_PARAM_PART_FILESYSTEM |
					    OG_PARAM_PART_SIZE |
					    OG_PARAM_PART_FORMAT) < 0)
			return -1;

		params->flags |= (OG_REST_PARAM_PART_0 << i);
	}
	return 0;
}

static int og_json_parse_time_params(json_t *element,
				     struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "years")) {
			err = og_json_parse_uint(value, &params->time.years);
			params->flags |= OG_REST_PARAM_TIME_YEARS;
		} else if (!strcmp(key, "months")) {
			err = og_json_parse_uint(value, &params->time.months);
			params->flags |= OG_REST_PARAM_TIME_MONTHS;
		} else if (!strcmp(key, "weeks")) {
			err = og_json_parse_uint(value, &params->time.weeks);
			params->flags |= OG_REST_PARAM_TIME_WEEKS;
		} else if (!strcmp(key, "week_days")) {
			err = og_json_parse_uint(value, &params->time.week_days);
			params->flags |= OG_REST_PARAM_TIME_WEEK_DAYS;
		} else if (!strcmp(key, "days")) {
			err = og_json_parse_uint(value, &params->time.days);
			params->flags |= OG_REST_PARAM_TIME_DAYS;
	        } else if (!strcmp(key, "hours")) {
			err = og_json_parse_uint(value, &params->time.hours);
			params->flags |= OG_REST_PARAM_TIME_HOURS;
		} else if (!strcmp(key, "am_pm")) {
			err = og_json_parse_uint(value, &params->time.am_pm);
			params->flags |= OG_REST_PARAM_TIME_AM_PM;
		} else if (!strcmp(key, "minutes")) {
			err = og_json_parse_uint(value, &params->time.minutes);
			params->flags |= OG_REST_PARAM_TIME_MINUTES;
		}
		if (err != 0)
			return err;
	}

	return err;
}

static const char *og_cmd_to_uri[OG_CMD_MAX] = {
	[OG_CMD_WOL]		= "wol",
	[OG_CMD_PROBE]		= "probe",
	[OG_CMD_SHELL_RUN]	= "shell/run",
	[OG_CMD_SESSION]	= "session",
	[OG_CMD_POWEROFF]	= "poweroff",
	[OG_CMD_REFRESH]	= "refresh",
	[OG_CMD_REBOOT]		= "reboot",
	[OG_CMD_STOP]		= "stop",
	[OG_CMD_HARDWARE]	= "hardware",
	[OG_CMD_SOFTWARE]	= "software",
	[OG_CMD_IMAGE_CREATE]	= "image/create",
	[OG_CMD_IMAGE_RESTORE]	= "image/restore",
	[OG_CMD_SETUP]		= "setup",
	[OG_CMD_RUN_SCHEDULE]	= "run/schedule",
};

static bool og_client_is_busy(const struct og_client *cli,
			      enum og_cmd_type type)
{
	switch (type) {
	case OG_CMD_REBOOT:
	case OG_CMD_POWEROFF:
	case OG_CMD_STOP:
		break;
	default:
		if (cli->last_cmd != OG_CMD_UNSPEC)
			return true;
		break;
	}

	return false;
}

static int og_send_request(enum og_rest_method method, enum og_cmd_type type,
			   const struct og_msg_params *params,
			   const json_t *data)
{
	const char *content_type = "Content-Type: application/json";
	char content [OG_MSG_REQUEST_MAXLEN - 700] = {};
	char buf[OG_MSG_REQUEST_MAXLEN] = {};
	unsigned int content_length;
	char method_str[5] = {};
	struct og_client *cli;
	const char *uri;
	unsigned int i;
	int client_sd;

	if (method == OG_METHOD_GET)
		snprintf(method_str, 5, "GET");
	else if (method == OG_METHOD_POST)
		snprintf(method_str, 5, "POST");
	else
		return -1;

	if (!data)
		content_length = 0;
	else
		content_length = json_dumpb(data, content,
					    OG_MSG_REQUEST_MAXLEN - 700,
					    JSON_COMPACT);

	uri = og_cmd_to_uri[type];
	snprintf(buf, OG_MSG_REQUEST_MAXLEN,
		 "%s /%s HTTP/1.1\r\nContent-Length: %d\r\n%s\r\n\r\n%s",
		 method_str, uri, content_length, content_type, content);

	for (i = 0; i < params->ips_array_len; i++) {
		cli = og_client_find(params->ips_array[i]);
		if (!cli)
			continue;

		if (og_client_is_busy(cli, type))
			continue;

		client_sd = cli->io.fd;
		if (client_sd < 0) {
			syslog(LOG_INFO, "Client %s not conected\n",
			       params->ips_array[i]);
			continue;
		}

		if (send(client_sd, buf, strlen(buf), 0) < 0)
			continue;

		cli->last_cmd = type;
	}

	return 0;
}

static int og_cmd_post_clients(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	return og_send_request(OG_METHOD_POST, OG_CMD_PROBE, params, NULL);
}

struct og_buffer {
	char 	*data;
	int	len;
};

static int og_json_dump_clients(const char *buffer, size_t size, void *data)
{
	struct og_buffer *og_buffer = (struct og_buffer *)data;

	memcpy(og_buffer->data + og_buffer->len, buffer, size);
	og_buffer->len += size;

	return 0;
}

static int og_cmd_get_clients(json_t *element, struct og_msg_params *params,
			      char *buffer_reply)
{
	json_t *root, *array, *addr, *state, *object;
	struct og_client *client;
	struct og_buffer og_buffer = {
		.data	= buffer_reply,
	};

	array = json_array();
	if (!array)
		return -1;

	list_for_each_entry(client, &client_list, list) {
		if (!client->agent)
			continue;

		object = json_object();
		if (!object) {
			json_decref(array);
			return -1;
		}
		addr = json_string(inet_ntoa(client->addr.sin_addr));
		if (!addr) {
			json_decref(object);
			json_decref(array);
			return -1;
		}
		json_object_set_new(object, "addr", addr);
		state = json_string(og_client_status(client));
		if (!state) {
			json_decref(object);
			json_decref(array);
			return -1;
		}
		json_object_set_new(object, "state", state);
		json_array_append_new(array, object);
	}
	root = json_pack("{s:o}", "clients", array);
	if (!root) {
		json_decref(array);
		return -1;
	}

	json_dump_callback(root, og_json_dump_clients, &og_buffer, 0);
	json_decref(root);

	return 0;
}

static int og_json_parse_target(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;

	if (json_typeof(element) != JSON_OBJECT) {
		return -1;
	}

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "addr")) {
			if (json_typeof(value) != JSON_STRING)
				return -1;

			params->ips_array[params->ips_array_len] =
				json_string_value(value);

			params->flags |= OG_REST_PARAM_ADDR;
		} else if (!strcmp(key, "mac")) {
			if (json_typeof(value) != JSON_STRING)
				return -1;

			params->mac_array[params->ips_array_len] =
				json_string_value(value);

			params->flags |= OG_REST_PARAM_MAC;
		}
	}

	return 0;
}

static int og_json_parse_targets(json_t *element, struct og_msg_params *params)
{
	unsigned int i;
	json_t *k;
	int err;

	if (json_typeof(element) != JSON_ARRAY)
		return -1;

	for (i = 0; i < json_array_size(element); i++) {
		k = json_array_get(element, i);

		if (json_typeof(k) != JSON_OBJECT)
			return -1;

		err = og_json_parse_target(k, params);
		if (err < 0)
			return err;

		params->ips_array_len++;
	}
	return 0;
}

static int og_json_parse_type(json_t *element, struct og_msg_params *params)
{
	const char *type;

	if (json_typeof(element) != JSON_STRING)
		return -1;

	params->wol_type = json_string_value(element);

	type = json_string_value(element);
	if (!strcmp(type, "unicast"))
		params->wol_type = "2";
	else if (!strcmp(type, "broadcast"))
		params->wol_type = "1";

	params->flags |= OG_REST_PARAM_WOL_TYPE;

	return 0;
}

static int og_cmd_wol(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients")) {
			err = og_json_parse_targets(value, params);
		} else if (!strcmp(key, "type")) {
			err = og_json_parse_type(value, params);
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_MAC |
					    OG_REST_PARAM_WOL_TYPE))
		return -1;

	if (!Levanta((char **)params->ips_array, (char **)params->mac_array,
		     params->ips_array_len, (char *)params->wol_type))
		return -1;

	return 0;
}

static int og_json_parse_run(json_t *element, struct og_msg_params *params)
{
	if (json_typeof(element) != JSON_STRING)
		return -1;

	snprintf(params->run_cmd, sizeof(params->run_cmd), "%s",
		 json_string_value(element));

	params->flags |= OG_REST_PARAM_RUN_CMD;

	return 0;
}

static int og_cmd_run_post(json_t *element, struct og_msg_params *params)
{
	json_t *value, *clients;
	const char *key;
	unsigned int i;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);
		else if (!strcmp(key, "run"))
			err = og_json_parse_run(value, params);
		else if (!strcmp(key, "echo")) {
			err = og_json_parse_bool(value, &params->echo);
			params->flags |= OG_REST_PARAM_ECHO;
                }

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_RUN_CMD |
					    OG_REST_PARAM_ECHO))
		return -1;

	clients = json_copy(element);
	json_object_del(clients, "clients");

	err = og_send_request(OG_METHOD_POST, OG_CMD_SHELL_RUN, params, clients);
	if (err < 0)
		return err;

	for (i = 0; i < params->ips_array_len; i++) {
		char filename[4096];
		FILE *f;

		sprintf(filename, "/tmp/_Seconsola_%s", params->ips_array[i]);
		f = fopen(filename, "wt");
		fclose(f);
	}

	return 0;
}

static int og_cmd_run_get(json_t *element, struct og_msg_params *params,
			  char *buffer_reply)
{
	struct og_buffer og_buffer = {
		.data	= buffer_reply,
	};
	json_t *root, *value, *array;
	const char *key;
	unsigned int i;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			return err;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	array = json_array();
	if (!array)
		return -1;

	for (i = 0; i < params->ips_array_len; i++) {
		json_t *object, *output, *addr;
		char data[4096] = {};
		char filename[4096];
		int fd, numbytes;

		sprintf(filename, "/tmp/_Seconsola_%s", params->ips_array[i]);

		fd = open(filename, O_RDONLY);
		if (!fd)
			return -1;

		numbytes = read(fd, data, sizeof(data));
		if (numbytes < 0) {
			close(fd);
			return -1;
		}
		data[sizeof(data) - 1] = '\0';
		close(fd);

		object = json_object();
		if (!object) {
			json_decref(array);
			return -1;
		}
		addr = json_string(params->ips_array[i]);
		if (!addr) {
			json_decref(object);
			json_decref(array);
			return -1;
		}
		json_object_set_new(object, "addr", addr);

		output = json_string(data);
		if (!output) {
			json_decref(object);
			json_decref(array);
			return -1;
		}
		json_object_set_new(object, "output", output);

		json_array_append_new(array, object);
	}

	root = json_pack("{s:o}", "clients", array);
	if (!root)
		return -1;

	json_dump_callback(root, og_json_dump_clients, &og_buffer, 0);
	json_decref(root);

	return 0;
}

static int og_cmd_session(json_t *element, struct og_msg_params *params)
{
	json_t *clients, *value;
	const char *key;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients")) {
			err = og_json_parse_clients(value, params);
		} else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		}

		if (err < 0)
			return err;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION))
		return -1;

	clients = json_copy(element);
	json_object_del(clients, "clients");

	return og_send_request(OG_METHOD_POST, OG_CMD_SESSION, params, clients);
}

static int og_cmd_poweroff(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	return og_send_request(OG_METHOD_POST, OG_CMD_POWEROFF, params, NULL);
}

static int og_cmd_refresh(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	return og_send_request(OG_METHOD_GET, OG_CMD_REFRESH, params, NULL);
}

static int og_cmd_reboot(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	return og_send_request(OG_METHOD_POST, OG_CMD_REBOOT, params, NULL);
}

static int og_cmd_stop(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	return og_send_request(OG_METHOD_POST, OG_CMD_STOP, params, NULL);
}

static int og_cmd_hardware(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	return og_send_request(OG_METHOD_GET, OG_CMD_HARDWARE, params, NULL);
}

static int og_cmd_software(json_t *element, struct og_msg_params *params)
{
	json_t *clients, *value;
	const char *key;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);
		else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		}
		else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION))
		return -1;

	clients = json_copy(element);
	json_object_del(clients, "clients");

	return og_send_request(OG_METHOD_POST, OG_CMD_SOFTWARE, params, clients);
}

static int og_cmd_create_image(json_t *element, struct og_msg_params *params)
{
	json_t *value, *clients;
	const char *key;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "repository")) {
			err = og_json_parse_string(value, &params->repository);
			params->flags |= OG_REST_PARAM_REPO;
		} else if (!strcmp(key, "clients")) {
			err = og_json_parse_clients(value, params);
		} else if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		} else if (!strcmp(key, "code")) {
			err = og_json_parse_string(value, &params->code);
			params->flags |= OG_REST_PARAM_CODE;
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION |
					    OG_REST_PARAM_CODE |
					    OG_REST_PARAM_ID |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_REPO))
		return -1;

	clients = json_copy(element);
	json_object_del(clients, "clients");

	return og_send_request(OG_METHOD_POST, OG_CMD_IMAGE_CREATE, params,
			       clients);
}

static int og_cmd_restore_image(json_t *element, struct og_msg_params *params)
{
	json_t *clients, *value;
	const char *key;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "repository")) {
			err = og_json_parse_string(value, &params->repository);
			params->flags |= OG_REST_PARAM_REPO;
		} else if (!strcmp(key, "clients")) {
			err = og_json_parse_clients(value, params);
		} else if (!strcmp(key, "type")) {
			err = og_json_parse_string(value, &params->type);
			params->flags |= OG_REST_PARAM_TYPE;
		} else if (!strcmp(key, "profile")) {
			err = og_json_parse_string(value, &params->profile);
			params->flags |= OG_REST_PARAM_PROFILE;
		} else if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_REPO |
					    OG_REST_PARAM_TYPE |
					    OG_REST_PARAM_PROFILE |
					    OG_REST_PARAM_ID))
		return -1;

	clients = json_copy(element);
	json_object_del(clients, "clients");

	return og_send_request(OG_METHOD_POST, OG_CMD_IMAGE_RESTORE, params,
			       clients);
}

static int og_cmd_setup(json_t *element, struct og_msg_params *params)
{
	json_t *value, *clients;
	const char *key;
	int err = 0;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients")) {
			err = og_json_parse_clients(value, params);
		} else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "cache")) {
			err = og_json_parse_string(value, &params->cache);
			params->flags |= OG_REST_PARAM_CACHE;
		} else if (!strcmp(key, "cache_size")) {
			err = og_json_parse_string(value, &params->cache_size);
			params->flags |= OG_REST_PARAM_CACHE_SIZE;
		} else if (!strcmp(key, "partition_setup")) {
			err = og_json_parse_partition_setup(value, params);
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_CACHE |
					    OG_REST_PARAM_CACHE_SIZE |
					    OG_REST_PARAM_PART_0 |
					    OG_REST_PARAM_PART_1 |
					    OG_REST_PARAM_PART_2 |
					    OG_REST_PARAM_PART_3))
		return -1;

	clients = json_copy(element);
	json_object_del(clients, "clients");

	return og_send_request(OG_METHOD_POST, OG_CMD_SETUP, params, clients);
}

static int og_cmd_run_schedule(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err = 0;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR))
		return -1;

	return og_send_request(OG_METHOD_GET, OG_CMD_RUN_SCHEDULE, params,
			       NULL);
}

static int og_cmd_create_basic_image(json_t *element, struct og_msg_params *params)
{
	char buf[4096] = {};
	int err = 0, len;
	const char *key;
	json_t *value;
	TRAMA *msg;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients")) {
			err = og_json_parse_clients(value, params);
		} else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		} else if (!strcmp(key, "code")) {
			err = og_json_parse_string(value, &params->code);
			params->flags |= OG_REST_PARAM_CODE;
		} else if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "repository")) {
			err = og_json_parse_string(value, &params->repository);
			params->flags |= OG_REST_PARAM_REPO;
		} else if (!strcmp(key, "sync_params")) {
			err = og_json_parse_sync_params(value, params);
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION |
					    OG_REST_PARAM_CODE |
					    OG_REST_PARAM_ID |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_REPO |
					    OG_REST_PARAM_SYNC_SYNC |
					    OG_REST_PARAM_SYNC_DIFF |
					    OG_REST_PARAM_SYNC_REMOVE |
					    OG_REST_PARAM_SYNC_COMPRESS |
					    OG_REST_PARAM_SYNC_CLEANUP |
					    OG_REST_PARAM_SYNC_CACHE |
					    OG_REST_PARAM_SYNC_CLEANUP_CACHE |
					    OG_REST_PARAM_SYNC_REMOVE_DST))
		return -1;

	len = snprintf(buf, sizeof(buf),
		       "nfn=CrearImagenBasica\rdsk=%s\rpar=%s\rcpt=%s\ridi=%s\r"
		       "nci=%s\ripr=%s\rrti=\rmsy=%s\rwhl=%s\reli=%s\rcmp=%s\rbpi=%s\r"
		       "cpc=%s\rbpc=%s\rnba=%s\r",
		       params->disk, params->partition, params->code, params->id,
		       params->name, params->repository, params->sync_setup.sync,
		       params->sync_setup.diff, params->sync_setup.remove,
		       params->sync_setup.compress, params->sync_setup.cleanup,
		       params->sync_setup.cache, params->sync_setup.cleanup_cache,
		       params->sync_setup.remove_dst);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
		    CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
}

static int og_cmd_create_incremental_image(json_t *element, struct og_msg_params *params)
{
	char buf[4096] = {};
	int err = 0, len;
	const char *key;
	json_t *value;
	TRAMA *msg;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients"))
			err = og_json_parse_clients(value, params);
		else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		} else if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "repository")) {
			err = og_json_parse_string(value, &params->repository);
			params->flags |= OG_REST_PARAM_REPO;
		} else if (!strcmp(key, "sync_params")) {
			err = og_json_parse_sync_params(value, params);
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION |
					    OG_REST_PARAM_ID |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_REPO |
					    OG_REST_PARAM_SYNC_SYNC |
					    OG_REST_PARAM_SYNC_PATH |
					    OG_REST_PARAM_SYNC_DIFF |
					    OG_REST_PARAM_SYNC_DIFF_ID |
					    OG_REST_PARAM_SYNC_DIFF_NAME |
					    OG_REST_PARAM_SYNC_REMOVE |
					    OG_REST_PARAM_SYNC_COMPRESS |
					    OG_REST_PARAM_SYNC_CLEANUP |
					    OG_REST_PARAM_SYNC_CACHE |
					    OG_REST_PARAM_SYNC_CLEANUP_CACHE |
					    OG_REST_PARAM_SYNC_REMOVE_DST))
		return -1;

	len = snprintf(buf, sizeof(buf),
		       "nfn=CrearSoftIncremental\rdsk=%s\rpar=%s\ridi=%s\rnci=%s\r"
		       "rti=%s\ripr=%s\ridf=%s\rncf=%s\rmsy=%s\rwhl=%s\reli=%s\rcmp=%s\r"
		       "bpi=%s\rcpc=%s\rbpc=%s\rnba=%s\r",
		       params->disk, params->partition, params->id, params->name,
		       params->sync_setup.path, params->repository, params->sync_setup.diff_id,
		       params->sync_setup.diff_name, params->sync_setup.sync,
		       params->sync_setup.diff, params->sync_setup.remove_dst,
		       params->sync_setup.compress, params->sync_setup.cleanup,
		       params->sync_setup.cache, params->sync_setup.cleanup_cache,
		       params->sync_setup.remove_dst);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
		    CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
}

static int og_cmd_restore_basic_image(json_t *element, struct og_msg_params *params)
{
	char buf[4096] = {};
	int err = 0, len;
	const char *key;
	json_t *value;
	TRAMA *msg;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients")) {
			err = og_json_parse_clients(value, params);
		} else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		} else if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "repository")) {
			err = og_json_parse_string(value, &params->repository);
			params->flags |= OG_REST_PARAM_REPO;
		} else if (!strcmp(key, "profile")) {
			err = og_json_parse_string(value, &params->profile);
			params->flags |= OG_REST_PARAM_PROFILE;
		} else if (!strcmp(key, "type")) {
			err = og_json_parse_string(value, &params->type);
			params->flags |= OG_REST_PARAM_TYPE;
		} else if (!strcmp(key, "sync_params")) {
			err = og_json_parse_sync_params(value, params);
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION |
					    OG_REST_PARAM_ID |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_REPO |
					    OG_REST_PARAM_PROFILE |
					    OG_REST_PARAM_TYPE |
					    OG_REST_PARAM_SYNC_PATH |
					    OG_REST_PARAM_SYNC_METHOD |
					    OG_REST_PARAM_SYNC_SYNC |
					    OG_REST_PARAM_SYNC_DIFF |
					    OG_REST_PARAM_SYNC_REMOVE |
					    OG_REST_PARAM_SYNC_COMPRESS |
					    OG_REST_PARAM_SYNC_CLEANUP |
					    OG_REST_PARAM_SYNC_CACHE |
					    OG_REST_PARAM_SYNC_CLEANUP_CACHE |
					    OG_REST_PARAM_SYNC_REMOVE_DST))
		return -1;

	len = snprintf(buf, sizeof(buf),
		       "nfn=RestaurarImagenBasica\rdsk=%s\rpar=%s\ridi=%s\rnci=%s\r"
			   "ipr=%s\rifs=%s\rrti=%s\rmet=%s\rmsy=%s\rtpt=%s\rwhl=%s\r"
			   "eli=%s\rcmp=%s\rbpi=%s\rcpc=%s\rbpc=%s\rnba=%s\r",
		       params->disk, params->partition, params->id, params->name,
			   params->repository, params->profile, params->sync_setup.path,
			   params->sync_setup.method, params->sync_setup.sync, params->type,
			   params->sync_setup.diff, params->sync_setup.remove,
		       params->sync_setup.compress, params->sync_setup.cleanup,
		       params->sync_setup.cache, params->sync_setup.cleanup_cache,
		       params->sync_setup.remove_dst);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
		    CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
}

static int og_cmd_restore_incremental_image(json_t *element, struct og_msg_params *params)
{
	char buf[4096] = {};
	int err = 0, len;
	const char *key;
	json_t *value;
	TRAMA *msg;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "clients")) {
			err = og_json_parse_clients(value, params);
		} else if (!strcmp(key, "disk")) {
			err = og_json_parse_string(value, &params->disk);
			params->flags |= OG_REST_PARAM_DISK;
		} else if (!strcmp(key, "partition")) {
			err = og_json_parse_string(value, &params->partition);
			params->flags |= OG_REST_PARAM_PARTITION;
		} else if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "repository")) {
			err = og_json_parse_string(value, &params->repository);
			params->flags |= OG_REST_PARAM_REPO;
		} else if (!strcmp(key, "profile")) {
			err = og_json_parse_string(value, &params->profile);
			params->flags |= OG_REST_PARAM_PROFILE;
		} else if (!strcmp(key, "type")) {
			err = og_json_parse_string(value, &params->type);
			params->flags |= OG_REST_PARAM_TYPE;
		} else if (!strcmp(key, "sync_params")) {
			err = og_json_parse_sync_params(value, params);
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION |
					    OG_REST_PARAM_ID |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_REPO |
					    OG_REST_PARAM_PROFILE |
					    OG_REST_PARAM_TYPE |
					    OG_REST_PARAM_SYNC_DIFF_ID |
					    OG_REST_PARAM_SYNC_DIFF_NAME |
					    OG_REST_PARAM_SYNC_PATH |
					    OG_REST_PARAM_SYNC_METHOD |
					    OG_REST_PARAM_SYNC_SYNC |
					    OG_REST_PARAM_SYNC_DIFF |
					    OG_REST_PARAM_SYNC_REMOVE |
					    OG_REST_PARAM_SYNC_COMPRESS |
					    OG_REST_PARAM_SYNC_CLEANUP |
					    OG_REST_PARAM_SYNC_CACHE |
					    OG_REST_PARAM_SYNC_CLEANUP_CACHE |
					    OG_REST_PARAM_SYNC_REMOVE_DST))
		return -1;

	len = snprintf(buf, sizeof(buf),
		       "nfn=RestaurarSoftIncremental\rdsk=%s\rpar=%s\ridi=%s\rnci=%s\r"
			   "ipr=%s\rifs=%s\ridf=%s\rncf=%s\rrti=%s\rmet=%s\rmsy=%s\r"
			   "tpt=%s\rwhl=%s\reli=%s\rcmp=%s\rbpi=%s\rcpc=%s\rbpc=%s\r"
			   "nba=%s\r",
		       params->disk, params->partition, params->id, params->name,
			   params->repository, params->profile, params->sync_setup.diff_id,
			   params->sync_setup.diff_name, params->sync_setup.path,
			   params->sync_setup.method, params->sync_setup.sync, params->type,
			   params->sync_setup.diff, params->sync_setup.remove,
		       params->sync_setup.compress, params->sync_setup.cleanup,
		       params->sync_setup.cache, params->sync_setup.cleanup_cache,
		       params->sync_setup.remove_dst);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
		    CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
}

struct og_cmd {
	uint32_t		id;
	struct list_head	list;
	uint32_t		client_id;
	const char		*ip;
	const char		*mac;
	enum og_cmd_type	type;
	enum og_rest_method	method;
	struct og_msg_params	params;
	json_t			*json;
};

static LIST_HEAD(cmd_list);

static const struct og_cmd *og_cmd_find(const char *client_ip)
{
	struct og_cmd *cmd, *next;

	list_for_each_entry_safe(cmd, next, &cmd_list, list) {
		if (strcmp(cmd->ip, client_ip))
			continue;

		list_del(&cmd->list);
		return cmd;
	}

	return NULL;
}

static void og_cmd_free(const struct og_cmd *cmd)
{
	struct og_msg_params *params = (struct og_msg_params *)&cmd->params;
	int i;

	for (i = 0; i < params->ips_array_len; i++) {
		free((void *)params->ips_array[i]);
		free((void *)params->mac_array[i]);
	}
	free((void *)params->wol_type);

	if (cmd->json)
		json_decref(cmd->json);

	free((void *)cmd->ip);
	free((void *)cmd->mac);
	free((void *)cmd);
}

static void og_cmd_init(struct og_cmd *cmd, enum og_rest_method method,
			enum og_cmd_type type, json_t *root)
{
	cmd->type = type;
	cmd->method = method;
	cmd->params.ips_array[0] = strdup(cmd->ip);
	cmd->params.ips_array_len = 1;
	cmd->json = root;
}

static int og_cmd_legacy_wol(const char *input, struct og_cmd *cmd)
{
	char wol_type[2] = {};

	if (sscanf(input, "mar=%s", wol_type) != 1) {
		syslog(LOG_ERR, "malformed database legacy input\n");
		return -1;
	}

	og_cmd_init(cmd, OG_METHOD_NO_HTTP, OG_CMD_WOL, NULL);
	cmd->params.mac_array[0] = strdup(cmd->mac);
	cmd->params.wol_type = strdup(wol_type);

	return 0;
}

static int og_cmd_legacy_shell_run(const char *input, struct og_cmd *cmd)
{
	json_t *root, *script, *echo;

	script = json_string(input + 4);
	echo = json_boolean(false);

	root = json_object();
	if (!root)
		return -1;
	json_object_set_new(root, "run", script);
	json_object_set_new(root, "echo", echo);

	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_SHELL_RUN, root);

	return 0;
}

#define OG_DB_SMALLINT_MAXLEN	6

static int og_cmd_legacy_session(const char *input, struct og_cmd *cmd)
{
	char part_str[OG_DB_SMALLINT_MAXLEN + 1];
	char disk_str[OG_DB_SMALLINT_MAXLEN + 1];
	json_t *root, *disk, *partition;

	if (sscanf(input, "dsk=%s\rpar=%s\r", disk_str, part_str) != 2)
		return -1;
	partition = json_string(part_str);
	disk = json_string(disk_str);

	root = json_object();
	if (!root)
		return -1;
	json_object_set_new(root, "partition", partition);
	json_object_set_new(root, "disk", disk);

	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_SESSION, root);

	return 0;
}

static int og_cmd_legacy_poweroff(const char *input, struct og_cmd *cmd)
{
	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_POWEROFF, NULL);

	return 0;
}

static int og_cmd_legacy_refresh(const char *input, struct og_cmd *cmd)
{
	og_cmd_init(cmd, OG_METHOD_GET, OG_CMD_REFRESH, NULL);

	return 0;
}

static int og_cmd_legacy_reboot(const char *input, struct og_cmd *cmd)
{
	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_REBOOT, NULL);

	return 0;
}

static int og_cmd_legacy_stop(const char *input, struct og_cmd *cmd)
{
	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_STOP, NULL);

	return 0;
}

static int og_cmd_legacy_hardware(const char *input, struct og_cmd *cmd)
{
	og_cmd_init(cmd, OG_METHOD_GET, OG_CMD_HARDWARE, NULL);

	return 0;
}

static int og_cmd_legacy_software(const char *input, struct og_cmd *cmd)
{
	og_cmd_init(cmd, OG_METHOD_GET, OG_CMD_SOFTWARE, NULL);

	return 0;
}

#define OG_DB_IMAGE_NAME_MAXLEN	50
#define OG_DB_FILESYSTEM_MAXLEN	16
#define OG_DB_INT8_MAXLEN	8
#define OG_DB_INT_MAXLEN	11
#define OG_DB_IP_MAXLEN		15

struct og_image_legacy {
	char software_id[OG_DB_INT_MAXLEN + 1];
	char image_id[OG_DB_INT_MAXLEN + 1];
	char name[OG_DB_IMAGE_NAME_MAXLEN + 1];
	char repo[OG_DB_IP_MAXLEN + 1];
	char part[OG_DB_SMALLINT_MAXLEN + 1];
	char disk[OG_DB_SMALLINT_MAXLEN + 1];
	char code[OG_DB_INT8_MAXLEN + 1];
};

struct og_legacy_partition {
	char partition[OG_DB_SMALLINT_MAXLEN + 1];
	char code[OG_DB_INT8_MAXLEN + 1];
	char size[OG_DB_INT_MAXLEN + 1];
	char filesystem[OG_DB_FILESYSTEM_MAXLEN + 1];
	char format[2]; /* Format is a boolean 0 or 1 => length is 2 */
};

static int og_cmd_legacy_image_create(const char *input, struct og_cmd *cmd)
{
	json_t *root, *disk, *partition, *code, *image_id, *name, *repo;
	struct og_image_legacy img = {};

	if (sscanf(input, "dsk=%s\rpar=%s\rcpt=%s\ridi=%s\rnci=%s\ripr=%s\r",
		   img.disk, img.part, img.code, img.image_id, img.name,
		   img.repo) != 6)
		return -1;
	image_id = json_string(img.image_id);
	partition = json_string(img.part);
	code = json_string(img.code);
	name = json_string(img.name);
	repo = json_string(img.repo);
	disk = json_string(img.disk);

	root = json_object();
	if (!root)
		return -1;
	json_object_set_new(root, "partition", partition);
	json_object_set_new(root, "repository", repo);
	json_object_set_new(root, "id", image_id);
	json_object_set_new(root, "code", code);
	json_object_set_new(root, "name", name);
	json_object_set_new(root, "disk", disk);

	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_IMAGE_CREATE, root);

	return 0;
}

#define OG_DB_RESTORE_TYPE_MAXLEN	64

static int og_cmd_legacy_image_restore(const char *input, struct og_cmd *cmd)
{
	json_t *root, *disk, *partition, *image_id, *name, *repo;
	char restore_type_str[OG_DB_RESTORE_TYPE_MAXLEN + 1] = {};
	char software_id_str[OG_DB_INT_MAXLEN + 1] = {};
	json_t *software_id, *restore_type;
	struct og_image_legacy img = {};

	if (sscanf(input,
		   "dsk=%s\rpar=%s\ridi=%s\rnci=%s\ripr=%s\rifs=%s\rptc=%s\r",
		   img.disk, img.part, img.image_id, img.name, img.repo,
		   software_id_str, restore_type_str) != 7)
		return -1;

	restore_type = json_string(restore_type_str);
	software_id = json_string(software_id_str);
	image_id = json_string(img.image_id);
	partition = json_string(img.part);
	name = json_string(img.name);
	repo = json_string(img.repo);
	disk = json_string(img.disk);

	root = json_object();
	if (!root)
		return -1;
	json_object_set_new(root, "profile", software_id);
	json_object_set_new(root, "partition", partition);
	json_object_set_new(root, "type", restore_type);
	json_object_set_new(root, "repository", repo);
	json_object_set_new(root, "id", image_id);
	json_object_set_new(root, "name", name);
	json_object_set_new(root, "disk", disk);

	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_IMAGE_RESTORE, root);

	return 0;
}

static int og_cmd_legacy_setup(const char *input, struct og_cmd *cmd)
{
	json_t *root, *disk, *cache, *cache_size, *partition_setup, *object;
	struct og_legacy_partition part_cfg[OG_PARTITION_MAX] = {};
	char cache_size_str [OG_DB_INT_MAXLEN + 1];
	char disk_str [OG_DB_SMALLINT_MAXLEN + 1];
	json_t *part, *code, *fs, *size, *format;
	unsigned int partition_len = 0;
	const char *in_ptr;
	char cache_str[2];

	if (sscanf(input, "dsk=%s\rcfg=dis=%*[^*]*che=%[^*]*tch=%[^!]!",
		   disk_str, cache_str, cache_size_str) != 3)
		return -1;

	in_ptr = strstr(input, "!") + 1;
	while (strlen(in_ptr) > 0) {
		if(sscanf(in_ptr,
			  "par=%[^*]*cpt=%[^*]*sfi=%[^*]*tam=%[^*]*ope=%[^%%]%%",
			  part_cfg[partition_len].partition,
			  part_cfg[partition_len].code,
			  part_cfg[partition_len].filesystem,
			  part_cfg[partition_len].size,
			  part_cfg[partition_len].format) != 5)
			return -1;
		in_ptr = strstr(in_ptr, "%") + 1;
		partition_len++;
	}

	root = json_object();
	if (!root)
		return -1;

	cache_size = json_string(cache_size_str);
	cache = json_string(cache_str);
	partition_setup = json_array();
	disk = json_string(disk_str);

	for (unsigned int i = 0; i < partition_len; ++i) {
		object = json_object();
		if (!object) {
			json_decref(root);
			return -1;
		}

		part = json_string(part_cfg[i].partition);
		fs = json_string(part_cfg[i].filesystem);
		format = json_string(part_cfg[i].format);
		code = json_string(part_cfg[i].code);
		size = json_string(part_cfg[i].size);

		json_object_set_new(object, "partition", part);
		json_object_set_new(object, "filesystem", fs);
		json_object_set_new(object, "format", format);
		json_object_set_new(object, "code", code);
		json_object_set_new(object, "size", size);

		json_array_append_new(partition_setup, object);
	}

	json_object_set_new(root, "partition_setup", partition_setup);
	json_object_set_new(root, "cache_size", cache_size);
	json_object_set_new(root, "cache", cache);
	json_object_set_new(root, "disk", disk);

	og_cmd_init(cmd, OG_METHOD_POST, OG_CMD_SETUP, root);

	return 0;
}

static int og_cmd_legacy_run_schedule(const char *input, struct og_cmd *cmd)
{
	og_cmd_init(cmd, OG_METHOD_GET, OG_CMD_RUN_SCHEDULE, NULL);

	return 0;
}

static int og_cmd_legacy(const char *input, struct og_cmd *cmd)
{
	char legacy_cmd[32] = {};
	int err = -1;

	if (sscanf(input, "nfn=%31s\r", legacy_cmd) != 1) {
		syslog(LOG_ERR, "malformed database legacy input\n");
		return -1;
	}
	input = strchr(input, '\r') + 1;

	if (!strcmp(legacy_cmd, "Arrancar")) {
		err = og_cmd_legacy_wol(input, cmd);
	} else if (!strcmp(legacy_cmd, "EjecutarScript")) {
		err = og_cmd_legacy_shell_run(input, cmd);
	} else if (!strcmp(legacy_cmd, "IniciarSesion")) {
		err = og_cmd_legacy_session(input, cmd);
	} else if (!strcmp(legacy_cmd, "Apagar")) {
		err = og_cmd_legacy_poweroff(input, cmd);
	} else if (!strcmp(legacy_cmd, "Actualizar")) {
		err = og_cmd_legacy_refresh(input, cmd);
	} else if (!strcmp(legacy_cmd, "Reiniciar")) {
		err = og_cmd_legacy_reboot(input, cmd);
	} else if (!strcmp(legacy_cmd, "Purgar")) {
		err = og_cmd_legacy_stop(input, cmd);
	} else if (!strcmp(legacy_cmd, "InventarioHardware")) {
		err = og_cmd_legacy_hardware(input, cmd);
	} else if (!strcmp(legacy_cmd, "InventarioSoftware")) {
		err = og_cmd_legacy_software(input, cmd);
	} else if (!strcmp(legacy_cmd, "CrearImagen")) {
		err = og_cmd_legacy_image_create(input, cmd);
	} else if (!strcmp(legacy_cmd, "RestaurarImagen")) {
		err = og_cmd_legacy_image_restore(input, cmd);
	} else if (!strcmp(legacy_cmd, "Configurar")) {
		err = og_cmd_legacy_setup(input, cmd);
	} else if (!strcmp(legacy_cmd, "EjecutaComandosPendientes") ||
		   !strcmp(legacy_cmd, "Actualizar")) {
		err = og_cmd_legacy_run_schedule(input, cmd);
	}

	return err;
}

static int og_dbi_add_action(const struct og_dbi *dbi, const struct og_task *task,
			     struct og_cmd *cmd)
{
	char start_date_string[24];
	struct tm *start_date;
	const char *msglog;
	dbi_result result;
	time_t now;

	time(&now);
	start_date = localtime(&now);

	sprintf(start_date_string, "%hu/%hhu/%hhu %hhu:%hhu:%hhu",
		start_date->tm_year + 1900, start_date->tm_mon + 1,
		start_date->tm_mday, start_date->tm_hour, start_date->tm_min,
		start_date->tm_sec);
	result = dbi_conn_queryf(dbi->conn,
				"INSERT INTO acciones (idordenador, "
				"tipoaccion, idtipoaccion, descriaccion, ip, "
				"sesion, idcomando, parametros, fechahorareg, "
				"estado, resultado, ambito, idambito, "
				"restrambito, idprocedimiento, idcentro, "
				"idprogramacion) "
				"VALUES (%d, %d, %d, '%s', '%s', %d, %d, '%s', "
				"'%s', %d, %d, %d, %d, '%s', %d, %d, %d)",
				cmd->client_id, EJECUCION_TAREA, task->task_id,
				"", cmd->ip, 0, task->command_id,
				task->params, start_date_string,
				ACCION_INICIADA, ACCION_SINRESULTADO,
				task->type_scope, task->scope, "",
				task->procedure_id, task->center_id,
				task->schedule_id);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}
	cmd->id = dbi_conn_sequence_last(dbi->conn, NULL);
	dbi_result_free(result);

	return 0;
}

static int og_queue_task_command(struct og_dbi *dbi, const struct og_task *task,
				 char *query)
{
	struct og_cmd *cmd;
	const char *msglog;
	dbi_result result;

	result = dbi_conn_queryf(dbi->conn, query);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}

	while (dbi_result_next_row(result)) {
		cmd = (struct og_cmd *)calloc(1, sizeof(struct og_cmd));
		if (!cmd) {
			dbi_result_free(result);
			return -1;
		}

		cmd->client_id	= dbi_result_get_uint(result, "idordenador");
		cmd->ip		= strdup(dbi_result_get_string(result, "ip"));
		cmd->mac	= strdup(dbi_result_get_string(result, "mac"));
		og_cmd_legacy(task->params, cmd);

		if (og_dbi_add_action(dbi, task, cmd)) {
			dbi_result_free(result);
			return -1;
		}

		list_add_tail(&cmd->list, &cmd_list);
	}

	dbi_result_free(result);

	return 0;
}

static int og_queue_task_group_clients(struct og_dbi *dbi, struct og_task *task,
				       char *query)
{

	const char *msglog;
	dbi_result result;

	result = dbi_conn_queryf(dbi->conn, query);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}

	while (dbi_result_next_row(result)) {
		uint32_t group_id = dbi_result_get_uint(result, "idgrupo");

		sprintf(query, "SELECT idgrupo FROM gruposordenadores "
				"WHERE grupoid=%d", group_id);
		if (og_queue_task_group_clients(dbi, task, query)) {
			dbi_result_free(result);
			return -1;
		}

		sprintf(query,"SELECT ip, mac, idordenador FROM ordenadores "
			      "WHERE grupoid=%d", group_id);
		if (og_queue_task_command(dbi, task, query)) {
			dbi_result_free(result);
			return -1;
		}

	}

	dbi_result_free(result);

	return 0;
}

static int og_queue_task_group_classrooms(struct og_dbi *dbi,
					  struct og_task *task, char *query)
{

	const char *msglog;
	dbi_result result;

	result = dbi_conn_queryf(dbi->conn, query);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}

	while (dbi_result_next_row(result)) {
		uint32_t group_id = dbi_result_get_uint(result, "idgrupo");

		sprintf(query, "SELECT idgrupo FROM grupos "
				"WHERE grupoid=%d AND tipo=%d", group_id, AMBITO_GRUPOSAULAS);
		if (og_queue_task_group_classrooms(dbi, task, query)) {
			dbi_result_free(result);
			return -1;
		}

		sprintf(query,
			"SELECT ip,mac,idordenador "
			"FROM ordenadores INNER JOIN aulas "
			"WHERE ordenadores.idaula=aulas.idaula "
			"AND aulas.grupoid=%d",
			group_id);
		if (og_queue_task_command(dbi, task, query)) {
			dbi_result_free(result);
			return -1;
		}

	}

	dbi_result_free(result);

	return 0;
}

static int og_queue_task_clients(struct og_dbi *dbi, struct og_task *task)
{
	char query[4096];

	switch (task->type_scope) {
		case AMBITO_CENTROS:
			sprintf(query,
				"SELECT ip,mac,idordenador "
				"FROM ordenadores INNER JOIN aulas "
				"WHERE ordenadores.idaula=aulas.idaula "
				"AND idcentro=%d",
				task->scope);
			return og_queue_task_command(dbi, task, query);
		case AMBITO_GRUPOSAULAS:
			sprintf(query,
				"SELECT idgrupo FROM grupos "
				"WHERE idgrupo=%i AND tipo=%d",
				task->scope, AMBITO_GRUPOSAULAS);
			return og_queue_task_group_classrooms(dbi, task, query);
		case AMBITO_AULAS:
			sprintf(query,
				"SELECT ip,mac,idordenador FROM ordenadores "
				"WHERE idaula=%d",
				task->scope);
			return og_queue_task_command(dbi, task, query);
		case AMBITO_GRUPOSORDENADORES:
			sprintf(query,
				"SELECT idgrupo FROM gruposordenadores "
				"WHERE idgrupo = %d",
				task->scope);
			return og_queue_task_group_clients(dbi, task, query);
		case AMBITO_ORDENADORES:
			sprintf(query,
				"SELECT ip, mac, idordenador FROM ordenadores "
				"WHERE idordenador = %d",
				task->scope);
			return og_queue_task_command(dbi, task, query);
	}
	return 0;
}

static int og_dbi_queue_procedure(struct og_dbi *dbi, struct og_task *task)
{
	uint32_t procedure_id;
	const char *msglog;
	dbi_result result;

	result = dbi_conn_queryf(dbi->conn,
			"SELECT parametros, procedimientoid, idcomando "
			"FROM procedimientos_acciones "
			"WHERE idprocedimiento=%d ORDER BY orden", task->procedure_id);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}

	while (dbi_result_next_row(result)) {
		procedure_id = dbi_result_get_uint(result, "procedimientoid");
		if (procedure_id > 0) {
			task->procedure_id = procedure_id;
			if (og_dbi_queue_procedure(dbi, task))
				return -1;
			continue;
		}

		task->params	= strdup(dbi_result_get_string(result, "parametros"));
		task->command_id = dbi_result_get_uint(result, "idcomando");
		if (og_queue_task_clients(dbi, task))
			return -1;
	}

	dbi_result_free(result);

	return 0;
}

static int og_dbi_queue_task(struct og_dbi *dbi, uint32_t task_id,
			     uint32_t schedule_id)
{
	struct og_task task = {};
	uint32_t task_id_next;
	struct og_cmd *cmd;
	const char *msglog;
	dbi_result result;

	task.schedule_id = schedule_id;

	result = dbi_conn_queryf(dbi->conn,
			"SELECT tareas_acciones.orden, "
				"tareas_acciones.idprocedimiento, "
				"tareas_acciones.tareaid, "
				"tareas.idtarea, "
				"tareas.idcentro, "
				"tareas.ambito, "
				"tareas.idambito, "
				"tareas.restrambito "
			" FROM tareas"
				" INNER JOIN tareas_acciones ON tareas_acciones.idtarea=tareas.idtarea"
                        " WHERE tareas_acciones.idtarea=%u ORDER BY tareas_acciones.orden ASC", task_id);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}

	while (dbi_result_next_row(result)) {
		task_id_next = dbi_result_get_uint(result, "tareaid");

		if (task_id_next > 0) {
			if (og_dbi_queue_task(dbi, task_id_next, schedule_id))
				return -1;

			continue;
		}
		task.task_id = dbi_result_get_uint(result, "idtarea");
		task.center_id = dbi_result_get_uint(result, "idcentro");
		task.procedure_id = dbi_result_get_uint(result, "idprocedimiento");
		task.type_scope = dbi_result_get_uint(result, "ambito");
		task.scope = dbi_result_get_uint(result, "idambito");
		task.filtered_scope = dbi_result_get_string(result, "restrambito");

		og_dbi_queue_procedure(dbi, &task);
	}

	dbi_result_free(result);

	list_for_each_entry(cmd, &cmd_list, list) {
		if (cmd->type != OG_CMD_WOL)
			continue;

		if (!Levanta((char **)cmd->params.ips_array,
			     (char **)cmd->params.mac_array,
			     cmd->params.ips_array_len,
			     (char *)cmd->params.wol_type))
			return -1;
	}

	return 0;
}

void og_dbi_schedule_task(unsigned int task_id, unsigned int schedule_id)
{
	struct og_msg_params params = {};
	bool duplicated = false;
	struct og_cmd *cmd;
	struct og_dbi *dbi;
	unsigned int i;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return;
	}
	og_dbi_queue_task(dbi, task_id, schedule_id);
	og_dbi_close(dbi);

	list_for_each_entry(cmd, &cmd_list, list) {
		for (i = 0; i < params.ips_array_len; i++) {
			if (!strncmp(cmd->ip, params.ips_array[i],
				     OG_DB_IP_MAXLEN)) {
				duplicated = true;
				break;
			}
		}

		if (!duplicated)
			params.ips_array[params.ips_array_len++] = cmd->ip;
		else
			duplicated = false;
	}

	og_send_request(OG_METHOD_GET, OG_CMD_RUN_SCHEDULE, &params, NULL);
}

static int og_cmd_task_post(json_t *element, struct og_msg_params *params)
{
	struct og_cmd *cmd;
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "task")) {
			err = og_json_parse_string(value, &params->task_id);
			params->flags |= OG_REST_PARAM_TASK;
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_TASK))
		return -1;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
			   __func__, __LINE__);
		return -1;
	}

	og_dbi_queue_task(dbi, atoi(params->task_id), 0);
	og_dbi_close(dbi);

	list_for_each_entry(cmd, &cmd_list, list)
		params->ips_array[params->ips_array_len++] = cmd->ip;

	return og_send_request(OG_METHOD_GET, OG_CMD_RUN_SCHEDULE, params,
			       NULL);
}

static int og_dbi_schedule_get(void)
{
	uint32_t schedule_id, task_id;
	struct og_schedule_time time;
	struct og_dbi *dbi;
	const char *msglog;
	dbi_result result;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}

	result = dbi_conn_queryf(dbi->conn,
				 "SELECT idprogramacion, tipoaccion, identificador, "
				 "sesion, annos, meses, diario, dias, semanas, horas, "
				 "ampm, minutos FROM programaciones "
				 "WHERE suspendida = 0");
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		og_dbi_close(dbi);
		return -1;
	}

	while (dbi_result_next_row(result)) {
		memset(&time, 0, sizeof(time));
		schedule_id = dbi_result_get_uint(result, "idprogramacion");
		task_id = dbi_result_get_uint(result, "identificador");
		time.years = dbi_result_get_uint(result, "annos");
		time.months = dbi_result_get_uint(result, "meses");
		time.weeks = dbi_result_get_uint(result, "semanas");
		time.week_days = dbi_result_get_uint(result, "dias");
		time.days = dbi_result_get_uint(result, "diario");
		time.hours = dbi_result_get_uint(result, "horas");
		time.am_pm = dbi_result_get_uint(result, "ampm");
		time.minutes = dbi_result_get_uint(result, "minutos");

		og_schedule_create(schedule_id, task_id, &time);
	}

	dbi_result_free(result);
	og_dbi_close(dbi);

	return 0;
}

static int og_dbi_schedule_create(struct og_dbi *dbi,
				  struct og_msg_params *params,
				  uint32_t *schedule_id)
{
	const char *msglog;
	dbi_result result;
	uint8_t suspended = 0;
	uint8_t type = 3;

	result = dbi_conn_queryf(dbi->conn,
				 "INSERT INTO programaciones (tipoaccion,"
				 " identificador, nombrebloque, annos, meses,"
				 " semanas, dias, diario, horas, ampm, minutos,"
				 " suspendida) VALUES (%d, %s, '%s', %d, %d,"
				 " %d, %d, %d, %d, %d, %d, %d)", type,
				 params->task_id, params->name,
				 params->time.years, params->time.months,
				 params->time.weeks, params->time.week_days,
				 params->time.days, params->time.hours,
				 params->time.am_pm, params->time.minutes,
				 suspended);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}
	dbi_result_free(result);

	*schedule_id = dbi_conn_sequence_last(dbi->conn, NULL);

	return 0;
}

static int og_dbi_schedule_update(struct og_dbi *dbi,
				  struct og_msg_params *params)
{
	const char *msglog;
	dbi_result result;
	uint8_t type = 3;

	result = dbi_conn_queryf(dbi->conn,
				 "UPDATE programaciones SET tipoaccion=%d, "
				 "identificador='%s', nombrebloque='%s', "
				 "annos=%d, meses=%d, "
				 "diario=%d, horas=%d, ampm=%d, minutos=%d "
				 "WHERE idprogramacion='%s'",
				 type, params->task_id, params->name,
				 params->time.years, params->time.months,
				 params->time.days, params->time.hours,
				 params->time.am_pm, params->time.minutes,
				 params->id);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}
	dbi_result_free(result);

	return 0;
}

static int og_dbi_schedule_delete(struct og_dbi *dbi, uint32_t id)
{
	const char *msglog;
	dbi_result result;

	result = dbi_conn_queryf(dbi->conn,
				 "DELETE FROM programaciones WHERE idprogramacion=%d",
				 id);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}
	dbi_result_free(result);

	return 0;
}

struct og_db_schedule {
	uint32_t		id;
	uint32_t		task_id;
	const char		*name;
	struct og_schedule_time	time;
	uint32_t		week_days;
	uint32_t		weeks;
	uint32_t		suspended;
	uint32_t		session;
};

static int og_dbi_schedule_get_json(struct og_dbi *dbi, json_t *root,
				    const char *task_id, const char *schedule_id)
{
	struct og_db_schedule schedule;
	json_t *obj, *array;
	const char *msglog;
	dbi_result result;
	int err = 0;

	if (task_id) {
		result = dbi_conn_queryf(dbi->conn,
					 "SELECT idprogramacion,"
					 "	 identificador, nombrebloque,"
					 "	 annos, meses, diario, dias,"
					 "	 semanas, horas, ampm,"
					 "	 minutos,suspendida, sesion "
					 "FROM programaciones "
					 "WHERE identificador=%d",
					 atoi(task_id));
	} else if (schedule_id) {
		result = dbi_conn_queryf(dbi->conn,
					 "SELECT idprogramacion,"
					 "	 identificador, nombrebloque,"
					 "	 annos, meses, diario, dias,"
					 "	 semanas, horas, ampm,"
					 "	 minutos,suspendida, sesion "
					 "FROM programaciones "
					 "WHERE idprogramacion=%d",
					 atoi(schedule_id));
	} else {
		result = dbi_conn_queryf(dbi->conn,
					 "SELECT idprogramacion,"
					 "	 identificador, nombrebloque,"
					 "	 annos, meses, diario, dias,"
					 "	 semanas, horas, ampm,"
					 "	 minutos,suspendida, sesion "
					 "FROM programaciones");
	}

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return -1;
	}

	array = json_array();
	if (!array)
		return -1;

	while (dbi_result_next_row(result)) {
		schedule.id = dbi_result_get_uint(result, "idprogramacion");
		schedule.task_id = dbi_result_get_uint(result, "identificador");
		schedule.name = dbi_result_get_string(result, "nombrebloque");
		schedule.time.years = dbi_result_get_uint(result, "annos");
		schedule.time.months = dbi_result_get_uint(result, "meses");
		schedule.time.days = dbi_result_get_uint(result, "diario");
		schedule.time.hours = dbi_result_get_uint(result, "horas");
		schedule.time.am_pm = dbi_result_get_uint(result, "ampm");
		schedule.time.minutes = dbi_result_get_uint(result, "minutos");
		schedule.week_days = dbi_result_get_uint(result, "dias");
		schedule.weeks = dbi_result_get_uint(result, "semanas");
		schedule.suspended = dbi_result_get_uint(result, "suspendida");
		schedule.session = dbi_result_get_uint(result, "sesion");

		obj = json_object();
		if (!obj) {
			err = -1;
			break;
		}
		json_object_set_new(obj, "id", json_integer(schedule.id));
		json_object_set_new(obj, "task", json_integer(schedule.task_id));
		json_object_set_new(obj, "name", json_string(schedule.name));
		json_object_set_new(obj, "years", json_integer(schedule.time.years));
		json_object_set_new(obj, "months", json_integer(schedule.time.months));
		json_object_set_new(obj, "days", json_integer(schedule.time.days));
		json_object_set_new(obj, "hours", json_integer(schedule.time.hours));
		json_object_set_new(obj, "am_pm", json_integer(schedule.time.am_pm));
		json_object_set_new(obj, "minutes", json_integer(schedule.time.minutes));
		json_object_set_new(obj, "week_days", json_integer(schedule.week_days));
		json_object_set_new(obj, "weeks", json_integer(schedule.weeks));
		json_object_set_new(obj, "suspended", json_integer(schedule.suspended));
		json_object_set_new(obj, "session", json_integer(schedule.session));

		json_array_append_new(array, obj);
	}

	json_object_set_new(root, "schedule", array);

	dbi_result_free(result);

	return err;
}

static struct ev_loop *og_loop;

static int og_task_schedule_create(struct og_msg_params *params)
{
	uint32_t schedule_id;
	struct og_dbi *dbi;
	int err;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}

	err = og_dbi_schedule_create(dbi, params, &schedule_id);
	if (err < 0) {
		og_dbi_close(dbi);
		return -1;
	}
	og_schedule_create(schedule_id, atoi(params->task_id), &params->time);
	og_schedule_refresh(og_loop);
	og_dbi_close(dbi);

	return 0;
}

static int og_cmd_schedule_create(json_t *element, struct og_msg_params *params)
{
	const char *key;
	json_t *value;
	int err;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "task")) {
			err = og_json_parse_string(value, &params->task_id);
			params->flags |= OG_REST_PARAM_TASK;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "when")) {
			err = og_json_parse_time_params(value, params);
		} else if (!strcmp(key, "type")) {
			err = og_json_parse_string(value, &params->type);
			params->flags |= OG_REST_PARAM_TYPE;
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_TASK |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_TIME_YEARS |
					    OG_REST_PARAM_TIME_MONTHS |
					    OG_REST_PARAM_TIME_WEEKS |
					    OG_REST_PARAM_TIME_WEEK_DAYS |
					    OG_REST_PARAM_TIME_DAYS |
					    OG_REST_PARAM_TIME_HOURS |
					    OG_REST_PARAM_TIME_MINUTES |
					    OG_REST_PARAM_TIME_AM_PM))
		return -1;

	return og_task_schedule_create(params);
}

static int og_cmd_schedule_update(json_t *element, struct og_msg_params *params)
{
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		} else if (!strcmp(key, "task")) {
			err = og_json_parse_string(value, &params->task_id);
			params->flags |= OG_REST_PARAM_TASK;
		} else if (!strcmp(key, "name")) {
			err = og_json_parse_string(value, &params->name);
			params->flags |= OG_REST_PARAM_NAME;
		} else if (!strcmp(key, "when"))
			err = og_json_parse_time_params(value, params);

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ID |
					    OG_REST_PARAM_TASK |
					    OG_REST_PARAM_NAME |
					    OG_REST_PARAM_TIME_YEARS |
					    OG_REST_PARAM_TIME_MONTHS |
					    OG_REST_PARAM_TIME_DAYS |
					    OG_REST_PARAM_TIME_HOURS |
					    OG_REST_PARAM_TIME_MINUTES |
					    OG_REST_PARAM_TIME_AM_PM))
		return -1;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
			   __func__, __LINE__);
		return -1;
	}

	err = og_dbi_schedule_update(dbi, params);
	og_dbi_close(dbi);

	if (err < 0)
		return err;

	og_schedule_update(og_loop, atoi(params->id), atoi(params->task_id),
			   &params->time);
	og_schedule_refresh(og_loop);

	return err;
}

static int og_cmd_schedule_delete(json_t *element, struct og_msg_params *params)
{
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err;

	if (json_typeof(element) != JSON_OBJECT)
		return -1;

	json_object_foreach(element, key, value) {
		if (!strcmp(key, "id")) {
			err = og_json_parse_string(value, &params->id);
			params->flags |= OG_REST_PARAM_ID;
		} else {
			return -1;
		}

		if (err < 0)
			break;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ID))
		return -1;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
			   __func__, __LINE__);
		return -1;
	}

	err = og_dbi_schedule_delete(dbi, atoi(params->id));
	og_dbi_close(dbi);

	og_schedule_delete(og_loop, atoi(params->id));

	return err;
}

static int og_cmd_schedule_get(json_t *element, struct og_msg_params *params,
			       char *buffer_reply)
{
	struct og_buffer og_buffer = {
		.data	= buffer_reply,
	};
	json_t *schedule_root;
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err;

	if (element) {
		if (json_typeof(element) != JSON_OBJECT)
			return -1;

		json_object_foreach(element, key, value) {
			if (!strcmp(key, "task")) {
				err = og_json_parse_string(value,
							   &params->task_id);
			} else if (!strcmp(key, "id")) {
				err = og_json_parse_string(value, &params->id);
			} else {
				return -1;
			}

			if (err < 0)
				break;
		}
	}

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
			   __func__, __LINE__);
		return -1;
	}

	schedule_root = json_object();
	if (!schedule_root) {
		og_dbi_close(dbi);
		return -1;
	}

	err = og_dbi_schedule_get_json(dbi, schedule_root,
				       params->task_id, params->id);
	og_dbi_close(dbi);

	if (err >= 0)
		json_dump_callback(schedule_root, og_json_dump_clients, &og_buffer, 0);

	json_decref(schedule_root);

	return err;
}

static int og_client_method_not_found(struct og_client *cli)
{
	/* To meet RFC 7231, this function MUST generate an Allow header field
	 * containing the correct methods. For example: "Allow: POST\r\n"
	 */
	char buf[] = "HTTP/1.1 405 Method Not Allowed\r\n"
		     "Content-Length: 0\r\n\r\n";

	send(og_client_socket(cli), buf, strlen(buf), 0);

	return -1;
}

static int og_client_bad_request(struct og_client *cli)
{
	char buf[] = "HTTP/1.1 400 Bad Request\r\nContent-Length: 0\r\n\r\n";

	send(og_client_socket(cli), buf, strlen(buf), 0);

	return -1;
}

static int og_client_not_found(struct og_client *cli)
{
	char buf[] = "HTTP/1.1 404 Not Found\r\nContent-Length: 0\r\n\r\n";

	send(og_client_socket(cli), buf, strlen(buf), 0);

	return -1;
}

static int og_client_not_authorized(struct og_client *cli)
{
	char buf[] = "HTTP/1.1 401 Unauthorized\r\n"
		     "WWW-Authenticate: Basic\r\n"
		     "Content-Length: 0\r\n\r\n";

	send(og_client_socket(cli), buf, strlen(buf), 0);

	return -1;
}

static int og_server_internal_error(struct og_client *cli)
{
	char buf[] = "HTTP/1.1 500 Internal Server Error\r\n"
		     "Content-Length: 0\r\n\r\n";

	send(og_client_socket(cli), buf, strlen(buf), 0);

	return -1;
}

static int og_client_payload_too_large(struct og_client *cli)
{
	char buf[] = "HTTP/1.1 413 Payload Too Large\r\n"
		     "Content-Length: 0\r\n\r\n";

	send(og_client_socket(cli), buf, strlen(buf), 0);

	return -1;
}

#define OG_MSG_RESPONSE_MAXLEN	65536

static int og_client_ok(struct og_client *cli, char *buf_reply)
{
	char buf[OG_MSG_RESPONSE_MAXLEN] = {};
	int err = 0, len;

	len = snprintf(buf, sizeof(buf),
		       "HTTP/1.1 200 OK\r\nContent-Length: %ld\r\n\r\n%s",
		       strlen(buf_reply), buf_reply);
	if (len >= (int)sizeof(buf))
		err = og_server_internal_error(cli);

	send(og_client_socket(cli), buf, strlen(buf), 0);

	return err;
}

static int og_client_state_process_payload_rest(struct og_client *cli)
{
	char buf_reply[OG_MSG_RESPONSE_MAXLEN] = {};
	struct og_msg_params params = {};
	enum og_rest_method method;
	const char *cmd, *body;
	json_error_t json_err;
	json_t *root = NULL;
	int err = 0;

	syslog(LOG_DEBUG, "%s:%hu %.32s ...\n",
	       inet_ntoa(cli->addr.sin_addr),
	       ntohs(cli->addr.sin_port), cli->buf);

	if (!strncmp(cli->buf, "GET", strlen("GET"))) {
		method = OG_METHOD_GET;
		cmd = cli->buf + strlen("GET") + 2;
	} else if (!strncmp(cli->buf, "POST", strlen("POST"))) {
		method = OG_METHOD_POST;
		cmd = cli->buf + strlen("POST") + 2;
	} else
		return og_client_method_not_found(cli);

	body = strstr(cli->buf, "\r\n\r\n") + 4;

	if (strcmp(cli->auth_token, auth_token)) {
		syslog(LOG_ERR, "wrong Authentication key\n");
		return og_client_not_authorized(cli);
	}

	if (cli->content_length) {
		root = json_loads(body, 0, &json_err);
		if (!root) {
			syslog(LOG_ERR, "malformed json line %d: %s\n",
			       json_err.line, json_err.text);
			return og_client_not_found(cli);
		}
	}

	if (!strncmp(cmd, "clients", strlen("clients"))) {
		if (method != OG_METHOD_POST &&
		    method != OG_METHOD_GET)
			return og_client_method_not_found(cli);

		if (method == OG_METHOD_POST && !root) {
			syslog(LOG_ERR, "command clients with no payload\n");
			return og_client_bad_request(cli);
		}
		switch (method) {
		case OG_METHOD_POST:
			err = og_cmd_post_clients(root, &params);
			break;
		case OG_METHOD_GET:
			err = og_cmd_get_clients(root, &params, buf_reply);
			break;
		default:
			return og_client_bad_request(cli);
		}
	} else if (!strncmp(cmd, "wol", strlen("wol"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command wol with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_wol(root, &params);
	} else if (!strncmp(cmd, "shell/run", strlen("shell/run"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command run with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_run_post(root, &params);
	} else if (!strncmp(cmd, "shell/output", strlen("shell/output"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command output with no payload\n");
			return og_client_bad_request(cli);
		}

		err = og_cmd_run_get(root, &params, buf_reply);
	} else if (!strncmp(cmd, "session", strlen("session"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command session with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_session(root, &params);
	} else if (!strncmp(cmd, "poweroff", strlen("poweroff"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command poweroff with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_poweroff(root, &params);
	} else if (!strncmp(cmd, "reboot", strlen("reboot"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command reboot with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_reboot(root, &params);
	} else if (!strncmp(cmd, "stop", strlen("stop"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command stop with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_stop(root, &params);
	} else if (!strncmp(cmd, "refresh", strlen("refresh"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command refresh with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_refresh(root, &params);
	} else if (!strncmp(cmd, "hardware", strlen("hardware"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command hardware with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_hardware(root, &params);
	} else if (!strncmp(cmd, "software", strlen("software"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command software with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_software(root, &params);
	} else if (!strncmp(cmd, "image/create/basic",
			    strlen("image/create/basic"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_create_basic_image(root, &params);
	} else if (!strncmp(cmd, "image/create/incremental",
			    strlen("image/create/incremental"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_create_incremental_image(root, &params);
	} else if (!strncmp(cmd, "image/create", strlen("image/create"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_create_image(root, &params);
	} else if (!strncmp(cmd, "image/restore/basic",
				strlen("image/restore/basic"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_restore_basic_image(root, &params);
	} else if (!strncmp(cmd, "image/restore/incremental",
				strlen("image/restore/incremental"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_restore_incremental_image(root, &params);
	} else if (!strncmp(cmd, "image/restore", strlen("image/restore"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_restore_image(root, &params);
	} else if (!strncmp(cmd, "setup", strlen("setup"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_setup(root, &params);
	} else if (!strncmp(cmd, "run/schedule", strlen("run/schedule"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command create with no payload\n");
			return og_client_bad_request(cli);
		}

		err = og_cmd_run_schedule(root, &params);
	} else if (!strncmp(cmd, "task/run", strlen("task/run"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command task with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_task_post(root, &params);
	} else if (!strncmp(cmd, "schedule/create",
			    strlen("schedule/create"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command task with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_schedule_create(root, &params);
	} else if (!strncmp(cmd, "schedule/delete",
			    strlen("schedule/delete"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command task with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_schedule_delete(root, &params);
	} else if (!strncmp(cmd, "schedule/update",
			    strlen("schedule/update"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		if (!root) {
			syslog(LOG_ERR, "command task with no payload\n");
			return og_client_bad_request(cli);
		}
		err = og_cmd_schedule_update(root, &params);
	} else if (!strncmp(cmd, "schedule/get",
			    strlen("schedule/get"))) {
		if (method != OG_METHOD_POST)
			return og_client_method_not_found(cli);

		err = og_cmd_schedule_get(root, &params, buf_reply);
	} else {
		syslog(LOG_ERR, "unknown command: %.32s ...\n", cmd);
		err = og_client_not_found(cli);
	}

	if (root)
		json_decref(root);

	if (err < 0)
		return og_client_bad_request(cli);

	err = og_client_ok(cli, buf_reply);
	if (err < 0) {
		syslog(LOG_ERR, "HTTP response to %s:%hu is too large\n",
		       inet_ntoa(cli->addr.sin_addr),
		       ntohs(cli->addr.sin_port));
	}

	return err;
}

static int og_client_state_recv_hdr_rest(struct og_client *cli)
{
	char *ptr;

	ptr = strstr(cli->buf, "\r\n\r\n");
	if (!ptr)
		return 0;

	cli->msg_len = ptr - cli->buf + 4;

	ptr = strstr(cli->buf, "Content-Length: ");
	if (ptr) {
		sscanf(ptr, "Content-Length: %i[^\r\n]", &cli->content_length);
		if (cli->content_length < 0)
			return -1;
		cli->msg_len += cli->content_length;
	}

	ptr = strstr(cli->buf, "Authorization: ");
	if (ptr)
		sscanf(ptr, "Authorization: %63[^\r\n]", cli->auth_token);

	return 1;
}

static int og_client_recv(struct og_client *cli, int events)
{
	struct ev_io *io = &cli->io;
	int ret;

	if (events & EV_ERROR) {
		syslog(LOG_ERR, "unexpected error event from client %s:%hu\n",
			       inet_ntoa(cli->addr.sin_addr),
			       ntohs(cli->addr.sin_port));
		return 0;
	}

	ret = recv(io->fd, cli->buf + cli->buf_len,
		   sizeof(cli->buf) - cli->buf_len, 0);
	if (ret <= 0) {
		if (ret < 0) {
			syslog(LOG_ERR, "error reading from client %s:%hu (%s)\n",
			       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
			       strerror(errno));
		} else {
			syslog(LOG_DEBUG, "closed connection by %s:%hu\n",
			       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));
		}
		return ret;
	}

	return ret;
}

static void og_client_read_cb(struct ev_loop *loop, struct ev_io *io, int events)
{
	struct og_client *cli;
	int ret;

	cli = container_of(io, struct og_client, io);

	ret = og_client_recv(cli, events);
	if (ret <= 0)
		goto close;

	if (cli->keepalive_idx >= 0)
		return;

	ev_timer_again(loop, &cli->timer);

	cli->buf_len += ret;
	if (cli->buf_len >= sizeof(cli->buf)) {
		syslog(LOG_ERR, "client request from %s:%hu is too long\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));
		og_client_payload_too_large(cli);
		goto close;
	}

	switch (cli->state) {
	case OG_CLIENT_RECEIVING_HEADER:
		ret = og_client_state_recv_hdr_rest(cli);
		if (ret < 0)
			goto close;
		if (!ret)
			return;

		cli->state = OG_CLIENT_RECEIVING_PAYLOAD;
		/* Fall through. */
	case OG_CLIENT_RECEIVING_PAYLOAD:
		/* Still not enough data to process request. */
		if (cli->buf_len < cli->msg_len)
			return;

		cli->state = OG_CLIENT_PROCESSING_REQUEST;
		/* fall through. */
	case OG_CLIENT_PROCESSING_REQUEST:
		ret = og_client_state_process_payload_rest(cli);
		if (ret < 0) {
			syslog(LOG_ERR, "Failed to process HTTP request from %s:%hu\n",
			       inet_ntoa(cli->addr.sin_addr),
			       ntohs(cli->addr.sin_port));
		}
		if (ret < 0)
			goto close;

		if (cli->keepalive_idx < 0) {
			syslog(LOG_DEBUG, "server closing connection to %s:%hu\n",
			       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));
			goto close;
		} else {
			syslog(LOG_DEBUG, "leaving client %s:%hu in keepalive mode\n",
			       inet_ntoa(cli->addr.sin_addr),
			       ntohs(cli->addr.sin_port));
			og_client_keepalive(loop, cli);
			og_client_reset_state(cli);
		}
		break;
	default:
		syslog(LOG_ERR, "unknown state, critical internal error\n");
		goto close;
	}
	return;
close:
	ev_timer_stop(loop, &cli->timer);
	og_client_release(loop, cli);
}

enum og_agent_state {
	OG_AGENT_RECEIVING_HEADER	= 0,
	OG_AGENT_RECEIVING_PAYLOAD,
	OG_AGENT_PROCESSING_RESPONSE,
};

static int og_agent_state_recv_hdr_rest(struct og_client *cli)
{
	char *ptr;

	ptr = strstr(cli->buf, "\r\n\r\n");
	if (!ptr)
		return 0;

	cli->msg_len = ptr - cli->buf + 4;

	ptr = strstr(cli->buf, "Content-Length: ");
	if (ptr) {
		sscanf(ptr, "Content-Length: %i[^\r\n]", &cli->content_length);
		if (cli->content_length < 0)
			return -1;
		cli->msg_len += cli->content_length;
	}

	return 1;
}

static void og_agent_reset_state(struct og_client *cli)
{
	cli->state = OG_AGENT_RECEIVING_HEADER;
	cli->buf_len = 0;
	cli->content_length = 0;
	memset(cli->buf, 0, sizeof(cli->buf));
}

static int og_dbi_get_computer_info(struct og_computer *computer,
				    struct in_addr addr)
{
	const char *msglog;
	struct og_dbi *dbi;
	dbi_result result;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}
	result = dbi_conn_queryf(dbi->conn,
				 "SELECT ordenadores.idordenador,"
				 "       ordenadores.nombreordenador,"
				 "       ordenadores.idaula,"
				 "       centros.idcentro FROM ordenadores "
				 "INNER JOIN aulas ON aulas.idaula=ordenadores.idaula "
				 "INNER JOIN centros ON centros.idcentro=aulas.idcentro "
				 "WHERE ordenadores.ip='%s'", inet_ntoa(addr));
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		og_dbi_close(dbi);
		return -1;
	}
	if (!dbi_result_next_row(result)) {
		syslog(LOG_ERR, "client does not exist in database (%s:%d)\n",
		       __func__, __LINE__);
		dbi_result_free(result);
		og_dbi_close(dbi);
		return -1;
	}

	computer->id = dbi_result_get_uint(result, "idordenador");
	computer->center = dbi_result_get_uint(result, "idcentro");
	computer->room = dbi_result_get_uint(result, "idaula");
	strncpy(computer->name,
		dbi_result_get_string(result, "nombreordenador"),
		OG_COMPUTER_NAME_MAXLEN);

	dbi_result_free(result);
	og_dbi_close(dbi);

	return 0;
}

static int og_resp_probe(struct og_client *cli, json_t *data)
{
	const char *status = NULL;
	const char *key;
	json_t *value;
	int err = 0;

	if (json_typeof(data) != JSON_OBJECT)
		return -1;

	json_object_foreach(data, key, value) {
		if (!strcmp(key, "status")) {
			err = og_json_parse_string(value, &status);
			if (err < 0)
				return err;
		} else {
			return -1;
		}
	}

	if (!strcmp(status, "BSY"))
		cli->status = OG_CLIENT_STATUS_BUSY;
	else if (!strcmp(status, "OPG"))
		cli->status = OG_CLIENT_STATUS_OGLIVE;

	return status ? 0 : -1;
}

static int og_resp_shell_run(struct og_client *cli, json_t *data)
{
	const char *output = NULL;
	char filename[4096];
	const char *key;
	json_t *value;
	int err = -1;
	FILE *file;

	if (json_typeof(data) != JSON_OBJECT)
		return -1;

	json_object_foreach(data, key, value) {
		if (!strcmp(key, "out")) {
			err = og_json_parse_string(value, &output);
			if (err < 0)
				return err;
		} else {
			return -1;
		}
	}

	if (!output) {
		syslog(LOG_ERR, "%s:%d: malformed json response\n",
		       __FILE__, __LINE__);
		return -1;
	}

	sprintf(filename, "/tmp/_Seconsola_%s", inet_ntoa(cli->addr.sin_addr));
	file = fopen(filename, "wt");
	if (!file) {
		syslog(LOG_ERR, "cannot open file %s: %s\n",
		       filename, strerror(errno));
		return -1;
	}

	fprintf(file, "%s", output);
	fclose(file);

	return 0;
}

struct og_computer_legacy  {
	char center[OG_DB_INT_MAXLEN + 1];
	char id[OG_DB_INT_MAXLEN + 1];
	char hardware[8192];
};

static int og_resp_hardware(json_t *data, struct og_client *cli)
{
	struct og_computer_legacy legacy = {};
	const char *hardware = NULL;
	struct og_computer computer;
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err = 0;
	bool res;

	if (json_typeof(data) != JSON_OBJECT)
		return -1;

	json_object_foreach(data, key, value) {
		if (!strcmp(key, "hardware")) {
			err = og_json_parse_string(value, &hardware);
			if (err < 0)
				return -1;
		} else {
			return -1;
		}
	}

	if (!hardware) {
		syslog(LOG_ERR, "malformed response json\n");
		return -1;
	}

	err = og_dbi_get_computer_info(&computer, cli->addr.sin_addr);
	if (err < 0)
		return -1;

	snprintf(legacy.center, sizeof(legacy.center), "%d", computer.center);
	snprintf(legacy.id, sizeof(legacy.id), "%d", computer.id);
	snprintf(legacy.hardware, sizeof(legacy.hardware), "%s", hardware);

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}

	res = actualizaHardware(dbi, legacy.hardware, legacy.id, computer.name,
				legacy.center);
	og_dbi_close(dbi);

	if (!res) {
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return -1;
	}

	return 0;
}

struct og_software_legacy {
	char software[8192];
	char center[OG_DB_INT_MAXLEN + 1];
	char part[OG_DB_SMALLINT_MAXLEN + 1];
	char id[OG_DB_INT_MAXLEN + 1];
};

static int og_resp_software(json_t *data, struct og_client *cli)
{
	struct og_software_legacy legacy = {};
	const char *partition = NULL;
	const char *software = NULL;
	struct og_computer computer;
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err = 0;
	bool res;

	if (json_typeof(data) != JSON_OBJECT)
		return -1;

	json_object_foreach(data, key, value) {
		if (!strcmp(key, "software"))
			err = og_json_parse_string(value, &software);
		else if (!strcmp(key, "partition"))
			err = og_json_parse_string(value, &partition);
		else
			return -1;

		if (err < 0)
			return -1;
	}

	if (!software || !partition) {
		syslog(LOG_ERR, "malformed response json\n");
		return -1;
	}

	err = og_dbi_get_computer_info(&computer, cli->addr.sin_addr);
	if (err < 0)
		return -1;

	snprintf(legacy.software, sizeof(legacy.software), "%s", software);
	snprintf(legacy.part, sizeof(legacy.part), "%s", partition);
	snprintf(legacy.id, sizeof(legacy.id), "%d", computer.id);
	snprintf(legacy.center, sizeof(legacy.center), "%d", computer.center);

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}

	res = actualizaSoftware(dbi, legacy.software, legacy.part, legacy.id,
				computer.name, legacy.center);
	og_dbi_close(dbi);

	if (!res) {
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return -1;
	}

	return 0;
}

#define OG_PARAMS_RESP_REFRESH	(OG_PARAM_PART_DISK |		\
				 OG_PARAM_PART_NUMBER |		\
				 OG_PARAM_PART_CODE |		\
				 OG_PARAM_PART_FILESYSTEM |	\
				 OG_PARAM_PART_OS |		\
				 OG_PARAM_PART_SIZE |		\
				 OG_PARAM_PART_USED_SIZE)

static int og_json_parse_partition_array(json_t *value,
					 struct og_partition *partitions)
{
	json_t *element;
	int i, err;

	if (json_typeof(value) != JSON_ARRAY)
		return -1;

	for (i = 0; i < json_array_size(value) && i < OG_PARTITION_MAX; i++) {
		element = json_array_get(value, i);

		err = og_json_parse_partition(element, &partitions[i],
					      OG_PARAMS_RESP_REFRESH);
		if (err < 0)
			return err;
	}

	return 0;
}

static int og_resp_refresh(json_t *data, struct og_client *cli)
{
	struct og_partition partitions[OG_PARTITION_MAX] = {};
	const char *serial_number = NULL;
	struct og_partition disk_setup;
	struct og_computer computer;
	char cfg[1024] = {};
	struct og_dbi *dbi;
	const char *key;
	unsigned int i;
	json_t *value;
	int err = 0;
	bool res;

	if (json_typeof(data) != JSON_OBJECT)
		return -1;

	json_object_foreach(data, key, value) {
		if (!strcmp(key, "disk_setup")) {
			err = og_json_parse_partition(value,
						      &disk_setup,
						      OG_PARAMS_RESP_REFRESH);
		} else if (!strcmp(key, "partition_setup")) {
			err = og_json_parse_partition_array(value, partitions);
		} else if (!strcmp(key, "serial_number")) {
			err = og_json_parse_string(value, &serial_number);
		} else {
			return -1;
		}

		if (err < 0)
			return err;
	}

	err = og_dbi_get_computer_info(&computer, cli->addr.sin_addr);
	if (err < 0)
		return -1;

	if (strlen(serial_number) > 0)
		snprintf(cfg, sizeof(cfg), "ser=%s\n", serial_number);

	if (!disk_setup.disk || !disk_setup.number || !disk_setup.code ||
	    !disk_setup.filesystem || !disk_setup.os || !disk_setup.size ||
	    !disk_setup.used_size)
		return -1;

	snprintf(cfg + strlen(cfg), sizeof(cfg) - strlen(cfg),
		 "disk=%s\tpar=%s\tcpt=%s\tfsi=%s\tsoi=%s\ttam=%s\tuso=%s\n",
		 disk_setup.disk, disk_setup.number, disk_setup.code,
		 disk_setup.filesystem, disk_setup.os, disk_setup.size,
		 disk_setup.used_size);

	for (i = 0; i < OG_PARTITION_MAX; i++) {
		if (!partitions[i].disk || !partitions[i].number ||
		    !partitions[i].code || !partitions[i].filesystem ||
		    !partitions[i].os || !partitions[i].size ||
		    !partitions[i].used_size)
			continue;

		snprintf(cfg + strlen(cfg), sizeof(cfg) - strlen(cfg),
			 "disk=%s\tpar=%s\tcpt=%s\tfsi=%s\tsoi=%s\ttam=%s\tuso=%s\n",
			 partitions[i].disk, partitions[i].number,
			 partitions[i].code, partitions[i].filesystem,
			 partitions[i].os, partitions[i].size,
			 partitions[i].used_size);
	}

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
				  __func__, __LINE__);
		return -1;
	}
	res = actualizaConfiguracion(dbi, cfg, computer.id);
	og_dbi_close(dbi);

	if (!res) {
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return -1;
	}

	return 0;
}

static int og_resp_image_create(json_t *data, struct og_client *cli)
{
	struct og_software_legacy soft_legacy;
	struct og_image_legacy img_legacy;
	const char *partition = NULL;
	const char *software = NULL;
	const char *image_id = NULL;
	struct og_computer computer;
	const char *disk = NULL;
	const char *code = NULL;
	const char *name = NULL;
	const char *repo = NULL;
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err = 0;
	bool res;

	if (json_typeof(data) != JSON_OBJECT)
		return -1;

	json_object_foreach(data, key, value) {
		if (!strcmp(key, "software"))
			err = og_json_parse_string(value, &software);
		else if (!strcmp(key, "partition"))
			err = og_json_parse_string(value, &partition);
		else if (!strcmp(key, "disk"))
			err = og_json_parse_string(value, &disk);
		else if (!strcmp(key, "code"))
			err = og_json_parse_string(value, &code);
		else if (!strcmp(key, "id"))
			err = og_json_parse_string(value, &image_id);
		else if (!strcmp(key, "name"))
			err = og_json_parse_string(value, &name);
		else if (!strcmp(key, "repository"))
			err = og_json_parse_string(value, &repo);
		else
			return -1;

		if (err < 0)
			return err;
	}

	if (!software || !partition || !disk || !code || !image_id || !name ||
	    !repo) {
		syslog(LOG_ERR, "malformed response json\n");
		return -1;
	}

	err = og_dbi_get_computer_info(&computer, cli->addr.sin_addr);
	if (err < 0)
		return -1;

	snprintf(soft_legacy.center, sizeof(soft_legacy.center), "%d",
		 computer.center);
	snprintf(soft_legacy.software, sizeof(soft_legacy.software), "%s",
		 software);
	snprintf(img_legacy.image_id, sizeof(img_legacy.image_id), "%s",
		 image_id);
	snprintf(soft_legacy.id, sizeof(soft_legacy.id), "%d", computer.id);
	snprintf(img_legacy.part, sizeof(img_legacy.part), "%s", partition);
	snprintf(img_legacy.disk, sizeof(img_legacy.disk), "%s", disk);
	snprintf(img_legacy.code, sizeof(img_legacy.code), "%s", code);
	snprintf(img_legacy.name, sizeof(img_legacy.name), "%s", name);
	snprintf(img_legacy.repo, sizeof(img_legacy.repo), "%s", repo);

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}

	res = actualizaSoftware(dbi,
				soft_legacy.software,
				img_legacy.part,
				soft_legacy.id,
				computer.name,
				soft_legacy.center);
	if (!res) {
		og_dbi_close(dbi);
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return -1;
	}

	res = actualizaCreacionImagen(dbi,
				      img_legacy.image_id,
				      img_legacy.disk,
				      img_legacy.part,
				      img_legacy.code,
				      img_legacy.repo,
				      soft_legacy.id);
	og_dbi_close(dbi);

	if (!res) {
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return -1;
	}

	return 0;
}

static int og_resp_image_restore(json_t *data, struct og_client *cli)
{
	struct og_software_legacy soft_legacy;
	struct og_image_legacy img_legacy;
	const char *partition = NULL;
	const char *image_id = NULL;
	struct og_computer computer;
	const char *disk = NULL;
	dbi_result query_result;
	struct og_dbi *dbi;
	const char *key;
	json_t *value;
	int err = 0;
	bool res;

	if (json_typeof(data) != JSON_OBJECT)
		return -1;

	json_object_foreach(data, key, value) {
		if (!strcmp(key, "partition"))
			err = og_json_parse_string(value, &partition);
		else if (!strcmp(key, "disk"))
			err = og_json_parse_string(value, &disk);
		else if (!strcmp(key, "image_id"))
			err = og_json_parse_string(value, &image_id);
		else
			return -1;

		if (err < 0)
			return err;
	}

	if (!partition || !disk || !image_id) {
		syslog(LOG_ERR, "malformed response json\n");
		return -1;
	}

	err = og_dbi_get_computer_info(&computer, cli->addr.sin_addr);
	if (err < 0)
		return -1;

	snprintf(img_legacy.image_id, sizeof(img_legacy.image_id), "%s",
		 image_id);
	snprintf(img_legacy.part, sizeof(img_legacy.part), "%s", partition);
	snprintf(img_legacy.disk, sizeof(img_legacy.disk), "%s", disk);
	snprintf(soft_legacy.id, sizeof(soft_legacy.id), "%d", computer.id);

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}

	query_result = dbi_conn_queryf(dbi->conn,
				       "SELECT idperfilsoft FROM imagenes "
				       " WHERE idimagen='%s'",
				       image_id);
	if (!query_result) {
		og_dbi_close(dbi);
		syslog(LOG_ERR, "failed to query database\n");
		return -1;
	}
	if (!dbi_result_next_row(query_result)) {
		dbi_result_free(query_result);
		og_dbi_close(dbi);
		syslog(LOG_ERR, "software profile does not exist in database\n");
		return -1;
	}
	snprintf(img_legacy.software_id, sizeof(img_legacy.software_id),
		 "%d", dbi_result_get_uint(query_result, "idperfilsoft"));
	dbi_result_free(query_result);

	res = actualizaRestauracionImagen(dbi,
					  img_legacy.image_id,
					  img_legacy.disk,
					  img_legacy.part,
					  soft_legacy.id,
					  img_legacy.software_id);
	og_dbi_close(dbi);

	if (!res) {
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return -1;
	}

	return 0;
}

static int og_dbi_update_action(struct og_client *cli, bool success)
{
	char end_date_string[24];
	struct tm *end_date;
	const char *msglog;
	struct og_dbi *dbi;
	uint8_t status = 2;
	dbi_result result;
	time_t now;

	if (!cli->last_cmd_id)
		return 0;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return -1;
	}

	time(&now);
	end_date = localtime(&now);

	sprintf(end_date_string, "%hu/%hhu/%hhu %hhu:%hhu:%hhu",
		end_date->tm_year + 1900, end_date->tm_mon + 1,
		end_date->tm_mday, end_date->tm_hour, end_date->tm_min,
		end_date->tm_sec);
	result = dbi_conn_queryf(dbi->conn,
				 "UPDATE acciones SET fechahorafin='%s', "
				 "estado=%d, resultado=%d WHERE idaccion=%d",
				 end_date_string, ACCION_FINALIZADA,
				 status - success, cli->last_cmd_id);

	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		og_dbi_close(dbi);
		return -1;
	}
	cli->last_cmd_id = 0;
	dbi_result_free(result);
	og_dbi_close(dbi);

	return 0;
}

static int og_agent_state_process_response(struct og_client *cli)
{
	json_error_t json_err;
	json_t *root;
	int err = -1;
	char *body;

	if (!strncmp(cli->buf, "HTTP/1.0 202 Accepted",
		     strlen("HTTP/1.0 202 Accepted"))) {
		og_dbi_update_action(cli, true);
		return 1;
	}

	if (strncmp(cli->buf, "HTTP/1.0 200 OK", strlen("HTTP/1.0 200 OK"))) {
		og_dbi_update_action(cli, false);
		return -1;
	}
	og_dbi_update_action(cli, true);

	if (!cli->content_length) {
		cli->last_cmd = OG_CMD_UNSPEC;
		return 0;
	}

	body = strstr(cli->buf, "\r\n\r\n") + 4;

	root = json_loads(body, 0, &json_err);
	if (!root) {
		syslog(LOG_ERR, "%s:%d: malformed json line %d: %s\n",
		       __FILE__, __LINE__, json_err.line, json_err.text);
		return -1;
	}

	switch (cli->last_cmd) {
	case OG_CMD_PROBE:
		err = og_resp_probe(cli, root);
		break;
	case OG_CMD_SHELL_RUN:
		err = og_resp_shell_run(cli, root);
		break;
	case OG_CMD_HARDWARE:
		err = og_resp_hardware(root, cli);
		break;
	case OG_CMD_SOFTWARE:
		err = og_resp_software(root, cli);
		break;
	case OG_CMD_REFRESH:
		err = og_resp_refresh(root, cli);
		break;
	case OG_CMD_SETUP:
		err = og_resp_refresh(root, cli);
		break;
	case OG_CMD_IMAGE_CREATE:
		err = og_resp_image_create(root, cli);
		break;
	case OG_CMD_IMAGE_RESTORE:
		err = og_resp_image_restore(root, cli);
		break;
	default:
		err = -1;
		break;
	}

	cli->last_cmd = OG_CMD_UNSPEC;

	return err;
}

static void og_agent_deliver_pending_cmd(struct og_client *cli)
{
	const struct og_cmd *cmd;

	cmd = og_cmd_find(inet_ntoa(cli->addr.sin_addr));
	if (!cmd)
		return;

	og_send_request(cmd->method, cmd->type, &cmd->params, cmd->json);
	cli->last_cmd_id = cmd->id;

	og_cmd_free(cmd);
}

static void og_agent_read_cb(struct ev_loop *loop, struct ev_io *io, int events)
{
	struct og_client *cli;
	int ret;

	cli = container_of(io, struct og_client, io);

	ret = og_client_recv(cli, events);
	if (ret <= 0)
		goto close;

	ev_timer_again(loop, &cli->timer);

	cli->buf_len += ret;
	if (cli->buf_len >= sizeof(cli->buf)) {
		syslog(LOG_ERR, "client request from %s:%hu is too long\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));
		goto close;
	}

	switch (cli->state) {
	case OG_AGENT_RECEIVING_HEADER:
		ret = og_agent_state_recv_hdr_rest(cli);
		if (ret < 0)
			goto close;
		if (!ret)
			return;

		cli->state = OG_AGENT_RECEIVING_PAYLOAD;
		/* Fall through. */
	case OG_AGENT_RECEIVING_PAYLOAD:
		/* Still not enough data to process request. */
		if (cli->buf_len < cli->msg_len)
			return;

		cli->state = OG_AGENT_PROCESSING_RESPONSE;
		/* fall through. */
	case OG_AGENT_PROCESSING_RESPONSE:
		ret = og_agent_state_process_response(cli);
		if (ret < 0) {
			syslog(LOG_ERR, "Failed to process HTTP request from %s:%hu\n",
			       inet_ntoa(cli->addr.sin_addr),
			       ntohs(cli->addr.sin_port));
			goto close;
		} else if (ret == 0) {
			og_agent_deliver_pending_cmd(cli);
		}

		syslog(LOG_DEBUG, "leaving client %s:%hu in keepalive mode\n",
		       inet_ntoa(cli->addr.sin_addr),
		       ntohs(cli->addr.sin_port));
		og_agent_reset_state(cli);
		break;
	default:
		syslog(LOG_ERR, "unknown state, critical internal error\n");
		goto close;
	}
	return;
close:
	ev_timer_stop(loop, &cli->timer);
	og_client_release(loop, cli);
}

static void og_client_timer_cb(struct ev_loop *loop, ev_timer *timer, int events)
{
	struct og_client *cli;

	cli = container_of(timer, struct og_client, timer);
	if (cli->keepalive_idx >= 0) {
		ev_timer_again(loop, &cli->timer);
		return;
	}
	syslog(LOG_ERR, "timeout request for client %s:%hu\n",
	       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));

	og_client_release(loop, cli);
}

static void og_agent_send_refresh(struct og_client *cli)
{
	struct og_msg_params params;
	int err;

	params.ips_array[0] = inet_ntoa(cli->addr.sin_addr);
	params.ips_array_len = 1;

	err = og_send_request(OG_METHOD_GET, OG_CMD_REFRESH, &params, NULL);
	if (err < 0) {
		syslog(LOG_ERR, "Can't send refresh to: %s\n",
		       params.ips_array[0]);
	} else {
		syslog(LOG_INFO, "Sent refresh to: %s\n",
		       params.ips_array[0]);
	}
}

static int socket_rest, socket_agent_rest;

static void og_server_accept_cb(struct ev_loop *loop, struct ev_io *io,
				int events)
{
	struct sockaddr_in client_addr;
	socklen_t addrlen = sizeof(client_addr);
	struct og_client *cli;
	int client_sd;

	if (events & EV_ERROR)
		return;

	client_sd = accept(io->fd, (struct sockaddr *)&client_addr, &addrlen);
	if (client_sd < 0) {
		syslog(LOG_ERR, "cannot accept client connection\n");
		return;
	}

	cli = (struct og_client *)calloc(1, sizeof(struct og_client));
	if (!cli) {
		close(client_sd);
		return;
	}
	memcpy(&cli->addr, &client_addr, sizeof(client_addr));
	if (io->fd == socket_agent_rest)
		cli->keepalive_idx = 0;
	else
		cli->keepalive_idx = -1;

	if (io->fd == socket_rest)
		cli->rest = true;
	else if (io->fd == socket_agent_rest)
		cli->agent = true;

	syslog(LOG_DEBUG, "connection from client %s:%hu\n",
	       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));

	if (io->fd == socket_agent_rest)
		ev_io_init(&cli->io, og_agent_read_cb, client_sd, EV_READ);
	else
		ev_io_init(&cli->io, og_client_read_cb, client_sd, EV_READ);

	ev_io_start(loop, &cli->io);
	if (io->fd == socket_agent_rest) {
		ev_timer_init(&cli->timer, og_client_timer_cb,
			      OG_AGENT_CLIENT_TIMEOUT, 0.);
	} else {
		ev_timer_init(&cli->timer, og_client_timer_cb,
			      OG_CLIENT_TIMEOUT, 0.);
	}
	ev_timer_start(loop, &cli->timer);
	list_add(&cli->list, &client_list);

	if (io->fd == socket_agent_rest) {
		og_agent_send_refresh(cli);
	}
}

static int og_socket_server_init(const char *port)
{
	struct sockaddr_in local;
	int sd, on = 1;

	sd = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (sd < 0) {
		syslog(LOG_ERR, "cannot create main socket\n");
		return -1;
	}
	setsockopt(sd, SOL_SOCKET, SO_REUSEPORT, &on, sizeof(int));

	local.sin_addr.s_addr = htonl(INADDR_ANY);
	local.sin_family = AF_INET;
	local.sin_port = htons(atoi(port));

	if (bind(sd, (struct sockaddr *) &local, sizeof(local)) < 0) {
		close(sd);
		syslog(LOG_ERR, "cannot bind socket\n");
		return -1;
	}

	listen(sd, 250);

	return sd;
}

int main(int argc, char *argv[])
{
	struct ev_io ev_io_server_rest, ev_io_agent_rest;
	int i;

	og_loop = ev_default_loop(0);

	if (signal(SIGPIPE, SIG_IGN) == SIG_ERR)
		exit(EXIT_FAILURE);

	openlog("ogAdmServer", LOG_PID, LOG_DAEMON);

	/*--------------------------------------------------------------------------------------------------------
	 Validación de parámetros de ejecución y lectura del fichero de configuración del servicio
	 ---------------------------------------------------------------------------------------------------------*/
	if (!validacionParametros(argc, argv, 1)) // Valida parámetros de ejecución
		exit(EXIT_FAILURE);

	if (!tomaConfiguracion(szPathFileCfg)) { // Toma parametros de configuracion
		exit(EXIT_FAILURE);
	}

	/*--------------------------------------------------------------------------------------------------------
	 // Inicializa array de información de los clientes
	 ---------------------------------------------------------------------------------------------------------*/
	for (i = 0; i < MAXIMOS_CLIENTES; i++) {
		tbsockets[i].ip[0] = '\0';
		tbsockets[i].cli = NULL;
	}
	/*--------------------------------------------------------------------------------------------------------
	 Creación y configuración del socket del servicio
	 ---------------------------------------------------------------------------------------------------------*/

	socket_rest = og_socket_server_init("8888");
	if (socket_rest < 0)
		exit(EXIT_FAILURE);

	ev_io_init(&ev_io_server_rest, og_server_accept_cb, socket_rest, EV_READ);
	ev_io_start(og_loop, &ev_io_server_rest);

	socket_agent_rest = og_socket_server_init("8889");
	if (socket_agent_rest < 0)
		exit(EXIT_FAILURE);

	ev_io_init(&ev_io_agent_rest, og_server_accept_cb, socket_agent_rest, EV_READ);
	ev_io_start(og_loop, &ev_io_agent_rest);

	if (og_dbi_schedule_get() < 0)
		exit(EXIT_FAILURE);

	og_schedule_next(og_loop);

	infoLog(1); // Inicio de sesión

	/* old log file has been deprecated. */
	og_log(97, false);

	syslog(LOG_INFO, "Waiting for connections\n");

	while (1)
		ev_loop(og_loop, 0);

	exit(EXIT_SUCCESS);
}
