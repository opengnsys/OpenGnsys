
OGAgent: agentes OpenGnsys para sistemas operativos
---------------------------------------------------


Instalar el paquete correspondiente al sistema operativo de los equipos cliente:

 - ogagent_Version_all.deb      OGAgent para sistemas Ubuntu y /Debian
 - ogagent-Version.noarch.rpm   OGAgent para sistemas Red Hat y Fedora
 - ogagent-opensuse-Version.noarch.rpm   OGAgent para sistemas SuSE y OpenSuSE
 - OGAgentSetup-Version.exe     OGAgent para sistemas Windows


Instalación manual de los agentes.

 - Ubuntu, Debian y derivados:
   - Instalar dependencias:
	sudo apt-get install -y libxss1 policykit-1 python python-requests python-qt4 python-six python-prctl
   - Instalar agente:
	sudo dpkg -i ogagent_Version_all.deb
   - Configurar el agente:
	sudo sed -i "0,/remote=/ s,remote=.*,remote=https://IPServidorOpenGnsys/opengnsys/rest/," /usr/share/OGAgent/cfg/ogagent.cfg
   - Iniciar el servicio (se iniciará automáticamente en el proceso de arranque):
	sudo service ogagent start

 - Red Hat, Fedora y derivados (como root):
	(en preparación)

 - OpenSuSE:
	(en preparación)

 - Windows:
	(en preparación)


