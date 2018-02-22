# makefile

# Nombre del proyecto
PROYECTO := ogAdmClient

# Directorios y librerias
DIRS :=
LIBS := -static

# Opciones de compilacion
#OPCS := -m32 -O0 -g -Wall	# Depuracion
OPCS := -m32 -O3 -Wall		# Optimizacion

# Ficheros objetos
OBJS := sources/ogAdmClient.o

all: $(PROYECTO)

$(PROYECTO): $(OBJS)
	gcc $(OPCS) $(DIRS) $(LIBS) $(OBJS) -o $(PROYECTO)
	strip $(PROYECTO)	# Optimizacion

clean:
	rm -f $(PROYECTO) $(OBJS)

sources/%.o: sources/%.c
	gcc $(OPCS) -I ../../Includes -c -o"$@" "$<"




