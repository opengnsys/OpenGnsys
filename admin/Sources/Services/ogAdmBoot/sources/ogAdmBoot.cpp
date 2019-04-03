// ********************************************************************************************************
// Servicio: ogAdmBoot
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Julio-2010
// Fecha Última modificación: Julio-2010
// Nombre del fichero: ogAdmBoot.cpp
// Descripción :Este fichero implementa el servicio dhcp y tftp propios del sistema
// ********************************************************************************************************
#include "ogAdmBoot.h"
#include "ogAdmLib.c"
//________________________________________________________________________________________________________
//	Función: tomaConfiguracion
//
//	Descripción:
//		Lee el fichero de configuración del servicio
//	Parámetros:
//		filecfg : Ruta completa al fichero de configuración
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error 
//________________________________________________________________________________________________________
BOOLEAN tomaConfiguracion(char* filecfg)
{
	char modulo[] = "tomaConfiguracion()";

	if (filecfg == NULL || strlen(filecfg) == 0) {
		errorLog(modulo, 1, FALSE); // Fichero de configuración del servicio vacío
		return (FALSE);
	}
	FILE *fcfg;
	long lSize;
	char * buffer, *lineas[MAXPRM], *dualparametro[2];
	int i, numlin, resul;

	fcfg = fopen(filecfg, "rt");
	if (fcfg == NULL) {
		errorLog(modulo, 2, FALSE); // No existe fichero de configuración del servicio
		return (FALSE);
	}

	fseek(fcfg, 0, SEEK_END);
	lSize = ftell(fcfg); // Obtiene tamaño del fichero.
	rewind(fcfg);
	buffer = (char*) reservaMemoria(lSize+1); // Toma memoria para el buffer de lectura.
	if (buffer == NULL) { // No hay memoria suficiente para el buffer
		errorLog(modulo, 3, FALSE);
		return (FALSE);
	}
	fread(buffer, 1, lSize, fcfg); // Lee contenido del fichero
	buffer[lSize]=(char) NULL;
	fclose(fcfg);

	IPlocal[0] = (char) NULL; //inicializar variables globales
	usuario[0] = (char) NULL;
	pasguor[0] = (char) NULL;
	datasource[0] = (char) NULL;
	catalog[0] = (char) NULL;
	router[0] = (char) NULL;
	mascara[0] = (char) NULL;

	numlin = splitCadena(lineas, buffer, '\n');
	for (i = 0; i < numlin; i++) {
		splitCadena(dualparametro, lineas[i], '=');
		resul = strcmp(StrToUpper(dualparametro[0]), "IPLOCAL");
		if (resul == 0)
			strcpy(IPlocal, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "USUARIO");
		if (resul == 0)
			strcpy(usuario, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "PASSWORD");
		if (resul == 0)
			strcpy(pasguor, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "DATASOURCE");
		if (resul == 0)
			strcpy(datasource, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "CATALOG");
		if (resul == 0)
			strcpy(catalog, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "ROUTER");
		if (resul == 0)
			strcpy(router, dualparametro[1]);
		resul = strcmp(StrToUpper(dualparametro[0]), "MASCARA");
		if (resul == 0)
			strcpy(mascara, dualparametro[1]);
	}
	if (IPlocal[0] == (char) NULL) {
		errorLog(modulo, 4, FALSE); // Falta parámetro IPLOCAL
		return (FALSE);
	}
	if (usuario[0] == (char) NULL) {
		errorLog(modulo, 6, FALSE); // Falta parámetro USUARIO
		return (FALSE);
	}
	if (pasguor[0] == (char) NULL) {
		errorLog(modulo, 7, FALSE); // Falta parámetro PASSWORD
		return (FALSE);
	}
	if (datasource[0] == (char) NULL) {
		errorLog(modulo, 8, FALSE); // Falta parámetro DATASOURCE
		return (FALSE);
	}
	if (catalog[0] == (char) NULL) {
		errorLog(modulo, 9, FALSE); // Falta parámetro CATALOG
		return (FALSE);
	}
	return (TRUE);
}
// _____________________________________________________________________________________________________________
// Función: ServicioDHCP
//
//		Descripción:
//			Esta hebra implementa el servicio DHCP
// _____________________________________________________________________________________________________________

LPVOID ServicioDHCP(LPVOID ipl)
{
	SOCKET socket_s; // Socket donde escucha el servidor
	struct TramaDhcpBootp * trmInfo;
	struct sockaddr_in local;
	int ret,resul;
	BOOLEAN bOpt;
	pthread_t hThread;
	char modulo[]="ServicioDHCP()";
			
	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		errorLog(modulo,61, TRUE);
		return(FALSE);
	}
	local.sin_addr.s_addr = htonl(INADDR_ANY); // selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(PUERTODHCPORIGEN); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		errorLog(modulo,62, TRUE);
		return(FALSE);
	}
	
   	bOpt=TRUE; // Pone el socket en modo "soportar Broadcast"
   	ret=setsockopt(socket_s,SOL_SOCKET,SO_BROADCAST,(char *)&bOpt,sizeof(bOpt));
   	if (ret == SOCKET_ERROR){
		errorLog(modulo,48, TRUE);
		return(FALSE);
    }
	while(true){
		trmInfo = (struct  TramaDhcpBootp*)malloc(sizeof(struct  TramaDhcpBootp)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
    		errorLog(modulo,64, FALSE);
			return(FALSE);
        }
		// Inicializa parámetros
		memset(trmInfo,0,sizeof(struct  TramaDhcpBootp));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		trmInfo->sck=socket_s;
		// Espera tramas DHCP
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->pckDchp, sizeof(trmInfo->pckDchp),0,(struct sockaddr *)&trmInfo->cliente, &trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
    		errorLog(modulo,65, TRUE);
			return(FALSE);
		}
		else{
			if (ret>0){
					resul=pthread_create(&hThread,NULL,GestionaServicioDHCP,(LPVOID)trmInfo);
					if(resul!=0){
			    		errorLog(modulo,66,TRUE);
		    			return(FALSE);
        			}
        			pthread_detach(hThread);	
			}
		}
	}
	close(socket_s);
}
// _____________________________________________________________________________________________________________
// Función: ServicioBOOTP
//
//		Descripción:
//			Esta hebra implementa el servicio BOOTP
// _____________________________________________________________________________________________________________

