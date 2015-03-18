<?php

namespace Events\UI;

use ElggBatch;
use ElggGroup;
use ElggMenuItem;
use ElggUser;
use Events\API\Calendar;
use Events\API\Event;

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
	} else if ($entity instanceof ElggGroup && $entity->calendar_enable == 'yes') {
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
						'data-confirm' => elgg_echo('events_ui:cancel:confirm'),
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

			$value = get_input($method . $notification_name, null);

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
	// get events
	$options = array(
		'type' => 'object',
		'subtype' => 'event',
		'metadata_name_value_pairs' => array(
			array(
				'name' => 'reminder',
				'value' => $last_time,
				'operand' => '>'
			),
			array(
				'name' => 'reminder',
				'value' => $time,
				'operand' => '<='
			)
		),
		'limit' => false
	);

	$events = new ElggBatch('elgg_get_entities_from_metadata', $options);

	foreach ($events as $event) {
		send_event_reminder($event);
	}

	elgg_set_ignore_access($ia);
}
