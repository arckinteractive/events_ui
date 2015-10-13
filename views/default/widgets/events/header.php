<?php

namespace Events\UI;

use DateTime;
use DateTimeZone;
use Events\API\Util;

$start = (int) Util::getMonthStart((int) get_input('event_widget_start', time()));

$dt = new DateTime(null, new DateTimeZone(Util::UTC));
$prev_start = $dt->setTimestamp($start)->modify('-1 month')->getTimestamp();
$next_start = $dt->setTimestamp($start)->modify('+1 month')->getTimestamp();

$prev = elgg_view('output/url', array(
	'text' => '<<',
	'href' => '#',
	'class' => 'elgg-button elgg-button-action events-widget-nav',
	'data-guid' => $vars['entity']->guid,
	'data-start' => $prev_start
));

if ($prev_start < $now && $start < $now && $vars['entity']->upcoming) {
	$prev = '&nbsp;';
}

$next = elgg_view('output/url', array(
	'text' => '>>',
	'href' => '#',
	'class' => 'elgg-button elgg-button-action events-widget-nav float-alt',
	'data-guid' => $vars['entity']->guid,
	'data-start' => $next_start
));

$current = $dt->setTimestamp($start)->format('F');

?>
<div class="row clearfix mbm">
	<div class="elgg-col elgg-col-1of3">
		<?php echo $prev; ?>
	</div>
	<div class="elgg-col elgg-col-1of3 center pts">
		<h3><?php echo $current; ?></h3>
	</div>
	<div class="elgg-col elgg-col-1of3">
		<?php echo $next; ?>
	</div>
</div>


<script>
	require(['widgets/events/header']);
</script>