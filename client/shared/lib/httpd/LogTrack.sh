#!/bin/bash
echo "Content-type: text/html"
echo ""
echo "<html><head><meta http-equiv='Refresh' content='5,URL=./LogTrack.sh'> <link rel='stylesheet' type='text/css' href='oglive.css' /> <title>Bash as CGI"
echo "</title></head><body>"

$(strings /tmp/track.log > /tmp/track.log.tmp)

echo "<table>"

echo "<tr>"


echo "<TEXTAREA NAME='trackloghead' ROWS='10' COLS='175'>"
echo "$(head -n 10 /tmp/track.log.tmp)"
echo "</TEXTAREA>"

echo "</tr>"


echo "<tr>"

echo "<TEXTAREA NAME='tracklogtail' ROWS='10' COLS='175'>"
echo "$(tail -n 5 /tmp/track.log.tmp)"
echo "</TEXTAREA>"


echo "</tr>"

echo "</table>"


echo "</body></html>"