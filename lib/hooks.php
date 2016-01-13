<?php

namespace Events\UI;

use ElggBatch;
use ElggGroup;
use ElggMenuItem;
use ElggUser;
use Events\API\Calendar;
use Events\API\Event;
use Events\API\EventInstance;
use Events\API\Util;

/**
 * Sets up owner block menu
 *
 * @param string $hook   "register"
 * @param string $type   "menu:owner_block"
 * @param array  $return Menu
 * @param array  $params Hook params
 * @return array
 */
function owner_block_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if ($entity instanceof ElggUser) {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'calendar',
					'href' => "calendar/view/$entity->guid",
					'text' => elgg_echo('events:calendar'),
		));
	} else if ($entity instanceof ElggGroup && $entity->calendar_enable != 'no') {
		$return[] = ElggMenuItem::factory(array(
					'name' => 'calendar',
					'href' => "calendar/view/$entity->guid",
					'text' => elgg_echo('events:calendar:group'),
		));
	}

	return $return;
}

/**
 * Sets up entity menu
 *
 * @param string $hook   "register"
 * @param string $type   "menu:entity"
 * @param array  $return Menu
 * @param array  $params Hook params
 * @return array
 */
function entity_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if ($entity instanceof Calendar) {
		if ($entity->isPublicCalendar()) {
			// Remove Edit and Delete links from public caendar
			foreach ($return as $key => $item) {
				if ($item instanceof ElggMenuItem && in_array($item->getName(), array('edit', 'delete'))) {
					unset($return[$key]);
				}
			}
		}
	}
	if ($entity instanceof Event) {
		if ($entity->canEdit()) {
			$return[] = ElggMenuItem::factory(array(
						'name' => 'edit',
						'text' => elgg_echo('edit'),
						'href' => 'calendar/events/edit/' . $entity->guid,
						'link_class' => 'events-ui-event-action-edit',
						'data-object-event' => true,
						'data-guid' => $entity->guid,
						'priority' => 200,
			));
			$ts = elgg_extract('ts', $params);
			$return[] = ElggMenuItem::factory(array(
						'name' => 'delete',
						'text' => elgg_view_icon('delete'),
						'href' => 'action/events/cancel?guid=' . $entity->guid . '&ts=' . $ts, // add calendar_guid for proper forwarding
						'is_action' => true,
						'link_class' => 'events-ui-event-action-cancel',
						'confirm' => elgg_echo('events_ui:cancel:confirm'),
						'data-object-event' => true,
						'data-guid' => $entity->guid,
						'priority' => 300,
			));
		}
	}

	return $return;
}

/**
 * Filter container permissions
 * 
 * @param string $hook   "container_permissions_check"
 * @param string $type   "object"
 * @param bool   $return Permission
 * @param array  $params Hook params
 * @return bool
 */
function container_permissions_check($hook, $type, $return, $params) {

	$container = elgg_extract('container', $params);
	$subtype = elgg_extract('subtype', $params);

	if (!elgg_instanceof($container, 'group')) {
		return $return;
	}

	// Do not allow events and calendars in groups if calendar tool is disabled
	if ($container->calendar_enable == 'no' && in_array($subtype, array(Calendar::SUBTYPE, Event::SUBTYPE))) {
		return false;
	}

	return $return;
}

function entity_icon_url($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);

	if ($entity instanceof Event) {
		return 'mod/events_ui/graphics/event-' . $params['size'] . '.jpg';
	}

	return $return;
}

function notification_settings_save($h, $t, $r, $p) {
	$current_user = elgg_get_logged_in_user_entity();

	$guid = (int) get_input('guid', 0);
	if (!$guid || !($user = get_entity($guid))) {
		forward();
	}
	if (($user->guid != $current_user->guid) && !$current_user->isAdmin()) {
		forward();
	}

	$calendar_notifications = get_calendar_notifications();

	global $NOTIFICATION_HANDLERS;
	foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
		foreach ($calendar_notifications as $notification_name) {
			$attr = '__notify_' . $method . '_' . $notification_name;

			$value = (int) get_input($method . $notification_name, 0);

			$user->$attr = $value;
		}
	}
}

/**
 * Add calendar feeds to public pages
 *
 * @param string $hook   "public_pages"
 * @param string $type   "walled_garden"
 * @param array  $return Public pages
 * @eturn array
 */
function setup_public_pages($hook, $type, $return) {
	$return[] = "calendar/feed/.*";
	$return[] = "calendar/ical/.*";
	return $return;
}

/**
 * Minutely cron callback
 *
 * @param string $hook   "cron"
 * @param string $type   "minute"
 * @param mixed  $return Previous callback return
 * @param array  $params Hook params
 * @return void
 */
function event_reminders($hook, $type, $return, $params) {
	// run our reminders a couple of minutes ahead of schedule
	// to account for processing time and delivery time
	$offset = (int) elgg_get_plugin_setting('reminder_offset', 'events_ui');
	if (!$offset && $offset !== 0) {
		$offset = 120;
	}
	$time = $params['time'] + $offset;

	$last_time = elgg_get_plugin_setting('last_reminder_cron', 'events_ui');
	if (!$last_time) {
		$last_time = $time - 60;
	}

	elgg_set_plugin_setting('last_reminder_cron', $time, 'events_ui');

	$ia = elgg_set_ignore_access(true);
	
	$options = array(
		'type' => 'object',
		'subtype' => 'event',
		'annotation_name' => 'reminder',
		'wheres' => array(
			"CAST(v.string AS SIGNED) BETWEEN {$last_time} AND {$time}"
		)
	);

	$reminders = new ElggBatch('elgg_get_annotations', $options, null, 50, false);

	foreach ($reminders as $reminder) {
		$event = get_entity($reminder->entity_guid);
		send_event_reminder($event);
	}

	elgg_set_ignore_access($ia);
}

