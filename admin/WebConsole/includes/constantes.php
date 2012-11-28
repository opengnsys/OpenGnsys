<?php

// Código de los ambitos para comandos
$AMBITO_CENTROS=0x01;
$AMBITO_GRUPOSAULAS=0x02;
$AMBITO_AULAS=0x04;
$AMBITO_GRUPOSORDENADORES=0x08;
$AMBITO_ORDENADORES=0x10;


// Código del resto de �bitos
$AMBITO_IMAGENES=0x20;
$AMBITO_PROCEDIMIENTOS=0x21;
$AMBITO_TAREAS=0x22;
$AMBITO_COMANDOS=0x23;
$AMBITO_IMAGENESMONOLITICAS=0x49;
$AMBITO_IMAGENESBASICAS=0x50;
$AMBITO_IMAGENESINCREMENTALES=0x51;

$AMBITO_COMPONENTESHARD=0x24;
$AMBITO_COMPONENTESSOFT=0x25;
$AMBITO_PERFILESHARD=0x26;
$AMBITO_PERFILESSOFT=0x27;
$AMBITO_MENUS=0x28;
$AMBITO_REPOSITORIOS=0x29;
$AMBITO_RESERVAS=0x31; 

// Código del resto de ambitos( grupos )
$AMBITO_GRUPOSIMAGENES=0x32;
$AMBITO_GRUPOSPROCEDIMIENTOS=0x33;
$AMBITO_GRUPOSTAREAS=0x34;
$AMBITO_GRUPOSCOMPONENTESHARD=0x36;
$AMBITO_GRUPOSCOMPONENTESSOFT=0x37;
$AMBITO_GRUPOSPERFILESHARD=0x38;
$AMBITO_GRUPOSPERFILESSOFT=0x39;
$AMBITO_GRUPOSMENUS=0x40;
$AMBITO_GRUPOSREPOSITORIOS=0x41;
$AMBITO_GRUPOSRESERVAS=0x44;
$AMBITO_GRUPOSENTIDADES=0x45;
$AMBITO_GRUPOSIMAGENESMONOLITICAS=0x46;
$AMBITO_GRUPOSIMAGENESBASICAS=0x47;
$AMBITO_GRUPOSIMAGENESINCREMENTALES=0x48;
$AMBITO_GRUPOSIMAGENESMONOLITICAS=0x46;
$AMBITO_GRUPOSIMAGENESBASICAS=0x47;
$AMBITO_GRUPOSIMAGENESINCREMENTALES=0x48;

// Literales de los ambitos
$LITAMBITO_CENTROS="centros";
$LITAMBITO_AULAS="aulas";
$LITAMBITO_ORDENADORES="ordenadores";
$LITAMBITO_IMAGENES="imagenes";
$LITAMBITO_IMAGENESMONOLITICAS="imagenesmonoliticas";
$LITAMBITO_IMAGENESBASICAS="imagenesbasicas";
$LITAMBITO_IMAGENESINCREMENTALES="imagenesincrementales";
$LITAMBITO_PROCEDIMIENTOS="procedimientos";
$LITAMBITO_TAREAS="tareas";

$LITAMBITO_TIPOHARDWARES="tipohardwares";
$LITAMBITO_COMPONENTESHARD="componeneteshard";
$LITAMBITO_COMPONENTESSOFT="componenetessoft";
$LITAMBITO_PERFILESHARD="perfileshard";
$LITAMBITO_PERFILESSOFT="perfilessoft";
$LITAMBITO_MENUS="menus";
$LITAMBITO_REPOSITORIOS="repositorios";

$LITAMBITO_RESERVAS="reservas";
$LITAMBITO_ADMINISTRACION="administracion";
$LITAMBITO_UNIVERSIDADES="universidades";
$LITAMBITO_ENTIDADES="entidades";
$LITAMBITO_USUARIOS="usuarios";
// Literales de los ambitos ( Grupos )
$LITAMBITO_GRUPOSAULAS="gruposaulas";
$LITAMBITO_GRUPOSORDENADORES="gruposordenadores";
$LITAMBITO_GRUPOSIMAGENES="gruposimagenes";
$LITAMBITO_GRUPOSIMAGENESMONOLITICAS="gruposimagenesmonoliticas";
$LITAMBITO_GRUPOSIMAGENESBASICAS="gruposimagenesbasicas";
$LITAMBITO_GRUPOSIMAGENESINCREMENTALES="gruposimagenesincrementales";
$LITAMBITO_GRUPOSPROCEDIMIENTOS="gruposprocedimientos";
$LITAMBITO_GRUPOSTAREAS="grupostareas";

