<?php

namespace Events\UI;

use Events\API\Calendar;

$guid = get_input('container_guid');
$group = get_entity($guid);

if (!elgg_instanceof($group, 'group')) {
	forward('', '404');
}

elgg_set_page_owner_guid($group->guid);

group_gatekeeper();
if ($group->calendar_enable == 'no') {
	forward('', '404');
}

//elgg_push_breadcrumb(elgg_echo('events:calendar'), "calendar/all");
elgg_push_breadcrumb($group->name, "calendar/group/$group->guid");

if ($group->canWriteToContainer(0, 'object', Calendar::SUBTYPE)) {
	elgg_register_title_button();
}

$title = elgg_echo('events:calendar:group');
$content = elgg_list_entities(array(
	'types' =>'object',
	'subtypes' => Calendar::SUBTYPE,
	'container_guids' => $group->guid,
	'full_view' => false,
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' =>$content,
	'filter' => false,
));

echo elgg_view_page($title, $layout);