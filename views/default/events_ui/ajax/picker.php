<?php

namespace Events\UI;

use Events\API\Calendar;

if (!elgg_is_logged_in()) {
	return;
}

$calendars = Calendar::getCalendars(elgg_get_logged_in_user_entity());

$options = array();
$value = array();
foreach ($calendars as $c) {
	/* @var $c Calendar */
	
	$title = $c->getDisplayName();
	$options[$title] = $c->guid;
	
	if ($c->hasEvent($vars['entity'])) {
		$value[] = $c->guid;
	}
}

$body .= elgg_view('output/longtext', array(
	'value' => elgg_echo('events:calendar:picker:help'),
	'class' => 'elgg-subtext'
));
$body .= elgg_view('input/checkboxes', array(
	'name' => 'calendars',
	'value' => $value,
	'options' => $options
));

$body .= '<div class="elgg-foot mtm">';
$body .= elgg_view('input/hidden', array('name' => 'event_guid', 'value' => $vars['guid']));

$body .= elgg_view('input/submit', array('value' => elgg_echo('submit')));
$body .= '</div>';

$form = elgg_view('input/form', array(
	'action' => 'action/calendar/add_event',
	'method' => 'post',
	'body' => $body,
	'class' => 'elgg-form-calendar-add-event',
));

$title = elgg_echo('events:calendar:picker:title');
echo elgg_view_module('info', $title, $form);