<?php

$title = elgg_echo('events_ui:repeat');

$repeat_options = array(
	'daily' => elgg_echo('events_ui:repeat:daily'),
	'weekday' => elgg_echo('events_ui:repeat:weekday'),
	'dailymwf' => elgg_echo('events_ui:repeat:mwf'),
	'dailytt' => elgg_echo('events_ui:repeat:tt'),
	'weekly' => elgg_echo('events_ui:repeat:weekly'),
	'monthly' => elgg_echo('events_ui:repeat:monthly'),
	'yearly' => elgg_echo('events_ui:repeat:yearly'),
);

$after_input = elgg_view('input/text', array(
	'name' => 'repeat_end_after',
	'value' => $vars['entity'] ? $vars['entity']->repeat_end_after : $vars['repeat_end_after'],
	'pattern' => '\d+',
	'class' => 'events-text-small'
));

$on_input = elgg_view('input/text', array(
	'name' => 'repeat_end_on',
	'value' => $vars['entity'] ? $vars['entity']->repeat_end_on : $vars['repeat_end_on'],
	'class' => 'events-ui-datepicker events-text-small',
	'autoinit' => $vars['dateautoinit']
));

$repeat_ends_options = array(
	elgg_echo('events_ui:repeat_ends:never') => 'never',
	elgg_echo('events_ui:repeat_ends:after', array($after_input)) => 'after',
	elgg_echo('events_ui:repeat_ends:on', array($on_input)) => 'on'
);

if (!$vars['repeat_frequency']) {
	$vars['repeat_frequency'] = 'weekly';
}

$repeat_frequency_label = elgg_echo('events_ui:repeats') . ':';
$repeat_frequency = elgg_view('input/dropdown', array(
	'name' => 'repeat_frequency',
	'value' => $vars['entity'] ? $vars['entity']->repeat_frequency : $vars['repeat_frequency'],
	'options_values' => $repeat_options
));

if (!$vars['repeat_end_type']) {
	$vars['repeat_end_type'] = 'never';
}
$repeat_ends_label = elgg_echo('repeat_ui:repeat_ends') . ':';
$repeat_ends_input = elgg_view('input/radio', array(
	'name' => 'repeat_end_type',
	'value' => $vars['entity'] ? $vars['entity']->repeat_end_type : $vars['repeat_end_type'],
	'options' => $repeat_ends_options
));


$body = <<<BODY
	<table>
		<tr>
			<td>
			$repeat_frequency_label
			</td>
			<td>
			$repeat_frequency
			</td>
		</tr>
		<tr>
			<td>
			$repeat_ends_label
			</td>
			<td>
			$repeat_ends_input
			</td>
		</tr>
	</table>
BODY;

echo elgg_view_module('info', $title, $body);