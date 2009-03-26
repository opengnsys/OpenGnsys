// ***************************************************************************************************************************************
// Aplicación HIDRA (Gestin y Admistracin de aulas de informtica)
// Copyright 2003-2007 Jos Manuel Alonso. Todos los derechos reservados.
// Fichero: hidrax.cpp
//	Descripcin:
//		Este programa es el que utiliza el cliente Hidra para comunicarse con su servidor mediante sockets
// ***************************************************************************************************************************************
#include "hidrac.h"
//__________________________________________________________________________________________________
//
// Función: Encripta
//
//	 Descripcin:
//		Esta funcin encripta una cadena y la devuelve como parametro
//_____________________________________________________________________________________________________________
char * Encriptar(char *cadena)
{
	 return(cadena);
	
	int i,lon;
	char clave; 
	
	clave = 12 & 0xFFU; // La clave elegida entre 0-255, en este caso 12
	lon=strlen(cadena);
	for(i=0;i<lon;i++)
      cadena[i]=((char)cadena[i] ^ clave) & 0xFF; 
	return(cadena);
}
//_____________________________________________________________________________________________________________
//
// Funcin: Desencripta
//
//	 Descripcin:
//		Esta funcin desencripta una cadena y la devuelve como parametro
//_____________________________________________________________________________________________________________
char * Desencriptar(char *cadena)
{
	return(cadena);
	
	int i,lon;
	char clave; 
	
	clave = 12 & 0xFFU; // La clave elegida entre 0-255, en este caso 12
	lon=strlen(cadena);
	for(i=0;i<lon;i++)
		cadena[i]=((char)cadena[i] ^ clave) & 0xFF;
	return(cadena);

}

//_____________________________________________________________________________________________________________
//
// Funcin: ejecutarscript
//
//	 Descripcin:
//		Esta función ejecuta un script creando un proceso hijo para ello
// 
//_____________________________________________________________________________________________________________
int ejecutarscript ( char *script,char * parametros,char *salida)
{
    int  descr[2];	/* Descriptores de E y S de la turbería */
    int  bytesleidos;	/* Bytes leidos en el mensaje */
    int resul;
    int estado;	// Devuelve el estatus del hijo
    pid_t  pid;
    char mensaje[256]="";	   /* Mensajes leído */
   // char *script="DetectarDiscos"; /* Script a ejecutar */

    pipe (descr);
    if((pid=fork())==0){
		/* Proceso hijo que ejecuta el script */
       close (descr[LEER]);
       dup2 (descr[ESCRIBIR], 1);
       close (descr[ESCRIBIR]);
       //resul=execl (script, script, parametros,NULL);
       resul=execlp (script, script, parametros,NULL);    
       exit(resul);   
    }
    else {
       if (pid ==-1){
       	sprintf(msglog,"***Error en la creación del proceso hijo pid=%d",pid);
		Log(msglog);
       	 return(0);
       }
		/* Proceso padre que lee la salida del script */
       close (descr[ESCRIBIR]);
       if(salida!=(char*)NULL){
			bytesleidos = read (descr[LEER], mensaje, 255);
	       while(bytesleidos>0){
    	   		mensaje[bytesleidos]=(char)NULL;
			strcat(salida,mensaje);
			bytesleidos = read (descr[LEER], mensaje, 255);
	       }
       }
        sprintf(msglog,"***Información que devuelve el comando %s=%s",script,salida);
		Log(msglog);
       close (descr[LEER]);
       //kill(pid,SIGQUIT);
       waitpid(pid,&estado,0);  
       resul=WEXITSTATUS(estado);
       sprintf(msglog,"***Estatus de finalizacion del scripts %s=%d",script,resul);
		Log(msglog);   
       return(resul);
    }
     sprintf(msglog,"***Captura resul en padres=%d",resul);
		Log(msglog);
    return(-1); 
}
//_______________________________________________________________________________________________________________
//
// Toma la IP local
//_______________________________________________________________________________________________________________
int TomaIPlocal()
{
   	int herror;
	sprintf(cmdshell,"%s/IPLocal",HIDRASCRIPTS);
	herror=ejecutarscript (cmdshell,NULL,IPlocal);	
	if(herror){
	    RaiseError(4,tbErrores[4],"TomaIPlocal()");	 // Se ha producido algun error
	    strcpy(IPlocal,"0.0.0.0");
	    return(false);

    }
   	return(true); 	
}
//_______________________________________________________________________________________________________________
//
// Crea un socket en un puerto determinado para la conversacin UDP con el repositorio
// 
//_______________________________________________________________________________________________________________
SOCKET UDPConnect(char *ips )
{
	SOCKET socket_c; // Socket para hebras (UDP)
   	//struct sockaddr_in cliente;
	//int puertolibre;

	socket_c = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP); // Crea socket para UDP

	if (socket_c == SOCKET_ERROR)
		return (INVALID_SOCKET);
	
	return(socket_c);
/*
	cliente.sin_addr.s_addr = inet_addr(ips); // selecciona interface
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
	puerto=puertolibre
	return(socket_c);
	*/
}
//________________________________________________________________________________________________________
// Funcin: envia_comandos
//
//		Descripcin: 
//			Enva trama UDP
// ________________________________________________________________________________________________________
int envia_comandos(SOCKET s,TRAMA* trama, char* ipsrv,char *wpuerto)
{
	int ret,lon;
	int puerto=atoi(wpuerto);
	struct sockaddr_in  addrRepo;
	 
	trama->arroba='@';										// cabecera de la trama
	strcpy(trama->identificador,"JMMLCAMDJ");	// identificador de la trama
	trama->ejecutor='1';										// ejecutor de la trama 1=el servidor hidra  2=el cliente hidra
				
	// Compone la trama
	lon=strlen(trama->parametros); 
	lon+=sprintf(trama->parametros+lon,"iph=%s\r",Propiedades.IPlocal);	// Ip del ordenador
	lon+=sprintf(trama->parametros+lon,"ido=%s\r",Propiedades.idordenador);	// identificador del ordenador
	
	addrRepo.sin_family = AF_INET;
    addrRepo.sin_port = htons((short)puerto);
    addrRepo.sin_addr.s_addr = inet_addr(ipsrv); //  Direccin IP repositorio
	Encriptar((char*)trama);
	ret = sendto(s,(char *)trama,lon+11,0,(struct sockaddr *)&addrRepo, sizeof(addrRepo));
    if (ret == SOCKET_ERROR)
		return(FALSE);
	return true;
}
//________________________________________________________________________________________________________
// Funcin: UDPConnect
//
//		Descripcin: 
//			Crea socket para comunicar con el repositorio hidra
// ________________________________________________________________________________________________________
char* recibe_comandos(SOCKET s)
{
	int ret;
	struct sockaddr_in addrRepo;
	
	socklen_t iAddrSize = sizeof(addrRepo);
	TRAMA *trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
	if(trama){
		ret = recvfrom(s,(char *)trama, LONGITUD_TRAMA,0,(struct sockaddr *)&addrRepo,&iAddrSize);
		if (ret != SOCKET_ERROR){
			Desencriptar((char*)trama);
			return((char*)trama);
		}
	}
	return(NULL);
}
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int BuildDiskImage(int d, char *p, char* f)
{
	return(true);
} 
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int CreateVirtualImage(char *f, char* d, char* p)
{
	return(true);
} 
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int Synchronize(char *f, char* d, char* p)
{
	return(true);
} 
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int GetCachePartitionSize(char*d)
{
	return(0);
} 

//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int Reboot()
{
	char nomcmd[512];
	int resul;
	
	
	strcpy(nomcmd,"shutdown -r now");
	resul=system(nomcmd);
	return(resul);
} 
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int CreateTree(char* p)
{
	return(true);
} 

//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int PowerOff()
{
	char nomcmd[512];
	int resul;
	
	strcpy(nomcmd,"shutdown -h now");
	resul=system(nomcmd);
	return(resul);
} 
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int CloseWindow(char *w)
{
	return(true);
} 
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int FreeVirtualImage(char* imgv)
{
	return(true);
} 
//___________________________________________________________________________________________________
//
//___________________________________________________________________________________________________
int CreateDir(char* d)
{
	return(true);
} 
//___________________________________________________________________________________________________
//
//		Activa el modo rembo offline para que el ordenador pueda arrancar y poder tener todas las ventajas
//		del modo online ( Restauracin de imgenes, arranque de particiones, menús, etc)
//___________________________________________________________________________________________________
int ActivarRemboOffline()
{
	if(!CACHEEXISTS){ // No existe caché
		RaiseError(8,tbErrores[8],"ActivarRemboOffline()");	
		return(false); // Se ha producido algun error
	}
	return(true);
 }
//___________________________________________________________________________________________________
//	Funcin : CrearPerfilMsdos
//	Descripcin:
//		Crea el perfil software de una particin MsDos
//	Parmetros:
//		- fileimg: Fichero que contendr el perfil software
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin de la cual se va a crear el perfil
//__________________________________________________________________________________________________
int CrearPerfilMsdos(char* fileimg,char* pathimg,char* particion)
{
	char perfile[250];
	sprintf(perfile,"%s/%s",pathimg,fileimg);
	if (ExisteFichero(perfile) )// Elimina el perfil anterior
		RemoveFile(perfile);
	if(!BuildDiskImage(0,particion,perfile)){ // Constuye el perfil actual
		RaiseError(0,tbErrores[0],"CrearPerfilMsdos()");			
		return(false);			
	}
	return(true);
}
//___________________________________________________________________________________________________
//	Funcin : CrearPerfilW9x
//	Descripcin:
//		Crea el perfil software de una particin Windows 9x
//	Parmetros:
//		- fileimg: Fichero que contendr el perfil software
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin de la cual se va a crear el perfil
//__________________________________________________________________________________________________
int CrearPerfilW9x(char* fileimg,char* pathimg,char* particion)
{
	char perfile[250];
	sprintf(perfile,"%s/%s",pathimg,fileimg);
	if (ExisteFichero(perfile) )// Elimina el perfil anterior
		RemoveFile(perfile);		
	CreateVirtualImage("fileimgtmp","disk://0",particion); // Crea imagen virtual de la particin
	Synchronize("link://fileimgtmp",perfile,"b"); // Constuye el perfil actual
	FreeVirtualImage("fileimgtmp");
	return(true);
}
//___________________________________________________________________________________________________
//	Funcin : +
//	Descripcin:
//		Crea el perfil software de una particin Windows NTFS
//	Parmetros:
//		- filemasterboot: Fichero que contendrla imagen del sector master boot
//		- fileimg: Fichero que contendr el perfil software
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin de la cual se va a crear el perfil
//__________________________________________________________________________________________________
int Rembo_CrearPerfilNTFS(char* filemasterboot,char* fileimg,char* pathimg,char* particion)
{
	/*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="CrearPerfilNTFS";
		exc.resume=true;
		return exc;
	}
	with(BasicErrorHandler) 
	try {
		var bootsect = DevReadBootSects("disk://0:0");  // Salva el sector de arranque master (MBR)
		SaveFile(bootsect,pathimg+"/"+filemasterboot);
		CreateVirtualImage("winntimg","disk://0:"+particion);  // Crea imagen virtual
		if (ExisteFichero("link://winntimg/pagefile.sys"))  // Elimina fichero swap
			RemoveFile("link://winntimg/pagefile.sys");
		 if (ExisteFichero(pathimg+"/"+fileimg))  // Elimina el perfil anterior
			RemoveFile(pathimg+"/"+fileimg); 
		Synchronize("link://winntimg",pathimg+"/"+fileimg,"b"); // Constuye el perfil actual
		FreeVirtualImage("winntimg");  // Libera imagen virtual
	}
	return(wresul);
	*/
	return(true);
}
//___________________________________________________________________________________________________
//	Funcin : CrearPerfilNTFS
//	Descripcin:
//		Crea el perfil software de una particin Windows NTFS
//	Parmetros:
//		- filemasterboot: Fichero que contendrla imagen del sector master boot
//		- fileimg: Fichero que contendr el perfil software
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin de la cual se va a crear el perfil
//__________________________________________________________________________________________________
int CrearPerfilNTFS(char* filemasterboot,char* fileimg,char* pathimg,char* particion)
{
	return(CrearPerfil(fileimg,pathimg,particion,"CrearImagenNTFS"));
}
//___________________________________________________________________________________________________
//	Funcin : CrearPerfilLinux
// 	Descripcin:
//		Crea el perfil software de una particin Linux
//	Parmetros:
//		- fileimg: Fichero que contendr el perfil software
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin de la cual se va a crear el perfil
//__________________________________________________________________________________________________
int CrearPerfilLinux(char* fileimg,char* pathimg,char* particion)
{
	return(CrearPerfil(fileimg,pathimg,particion,"CrearImagenEXT3"));	
}
//__________________________________________________________________________________________________
int CrearPerfil(char* fileimg,char* pathimg,char* particion,char* script)   
{
   	int herror;
	char *disco;
	char *parametros;
	char *respuesta;
	
	disco=(char*)Buffer(3);
	sprintf(disco,"1");
	parametros=(char*)Buffer(250);
	sprintf(cmdshell,"%s/%s",HIDRASCRIPTS,script);
	sprintf(parametros,"%s %s %s %s %s.gzip",disco,particion,Propiedades.iprepo,"hdimages/pruebashidra/",fileimg);
	respuesta=(char*)Buffer(3);
	sprintf(respuesta,"0");
	
	// Registro en Log
	sprintf(msglog,"Creando Perfil Software disco:%s, partición:%s, IP del repositorio:%s, Nombre de la imagen:%s, Path de la imagen:%s",disco,particion,Propiedades.iprepo,fileimg,"hdimages/pruebashidra");
	Log(msglog);
	
	herror=ejecutarscript (cmdshell,parametros,respuesta);
	
	if(herror){
	    RaiseError(4,tbErrores[4],"CrearPerfil()");	 // Se ha producido algun error
		return(false); 
    }
    else{
		if(strcmp(respuesta,"0")!=0) {  	// Si se devuelve algún error ...
	    	herror=atoi(respuesta);
	        RaiseError(herror,tbErrores[herror],"CrearPerfil()");	 // Se ha producido algun error
	    	return(false);
	    }
	    else
	        return(true);
    }
}

