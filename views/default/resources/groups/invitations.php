<?php

elgg_gatekeeper();

$identifier = elgg_extract('identifier', $vars, 'groups');

$username = elgg_extract('username', $vars);
if ($username) {
	$user = get_user_by_username($username);
	elgg_set_page_owner_guid($user->guid);
} else {
	$user = elgg_get_logged_in_user_entity();
	elgg_set_page_owner_guid($user->guid);
}

if (!$user || !$user->canEdit()) {
	register_error(elgg_echo('noaccess'));
	forward('');
}

$title = elgg_echo("$identifier:invitations");

elgg_push_breadcrumb(elgg_echo($identifier), "$identifier/all");
if ($user->guid !== elgg_get_logged_in_user_guid()) {
	elgg_push_breadcumb($user->name, $user->getURL());
}
elgg_push_breadcrumb($title);

$content = elgg_view('groups/invitationrequests', array(
	'user' => $user,
));

$params = array(
	'content' => $content,
	'title' => $title,
	'filter' => '',
);
$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);