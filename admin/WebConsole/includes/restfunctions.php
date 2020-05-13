
<?php

define('OG_REST_URL', 'http://127.0.0.1:8888/');

define('GET', 1);
define('POST', 2);
define('CUSTOM', 3);

define('OG_REST_CMD_CLIENTS', 'clients');
define('OG_REST_CMD_WOL', 'wol');
define('OG_REST_CMD_SESSION', 'session');
define('OG_REST_CMD_RUN', 'shell/run');
define('OG_REST_CMD_OUTPUT', 'shell/output');
define('OG_REST_CMD_POWEROFF', 'poweroff');
define('OG_REST_CMD_REBOOT', 'reboot');
define('OG_REST_CMD_STOP', 'stop');
define('OG_REST_CMD_REFRESH', 'refresh');
define('OG_REST_CMD_HARDWARE', 'hardware');
define('OG_REST_CMD_SOFTWARE', 'software');
define('OG_REST_CMD_CREATE_IMAGE', 'image/create');
define('OG_REST_CMD_RESTORE_IMAGE', 'image/restore');
define('OG_REST_CMD_SETUP', 'setup');
define('OG_REST_CMD_CREATE_BASIC_IMAGE', 'image/create/basic');
define('OG_REST_CMD_CREATE_INCREMENTAL_IMAGE', 'image/create/incremental');
define('OG_REST_CMD_RESTORE_BASIC_IMAGE', 'image/restore/basic');
define('OG_REST_CMD_RESTORE_INCREMENTAL_IMAGE', 'image/restore/incremental');
define('OG_REST_CMD_RUN_SCHEDULE', 'run/schedule');
define('OG_REST_CMD_RUN_TASK', 'task/run');
define('OG_REST_CMD_CREATE_SCHEDULE', 'schedule/create');
define('OG_REST_CMD_DELETE_SCHEDULE', 'schedule/delete');
define('OG_REST_CMD_UPDATE_SCHEDULE', 'schedule/update');
define('OG_REST_CMD_GET_SCHEDULE', 'schedule/get');

define('OG_REST_PARAM_CLIENTS', 'clients');
define('OG_REST_PARAM_ADDR', 'addr');
define('OG_REST_PARAM_MAC', 'mac');
define('OG_REST_PARAM_DISK', 'disk');
define('OG_REST_PARAM_PART', 'partition');
define('OG_REST_PARAM_RUN', 'run');
define('OG_REST_PARAM_TYPE', 'type');
define('OG_REST_PARAM_STATE', 'state');
define('OG_REST_PARAM_NAME', 'name');
define('OG_REST_PARAM_REPOS', 'repository');
define('OG_REST_PARAM_ID', 'id');
define('OG_REST_PARAM_CODE', 'code');
define('OG_REST_PARAM_PROFILE', 'profile');
define('OG_REST_PARAM_CACHE', 'cache');
define('OG_REST_PARAM_CACHE_SIZE', 'cache_size');
define('OG_REST_PARAM_FILE_SYSTEM', 'filesystem');
define('OG_REST_PARAM_SIZE', 'size');
define('OG_REST_PARAM_FORMAT', 'format');
define('OG_REST_PARAM_PARTITION_SETUP', 'partition_setup');
define('OG_REST_PARAM_SYNC_PARAMS', 'sync_params');
define('OG_REST_PARAM_SYNC', 'sync');
define('OG_REST_PARAM_DIFF', 'diff');
define('OG_REST_PARAM_REMOVE', 'remove');
define('OG_REST_PARAM_COMPRESS', 'compress');
define('OG_REST_PARAM_CLEANUP', 'cleanup');
define('OG_REST_PARAM_CLEANUP_CACHE', 'cleanup_cache');
define('OG_REST_PARAM_REMOVE_DST', 'remove_dst');
define('OG_REST_PARAM_PATH', 'path');
define('OG_REST_PARAM_DIFF_ID', 'diff_id');
define('OG_REST_PARAM_DIFF_NAME', 'diff_name');
define('OG_REST_PARAM_METHOD', 'method');
define('OG_REST_PARAM_ECHO', 'echo');
define('OG_REST_PARAM_TASK', 'task');
define('OG_REST_PARAM_WHEN', 'when');
define('OG_REST_PARAM_YEARS', 'years');
define('OG_REST_PARAM_MONTHS', 'months');
define('OG_REST_PARAM_WEEKS', 'weeks');
define('OG_REST_PARAM_WEEK_DAYS', 'week_days');
define('OG_REST_PARAM_DAYS', 'days');
define('OG_REST_PARAM_HOURS', 'hours');
define('OG_REST_PARAM_AM_PM', 'am_pm');
define('OG_REST_PARAM_MINUTES', 'minutes');

