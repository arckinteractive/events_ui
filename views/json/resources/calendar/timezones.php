<?php

namespace Events\UI;

$country = get_input('country');

$timezones = \Events\API\Util::getTimezonesByCountry();

if (!$country) {
	echo json_encode($timezones);
	return;
}

$country = strtoupper($country);
$country_timezones = elgg_extract($country, $timezones);
echo json_encode($country_timezones);