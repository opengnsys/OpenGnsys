
#!/bin/bash
echo "Content-type: text/html"
echo ""
#echo "<html><head><meta http-equiv='Refresh' content='2,URL=./example3.sh'><title>Bash as CGI"
echo "<html><head><title>OpenGnsys Client</title></head><body>"

echo "<h1>  host $(hostname -s)</h1> "
echo "<IFRAME SRC='bandwidth.sh' WIDTH=250 HEIGHT=80> <A HREF="bandwidth.sh">link</A> </IFRAME> "


echo "<IFRAME SRC='LogStandar.sh' WIDTH=850 HEIGHT=200> <A HREF="LogStandar.sh">link</A> </IFRAME>"
echo "<IFRAME SRC='LogTrack.sh' WIDTH=850 HEIGHT=250> <A HREF="LogTrack.sh">link</A> </IFRAME>"

echo "</body>