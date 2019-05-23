curl -X POST http://127.0.0.1:8888/clients -d @post_clients.json
curl -X GET http://127.0.0.1:8888/clients
curl -X POST http://127.0.0.1:8888/wol -d @wol.json
curl -X POST http://127.0.0.1:8888/shell/run -d @post_shell_run.json