define('TYPE_COMMAND', 1);
define('TYPE_PROCEDURE', 2);
define('TYPE_TASK', 3);
define('OG_SCHEDULE_COMMAND', 'command');
define('OG_SCHEDULE_PROCEDURE', 'procedure');
define('OG_SCHEDULE_TASK', 'task');

$conf_file = parse_ini_file(__DIR__ . '/../../etc/ogAdmServer.cfg');
define('OG_REST_API_TOKEN', 'Authorization: ' . $conf_file['APITOKEN']);

function common_request($command, $type, $data = null) {

	$json = json_encode($data);

	$service_url = OG_REST_URL.$command;

	$curl = curl_init($service_url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		OG_REST_API_TOKEN,
	));

	switch ($type) {
		default:
		case GET:
			break;
		case POST:
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
	}

	$curl_response = curl_exec($curl);
	$info = curl_getinfo($curl);

	if ($curl_response === false || $info['http_code'] != 200) {
		syslog(LOG_ERR, 'error occured during curl exec. Additioanl info: ' . print_r($info, TRUE));
		return 0;
	}

	curl_close($curl);

	syslog(LOG_INFO, 'response '.$command.' ok!');

	return json_decode($curl_response, true);
}


function shell($case, $string_ips, $command) {

	$ips = explode(';',$string_ips);

	switch ($case) {
		case 1:
			$data = array(OG_REST_PARAM_CLIENTS => $ips,
				      OG_REST_PARAM_RUN => $command,
				      OG_REST_PARAM_ECHO => true);
			$command = OG_REST_CMD_RUN;
			break;
		default:
		case 2:
			$data = array(OG_REST_PARAM_CLIENTS => $ips);
			$command = OG_REST_CMD_OUTPUT;
			break;
		case 3:
			$decoded_cmds = rawurldecode(substr($command, 4));
			$command = substr($decoded_cmds, 0, -2);
			$data = array(OG_REST_PARAM_CLIENTS => $ips,
				      OG_REST_PARAM_RUN => $command,
				      OG_REST_PARAM_ECHO => false);
			$command = OG_REST_CMD_RUN;
			break;
		case 4:
			$decoded_cmds = rawurldecode(substr($command, 4));
			$command = substr($decoded_cmds, 0, -1);
			$data = array(OG_REST_PARAM_CLIENTS => $ips,
				      OG_REST_PARAM_RUN => $command,
				      OG_REST_PARAM_ECHO => false);
			$command = OG_REST_CMD_RUN;
	}

	$result = common_request($command, POST,
		$data)[OG_REST_PARAM_CLIENTS][0]['output'];

	return (is_null($result) ? '1' : $result);
}

function clients($case, $ips) {

	switch ($case) {
		case 1:
			$type = POST;
			$data = array(OG_REST_PARAM_CLIENTS => $ips);
			break;
		case 2:
			$type = GET;
			$data = null;
			break;
	}

	$result = common_request(OG_REST_CMD_CLIENTS, $type, $data);

	$trama_notificacion = "";
	if (isset($result[OG_REST_PARAM_CLIENTS])) {
		foreach ($result[OG_REST_PARAM_CLIENTS] as $client) {
			$trama_notificacion .= $client[OG_REST_PARAM_ADDR].'/'.
				$client[OG_REST_PARAM_STATE].';';
		}
	}

	return $trama_notificacion;
}

function wol($type_wol, $macs, $ips) {

	switch ($type_wol) {
		default:
		case 1:
			$wol = 'broadcast';
			break;
		case 2:
			$wol = 'unicast';
	}

	$clients = array();

	for($i=0; $i<count($macs); $i++) {
		$clients[] = array(OG_REST_PARAM_ADDR => $ips[$i],
			OG_REST_PARAM_MAC => $macs[$i]);
	}

	$data = array(OG_REST_PARAM_TYPE => $wol,
		OG_REST_PARAM_CLIENTS => $clients);

	common_request(OG_REST_CMD_WOL, POST, $data);
}

