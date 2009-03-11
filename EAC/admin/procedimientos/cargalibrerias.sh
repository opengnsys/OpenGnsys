#!/bin/bash
###########################################################
#####Cargador de librerias de funciones v0.0.7 para Advanced Deploy enViorenment###########
# Liberado bajo licencia GPL <http://www.gnu.org/licenses/gpl.html>################
############# 2008 Antonio Doblas Viso##########################
########### Universidad de Malaga (Spain)############################
##########################################################


for i in `ls -1 /var/EAC/admin/librerias/*.lib`
do
source $i
done
