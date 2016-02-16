<?php

/**
 * Group Lists
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'group_list_init');

/**
 * Initialize the plugin
 * @return void
 */
function group_list_init() {

	elgg_extend_view('elgg.css', 'group/format/membership.css');
	elgg_extend_view('admin.css', 'group/format/membership.css');

	elgg_register_plugin_hook_handler('route', 'groups', 'group_list_router', 999);

	elgg_unregister_plugin_hook_handler('register', 'menu:entity', 'groups_entity_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'group_list_entity_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:membership', 'group_list_membership_group_menu_setup');

	elgg_unregister_event_handler('pagesetup', 'system', 'groups_setup_sidebar_menus');

	elgg_register_plugin_hook_handler('view_vars', 'group/elements/summary', 'group_list_filter_listing_subtitle');

	elgg_register_plugin_hook_handler('view', 'widgets/a_users_groups/content', 'group_list_users_groups_widget_access');
}

/**
 * Route groups pages
 *
 * @param string $hook   "route"
 * @param string $type   "groups"
 * @param array  $return Identifier and segments
 * @param array  $params Hook params
 * @return array
 */
function group_list_router($hook, $type, $return, $params) {

	if (!is_array($return)) {
		return;
	}

	// Initial page identifier might be different from /groups
	// i.e. subtype specific handler e.g. /schools
	$initial_identifier = elgg_extract('identifier', $params);
	$identifier = elgg_extract('identifier', $return);
	$segments = elgg_extract('segments', $return);

	if ($identifier !== 'groups') {
		return;
	}

	$page = array_shift($segments);
	if (!$page) {
		$page = 'all';
	}

	// we want to pass the original identifier to the resource view
	// doing this via route hook in order to keep the page handler intact
	$resource_params = array(
		'identifier' => $initial_identifier ? : 'groups',
		'segments' => $segments,
	);

	$user = elgg_get_logged_in_user_entity();
	$username = $user->username;
	switch ($page) {
		case 'invitations':
			$resource_params['rel'] = 'invited';
			$username = array_shift($segments);
			break;
		case 'member':
			$resource_params['rel'] = 'member';
			$username = array_shift($segments);
			break;
		case 'owner' :
			$resource_params['rel'] = 'admin';
			$username = array_shift($segments);
			break;
		case 'search' :
		case 'all' :
		case '' :
			break;
		case 'discussions' :
			echo elgg_view_resource('groups/discussions', $resource_params);
			return false;
		default:
			return;
	}

	$resource_params['page'] = $page;
	$resource_params['segments'] = $segments;
	if (!$username || $username == $user->username) {
		echo elgg_view_resource('groups/all', $resource_params);
	} else {
		$resource_params['username'] = $username;
		echo elgg_view_resource("groups/$page", $resource_params);
	}
	return false;
}

/**
 * Setup group/user membership menu
 *
 * @param string         $hook   "register"
 * @param string         $type   "menu:membership"
 * @param ElggMenuItem[] $return Menu
 * @param array          $params Hook params
 * @return ElggMenuItem[]
 */
function group_list_membership_group_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);
	if (!$entity instanceof ElggGroup) {
		return;
	}

	$items = group_list_get_profile_buttons($entity);
	return array_merge((array) $return, $items);
}

/**
 * Setup group entity menu
 *
 * @param string         $hook   "register"
 * @param string         $type   "menu:entity"
 * @param ElggMenuItem[] $return Menu
 * @param array          $params Hook params
 * @return ElggMenuItem[]
 */
function group_list_entity_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);
	if (!$entity instanceof ElggGroup) {
		return;
	}

	foreach ($return as $index => $item) {
		if (in_array($item->getName(), array('access', 'delete'))) {
			unset($return[$index]);
		}
	}

	return $return;
}

/**
 * Returns menu items to be registered in the title menu of the group profile
 *
 * @param ElggGroup $entity Group entity
 * @return ElggMenuItem[]
 */
