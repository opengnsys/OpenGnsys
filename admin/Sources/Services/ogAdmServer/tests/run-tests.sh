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
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/software -d @post_clients.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/image/create -d @create_image.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/image/restore -d @restore_image.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/setup -d @setup_image.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/image/create/basic -d @create_basic_image.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/image/create/incremental -d @create_incremental_image.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/image/restore/basic -d @restore_basic_image.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/image/restore/incremental -d @restore_incremental_image.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/run/schedule -d @run_schedule.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/task/run -d @task.json
curl -X POST -H "Authorization: $API_KEY" http://127.0.0.1:8888/schedule/create -d @create_schedule.json
