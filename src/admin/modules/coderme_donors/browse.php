<?php

/**
 *
 * CoderMe Donation FREE
 * Copyright (c) CoderMe.com, All Rights Reserved
 *
 * Website: https://coderme.com
 * Home:    https://coderme.com/mybb-donation-plugin
 * License: https://coderme.com/mybb-donation-plugin#license
 * Version: 6.0.0
 *
 **/




# Disallow direct access to this file for security reasons
defined("IN_MYBB") or 
	exit("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");


# support Mybb 1.4 as well
sprintf('%.1f', $mybb->version) == 1.4 ? $sep = '/' : $sep = '-';
$lang->load('naoardonate_browse');
$lang->load('naoardonate_global');


$page->add_breadcrumb_item($lang->naoardonate_global_browse, "index.php?module=coderme_donors{$sep}browse");

$page->extra_header=<<<CODERME_HEADER
<style type="text/css">

 .trow_selected td {
	background: #FFF59C;
}
.no{
background-color: #FFABBA;
}

.naoar_info div {
float:left;
width:20px
}
    
div.naoardonate_note {
background: khaki;
border: 1px solid black;
width: auto;
padding: 7px;
}

</style>
<script type="text/javascript" src="./../jscripts/inline_moderation.js?ver=1800"></script>
CODERME_HEADER;


$table =<<<TABLE_HEAD
       <div class="border_wrapper">
       <div class="title">$lang->naoardonate_global_donations</div>
		<table cellspacing="0" class="general">
       <thead>
        <tr>
		<th class="align_center">$lang->naoardonate_global_name</th>
		<th class="align_center">$lang->naoardonate_global_amount</th>
		<th class="align_center">$lang->naoardonate_global_payment_method</th>
		<th class="align_center">$lang->naoardonate_global_ip</th>
       	<th class="align_center">$lang->naoardonate_global_invoice_mtcn</th>       
		<th class="align_center">$lang->naoardonate_global_extra</th>
		<th class="align_center">$lang->naoardonate_global_date</th>
	    <th class="align_center"><input type="checkbox" name="allbox" onclick="inlineModeration.checkAll(this);" /></th>
	    </tr>
       </thead>
       <tbody>
TABLE_HEAD;



$sub_tabs['unconfirmed'] = array(
	'title' => $lang->naoardonate_browse_unconfirmed,
	'link' => "index.php?module=coderme_donors{$sep}browse&amp;action=unconfirmed",
	'description' => $lang->naoardonate_browse_unconfirmed_desc
);

$sub_tabs['confirmed'] = array(
	'title' => $lang->naoardonate_browse_confirmed,
	'link' => "index.php?module=coderme_donors{$sep}browse&amp;action=confirmed",
	'description' => $lang->naoardonate_browse_confirmed_desc
);

$sub_tabs['bannedunconfirmed'] = array(
	'title' => $lang->naoardonate_browse_banned_unconfirmed,
	'link' => "index.php?module=coderme_donors{$sep}browse&amp;action=bannedunconfirmed",
	'description' => $lang->naoardonate_browse_unconfirmed_desc
);

$sub_tabs['bannedconfirmed'] = array(
	'title' => $lang->naoardonate_browse_bannedconfirmed_confirmed,
	'link' => "index.php?module=coderme_donors{$sep}browse&amp;action=bannedconfirmed",
	'description' => $lang->naoardonate_browse_bannedconfirmed_desc
);

$sub_tabs['all'] = array(
	'title' => $lang->naoardonate_browse_all,
	'link' => "index.php?module=coderme_donors{$sep}browse",
	'description' => $lang->naoardonate_browse_all_desc
);

