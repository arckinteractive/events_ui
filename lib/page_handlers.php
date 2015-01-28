<?php

namespace Events\UI;
use \Events\API\Calendar;
use ElggBatch;

function handle_container_calendar($container_guid) {
	$container = get_entity($container_guid);
	if (!$container) {
		forward('404');
	}
	
	elgg_set_page_owner_guid($container_guid);
	
	// get the calendar
	$calendar = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'calendar',
		'container_guid' => $container->guid,
		'limit' => 1
	));
	
	if (!$calendar) {
		// create one, they're public
		$calendar = new Calendar();
		$calendar->access_id = ACCESS_PUBLIC;
		$calendar->owner_guid = $container_guid;
		$calendar->container_guid = $container_guid;
		
		$ia = elgg_set_ignore_access(true);
		$calendar->save();
		elgg_set_ignore_access($ia);
	}
	else {
		$calendar = $calendar[0];
	}
	
	$title = "Calendar";
	
	$body = elgg_view_layout('one_column', array(
		'content' => elgg_view(PLUGIN_ID . '/calendar', array('entity' => $calendar))
	));
	
	echo elgg_view_page($title, $body);
}



function handle_calendar_feed($params) {
	
	// normalize the timezone (set start for the start of the day, end for the end of its day)
	$start = mktime(0,0,0, date('n', $params['start']), date('j', $params['start']), date('Y', $params['start']));
	$end = mktime(0,0,0, date('n', $params['end']), date('j', $params['end']) + 1, date('Y', $params['end']));

	$calendar = get_entity($params['guid']);

	if (!elgg_instanceof($calendar, 'object', 'calendar') || !$start || !$end) {
		echo json_encode(array());
		exit;
	}

	$events = $calendar->getAllEvents($start, $end);

	$result = array();
	foreach ($events as $e) {
		if (!$e->repeat) {
			$result[] = array(
				'id' => $e->guid,
				'title' => $e->title,
				'description' => $e->description,
				'allDay' => $e->allDay ? 1 : 0,
				'start' => date('c', $e->start_timestamp),
				'end' => date('c', $e->end_timestamp),
				'url' => $e->getURL()
			);
		}
		else {
			$starts = $e->getStartTimes($start, $end);
			
			foreach ($starts as $s) {
				$result[] = array(
					'id' => $e->guid,
					'title' => $e->title,
					'description' => $e->description,
					'allDay' => $e->all_day ? 1 : 0,
					'start' => date('c', $s),
					'end' => date('c', $s + $e->end_delta),
					'url' => $e->getURL()
				);
			}
		}
	}

	// get repeating events
	/*
	unset($options['metadata_name_value_pairs'][0]);

	$options['metadata_name_value_pairs'][2]['values'] = array('daily', 'weekly', 'monthly');

	$r_events = new ElggBatch('elgg_get_entities_from_metadata', $options);
	foreach ($r_events as $e) {
		switch ($e->repeat) {
			case 'daily':
				$day_interval = 60 * 60 * 24;
				for ($i = $interval_start; $i < $interval_end; $i += $day_interval) {

					if ($e->starttime < $i) {
						// this is a day match, and it's after it's started
						// calculate the right timestamp
						$start = mktime(
							date('H', $e->starttime), date('i', $e->starttime), date('s', $e->starttime), date('n', $i), date('j', $i), date('Y', $i)
						);

						$result[] = array(
							'id' => $e->guid,
							'title' => $e->title,
							'description' => $e->description,
							'allDay' => false,
							'start' => date('c', $start),
							'end' => date('c', $end),
							$e->getURL()
						);
					}
				}
				break;
			case 'weekly':
				// determine which week day this falls on
				$day = date('D', $e->starttime);

				$day_interval = 60 * 60 * 24;
				for ($i = $interval_start; $i < $interval_end; $i += $day_interval) {
					$test_day = date('D', $i);

					if ($day == $test_day && $e->starttime < $i) {
						// this is a day match, and it's after it's started
						// calculate the right timestamp
						$start = mktime(
								date('H', $e->starttime), date('i', $e->starttime), date('s', $e->starttime), date('n', $i), date('j', $i), date('Y', $i)
						);

						$result[] = array(
							'id' => $e->guid,
							'title' => $e->title,
							'description' => $e->description,
							'allDay' => false,
							'start' => date('c', $start)
						);
					}
				}
				break;
				
			case 'monthly':
				// determine which day of the month this falls on
				$day = date('j', $e->starttime);

				$day_interval = 60 * 60 * 24;
				for ($i = $interval_start; $i < $interval_end; $i += $day_interval) {
					$test_day = date('j', $i);

					if ($day == $test_day && $e->starttime < $i) {
						// this is a day match, and it's after it's started
						// calculate the right timestamp
						$start = mktime(
								date('H', $e->starttime), date('i', $e->starttime), date('s', $e->starttime), date('n', $i), date('j', $i), date('Y', $i)
						);

						$result[] = array(
							'id' => $e->guid,
							'title' => $e->title,
							'description' => $e->description,
							'allDay' => false,
							'start' => date('c', $start)
						);
					}
				}
				break;
		}
	}
	*/
	
	echo json_encode($result);
	exit;
}


function handle_event_page($guid) {
	$event = get_entity($guid);
	
	if (!elgg_instanceof($event, 'object', 'event')) {
		forward('404');
	}
	
	$calendar = $event->getContainerEntity();
	elgg_set_page_owner_guid($calendar->container_guid);
	
	// breadcrumbs
	elgg_push_breadcrumb(elgg_echo('calendar'), $calendar->getURL());
	elgg_push_breadcrumb($event->title);
	
	// register title menu items
	register_event_title_menu($event);
	
	$content = elgg_view_entity($event);
	
	$layout = elgg_view_layout('content', array(
		'title' => $event->title,
		'content' => $content,
		'filter' => false
	));
	
	echo elgg_view_page($event->title, $layout);
}


function handle_event_edit_page($guid) {
	$event = get_entity($guid);

	if (!elgg_instanceof($event, 'object', 'event') || !$event->canEdit()) {
		forward('404');
	}
	
	$calendar = $event->getContainerEntity();
	elgg_set_page_owner_guid($calendar->container_guid);
	
	// breadcrumbs
	elgg_push_breadcrumb(elgg_echo('calendar'), $calendar->getURL());
	elgg_push_breadcrumb($event->title, $event->getURL());
	elgg_push_breadcrumb(elgg_echo('edit'));
	
	$content = elgg_view_form('events/edit', array('enctype' => 'multipart/form-data'), array('entity' => $event, 'dateautoinit' => 1));
	
	$layout = elgg_view_layout('content', array(
		'title' => $event->title,
		'content' => $content,
		'filter' => false
	));
	
	echo elgg_view_page($event->title, $layout);
}