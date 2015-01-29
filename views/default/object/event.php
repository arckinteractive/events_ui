<?php


$full = elgg_extract('full_view', $vars, FALSE);
$event = elgg_extract('entity', $vars, FALSE);

if (!$event) {
	return TRUE;
}

$owner = $event->getOwnerEntity();
$calendar = $event->getContainerEntity();
$categories = elgg_view('output/categories', $vars);
$excerpt = $event->excerpt;
if (!$excerpt) {
	$excerpt = elgg_get_excerpt($event->description);
}

$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array(
	'href' => "blog/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));
$date = elgg_view_friendly_time($event->time_created);

// The "on" status changes for comments, so best to check for !Off
if ($event->comments_on != 'Off') {
	$comments_count = $event->countComments();
	//only display if there are commments
	if ($comments_count != 0) {
		$text = elgg_echo("comments") . " ($comments_count)";
		$comments_link = elgg_view('output/url', array(
			'href' => $event->getURL() . '#blog-comments',
			'text' => $text,
			'is_trusted' => true,
		));
	} else {
		$comments_link = '';
	}
} else {
	$comments_link = '';
}

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$subtitle = "$author_text $date $comments_link $categories";

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}

if ($full) {

	$body = elgg_view('output/longtext', array(
		'value' => $event->description,
		'class' => 'blog-post',
	));

	$params = array(
		'entity' => $event,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);

	echo elgg_view('object/elements/full', array(
		'summary' => $summary,
		'icon' => $owner_icon,
		'body' => $body,
	));

} else {
	// brief view

	$params = array(
		'entity' => $event,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'content' => $excerpt,
	);
	$params = $params + $vars;
	$list_body = elgg_view('object/elements/summary', $params);

	echo elgg_view_image_block($owner_icon, $list_body);
}
