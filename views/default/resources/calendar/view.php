<?php

namespace Events\UI;

use Events\API\Calendar;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity) {
	$entity = elgg_get_logged_in_user_entity();
}

if (!$entity instanceof Calendar) {
	$entity = Calendar::getPublicCalendar($entity);
}

if (!$entity) {
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

$title = $entity->getDisplayName();
elgg_push_breadcrumb($title);

elgg_register_menu_item('title', array(
	'name' => 'feed_view',
	'href' => "calendar/feed/$entity->guid",
	'text' => elgg_echo('events:view:feed'), //elgg_view_icon('events-feed'),
	'title' => elgg_echo('events:view:feed:switch'),
	'link_class' => 'elgg-button elgg-button-action',
));
elgg_register_menu_item('title', array(
	'name' => 'ical_view',
	'href' => $entity->getIcalURL("calendar/ical/{$entity->guid}/calendar{$entity->guid}.ics"),
	'text' => elgg_echo('events:view:ical'), //elgg_view_icon('events-ical'),
	'title' => elgg_echo('events:view:ical'),
	'link_class' => 'elgg-button elgg-button-action js-events-ui-ical-modal-trigger',
));

$sidebar = elgg_view('events_ui/sidebar', array(
	'entity' => $entity,
		));

$content = elgg_view_entity($entity, array(
	'full_view' => true,
		));

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
