<?php

namespace Events\UI;

use ElggObject;
use Events\API\Calendar;
use Events\API\Event;

/**
 * Handles calendar pages
 * /calendar/view[/<guid>]       View a calendar (if guid is a user or group, displays their public calendar)
 * /calendar/owner/<username>    List all user calendars
 * /calendar/friends/<username>  List all friend calendars
 * /calendar/group/<guid>        List all group calendars
 * /calendar/feed/<guid>         View an events feed for a given calendar
 *                               Query elements:
 *                                 ?start=<start_timestamp>
 *                                 &end=<end_timestamp>
 *                                 [&view=<json|ical>]
 * /calendar/ical/<guid>[/<filename>] Calendar's iCal feed
 *                               Query elements:
 *                                 ?start=<start_timestamp>
 *                                 &end=<end_timestamp>
 * /calendar/events				 Reroute to {@see event_page_handler()}
 *
 * @param array $page URL segments
 * @return boolean
 */
function page_handler($page) {

	elgg_load_css('jquery-ui');
	elgg_load_css('components/calendar');
	elgg_require_js('components/calendar');

	switch ($page[0]) {
		case 'site':
			$site_calendar = elgg_get_plugin_setting('sitecalendar', 'events_ui');
			if (!$site_calendar) {
				return false;
			}
			set_input('guid', elgg_get_site_entity()->guid);
			$page_view = elgg_view('resources/calendar/view');
			break;
		case 'all':
			$page_view = elgg_view('resources/calendar/all');
			break;

		default :
		case 'view':
			set_input('guid', $page[1]);
			$page_view = elgg_view('resources/calendar/view');
			break;

		case 'owner' :
			set_input('username', $page[1]);
			$page_view = elgg_view('resources/calendar/owner');
			break;

		case 'friends' :
			set_input('username', $page[1]);
			$page_view = elgg_view('resources/calendar/friends');
			break;

		case 'group' :
			set_input('container_guid', $page[1]);
			$page_view = elgg_view('resources/calendar/group');
			break;

		case 'add' :
			set_input('container_guid', $page[1]);
			$page_view = elgg_view('resources/calendar/add');
			break;

		case 'edit' :
			set_input('guid', $page[1]);
			$page_view = elgg_view('resources/calendar/edit');
			break;

		case 'feed':
			set_input('guid', $page[1]);
			$page_view = elgg_view('resources/calendar/feed');
			break;

		case 'ical' :
			set_input('guid', $page[1]);
			set_input('filename', $page[2]);
			elgg_set_viewtype('ical');
			$page_view = elgg_view('resources/calendar/feed');
			break;

		case 'timezones' :
			set_input('country', $page[1]);
			elgg_set_viewtype('json');
			$page_view = elgg_view('resources/calendar/timezones');
			break;

		case 'settings':
			elgg_set_context('settings');
			set_input('username', $page[1]);
			$page_view = elgg_view('resources/calendar/settings');
			break;

		case 'events':
			return event_pagehandler(array_slice($page, 1));
	}

	if (isset($page_view)) {
		echo $page_view;
		return true;
	}

	return false;
}

/**
 * Handles event pages
 *
 * @param array $page URL segments
 * @return boolean
 */
function event_pagehandler($page) {

	elgg_load_css('jquery-ui');
	elgg_load_css('components/calendar');
	elgg_require_js('components/calendar');

	switch ($page[0]) {
		case 'view':
			set_input('guid', $page[1]);
			echo elgg_view('resources/events/view');
			return true;

		case 'add' :
			set_input('container_guid', $page[1]);
			echo elgg_view('resources/events/add');
			return true;

		case 'edit':
			set_input('guid', $page[1]);
			echo elgg_view('resources/events/edit');
			return true;
	}
	return false;
}