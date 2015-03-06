<?php

namespace Events\UI;

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
				if ($item instanceof ElggMenuItem && in_array($item, array('edit', 'delete'))) {
					unset($return[$key]);
				}
			}
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