LPVOID ServicioBOOTP(LPVOID iplocal)
{
	SOCKET socket_s; // Socket donde escucha el servidor
	struct TramaDhcpBootp * trmInfo;
	struct sockaddr_in local;
	int ret,resul;
	pthread_t hThread;
	char modulo[]="ServicioBOOTP()";
	
	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		errorLog(modulo,67, TRUE);
		return(FALSE);
	}
	local.sin_addr.s_addr = htonl(INADDR_ANY); // selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(PUERTOBOOTPORIGEN); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		errorLog(modulo,68, TRUE);
		return(FALSE);
	}
	while(true){
		trmInfo = (struct  TramaDhcpBootp*)malloc(sizeof(struct  TramaDhcpBootp)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
    		errorLog(modulo,69, FALSE);
		    return(FALSE);
        }
		// Inicializa parámetros
		memset(trmInfo,0,sizeof(struct  TramaDhcpBootp));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		trmInfo->sck=socket_s;
		// Espera tramas BOOTP
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->pckDchp, sizeof(trmInfo->pckDchp),0,(struct sockaddr *)&trmInfo->cliente, &trmInfo->sockaddrsize);

		if (ret == SOCKET_ERROR){
    		errorLog(modulo,70, TRUE);
			return(FALSE);
		}
		else{
			if (ret>0){
					resul=pthread_create(&hThread,NULL,GestionaServicioBOOTP,(LPVOID)trmInfo);
					if(resul!=0){
			    		errorLog(modulo,71, TRUE);
		    			return(FALSE);
        			}
        			pthread_detach(hThread);					
			}
		}
	}
	close(socket_s);
}
// _____________________________________________________________________________________________________________
// Función: ServicioTFTP
//
//		Descripción:
//			Esta hebra implementa el servicio TFTP
// _____________________________________________________________________________________________________________

