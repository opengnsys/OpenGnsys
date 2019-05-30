#!/usr/bin/env python3

import subprocess, glob, os

sql_data = "INSERT INTO aulas (nombreaula, idcentro, urlfoto, grupoid, ubicacion, puestos, modomul, ipmul, pormul, velmul, router, netmask, ntp, dns, proxy, modp2p, timep2p) VALUES  ('Aula virtual', 1, 'aula.jpg', 0, 'Despliegue virtual con Vagrant.', 5, 2, '239.194.2.11', 9000, 70, '192.168.56.1', '255.255.255.0', '', '', '', 'peer', 30); INSERT INTO ordenadores (nombreordenador, ip, mac, idaula, idrepositorio, idperfilhard, idmenu, idproautoexec, grupoid, router, mascara, arranque, netiface, netdriver, fotoord) VALUES ('pc2', '192.168.2.1', '0800270E6501', 1, 1, 0, 0, 0, 0, '192.168.56.1', '255.255.255.0', '00unknown', 'eth0', 'generic', 'fotoordenador.gif'), ('pc2', '192.168.2.2', '0800270E6502', 1, 1, 0, 0, 0, 0, '192.168.56.1', '255.255.255.0', '00unknown', 'eth0', 'generic', 'fotoordenador.gif');"

sql_create_user = "CREATE USER 'test-db'@'hostname'; GRANT ALL PRIVILEGES ON *.* To 'test-db'@'hostname' IDENTIFIED BY 'test-db';"

sql_delete_user = "DROP USER 'test-db'@'hostname';"

def start_mysql():

    subprocess.run(['mysqladmin', 'drop', '-f', 'test-db'],
            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    subprocess.run(['mysqladmin', 'create', 'test-db'])
    subprocess.run('mysql --default-character-set=utf8 test-db < ../../../../Database/ogAdmBD.sql', shell=True)
    subprocess.run(['mysql', '-D', 'test-db', '-e', sql_data])
    subprocess.run(['mysql', '-D', 'test-db', '-e', sql_create_user])

    return

def stop_mysql():

    subprocess.run(['mysql', '-D', 'test-db', '-e', sql_delete_user])
    subprocess.run(['mysqladmin', 'drop', '-f', 'test-db'])

    return

if os.getuid() is not 0:
    print('You need to be root to run these tests :-)')
    exit()

start_mysql();

subprocess.Popen(['../ogAdmServer', '-f', 'config/ogAdmServer.cfg'])

for filename in glob.iglob('units/**'):
    subprocess.run(['python3', filename, '-v'])

stop_mysql();

subprocess.run(['pkill', 'ogAdmServer'])
