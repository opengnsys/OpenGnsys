# makefile

# Nombre del proyecto
PROYECTO := ogAdmAgent

# Directorio de instalación
INSTALL_DIR := /opt/opengnsys

# Opciones de compilacion
CFLAGS := -O0 -g -Wall -I../includes	# Depuracion
#CFLAGS := -O3 -Wall			# Optimizacion
CPPFLAGS := $(CFLAGS)

# Opciones de linkado
LDFLAGS := -L/usr/lib -L/usr/lib/mysql -lpthread -lmysqlclient

# Ficheros objetos
OBJS := ../includes/Database.o sources/ogAdmAgent.o 


all: $(PROYECTO)

$(PROYECTO): $(OBJS)
	g++ $(LDFLAGS) $(OBJS) -o $(PROYECTO)
#	strip $(PROYECTO)		# Optimizacion

install: $(PROYECTO)
	cp $(PROYECTO) $(INSTALL_DIR)/sbin
	cp $(PROYECTO).cfg $(INSTALL_DIR)/etc
 
clean:
	rm -f $(PROYECTO) $(OBJS)

uninstall: clean
	rm -f /usr/local/sbin/$(PROYECTO) /usr/local/etc/$(PROYECTO).cfg

sources/%.o: sources/%.cpp
	g++ $(CPPFLAGS) -I ../includes -c -o"$@" "$<"
	
sources/%.o: sources/%.c
	gcc $(CFLAGS) -I ../includes -c -o"$@" "$<"


