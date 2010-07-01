//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmRepo.cpp
//	Descripción:
//		Este módulo de la aplicación OpenGNSys implementa las comunicaciones con el Repositorio.
// ****************************************************************************************************************************************************
#include "ogAdmRepo.h"
#include "ogAdmLib.c"
//________________________________________________________________________________________________________
// Función: TomaConfiguracion
//
//		Descripción:
//			Esta función lee el fichero de configuracinn del programa
//		Parámetros:
//			- pathfilecfg : Ruta al fichero de configuración
//________________________________________________________________________________________________________
int TomaConfiguracion(char* pathfilecfg)
{
	long lSize;
	char * buffer,*lineas[100],*dualparametro[2];
	char ch[2];
	int i,numlin,resul;

	if(pathfilecfg==NULL)
		exit(EXIT_FAILURE);; // Nombre del fichero en blanco

	Fconfig = fopen ( pathfilecfg , "rb" );
	if (Fconfig==NULL)
		return(FALSE);
	fseek (Fconfig , 0 , SEEK_END);  // Obtiene tamaño del fichero.
	lSize = ftell (Fconfig);
	rewind (Fconfig);
	buffer = (char*) malloc (lSize);  // Toma memoria para el buffer de lectura.
	if (buffer == NULL)
		exit(EXIT_FAILURE);;
	fread (buffer,1,lSize,Fconfig); 	// Lee contenido del fichero
	fclose(Fconfig);

	//inicializar
	IPlocal[0]=(char)NULL;
	servidorhidra[0]=(char)NULL;
	Puerto[0]=(char)NULL;
	
	strcpy(ch,"\n");// carácter delimitador ( salto de línea)
	numlin=split_parametros(lineas,buffer,ch);
	for (i=0;i<numlin;i++){
		strcpy(ch,"=");// carácter delimitador
		split_parametros(dualparametro,lineas[i],ch); // Toma primer nombre del parámetro

		resul=strcmp(dualparametro[0],"IPlocal");
		if(resul==0) strcpy(IPlocal,dualparametro[1]);

		resul=strcmp(dualparametro[0],"IPhidra");
		if(resul==0) strcpy(servidorhidra,dualparametro[1]);

		resul=strcmp(dualparametro[0],"Puerto");
		if(resul==0) strcpy(Puerto,dualparametro[1]);
		
		resul=strcmp(dualparametro[0],"RepoScripts");
		if(resul==0) strcpy(reposcripts,dualparametro[1]);
	}
	if(IPlocal[0]==(char)NULL){
		RegistraLog("IPlocal, NO se ha definido este parámetro",false);
		exit(EXIT_FAILURE);;
	}
	if(servidorhidra[0]==(char)NULL){
		RegistraLog("IPhidra, NO se ha definido este parámetro",false);
		exit(EXIT_FAILURE);;
	}
	if(Puerto[0]==(char)NULL){
		RegistraLog("Puerto, NO se ha definido este parámetro",false);
		exit(EXIT_FAILURE);;
	}
	puerto=atoi(Puerto);

	if(reposcripts[0]==(char)NULL){
		RegistraLog("RepoScripts, NO se ha definido este parámetro",false);
		exit(EXIT_FAILURE);;
	}
	return(TRUE);
}
//_______________________________________________________________________________________________________________
// Función: ClienteExistente
//
//	Descripción:
// 		Comprueba si la IP del cliente est?a en la base de datos de Hidra
// 	parámetros:
//		trmInfo: Puntero a la estructura de control de la conversacin DHCP
//	Devuelve:
//		true: Si el cliente est en la base de datos
//		false: En caso contrario
// 
//  Comentarios:
//		Sólo se procesarn mensajes dhcp de clientes hidra.
//_______________________________________________________________________________________________________________
int ClienteExistente(TramaRepos *trmInfo)
{
	char sqlstr[1000],ErrStr[200];	
	Database db;
	Table tbl;

	/////////////////////////////////////////////////////////////////
	// ACCESO único A TRAVES DE OBJETO MUTEX a este trozo de código
	pthread_mutex_lock(&guardia); 
	
	if(strcmp(servidorhidra,inet_ntoa(trmInfo->cliente.sin_addr))==0){ // Se trata del servidor hidra
		pthread_mutex_unlock(&guardia); 
		return(true);
	}

	// Abre conexion con base de datos
	if(!db.Open(usuario,pasguor,datasource,catalog)){ // error de conexion
		db.GetErrorErrStr(ErrStr);
		pthread_mutex_unlock(&guardia); 
		return(false);
	}	
	
	sprintf(sqlstr,"SELECT ip FROM ordenadores WHERE ip='%s' ",inet_ntoa(trmInfo->cliente.sin_addr));
	if(!db.Execute(sqlstr,tbl)){ // Error al leer
		db.GetErrorErrStr(ErrStr);
		pthread_mutex_unlock(&guardia); 
		db.Close();
		return(false);
	}

	if(tbl.ISEOF()){ // No existe el cliente
		db.Close();
		pthread_mutex_unlock(&guardia); 
		return(false);
	}
	db.Close();
	pthread_mutex_unlock(&guardia); 
	//////////////////////////////////////////////////
	return(true);
}
//___________________________________________________________________________________________________
// Función: inclusion_REPO
//
//		Parámetros:
//			 Ninguno
//		Descripción:
//			 Abre una sesión en el servidor Hidra
//___________________________________________________________________________________________________
int inclusion_REPO()
{ 
	TRAMA *trama;
	SOCKET sock;
	// Compone la trama
	int lon;		

	trama=(TRAMA*)malloc(LONGITUD_TRAMA);
	if(!trama)
		return(false);
	lon=sprintf(trama->parametros,"nfn=inclusion_REPO\r");	// Nombre de la funcin a ejecutar en el servidor HIDRA 
	lon+=sprintf(trama->parametros+lon,"iph=%s\r",IPlocal);	// Ip del ordenador

	sock=AbreConexion(servidorhidra,puerto);
	if(sock==INVALID_SOCKET) {
		sprintf(msglog,"Error al crear socket del Repositorio");
		RegistraLog(msglog,false);
		return(false);
	}
	envia_tramas(sock,trama);
	recibe_tramas(sock,trama);
	close(sock);
	if(!RESPUESTA_inclusionREPO(trama)){
		return(false);
	}
	return(true);
}

