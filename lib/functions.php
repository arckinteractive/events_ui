<?php

namespace Events\UI;

use ElggEntity;
use Events\API\Calendar;
use Events\API\Event;
use ElggGroup;
use ElggBatch;

function register_event_title_menu($event) {

	/**
	 * @todo: implement support for picking the calendar
	 */
	$calendar_count = 0;
	if (elgg_is_logged_in()) {
		$calendar_count = Calendar::getCalendars(elgg_get_logged_in_user_entity(), true);
	}
	
	if ($calendar_count) {
		$calendar_picker = ' addtocalendar-picker';
	}
	
	$calendar = Calendar::getPublicCalendar(elgg_get_logged_in_user_entity());
	elgg_register_menu_item('title', array(
		'name' => 'add_to_calendar',
		'href' => elgg_http_add_url_query_elements('action/calendar/add_event', array(
			'event_guid' => $event->guid,
			'calendars[]' => $calendar->guid
		)),
		'is_action' => true,
		'data-guid' => $event->guid,
		'text' => elgg_echo('events:add_to_calendar:default'),
		'link_class' => 'elgg-button elgg-button-action' . $calendar_picker
	));

	if ($event->canEdit()) {
		elgg_register_menu_item('title', array(
			'name' => 'edit',
			'text' => elgg_echo('edit'),
			'href' => 'calendar/events/edit/' . $event->guid,
			'link_class' => 'elgg-button elgg-button-action'
		));

		elgg_register_menu_item('title', array(
			'name' => 'delete',
			'text' => elgg_echo('delete'),
			'href' => 'action/events/delete?guid=' . $event->guid, // add calendar_guid for proper forwarding
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-delete elgg-requires-confirmation'
		));
	}
}


/**
 * adds group events to the default calendar of interested members
 * 
 * @param type $event_guid
 * @param type $group_guid
 */
function autosync_group_event($event_guid, $group_guid) {
	// note that this function can be called after shutdown with vroom
	// using guids for params so that we're not performing operations on potentially stale entities
	$event = get_entity($event_guid);
	$group = get_entity($group_guid);

	if (!($event instanceof Event) || !($group instanceof ElggGroup)) {
		return false;
	}
	
	// get group members
	$options = array(
		'type' => 'user',
		'relationship' => 'member',
		'relationship_guid' => $group->guid,
		'inverse_relationship' => true,
		'limit' => false
	);
	
	$users = new ElggBatch('elgg_get_entities_from_relationship', $options);
	foreach ($users as $u) {
		// only add to the calendar if they have not opted out
		if (!check_entity_relationship($u->guid, 'calendar_nosync', $group->guid)) {
			// they have not opted out, we should add it to their calendars
			$calendar = Calendar::getPublicCalendar($u);
			$calendar->addEvent($event);
		}
	}
}


function register_vroom_function($function, $args) {
	$vroom_functions = elgg_get_config('event_vroom_functions');
	
	if (!is_array($vroom_functions)) {
		$vroom_functions = array();
	}
	
	$vroom_functions[] = array($function => $args);

	elgg_set_config('event_vroom_functions', $vroom_functions);
}