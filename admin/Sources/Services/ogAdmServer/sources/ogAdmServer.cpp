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
#include <ev.h>
#include <syslog.h>
#include <sys/ioctl.h>
#include <ifaddrs.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <jansson.h>

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

#define OG_MSG_REQUEST_MAXLEN	4096

/* Shut down connection if there is no complete message after 10 seconds. */
#define OG_CLIENT_TIMEOUT	10

struct og_client {
	struct ev_io		io;
	struct ev_timer		timer;
	struct sockaddr_in	addr;
	enum og_client_state	state;
	char			buf[OG_MSG_REQUEST_MAXLEN];
	unsigned int		buf_len;
	unsigned int		msg_len;
	int			keepalive_idx;
	bool			rest;
	int			content_length;
	char			auth_token[64];
};

static inline int og_client_socket(const struct og_client *cli)
{
	return cli->io.fd;
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
	struct og_dbi *dbi;
	const char *msglog;
	dbi_result result;
	char *iph;

	// Toma parámetros
	iph = copiaParametro("iph",ptrTrama); // Toma ip

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		goto err_dbi_open;
	}

	result = dbi_conn_queryf(dbi->conn,
			"SELECT idordenador,nombreordenador FROM ordenadores "
				" WHERE ordenadores.ip = '%s'", iph);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		goto err_query_fail;
	}

	if (!dbi_result_next_row(result)) {
		syslog(LOG_ERR, "client does not exist in database (%s:%d)\n",
		       __func__, __LINE__);
		dbi_result_free(result);
		goto err_query_fail;
	}

	syslog(LOG_DEBUG, "Client %s requesting inclusion\n", iph);

	*idordenador = dbi_result_get_uint(result, "idordenador");
	nombreordenador = (char *)dbi_result_get_string(result, "nombreordenador");

	dbi_result_free(result);
	og_dbi_close(dbi);

	if (!registraCliente(iph)) { // Incluyendo al cliente en la tabla de sokets
		liberaMemoria(iph);
		syslog(LOG_ERR, "client table is full\n");
		return false;
	}
	liberaMemoria(iph);
	return true;

err_query_fail:
	og_dbi_close(dbi);
