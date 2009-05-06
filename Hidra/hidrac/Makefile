# makefile

# Nombre del proyecto
PROYECTO := hidrac

# Directorios y librerias
DIRS :=
LIBS := -static

# Opciones de compilacion
OPCS := -O0 -g -Wall	# Depuracion
#OPCS := -O3 -Wall	# Optimizacion

# Ficheros objetos
OBJS := fuentes/hidrac.o


all: $(PROYECTO)

$(PROYECTO): $(OBJS)
	g++ $(DIRS) $(LIBS) $(OBJS) -o $(PROYECTO)
#	strip $(PROYECTO)	# Optimizacion

clean:
	rm $(PROYECTO) $(OBJS)

fuentes/%.o: fuentes/%.cpp
	g++ $(OPCS) -c -o"$@" "$<"

fuentes/%.o: fuentes/%.c
	gcc $(OPCS) -c -o"$@" "$<"


