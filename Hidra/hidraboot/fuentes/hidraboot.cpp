// *************************************************************************************************************
// Aplicacin HIDRA
// Copyright 2003-2005 Jos Manuel Alonso. Todos los derechos reservados.
// Fichero: hidrabootp.cpp
// 
//	Descripcin:
//	Este fichero implementa el servicio dhcp para la aplicacin hidra, en  un ordenador con plataforma windows NT.
// **************************************************************************************************************
#include "hidraboot.h"
#include "encriptacion.c"
// ________________________________________________________________________________________________________
// Funcin�: RegistraLog
//
//		Descripcin�:
//			Esta funcin� registra los evento de errores en un fichero log
//		Parametros:
//			- msg : Mensage de error
//			- swerrno: Switch que indica que recupere literal de error del sistema
// ________________________________________________________________________________________________________
void RegistraLog(char *msg,int swerrno)
{
	struct tm * timeinfo;
	timeinfo = TomaHora();

	FLog=fopen(szPathFileLog,"at");
	if(swerrno)
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s:%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg,strerror(errno));
	else
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	fclose(FLog);
}
// ________________________________________________________________________________________________________
// Funcin�: TomaHora
//
//		Descripcin�:
//			Esta funcin� toma la hora actual  del sistema y devuelve una estructura conlos datos
//		Parametros:
//			- msg : Mensage de error
//			- swerrno: Switch que indica que recupere literal de error del sistema
// ________________________________________________________________________________________________________
struct tm * TomaHora()
{
	time_t rawtime;
	time ( &rawtime );
	return(gmtime(&rawtime));
}
//________________________________________________________________________________________________________
//
// Funcin: TomaConfiguracion
//
//		Descripcin:
//		Esta funcin lee el fichero de configuracin del programa hidralinuxcli  y toma los parametros
//		Parametros:
//				- pathfilecfg : Ruta al fichero de configuracin
//________________________________________________________________________________________________________
int TomaConfiguracion(char* pathfilecfg)
{
	long lSize;
	char * buffer,*lineas[100],*dualparametro[2];
	char ch[2];
	int i,numlin,resul;

	if(pathfilecfg==NULL) return(FALSE); // Nombre del fichero en blanco

	Fconfig = fopen ( pathfilecfg , "rb" );
	if (Fconfig==NULL)	return(FALSE);
	fseek (Fconfig , 0 , SEEK_END);  // Obtiene tamaño del fichero.
	lSize = ftell (Fconfig);
	rewind (Fconfig);
	buffer = (char*) malloc (lSize);  // Toma memoria para el buffer de lectura.
	if (buffer == NULL)	 	return(FALSE);
	fread (buffer,1,lSize,Fconfig); 	// Lee contenido del fichero
	fclose(Fconfig);

	//inicializar
	IPlocal[0]=(char)NULL;	
	usuario[0]=(char)NULL;
	pasguor[0]=(char)NULL;
	datasource[0]=(char)NULL;
	catalog[0]=(char)NULL;
	
	strcpy(ch,"\n");// caracter delimitador ( salto de linea)
	numlin=split_parametros(lineas,buffer,ch);
	for (i=0;i<numlin;i++){
		strcpy(ch,"=");// caracter delimitador
		split_parametros(dualparametro,lineas[i],ch); // Toma primer nombre del parametros
	
		resul=strcmp(dualparametro[0],"IPLocal");
		if(resul==0) strcpy(IPlocal,dualparametro[1]);
		
		resul=strcmp(dualparametro[0],"Usuario");
		if(resul==0) strcpy(usuario,dualparametro[1]);

		resul=strcmp(dualparametro[0],"PassWord");
		if(resul==0) strcpy(pasguor,dualparametro[1]);

		resul=strcmp(dualparametro[0],"DataSource");
		if(resul==0) strcpy(datasource,dualparametro[1]);

		resul=strcmp(dualparametro[0],"Catalog");
		if(resul==0) strcpy(catalog,dualparametro[1]);
	}
	if(IPlocal[0]==(char)NULL){
		RegistraLog("IPlocal, NO se ha definido este parmetro",false);
		return(FALSE);
	}
	if(usuario[0]==(char)NULL){
		RegistraLog("Usuario, NO se ha definido este parmetro",false);
		return(FALSE);
	}	
	if(pasguor[0]==(char)NULL){
		RegistraLog("PassWord, NO se ha definido este parmetro",false);
		return(FALSE);
	}	
	if(datasource[0]==(char)NULL){
		RegistraLog("DataSource, NO se ha definido este parmetro",false);
		return(FALSE);
	}	
	if(catalog[0]==(char)NULL){
		RegistraLog("Catalog, NO se ha definido este parmetro",false);
		return(FALSE);
	}	
	return(TRUE);
}
// ________________________________________________________________________________________________________
// Funci�: split_parametros
//
//		Descripci�:
//			Esta funci� trocea una cadena segn un car�ter delimitador, Devuelve el nmero de trozos
// 		Par�etros:
// 			- trozos: Array de punteros a cadenas
// 			- cadena: Cadena a trocear
// 			- ch: Car�ter delimitador
// ________________________________________________________________________________________________________
int split_parametros(char **trozos,char *cadena, char * ch){
	int i=0;
	char* token;

	token= strtok(cadena,ch); // Trocea segn delimitador
	while( token != NULL ){
		trozos[i++]=token;
		token=strtok(NULL,ch); // Siguiente token
	}
	trozos[i++]=token; 
	return(i-1); // Devuelve el numero de trozos
}
// _____________________________________________________________________________________________________________
// Funcin: ServicioDHCP
//
//		Descripcin:
//			Esta hebra despacha el servicio DHCP
// _____________________________________________________________________________________________________________

