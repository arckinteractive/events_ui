<?php

namespace Events\UI;

use Events\API\Calendar;
use Events\API\Event;

$guid = get_input('guid');
$entity = get_entity($guid);

$calendar_guid = get_input('calendar');
$calendar = get_entity($calendar_guid);

$ts = get_input('ts');

if (!$entity instanceof Event) {
	forward('', '404');
}

$container = $entity->getContainerEntity();
elgg_set_page_owner_guid($container->guid);

//elgg_push_breadcrumb(elgg_echo('events:calendar'), "calendar/all");
if (elgg_instanceof($container, 'user')) {
	elgg_push_breadcrumb($container->name, "calendar/owner/$container->username");
} else if (elgg_instanceof($container, 'group')) {
	elgg_push_breadcrumb($container->name, "calendar/group/$container->guid");
}
if ($calendar instanceof Calendar) {
	elgg_push_breadcrumb($calendar->getDisplayName(), $calendar->getURL());
}
$title = $entity->getDisplayName();
elgg_push_breadcrumb($title);

register_event_title_menu($entity, $ts, $calendar);

$sidebar = elgg_view('events_ui/sidebar', array(
	'entity' => $calendar,
));


if (elgg_is_xhr()) {
	echo elgg_view('object/event/modal', array(
		'entity' => $entity,
		'instance' => array(
			'start_timestamp' => $ts,
		),
		'calendar' => $calendar,
	));
} else {
	$content = elgg_view_entity($entity, array(
		'full_view' => true,
		'instance' => array(
			'start_timestamp' => $ts,
		),
		'calendar' => $calendar,
	));
	$content .= elgg_view_comments($entity);
	
	$layout = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'filter' => false,
		'sidebar' => $sidebar,
		'entity' => $entity,
	));

	echo elgg_view_page($title, $layout, 'default', array(
		'entity' => $entity,
	));
}