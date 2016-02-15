<?php

$identifier = elgg_extract('identifier', $vars, 'groups');

// pushing context to make it easier to user 'menu:filter' hook
elgg_push_context("$identifier/all");

$segments = (array) elgg_extract('segments', $vars, array());

$title = elgg_echo("$identifier:list:discussions");

elgg_push_breadcrumb(elgg_echo($identifier), "$identifier/all");

$params = array(
	'identifier' => $identifier,
	'filter_context' => 'discussions',
);

$sidebar = elgg_view('groups/sidebar/featured', $params);

$filter = elgg_view('filters/groups', $params);

$content = elgg_view('lists/groups/discussions', $params);

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => $sidebar,
		));

echo elgg_view_page($title, $layout);

