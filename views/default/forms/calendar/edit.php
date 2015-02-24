<?php

namespace Events\UI;

$entity = elgg_extract('entity', $vars);
$container = elgg_extract('container', $vars, elgg_get_logged_in_user_entity());
?>
<div class="events-ui-row">
	<label><?php echo elgg_echo('title') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'title',
		'value' => elgg_extract('title', $vars, $entity->title),
		'placeholder' => elgg_echo('events:calendar:edit:title:placeholder')
	));
	?>
</div>
<div class="events-ui-row">
	<label><?php echo elgg_echo('description') ?></label>
	<?php
	echo elgg_view('input/plaintext', array(
		'name' => 'description',
		'value' => elgg_extract('description', $vars, $entity->title),
	));
	?>
</div>
<div class="events-ui-row">
	<label><?php echo elgg_echo('tags') ?></label>
	<?php
	echo elgg_view('input/tags', array(
		'name' => 'tags',
		'value' => elgg_extract('tags', $vars, $entity->tags),
	));
	?>
</div>

<?php
// extension point for other plugins
echo elgg_view('calendar/edit/extend');
?>

<div class="events-ui-row">
	<label><?php echo elgg_echo('access') ?></label>
	<?php
	echo elgg_view('input/access', array(
		'entity' => $entity
	));
	?>
</div>

<div class="events-ui-row elgg-foot">
	<?php
	echo elgg_view('input/hidden', array(
		'name' => 'guid',
		'value' => $entity->guid
	));
	echo elgg_view('input/hidden', array(
		'name' => 'container_guid',
		'value' => $container->guid
	));
	echo elgg_view('input/submit', array(
		'value' => elgg_echo('save')
	));
	?>
</div>