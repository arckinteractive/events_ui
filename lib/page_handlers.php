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
	
	$content = elgg_view_entity($event, array('full_view' => true));
	
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