LPVOID ServicioDHCP(LPVOID ipl)
{
	SOCKET socket_s; // Socket donde escucha el servidor
	struct TramaDhcpBootp * trmInfo;
	struct sockaddr_in local;
	int ret,resul;
	BOOL bOpt;
	pthread_t hThread;
			
	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		RegistraLog("***Error al crear socket para servicio DHCP:",true);
		return(FALSE);
	}
	local.sin_addr.s_addr = htonl(INADDR_ANY); // selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(PUERTODHCPORIGEN); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		RegistraLog("***Error al enlazar socket con interface para servicio DHCP",true);
		return(FALSE);
	}
	
   	bOpt=TRUE; // Pone el socket en modo "soportar Broadcast"
   	ret=setsockopt(socket_s,SOL_SOCKET,SO_BROADCAST,(char *)&bOpt,sizeof(bOpt));
   	if (ret == SOCKET_ERROR){
		RegistraLog("*** setsockopt(SO_BROADCAST) fallo",true);
		return(FALSE);
    }
	while(true){
		trmInfo = (struct  TramaDhcpBootp*)malloc(sizeof(struct  TramaDhcpBootp)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
			RegistraLog("***Fallo al crear estructura de control para protocolo DHCP",false);
			return(FALSE);
        }
		// Inicializa parmetros
		memset(trmInfo,0,sizeof(struct  TramaDhcpBootp));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		trmInfo->sck=socket_s;
		// Espera tramas DHCP
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->pckDchp, sizeof(trmInfo->pckDchp),0,(struct sockaddr *)&trmInfo->cliente, &trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			RegistraLog("***Error al recibir mensaje DHCP. Se para el servicio",true);
			return(FALSE);
		}
		else{
			if (ret>0){
					resul=pthread_create(&hThread,NULL,GestionaServicioDHCP,(LPVOID)trmInfo);
					if(resul!=0){
						RegistraLog("***Fallo al crear la hebra cliente DHCP",false);
		    			return(FALSE);
        			}
        			pthread_detach(hThread);	
			}
		}
	}
	close(socket_s);
}
// _____________________________________________________________________________________________________________
// Funcin: ServicioBOOTP
//
//		Descripcin:
//			Esta hebra despacha el servicio BOOTP
// _____________________________________________________________________________________________________________