//___________________________________________________________________________________________________
// Función: envia_tramas
//
//		Descripción:
//  		Envía tramas al servidor HIDRA
//		Parámetros:
//			s: Socket de la conexión
//			trama: Trama a enviar
//___________________________________________________________________________________________________
int envia_tramas(SOCKET s,TRAMA *trama)
{
	trama->arroba='@';							// cabecera de la trama
	strcpy(trama->identificador,"JMMLCAMDJ");	// identificador de la trama
	trama->ejecutor='1';						// ejecutor de la trama 1=el servidor hidra  2=el cliente hidra

	int nLeft,idx,ret;
	Encriptar((char*)trama);
	nLeft = strlen((char*)trama);
	idx = 0;
	while(nLeft > 0){
		ret = send(s,(char*)&trama[idx], nLeft, 0);
		if (ret == 0)
			break;
		else
			if (ret == SOCKET_ERROR){
				return(false);
			}
		nLeft -= ret;
		idx += ret;
	}
	return(true);
}

//_______________________________________________________________________________________________________________
//
// Función: GestionaServicioRepositorio
//
//		Descripción:
// 			Gestiona la conexion con un cliente que sea Hidra para el servicio de repositorio
//		Parámetros:
//			lpParam: Puntero a una estructura del tipo TramaRepos
//_______________________________________________________________________________________________________________
LPVOID GestionaServicioRepositorio(LPVOID lpParam)
{
	TramaRepos *trmInfo=(TramaRepos *)lpParam;
	
	Desencriptar((char*)&trmInfo->trama);
	if (strncmp(trmInfo->trama.identificador,"JMMLCAMDJ",9)==0){ // Es una trmInfo hidra
		//if(ClienteExistente(trmInfo)) // Comprueba que se trata de un cliente Hidra
			gestiona_comando(trmInfo);
	}
	free(trmInfo);
	return(trmInfo);
}
//_______________________________________________________________________________________________________________
//
// Función: NwGestionaServicioRepositorio
//
//		Descripción:
// 			Gestiona la conexion con un cliente que sea Hidra para el servicio de repositorio
//		Parámetros:
//			trmInfo: Puntero a una estructura del tipo TramaRepos
//_______________________________________________________________________________________________________________
void NwGestionaServicioRepositorio(TramaRepos * trmInfo)
{
	Desencriptar((char*)&trmInfo->trama);
	if (strncmp(trmInfo->trama.identificador,"JMMLCAMDJ",9)==0){ // Es una trmInfo hidra
		//if(ClienteExistente(trmInfo)) // Comprueba que se trata de un cliente Hidra
			gestiona_comando(trmInfo);
	}
	free(trmInfo);
}
//_______________________________________________________________________________________________________________
//
// Función: gestiona_comando
//
//		Descripción:
// 			Gestiona la conexion con un cliente que sea Hidra para el servicio de repositorio
//		Parámetros:
//			trmInfo: Puntero a una estructura del tipo TramaRepos
//_______________________________________________________________________________________________________________
int gestiona_comando(TramaRepos *trmInfo)
{
	char* nombrefuncion;
	int resul;

	INTROaFINCAD(trmInfo->trama.parametros);
	nombrefuncion=toma_parametro("nfn=",trmInfo->trama.parametros); // Toma nombre funcin
	
	
	resul=strcmp(nombrefuncion,"Arrancar");
	if(resul==0)
		return(Arrancar(trmInfo));
		
	resul=strcmp(nombrefuncion,"Apagar");
	if(resul==0)
		return(RegistraComando(trmInfo));	
			
	resul=strcmp(nombrefuncion,"Reiniciar");
	if(resul==0)
		return(RegistraComando(trmInfo));		
	
	resul=strcmp(nombrefuncion,"IniciarSesion");
	if(resul==0)
		return(RegistraComando(trmInfo));	
			
	resul=strcmp(nombrefuncion,"FicheroOperador");
	if(resul==0)
		return(FicheroOperador(trmInfo));
		
	resul=strcmp(nombrefuncion,"Actualizar");
	if(resul==0){
		return(RegistraComando(trmInfo));
	}

	resul=strcmp(nombrefuncion,"ConsolaRemota");
	if(resul==0){
		return(RegistraComando(trmInfo));
	}

	resul=strcmp(nombrefuncion,"IconoItem");
	if(resul==0)
		return(IconoItem(trmInfo));		
		
	resul=strcmp(nombrefuncion,"ExisteFichero");
	if(resul==0)
		return(ExisteFichero(trmInfo));
	
	resul=strcmp(nombrefuncion,"EliminaFichero");
	if(resul==0)
		return(EliminaFichero(trmInfo));

	resul=strcmp(nombrefuncion,"LeeFicheroTexto");
	if(resul==0)
		return(LeeFicheroTexto(trmInfo));

	resul=strcmp(nombrefuncion,"mandaFichero");
	if(resul==0)
		return(mandaFichero(trmInfo));

	resul=strcmp(nombrefuncion,"sesionMulticast");
	if(resul==0)
		return(sesionMulticast(trmInfo));

	resul=strcmp(nombrefuncion,"ExecShell");
	if(resul==0)
		return(RegistraComando(trmInfo));	

	resul=strcmp(nombrefuncion,"TomaConfiguracion");
	if(resul==0)
		return(RegistraComando(trmInfo));	
		
	resul=strcmp(nombrefuncion,"InventarioHardware");
	if(resul==0)
		return(RegistraComando(trmInfo));					
			
	resul=strcmp(nombrefuncion,"InventarioSoftware");
	if(resul==0)
		return(RegistraComando(trmInfo));		

	resul=strcmp(nombrefuncion,"RestaurarImagen");
	if(resul==0)
		return(RegistraComando(trmInfo));		
					
	resul=strcmp(nombrefuncion,"CrearPerfilSoftware");
	if(resul==0)
		return(RegistraComando(trmInfo));	
		
	resul=strcmp(nombrefuncion,"ParticionaryFormatear");
	if(resul==0)
		return(RegistraComando(trmInfo));
			
	return(false);	
}
//_____________________________________________________________________________________________________________
// Función: RegistraComando
//
//	 Descripción:
//		Crea un fichero de comando para cada cliente hidra
//	 Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_____________________________________________________________________________________________________________
int RegistraComando(TramaRepos *trmInfo)
{
	char* ipes[MAXIMOS_CLIENTES];
	char ch[2];
	int i,numipes,lon;
	char nomfilecmd[1024];
	FILE *Fcomandos;
	
	char *iph=toma_parametro("iph",trmInfo->trama.parametros); // Toma nombre funcin
	if(!iph) return(false);
	strcpy(ch,";");// caracter delimitador
	numipes=split_parametros(ipes,iph,ch);
	
	FINCADaINTRO(trmInfo->trama.parametros,iph);
	*(iph-4)=(char)NULL;
	lon=strlen((char*)&trmInfo->trama);	
	
	//sprintf(msglog,"Registra comandos %s",(char*)&trmInfo->trama);
	//RegistraLog(msglog,false);
	
	for(i=0;i<numipes;i++){
		strcpy(nomfilecmd,PathComandos);
		strcat(nomfilecmd,"/CMD_");
		strcat(nomfilecmd,ipes[i]);
		//sprintf(msglog,"Crea fichero de comandos %s",nomfilecmd);
		//RegistraLog(msglog,false);
		
		Fcomandos=fopen( nomfilecmd,"w");
		if(!Fcomandos) return(false);
		//sprintf(msglog,"Fichero creado %s",nomfilecmd);
		//RegistraLog(msglog,false);
		
		fwrite((char*)&trmInfo->trama,lon,1,Fcomandos);
		fclose(Fcomandos);
	}
	return(true);
}
//_____________________________________________________________________________________________________________
// Función: Arrancar
//
//	 Descripcinn:
//		Esta función enciende un ordenador
//	 Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_____________________________________________________________________________________________________________
int Arrancar(TramaRepos *trmInfo)
{
	int i,nummacs;
	char* macs[MAXIMOS_CLIENTES];
	char ch[2]; // Carácter delimitador

	char *mac=toma_parametro("mac",trmInfo->trama.parametros); // Toma Mac
	strcpy(ch,";");// caracter delimitador
	nummacs=split_parametros(macs,mac,ch);
	for(i=0;i<nummacs;i++){
		levanta(macs[i]);
	}
	return(RegistraComando(trmInfo));
}
//_____________________________________________________________________________________________________________
// Función: levanta
//
// Descripcion:
//    Enciende el ordenador cuya MAC se pasa como parámetro
//	Parámetros de entrada:
//		- mac: La mac del ordenador
//_____________________________________________________________________________________________________________
int levanta(char * mac)
{
	BOOLEAN          bOpt;
	SOCKET		  s;
    sockaddr_in   local;
	int	ret;

	int puertowakeup=PUERTO_WAKEUP;
	s = socket(AF_INET, SOCK_DGRAM, 0); // Crea socket
   	if (s == INVALID_SOCKET) {
		RegistraLog("Fallo en creacin de socket, mndulo levanta",true);
		return(FALSE);
   	}
   	bOpt = TRUE; 	// Pone el socket en modo Broadcast
   	ret = setsockopt(s, SOL_SOCKET, SO_BROADCAST, (char *)&bOpt,sizeof(bOpt));
   	if (ret == SOCKET_ERROR){
		RegistraLog("Fallo en funcinn setsockopt(SO_BROADCAST), mndulo levanta",true);
		return(FALSE);
    }
    local.sin_family = AF_INET;
    local.sin_port = htons((short)puertowakeup);
	local.sin_addr.s_addr = htonl(INADDR_ANY); // cualquier interface
    if (bind(s, (sockaddr *)&local, sizeof(local)) == SOCKET_ERROR){
		RegistraLog("Fallo en funcinn bind(), mndulo levanta",true);
		return(FALSE);
    }
	Wake_Up(s,mac);
	close(s);
	return(TRUE);
}
//_____________________________________________________________________________________________________________
// Función: Wake_Up
//
//	 Descripcion:
//		Enciende el ordenador cuya MAC se pasa como parámetro
//	Parámetros:
//		- s : Socket para enviar trama en modo broadcast o a la ip del ordenador en cuestin
//		- mac : Cadena con el contenido de la mac
//_____________________________________________________________________________________________________________
int Wake_Up(SOCKET s,char * mac)
{
	int i,ret;
	char HDaddress_bin[6];
	struct {
		BYTE secuencia_FF[6];
		char macbin[16][6];
	}Trama_WakeUp;
    sockaddr_in    WakeUpCliente;

	int puertowakeup=PUERTO_WAKEUP;
	for (i=0;i<6;i++) 	// Primera secuencia de la trama Wake Up (0xFFFFFFFFFFFF)
		Trama_WakeUp.secuencia_FF[i] = 0xFF;
	PasaHexBin( mac,HDaddress_bin); // Pasa a binario la MAC
	for (i=0;i<16;i++) // Segunda secuencia de la trama Wake Up , repetir 16 veces su la MAC
		memcpy( &Trama_WakeUp.macbin[i][0], &HDaddress_bin, 6 );
	WakeUpCliente.sin_family = AF_INET;
    WakeUpCliente.sin_port = htons((short)puertowakeup);
    WakeUpCliente.sin_addr.s_addr = htonl(INADDR_BROADCAST); //  Para hacerlo con broadcast
	ret = sendto(s,(char *)&Trama_WakeUp, sizeof(Trama_WakeUp), 0,(sockaddr *)&WakeUpCliente, sizeof(WakeUpCliente));
    if (ret == SOCKET_ERROR){
		RegistraLog("Fallo en funcinn send(), mndulo Wake_Up",true);
		return(FALSE);
    	}
	return 0;
}
//_____________________________________________________________________________________________________________
// Funcinn: PasaHexBin
//
//		Descripcion:
//			Convierte a binario una direccinn mac desde una cadena de longitud 12
//
//		Parámetros:
//			- cadena : Cadena con el contenido de la mac
//			- numero : la dirección mac convertida a binario (6 bytes) (salida)
//_____________________________________________________________________________________________________________
void PasaHexBin( char *cadena,char *numero)
{
	int i,j,p;	
	char matrizHex[]="0123456789ABCDEF";
	char Ucadena[12], aux;

	for (i=0;i<12;i++)
		Ucadena[i]=toupper(cadena[i]);
	p=0;
	for (i=0;i<12;i++){
		for (j=0;j<16;j++){
			if (Ucadena[i]==matrizHex[j]){
				if (i%2){
					aux=numero[p];
					aux=(aux << 4);
					numero[p]=j;
					numero[p]=numero[p] | aux;
					p++;
				}
				else
					numero[p]=j;
				break;
			}
		}
	}
}
//_____________________________________________________________________________________________________________
// Función: FicheroOperador
//
//	 Descripción:
//		Crea un fichero para que un operador de aula o administrador de centro pueda entrar en el menú privado de los clientes rembo
//	Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_____________________________________________________________________________________________________________
int FicheroOperador(TramaRepos *trmInfo)
{
	FILE *FLogin;
	char *amb,*usu,*psw,*ida;
	char nomfilelogin[250];
	char nomcmd[512];
	int op,resul,ext;

	amb=toma_parametro("amb",trmInfo->trama.parametros); // Toma operacion: Alta,o Baja
	usu=toma_parametro("usu",trmInfo->trama.parametros); // Toma nombre del fichero de login de operador
	psw=toma_parametro("psw",trmInfo->trama.parametros); // Toma login del fichero de login de operador
	ida=toma_parametro("ida",trmInfo->trama.parametros); // Toma identificador del aula
	strcpy(nomfilelogin,PathUsuarios);
	strcat(nomfilelogin,usu);
	ext=atoi(ida);
	if(ext>0){
		strcat(nomfilelogin,"-");
		strcat(nomfilelogin,ida);
	}
	op=atoi(amb);
	switch(op){
		case 1:
			FLogin=fopen( nomfilelogin,"w");
			if(FLogin==NULL)
				RegistraLog("PathComandos, NO existe el Path para el fichero de login de operador ",false);
			Encriptar(psw);
			fprintf (FLogin,"%s",psw);
			fclose(FLogin);
			break;
		case 3:
			strcpy(nomcmd,"rm -f ");
			strcat(nomcmd,nomfilelogin);
			resul=system(nomcmd);
			break;
	}
	return(true);
}
//_____________________________________________________________________________________________________________
// Función: IconoItem
//
//	 Descripción:
//		Crea un fichero para que un operador de aula o administrador de centro pueda entrar en el menú privado de los clientes rembo
//	Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_____________________________________________________________________________________________________________
int IconoItem(TramaRepos *trmInfo)
{
	FILE *FIcono;
	char *nii,*amb,*lii,*iit;
	int lon,op,resul;
	char nomfileicono[250];
	char nomcmd[260];
	
	nii=toma_parametro("nii",trmInfo->trama.parametros); // Toma el nombre del fichero
	amb=toma_parametro("amb",trmInfo->trama.parametros); // Toma operacion: Alta,o Baja
	lii=toma_parametro("lii",trmInfo->trama.parametros); // Toma longitud del fichero de icono
	iit=toma_parametro("iit",trmInfo->trama.parametros); // Toma contenido del fichero de icono
	lon=atoi(lii);
	op=atoi(amb);
	strcpy(nomfileicono,PathIconos);
	strcat(nomfileicono,nii);
	switch(op){
		case 1:
			FIcono=fopen( nomfileicono,"w");
			fwrite (iit,lon,1,FIcono);
			fclose(FIcono);
			break;
		case 3:
			strcpy(nomcmd,"rm -f ");
			strcat(nomcmd,nomfileicono);
			resul=system(nomcmd);
			break;
	}
	return(true);
}
//_______________________________________________________________________________________________________________
//
// Función: ExisteFichero
//
//	 Descripción:
// 		Comprueba si existe un fichero
//	 Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_______________________________________________________________________________________________________________
BOOLEAN ExisteFichero(TramaRepos *trmInfo)
{
	FILE *f;
	char swf[2];
	char pathfile[250];
			
	char *nomfile=toma_parametro("nfl",trmInfo->trama.parametros); // Toma nombre funcin
	sprintf(pathfile,"%s%s",PathHidra,nomfile);
	
	f = fopen(pathfile,"rt");
	if(f==NULL)
		strcpy(swf,"0");
	else
		strcpy(swf,"1");
	if(f) fclose(f);
	return(respuesta_peticion(trmInfo,"Respuesta_ExisteFichero",swf,nomfile));
}
//_______________________________________________________________________________________________________________
// Función: respuesta_clienteHidra
//
//	 Descripción:
// 		Envia respuesta a petición de comando
//	 Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_______________________________________________________________________________________________________________
BOOLEAN respuesta_clienteHidra(TramaRepos *trmInfo)
{
	int ret;
	//MandaRespuesta
	Encriptar((char*)&trmInfo->trama);
	ret=sendto(trmInfo->sck,(char*)&trmInfo->trama,strlen(trmInfo->trama.parametros)+11,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	if (ret == SOCKET_ERROR){
		RegistraLog("sendto() fallo al enviar respuesta modulo respuesta_clienteHidra() :",true);
		return(false);
	}
	return(true);	
}
//_______________________________________________________________________________________________________________
// Función: respuesta_peticion
//
//	 Descripción:
// 		Envia respuesta a petición de comando
//	 Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//		LitRes: Nombre de la función a ejecutar en el cliente en respuesta a una petición
//		swf: Respuesta de la petición
//		txt: Nombre del fichero implicado en la petición
//_______________________________________________________________________________________________________________
BOOLEAN respuesta_peticion(TramaRepos *trmInfo,const char *LitRes,char* swf,char*txt)
{
	int lon,ret;
	TRAMA *trama=(TRAMA*)malloc(LONGITUD_TRAMA);
	if(!trama){
		RegistraLog("No hay memoria suficiente para enviar la respuesta al comando",false);
		return(false);
	}
	trama->arroba='@';
	strncpy(trama->identificador,"JMMLCAMDJ",9);
	trama->ejecutor='1';
	lon=sprintf(trama->parametros,"nfn=%s\r",LitRes);
	lon+=sprintf(trama->parametros+lon,"res=%s\r",swf);		
	lon+=sprintf(trama->parametros+lon,"txt=%s\r",txt);		
	//MandaRespuesta
	Encriptar((char*)trama);
	ret=sendto(trmInfo->sck,(char*)trama,lon+11,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	if (ret == SOCKET_ERROR){
		RegistraLog("sendto() fallo al enviar respuesta a peticin de comando:",true);
		return(false);
	}
	return(true);	
}
//_______________________________________________________________________________________________________________
//
// Función: EliminaFichero
//
//	 Descripción:
// 		Comprueba si existe un fichero
//	 Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_______________________________________________________________________________________________________________
BOOLEAN EliminaFichero(TramaRepos *trmInfo)
{
	char swf[2];
	char cmdshell[512];
	int res;
	char pathfile[250];
	
	char *nomfile=toma_parametro("nfl",trmInfo->trama.parametros); // Toma nombre funcin
	//sprintf(pathfile,"%s%s",PathHidra,nomfile);
	sprintf(cmdshell,"rm -f %s",nomfile);
	res=system(cmdshell);
	if(res==0)
		strcpy(swf,"1");
	else
		strcpy(swf,"0");
	return(respuesta_peticion(trmInfo,"Respuesta_EliminaFichero",swf,nomfile));
}
//_______________________________________________________________________________________________________________
// Función: LeeFicheroTexto
//
//	 Descripción:
// 		Comprueba si existe un fichero
//	 Parámetros:
//		trmInfo: Puntero a una estructura del tipo TramaRepos
//_______________________________________________________________________________________________________________
BOOLEAN LeeFicheroTexto(TramaRepos *trmInfo)
{
	char *texto;
	long lSize;
	FILE *f;	
	char pathfile[250];
	char swf[2];
	
	char *nomfile=toma_parametro("nfl",trmInfo->trama.parametros); // Toma nombre funcin
	sprintf(pathfile,"%s%s",PathHidra,nomfile);
	
	f = fopen(pathfile,"rt");
	if(!f){ // El fichero no existe
		texto=(char*)malloc(2);
		strcpy(texto," ");
		strcpy(swf,"0");
	}
	else{
		fseek(f,0,SEEK_END);
		lSize=ftell(f);
		texto=(char*)malloc(lSize);
		if(!texto){ 
			texto=(char*)malloc(2);
			strcpy(texto," ");
			strcpy(swf,"0");
		}
		else{				
			rewind (f); // Coloca al principio el puntero de lectura
			fread (texto,1,lSize,f); // Lee el contenido del fichero
			strcpy(swf,"1");
			fclose(f);
		}
	}
	return(respuesta_peticion(trmInfo,"Respuesta_LeeFicheroTexto",swf,texto));
}
//______________________________________________________________________________________________________
// Función: mandaFichero
//
//	Descripción:
//		Envía un fichero por la red
//	Parámetros:
//		- trmInfo : Trama recibida
//	Devuelve:
//		true siempre aunque escribe en log si hay error
// ________________________________________________________________________________________________________
BOOLEAN mandaFichero(TramaRepos *trmInfo)
{
	char *b,*l;
	FILE *f;
	int blk,lsize,ret;

	char *nomfile=toma_parametro("nfl",trmInfo->trama.parametros); // Toma nombre completo del archivo
	f = fopen(nomfile,"rb");
	blk=0;
	b=&trmInfo->trama.arroba; // Puntero al comienzo de la trama para colocar el bloque leido
	l=b+sizeof(blk); // Puntero después del dato bloque para colocar los bytes leidos
	if(f){ // El fichero no existe
		while(!feof(f)){
			blk++;
			memcpy(b,&blk,sizeof(blk));
			lsize=fread (trmInfo->trama.parametros,1,LONGITUD_PARAMETROS-1,f); // Lee el contenido del fichero
			memcpy(l,&lsize,sizeof(lsize));
			ret=sendto(trmInfo->sck,(char*)&trmInfo->trama,lsize+LONGITUD_CABECERATRAMA,0,\
					(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
			ret = recvfrom(trmInfo->sck,(char *)&trmInfo->trama,LONGITUD_TRAMA,0,(struct sockaddr*)&trmInfo->cliente,&trmInfo->sockaddrsize);
		}
		fclose(f);
	}
	blk++;
	memcpy(b,&blk,sizeof(blk));
	lsize=0;
	memcpy(l,&lsize,sizeof(lsize));
	trmInfo->trama.parametros[0]=(char)NULL;
	ret=sendto(trmInfo->sck,(char*)&trmInfo->trama,lsize+LONGITUD_CABECERATRAMA,0,\
			(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	return(true);
}
//_______________________________________________________________________________________________________________
// Función: sesionMulticast
//
//		Descripción:
//    		Comprueba si debe comenzar una sesión multicast para envio de imagenes
//		Parámetros:
//			- trmInfo : Trama recibida
//_______________________________________________________________________________________________________________
BOOLEAN sesionMulticast(TramaRepos *trmInfo)
{
	char *img,*ipm,*pom,*mom,*vlm,*iph,*nip,*ide,cmdshell[512];
	int res;

	img=toma_parametro("img",trmInfo->trama.parametros); // Nombre del fichero (Imagen)
	ipm=toma_parametro("ipm",trmInfo->trama.parametros); // Dirección IP multicast
	pom=toma_parametro("pom",trmInfo->trama.parametros); // Puerto multicast
	mom=toma_parametro("mom",trmInfo->trama.parametros); // Modo transmisión multicast
	vlm=toma_parametro("vlm",trmInfo->trama.parametros); // Velocidad transmisión multicast
	iph=toma_parametro("iph",trmInfo->trama.parametros); // Dirección ip cliente
	nip=toma_parametro("nip",trmInfo->trama.parametros); // Clientes necesarios para iniciar sesión
	ide=toma_parametro("ide",trmInfo->trama.parametros); // Identificador dela sesión multicast

	if(iniSesionMulticast(iph,ide,nip)){
		sprintf(cmdshell,"%s/sendFileMcast %s \"%s:%s:%s:%sM:%s:%s\"",reposcripts,img,pom,mom,ipm,vlm,nip,"0");
		res=system(cmdshell);
		if(res>0)
			RegistraLog(" Ha habido algún problema al iniciar sesión multicast",false);
	}
	return(true);
}
// ________________________________________________________________________________________________________
// Función: iniSesionMulticast
//
//		Descripción:
// 			Devuelve true o false dependiendo de si se está esperando comenzar una sesioón multicast
//		Parámetros:
//			- iph : La ip del cliente a incorporar a la sesión
//			- ide: Identificador de la sesión (Puerto multicast)
//			- nip: Número de ordenadores
// ________________________________________________________________________________________________________
BOOLEAN iniSesionMulticast(char *iph,char *ide,char *nip)
{
	int i,numipes,sw,idx;

	sw=false;
	for (i=0;i<MAXIMAS_MULSESIONES;i++){
		if (strcmp(ide,tbsmul[i].ides)==0){ // Si existe la sesión y está esperando activarse
			if (!IgualIP(iph,tbsmul[i].ipes)){ // Si NO existe la IP en la cadena
				 strcat( tbsmul[i].ipes,";");
				 strcat( tbsmul[i].ipes,iph); // Añade IP del cliente
			}
			idx=i;
			sw=true;
			break;
		}
	}
	if(!sw){ // No existe la entrada de la sesión
		if (!hay_hueco(&idx)){ // Busca hueco para el nuevo cliente
			RegistraLog(" No hay hueco para nueva sesión multicast",false);
			return(false); // No hay huecos
		}
		strcpy(tbsmul[idx].ides,ide);// Copia identificador de la sesión
		tbsmul[idx].ipes=Buffer(16*(atoi(nip)+1));  // Toma memoria para el buffer de lectura.
		if (tbsmul[idx].ipes == NULL) return(false);
		strcpy(tbsmul[idx].ipes,iph); // Copia primer cliente de la sesión multicast
	}

	numipes=cuenta_ipes(tbsmul[idx].ipes); // Número de ipes a los que enviar la trama multicast
	if(numipes==atoi(nip)){
		tbsmul[idx].ides[0]=(char)NULL; // Libera sesión de la tabla de sesiones
		free(tbsmul[idx].ipes);
		tbsmul[idx].ipes=NULL;
		return(TRUE); // Que de comienzo la transmisión multicast
	}
	else
		return(FALSE); // Aún no están preparados todos los clientes para la transmisión
}
// ________________________________________________________________________________________________________
// Función: hay_hueco
//
// 		Descripción:
// 			Esta función devuelve true o false dependiendo de que haya hueco en la tabla de sockets para un nuevo cliente.
// 		Parametros:
// 			- idx:   Primer indice libre que se podrn utilizar
// ________________________________________________________________________________________________________
int hay_hueco(int *idx)
{
	int i;

	for (i=0;i<MAXIMAS_MULSESIONES;i++){
		if (strncmp(tbsmul[i].ides,"\0",1)==0){ // Hay un hueco
			*idx=i;
			return(TRUE);
		}
	}
	return(FALSE);
}


//_________________________________________________________________________________________________
//	Función: Buffer
//
//	Descripción:
// 		Reserva memoria  
//	Parámetros:
//		- l: 	Longitud en bytes de la reserva
//	Devuelve:
//		Un puntero a la memoria reservada
//___________________________________________________________________________________________________
char * Buffer(int l)
{
	char *buf;
	buf=(char*)malloc(l);
	if(buf==NULL){
		RegistraLog(" fallo de reserva de memoria en modulo Buffer()",true);
		return(false);
	}
	memset(buf,0,l);	
	return(buf);
}
//_______________________________________________________________________________________________________________
//
// Función: TomaPuertoLibre
//
//	  Descripción:
//  	Crea un socket en un puerto determinado para la conversación UDP con el repositorio
//	  Parámetros:
//		 - puerto: 	Puerto para la creación del socket
// 
//_______________________________________________________________________________________________________________
int TomaPuertoLibre(int * puerto)
{
	SOCKET socket_c; // Socket para hebras (UDP)
   	struct sockaddr_in cliente;
	int puertolibre;

	socket_c = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP

	if (socket_c == SOCKET_ERROR)
		return (false);

	cliente.sin_addr.s_addr = inet_addr(IPlocal); // selecciona interface
	cliente.sin_family = AF_INET;
	puertolibre=PUERTOMINUSER;
	while(puertolibre<PUERTOMAXUSER){ // Busca puerto libre
		cliente.sin_port = htons(puertolibre); // Puerto asignado
		if (bind(socket_c,(struct sockaddr *)&cliente,sizeof(cliente)) == SOCKET_ERROR)
			puertolibre++;
		else
			break;
	}
	if(puertolibre>=PUERTOMAXUSER){ // No hay puertos libres
		return(INVALID_SOCKET);
	}
	*puerto=puertolibre;
	return(true);
}
//________________________________________________________________________________________________________
// Función: RESPUESTA_inclusionREPO
//
//		Descripción:
//			Esta función lee la trama respuesta de inclusión del repositorio hidra
//	    Parámetros:
//		    - trama: trama a leer
//________________________________________________________________________________________________________
int RESPUESTA_inclusionREPO(TRAMA *trama)
{

	INTROaFINCAD(trama->parametros);
	char* prm;
	prm=toma_parametro("prp",trama->parametros); // Puero de comunicaciones
	
	if (prm == NULL){
		RegistraLog("ATENCIÓN.- Este repositorio no está dado de alta en el sistema. Utilice la consola de administración para hacer esto.",false);
		return(false);
     }
	
	puertorepo=atoi(prm);
	prm=toma_parametro("pth",trama->parametros); // Path al directorio base de Hidra
	strcpy(PathHidra,prm);
	prm=toma_parametro("ptx",trama->parametros); // Path al directorio PXE
	strcpy(PathPXE,prm);
	
	strcpy(PathUsuarios,PathHidra);
	strcpy(PathIconos,PathHidra);
	strcpy(PathComandos,PathHidra);
	strcat(PathComandos,"/comandos");
	strcat(PathUsuarios,"/usuarios/");
	strcat(PathIconos,"/iconos/");
	
	prm=toma_parametro("usu",trama->parametros); // usuario acceso B.D.
	strcpy(usuario,prm);
	prm=toma_parametro("pwd",trama->parametros); // Pasword
	strcpy(pasguor,prm);
	prm=toma_parametro("dat",trama->parametros); // Ip gestor de datos
	strcpy(datasource,prm);
	prm=toma_parametro("cat",trama->parametros); // Nombre B.D.
	strcpy(catalog,prm);

	return(true);
}
//***************************************************************************************************************
// PROGRAMA PRINCIPAL 
//***************************************************************************************************************
int main(int argc, char **argv)
{
    SOCKET socket_s; // Socket donde escucha el repositorio
	TramaRepos *trmInfo;
	struct sockaddr_in local;
	int i,ret;

	strcpy(szPathFileCfg,"ogAdmRepo.cfg");
	strcpy(szPathFileLog,"ogAdmRepo.log");	
	for(i = 1; i < argc; i++){
       if (argv[i][0] == '-'){
           switch (tolower(argv[i][1])){
               case 'f':
                   if (argv[i+1]!=NULL)
                       strcpy(szPathFileCfg, argv[i+1]);
				else{
					RegistraLog("Fallo en los parámetros: Debe especificar el fichero de configuración del servicio",false);
					exit(EXIT_FAILURE);
				}
                   break;
               case 'l':
                   if (argv[i+1]!=NULL)
                       strcpy(szPathFileLog, argv[i+1]);
				else{
					RegistraLog("Fallo en los parámetros: Debe especificar el fichero de log para el servicio",false);
					exit(EXIT_FAILURE);
				}
                   break;
               default:
                   	RegistraLog("Fallo de sintaxis en los parámetros: Debe especificar -f nombre_del_fichero_de_configuracion_del_servicio -l nombre_del_fichero_de_log_del_servicio",false);
					exit(EXIT_FAILURE);
                   break;
           }
       }
    }
	if(!TomaConfiguracion(szPathFileCfg)){ // Toma parametros de configuracion
		RegistraLog("NO existe fichero de configuración o contiene un error de sintaxis",false);
		exit(EXIT_FAILURE);
	}
	if(!inclusion_REPO()){
		RegistraLog("Ha habido algún problema al abrir sesión con el servidor de administración",false);
		exit(EXIT_FAILURE);
	}
	
	for (i=0;i<MAXIMAS_MULSESIONES;i++){
		tbsmul[i].ides[0]=(char)NULL; // Inicializa identificadores de sesiones multicast
		tbsmul[i].ipes=(char)NULL;
	}

	RegistraLog("Inicio de sesion***",false);

	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		RegistraLog("Error al crear socket para servicio del Repositorio:",true);
		exit(EXIT_FAILURE);
	}
	RegistraLog("Creando Socket para comunicaciones***",false);
	
	local.sin_addr.s_addr = inet_addr(IPlocal);// selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(puertorepo); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		RegistraLog("Error al enlazar socket con interface para servicio de Repositorio Hidra",true);
		exit(EXIT_FAILURE);;
	}
	RegistraLog("Enlazado Socket para comunicaciones***",false);
	while(true){
		trmInfo = (TramaRepos*)malloc(sizeof(TramaRepos)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
			RegistraLog("Fallo al crear estructura de control para protocolo REPO",false);
			exit(EXIT_FAILURE);;
        }
		// Inicializa trmInfo
		memset(trmInfo,0,sizeof(struct  TramaRepos));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		trmInfo->sck=socket_s;
		// Espera trmInfos Repositorio
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->trama, sizeof(trmInfo->trama),0,(struct sockaddr *)&trmInfo->cliente, &trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			RegistraLog("Error al recibir mensaje de cliente hidra. Se para el servicio de repositorio",true);
			exit(EXIT_FAILURE);
		}
		else{
			if (ret>0){
				/*
				resul=pthread_create(&hThread,NULL,GestionaServicioRepositorio,(LPVOID)trmInfo);
				if(resul!=0){
					RegistraLog("Fallo al crear la hebra cliente de repositorio Hidra",false);
		    		exit(EXIT_FAILURE);
        		}
        		pthread_detach(hThread);	
        		*/
   				NwGestionaServicioRepositorio(trmInfo);
   				close(trmInfo->sck);
			}
		}
	}
	close(socket_s);
	exit(EXIT_SUCCESS);
}
