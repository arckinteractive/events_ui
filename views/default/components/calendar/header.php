<?php

namespace Events\UI;

use DateTime;
use DateTimeZone;
use Events\API\Util;

$calendars = elgg_extract('calendars', $vars);
if (empty($calendars)) {
	return;
}

$calendar_guids = array_map(function($cal) {
	return $cal->guid;
}, $calendars);

$start = (int) Util::getMonthStart((int) elgg_extract('start_time', $vars, time()));

$dt = new DateTime(null, new DateTimeZone(Util::UTC));
$prev_start = $dt->setTimestamp($start)->modify('-1 month')->getTimestamp();
$next_start = $dt->setTimestamp($start)->modify('+1 month')->getTimestamp();

$prev = elgg_view('output/url', array(
	'text' => elgg_view_icon('angle-double-left'),
	'href' => 'javascript:void(0);',
	'class' => 'events-widget-nav',
	'data-opts' => json_encode(array(
		'start_time' => $prev_start,
		'items' => $calendar_guids,
	)),
		));

if ($prev_start < $now && $start < $now && elgg_extract('upcoming', $vars, true)) {
	$prev = '&nbsp;';
}

$next = elgg_view('output/url', array(
	'text' => elgg_view_icon('angle-double-right'),
	'href' => 'javascript:void(0);',
	'class' => 'events-widget-nav',
	'data-opts' => json_encode(array(
		'start_time' => $next_start,
		'items' => $calendar_guids,
	)),
		));

$current = $dt->setTimestamp($start)->format('F');
?>
<div class="row clearfix events-ui-controls">
	<div class="elgg-col elgg-col-1of4 left">
		<?php echo $prev; ?>
	</div>
	<div class="elgg-col elgg-col-1of2 center">
		<?php echo $current; ?>
	</div>
	<div class="elgg-col elgg-col-1of4 right">
		<?php echo $next; ?>
	</div>
</div>

<script>
	require(['components/calendar/header']);
</script>