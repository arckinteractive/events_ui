<?php

namespace Events\UI;
use Events\API\Event;
use Events\API\Calendar;
use DateTime;
use DateTimeZone;

// upgrade scripts

function event_calendar_migration() {
	$ia = elgg_set_ignore_access(true);
	
	// don't want to send notifications for these events
	elgg_unregister_event_handler('events_api', 'add_to_calendar', __NAMESPACE__ . '\\add_to_calendar');
	
	$dbprefix = elgg_get_config('dbprefix');
	
	$options = array(
		'type' => 'object',
		'subtype' => 'event_calendar',
		'limit' => false
	);
	
	$old_events = new \ElggBatch('elgg_get_entities', $options, null, 25, false);
	
	foreach ($old_events as $old_event) {
		elgg_set_plugin_setting('migration_process', time(), 'events_ui');
		
		$container = $old_event->getContainerEntity();
		if (!$container) {
			// nothing we can do about this
			$old_event->delete();
			continue;
		}
		
		$tza = DateTimeZone::listAbbreviations();
		$tzlist = array();
		foreach ($tza as $zone) {
			foreach ($zone as $item) {
				if (is_string($item['timezone_id']) && $item['timezone_id'] != '') {
					$tzlist[] = $item['timezone_id'];
				}
			}
		}
		// a list of all valid timezones
		$tzlist = array_unique($tzlist);
		
		$event = new Event();
		$event->owner_guid = $old_event->owner_guid;
		$event->container_guid = $old_event->container_guid;
		$event->access_id = $old_event->access_id;
		$event->title = $old_event->title;
		$event->description = $old_event->long_description ? $old_event->long_description : $old_event->description;
		$event->location = $old_event->location;
		$event->tags = $old_event->tags;
		
		// date and time
		$start_date = $old_event->start_date ? date('Y-m-d', $old_event->start_date) : date('Y-m-d', $old_event->time_created);
		$event->start_date = $start_date;

		$end_date = $old_event->end_date ? date('Y-m-d', $old_event->end_date) : $start_date;
		$event->end_date = $end_date;

		if ($old_event->start_time !== '') {
			$hr = floor(((int)$old_event->start_time/15)/4);
			$min = (((int)$old_event->start_time/15) % 4)* 15;
			$am = ($hr < 12) ? 'am' : 'pm';
			if ($hr > 12) {
				$hr -= 12;
			}
			if ($hr == 0) {
				$hr = 12;
			}

			if (!$min) {
				$min = '00';
			}
			
			$start_time = $hr . ":" . $min . $am;
		}
		else {
			$start_time = '12:00am';
			$end_time = '1:00am';
		}
		
		if ($old_event->end_time !== '') {
			$hr = floor(((int)$old_event->end_time/15)/4);
			$min = (((int)$old_event->end_time/15) % 4)* 15;
			$am = ($hr < 12) ? 'am' : 'pm';
			if ($hr > 12) {
				$hr -= 12;
			}
			if ($hr == 0) {
				$hr = 12;
			}

			if (!$min) {
				$min = '00';
			}
			
			$end_time = $hr . ":" . $min . $am;
		}
		else {
			if (!$end_time) {
				$hr++;
				$am = ($hr < 12) ? 'am' : 'pm';
				if ($hr > 12) {
					$hr -= 12;
				}
				if ($hr == 0) {
					$hr = 12;
				}
				$end_time = $hr . ":" . $min . $am;
			}
		}
		
		$event->start_time = $start_time;
		$event->end_time = $end_time;
		
		$timezone = in_array($old_event->time_zone, $tzlist) ? $old_event->time_zone : 'America/New_York';

		// event calendar used some invalid timezones...
		// default to UTC if it fails
		try {
			$dt = new DateTime(null, new DateTimeZone($timezone));	
		} catch (Exception $exc) {
			$dt = new DateTime(null, new DateTimeZone('America/New_York'));
			$timezone = 'America/New_York'; // good as any, and requested by client
		}

		$start_timestamp = $dt->modify("$start_date $start_time")->getTimestamp();
		$start_timestamp_iso = $dt->format('c');

		$end_timestamp = $dt->modify("$end_date $end_time")->getTimestamp();
		$end_timestamp_iso = $dt->format('c');
		
		$event->start_timestamp = $start_timestamp;
		$event->end_timestamp = $end_timestamp;

		$event->start_timestamp_iso = $start_timestamp_iso;
		$event->end_timestamp_iso = $end_timestamp_iso;
		$event->timezone = $timezone;
		$event->all_day = 0;
		$event->end_delta = abs($end_timestamp - $start_timestamp);
		$event->repeat = 0;

		if (!$event->save()) {
			// something went wrong
			continue;
		}
		
		$event->repeat_end_timestamp = $event->calculateRepeatEndTimestamp();
		
		$event->time_created = $old_event->time_created; // might as well preserve this
		$event->save(); // time_created can only be updated, not set on creation
		
		// now we need to add the event to calendars
		
		// first add to the container calendar
		$calendar = Calendar::getPublicCalendar($container);
		$calendar->addEvent($event);

		// now add to any other calendars
		$user_options = array(
			'type' => 'user',
			'joins' => array(
				"JOIN {$dbprefix}entity_relationships r ON r.guid_one = e.guid"
			),
			'wheres' => array(
				"r.relationship = 'personal_event' AND r.guid_two = {$old_event->guid}"
			),
			'limit' => false
		);
				
		$users = new \ElggBatch('elgg_get_entities', $user_options);
		
		foreach ($users as $u) {
			$ucal = Calendar::getPublicCalendar($u);
			$ucal->addEvent($event);
		}
		
		// all done, lets kill the old event
		$old_event->delete();
	}
	
	elgg_set_plugin_setting('migration_process', 0, 'events_ui');
	elgg_set_ignore_access($ia);
}