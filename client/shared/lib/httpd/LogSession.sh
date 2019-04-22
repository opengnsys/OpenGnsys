#!/bin/bash
OGLOGSESSION=${OGLOGSESSION:-"/tmp/session.log"}
echo "Content-type: text/html"
echo ""
echo "<html><head>"
echo "   <meta charset='utf-8'>"
echo "   <meta http-equiv='Refresh' content='5,URL=./LogSession.sh'>"
echo "   <title>Bash as CGI</title>"

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
uniq $OGLOGSESSION
echo "</TEXTAREA>"

echo "</body></html>"

