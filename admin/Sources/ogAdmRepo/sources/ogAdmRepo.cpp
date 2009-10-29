//****************************************************************************************************************************************************
//	Aplicación OpenGNSys
//	Autor: José Manuel Alonso.
//	Licencia: Open Source 
//	Fichero: ogAdmServer.cpp
//	Descripción:
//		Este módulo de la aplicación OpenGNSys implementa las comunicaciones con el Repositorio.
// ****************************************************************************************************************************************************
#include "ogAdmRepo.h"
#include "encriptacion.c"
// ________________________________________________________________________________________________________
// Funcin�:RegistraLog
//
//		Descripcin�:
//			Esta funcin� registra los evento de errores en un fichero log
//		Parametros:
//			- msg : Mensage de error
//			- swerrno: Switch que indica que recupere literal de error del sistema
// ________________________________________________________________________________________________________
void RegistraLog(const char *msg,int swerrno)
{
	time_t rawtime;
	struct tm * timeinfo;

	time ( &rawtime );
	timeinfo = gmtime(&rawtime);

	FLog=fopen(szPathFileLog,"at");
	if(swerrno)
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s:%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg,strerror(errno));
	else
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	fclose(FLog);
}

//________________________________________________________________________________________________________
// Funcinn: TomaConfiguracion
//
//		Descripcinn:
//		Esta funcinn lee el fichero de configuracinn del programa
//		Parametros:
//				- pathfilecfg : Ruta al fichero de configuracinn
//________________________________________________________________________________________________________
int TomaConfiguracion(char* pathfilecfg)
{
	long lSize;
	char * buffer,*lineas[100],*dualparametro[2];
	char ch[2];
	int i,numlin,resul;

	if(pathfilecfg==NULL) exit(EXIT_FAILURE);; // Nombre del fichero en blanco

	Fconfig = fopen ( pathfilecfg , "rb" );
	if (Fconfig==NULL)	exit(EXIT_FAILURE);;
	fseek (Fconfig , 0 , SEEK_END);  // Obtiene tamaño del fichero.
	lSize = ftell (Fconfig);
	rewind (Fconfig);
	buffer = (char*) malloc (lSize);  // Toma memoria para el buffer de lectura.
	if (buffer == NULL)	 	exit(EXIT_FAILURE);;
	fread (buffer,1,lSize,Fconfig); 	// Lee contenido del fichero
	fclose(Fconfig);

	//inicializar
	IPlocal[0]=(char)NULL;
	servidorhidra[0]=(char)NULL;
	Puerto[0]=(char)NULL;
	
	strcpy(ch,"\n");// caracter delimitador ( salto de linea)
	numlin=split_parametros(lineas,buffer,ch);
	for (i=0;i<numlin;i++){
		strcpy(ch,"=");// caracter delimitador
		split_parametros(dualparametro,lineas[i],ch); // Toma primer nombre del parametros

		resul=strcmp(dualparametro[0],"IPlocal");
		if(resul==0) strcpy(IPlocal,dualparametro[1]);

		resul=strcmp(dualparametro[0],"IPhidra");
		if(resul==0) strcpy(servidorhidra,dualparametro[1]);

		resul=strcmp(dualparametro[0],"Puerto");
		if(resul==0) strcpy(Puerto,dualparametro[1]);
		
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

	return(TRUE);
}
// ________________________________________________________________________________________________________
// Funcinn: INTROaFINCAD
//
//		Descripcinn?:
// 			Cambia INTROS por caracteres fin de cadena ('\0') en una cadena
//		Parametros:
//				- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void INTROaFINCAD(char* parametros)
{
	int lon,i;
	lon=strlen(parametros);
	for(i=0;i<lon;i++){ // Cambia los INTROS por NULOS
		if(parametros[i]=='\r') parametros[i]='\0';
	}
}
// ________________________________________________________________________________________________________
// Funcinn: INTROaFINCAD
//
//		Descripcinn?:
// 			Cambia INTROS por caracteres fin de cadena ('\0') en una cadena
//		Parametros:
//				- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void FINCADaINTRO(char* a,char *b)
{
	char *i;
	for(i=a;i<b;i++){ // Cambia los NULOS por INTROS
		if(*i=='\0') *i='\r';
	}
}
// ________________________________________________________________________________________________________
// Funcinn: toma_parametro
// 
//		Descripcinn?:
// 			Esta funci? devuelve el valor de un parametro incluido en la trmInfo.
// 			El formato del protocolo es: "nombre_parametro=valor_parametro"
// 		Par?etros:
// 			- nombre_parametro: Es el nombre del par?etro a recuperar
// 			- parametros: Es la matriz que contiene todos los par?etros
// ________________________________________________________________________________________________________
char * toma_parametro(const char* nombre_parametro,char *parametros)
{
	int i=0;
	char* pos;

	for(i=0;i<LONGITUD_PARAMETROS-4;i++){ 
		if(parametros[i]==nombre_parametro[0]){
			if(parametros[i+1]==nombre_parametro[1]){
				if(parametros[i+2]==nombre_parametro[2]){
					if(parametros[i+3]=='='){
						pos=&parametros[i+4];
						return(pos);
					}
				}
			}
		}
	}
	return(NULL);
}
// ________________________________________________________________________________________________________
// Funci�: split_parametros
//
//		Descripción:
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
//_______________________________________________________________________________________________________________
//
// Comprueba si la IP del cliente est?a en la base de datos de Hidra
// parámetros:
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
int ClienteExistente(TramaRepos *trmInfo)
{
	char sqlstr[1000],ErrStr[200];	
	Database db;
	Table tbl;

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACCESO atnico A TRAVEZ DE OBJETO MUTEX a este trozo de cnigo 
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
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	return(true);
}
//___________________________________________________________________________________________________
// Funcin: inclusion_REPO
//
//		Descripcin: 
//			 Abre una sesin en el servidor Hidra
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

	sock=Abre_conexion(servidorhidra,puerto);
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
// ________________________________________________________________________________________________________
// Funcin: Abre_conexion
//
//		Descripcin: 
//			Crea un socket y lo conecta a un servidor
//		parámetros:
//			- ips : La direccin IP del servidor
//			- port : Puerto para la comunicacin
//		Devuelve:
//			- El socket o nulo dependiendo de si se ha establecido la comunicacin
// ________________________________________________________________________________________________________
SOCKET Abre_conexion(char *ips,int wpuerto)
{
    struct sockaddr_in server;
	SOCKET s;
	// Crea el socket y se intenta conectar
	s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (s == INVALID_SOCKET){
		return (INVALID_SOCKET);
	}
	server.sin_family = AF_INET;
	server.sin_port = htons((short)wpuerto);
	server.sin_addr.s_addr = inet_addr(ips);
	if (connect(s, (struct sockaddr *)&server, sizeof(server)) == INVALID_SOCKET)
		return (INVALID_SOCKET);
	return(s); // Conectado
}
//___________________________________________________________________________________________________
//
//  Enva tramas al servidor HIDRA 
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
//___________________________________________________________________________________________________
//
// Recibe tramas desde el servidor HIDRA
//___________________________________________________________________________________________________
int recibe_tramas(SOCKET s,TRAMA *trama)
{
	int ret;

	ret = recv(s,(char*)trama,LONGITUD_TRAMA,0);
	if (ret == 0) // Conexin cerrada por parte del cliente (Graceful close)
		return (false);
	else{ 
		if (ret == SOCKET_ERROR){
			return (false);
		}
		else{ // Datos recibidos
			Desencriptar((char*)trama);
			trama->parametros[ret-11]=(char)NULL; // Coloca caracter fin de cadena en trama
			return(true);
		}
	}
}
//_______________________________________________________________________________________________________________
//
// Gestiona la conexion con un cliente que sea Hidra para el servicio de repositorio
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
// Gestiona la conexion con un cliente que sea Hidra para el servicio de repositorio
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
// Gestiona la conexion con un cliente que sea Hidra para el servicio de repositorio
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
	
	resul=strcmp(nombrefuncion,"FicheroOperador");
	if(resul==0)
		return(FicheroOperador(trmInfo));
		
	resul=strcmp(nombrefuncion,"Actualizar");
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

	resul=strcmp(nombrefuncion,"ExecShell");
	if(resul==0)
		return(RegistraComando(trmInfo));	

	resul=strcmp(nombrefuncion,"TomaConfiguracion");
	if(resul==0)
		return(RegistraComando(trmInfo));	
		
	resul=strcmp(nombrefuncion,"InventarioHardware");
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
// Funcinn: RegistraComando
//
//	 Descripcinn:
//		Crea un fichero de comando para cada cliente hidra
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
	RegistraLog(msglog,false);
	
	for(i=0;i<numipes;i++){
		strcpy(nomfilecmd,PathComandos);
		strcat(nomfilecmd,"/CMD_");
		strcat(nomfilecmd,ipes[i]);
		//sprintf(msglog,"Crea fichero de comandos %s",nomfilecmd);
		RegistraLog(msglog,false);
		
		Fcomandos=fopen( nomfilecmd,"w");
		if(!Fcomandos) return(false);
		//sprintf(msglog,"Fichero creado %s",nomfilecmd);
		RegistraLog(msglog,false);
		
		fwrite((char*)&trmInfo->trama,lon,1,Fcomandos);
		fclose(Fcomandos);
	}
	return(true);
}
//_____________________________________________________________________________________________________________
// Funcin: Arrancar
//
//	 Descripcinn:
//		Esta funcinn enciende un ordenadores
//	Parámetros de entrada:
//		- parametros: Cadena con las mac de los ordenadores que se van a arrancar separadas por punto y coma
//_____________________________________________________________________________________________________________
int Arrancar(TramaRepos *trmInfo)
{
	int i,nummacs;
	char* macs[MAXIMOS_CLIENTES];
	char ch[2]; // Caracter delimitador

	char *mac=toma_parametro("mac",trmInfo->trama.parametros); // Toma Mac
	strcpy(ch,";");// caracter delimitador
	nummacs=split_parametros(macs,mac,ch);
	for(i=0;i<nummacs;i++){
		levanta(macs[i]);
	}
	return(RegistraComando(trmInfo));
}
//_____________________________________________________________________________________________________________
// Funcinn: levanta
//
// Descripcion:
//    Enciende el ordenador cuya MAC se pasa como parámetro
//	Parámetros de entrada:
//		- mac: La mac del ordenador
//_____________________________________________________________________________________________________________
int levanta(char * mac)
{
	BOOL          bOpt;
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
// Funcinn: Wake_Up
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
//		Parámetros de entrada:
//			- cadena : Cadena con el contenido de la mac
//		Parámetros de salida:
//			- numero : la direccinn mac convertida a binario (6 bytes)
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
// Funcinn: FicheroOperador
//
//	 Descripcinn:
//		Crea un fichero para que un operador de aula o administrador de centro pueda entrar en el menú privado de los clientes rembo
//	Parámetros de entrada:
//		- parametros: Parámetros del comando
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
// Funcinn: FicheroOperador
//
//	 Descripcinn:
//		Crea un fichero para que un operador de aula o administrador de centro pueda entrar en el menú privado de los clientes rembo
//	Parámetros de entrada:
//		- parametros: Parámetros del comando
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
// Comprueba si existe un fichero
//_______________________________________________________________________________________________________________
bool ExisteFichero(TramaRepos *trmInfo)
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
//
// Envia respuesta a peticin de comando
//_______________________________________________________________________________________________________________
bool respuesta_clienteHidra(TramaRepos *trmInfo)
{
	int ret;
	//MandaRespuesta
	Encriptar((char*)&trmInfo->trama);
	ret=sendto(trmInfo->sck,(char*)&trmInfo->trama,strlen(trmInfo->trama.parametros)+11,0,(struct sockaddr*)&trmInfo->cliente,trmInfo->sockaddrsize);
	if (ret == SOCKET_ERROR){
		RegistraLog("***sendto() fallo al enviar respuesta modulo respuesta_clienteHidra() :",true);
		return(false);
	}
	return(true);	
}
//_______________________________________________________________________________________________________________
//
// Envia respuesta a peticin de comando
//_______________________________________________________________________________________________________________
bool respuesta_peticion(TramaRepos *trmInfo,const char *LitRes,char* swf,char*txt)
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
		RegistraLog("***sendto() fallo al enviar respuesta a peticin de comando:",true);
		return(false);
	}
	return(true);	
}
//_______________________________________________________________________________________________________________
//
// Comprueba si existe un fichero
//_______________________________________________________________________________________________________________
bool EliminaFichero(TramaRepos *trmInfo)
{
	char swf[2];
	char cmdshell[512];
	int res;
	char pathfile[250];
	
	char *nomfile=toma_parametro("nfl",trmInfo->trama.parametros); // Toma nombre funcin
	sprintf(pathfile,"%s%s",PathHidra,nomfile);
	sprintf(cmdshell,"rm -f %s",pathfile);
	res=system(cmdshell);
	if(res==0)
		strcpy(swf,"1");
	else
		strcpy(swf,"0");
	return(respuesta_peticion(trmInfo,"Respuesta_EliminaFichero",swf,nomfile));
}
//_______________________________________________________________________________________________________________
//
// Comprueba si existe un fichero
//_______________________________________________________________________________________________________________
bool LeeFicheroTexto(TramaRepos *trmInfo)
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
			rewind (f);								// Coloca al principio el puntero de lectura
			fread (texto,1,lSize,f); 	// Lee el contenido del fichero
			strcpy(swf,"1");
			fclose(f);
		}
	}
	return(respuesta_peticion(trmInfo,"Respuesta_LeeFicheroTexto",swf,texto));
}