function group_list_get_profile_buttons(ElggGroup $entity) {

	if (!elgg_is_logged_in()) {
		return [];
	}

	$identifier = is_callable('group_subtypes_get_identifier') ? group_subtypes_get_identifier($entity) : 'groups';

	$items = elgg_trigger_plugin_hook('profile_buttons', 'group', ['entity' => $entity], []);
	$unset = array(
		'groups:edit',
		'groups:invite',
		'groups:leave',
		'groups:join',
		'groups:joinrequest',
	);
	foreach ($items as $key => $item) {
		if (in_array($item->getName(), $unset)) {
			unset($items[$key]);
		}
	}

	if (elgg_is_admin_logged_in()) {
		$isFeatured = $entity->featured_group == "yes";
		$items[] = ElggMenuItem::factory(array(
					'name' => 'feature',
					'text' => elgg_echo("$identifier:makefeatured"),
					'href' => elgg_add_action_tokens_to_url("action/groups/featured?group_guid={$entity->guid}&action_type=feature"),
					'item_class' => $isFeatured ? 'hidden' : '',
		));

		$items[] = ElggMenuItem::factory(array(
					'name' => 'unfeature',
					'text' => elgg_echo("$identifier:makeunfeatured"),
					'href' => elgg_add_action_tokens_to_url("action/groups/featured?group_guid={$entity->guid}&action_type=unfeature"),
					'item_class' => $isFeatured ? '' : 'hidden',
		));
	}

	$actions = [];

	$user = elgg_get_logged_in_user_entity();
	if ($user && $entity->isMember($user)) {
		if ($entity->owner_guid != $user->guid) {
			// a member can leave a group if he/she doesn't own it
			$actions["$identifier:leave"] = "action/groups/leave?group_guid={$entity->guid}";
		}
	} else if ($user && check_entity_relationship($entity->guid, 'invited', $user->guid)) {
		$actions["$identifier:invitation:accept"] = elgg_http_add_url_query_elements('action/groups/join', array(
			'user_guid' => $user->guid,
			'group_guid' => $entity->guid,
		));
		$actions["$identifier:invitation:decline"] = elgg_http_add_url_query_elements('action/groups/killinvitation', array(
			'user_guid' => $user->guid,
			'group_guid' => $entity->guid,
		));
	} else if ($user && check_entity_relationship($user->guid, 'membership_request', $entity->guid)) {
		$actions["$identifier:killrequest"] = elgg_http_add_url_query_elements('action/groups/killrequest', array(
			'user_guid' => $user->guid,
			'group_guid' => $entity->guid,
		));
	} else if ($user) {
		$url = "action/groups/join?group_guid={$entity->guid}";
		if ($entity->isPublicMembership() || $entity->canEdit()) {
			// admins can always join
			// non-admins can join if membership is public
			$actions["$identifier:join"] = $url;
		} else {
			// request membership
			$actions["$identifier:joinrequest"] = $url;
		}
	}

	if ($entity->canEdit() || ($entity->isMember() && $entity->invites_enable == 'yes')) {
		$actions["$identifier:invite"] = "$identifier/invite/{$entity->guid}";
	}

	if ($entity->canEdit()) {
		$actions["$identifier:edit"] = "$identifier/edit/{$entity->guid}";
		if (!$entity->isPublicMembership()) {
			$count = elgg_get_entities_from_relationship(array(
				'type' => 'user',
				'relationship' => 'membership_request',
				'relationship_guid' => $entity->guid,
				'inverse_relationship' => true,
				'count' => true,
			));

			if ($count) {
				$text = elgg_echo("$identifier:membershiprequests:pending", array($count));
				$items[] = ElggMenuItem::factory(array(
							'name' => 'membership_requests',
							'text' => $text,
							'href' => "$identifier/requests/{$entity->guid}",
				));
			}
		}
	}

	foreach ($actions as $action => $url) {
		$items[] = ElggMenuItem::factory(array(
					'name' => $action,
					'href' => elgg_normalize_url($url),
					'text' => elgg_echo($action),
					'is_action' => 0 === strpos($url, 'action'),
					'confirm' => in_array($action, array(
						"$identifier:leave",
						"$identifier:invitation:decline",
						"$identifier:killrequest"
					)),
		));
	}

	foreach ($items as &$item) {
		if (!$item instanceof ElggMenuItem) {
			continue;
		}
		$link_class = $item->getLinkClass();
		$link_class = trim(preg_replace("/(elgg-button-action|elgg-button-submit|elgg-button|mlm)/", '', $link_class));
		$item->setLinkClass($link_class);
	}

	return $items;
}

/**
 * Returns title buttons to be registered on group pages
 *
 * @param ElggGroup $entity     Group entity
 * @param string    $identifier Page identifier
 * @param string    $menu       Menu name
 * @return void
 */
function group_list_register_title_buttons(ElggGroup $entity = null, $identifier = 'groups', $menu = 'title') {
	$buttons = group_list_get_title_buttons($entity, $identifier);
	foreach ($buttons as $button) {
		elgg_register_menu_item($menu, $button);
	}
}

/**
 * Returns title buttons to be registered on group pages
 *
 * @param ElggGroup $entity     Group entity
 * @param string    $identifier Page identifier
 * @return ElggMenuItem[]
 */
function group_list_get_title_buttons(ElggGroup $entity = null, $identifier = 'groups') {

	$buttons = array();
	if ($entity) {
		$buttons = group_list_get_profile_buttons($entity);
		foreach ($buttons as &$button) {
			$button->addClass('elgg-button elgg-button-action');
		}
	} else {
		if (elgg_get_plugin_setting('limited_groups', 'groups') != 'yes' || elgg_is_admin_logged_in()) {
			$page_owner = elgg_get_page_owner_entity();
			if (!$page_owner) {
				$page_owner = elgg_get_logged_in_user_entity();
			}
			$subtypes = get_registered_entity_types('group');
			if (empty($subtypes)) {
				$subtypes = array(ELGG_ENTITIES_ANY_VALUE);
			}
			foreach ($subtypes as $subtype) {
				if ($page_owner && $page_owner->canWriteToContainer(0, 'group', $subtype)) {
					// can write to container ignores hierarchy logic
					$params = array(
						'parent' => $page_owner,
						'type' => 'group',
						'subtype' => $subtype,
					);
					$can_contain = elgg_trigger_plugin_hook('permissions_check:parent', 'group', $params, true);
					if ($can_contain) {
						$buttons[] = ElggMenuItem::factory(array(
									'name' => "$subtype:add",
									'text' => elgg_echo("groups:add:$subtype"),
									'href' => "{$identifier}/add/{$page_owner->guid}/{$subtype}",
									'link_class' => 'elgg-button elgg-button-action',
						));
					}
				}
			}
		}
	}

	$params = array(
		'entity' => $entity,
	);
	return elgg_trigger_plugin_hook('title_buttons', $identifier, $params, $buttons);
}

