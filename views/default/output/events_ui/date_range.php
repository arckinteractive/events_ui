<?php

/**
 * Outputs a formatted date range
 * @uses $vars['start'] Start timestamp
 * @uses $vars['end'] End timestamp
 * @uses $vars['timezone'] Original timezone, if not UTC
 */
namespace Events\UI;

use DateTime;
use DateTimeZone;
use Events\API\Util;

$start = (int) elgg_extract('start', $vars);
$end = (int) elgg_extract('end', $vars);
$timezone = elgg_extract('timezone', $vars, Util::UTC);

if (!$start || !$end) {
	return;
}

$client_tz = Util::getClientTimezone();
$dt_start = new DateTime("@$start", new DateTimeZone($timezone));
$start_at_org_tz = $dt_start->format('D, F j, Y H:ia T');

$dt_start->setTimezone(new DateTimeZone($client_tz));

$dt_end = new DateTime("@$end", new DateTimeZone($timezone));
$end_at_org_tz = $dt_end->format('D, F j, Y H:ia T');

$dt_end->setTimezone(new DateTimeZone($client_tz));

$output = '';

if ($dt_start->format('Y-m-d') == $dt_end->format('Y-m-d')) {
	if (date('Y') == $dt_start->format('Y')) {
		$output .= $dt_start->format('D, F j');
	} else {
		$output .= $dt_start->format('D, F j, Y');
	}
	$output .= ', ' . $dt_start->format('g:ia') . ' - ' . $dt_end->format('g:ia');
} else {
	if (date('Y') == $dt_start->format('Y')) {
		$output .= $dt_start->format('D, F j g:ia');
		$output .= ' - ' . $dt_end->format('D, F j g:ia');
	} else {	
		$output .= $dt_start->format('D, F j, Y g:ia');
		$output .= ' - ' . $dt_end->format('D, F j, Y g:ia');
	}
}

$output .= ', ' . $dt_start->format('T');

$attrs = elgg_format_attributes(array(
	'class' => 'events-date-range',
	'title' => "$start_at_org_tz - $end_at_org_tz"
));

echo "<span $attrs>$output</span>";