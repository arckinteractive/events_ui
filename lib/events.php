<?php

namespace Events\UI;

/**
 * Registers menu items on page setup
 */
function pagesetup() {

	elgg_register_menu_item('site', array(
		'name' => 'calendar',
		'href' => 'calendar',
		'text' => elgg_echo('events:calendar'),
	));

	
}