function session($string_ips, $params) {

	preg_match_all('!\d{1}!', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];

	$data = array(OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk, OG_REST_PARAM_PART => $part);

	common_request(OG_REST_CMD_SESSION, POST, $data);
}

function create_image($string_ips, $params) {

	preg_match_all('/(?<=\=)(.*?)(?=\r)/', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];
	$code = $matches[0][2];
	$id = $matches[0][3];
	$name = $matches[0][4];
	$repos = $matches[0][5];

	$data = array(OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk,
		OG_REST_PARAM_PART => $part,
		OG_REST_PARAM_CODE => $code,
		OG_REST_PARAM_ID => $id,
		OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_REPOS => $repos);

	common_request(OG_REST_CMD_CREATE_IMAGE, POST, $data);
}

function restore_image($string_ips, $params) {

	preg_match_all('/(?<=\=)(.*?)(?=\r)/', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];
	$image_id = $matches[0][2];
	$name = $matches[0][3];
	$repos = $matches[0][4];
	$profile = $matches[0][5];
	$type = $matches[0][6];

	$data = array(OG_REST_PARAM_DISK => $disk, OG_REST_PARAM_PART => $part,
		OG_REST_PARAM_ID => $image_id, OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_REPOS => $repos,
		OG_REST_PARAM_PROFILE => $profile,
		OG_REST_PARAM_TYPE => $type,
		OG_REST_PARAM_CLIENTS => $ips);

	common_request(OG_REST_CMD_RESTORE_IMAGE, POST, $data);
}

function create_basic_image($string_ips, $params) {

	preg_match_all('/(?<=\=)[^\r]*(?=\r)?/', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];
	$code = $matches[0][2];
	$image_id = $matches[0][3];
	$name = $matches[0][4];
	$repos = $matches[0][5];

	$sync = $matches[0][7]; // Syncronization method

	$diff = $matches[0][8]; // Send the whole file if there are differences
	$remove = $matches[0][9]; // Delete files at destination that are not at source
	$compress = $matches[0][10]; // Compress before sending

	$cleanup = $matches[0][11]; // Delete image before creating it
	$cache = $matches[0][12]; // Copy image to cache
	$cleanup_cache = $matches[0][13]; // Delete image from cache before copying
	$remove_dst = $matches[0][14]; // Dont delete files in destination

	$data = array(OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk,
		OG_REST_PARAM_PART => $part,
		OG_REST_PARAM_CODE => $code,
		OG_REST_PARAM_ID => $image_id,
		OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_REPOS => $repos,
		OG_REST_PARAM_SYNC_PARAMS => array(
			OG_REST_PARAM_SYNC => $sync,
			OG_REST_PARAM_DIFF => $diff,
			OG_REST_PARAM_REMOVE => $remove,
			OG_REST_PARAM_COMPRESS => $compress,
			OG_REST_PARAM_CLEANUP => $cleanup,
			OG_REST_PARAM_CACHE => $cache,
			OG_REST_PARAM_CLEANUP_CACHE => $cleanup_cache,
			OG_REST_PARAM_REMOVE_DST => $remove_dst,
		)
	);

	common_request(OG_REST_CMD_CREATE_BASIC_IMAGE, POST, $data);
}

function create_incremental_image($string_ips, $params) {

	preg_match_all('/(?<=\=)[^\r]*(?=\r)?/', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];
	$id = $matches[0][2];
	$name = $matches[0][3];
	$repos = $matches[0][4];
	$diff_id = $matches[0][5];
	$diff_name = $matches[0][6];
	$path = $matches[0][7];
	$sync = $matches[0][8];
	$diff = $matches[0][9];
	$remove = $matches[0][10];
	$compress = $matches[0][11];
	$cleanup = $matches[0][12];
	$cache = $matches[0][13];
	$cleanup_cache = $matches[0][14];
	$remove_dst = $matches[0][15];

	$data = array(OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk,
		OG_REST_PARAM_PART => $part,
		OG_REST_PARAM_ID => $id,
		OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_REPOS => $repos,
		OG_REST_PARAM_SYNC_PARAMS => array(
			OG_REST_PARAM_SYNC => $sync,
			OG_REST_PARAM_PATH => $path,
			OG_REST_PARAM_DIFF => $diff,
			OG_REST_PARAM_DIFF_ID => $diff_id,
			OG_REST_PARAM_DIFF_NAME => $diff_name,
			OG_REST_PARAM_REMOVE => $remove,
			OG_REST_PARAM_COMPRESS => $compress,
			OG_REST_PARAM_CLEANUP => $cleanup,
			OG_REST_PARAM_CACHE => $cache,
			OG_REST_PARAM_CLEANUP_CACHE => $cleanup_cache,
			OG_REST_PARAM_REMOVE_DST => $remove_dst)
	);

	common_request(OG_REST_CMD_CREATE_INCREMENTAL_IMAGE, POST, $data);
}

