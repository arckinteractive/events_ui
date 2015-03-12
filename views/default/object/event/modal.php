<?php

namespace Events\UI;

$entity = elgg_extract('entity',$vars);

elgg_push_context('widgets');
echo elgg_view_entity($entity, $vars);
elgg_pop_context();

echo '<div class="clearfix mtl mbl">';
echo elgg_view_menu('title', array(
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz float',
));
echo '</div>';