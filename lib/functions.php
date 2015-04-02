<?php

namespace Events\UI;

use ElggBatch;
use ElggGroup;
use ElggUser;
use Events\API\Calendar;
use Events\API\Event;
use Events\API\Util;
use DateTime;
use DateTimeZone;

/**
 * Register title menu items for an event
 *
 * @param Event $event
 * @return void
 */
function register_event_title_menu($event, $ts = null, $calendar = null) {

	if (!$event instanceof Event) {
		return;
	}

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
		
		elgg_register_menu_item('title', array(
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
		elgg_register_menu_item('title', array(
			'name' => 'delete',
			'text' => elgg_echo('events_ui:cancel'),
			'href' => 'action/events/cancel?guid=' . $event->guid . '&ts=' . $ts, // add calendar_guid for proper forwarding
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-delete elgg-requires-confirmation events-ui-event-action-cancel',
			'data-object-event' => true,
			'data-guid' => $event->guid,
			'priority' => 300,
		));
	}

	if ($event->canEdit() && $event->isRecurring()) {
		elgg_register_menu_item('title', array(
			'name' => 'delete_all',
			'text' => elgg_echo('events_ui:cancel:all'),
			'href' => 'action/events/delete?guid=' . $event->guid, // add calendar_guid for proper forwarding
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-delete elgg-requires-confirmation events-ui-event-action-cancel-all',
			'rel' => elgg_echo('events_ui:cancel:all:confirm'),
			'data-object-event' => true,
			'data-guid' => $event->guid,
			'priority' => 400,
		));
	}
}

/**
 * Adds group events to the default calendar of interested members
 * 
 * @param int $event_guid GUID of the event
 * @param int $group_guid GUID of the group
 * @return void
 */
function autosync_group_event($event_guid, $group_guid) {
	$ia = elgg_set_ignore_access(true);
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

	elgg_set_ignore_access($ia);
}

/**
 * Registered a deferred function
 *
 * @param string $function the name of the function to be called
 * @param array  $args     an array of arguments to pass to the function
 * @param bool   $runonce  limit the function to only running once with a set of arguments
 * @return void
 */
function register_vroom_function($function, $args = array(), $runonce = true) {
	$vroom_functions = elgg_get_config('event_vroom_functions');

	if (!is_array($vroom_functions)) {
		$vroom_functions = array();
	}

	if ($runonce) {
		foreach ($vroom_functions as $array) {
			foreach ($array as $f => $a) {
				if ($f === $function && $a === $args) {
					return true; // this function is already registered with these args
				}
			}
		}
	}

	$vroom_functions[] = array($function => $args);

	elgg_set_config('event_vroom_functions', $vroom_functions);
}

/**
 * Returns preferred calendar notifications methods for the user
 *
 * @global array $NOTIFICATION_HANDLERS
 * @param ElggUser $user              User
 * @param string   $notification_name Notification name
 * @return type
 */
function get_calendar_notification_methods($user, $notification_name) {

	if (!($user instanceof ElggUser)) {
		return array();
	}

	$methods = array();
	global $NOTIFICATION_HANDLERS;
	foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
		$attr = '__notify_' . $method . '_' . $notification_name;

		// default to on if not set
		if (!isset($user->$attr) || $user->$attr) {
			$methods[] = $method;
		}
	}

	return $methods;
}

/**
 * Returns calendar  notification types
 * @return string[]
 */
function get_calendar_notifications() {
	$calendar_notifications = array(
		'addtocal',
		'eventupdate',
		'eventreminder'
	);

	return $calendar_notifications;
}

/**
 * Send notifications about event updates to those users that have added the event
 * to their calendar
 * 
 * @param int $event_guid GUID of the event
 * @return void
 */
