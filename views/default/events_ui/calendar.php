<?php

namespace Events\UI;

$calendar = $vars['entity'];

elgg_load_css('jquery-ui');
elgg_load_css('events-ui');
elgg_load_css('fullcalendar');
elgg_load_js('fullcalendar');
elgg_load_js('events-ui');
elgg_load_js('jquery.form');
elgg_load_js('moment.js');

elgg_load_css('lightbox');
elgg_load_js('lightbox');

$attr = array(
	'id' => "events-ui-calendar",
	'data-guid' => $calendar->guid,
	'data-editable' => $calendar->canEdit() ? 1 : 0
);

echo '<div ' . elgg_format_attributes($attr) . '></div>';

echo '<div class="events-ui-add-event-form hidden">';
echo elgg_view_form('events/edit', array('enctype' => 'multipart/form-data'), array('calendar' => $calendar));
echo '</div>';