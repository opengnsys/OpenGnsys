#include "dbi.h"

struct og_dbi *og_dbi_open(struct og_dbi_config *config)
{
	struct og_dbi *dbi;

	dbi = (struct og_dbi *)malloc(sizeof(struct og_dbi));
	if (!dbi)
		return NULL;

	dbi_initialize_r(NULL, &dbi->inst);
	dbi->conn = dbi_conn_new_r("mysql", dbi->inst);
	if (!dbi->conn) {
		free(dbi);
		return NULL;
	}

	dbi_conn_set_option(dbi->conn, "host", config->host);
	dbi_conn_set_option(dbi->conn, "username", config->user);
	dbi_conn_set_option(dbi->conn, "password", config->passwd);
	dbi_conn_set_option(dbi->conn, "dbname", config->database);
	dbi_conn_set_option(dbi->conn, "encoding", "UTF-8");

	if (dbi_conn_connect(dbi->conn) < 0) {
		free(dbi);
		return NULL;
	}

	return dbi;
}

void og_dbi_close(struct og_dbi *dbi)
{
	dbi_conn_close(dbi->conn);
	dbi_shutdown_r(dbi->inst);
	free(dbi);
}
