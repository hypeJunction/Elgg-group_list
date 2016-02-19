<?php

$identifier = elgg_extract('identifier', $vars, 'groups');
$filter_context = elgg_extract('filter_context', $vars, elgg_extract('selected', $vars, 'newest'));

$vars['selected'] = $filter_context;

$old_filter = elgg_view('groups/group_sort_menu', $vars);
if ($old_filter) {
	echo $old_filter;
	return;
}

if ($filter_context == 'newest') {
	$filter_context = 'all';
}

$tabs = [
	'all' => "$identifier/all",
];

//if ($user) {
//	$tabs['member'] = "$identifier/member/$user->username";
//}
//
//if ($user && (elgg_get_plugin_setting('limited_groups', 'groups') != 'yes' || elgg_is_admin_logged_in())) {
//	$tabs['owner'] = "$identifier/owner/$user->username";
//}
//
//if ($user) {
//	$tabs['invitations'] = "$identifier/invitations/$user->username";
//}

$tabs['discussions'] = "$identifier/discussions";

foreach ($tabs as $tab => $url) {
	elgg_register_menu_item('filter', array(
		'name' => "$identifier:list:$tab",
		'text' => elgg_echo("$identifier:list:$tab"),
		'href' => elgg_normalize_url($url),
		'selected' => $tab == $filter_context,
	));
}

$vars['selected'] = $filter_context;

$params = $vars;
$params['sort_by'] = 'priority';
echo elgg_view_menu('filter', $params);