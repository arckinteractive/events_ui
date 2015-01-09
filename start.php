<?php

// NOTE: due to the old version of jquery in Elgg 1.8 we need to use the old version of fullcalendar
// tried upgrading but too much stuff breaks

namespace Events\UI;

const PLUGIN_ID = 'events_ui';
const UPGRADE_VERSION = 20141215;

require_once __DIR__ . '/lib/page_handlers.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

function init() {
	
	elgg_register_css('fullcalendar', 'mod/' . PLUGIN_ID . '/vendors/fullcalendar-1.6/fullcalendar/fullcalendar.css');
	elgg_register_css('fullcalendar:print', 'mod/' . PLUGIN_ID . '/vendors/fullcalendar-1.6/fullcalendar/fullcalendar.print.css');
	elgg_register_js('fullcalendar', 'mod/' . PLUGIN_ID . '/vendors/fullcalendar-1.6/fullcalendar/fullcalendar.min.js');
	
	elgg_register_js('moment.js', 'mod/' . PLUGIN_ID . '/vendors/jquery/moment.min.js');
	
	elgg_register_simplecache_view('css/events_ui');
	$url = elgg_get_simplecache_url('css', 'events_ui');
	elgg_register_css('events-ui', $url);
	
	elgg_register_simplecache_view('js/events_ui');
	$url = elgg_get_simplecache_url('js', 'events_ui');
	elgg_register_js('events-ui', $url);
	
	elgg_register_page_handler('calendar', __NAMESPACE__ . '\\page_handler');
}


function page_handler($page) {
	switch ($page[0]) {
		case 'view':
			if (!$page[1]) {
				$page[1] = elgg_get_logged_in_user_guid();
			}
			handle_container_calendar($page[1]);
			return true;
			break;
		case 'feed':
			
			$start = get_input('start', false);
			$end = get_input('end', false);
			if (!$page[1] || !is_numeric($start) || !is_numeric($end)) {
				return false;
			}
			
			$params = array(
				'guid' => $page[1],
				'start' => $start,
				'end' => $end
			);
			handle_calendar_feed($params);
			break;
		case 'event':
			handle_event_page($page[1]);
			return true;
			break;
	}
	
	return false;
}