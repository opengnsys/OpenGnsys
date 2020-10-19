#!/bin/bash
#/**
#@file    ogfunctions.sh
#@brief   Generic functions for OpenGnsys Server and OpenGnsys Repository.
#@version 1.1.1 - Initial version
#@author  Ramón M. Gómez, ETSII Universidad de Sevilla
#@date    2017-10-08
#*/


# Showing an error message.
function raiseError() {
    [ "$PROG" ] || PROG="$(basename "$0")"
    case "$1" in
        usage)
            echo "$PROG: Usage error: Type \"$PROG help\"" >&2
            exit 1 ;;
        notfound)
            echo "$PROG: Resource not found: $2" >&2
            exit 2 ;;
        access)
            echo "$PROG: Access error: $2" >&2
            exit 3 ;;
        download)
            echo "$PROG: Download error: $2" >&2
            exit 4 ;;
        cancel)
            echo "$PROG: Operation cancelled: $2" >&2
            exit 5 ;;
        *)
            echo "$PROG: Unknown error" >&2
            exit 1 ;;
    esac
}

# Showing help message.
function help() {
    [ -n "$1" ] && DESCRIPTION="$1" || DESCRIPTION=$(grep "^#@brief" "$0" | cut -f2- -d" ")
    shift
    if [ -n "$1" ]; then
         USAGE="$1"
         shift
    else
         USAGE=$(grep "^#@usage" "$0" | cut -f2- -d" ")
         [ -n "$USAGE" ] && PARAMS=$(awk '$1=="#@param" {sub($1,""); print "\t",$0}' "$0")
    fi
    [ "$PROG" ] || PROG="$(basename "$0")"
    # Showing help.
    echo "$PROG: ${DESCRIPTION:-"no description"}"
    echo "Usage: ${USAGE:-"no usage info"}"
    [ -n "$PARAMS" ] && echo -e "$PARAMS"
    if [ -n "$*" ]; then
        echo "Examples:"
        while (( "$#" )); do
            echo -e "\t$1"
            shift
        done
    fi
    exit 0
}

# Showing script version number.
function version() {
    awk '/^#@version/ {v=$2} END {if (v) print v}' "$0"
    exit 0
}

# Functions to manage a service.
function restart() {
    _service restart "$1"
}
function start() {
    _service start "$1"
}
function stop() {
    _service stop "$1"
}

# Execute database operation.
function dbexec () {
    MYCNF=$(mktemp)
    trap "rm -f $MYCNF" 0 1 2 3 6 9 15
    touch $MYCNF
    chmod 600 $MYCNF
    cat << EOT > $MYCNF
[client]
user=$USUARIO
password=$PASSWORD
EOT
    mysql --defaults-extra-file="$MYCNF" -D "$CATALOG" -s -N -e "$1" || \
        raiseError access "Cannot access the databse"
    rm -f "$MYCNF"
}

# Returns parent process name (program or script)
function getcaller () {
    basename "$(COLUMNS=200 ps hp $PPID -o args | \
                awk '{if ($1~/bash/ && $2!="") { print $2; }
                      else { sub(/^-/,"",$1); print $1; } }')"
}

### Meta-functions and private functions.

# Metafunction to check if JSON result exists.
JQ=$(which jq 2>/dev/null) || raiseError notfound "Need to install \"jq\"."
function jq() {
    local OUTPUT
    OUTPUT=$($JQ "$@") || return $?
    [[ "$OUTPUT" = "null" ]] && return 1
    echo "$OUTPUT"
}

function source_json_config() {
    FILE=$1
    export ServidorAdm=$(jq -r ".rest.ip" $FILE)
    export PUERTO=$(jq -r ".rest.port" $FILE)
    export APITOKEN=$(jq -r ".rest.api_token" $FILE)
    export USUARIO=$(jq -r ".database.user" $FILE)
    export PASSWORD=$(jq -r ".database.pass" $FILE)
    export datasource=$(jq -r ".database.ip" $FILE)
    export CATALOG=$(jq -r ".database.name" $FILE)
    export INTERFACE=$(jq -r ".wol.interface" $FILE)
}

# Private function to acts on a service (do not use directly).
function _service() {
    local ACTION="$1"
    local SERVICE="$2"
    if which systemctl 2>/dev/null; then
        systemctl "$ACTION" "$SERVICE"
    elif which service 2>/dev/null; then
        service "$SERVICE" "$ACTION"
    elif [ -x /etc/init.d/"$SERVICE" ]; then
        /etc/init.d/"$SERVICE" "$ACTION"
    else
        raiseError notfound "Service $SERVICE"
    fi
}

