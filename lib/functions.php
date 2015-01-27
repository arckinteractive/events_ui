<?php

namespace Events\UI;

function register_event_title_menu($event) {
	if ($event->canEdit()) {
		elgg_register_menu_item('title', array(
			'name' => 'edit',
			'text' => elgg_echo('edit'),
			'href' => 'calendar/events/edit/' . $event->guid,
			'class' => 'elgg-button elgg-button-action'
		));
		
		elgg_register_menu_item('title', array(
			'name' => 'delete',
			'text' => elgg_echo('delete'),
			'href' => 'action/events/delete?guid=' . $event->guid,
			'is_action' => true,
			'class' => 'elgg-button elgg-button-delete elgg-requires-confirmation'
		));
	}
}