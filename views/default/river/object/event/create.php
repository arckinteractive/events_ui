<?php
/**
 * Event river view.
 */

$event = $vars['item']->getObjectEntity();

$owner = $event->getOwnerEntity();
$owner_link = elgg_view('output/url', array(
	'text' => $owner->name,
	'href' => $owner->getURL()
));

$event_link = elgg_view('output/url', array(
	'text' => $event->title,
	'href' => $event->getURL()
));


$vars['summary'] = elgg_echo('river:event:create', array($owner_link, $event_link));

$vars['message'] = elgg_view('river/object/event/message', array('event' => $event));

echo elgg_view('page/components/image_block', array(
	'image' => elgg_view_entity_icon($event, 'small'),
	'body' => elgg_view('river/elements/body', $vars),
	'class' => 'elgg-river-item',
));
