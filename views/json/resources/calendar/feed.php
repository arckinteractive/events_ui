<?php

namespace Events\UI;

use Events\API\Calendar;
use Events\API\Util;

$guid = get_input('guid');
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