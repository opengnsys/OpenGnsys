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
#include <ev.h>
#include <syslog.h>
#include <sys/ioctl.h>
#include <ifaddrs.h>

static char usuario[LONPRM]; // Usuario de acceso a la base de datos
static char pasguor[LONPRM]; // Password del usuario
static char datasource[LONPRM]; // Dirección IP del gestor de base de datos
static char catalog[LONPRM]; // Nombre de la base de datos
static char interface[LONPRM]; // Interface name

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

	servidoradm[0] = (char) NULL; //inicializar variables globales

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


		line = fgets(buf, sizeof(buf), fcfg);
	}

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

/* Shut down connection if there is no complete message after 10 seconds. */
#define OG_CLIENT_TIMEOUT	10

struct og_client {
	struct ev_io		io;
	struct ev_timer		timer;
	struct sockaddr_in	addr;
	enum og_client_state	state;
	char			buf[4096];
	unsigned int		buf_len;
	unsigned int		msg_len;
	int			keepalive_idx;
};

static inline int og_client_socket(const struct og_client *cli)
{
	return cli->io.fd;
}

// ________________________________________________________________________________________________________
// Función: Sondeo
//
//	Descripción:
//		Solicita a los clientes su disponibiliad para recibir comandos interactivos
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros del mensaje
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool Sondeo(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_APAGADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: respuestaSondeo
//
//	Descripción:
//		Recupera el estatus de los ordenadores solicitados leyendo la tabla de sockets
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros del mensaje
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool respuestaSondeo(TRAMA* ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);
	int i;
	long lSize;
	char *iph, *Ipes;

	iph = copiaParametro("iph",ptrTrama); // Toma dirección/es IP
	lSize = strlen(iph); // Calcula longitud de la cadena de direccion/es IPE/S
	Ipes = (char*) reservaMemoria(lSize + 1);
	if (Ipes == NULL) {
		liberaMemoria(iph);
		syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
		return false;
	}
	strcpy(Ipes, iph); // Copia cadena de IPES
	liberaMemoria(iph);
	initParametros(ptrTrama,0);
	strcpy(ptrTrama->parametros, "tso="); // Compone retorno tso (sistemas operativos de los clientes )
	for (i = 0; i < MAXIMOS_CLIENTES; i++) {
		if (strncmp(tbsockets[i].ip, "\0", 1) != 0) { // Si es un cliente activo
			if (contieneIP(Ipes, tbsockets[i].ip)) { // Si existe la IP en la cadena
				strcat(ptrTrama->parametros, tbsockets[i].ip); // Compone retorno
				strcat(ptrTrama->parametros, "/"); // "ip/sistema operativo;"
				strcat(ptrTrama->parametros, tbsockets[i].estado);
				strcat(ptrTrama->parametros, ";");
			}
		}
	}
	strcat(ptrTrama->parametros, "\r");
	liberaMemoria(Ipes);
	if (!mandaTrama(&socket_c, ptrTrama)) {
		syslog(LOG_ERR, "failed to send response to %s:%hu reason=%s\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
		       strerror(errno));
		return false;
	}
	return true;
}
// ________________________________________________________________________________________________________
// Función: Actualizar
//
//	Descripción:
//		Obliga a los clientes a iniciar sesión en el sistema
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros del mensaje
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool Actualizar(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_APAGADO))
		return false;

	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: Purgar
//
//	Descripción:
//		Detiene la ejecución del browser en el cliente
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros del mensaje
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool Purgar(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_APAGADO))
		return false;

	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: ConsolaRemota