LPVOID ServicioTFTP(LPVOID ipl)
{
	SOCKET socket_s; // Socket donde escucha el servidor
	struct TramaTftp * trmInfo;
	struct sockaddr_in local;
	pthread_t hThread;
	int ret,resul;
	char modulo[]="ServicioTFTP()";

	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		errorLog(modulo,72, TRUE);
        return(FALSE);
	}
	local.sin_addr.s_addr = htonl(INADDR_ANY); // selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(PUERTOTFTPORIGEN); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		errorLog(modulo,73, TRUE);
        return(FALSE);
	}
	while(true){
		trmInfo = (struct  TramaTftp*)malloc(sizeof(struct  TramaTftp)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
    		errorLog(modulo,74, FALSE);
		    return(FALSE);
        }
		memset(trmInfo,0,sizeof(struct  TramaTftp));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		// Espera tramas TFTP
		ret = recvfrom(socket_s,(char *)&trmInfo->pckTftp, sizeof(trmInfo->pckTftp),0,(struct sockaddr *)&trmInfo->cliente,&trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			errorLog(modulo,75, TRUE);
			return(FALSE);
		}
		else{
			if (ret>0){
				resul=pthread_create(&hThread,NULL,GestionaServicioTFTP,(LPVOID)trmInfo);
				if(resul!=0){
					errorLog(modulo,76, TRUE);
		    		return(FALSE);
        		}
        		pthread_detach(hThread);
			}
		}
		
	}
	close(socket_s);
}
//________________________________________________________________________________________________________
//	Función: GestionaServicioDHCP
//
//	Descripción:
//		Gestiona la conexiónn con un cliente que sea Hidra para el servicio DHCP
//	Parámetros:
//		lpParam: Puntero a la estructura de control para la conversacion DHCP
//	Devuelve:
//		TRUE: Si el proceso es correcto
//		FALSE: En caso de ocurrir algún error 
//________________________________________________________________________________________________________
LPVOID GestionaServicioDHCP(LPVOID lpParam)
{
	struct TramaDhcpBootp * trmInfo=(struct TramaDhcpBootp *)lpParam;
	char IPCliente[16]; // Ip del cliente
	char MACCliente[16]; // Mac del cliente

	if(!OpcionesPresentes(trmInfo->pckDchp.magiccookie)) // Comprueba que existen opciones en la trama Dhcp
		return(false);
	strcpy(IPCliente,inet_ntoa(trmInfo->cliente.sin_addr));
	if(strcmp(IPCliente,"0.0.0.0")!=0){ // El cliente tiene una IP concreta distinta de 0.0.0.0
		if(!ClienteExistente(trmInfo,IPCliente,1)){ // Comprueba que se trata de un cliente Hidra
			free(trmInfo);
			return(false);
		}
	}
	else{
		sprintf(MACCliente,"%02.2x%02.2x%02.2x%02.2x%02.2x%02.2x",(unsigned int)trmInfo->pckDchp.chaddr[0],(unsigned int)trmInfo->pckDchp.chaddr[1],(unsigned int)trmInfo->pckDchp.chaddr[2],(unsigned int)trmInfo->pckDchp.chaddr[3],(unsigned int)trmInfo->pckDchp.chaddr[4],(unsigned int)trmInfo->pckDchp.chaddr[5]);
		if(!ClienteExistente(trmInfo,MACCliente,0)){ // Comprueba que se trata de un cliente Hidra (Por la Mac)
			free(trmInfo);
			return(false);
		}
	}
	if(OpcionPXEClient(&trmInfo->pckDchp)) // Comprueba que sea un cliente PXE 
		ProcesaTramaClientePXE(trmInfo); // Procesa DHCP para el protocolo PXE
	else
		ProcesaTramaClienteDHCP(trmInfo); // Procesa DHCP de cliente Windows o Linux

	free(trmInfo);
	return(false);
}
//_______________________________________________________________________________________________________________
//
// Gestiona la conexion con un cliente que sea Hidra para el servicio BOOTP
// Parámetros:
//		lpParam: Puntero a la estructura de control para la conversacion BOOTP
//_______________________________________________________________________________________________________________
LPVOID GestionaServicioBOOTP(LPVOID lpParam)
{
	struct TramaDhcpBootp * trmInfo=(struct TramaDhcpBootp *)lpParam;
	char IPCliente[16]; // Ip del cliente

	if(!OpcionesPresentes(trmInfo->pckDchp.magiccookie)) // Comprueba que existen opciones en la trama Dhcp
		return(false);

	strcpy(IPCliente,inet_ntoa(trmInfo->cliente.sin_addr));
	if(!ClienteExistente(trmInfo,IPCliente,1)) // Comprueba que se trata de un cliente Hidra
		return(false);

	if(OpcionPXEClient(&trmInfo->pckDchp)) // Comprueba que sea un cliente PXE 
		ProcesaTramaClienteBOOTP(trmInfo); // Procesa DHCP para el protocolo PXE

	free(trmInfo);
	return(trmInfo);
}
//_______________________________________________________________________________________________________________
//
// Comprueba si la IP del cliente está en la base de datos de Hidra
// Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//		ipmac: IP o MAC del cliente que ha abierto la hebra
//		sw: Si vale 1 o 2 o 3 el parámetro anterior ser una IP en caso contrario ser una MAC
//
//	Devuelve:
//		true: Si el cliente est en la base de datos
//		false: En caso contrario
// 
//  Comentarios:
//		Slo se procesarn mensages dhcp de clientes hidra.
//_______________________________________________________________________________________________________________
int ClienteExistente(struct TramaDhcpBootp *trmInfo,char* ipmac,int sw)
{
	char sqlstr[1000],ErrStr[200];	
	Database db;
	Table tbl;
	char wrouter[LONPRM];
	char wmascara[LONPRM];
	/////////////////////////////////////////////////////////////////////////
	// ACCESO atnico A TRAVEZ DE OBJETO MUTEX a este trozo de cnigo 
	pthread_mutex_lock(&guardia); 
	
	// Abre conexion con base de datos
	if(!db.Open(usuario,pasguor,datasource,catalog)){ // error de conexion
		db.GetErrorErrStr(ErrStr);
		return(false);
	}	
	
	if(sw==1 || sw==2){ // Bsqueda por IP
		sprintf(sqlstr,"SELECT ip,router,mascara FROM ordenadores WHERE ip='%s' ",ipmac);
	}
	else{ // Bsqueda por MAC
		sprintf(sqlstr,"SELECT ip,router,mascara FROM ordenadores WHERE mac='%s' ",ipmac);
	}
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		db.Close();
		pthread_mutex_unlock(&guardia); 
		return(false);
	}

	if(tbl.ISEOF()){ // No existe el cliente
		db.Close();
		pthread_mutex_unlock(&guardia); 
		return(false);
	}
	if(sw==1 || sw==0){ // Sólo para las tramas dhcp PXE y BOOTP
		if(!tbl.Get("ip",trmInfo->bdIP)){ // Incorpora la IP a asignar al cliente a la estructura de control
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			db.Close();
			pthread_mutex_unlock(&guardia); 
			return(false);
		}	
		if(!tbl.Get("router",wrouter)){ // Toma la configuración router del cliente
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			db.Close();
			pthread_mutex_unlock(&guardia);
			return(false);
		}
		if(strlen(wrouter)>0)
			strcpy(oProuter,wrouter);
		else
			strcpy(oProuter,router);

		if(!tbl.Get("mascara",wmascara)){ // Toma la configuración router del cliente
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			db.Close();
			pthread_mutex_unlock(&guardia);
			return(false);
		}
		if(strlen(wmascara)>0)
			strcpy(oPmascara,wmascara);
		else
			strcpy(oPmascara,mascara);
	}
	db.Close();
	pthread_mutex_unlock(&guardia); 
	////////////////////////////////////////////////////////////////////////////////
	return(true);
}
//_______________________________________________________________________________________________________________
//
// Comprueba que existen opciones en el mensage dhcp analizando la magic cookie
// Parámetros:
//		mc: Puntero al primer elemento de la magic cookie (primer campo de las opciones)
//
//	Devuelve:
//		true: Si esta presenta el valor 99..130.83.99
//		false: En caso contrario
//_______________________________________________________________________________________________________________
int OpcionesPresentes(unsigned char *mc)
{
	if(mc[0]!=0x63) return(false);
	if(mc[1]!=0x82) return(false);
	if(mc[2]!=0x53) return(false);
	if(mc[3]!=0x63) return(false);

	// Magic Cookie presente
	return(true);
}
//_______________________________________________________________________________________________________________
//
// Busca una determinada opción dentro de la trama dhcp
// Parámetros:
//		dhcp_packet: Puntero a la estructura que contiene el mensaje dhcp
//		codop: Cdigo de la opción a buscar
//
//	Devuelve:
//		Si existe, un puntero al primer elemento de la estructura de la opción (codigo,longitud,valor)
//		en caso contrario devuelve null
//_______________________________________________________________________________________________________________
unsigned char * BuscaOpcion(dhcp_packet* tDhcp,unsigned char codop)
{
	unsigned char wcodop;
	unsigned char wlongitud;
	unsigned char *ptrOp,*ptrDhcp;

	ptrDhcp=(unsigned char*)tDhcp; // Se toma el puntero al principio del mensage
	ptrOp=(unsigned char*)&tDhcp->options[0]; // Se toma el puntero a las opciones

	while(ptrOp-ptrDhcp<DHCP_OPTION_LEN-4){
		wcodop = ptrOp[0];
		if(wcodop==DHCP_PAD)
			ptrOp++;
		else{
			if(wcodop==DHCP_END) // Fin de la cadena de opciones, no se encontr la opción
				return(NULL);
			else{
				
				if (wcodop == codop) // Encuentra la opción
					return(ptrOp); // Devuelve el puntero a la estructura variable opción
				else{
					wlongitud = ptrOp[1]; 
					ptrOp+=wlongitud+2; // Avanza hasta la prxima opción
				}
			}
		}
	}
	return(NULL);
}
//_______________________________________________________________________________________________________________
//
// Comprueba si el mensaje recibido proviene de un cliente PXE
// Parámetros:
//		dhcp_packet: Puntero a la estructura que contiene el mensaje dhcp
//
//	Devuelve:
//		true: Si el mensaje lo ha enviado un cliente PXE
//		false: En caso contrario
//_______________________________________________________________________________________________________________
int OpcionPXEClient(dhcp_packet* tDhcp)
{
	if(!BuscaOpcion(tDhcp,DHCP_CLASS_IDENTIFIER))
		return(false);
	else
		return(true);
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje DHCP recibido que proviene de un cliente PXE
// Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//_______________________________________________________________________________________________________________
void ProcesaTramaClientePXE(struct TramaDhcpBootp* trmInfo)
{
	unsigned char codop;
	unsigned char longitud;
	unsigned char *ptrOp;
	unsigned char *msgDhcp;
	char modulo[]="ProcesaTramaClientePXE()";

	ptrOp=BuscaOpcion(&trmInfo->pckDchp,DHCP_MESSAGE_TYPE); // Puntero a la opción tipo de mensaje
	if(!ptrOp){ // No existe la opción DHCP
		errorLog(modulo,77, FALSE);
		return; 
	}
	codop = ptrOp[0];
	longitud=ptrOp[1];
	msgDhcp=ptrOp+2; // Puntero al dato tipo de mensaje
	switch(*msgDhcp){
		case DHCPDISCOVER:
			dhcpDISCOVER_PXE(trmInfo);
			break;
		case DHCPREQUEST:
			dhcpREQUEST_PXE(trmInfo);
			break;
	}
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje BOOTP recibido que proviene de un cliente PXE
// Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin BOOTP
//_______________________________________________________________________________________________________________
void ProcesaTramaClienteBOOTP(struct TramaDhcpBootp* trmInfo)
{
	unsigned char codop;
	unsigned char longitud;
	unsigned char *ptrOp;
	unsigned char *msgDhcp;
	char modulo[]="ProcesaTramaClienteBOOTP()";

	ptrOp=BuscaOpcion(&trmInfo->pckDchp,DHCP_MESSAGE_TYPE); // Puntero a la opción tipo de mensaje
	if(!ptrOp){ // No existe la opción DHCP
		errorLog(modulo,77, FALSE);
		return; 
	}
	codop = ptrOp[0];
	longitud=ptrOp[1];
	msgDhcp=ptrOp+2; // Puntero al dato tipo de mensaje
	switch(*msgDhcp){
		case DHCPREQUEST:
			bootpREQUEST_PXE(trmInfo);
			break;
	}
}
//_______________________________________________________________________________________________________________
//
// Gestiona la conexion con un cliente que sea Hidra para el servicio TFTP
// Parámetros:
//		lpParam: Puntero a la estructura de control para la conversacion TFTP
//_______________________________________________________________________________________________________________
LPVOID GestionaServicioTFTP(LPVOID lpParam)
{
	struct TramaTftp * trmInfo=(struct TramaTftp *)lpParam;
	char IPCliente[16]; // Ip del cliente

	strcpy(IPCliente,inet_ntoa(trmInfo->cliente.sin_addr));
	if(!ClienteExistente((struct TramaDhcpBootp*)trmInfo,IPCliente,2)) // Comprueba que se trata de un cliente Hidra
		return(false);

	// Inicializa parámetros
	trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
	trmInfo->sck=TomaSocketUser();
	if(trmInfo->sck==INVALID_SOCKET) return(false); // Ha habido algn problama para crear el socket de usuario

	trmInfo->currentopCode=0x0000;	// Cdigo de operación para detectar errores de secuencia
	trmInfo->bloquesize=512;		// Tamao de bloque por defecto 512
	trmInfo->tsize=0;				// Tamao del fichero
	trmInfo->interval=0;			// Intervalo

	ProcesaTramaClienteTFTP(trmInfo); // Procesa TFTP para el protocolo PXE
	close(trmInfo->sck);
	free(trmInfo);
	return(trmInfo);
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje TFTP recibido que proviene de un cliente PXE
// Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin TFTP
//_______________________________________________________________________________________________________________
void ProcesaTramaClienteTFTP(struct TramaTftp* trmInfo)
{
	char *ptr;
	int bloque,lon,ret;
	char tam[20];
	struct tftppacket_ack* ptrack;
	char modulo[]="ProcesaTramaClienteTFTP()";
	
	while(true){
		switch(ntohs(trmInfo->pckTftp.opcode)){
			case TFTPRRQ: // Read Request
				if(trmInfo->currentopCode!=0x0000) return; // Error en la secuencia de operaciones
				if(!PeticionFichero(trmInfo)) return;
				fseek(trmInfo->fileboot,0,SEEK_SET);
				trmInfo->pckTftp.opcode= htons(TFTPOACK);
				trmInfo->currentopCode=TFTPOACK;
				ptr=&trmInfo->pckTftp.buffer[0];
				if(trmInfo->tsize>0){ // opción tsize
					strcpy(ptr,"tsize");
					ptr+=strlen(ptr)+1;
					//itoa(trmInfo->tsize,tam,10);
					sprintf(tam,"%d",trmInfo->tsize);
					strcpy(ptr,tam);
					ptr+=strlen(ptr)+1;
					*ptr=0x00;
				}
				else{
					if(trmInfo->bloquesize>0){ // opción blksize
						strcpy(ptr,"blksize");
						ptr+=strlen(ptr)+1;
						//itoa(trmInfo->bloquesize,tam,10);
						sprintf(tam,"%d",trmInfo->bloquesize);
						strcpy(ptr,tam);
						ptr+=strlen(ptr)+1;
						*ptr=0x00;
					}
					else
						trmInfo->bloquesize=512;
				}

				lon=ptr-(char*)&trmInfo->pckTftp;
				//ret=connect(trmInfo->sck,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
				//if (ret == SOCKET_ERROR){
				//	RegistraLog("***connect() fallo:",true);
				//	return;
				//}
				ret=sendto(trmInfo->sck,(char*)&trmInfo->pckTftp,lon,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
				//ret=send(trmInfo->sck,(char*)&trmInfo->pckTftp,lon,0);
				if (ret == SOCKET_ERROR){
					errorLog(modulo,26, TRUE);
					return;
				}
				break;

			case TFTPACK: // 
				if(trmInfo->currentopCode!=TFTPOACK && trmInfo->currentopCode!=TFTPDATA) return; // Error en la secuencia de operaciones
				ptrack=(struct tftppacket_ack*)&trmInfo->pckTftp;
				bloque=ntohs(ptrack->block);
				trmInfo->currentopCode=TFTPDATA;
				ptrack->opcode=htons(TFTPDATA);
				bloque++;
				ptrack->block=htons(bloque);
				trmInfo->numblock=bloque;
				lon=fread(ptrack->buffer,1,trmInfo->bloquesize,trmInfo->fileboot);
				//ret=connect(trmInfo->sck,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
				//if (ret == SOCKET_ERROR){
				//	RegistraLog("***connect() fallo:",true);
				//	return;
				//}
				ret=sendto(trmInfo->sck,(char*)&trmInfo->pckTftp,lon+4,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
				//ret=send(trmInfo->sck,(char*)&trmInfo->pckTftp,lon+4,0);
				if (ret == SOCKET_ERROR){
					errorLog(modulo,26, TRUE);
					return;
				}
				if(lon==0)
					return; // Fin de la trama tftp
				break;					
			case TFTPERROR:
				errorLog(modulo,78, TRUE);
		}
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->pckTftp, sizeof(trmInfo->pckTftp),0,(struct sockaddr *)&trmInfo->cliente,&trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			errorLog(modulo,79, TRUE);
			return;
		}
		else
			if(ret==0) break;
	 }
	 return;
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje Tftp de peticin de fichero. Recupera datos de tamao de bloque y otros parámetros
//	para gestionar la conversacin.
// 
//	Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin TFTP
//_______________________________________________________________________________________________________________
int PeticionFichero(struct TramaTftp* trmInfo)
{
	char *ptr;
	char nomfile[250];

	if(strncmp(trmInfo->pckTftp.buffer,"pxelinux.cfg",12)==0)
		strcpy(nomfile,"default");
	else
		strcpy(nomfile,trmInfo->pckTftp.buffer);
	
	trmInfo->currentopCode=ntohs(trmInfo->pckTftp.opcode); // Guarda código de operación
	// Localiza parámetros
	ptr=&trmInfo->pckTftp.buffer[0];
	ptr+=strlen(ptr)+1; // Avanza al campo siguiente al del nombre de fichero
	if(!strcmp(ptr,"octet")){	// Modo de apertura
		//trmInfo->fileboot=fopen(trmInfo->pckTftp.buffer,"rb");
		trmInfo->fileboot=fopen(nomfile,"rb");
	}
	else{
		//trmInfo->fileboot=fopen(trmInfo->pckTftp.buffer,"rt");
		trmInfo->fileboot=fopen(nomfile,"rt");		
	}
	if(trmInfo->fileboot==NULL)
		return(false); // No existe el fichero boot
	ptr+=strlen(ptr)+1; // Paso al parámetro siguiente
	while(*ptr){
		if(strcmp(ptr,"blksize")==0){	// parámetro blksize
			ptr+=strlen(ptr) + 1;
			trmInfo->bloquesize=atoi(ptr);
			if(trmInfo->bloquesize<512) trmInfo->bloquesize=512; 
			if(trmInfo->bloquesize>MAXBLOCK) trmInfo->bloquesize=512; 
			ptr+=strlen(ptr) + 1;
		}
		else{
			if(strcmp(ptr,"tsize")==0){	// parámetro tsize
				ptr+=strlen(ptr) + 1;
				fseek(trmInfo->fileboot,0,SEEK_END);
				trmInfo->tsize=ftell(trmInfo->fileboot);
				ptr+=strlen(ptr) + 1;
			}
			else{
				if(strcmp(ptr,"interval")==0){	// Tamao de los bloques
					ptr+=strlen(ptr) + 1;
					trmInfo->interval=atoi(ptr);
					ptr+=strlen(ptr) + 1;
				}
				else
					return(false);
			}
		
		}
	}
	return(true);
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje recibido que proviene de un cliente Hidra pero no en el momento de arranque con PXE
// sino cuando arranca con algn S.O. como (Windows oLinux)
// 
//	Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//_______________________________________________________________________________________________________________
void ProcesaTramaClienteDHCP(struct TramaDhcpBootp* trmInfo)
{
	unsigned char codop;
	unsigned char longitud;
	unsigned char *ptrOp;
	unsigned char *msgDhcp;
	int ret;
	char modulo[]="ProcesaTramaClienteDHCP()";

	while(true){
		ptrOp=BuscaOpcion(&trmInfo->pckDchp,DHCP_MESSAGE_TYPE); // Puntero a la opción tipo de mensaje
		if(!ptrOp){ // No existe la opción DHCP
			errorLog(modulo,77, FALSE);
			return; 
		}
		codop = ptrOp[0];
		longitud=ptrOp[1];
		msgDhcp=ptrOp+2; // Puntero al dato tipo de mensaje

		switch(*msgDhcp){
			case DHCPDISCOVER:
				dhcpDISCOVER_PXE(trmInfo);
				break;
			case DHCPREQUEST:
				dhcpREQUEST_PXE(trmInfo);
				break;
		}
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->pckDchp, sizeof(trmInfo->pckDchp),0,(struct sockaddr *)&trmInfo->cliente,&trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			errorLog(modulo,80, TRUE);
		}
		else
			if(ret==0) break;
	 }
}
//_______________________________________________________________________________________________________________
//
// Rellena el campo IP asignada(yiaddr) al cliente dentro del mensaje DHCP
//
//	Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//_______________________________________________________________________________________________________________
void RellenaIPCLiente(struct TramaDhcpBootp* trmInfo)
{
	unsigned long aux;

	aux=inet_addr(trmInfo->bdIP); // Ip para el cliente
	memcpy((void*)&trmInfo->pckDchp.yiaddr,&aux,sizeof(aux));
}
//_______________________________________________________________________________________________________________
//
// Rellena el campo IP del servidor(siaddr) dentro del mensaje DHCP
//
//	Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//_______________________________________________________________________________________________________________
void RellenaIPServidor(struct TramaDhcpBootp* trmInfo)
{
	unsigned long aux;

	aux=inet_addr(IPlocal); // Ip del servidor
	memcpy(&trmInfo->pckDchp.siaddr,&aux,sizeof(aux));
}
//_______________________________________________________________________________________________________________
//
// Rellena el campo nombre del servidor boot dentro del mensaje BOOTP 
//
//	Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin BOOTP
//_______________________________________________________________________________________________________________
void RellenaNombreServidorBoot(struct TramaDhcpBootp* trmInfo)
{
	char aux[100];

	strcpy(aux,"Hidra 2.0 PXE Boot Server");
	memcpy(&trmInfo->pckDchp.sname,&aux,25);
}
//_______________________________________________________________________________________________________________
//
// Rellena el campo nombre del fichero boot dentro del mensaje BOOTP 
//
//	Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin BOOTP
//_______________________________________________________________________________________________________________
void RellenaNombreFicheroBoot(struct TramaDhcpBootp* trmInfo)
{
	char aux[100];

	strcpy(aux,"pxelinux.0");
	memcpy(&trmInfo->pckDchp.file,&aux,25);
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje DHCPDISCOVER
// 
//	Parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//
//	Devuelve:
//		true: Si el mensaje se procesa correctamente
//		false: En caso contrario
//_______________________________________________________________________________________________________________
void dhcpDISCOVER_PXE(struct TramaDhcpBootp* trmInfo)
{
	unsigned char *ptrOp,*ptrDhcp;
	int lon,ret;
	char modulo[]="dhcpDISCOVER_PXE()";

	ptrDhcp=(unsigned char*)&trmInfo->pckDchp; // Se toma el puntero al principio del mensage
	ptrOp=(unsigned char*)&trmInfo->pckDchp.options[0]; // Se toma el puntero a las opciones
	lon=ptrOp-ptrDhcp; //Longitud del mensaje sin las opciones ni la magic coockie

	RellenaIPCLiente(trmInfo);
	RellenaIPServidor(trmInfo);
	*ptrOp='\0'; //Borra opciones del mensaje recibido para colocar las nuevas
	

	AdjDHCPOFFER(&ptrOp,&lon);			// Genera opción de Mensaje (0x35) dhcp valor 1
	AdjSERVERIDENTIFIER(&ptrOp,&lon);	// Genera opción de Mensaje (0x36) Dhcp
	AdjLEASETIME(&ptrOp,&lon);			// Genera opción de Mensaje (0x33) Dhcp
	AdjSUBNETMASK(&ptrOp,&lon);			// Genera opción de Mensaje (0x01) Dhcp
	AdjROUTERS(&ptrOp,&lon);			// Genera opción de Mensaje (0x03) Dhcp
	AdjCLASSIDENTIFIER(&ptrOp,&lon);	// Genera opción de Mensaje (0x3c) Dhcp
	*ptrOp=DHCP_END;
	lon++;
	trmInfo->pckDchp.op=DHCPOFFER;
	//MandaRespuesta(&trmInfo->pckDchp,htonl(INADDR_BROADCAST),lon,htons(PUERTODHCPDESTINO));
	trmInfo->cliente.sin_addr.s_addr=htonl(INADDR_BROADCAST);
	ret=sendto(trmInfo->sck,(char*)&trmInfo->pckDchp,lon,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	if (ret == SOCKET_ERROR){
		errorLog(modulo,26, TRUE);
	}
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje DHCPREQUEST (DHCP)
// 
//	Parámetros:
//		trmInfo: Puntero a la estructura de control para la conversacin DHCP
//
//	Devuelve:
//		true: Si el mensaje se procesa correctamente
//		false: En caso contrario
//_______________________________________________________________________________________________________________
void dhcpREQUEST_PXE(struct TramaDhcpBootp* trmInfo)
{
	unsigned char * ptrOp,*ptrDhcp;
	struct dhcp_opcion;
	int lon,ret;
	char modulo[]="dhcpREQUEST_PXE()";

	ptrDhcp=(unsigned char*)&trmInfo->pckDchp; // Se toma el puntero al principio del mensage
	ptrOp=(unsigned char*)&trmInfo->pckDchp.options[0]; // Se toma el puntero a las opciones
	lon=ptrOp-ptrDhcp; //Longitud del mensaje sin las opciones ni la magic coockie

	RellenaIPCLiente(trmInfo);
	RellenaIPServidor(trmInfo);
	*ptrOp='\0'; //Borra opciones del mensaje recibido para colocar las nuevas

	AdjDHCPACK(&ptrOp,&lon);			// Ge		db.Close();nera opción de Mensaje (0x35) dhcp valor 5
	AdjSERVERIDENTIFIER(&ptrOp,&lon);	// Genera opción de Mensaje (0x36) Dhcp
	AdjLEASETIME(&ptrOp,&lon);			// Genera opción de Mensaje (0x33) Dhcp
	AdjSUBNETMASK(&ptrOp,&lon);			// Genera opción de Mensaje (0x01) Dhcp
	AdjROUTERS(&ptrOp,&lon);			// Genera opción de Mensaje (0x03) Dhcp
	AdjCLASSIDENTIFIER(&ptrOp,&lon);	// Genera opción de Mensaje (0x3c) Dhcp

	*ptrOp=DHCP_END;
	lon++;

	trmInfo->pckDchp.op=DHCPOFFER;
	//MandaRespuesta(&trmInfo->pckDchp,htonl(INADDR_BROADCAST),lon,htons(PUERTODHCPDESTINO));
	trmInfo->cliente.sin_addr.s_addr=htonl(INADDR_BROADCAST);
	ret=sendto(trmInfo->sck,(char*)&trmInfo->pckDchp,lon,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	if (ret == SOCKET_ERROR){
		errorLog(modulo,26, TRUE);
	}
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje DHCPREQUEST (BOOTP)
// 
//	Parámetros:
//		trmInfo: Puntero a la estructura de control para la conversacin BOOTP
//
//	Devuelve:
//		true: Si el mensaje se procesa correctamente
//		false: En caso contrario
//_______________________________________________________________________________________________________________
void bootpREQUEST_PXE(struct TramaDhcpBootp* trmInfo)
{
	unsigned char * ptrOp,*ptrDhcp;
	struct dhcp_opcion;
	int lon,ret;
	unsigned long aux;
	char modulo[]="bootpREQUEST_PXE()";

	ptrDhcp=(unsigned char*)&trmInfo->pckDchp; // Se toma el puntero al principio del mensage
	ptrOp=(unsigned char*)&trmInfo->pckDchp.options[0]; // Se toma el puntero a las opciones
	lon=ptrOp-ptrDhcp; //Longitud del mensaje sin las opciones ni la magic coockie

	aux=inet_addr("0.0.0.0"); // Borra Ip del cliente ( No se porqu pero en la trama aparece as)
	memcpy(&trmInfo->pckDchp.ciaddr,&aux,4);
	RellenaNombreServidorBoot(trmInfo);
	RellenaNombreFicheroBoot(trmInfo);

	*ptrOp='\0'; //Borra opciones del mensaje recibido para colocar las nuevas

	AdjDHCPACK(&ptrOp,&lon);			// Genera opción de Mensaje (0x35) dhcp valor 5
	AdjSERVERIDENTIFIER(&ptrOp,&lon);	// Genera opción de Mensaje (0x36) Dhcp
	AdjBOOTSIZE(&ptrOp,&lon);			// Genera opción de Mensaje (0x0D) Dhcp
	AdjCLASSIDENTIFIER(&ptrOp,&lon);	// Genera opción de Mensaje (0x3c) Dhcp

	*ptrOp=DHCP_END;
	lon++;

	trmInfo->pckDchp.op=DHCPOFFER;
	//ret=connect(trmInfo->sck,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	//if (ret == SOCKET_ERROR){
	//	RegistraLog("***connect() fallo:",true);
	//	return;
	//}
	//ret=send(trmInfo->sck,(char*)&trmInfo->pckDchp,lon,0);
	ret=sendto(trmInfo->sck,(char*)&trmInfo->pckDchp,lon,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);	
	if (ret == SOCKET_ERROR){
		errorLog(modulo,26,TRUE);
		return;
	}
}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x35(53) con el valor "Dhcp Offer" valor 2
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de Mensaje dhcp Offer
//_______________________________________________________________________________________________________________
void AdjDHCPOFFER(unsigned char* *ptrOp,int*lon)
{
	**ptrOp=DHCP_MESSAGE_TYPE;
	*ptrOp+=1;
	**ptrOp=1;
	*ptrOp+=1;
	**ptrOp=DHCPOFFER;
	*ptrOp+=1;
	*lon+=3;
}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x35(53) con el valor "Dhcp Ack" valor 5
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de Mensaje dhcp Ack
//_______________________________________________________________________________________________________________
void AdjDHCPACK(unsigned char** ptrOp,int*lon)

{
	**ptrOp=DHCP_MESSAGE_TYPE;
	*ptrOp+=1;
	**ptrOp=1;
	*ptrOp+=1;
	**ptrOp=DHCPACK;
	*ptrOp+=1;
	*lon+=3;
}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x03(3) con la IP del router
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de Ip del Routers
//_______________________________________________________________________________________________________________
void AdjROUTERS(unsigned char** ptrOp,int*lon)
{
	unsigned long aux;
	aux=inet_addr(oProuter); // Router

	**ptrOp=DHCP_ROUTERS;
	*ptrOp+=1;
	**ptrOp=4;
	*ptrOp+=1;
	memcpy(*ptrOp,&aux,4); // Copia la Ip del router en la estructura
	*ptrOp+=4;
	*lon+=6;

}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x01(1) con la mascara de red
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de mscara de red
//_______________________________________________________________________________________________________________
void AdjSUBNETMASK(unsigned char** ptrOp,int*lon)
{
	unsigned long aux;
	aux=inet_addr(oPmascara); // Mascara de red

	**ptrOp=DHCP_SUBNET_MASK;
	*ptrOp+=1;
	**ptrOp=4;
	*ptrOp+=1;
	memcpy(*ptrOp,&aux,4); // Copia la máscara de red
	*ptrOp+=4;
	*lon+=6;
}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x3c(60) con el literal "PXECLient" para clientes PXE
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de clase de cliente
//_______________________________________________________________________________________________________________
void AdjCLASSIDENTIFIER(unsigned char** ptrOp,int*lon)
{
	**ptrOp=DHCP_CLASS_IDENTIFIER;
	*ptrOp+=1;
	**ptrOp=9;
	*ptrOp+=1;
	memcpy(*ptrOp,"PXEClient",9); // Copia el literal PXClient
	*ptrOp+=9;
	*lon+=11;
}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x36(54) con la IP del servidor
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de Ip del servidor
//_______________________________________________________________________________________________________________
void AdjSERVERIDENTIFIER(unsigned char** ptrOp,int*lon)
{
	unsigned long aux;
	aux=inet_addr(IPlocal); // Ip del servidor

	**ptrOp=DHCP_SERVER_IDENTIFIER;
	*ptrOp+=1;
	**ptrOp=4;
	*ptrOp+=1;
	memcpy(*ptrOp,&aux,4); // Copia la Ip del ervidor en la estructura
	*ptrOp+=4;
	*lon+=6;

}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x33(51) con el tiempo de "lease" de la IP
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones del "Lease Time"
//_______________________________________________________________________________________________________________
void AdjLEASETIME(unsigned char** ptrOp,int*lon)
{
	unsigned long aux;
	aux=0x00006054; // tiempo en segundos 

	**ptrOp=DHCP_LEASE_TIME;
	*ptrOp+=1;
	**ptrOp=4;
	*ptrOp+=1;
	memcpy(*ptrOp,&aux,4); // Copia el lease time en la estructura
	*ptrOp+=4;
	*lon+=6;
}
//_______________________________________________________________________________________________________________
//
// Genera una opción del tipo 0x0D(13) con el tiempo tamao del fichero boot
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones del "Lease Time"
//_______________________________________________________________________________________________________________
void AdjBOOTSIZE(unsigned char** ptrOp,int*lon)
{
	unsigned short aux;
	aux=0x0402; // Tamao en bytes 

	**ptrOp=DHCP_BOOT_SIZE;
	*ptrOp+=1;
	**ptrOp=2;
	*ptrOp+=1;
	memcpy(*ptrOp,&aux,2); // Copia el tamao en la estructura
	*ptrOp+=2;
	*lon+=4;
}
//_______________________________________________________________________________________________________________
//
// Crea un socket en un puerto determinado para la conversacin de las distintas hebras
// 
//_______________________________________________________________________________________________________________
SOCKET TomaSocketUser()
{
	SOCKET socket_c; // Socket para hebras (UDP)
    struct sockaddr_in cliente;
	int ret,puerto;
	BOOLEAN bOpt;
	char modulo[]="TomaSocketUser()";

	socket_c = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP

	if (socket_c == SOCKET_ERROR){
		errorLog(modulo,81,TRUE);
        return(false);
	}
	cliente.sin_addr.s_addr = htonl(INADDR_ANY); // selecciona interface
	cliente.sin_family = AF_INET;
	puerto=PUERTOMINUSER;
	while(puerto<PUERTOMAXUSER){ // Busca puerto libre
		cliente.sin_port = htons(puerto); // Puerto asignado
		if (bind(socket_c,(struct sockaddr *)&cliente,sizeof(cliente)) == SOCKET_ERROR)
			puerto++;
		else
			break;
	}
	if(puerto>=PUERTOMAXUSER){ // No hay puertos libres
		errorLog(modulo,63, TRUE);
		return(INVALID_SOCKET);
	}

   	bOpt=TRUE; // Pone el socket en modo "soportar Broadcast"
   	ret=setsockopt(socket_c,SOL_SOCKET,SO_BROADCAST,(char *)&bOpt,sizeof(bOpt));
   	if (ret == SOCKET_ERROR){
		errorLog(modulo,48, TRUE);
		return(INVALID_SOCKET);
    }
	return(socket_c);
}
//_______________________________________________________________________________________________________________
void Pinta(dhcp_packet* tdp)
{
	return;
	printf("\nop = %d  htype = %d  hlen = %d  hops = %d",tdp -> op, tdp -> htype, tdp -> hlen, tdp -> hops);
	//printf ("\nxid = %x  secs = %d  flags = %x",tdp -> xid, tdp -> secs, tdp -> flags);
	printf ("\nciaddr = %s", inet_ntoa (tdp -> ciaddr));
	printf ("\nyiaddr = %s", inet_ntoa (tdp -> yiaddr));
	printf ("\nsiaddr = %s", inet_ntoa (tdp -> siaddr));
	printf ("\ngiaddr = %s", inet_ntoa (tdp -> giaddr));
	printf ("\nchaddr = %x:%x:%x:%x:%x:%x",((unsigned char *)(tdp -> chaddr)) [0],((unsigned char *)(tdp -> chaddr)) [1],((unsigned char *)(tdp -> chaddr)) [2],((unsigned char *)(tdp -> chaddr)) [3],((unsigned char *)(tdp -> chaddr)) [4],((unsigned char *)(tdp -> chaddr)) [5]);
	printf ("\nfilename = %s", tdp -> file);
	printf ("\nserver_name = %s", tdp -> sname);

	printf ("\n\n");
}
//***************************************************************************************************************
// PROGRAMA PRINCIPAL 
//***************************************************************************************************************
int main(int argc, char **argv)
{
	pthread_t hThreadDHCP,hThreadBOOTP,hThreadTFTP;
	int resul;
 	char modulo[] = "main()";
	
	/*--------------------------------------------------------------------------------------------------------
		Validación de parámetros de ejecución y lectura del fichero de configuración del servicio
	 ---------------------------------------------------------------------------------------------------------*/
	if (!validacionParametros(argc, argv,4)) // Valida parámetros de ejecución
		exit(EXIT_FAILURE);

	if (!tomaConfiguracion(szPathFileCfg)) { // Toma parametros de configuracion
		exit(EXIT_FAILURE);
	}
	
	infoLog(1); // Inicio de sesión
	
	// Hebra servicio DHCP ---------------------------------
	resul=pthread_create(&hThreadDHCP,NULL,ServicioDHCP,(LPVOID)IPlocal);
	if(resul!=0){
		errorLog(modulo, 58, TRUE);
		exit(EXIT_FAILURE);
    }
    pthread_detach(hThreadDHCP);
    
  
	// Hebra servicio BOOTP  ---------------------------------
  	resul=pthread_create(&hThreadBOOTP,NULL,ServicioBOOTP,(LPVOID)IPlocal);
	if(resul!=0){
		errorLog(modulo,59,TRUE);
		exit(EXIT_FAILURE);
    }
    pthread_detach(hThreadBOOTP);
    
	// Hebra servicio TFTP  ----------------------------------
   	resul=pthread_create(&hThreadTFTP,NULL,ServicioTFTP,(LPVOID)IPlocal);
	if(resul!=0){
		errorLog(modulo,60,TRUE);
		exit(EXIT_FAILURE);
    }
    pthread_detach(hThreadTFTP);
    
    while (true)
    	sleep(1000);
    	
}
