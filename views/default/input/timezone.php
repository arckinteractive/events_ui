<?php

/**
 * Display a timezone input
 * @uses $vars['format'] Optional. Format of the timezone label
 * @uses $vars['timestamp'] Optional. Timestamp for the timezone label
 * @uses $vars['sort_by'] Optional. Sorting: 'alpha' or 'offset'
 */
namespace Events\UI;

use Events\API\Util;

if (isset($vars['format'])) {
	$format = $vars['format'];
	unset($vars['format']);
}

if (isset($vars['timestamp'])) {
	$timestamp = $vars['timestamp'];
	unset($vars['timestamp']);
} else {
	$timestamp = time();
}

if (isset($vars['sort_by'])) {
	$sort_by = $vars['sort_by'];
	unset($vars['sort_by']);
} else {
	$sort_by = Util::TIMEZONE_SORT_ALPHA;
}

$vars['options_values'] = Util::getTimezones(true, $format, $timestamp);

echo elgg_view('input/dropdown', $vars);