//___________________________________________________________________________________________________
//	Funcin : CrearIncremental
//	Descripcin:
//		Crea un software incremental entre el contenido de una particin y un perfil software
//	Parmetros:
//		- fileimg: Fichero que contendr el perfil software
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin de la cual se va a crear el perfil
//		- pathincr: Nombre del directorio que acoger los ficheros del proceso diferencial
//__________________________________________________________________________________________________
int CrearIncremental(char* fileimg,char* pathimg,char* particion,char* pathincr,char* dirsys)
{
	 /*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="CrearIncremental";
		exc.resume=true;
		return exc;
	}
	with(BasicErrorHandler) 
	try {
		char* fileLogTextDiff=pathincr+".log";
		NTSetPartition((int)particion);
		NTDetect();
		TextDiff("disk://0:"+particion,pathimg+"/"+fileimg,fileLogTextDiff,true);
		MakeDiffFromText("disk://0:"+particion,pathimg+"/"+fileimg,fileLogTextDiff,pathincr,true);
	}
	return(wresul); 
	*/
	return(true);	
}
//___________________________________________________________________________________________________
//	Funcin : ParticionaryBorrar
//	Descripcin:
//		Particiona un disco duro y formatea sus distintos sistemas de ficheros
//	Parmetros:
//		- PrParticion: Cadena con la sintaxis de particionado de las particiones primarias
//		- LoParticion: Cadena con la sintaxis de particionado de las particiones secundarias
//__________________________________________________________________________________________________
int ParticionaryBorrar (char* PrParticion,char* LoParticion)
{
	/*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="ParticionaryBorrar";
		exc.resume=true;
		return exc;
	}

 with(BasicErrorHandler) 
 try {
		if (PrParticion!=""){
			SetPrimaryPartitions(0,PrParticion); // Crea las particiones primarias
			var ppar=char*Parse(PrParticion," "); // Split de las especificaciones de particionado de primarias
			for (int i=0;i<sizeof(ppar);i++) // Formatea cada una de las particiones primarias creadas
			HDClean(0,i+1);
		}
		if (LoParticion!=""){
			SetLogicalPartitions(0, LoParticion);// Crea las particiones secundarias
			var spar=StrParse(LoParticion," "); // Split de las especificaciones de particionado de secundarias
			for (int i=0;i<sizeof(spar);i++) // Formatea cada una de las particiones secundarias creadas
			HDClean(0,5+i);
		}
	}
	return(wresul);
	*/
	return(true);	
}
//___________________________________________________________________________________________________
//	Funcin : Particionar
//	Descripcin:
//		Modifica la tabla de particiones del sector de arranque master pero SIN formatear
//	Parmetros:
//		- PrParticion: Cadena con la sintaxis de particionado de las particiones primarias
//		- LoParticion: Cadena con la sintaxis de particionado de las particiones secundarias
//__________________________________________________________________________________________________
int Particionar(char* PrParticion,char* LoParticion)
{
	int herror;
	char disco[3];
	sprintf(disco,"1"); // Siempre el disco 1	

	if (strlen(PrParticion)>0){
		sprintf(cmdshell,"%s/CrearParticionesPrimarias %s %s ",HIDRASCRIPTS,disco,PrParticion);
		herror=ExecShell(cmdshell,NULL);
		if(!herror){
	    	RaiseError(4,tbErrores[4],"Particionar()");	 // Se ha producido algun error
			return(false); 
    	}	
	}
	if (strlen(LoParticion)>0){	
		sprintf(cmdshell,"%s/CrearParticionesLogicas %s %s ",HIDRASCRIPTS,disco,LoParticion);
		herror=ExecShell(cmdshell,NULL);
		if(!herror){
	    	RaiseError(4,tbErrores[4],"Particionar()");	 // Se ha producido algun error
			return(false); 
    	}
	}
 	return(true);	
}
//___________________________________________________________________________________________________
//	Funcin : Restaurar_MSDos
//	Descripcin:
//		Restaura la imagen de una particin MsDos
//	Parmetros:
//		- fileimg: Fichero que contendr el perfil software base de la imagen
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin en la cual se va a restaurar la imagen
//__________________________________________________________________________________________________
int Restaurar_MSDos(char* fileimg,char* pathimg,char* particion)
{
	/*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="Restaurar_MSDos";
		exc.resume=true;
		return exc;
	}
	with(BasicErrorHandler)
	try {
 		char* images[];
		images[0]=pathimg+"/"+fileimg;
		if(CACHEEXISTS && !OFFLINE) // Si existe cach
			CopyCache(images,Settings.CachePath,false,"");	// Se asegura que el perfil software base est en la cach
		Synchronize(pathimg+"/"+fileimg,"disk://0:"+particion,"b"); // Restaura el perfil
 }
 return(wresul); 
 */
	return(true); 
}
//___________________________________________________________________________________________________
//	Funcin : Restaurar_Windows9x
//	Descripcin:
//		Restaura la imagen de una particin Windows 9x
//	Parmetros:
//		- fileimg: Fichero que contendr el perfil software base de la imagen
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin en la cual se va a restaurar la imagen
//		- Hostname: Nombre del  ordenador
//		- GrupoTrabajo: Grupo de trabajo del ordenador
//___________________________________________________________________________________________________
int Restaurar_Windows9x(char* fileimg,char* pathimg,char* particion,char* Hostname,char* GrupoTrabajo)
{
	/*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="Restaurar_Windows9x";
		exc.resume=true;
		return exc;
	}
	with(BasicErrorHandler)
	try {
		char* images[];
		char* pathclw=HIDRACHERAIZ;	// Path en el servidor o cach del ejecutable para el cliente hidra (Windows)
		char* fileclw="hidracli.exe"; // Nombre del ejecutable
		char* lpathclw="disk://0:"+particion+"/windows/system";	 // Path local del ejecutable para el cliente hidra (Windows)
		char* lfileclw="hidracli.exe"; // // Nombre del ejecutable en la particin Windows
		images[0]=pathimg+"/"+fileimg;
		if(CACHEEXISTS && !OFFLINE) // Si existe cach
			CopyCache(images,Settings.CachePath,false,""); // Se asegura que el perfil software base est en la cach

		Synchronize(pathimg+"/"+fileimg,"disk://0:"+particion,"b"); // Restaura el perfil
		char* CadenaParche=TextoFileParche(Hostname,GrupoTrabajo); // Crea el texto con las ramas a parchear
		CreateTextFile("disk://0:"+particion+"/rembo.reg",CadenaParche); // Crea fichero que contiene este exto
		char* CadenaBat=TextoFileBat("rembo.reg"); // Crea sintaxis del comando regedit
		CreateTextFile("disk://0:"+particion+"/rembo.bat",CadenaBat); // Crea fichero ."bat" para parcheo

		// Crea cadena de parcheo para insertar al final del autoexec.bat
		char* cadenaautoexec="\r\n if not exist rembo.bat goto fin \r\n";
		cadenaautoexec+="call rembo.bat \r\n";
		cadenaautoexec+="del rembo.reg \r\n";
		cadenaautoexec+="del rembo.bat \r\n";
		cadenaautoexec+=":fin\r\n";

		char* FileAutx="disk://0:"+particion+"/autoexec.bat";
		char* contenidoautoexec;
		if (ExisteFichero(FileAutx)){	 // Comprueba si existe el fichero autoexec.bat
			contenidoautoexec=LoadTextFile(FileAutx);
			int pos;
			pos=0;
			pos=StrFind(contenidoautoexec,cadenaautoexec);
			if(pos<0){
				contenidoautoexec+=cadenaautoexec;
				RemoveFile(FileAutx);
				CreateTextFile(FileAutx,contenidoautoexec); // Crea autoexec definitivo
			}
		}
		CopyFile(pathclw+"/"+fileclw,lpathclw+"/"+lfileclw); // Copia el ejecutable hidra desde el servidor o la cach, al disco
	}
	return(wresul); 
	*/
		return(true);
}
//___________________________________________________________________________________________________
//	Funcin : TextoFileParche
//	Descripcin:
//		Crea una cadena con el contenido del fichero de parcheo para windows 98
//	Parmetros:
//		- Hostname: Nombre del  ordenador
//		- GrupoTrabajo: Grupo de trabajo del ordenador
//___________________________________________________________________________________________________
char* TextoFileParche(char* Hostname,char* GrupoTrabajo)
{

	char* cadena="";
	/*
	cadena="REGEDIT4"+INTRO;
	cadena+="[HKEY_LOCAL_MACHINE\\System\\CurrentControlSet\\Services\\VxD\\VNETSUP]"+INTRO;
	cadena+='"ComputerName"="'+Hostname+'"'+INTRO;
	cadena+='"Workgroup"="'+GrupoTrabajo+'"'+INTRO;
	cadena+="[HKEY_LOCAL_MACHINE\\System\\CurrentControlSet\\Control\\ComputerName\\ComputerName]"+INTRO;
	cadena+='"ComputerName"="'+Hostname+'"'+INTRO;
	cadena+="[HKEY_CURRENT_USER\\Software\\Microsoft\\Windows\\CurrentVersion\\Run]"+INTRO;
	cadena+='"hidracli"="C:\\\\WINDOWS\\\\SYSTEM\\\\hidracli.exe"'+INTRO;
	char* ipservidorhidra=Propiedades.servidorhidra;	// Direccin IP del servidor HIDRA
	char* puerto=Propiedades.puerto;
	cadena+="[HKEY_LOCAL_MACHINE\\SOFTWARE\\Alonsoft\\Hidra]"+INTRO;
	cadena+='"servidorhidra"="'+ipservidorhidra+'"'+INTRO;
	cadena+='"puerto"="'+puerto+'"'+INTRO;
	return(cadena);
	*/
	return(cadena);	
}
//___________________________________________________________________________________________________
//	Funcin : TextoFileBat
//	Descripcin:
//		Crea una cadena con la sintaxis del fichero ".bat" que parcherar el registro de Windows 98
//	Parmetros:
//		- filereg: Nombre del  fichero con las ramas del registro de Windows98 a parchear
//___________________________________________________________________________________________________
char* TextoFileBat(char* filereg)
{
	
	char* cadena="";
	/*
	cadena+='c:\\windows\\regedit  /L:c:\\windows\\system.dat /R:c:\\windows\\user.dat c:\\'+filereg;
	return(cadena);
	*/
	return(cadena);
}
//___________________________________________________________________________________________________
//	Funcin : Restaurar_WindowsNTFS
//	Descripcin:
//		Restaura la imagen de una particin  Windows 2000 o Windows XP 
//	Parmetros:
//		- filemasterboot: Fichero que contiene la imagen del master boot
//		- fileimg: Fichero que contendr el perfil software base de la imagen
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin en la cual se va a restaurar la imagen
//		- hostname: Nombre del  ordenador
//		- wdir: Vale WINNT  WINDOWS depende de si es un window 2K o XP
//___________________________________________________________________________________________________
int Restaurar_WindowsNTFS(char* filemasterboot,char* fileimg,char* pathimg,char* particion,char* hostname,char* wdir)
{
		return(Restaura_Imagen(fileimg,pathimg,particion,"RestaurarImagenNTFS"));	
}
//___________________________________________________________________________________________________
int Restaura_Imagen(char* fileimg,char* pathimg,char* particion,char* script)
{
	int herror;
	char *disco;
	char *parametros;
	
	disco=(char*)Buffer(250);
	sprintf(disco,"1"); // Siempre el disco 1
    
    // Notificación al repositoriode que se va a enviar por netcat la imagen
    SOCKET udpsock;
	TRAMA *trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
	if(trama== NULL){
			RaiseError(4,tbErrores[4],"Restaura_Imagen()");
			return(false);
	}		
	udpsock=UDPConnect(Propiedades.IPlocal); 
	if (udpsock == INVALID_SOCKET){ 
		RaiseError(2,tbErrores[2],"Restaura_Imagen()");
		return(false);
	}
	sprintf(trama->parametros,"nfn=EnviaPerfilSoftware\rnfp=/usr/local/hidra/imagenes/%s\riph=%s\r",fileimg,Propiedades.IPlocal);	// Nombre de la funcin a ejecutar en el servidor HIDRA 
	if(!envia_comandos(udpsock,trama,Propiedades.iprepo,Propiedades.puertorepo)){
		RaiseError(2,tbErrores[2],"Restaura_Imagen()");
		return(false);
	}
	trama=(TRAMA*)recibe_comandos(udpsock); // Recibe respuesta del puerto para netcat
	if(!trama){
		RaiseError(2,tbErrores[2],"Restaura_Imagen()");
		return(false);
	}		
	close(udpsock);
	INTROaFINCAD(trama->parametros);
	char *puertonetcat=toma_parametro("pnt",trama->parametros); // Toma nombre puerto netcat
	// FIn sincronización con repositorio
	
	// Uso del ntfsclone 
    parametros=(char*)Buffer(LONGITUD_SCRIPTSALIDA);
	//sprintf(cmdshell,"./scripts/CrearPerfilSoftware");
	//sprintf(parametros," %s %s %s %s","0",particion,Propiedades.iprepo,puertonetcat);
	//herror=ejecutarscript ( cmdshell,parametros,NULL);
	
	sprintf(cmdshell,"%s/%s %s %s %s",HIDRASCRIPTS,script,disco,particion,puertonetcat);
	herror=ExecShell(cmdshell,NULL);
	if(!herror){
	    RaiseError(4,tbErrores[4],"Restaura_Imagen()");	 // Se ha producido algun error
		return(false); 
    }
    return(true);
}
//__________________________________________________________________________________________________
//
// Guarda la tabla de particiones en una variable de cadena que es devuelta por la funcin
// El formato ser una cadena con las particiones primarias y lgicas separadas por un caracter INTRO
//___________________________________________________________________________________________________
char* TomaTablaParticiones()
{
	/*
	var parts;
	var logparts;
	char* ppStr="";
	char* lpStr="";
	char* tipopart ;
	char* tamapart;
	
	parts = StrParse(GetPrimaryPartitions(0)," "); // Guarda tabla de particiones
	for(int i = 0; i < sizeof(parts); i++) {
		var dualparts = StrParse(parts[i],":");
		tipopart = dualparts[0];
		tamapart = dualparts[1];
		ppStr+=tipopart+":"+tamapart;
		if((i+1)<sizeof(parts))
			ppStr+=" ";
	}
	if(FindExtendedPartition(0) > 0) {
		logparts = StrParse(GetLogicalPartitions(0)," ");
		for(int i = 0; i < sizeof(logparts); i++) {
			var dualparts = StrParse(logparts[i],":");
			tipopart = dualparts[0];
			tamapart = dualparts[1];
			lpStr+=tipopart+":"+tamapart;
			if((i+1)<sizeof(logparts))
				lpStr+=" ";
		}
	}
	return((char*)ppStr+INTRO+lpStr);
	*/
		return(char*)NULL;
}
//__________________________________________________________________________________________________
//	Funcin : PoneTablaParticiones
//	Descripcin:
//		Modifica la tabla de particiones
//	Parmetros:
//		- tbpart: Cadena con la sintaxis de rembo para particiones primarias y lgicas separadas ambas por un caracter INTRO;
//___________________________________________________________________________________________________
int PoneTablaParticiones(char* tbpart)
{
	/*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="PoneTablaParticiones";
		exc.resume=true;
		return exc;
	}
	with(BasicErrorHandler)
	try {
		char* PrimaryPartitions="";
		char* LogicalPartitions="";
		var auxsplit=StrParse(tbpart,INTRO); // Separa los parametros
		if(sizeof(auxsplit)>0){
			PrimaryPartitions=auxsplit[0];
			if(sizeof(auxsplit)>1)
				LogicalPartitions=auxsplit[1];
			wresul=Particionar(PrimaryPartitions,LogicalPartitions);
		}
	}
	return(wresul);
	*/
		return(true);
}
//__________________________________________________________________________________________________
//
//	Modifica el registro de Windows 2000  Windows XP
//__________________________________________________________________________________________________
int ParcheaRegistro()
{
	/*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="ParcheaRegistro";
		exc.resume=true;
		if (ExisteFichero("reg://rama")) CloseRegistry("rama");
		return exc;
	}
	with(BasicErrorHandler)
	try {
		OpenRegistry("rama",StrChDir(NTLocation,NTSystemRoot+"/system32/config/software"));
		if(!ExisteFichero("reg://rama/Alonsoft")){ // Creacin carpeta Alonsoft
			CreateDir("reg://rama/Alonsoft");
			if(!ExisteFichero("reg://rama/Alonsoft/Hidra")){ // Creacin carpeta Hidra
				CreateDir("reg://rama/Alonsoft/Hidra");
				CreateUnicodeFile("reg://rama/Alonsoft/Hidra/servidorhidra.unicode",Propiedades.servidorhidra);
				CreateUnicodeFile("reg://rama/Alonsoft/Hidra/puerto.unicode",Propiedades.puerto);
				char* cadenaexe="C:\\\\"+NTSystemRoot+"\\\\SYSTEM32\\\\hidracli.exe";
				CreateUnicodeFile("reg://rama/Microsoft/Windows/CurrentVersion/Run/hidracli.unicode",cadenaexe);
			}
		}
		CloseRegistry("rama");
		CopyFile(HIDRACHERAIZ+"/hidracli.exe",StrChDir(NTLocation,NTSystemRoot+"/system32"));
	}
	return(wresul);
	*/
		return(true);
}
//__________________________________________________________________________________________________
//	Funcin : RestaurarIncrementales
//	Descripcin:
//		Restaura un software incremental
//	Parmetros:
//		- particion: Particin en la que se restaura el software incremental
//		- wdir: Vale WINNT  WINDOWS depende del  sistema S.O. que compone el perfil software base
//		- icr: Cadena con los identificadores de los distintos incrementales (separadas por coma)
//		- ifs: Identificador del perfil software base de la imagen con la cual combina el software incremental 
//		- ifh: Identificador del perfil hardware del ordenador
//		- nem: Nemonico del S.O. contenido en el perfil software base
//___________________________________________________________________________________________________
int RestaurarIncrementales(char* particion,char* wdir,char* icr,char* ifs,char* ifh,char* nem)
{
	/*
	int wresul=0;
	var BasicErrorHandler(var exc){
		wresul=exc.errno;
		modulo="RestaurarIncrementales";
		exc.resume=true;
		return exc;
	}
	with(BasicErrorHandler)
	try {
		char* images[];
		NTSetPartition((int)particion);
		NTLocation = "disk://0:"+particion; // Particin donde est instalado el S.O. Windows
		NTSystemRoot = wdir; // Nombre del directorio raiz donde est dicho sistema
		NTDetect();
		var auxicr = StrParse(icr,";");
		for(int i = 0; i < sizeof(auxicr); i++){
			char* pathincremental=HIDRACHEIMAGENES+"/"+nem+"/INC/INC"+auxicr[i]+"_PS"+ifs+"_PH"+ifh;
			ApplyDiff(pathincremental,"disk://0:"+particion,true);
		}
	}
	return(wresul); // Se ha producido algún error
	*/
		return(true);
}
//__________________________________________________________________________________________________
//	Funcin : Restaurar_Linux
//	Descripcin:
//		Restaura la imagen de una particin Linux 
//	Parmetros:
//		- fileimg: Fichero que contendr el perfil software
//		- pathimg: Camino de ese fichero en el servidor o la cach
//		- particion: Particin de la cual se va a restaurar la imagen
//___________________________________________________________________________________________________
int Restaurar_Linux(char* fileimg,char* pathimg,char* particion)
{
	return(Restaura_Imagen(fileimg,pathimg,particion,"RestaurarImagenEXT3"));	
}
//__________________________________________________________________________________________________
//
// Captura la última terna de la direccin IP del ordenador
//__________________________________________________________________________________________________
char* Uternaip(void)
{

	char* utip="";
		/*
	var wip = StrParse(NetInfo.IPAddress,".");
	utip=wip[3];
*/
	return(utip);

}
//__________________________________________________________________________________________________
//
//	Muestra la ventana  para introducir los datos de acceso de un operador de aula o administrador de Centro al
//	menú privado. Si dicho usuario est registrado se mostrar este menú.
//__________________________________________________________________________________________________
BOOL Autentificar(void)
{
	/*
	var win = OpenWindow("authwin",35,30,100,100);
	win.alwaysOnTop = true;
	acceso=false;
	SaveText("<title>Autentificacion de Usuario</title>"
						"<table>"
							"<tr><td>&nbsp;<td><td><td>&nbsp;"
							"<tr><td><td><img src="+HIDRACHERAIZ+"/iconos/lock64.pcx>"
							"<td text=blue face='sans-serif'><br>Acceso para administradores"
							"<tr><td><tr><td><td>Usuario:&nbsp;<td><input name=username size=16>"
							"<tr><td><tr><td><td>Password:&nbsp;<td><input name=password size=16 type=password>"
							"<tr><td><td colspan=2><br><br><center>"
							"<button onmouseup='acceso=Autentica(username,password);CloseWindow(\"authwin\");'>"
							"&nbsp;Aceptar&nbsp;</button>&nbsp;"
							"<button onmouseup='CloseWindow(\"authwin\");'>"
							"&nbsp;Cancelar&nbsp;</button>"
							"</center></table>",
							"display://authwin/SELF");
	AutoResizeWindow("authwin",true,true);
	while(ExisteFichero("display://authwin")) 	delay(50);	 // Espera medio segundo antes de comprobar la existencia de la ventana
	return(acceso);
	*/
		return(true);
}
//__________________________________________________________________________________________________
//
//	Comprueba si un usuario est registrado como operador del aula  administrador del Centro. El sistema busca un
//	fichero con el nombre introducido como usuario y cuyo contenido ser la clave de acceso. Est clave esta
//	encriptada y ha sido aportada por el administrador de Centro desde el Centro web de adminstracin de HIDRA.
//	En el caso de no encontrar dicho fichero le añadirà el identificador del aula al que est adscrito el ordenador
//	y volver a repetir el proceso de autenticacin con el nuevo nombre de fichero.. Si en nigún caso se encuentra dicho
// fichero, el usuario NO estar  autorizado para acceder al menú privado.
//__________________________________________________________________________________________________
BOOL Autentica(char* username, char* password)
{
	/*
	char* uname;
	char* pwd;
	char* pasguor;
	
	uname = username.value;
	pwd   = password.value;
	var bin;
	if(uname=="") return(false);	 // No se introdujo usuario
	char* FileOpe=HIDRACHERAIZ+"/usuarios/"+uname+"-"+Propiedades.idaula;
	char* FileAdm=HIDRACHERAIZ+"/usuarios/"+uname;
	if (ExisteFichero(FileOpe))	 // Comprueba si es operador de aula
		pasguor=LoadTextFile(FileOpe);
	else{
		if (ExisteFichero(FileAdm))	// Comprueba si es administrador del Centro
			pasguor=LoadTextFile(FileAdm);
		else
			return(false);
	}
	bin=BinFromStr(pasguor);
	Desencriptar(bin);
	pasguor=BinToStr(bin);
	var auxsplit=StrParse(pasguor,"[\n\r\t]");	// Ignora caracteres especiales detrs de la clave
	if(sizeof(auxsplit)>0){
		if(auxsplit[0]==pwd)	
			return(true);
 }
 return(false);
 */
 	return(true);
}
//__________________________________________________________________________________________________
//
// Mensaje personalizado (Redefine el aportado por la libreria util del SDK de Rembo)
//__________________________________________________________________________________________________
BOOL OpenMessage(char* name, char* msg)
{
	/*
	var win = OpenWindow(name,28,40,82,50);
	win.widgets = "<style>B {font-weight: normal; color: #29594A}</style><body bgcolor=#ADCF8C><center><br><i><b>"+msg;
	return win;
	*/
	Log(msg);
	return(true);
}
//_________________________________________________________________________________________________
//	Funcin: Log
//
//	Descripcin:
// 		Visualiza un mensaje por pantalla
//	Parmetros:
//		- msg: 	Contenido del mensaje
//___________________________________________________________________________________________________
void Log(char* msg)
{
	time_t rawtime;
	struct tm * timeinfo;
	char LogMsg[250];
		
	time ( &rawtime );
	timeinfo = gmtime(&rawtime);

	FILE*	FLog;
	FLog=fopen("hidrac.log","at");
	if(FLog!=NULL)
		fprintf (FLog,"%02d/%02d/%d %02d:%02d ***%s\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	fclose(FLog);	
	
	sprintf(LogMsg,"echo %02d/%02d/%d %02d:%02d ***\"%s\"\n",timeinfo->tm_mday,timeinfo->tm_mon+1,timeinfo->tm_year+1900,timeinfo->tm_hour,timeinfo->tm_min,msg);
	system(LogMsg);
}
//_________________________________________________________________________________________________
//	Funcin: SacaMensaje
//
//	Descripcin:
// 		Visualiza un mensaje por pantalla en una ventana
//	Parmetros:
//		- w: 	Nombre de la ventana
//		- msg: 	Mensaje a mostrar
//		- t: 	Tiempo en milisegundos que se mostrar
//___________________________________________________________________________________________________
BOOL SacaMensaje(char* w,char *msg,int t)
{
		/*
	OpenMessage(nven,msg);
	(tiempo);
	CloseWindow(nven);
	*/
	Log(msg);
	return(true);
}
//_________________________________________________________________________________________________
//	Funcin: RaiseError
//
//	Descripcin:
// 		Genera una excepcin
//	Parmetros:
//		- h: 	Cdigo de error
//		- m:	Descripcin del mensaje de error    
//___________________________________________________________________________________________________
void RaiseError(int herror,char* msg, char*modulo)
{
	e.herror=herror;
	strcpy(e.msg,msg);
	strcpy(e.modulo,modulo);	
	LogError(modulo,e);
}
//_________________________________________________________________________________________________
//	Funcin: LogError
//
//	Descripcin:
// 		Visualiza por pantalla un error producido
//	Parmetros:
//		- md: 	Mdulo o funcin donde se ha producido el error
//		- exc: 	Estructura de excepcin de errores con la informacin del error
//___________________________________________________________________________________________________
void LogError(char* modulo, struct excepcion exc)
{
		char LogMsg[250];
		
		sprintf(LogMsg,"Error.-(%s) en modulo %s",exc.msg,modulo);
		Log(LogMsg);	
}
//_________________________________________________________________________________________________
//	Funcin: Buffer
//
//	Descripcin:
// 		Reserva memoria  
//	Parmetros:
//		- l: 	Longitud en bytes de la reserva
//	Devuelve:
//		Un puntero a la memoria reservada
//___________________________________________________________________________________________________
char * Buffer(int l)
{
	char *buf;
	buf=(char*)malloc(l);
	if(buf==NULL)
		RaiseError(1,tbErrores[1],"Buffer()");
	memset(buf,0,l);
	return(buf);
}
//_________________________________________________________________________________________________
//	Funcin: Nemonico
//
//	Descripcin:
// 		Identifca nemnicos de los sistemas operativos 
//	Parmetros:
//		- nem: 	Nemnico del S.O.
//	Devuelve:
//		El cdigo del nemnico del S.O.
//___________________________________________________________________________________________________
int Nemonico(char* nem)
{
	if(strcmp(nem,"MsDos")==0) 
		return(MsDos);

	if(strcmp(nem,"Win98")==0)
		return(Win98);

	if(strcmp(nem,"Win2K")==0)
		return(Win2K);
	
	if(strcmp(nem,"WinXP")==0) 
		return( WinXP);
	
	if(strcmp(nem,"Linux")==0)
		return(Linux);

	return(0);
	
}
//________________________________________________________________________________________________________
//	Funcin: ExecShell
//
//	Descripcin:
//		Ejecuta cdigo script
// ________________________________________________________________________________________________________
int ExecShell(char* cod,char *salida)
{
	int herror;
   	
   	herror=system(cod);
	if(herror){
	    RaiseError(4,tbErrores[4],"ExecShell()");	 // Se ha producido algun error
		return(false); 
    }
   	return(true);

/*
	FILE* f;
	long lSize;
	int herror;
	
	sprintf(filecmdshell,"%s","/usr/local/hidra/scripts/_hidrascript_");
	f = fopen(filecmdshell,"wt");
	lSize=strlen(cod);
	fwrite(cod,1,lSize,f); 	// Lee el contenido del fichero
	fclose(f);

	sprintf(cmdshell,"chmod +x %s",filecmdshell);
	herror=system(cmdshell);
	if(herror){
	    RaiseError(4,tbErrores[4],"ExecShell()");	 // Se ha producido algun error
		return(false); 
    }	
    
   	herror=system(filecmdshell);
	//herror=execl ("/usr/local/hidra/scripts/_hidrascript_","/usr/local/hidra/scripts/_hidrascript_",NULL);
	if(herror){
	    RaiseError(4,tbErrores[4],"ExecShell()");	 // Se ha producido algun error
		return(false); 
    }
   	return(true);
*/

}
// ________________________________________________________________________________________________________
// Funcin: TCPConnect
//
//		Descripcin: 
//			Crea un socket y lo conecta a un servidor
//		Parmetros:
//			- ips : La direccin IP del servidor
//			- port : Puerto para la comunicacin
//		Devuelve:
//			- El socket o nulo dependiendo de si se ha establecido la comunicacin
// ________________________________________________________________________________________________________
SOCKET TCPConnect(char *ips,char* wpuerto)
{
    struct sockaddr_in server;
	SOCKET s;
	puerto=atoi(wpuerto);
	// Crea el socket y se intenta conectar
	s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (s == INVALID_SOCKET){
		return (INVALID_SOCKET);
	}
	server.sin_family = AF_INET;
	server.sin_port = htons((short)puerto);
	server.sin_addr.s_addr = inet_addr(ips);

	if (connect(s, (struct sockaddr *)&server, sizeof(server)) == INVALID_SOCKET)
		return (INVALID_SOCKET);
		
	return(s); // Conectado
}
// ________________________________________________________________________________________________________
// Funcin: TCPClose
//
//		Descripcin: 
//			Cierra una conexin establecido a travs de un socket 
//		Parmetros:
//			- s : El socket que implementa la conexin
// ________________________________________________________________________________________________________
void TCPClose(SOCKET s){
	close(s);
}
// ________________________________________________________________________________________________________
// Funcin: CreateTextFile
//
//		Descripcin: 
//			Lee el contenido de un fichero de texto
//		Parmetros:
//			- nomfile : Nombre del fichero
//		Devuelve:
//			- La longitud del contenido escrito
// ________________________________________________________________________________________________________
long CreateTextFile(char *nomfile,char *texto)
{
	long lSize;
	FILE *f;
	f = fopen(nomfile,"wt");
	if(!f){ // El fichero no existe
		RaiseError(3,tbErrores[3],"CreateTextFile()");
		return(0);
	}
	lSize=strlen(texto);
	fwrite(texto,1,lSize,f); 	// Lee el contenido del fichero
	fclose(f);
	return(lSize);
}
//________________________________________________________________________________________________________
// Funcin: ExisteFichero
//
//		Descripcin: 
//			Lee el contenido de un fichero de texto
//		Parmetros:
//			- nomfile : Nombre del fichero
//		Devuelve:
//			- Un puntero tipo caracter que contiene el texto leido
// ________________________________________________________________________________________________________
int ExisteFichero(char *nomfile)
{
	if(OFFLINE || !ADMINISTRADO){	// Modo offline o modo no administrado
		FILE *f;
		BOOL swf;
		f = fopen(nomfile,"rt");
		swf=(f!=NULL);
		if(f)	fclose(f);
		return(swf);
	}

	SOCKET udpsock;
	TRAMA *trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
	if(trama){
		sprintf(trama->parametros,"nfn=ExisteFichero\rnfl=%s\r",nomfile);	// Nombre de la funcin a ejecutar en el servidor HIDRA 
		udpsock=UDPConnect(Propiedades.IPlocal); 
		if (udpsock == INVALID_SOCKET){ 
			RaiseError(2,tbErrores[2],"ExisteFichero()");
			return(false);
		}
		if(envia_comandos(udpsock,trama,Propiedades.iprepo,Propiedades.puertorepo)){
			trama=(TRAMA*)recibe_comandos(udpsock);
			if(trama){
				close(udpsock);
				int ret=strcmp(gestion_comandos(trama),"1");
				return(ret==0);
			}
		}
		close(udpsock);
	}
	RaiseError(1,tbErrores[1],"ExisteFichero()");
	return(false);	
}
//________________________________________________________________________________________________________
// Funcin: RemoveFile
//
//		Descripcin: 
//			Elimina un fichero
//		Parmetros:
//			- nomfile : Nombre del fichero
// ________________________________________________________________________________________________________
BOOL RemoveFile(char *nomfile)
{
	if(OFFLINE || !ADMINISTRADO){	// Modo offline o modo no administrado
		int res;
		char cmdshell[250];
		sprintf(cmdshell,"rm -f %s",nomfile);
		res=system(cmdshell);
		return(res);
	}
	SOCKET udpsock;
	TRAMA *trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
	if(trama){
		sprintf(trama->parametros,"nfn=EliminaFichero\rnfl=%s\r",nomfile);	// Nombre de la funcin a ejecutar en el servidor HIDRA 
		udpsock=UDPConnect(Propiedades.IPlocal); 
		if (udpsock == INVALID_SOCKET){ 
			RaiseError(2,tbErrores[2],"RemoveFile()");
			return(false);
		}
		if(envia_comandos(udpsock,trama,Propiedades.iprepo,Propiedades.puertorepo)){
			trama=(TRAMA*)recibe_comandos(udpsock);
			if(trama){
				close(udpsock);
				int ret=strcmp(gestion_comandos(trama),"1");
				return(ret==0);
			}
		}
		close(udpsock);
	}
	RaiseError(1,tbErrores[1],"RemoveFile()");
	return(false);
}
// ________________________________________________________________________________________________________
// Funcin: LoadTextFile
//
//		Descripcin: 
//			Lee el contenido de un fichero de texto
//		Parmetros:
//			- nomfile : Nombre del fichero
//		Devuelve:
//			- Un puntero tipo caracter que contiene el texto leido
// ________________________________________________________________________________________________________
char * LoadTextFile(char *nomfile)
{
	if(OFFLINE || !ADMINISTRADO){	// Modo offline o modo no administrado
		char *texto;
		long lSize;
		FILE *f;
		f = fopen(nomfile,"rt");
		if(!f){ // El fichero no existe
			RaiseError(3,tbErrores[3],"LoadTextFile()");
			return(false);
		}
		fseek(f,0,SEEK_END);
		lSize=ftell(f);
		texto=Buffer(lSize);				// Reserva memoria para buffer de lectura
		if(!texto){ 
			RaiseError(5,tbErrores[5],"LoadTextFile()");
			return(false);
		}
		rewind (f);								// Coloca al principio el puntero de lectura
		fread (texto,1,lSize,f); 	// Lee el contenido del fichero
		fclose(f);
		return(texto);
	}
	
	SOCKET udpsock;
	TRAMA *trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
	if(trama){
		sprintf(trama->parametros,"nfn=LeeFicheroTexto\rnfl=%s\r",nomfile);	// Nombre de la funcin a ejecutar en el servidor HIDRA 
		udpsock=UDPConnect(Propiedades.IPlocal); 
		if (udpsock == INVALID_SOCKET){ 
			RaiseError(2,tbErrores[2],"LoadTextFile()");
			return(false);
		}
		if(envia_comandos(udpsock,trama,Propiedades.iprepo,Propiedades.puertorepo)){
			trama=(TRAMA*)recibe_comandos(udpsock);
			if(trama){
				close(udpsock);
				return(gestion_comandos(trama));
			}
		}
		close(udpsock);
	}
	RaiseError(1,tbErrores[1],"LoadTextFile()");
	return(false);
}
//________________________________________________________________________________________________________
// Funcin: SetCachePartitionSize
//
//		Descripcin: 
//			Dimensiona la cach del cliente a un determinado valor
//		Parmetros:
//			- t : Tamao de la cach
// ________________________________________________________________________________________________________
int SetCachePartitionSize(int t)
{
	return(true);
}
//________________________________________________________________________________________________________
// Funcin: CopyFile
//
//		Descripcin: 
//			Copia un fichero de un lugar a otro
//		Parmetros:
//			- fsrc : Path del fichero origen
//			- fdes : Path del fichero destino
// ________________________________________________________________________________________________________
int CopyFile(char*fsrc,char* fdes)
{
	return(true);
}
// ________________________________________________________________________________________________________
// Funcin: toma_parametro
// 
//		Descripcin:
// 			Esta funcin devuelve el valor de un parametro incluido en la trama.
// 			El formato del protocolo es: "nombre_parametro=valor_parametro"
// 		Parmetros:
// 			- nombre_parametro: Es el nombre del parnetro a recuperar
// 			- parametros: Es la matriz que contiene todos los parnetros
// ________________________________________________________________________________________________________
char * toma_parametro(char* nombre_parametro,char *parametros)
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
// Funcin: INTROaFINCAD
//
//		Descripcin:
// 			Cambia INTROS por caracteres fin de cadena ('\0') en una cadena
//		Parametros:
//				- parametros : La cadena a explorar
// ________________________________________________________________________________________________________
void INTROaFINCAD(char* parametros)
{
	int lon,i;
	lon=strlen(parametros);
	for(i=0;i<lon;i++){
		if(parametros[i]=='\r') parametros[i]='\0';
	}
}
// ________________________________________________________________________________________________________
// Funcinn: FINCADaINTRO
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
		if(*i=='\r') *i='\0';
	}
}
// ________________________________________________________________________________________________________
// Funcin: split_parametros
//
//		Descripcin:
//			Esta funcin trocea una cadena segn un carnter delimitador, Devuelve el nmero de trozos
// 		Parnetros:
// 			- trozos: Array de punteros a cadenas
// 			- cadena: Cadena a trocear
// 			- ch: Carcter delimitador
// ________________________________________________________________________________________________________
int split_parametros(char **trozos,char *cadena, char * ch){
	int w=0;
	char* token;

	token= strtok(cadena,ch); // Trocea segn delimitador
	while( token != NULL ){
		trozos[w++]=token;
		token=strtok(NULL,ch); // Siguiente token
	}
	trozos[w++]=token; 
	return(w-1); // Devuelve el numero de trozos
}
//___________________________________________________________________________________________________
//
// Gestión de tramas
//___________________________________________________________________________________________________
int gestion_tramas(TRAMA *trama)
{
	TRAMA *nwtrama=NULL;
	int res;
	char *nombrefuncion;
	INTROaFINCAD(trama->parametros);
	nombrefuncion=toma_parametro("nfn",trama->parametros); 
	nwtrama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama	devuelta		
	if(!nwtrama){
		RaiseError(1,tbErrores[1],"gestion_tramas()");
		return(false);
	}
		
	res=strcmp(nombrefuncion,"Apagar");
	if(res==0)
		return(Apagar(trama,nwtrama));

	res=strcmp(nombrefuncion,"Arrancar");
	if(res==0)
		return(Arrancar(trama,nwtrama));
			
	res=strcmp(nombrefuncion,"Reiniciar");
	if(res==0)
		return(Reiniciar(trama,nwtrama));
			
	res=strcmp(nombrefuncion,"RESPUESTA_inclusion_cliRMB");
	if(res==0)
		return(RESPUESTA_inclusion_cliRMB(trama));
			
	res=strcmp(nombrefuncion,"RemboOffline");
	if(res==0)
		return(RemboOffline(trama,nwtrama));
			
	res=strcmp(nombrefuncion,"Actualizar");
	if(res==0)
		return(Actualizar());		
		
	res=strcmp(nombrefuncion,"NoComandosPtes");
	if(res==0)
		return(NoComandosPtes());
			
	res=strcmp(nombrefuncion,"Cortesia");
	if(res==0)
		return(Cortesia());			
					
	res=strcmp(nombrefuncion,"EjecutarScript");
	if(res==0)
		return(EjecutarScript(trama,nwtrama));			
			
	res=strcmp(nombrefuncion,"CrearPerfilSoftware");
	if(res==0)
		return(CrearPerfilSoftware(trama,nwtrama));			

	res=strcmp(nombrefuncion,"CrearSoftwareIncremental");
	if(res==0)
		return(CrearSoftwareIncremental(trama,nwtrama));	

	res=strcmp(nombrefuncion,"RestaurarImagen");
	if(res==0)
		return(RestaurarImagen(trama,nwtrama));			
	
	res=strcmp(nombrefuncion,"TomaConfiguracion");
	if(res==0)
		return(TomaConfiguracion(trama,nwtrama));		
		
	res=strcmp(nombrefuncion,"InventarioHardware");
	if(res==0)
		return(InventarioHardware(trama,nwtrama));		
		
	res=strcmp(nombrefuncion,"ParticionaryFormatear");
	if(res==0)
		return(ParticionaryFormatear(trama,nwtrama));				
			
	RaiseError(4,tbErrores[4],"gestion_tramas()");
	return(false);	
}

