<?php
/**
 * Blog river view.
 */

$object = $vars['item']->getObjectEntity();

$excerpt = strip_tags($object->description);
$excerpt = elgg_get_excerpt($excerpt);

/**
 * @todo: Add next occurence info
 */

echo elgg_view('river/elements/layout', array(
	'item' => $vars['item'],
	'message' => $excerpt,
));
