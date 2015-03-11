<?php

namespace Events\UI;

/**
 * @uses $vars['user'] ElggUser
 */

/* @var ElggUser $user */
$user = $vars['user'];

global $NOTIFICATION_HANDLERS;
$calendar_notifications = get_calendar_notifications();

?>
<div class="notification_calendar">
<div class="elgg-module elgg-module-info">
	<div class="elgg-head">
		<h3>
			<?php echo elgg_echo('calendar:notifications'); ?>
		</h3>
	</div>
</div>
<table id="notificationstable" cellspacing="0" cellpadding="4" width="100%">
	<tr>
		<td>&nbsp;</td>
<?php
$i = 0; 
foreach($NOTIFICATION_HANDLERS as $method => $foo) {
	if ($i > 0) {
		echo "<td class='spacercolumn'>&nbsp;</td>";
	}
?>
		<td class="<?php echo $method; ?>togglefield"><?php echo elgg_echo('notification:method:'.$method); ?></td>
<?php
	$i++;
}
?>
		<td>&nbsp;</td>
	</tr>

<?php
foreach ($calendar_notifications as $notification_name):
?>
	<tr>
		<td class="namefield">
			<p>
				<?php echo elgg_echo('calendar:notifications:' . $notification_name); ?>
			</p>
		</td>

<?php

$fields = '';
$i = 0;
foreach($NOTIFICATION_HANDLERS as $method => $foo) {
	$attr = '__notify_' . $method . '_' . $notification_name;
	$checked = '';
	if ($user->$attr) {
		$checked = 'checked="checked"';
	}
	
	if ($i > 0) {
		$fields .= "<td class='spacercolumn'>&nbsp;</td>";
	}
	$fields .= <<< END
		<td class="{$method}togglefield">
		<a  border="0" id="{$method}{$notification_name}" class="{$method}toggleOff" onclick="adjust{$method}_alt('{$method}{$notification_name}');">
		<input type="checkbox" name="{$method}{$notification_name}" id="{$method}-{$notification_name}-checkbox" onclick="adjust{$method}('{$method}{$notification_name}');" value="1" {$checked} /></a></td>
END;
	$i++;
}
echo $fields;

?>

		<td>&nbsp;</td>
	</tr>
<?php endforeach; ?>
</table>
</div>