err_dbi_open:
	liberaMemoria(iph);
	return false;
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
	char *ptrPar[MAXPAR], *ptrCfg[7], *ptrDual[2], tbPar[LONSTD];
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

		sprintf(sqlstr, "SELECT numdisk, numpar, tamano, uso, idsistemafichero, idnombreso"
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
			if (!tbl.Get("tamano", dato)) { // Toma dato
				tbl.GetErrorErrStr(msglog); // Error al acceder al registro
				og_info(msglog);
				return false;
			}
			if (atoi(tam) == dato) { // Parámetro tamaño igual al almacenado
				if (!tbl.Get("idsistemafichero", dato)) { // Toma dato
					tbl.GetErrorErrStr(msglog); // Error al acceder al registro
					og_info(msglog);
					return false;
				}
				if (idsfi == dato) { // Parámetro sistema de fichero igual al almacenado
					if (!tbl.Get("idnombreso", dato)) { // Toma dato
						tbl.GetErrorErrStr(msglog); // Error al acceder al registro
						og_info(msglog);
						return false;
					}
					if (idsoi == dato) { // Parámetro sistema operativo distinto al almacenado
						swu = false; // Todos los parámetros de la partición son iguales, no se actualiza
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
					" codpar=0x%s,"
					" uso=%s"
					" WHERE idordenador=%d AND numdisk=%s AND numpar=%s",
					cpt, uso, ido, disk, par);
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
	char *iph, *exe;
	FILE *fileexe;
	char fileautoexec[LONPRM];
	char parametros[LONGITUD_PARAMETROS];
	struct og_dbi *dbi;

	iph = copiaParametro("iph",ptrTrama); // Toma dirección IP del cliente
	exe = copiaParametro("exe",ptrTrama); // Toma identificador del procedimiento inicial

	sprintf(fileautoexec, "/tmp/Sautoexec-%s", iph);
	liberaMemoria(iph);
	fileexe = fopen(fileautoexec, "wb"); // Abre fichero de script
	if (fileexe == NULL) {
		syslog(LOG_ERR, "cannot create temporary file\n");
		return false;
	}

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		return false;
	}
	initParametros(ptrTrama,0);
	if (recorreProcedimientos(dbi, parametros, fileexe, exe)) {
		lon = sprintf(ptrTrama->parametros, "nfn=RESPUESTA_AutoexecCliente\r");
		lon += sprintf(ptrTrama->parametros + lon, "nfl=%s\r", fileautoexec);
		lon += sprintf(ptrTrama->parametros + lon, "res=1\r");
	} else {
		lon = sprintf(ptrTrama->parametros, "nfn=RESPUESTA_AutoexecCliente\r");
		lon += sprintf(ptrTrama->parametros + lon, "res=0\r");
	}

	og_dbi_close(dbi);
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
bool recorreProcedimientos(struct og_dbi *dbi, char *parametros, FILE *fileexe, char *idp)
{
	char idprocedimiento[LONPRM];
	int procedimientoid, lsize;
	const char *msglog, *param;
	dbi_result result;

	result = dbi_conn_queryf(dbi->conn,
			"SELECT procedimientoid,parametros FROM procedimientos_acciones"
				" WHERE idprocedimiento=%s ORDER BY orden", idp);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		return false;
	}
	while (dbi_result_next_row(result)) {
		procedimientoid = dbi_result_get_uint(result, "procedimientoid");
		if (procedimientoid > 0) { // Procedimiento recursivo
			sprintf(idprocedimiento, "%d", procedimientoid);
			if (!recorreProcedimientos(dbi, parametros, fileexe, idprocedimiento)) {
				return false;
			}
		} else {
			param = dbi_result_get_string(result, "parametros");
			sprintf(parametros, "%s@", param);
			lsize = strlen(parametros);
			fwrite(parametros, 1, lsize, fileexe); // Escribe el código a ejecutar
		}
	}
	dbi_result_free(result);

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
	const char *param, *msglog;
	struct og_dbi *dbi;
	dbi_result result;
	unsigned int lonprm;

	dbi = og_dbi_open(&dbi_config);
	if (!dbi) {
		syslog(LOG_ERR, "cannot open connection database (%s:%d)\n",
		       __func__, __LINE__);
		goto err_dbi_open;
	}
	result = dbi_conn_queryf(dbi->conn,
			"SELECT sesion, parametros"\
			" FROM acciones WHERE idordenador=%s AND estado='%d'"\
			" ORDER BY idaccion", ido, ACCION_INICIADA);
	if (!result) {
		dbi_conn_error(dbi->conn, &msglog);
		syslog(LOG_ERR, "failed to query database (%s:%d) %s\n",
		       __func__, __LINE__, msglog);
		goto err_query_fail;
	}
	if (!dbi_result_next_row(result)) {
		dbi_result_free(result);
		og_dbi_close(dbi);
		return false; // No hay comandos pendientes
	}

	*ids = dbi_result_get_uint(result, "sesion");
	param = dbi_result_get_string(result, "parametros");
	lonprm = strlen(param);

	if(!initParametros(ptrTrama,lonprm + LONGITUD_PARAMETROS)){
		syslog(LOG_ERR, "%s:%d OOM\n", __FILE__, __LINE__);
		goto err_init_params;
	}
	sprintf(ptrTrama->parametros, "%s", param);

	dbi_result_free(result);
	og_dbi_close(dbi);

	return true; // Hay comandos pendientes, se toma el primero de la cola

err_init_params:
	dbi_result_free(result);
err_query_fail:
	og_dbi_close(dbi);
err_dbi_open:
	return false;
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

	ids = copiaParametro("ids",ptrTrama);
	res = copiaParametro("res",ptrTrama);

	if (ids == NULL) {
		if (atoi(res) == ACCION_FALLIDA) {
			liberaMemoria(res);
			return false;
		}
		liberaMemoria(res);
		return true;
	}

	if (atoi(ids) == 0) {
		liberaMemoria(ids);
		if (atoi(res) == ACCION_FALLIDA) {
			liberaMemoria(res);
			return false;
		}
		liberaMemoria(res);
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
		return false;
	}

	liberaMemoria(res);
	return true;
}

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
	int lon;

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

	if (!og_send_cmd(ptrIpes, lon, estado, ptrTrama))
		return false;

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
	sockaddr_in local;
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
	bool res = true;

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
		res = actualizaConfiguracion(db, tbl, cfg, atoi(ido)); // Actualiza la configuración del ordenador
		liberaMemoria(cfg);	
	}

	liberaMemoria(iph);
	liberaMemoria(ido);

	if (!res) {
		syslog(LOG_ERR, "Problem updating client configuration\n");
		return false;
	}

	db.Close(); // Cierra conexión
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
	char *ptrIP[MAXIMOS_CLIENTES],*ptrMacs[MAXIMOS_CLIENTES];
	char sqlstr[LONSQL], msglog[LONSTD];
	char *idp,iph[LONIP],mac[LONMAC];
	Database db;
	Table tbl;
	int idx,idcomando,lon;

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

			lon = splitCadena(ptrIP, iph, ';');
			lon = splitCadena(ptrMacs, mac, ';');

			// Se manda por broadcast y por unicast
			if (!Levanta(ptrIP, ptrMacs, lon, (char*)"1"))
				return false;

			if (!Levanta(ptrIP, ptrMacs, lon, (char*)"2"))
				return false;

		}
		if (clienteDisponible(iph, &idx)) { // Si el cliente puede recibir comandos
			int sock = tbsockets[idx].cli ? tbsockets[idx].cli->io.fd : -1;

			strcpy(tbsockets[idx].estado, CLIENTE_OCUPADO); // Actualiza el estado del cliente
			if (sock >= 0 && !mandaTrama(&sock, ptrTrama)) {
				syslog(LOG_ERR, "failed to send response: %s\n",
				       strerror(errno));
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
	{ "InclusionCliente",			InclusionCliente,	},
	{ "InclusionClienteWinLnx",		InclusionClienteWinLnx, },
	{ "AutoexecCliente",			AutoexecCliente,	},
	{ "ComandosPendientes",			ComandosPendientes,	},
	{ "DisponibilidadComandos",		DisponibilidadComandos, },
	{ "RESPUESTA_Arrancar",			RESPUESTA_Arrancar,	},
	{ "RESPUESTA_Apagar",			RESPUESTA_Apagar,	},
	{ "RESPUESTA_Reiniciar",		RESPUESTA_Reiniciar,	},
	{ "RESPUESTA_IniciarSesion",		RESPUESTA_IniciarSesion, },
	{ "RESPUESTA_CrearImagen",		RESPUESTA_CrearImagen,	},
	{ "CrearImagenBasica",			CrearImagenBasica,	},
	{ "RESPUESTA_CrearImagenBasica",	RESPUESTA_CrearImagenBasica, },
	{ "CrearSoftIncremental",		CrearSoftIncremental,	},
	{ "RESPUESTA_CrearSoftIncremental",	RESPUESTA_CrearSoftIncremental, },
	{ "RESPUESTA_RestaurarImagen",		RESPUESTA_RestaurarImagen },
	{ "RestaurarImagenBasica",		RestaurarImagenBasica, },
	{ "RESPUESTA_RestaurarImagenBasica",	RESPUESTA_RestaurarImagenBasica, },
	{ "RestaurarSoftIncremental",		RestaurarSoftIncremental, },
	{ "RESPUESTA_RestaurarSoftIncremental",	RESPUESTA_RestaurarSoftIncremental, },
	{ "Configurar",				Configurar,		},
	{ "RESPUESTA_Configurar",		RESPUESTA_EjecutarScript, },
	{ "EjecutarScript",			EjecutarScript,		},
	{ "RESPUESTA_EjecutarScript",		RESPUESTA_EjecutarScript, },
	{ "RESPUESTA_InventarioHardware",	RESPUESTA_InventarioHardware, },
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

static int og_client_state_process_payload(struct og_client *cli)
{
	TRAMA *ptrTrama;
	char *data;
	int len;

	len = cli->msg_len - (LONGITUD_CABECERATRAMA + LONHEXPRM);
	data = &cli->buf[LONGITUD_CABECERATRAMA + LONHEXPRM];

	ptrTrama = og_msg_alloc(data, len);
	if (!ptrTrama)
		return -1;

	gestionaTrama(ptrTrama, cli);

	og_msg_free(ptrTrama);

	return 1;
}

#define OG_CLIENTS_MAX	4096
#define OG_PARTITION_MAX 4

struct og_partition {
	const char	*number;
	const char	*code;
	const char	*size;
	const char	*filesystem;
	const char	*format;
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
	uint64_t	flags;
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

static int og_json_parse_partition(json_t *element,
				   struct og_msg_params *params,
				   unsigned int i)
{
	struct og_partition *part = &params->partition_setup[i];
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
		}

		if (err < 0)
			return err;
	}

	if (flags != (OG_PARAM_PART_NUMBER |
		      OG_PARAM_PART_CODE |
		      OG_PARAM_PART_FILESYSTEM |
		      OG_PARAM_PART_SIZE |
		      OG_PARAM_PART_FORMAT))
		return -1;

	params->flags |= (OG_REST_PARAM_PART_0 << i);

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

		if (og_json_parse_partition(k, params, i) != 0)
			return -1;
	}
	return 0;
}

