
#!/bin/bash
echo "Content-type: text/html"
echo ""
#echo "<html><head><meta http-equiv='Refresh' content='2,URL=./example3.sh'><title>Bash as CGI"
echo "<html><head><title>OpenGnsys Client</title></head><body>"

echo "<h1> $(./httpd-runengine.sh 'ogEcho $MSG_HTTPLOG_NOUSE'  ) .    host $(hostname -s)</h1> "

echo "<IFRAME SRC='bandwidth.sh' WIDTH=250 HEIGHT=80> <A HREF="bandwidth.sh">link</A> </IFRAME> "


echo "<IFRAME SRC='LogSession.sh' WIDTH=850 HEIGHT=230> <A HREF="LogSession.sh">link</A> </IFRAME>"
echo "<IFRAME SRC='LogCommand.sh' WIDTH=850 HEIGHT=250> <A HREF="LogCommand.sh">link</A> </IFRAME>"

echo "</body>