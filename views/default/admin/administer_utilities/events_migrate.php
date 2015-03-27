<?php

$count = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'event_calendar',
	'count' => true
));

$title = elgg_echo('events:migrate:title');

if (!$count) {
	$body = elgg_echo('events:migrate:count:none');
	echo elgg_view_module('main', $title, $body);
	return;
}

$progress = (int) elgg_get_plugin_setting('migration_progress', 'events_ui');
$time = time() - 180; // 3 minute buffer
if ($progress > $time) {
	$body = elgg_echo('events:migrate:inprogress', array($count));
	echo elgg_view_module('main', $title, $body);
	return;
}

$body = elgg_echo('events:migrate:count', array($count));
$body .= '<div class="pvl">';
$body .= elgg_view('output/confirmlink', array(
	'text' => elgg_echo('events:migrate:run'),
	'href' => 'action/events/migrate',
	'class' => 'elgg-button elgg-button-action'
));
$body .= '</div>';

echo elgg_view_module('main', $title, $body);