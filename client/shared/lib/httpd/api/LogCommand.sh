#! /bin/bash -wT

# (internal) routine to store POST data
function cgi_get_POST_vars() {
    # check content type
    # FIXME: not sure if we could handle uploads with this..
    #if [ "${CONTENT_TYPE}" != "application/x-www-form-urlencoded" ]; then
    #    return
    #fi
    # save POST variables (only first time this is called)
    [ -z "$QUERY_STRING_POST" \
      -a "$REQUEST_METHOD" = "POST" -a ! -z "$CONTENT_LENGTH" ] && \
        read -n $CONTENT_LENGTH QUERY_STRING_POST
    
    #echo $QUERY_STRING_POST
    return
}


function cgi_decodevar() {
    local url_encoded="${1//+/ }"
    printf '%b' "${url_encoded//%/\\x}"
}

 
# routine to get variables from http requests
# usage: cgi_getvars method varname1 [.. varnameN]
# method is either GET or POST or BOTH
# the magic varible name ALL gets everything
function cgi_getvars() {
    [ $# -lt 2 ] && return
    local q="" p k v s
    # get query
    case $1 in
        GET)
            [ ! -z "${QUERY_STRING}" ] && q="${QUERY_STRING}&"
            ;;
        POST)
            cgi_get_POST_vars
            [ ! -z "${QUERY_STRING_POST}" ] && q="${QUERY_STRING_POST}&"
            ;;
        BOTH)
            [ ! -z "${QUERY_STRING}" ] && q="${QUERY_STRING}&"
            cgi_get_POST_vars
            [ ! -z "${QUERY_STRING_POST}" ] && q="${q}${QUERY_STRING_POST}&"


            ;;
    esac
    shift
    s=" $* "
    # parse the query data
    while [ ! -z "$q" ]; do
        p="${q%%&*}"  # get first part of query string
        k="${p%%=*}"  # get the key (variable name) from it
        v="${p#*=}"   # get the value from it
        q="${q#$p&*}" # strip first part from query string
        # decode and evaluate var if requested
        [ "$1" = "ALL" -o "${s/ $k /}" != "$s" ] && \
            eval "$k=\"$(cgi_decodevar $v | sed 's/[\"\\\$]/\\&/g')\""
	
    done
    return
}

function debug() {
/bin/cat <<EOF
Content-type: text/html


<html>
<head><title>Test</title></head>
<body>
<h1>Test for Bash CGI-Scripting</h1>
<h2>POST values from Standard-In</h2>
<pre>
$(</dev/stdin)
</pre>
<h2>Values</h2>
<pre>
Auth: $AUTH
Method: $METHOD
id: $id
script: $script
redirect_uri: $redirect_uri
</pre>
<h2>Environment</h2>
<pre>$(env)</pre>
</body>
</html
EOF

}

AUTH=$HTTP_AUTHORIZATION
METHOD=$REQUEST_METHOD

#echo $AUTH
#echo $METHOD
#echo $CONTENT_LENGTH

cgi_getvars BOTH ALL

debug



#exec >&-
#exec 2>&-

# Una vez comprobado que el request es correcto, ejecutamos los comandos y liberamos la conexion
nohup /bin/bash -c "./ogAgent.sh $id \"$script\" \"$redirect_uri\"" &>/dev/null & 

