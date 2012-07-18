#!/bin/bash
echo "Content-type: text/html"
echo ""
echo "<html><head><meta http-equiv='Refresh' content='5,URL=./LogCommand.sh'> <link rel='stylesheet' type='text/css' href='oglive.css' /> <title>Bash as CGI"
echo "</title></head><body>"

$(strings /tmp/command.log > /tmp/command.log.tmp)

echo "<table>"

echo "<tr>"


echo "<TEXTAREA NAME='trackloghead' ROWS='13' COLS='175'>"
#echo "$(head -n 10 /tmp/command.log.tmp | uniq)"
echo "$(grep -v '^Elapsed:\|^Total [Tt]ime:\|^-\|^|\|^bytes\|^\[' /tmp/command.log.tmp | uniq | head -n 10)" 
echo "</TEXTAREA>"

echo "</tr>"


echo "<tr>"

echo "<TEXTAREA NAME='tracklogtail' ROWS='2' COLS='175'>"
#echo "$(tail -n 5 /tmp/command.log.tmp | uniq)"
echo "$(grep '^Elapsed:\|^Total [Tt]ime:\|^-\|^|\|^bytes' /tmp/command.log.tmp | uniq | tail -n 2)" 
echo "</TEXTAREA>"


echo "</tr>"

echo "</table>"


echo "</body></html>"