if($mybb->request_method == 'post'){


	# Verify incoming POST request
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect($mybb->input['naoar_referrer']);
	}

	$dids = explode("|", $mybb->cookies[$mybb->input['naoar_cookie']]);
	foreach($dids as $did)
	{
		if($did != '')
		{
			$selected[] = intval($did);
		}
	}

	# If there isn't anything to select, then output an error
	if(empty($selected))
	{
		flash_message($lang->naoardonate_browse_inline_nodonors_selected, 'error');
		admin_redirect($mybb->input['naoar_referrer']);

	}
	else
	{
	    # Get members selected
	    $sql_array = implode(",", $selected);
	    if($mybb->settings['naoardonate_unmovable'])
	    {
	        $condition = "did IN ($sql_array) AND uid != 0 AND ogid NOT IN (" . $mybb->settings['naoardonate_unmovable'] . ')';
	    }
	    else
	    {
	        $condition = "did IN ($sql_array) AND uid != 0";
	    }

	    $members_selected = array();
	    if($query = $db->simple_select('naoardonate', 'uid,ogid', $condition))
	    {
	        while($member = $db->fetch_array($query)){
	            $members_selected[(int)$member['uid']] = (int)$member['ogid'];
	        }
	    }
	}

	switch($mybb->input['inline_action'])
	{
		case 'multiunconfirm':


		$db->write_query("UPDATE ".TABLE_PREFIX."naoardonate SET confirmed = '0' WHERE did IN ($sql_array)");

		# Revert each member to her/his original group :)
		if($members_selected and $mybb->settings['naoardonate_donorsgroup'] != 'nochange')
		{
			foreach($members_selected as $k => $v)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET usergroup = $v WHERE uid = $k");

			}

			$lang->naoardonate_browse_inline_unconfirmed = $lang->sprintf($lang->naoardonate_browse_inline_unconfirmedmove, my_number_format(count($selected)), my_number_format(count($members_selected)));
		}
		else
		{
			$lang->naoardonate_browse_inline_unconfirmed = $lang->sprintf($lang->naoardonate_browse_inline_unconfirmed, my_number_format(count($selected)));
		}

		# Calculate donations
		$total = cal_target();

		# Update cache
		$cache->update('naoardonate_goal',$total);

		# Count Unconfirmed donations if you want
		count_unconfirmed();

		# Action complete, grab stats and show success message - redirect user
		log_admin_action($lang->naoardonate_browse_inline_unconfirmed); # Add to adminlog
		my_unsetcookie($mybb->input['naoar_cookie']); # Unset the cookie, so that the users aren't still selected when we're redirected

		flash_message($lang->naoardonate_browse_inline_unconfirmed, 'success');
		admin_redirect($mybb->input['naoar_referrer']);


		break;

		case 'multidelete':


		$db->delete_query("naoardonate", "did IN ($sql_array)");

		# Revert each member to her/his original group :)
		if($members_selected and $mybb->settings['naoardonate_donorsgroup'] != 'nochange')
		{
			foreach($members_selected as $k => $v)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET usergroup = $v WHERE uid = $k");
			}

			$lang->naoardonate_browse_inline_deleted = $lang->sprintf($lang->naoardonate_browse_inline_deletedmove, my_number_format(count($selected)), my_number_format(count($members_selected)));
		}
		else
		{
			$lang->naoardonate_browse_inline_deleted = $lang->sprintf($lang->naoardonate_browse_inline_deleted, my_number_format(count($selected)));

		}

		# Calculate donations
		$total = cal_target();

		# Count Unconfirmed donations if you want
		count_unconfirmed();

		# Update cache
		$cache->update('naoardonate_goal',$total);

		# Action complete, grab stats and show success message - redirect user
		log_admin_action($lang->naoardonate_browse_inline_deleted);
		my_unsetcookie($mybb->input['naoar_cookie']);

		flash_message($lang->naoardonate_browse_inline_deleted, 'success');
		admin_redirect($mybb->input['naoar_referrer']);


		case 'multiconfirm':

		$db->write_query("UPDATE ".TABLE_PREFIX."naoardonate SET confirmed = '1' WHERE did IN ($sql_array)");

		# Move each member to donors group ONLY if this is the admin's wish :)
		if($members_selected and $mybb->settings['naoardonate_donorsgroup'] != 'nochange')
		{
			$members_selected_sql = implode(',',array_keys($members_selected));

			$db->write_query("UPDATE ".TABLE_PREFIX."users SET usergroup = " . (int)$mybb->settings['naoardonate_donorsgroup'] . " WHERE uid IN ($members_selected_sql)");

			$lang->naoardonate_browse_inline_confirmed = $lang->sprintf($lang->naoardonate_browse_inline_confirmedmove, my_number_format(count($selected)), my_number_format(count($members_selected)));
		}
