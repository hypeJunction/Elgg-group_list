<?php

$identifier = elgg_extract('identifier', $vars, 'groups');

// pushing context to make it easier to user 'menu:filter' hook
elgg_push_context("$identifier/all");

$segments = (array) elgg_extract('segments', $vars, array());

group_list_register_title_buttons(null, $identifier);

$page = elgg_extract('page', $vars, 'all');
$title = elgg_echo("$identifier:list:$page");

elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo($identifier), "$identifier/all");
if ($page != 'all') {
	elgg_push_breadcrumb($title);
}

$params = array(
	'identifier' => $identifier,
	'filter_context' => 'all',
	'rel' => elgg_extract('rel', $vars)
);

$sidebar = elgg_view('groups/sidebar/featured', $params);

$filter = elgg_view('filters/groups', $params);

$content = elgg_view('lists/groups/all', $params);

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => $sidebar,
		));

echo elgg_view_page($title, $layout);

