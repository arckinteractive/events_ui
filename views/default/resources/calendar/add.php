<?php

namespace Events\UI;

use Events\API\Calendar;

$container_guid = get_input('container_guid');
$container = get_entity($container_guid);

if ($container) {
	if (!$container->canWriteToContainer(0, 'object', Calendar::SUBTYPE)) {
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

$title = elgg_echo('events:calendar:add');
elgg_push_breadcrumb($title);

if (elgg_is_sticky_form('calendar/edit')) {
	$vars = elgg_get_sticky_values('calendar/edit');
	elgg_clear_sticky_form('calendar/edit');
} else {
	$vars = array();
}
$vars['container'] = $container;
$vars['dateautoinit'] = 1;

$content = elgg_view_form('calendar/edit', array(
	'enctype' => 'multipart/form-data',
), $vars);

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => false,
		));

echo elgg_view_page($title, $layout);

