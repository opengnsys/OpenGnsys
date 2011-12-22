#!/bin/bash

echo "export OGLOGCOMMAND=/tmp/command.log" >> /etc/profile.d/loadenviron.sh
echo "export OGLOGSESSION=/tmp/session.log" >> /etc/profile.d/loadenviron.sh
    export OGLOGCOMMAND=/tmp/command.log
    export OGLOGSESSION=/tmp/session.log

#httd-log-status
cp /etc/lighttpd/lighttpd.conf /etc/lighttpd/lighttpd.conf.back
cp /opt/opengnsys/lib/httpd/lighttpd.conf /etc/lighttpd/
cp /etc/lighttpd/conf-enabled/10-cgi.conf /etc/lighttpd/conf-enabled/10-cgi.conf.back
cp /opt/opengnsys/lib/httpd/10-cgi.conf /etc/lighttpd/conf-enabled/
/etc/init.d/lighttpd start
chmod  755 /opt
cp /opt/opengnsys/lib/httpd/* /usr/lib/cgi-bin
#TODO: 
dstat -dn 10 > /tmp/bandwidth &
#Se pasan al loadenviro para su uso en ssh
#export OGLOGSESSION=/tmp/session.log
#export OGLOGCOMMAND=/tmp/command.log
touch  $OGLOGCOMMAND
touch  $OGLOGSESSION
touch  ${OGLOGCOMMAND}.tmp
chmod 777 $OGLOGCOMMAND
chmod 777 $OGLOGSESSION
chmod 777 ${OGLOGCOMMAND}.tmp
touch /tmp/menu.tmp
chmod 777 /tmp/menu.tmp
echo "WAITING" >> $OGLOGSESSION
# http-log-status