//
//	Descripción:
// 		Envia un script al cliente, éste lo ejecuta y manda el archivo que genera la salida por pantalla
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros del mensaje
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool ConsolaRemota(TRAMA* ptrTrama, struct og_client *cli)
{
	char *iph, fileco[LONPRM], *ptrIpes[MAXIMOS_CLIENTES];;
	FILE* f;
	int i,lon;

	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	INTROaFINCAD(ptrTrama);
	/* Destruye contenido del fichero de eco anterior */
	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip del cliente
	lon = splitCadena(ptrIpes,iph,';');
	for (i = 0; i < lon; i++) {
		sprintf(fileco,"/tmp/_Seconsola_%s",ptrIpes[i]); // Nombre que tendra el archivo en el Servidor
		f = fopen(fileco, "wt");
		fclose(f);
	}
	liberaMemoria(iph);
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: EcoConsola
//
//	Descripción:
//		Solicita el eco de una consola remota almacenado en un archivo de eco
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros del mensaje
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool EcoConsola(TRAMA* ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);
	char *iph,fileco[LONPRM],*buffer;
	int lSize;

	INTROaFINCAD(ptrTrama);
	// Lee archivo de eco de consola
	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip del cliente
	sprintf(fileco,"/tmp/_Seconsola_%s",iph); // Nombre del archivo en el Servidor
	liberaMemoria(iph);
	lSize=lonArchivo(fileco);
	if(lSize>0){ // Si el fichero tiene contenido...
		initParametros(ptrTrama,lSize+LONGITUD_PARAMETROS);
		buffer=leeArchivo(fileco);
		sprintf(ptrTrama->parametros,"res=%s\r",buffer);
		liberaMemoria(buffer);
	}
	else{
		initParametros(ptrTrama,0);
		sprintf(ptrTrama->parametros,"res=\r");
	}
	ptrTrama->tipo=MSG_RESPUESTA; // Tipo de mensaje
	if (!mandaTrama(&socket_c, ptrTrama)) {
		syslog(LOG_ERR, "failed to send response to %s:%hu reason=%s\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
		       strerror(errno));
		return false;
	}
	return true;
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
// Función: hayHueco
// 
// 	Descripción:
// 		Esta función devuelve true o false dependiendo de que haya hueco en la tabla de sockets para un nuevo cliente.
// 	Parametros:
// 		- idx:   Primer indice libre que se podrn utilizar
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool hayHueco(int *idx)
{
	int i;

	for (i = 0; i < MAXIMOS_CLIENTES; i++) {
		if (strncmp(tbsockets[i].ip, "\0", 1) == 0) { // Hay un hueco
			*idx = i;
			return true;
		}
	}
	return false;
}
// ________________________________________________________________________________________________________
// Función: InclusionClienteWin
//
//	Descripción:
//		Esta función incorpora el socket de un nuevo cliente Windows o Linux a la tabla de clientes 
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool InclusionClienteWinLnx(TRAMA *ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);
	int res,idordenador,lon;
	char nombreordenador[LONFIL];

	res = procesoInclusionClienteWinLnx(socket_c, ptrTrama, &idordenador,
					    nombreordenador);

	// Prepara la trama de respuesta

	initParametros(ptrTrama,0);
	ptrTrama->tipo=MSG_RESPUESTA;
	lon = sprintf(ptrTrama->parametros, "nfn=RESPUESTA_InclusionClienteWinLnx\r");
	lon += sprintf(ptrTrama->parametros + lon, "ido=%d\r", idordenador);
	lon += sprintf(ptrTrama->parametros + lon, "npc=%s\r", nombreordenador);	
	lon += sprintf(ptrTrama->parametros + lon, "res=%d\r", res);	

	if (!mandaTrama(&socket_c, ptrTrama)) {
		syslog(LOG_ERR, "failed to send response to %s:%hu reason=%s\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
		       strerror(errno));
		return false;
	}
	return true;
}
// ________________________________________________________________________________________________________
// Función: procesoInclusionClienteWinLnx
//
//	Descripción:
//		Implementa el proceso de inclusión en el sistema del Cliente Windows o Linux
//	Parámetros de entrada:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Parámetros de salida:
//		- ido: Identificador del ordenador
//		- nombreordenador: Nombre del ordenador
//	Devuelve:
//		Código del error producido en caso de ocurrir algún error, 0 si el proceso es correcto
// ________________________________________________________________________________________________________
bool procesoInclusionClienteWinLnx(int socket_c, TRAMA *ptrTrama, int *idordenador, char *nombreordenador)
 {
	char msglog[LONSTD], sqlstr[LONSQL];
	Database db;
	Table tbl;
	char *iph;

	// Toma parámetros
	iph = copiaParametro("iph",ptrTrama); // Toma ip

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		liberaMemoria(iph);
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	// Recupera los datos del cliente
	sprintf(sqlstr,
			"SELECT idordenador,nombreordenador FROM ordenadores "
				" WHERE ordenadores.ip = '%s'", iph);

	if (!db.Execute(sqlstr, tbl)) {
		liberaMemoria(iph);
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		db.Close();
		return false;
	}

	if (tbl.ISEOF()) {
		liberaMemoria(iph);
		syslog(LOG_ERR, "client does not exist in database (%s:%d)\n",
		       __func__, __LINE__);
		db.liberaResult(tbl);
		db.Close();
		return false;
	}

	syslog(LOG_DEBUG, "Client %s requesting inclusion\n", iph);

	if (!tbl.Get("idordenador", *idordenador)) {
		liberaMemoria(iph);
		db.liberaResult(tbl);
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		db.Close();
		return false;
	}
	if (!tbl.Get("nombreordenador", nombreordenador)) {
		liberaMemoria(iph);
		db.liberaResult(tbl);
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		db.Close();
		return false;
	}
	db.liberaResult(tbl);
	db.Close();

	if (!registraCliente(iph)) { // Incluyendo al cliente en la tabla de sokets
		liberaMemoria(iph);
		syslog(LOG_ERR, "client table is full\n");
		return false;
	}
	liberaMemoria(iph);
	return true;
}
// ________________________________________________________________________________________________________
// Función: InclusionCliente
//
//	Descripción:
//		Esta función incorpora el socket de un nuevo cliente a la tabla de clientes y le devuelve alguna de sus propiedades:
//		nombre, identificador, tamaño de la caché , etc ...
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool InclusionCliente(TRAMA *ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);

	if (!procesoInclusionCliente(cli, ptrTrama)) {
		initParametros(ptrTrama,0);
		strcpy(ptrTrama->parametros, "nfn=RESPUESTA_InclusionCliente\rres=0\r");
		if (!mandaTrama(&socket_c, ptrTrama)) {
			syslog(LOG_ERR, "failed to send response to %s:%hu reason=%s\n",
			       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
			       strerror(errno));
			return false;
		}
	}
	return true;
}
// ________________________________________________________________________________________________________
// Función: procesoInclusionCliente
//
//	Descripción:
//		Implementa el proceso de inclusión en el sistema del Cliente
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool procesoInclusionCliente(struct og_client *cli, TRAMA *ptrTrama)
{
	int socket_c = og_client_socket(cli);
	char msglog[LONSTD], sqlstr[LONSQL];
	Database db;
	Table tbl;

	char *iph, *cfg;
	char nombreordenador[LONFIL];
	int lon, resul, idordenador, idmenu, cache, idproautoexec, idaula, idcentro;

	// Toma parámetros
	iph = copiaParametro("iph",ptrTrama); // Toma ip
	cfg = copiaParametro("cfg",ptrTrama); // Toma configuracion

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		liberaMemoria(iph);
		liberaMemoria(cfg);
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	// Recupera los datos del cliente
	sprintf(sqlstr,
			"SELECT ordenadores.*,aulas.idaula,centros.idcentro FROM ordenadores "
				" INNER JOIN aulas ON aulas.idaula=ordenadores.idaula"
				" INNER JOIN centros ON centros.idcentro=aulas.idcentro"
				" WHERE ordenadores.ip = '%s'", iph);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	if (tbl.ISEOF()) {
		syslog(LOG_ERR, "client does not exist in database (%s:%d)\n",
		       __func__, __LINE__);
		return false;
	}

	syslog(LOG_DEBUG, "Client %s requesting inclusion\n", iph);

	if (!tbl.Get("idordenador", idordenador)) {
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}
	if (!tbl.Get("nombreordenador", nombreordenador)) {
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}
	if (!tbl.Get("idmenu", idmenu)) {
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}
	if (!tbl.Get("cache", cache)) {
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}
	if (!tbl.Get("idproautoexec", idproautoexec)) {
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}
	if (!tbl.Get("idaula", idaula)) {
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}
	if (!tbl.Get("idcentro", idcentro)) {
		tbl.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}

	resul = actualizaConfiguracion(db, tbl, cfg, idordenador); // Actualiza la configuración del ordenador
	liberaMemoria(cfg);
	db.Close();

	if (!resul) {
		liberaMemoria(iph);
		syslog(LOG_ERR, "Cannot add client to database\n");
		return false;
	}

	if (!registraCliente(iph)) { // Incluyendo al cliente en la tabla de sokets
		liberaMemoria(iph);
		syslog(LOG_ERR, "client table is full\n");
		return false;
	}

	/*------------------------------------------------------------------------------------------------------------------------------
	 Prepara la trama de respuesta
	 -------------------------------------------------------------------------------------------------------------------------------*/
	initParametros(ptrTrama,0);
	ptrTrama->tipo=MSG_RESPUESTA;
	lon = sprintf(ptrTrama->parametros, "nfn=RESPUESTA_InclusionCliente\r");
	lon += sprintf(ptrTrama->parametros + lon, "ido=%d\r", idordenador);
	lon += sprintf(ptrTrama->parametros + lon, "npc=%s\r", nombreordenador);
	lon += sprintf(ptrTrama->parametros + lon, "che=%d\r", cache);
	lon += sprintf(ptrTrama->parametros + lon, "exe=%d\r", idproautoexec);
	lon += sprintf(ptrTrama->parametros + lon, "ida=%d\r", idaula);
	lon += sprintf(ptrTrama->parametros + lon, "idc=%d\r", idcentro);
	lon += sprintf(ptrTrama->parametros + lon, "res=%d\r", 1); // Confirmación proceso correcto

	if (!mandaTrama(&socket_c, ptrTrama)) {
		syslog(LOG_ERR, "failed to send response to %s:%hu reason=%s\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
		       strerror(errno));
		return false;
	}
	liberaMemoria(iph);
	return true;
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
bool actualizaConfiguracion(Database db, Table tbl, char *cfg, int ido)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	int lon, p, c,i, dato, swu, idsoi, idsfi,k;
	char *ptrPar[MAXPAR], *ptrCfg[6], *ptrDual[2], tbPar[LONSTD];
	char *ser, *disk, *par, *cpt, *sfi, *soi, *tam, *uso; // Parametros de configuración.

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
				sprintf(sqlstr, "UPDATE ordenadores SET numserie='%s'"
						" WHERE idordenador=%d AND numserie IS NULL",
						ser, ido);
				if (!db.Execute(sqlstr, tbl)) { // Error al insertar
					db.GetErrorErrStr(msglog);
					og_info(msglog);
					return false;
				}
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
			idsfi = checkDato(db, tbl, sfi, "sistemasficheros", "descripcion","idsistemafichero");
		}
		else
			idsfi=0;

		k=splitCadena(ptrDual, ptrCfg[4], '=');
		if(k==2){ // Sistema operativo detecdtado
			soi = ptrDual[1]; // Nombre del S.O. instalado
			/* Comprueba existencia del sistema operativo instalado */
			idsoi = checkDato(db, tbl, soi, "nombresos", "nombreso", "idnombreso");
		}
		else
			idsoi=0;

		splitCadena(ptrDual, ptrCfg[5], '=');
		tam = ptrDual[1]; // Tamaño de la partición

		splitCadena(ptrDual, ptrCfg[6], '=');
		uso = ptrDual[1]; // Porcentaje de uso del S.F.

		lon += sprintf(tbPar + lon, "(%s, %s),", disk, par);

		sprintf(sqlstr, "SELECT numdisk, numpar, codpar, tamano, uso, idsistemafichero, idnombreso"
				"  FROM ordenadores_particiones"
				" WHERE idordenador=%d AND numdisk=%s AND numpar=%s",
				ido, disk, par);


		if (!db.Execute(sqlstr, tbl)) {
			db.GetErrorErrStr(msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}
		if (tbl.ISEOF()) { // Si no existe el registro
			sprintf(sqlstr, "INSERT INTO ordenadores_particiones(idordenador,numdisk,numpar,codpar,tamano,uso,idsistemafichero,idnombreso,idimagen)"
					" VALUES(%d,%s,%s,0x%s,%s,%s,%d,%d,0)",
					ido, disk, par, cpt, tam, uso, idsfi, idsoi);


			if (!db.Execute(sqlstr, tbl)) { // Error al insertar
				db.GetErrorErrStr(msglog);
				og_info(msglog);
				return false;
			}
		} else { // Existe el registro
			swu = true; // Se supone que algún dato ha cambiado
			if (!tbl.Get("codpar", dato)) { // Toma dato
				tbl.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}
			if (strtol(cpt, NULL, 16) == dato) {// Parámetro tipo de partición (hexadecimal) igual al almacenado (decimal)
				if (!tbl.Get("tamano", dato)) { // Toma dato
					tbl.GetErrorErrStr(msglog); // Error al acceder al registro
					og_info(msglog);
					return false;
				}
				if (atoi(tam) == dato) {// Parámetro tamaño igual al almacenado
					if (!tbl.Get("idsistemafichero", dato)) { // Toma dato
						tbl.GetErrorErrStr(msglog); // Error al acceder al registro
						og_info(msglog);
						return false;
					}
					if (idsfi == dato) {// Parámetro sistema de fichero igual al almacenado
						if (!tbl.Get("idnombreso", dato)) { // Toma dato
							tbl.GetErrorErrStr(msglog); // Error al acceder al registro
							og_info(msglog);
							return false;
						}
						if (idsoi == dato) {// Parámetro sistema de fichero distinto al almacenado
							swu = false; // Todos los parámetros de la partición son iguales, no se actualiza
						}
					}
				}
			}
			if (swu) { // Hay que actualizar los parámetros de la partición
				sprintf(sqlstr, "UPDATE ordenadores_particiones SET "
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
				sprintf(sqlstr, "UPDATE ordenadores_particiones SET "
					" uso=%s"
					" WHERE idordenador=%d AND numdisk=%s AND numpar=%s",
					uso, ido, disk, par);
			}
			if (!db.Execute(sqlstr, tbl)) {
				db.GetErrorErrStr(msglog);
				syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
				       __func__, __LINE__, msglog);
				return false;
			}
		}
	}
	lon += sprintf(tbPar + lon, "(0,0)");
	// Eliminar particiones almacenadas que ya no existen
	sprintf(sqlstr, "DELETE FROM ordenadores_particiones WHERE idordenador=%d AND (numdisk, numpar) NOT IN (%s)",
			ido, tbPar);
	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
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

