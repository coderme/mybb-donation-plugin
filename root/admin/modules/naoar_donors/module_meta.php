<?php

#  Naoar Donation v2 for mybb 1.4x, 1.6x
#  This file will be used for handle 'naoar_donors' module information
#  Copyright(c) 2015  """ https://coderme.com """
#
#  This is a free software, you can redistribute it freely provided that you keep my credits, files of this module and this notice unchanged.
#
#  This module released UNDER THE TERMS OF CREATIVE COMMONS - Attribution No Derivatives("cc by-nd"). THIS MODULE IS PROTECTED BY COPYRIGHT AND/OR OTHER APPLICABLE LAW. ANY USE OF THIS MODULE OTHER THAN AS AUTHORIZED UNDER THIS LICENSE OR COPYRIGHT LAW IS PROHIBITED
#  For Details visit: http://creativecommons.org/licenses/by-nd/3.0/legalcode  or http://creativecommons.org/licenses/by-nd/3.0/



# Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	exit("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*	# check if naoardonate plugin installed?
	if(!array_key_exists('naoardonate_newgoal', $mybb->settings)):
		require_once  MYBB_ROOT . "/" . $mybb->config['admin_dir'] . "/inc/functions.php";
		change_admin_permission($tab, $page="", $default=1);
	endif;
	*/
	# load my global language phrases :)
	$lang->load('naoardonate_global');
	$lang->load('naoardonate_module_meta');

	# support Mybb 1.4 as well
	sprintf('%.1f', $mybb->version) == 1.4 ? $sep = '/' : $sep = '-';

function naoar_donors_meta()
{
    global $page, $lang, $sep, $plugins;


	$sub_menu['10'] = array("id" => "browse", "title" => $lang->naoardonate_global_browse, "link" => "index.php?module=naoar_donors{$sep}browse");
	$sub_menu['20'] = array("id" => "stats", "title" => $lang->naoardonate_global_stats, "link" => "index.php?module=naoar_donors{$sep}stats");
	$sub_menu['30'] = array("id" => "donate", "title" => 'Naoar Donation Plugin is free plugin please donate<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=TASTPZE7LQ7HS&lc=GB&item_name=Support&button_subtype=services&no_note=1&no_shipping=1&rm=1&return=http%3a%2f%2fnaoar%2ecom&cancel_return=http%3a%2f%2fnaoar%2ecom&currency_code=USD&bn=PP%2dBuyNowBF%3abtn_donateCC_LG%2egif%3aNonHosted" title="new page" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" /></a>');
	$sub_menu = $plugins->run_hooks("admin_config_menu", $sub_menu);
	$page->add_menu_item($lang->naoardonate_meta_donors, "naoar_donors", "index.php?module=naoar_donors", 60, $sub_menu);
	return true;

}

function naoar_donors_action_handler($action)
{
	global $page;

	$page->active_module = "naoar_donors";

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

function naoar_donors_admin_permissions()
{
	global $lang, $plugins;

	$admin_permissions = array(
		"browse" => $lang->naoardonate_meta_can_manage_donors,
		"stats" => $lang->naoardonate_meta_can_view_donations_stats
	);

	$admin_permissions = $plugins->run_hooks("admin_config_permissions", $admin_permissions);
	return array("name" => $lang->naoardonate_meta_donors, "permissions" => $admin_permissions, "disporder" => 60);
}
?>