//___________________________________________________________________________________________________
//
// Gestion de tramas
//___________________________________________________________________________________________________
char * gestion_comandos(TRAMA *trama)
{
	TRAMA *nwtrama=NULL;
	int ret;
	char *nombrefuncion,*res;
	INTROaFINCAD(trama->parametros);
	nombrefuncion=toma_parametro("nfn",trama->parametros); 
	nwtrama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama	devuelta		
	if(!nwtrama){
		RaiseError(1,tbErrores[1],"gestion_tramas()");
		return(false);
	}
		
	ret=strcmp(nombrefuncion,"Respuesta_ExisteFichero");
	if(ret==0){
		res=toma_parametro("res",trama->parametros); 
		return(res);
	}
			
	ret=strcmp(nombrefuncion,"Respuesta_EliminaFichero");
	if(ret==0){
		res=toma_parametro("res",trama->parametros); 
		return(res);
	}
		
	ret=strcmp(nombrefuncion,"Respuesta_LeeFicheroTexto");
	if(ret==0){
		res=toma_parametro("res",trama->parametros); 
		return(res);
	}
	RaiseError(4,tbErrores[4],"gestion_tramas()");
	return(false);		
}
//___________________________________________________________________________________________________
//
//  Enva tramas al servidor HIDRA 
//___________________________________________________________________________________________________
int envia_tramas(SOCKET s,TRAMA *trama)
{
	trama->arroba='@';										// cabecera de la trama
	strcpy(trama->identificador,"JMMLCAMDJ");	// identificador de la trama
	trama->ejecutor='1';										// ejecutor de la trama 1=el servidor hidra  2=el cliente hidra
				
	int lon;
	// Compone la trama
	lon=strlen(trama->parametros); 
	lon+=sprintf(trama->parametros+lon,"iph=%s\r",Propiedades.IPlocal);	// Ip del ordenador
	lon+=sprintf(trama->parametros+lon,"ido=%s\r",Propiedades.idordenador);	// identificador del ordenador
	return(TCPWrite(s,trama));
}
//___________________________________________________________________________________________________
//
// Recibe tramas desde el servidor HIDRA
//___________________________________________________________________________________________________
int recibe_tramas(SOCKET s,TRAMA *trama)
{
	return(TCPRead(s,trama));
}
// ________________________________________________________________________________________________________
//
// Funcin: TCPWrite
//
//		 Descripcin?:
//			Esta Funcin envia una trama por la red (TCP) 
//		Parametros:
//				- sock : El socket del host al que se dirige la trama
//				- trama: El contenido de la trama
// ________________________________________________________________________________________________________
int TCPWrite(SOCKET s,TRAMA* trama)
{
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
// ________________________________________________________________________________________________________
// Funcin: TCPRead
//
//		Descripcin:
//			Esta funcin recibe una trama por la red (TCP)
//		Parametros:
//			- sock : El socket del cliente
//			- trama: El buffer para recibir la trama
// ________________________________________________________________________________________________________
int TCPRead(SOCKET s,TRAMA* trama)
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
//____________________________________________________________________________________________________
//
// Recupera la configuracin de particiones del ordenador 
//____________________________________________________________________________________________________
char * LeeConfiguracion()
{
	int herror;
	char *disco;	
	char *cadenaparticiones;
	char *nomso;
	
	disco=(char*)Buffer(3);
	sprintf(disco,"1");
	cadenaparticiones=(char*)Buffer(LONGITUD_SCRIPTSALIDA);
	sprintf(cmdshell,"%s/ListarParticionesPrimarias",HIDRASCRIPTS);	
	herror=ejecutarscript (cmdshell,disco,cadenaparticiones);
	if(herror){
	    RaiseError(4,tbErrores[4],"LeeConfiguracion()");	 // Se ha producido algun error
		return(false); 
    }
	struct s_Particiones *tbcfg[MAXPARTICIONES];
	char *duplasparticiones[MAXPARTICIONES],*duplaparticion[2];
	
	int iPar=split_parametros(duplasparticiones,cadenaparticiones," ");	// Caracter separatorio de los elementos de un item
	int i,j;
	for( i = 0; i<iPar; i++){
		split_parametros(duplaparticion,duplasparticiones[i],":");
		tbcfg[i]=(struct s_Particiones*)Buffer(sizeof(struct s_Particiones)); // Toma espacio para tabla de configuraciones
		strcpy(tbcfg[i]->tipopart,duplaparticion[0]); // Tipo de particin
		strcpy(tbcfg[i]->tamapart,duplaparticion[1]); // Tamao de particin
		sprintf(tbcfg[i]->numpart,"%d",i+1); // Nmero de particin
		
		for(j=0;j<ntiposo;j++){
			if(strcmp(tiposos[j].tipopart,duplaparticion[0])==0){
				nomso=TomaNomSO(disco,i+1);
				if(nomso!=NULL){ // Averigua qué sistema operativo está instalado en la partición
					strcpy(tbcfg[i]->tiposo,tiposos[j].tiposo); // Nombre S.O.
					strcpy(tbcfg[i]->nombreso,nomso); // Nombre completo S.O.
				}
				else{
					strcpy(tbcfg[i]->tiposo,""); // Nombre S.O.
					strcpy(tbcfg[i]->nombreso,""); // Nombre completo S.O.
				}
				break;
			}
		}
	}
	char *cfg=Buffer(LONGITUD_CONFIGURACION);
	if(!cfg){
		RaiseError(0,tbErrores[0],"LeeConfiguracion()");
		return(false);
	}
	int lon=0;
	for( i = 0; i<iPar; i++){
		lon+=sprintf(cfg+lon,"@cfg\n");
		lon+=sprintf(cfg+lon,"tiposo=%s\n",tbcfg[i]->tiposo);	
		lon+=sprintf(cfg+lon,"tipopart=%s\n",tbcfg[i]->tipopart);
		lon+=sprintf(cfg+lon,"tamapart=%s\n",tbcfg[i]->tamapart);
		lon+=sprintf(cfg+lon,"numpart=%s\n",tbcfg[i]->numpart);		
		lon+=sprintf(cfg+lon,"nombreso=%s\t",tbcfg[i]->nombreso);
	}
	return(cfg);
}
//____________________________________________________________________________________________________
//
// Recupera la configuracin de hardware del ordenador 
//____________________________________________________________________________________________________
char* TomaNomSO(char*disco,int particion)
{
	int herror;
	char *parametros=(char*)Buffer(8);
	char *infosopar=(char*)Buffer(LONGITUD_SCRIPTSALIDA); // Información del S.O. de la partición
	
	sprintf(parametros,"%s %d",disco,particion);
	sprintf(cmdshell,"%s/TipoSO",HIDRASCRIPTS);	
	herror=ejecutarscript (cmdshell,parametros,infosopar);
	if(herror){
	    RaiseError(4,tbErrores[4],"TomaNomSO()");	 // Se ha producido algun error
		return(NULL); 
    }
    if(strlen(infosopar)==0) return(NULL); // NO Existe S.O. en la partición
	   	
   	INTROaFINCAD(infosopar);
   	return(toma_parametro("Vso",infosopar));		
}
//____________________________________________________________________________________________________
//
// Recupera la configuracin de hardware del ordenador 
//____________________________________________________________________________________________________
char * LeeHardware()
{
	int herror;
	char *cadenaharwares;
	
	cadenaharwares=(char*)Buffer(LONGITUD_SCRIPTSALIDA);
	//sprintf(cadenaharwares,"cpu=Amd 1200\nmem=20000\nnet=0001:0002\nvga=AAAA:BBBB");
	// return(cadenaharwares);
		
	sprintf(cmdshell,"%s/Inventario",HIDRASCRIPTS);
	herror=ejecutarscript ( cmdshell,NULL,cadenaharwares);
	if(herror){
	    RaiseError(4,tbErrores[4],"LeeHardware()");	 // Se ha producido algun error
		return(NULL); 
    }
    return(cadenaharwares);
}
//___________________________________________________________________________________________________
//
//  Recupera los items del menu y los va a la cach para ejecutarlos posteriormente sin peticin previa 
//___________________________________________________________________________________________________
int RecuperaItems()
{
	Log(" Recuperando Items del Menu RecuperaItems()");
	
	TRAMA *trama;
	int lon;		
	trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama			
	if(!trama){
		RaiseError(1,tbErrores[1],"RecuperaItems()");
		return(false);		
	}
	int i;
	for( i = 0; i<contitems; i++){
		sprintf(fileitem,"%s/menus/fileitem_%s",HIDRACHERAIZ,tbMenu[i].idaccionmenu);	// Nombre del fichero con los parmetros del  item
		if(!ExisteFichero(fileitem) && strcmp(tbMenu[i].tipoaccion,"0")==0){	 // Si no existe el fichero hace una peticin al servidor HIDRA
			// Compone la trama
			lon=sprintf(trama->parametros,"nfn=RecuperaItem\r");	// Nombre de la funcin a ejecutar en el servidor HIDRA 				
			lon+=sprintf(trama->parametros+lon,"ida=%s\r",tbMenu[i].idaccionmenu);	// Identificador de la accin a ejecutar por el item	
			if(Abre_conexion()){
				envia_tramas(sock,trama);
				recibe_tramas(sock,trama);
				Cierra_conexion();
				CreateTextFile(fileitem,(char*)trama); // ... crea fichero con los parmetros del  item
			}
		}
	}
	return(true);				
}
//===================================================================================================



//_________________________________________________________________________________________________
// Funcin: Abre_conexion
//
//		Descripcin: 
//			Abre la conexin entre el cliente y el servidor HIDRA
//___________________________________________________________________________________________________
int Abre_conexion()
{
	Log(" Abriendo conexion con el Servidor Hidra");
	if(OFFLINE || !ADMINISTRADO) return(true);	// Modo offline o modo no administrado
	BOOL swloop=true;
	int vez=0;				
	while(swloop){			
		sock=TCPConnect(Propiedades.servidorhidra,Propiedades.puerto); 
		if(sock!= INVALID_SOCKET){
			ADMINISTRADO=true;	// La conexin se ha establecido
			return(true);
		}
		if(swloop){
			vez++;
			if (vez>MAXCNX){
				swloop=false;
				ADMINISTRADO=false;	 // No ha podido establecerse la conexin, pasa  a modo NO administrado
				RaiseError(1,tbErrores[1],"Abre_conexion()");
				return(false);	
			}
		}
		sleep(2); // Espera dos segundos antes de intentar una nueva conexin con el servidor Hidra
	}
	return(true);
}
//___________________________________________________________________________________________________
// Funcin: Cierra_conexion
//
//		Descripcin: 
//			Cierra la conexin entre el cliente y el servidor HIDRA
//___________________________________________________________________________________________________
void Cierra_conexion()
{
	if(OFFLINE || !ADMINISTRADO) return;	// Modo offline o modo no administrado
	TCPClose(sock);
}
//___________________________________________________________________________________________________
// Funcin: Cortesia
//
//		Descripcin: 
//			 Respuesta de servidor estandar.
//___________________________________________________________________________________________________
int Cortesia(){
	 return(true);
}
//___________________________________________________________________________________________________
// Funcin: NoComandosPtes
//
//		Descripcin: 
//			 Respuesta de servidor estandar. Se ejecuta para indicar que no hay ms comandos pendientes
//___________________________________________________________________________________________________
int NoComandosPtes(){
	CMDPTES=false; // Corta el bucle de comandos pendientes
	return(true);
}
//___________________________________________________________________________________________________
// Funcin: inclusion_cliRMB
//
//		Descripcin: 
//			 Abre una sesin en el servidor Hidra
//___________________________________________________________________________________________________
int inclusion_cliRMB()
{ 
	Log(" Proceso de inclusión del cliente Menu inclusion_cliRMB()");
	TRAMA *trama;
	if(OFFLINE || !ADMINISTRADO){	// Modo offline o modo no administrado
		Log("Modo offline o NO administrado activado");
		trama=(TRAMA*)LoadTextFile(filemenu); // Lee fichero que contiene su men
		if(!trama){
			RaiseError(1,tbErrores[1],"inclusion_cliRMB()");
			return(false);		
		}
		gestion_tramas(trama);
		return(true);
	}
	// Toma configuracin
	char *parametroscfg;
	parametroscfg=(char*)Buffer(80);
	parametroscfg=LeeConfiguracion();	// Toma configuracin

	// Compone la trama
	int lon;		
	trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
	if(!trama){
		RaiseError(1,tbErrores[1],"inclusion_cliRMB()");
		return(false);		
	}
	lon=sprintf(trama->parametros,"nfn=inclusion_cliRMB\r");							// Nombre de la funcin a ejecutar en el servidor HIDRA 
	lon+=sprintf(trama->parametros+lon,"cfg=%s\r",parametroscfg);				// Configuracin de los Sistemas Operativos del cliente
	if(Abre_conexion()){
		Log(" Abre conexión para inclusión de cliente Abre_conexion()");
		// Modo administrado
		Log("Enviando peticion de inclusion");
		envia_tramas(sock,trama);
		recibe_tramas(sock,trama);
		Cierra_conexion();
		if(CACHEEXISTS)	// Si existe cach
			CreateTextFile(filemenu,(char*)trama);  // ... guarda la trama recibida para cuando no est en modo administrado
		if(!gestion_tramas(trama)){
			RaiseError(0,tbErrores[0],"inclusion_cliRMB()");
			return(false);		
		}
		if(CACHEEXISTS) 	// Si existe cach ...
			RecuperaItems();	// Recupera los parmetros de los items del men para ejecutarlos sin peticin previa al servidor HIDRA
		return(true);
	}
	if( !ADMINISTRADO){	// Modo no administrado
			Log("Modo offline o NO administrado activado");
			if(!CACHEEXISTS){ // No existe cach
				Log("No hay ningun espacio reservado para la cache en este disco");
				PROCESO=false;	// Proceso principal terminado en la primera iteracin? del bucle
				return(false);
			}
			trama=(TRAMA*)LoadTextFile(filemenu); // Lee fichero que contiene su men						
			gestion_tramas(trama);	
	}		
	return(true);				
}
//___________________________________________________________________________________________________
//
//  Ejecuta un fichero autoexec para  el ordenador
//___________________________________________________________________________________________________
int autoexec_cliRMB()
{
	
	char* codigo;
	int res;
	sprintf(fileini,"/inicio/INI_%s",Propiedades.IPlocal);	// Nombre del fichero autoexec		
	if(ExisteFichero(fileini)){
		codigo=LoadTextFile(fileini); // Lee fichero que contiene su men				
		if(codigo){
			res=system(codigo);
			if(res){
				RaiseError(6,tbErrores[6],"autoexec_cliRMB()");
				return(false);
			}
		}
	}
	return(true);
} 
//___________________________________________________________________________________________________
//
//  Respuesta del servidor HIDRA a la peticin de inicio enviando los datos identificativos del cliente rembo
//___________________________________________________________________________________________________
int RESPUESTA_inclusion_cliRMB(TRAMA *trama)
{
	strcpy(Propiedades.idordenador,toma_parametro("ido",trama->parametros));					// Identificador del ordenador
	strcpy(Propiedades.nombreordenador,toma_parametro("npc",trama->parametros));		//  Nombre del ordenador
	strcpy(Propiedades.idaula,toma_parametro("ida",trama->parametros));							//  Identificador del aula a la que pertenece
	strcpy(Propiedades.idperfilhard,toma_parametro("ifh",trama->parametros));					//	Identificador del perfil hardware que posee
	strcpy(Propiedades.servidorhidra,toma_parametro("hrd",trama->parametros));				//	 Direccin IP del servidor HIDRA
	strcpy(Propiedades.puerto,toma_parametro("prt",trama->parametros));		
	strcpy(Propiedades.iprepo,toma_parametro("ipr",trama->parametros));	//	 Dirección IP del repositorio
	strcpy(Propiedades.puertorepo,toma_parametro("repr",trama->parametros));	//	 Puerto por donde se envan y reciben las tramas
	
	char *auxtpar=toma_parametro("che",trama->parametros);											//	 Tamaño asignado a la cach
	int tpar=atoi(auxtpar);
	if(tpar!=TPAR){ // Si el tamaño asignado a la cach se ha modificado desde la ltima vez ...
		if(!SetCachePartitionSize(tpar)){ // Ajusta el tamaño de la cach
			RaiseError(0,tbErrores[0],"RESPUESTA_inclusion_cliRMB()");	
			return(false);
		}
		TPAR=tpar;
		CACHEEXISTS=(TPAR>0);
	}
	// Guarda items del men
	char* cabmenu=toma_parametro("cmn",trama->parametros);
	if (cabmenu){
		swmnu=true;
		char *auxCab[15]; 
		split_parametros(auxCab,cabmenu,"&");	// Caracter separatorio de los elementos de un item
		strcpy(CabMnu.titulo,auxCab[0]);					// Tìtulo del men
		strcpy(CabMnu.coorx,auxCab[1]);				// Coordenada x del men pblico
		strcpy(CabMnu.coory,auxCab[2]);				// Coordenada y del men pblico
		strcpy(CabMnu.modalidad,auxCab[3]);		// Modalidad de columnas del men pblico
		strcpy(CabMnu.scoorx,auxCab[4]);				// Coordenada x del men privado
		strcpy(CabMnu.scoory,auxCab[5]);				// Coordenada y del men privado
		strcpy(CabMnu.smodalidad,auxCab[6]);	// Modalidad de columnas del men privado
		strcpy(CabMnu.resolucion,auxCab[7]);		// Resolucin de pantalla
	}
	char* htmmenu=toma_parametro("htm",trama->parametros);	// Men personalizado
	if (htmmenu){
		if (strlen(htmmenu)>0){	// Se aporta un men personalizado
			char *auxHtm[2];
			int lHtml=split_parametros(auxHtm,htmmenu,";"); 
			if (lHtml>0){ // Existe men personalizado
				char filesrvhtm[250], filechehtm[250];					
				sprintf(filesrvhtm,"/menus/%s",auxHtm[0]); 
				sprintf(filechehtm,"/menus/%s",auxHtm[0]); 	
				if(CACHEEXISTS){
					if(ExisteFichero(filesrvhtm)){
						CopyFile(filesrvhtm,filechehtm);
						char* htmmenu = LoadTextFile(filechehtm);
						strcpy(CabMnu.htmmenupub,htmmenu);
					}
				}
				else{
						if(ExisteFichero(filesrvhtm)){
							char* htmmenu = LoadTextFile(filesrvhtm);
							strcpy(CabMnu.htmmenupub,htmmenu);
					}
				}
			}
			if (lHtml>1){ // Existe men personalizado en la parte privada
				char filesrvhtm[250], filechehtm[250];					
				sprintf(filesrvhtm,"/menus/%s",auxHtm[1]); 
				sprintf(filechehtm,"/menus/%s",auxHtm[1]); 	
				if(CACHEEXISTS){
					if(ExisteFichero(filesrvhtm)){
						CopyFile(filesrvhtm,filechehtm);
						char* htmmenu = LoadTextFile(filechehtm);
						strcpy(CabMnu.htmmenupri,htmmenu);
					}
				}
				else{
						if(ExisteFichero(filesrvhtm)){
							char* htmmenu = LoadTextFile(filesrvhtm);
							strcpy(CabMnu.htmmenupri,htmmenu);
					}
				}
			}
		}
	}
	char* menu=toma_parametro("mnu",trama->parametros);	 // Men estandar
	char * auxMenu[MAXITEMS]; 
	char* auxItem[10];
	int iMnu=split_parametros(auxMenu,menu,"?"); // Caracter separatorio de  los item 
	int i;
	for( i = 0; i<iMnu; i++){
		struct s_Item Item;
		split_parametros(auxItem,auxMenu[i],"&"); // // Caracter separatorio de los elementos de un item
		strcpy(Item.idaccionmenu,auxItem[0]);	// Identificador de la accin
		strcpy(Item.urlimg,auxItem[1]);	// Url de la imagen del item
		strcpy(Item.literal,auxItem[2]);		// Literal del item
		strcpy(Item.tipoitem,auxItem[3]);	// Tipo de item ( Pblico o privado )
		strcpy(Item.tipoaccion,auxItem[4]);	// Tipo de accin ( Procedimiento,Tarea oTrabajo )
		tbMenu[i]=Item;
	}
	contitems=i;	 // Nmero de iterms totales de los dos mens
	return(true);
}
//___________________________________________________________________________________________________
//
//  Bsqueda de acciones pendientes en el servidor HIDRA
//___________________________________________________________________________________________________
int COMANDOSpendientes()
{

		CMDPTES=true;
		while(CMDPTES){
			// Compone la trama
			TRAMA *trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
			if(!trama){
				RaiseError(0,tbErrores[0],"COMANDOSpendientes()");			
				return(false);
			}
			sprintf(trama->parametros,"nfn=COMANDOSpendientes\r");	// Nombre de la funcin a ejecutar en el servidor HIDRA 
			if(Abre_conexion()){
				envia_tramas(sock,trama);
				recibe_tramas(sock,trama);
				Cierra_conexion();
				gestion_tramas(trama);
			}
			else{
					ADMINISTRADO=false;	
					SacaMensaje("vv","ATENCION el modo administrado ha sido DESACTIVADO",300);
					Log("Modo administrado DESACTIVADO");
					CMDPTES=false;
			}
		}
		return(true); 
}
//___________________________________________________________________________________________________
//
//  Espera algn comando desde el servidor HIDRA para ejecutarlo
//___________________________________________________________________________________________________
int procesaCOMANDOS()
{
		sprintf(filecmd,"/comandos/CMD_%s",Propiedades.IPlocal);	// Nombre del fichero de comandos		
		if(ExisteFichero(filecmd))	 // Borra fichero de comandos si previamente exista de anteriores procesos
			RemoveFile(filecmd);
		if(!disponibilidadCOMANDOS(false)){		// Notifica  al servidor HIDRA su disponibilidad para recibir comandos
			RaiseError(0,tbErrores[0],"procesaCOMANDOS()");	
			return(false);	
		}
		Log("Disponibilidad para comandos interactivos activada ...");
		PRCCMD=true;
		
		while(PRCCMD){	// Bucle de espera de comandos interactivos
			
			if(ExisteFichero(filecmd)){	 // Busca fichero de comandos
				TRAMA* trama =(TRAMA*) LoadTextFile(filecmd); // Toma parmetros
				RemoveFile(filecmd);	// Lo elimina
				gestion_tramas(trama);	// Procesa el comando
				// Se pega una vuelta para ver si hay comandos pendientes
				Log("Procesa comandos pendientes");
				COMANDOSpendientes(); // Bucle para procesar comandos pendientes
				Log("Acciones pendientes procesadas");
				Log("Disponibilidad para comandos interactivos activada ...");				
				if(!disponibilidadCOMANDOS(false)){		// Notifica  al servidor HIDRA su disponibilidad para recibir comandos
					RaiseError(0,tbErrores[0],"procesaCOMANDOS()");	
					return(false);
				}
			}
			
			sleep(5);	// Espera 5 segundos antes de volver a esperar comandos
		}
		return(true);
}
//___________________________________________________________________________________________________
//
//  Funcin conmutar en modo administrado para evitar error
//___________________________________________________________________________________________________
void Conmutar(void)
{
	SacaMensaje("vv","ATENCION el modo administrado ya ha sido ACTIVADO",300);
}
//___________________________________________________________________________________________________
//
//  Espera el comando para conmutar del modo NO administrado al modo administrado
//___________________________________________________________________________________________________
int EsperaConmutaModo(void)
{
		sprintf(filecmd,"/comandos/CMD_%s",Propiedades.IPlocal);	// Nombre del fichero de comandos
		if(ExisteFichero(filecmd))	 // Borra fichero de comandos si previamente exista de anteriores procesos
			RemoveFile(filecmd);
		while(!ADMINISTRADO){
			if(ExisteFichero(filecmd)){ // Busca fichero de comandos
				TRAMA* trama=(TRAMA*) LoadTextFile(filecmd); // Toma parmetros
				RemoveFile(filecmd);
				char *nombrefuncion;
				INTROaFINCAD(trama->parametros);
				nombrefuncion=toma_parametro("nfn",trama->parametros); 
				int res=strcmp(nombrefuncion,"Conmutar");
				if(res==0){
					ADMINISTRADO=true;	
					SacaMensaje("vv","ATENCION el modo administrado ha sido ACTIVADO",300);
					Log("Modo administrado ACTIVADO");
				}
				sleep(10);	// Espera 10 segundos antes de volver a comprobar la conmutacin					
			}
		}
		return(true);
}				
//___________________________________________________________________________________________________
//
//  Notifica al servidor su disponibilidad a recibir comandos ( Lgica negativa )
//___________________________________________________________________________________________________
int disponibilidadCOMANDOS(int swoff)
{
		// Compone la trama
		TRAMA *trama;
		int lon;		
		trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama
		if(!trama){
			RaiseError(0,tbErrores[0],"disponibilidadCOMANDOS()");	
			return(false);
		}
		lon=sprintf(trama->parametros,"nfn=disponibilidadCOMANDOS\r");	// Nombre de la funcin a ejecutar en el servidor HIDRA 
		if(swoff)
			lon+=sprintf(trama->parametros+lon,"swd=0\r");	// No disponible				
		else
			lon+=sprintf(trama->parametros+lon,"swd=1\r");	// Disponible				
		if(!Abre_conexion()){
			RaiseError(0,tbErrores[0],"disponibilidadCOMANDOS()");	
			return(false);
		}
		envia_tramas(sock,trama);
		recibe_tramas(sock,trama);
		Cierra_conexion();
		gestion_tramas(trama);		
		return(true);
}
//__________________________________________________________________________________________________
//
//		Activa el sector de arranque con rembo offline
//__________________________________________________________________________________________________
int RemboOffline(TRAMA *trama,TRAMA *nwtrama)
{
		int res=ActivarRemboOffline();
		if(ADMINISTRADO){	// Modo administrado
			sprintf(nwtrama->parametros,"nfn=RESPUESTA_RemboOffline\r");					
			RespuestaEjecucionComando(trama,nwtrama,res);
		}
		if(res)
			Reboot();	// Reinicia el ordenador
		return(true);			
}
//__________________________________________________________________________________________________
//
//		Notifica al servidor HIDRA que est encendido
//__________________________________________________________________________________________________
int Arrancar(TRAMA *trama,TRAMA *nwtrama)
{
		  // Acciones ...

		if(ADMINISTRADO){	// Modo administrado
			sprintf(nwtrama->parametros,"nfn=RESPUESTA_Arrancar\r");					
			RespuestaEjecucionComando(trama,nwtrama,true);	
		}
		return(true);
}
//__________________________________________________________________________________________________
//
//		Apaga el ordenador
//__________________________________________________________________________________________________
int Apagar(TRAMA *trama,TRAMA *nwtrama)
{ 
		
		  // Acciones ...		
		if(ADMINISTRADO){	// Modo administrado
			sprintf(nwtrama->parametros,"nfn=RESPUESTA_Apagar\r");					
			RespuestaEjecucionComando(trama,nwtrama,true);	
		}
		
		PowerOff();
		return(true);			
}
//__________________________________________________________________________________________________
//
//		Reinicia el ordenador
//__________________________________________________________________________________________________
int Reiniciar(TRAMA *trama,TRAMA *nwtrama)
{

		  // Acciones ...		
		if(ADMINISTRADO){	// Modo administrado
			sprintf(nwtrama->parametros,"nfn=RESPUESTA_Reiniciar\r");					
			RespuestaEjecucionComando(trama,nwtrama,true);	
		}
		Reboot();
		return(true);			
}
//__________________________________________________________________________________________________
//
//		Actualiza los datos de un ordenador  como si volviera a solicitar la entrada  en el sitema al servidor HIDRA
//__________________________________________________________________________________________________
int Actualizar()
{ 

		OpenMessage("WW","... Actualizando, por favor espere");
		inclusion_cliRMB();
		CloseWindow("WW");
		Pantallazo(CabMnu.resolucion);
		Muestra_Menu_Principal();
		return(true);

}
//___________________________________________________________________________________________________
//
//	Ejecuta un script Rembo-C
//___________________________________________________________________________________________________
int EjecutarScript(TRAMA *trama,TRAMA *nwtrama)
{
		char *wscript=toma_parametro("scp",trama->parametros); 	// Cdigo del script	
		char* codigo=URLDecode(wscript);
		
		char *salida=(char*)Buffer(LONGITUD_SCRIPTSALIDA);	// Reserva buffer  para la cadena			
		
		int res=ExecShell(codigo,salida);
		// Toma  la nueva configuracin
		char* parametroscfg=LeeConfiguracion();
		if(ADMINISTRADO){	// Modo administrado
			int lon;
			lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_EjecutarScript\r");		
			lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);	// Ip del ordenador
			RespuestaEjecucionComando(trama,nwtrama,res);	
		}
		return(true);
}
//___________________________________________________________________________________________________
//	Funcin : URLDecode
//	Descripcin:
//		Decodifica una cadena codificada con UrlEncode
//	Parmetros:
//		 - cadena: Contenido de la cadena
//__________________________________________________________________________________________________
char* URLDecode(  char *src)
{
	const char *p = src;
	char code[3] = {0};
	unsigned long ascii = 0;
	char *end = NULL;
	char *dest,*cad;

	dest=(char*)Buffer(strlen(src));	// Reserva buffer  para la cadena			
	cad=dest;
	while(*p){
		if(*p == '%'){
			memcpy(code, ++p, 2);
			ascii = strtoul(code, &end, 16);
			*dest++ = (char)ascii;
			p += 2;
		}
		else
			*dest++ = *p++;
	}
	return(cad);
}
//___________________________________________________________________________________________________
//	Funcin : Reemplaza
//	Descripcin:
//		Reemplaza en una cadena, otra cadena dada
//	Parmetros:
//		 - cadenaorigien: Contenido de la cadena
//		 - patron: Cadena a sustituir
//		 - susti: Cadena sustituta
//__________________________________________________________________________________________________
char* Reemplaza(char* cadenaorigen,char* patron,char* susti)
{

	char* cadenafinal="";
		/*
	int pos,lon;
	int lonp=StrLength(patron);
	pos=0;
	pos=StrFind(cadenaorigen,patron);
	if(pos<0) return(cadenaorigen); // No existe el caracter o la semicadena a reemplazar
	while(pos>=0){
		cadenafinal=StrInsert(cadenafinal,StrLength(cadenafinal),StrCopy(cadenaorigen,0,pos));
		cadenafinal=StrInsert(cadenafinal,StrLength(cadenafinal),susti);
		pos+=lonp; // Adelanta el puntero
		lon=StrLength(cadenaorigen);
		cadenaorigen=StrCopy(cadenaorigen,pos,lon);
		pos=StrFind(cadenaorigen,patron);
	}
	cadenafinal=StrInsert(cadenafinal,StrLength(cadenafinal),StrCopy(cadenaorigen,0,StrLength(cadenaorigen)));
*/
	return(cadenafinal);

}
//___________________________________________________________________________________________________
//		Crea un perfil software
//__________________________________________________________________________________________________
int CrearPerfilSoftware(TRAMA*trama,TRAMA*nwtrama)
{

		int res=0;
		char *wparticion=toma_parametro("par",trama->parametros);		// Particin de donde se crear el perfil
		char *widperfilsoft=toma_parametro("ifs",trama->parametros);		// Perfil software a crear
		char *widperfilhard=toma_parametro("ifh",trama->parametros);		// Perfil hardware del  ordenador
		char *wnemonico=toma_parametro("nem",trama->parametros);		// Nemnico del S.O. de la particin
				
		if(!ExisteFichero(HIDRACHEIMAGENES)) // Creacin carpeta imagenes
			CreateDir(HIDRACHEIMAGENES);
	
		char pathperfil[250];
		sprintf(pathperfil,"%s/%s",HIDRACHEIMAGENES,wnemonico);	// Path del perfil creado	
		if(!ExisteFichero(pathperfil))// Creacin carpeta S.O. especifico
			CreateDir(pathperfil);

		char fileperfil[64];
		sprintf(fileperfil,"PS%s_PH%s",widperfilsoft,widperfilhard);	// Nombre del fichero del perfil creado
		char filemasterboot[64];
		sprintf(filemasterboot,"PS%s_PH%s.msb",widperfilsoft,widperfilhard);	// Idem para el sector de arranque MBR
		int nem=Nemonico(wnemonico);
		switch(nem){
			case 1:
				SacaMensaje("vv","Creando perfil software de un sistema MsDos ...",300);
				res=CrearPerfilMsdos(fileperfil,pathperfil,wparticion);
				break;
			case 2:
				SacaMensaje("vv","Creando perfil software de un sistema windows 98...",300);
				res=CrearPerfilW9x(fileperfil,pathperfil,wparticion);
				break;
			case 3:
				SacaMensaje("vv","Creando perfil software de un sistema windows 2000...",300);
				res=CrearPerfilNTFS(filemasterboot,fileperfil,pathperfil,wparticion);
				 break;
			case 4:
				SacaMensaje("vv","Creando perfil software de un sistema windows XP...",300);
				res=CrearPerfilNTFS(filemasterboot,fileperfil,pathperfil,wparticion);
				break;
			 case 5:
				SacaMensaje("vv","Creando perfil software de un sistema Linux...",300);
				res=CrearPerfilLinux(fileperfil,pathperfil,wparticion);
				break;
		}
		SacaMensaje("vv","Finalizada la creacion del perfil software",300);
		if(ADMINISTRADO){	// Modo administrado
			int lon;
			lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_CrearPerfilSoftware\r");	
			lon+=sprintf(nwtrama->parametros+lon,"ifs=%s\r",widperfilsoft);		
			lon+=sprintf(nwtrama->parametros+lon,"ifh=%s\r",widperfilhard);		
			RespuestaEjecucionComando(trama,nwtrama,res);			
		}		
		return(true);	
}
//___________________________________________________________________________________________________
//
//		Crea un software incremental
//__________________________________________________________________________________________________
int CrearSoftwareIncremental(TRAMA*trama,TRAMA*nwtrama)
{
		int res;
		char *wparticion=toma_parametro("par",trama->parametros);				// Particin de donde se crear el perfil
		char *widperfilsoft=toma_parametro("ifs",trama->parametros);				// Perfil software a crear
		char *widperfilhard=toma_parametro("ifh",trama->parametros);				// Perfil hardware del  ordenador
		char * widsoftincremental=toma_parametro("icr",trama->parametros);	// Cadena con los identificadores de lsoftware incremental			
		char * wnemonico=toma_parametro("nem",trama->parametros);			// Nemnico del S.O. de la particin				
		
		if(!ExisteFichero(HIDRACHEIMAGENES)) // Creacin carpeta imagenes
			CreateDir(HIDRACHEIMAGENES);
			
		char pathperfil[250];
		sprintf(pathperfil,"%s/%s",HIDRACHEIMAGENES,wnemonico);	// Path del perfil creado	
		if(!ExisteFichero(pathperfil)) // Creacin carpeta S.O. especifico
			CreateDir(pathperfil);
			
		char pathperfilinc[250];
		sprintf(pathperfilinc,"%s/%s/INC",HIDRACHEIMAGENES,wnemonico);	// Creacin carpeta software incremental
		if(!ExisteFichero(pathperfilinc)) // Creacin carpeta S.O. especifico
			CreateDir(pathperfilinc);			
			
		char fileperfil[64];
		sprintf(fileperfil,"PS%s_PH%s.psf",widperfilsoft,widperfilhard);	// Nombre del fichero del perfil creado

		char pathincremental[64];
		sprintf(pathincremental,"%s/%s/INC/INC%s_PS%s_PH%s",HIDRACHEIMAGENES,wnemonico,widsoftincremental,widperfilsoft,widperfilhard);	// Nombre del fichero del software incremental			
		int nem=Nemonico(wnemonico);
		switch(nem){
			case 1:
				SacaMensaje("vv","No se ha implementado esta funcin para MsDos ...",300);
				//res=CrearIncremental(fileperfil,pathperfil,wparticion,pathincremental,"");
				break;
			case 2:
				SacaMensaje("vv","No se ha implementado esta funcin para Windows 98...",300);
				//res=CrearIncremental(fileperfil,pathperfil,wparticion,pathincremental,"");
				break;
			case 3:
				SacaMensaje("vv","Creando software incremental de un sistema windows 2000...",300);
				res=CrearIncremental(fileperfil,pathperfil,wparticion,pathincremental,"WINNT");
				break;
			case 4:
				SacaMensaje("vv","Creando software incremental de un sistema windows XP...",300);
				res=CrearIncremental(fileperfil,pathperfil,wparticion,pathincremental,"WINDOWS");
				 break;
			case 5:
				SacaMensaje("vv","No se ha implementado esta funcin para Linux ...",300);
				//res=CrearIncremental(fileperfil,pathperfil,wparticion,pathincremental,"");
				break;
		}
		SacaMensaje("vv","Finalizada la creacion del software incremental",300);
		if(ADMINISTRADO){	// Modo administrado
			// Compone la trama
			int lon;		
			lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_CrearSoftwareIncremental\r");	
			lon+=sprintf(nwtrama->parametros+lon,"ifs=%s\r",widperfilsoft);		
			lon+=sprintf(nwtrama->parametros+lon,"ifh=%s\r",widperfilhard);
			lon+=sprintf(nwtrama->parametros+lon,"icr=%s\r",widsoftincremental);		
			RespuestaEjecucionComando(trama,nwtrama,res);			
		}		
		return(true);			
}
//___________________________________________________________________________________________________
//
// Descripcin: Restaura una imagen
//___________________________________________________________________________________________________
int RestaurarImagen(TRAMA*trama,TRAMA*nwtrama)
{
		int res=0;
		char *wparticion=toma_parametro("par",trama->parametros);				// Particin de donde se crear el perfil
		char *widimagen=toma_parametro("idi",trama->parametros);				// Identificador de la imagen		
		char *widperfilsoft=toma_parametro("ifs",trama->parametros);				// Perfil software a crear
		char *widperfilhard=toma_parametro("ifh",trama->parametros);				// Perfil hardware del  ordenador
		//char *widcentro=toma_parametro("idc",trama->parametros);						// Identificador del Centro
		char *wtipopar=toma_parametro("tpa",trama->parametros);							// Tipo de particin
		char *wnemonico=toma_parametro("nem",trama->parametros);					// Nemonico del S.O.  contenido en la particin
		char *wswrestauraimg=toma_parametro("swr",trama->parametros);				// Indica si la imagen a restaurar contiene un S.O. distinto al actual
		char *widsoftincremental=toma_parametro("icr",trama->parametros);		// Cadena con los identificadores de lsoftware incremental
		char *wpathimagen=toma_parametro("pth",trama->parametros);					// Indica si la imagen se descargar de la cach(cache) o del servidor(net)
		if(wpathimagen=='\0') wpathimagen="1";					// Por defecto de cach
		
		int idxpath=atoi(wpathimagen);
		if(!CACHEEXISTS) idxpath=2;						// Sin no existe cache siempre desde el servidor
		if(wswrestauraimg=="O")
			res=reparticiona((int)wparticion,wtipopar);			// Reparticiona si la imagen va a una particin distinta a la original
		if(res==0){
			char pathperfil[250];
			if(idxpath==2){
				sprintf(pathperfil,"%s/%s",HIDRASRVIMAGENES,wnemonico);	// Creacin carpeta software incremental
			}
			else{
				if(idxpath==1){
					sprintf(pathperfil,"%s/%s",HIDRACHEIMAGENES,wnemonico);	// Creacin carpeta software incremental					
				}
			}
			char fileperfil[64];
			sprintf(fileperfil,"PS%s_PH%s.psf",widperfilsoft,widperfilhard);	// Nombre del fichero del perfil creado	
			char filemasterboot[64];
			sprintf(filemasterboot,"PS%s_PH%s.msb",widperfilsoft,widperfilhard);	// Idem para el sector de arranque MBR			
			int nem=Nemonico(wnemonico);
			switch(nem){
				case 1:
					SacaMensaje("vv","Restaurando imagen MsDos...",500);
					res=Restaurar_MSDos(fileperfil,pathperfil,wparticion);
					break;
				case 2:
					SacaMensaje("vv","Restaurando imagen Windows 98...",500);
					char wgrupotrabajo[64];
					sprintf(wgrupotrabajo,"GrupoAula_%s",Propiedades.idaula);
					res=Restaurar_Windows9x(fileperfil,pathperfil,wparticion,Propiedades.nombreordenador,wgrupotrabajo);
					break;
				case 3:
					SacaMensaje("vv","Restaurar imagen Windows 2000...",500);
					res=Restaurar_WindowsNTFS(filemasterboot,fileperfil,pathperfil,wparticion,Propiedades.nombreordenador,"WINNT");
					if(widsoftincremental!="") 
						RestaurarIncrementales(wparticion,"WINNT",widsoftincremental,widperfilsoft,widperfilhard,wnemonico);
					break;
				case 4:
					SacaMensaje("vv","Restaurar imagen Windows XP...",500);
					res=Restaurar_WindowsNTFS(filemasterboot,fileperfil,pathperfil,wparticion,Propiedades.nombreordenador,"WINDOWS");
					if(widsoftincremental!="")
						RestaurarIncrementales(wparticion,"WINDOWS",widsoftincremental,widperfilsoft,widperfilhard,wnemonico);
					break;
				case 5:
					SacaMensaje("vv","Restaurar imagen Linux...",500);
					res=Restaurar_Linux(fileperfil, pathperfil,wparticion);
					if(wswrestauraimg=="O")
						cambiaFstab("disk://0:",wparticion,wparticion);
					break;
			}
			// Toma la nueva configuracin
			char *parametroscfg=LeeConfiguracion();
			SacaMensaje("vv","Finalizada la restauracion de imagen",500);
			if(ADMINISTRADO){	// Modo administrado
				int lon;			
				lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_RestaurarImagen\r");	
				lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);		
				lon+=sprintf(nwtrama->parametros+lon,"idi=%s\r",widimagen);	
				lon+=sprintf(nwtrama->parametros+lon,"par=%s\r",wparticion);	
				RespuestaEjecucionComando(trama,nwtrama,res);	
			}
			return(true);		
		}
		return(false);
}