//_________________________________________________________________________________________________
//	Funcin: Buffer
//
//	Descripcin:
// 		Reserva memoria  
//	parámetros:
//		- l: 	Longitud en bytes de la reserva
//	Devuelve:
//		Un puntero a la memoria reservada
//___________________________________________________________________________________________________
char * Buffer(int l)
{
	char *buf;
	buf=(char*)malloc(l);
	if(buf==NULL){
		RegistraLog("*** fallo de reserva de memoria en modulo Buffer()",true);
		return(false);
	}
	memset(buf,0,l);	
	return(buf);
}
//_______________________________________________________________________________________________________________
//
// Crea un socket en un puerto determinado para la conversacin UDP con el repositorio
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
// Funcinn: TomaRestoConfiguracion;
//
//		Descripcinn:
//		Esta funcinn lee la trama respuesta de inclusin del repositorio hidra

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
					RegistraLog("Fallo en los parámetros: Debe especificar el fichero de configuracin del servicio",false);
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
                   	RegistraLog("Fallo de sintaxis en los parámetros: Debe especificar -f nombre_del_fichero_de_configuracin_del_servicio -l nombre_del_fichero_de_log_del_servicio",false);
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
	
	RegistraLog("***Inicio de sesion***",false);

	socket_s = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP
	if (socket_s == SOCKET_ERROR){
		RegistraLog("***Error al crear socket para servicio del Repositorio:",true);
		exit(EXIT_FAILURE);
	}
	RegistraLog("***Creando Socket para comunicaciones***",false);
	
	local.sin_addr.s_addr = inet_addr(IPlocal);// selecciona interface
	local.sin_family = AF_INET;	
	local.sin_port = htons(puertorepo); // Puerto

	// Enlaza socket
	if (bind(socket_s,(struct sockaddr *)&local,sizeof(local)) == SOCKET_ERROR){
		RegistraLog("***Error al enlazar socket con interface para servicio de Repositorio Hidra",true);
		exit(EXIT_FAILURE);;
	}
	RegistraLog("***Enlazado Socket para comunicaciones***",false);
	while(true){
		trmInfo = (TramaRepos*)malloc(sizeof(TramaRepos)); // Crea estructura de control para hebra
        if (trmInfo == NULL){
			RegistraLog("***Fallo al crear estructura de control para protocolo REPO",false);
			exit(EXIT_FAILURE);;
        }
		// Inicializa trmInfo
		memset(trmInfo,0,sizeof(struct  TramaRepos));
		trmInfo->sockaddrsize = sizeof(trmInfo->cliente);
		trmInfo->sck=socket_s;
		// Espera trmInfos Repositorio
		ret = recvfrom(trmInfo->sck,(char *)&trmInfo->trama, sizeof(trmInfo->trama),0,(struct sockaddr *)&trmInfo->cliente, &trmInfo->sockaddrsize);
		if (ret == SOCKET_ERROR){
			RegistraLog("***Error al recibir mensaje de cliente hidra. Se para el servicio de repositorio",true);
			exit(EXIT_FAILURE);
		}
		else{
			if (ret>0){
				/*
				resul=pthread_create(&hThread,NULL,GestionaServicioRepositorio,(LPVOID)trmInfo);
				if(resul!=0){
					RegistraLog("***Fallo al crear la hebra cliente de repositorio Hidra",false);
		    		exit(EXIT_FAILURE);
        		}
        		pthread_detach(hThread);	
        		*/
   				NwGestionaServicioRepositorio(trmInfo);
			}
		}
	}
	close(socket_s);
	exit(EXIT_SUCCESS);
}
