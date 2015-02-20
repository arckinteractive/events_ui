<?php

namespace Events\UI;

use ElggEntity;

$events = elgg_extract('events', $vars);

if (empty($events)) {
	echo '<p class="elgg-no-results">' . elgg_echo('events:no_results') . '</p>';
	return;
}

echo '<ul class="elgg-list">';
foreach ($events as $instance) {
	$event = get_entity($instance['id']);
	if (!$event instanceof ElggEntity) {
		continue;
	}
	echo '<li class="elgg-item">';
	echo elgg_view_entity($event, array(
		'full_view' => false,
		'instance' => $instance,
	));
	echo '</li>';
}
echo '</ul>';