$LITAMBITO_GRUPOSCOMPONENTESHARD="gruposcomponenteshard";
$LITAMBITO_GRUPOSCOMPONENTESSOFT="gruposcomponentessoft";
$LITAMBITO_GRUPOSPERFILESHARD="gruposperfileshard";
$LITAMBITO_GRUPOSPERFILESSOFT="gruposperfilessoft";
$LITAMBITO_GRUPOSMENUS="gruposmenus";
$LITAMBITO_GRUPOSREPOSITORIOS="gruporepositorio";
$LITAMBITO_GRUPOSRESERVAS="gruposreservas";
$LITAMBITO_GRUPOSENTIDADES="gruposentidades";
// Código de los tipo de acciones

$EJECUCION_COMANDO=0x0001;
$EJECUCION_PROCEDIMIENTO=0x0002;
$EJECUCION_TAREA=0x0003;
$EJECUCION_RESERVA=0x0004;
$EJECUCION_AUTOEXEC=0x0005;

// Código de los tipo de notificadores
$NOTIFICADOR_ORDENADOR=0x0001;
$NOTIFICADOR_COMANDO=0x0002;
$NOTIFICADOR_TAREA=0x0003;

// Categorias de sucesos
$PROCESOS=0x01;
$INFORMACIONES=0x02;
$NOTIFICACIONES=0x03;

// Código de los tipos de mensajes
$MSG_COMANDO=0x01; // Mensaje del tipo comando
$MSG_NOTIFICACION=0x02; // Respuesta a la ejecución un comando
$MSG_PETICION=0x03; // Petición de cualquier actuación
$MSG_RESPUESTA=0x04; // Respuesta a una petición
$MSG_INFORMACION=0x05; // Envío de cualquier información sin espera de confirmación o respuesta


$RESPUESTA_EJECUCION_COMANDO=0x0001;
$RESPUESTA_EJECUCION_TAREA=0x0002;
$RESPUESTA_EJECUCION_TRABAJO=0x0003;
$RESPUESTA_EJECUCION_PETICION=0x0004;

// Código de los tipos de items de los mens de clientes
$ITEM_PUBLICO=0x0001;
$ITEM_PRIVADO=0x0002;

//Codificaci� de los resultados de las acciones

$ACCION_SINRESULTADO=0; // Sin resultado
$ACCION_EXITOSA=1; // Finalizada con éxito
$ACCION_FALLIDA=2; // Finalizada con errores
$LITACCION_FALLIDA="Acción CANCELADA manualmente";
$LITACCION_EXITOSA="Acción TERMINADA manualmente";

//Codificaci� de los estados de las acciones

$ACCION_INICIADA=1; // Acci� activa
$ACCION_DETENIDA=2; // Acci� momentanemente parada
$ACCION_FINALIZADA=3; // Acci� finalizada
$ACCION_PROGRAMADA=4; // Acción programada

// Nombrey path del fichero de intercambio de parametros entre páginas 
//  Ha sido necesario porque cuando los parametros enviados execed�n de cierta longitud
//  ocurria una excepci� al llamar a la p�ina por GET.

$fileparam="../includes/PRM_".$usuario;
$pathfileco="/opt/opengnsys/log/clients"; // Path del    fichero de eco de consola 

// M�ima longitud de los parametros enviados entre páginas
$MAXLONPRM=16000;
$MAXLONVISUSCRIPT =1024; // longitud Maxima de visualizaci� del script en las colas de acciones
$MAXSIZEFILERBC=100000; // longitud Maxima de los fichero de script enviados como comandos ejecuci� de script

$LONHEXPRM=5; // Longitud de la cadena hexdecimal que contiene la longitud total de la trama 
$LONCABECERA=16; // Longitud de la cabecera de las tramas 
$LONBLK=512; // Longitud de los paquetes de tramas leidos cada vez	

$tbTiposParticiones=""; 
$tbTiposParticiones[0]="EMPTY";
$tbTiposParticiones[1]="BIGDOS";
$tbTiposParticiones[2]="FAT32";
$tbTiposParticiones[3]="NTFS";
$tbTiposParticiones[4]="EXT2";
$tbTiposParticiones[5]="EXT3";
$tbTiposParticiones[6]="EXT4";
$tbTiposParticiones[7]="LINUX-SWAP";
$tbTiposParticiones[8]="CACHE";
$tbTiposParticiones[9]="VFAT";
$tbTiposParticiones[10]="UNKNOW";


//Codificaci� de los estados de las reservas
$RESERVA_CONFIRMADA=1; // Reserva confirmada
$RESERVA_PENDIENTE=2; // Reserva pendiente
$RESERVA_DENEGADA=3; // Reserva denegada

$SUPERADMINISTRADOR=1; // administrador de la Aplicación
$ADMINISTRADOR=2; // administrador de Centro
$OPERADOR=3; // operador de aula

$msk_sysFi=0x01;
$msk_nombreSO=0x02;
$msk_tamano=0x04;
$msk_imagen=0x08;
$msk_perfil=0x10;
$msk_cache=0x12;

// Tipos de imagenes
$IMAGENES_MONOLITICAS=0x01;
$IMAGENES_BASICAS=0x02;
$IMAGENES_INCREMENTALES=0x03;

?>

