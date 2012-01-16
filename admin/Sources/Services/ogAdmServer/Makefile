# makefile

# Nombre del proyecto
PROYECTO := ogAdmServer

# Directorio de instalación
INSTALL_DIR := /opt/opengnsys

# Opciones de compilacion
CFLAGS := $(shell mysql_config --cflags)
CFLAGS += -O0 -g -Wall -I../../Includes	# Depuracion
#CFLAGS += -O3 -Wall -I../../Includes	# Optimizacion
CPPFLAGS := $(CFLAGS)

# Opciones de linkado
LDFLAGS := -Wl,--no-as-needed $(shell mysql_config --libs) -lpthread

# Ficheros objetos
OBJS := ../../Includes/Database.o sources/ogAdmServer.o 


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
	g++ $(CPPFLAGS) -c -o"$@" "$<"
	
sources/%.o: sources/%.c
	gcc $(CFLAGS) -c -o"$@" "$<"


