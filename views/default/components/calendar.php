<?php

/**
 * Calendar component view
 * 
 * @uses $vars['items'] Calendar entities or guids
 * @uses $vars['start_time'] Start time
 * @uses $vars['upcoming'] Default to upcoming events
 * @uses $vars['limit'] Limit
 */
namespace Events\UI;

use Events\API\Calendar;

elgg_load_css('components/calendar');

$calendars = array();
$items = elgg_extract('items', $vars, array());
foreach ($items as $item) {
	if (is_numeric($item)) {
		$item = get_entity($item);
	}
	if ($item instanceof Calendar) {
		$calendars[] = $item;
	}
}

if (empty($calendars)) {
	echo elgg_echo('events:widgets:noresults');
	return;
}

$vars['calendars'] = $calendars;
$header = elgg_view('components/calendar/header', $vars);
$content = elgg_view('components/calendar/feed', $vars);

echo elgg_view_module('aside', $header, $content, array(
	'class' => 'events-ui-calendar-component',
));