static int og_cmd_legacy_send(struct og_msg_params *params, const char *cmd,
			      const char *state)
{
	char buf[4096] = {};
	int len, err = 0;
	TRAMA *msg;

	len = snprintf(buf, sizeof(buf), "nfn=%s\r", cmd);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	if (!og_send_cmd((char **)params->ips_array, params->ips_array_len,
			 state, msg))
		err = -1;

	og_msg_free(msg);

	return err;
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

	return og_cmd_legacy_send(params, "Sondeo", CLIENTE_APAGADO);
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
	struct og_buffer og_buffer = {
		.data	= buffer_reply,
	};
	int i;

	array = json_array();
	if (!array)
		return -1;

	for (i = 0; i < MAXIMOS_CLIENTES; i++) {
		if (tbsockets[i].ip[0] == '\0')
			continue;

		object = json_object();
		if (!object) {
			json_decref(array);
			return -1;
		}
		addr = json_string(tbsockets[i].ip);
		if (!addr) {
			json_decref(object);
			json_decref(array);
			return -1;
		}
		json_object_set_new(object, "addr", addr);

		state = json_string(tbsockets[i].estado);
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
	char buf[4096] = {}, iph[4096] = {};
	int err = 0, len;
	const char *key;
	unsigned int i;
	json_t *value;
	TRAMA *msg;

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

	for (i = 0; i < params->ips_array_len; i++) {
		len = snprintf(iph + strlen(iph), sizeof(iph), "%s;",
			       params->ips_array[i]);
	}

	if (params->echo) {
		len = snprintf(buf, sizeof(buf),
			       "nfn=ConsolaRemota\riph=%s\rscp=%s\r",
			       iph, params->run_cmd);
	} else {
		len = snprintf(buf, sizeof(buf),
			       "nfn=EjecutarScript\riph=%s\rscp=%s\r",
			       iph, params->run_cmd);
	}

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	if (!og_send_cmd((char **)params->ips_array, params->ips_array_len,
			 CLIENTE_OCUPADO, msg))
		err = -1;

	og_msg_free(msg);

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
	char buf[4096], iph[4096];
	int err = 0, len;
	const char *key;
	unsigned int i;
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
		}

		if (err < 0)
			return err;
	}

	if (!og_msg_params_validate(params, OG_REST_PARAM_ADDR |
					    OG_REST_PARAM_DISK |
					    OG_REST_PARAM_PARTITION))
		return -1;

	for (i = 0; i < params->ips_array_len; i++) {
		snprintf(iph + strlen(iph), sizeof(iph), "%s;",
			 params->ips_array[i]);
	}
	len = snprintf(buf, sizeof(buf),
		       "nfn=IniciarSesion\riph=%s\rdsk=%s\rpar=%s\r",
		       iph, params->disk, params->partition);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	if (!og_send_cmd((char **)params->ips_array, params->ips_array_len,
			 CLIENTE_APAGADO, msg))
		err = -1;

	og_msg_free(msg);

	return 0;
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

	return og_cmd_legacy_send(params, "Apagar", CLIENTE_OCUPADO);
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

	return og_cmd_legacy_send(params, "Actualizar", CLIENTE_APAGADO);
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

	return og_cmd_legacy_send(params, "Reiniciar", CLIENTE_OCUPADO);
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

	return og_cmd_legacy_send(params, "Purgar", CLIENTE_APAGADO);
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

	return og_cmd_legacy_send(params, "InventarioHardware",
				  CLIENTE_OCUPADO);
}

