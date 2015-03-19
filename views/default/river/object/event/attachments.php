<?php

namespace Events\UI;

use Events\API\Event;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Event) {
	return;
}

elgg_push_context('widgets');
echo elgg_view_entity($entity, array(
	'full_view' => false,
//	'instance' => array(
//		'start_timestamp' => $event->getNextOccurrence(),
//	),
));
elgg_pop_context();