<?php

namespace Events\UI;

use Events\API\Calendar;

$username = get_input('username');
$user = get_user_by_username($username);

$viewer = elgg_get_logged_in_user_entity();

if (!$user) {
	forward('', '404');
}

if (!$user->canEdit()) {
	register_error(elgg_echo('noaccess'));
	forward("calendar/friends/$viewer->username");
}

elgg_set_page_owner_guid($user->guid);

//elgg_push_breadcrumb(elgg_echo('events:calendar'), "calendar/all");
elgg_push_breadcrumb($user->name, "calendar/owner/$user->username");
elgg_push_breadcrumb(elgg_echo('friends'), "calendar/friends/$user->username");

$filter_context = false;
if ($user->guid == $viewer->guid) {
	elgg_register_title_button();
	$filter = elgg_view('events_ui/filter', array(
		'filter_context' => 'mine',
	));
} else {
	$filter = false;
}

$title = elgg_echo('events:calendar:friends');

$dbprefix = elgg_get_config('dbprefix');
$content = elgg_list_entities_from_relationship(array(
	'types' => 'object',
	'subtypes' => Calendar::SUBTYPE,
	'joins' => array(
		"JOIN {$dbprefix}entity_relationships er ON er.guid_two = e.container_guid"
	),
	'wheres' => array(
		"er.guid_one = {$user->guid} AND er.relationship = 'friend'"
	),
	'full_view' => false,
		));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
		));

echo elgg_view_page($title, $layout);