static int og_cmd_software(json_t *element, struct og_msg_params *params)
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

	len = snprintf(buf, sizeof(buf),
		       "nfn=InventarioSoftware\rdsk=%s\rpar=%s\r",
		       params->disk, params->partition);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
		    CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
}

static int og_cmd_create_image(json_t *element, struct og_msg_params *params)
{
	char buf[4096] = {};
	int err = 0, len;
	const char *key;
	json_t *value;
	TRAMA *msg;

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

	len = snprintf(buf, sizeof(buf),
			"nfn=CrearImagen\rdsk=%s\rpar=%s\rcpt=%s\ridi=%s\rnci=%s\ripr=%s\r",
			params->disk, params->partition, params->code,
			params->id, params->name, params->repository);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
		    CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
}

static int og_cmd_restore_image(json_t *element, struct og_msg_params *params)
{
	char buf[4096] = {};
	int err = 0, len;
	const char *key;
	json_t *value;
	TRAMA *msg;

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

	len = snprintf(buf, sizeof(buf),
		       "nfn=RestaurarImagen\ridi=%s\rdsk=%s\rpar=%s\rifs=%s\r"
		       "nci=%s\ripr=%s\rptc=%s\r",
		       params->id, params->disk, params->partition,
		       params->profile, params->name,
		       params->repository, params->type);

	msg = og_msg_alloc(buf, len);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
		    CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
}

