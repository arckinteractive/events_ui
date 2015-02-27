<?php

namespace Events\UI;

use Events\API\Event;

$entity = elgg_extract('entity', $vars);
$instance = elgg_extract('instance', $vars, array());
$calendar = elgg_extract('calendar', $vars);
$full = elgg_extract('full_view', $vars, false);

if (!$entity instanceof Event) {
	return;
}

$owner = $entity->getOwnerEntity();

$owner_link = elgg_view('output/url', array(
	'href' => "collections/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
		));
$author_text = elgg_echo('byline', array($owner_link));

$start = elgg_extract('start_timestamp', $instance, $entity->start_timestamp);
if (!$entity->isValidStartTime($start)) {
	$start = $entity->getNextOccurrence();
}
$end = $start + $entity->end_delta;

$date = elgg_view('output/events_ui/date_range', array(
	'start' => $start,
	'end' => $end,
));

$categories = elgg_view('output/categories', $vars);

$subtitle = "$date<br />$author_text $categories";

if ($full) {
	$title = false;
	$summary = '';
	$content = elgg_view('output/longtext', array(
		'value' => $entity->description,
	));
	$metadata = elgg_view_menu('entity', array(
		'entity' => $entity,
		'handler' => 'events',
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
	));
	$tags = '';
} else {
	$title = elgg_view('output/url', array(
		'text' => $entity->getDisplayName(),
		'href' => $entity->getURL($start, $calendar->guid),
	));
	$summary = elgg_get_excerpt($entity->description);
	$metadata = false;
	$tags = false;
}

$summary = elgg_view('object/elements/summary', array(
	'entity' => $entity,
	'title' => $title,
	'subtitle' => $subtitle,
	'content' => $summary,
	'metadata' => $metadata,
		));

$icon = elgg_view_entity_icon($entity, 'small');

if ($full) {
	echo elgg_view('object/elements/full', array(
		'entity' => $entity,
		'summary' => $summary,
		'icon' => $icon,
		'body' => $content,
	));
} else {
	echo elgg_view_image_block($icon, $summary);
}