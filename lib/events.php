<?php

namespace Events\UI;

use ElggGroup;
use ElggUser;
use Events\API\Event;

/**
 * Registers menu items on page setup
 */
function pagesetup() {
	elgg_register_menu_item('site', array(
		'name' => 'calendar',
		'href' => 'calendar',
		'text' => elgg_echo('events:calendar'),
	));
}

function event_create($event, $type, $event) {
	if (!($event instanceof Event)) {
		return true;
	}

	// we've created an event, if it's a group event we need to add it to
	// members default calendars
	$container = $event->getContainerEntity();

	if ($container instanceof ElggGroup) {
		// note that groups could have *lots* of users, it may not be practical
		// to add them all now, use vroom to do it in the background if possible
		if (!elgg_get_config('shutdown_initiated')) {
			register_vroom_function(__NAMESPACE__ . '\\autosync_group_event', array(
				$event->guid,
				$container->guid
			));
		} else {
			autosync_group_event($event->guid, $container->guid);
		}
	}
}

/**
 * called on shutdown event after vroom has flushed to browser
 * used for background processes
 */
function vroom_functions() {
	elgg_set_config('shutdown_initiated', 1);
	
	$vroom_functions = elgg_get_config('event_vroom_functions');

	if ($vroom_functions && is_array($vroom_functions)) {
		foreach ($vroom_functions as $params) {
			foreach ($params as $function => $args) {
				if (is_callable($function)) {
					call_user_func_array($function, $args);
				}
			}
		}
	}
}

function add_to_calendar($event, $type, $params) {
	$event = $params['event'];
	$calendar = $params['calendar'];
	
	$user = $calendar->getContainerEntity();
	
	if (!($user instanceof ElggUser)) {
		return true;
	}

	// notify the user
	$notify_self = false;
	// support for notify self
	if (is_callable('notify_self_should_notify')) {
		$notify_self = notify_self_should_notify($user);
	}

	if (elgg_get_logged_in_user_guid() == $user->guid && !$notify_self) {
		return true;
	}

	$methods = get_calendar_notification_methods($user, 'addtocal');
	if (!$methods) {
		return true;
	}
	
	$subject = elgg_echo('event:notify:addtocal:subject', array($event->title));
	$subject = elgg_trigger_plugin_hook('events_ui', 'subject:addtocal', array('event' => $event, 'calendar' => $calendar, 'user' => $user), $subject);

	$message = elgg_echo('event:notify:addtocal:message', array(
		$event->title,
		elgg_view('output/events_ui/date_range', array('start' => $event->start_timestamp, 'end' => $event->end_timestamp)),
		$event->location,
		$event->description,
		$event->getURL()
	));

	$message = elgg_trigger_plugin_hook('events_ui', 'message:addtocal', array('event' => $event, 'calendar' => $calendar, 'user' => $user), $message);
	notify_user(
			$user->guid,
			$event->container_guid, // user or group
			$subject,
			$message,
			array(),
			$methods
	);
}


function event_update($event, $type, $event) {
	if (!($event instanceof Event)) {
		return true;
	}

	// we've updated an event, do we need to notify people?
	if (!get_input('resend_notifications')) {
		return true;
	}
	
	// note that the event could be on *lots* of calendars, it may not be practical
	// to notify them all now, use vroom to do it in the background if possible
	if (!elgg_get_config('shutdown_intiated')) {
		register_vroom_function(__NAMESPACE__ . '\\event_update_notify', array(
			$event->guid
		));
	} else {
		event_update_notify($event->guid);
	}
}