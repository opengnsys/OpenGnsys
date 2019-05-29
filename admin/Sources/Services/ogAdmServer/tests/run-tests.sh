curl -X POST http://127.0.0.1:8888/clients -d @post_clients.json
curl -X GET http://127.0.0.1:8888/clients
curl -X POST http://127.0.0.1:8888/wol -d @wol.json
curl -X POST http://127.0.0.1:8888/shell/run -d @post_shell_run.json
curl -X POST http://127.0.0.1:8888/shell/output -d @post_shell_output.json
curl -X POST http://127.0.0.1:8888/session -d @session.json
curl -X POST http://127.0.0.1:8888/poweroff -d @poweroff.json
curl -X POST http://127.0.0.1:8888/reboot -d @reboot.json
curl -X POST http://127.0.0.1:8888/stop -d @stop.json
curl -X POST http://127.0.0.1:8888/refresh -d @refresh.json
