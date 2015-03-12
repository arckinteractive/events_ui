<?php

namespace Events\UI;

use ElggEntity;
use Events\API\Calendar;
use Events\API\Event;
use ElggGroup;
use ElggBatch;
use ElggUser;

function register_event_title_menu($event) {

	$calendar_count = 0;
	if (elgg_is_logged_in()) {
		$calendar_count = Calendar::getCalendars(elgg_get_logged_in_user_entity(), true);
	}

	if ($calendar_count) {
		$calendar_picker = ' events-ui-event-action-addtocalendar';
	}

	$calendar = Calendar::getPublicCalendar(elgg_get_logged_in_user_entity());
	elgg_register_menu_item('title', array(
		'name' => 'add_to_calendar',
		'href' => elgg_http_add_url_query_elements('action/calendar/add_event', array(
			'event_guid' => $event->guid,
			'calendars[]' => $calendar->guid
		)),
		'is_action' => true,
		'data-object-event' => true,
		'data-guid' => $event->guid,
		'text' => elgg_echo('events:add_to_calendar:default'),
		'link_class' => 'elgg-button elgg-button-action' . $calendar_picker,
		'priority' => 100,
	));

	if ($event->canEdit()) {
		elgg_register_menu_item('title', array(
			'name' => 'edit',
			'text' => elgg_echo('edit'),
			'href' => 'calendar/events/edit/' . $event->guid,
			'link_class' => 'elgg-button elgg-button-action events-ui-event-action-edit',
			'data-object-event' => true,
			'data-guid' => $event->guid,
			'priority' => 200,
		));

		if ($event->isRecurring() && ($ts = get_input('ts'))) {
			elgg_register_menu_item('title', array(
				'name' => 'cancel',
				'text' => elgg_echo('events_ui:cancel'),
				'href' => 'action/events/cancel?guid=' . $event->guid . '&ts=' . $ts, // add calendar_guid for proper forwarding
				'is_action' => true,
				'link_class' => 'elgg-button elgg-button-delete events-ui-event-action-cancel',
				'data-confirm' => elgg_echo('events_ui:cancel:confirm'),
				'data-object-event' => true,
				'data-guid' => $event->guid,
				'priority' => 300,
			));
		}

		elgg_register_menu_item('title', array(
			'name' => 'delete',
			'text' => ($event->isRecurring()) ? elgg_echo('events_ui:cancel:all') : elgg_echo('events_ui:cancel'),
			'href' => 'action/events/delete?guid=' . $event->guid, // add calendar_guid for proper forwarding
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-delete events-ui-event-action-cancel-all',
			'data-confirm' => ($event->isRecurring()) ? elgg_echo('events_ui:cancel:all:confirm') : elgg_echo('events_ui:cancel:confirm'),
			'data-object-event' => true,
			'data-guid' => $event->guid,
			'priority' => 400,
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

function get_calendar_notification_methods($user, $notification_name) {

	if (!($user instanceof ElggUser)) {
		return array();
	}

	$methods = array();
	global $NOTIFICATION_HANDLERS;
	foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
		$attr = '__notify_' . $method . '_' . $notification_name;

		if ($user->$attr) {
			$methods[] = $method;
		}
	}

	return $methods;
}

function get_calendar_notifications() {
	$calendar_notifications = array(
		'addtocal',
		'eventreminder'
	);

	return $calendar_notifications;
}
