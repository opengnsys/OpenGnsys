rem preguntamos por el grupo a crear
#set /p grupo="introduce grupo:"
grupo=asi

rem creamos el grupo profesor_%grupo%
net localgroup /add profesor_%grupo%
rem creamos el grupo alumnos_%grupo%
net localgroup /add alumnos_%grupo%
rem incluimos el grupo alumnos_grupo como miembro del grupo usuarios
net localgroup usuarios alumnos_%grupo% /add

rem creamos el directorio base de los usuarios del grupo.
mkdir c:\homes\%grupo%
rem establecemos en el directorio homes lectura y acceso para los usuarios.
echo S|cacls c:\homes /c /e /P usuarios:R
rem en directorio grupo, permisos de modificcion para el profesoregrupo, y Read para alumnos. 
echo S|cacls c:\homes\%grupo% /c /g profesor_%grupo%:c alumnos_%grupo%:R administradores:F


rem creamos el directorio base del perfil del grupo
mkdir c:\perfiles\%grupo%
rem establecemos en el directorio perfiless lectura y acceso para los usuarios.
echo S|cacls c:\perfiles /c /e /P usuarios:R
echo S|cacls c:\perfiles\%grupo% /c /g profesor_%grupo%:R alumnos_%grupo%:R administradores:F 

rem copiamos la estructura
xcopy "c:\Documents and Settings\Default User\*" c:\perfiles\%grupo%\*


rem creamos la ubicacion de los scrits locales especificados en el perfil de cada usuario
mkdir C:\WINDOWS\system32\repl\import\scripts

rem creamos la plantilla
rem net user %grupo%00 %grupo%00 /add /expires:01/10/2008 /homedir:c:\homes\%grupo%\%grupo%00 /profilepath:c:\perfiles\%grupo% /scriptpath:logon.cmd && net localgroup alumnos_%grupo% %grupo%00 /add && mkdir c:\homes\%grupo%\%grupo%%00 && echo S|cacls c:\homes\%grupo%\%grupo%00 /c /g profesor_%grupo%:F %grupo%00:c administradores:F 		

rem creamos los usuarios
for /L %%i in (1,1,1) do for /L %%j in (1,1,2) do net user %grupo%%%i%%j %grupo%%%i%%j /add /expires:01/10/2008 /homedir:c:\homes\%grupo%\%grupo%%%i%%j /profilepath:c:\perfiles\%grupo% /scriptpath:logon.cmd && net localgroup alumnos_%grupo% %grupo%%%i%%j /add && mkdir c:\homes\%grupo%\%grupo%%%i%%j && echo S|cacls c:\homes\%grupo%\%grupo%%%i%%j /c /g profesor_%grupo%:F %grupo%%%i%%j:R administradores:F && echo S|cacls c:\homes\%grupo%\%grupo%%%i%%j /c /e /g %grupo%%%i%%j:W	

