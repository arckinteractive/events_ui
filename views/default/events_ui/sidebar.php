<?php

namespace Events\UI;

use ElggBatch;
use Events\API\Calendar;

$entity = elgg_extract('entity', $vars);
$page_owner = elgg_get_page_owner_entity();

$calendars = new ElggBatch('elgg_get_entities', array(
	'types' => 'object',
	'subtypes' => Calendar::SUBTYPE,
	'container_guids' => (int) $page_owner->guid,
	'limit' => 0,
	'order_by' => 'e.time_created ASC',
));

$priority = 100;
foreach ($calendars as $calendar) {
	/* @var Calendar $calendar */
	$priority += 10;
	elgg_register_menu_item('page_owner_calendars', array(
		'name' => $calendar->guid,
		'text' => $calendar->getDisplayName(),
		'href' => $calendar->getURL(),
		'priority' => $priority,
	));
}

$priority += 10;
if ($page_owner instanceof \ElggUser && $page_owner->canEdit()) {
	elgg_register_menu_item('page_owner_calendars', array(
		'name' => 'addcalendar',
		'text' => '[+] ' . elgg_echo('events:calendar:add'),
		'href' => 'calendar/add/' . $page_owner->guid,
		'priority' => $priority,
	));
}

$menu = elgg_view_menu('page_owner_calendars', array(
	'entity' => $entity,
	'sort_by' => 'priority',
	'class' => 'elgg-menu-page',
));

if ($menu) {
	echo elgg_view_module('aside', elgg_echo('events:calendar:owner', array($page_owner->name)), $menu);
}