static int og_cmd_setup(json_t *element, struct og_msg_params *params)
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

	len = snprintf(buf, sizeof(buf),
			"nfn=Configurar\rdsk=%s\rcfg=dis=%s*che=%s*tch=%s!",
			params->disk, params->disk, params->cache, params->cache_size);

	for (unsigned int i = 0; i < OG_PARTITION_MAX; ++i) {
		const struct og_partition *part = &params->partition_setup[i];

		len += snprintf(buf + strlen(buf), sizeof(buf),
			"par=%s*cpt=%s*sfi=%s*tam=%s*ope=%s%%",
			part->number, part->code, part->filesystem, part->size, part->format);
	}

	msg = og_msg_alloc(buf, len + 1);
	if (!msg)
		return -1;

	og_send_cmd((char **)params->ips_array, params->ips_array_len,
			CLIENTE_OCUPADO, msg);

	og_msg_free(msg);

	return 0;
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

	og_cmd_legacy_send(params, "EjecutaComandosPendientes", CLIENTE_OCUPADO);

	return 0;
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

enum og_rest_method {
	OG_METHOD_GET	= 0,
	OG_METHOD_POST,
};

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
	if (cli->buf_len >= sizeof(cli->buf)) {
		syslog(LOG_ERR, "client request from %s:%hu is too long\n",
		       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));
		goto close;
	}

	switch (cli->state) {
	case OG_CLIENT_RECEIVING_HEADER:
		if (cli->rest)
			ret = og_client_state_recv_hdr_rest(cli);
		else
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
		if (cli->rest) {
			ret = og_client_state_process_payload_rest(cli);
			if (ret < 0) {
				syslog(LOG_ERR, "Failed to process HTTP request from %s:%hu\n",
				       inet_ntoa(cli->addr.sin_addr),
				       ntohs(cli->addr.sin_port));
			}
		} else {
			ret = og_client_state_process_payload(cli);
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

static int socket_s, socket_rest;

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

	if (io->fd == socket_rest)
		cli->rest = true;

	syslog(LOG_DEBUG, "connection from client %s:%hu\n",
	       inet_ntoa(cli->addr.sin_addr), ntohs(cli->addr.sin_port));

	ev_io_init(&cli->io, og_client_read_cb, client_sd, EV_READ);
	ev_io_start(loop, &cli->io);
	ev_timer_init(&cli->timer, og_client_timer_cb, OG_CLIENT_TIMEOUT, 0.);
	ev_timer_start(loop, &cli->timer);
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
	struct ev_io ev_io_server, ev_io_server_rest;
	struct ev_loop *loop = ev_default_loop(0);
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
	socket_s = og_socket_server_init(puerto);
	if (socket_s < 0)
		exit(EXIT_FAILURE);

	ev_io_init(&ev_io_server, og_server_accept_cb, socket_s, EV_READ);
	ev_io_start(loop, &ev_io_server);

	socket_rest = og_socket_server_init("8888");
	if (socket_rest < 0)
		exit(EXIT_FAILURE);

	ev_io_init(&ev_io_server_rest, og_server_accept_cb, socket_rest, EV_READ);
	ev_io_start(loop, &ev_io_server_rest);

	infoLog(1); // Inicio de sesión

	/* old log file has been deprecated. */
	og_log(97, false);

	syslog(LOG_INFO, "Waiting for connections\n");

	while (1)
		ev_loop(loop, 0);

	exit(EXIT_SUCCESS);
}
