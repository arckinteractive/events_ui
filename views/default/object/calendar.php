<?php

namespace Events\UI;

use Events\API\Calendar;

$entity = elgg_extract('entity', $vars);
$full = elgg_extract('full_view', $vars, false);

if (!$entity instanceof Calendar) {
	return;
}

$owner = $entity->getOwnerEntity();
$owner_link = elgg_view('output/url', array(
	'href' => "calendar/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
		));

//$subtitle = elgg_echo('byline', array($owner_link));

$metadata = elgg_view_menu('entity', array(
	'entity' => $entity,
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
	'handler' => 'calendar',
		));

if ($full) {
	$title = '';
	$summary = elgg_view('output/longtext', array(
		'value' => $entity->description,
	));
} else {
	$title = elgg_view('output/url', array(
		'text' => $entity->getDisplayName(),
		'href' => $entity->getURL(),
	));
	$summary = elgg_get_excerpt($entity->description);
}

$summary = elgg_view('object/elements/summary', array(
	'entity' => $entity,
	'title' => $title,
	'subtitle' => $subtitle,
	'content' => $summary,
	'metadata' => $metadata,
		));

$icon = false;
if ($owner instanceof \ElggUser || $owner instanceof \ElggGroup) {
	$icon = elgg_view_entity_icon($owner, 'small');
}

if ($full) {
	$content = elgg_view('events_ui/calendar', $vars);
	echo elgg_view('object/elements/full', array(
		'entity' => $entity,
		'summary' => $summary,
		'icon' => $icon,
		'body' => $content,
	));
} else {
	echo elgg_view_image_block($icon, $summary);
}