function event_update_notify($event_guid) {
	$ia = elgg_set_ignore_access(true);
	$event = get_entity($event_guid);

	if (!($event instanceof Event)) {
		return false;
	}

	$dbprefix = elgg_get_config('dbprefix');
	$options = array(
		'type' => 'object',
		'subtype' => 'calendar',
		'relationship' => Calendar::EVENT_CALENDAR_RELATIONSHIP,
		'relationship_guid' => $event->guid,
		'joins' => array(
			// limit the results to calendars contained by users
			"JOIN {$dbprefix}users_entity ue ON e.container_guid = ue.guid"
		),
		'limit' => false
	);

	$calendars = new ElggBatch('elgg_get_entities_from_relationship', $options);
	
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

	$notified = array(); // users could have multiple calendars
	foreach ($calendars as $c) {
		$user = $c->getContainerEntity();

		if (in_array($user->guid, $notified)) {
			continue;
		}

		$ia = elgg_set_ignore_access(false);
		if (!has_access_to_entity($event, $user)) {
			// the user can't see it, lets not notify them
			$notified[] = $user->guid;
			elgg_set_ignore_access($ia);
			continue;
		}
		elgg_set_ignore_access($ia);

		$notify_self = false;
		// support for notify self
		if (is_callable('notify_self_should_notify')) {
			$notify_self = notify_self_should_notify($user);
		}

		if (elgg_get_logged_in_user_guid() == $user->guid && !$notify_self) {
			$notified[] = $user->guid;
			continue;
		}

		$methods = get_calendar_notification_methods($user, 'eventupdate');
		if (!$methods) {
			$notified[] = $user->guid;
			continue;
		}
		
		$starttimestamp = $event->getNextOccurrence();
		$endtimestamp = $starttimestamp + $event->delta;
		
		$timezone = Util::getClientTimezone($user);

		$subject = elgg_echo('event:notify:eventupdate:subject', array(
			$event->title,
			$in_group,
			$owner->name
		));
		$subject = elgg_trigger_plugin_hook('events_ui', 'subject:eventupdate', array('event' => $event, 'calendar' => $c, 'user' => $user), $subject);

		$message = elgg_echo('event:notify:eventupdate:message', array(
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
			'calendar' => $c,
			'user' => $user
		);
		$message = elgg_trigger_plugin_hook('events_ui', 'message:eventupdate', $params, $message);
		
		$params = array();
		if ($event->canComment($user->guid)) {
			$params = array('entity' => $event);
		}
		notify_user(
				$user->guid,
				$event->container_guid, // user or group
				$subject,
				$message,
				$params,
				$methods
		);

		$notified[] = $user->guid;
	}

	elgg_set_ignore_access($ia);
}

/**
 * Send reminder notifications to users based on their notification settings
 * @todo if there are a *lot* of recipients we should somehow break this off into parallel threads
 * 
 * @param Event $event Event
 * @return void
 */
function send_event_reminder($event, $remindertime = null) {

	if ($remindertime === null) {
		$remindertime = time();
	}

	$dbprefix = elgg_get_config('dbprefix');
	$options = array(
		'type' => 'object',
		'subtype' => 'calendar',
		'relationship' => Calendar::EVENT_CALENDAR_RELATIONSHIP,
		'relationship_guid' => $event->guid,
		'joins' => array(
			// limit the results to calendars contained by users
			"JOIN {$dbprefix}users_entity ue ON e.container_guid = ue.guid"
		),
		'limit' => false
	);

	$calendars = new ElggBatch('elgg_get_entities_from_relationship', $options);

	$starttimestamp = $event->getNextOccurrence($remindertime);
	$endtimestamp = $starttimestamp + $event->delta;
	
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

	$notified = array(); // users could have multiple calendars
	foreach ($calendars as $calendar) {
		$user = $calendar->getContainerEntity();

		if (in_array($user->guid, $notified)) {
			continue;
		}

		$ia = elgg_set_ignore_access(false);
		if (!has_access_to_entity($event, $user)) { error_log($user->username . ' does not have access to ' . $event->guid);
			// the user can't see it, lets not notify them
			$notified[] = $user->guid;
			elgg_set_ignore_access($ia);
			continue;
		}
		elgg_set_ignore_access($ia);

		$notify_self = false;
		// support for notify self
		if (is_callable('notify_self_should_notify')) {
			$notify_self = notify_self_should_notify($user);
		}

		if (elgg_get_logged_in_user_guid() == $user->guid && !$notify_self) {
			$notified[] = $user->guid;
			continue;
		}

		$methods = get_calendar_notification_methods($user, 'eventreminder');
		if (!$methods) {
			$notified[] = $user->guid;
			continue;
		}

		$timezone = Util::getClientTimezone($user);
		$dt = new DateTime(null, new DateTimeZone($timezone));
		$dt->modify("$event->start_date $event->start_time");

		$original_subject = elgg_echo('event:notify:eventreminder:subject', array(
			$event->title,
			$in_group,
			$dt->format('D, F j g:ia T')
		));

		$original_message = elgg_echo('event:notify:eventreminder:message', array(
			$event_link,
			$in_group_link,
			elgg_view('output/events_ui/date_range', array('start' => $starttimestamp, 'end' => $endtimestamp, 'timezone' => $timezone)),
			$event->location,
			$event->description,
		));

		$params = array(
			'event' => $event,
			'entity' => $event, // for back compatibility with some internal Arck message parsing plugins
			'calendar' => $calendar,
			'user' => $user,
			'starttime' => $starttimestamp,
			'endtime' => $endtimestamp
		);
		$subject = elgg_trigger_plugin_hook('events_ui', 'subject:eventreminder', $params, $original_subject);
		$message = elgg_trigger_plugin_hook('events_ui', 'message:eventreminder', $params, $original_message);

		notify_user(
				$user->guid, $event->container_guid, // user or group
				$subject, $message, array(), $methods
		);

		$notified[] = $user->guid;
	}
}


/**
 * @TODO this should be able to be removed for 1.9+
 * This does a real check for access to entity, doesn't care if you're logged out
 * Unlike buggy core version
 * 
 * @param type $entity
 * @param type $user
 */
function has_access_to_entity($entity, $user) {
	global $CONFIG;

	$ia = elgg_set_ignore_access(false);
	if (!isset($user)) {
		$access_bit = get_access_sql_suffix("e");
	} else {
		$access_bit = get_access_sql_suffix("e", $user->getGUID());
	}
	elgg_set_ignore_access($ia);

	$query = "SELECT guid from {$CONFIG->dbprefix}entities e WHERE e.guid = " . $entity->getGUID();
	// Add access controls
	$query .= " AND " . $access_bit;
	if (get_data($query)) {
		return true;
	} else {
		return false;
	}
}

function get_access_sql_suffix($table_prefix = '', $owner = null) {
	global $ENTITY_SHOW_HIDDEN_OVERRIDE, $CONFIG;

	$sql = "";
	$friends_bit = "";
	$enemies_bit = "";

	if ($table_prefix) {
		$table_prefix = sanitise_string($table_prefix) . ".";
	}

	if (!isset($owner)) {
		$owner = elgg_get_logged_in_user_guid();
	}

	if (!$owner) {
		$owner = -1;
	}

	$ignore_access = elgg_check_access_overrides($owner);
	$access = get_access_list($owner);

	if ($ignore_access) {
		$sql = " (1 = 1) ";
	} else if ($owner != -1) {
		// we have an entity's guid and auto check for friend relationships
		$friends_bit = "{$table_prefix}access_id = " . ACCESS_FRIENDS . "
			AND {$table_prefix}owner_guid IN (
				SELECT guid_one FROM {$CONFIG->dbprefix}entity_relationships
				WHERE relationship='friend' AND guid_two=$owner
			)";

		$friends_bit = '(' . $friends_bit . ') OR ';

		// @todo untested and unsupported at present
		if ((isset($CONFIG->user_block_and_filter_enabled)) && ($CONFIG->user_block_and_filter_enabled)) {
			// check to see if the user is in the entity owner's block list
			// or if the entity owner is in the user's filter list
			// if so, disallow access
			$enemies_bit = get_access_restriction_sql('elgg_block_list', "{$table_prefix}owner_guid", $owner, false);
			$enemies_bit = '('
				. $enemies_bit
				. '	AND ' . get_access_restriction_sql('elgg_filter_list', $owner, "{$table_prefix}owner_guid", false)
			. ')';
		}
	}

	if (empty($sql)) {
		$sql = " $friends_bit ({$table_prefix}access_id IN {$access}
			OR ({$table_prefix}owner_guid = {$owner})
			OR (
				{$table_prefix}access_id = " . ACCESS_PRIVATE . "
				AND {$table_prefix}owner_guid = $owner
			)
		)";
	}

	if ($enemies_bit) {
		$sql = "$enemies_bit AND ($sql)";
	}

	if (!$ENTITY_SHOW_HIDDEN_OVERRIDE) {
		$sql .= " and {$table_prefix}enabled='yes'";
	}

	return '(' . $sql . ')';
}


