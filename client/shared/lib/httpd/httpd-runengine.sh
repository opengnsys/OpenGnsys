# Exportar parámetros del kernel.

for i in $(cat /proc/cmdline); do
       echo $i | grep -q "=" && export $i
done


. /opt/opengnsys/etc/preinit/loadenviron.sh > /dev/null
eval $1
