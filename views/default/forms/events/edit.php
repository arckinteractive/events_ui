<?php

namespace Events\UI;

use DateTime;
use DateTimeZone;
use Events\API\Event;
use Events\API\Util;

$entity = elgg_extract('entity', $vars);

if ($entity && !($entity instanceof Event)) {
	echo '<p>' . elgg_echo('events:error:invalid:guid') . '</p>';
	return;
}

$container = elgg_extract('container', $vars, elgg_get_logged_in_user_entity());

$calendar = $vars['calendar'];
if (!$calendar && $entity) {
	$calendar = $entity->getContainerEntity();
}

$dt = new DateTime(null, new DateTimeZone(Util::UTC));
$hour_options = array();
$hour_options_ts = range(0, Util::SECONDS_IN_A_DAY, 900); // step of 15 minutes
foreach ($hour_options_ts as $ts) {
	$hour_options[] = $dt->setTimestamp($ts)->format('g:ia');
}

$dt->setTimezone(new DateTimeZone(Util::getClientTimezone()));
$start = $vars['start_date'] ? $vars['start_date'] : $dt->format('Y-m-d');
$start_time = round(($dt->format('U') + 900 / 2) / 900) * 900;

$end = $vars['end_date'] ? $vars['end_date'] : $dt->modify('+1 hour')->format('Y-m-d');
$end_time = round(($dt->format('U') + 900 / 2) / 900) * 900;

$recurring = ($entity) ? $entity->isRecurring() : false;
$has_reminders = ($entity) ? $entity->hasReminders() : false;
?>
<div class="events-ui-row">
	<?php
	echo elgg_view('input/text', array(
		'name' => 'title',
		'value' => $entity ? $entity->title : '',
		'placeholder' => elgg_echo('events:edit:title:placeholder')
	));
	?>
</div>
<div class="events-ui-row">
	<label><?php echo elgg_echo('events:edit:label:location') ?></label>
	<?php
	echo elgg_view('input/location', array(
		'name' => 'location',
		'value' => ($entity) ? $entity->getLocation() : '',
	));
	?>
</div>
<div class="events-ui-row">
	<div class="elgg-col elgg-col-1of2">
		<label class="elgg-col elgg-col-1of1"><?php echo elgg_echo('events:edit:label:start') ?></label>
		<div class="elgg-col elgg-col-1of2">
			<?php
			echo elgg_view('input/text', array(
				'name' => 'start_date',
				'value' => $entity ? $entity->start_date : $start,
				'class' => 'events-ui-datepicker',
				'autoinit' => $vars['dateautoinit']
			));
			?>
		</div>
		<div class="elgg-col elgg-col-1of2">
			<?php
			echo elgg_view('input/dropdown', array(
				'name' => 'start_time',
				'value' => $entity ? $entity->start_time : $start_time,
				'options' => $hour_options,
				'class' => 'events-ui-time',
			));
			?>
		</div>
	</div>
	<div class="elgg-col elgg-col-1of2">
		<label class="elgg-col elgg-col-1of1"><?php echo elgg_echo('events:edit:label:end') ?></label>
		<div class="elgg-col elgg-col-1of2">
			<?php
			echo elgg_view('input/text', array(
				'name' => 'end_date',
				'value' => $entity ? $entity->end_date : $end,
				'class' => 'events-ui-datepicker',
				'autoinit' => $vars['dateautoinit']
			));
			?>
		</div>
		<div class="elgg-col elgg-col-1of2">
			<?php
			echo elgg_view('input/dropdown', array(
				'name' => 'end_time',
				'value' => $entity ? $entity->end_time : $end_time,
				'options' => $hour_options,
				'class' => 'events-ui-time',
			));
			?>
		</div>
	</div>
</div>
<?php
if (elgg_get_plugin_setting('timezone_picker', 'events_ui')) {
	?>
	<div class="events-ui-row">
		<label><?php echo elgg_echo('events:edit:label:timezone') ?></label>
		<?php
		echo elgg_view('input/timezone', array(
			'name' => 'timezone',
			'value' => ($entity->timezone) ? : Util::getClientTimezone(),
		));
		?>
	</div>
	<?php
} else {
	elgg_echo('input/hidden', array(
		'name' => 'timezone',
		'value' => Util::getClientTimezone(),
	));
}
?>
<div class="events-ui-row">
	<ul class="elgg-menu elgg-menu-hz">
		<li>
			<label>
				<?php
				echo elgg_view('input/checkbox', array(
					'name' => 'all_day',
					'value' => 1,
					'checked' => $entity->all_day ? true : false
				));
				echo elgg_echo('events_ui:allday');
				?>
			</label>
		</li>
		<li>
			<label>
				<?php
				echo elgg_view('input/checkbox', array(
					'name' => 'repeat',
					'value' => 1,
					'checked' => $recurring,
				));
				echo elgg_echo('events_ui:repeat')
				?>
			</label>
		</li>
		<li>
			<label>
				<?php
				echo elgg_view('input/checkbox', array(
					'name' => 'has_reminders',
					'value' => 1,
					'checked' => $has_reminders,
				));
				echo elgg_echo('events_ui:enable_reminders')
				?>
			</label>
		</li>
	</ul>
</div>
<div class="events-ui-row">
	<div class="events-ui-repeat <?php echo ($recurring) ? '' : 'hidden' ?>">
		<?php
		echo elgg_view('forms/events/repeat', $vars);
		?>
	</div>
</div>
<div class="events-ui-row">
	<div class="events-ui-reminders <?php echo ($has_reminders) ? '' : 'hidden' ?>">
		<?php
		echo elgg_view('forms/events/reminders', $vars);
		?>
	</div>
</div>
<div class="events-ui-row">
	<label><?php echo elgg_echo('events_ui:description') ?></label>
	<?php
	echo elgg_view('input/plaintext', array(
		'name' => 'description',
		'value' => $entity ? $entity->description : '',
		'rows' => 3,
	));
	?>
</div>

<?php
// extension point for other plugins
echo elgg_view('events/add/extend');
?>

<div class="events-ui-row">
	<label><?php echo elgg_echo('access') ?></label>
	<?php
	echo elgg_view('input/access', array(
		'name' => 'access_id',
		'entity' => $entity
	));
	?>
</div>

<?php
if ($entity):
	?>
	<div class="events-ui-row">
		<label>
			<?php
			echo elgg_view('input/checkbox', array(
				'name' => 'resend_notifications',
				'value' => 1
			));
			echo elgg_echo('events_ui:resend:notifications');
			?>
		</label>
	</div>
	<?php
endif;
?>

<div class="events-ui-row elgg-foot">
	<?php
	echo elgg_view('input/hidden', array(
		'name' => 'calendar',
		'value' => $calendar->guid
	));
	echo elgg_view('input/hidden', array(
		'name' => 'container_guid',
		'value' => $container->guid,
	));
	echo elgg_view('input/hidden', array(
		'name' => 'guid',
		'value' => $entity->guid,
	));
	echo elgg_view('input/submit', array(
		'value' => elgg_echo('save')
	));
	?>
</div>

<?php
// this is solely to force the loading of core js libs
echo elgg_view('input/date', array('class' => 'hidden', 'style' => 'display: none;'));
