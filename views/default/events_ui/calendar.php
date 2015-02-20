<?php

namespace Events\UI;

use Events\API\Calendar;

$calendar = elgg_extract('entity', $vars);
if (!$calendar instanceof Calendar) {
	return;
}

$attr = array(
	'id' => "events-ui-calendar",
	'data-guid' => $calendar->guid,
	'data-editable' => $calendar->canAddEvent() ? 1 : 0
);

echo '<div ' . elgg_format_attributes($attr) . '></div>';

echo '<div class="events-ui-add-event-form hidden">';
echo elgg_view_form('events/edit', array('enctype' => 'multipart/form-data'), array('calendar' => $calendar));
echo '</div>';