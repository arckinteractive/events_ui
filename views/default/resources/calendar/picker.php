<?php

if (!elgg_is_logged_in()) {
	return;
}

$calendars = Events\API\Calendar::getCalendars(elgg_get_logged_in_user_entity());

$options = array();
$value = array();
foreach ($calendars as $c) {
	$title = $c->title ? $c->title : false;
	if (!$title) {
		$title = $c->__public_calendar ? elgg_echo('events_ui:default:calendar') : elgg_echo('Calendar');
	}
	
	$options[$title] = $c->guid;
	
	if ($c->hasEvent($vars['entity'])) {
		$value[] = $c->guid;
	}
}

$body = elgg_view('input/checkboxes', array(
	'name' => 'calendars',
	'value' => $value,
	'options' => $options
));

$body .= '<div class="elgg-foot mtm">';
$body .= elgg_view('input/hidden', array('name' => 'event_guid', 'value' => $vars['guid']));

$body .= elgg_view('input/submit', array('value' => elgg_echo('submit')));
$body .= '</div>';

echo elgg_view('input/form', array(
	'action' => 'action/calendar/add_event',
	'method' => 'post',
	'body' => $body
));