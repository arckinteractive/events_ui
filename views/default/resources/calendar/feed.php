<?php

namespace Events\UI;

use DateTime;
use DateTimeZone;
use Events\API\Calendar;
use Events\API\Event;
use Events\API\PAM;
use Events\API\Util;
use Exception;

$is_logged_in = elgg_is_logged_in();

$guid = get_input('guid');

if (!$is_logged_in) {
	try {
		PAM::authenticate();
	} catch (Exception $ex) {
		register_error($ex->getMessage());
		forward('', '403');
	}
}

$entity = get_entity($guid);
if (!$entity instanceof Calendar) {
	forward('', '404');
}

$owner = $entity->getOwnerEntity();

elgg_set_page_owner_guid($owner->guid);

elgg_register_title_button('events', 'add', 'object', Event::SUBTYPE);

elgg_push_breadcrumb($owner->name, "calendar/owner/$owner->username");
elgg_push_breadcrumb($entity->getDisplayName());

$start = (int) get_input('start', time());

$start = (int) Util::getMonthStart($start);
$end = (int) Util::getMonthEnd($start);

$timezone = Util::getClientTimezone();
$start_local = $start - Util::getOffset($start, Util::UTC, $timezone);
$end_local = $end - Util::getOffset($end, Util::UTC, $timezone);

$events = $entity->getAllEventInstances($start_local, $end_local);

if (!$is_logged_in) {
	logout();
}

elgg_register_menu_item('title', array(
	'name' => 'calendar_view',
	'href' => elgg_http_add_url_query_elements("calendar/view/$entity->guid", array(
		'start' => $start,
		'end' => $end,
	)),
	'text' => elgg_echo('events:view:calendar'), //elgg_view_icon('events-calendar'),
	'title' => elgg_echo('events:view:calendar:switch'),
	'link_class' => 'elgg-button elgg-button-action',
));
elgg_register_menu_item('title', array(
	'name' => 'ical_view',
	'href' => $entity->getIcalURL("calendar/ical/{$entity->guid}/calendar{$entity->guid}.ics", array(
			//'start' => $start,
			//'end' => $end,
	)),
	'text' => elgg_echo('events:view:ical'), //elgg_view_icon('events-ical'),
	'title' => elgg_echo('events:view:ical'),
	'link_class' => 'elgg-button elgg-button-action js-events-ui-ical-modal-trigger',
));

$dt = new DateTime(null, new DateTimeZone(Util::UTC));
$prev_start = $dt->setTimestamp($start)->modify('-1 month')->getTimestamp();
$next_start = $dt->setTimestamp($start)->modify('+1 month')->getTimestamp();

elgg_register_menu_item('title', array(
	'name' => 'prev_month',
	'text' => "&laquo;&nbsp;" . $dt->setTimestamp($prev_start)->format('F'),
	'href' => elgg_http_add_url_query_elements("calendar/feed/$entity->guid", array(
		'start' => $prev_start,
	)),
	'link_class' => 'elgg-button elgg-button-action mlm',
	'priority' => 100,
));
elgg_register_menu_item('title', array(
	'name' => 'next_month',
	'text' => $dt->setTimestamp($next_start)->format('F') . "&nbsp;&raquo;",
	'href' => elgg_http_add_url_query_elements("calendar/feed/$entity->guid", array(
		'start' => $next_start,
	)),
	'link_class' => 'elgg-button elgg-button-action',
	'priority' => 101,
));

$title = elgg_echo('events:feed:month', array($dt->setTimestamp($start)->format('F')));
$content = elgg_view('events_ui/feed', array(
	'events' => $events
		));

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'sidebar' => false,
	'filter' => false,
	'entity' => $entity,
		));

echo elgg_view_page($title, $layout, 'default', array(
	'entity' => $entity,
));
