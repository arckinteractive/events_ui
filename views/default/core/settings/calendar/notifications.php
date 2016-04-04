<?php

namespace Events\UI;

/**
 * @uses $vars['user'] ElggUser
 */
/* @var ElggUser $user */
$user = elgg_extract('user', $vars);

$methods = _elgg_services()->notifications->getMethods();
$calendar_notifications = get_calendar_notifications();
?>
<div class="notification_calendar">
	<div class="elgg-module elgg-module-info elgg-subsriptions-module">
		<div class="elgg-head">
			<h3>
				<?php echo elgg_echo('calendar:notifications'); ?>
			</h3>
		</div>
		<div class="elgg-body">
			<table id="notificationstable" class="elgg-subscriptions-table">
				<thead>
					<tr>
						<th class="namefield elgg-subscriptions-type-label"></th>
						<?php
						foreach ($methods as $method) {
							echo elgg_format_element('th', [
								'class' => $method ? "{$method}togglefield elgg-subscriptions-toggle-cell" : 'elgg-subscriptions-toggle-cell',
									], elgg_echo("notification:method:$method"));
						}
						?>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>

					<?php
					foreach ($calendar_notifications as $notification_name) {
						$user_notification_settings = get_calendar_notification_methods($user, $notification_name);
						?>
						<tr class="elgg-subscriptions-calendar">
							<td class="namefield elgg-subscriptions-type-label">
								<?php echo elgg_echo('calendar:notifications:' . $notification_name); ?>
							</td>

							<?php
							$fields = '';
							foreach ($methods as $method) {
								$attr = '__notify_' . $method . '_' . $notification_name;
								$checked = '';
								if (in_array($method, $user_notification_settings)) {
									$checked = 'checked="checked"';
								}
								$fields .= <<< END
		<td class="{$method}togglefield">
		<a  border="0" id="{$method}{$notification_name}" class="{$method}toggleOff elgg-subscriptions-toggle-cell" onclick="adjust{$method}_alt('{$method}{$notification_name}');">
		<input type="checkbox" name="{$method}{$notification_name}" id="{$method}-{$notification_name}-checkbox" onclick="adjust{$method}('{$method}{$notification_name}');" value="1" {$checked} /></a></td>
END;
							}
							echo $fields;
							?>

							<td>&nbsp;</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>