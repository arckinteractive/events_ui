<?php

namespace Events\UI;

use Events\API\Calendar;
use Events\API\Util;
use Events\API\PAM;

$is_logged_in = elgg_is_logged_in();

$guid = get_input('guid');
$consumer = get_input('consumer');

if (!$is_logged_in) {
	$token = get_input('token');
	$user_guid = get_input('uid');

	try {
		PAM::authenticate();
	} catch (Exception $ex) {
		register_error($ex->getMessage());
		forward('', '403');
	}
}

$entity = get_entity($guid);

if (!$entity instanceof Calendar) {
	forward('', '404');
}

$start_iso = get_input('start_iso');
if ($start_iso) {
	$start = strtotime($start_iso);
} else {
	$start = (int) get_input('start', time());
}

$end_iso = get_input('end_iso');
if ($end_iso) {
	$end = strtotime($end_iso);
} else {
	$end = (int) get_input('end', strtotime('+1 month', $start));
}

$start = (int) Util::getDayStart($start);
$end = (int) Util::getDayEnd($end);

$events = $entity->getAllEventInstances($start, $end, true, $consumer);

echo json_encode($events);

if (!$is_logged_in) {
	logout();
}