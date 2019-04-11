#!/bin/bash
echo "Content-type: text/html"
echo ""
echo "<html><head><meta http-equiv='Refresh' content='11,URL=./bandwidth.sh'> <link rel='stylesheet' type='text/css' href='oglive.css' /> <title>Bash as CGI </title></head><body>"

echo "<TEXTAREA class='example1' NAME='contenido' ROWS='35' COLS='50'  >"
echo "  DISK     ||   NET " 
echo "Read:Write || Recv:Send " 
echo $(tail -n1  /tmp/bandwidth) 
#echo " $(dstat -dn  -f 1 1 ) "
echo "</TEXTAREA>"
echo "</body></html>"