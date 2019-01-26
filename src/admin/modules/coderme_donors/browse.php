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
		else
		{
			$lang->naoardonate_browse_inline_confirmed = $lang->sprintf($lang->naoardonate_browse_inline_confirmed, my_number_format(count($selected)));
		}

		# Calculate donations
		$total = cal_target();

		# Update cache
		$cache->update('naoardonate_goal',$total);

		# Count Unconfirmed donations if you want
		count_unconfirmed();

		# Action complete, grab stats and show success message - redirect user
		log_admin_action($lang->naoardonate_browse_inline_confirmed);
		my_unsetcookie($mybb->input['naoar_cookie']);

		flash_message($lang->naoardonate_browse_inline_confirmed, 'success');
		admin_redirect($mybb->input['naoar_referrer']);

	}

}


$groups = $cache->read('usergroups');

if( ! in_array( $mybb->input['action'], array('confirmed', 'unconfirmed'))){

	$page->output_header($lang->naoardonate_global_browse);
	$page->output_nav_tabs($sub_tabs, 'all');

	$inlinecount = 0;
	# $mybb->input['naoar_cookie']
	$inlinecookie = "inlinemod_donor1";

	$query = $db->simple_select('naoardonate', '*', "",array('order_by'=> 'real_amount', 'order_dir'=>'DESC'));

	while($donor = $db->fetch_array($query))
	{

		if(my_strpos($mybb->cookies[$inlinecookie], "|$donor[did]|"))
		{
			$inlinecheck = 'checked="checked"';
				          ++$inlinecount;
		}
		else
		{
			$inlinecheck = '';
		}
		if($donor['uid']) {

		    $donor['name'] = "<a href=\"index.php?module=user{$sep}users&amp;action=edit&amp;uid=$donor[uid]\" target=\"_blank\">$donor[name]</a>";
		    $donor['ogid'] = '<img src="./../images/naoar/group.gif" alt="" title="' .$lang->naoardonate_browse_ogid .  $groups[$donor['ogid']]['title'] . '" />';
		} else {

		    $donor['name'] = $lang->naoardonate_global_guest;
		    $donor['ogid'] = '';
		}

		if($donor['confirmed']){

		    $class = '';
		    $confirmed = '<img src="./../images/naoar/tick.gif" alt="" title="' . $lang->naoardonate_global_you_confirmed . '" />';

		}else {
		    $class = 'class="no"';
		    $confirmed = '&nbsp;';
		}
		if($donor['note']){
		    $note ="<div style=\"display:none\" id=\"note_$donor[did]_popup\" class=\"modal\">
					<div class=\"naoardonate_note\">" . wordwrap($donor['note'],30,'<br />',true) . "</div>
				</div>
			<a href=\"#0\" onclick=\"$('#note_$donor[did]_popup').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== 'undefined' ? modal_zindex : 9999) }); return false;\" id=\"note_$donor[did]\"><img src=\"./../images/naoar/note.gif\" title=\"" . $lang->naoardonate_global_note_recieved . "\" style=\"border:0\" /></a>
";
		} else {

		    $note ='&nbsp;';

		}
		if($donor['email']){
		    $email = "<a href=\"mailto:$donor[email]\" title=\"" . $lang->naoardonate_global_email_donor . "\" ><img src=\"./../images/naoar/email.gif\" style=\"border:0\" /></a>";
		} else {
		    $email ='&nbsp;';

		}


		$donor['dateline']= my_date($mybb->settings['dateformat'], $donor['dateline']).", ".my_date($mybb->settings['timeformat'], $donor['dateline']);
		$table .= <<<TABLE_BODY
		<tr id="donor1_$donor[did]">
				<td class="align_center">$donor[name]</td>
				<td class="align_center">$donor[real_amount] $donor[currency]</td>
				<td class="align_center">$donor[payment_method]</td>
				<td class="align_center">$donor[ip]</td>
                <td class="align_center">$donor[invoice_id]</td>
				<td class="align_center"><div class="naoar_info"><div>$confirmed</div><div>$email</div><div>$donor[ogid]</div><div>$note</div></div></td>
				<td class="align_center">$donor[dateline]</td>
				<td $class align="center" style="white-space: nowrap"><input type="checkbox" class="checkbox" name="inlinemod_$donor[did]" id="inlinemod_$donor[did]" value="1" /></td>
		</tr>
TABLE_BODY;
	}
	if(strpos($table,'<td class="align_center">') !== false){
	    $table .= "</tbody></table></div>";
	    print $table;
	    print <<<CODERME_INLINE

<div style="float:right"><form action="index.php?module=coderme_donors{$sep}browse" method="post">
<input type="hidden" name="my_post_key" value="$mybb->post_code" />
<input type="hidden" name="naoar_cookie" value="inlinemod_donor1" />
<input type="hidden" name="naoar_referrer" value="index.php?module=coderme_donors{$sep}browse" />
<span class="smalltext"><strong>$lang->naoardonate_browse_inline_moderation</strong></span>
<select name="inline_action">
		<option value="multidelete" >$lang->naoardonate_browse_delete</option>
		<option value="multiunconfirm">$lang->naoardonate_browse_unconfirm</option>
		<option value="multiconfirm" selected="selected" >$lang->naoardonate_browse_confirm</option>

</select>
<input type="submit" class="button" name="go" value="$lang->naoardonate_global_go ($inlinecount)" id="inline_go" />&nbsp;
<input type="button" onclick="javascript:inlineModeration.clearChecked();" value="$lang->naoardonate_browse_clear" class="button" />
</form></div>
<script type="text/javascript">
<!--
	var go_text = "$lang->naoardonate_global_go";
	var all_text = "1";
	var inlineType = "donor";
	var inlineId = "1";
// -->
</script>

CODERME_INLINE;

    } else {
	    $table .= '<tr><td colspan="7" class="align_center">' . $lang->naoardonate_global_nothing . '</td></tr>
</tbody></table></div>';

	    print $table;

    }
	$page->output_footer();

} elseif ($mybb->input['action'] == 'unconfirmed'){

	$page->output_header($lang->naoardonate_browse_unconfirmed);
	$page->output_nav_tabs($sub_tabs, 'unconfirmed');

	$inlinecount = 0;
	$inlinecookie = "inlinemod_donor2";
	# $mybb->input['naoar_cookie']
	$query = $db->simple_select('naoardonate', '*', "confirmed = 0",array('order_by'=> 'real_amount', 'order_dir'=>'DESC'));



	while($donor = $db->fetch_array($query))
	{

		if(my_strpos($mybb->cookies[$inlinecookie], "|$donor[did]|"))
		{
			$inlinecheck = 'checked="checked"';
				          ++$inlinecount;
		}
		else
		{
			$inlinecheck = '';
		}

		if($donor['uid']) {
		    $donor['name'] = "<a href=\"index.php?module=user{$sep}users&amp;action=edit&amp;uid=$donor[uid]\" target=\"_blank\">$donor[name]</a>" ;
		    $donor['ogid'] = '<img src="./../images/naoar/group.gif" alt="" title="' .$lang->naoardonate_browse_ogid .  $groups[$donor['ogid']]['title'] . '" />';
		} else {
		    $donor['ogid'] = '';
		    $donor['name'] = $lang->naoardonate_global_guest;
		}

		if($donor['note']){
		    $note ="<div style=\"float:left\" id=\"note_$donor[did]_popup\">
					<div class=\"naoardonate_note\">" . wordwrap($donor['note'],30,'<br />',true) . "</div>
				</div>
			<a href=\"javascript:;\" id=\"note_$donor[did]\"><img src=\"./../images/naoar/note.gif\" title=\"" . $lang->naoardonate_global_note_recieved . "\" style=\"border:0\" /></a>
<script type=\"text/javascript\">
new PopupMenu('note_$donor[did]');
</script>";
		} else {

		    $note ='&nbsp;';

		}
		if($donor['email']){
		    $email = "<a href=\"mailto:$donor[email]\" title=\"" . $lang->naoardonate_global_email_donor . "\" ><img src=\"./../images/naoar/email.gif\" style=\"border:0\" /></a>";
		} else {
		    $email ='&nbsp;';

		}


		$donor['dateline']= my_date($mybb->settings['dateformat'], $donor['dateline']).", ".my_date($mybb->settings['timeformat'], $donor['dateline']);
		$table .= <<<TABLE_BODY
