<?php

use Events\API\Calendar;

namespace Events\UI;

elgg_push_breadcrumb(elgg_echo('events:calendars'), Calendar::SUBTYPE);

elgg_register_title_button();

$title = elgg_echo('events:calendar:all');
$content = elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => Calendar::SUBTYPE,
	'no_results' => elgg_echo('events:calendar:none'),
));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter_context' => 'all',
		));

echo elgg_view_page($title, $layout);