//___________________________________________________________________________________________________
//
//	Reparticiona para restaurar imagen
//___________________________________________________________________________________________________
int  reparticiona(int numpar,char* tipar)
{
	/*
	 resul=0;
	try {
		char* tipopart="";
		char* tamapart="";
		char* ppStr="";
		char* lpStr="";
		var parts = StrParse(GetPrimaryPartitions(0)," ");
		for(int i = 0; i < sizeof(parts); i++) {
			var dualparts = StrParse(parts[i],":");
			tipopart = dualparts[0];
			tamapart = dualparts[1];
			if(i+1==numpar)
				tipopart=tipar;
			ppStr+=tipopart+":"+tamapart;
			if((i+1)<sizeof(parts))
				ppStr+=" ";
		}
		if(FindExtendedPartition(0) > 0) {
			var logparts = StrParse(GetLogicalPartitions(0)," ");
			for(int i = 0; i < sizeof(logparts); i++) {
				var dualparts = StrParse(logparts[i],":");
				tipopart = dualparts[0];
				tamapart = dualparts[1];
				if(i+5==numpar)
					tipopart=tipar;
				lpStr+=tipopart+":"+tamapart;
				if((i+1)<sizeof(logparts))
					lpStr+=" ";
			}
		}
		resul=Particionar(ppStr,lpStr);
		if(resul==0)
			HDClean(0,numpar);
	}
	catch(excepcion exc){
		resul=exc.herror;
		strcpy(modulo,"reparticiona()");
		strcpy(herror,exc.msg);
		strcpy(LogMsg,"Error.-%d (%s) en modulo %s",exc.herror,exc.msg,modulo);
		Log(LogMsg);		
		return(false);	
	}			
	*/
	return(true);
}
//___________________________________________________________________________________________________
//	Funcin : cambiaFstab
//	Descripcin:
//		Modifica el fichero fstab cuando se restaura un linux en otra particin
//	Parmetros:
//		 - root: Particin original
//		 - nwpar: Nueva particin
//___________________________________________________________________________________________________
int cambiaFstab(char* root,char * par,char* nwpar)
{
	/*
    char* fstabfile = StrChDir(root, "/etc/fstab");
    if(!ExisteFichero(fstabfile)) {
        return false;
    }
    var fstable = LoadTextFile(fstabfile);
    var fslines = StrParse(fstable,"\r\n");
    char* nwfstable = "";
    for (int i=0;i<sizeof(fslines);i++) {
        if (fslines[i][0]!="#"){
	       var detail = StrParse(fslines[i]," \t");
			if (StrMatch(detail[0],"/dev/hda*") && detail[1]=="/" && (detail[2]=="ext2" || detail[2]=="ext3") ) {
				 fslines[i]=Reemplaza(fslines[i],detail[0],"/dev/hda"+nwpar); // Reeplaza la antigua particion
			}
		}
		nwfstable+=fslines[i]+"\r\n";
    }
    CreateTextFile(fstabfile,nwfstable);
    return true;
    */
        return true;
}
//___________________________________________________________________________________________________
//
//  Particiona y formatea
//___________________________________________________________________________________________________
int ParticionaryFormatear(TRAMA*trama,TRAMA*nwtrama)
{
	int res,i;
	char msg[250],ch[2];
	char* parametroscfg;
	int parfor; // Particiones a formatear
	char *parhdc[8];
	char *PrimaryPartitions=toma_parametro("ppa",trama->parametros);
	char *LogicalPartitions=toma_parametro("lpa",trama->parametros);
	char *HDCleanPartition=toma_parametro("hdc",trama->parametros);

	Log("Creando o modificando tabla de particiones");
	res=Particionar(PrimaryPartitions,LogicalPartitions);
	if (res){
		strcpy(ch,";");// caracter delimitador ( salto de linea)
		parfor=split_parametros(parhdc,HDCleanPartition,ch);
 		char *disco=(char*)Buffer(2);
		sprintf(disco,"1"); // Siempre el disco 1
   		for(i = 0; i<parfor; i++){
			sprintf(msg,"Formateando la partición: %s ",parhdc[i]);	
			Log(msg);
			sprintf(cmdshell,"%s/Formatear %s %s ",HIDRASCRIPTS,disco,parhdc[i]);
			res=ExecShell(cmdshell,NULL);
			if(!res){
	    		RaiseError(4,tbErrores[4],"ParticionaryFormatear()");	 // Se ha producido algun error
				break;
    		}			
		}
	}
	Log("Finalizado el particionado y formateado de particiones");
	// Toma la nueva configuración
	parametroscfg=LeeConfiguracion();
	if(ADMINISTRADO){	// Modo administrado
		int lon;
		lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_ParticionaryFormatear\r");		
		lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);
		RespuestaEjecucionComando(trama,nwtrama,res);	
	}
	return(res);
		
	/*

		char* PrimaryPartitions=toma_parametro("ppa");
		char* LogicalPartitions=toma_parametro("lpa");
		char* HDCleanPartition=toma_parametro("hdc");
		SacaMensaje("vv","Creando o modificando tabla de particiones",300);
		resul=Particionar(PrimaryPartitions,LogicalPartitions);
		if (resul==0){
    			var parhdc = StrParse(HDCleanPartition,";");
			for(int i = 0; i<sizeof(parhdc); i++){
				SacaMensaje("vv","Formateando la particion: "+parhdc[i],300);
				int pid=Eval("HDClean(0,"+parhdc[i]+");");
				join(pid);
			}
			// Toma la nueva configuracin
			parametroscfg=LeeConfiguracion();
		}
	}
	SacaMensaje("vv","Finalizado el particionado y formateado de particiones",300);
	if(ADMINISTRADO){	// Modo administrado
		char* nfn="nfn=RESPUESTA_ParticionaryFormatear"; 
		char* cfg="cfg="+parametroscfg; 
		char* semitrama="MLCAMDJ1"+nfn+INTRO+cfg+INTRO;
		RespuestaEjecucionComando(semitrama);
	}
	*/
}
//___________________________________________________________________________________________________
//
//  Configura las particiones del ordenador
//___________________________________________________________________________________________________
void Configurar()
{

	/*
	resul=0;
	char* parametroscfg="";
	var BasicErrorHandler(var exc){
		parametroscfg="";
		resul=exc.herror;
		modulo="ParticionaryFormatear()";
		herror=exc.msg;
		Log(Strf("Error.-%d (%s) en modulo %s",exc.herror,exc.msg,modulo));
		exc.resume=true;
		return exc;
	}
	char* PrimaryPartitions=toma_parametro("ppa");
	char* LogicalPartitions=toma_parametro("lpa");
	char* HDCleanPartition=toma_parametro("hdc");
	with(BasicErrorHandler)
	try {
		SacaMensaje("vv","Creando o modificando tabla de particiones",300);
		resul=Particionar(PrimaryPartitions,LogicalPartitions);
		if (resul==0){
			var parhdc = StrParse(HDCleanPartition,";");
			for(int i = 0; i<sizeof(parhdc); i++){
				SacaMensaje("vv","Formateando la particion: "+parhdc[i],300);
				int pid=Eval("HDClean(0,"+parhdc[i]+");");
				join(pid);
			}
		   // Toma  la nueva configuracin
			parametroscfg=LeeConfiguracion();
		}
	}
	SacaMensaje("vv","Finalizado el proceso de configuracion",300);
	if(ADMINISTRADO){	// Modo administrado
		char* nfn="nfn=RESPUESTA_Configurar"; 
		char* cfg="cfg="+parametroscfg; 
		char* hdc="hdc="+HDCleanPartition;
		char* semitrama="MLCAMDJ1"+nfn+INTRO+cfg+INTRO+hdc+INTRO;
		RespuestaEjecucionComando(semitrama);
	}
	*/
}
//___________________________________________________________________________________________________
//
// Toma la configuracin de particiones de un ordenador
//___________________________________________________________________________________________________
int TomaConfiguracion(TRAMA *trama,TRAMA *nwtrama)
{
		// Toma  la nueva configuracin
		char* parametroscfg=LeeConfiguracion();
		if(ADMINISTRADO){	// Modo administrado
			int lon;
			lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_TomaConfiguracion\r");		
			lon+=sprintf(nwtrama->parametros+lon,"cfg=%s\r",parametroscfg);	// Ip del ordenador
			RespuestaEjecucionComando(trama,nwtrama,true);	
		}
		return(true);
}
//___________________________________________________________________________________________________
//
// Toma la configuracin de particiones de un ordenador
//___________________________________________________________________________________________________
int InventarioHardware(TRAMA *trama,TRAMA *nwtrama)
{
		// Toma  la nueva configuracin
		char* parametroshrd=LeeHardware();
		if(ADMINISTRADO){	// Modo administrado
			int lon;
			lon=sprintf(nwtrama->parametros,"nfn=RESPUESTA_TomaHardware\r");		
			lon+=sprintf(nwtrama->parametros+lon,"hrd=%s\r",parametroshrd);	
			RespuestaEjecucionComando(trama,nwtrama,true);	
		}
		return(true);
}
//___________________________________________________________________________________________________
//	Funcin : RespuestaEjecucionComando
//	Descripcin:
//		Envia una respuesta a una ejecucion de comando
//	Parmetros:
//	  - semitrama: Parte de la trama con las respuestas de las funciones correspondientes
//___________________________________________________________________________________________________
int RespuestaEjecucionComando(TRAMA* trama, TRAMA *nwtrama, int res)
{ 

		// Compone la trama
		int idsuceso=0;
		char *widsuceso=toma_parametro("ids",trama->parametros);
		if(widsuceso) idsuceso=atoi(widsuceso);	
		int lon;
		lon=strlen(nwtrama->parametros); 
		lon+=sprintf(nwtrama->parametros+lon,"ids=%d\r",idsuceso);	//  Identificador del suceso
		char descrierror[250];
		if (res){
			lon+=sprintf(nwtrama->parametros+lon,"res=%s\r","1");	// Resultado de la ejecucin del comando	
			sprintf(descrierror,"%s "," ");	
			lon+=sprintf(nwtrama->parametros+lon,"der=%s\r",descrierror);	// Dscripcin del error si lo ha habido
		}	
		else{
			lon+=sprintf(nwtrama->parametros+lon,"res=%s\r","2");	// Resultado de la ejecucin del comando		
			sprintf(descrierror,"Error.-(%s) en modulo %s",e.msg,e.modulo);
			lon+=sprintf(nwtrama->parametros+lon,"der=%s\r",descrierror);	// Descripcin del error si lo ha habido
		}
		if(!Abre_conexion()){
			RaiseError(0,tbErrores[0],"RespuestaEjecucionComando()");	
			return(false);			
		}
		envia_tramas(sock,nwtrama);
		recibe_tramas(sock,trama);
		Cierra_conexion();
		gestion_tramas(trama);
		return(true);
}
//___________________________________________________________________________________________________
//
// Muestra el menu pblico
//___________________________________________________________________________________________________
int  Muestra_Menu_Principal()
{
	int herror;

	
	if(!swmnu)
		RaiseError(15,tbErrores[15],"Muestra_Menu_Principal()");	 // No existe menu rincipal
	char *strMenu=Buffer(MAXHTMLMNU);	
	if(!strMenu) 
		RaiseError(1,tbErrores[1],"Muestra_Menu_Principal()");	 // No hay memoria bastante

	
	// Items pblicos
	int i,lon,resul,coni;
	lon=coni=0;
	for(i = 0; i<contitems; i++){
		resul=strcmp(tbMenu[i].tipoitem,"1");
		if(resul==0) 
			lon+=sprintf(strMenu+lon,"%d '%c%s%c' ",++coni,34,tbMenu[i].literal,34);
	}
	sprintf(cmdshell,"%s/menu %c%s%c",HIDRASCRIPTS,34,strMenu,34);
	
   	herror=system(cmdshell);
	if(herror){
	    RaiseError(4,tbErrores[4],"Muestra_Menu_Principal()");	 // Se ha producido algun error
		return(false); 
    }
   	return(true);	
	
	/*
	Log("Va a ejecutar   menu");
	Log(cmdshell);
	herror=ejecutarscript (cmdshell,NULL,op);	
	if(herror){
	    RaiseError(4,tbErrores[4],"Muestra_Menu_Principal()");	 // Se ha producido algun error
	    return(false);

    }
   	return(true); 
   	*/
}
//___________________________________________________________________________________________________
//
// Muestra el menu administracin
//___________________________________________________________________________________________________
void  Muestra_Menu_Admon()
{
	/*
	 if (!aut)
		aut=Autentificar();
	 if(!aut){
		SacaMensaje("Werror","Nombre de usuario o contrasea incorrectos o ha cerrado la ventana",300);
		return;
	}
	if(CabMnu.htmmenupri!=""){ // Se ha aportado fichero de HTML para el men privado  ( Men personalizado )
		char* strHTML=CabMnu.htmmenupri;
		SaveText(CabMnu.htmmenupri,"display://root/SELF");
		SaveText(HIDRACHERAIZ+"/iconos/fondo1024x768.pcx","display://root/image");
		return;
	}
	int itemscol=(int)CabMnu.smodalidad; // Nuero de items por columnas
	int numcolspan=itemscol*2; // Calcula el cospan del TD
	int wleft,wtop;
	wleft=CabMnu.scoorx;
	wtop=CabMnu.scoory;
	char* strMenu="";
	strMenu+='<TABLE align="left" style="font-family:sans-serif;color: #a71026">';
	strMenu+='<TR><TD height="'+(char*)wtop+'" width="'+(char*)wleft+'">&nbsp;</TD><TD colspan='+(char*)numcolspan+'>&nbsp;</TD></TR>';
	// Items privados
	var tbMenuPri[];
	int cpri=0; // contador items privados
	int r; // Resto
	strMenu+='<TR><TD>&nbsp;</TD>';
	for(int i = 0; i<contitems; i++){
		if(tbMenu[i].tipoitem=="2"){
			strMenu+='<TD align="right">&nbsp;<BUTTON onmouseup="EjecutarItem('+tbMenu[i].idaccionmenu+');">';
			strMenu+='<IMG src="'+HIDRACHERAIZ+'/iconos/'+tbMenu[i].urlimg+'"></BUTTON>&nbsp;</TD>';
			strMenu+='<TD align="left"><H3 color="#941a25"><B>&nbsp;'+tbMenu[i].literal+'</B></H3></TD>';
			cpri++;
			r=cpri%itemscol;
			if(r==0){
				strMenu+='</TR>';
				strMenu+='<TR><TD>&nbsp;</TD><TD colspan='+(char*)numcolspan+'>&nbsp;</TD></TR>';
				strMenu+='<TR><TD>&nbsp;</TD>';
			}
		}
	}
	for(int i = r; i<itemscol; i++){
		strMenu+='<TD>&nbsp;</TD>';
		strMenu+='<TD>&nbsp;</TD>';
	}
	strMenu+='</TR>';
	// --------------------- Men pblico ---------------------------------------------------------------------------------------------
	if((CabMnu.scoorx>"0" && CabMnu.scoory>"0") || CabMnu.htmmenupri!=""){
		strMenu+='</TR>';
		strMenu+='<TR><TD>&nbsp;</TD><TD colspan='+(char*)numcolspan+'>&nbsp;</TD></TR>';
		strMenu+='<TR><TD>&nbsp;</TD>';
		strMenu+='<TR><TD>&nbsp;</TD><TD colspan='+(char*)numcolspan+'>';
		strMenu +='<IMG src="'+HIDRACHERAIZ+'/iconos/menupral.pcx" onmouseup="Muestra_Menu_Principal();">';
		strMenu +='</TD></TR>';
	}
	//----------------------------------------------------------------------------------------------------------------------------------------------
	strMenu +='</TABLE>';
	SaveText(strMenu,"display://root/SELF");
	*/
}
//___________________________________________________________________________________________________
//
//  Ejecuta un item de un men 
//___________________________________________________________________________________________________
int EjecutarItem(char* iditem)
{
		TRAMA* trama;
		if(CACHEEXISTS){ // Si existe particin
			sprintf(fileitem,"%s/menus/fileitem_%s",HIDRACHERAIZ,iditem);
			if(ExisteFichero(fileitem)){
				trama=(TRAMA*) LoadTextFile(filecmd); // Toma parmetros
				SacaMensaje("ejcmd","<b>ATENCION.- Se va a ejecutar</b> un item...",500);
				gestion_tramas(trama);
				return(true);
			}
		}
		trama=(TRAMA*)Buffer(LONGITUD_TRAMA);	// Reserva buffer  para la trama			
		if(!trama){
			RaiseError(1,tbErrores[1],"EjecutarItem()");			
			return(false);			
		}
		// Compone la trama
		int lon;
		lon=sprintf(trama->parametros,"nfn=EjecutarItem\r");	// Nombre de la funcin a ejecutar en el servidor HIDRA 				
		lon+=sprintf(trama->parametros+lon,"idt=%s\r",iditem);	// Identificador de la accin a ejecutar por el item	
		SacaMensaje("ejcmd","<b>ATENCION.- Se va a ejecutar</b> un item. Espere por favor ...",500);				
		if(Abre_conexion()){
			envia_tramas(sock,trama);
			recibe_tramas(sock,trama);
			Cierra_conexion();		
		}		
		return(true);
}
//_______________________________________________________________________________________________________
//
//  Muestra la pantalla 
//___________________________________________________________________________________________________
int Pantallazo(char* resol)
{
	/*
	char* DefVideoMode;
	char* DefKeyMap;
	char* DefCodeMap;
	var TBresolucion={"","800x600","1024x768"};
	var BasicErrorPantalla(var exc) {
		resul=exc.herror;
		modulo="Pantallazo";
		herror=exc.msg;
		Log(Strf("Error.-%d (%s) en modulo %s",exc.herror,exc.msg,modulo));
		exc.resume=true;
		return exc;
	}
	with(BasicErrorPantalla)
	try{
		if(resol!="")
			Settings.VideoMode =TBresolucion[(int)resol];
		else
			Settings.VideoMode ="800x600";
		Keyb("es");
		if(DefCodeMap != "")
			CodePage((int)DefCodeMap);
	}
	*/
	    return true;
}
//________________________________________________________________________________________________________
// Función: LeeFileConfiguracion
//
//		Descripcinn:
//		Esta funcinn lee el fichero de configuracinn del programa hidralinuxcli  y toma los parametros
//		Parametros:
//				- pathfilecfg : Ruta al fichero de configuracinn
//________________________________________________________________________________________________________
int LeeFileConfiguracion(char* pathfilecfg)
{
	
	long lSize;
	char * buffer,*lineas[100],*dualparametro[2];
	char ch[2];
	int i,numlin,resul;
	FILE* Fconfig;
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

		resul=strcmp(dualparametro[0],"IPhidra");
		if(resul==0) strcpy(servidorhidra,dualparametro[1]);

		resul=strcmp(dualparametro[0],"Puerto");
		if(resul==0) strcpy(Puerto,dualparametro[1]);
		
	}

	if(servidorhidra[0]==(char)NULL){
		RaiseError(8,tbErrores[11],"");	
		return(FALSE);
	}
	if(Puerto[0]==(char)NULL){
		RaiseError(8,tbErrores[12],"");	
		return(FALSE);
	}
	puerto=atoi(Puerto);

	return(TRUE);
}

