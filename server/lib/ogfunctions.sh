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

# Metafunction to check if JSON result exists.
JQ=$(which jq 2>/dev/null) || raiseError notfound "Need to install \"jq\"."
function jq() {
    local OUTPUT
    OUTPUT=$($JQ "$@") || return $?
    [[ "$OUTPUT" = "null" ]] && return 1
    echo "$OUTPUT"
}

