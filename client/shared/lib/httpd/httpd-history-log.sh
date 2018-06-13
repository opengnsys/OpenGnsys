#!/bin/bash

echo "Content-type: text/html"
echo ""
echo "<html><head><title>OpenGnsys Client</title>"
echo "   <meta charset='utf-8'>"
echo "<script>function scrollDown(){ setTimeout(function() {window.scrollTo(0,document.body.scrollHeight);}, 100);}</script>"
echo "</head><body onload=\"scrollDown()\">"

IP=$HTTP_HOST
LOG=$(cat /opt/opengnsys/log/$IP.log)
echo "<div><pre>$LOG</pre></div>"
echo "</body>"
