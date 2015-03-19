<?php
/**
 * Event river view.
 */

namespace Events\UI;

use Events\API\Event;

$event = $vars['item']->getObjectEntity();

if (!$event instanceof Event) {
	return;
}

$owner = $event->getOwnerEntity();
if (!$owner instanceof \ElggEntity) {
	return;
}

$owner_link = elgg_view('output/url', array(
	'text' => $owner->name,
	'href' => $owner->getURL()
));

$event_link = elgg_view('output/url', array(
	'text' => $event->title,
	'href' => $event->getURL()
));

if ($event->isRecurring()) {
	$vars['summary'] = elgg_echo('river:event:create:recurring', array($owner_link, $event_link));
} else {
	$vars['summary'] = elgg_echo('river:event:create', array($owner_link, $event_link));
}

$vars['message'] = elgg_get_excerpt($event->description);
$vars['attachments'] = elgg_view('river/object/event/attachments', array('entity' => $event));

echo elgg_view('page/components/image_block', array(
	'image' => elgg_view_entity_icon($owner, 'small'),
	'body' => elgg_view('river/elements/body', $vars),
	'class' => 'elgg-river-item',
));
