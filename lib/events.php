<?php

namespace Events\UI;
use ElggGroup;
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
		if (elgg_is_active_plugin('vroom') && !$GLOBALS['shutdown_flag']) {
			register_vroom_function(__NAMESPACE__ . '\\autosync_group_event', array(
				$event->guid,
				$container->guid
			));
		}
		else {
			autosync_group_event($event->guid, $container->guid);
		}
	}
}

/**
 * called on shutdown event after vroom has flushed to browser
 * used for background processes
 */
function vroom_functions() {
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