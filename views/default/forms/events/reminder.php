<?php

namespace Events\UI;

use Events\API\Util;

$reminder = elgg_extract('reminder', $vars);
if (!$reminder) {
	return;
}

if ($reminder >= Util::SECONDS_IN_A_DAY && $reminder % Util::SECONDS_IN_A_DAY == 0) {
	$increment = 'day';
	$value = $reminder / Util::SECONDS_IN_A_DAY;
} else if ($reminder >= Util::SECONDS_IN_AN_HOUR && $reminder % Util::SECONDS_IN_AN_HOUR == 0) {
	$increment = 'hour';
	$value = $reminder / Util::SECONDS_IN_AN_HOUR;
} else {
	$increment = 'minute';
	$value = round($reminder / Util::SECONDS_IN_A_MINUTE);
}

?>
<span class="events-ui-reminder-value">
	<?php
	echo elgg_view('input/text', array(
		'name' => 'reminders[value][]',
		'value' => $value,
	));
	?>
</span>
<span class="events-ui-reminder-increment">
	<?php
	echo elgg_view('input/dropdown', array(
		'name' => 'reminders[increment][]',
		'value' => $increment,
		'options_values' => array(
			'minute' => elgg_echo('events_ui:minutes'),
			'hour' => elgg_echo('events_ui:hours'),
			'day' => elgg_echo('events_ui:days'),
		),
	));
	?>
</span>
<?php
echo elgg_view('output/url', array(
	'text' => elgg_view_icon('delete'),
	'href' => '#',
	'class' => 'js-events-ui-reminder-remove',
));
?>

