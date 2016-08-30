<?php

namespace Events\UI;

use Events\API\Calendar;

$calendar = elgg_extract('entity', $vars);
if (!$calendar instanceof Calendar) {
	return;
}

$container = $calendar->getContainerEntity();

$event_form = elgg_http_add_url_query_elements("events/add/$container->guid", [
	'calendar_guid' => $calendar->guid,
]);

$attrs = array(
	'id' => "js-events-ui-calendar-$calendar->guid",
	'class' => 'js-events-ui-fullcalendar',
	'data-guid' => $calendar->guid,
	'data-editable' => $calendar->canAddEvent() ? 1 : 0,
	'data-event-form' => elgg_normalize_url($event_form)
);

echo elgg_format_element('div', $attrs);
