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
						'link_class' => 'elgg-requires-confirmation events-ui-event-action-cancel',
						'rel' => elgg_echo('events_ui:cancel:confirm'),
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