/**
 * Filter default timezones to only include those specified in plugin settings
 *
 * @param string $hook   "timezones"
 * @param string $type   "events_api"
 * @param array  $return Current list of timezones
 * @param array  $params Additional params
 * @return array Filtered list
 */
function filter_timezones($hook, $type, $return, $params) {

	$setting = elgg_get_plugin_setting('custom_timezones', 'events_ui');
	$custom = ($setting) ? unserialize($setting) : false;
	
	if (!empty($custom)) {
		$default = Util::getClientTimezone();
		foreach ($return as $key => $value) {
			if (!in_array($key, $custom) && $key !== $default) {
				unset($return[$key]);
			}
		}
	}

	return $return;
}

/**
 * Sets default user timezone
 * Called when 'usersettings:save','user' hook is triggered
 * @return void
 */
function save_default_user_timezone() {

	$timezone = get_input('timezone');
	
	$user_guid = get_input('guid');
	$user = get_entity($user_guid);

	if (($user) && ($timezone)) {
		if (strcmp($timezone, $user->timezone) != 0) {
			$user->timezone = $timezone;
			if ($user->save()) {
				system_message(elgg_echo('user:timezone:success'));
				return true;
			} else {
				register_error(elgg_echo('user:timezone:fail'));
			}
		} else {
			// no change
			return null;
		}
	} else {
		register_error(elgg_echo('user:timezone:fail'));
	}
}

/**
 * Filters instance export values for fullcalendar views
 *
 * @param string $hook   "export:instance"
 * @param string $type   "events_api"
 * @param array  $return Exported values
 * @param array  $params Hook params
 * @return array
 */
function export_event_instance($hook, $type, $return, $params) {

	$instance = elgg_extract('instance', $params);
	$consumer = elgg_extract('consumer', $params);

	if (!$instance instanceof EventInstance) {
		return $return;
	}

	if ($consumer == 'fullcalendar') {
		$event = $instance->getEvent();
		$export = array(
			'id' => $event->guid,
			'allDay' => $event->isAllDay(),
			//'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
		);
		$return = array_merge($return, $export);
	}

	return $return;
}



function register_comment_tracker($hook, $type, $return, $params) {
	$return[] = 'event';
	return $return;
}


/**
 * Returns a canonical URL of an object
 * @return string
 */
function url_handler($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	if ($entity instanceof Calendar) {
		return "calendar/view/$entity->guid";
	} else if ($entity instanceof Event) {
		return "calendar/events/view/$entity->guid";
	}
}

/**
 * Prepare buttons to be registered as title menu items
 * 
 * @param string         $hook   "profile_buttons"
 * @param string         $type   "object:event"
 * @param ElggMenuItem[] $return Profile buttons
 * @param array          $params Hook params
 * @return ElggMenuItem[]
 */
function prepare_profile_buttons($hook, $type, $return, $params) {

	$event = elgg_extract('event', $params);
	$ts = elgg_extract('timestamp', $params);
	$calendar = elgg_extract('calendar', $params);
	
	$calendar_count = 0;
	if (elgg_is_logged_in()) {
		$calendar_count = Calendar::getCalendars(elgg_get_logged_in_user_entity(), true);
	}

	if ($calendar_count) {
		// may be different than the calendar being viewed
		// make the add/remove button work for the current calendar if they own it
		// or their default calendar if they're viewing another calendar
		if ($calendar->owner_guid == elgg_get_logged_in_user_guid()) {
			$mycalendar = $calendar;
		}
		else {
			$mycalendar = Calendar::getPublicCalendar(elgg_get_logged_in_user_entity());
		}

		$text = elgg_echo('events:add_to_calendar:default');
		$add_remove_calendar = $mycalendar->guid;
		if ($mycalendar->hasEvent($event)) {
			$text = elgg_echo('events:remove_from_calendar:default');
			$add_remove_calendar = '';
		}

		$return[] = ElggMenuItem::factory(array(
			'name' => 'add_to_calendar',
			'href' => elgg_http_add_url_query_elements('action/calendar/add_event', array(
				'event_guid' => $event->guid,
				'calendars[]' => $add_remove_calendar
			)),
			'is_action' => true,
			'data-object-event' => true,
			'data-guid' => $event->guid,
			'text' => $text,
			'data-calendar-count' => $calendar_count,
			'link_class' => 'elgg-button elgg-button-action events-ui-event-action-addtocalendar',
			'priority' => 100,
		));
	}

	if ($event->canEdit()) {
		$return[] = ElggMenuItem::factory(array(
			'name' => 'delete',
			'text' => elgg_echo('events_ui:cancel'),
			'href' => 'action/events/cancel?guid=' . $event->guid . '&ts=' . $ts, // add calendar_guid for proper forwarding
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-delete events-ui-event-action-cancel',
			'confirm' => true,
			'data-object-event' => true,
			'data-guid' => $event->guid,
			'priority' => 300,
		));
	}

	if ($event->canEdit() && $event->isRecurring()) {
		$return[] = ElggMenuItem::factory(array(
			'name' => 'delete_all',
			'text' => elgg_echo('events_ui:cancel:all'),
			'href' => 'action/events/delete?guid=' . $event->guid, // add calendar_guid for proper forwarding
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-delete events-ui-event-action-cancel-all',
			'confirm' => elgg_echo('events_ui:cancel:all:confirm'),
			'data-object-event' => true,
			'data-guid' => $event->guid,
			'priority' => 400,
		));
	}

	return $return;
}