function restore_basic_image($string_ips, $params) {

	preg_match_all('/(?<=\=)[^\r]*(?=\r)?/', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];
	$image_id = $matches[0][2];
	$name = $matches[0][3];
	$repos = $matches[0][4];
	$profile = $matches[0][5];

	$path = $matches[0][6];
	$method = $matches[0][7];
	$sync = $matches[0][8]; // Syncronization method

	$type = $matches[0][9];

	$diff = $matches[0][10]; // Send the whole file if there are differences
	$remove = $matches[0][11]; // Delete files at destination that are not at source
	$compress = $matches[0][12]; // Compress before sending

	$cleanup = $matches[0][13]; // Delete image before creating it
	$cache = $matches[0][14]; // Copy image to cache
	$cleanup_cache = $matches[0][15]; // Delete image from cache before copying
	$remove_dst = $matches[0][16]; // Dont delete files in destination

	$data = array(OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk,
		OG_REST_PARAM_PART => $part,
		OG_REST_PARAM_ID => $image_id,
		OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_REPOS => $repos,
		OG_REST_PARAM_PROFILE => $profile,
		OG_REST_PARAM_TYPE => $type,
		OG_REST_PARAM_SYNC_PARAMS => array(
			OG_REST_PARAM_PATH => $path,
			OG_REST_PARAM_METHOD => $method,
			OG_REST_PARAM_SYNC => $sync,
			OG_REST_PARAM_DIFF => $diff,
			OG_REST_PARAM_REMOVE => $remove,
			OG_REST_PARAM_COMPRESS => $compress,
			OG_REST_PARAM_CLEANUP => $cleanup,
			OG_REST_PARAM_CACHE => $cache,
			OG_REST_PARAM_CLEANUP_CACHE => $cleanup_cache,
			OG_REST_PARAM_REMOVE_DST => $remove_dst,
		)
	);

	common_request(OG_REST_CMD_RESTORE_BASIC_IMAGE, POST, $data);
}

function restore_incremental_image($string_ips, $params) {

	preg_match_all('/(?<=\=)[^\r]*(?=\r)?/', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];
	$image_id = $matches[0][2];
	$name = $matches[0][3];
	$repos = $matches[0][4];
	$profile = $matches[0][5];
	$diff_id = $matches[0][6];
	$diff_name = $matches[0][7];
	$path = $matches[0][8];
	$method = $matches[0][9];
	$sync = $matches[0][10];
	$type = $matches[0][11];
	$diff = $matches[0][12];
	$remove = $matches[0][13];
	$compress = $matches[0][14];
	$cleanup = $matches[0][15];
	$cache = $matches[0][16];
	$cleanup_cache = $matches[0][17];
	$remove_dst = $matches[0][18];

	$data = array(OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk,
		OG_REST_PARAM_PART => $part,
		OG_REST_PARAM_ID => $image_id,
		OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_REPOS => $repos,
		OG_REST_PARAM_PROFILE => $profile,
		OG_REST_PARAM_TYPE => $type,
		OG_REST_PARAM_SYNC_PARAMS => array(
			OG_REST_PARAM_DIFF_ID => $diff_id,
			OG_REST_PARAM_DIFF_NAME => $diff_name,
			OG_REST_PARAM_PATH => $path,
			OG_REST_PARAM_METHOD => $method,
			OG_REST_PARAM_SYNC => $sync,
			OG_REST_PARAM_DIFF => $diff,
			OG_REST_PARAM_REMOVE => $remove,
			OG_REST_PARAM_COMPRESS => $compress,
			OG_REST_PARAM_CLEANUP => $cleanup,
			OG_REST_PARAM_CACHE => $cache,
			OG_REST_PARAM_CLEANUP_CACHE => $cleanup_cache,
			OG_REST_PARAM_REMOVE_DST => $remove_dst,
		)
	);

	common_request(OG_REST_CMD_RESTORE_INCREMENTAL_IMAGE, POST, $data);
}

