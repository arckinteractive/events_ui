<?php

namespace Events\UI;

use Events\API\Event;
use Events\API\Util;

$entity = elgg_extract('entity', $vars);
/* @var Event $entity */

$reminders = $entity->reminder;
if (!$reminders) {
	$reminders = array(15 * Util::SECONDS_IN_A_MINUTE);
} else if (!is_array($reminders)) {
	$reminders = array($reminders);
}
?>

<div class="events-ui-reminders-options">
	<div class="events-ui-row">
		<div class="elgg-col elgg-col-1of4 events-ui-label">
			<?php echo elgg_echo('events_ui:reminders') ?>
		</div>
		<div class="elgg-col elgg-col-3of4">
			<ul class="js-events-ui-reminders-list">
				<?php
				foreach ($reminders as $reminder) {
					?>
					<li class="js-events-ui-reminder">
						<?php
						echo elgg_view('forms/events/reminder', array(
							'reminder' => $reminder,
						));
						?>
					</li>
					<?php
				}
				?>
			</ul>
			<div>
				<?php
				echo elgg_view('output/url', array(
					'text' => elgg_echo('events_ui:reminders:add'),
					'href' => '#',
					'class' => 'js-events-ui-reminders-add',
				));
				?>
			</div>
			<div class="js-events-ui-reminder-tmpl hidden">
				<?php
				echo elgg_view('forms/events/reminder', array(
					'reminder' => 15 * Util::SECONDS_IN_A_MINUTE,
				));
				?>
			</div>
		</div>
	</div>
</div>
