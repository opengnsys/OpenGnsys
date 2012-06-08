#!/bin/bash


#!/bin/bash
echo "Content-type: text/html"
echo ""

echo "<html><head><title>OpenGnsys Client</title></head><body>"

$(wget http://172.17.9.205/opengnsys/varios/menubrowser.php -O /tmp/menu.tmp)
echo "$(cat /tmp/menu.tmp)"

echo "</body>

