#!/bin/bash

#httd-log-status
cp /etc/lighttpd/lighttpd.conf /etc/lighttpd/lighttpd.conf.back
cp /opt/opengnsys/lib/httpd/lighttpd.conf /etc/lighttpd/
cp /etc/lighttpd/conf-enabled/10-cgi.conf /etc/lighttpd/conf-enabled/10-cgi.conf.back
cp /opt/opengnsys/lib/httpd/10-cgi.conf /etc/lighttpd/conf-enabled/
/etc/init.d/lighttpd start
chmod  755 /opt
mkdir -p /usr/lib/cgi-bin
cp /opt/opengnsys/lib/httpd/* /usr/lib/cgi-bin
#TODO: 
dstat -dn 10 > /tmp/bandwidth &
echo "WAITING" >> $OGLOGSESSION
# http-log-status
