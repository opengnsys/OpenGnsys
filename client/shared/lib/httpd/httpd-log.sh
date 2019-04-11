#!/bin/bash

echo "Content-type: text/html"
echo ""
echo "<html><head><title>OpenGnsys Client</title>"
echo "   <meta charset='utf-8'>"
echo "</head><body>"

echo "<h1> $(./httpd-runengine.sh 'ogEcho $MSG_HTTPLOG_NOUSE').</br>host $(hostname -s)</h1> "

echo "<IFRAME SRC='bandwidth.sh' WIDTH=250 HEIGHT=90> <A HREF="bandwidth.sh">link</A> </IFRAME> "
echo "<IFRAME SRC='cache.sh'     WIDTH=590 HEIGHT=90><A HREF="cache.sh">link</a> </IFRAME><br>"

echo "<IFRAME SRC='LogSession.sh' WIDTH=850 HEIGHT=230> <A HREF="LogSession.sh">link</A> </IFRAME>"
echo "<IFRAME SRC='LogCommand.sh' WIDTH=850 HEIGHT=280> <A HREF="LogCommand.sh">link</A> </IFRAME>"

echo "</body>
