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
$author_text = elgg_echo('events_ui:byline', array($owner_link));

$start = (int) elgg_extract('start_timestamp', $instance, $entity->getStartTimestamp());

if (!$entity->isValidStartTime($start)) {
	$start = $entity->getNextOccurrence();
}
$end = $start + $entity->end_delta;

$date = elgg_view('output/events_ui/date_range', array(
	'start' => $start,
	'end' => $end,
		));
$recurring = ($entity->isRecurring()) ? elgg_echo('events:status:recurring') : '';
$location = elgg_view('output/location', array(
	'value' => $entity->getLocation(),
		));
$categories = elgg_view('output/categories', $vars);

$subtitle = '';
foreach (array($date, $recurring, $location, $author_text, $categories) as $subtitle_element) {
	// reulctant to just implode with <br /> for styling reasons
	if ($subtitle_element) {
		$subtitle .= '<div>' . $subtitle_element . '</div>';
	}
}

$metadata = '';
if (!elgg_in_context('widgets')) {
	$metadata = elgg_view_menu('entity', array(
		'entity' => $entity,
		'handler' => 'events',
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
		'full_view' => $full,
		'ts' => $start,
	));
}

if ($full) {
	$title = false;
	$summary = '';
	$content = elgg_view('output/longtext', array(
		'value' => $entity->description,
	));
	$tags = '';
} else {
	$title = elgg_view('output/url', array(
		'text' => $entity->getDisplayName(),
		'href' => $entity->getURL($start, $calendar->guid),
	));
	$summary = elgg_get_excerpt($entity->description);
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