function poweroff($string_ips) {

	$ips = explode(';',$string_ips);

	$data = array(OG_REST_PARAM_CLIENTS => $ips);

	common_request(OG_REST_CMD_POWEROFF, POST, $data);
}

function reboot($string_ips) {

	$ips = explode(';',$string_ips);

	$data = array(OG_REST_PARAM_CLIENTS => $ips);

	common_request(OG_REST_CMD_REBOOT, POST, $data);
}

function stop($string_ips) {

	$ips = explode(';',$string_ips);

	$data = array(OG_REST_PARAM_CLIENTS => $ips);

	common_request(OG_REST_CMD_STOP, POST, $data);
}

function refresh($string_ips) {

	$ips = explode(';',$string_ips);

	$data = array(OG_REST_PARAM_CLIENTS => $ips);

	common_request(OG_REST_CMD_REFRESH, POST, $data);
}

function hardware($string_ips) {

	$ips = explode(';',$string_ips);

	$data = array(OG_REST_PARAM_CLIENTS => $ips);

	common_request(OG_REST_CMD_HARDWARE, POST, $data);
}

function software($string_ips, $params) {

	preg_match_all('/(?<=\=)(.*?)(?=\r)/', $params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$part = $matches[0][1];

	$data = array(OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk,
		OG_REST_PARAM_PART => $part);

	common_request(OG_REST_CMD_SOFTWARE, POST, $data);
}

function setup($string_ips, $params) {

	preg_match_all('/(?<=\=)(?!dis)(.*?)((?=\*)|(?=\r)|(?=\!)|(?=\%))/',
		$params, $matches);

	$ips = explode(';',$string_ips);
	$disk = $matches[0][0];
	$cache = $matches[0][2];
	$cache_size = $matches[0][3];
	$partition_number = array();
	$partition_code = array();
	$file_system = array();
	$part_size = array();
	$format = array();
	for ($x = 0; $x < 4; $x++) {
		$partition_number[$x] = $matches[0][4 + 5 * $x];
		$partition_code[$x] = $matches[0][5 + 5 * $x];
		$file_system[$x] = $matches[0][6 + 5 * $x];
		$part_size[$x] = $matches[0][7 + 5 * $x];
		$format[$x] = $matches[0][8 + 5 * $x];
	}

	$data = array(
		OG_REST_PARAM_CLIENTS => $ips,
		OG_REST_PARAM_DISK => $disk,
		OG_REST_PARAM_CACHE => $cache,
		OG_REST_PARAM_CACHE_SIZE => $cache_size,
		OG_REST_PARAM_PARTITION_SETUP => array()
	);

	for ($i = 0; $i < sizeof($partition_number); $i++) {
		$partition_setup = array(
			OG_REST_PARAM_PART => $partition_number[$i],
			OG_REST_PARAM_CODE => $partition_code[$i],
			OG_REST_PARAM_FILE_SYSTEM => $file_system[$i],
			OG_REST_PARAM_SIZE => $part_size[$i],
			OG_REST_PARAM_FORMAT => $format[$i]
		);
		array_push($data[OG_REST_PARAM_PARTITION_SETUP], $partition_setup);
	}

	common_request(OG_REST_CMD_SETUP, POST, $data);
}

function run_schedule($string_ips) {
	$ips = explode(';',$string_ips);
	$data = array(OG_REST_PARAM_CLIENTS => $ips);
	common_request(OG_REST_CMD_RUN_SCHEDULE, POST, $data);
}

function run_task($task_id) {
	$data = array(OG_REST_PARAM_TASK => $task_id);
	return common_request(OG_REST_CMD_RUN_TASK, POST, $data);
}

function create_schedule($task_id, $type, $name, $years, $months, $weeks,
			 $week_days, $days, $hours, $am_pm, $minutes) {
	$type_string;

	switch ($type) {
	case TYPE_COMMAND:
		$type_string = OG_SCHEDULE_COMMAND;
		break;
	case TYPE_PROCEDURE:
		$type_string = OG_SCHEDULE_PROCEDURE;
		break;
	case TYPE_TASK:
	default:
		$type_string = OG_SCHEDULE_TASK;
	}

	$data = array (
		OG_REST_PARAM_TASK => $task_id,
		OG_REST_PARAM_TYPE => $type_string,
		OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_WHEN => array (
			OG_REST_PARAM_YEARS => intval($years),
			OG_REST_PARAM_MONTHS => intval($months),
			OG_REST_PARAM_WEEKS => intval($weeks),
			OG_REST_PARAM_WEEK_DAYS => intval($week_days),
			OG_REST_PARAM_DAYS => intval($days),
			OG_REST_PARAM_HOURS => intval($hours),
			OG_REST_PARAM_AM_PM => intval($am_pm),
			OG_REST_PARAM_MINUTES => intval($minutes)
		)
	);

	return common_request(OG_REST_CMD_CREATE_SCHEDULE, POST, $data);
}

function delete_schedule($schedule_id) {

	$data = array (
		OG_REST_PARAM_ID => $schedule_id,
	);

	return common_request(OG_REST_CMD_DELETE_SCHEDULE, POST, $data);
}

function update_schedule($schedule_id, $task_id, $name, $years, $months, $days,
			 $hours, $am_pm, $minutes) {

	$data = array (
		OG_REST_PARAM_ID => $schedule_id,
		OG_REST_PARAM_TASK => $task_id,
		OG_REST_PARAM_NAME => $name,
		OG_REST_PARAM_WHEN => array (
			OG_REST_PARAM_YEARS => intval($years),
			OG_REST_PARAM_MONTHS => intval($months),
			OG_REST_PARAM_DAYS => intval($days),
			OG_REST_PARAM_HOURS => intval($hours),
			OG_REST_PARAM_AM_PM => intval($am_pm),
			OG_REST_PARAM_MINUTES => intval($minutes)
		)
	);

	return common_request(OG_REST_CMD_UPDATE_SCHEDULE, POST, $data);
}

function get_schedule($task_id = null, $schedule_id = null) {
	if (isset($task_id))
		$data = array(OG_REST_PARAM_TASK => strval($task_id));
	else if (isset($schedule_id))
		$data = array(OG_REST_PARAM_ID => strval($schedule_id));
	else
		$data = null;

	$result = common_request(OG_REST_CMD_GET_SCHEDULE, POST, $data);
	return $result;
}

/*
 * @function multiRequest.
 * @param    URLs array (may include header and POST data), cURL options array.
 * @return   Array of arrays with JSON requests and response codes.
 * @warning  Default options: does not verifying certificate, connection timeout 200 ms.
 * @Date     2015-10-14
 */
function multiRequest($data, $options=array(CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_TIMEOUT_MS => 500)) {
 
  // array of curl handles
  $curly = array();
  // Data to be returned (response data and code)
  $result = array();
 
  // multi handle
  $mh = curl_multi_init();
 
  // loop through $data and create curl handles
  // then add them to the multi-handle
  foreach ($data as $id => $d) {
 

    $curly[$id] = curl_init();
 
    $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
    curl_setopt($curly[$id], CURLOPT_URL, $url);
    // HTTP headers?
    if (is_array($d) && !empty($d['header'])) {
       curl_setopt($curly[$id], CURLOPT_HTTPHEADER, $d['header']);
    } else {
       curl_setopt($curly[$id], CURLOPT_HEADER, 0);
    }
    curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
 
    // post?
    if (is_array($d)) {
      if (!empty($d['post'])) {
        curl_setopt($curly[$id], CURLOPT_POST, 1);
        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
      }
    }

    // extra options?
    if (!empty($options)) {
      curl_setopt_array($curly[$id], $options);
    }
 
    curl_multi_add_handle($mh, $curly[$id]);
  }
 
  // execute the handles
  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while($running > 0);
 
 
  // Get content and HTTP code, and remove handles
  foreach($curly as $id => $c) {
    $result[$id]['data'] = curl_multi_getcontent($c);
    $result[$id]['code'] = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $c);
  }

 // all done
  curl_multi_close($mh);
 
  return $result;
}

