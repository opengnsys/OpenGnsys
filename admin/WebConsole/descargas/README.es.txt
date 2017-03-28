
OGAgent: agentes OpenGnsys para sistemas operativos
---------------------------------------------------


Ficheros disponibles para descarga de agente OGAgent:

 - ogagent_Version_all.deb      OGAgent para sistemas Ubuntu, Debian y derivados
 - ogagent-Version.noarch.rpm   OGAgent para sistemas Red Hat, Fedora y derivados
 - ogagent-opensuse-Version.noarch.rpm   OGAgent para sistemas SuSE y OpenSuSE
 - OGAgentInstaller-Version.pkg OGAgent para sistemas macOS X
 - OGAgentSetup-Version.exe     OGAgent para sistemas Windows


Instalación manual de los agentes.

 - Ubuntu, Debian y derivados:
   - Instalar dependencias (NOTA: revisar dependencias para Ubuntu 12.04):
	sudo apt-get install -y libxss1 policykit-1 python python-requests python-qt4 python-six python-prctl
   - Descargar el fichero e instalar el agente:
	sudo dpkg -i ogagent_Version_all.deb
   - Configurar el agente:
	sudo sed -i "0,/remote=/ s,remote=.*,remote=https://IPServidorOpenGnsys/opengnsys/rest/," /usr/share/OGAgent/cfg/ogagent.cfg
   - Iniciar el servicio (se iniciará automáticamente en el proceso de arranque):
	sudo service ogagent start

 - Red Hat, Fedora y derivados (como root):
   - Descargar el fichero e instalar el agente:
	yum install ogagent-Version.noarch.rpm
   - Configurar el agente:
	sed -i "0,/remote=/ s,remote=.*,remote=https://IPServidorOpenGnsys/opengnsys/rest/," /usr/share/OGAgent/cfg/ogagent.cfg
   - Puede ser necesario corregir permisos antes de iniciar el servicio:
	chmod +x /etc/init.d/ogagent
   - Iniciar el servicio (se iniciará automáticamente en el proceso de arranque):
	service ogagent start

 - OpenSuSE:
	(en preparación)

 - macOS X:
   - Instalar dependencias (la instalación puebe realizar estas operaciones):
	sudo easy_install pip
	sudo pip install netifaces requests six
   - Descargar el fichero e instalar el agente:
	sudo installer -pkg OGAgentInstaller-Version.pkg -target /
   - Configurar el agente:
	sudo sed "/remote=/ s,remote=.*,remote=https://IPServidorOpenGnsys/opengnsys/rest/," /Applications/OGAgent.app/cfg/ogagent.cfg > /tmp/ogagent.cfg
	sudo mv /tmp/ogagent.cfg /Applications/OGAgent.app/cfg/ogagent.cfg
   - Iniciar el servicio (se iniciará automáticamente en el proceso de arranque):
	sudo ogagent start

 - Windows (como usuario administrador):
   - Descargar el fichero e instalar el agente ejecutando:
	OGAgentSetup-Version.exe
   - Seguir las instrucciones del instalador.
   - Editar el fichero de configuación "C:\Program Files\OGAgent\cfg\ogagent.cfg" (o C:\Archivos de programa\OGAgent\cfg\ogagent.cfg) y modificar el valor de la cláusula "remote" de la sección [OGAgent] inclyendo la dirección IP del servidor OpenGnsys.
   - Iniciar el servicio (se iniciará automáticamente en el proceso de arranque):
	NET START OGAgent