//***********************************************************************************************************************
// PROGRAMA PRINCIPAL
//***********************************************************************************************************************
int  main(int argc, char *argv[])
{
	int i;
	//pid_t  pid;

	for(i = 1; i < argc; i++){
       if (argv[i][0] == '-'){
           switch (tolower(argv[i][1])){
               case 'f':
                   if (argv[i+1]!=NULL)
                       strcpy(szPathFileCfg, argv[i+1]);
				else{
					RaiseError(8,tbErrores[13],"");	
					exit(EXIT_FAILURE);
				}
                   break;
               default:
              		 RaiseError(8,tbErrores[14],"");	
 					exit(EXIT_FAILURE);
                   break;
           }
       }
    }
		// Lee   fichero de configuración
	if(!LeeFileConfiguracion(szPathFileCfg)){ // Toma parametros de configuracion
		RaiseError(8,tbErrores[13],"");	
		exit(EXIT_FAILURE);
	}
	strcpy(HIDRASCRIPTS,"/var/EAC/hidra/scripts");

	  // Datos del ordenador
	TomaIPlocal();
	strcpy(Propiedades.IPlocal,IPlocal);	
	strcpy(Propiedades.servidorhidra,servidorhidra);
	strcpy(Propiedades.puerto,Puerto);
	strcpy(Propiedades.idordenador,"0");

	if(strcmp(Propiedades.IPlocal,"0.0.0.0")==0){		//  Detecta modo offline
		OFFLINE=true;
		ADMINISTRADO=false;
	}
	else{
		OFFLINE=false;
		ADMINISTRADO=true;	// Por defecto se supone administrado hasta que intente la conexin con HIDRA
	}
	
	TPAR=GetCachePartitionSize("hda0");	 // Toma el tamao de la cach
	CACHEEXISTS=(TPAR>0);						// Existe o no existe cach

	strcpy(HIDRASRVRAIZ,"/usr/local/hidra");			// Path al directorio raiz HIDRA referido al servidor	
	strcpy(HIDRACHERAIZ,"/usr/hidra");			// Path al directorio raiz HIDRA referido a la cach
	
	strcpy(HIDRACHEIMAGENES,HIDRACHERAIZ);
	strcat(HIDRACHEIMAGENES,"/imagenes");				// Path al directorio de imgenes de HIDRA referido a la cach
	
	strcpy(HIDRASRVIMAGENES,HIDRASRVRAIZ);	// Path al directorio de imgenes de HIDRA referido al servidor
	strcat(HIDRASRVIMAGENES,"/imagenes");
	
	strcpy(HIDRASRVCMD,HIDRASRVRAIZ);				// Path al directorio de comandos de HIDRA
	strcat(HIDRASRVCMD,"/comandos");	
	
	sprintf(filecmdshell,"%s/comandos/filecmdshell",HIDRACHERAIZ);	// Path de  fichero del men inicial
	
	sprintf(filemenu,"%s/menus/menusini",HIDRACHERAIZ);	// Path de  fichero del men inicial
	Pantallazo(NULL);
	if(!OFFLINE)
		OpenMessage("CNH","Conectando con el servidor HIDRA, espere por favor ...");
	
	if(OFFLINE){	// Modo offline o modo no administrado
		if(!CACHEEXISTS){ // No existe cach
			PROCESO=false;	// Proceso principal terminado en la primera iteracin del bucle
			RaiseError(8,tbErrores[8],"main()");	
			return(false);
		}
	}
	if(inclusion_cliRMB()){	// El cliente ha abierto sesin correctamente
		if(strcmp(Propiedades.idordenador,"0")==0) {
			RaiseError(0,tbErrores[0],"main()");	
			return(false);				
		}
		if(!OFFLINE) CloseWindow("CNH"); // Cierra ventana conexin con servidor HIDRA
		Log("Cliente rembo iniciado");	
		Pantallazo(CabMnu.resolucion);
		Log("Ejecución de comandos Autoexec");
		//if(!OFFLINE) autoexec_cliRMB();  // Ejecucin fichero autoexec			
		if(ADMINISTRADO){
			Log("Procesa comandos pendientes");
			OpenMessage("WW","... procesando acciones pendientes");
			COMANDOSpendientes(); // Bucle para procesar comandos pendientes
			Log("Acciones pendientes procesadas");
			CloseWindow("WW");
		}
		Log("Conmutación de espera para comandos");
		procesaCOMANDOS(); // Bucle para procesar comandos interactivos 
		Log("Salida de la conmutación de espera para comandos");
		/*
		if((pid=fork())==0){
			if(ADMINISTRADO)	// Modo administrado
				procesaCOMANDOS(); // Bucle para procesar comandos interactivos    
		}
		else
		 	Muestra_Menu_Principal();
		 	*/
	}
	exit(0);
}




