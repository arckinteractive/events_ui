<?php

namespace Events\UI;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity) {
	forward('', '404');
}

if (!$entity->canEdit()) {
	register_error(elgg_echo('noaccess'));
	forward(REFERER);
}

$container = $entity->getContainerEntity();
elgg_set_page_owner_guid($container->guid);

//elgg_push_breadcrumb(elgg_echo('events:calendar'), "calendar/all");
if (elgg_instanceof($container, 'user')) {
	elgg_push_breadcrumb($container->name, "calendar/owner/$container->username");
} else if (elgg_instanceof($container, 'group')) {
	elgg_push_breadcrumb($container->name, "calendar/group/$container->guid");
}
elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());

$title = elgg_echo('events:edit');
elgg_push_breadcrumb($title);

if (elgg_is_sticky_form('events/edit')) {
	$vars = elgg_get_sticky_values('events/edit');
	elgg_clear_sticky_form('events/edit');
} else {
	$vars = array();
}

$vars['entity'] = $entity;
$vars['container'] = $container;
$vars['dateautoinit'] = 1;

$content = elgg_view_form('events/edit', array(
	'enctype' => 'multipart/form-data'
		), $vars);

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => false,
		));

echo elgg_view_page($title, $layout);

