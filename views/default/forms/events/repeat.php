<?php

namespace Events\UI;

use Events\API\Event;
use Events\API\Util;

$entity = elgg_extract('entity', $vars);
/* @var Event $entity */
?>

<div class="events-ui-repeat-options">
	<div class="events-ui-row">
		<div class="elgg-col elgg-col-1of4 events-ui-label">
			<?php echo elgg_echo('events_ui:repeats') ?>
		</div>
		<div>
			<?php
			$repeat_options = Util::getRepeatFrequencies();
			if (!$vars['repeat_frequency']) {
				$vars['repeat_frequency'] = Util::FREQUENCY_WEEKLY;
			}
			echo elgg_view('input/dropdown', array(
				'name' => 'repeat_frequency',
				'value' => $entity ? $entity->repeat_frequency : $vars['repeat_frequency'],
				'options_values' => $repeat_options
			));
			?>
		</div>
	</div>
	<div class="events-ui-row" data-frequency="<?php echo Util::FREQUENCY_MONTHLY ?>" class="hidden">
		<div class="elgg-col elgg-col-1of4 events-ui-label">
			<?php
			echo elgg_echo('repeat_ui:repeat_monthly_by');
			?>
		</div>
		<div class="elgg-col elgg-col-3of4">
			<?php
			if (!$vars['repeat_monthly_by']) {
				$vars['repeat_monthly_by'] = Util::REPEAT_MONTHLY_BY_DATE;
			}
			echo elgg_view('input/radio', array(
				'name' => 'repeat_monthly_by',
				'value' => $entity ? $entity->repeat_monthly_by : $vars['repeat_monthly_by'],
				'align' => 'horizontal',
				'options' => array(
					elgg_echo('repeat_ui:repeat_monthly_by:day_of_month') => Util::REPEAT_MONTHLY_BY_DATE,
					elgg_echo('repeat_ui:repeat_monthly_by:day_of_week') => Util::REPEAT_MONTHLY_BY_DAY_OF_WEEK,
			)));
			?>
		</div>
	</div>
	<div class="events-ui-row" data-frequency="<?php echo Util::FREQUENCY_WEEKLY ?>" class="hidden">
		<div class="elgg-col elgg-col-1of4 events-ui-label">
			<?php
			echo elgg_echo('repeat_ui:repeat_weekly_days');
			?>
		</div>
		<div class="elgg-col elgg-col-3of4">
			<?php
			$weekdays_options = array();
			$weekdays = Util::getWeekdays();
			foreach ($weekdays as $weekday) {
				$label = substr(elgg_echo("events:wd:$weekday"), 0, 3);
				$weekdays_options[$label] = $weekday;
			}
			if (!$vars['repeat_weekly_days']) {
				$vars['repeat_weekly_days'] = ($entity) ? date('D', $entity->start_timestamp) : array();
			}
			$value = $entity ? $entity->repeat_weekly_days : $vars['repeat_weekly_days'];
			$value = (!is_array($value)) ? array($value) : $value;

			echo elgg_view('input/checkboxes', array(
				'name' => 'repeat_weekly_days',
				'value' => $value,
				'align' => 'horizontal',
				'default' => '',
				'options' => $weekdays_options
			));
			?>
		</div>
	</div>
	<div class="events-ui-row">
		<div class="elgg-col elgg-col-1of4 events-ui-label">
			<?php
			echo elgg_echo('repeat_ui:repeat_ends');
			?>
		</div>
		<div class="elgg-col elgg-col-3of4">
			<?php
			$after_input = elgg_view('input/text', array(
				'name' => 'repeat_end_after',
				'value' => $entity ? $entity->repeat_end_after : $vars['repeat_end_after'],
				'pattern' => '\d+',
				'class' => 'events-text-small'
			));

			$on_input = elgg_view('input/text', array(
				'name' => 'repeat_end_on',
				'value' => $entity ? $entity->repeat_end_on : $vars['repeat_end_on'],
				'class' => 'events-ui-datepicker events-text-small',
				'autoinit' => $vars['dateautoinit']
			));
			$repeat_ends_options = array(
				elgg_echo('events_ui:repeat_ends:never') => Util::REPEAT_END_NEVER,
				elgg_echo('events_ui:repeat_ends:after', array($after_input)) => Util::REPEAT_END_AFTER,
				elgg_echo('events_ui:repeat_ends:on', array($on_input)) => Util::REPEAT_END_ON,
			);
			if (!$vars['repeat_end_type']) {
				$vars['repeat_end_type'] = Util::REPEAT_END_NEVER;
			}
			echo elgg_view('input/radio', array(
				'name' => 'repeat_end_type',
				'value' => $entity ? $entity->repeat_end_type : $vars['repeat_end_type'],
				'options' => $repeat_ends_options,
			));
			?>
		</div>
	</div>
	<div class="events-ui-row">
		<label class="events-ui-repeat-text"></label>
	</div>
</div>
