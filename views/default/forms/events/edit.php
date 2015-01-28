<?php

elgg_load_css('jquery-ui');
elgg_load_css('events-ui');
elgg_load_js('events-ui');

$calendar = $vars['calendar'];
if (!$calendar && $vars['entity']) {
	$calendar = $vars['entity']->getContainerEntity();
}

$hour_options = array();
$time = mktime(0, 0, 0);
$count = 0;
while ($count < 96) {
	$count++;
	$hour_options[] = date('g:ia', $time);
	
	$time += 60*15; //add half an hour
}

echo '<div class="elgg-col elgg-col-1of1">';
echo elgg_view('input/text', array(
	'name' => 'title',
	'value' => $vars['entity'] ? $vars['entity']->title : '',
	'placeholder' => elgg_echo('events:edit:title:placeholder')
));
echo '</div>';

$start = $vars['start_date'] ? $vars['start_date'] : gmdate('Y-m-d');
$end = $vars['end_date'] ? $vars['end_date'] : gmdate('Y-m-d');
echo '<div class="elgg-col elgg-col-1of1">';
echo '<div class="elgg-col elgg-col-1of2">';
echo '<label>' . elgg_echo('events:edit:label:start') . '</label>';
echo '<div class="clearfix clearfloat"></div>';
echo '<div class="elgg-col elgg-col-1of2">';
echo elgg_view('input/text', array(
	'name' => 'start_date',
	'value' => $vars['entity'] ? $vars['entity']->start_date : $start,
	'class' => 'events-ui-datepicker',
	'autoinit' => $vars['dateautoinit']
));
echo '</div>';
echo '<div class="elgg-col elgg-col-1of2">';
echo elgg_view('input/dropdown', array(
	'name' => 'start_time',
	'value' => $vars['entity'] ? $vars['entity']->start_time : '12:00am',
	'options' => $hour_options
));
echo '</div>';
echo '</div>';

echo '<div class="elgg-col elgg-col-1of2">';
echo '<label>' . elgg_echo('events:edit:label:end') . '</label>';
echo '<div class="clearfix clearfloat"></div>';
echo '<div class="elgg-col elgg-col-1of2">';
echo elgg_view('input/text', array(
	'name' => 'end_date',
	'value' => $vars['entity'] ? $vars['entity']->end_date : $end,
	'class' => 'events-ui-datepicker',
	'autoinit' => $vars['dateautoinit']
));
echo '</div>';
echo '<div class="elgg-col elgg-col-1of2">';
echo elgg_view('input/dropdown', array(
	'name' => 'end_time',
	'value' => $vars['entity'] ? $vars['entity']->end_time : '12:00am',
	'options' => $hour_options
));
echo '</div>';
echo '</div>';

echo '<div class="elgg-col elgg-col-1of2">';
echo elgg_view('input/checkbox', array(
	'name' => 'all_day',
	'value' => 1,
	'checked' => $vars['entity']->all_day ? true : false
));
echo '<label>' . elgg_echo('events_ui:allday') . '</label>';
echo '</div>';

echo '<div class="elgg-col elgg-col-1of2">';
$checked = $vars['entity'] ? ($vars['entity']->repeat ? true : false) : false;
echo elgg_view('input/checkbox', array(
	'name' => 'repeat',
	'value' => 1,
	'checked' => $checked,
	)) . '<label>' . elgg_echo('events_ui:repeat') . '...</label>';
echo '</div>';

echo '<div class="events-ui-repeat clearfix clearfloat ptm' . ($checked ? '' : ' hidden') . '">';
echo elgg_view('forms/events/repeat', $vars);
echo '</div>';

echo '<div class="elgg-col elgg-col-1of1">';
echo '<label>' . elgg_echo('events_ui:description') . '</label>';
echo elgg_view('input/plaintext', array(
	'name' => 'description',
	'value' => $vars['entity'] ? $vars['entity']->description : ''
));
echo '</div>';

echo elgg_view('events/add/extend'); // extension point for other plugins

echo '<div class="elgg-col elgg-col-1of1">';
echo '<label>' . elgg_echo('access') . '</label>';
echo elgg_view('input/access', array('entity' => $vars['entity']));
echo '</div>';

echo '<div class="elgg-foot ptm">';
echo elgg_view('input/hidden', array('name' => 'calendar', 'value' => $calendar->guid));
echo elgg_view('input/hidden', array('name' => 'guid', 'value' => $vars['entity'] ? $vars['entity']->guid : 0));
echo elgg_view('input/submit', array('value' => elgg_echo('save')));
echo '</div>';

echo '</div>';


// this is solely to force the loading of core js libs
echo elgg_view('input/date', array('class' => 'hidden'));