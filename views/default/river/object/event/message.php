<?php

$event = $vars['event'];

echo '<div>';
echo '<strong>' . elgg_echo('events:start:time') . ':</strong> ' . $event->start_date . ' ' . $event->start_time;
echo '</div>';
echo '<div>';
echo '<strong>' . elgg_echo('events:end:time') . ':</strong> ' . $event->end_date . ' ' . $event->end_time;
echo '</div>';

echo elgg_view('output/longtext', array(
	'value' => elgg_get_excerpt($event->description)
));