int checkDato(Database db, Table tbl, char *dato, const char *tabla,
		     const char *nomdato, const char *nomidentificador)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	int identificador;

	if (strlen(dato) == 0)
		return (0); // EL dato no tiene valor
	sprintf(sqlstr, "SELECT %s FROM %s WHERE %s ='%s'", nomidentificador,
			tabla, nomdato, dato);

	// Ejecuta consulta
	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return (0);
	}
	if (tbl.ISEOF()) { //  Software NO existente
		sprintf(sqlstr, "INSERT INTO %s (%s) VALUES('%s')", tabla, nomdato, dato);
		if (!db.Execute(sqlstr, tbl)) { // Error al insertar
			db.GetErrorErrStr(msglog); // Error al acceder al registro
			og_info(msglog);
			return (0);
		}
		// Recupera el identificador del software
		sprintf(sqlstr, "SELECT LAST_INSERT_ID() as identificador");
		if (!db.Execute(sqlstr, tbl)) { // Error al leer
			db.GetErrorErrStr(msglog); // Error al acceder al registro
			og_info(msglog);
			return (0);
		}
		if (!tbl.ISEOF()) { // Si existe registro
			if (!tbl.Get("identificador", identificador)) {
				tbl.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return (0);
			}
		}
	} else {
		if (!tbl.Get(nomidentificador, identificador)) { // Toma dato
			tbl.GetErrorErrStr(msglog); // Error al acceder al registro
			og_info(msglog);
			return (0);
		}
	}
	return (identificador);
}
// ________________________________________________________________________________________________________
// Función: registraCliente
//
//	Descripción:
//		 Incluye al cliente en la tabla de sokets
//	Parámetros:
//		- iph: Dirección ip del cliente
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool registraCliente(char *iph)
{
	int idx;

	if (!clienteExistente(iph, &idx)) { // Si no existe la IP ...
		if (!hayHueco(&idx)) { // Busca hueco para el nuevo cliente
			return false; // No hay huecos
		}
	}
	strcpy(tbsockets[idx].ip, iph); // Copia IP
	strcpy(tbsockets[idx].estado, CLIENTE_INICIANDO); // Actualiza el estado del cliente
	return true;
}
// ________________________________________________________________________________________________________
// Función: AutoexecCliente
//
//	Descripción:
//		Envía archivo de autoexec al cliente
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool AutoexecCliente(TRAMA *ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);
	int lon;
	char *iph, *exe, msglog[LONSTD];
	Database db;
	FILE *fileexe;
	char fileautoexec[LONPRM];
	char parametros[LONGITUD_PARAMETROS];

	iph = copiaParametro("iph",ptrTrama); // Toma dirección IP del cliente
	exe = copiaParametro("exe",ptrTrama); // Toma identificador del procedimiento inicial

	sprintf(fileautoexec, "/tmp/Sautoexec-%s", iph);
	liberaMemoria(iph);
	fileexe = fopen(fileautoexec, "wb"); // Abre fichero de script
	if (fileexe == NULL) {
		syslog(LOG_ERR, "cannot create temporary file\n");
		return false;
	}

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	initParametros(ptrTrama,0);
	if (recorreProcedimientos(db, parametros, fileexe, exe)) {
		lon = sprintf(ptrTrama->parametros, "nfn=RESPUESTA_AutoexecCliente\r");
		lon += sprintf(ptrTrama->parametros + lon, "nfl=%s\r", fileautoexec);
		lon += sprintf(ptrTrama->parametros + lon, "res=1\r");
	} else {
		lon = sprintf(ptrTrama->parametros, "nfn=RESPUESTA_AutoexecCliente\r");
		lon += sprintf(ptrTrama->parametros + lon, "res=0\r");
	}

	db.Close();
	fclose(fileexe);

	if (!mandaTrama(&socket_c, ptrTrama)) {
		liberaMemoria(exe);
		syslog(LOG_ERR, "failed to send response to %s:%hu reason=%s\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
		       strerror(errno));
		return false;
	}
	liberaMemoria(exe);
	return true;
}
// ________________________________________________________________________________________________________
// Función: recorreProcedimientos
//
//	Descripción:
//		Crea un archivo con el código de un procedimiento separando cada comando  por un salto de linea
//	Parámetros:
//		Database db,char* parametros,FILE* fileexe,char* idp
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool recorreProcedimientos(Database db, char *parametros, FILE *fileexe, char *idp)
{
	int procedimientoid, lsize;
	char idprocedimiento[LONPRM], msglog[LONSTD], sqlstr[LONSQL];
	Table tbl;

	/* Busca procedimiento */
	sprintf(sqlstr,
			"SELECT procedimientoid,parametros FROM procedimientos_acciones"
				" WHERE idprocedimiento=%s ORDER BY orden", idp);
	// Ejecuta consulta
	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	while (!tbl.ISEOF()) { // Recorre procedimientos
		if (!tbl.Get("procedimientoid", procedimientoid)) { // Toma dato
			tbl.GetErrorErrStr(msglog); // Error al acceder al registro
			og_info(msglog);
			return false;
		}
		if (procedimientoid > 0) { // Procedimiento recursivo
			sprintf(idprocedimiento, "%d", procedimientoid);
			if (!recorreProcedimientos(db, parametros, fileexe, idprocedimiento)) {
				return false;
			}
		} else {
			if (!tbl.Get("parametros", parametros)) { // Toma dato
				tbl.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}
			strcat(parametros, "@");
			lsize = strlen(parametros);
			fwrite(parametros, 1, lsize, fileexe); // Escribe el código a ejecutar
		}
		tbl.MoveNext();
	}
	return true;
}
// ________________________________________________________________________________________________________
// Función: ComandosPendientes
//
//	Descripción:
//		Esta función busca en la base de datos,comandos pendientes de ejecutar por un  ordenador  concreto
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool ComandosPendientes(TRAMA *ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);
	char *ido,*iph,pids[LONPRM];
	int ids, idx;

	iph = copiaParametro("iph",ptrTrama); // Toma dirección IP
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!clienteExistente(iph, &idx)) { // Busca índice del cliente
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "client does not exist\n");
		return false;
	}
	if (buscaComandos(ido, ptrTrama, &ids)) { // Existen comandos pendientes
		ptrTrama->tipo = MSG_COMANDO;
		sprintf(pids, "\rids=%d\r", ids);
		strcat(ptrTrama->parametros, pids);
		strcpy(tbsockets[idx].estado, CLIENTE_OCUPADO);
	} else {
		initParametros(ptrTrama,0);
		strcpy(ptrTrama->parametros, "nfn=NoComandosPtes\r");
	}
	if (!mandaTrama(&socket_c, ptrTrama)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to send response to %s:%hu reason=%s\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port),
		       strerror(errno));
		return false;
	}
	liberaMemoria(iph);
	liberaMemoria(ido);	
	return true;
}
// ________________________________________________________________________________________________________
// Función: buscaComandos
//
//	Descripción:
//		Busca en la base de datos,comandos pendientes de ejecutar por el cliente
//	Parámetros:
//		- ido: Identificador del ordenador
//		- cmd: Parámetros del comando (Salida)
//		- ids: Identificador de la sesion(Salida)
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool buscaComandos(char *ido, TRAMA *ptrTrama, int *ids)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	Database db;
	Table tbl;
	int lonprm;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	sprintf(sqlstr,"SELECT sesion,parametros,length( parametros) as lonprm"\
			" FROM acciones WHERE idordenador=%s AND estado='%d' ORDER BY idaccion", ido, ACCION_INICIADA);
	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (tbl.ISEOF()) {
		db.Close();
		return false; // No hay comandos pendientes
	} else { // Busca entre todas las acciones de diversos ambitos
		if (!tbl.Get("sesion", *ids)) { // Toma identificador de la sesion
			tbl.GetErrorErrStr(msglog); // Error al acceder al registro
			og_info(msglog);
			return false;
		}
		if (!tbl.Get("lonprm", lonprm)) { // Toma parámetros del comando
			tbl.GetErrorErrStr(msglog); // Error al acceder al registro
			og_info(msglog);
			return false;
		}
		if(!initParametros(ptrTrama,lonprm+LONGITUD_PARAMETROS)){
			db.Close();
			syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
			return false;
		}
		if (!tbl.Get("parametros", ptrTrama->parametros)) { // Toma parámetros del comando
			tbl.GetErrorErrStr(msglog); // Error al acceder al registro
			og_info(msglog);
			return false;
		}
	}
	db.Close();
	return true; // Hay comandos pendientes, se toma el primero de la cola
}
// ________________________________________________________________________________________________________
// Función: DisponibilidadComandos
//
//	Descripción:
//		Esta función habilita a un cliente para recibir comandos desde la consola
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
//
static bool DisponibilidadComandos(TRAMA *ptrTrama, struct og_client *cli)
{
	char *iph, *tpc;
	int idx;

	iph = copiaParametro("iph",ptrTrama); // Toma ip
	if (!clienteExistente(iph, &idx)) { // Busca índice del cliente
		liberaMemoria(iph);
		syslog(LOG_ERR, "client does not exist\n");
		return false;
	}
	tpc = copiaParametro("tpc",ptrTrama); // Tipo de cliente (Plataforma y S.O.)
	strcpy(tbsockets[idx].estado, tpc);
	cli->keepalive_idx = idx;
	liberaMemoria(iph);
	liberaMemoria(tpc);		
	return true;
}
// ________________________________________________________________________________________________________
// Función: respuestaEstandar
//
//	Descripción:
//		Esta función actualiza la base de datos con el resultado de la ejecución de un comando con seguimiento
//	Parámetros:
//		- res: resultado de la ejecución del comando
//		- der: Descripción del error si hubiese habido
//		- iph: Dirección IP
//		- ids: identificador de la sesión
//		- ido: Identificador del ordenador que notifica
//		- db: Objeto base de datos (operativo)
//		- tbl: Objeto tabla
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool respuestaEstandar(TRAMA *ptrTrama, char *iph, char *ido, Database db,
		Table tbl)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	char *res, *ids, *der;
	char fechafin[LONPRM];
	struct tm* st;
	int idaccion;

	ids = copiaParametro("ids",ptrTrama); // Toma identificador de la sesión

	if (ids == NULL) // No existe seguimiento de la acción
		return true;

	if (atoi(ids) == 0){ // No existe seguimiento de la acción
		liberaMemoria(ids);
		return true;
	}

	sprintf(sqlstr,
			"SELECT * FROM acciones WHERE idordenador=%s"
			" AND sesion=%s ORDER BY idaccion", ido,ids);

	liberaMemoria(ids);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (tbl.ISEOF()) {
		syslog(LOG_ERR, "no actions available\n");
		return true;
	}
	if (!tbl.Get("idaccion", idaccion)) { // Toma identificador de la accion
		tbl.GetErrorErrStr(msglog); // Error al acceder al registro
		og_info(msglog);
		return false;
	}
	st = tomaHora();
	sprintf(fechafin, "%d/%d/%d %d:%d:%d", st->tm_year + 1900, st->tm_mon + 1,
			st->tm_mday, st->tm_hour, st->tm_min, st->tm_sec);

	res = copiaParametro("res",ptrTrama); // Toma resultado
	der = copiaParametro("der",ptrTrama); // Toma descripción del error (si hubiera habido)
	
	sprintf(sqlstr,
			"UPDATE acciones"\
			"   SET resultado='%s',estado='%d',fechahorafin='%s',descrinotificacion='%s'"\
			" WHERE idordenador=%s AND idaccion=%d",
			res, ACCION_FINALIZADA, fechafin, der, ido, idaccion);
			
	if (!db.Execute(sqlstr, tbl)) { // Error al actualizar
		liberaMemoria(res);
		liberaMemoria(der);
		db.GetErrorErrStr(msglog);
		og_info(msglog);
		return false;
	}
	
	liberaMemoria(der);
	
	if (atoi(res) == ACCION_FALLIDA) {
		liberaMemoria(res);
		return false; // Error en la ejecución del comando
	}

	liberaMemoria(res);
	return true;
}
// ________________________________________________________________________________________________________
// Función: enviaComando
//
//	Descripción:
//		Envía un comando a los clientes
//	Parámetros:
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//		- estado: Estado en el se deja al cliente mientras se ejecuta el comando
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool enviaComando(TRAMA* ptrTrama, const char *estado)
{
	char *iph, *Ipes, *ptrIpes[MAXIMOS_CLIENTES];
	int i, idx, lon;

	iph = copiaParametro("iph",ptrTrama); // Toma dirección/es IP
	lon = strlen(iph); // Calcula longitud de la cadena de direccion/es IPE/S
	Ipes = (char*) reservaMemoria(lon + 1);
	if (Ipes == NULL) {
		syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
		return false;
	}
	
	strcpy(Ipes, iph); // Copia cadena de IPES
	liberaMemoria(iph);

	lon = splitCadena(ptrIpes, Ipes, ';');
	FINCADaINTRO(ptrTrama);
	for (i = 0; i < lon; i++) {
		if (clienteDisponible(ptrIpes[i], &idx)) { // Si el cliente puede recibir comandos
			int sock = tbsockets[idx].cli ? tbsockets[idx].cli->io.fd : -1;

			strcpy(tbsockets[idx].estado, estado); // Actualiza el estado del cliente
			if (!mandaTrama(&sock, ptrTrama)) {
				syslog(LOG_ERR, "failed to send response to %s:%s\n",
				       ptrIpes[i], strerror(errno));
				return false;
			}
			//close(tbsockets[idx].sock); // Cierra el socket del cliente hasta nueva disponibilidad
		}
	}
	liberaMemoria(Ipes);
	return true;
}
//______________________________________________________________________________________________________
// Función: respuestaConsola
//
//	Descripción:
// 		Envia una respuesta a la consola sobre el resultado de la ejecución de un comando
//	Parámetros:
//		- socket_c: (Salida) Socket utilizado para el envío
//		- res: Resultado del envío del comando
// 	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
bool respuestaConsola(int socket_c, TRAMA *ptrTrama, int res)
{
	initParametros(ptrTrama,0);
	sprintf(ptrTrama->parametros, "res=%d\r", res);
	if (!mandaTrama(&socket_c, ptrTrama)) {
		syslog(LOG_ERR, "%s:%d failed to send response: %s\n",
		       __func__, __LINE__, strerror(errno));
		return false;
	}
	return true;
}
// ________________________________________________________________________________________________________
// Función: Arrancar
//
//	Descripción:
//		Procesa el comando Arrancar
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool Arrancar(TRAMA* ptrTrama, struct og_client *cli)
{
	char *iph,*mac,*mar;
	bool res;

	iph = copiaParametro("iph",ptrTrama); // Toma dirección/es IP
	mac = copiaParametro("mac",ptrTrama); // Toma dirección/es MAC
	mar = copiaParametro("mar",ptrTrama); // Método de arranque (Broadcast o Unicast)

	res=Levanta(iph,mac,mar);

	liberaMemoria(iph);
	liberaMemoria(mac);
	liberaMemoria(mar);

	if(!res){
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}

	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
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
bool Levanta(char *iph, char *mac, char *mar)
{
	char *ptrIP[MAXIMOS_CLIENTES],*ptrMacs[MAXIMOS_CLIENTES];
	unsigned int on = 1;
	sockaddr_in local;
	int i, lon, res;
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

	lon = splitCadena(ptrIP, iph, ';');
	lon = splitCadena(ptrMacs, mac, ';');
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
	free(ifaddr);

	ret = sendto(sd, msg, sizeof(*msg), 0,
		     (sockaddr *)client, sizeof(*client));
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
		     (sockaddr *)client, sizeof(*client));
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
	char HDaddress_bin[OG_WOL_MACADDR_LEN];
	struct sockaddr_in WakeUpCliente;
	struct wol_msg Trama_WakeUp;
	struct in_addr addr;
	bool ret;
	int i;

	for (i = 0; i < 6; i++) // Primera secuencia de la trama Wake Up (0xFFFFFFFFFFFF)
		Trama_WakeUp.secuencia_FF[i] = 0xFF;

	sscanf(mac, "%02x%02x%02x%02x%02x%02x",
	       (unsigned int *)&HDaddress_bin[0],
	       (unsigned int *)&HDaddress_bin[1],
	       (unsigned int *)&HDaddress_bin[2],
	       (unsigned int *)&HDaddress_bin[3],
	       (unsigned int *)&HDaddress_bin[4],
	       (unsigned int *)&HDaddress_bin[5]);

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
// Función: RESPUESTA_Arrancar
//
//	Descripción:
//		Respuesta del cliente al comando Arrancar
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_Arrancar(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	int i;
	char *iph, *ido;
	char *tpc;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false;
	}

	tpc = copiaParametro("tpc",ptrTrama); // Tipo de cliente (Plataforma y S.O.)
	if (clienteExistente(iph, &i)) // Actualiza estado
		strcpy(tbsockets[i].estado, tpc);
		
	liberaMemoria(iph);
	liberaMemoria(ido);
	liberaMemoria(tpc);
	
	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
// Función: Apagar
//
//	Descripción:
//		Procesa el comando Apagar
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool Apagar(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_Apagar
//
//	Descripción:
//		Respuesta del cliente al comando Apagar
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_Apagar(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	int i;
	char *iph, *ido;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false; // Error al registrar notificacion
	}

	if (clienteExistente(iph, &i)) // Actualiza estado
		strcpy(tbsockets[i].estado, CLIENTE_APAGADO);
	
	liberaMemoria(iph);
	liberaMemoria(ido);
	
	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
// Función: Reiniciar
//
//	Descripción:
//		Procesa el comando Reiniciar
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool Reiniciar(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_Reiniciar
//
//	Descripción:
//		Respuesta del cliente al comando Reiniciar
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_Reiniciar(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	int i;
	char *iph, *ido;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false; // Error al registrar notificacion
	}

	if (clienteExistente(iph, &i)) // Actualiza estado
		strcpy(tbsockets[i].estado, CLIENTE_APAGADO);
	
	liberaMemoria(iph);
	liberaMemoria(ido);

	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
// Función: IniciarSesion
//
//	Descripción:
//		Procesa el comando Iniciar Sesión
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool IniciarSesion(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_IniciarSesion
//
//	Descripción:
//		Respuesta del cliente al comando Iniciar Sesión
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_IniciarSesion(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	int i;
	char *iph, *ido;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false; // Error al registrar notificacion
	}

	if (clienteExistente(iph, &i)) // Actualiza estado
		strcpy(tbsockets[i].estado, CLIENTE_APAGADO);
		
	liberaMemoria(iph);
	liberaMemoria(ido);
		
	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
// Función: CrearImagen
//
//	Descripción:
//		Crea una imagen de una partición de un disco y la guarda o bien en un repositorio
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool CrearImagen(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_CrearImagen
//
//	Descripción:
//		Respuesta del cliente al comando CrearImagen
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_CrearImagen(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	char *iph, *dsk, *par, *cpt, *ipr, *ido;
	char *idi;
	bool res;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false; // Error al registrar notificacion
	}

	// Acciones posteriores
	idi = copiaParametro("idi",ptrTrama);
	dsk = copiaParametro("dsk",ptrTrama);
	par = copiaParametro("par",ptrTrama);
	cpt = copiaParametro("cpt",ptrTrama);
	ipr = copiaParametro("ipr",ptrTrama);

	res=actualizaCreacionImagen(db, tbl, idi, dsk, par, cpt, ipr, ido);

	liberaMemoria(idi);
	liberaMemoria(par);
	liberaMemoria(cpt);
	liberaMemoria(ipr);

	if(!res){
		syslog(LOG_ERR, "Problem processing update\n");
		db.Close();
		return false;
	}

	db.Close(); // Cierra conexión
	return true;
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
bool actualizaCreacionImagen(Database db, Table tbl, char *idi, char *dsk,
			     char *par, char *cpt, char *ipr, char *ido)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	int idr,ifs;

	/* Toma identificador del repositorio correspondiente al ordenador modelo */
	snprintf(sqlstr, LONSQL,
			"SELECT repositorios.idrepositorio"
			"  FROM repositorios"
			"  LEFT JOIN ordenadores USING (idrepositorio)"
			" WHERE repositorios.ip='%s' AND ordenadores.idordenador=%s", ipr, ido);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!tbl.Get("idrepositorio", idr)) { // Toma dato
		tbl.GetErrorErrStr(msglog); // Error al acceder al registro
		og_info(msglog);
		return false;
	}

	/* Toma identificador del perfilsoftware */
	snprintf(sqlstr, LONSQL,
			"SELECT idperfilsoft"
			"  FROM ordenadores_particiones"
			" WHERE idordenador=%s AND numdisk=%s AND numpar=%s", ido, dsk, par);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!tbl.Get("idperfilsoft", ifs)) { // Toma dato
		tbl.GetErrorErrStr(msglog); // Error al acceder al registro
		og_info(msglog);
		return false;
	}

	/* Actualizar los datos de la imagen */
	snprintf(sqlstr, LONSQL,
		"UPDATE imagenes"
		"   SET idordenador=%s, numdisk=%s, numpar=%s, codpar=%s,"
		"       idperfilsoft=%d, idrepositorio=%d,"
		"       fechacreacion=NOW(), revision=revision+1"
		" WHERE idimagen=%s", ido, dsk, par, cpt, ifs, idr, idi);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	/* Actualizar los datos en el cliente */
	snprintf(sqlstr, LONSQL,
		"UPDATE ordenadores_particiones"
		"   SET idimagen=%s, revision=(SELECT revision FROM imagenes WHERE idimagen=%s),"
		"       fechadespliegue=NOW()"
		" WHERE idordenador=%s AND numdisk=%s AND numpar=%s",
		idi, idi, ido, dsk, par);
	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	return true;
}
// ________________________________________________________________________________________________________
// Función: CrearImagenBasica
//
//	Descripción:
//		Crea una imagen basica usando sincronización
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool CrearImagenBasica(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_CrearImagenBasica
//
//	Descripción:
//		Respuesta del cliente al comando CrearImagenBasica
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_CrearImagenBasica(TRAMA* ptrTrama, struct og_client *cli)
{
	// La misma respuesta que la creación de imagen monolítica
	return RESPUESTA_CrearImagen(ptrTrama, cli);
}
// ________________________________________________________________________________________________________
// Función: CrearSoftIncremental
//
//	Descripción:
//		Crea una imagen incremental entre una partición de un disco y una imagen ya creada guardandola en el
//		mismo repositorio y en la misma carpeta donde está la imagen básica
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool CrearSoftIncremental(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_CrearSoftIncremental
//
//	Descripción:
//		Respuesta del cliente al comando crearImagenDiferencial
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_CrearSoftIncremental(TRAMA* ptrTrama, struct og_client *cli)
{
	Database db;
	Table tbl;
	char *iph,*par,*ido,*idf;
	int ifs;
	char msglog[LONSTD],sqlstr[LONSQL];

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false;
	}

	par = copiaParametro("par",ptrTrama);

	/* Toma identificador del perfilsoftware creado por el inventario de software */
	sprintf(sqlstr,"SELECT idperfilsoft FROM ordenadores_particiones WHERE idordenador=%s AND numpar=%s",ido,par);
	
	liberaMemoria(iph);
	liberaMemoria(ido);	
	liberaMemoria(par);	

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!tbl.Get("idperfilsoft", ifs)) { // Toma dato
		tbl.GetErrorErrStr(msglog); // Error al acceder al registro
		og_info(msglog);
		return false;
	}

	/* Actualizar los datos de la imagen */
	idf = copiaParametro("idf",ptrTrama);
	sprintf(sqlstr,"UPDATE imagenes SET idperfilsoft=%d WHERE idimagen=%s",ifs,idf);
	liberaMemoria(idf);	

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
// Función: RestaurarImagen
//
//	Descripción:
//		Restaura una imagen en una partición
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RestaurarImagen(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RestaurarImagenBasica
//
//	Descripción:
//		Restaura una imagen básica en una partición
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RestaurarImagenBasica(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RestaurarSoftIncremental
//
//	Descripción:
//		Restaura una imagen básica junto con software incremental en una partición
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RestaurarSoftIncremental(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_RestaurarImagen
//
//	Descripción:
//		Respuesta del cliente al comando RestaurarImagen
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
//
static bool RESPUESTA_RestaurarImagen(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	bool res;
	char *iph, *ido, *idi, *dsk, *par, *ifs, *cfg;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false;
	}

	// Acciones posteriores
	idi = copiaParametro("idi",ptrTrama); // Toma identificador de la imagen
	dsk = copiaParametro("dsk",ptrTrama); // Número de disco
	par = copiaParametro("par",ptrTrama); // Número de partición
	ifs = copiaParametro("ifs",ptrTrama); // Identificador del perfil software contenido
	cfg = copiaParametro("cfg",ptrTrama); // Configuración de discos
	if(cfg){
		actualizaConfiguracion(db, tbl, cfg, atoi(ido)); // Actualiza la configuración del ordenador
		liberaMemoria(cfg);	
	}
	res=actualizaRestauracionImagen(db, tbl, idi, dsk, par, ido, ifs);
	
	liberaMemoria(iph);
	liberaMemoria(ido);
	liberaMemoria(idi);
	liberaMemoria(par);
	liberaMemoria(ifs);

	if(!res){
		syslog(LOG_ERR, "Problem after restoring image\n");
		db.Close();
		return false;
	}

	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
//
// Función: RESPUESTA_RestaurarImagenBasica
//
//	Descripción:
//		Respuesta del cliente al comando RestaurarImagen
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
//
static bool RESPUESTA_RestaurarImagenBasica(TRAMA* ptrTrama, struct og_client *cli)
{
	return RESPUESTA_RestaurarImagen(ptrTrama, cli);
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_RestaurarSoftIncremental
//
//	Descripción:
//		Respuesta del cliente al comando RestaurarSoftIncremental
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_RestaurarSoftIncremental(TRAMA* ptrTrama, struct og_client *cli)
{
	return RESPUESTA_RestaurarImagen(ptrTrama, cli);
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
bool actualizaRestauracionImagen(Database db, Table tbl, char *idi,
				 char *dsk, char *par, char *ido, char *ifs)
{
	char msglog[LONSTD], sqlstr[LONSQL];

	/* Actualizar los datos de la imagen */
	snprintf(sqlstr, LONSQL,
			"UPDATE ordenadores_particiones"
			"   SET idimagen=%s, idperfilsoft=%s, fechadespliegue=NOW(),"
			"       revision=(SELECT revision FROM imagenes WHERE idimagen=%s),"
			"       idnombreso=IFNULL((SELECT idnombreso FROM perfilessoft WHERE idperfilsoft=%s),0)"
			" WHERE idordenador=%s AND numdisk=%s AND numpar=%s", idi, ifs, idi, ifs, ido, dsk, par);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	return true;
}
// ________________________________________________________________________________________________________
// Función: Configurar
//
//	Descripción:
//		Configura la tabla de particiones
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool Configurar(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_Configurar
//
//	Descripción:
//		Respuesta del cliente al comando Configurar
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
//
static bool RESPUESTA_Configurar(TRAMA* ptrTrama, struct og_client *ci)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	bool res;
	char *iph, *ido,*cfg;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false;
	}

	cfg = copiaParametro("cfg",ptrTrama); // Toma configuración de particiones
	res=actualizaConfiguracion(db, tbl, cfg, atoi(ido)); // Actualiza la configuración del ordenador
	
	liberaMemoria(iph);
	liberaMemoria(ido);	
	liberaMemoria(cfg);	

	if(!res){
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return false;
	}

	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
// Función: EjecutarScript
//
//	Descripción:
//		Ejecuta un script de código
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool EjecutarScript(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_EjecutarScript
//
//	Descripción:
//		Respuesta del cliente al comando EjecutarScript
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_EjecutarScript(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	char *iph, *ido,*cfg;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false;
	}
	
	cfg = copiaParametro("cfg",ptrTrama); // Toma configuración de particiones
	if(cfg){
		actualizaConfiguracion(db, tbl, cfg, atoi(ido)); // Actualiza la configuración del ordenador
		liberaMemoria(cfg);	
	}

	liberaMemoria(iph);
	liberaMemoria(ido);

	
	db.Close(); // Cierra conexión
	return true;
}
// ________________________________________________________________________________________________________
// Función: InventarioHardware
//
//	Descripción:
//		Solicita al cliente un inventario de su hardware
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool InventarioHardware(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_InventarioHardware
//
//	Descripción:
//		Respuesta del cliente al comando InventarioHardware
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_InventarioHardware(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	bool res;
	char *iph, *ido, *idc, *npc, *hrd, *buffer;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip del cliente
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del cliente

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false;
	}
	// Lee archivo de inventario enviado anteriormente
	hrd = copiaParametro("hrd",ptrTrama);
	buffer = rTrim(leeArchivo(hrd));
	
	npc = copiaParametro("npc",ptrTrama); 
	idc = copiaParametro("idc",ptrTrama); // Toma identificador del Centro
	
	if (buffer) 
		res=actualizaHardware(db, tbl, buffer, ido, npc, idc);
	
	liberaMemoria(iph);
	liberaMemoria(ido);			
	liberaMemoria(npc);			
	liberaMemoria(idc);		
	liberaMemoria(buffer);		
	
	if(!res){
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return false;
	}
		
	db.Close(); // Cierra conexión
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
bool actualizaHardware(Database db, Table tbl, char *hrd, char *ido, char *npc,
		       char *idc)
{
	char msglog[LONSTD], sqlstr[LONSQL];
	int idtipohardware, idperfilhard;
	int lon, i, j, aux;
	bool retval;
	char *whard;
	int tbidhardware[MAXHARDWARE];
	char *tbHardware[MAXHARDWARE],*dualHardware[2], descripcion[250], strInt[LONINT], *idhardwares;

	/* Toma Centro (Unidad Organizativa) */
	sprintf(sqlstr, "SELECT * FROM ordenadores WHERE idordenador=%s", ido);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	if (!tbl.Get("idperfilhard", idperfilhard)) { // Toma dato
		tbl.GetErrorErrStr(msglog); // Error al acceder al registro
		og_info(msglog);
		return false;
	}
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
		sprintf(sqlstr, "SELECT idtipohardware,descripcion FROM tipohardwares "
			" WHERE nemonico='%s'", dualHardware[0]);
		if (!db.Execute(sqlstr, tbl)) {
			db.GetErrorErrStr(msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}
		if (tbl.ISEOF()) { //  Tipo de Hardware NO existente
			sprintf(msglog, "%s: %s)", tbErrores[54], dualHardware[0]);
			og_info(msglog);
			return false;
		} else { //  Tipo de Hardware Existe
			if (!tbl.Get("idtipohardware", idtipohardware)) { // Toma dato
				tbl.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}
			if (!tbl.Get("descripcion", descripcion)) { // Toma dato
				tbl.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}

			sprintf(sqlstr, "SELECT idhardware FROM hardwares "
				" WHERE idtipohardware=%d AND descripcion='%s'",
					idtipohardware, dualHardware[1]);

			if (!db.Execute(sqlstr, tbl)) {
				db.GetErrorErrStr(msglog);
				syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
				       __func__, __LINE__, msglog);
				return false;
			}

			if (tbl.ISEOF()) { //  Hardware NO existente
				sprintf(sqlstr, "INSERT hardwares (idtipohardware,descripcion,idcentro,grupoid) "
							" VALUES(%d,'%s',%s,0)", idtipohardware,
						dualHardware[1], idc);
				if (!db.Execute(sqlstr, tbl)) { // Error al insertar
					db.GetErrorErrStr(msglog); // Error al acceder al registro
					og_info(msglog);
					return false;
				}
				// Recupera el identificador del hardware
				sprintf(sqlstr, "SELECT LAST_INSERT_ID() as identificador");
				if (!db.Execute(sqlstr, tbl)) {
					db.GetErrorErrStr(msglog);
					syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
					       __func__, __LINE__, msglog);
					return false;
				}
				if (!tbl.ISEOF()) { // Si existe registro
					if (!tbl.Get("identificador", tbidhardware[i])) {
						tbl.GetErrorErrStr(msglog); // Error al acceder al registro
						og_info(msglog);
						return false;
					}
				}
			} else {
				if (!tbl.Get("idhardware", tbidhardware[i])) { // Toma dato
					tbl.GetErrorErrStr(msglog); // Error al acceder al registro
					og_info(msglog);
					return false;
				}
			}
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

	if (!cuestionPerfilHardware(db, tbl, idc, ido, idperfilhard, idhardwares,
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
bool cuestionPerfilHardware(Database db, Table tbl, char *idc, char *ido,
		int idperfilhardware, char *idhardwares, char *npc, int *tbidhardware,
		int lon)
{
	char msglog[LONSTD], *sqlstr;
	int i;
	int nwidperfilhard;

	sqlstr = reservaMemoria(strlen(idhardwares)+LONSQL); // Reserva para escribir sentencia SQL
	if (sqlstr == NULL) {
		syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
		return false;
	}
	// Busca perfil hard del ordenador que contenga todos los componentes hardware encontrados
	sprintf(sqlstr, "SELECT idperfilhard FROM"
		" (SELECT perfileshard_hardwares.idperfilhard as idperfilhard,"
		"	group_concat(cast(perfileshard_hardwares.idhardware AS char( 11) )"
		"	ORDER BY perfileshard_hardwares.idhardware SEPARATOR ',' ) AS idhardwares"
		" FROM	perfileshard_hardwares"
		" GROUP BY perfileshard_hardwares.idperfilhard) AS temp"
		" WHERE idhardwares LIKE '%s'", idhardwares);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		liberaMemoria(sqlstr);
		return false;
	}
	if (tbl.ISEOF()) { // No existe un perfil hardware con esos componentes de componentes hardware, lo crea
		sprintf(sqlstr, "INSERT perfileshard  (descripcion,idcentro,grupoid)"
				" VALUES('Perfil hardware (%s) ',%s,0)", npc, idc);
		if (!db.Execute(sqlstr, tbl)) { // Error al insertar
			db.GetErrorErrStr(msglog);
			og_info(msglog);
			liberaMemoria(sqlstr);
			return false;
		}
		// Recupera el identificador del nuevo perfil hardware
		sprintf(sqlstr, "SELECT LAST_INSERT_ID() as identificador");
		if (!db.Execute(sqlstr, tbl)) { // Error al leer
			db.GetErrorErrStr(msglog);
			og_info(msglog);
			liberaMemoria(sqlstr);
			return false;
		}
		if (!tbl.ISEOF()) { // Si existe registro
			if (!tbl.Get("identificador", nwidperfilhard)) {
				tbl.GetErrorErrStr(msglog);
				og_info(msglog);
				liberaMemoria(sqlstr);
				return false;
			}
		}
		// Crea la relación entre perfiles y componenetes hardware
		for (i = 0; i < lon; i++) {
			sprintf(sqlstr, "INSERT perfileshard_hardwares  (idperfilhard,idhardware)"
						" VALUES(%d,%d)", nwidperfilhard, tbidhardware[i]);
			if (!db.Execute(sqlstr, tbl)) { // Error al insertar
				db.GetErrorErrStr(msglog);
				og_info(msglog);
				liberaMemoria(sqlstr);
				return false;
			}
		}
	} else { // Existe un perfil con todos esos componentes
		if (!tbl.Get("idperfilhard", nwidperfilhard)) {
			tbl.GetErrorErrStr(msglog);
			og_info(msglog);
			liberaMemoria(sqlstr);
			return false;
		}
	}
	if (idperfilhardware != nwidperfilhard) { // No coinciden los perfiles
		// Actualiza el identificador del perfil hardware del ordenador
		sprintf(sqlstr, "UPDATE ordenadores SET idperfilhard=%d"
			" WHERE idordenador=%s", nwidperfilhard, ido);
		if (!db.Execute(sqlstr, tbl)) { // Error al insertar
			db.GetErrorErrStr(msglog);
			og_info(msglog);
			liberaMemoria(sqlstr);
			return false;
		}
	}
	/* Eliminar Relación de hardwares con Perfiles hardware que quedan húerfanos */
	sprintf(sqlstr, "DELETE FROM perfileshard_hardwares WHERE idperfilhard IN "
		" (SELECT idperfilhard FROM perfileshard WHERE idperfilhard NOT IN"
		" (SELECT DISTINCT idperfilhard from ordenadores))");
	if (!db.Execute(sqlstr, tbl)) { // Error al insertar
		db.GetErrorErrStr(msglog);
		og_info(msglog);
		liberaMemoria(sqlstr);
		return false;
	}

	/* Eliminar Perfiles hardware que quedan húerfanos */
	sprintf(sqlstr, "DELETE FROM perfileshard WHERE idperfilhard NOT IN"
			" (SELECT DISTINCT idperfilhard FROM ordenadores)");
	if (!db.Execute(sqlstr, tbl)) { // Error al insertar
		db.GetErrorErrStr(msglog);
		og_info(msglog);
		liberaMemoria(sqlstr);
		return false;
	}
	/* Eliminar Relación de hardwares con Perfiles hardware que quedan húerfanos */
	sprintf(sqlstr, "DELETE FROM perfileshard_hardwares WHERE idperfilhard NOT IN"
			" (SELECT idperfilhard FROM perfileshard)");
	if (!db.Execute(sqlstr, tbl)) { // Error al insertar
		db.GetErrorErrStr(msglog);
		og_info(msglog);
		liberaMemoria(sqlstr);
		return false;
	}
	liberaMemoria(sqlstr);
	return true;
}
// ________________________________________________________________________________________________________
// Función: InventarioSoftware
//
//	Descripción:
//		Solicita al cliente un inventario de su software
//	Parámetros:
//		- socket_c: Socket de la consola al envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool InventarioSoftware(TRAMA* ptrTrama, struct og_client *cli)
{
	if (!enviaComando(ptrTrama, CLIENTE_OCUPADO)) {
		respuestaConsola(og_client_socket(cli), ptrTrama, false);
		return false;
	}
	respuestaConsola(og_client_socket(cli), ptrTrama, true);
	return true;
}
// ________________________________________________________________________________________________________
// Función: RESPUESTA_InventarioSoftware
//
//	Descripción:
//		Respuesta del cliente al comando InventarioSoftware
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool RESPUESTA_InventarioSoftware(TRAMA* ptrTrama, struct og_client *cli)
{
	char msglog[LONSTD];
	Database db;
	Table tbl;
	bool res;
	char *iph, *ido, *npc, *idc, *par, *sft, *buffer;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	iph = copiaParametro("iph",ptrTrama); // Toma dirección ip
	ido = copiaParametro("ido",ptrTrama); // Toma identificador del ordenador

	if (!respuestaEstandar(ptrTrama, iph, ido, db, tbl)) {
		liberaMemoria(iph);
		liberaMemoria(ido);
		syslog(LOG_ERR, "failed to register notification\n");
		return false;
	}

	npc = copiaParametro("npc",ptrTrama); 
	idc = copiaParametro("idc",ptrTrama); // Toma identificador del Centro	
	par = copiaParametro("par",ptrTrama);
	sft = copiaParametro("sft",ptrTrama);

	buffer = rTrim(leeArchivo(sft));
	if (buffer)
		res=actualizaSoftware(db, tbl, buffer, par, ido, npc, idc);

	liberaMemoria(iph);
	liberaMemoria(ido);	
	liberaMemoria(npc);	
	liberaMemoria(idc);	
	liberaMemoria(par);	
	liberaMemoria(sft);	

	if(!res){
		syslog(LOG_ERR, "cannot update software\n");
		return false;
	}

	db.Close(); // Cierra conexión
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
bool actualizaSoftware(Database db, Table tbl, char *sft, char *par,char *ido,
		       char *npc, char *idc)
{
	int i, j, lon, aux, idperfilsoft, idnombreso;
	bool retval;
	char *wsft;
	int tbidsoftware[MAXSOFTWARE];
	char *tbSoftware[MAXSOFTWARE],msglog[LONSTD], sqlstr[LONSQL], strInt[LONINT], *idsoftwares;

	/* Toma Centro (Unidad Organizativa) y perfil software */
	sprintf(sqlstr, "SELECT idperfilsoft,numpar"
		" FROM ordenadores_particiones"
		" WHERE idordenador=%s", ido);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	idperfilsoft = 0; // Por defecto se supone que el ordenador no tiene aún detectado el perfil software
	while (!tbl.ISEOF()) { // Recorre particiones
		if (!tbl.Get("numpar", aux)) {
			tbl.GetErrorErrStr(msglog);
			og_info(msglog);
			return false;
		}
		if (aux == atoi(par)) { // Se encuentra la partición
			if (!tbl.Get("idperfilsoft", idperfilsoft)) {
				tbl.GetErrorErrStr(msglog);
				og_info(msglog);
				return false;
			}
			break;
		}
		tbl.MoveNext();
	}
	wsft=escaparCadena(sft); // Codificar comillas simples
	if(!wsft)
		return false;

	/* Recorre componentes software*/
	lon = splitCadena(tbSoftware, wsft, '\n');

	if (lon == 0)
		return true; // No hay lineas que procesar
	if (lon > MAXSOFTWARE)
		lon = MAXSOFTWARE; // Limita el número de componentes software

	for (i = 0; i < lon; i++) {
		// Primera línea es el sistema operativo: se obtiene identificador
		if (i == 0) {
			idnombreso = checkDato(db, tbl, rTrim(tbSoftware[i]), "nombresos", "nombreso", "idnombreso");
			continue;
		}

		sprintf(sqlstr,
				"SELECT idsoftware FROM softwares WHERE descripcion ='%s'",
				rTrim(tbSoftware[i]));

		if (!db.Execute(sqlstr, tbl)) {
			db.GetErrorErrStr(msglog);
			syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
			       __func__, __LINE__, msglog);
			return false;
		}

		if (tbl.ISEOF()) { //  Software NO existente
			sprintf(sqlstr, "INSERT INTO softwares (idtiposoftware,descripcion,idcentro,grupoid)"
						" VALUES(2,'%s',%s,0)", tbSoftware[i], idc);

			if (!db.Execute(sqlstr, tbl)) { // Error al insertar
				db.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}
			// Recupera el identificador del software
			sprintf(sqlstr, "SELECT LAST_INSERT_ID() as identificador");
			if (!db.Execute(sqlstr, tbl)) { // Error al leer
				db.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}
			if (!tbl.ISEOF()) { // Si existe registro
				if (!tbl.Get("identificador", tbidsoftware[i])) {
					tbl.GetErrorErrStr(msglog); // Error al acceder al registro
					og_info(msglog);
					return false;
				}
			}
		} else {
			if (!tbl.Get("idsoftware", tbidsoftware[i])) { // Toma dato
				tbl.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}
		}
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
	if (!cuestionPerfilSoftware(db, tbl, idc, ido, idperfilsoft, idnombreso, idsoftwares, 
			npc, par, tbidsoftware, lon)) {
		syslog(LOG_ERR, "cannot update software\n");
		og_info(msglog);
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
bool cuestionPerfilSoftware(Database db, Table tbl, char *idc, char *ido,
			    int idperfilsoftware, int idnombreso,
			    char *idsoftwares, char *npc, char *par,
			    int *tbidsoftware, int lon)
{
	char *sqlstr, msglog[LONSTD];
	int i, nwidperfilsoft;

	sqlstr = reservaMemoria(strlen(idsoftwares)+LONSQL); // Reserva para escribir sentencia SQL
	if (sqlstr == NULL) {
		syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
		return false;
	}
	// Busca perfil soft del ordenador que contenga todos los componentes software encontrados
	sprintf(sqlstr, "SELECT idperfilsoft FROM"
		" (SELECT perfilessoft_softwares.idperfilsoft as idperfilsoft,"
		"	group_concat(cast(perfilessoft_softwares.idsoftware AS char( 11) )"
		"	ORDER BY perfilessoft_softwares.idsoftware SEPARATOR ',' ) AS idsoftwares"
		" FROM	perfilessoft_softwares"
		" GROUP BY perfilessoft_softwares.idperfilsoft) AS temp"
		" WHERE idsoftwares LIKE '%s'", idsoftwares);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		liberaMemoria(sqlstr);
		return false;
	}
	if (tbl.ISEOF()) { // No existe un perfil software con esos componentes de componentes software, lo crea
		sprintf(sqlstr, "INSERT perfilessoft  (descripcion, idcentro, grupoid, idnombreso)"
				" VALUES('Perfil Software (%s, Part:%s) ',%s,0,%i)", npc, par, idc,idnombreso);
		if (!db.Execute(sqlstr, tbl)) { // Error al insertar
			db.GetErrorErrStr(msglog);
			og_info(msglog);
			return false;
		}
		// Recupera el identificador del nuevo perfil software
		sprintf(sqlstr, "SELECT LAST_INSERT_ID() as identificador");
		if (!db.Execute(sqlstr, tbl)) { // Error al leer
			tbl.GetErrorErrStr(msglog);
			og_info(msglog);
			liberaMemoria(sqlstr);
			return false;
		}
		if (!tbl.ISEOF()) { // Si existe registro
			if (!tbl.Get("identificador", nwidperfilsoft)) {
				tbl.GetErrorErrStr(msglog);
				og_info(msglog);
				liberaMemoria(sqlstr);
				return false;
			}
		}
		// Crea la relación entre perfiles y componenetes software
		for (i = 0; i < lon; i++) {
			sprintf(sqlstr, "INSERT perfilessoft_softwares (idperfilsoft,idsoftware)"
						" VALUES(%d,%d)", nwidperfilsoft, tbidsoftware[i]);
			if (!db.Execute(sqlstr, tbl)) { // Error al insertar
				db.GetErrorErrStr(msglog);
				og_info(msglog);
				liberaMemoria(sqlstr);
				return false;
			}
		}
	} else { // Existe un perfil con todos esos componentes
		if (!tbl.Get("idperfilsoft", nwidperfilsoft)) {
			tbl.GetErrorErrStr(msglog);
			og_info(msglog);
			liberaMemoria(sqlstr);
			return false;
		}
	}

	if (idperfilsoftware != nwidperfilsoft) { // No coinciden los perfiles
		// Actualiza el identificador del perfil software del ordenador
		sprintf(sqlstr, "UPDATE ordenadores_particiones SET idperfilsoft=%d,idimagen=0"
				" WHERE idordenador=%s AND numpar=%s", nwidperfilsoft, ido, par);
		if (!db.Execute(sqlstr, tbl)) { // Error al insertar
			db.GetErrorErrStr(msglog);
			og_info(msglog);
			liberaMemoria(sqlstr);
			return false;
		}
	}

	/* DEPURACIÓN DE PERFILES SOFTWARE */

	 /* Eliminar Relación de softwares con Perfiles software que quedan húerfanos */
	sprintf(sqlstr, "DELETE FROM perfilessoft_softwares WHERE idperfilsoft IN "\
		" (SELECT idperfilsoft FROM perfilessoft WHERE idperfilsoft NOT IN"\
		" (SELECT DISTINCT idperfilsoft from ordenadores_particiones) AND idperfilsoft NOT IN"\
		" (SELECT DISTINCT idperfilsoft from imagenes))");
	if (!db.Execute(sqlstr, tbl)) { // Error al insertar
		db.GetErrorErrStr(msglog);
		og_info(msglog);
		liberaMemoria(sqlstr);
		return false;
	}
	/* Eliminar Perfiles software que quedan húerfanos */
	sprintf(sqlstr, "DELETE FROM perfilessoft WHERE idperfilsoft NOT IN"
		" (SELECT DISTINCT idperfilsoft from ordenadores_particiones)"\
		" AND  idperfilsoft NOT IN"\
		" (SELECT DISTINCT idperfilsoft from imagenes)");
	if (!db.Execute(sqlstr, tbl)) { // Error al insertar
		db.GetErrorErrStr(msglog);
		og_info(msglog);
		liberaMemoria(sqlstr);
		return false;
	}
	/* Eliminar Relación de softwares con Perfiles software que quedan húerfanos */
	sprintf(sqlstr, "DELETE FROM perfilessoft_softwares WHERE idperfilsoft NOT IN"
			" (SELECT idperfilsoft from perfilessoft)");
	if (!db.Execute(sqlstr, tbl)) { // Error al insertar
		db.GetErrorErrStr(msglog);
		og_info(msglog);
		liberaMemoria(sqlstr);
		return false;
	}
	liberaMemoria(sqlstr);
	return true;
}
// ________________________________________________________________________________________________________
// Función: enviaArchivo
//
//	Descripción:
//		Envia un archivo por la red, por bloques
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool enviaArchivo(TRAMA *ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);
	char *nfl;

	// Toma parámetros
	nfl = copiaParametro("nfl",ptrTrama); // Toma nombre completo del archivo
	if (!sendArchivo(&socket_c, nfl)) {
		liberaMemoria(nfl);
		syslog(LOG_ERR, "Problem sending file\n");
		return false;
	}
	liberaMemoria(nfl);
	return true;
}
// ________________________________________________________________________________________________________
// Función: enviaArchivo
//
//	Descripción:
//		Envia un archivo por la red, por bloques
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool recibeArchivo(TRAMA *ptrTrama, struct og_client *cli)
{
	int socket_c = og_client_socket(cli);
	char *nfl;

	// Toma parámetros
	nfl = copiaParametro("nfl",ptrTrama); // Toma nombre completo del archivo
	ptrTrama->tipo = MSG_NOTIFICACION;
	enviaFlag(&socket_c, ptrTrama);
	if (!recArchivo(&socket_c, nfl)) {
		liberaMemoria(nfl);
		syslog(LOG_ERR, "Problem receiving file\n");
		return false;
	}
	liberaMemoria(nfl);
	return true;
}
// ________________________________________________________________________________________________________
// Función: envioProgramacion
//
//	Descripción:
//		Envia un comando de actualización a todos los ordenadores que han sido programados con
//		alguna acción para que entren en el bucle de comandos pendientes y las ejecuten
//	Parámetros:
//		- socket_c: Socket del cliente que envió el mensaje
//		- ptrTrama: Trama recibida por el servidor con el contenido y los parámetros
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static bool envioProgramacion(TRAMA *ptrTrama, struct og_client *cli)
{
	char sqlstr[LONSQL], msglog[LONSTD];
	char *idp,iph[LONIP],mac[LONMAC];
	Database db;
	Table tbl;
	int idx,idcomando;

	if (!db.Open(usuario, pasguor, datasource, catalog)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "cannot open connection database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}

	idp = copiaParametro("idp",ptrTrama); // Toma identificador de la programación de la tabla acciones

	sprintf(sqlstr, "SELECT ordenadores.ip,ordenadores.mac,acciones.idcomando FROM acciones "\
			" INNER JOIN ordenadores ON ordenadores.ip=acciones.ip"\
			" WHERE acciones.idprogramacion=%s",idp);
	
	liberaMemoria(idp);

	if (!db.Execute(sqlstr, tbl)) {
		db.GetErrorErrStr(msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	db.Close();
	if(tbl.ISEOF())
		return true; // No existen registros

	/* Prepara la trama de actualizacion */

	initParametros(ptrTrama,0);
	ptrTrama->tipo=MSG_COMANDO;
	sprintf(ptrTrama->parametros, "nfn=Actualizar\r");

	while (!tbl.ISEOF()) { // Recorre particiones
		if (!tbl.Get("ip", iph)) {
			tbl.GetErrorErrStr(msglog);
			syslog(LOG_ERR, "cannot find ip column in table: %s\n",
			       msglog);
			return false;
		}
		if (!tbl.Get("idcomando", idcomando)) {
			tbl.GetErrorErrStr(msglog);
			syslog(LOG_ERR, "cannot find idcomando column in table: %s\n",
			       msglog);
			return false;
		}
		if(idcomando==1){ // Arrancar
			if (!tbl.Get("mac", mac)) {
				tbl.GetErrorErrStr(msglog);
				syslog(LOG_ERR, "cannot find mac column in table: %s\n",
				       msglog);
				return false;
			}

			// Se manda por broadcast y por unicast
			if (!Levanta(iph, mac, (char*)"1"))
				return false;

			if (!Levanta(iph, mac, (char*)"2"))
				return false;

		}
		if (clienteDisponible(iph, &idx)) { // Si el cliente puede recibir comandos
			int sock = tbsockets[idx].cli ? tbsockets[idx].cli->io.fd : -1;

			strcpy(tbsockets[idx].estado, CLIENTE_OCUPADO); // Actualiza el estado del cliente
			if (!mandaTrama(&sock, ptrTrama)) {
				syslog(LOG_ERR, "failed to send response: %s\n",
				       strerror(errno));
				return false;
			}
			//close(tbsockets[idx].sock); // Cierra el socket del cliente hasta nueva disponibilidad
		}
		tbl.MoveNext();
	}
	return true; // No existen registros
}

// This object stores function handler for messages
static struct {
	const char *nf; // Nombre de la función
	bool (*fcn)(TRAMA *, struct og_client *cli);
} tbfuncionesServer[] = {
	{ "Sondeo",				Sondeo,			},
	{ "respuestaSondeo",			respuestaSondeo,	},
	{ "ConsolaRemota",			ConsolaRemota,		},
	{ "EcoConsola",				EcoConsola,		},
	{ "Actualizar",				Actualizar,		},
	{ "Purgar",				Purgar,			},
	{ "InclusionCliente",			InclusionCliente,	},
	{ "InclusionClienteWinLnx",		InclusionClienteWinLnx, },
	{ "AutoexecCliente",			AutoexecCliente,	},
	{ "ComandosPendientes",			ComandosPendientes,	},
	{ "DisponibilidadComandos",		DisponibilidadComandos, },
	{ "Arrancar",				Arrancar, 		},
	{ "RESPUESTA_Arrancar",			RESPUESTA_Arrancar,	},
	{ "Apagar",				Apagar,			},
	{ "RESPUESTA_Apagar",			RESPUESTA_Apagar,	},
	{ "Reiniciar",				Reiniciar,		},
	{ "RESPUESTA_Reiniciar",		RESPUESTA_Reiniciar,	},
	{ "IniciarSesion",			IniciarSesion,		},
	{ "RESPUESTA_IniciarSesion",		RESPUESTA_IniciarSesion, },
	{ "CrearImagen",			CrearImagen,		},
	{ "RESPUESTA_CrearImagen",		RESPUESTA_CrearImagen,	},
	{ "CrearImagenBasica",			CrearImagenBasica,	},
	{ "RESPUESTA_CrearImagenBasica",	RESPUESTA_CrearImagenBasica, },
	{ "CrearSoftIncremental",		CrearSoftIncremental,	},
	{ "RESPUESTA_CrearSoftIncremental",	RESPUESTA_CrearSoftIncremental, },
	{ "RestaurarImagen",			RestaurarImagen,	},
	{ "RESPUESTA_RestaurarImagen",		RESPUESTA_RestaurarImagen },
	{ "RestaurarImagenBasica",		RestaurarImagenBasica, },
	{ "RESPUESTA_RestaurarImagenBasica",	RESPUESTA_RestaurarImagenBasica, },
	{ "RestaurarSoftIncremental",		RestaurarSoftIncremental, },
	{ "RESPUESTA_RestaurarSoftIncremental",	RESPUESTA_RestaurarSoftIncremental, },
	{ "Configurar",				Configurar,		},
	{ "RESPUESTA_Configurar",		RESPUESTA_Configurar,	},
	{ "EjecutarScript",			EjecutarScript,		},
	{ "RESPUESTA_EjecutarScript",		RESPUESTA_EjecutarScript, },
	{ "InventarioHardware",			InventarioHardware, 	},
	{ "RESPUESTA_InventarioHardware",	RESPUESTA_InventarioHardware, },
	{ "InventarioSoftware",			InventarioSoftware	},
	{ "RESPUESTA_InventarioSoftware",	RESPUESTA_InventarioSoftware, },
	{ "enviaArchivo",			enviaArchivo,		},
	{ "recibeArchivo",			recibeArchivo, 		},
	{ "envioProgramacion",			envioProgramacion,	},
	{ NULL,					NULL,			},
};

// ________________________________________________________________________________________________________
// Función: gestionaTrama
//
//		Descripción:
//			Procesa las tramas recibidas .
//		Parametros:
//			- s : Socket usado para comunicaciones
//	Devuelve:
//		true: Si el proceso es correcto
//		false: En caso de ocurrir algún error
// ________________________________________________________________________________________________________
static void gestionaTrama(TRAMA *ptrTrama, struct og_client *cli)
{
	int i, res;
	char *nfn;

	if (ptrTrama){
		INTROaFINCAD(ptrTrama);
		nfn = copiaParametro("nfn",ptrTrama); // Toma nombre de la función

		for (i = 0; tbfuncionesServer[i].fcn; i++) {
			if (!strncmp(tbfuncionesServer[i].nf, nfn,
				     strlen(tbfuncionesServer[i].nf))) {
				res = tbfuncionesServer[i].fcn(ptrTrama, cli);
				if (!res) {
					syslog(LOG_ERR, "Failed handling of %s for client %s:%hu\n",
					       tbfuncionesServer[i].nf,
					       inet_ntoa(cli->addr.sin_addr),
					       ntohs(cli->addr.sin_port));
				} else {
					syslog(LOG_DEBUG, "Successful handling of %s for client %s:%hu\n",
					       tbfuncionesServer[i].nf,
					       inet_ntoa(cli->addr.sin_addr),
					       ntohs(cli->addr.sin_port));
				}
				break;
			}
		}
		if (!tbfuncionesServer[i].fcn)
			syslog(LOG_ERR, "unknown request %s from client %s:%hu\n",
			       nfn, inet_ntoa(cli->addr.sin_addr),
			       ntohs(cli->addr.sin_port));

		liberaMemoria(nfn);
	}
}

static void og_client_release(struct ev_loop *loop, struct og_client *cli)
{
	if (cli->keepalive_idx >= 0) {
		syslog(LOG_DEBUG, "closing keepalive connection for %s:%hu in slot %d\n",
		       inet_ntoa(cli->addr.sin_addr),
		       ntohs(cli->addr.sin_port), cli->keepalive_idx);
		tbsockets[cli->keepalive_idx].cli = NULL;
	}

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

static int og_client_state_recv_hdr(struct og_client *cli)
{
	char hdrlen[LONHEXPRM];

	/* Still too short to validate protocol fingerprint and message
	 * length.
	 */
	if (cli->buf_len < 15 + LONHEXPRM)
		return 0;

	if (strncmp(cli->buf, "@JMMLCAMDJ_MCDJ", 15)) {
		syslog(LOG_ERR, "bad fingerprint from client %s:%hu, closing\n",
		       inet_ntoa(cli->addr.sin_addr),
		       ntohs(cli->addr.sin_port));
		return -1;
	}

	memcpy(hdrlen, &cli->buf[LONGITUD_CABECERATRAMA], LONHEXPRM);
	cli->msg_len = strtol(hdrlen, NULL, 16);

	/* Header announces more that we can fit into buffer. */
	if (cli->msg_len >= sizeof(cli->buf)) {
		syslog(LOG_ERR, "too large message %u bytes from %s:%hu\n",
		       cli->msg_len, inet_ntoa(cli->addr.sin_addr),
		       ntohs(cli->addr.sin_port));
		return -1;
	}

	return 1;
}

static int og_client_state_process_payload(struct og_client *cli)
{
	TRAMA *ptrTrama;
	char *data;
	int len;

	len = cli->msg_len - (LONGITUD_CABECERATRAMA + LONHEXPRM);
	data = &cli->buf[LONGITUD_CABECERATRAMA + LONHEXPRM];

	ptrTrama = (TRAMA *)reservaMemoria(sizeof(TRAMA));
	if (!ptrTrama) {
		syslog(LOG_ERR, "OOM\n");
		return -1;
	}

	initParametros(ptrTrama, len);
	memcpy(ptrTrama, cli->buf, LONGITUD_CABECERATRAMA);
	memcpy(ptrTrama->parametros, data, len);
	ptrTrama->lonprm = len;

	gestionaTrama(ptrTrama, cli);

	liberaMemoria(ptrTrama->parametros);
	liberaMemoria(ptrTrama);

	return 1;
}

static void og_client_read_cb(struct ev_loop *loop, struct ev_io *io, int events)
{
	struct og_client *cli;
	int ret;

	cli = container_of(io, struct og_client, io);

	if (events & EV_ERROR) {
		syslog(LOG_ERR, "unexpected error event from client %s:%hu\n",
			       inet_ntoa(cli->addr.sin_addr),
			       ntohs(cli->addr.sin_port));
		goto close;
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
		goto close;
	}

	if (cli->keepalive_idx >= 0)
		return;

	ev_timer_again(loop, &cli->timer);

	cli->buf_len += ret;

	switch (cli->state) {
	case OG_CLIENT_RECEIVING_HEADER:
		ret = og_client_state_recv_hdr(cli);
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
		syslog(LOG_DEBUG, "processing request from %s:%hu\n",
		       inet_ntoa(cli->addr.sin_addr),
		       ntohs(cli->addr.sin_port));

		ret = og_client_state_process_payload(cli);
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
	cli->keepalive_idx = -1;

	syslog(LOG_DEBUG, "connection from client %s:%hu\n",
	       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));

	ev_io_init(&cli->io, og_client_read_cb, client_sd, EV_READ);
	ev_io_start(loop, &cli->io);
	ev_timer_init(&cli->timer, og_client_timer_cb, OG_CLIENT_TIMEOUT, 0.);
	ev_timer_start(loop, &cli->timer);
}

int main(int argc, char *argv[])
{
	struct ev_loop *loop = ev_default_loop(0);
	struct ev_io ev_io_server;
	struct sockaddr_in local;
	int socket_s;
	int activo=1;
	int i;

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
	socket_s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP); // Crea socket del servicio
	setsockopt(socket_s, SOL_SOCKET, SO_REUSEPORT, &activo, sizeof(int));
	if (socket_s < 0) {
		syslog(LOG_ERR, "cannot create main socket\n");
		exit(EXIT_FAILURE);
	}

	local.sin_addr.s_addr = htonl(INADDR_ANY); // Configura el socket del servicio
	local.sin_family = AF_INET;
	local.sin_port = htons(atoi(puerto));

	if (bind(socket_s, (struct sockaddr *) &local, sizeof(local)) < 0) {
		syslog(LOG_ERR, "cannot bind socket\n");
		exit(EXIT_FAILURE);
	}

	listen(socket_s, 250); // Pone a escuchar al socket

	ev_io_init(&ev_io_server, og_server_accept_cb, socket_s, EV_READ);
	ev_io_start(loop, &ev_io_server);

	infoLog(1); // Inicio de sesión

	/* old log file has been deprecated. */
	og_log(97, false);

	syslog(LOG_INFO, "Waiting for connections\n");

	while (1)
		ev_loop(loop, 0);

	exit(EXIT_SUCCESS);
}