LPVOID ServicioBOOTP(LPVOID iplocal)
{
	SOCKET socket_s; // Socket donde escucha el servidor
	struct TramaDhcpBootp * trmInfo;
	struct sockaddr_in local;
	int ret,resul;
	pthread_t hThread;
	
	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		RegistraLog("***Error al crear socket para servicio BOOTP:",true);
		return(FALSE);
	}
	local.sin_addr.s_addr = htonl(INADDR_ANY); // selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(PUERTOBOOTPORIGEN); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		RegistraLog("***Error al enlazar socket con interface para servicio BOOTP",true);
		return(FALSE);
	}
	while(true){
		trmInfo = (struct  TramaDhcpBootp*)malloc(sizeof(struct  TramaDhcpBootp)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
			RegistraLog("***Fallo al crear estructura de control para protocolo TFTP",false);
		    return(FALSE);
        }
		// Inicializa parmetros
		memset(trmInfo,0,sizeof(struct  TramaDhcpBootp));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		trmInfo->sck=socket_s;
		// Espera tramas BOOTP
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->pckDchp, sizeof(trmInfo->pckDchp),0,(struct sockaddr *)&trmInfo->cliente, &trmInfo->sockaddrsize);

		if (ret == SOCKET_ERROR){
			RegistraLog("***Error al recibir mensaje BOOTP. Se para el servicio",true);
			return(FALSE);
		}
		else{
			if (ret>0){
					resul=pthread_create(&hThread,NULL,GestionaServicioBOOTP,(LPVOID)trmInfo);
					if(resul!=0){
						RegistraLog("***Fallo al crear la hebra cliente BOOTP",false);
		    			return(FALSE);
        			}
        			pthread_detach(hThread);					
			}
		}
	}
	close(socket_s);
}
// _____________________________________________________________________________________________________________
// Funcin: ServicioTFTP
//
//		Descripcin:
//			Esta hebra despacha el servicio TFTP
// _____________________________________________________________________________________________________________

