<?php

namespace Events\UI;

$options = array(
	'type' => 'object',
	'subtype' => 'calendar',
	'container_guids' => array($vars['entity']->container_guid),
	'limit' => false
);

$calendars = elgg_get_entities($options);

echo elgg_view('components/calendar', array(
	'items' => $calendars,
	'limit' => $vars['entity']->num_results,
	'upcoming' => $vars['entity']->upcoming,
));