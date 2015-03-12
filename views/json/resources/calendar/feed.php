<?php

namespace Events\UI;

use Events\API\Calendar;
use Events\API\Util;

$is_logged_in = elgg_is_logged_in();

$guid = get_input('guid');

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

$start = (int) get_input('start', time());
$end = (int) get_input('end', strtotime('+1 month', $start));

$start = (int) Util::getDayStart($start);
$end = (int) Util::getDayEnd($end);

$events = $entity->getAllEventInstances($start, $end);

echo json_encode($events);

if (!$is_logged_in) {
	logout();
}