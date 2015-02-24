<?php

namespace Events\UI;

use Events\API\Calendar;
use Events\API\Util;

set_time_limit(0);

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Calendar) {
	forward('', '404');
}

$start = (int) get_input('start', time());
$end = (int) get_input('end', strtotime('+1 month', $start));

$start = (int) Util::getDayStart($start);
$end = (int) Util::getDayEnd($end);

$filename = get_input('filename');
if ($filename) {
	header("Content-Disposition: attachment; filename=$filename");
} else {
//	header("Content-Disposition: inline");
//	header("Content-type: text/calendar; charset=utf-8");
}

echo $entity->getIcalFeed($start, $end);
