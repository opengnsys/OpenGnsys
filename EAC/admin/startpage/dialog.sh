#!/bin/bash
source /var/EAC/hidra/scripts/hidraEnviron
TMPFILE=/tmp/startpage$$
trap  'rm $TMPFILE;exit;' 1 2
#dialog --menu "Choose one:" 15 30 4 	\
dialog --menu "Opciones de arranque y restauracion" 15 40 6 \
	1 "Arracar XP" \
	2 "Restaurar XP desde servidor" \
	3 "Restaurar XP desde cache" \
	4 "Arrancar linux" \
	5 "Restaurar linux desde servidor" \
	6 "Restaurar linux desde cache" 2>  $TMPFILE
read OPCION < $TMPFILE
echo "La opcion elegida es $OPCION"
case $OPCION in
	1) hidraBoot 1 1 
	;;
	2) echo "Restaurar XP desde servidor"
	   hidraRestorePartitionFromImage 1 1 10.1.14.10 hdimages/XPBasico XPBasico.gzip-1.mcast 
	;;
	3) echo "Restaurar XP desde cache"
	   hidraRestorePartitionFromImage 1 1 $IP hdimages/XPBasico XPBasico.gzip-1.mcast
	;;
	4) hidraBoot 1 2
	;;
	5) echo "Restaurar linux desde servidor"
	   hidraRestorePartitionFromImage 1 2 10.1.14.10 hdimages/linux linux.gzip-2.mcast
	;;
        6) echo "Restaurar linux desde cache"
	   hidraRestorePartitionFromImage 1 2 $IP hdimages/linux linux.gzip-2
        ;;

	*) echo "poweroff"
	;;
esac
rm $TMPFILE
