<?php

namespace Events\UI;

use Events\API\Calendar;

$username = get_input('username');
$user = get_user_by_username($username);
$viewer = elgg_get_logged_in_user_entity();

if (!$user) {
	forward('', '404');
}

elgg_set_page_owner_guid($user->guid);

//elgg_push_breadcrumb(elgg_echo('events:calendar'), "calendar/all");
elgg_push_breadcrumb($user->name, "calendar/owner/$user->username");

$filter_context = false;
if ($user->guid == $viewer->guid) {
	elgg_register_title_button('calendar', 'add', 'object', Calendar::SUBTYPE);
	$filter = elgg_view('events_ui/filter', array(
		'filter_context' => 'mine',
	));
} else {
	$filter = false;
}

$title = elgg_echo('events:calendar:owner' , array($user->name));
$content = elgg_list_entities(array(
	'types' =>'object',
	'subtypes' => Calendar::SUBTYPE,
	'owner_guids' => $user->guid,
	'full_view' => false,
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' =>$content,
	'filter' => $filter,
));

echo elgg_view_page($title, $layout);