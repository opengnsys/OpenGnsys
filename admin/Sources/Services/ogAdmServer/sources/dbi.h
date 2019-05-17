#ifndef __OG_DBI
#define __OG_DBI

#include <dbi/dbi.h>

struct og_dbi_config {
	const char	*user;
	const char	*passwd;
	const char	*host;
	const char	*database;
};

struct og_dbi {
	dbi_conn	conn;
	dbi_inst	inst;
};

struct og_dbi *og_dbi_open(struct og_dbi_config *config);
void og_dbi_close(struct og_dbi *db);

#endif