LPVOID ServicioTFTP(LPVOID ipl)
{
	SOCKET socket_s; // Socket donde escucha el servidor
	struct TramaTftp * trmInfo;
	struct sockaddr_in local;
	pthread_t hThread;
	int ret,resul;

	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		RegistraLog("***Error al crear socket para servicio TFTP:",true);
        return(FALSE);
	}
	local.sin_addr.s_addr = htonl(INADDR_ANY); // selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(PUERTOTFTPORIGEN); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		RegistraLog("***Error al enlazar socket con interface para servicio TFTP",true);
        return(FALSE);
	}
	while(true){
		trmInfo = (struct  TramaTftp*)malloc(sizeof(struct  TramaTftp)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
			RegistraLog("***Fallo al crear estructura de control para protocolo TFTP",false);
		    return(FALSE);
        }
		memset(trmInfo,0,sizeof(struct  TramaTftp));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		// Espera tramas TFTP
		ret = recvfrom(socket_s,(char *)&trmInfo->pckTftp, sizeof(trmInfo->pckTftp),0,(struct sockaddr *)&trmInfo->cliente,&trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			RegistraLog("***Error al recibir mensaje TFTPP. Se para el servicio",true);
			return(FALSE);
		}
		else{
			if (ret>0){
				resul=pthread_create(&hThread,NULL,GestionaServicioTFTP,(LPVOID)trmInfo);
				if(resul!=0){
					RegistraLog("***Fallo al crear la hebra cliente TFTP",false);
		    		return(FALSE);
        		}
        		pthread_detach(hThread);
			}
		}
		
	}
	close(socket_s);
}
//_______________________________________________________________________________________________________________
//
// Gestiona la conexion con un cliente que sea Hidra para el servicio DHCP
// Parmetros:
//		lpParam: Puntero a la estructura de control para la conversacion DHCP
//_______________________________________________________________________________________________________________
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
// Parmetros:
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
// Comprueba si la IP del cliente est?a en la base de datos de Hidra
// Parmetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//		ipmac: IP o MAC del cliente que ha abierto la hebra
//		sw: Si vale 1 o 2 o 3 el parmetro anterior ser una IP en caso contrario ser una MAC
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
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACCESO atnico A TRAVEZ DE OBJETO MUTEX a este trozo de cnigo 
	pthread_mutex_lock(&guardia); 
	
	// Abre conexion con base de datos
	if(!db.Open(usuario,pasguor,datasource,catalog)){ // error de conexion
		db.GetErrorErrStr(ErrStr);
		return(false);
	}	
	
	if(sw==1 || sw==2){ // Bsqueda por IP
		sprintf(sqlstr,"SELECT ip FROM ordenadores WHERE ip='%s' ",ipmac);
	}
	else{ // Bsqueda por MAC
		sprintf(sqlstr,"SELECT ip FROM ordenadores WHERE mac='%s' ",ipmac);
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
	if(sw==1 || sw==0){ // Slo para las tramas dhcp PXE y BOOTP
		if(!tbl.Get("ip",trmInfo->bdIP)){ // Incorpora la IP a asignar al cliente a la estructura de control
			tbl.GetErrorErrStr(ErrStr); // error al acceder al registro
			db.Close();
			pthread_mutex_unlock(&guardia); 
			return(false);
		}	
	}
	db.Close();
	pthread_mutex_unlock(&guardia); 
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	return(true);
}
//_______________________________________________________________________________________________________________
//
// Comprueba que existen opciones en el mensage dhcp analizando la magic cookie
// Parmetros:
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
// Busca una determinada opcin dentro de la trama dhcp
// Parmetros:
//		dhcp_packet: Puntero a la estructura que contiene el mensaje dhcp
//		codop: Cdigo de la opcin a buscar
//
//	Devuelve:
//		Si existe, un puntero al primer elemento de la estructura de la opcin (codigo,longitud,valor) 
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
			if(wcodop==DHCP_END) // Fin de la cadena de opciones, no se encontr la opcin
				return(NULL);
			else{
				
				if (wcodop == codop) // Encuentra la opcin
					return(ptrOp); // Devuelve el puntero a la estructura variable opcin
				else{
					wlongitud = ptrOp[1]; 
					ptrOp+=wlongitud+2; // Avanza hasta la prxima opcin
				}
			}
		}
	}
	return(NULL);
}
//_______________________________________________________________________________________________________________
//
// Comprueba si el mensaje recibido proviene de un cliente PXE
// Parmetros:
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
// Parmetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//_______________________________________________________________________________________________________________
void ProcesaTramaClientePXE(struct TramaDhcpBootp* trmInfo)
{
	unsigned char codop;
	unsigned char longitud;
	unsigned char *ptrOp;
	unsigned char *msgDhcp;

	ptrOp=BuscaOpcion(&trmInfo->pckDchp,DHCP_MESSAGE_TYPE); // Puntero a la opcin tipo de mensaje
	if(!ptrOp){ // No existe la opcin DHCP
		RegistraLog("***No se encontr opción DHCP:",true);
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
// Parmetros:
//		trmInfo: Puntero a la estructura de control de la conversacin BOOTP
//_______________________________________________________________________________________________________________
void ProcesaTramaClienteBOOTP(struct TramaDhcpBootp* trmInfo)
{
	unsigned char codop;
	unsigned char longitud;
	unsigned char *ptrOp;
	unsigned char *msgDhcp;


	ptrOp=BuscaOpcion(&trmInfo->pckDchp,DHCP_MESSAGE_TYPE); // Puntero a la opcin tipo de mensaje
	if(!ptrOp){ // No existe la opcin DHCP
		RegistraLog("***No se encontr opción DHCP:",true);
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
// Parmetros:
//		lpParam: Puntero a la estructura de control para la conversacion TFTP
//_______________________________________________________________________________________________________________
LPVOID GestionaServicioTFTP(LPVOID lpParam)
{
	struct TramaTftp * trmInfo=(struct TramaTftp *)lpParam;
	char IPCliente[16]; // Ip del cliente

	strcpy(IPCliente,inet_ntoa(trmInfo->cliente.sin_addr));
	if(!ClienteExistente((struct TramaDhcpBootp*)trmInfo,IPCliente,2)) // Comprueba que se trata de un cliente Hidra
		return(false);

	// Inicializa parmetros
	trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
	trmInfo->sck=TomaSocketUser();
	if(trmInfo->sck==INVALID_SOCKET) return(false); // Ha habido algn problama para crear el socket de usuario

	trmInfo->currentopCode=0x0000;	// Cdigo de operacin para detectar errores de secuencia
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
// Parmetros:
//		trmInfo: Puntero a la estructura de control de la conversacin TFTP
//_______________________________________________________________________________________________________________
void ProcesaTramaClienteTFTP(struct TramaTftp* trmInfo)
{
	char *ptr;
	int bloque,lon,ret;
	char tam[20];
	struct tftppacket_ack* ptrack;
	
	while(true){
		switch(ntohs(trmInfo->pckTftp.opcode)){
			case TFTPRRQ: // Read Request
				if(trmInfo->currentopCode!=0x0000) return; // Error en la secuencia de operaciones
				if(!PeticionFichero(trmInfo)) return;
				fseek(trmInfo->fileboot,0,SEEK_SET);
				trmInfo->pckTftp.opcode= htons(TFTPOACK);
				trmInfo->currentopCode=TFTPOACK;
				ptr=&trmInfo->pckTftp.buffer[0];
				if(trmInfo->tsize>0){ // Opcin tsize
					strcpy(ptr,"tsize");
					ptr+=strlen(ptr)+1;
					//itoa(trmInfo->tsize,tam,10);
					sprintf(tam,"%d",trmInfo->tsize);
					strcpy(ptr,tam);
					ptr+=strlen(ptr)+1;
					*ptr=0x00;
				}
				else{
					if(trmInfo->bloquesize>0){ // Opcin blksize
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
					RegistraLog("***sendto() fallo:",true);
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
					RegistraLog("***sendto() fallo:",true);
					return;
				}
				if(lon==0)
					return; // Fin de la trama tftp
				break;					
			case TFTPERROR:
								RegistraLog("***ERROR TFTP:",false);
		}
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->pckTftp, sizeof(trmInfo->pckTftp),0,(struct sockaddr *)&trmInfo->cliente,&trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			RegistraLog("***Error al recibir mensaje TFTP en hebra cliente",true);
			return;
		}
		else
			if(ret==0) break;
	 }
	 return;
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje Tftp de peticin de fichero. Recupera datos de tamao de bloque y otros parmetros
//	para gestionar la conversacin.
// 
//	Parmetros:
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
	
	trmInfo->currentopCode=ntohs(trmInfo->pckTftp.opcode); // Guarda cdigo de operacin
	// Localiza parmetros
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
	ptr+=strlen(ptr)+1; // Paso al parmetro siguiente
	while(*ptr){
		if(strcmp(ptr,"blksize")==0){	// Parmetro blksize
			ptr+=strlen(ptr) + 1;
			trmInfo->bloquesize=atoi(ptr);
			if(trmInfo->bloquesize<512) trmInfo->bloquesize=512; 
			if(trmInfo->bloquesize>MAXBLOCK) trmInfo->bloquesize=512; 
			ptr+=strlen(ptr) + 1;
		}
		else{
			if(strcmp(ptr,"tsize")==0){	// Parmetro tsize
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
//	Parmetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//_______________________________________________________________________________________________________________
void ProcesaTramaClienteDHCP(struct TramaDhcpBootp* trmInfo)
{
	unsigned char codop;
	unsigned char longitud;
	unsigned char *ptrOp;
	unsigned char *msgDhcp;
	int ret;

	while(true){
		ptrOp=BuscaOpcion(&trmInfo->pckDchp,DHCP_MESSAGE_TYPE); // Puntero a la opcin tipo de mensaje
		if(!ptrOp){ // No existe la opcin DHCP
			RegistraLog("***No se encontr opción DHCP:",true);
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
			RegistraLog("***Error al recibir mensaje DHCP",true);
		}
		else
			if(ret==0) break;
	 }
}
//_______________________________________________________________________________________________________________
//
// Rellena el campo IP asignada(yiaddr) al cliente dentro del mensaje DHCP
//
//	Parmetros:
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
//	Parmetros:
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
//	Parmetros:
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
//	Parmetros:
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
//	Parmetros:
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

	ptrDhcp=(unsigned char*)&trmInfo->pckDchp; // Se toma el puntero al principio del mensage
	ptrOp=(unsigned char*)&trmInfo->pckDchp.options[0]; // Se toma el puntero a las opciones
	lon=ptrOp-ptrDhcp; //Longitud del mensaje sin las opciones ni la magic coockie

	RellenaIPCLiente(trmInfo);
	RellenaIPServidor(trmInfo);
	*ptrOp='\0'; //Borra opciones del mensaje recibido para colocar las nuevas
	

	AdjDHCPOFFER(&ptrOp,&lon);			// Genera opcin de Mensaje (0x35) dhcp valor 1
	AdjSERVERIDENTIFIER(&ptrOp,&lon);	// Genera opcin de Mensaje (0x36) Dhcp
	AdjLEASETIME(&ptrOp,&lon);			// Genera opcin de Mensaje (0x33) Dhcp
	AdjSUBNETMASK(&ptrOp,&lon);			// Genera opcin de Mensaje (0x01) Dhcp
	AdjROUTERS(&ptrOp,&lon);			// Genera opcin de Mensaje (0x03) Dhcp
	AdjCLASSIDENTIFIER(&ptrOp,&lon);	// Genera opcin de Mensaje (0x3c) Dhcp
	*ptrOp=DHCP_END;
	lon++;
	trmInfo->pckDchp.op=DHCPOFFER;
	//MandaRespuesta(&trmInfo->pckDchp,htonl(INADDR_BROADCAST),lon,htons(PUERTODHCPDESTINO));
	trmInfo->cliente.sin_addr.s_addr=htonl(INADDR_BROADCAST);
	ret=sendto(trmInfo->sck,(char*)&trmInfo->pckDchp,lon,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	if (ret == SOCKET_ERROR){
		RegistraLog("***sendto() fallo:",true);
	}
}

//_______________________________________________________________________________________________________________
//
// Procesa un mensaje DHCPREQUEST (DHCP)
// 
//	Parmetros:
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

	ptrDhcp=(unsigned char*)&trmInfo->pckDchp; // Se toma el puntero al principio del mensage
	ptrOp=(unsigned char*)&trmInfo->pckDchp.options[0]; // Se toma el puntero a las opciones
	lon=ptrOp-ptrDhcp; //Longitud del mensaje sin las opciones ni la magic coockie

	RellenaIPCLiente(trmInfo);
	RellenaIPServidor(trmInfo);
	*ptrOp='\0'; //Borra opciones del mensaje recibido para colocar las nuevas

	AdjDHCPACK(&ptrOp,&lon);			// Ge		db.Close();nera opcin de Mensaje (0x35) dhcp valor 5
	AdjSERVERIDENTIFIER(&ptrOp,&lon);	// Genera opcin de Mensaje (0x36) Dhcp
	AdjLEASETIME(&ptrOp,&lon);			// Genera opcin de Mensaje (0x33) Dhcp
	AdjSUBNETMASK(&ptrOp,&lon);			// Genera opcin de Mensaje (0x01) Dhcp
	AdjROUTERS(&ptrOp,&lon);			// Genera opcin de Mensaje (0x03) Dhcp
	AdjCLASSIDENTIFIER(&ptrOp,&lon);	// Genera opcin de Mensaje (0x3c) Dhcp

	*ptrOp=DHCP_END;
	lon++;

	trmInfo->pckDchp.op=DHCPOFFER;
	//MandaRespuesta(&trmInfo->pckDchp,htonl(INADDR_BROADCAST),lon,htons(PUERTODHCPDESTINO));
	trmInfo->cliente.sin_addr.s_addr=htonl(INADDR_BROADCAST);
	ret=sendto(trmInfo->sck,(char*)&trmInfo->pckDchp,lon,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	if (ret == SOCKET_ERROR){
		RegistraLog("***sendto() fallo:",true);
	}
}
//_______________________________________________________________________________________________________________
//
// Procesa un mensaje DHCPREQUEST (BOOTP)
// 
//	Parmetros:
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

	ptrDhcp=(unsigned char*)&trmInfo->pckDchp; // Se toma el puntero al principio del mensage
	ptrOp=(unsigned char*)&trmInfo->pckDchp.options[0]; // Se toma el puntero a las opciones
	lon=ptrOp-ptrDhcp; //Longitud del mensaje sin las opciones ni la magic coockie

	aux=inet_addr("0.0.0.0"); // Borra Ip del cliente ( No se porqu pero en la trama aparece as)
	memcpy(&trmInfo->pckDchp.ciaddr,&aux,4);
	RellenaNombreServidorBoot(trmInfo);
	RellenaNombreFicheroBoot(trmInfo);

	*ptrOp='\0'; //Borra opciones del mensaje recibido para colocar las nuevas

	AdjDHCPACK(&ptrOp,&lon);			// Genera opcin de Mensaje (0x35) dhcp valor 5
	AdjSERVERIDENTIFIER(&ptrOp,&lon);	// Genera opcin de Mensaje (0x36) Dhcp
	AdjBOOTSIZE(&ptrOp,&lon);			// Genera opcin de Mensaje (0x0D) Dhcp
	AdjCLASSIDENTIFIER(&ptrOp,&lon);	// Genera opcin de Mensaje (0x3c) Dhcp

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
		RegistraLog("***sendto() fallo:",true);
		return;
	}
}
//_______________________________________________________________________________________________________________
//
// Genera una opcin del tipo 0x35(53) con el valor "Dhcp Offer" valor 2
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
// Genera una opcin del tipo 0x35(53) con el valor "Dhcp Ack" valor 5
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
// Genera una opcin del tipo 0x03(3) con la IP del router
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de Ip del Routers
//_______________________________________________________________________________________________________________
void AdjROUTERS(unsigned char** ptrOp,int*lon)
{
	unsigned long aux;
	aux=inet_addr("10.1.12.1"); // Router

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
// Genera una opcin del tipo 0x01(1) con la mascara de red
// 
//	Devuelve:
//		Una estructura de opciones de dhcp(codigo,longitud,dato) con la opciones de mscara de red
//_______________________________________________________________________________________________________________
void AdjSUBNETMASK(unsigned char** ptrOp,int*lon)
{
	unsigned long aux;
	aux=inet_addr("255.255.252.0"); // Mascara de red

	**ptrOp=DHCP_SUBNET_MASK;
	*ptrOp+=1;
	**ptrOp=4;
	*ptrOp+=1;
	memcpy(*ptrOp,&aux,4); // Copia la mscara de red
	*ptrOp+=4;
	*lon+=6;
}
//_______________________________________________________________________________________________________________
//
// Genera una opcin del tipo 0x3c(60) con el literal "PXECLient" para clientes PXE
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
// Genera una opcin del tipo 0x36(54) con la IP del servidor 
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
// Genera una opcin del tipo 0x33(51) con el tiempo de "lease" de la IP 
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
// Genera una opcin del tipo 0x0D(13) con el tiempo tamao del fichero boot 
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
	BOOL bOpt;

	socket_c = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP

	if (socket_c == SOCKET_ERROR){
		RegistraLog("***Error al crear socket de usuario para hebra:",true);
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
		RegistraLog("*** No hay puertos libres para la hebra del servicio",true);
		return(INVALID_SOCKET);
	}

   	bOpt=TRUE; // Pone el socket en modo "soportar Broadcast"
   	ret=setsockopt(socket_c,SOL_SOCKET,SO_BROADCAST,(char *)&bOpt,sizeof(bOpt));
   	if (ret == SOCKET_ERROR){
		RegistraLog("*** setsockopt(SO_BROADCAST) fallo",true);
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
	int i,resul;
	
	
	for(i = 1; i < argc; i++){
        if (argv[i][0] == '-'){
            switch (tolower(argv[i][1])){
                case 'f':
                    if (argv[i+1]!=NULL)
                        strcpy(szPathFileCfg, argv[i+1]);
					else{
						RegistraLog("Fallo en los parmetros: Debe especificar el fichero de configuracin del servicio",false);
						exit(EXIT_FAILURE);
					}
                    break;
                case 'l':
                    if (argv[i+1]!=NULL)
                        strcpy(szPathFileLog, argv[i+1]);
					else{
						RegistraLog("Fallo en los parmetros: Debe especificar el fichero de log para el servicio",false);
						exit(EXIT_FAILURE);
					}
                    break;
                default:
                    	RegistraLog("Fallo de sintaxis en los parmetros: Debe especificar -f nombre_del_fichero_de_configuracin_del_servicio",false);
						exit(EXIT_FAILURE);
                    break;
            }
        }
    }
	if(!TomaConfiguracion(szPathFileCfg)){ // Toma parametros de configuracion
			RegistraLog("NO existe fichero de configuracin o contiene un error de sintaxis",false);
			exit(EXIT_FAILURE);
	}
	
	RegistraLog("***Inicio de sesion***",false);
	
	// Hebra servicio DHCP ---------------------------------
	resul=pthread_create(&hThreadDHCP,NULL,ServicioDHCP,(LPVOID)IPlocal);
	if(resul!=0){
		RegistraLog("***Fallo al crear la hebra DHCP",false);
		exit(EXIT_FAILURE);
    }
    pthread_detach(hThreadDHCP);
    
  
	// Hebra servicio BOOTP  ---------------------------------
  	resul=pthread_create(&hThreadBOOTP,NULL,ServicioBOOTP,(LPVOID)IPlocal);
	if(resul!=0){
		RegistraLog("***Fallo al crear la hebra BOOTP",false); 
		exit(EXIT_FAILURE);
    }
    pthread_detach(hThreadBOOTP);
    
	// Hebra servicio TFTP  ----------------------------------
   	resul=pthread_create(&hThreadTFTP,NULL,ServicioTFTP,(LPVOID)IPlocal);
	if(resul!=0){
		RegistraLog("***Fallo al crear la hebra TFTP",false);
		exit(EXIT_FAILURE);
    }
    pthread_detach(hThreadTFTP);
    
    while (true)
    	sleep(1000);
    	
}
