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
	'rel' => elgg_extract('rel', $vars),
);

$filtered = array_shift($segments);
switch ($filtered) {
	case 'newest' :
		$params['show_sort'] = false;
		$params['sort'] = 'time_created::desc';
		break;
	case 'yours' :
		$params['show_rel'] = false; // do not show relationship filter
		$params['rel'] = 'member';
		break;
	case 'alpha' :
		$params['show_sort'] = false;
		$params['sort'] = 'alpha::asc';
		break;
	case 'popular' :
		$params['show_sort'] = false;
		$params['sort'] = 'member_count::desc';
		break;
	case 'discussion' :
		echo elgg_view('resources/groups/discussions', $vars);
		return;
	case 'open' :
		$params['show_rel'] = false; // do not show relationship filter
		$params['rel'] = 'open';
		break;
	case 'closed' :
		$params['show_rel'] = false; // do not show relationship filter
		$params['rel'] = 'closed';
		break;
	default :
		if (elgg_view_exists("resources/groups/all/$filtered")) {
			echo elgg_view("resources/groups/all/$filtered", $vars);
			return;
		}
		break;
}

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

