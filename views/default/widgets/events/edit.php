<?php

echo '<label>' . elgg_echo('events:widget:settings:numresults') . ': </label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[num_results]',
	'value' => $vars['entity']->num_results ? $vars['entity']->num_results : 10,
	'options' => array_merge(range(1,10), range(15,50,5))
));

echo '<br><br>';

echo '<label>' . elgg_echo('events:widget:settings:upcoming') . ': </label>';
echo elgg_view('input/dropdown', array(
	'name' => 'params[upcoming]',
	'value' => $vars['entity']->upcoming ? $vars['entity']->upcoming : 0,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes')
	)
));

echo '<br><br>';