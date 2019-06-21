API_KEY="07b3bfe728954619b58f0107ad73acc1"

curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/clients -d @post_clients.json
curl -X GET -H "Authorization: $API_KEY" http://127.0.0.1:8888/clients
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/wol -d @wol.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/shell/run -d @post_shell_run.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/shell/output -d @post_shell_output.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/session -d @session.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/poweroff -d @poweroff.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/reboot -d @reboot.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/stop -d @stop.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/refresh -d @refresh.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/hardware -d @post_clients.json
