# makefile

# Nombre del proyecto
PROYECTO := ogAdmLnxClient

# Directorios y librerias
DIRS :=
LIBS := -static

# Opciones de compilacion
#OPCS := -m32 -O0 -g -Wall	# Depuracion
OPCS := -m32 -O3 -Wall		# Optimizacion

# Ficheros objetos
OBJS := sources/ogAdmLnxClient.o

all: $(PROYECTO)

$(PROYECTO): $(OBJS)
	g++ $(OPCS) $(DIRS) $(LIBS) $(OBJS) -o $(PROYECTO)
	strip $(PROYECTO)	# Optimizacion

clean:
	rm $(PROYECTO) $(OBJS)

sources/%.o: sources/%.cpp
	g++ $(OPCS) -c -o"$@" "$<"

sources/%.o: sources/%.c
	gcc $(OPCS) -I ../../Includes -c -o"$@" "$<"




