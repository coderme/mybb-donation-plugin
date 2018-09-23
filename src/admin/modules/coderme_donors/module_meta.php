<?php

/**
 *
 * CoderMe Donation FREE
 * Copyright 2018 CoderMe.com, All Rights Reserved
 *
 * Website: https://markit.coderme.com
 * Home:    https://red.coderme.com/mybb-donation-plugin
 * License: https://red.coderme.com/mybb-donation-plugin#license
 * Version: 6.0.0
 * GOLD VERSION: https://markit.coderme.com/mybb-donation-gold
 *
 **/





# Disallow direct access to this file for security reasons
defined("IN_MYBB") or
	exit("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");


/*	
  # check if naoardonate plugin installed?
	if(!array_key_exists('naoardonate_newgoal', $mybb->settings)):
		require_once  MYBB_ROOT . $mybb->config['admin_dir'] . "/inc/functions.php";
		change_admin_permission($tab, $page="", $default=1);
	endif;

*/
# load my global language phrases :)
$lang->load('naoardonate_global');
$lang->load('naoardonate_module_meta');

# support Mybb 1.4 as well
sprintf('%.1f', $mybb->version) == 1.4 ? $sep = '/' : $sep = '-';

function coderme_donors_meta(){
    global $page, $lang, $sep, $plugins;


	$sub_menu['10'] = array("id" => "browse", "title" => $lang->naoardonate_global_browse, "link" => "index.php?module=coderme_donors{$sep}browse");
	$sub_menu['20'] = array("id" => "stats", "title" => $lang->naoardonate_global_stats, "link" => "index.php?module=coderme_donors{$sep}stats");
	$sub_menu['30'] = array("id" => "donate", "title" => 'By CoderMe.com', "link" => 'https://markit.coderme.com?src=mybba');
	$sub_menu = $plugins->run_hooks("admin_config_menu", $sub_menu);
	$page->add_menu_item($lang->naoardonate_meta_donors, "coderme_donors", "index.php?module=coderme_donors", 60, $sub_menu);
	return true;

}

function coderme_donors_action_handler($action){
	global $page;

	$page->active_module = "coderme_donors";

	$actions = array(
		'browse' => array('active' => 'browse', 'file' => 'browse.php'),
		'stats' => array('active' => 'stats', 'file' => 'stats.php')
	);


	if(isset($actions[$action]))
	{
		$page->active_action = $actions[$action]['active'];
		return $actions[$action]['file'];
	}
	else
	{
		$page->active_action = "browse";
		return "browse.php";
	}
}

function coderme_donors_admin_permissions(){
	global $lang, $plugins;

	$admin_permissions = array(
		"browse" => $lang->naoardonate_meta_can_manage_donors,
		"stats" => $lang->naoardonate_meta_can_view_donations_stats
	);

	$admin_permissions = $plugins->run_hooks("admin_config_permissions", $admin_permissions);
	return array("name" => $lang->naoardonate_meta_donors, "permissions" => $admin_permissions, "disporder" => 60);
}

