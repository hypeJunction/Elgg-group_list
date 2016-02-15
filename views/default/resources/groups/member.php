<?php

$identifier = elgg_extract('identifier', $vars, 'groups');

$username = elgg_extract('username', $vars);
if ($username) {
	$user = get_user_by_username($username);
	elgg_set_page_owner_guid($user->guid);
} else {
	$user = elgg_get_logged_in_user_entity();
	elgg_set_page_owner_guid($user->guid);
}

if (!$user) {
	forward('', '404');
}

if (!group_list_can_view_membership($user)) {
	register_error(elgg_echo('user:groups:no_access'));
	forward($identifier);
}

elgg_push_breadcrumb(elgg_echo($identifier), "$identifier/all");
if ($user->guid == elgg_get_logged_in_user_guid()) {
	$title = elgg_echo("$identifier:yours");
} else {
	$title = elgg_echo("$identifier:user", array($user->name));
	elgg_push_breadcumb($user->name, $user->getURL());
}
elgg_push_breadcrumb($title);

$dbprefix = elgg_get_config('dbprefix');

$content = elgg_view('lists/groups/all', array(
	'rel' => 'member',
	'show_rel' => false,
	'user' => $user,
));

$params = array(
	'content' => $content,
	'title' => $title,
	'filter' => '',
);
$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);