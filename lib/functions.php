<?php

namespace Events\UI;

use ElggEntity;
use Events\API\Calendar;

function register_event_title_menu($event) {

	/**
	 * @todo: implement support for picking the calendar
	 */
	elgg_register_menu_item('title', array(
		'name' => 'add_to_calendar',
		'href' => elgg_http_add_url_query_elements('action/calendar/add_event', array(
			'event_guid' => $event->guid,
		)),
		'is_action' => true,
		'text' => elgg_echo('events:add_to_calendar:default'),
		'link_class' => 'elgg-button elgg-button-action',
	));

	if ($event->canEdit()) {
		elgg_register_menu_item('title', array(
			'name' => 'edit',
			'text' => elgg_echo('edit'),
			'href' => 'calendar/events/edit/' . $event->guid,
			'link_class' => 'elgg-button elgg-button-action'
		));

		elgg_register_menu_item('title', array(
			'name' => 'delete',
			'text' => elgg_echo('delete'),
			'href' => 'action/events/delete?guid=' . $event->guid, // add calendar_guid for proper forwarding
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-delete elgg-requires-confirmation'
		));
	}
}