function get_access_list($user_id = 0, $site_id = 0, $flush = false) {
	global $CONFIG, $init_finished;
	$cache = _elgg_get_access_cache();
	
	if ($flush) {
		$cache->clear();
	}

	if ($user_id == 0) {
		$user_id = elgg_get_logged_in_user_guid();
	}

	if (($site_id == 0) && (isset($CONFIG->site_id))) {
		$site_id = $CONFIG->site_id;
	}
	$user_id = (int) $user_id;
	$site_id = (int) $site_id;

	$hash = $user_id . $site_id . 'get_access_list';

	if ($cache[$hash]) {
		return $cache[$hash];
	}
	
	$access_array = get_access_array($user_id, $site_id, $flush);
	$access = "(" . implode(",", $access_array) . ")";

	if ($init_finished) {
		$cache[$hash] = $access;
	}
	
	return $access;
}


function get_access_array($user_id, $site_id, $flush) {
	global $CONFIG, $init_finished;

	$cache = _elgg_get_access_cache();

	if ($flush) {
		$cache->clear();
	}

	if ($user_id == 0) {
		$user_id = elgg_get_logged_in_user_guid();
	}

	if (($site_id == 0) && (isset($CONFIG->site_guid))) {
		$site_id = $CONFIG->site_guid;
	}

	$user_id = (int) $user_id;
	$site_id = (int) $site_id;

	$hash = $user_id . $site_id . 'get_access_array';

	if ($cache[$hash]) {
		$access_array = $cache[$hash];
	} else {
		$access_array = array(ACCESS_PUBLIC);

		// The following can only return sensible data if the user is logged in. - @Matt - nope!
		if ($user_id) {
			$access_array[] = ACCESS_LOGGED_IN;

			// Get ACL memberships
			$query = "SELECT am.access_collection_id"
				. " FROM {$CONFIG->dbprefix}access_collection_membership am"
				. " LEFT JOIN {$CONFIG->dbprefix}access_collections ag ON ag.id = am.access_collection_id"
				. " WHERE am.user_guid = $user_id AND (ag.site_guid = $site_id OR ag.site_guid = 0)";

			$collections = get_data($query);
			if ($collections) {
				foreach ($collections as $collection) {
					if (!empty($collection->access_collection_id)) {
						$access_array[] = (int)$collection->access_collection_id;
					}
				}
			}

			// Get ACLs owned.
			$query = "SELECT ag.id FROM {$CONFIG->dbprefix}access_collections ag ";
			$query .= "WHERE ag.owner_guid = $user_id AND (ag.site_guid = $site_id OR ag.site_guid = 0)";

			$collections = get_data($query);
			if ($collections) {
				foreach ($collections as $collection) {
					if (!empty($collection->id)) {
						$access_array[] = (int)$collection->id;
					}
				}
			}

			$ignore_access = elgg_check_access_overrides($user_id);

			if ($ignore_access == true) {
				$access_array[] = ACCESS_PRIVATE;
			}
		}

		if ($init_finished) {
			$cache[$hash] = $access_array;
		}
	}

	$options = array(
		'user_id' => $user_id,
		'site_id' => $site_id
	);
	
	return elgg_trigger_plugin_hook('access:collections:read', 'user', $options, $access_array);
}
