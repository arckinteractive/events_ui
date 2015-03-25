<?php

namespace Events\UI;

$feed_url = elgg_extract('feed_url', $vars);
$help_url = elgg_get_plugin_setting('ical_help_page_url', 'events_ui');
if ($help_url) {
	$help_url = elgg_view('output/url', array(
		'text' => elgg_echo('events:ical:learn_more'),
		'href' => $help_url,
	));
} else {
	$help_url = '';
}
?>

<div class="events-ui-ical-modal">
	<?php
	echo elgg_view('output/longtext', array(
		'value' => elgg_echo('events:ical:help', array($help_url)),
		'class' => 'elgg-text-help',
	));
	?>
	<div class="ptl pbl">
		<label><?php echo elgg_echo('events:ical:url') ?></label>
		<?php
		echo elgg_view('input/text', array(
			'value' => $feed_url,
			'class' => 'js-events-autoselect',
		));
		?>
	</div>
</div>