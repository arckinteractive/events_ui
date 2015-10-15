<?php

namespace Events\UI;

use ElggGroup;
use ElggObject;
use ElggUser;
use Events\API\Event;
use Events\API\Util;

/**
 * Registers menu items on page setup
 * @return void
 */
function pagesetup() {
	elgg_register_menu_item('site', array(
		'name' => 'calendar',
		'href' => 'calendar',
		'text' => elgg_echo('events:calendar'),
	));
}

/**
 * Callback that fires when event is created
 *
 * @param string      $event "create"
 * @param string      $type  "object"
 * @param ElggObject $event Object
 * @return boolean
 */
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
 * Called on shutdown event after vroom has flushed to browser
 * used for background processes
 * @return void
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

/**
 * Callback that fires on add_to_calendar event
 *
 * @param string $event  "events_api"
 * @param string $type   "add_to_calendar"
 * @param array  $params Event params
 * @return boolean
 */
function add_to_calendar($event, $type, $params) {
	$event = $params['event'];
	$calendar = $params['calendar'];

	if (!$event instanceof Event || !$calendar instanceof $calendar) {
		return true;
	}

	$user = $calendar->getContainerEntity();

	if (!$user instanceof ElggUser) {
		return true;
	}

	$ia = elgg_set_ignore_access(false);
	if (!has_access_to_entity($event, $user)) {
		// the user can't see it, lets not notify them
		elgg_set_ignore_access($ia);
		return true;
	}
	elgg_set_ignore_access($ia);

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
	
	$owner = $event->getOwnerEntity();
	$owner_link = elgg_view('output/url', array(
		'text' => $owner->name,
		'href' => $owner->getURL()
	));
	
	$in_group = '';
	$in_group_link = '';
	$container = $event->getContainerEntity();
	$container_link = elgg_view('output/url', array(
		'text' => $container->name,
		'href' => $container->getURL()
	));
	if ($container instanceof \ElggGroup) {
		$in_group = elgg_echo('events:notify:subject:ingroup', array($container->name));
		$in_group_link = elgg_echo('events:notify:subject:ingroup', array($container_link));
	}
	
	$event_link = elgg_view('output/url', array(
		'text' => $event->title,
		'href' => $event->getURL()
	));

	$subject = elgg_echo('event:notify:addtocal:subject', array(
		html_entity_decode($event->title),
		$in_group,
		$owner->name
	));
	
	$timezone = Util::getClientTimezone($user);

	$message = elgg_echo('event:notify:addtocal:message', array(
		$owner_link,
		$event_link,
		$in_group_link,
		elgg_view('output/events_ui/date_range', array('start' => $event->getStartTimestamp(), 'end' => $event->getEndTimestamp(), 'timezone' => $timezone)),
		$event->location,
		$event->description,
	));

	$params = array(
		'event' => $event,
		'entity' => $event, // for BC with internal Arck message parsing plugins
		'calendar' => $calendar,
		'user' => $user
	);
	$subject = elgg_trigger_plugin_hook('events_ui', 'subject:addtocal', $params, $subject);
	$message = elgg_trigger_plugin_hook('events_ui', 'message:addtocal', $params, $message);
	
	$params = array();
	if ($event->canComment($user->guid)) {
		$params = array('entity' => $event);
	}
	notify_user(
			$user->guid, $event->container_guid, // user or group
			$subject,
			$message,
			$params,
			$methods
	);
}

/**
 * Callback that fires when event is updated
 *
 * @param string     $event "update"
 * @param string     $type  "object"
 * @param ElggObject $event Object
 * @return boolean
 */
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
	if (!elgg_get_config('shutdown_initiated')) {
		register_vroom_function(__NAMESPACE__ . '\\event_update_notify', array(
			$event->guid
		));
	} else {
		event_update_notify($event->guid);
	}
}


function upgrades() {
	if (elgg_is_admin_logged_in()) {
		elgg_load_library('events:upgrades');
	}
}