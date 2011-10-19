#!/bin/bash
echo "Content-type: text/html"
echo ""
echo "<html><head> <meta http-equiv='Refresh' content='5,URL=./LogSession.sh'> <title>Bash as CGI"
echo "</title>"

echo "<style type='text/css'>"
echo "<!--"
echo "TEXTAREA {"
echo "background-color: navy;"
echo "border: black 2px solid;"
echo "color: white;"
echo "font-family: arial, verdana, ms sans serif;"
echo "font-size: 8pt;"
echo "font-weight: normal"
echo "} "
echo "-->"
echo "</style>"



echo "</head><body>"

echo "<TEXTAREA NAME='contenido' ROWS='115' COLS='175'  >"
echo "$(cat /tmp/session.log | uniq )"
echo "</TEXTAREA>"



echo "</body></html>"

