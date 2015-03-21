<?php

namespace Events\UI;

$username = get_input('username');
$user = get_user_by_username($username);
if (!$user || !$user->canEdit()) {
	forward('', '404');
}

elgg_set_page_owner_guid($user->guid);

$title = elgg_echo('calendar:settings');
$content = elgg_view('core/settings/calendar/notifications');

$layout = elgg_view_layout('one_sidebar', array(
	'title' => $title,
	'content' => $content,
		));

echo elgg_view_page($title, $layout);
