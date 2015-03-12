<?php

namespace Events\UI;

use Events\API\Calendar;

$calendar = elgg_extract('entity', $vars);
if (!$calendar instanceof Calendar) {
	return;
}

$container = $calendar->getContainerEntity();

$attr = array(
	'id' => "js-events-ui-calendar-$calendar->guid",
	'class' => 'js-events-ui-fullcalendar',
	'data-guid' => $calendar->guid,
	'data-editable' => $calendar->canAddEvent() ? 1 : 0
);

echo '<div ' . elgg_format_attributes($attr) . '></div>';

$form_attr = array(
	'id' => "js-events-ui-form-$calendar->guid",
	'class' => 'js-events-ui-form hidden',
	'data-guid' => $calendar->guid,
);

echo '<div ' . elgg_format_attributes($form_attr) . '>';
echo elgg_view_form('events/edit', array(
	'enctype' => 'multipart/form-data',
	'class' => 'events-ui-form',
		), array(
	'calendar' => $calendar,
	'container' => $container
));
echo '</div>';