/**
 * Filters listing subtitle
 * 
 * @param string $hook   "view_vars"
 * @param string $type   "group/elements/summary"
 * @param array  $return View vars
 * @param array  $params Hook params
 * @return array
 */
function group_list_filter_listing_subtitle($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $return);
	if (!$entity instanceof ElggGroup) {
		return;
	}

	$subtitle = array();
	if ($entity->isPublicMembership()) {
		$mem = elgg_echo('group:membership:open');
		$mem_class = 'group-membership-open';
	} else {
		$mem = elgg_echo('group:membership:closed');
		$mem_class = 'group-membership-closed';
	}

	$subtitle['public_membership'] = elgg_format_element('b', ['class' => $mem_class], $mem);

	if ($entity->featured_group == 'yes') {
		$subtitle['featured'] = elgg_format_element('b', ['class' => 'group-featured'], elgg_echo('group:featured'));
	}

	if (elgg_group_gatekeeper(false, $entity->guid)) {
		$member_count = $entity->getVolatileData('select:member_count');
		if (!isset($member_count)) {
			$member_count = $entity->getMembers(array('count' => true));
		}

		if ($member_count == 1) {
			$subtitle['member_count'] = elgg_echo('group:member:count:single', [$member_count]);
		} else {
			$subtitle['member_count'] = elgg_echo('group:member:count', [$member_count]);
		}
	}

	//$subtitle['time_created'] = elgg_echo('group:time_created', [date('j M, Y', $entity->time_created)]);

	if ($entity->last_action) {
		$subtitle['last_action'] = elgg_echo('group:last_activity', [elgg_get_friendly_time($entity->last_action)]);
	}

	$subtitle_str = '';
	foreach ($subtitle as $s) {
		$subtitle_str .= elgg_format_element('span', ['class' => 'elgg-group-subtitle-element'], $s);
	}

	$view_subtitle = elgg_extract('subtitle', $return);
	if ($view_subtitle) {
		$view_subtitle = $subtitle_str . '<br />' . $view_subtitle;
	} else {
		$view_subtitle = $subtitle_str;
	}

	$return['subtitle'] = $view_subtitle;
	return $return;
}

/**
 * Determines if $viewer has access to $user's group membership list
 *
 * @param ElggUser $user   User whose groups are to be displayed
 * @param ElggUser $viewer Viewer
 * @return bool
 */
function group_list_can_view_membership(ElggUser $user, ElggUser $viewer = null) {

	if (!isset($viewer)) {
		$viewer = elgg_get_logged_in_user_entity();
	}

	$permission = false;

	if ($viewer && elgg_check_access_overrides($viewer->guid)) {
		$permission = true;
	}

	$setting = elgg_get_plugin_user_setting('group_membership_visibility', $user->guid, 'group_list');
	if (!isset($setting)) {
		$setting = elgg_get_plugin_setting('group_membership_visibility', 'group_list', ACCESS_PUBLIC);
	}

	switch ((int) $setting) {
		case ACCESS_PRIVATE :
			$permission = $viewer && $user->canEdit($viewer->guid);
			break;

		case ACCESS_FRIENDS:
			$permission = $viewer && $user->isFriendsWith($viewer->guid);
			break;

		case ACCESS_LOGGED_IN :
			$permission = ($viewer);
			break;

		case ACCESS_PUBLIC :
			$permission = true;
			break;
	}

	$params = array(
		'viewer' => $viewer,
		'user' => $user,
	);

	return elgg_trigger_plugin_hook('permissions_check:view_group_membership', 'user', $params, $permission);
}

/**
 * Prevents the widget from showing users groups if membership visibility criteria is not met
 *
 * @param string $hook   "view"
 * @param string $type   "widgets/a_users_groups/content"
 * @param string $return View
 * @param array  $params Hook params
 * @return string
 */
function group_list_users_groups_widget_access($hook, $type, $return, $params) {

	$vars = elgg_extract('vars', $params);
	$entity = elgg_extract('entity', $vars);
	if (!$entity instanceof ElggWidget) {
		return;
	}

	$owner = $entity->getOwnerEntity();
	if (!group_list_can_view_membership($owner)) {
		return elgg_format_element('p', ['class' => 'elgg-no-results'], elgg_echo('user:groups:no_access'));
	}
}
