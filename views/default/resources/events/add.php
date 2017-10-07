<?php

namespace Events\UI;

use Events\API\Event;

$container_guid = get_input('container_guid');
$container = get_entity($container_guid);

if ($container) {
	if (!$container->canWriteToContainer(0, 'object', Event::SUBTYPE)) {
		register_error(elgg_echo('noaccess'));
		forward();
	}
} else {
	$container = elgg_get_logged_in_user_entity();
}

elgg_set_page_owner_guid($container->guid);

//elgg_push_breadcrumb(elgg_echo('events:calendar'), "calendar/all");
if (elgg_instanceof($container, 'user')) {
	elgg_push_breadcrumb($container->name, "calendar/owner/$container->username");
} else if (elgg_instanceof($container, 'group')) {
	elgg_push_breadcrumb($container->name, "calendar/group/$container->guid");
}

$title = elgg_echo('events:add');
elgg_push_breadcrumb($title);

if (elgg_is_sticky_form('events/edit')) {
	$vars = elgg_get_sticky_values('events/edit');
	elgg_clear_sticky_form('events/edit');
} else {
	$vars = array();
}
$vars['container'] = $container;

$calendar_guid = get_input('calendar_guid');
$vars['calendar'] = get_entity($calendar_guid);

$content = elgg_view_form('events/edit', array(
	'enctype' => 'multipart/form-data'
), $vars);

if (elgg_is_xhr()) {
	echo elgg_view_module('lightbox', $title, $content);
} else {
	$layout = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'filter' => false,
	));

	echo elgg_view_page($title, $layout);
}

