<?php

/**
 * Content filter
 *
 * @uses $vars['filter_context']  Filter context: all, friends, mine
 */
if (!elgg_is_logged_in()) {
	return;
}

$username = elgg_get_logged_in_user_entity()->username;
$filter_context = elgg_extract('filter_context', $vars, 'mine');

$owner = elgg_get_page_owner_entity();
$logged_in = elgg_get_logged_in_user_entity();

$tabs = array(
//	'all' => array(
//		'text' => elgg_echo('all'),
//		'href' => "caendar/all",
//		'selected' => ($filter_context == 'all'),
//		'priority' => 200,
//	),
	'mine' => array(
		'text' => elgg_echo('mine'),
		'href' => "calendar/owner/$logged_in->username",
		'selected' => ($filter_context == 'mine' && $owner->guid == $logged_in->guid),
		'priority' => 300,
	),
	'friend' => array(
		'text' => elgg_echo('friends'),
		'href' => "calendar/friends/$logged_in->username",
		'selected' => ($filter_context == 'friends' && $owner->guid == $logged_in->guid),
		'priority' => 400,
	),
);

foreach ($tabs as $name => $tab) {
	$tab['name'] = $name;

	elgg_register_menu_item('filter', $tab);
}

echo elgg_view_menu('filter', array('sort_by' => 'priority', 'class' => 'elgg-menu-hz'));
