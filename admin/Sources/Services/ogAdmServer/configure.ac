AC_INIT(ogAdmServer, 1.1.1, opengnsys-devel@listas.unizar.es)
AC_CONFIG_AUX_DIR([build-aux])
AC_PREFIX_DEFAULT(/opt/opengnsys)

AC_CANONICAL_HOST
AC_CONFIG_MACRO_DIR([m4])
AM_INIT_AUTOMAKE([-Wall foreign subdir-objects tar-pax no-dist-gzip dist-bzip2 1.6])

m4_ifdef([AM_SILENT_RULES], [AM_SILENT_RULES([yes])])

AC_PROG_CC
AC_PROG_INSTALL
AC_PROG_LN_S

case "$host" in
*-*-linux*) ;;
*) AC_MSG_ERROR([Linux only, sorry!]);;
esac

AC_CHECK_LIB([jansson], [json_object], , AC_MSG_ERROR([libjansson not found]))
AC_CHECK_LIB([dbi], [dbi_initialize], , AC_MSG_ERROR([libdbi not found]))
AC_CHECK_LIB([ev], [ev_loop_new], , AC_MSG_ERROR([libev not found]))

AC_CONFIG_FILES([Makefile])
AC_OUTPUT
