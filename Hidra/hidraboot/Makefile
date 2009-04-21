# makefile

# Nombre del proyecto
PROYECTO := hidraboot

# Opciones de compilacion
CFLAGS := -O0 -g -Wall -I../includes		# Depuracion
#CFLAGS := -O3 -Wall				# Optimizacion
CPPFLAGS := $(CFLAGS)

# Opciones de linkado
LDFLAGS := -L/usr/lib -lpthread -lmysqlclient	# Ubuntu
#LDFLAGS := -L/usr/lib -L/usr/lib/mysql \
	   -lpthread -lmysqlclient		# Fedora

# Ficheros objetos
OBJS := ../includes/Database.o ../includes/encriptacion.o fuentes/hidraboot.o 


all: $(PROYECTO)

$(PROYECTO): $(OBJS)
	g++ $(LDFLAGS) $(OBJS) -o $(PROYECTO)
#	strip $(PROYECTO)			# Optimizacion

install: $(PROYECTO)
	cp $(PROYECTO) /usr/local/sbin
	cp $(PROYECTO).cfg /usr/local/etc
 
clean:
	rm -f $(PROYECTO) $(OBJS)

uninstall: clean
	rm -f /usr/local/sbin/$(PROYECTO) /usr/local/etc/$(PROYECTO).cfg

fuentes/%.o: fuentes/%.cpp
	g++ $(CPPFLAGS) -c -o"$@" "$<"

fuentes/%.o: fuentes/%.c
	gcc $(CFLAGS) -c -o"$@" "$<"


