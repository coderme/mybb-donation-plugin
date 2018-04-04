<?php

/**
 *
 * CoderMe Donation FREE
 * Copyright 2018 CoderMe.com, All Rights Reserved
 *
 * Website: https://markit.coderme.com
 * Home:    https://red.coderme.com/mybb-donation-plugin
 * License: https://red.coderme.com/mybb-donation-plugin#license
 * Version: 5.0.0
 * GOLD VERSION: https://markit.coderme.com/mybb-donation-gold
 *
 **/





# Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	exit("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
$lang->load('naoardonate_stats');
$lang->load('naoardonate_global');

$page->add_breadcrumb_item($lang->naoardonate_global_stats, "index.php?module=coderme_donors-stats");
$page->extra_header=<<<TERA_HEADER
<style type="text/css">
.coderme_div{
border-bottom:thin silver solid;
background-color: #c9d9e5;
padding:5px;font-size:large;
font-weight:bold;
}

table {
   width: 100%;
}
    
.red {
	color:red;
}
.green {
	color:green;
}
.navy{
	color:navy;
}
.black{
	color:black;
}

.coderme_info div {
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
TERA_HEADER;

	# support Mybb 1.4 as well
	sprintf('%.1f', $mybb->version) == 1.4 ? $sep = '/' : $sep = '-';

	$sub_tabs['general'] = array(
		'title' => $lang->naoardonate_stats_general,
		'link' => "index.php?module=coderme_donors{$sep}stats",
		'description' => $lang->naoardonate_stats_general_desc
	);

	$sub_tabs['members'] = array(
		'title' => $lang->naoardonate_stats_members,
		'link' => "index.php?module=coderme_donors{$sep}stats&amp;action=members",
		'description' => $lang->naoardonate_stats_members_desc
	);
	$sub_tabs['guests'] = array(
		'title' => $lang->naoardonate_stats_guests,
		'link' => "index.php?module=coderme_donors{$sep}stats&amp;action=guests",
		'description' => $lang->naoardonate_stats_guests_desc
	);

	$groups = $cache->read('usergroups');

if( ! in_array( $mybb->input['action'], array('guests', 'members'))){
	$page->output_header($lang->naoardonate_stats_general);
	$page->output_nav_tabs($sub_tabs, 'general');


$currencies_array = array(
'AED' => $lang->naoardonate_global_currency_aed,
'AFN' => $lang->naoardonate_global_currency_afn,
'ALL' => $lang->naoardonate_global_currency_all,
'AMD' => $lang->naoardonate_global_currency_amd,
'ANG' => $lang->naoardonate_global_currency_ang,
'AOA' => $lang->naoardonate_global_currency_aoa,
'ARS' => $lang->naoardonate_global_currency_ars,
'AUD' => $lang->naoardonate_global_currency_aud,
'AWG' => $lang->naoardonate_global_currency_awg,
'AZN' => $lang->naoardonate_global_currency_azn,
'BAM' => $lang->naoardonate_global_currency_bam,
'BBD' => $lang->naoardonate_global_currency_bbd,
'BDT' => $lang->naoardonate_global_currency_bdt,
'BGN' => $lang->naoardonate_global_currency_bgn,
'BHD' => $lang->naoardonate_global_currency_bhd,
'BIF' => $lang->naoardonate_global_currency_bif,
'BMD' => $lang->naoardonate_global_currency_bmd,
'BND' => $lang->naoardonate_global_currency_bnd,
'BOB' => $lang->naoardonate_global_currency_bob,
'BOV' => $lang->naoardonate_global_currency_bov,
'BRL' => $lang->naoardonate_global_currency_brl,
'BSD' => $lang->naoardonate_global_currency_bsd,
'BTN' => $lang->naoardonate_global_currency_btn,
'BWP' => $lang->naoardonate_global_currency_bwp,
'BYR' => $lang->naoardonate_global_currency_byr,
'BZD' => $lang->naoardonate_global_currency_bzd,
'CAD' => $lang->naoardonate_global_currency_cad,
'CDF' => $lang->naoardonate_global_currency_cdf,
'CHE' => $lang->naoardonate_global_currency_che,
'CHF' => $lang->naoardonate_global_currency_chf,
'CHW' => $lang->naoardonate_global_currency_chw,
'CLF' => $lang->naoardonate_global_currency_clf,
'CLP' => $lang->naoardonate_global_currency_clp,
'CNH' => $lang->naoardonate_global_currency_cnh,
'CNY' => $lang->naoardonate_global_currency_cny,
'COP' => $lang->naoardonate_global_currency_cop,
'COU' => $lang->naoardonate_global_currency_cou,
'CRC' => $lang->naoardonate_global_currency_crc,
'CUC' => $lang->naoardonate_global_currency_cuc,
'CUP' => $lang->naoardonate_global_currency_cup,
'CVE' => $lang->naoardonate_global_currency_cve,
'CZK' => $lang->naoardonate_global_currency_czk,
'DJF' => $lang->naoardonate_global_currency_djf,
'DKK' => $lang->naoardonate_global_currency_dkk,
'DOP' => $lang->naoardonate_global_currency_dop,
'DZD' => $lang->naoardonate_global_currency_dzd,
'EGP' => $lang->naoardonate_global_currency_egp,
'ERN' => $lang->naoardonate_global_currency_ern,
'ETB' => $lang->naoardonate_global_currency_etb,
'EUR' => $lang->naoardonate_global_currency_eur,
'FJD' => $lang->naoardonate_global_currency_fjd,
'FKP' => $lang->naoardonate_global_currency_fkp,
'GBP' => $lang->naoardonate_global_currency_gbp,
'GEL' => $lang->naoardonate_global_currency_gel,
'GHS' => $lang->naoardonate_global_currency_ghs,
'GIP' => $lang->naoardonate_global_currency_gip,
'GMD' => $lang->naoardonate_global_currency_gmd,
'GNF' => $lang->naoardonate_global_currency_gnf,
'GTQ' => $lang->naoardonate_global_currency_gtq,
'GYD' => $lang->naoardonate_global_currency_gyd,
'HKD' => $lang->naoardonate_global_currency_hkd,
'HNL' => $lang->naoardonate_global_currency_hnl,
'HRK' => $lang->naoardonate_global_currency_hrk,
'HTG' => $lang->naoardonate_global_currency_htg,
'HUF' => $lang->naoardonate_global_currency_huf,
'IDR' => $lang->naoardonate_global_currency_idr,
'ILS' => $lang->naoardonate_global_currency_ils,
'INR' => $lang->naoardonate_global_currency_inr,
'IQD' => $lang->naoardonate_global_currency_iqd,
'IRR' => $lang->naoardonate_global_currency_irr,
'ISK' => $lang->naoardonate_global_currency_isk,
'JMD' => $lang->naoardonate_global_currency_jmd,
'JOD' => $lang->naoardonate_global_currency_jod,
'JPY' => $lang->naoardonate_global_currency_jpy,
'KES' => $lang->naoardonate_global_currency_kes,
'KGS' => $lang->naoardonate_global_currency_kgs,
'KHR' => $lang->naoardonate_global_currency_khr,
'KMF' => $lang->naoardonate_global_currency_kmf,
'KPW' => $lang->naoardonate_global_currency_kpw,
'KRW' => $lang->naoardonate_global_currency_krw,
'KWD' => $lang->naoardonate_global_currency_kwd,
'KYD' => $lang->naoardonate_global_currency_kyd,
'KZT' => $lang->naoardonate_global_currency_kzt,
'LAK' => $lang->naoardonate_global_currency_lak,
'LBP' => $lang->naoardonate_global_currency_lbp,
'LKR' => $lang->naoardonate_global_currency_lkr,
'LRD' => $lang->naoardonate_global_currency_lrd,
'LSL' => $lang->naoardonate_global_currency_lsl,
'LTL' => $lang->naoardonate_global_currency_ltl,
'LVL' => $lang->naoardonate_global_currency_lvl,
'LYD' => $lang->naoardonate_global_currency_lyd,
'MAD' => $lang->naoardonate_global_currency_mad,
'MDL' => $lang->naoardonate_global_currency_mdl,
'MGA' => $lang->naoardonate_global_currency_mga,
'MKD' => $lang->naoardonate_global_currency_mkd,
'MMK' => $lang->naoardonate_global_currency_mmk,
'MNT' => $lang->naoardonate_global_currency_mnt,
'MOP' => $lang->naoardonate_global_currency_mop,
'MRO' => $lang->naoardonate_global_currency_mro,
'MUR' => $lang->naoardonate_global_currency_mur,
'MVR' => $lang->naoardonate_global_currency_mvr,
'MWK' => $lang->naoardonate_global_currency_mwk,
'MXN' => $lang->naoardonate_global_currency_mxn,
'MXV' => $lang->naoardonate_global_currency_mxv,
'MYR' => $lang->naoardonate_global_currency_myr,
'MZN' => $lang->naoardonate_global_currency_mzn,
'NAD' => $lang->naoardonate_global_currency_nad,
'NGN' => $lang->naoardonate_global_currency_ngn,
'NIO' => $lang->naoardonate_global_currency_nio,
'NOK' => $lang->naoardonate_global_currency_nok,
'NPR' => $lang->naoardonate_global_currency_npr,
'NZD' => $lang->naoardonate_global_currency_nzd,
'OMR' => $lang->naoardonate_global_currency_omr,
'PAB' => $lang->naoardonate_global_currency_pab,
'PEN' => $lang->naoardonate_global_currency_pen,
'PGK' => $lang->naoardonate_global_currency_pgk,
'PHP' => $lang->naoardonate_global_currency_php,
'PKR' => $lang->naoardonate_global_currency_pkr,
'PLN' => $lang->naoardonate_global_currency_pln,
'PYG' => $lang->naoardonate_global_currency_pyg,
'QAR' => $lang->naoardonate_global_currency_qar,
'RON' => $lang->naoardonate_global_currency_ron,
'RSD' => $lang->naoardonate_global_currency_rsd,
'RUB' => $lang->naoardonate_global_currency_rub,
'RWF' => $lang->naoardonate_global_currency_rwf,
'SAR' => $lang->naoardonate_global_currency_sar,
'SBD' => $lang->naoardonate_global_currency_sbd,
'SCR' => $lang->naoardonate_global_currency_scr,
'SDG' => $lang->naoardonate_global_currency_sdg,
'SEK' => $lang->naoardonate_global_currency_sek,
'SGD' => $lang->naoardonate_global_currency_sgd,
'SHP' => $lang->naoardonate_global_currency_shp,
'SLL' => $lang->naoardonate_global_currency_sll,
'SOS' => $lang->naoardonate_global_currency_sos,
'SRD' => $lang->naoardonate_global_currency_srd,
'SSP' => $lang->naoardonate_global_currency_ssp,
'STD' => $lang->naoardonate_global_currency_std,
'SYP' => $lang->naoardonate_global_currency_syp,
'SZL' => $lang->naoardonate_global_currency_szl,
'THB' => $lang->naoardonate_global_currency_thb,
'TJS' => $lang->naoardonate_global_currency_tjs,
'TMT' => $lang->naoardonate_global_currency_tmt,
'TND' => $lang->naoardonate_global_currency_tnd,
'TOP' => $lang->naoardonate_global_currency_top,
'TRY' => $lang->naoardonate_global_currency_try,
'TTD' => $lang->naoardonate_global_currency_ttd,
'TWD' => $lang->naoardonate_global_currency_twd,
'TZS' => $lang->naoardonate_global_currency_tzs,
'UAH' => $lang->naoardonate_global_currency_uah,
'UGX' => $lang->naoardonate_global_currency_ugx,
'USD' => $lang->naoardonate_global_currency_usd,
'UYI' => $lang->naoardonate_global_currency_uyi,
'UYU' => $lang->naoardonate_global_currency_uyu,
'UZS' => $lang->naoardonate_global_currency_uzs,
'VEF' => $lang->naoardonate_global_currency_vef,
'VND' => $lang->naoardonate_global_currency_vnd,
'VUV' => $lang->naoardonate_global_currency_vuv,
'WST' => $lang->naoardonate_global_currency_wst,
'XAF' => $lang->naoardonate_global_currency_xaf,
'XCD' => $lang->naoardonate_global_currency_xcd,
'XOF' => $lang->naoardonate_global_currency_xof,
'XPF' => $lang->naoardonate_global_currency_xpf,
'YER' => $lang->naoardonate_global_currency_yer,
'ZAR' => $lang->naoardonate_global_currency_zar,
'ZMW' => $lang->naoardonate_global_currency_zmw,
'ZWL' => $lang->naoardonate_global_currency_zwl
);

	$totalnotconfirmed = $db->simple_select('naoardonate', 'real_amount', "confirmed = 0 AND real_amount > 0");
	$notconfirmedtotal = 0;

	while($row = $db->fetch_array($totalnotconfirmed)){

	$notconfirmedtotal += $row['real_amount'];

	}

	$totalconfirmed = $db->simple_select('naoardonate', 'real_amount', "confirmed = 1 AND real_amount > 0");

	$confirmedtotal = 0;
	while($row = $db->fetch_array($totalconfirmed)){

	$confirmedtotal += $row['real_amount'];

	}

	# you can get total by adding the previuos real_amounts together :)

	$total = $confirmedtotal + $notconfirmedtotal;

	$notconfirmedguest = $db->simple_select('naoardonate', 'real_amount', "confirmed = 0 AND uid = 0 AND real_amount > 0");

	$guestnotconfirmed =0;

	while($row = $db->fetch_array($notconfirmedguest)){

	$guestnotconfirmed += $row['real_amount'];

	}


	$confirmedguest = $db->simple_select('naoardonate', 'real_amount', "confirmed = 1 AND uid = 0 AND real_amount > 0");

	$guestconfirmed = 0;
	while($row = $db->fetch_array($confirmedguest)){

	$guestconfirmed += $row['real_amount'];

	}

	# calculate total guests donations

	$guesttotal = $guestnotconfirmed + $guestconfirmed;

	# calcuate members donatins : start with unconfirmed donations

	$membernotconfirmed = $notconfirmedtotal - $guestnotconfirmed;

	# members confirmed

	$memberconfirmed = $confirmedtotal - $guestconfirmed;

	# sum of members donations

	$membertotal = $memberconfirmed + $membernotconfirmed;

	# get highest donations real_amount
	$query = $db->simple_select('naoardonate', 'real_amount', "confirmed = '1'", array('order_by' => 'real_amount', 'order_dir' => 'DESC', 'limit' => 1));
	$highest = $db->fetch_field($query, 'real_amount');

	# get lowest donations real_amount
	$query =$db->simple_select('naoardonate', 'real_amount', "confirmed = '1' AND real_amount > '0'", array('order_by' => 'real_amount', 'limit'=> 1));
	$least = $db->fetch_field($query, 'real_amount');

	# count number of donations
	$query = $db->simple_select('naoardonate', 'COUNT(real_amount) AS donations', "confirmed = '1' AND real_amount > '0'");
	($no_donations = $db->fetch_field($query, 'donations'))? $average = (int)($confirmedtotal /$no_donations) : $average= 0 ;


	# get most used payment_methods ONLY if you accept multiple payment_methods
	$query = $db->simple_select('naoardonate', 'payment_method, COUNT(payment_method) AS toppayment_methods', "confirmed = '1' AND real_amount > '0' GROUP BY payment_method", array('order_by' => 'toppayment_methods', 'order_dir' => 'DESC', 'limit' => 4));
	while($row = $db->fetch_array($query))
	{
	$payment_methods[] =$row;
	}


	# get top donors' groups
	$query = $db->simple_select('naoardonate', 'ogid, COUNT(ogid) AS topgroups', 'confirmed = 1 AND real_amount > 0 GROUP BY ogid', array('order_by' => 'topgroups', 'order_dir' => 'DESC', 'limit' => 4));

	while($row = $db->fetch_array($query))
	{
	$topgroups[] =$row;
	}

	# get most used currencies ONLY if you accept multiple currencies
	$query = $db->simple_select('naoardonate', 'currency, COUNT(currency) AS topcurrencies', "confirmed = 1 AND real_amount > 0 GROUP BY currency", array('order_by'=>'topcurrencies','order_dir'=>'DESC','limit'=>3));

	while($row = $db->fetch_array($query))
	{
	$currencies[] =$row;
	}

	# get most paid real_amount
	$query = $db->simple_select('naoardonate', 'real_amount, COUNT(real_amount) AS topamounts', "confirmed = 1 AND real_amount > 0 GROUP BY real_amount", array('order_by'=>'topamounts','order_dir'=>'DESC','limit'=>3));

	while($row = $db->fetch_array($query)){

	$topamounts[] = $row;

	}

	print "<table cellspacing=\"0\"  class=\"coderme_table\">
	<tr valign=\"top\">
		<td><div class=\"coderme_div\">$lang->naoardonate_stats_total_donations :</div>";

	if($total)
	{
	print "<ul>". ( $confirmedtotal ? "<li class=\"green\">$lang->naoardonate_stats_confirmed : $confirmedtotal</li>" : '') . ( $notconfirmedtotal ? "<li class=\"red\">$lang->naoardonate_stats_unconfirmed : <a href=\"index.php?module=coderme_donors{$sep}browse&action=unconfirmed\" style=\"color:red;text-decoration:underline\" >$notconfirmedtotal</a></li>": '' ) .( $total? "<li class=\"navy\">$lang->naoardonate_stats_total : $total</li>" : '' ). "</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}
	print "</td><td><div class=\"coderme_div\">$lang->naoardonate_stats_guests_donations :</div>";

	if($guesttotal)
	{
	print "<ul>" . ( $guestconfirmed ? "<li class=\"green\">$lang->naoardonate_stats_confirmed : $guestconfirmed</li>" : '' ) . ($guestnotconfirmed? "<li class=\"red\">$lang->naoardonate_stats_unconfirmed : $guestnotconfirmed </li>" : '') . ( $guesttotal ? "<li class=\"navy\">$lang->naoardonate_stats_total : $guesttotal</li>" : '') . "</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}

	print "</td></tr><tr valign=\"top\"><td><div class=\"coderme_div\">$lang->naoardonate_stats_members_donations :</div>";

	if($membertotal)
	{
	print "<ul>" . ( $memberconfirmed ? "<li class=\"green\">$lang->naoardonate_stats_confirmed : $memberconfirmed </li>" : '') . ( $membernotconfirmed ? "<li class=\"red\">$lang->naoardonate_stats_unconfirmed : $membernotconfirmed </li>" : '' ) . ( $membertotal ? " <li class=\"navy\">$lang->naoardonate_stats_total : $membertotal </li>" : '' ). "</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}

	print "</td><td><div class=\"coderme_div\">$lang->naoardonate_stats_amount :</div>";

	if($total)
	{
	print "<ul>" .( $highest ? "<li class=\"red\">
			$lang->naoardonate_stats_highest : $highest </li>" : '') . ( $average ? "<li class=\"green\">$lang->naoardonate_stats_average : $average</li>" : '' ) . ( $least ? "<li class=\"navy\">
			$lang->naoardonate_stats_least : $least</li>" : '') . "</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}

	print "</td></tr><tr valign=\"top\"><td><div class=\"coderme_div\">$lang->naoardonate_stats_topcurrencies :</div>";

	if(!empty($currencies))
	{
	print "<ul>" .( $currencies[0]['currency'] ? "<li class=\"red\">" . $currencies_array[$currencies[0]['currency']] . " | " : '') . ( $currencies[0]['topcurrencies'] ? $currencies[0]['topcurrencies'] . ' ' : '') . ( $currencies[0]['topcurrencies'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ) . ( $currencies[1]['currency'] ? "<li class=\"green\">" . $currencies_array[$currencies[1]['currency']] . " | " : '' ) . ( $currencies[1]['topcurrencies'] ? $currencies[1]['topcurrencies'] . ' ' : '' ) . ( $currencies[1]['topcurrencies'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '') . ( $currencies[2]['currency'] ? "<li class=\"navy\">" . $currencies_array[$currencies[2]['currency']] . " | " : '') . ( $currencies[2]['topcurrencies'] ? $currencies[2]['topcurrencies'] . ' ' : '') . ( $currencies[2]['topcurrencies'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ) . "</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}

	print "</td><td><div class=\"coderme_div\">$lang->naoardonate_stats_topamounts :</div>";

	if($total)
	{
	print "<ul>" . ( $topamounts[0]['real_amount'] ? "<li class=\"red\">{$topamounts[0]['real_amount']} | " : '' ) . ( $topamounts[0]['topamounts'] ? $topamounts[0]['topamounts'] . ' ' : '') . ( $topamounts[0]['topamounts'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '') . ( $topamounts[1]['real_amount'] ? "<li class=\"green\">{$topamounts[1]['real_amount']} | " : '') . ( $topamounts[1]['topamounts'] ? $topamounts[1]['topamounts']. ' ' : '' ) . ( $topamounts[1]['topamounts'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '') . ( $topamounts[2]['real_amount'] ? "<li class=\"navy\">{$topamounts[2]['real_amount']} | " : '' ) . ($topamounts[2]['topamounts'] ? $topamounts[2]['topamounts'] . ' ' : '') . ($topamounts[2]['topamounts'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '') ."</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}
	print "</td></tr><tr valign=\"top\"><td><div class=\"coderme_div\">$lang->naoardonate_stats_toppayment_methods :</div>";

	if(!empty($payment_methods))
	{
	print "<ul>" . ( $payment_methods[0]['payment_method'] ? "<li class=\"red\">{$payment_methods[0]['payment_method']} | " : '') . ( $payment_methods[0]['toppayment_methods'] ? $payment_methods[0]['toppayment_methods'] . ' '  : '') . ( $payment_methods[0]['toppayment_methods'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ) . ( $payment_methods[1]['payment_method'] ? "<li class=\"green\">{$payment_methods[1]['payment_method']} | " : '' ) . ( $payment_methods[1]['toppayment_methods'] ? $payment_methods[1]['toppayment_methods'] . ' ' : '' ) .  ( $payment_methods[1]['toppayment_methods'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ) . ( $payment_methods[2]['payment_method'] ? "<li class=\"navy\">{$payment_methods[2]['payment_method']} | " : '') . ( $payment_methods[2]['toppayment_methods'] ? $payment_methods[2]['toppayment_methods'] . ' ' : '' ) . ( $payment_methods[2]['toppayment_methods'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ) . ( $payment_methods[3]['payment_method'] ? "<li class=\"black\">{$payment_methods[3]['payment_method']} | " : '') . ( $payment_methods[3]['toppayment_methods'] ? $payment_methods[3]['toppayment_methods'] . ' ' : '' ) . ( $payment_methods[3]['toppayment_methods'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ). "</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}

	print "</td><td><div class=\"coderme_div\">$lang->naoardonate_stats_topgroups :</div>";

	if($total)
	{
	print "<ul>" .( $topgroups[0]['ogid'] ? "<li class=\"red\">" . $groups[$topgroups[0]['ogid']]['title'] . " | " : '') . ( $topgroups[0]['topgroups'] ? $topgroups[0]['topgroups'] . ' '  : '') . ( $topgroups[0]['topgroups'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ) . ( $topgroups[1]['ogid'] ? "<li class=\"green\">" . $groups[$topgroups[1]['ogid']]['title'] . " | " : '' ) . ( $topgroups[1]['topgroups'] ? $topgroups[1]['topgroups'] . ' ' : '' ) . ($topgroups[1]['topgroups'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '') . ( $topgroups[2]['ogid'] ? "<li class=\"navy\">" . $groups[$topgroups[2]['ogid']]['title'] . " | " : '') . ( $topgroups[2]['topgroups'] ? $topgroups[2]['topgroups'] . ' ' : '' ) . ( $topgroups[2]['topgroups'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ) . ( $topgroups[3]['ogid'] ? "<li class=\"black\">" . $groups[$topgroups[3]['ogid']]['title'] . " | " : '') . ( $topgroups[3]['topgroups'] ? $topgroups[3]['topgroups'] . ' ' : '') . ( $topgroups[3]['topgroups'] > 0 ? $lang->naoardonate_stats_times . '</li>' : '' ). "</ul>";
	}
	else
	{
	print '<div style="padding:27px;text-align:left">' . $lang->naoardonate_stats_notyet . '</div>';
	}

	print "</td></tr></table>";

	$page->output_footer();


	} elseif ($mybb->input['action'] == 'members'){

		$page->output_header($lang->naoardonate_stats_members);
		$page->output_nav_tabs($sub_tabs, 'members');

		$query = $db->simple_select('naoardonate' ,'*' , 'real_amount > 0 AND uid != 0 AND confirmed=1',array('order_by' => 'real_amount', 'order_dir' => 'DESC', 'limit' => 21));

		$table =<<<TABLE_HEAD
		<table cellspacing="0" class="general">
<thead>
	<tr>
		<th class="align_center">$lang->naoardonate_global_name</th>
		<th class="align_center">$lang->naoardonate_global_amount</th>
		<th class="align_center">$lang->naoardonate_global_payment_method</th>
		<th class="align_center">$lang->naoardonate_global_ip</th>
		<th class="align_center">$lang->naoardonate_global_extra</th>
		<th class="align_center">$lang->naoardonate_global_date</th>
	</tr>
</thead>
<tbody>
TABLE_HEAD;
		 while($donor = $db->fetch_array($query)){

		 $donor['name']= "<a href=\"index.php?module=user{$sep}users&amp;action=edit&amp;uid=$donor[uid]\" target=\"_blank\">$donor[name]</a>";
		 $donor['dateline']= my_date($mybb->settings['dateformat'], $donor['dateline']).", ".my_date($mybb->settings['timeformat'], $donor['dateline']);
		 $donor['ogid'] = '<img src="./../images/naoar/group.gif" alt="" title="' .$lang->naoardonate_global_ogid .  $groups[$donor['ogid']]['title'] . '" />';


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


		 $table .= <<<TABLE_BODY
	<tr>
		<td class="align_center">$donor[name]</td>
		<td class="align_center">$donor[real_amount] $donor[currency]</td>
		<td class="align_center">$donor[payment_method]</td>
		<td class="align_center">$donor[ip]</td>
		<td class="align_center"><div class="coderme_info"><div>$email</div><div>$donor[ogid]</div><div>$note</div></div></td>
		<td class="align_center">$donor[dateline]</td>
</tr>
TABLE_BODY;
		}

	if(strpos($table, 'align_center') !== false){
		$table .='</tbody></table>';

	print $table;

	} else {

	$table .= '<tr><td colspan="6" class="align_center">' . $lang->naoardonate_global_nothing . '</td></tr></tbody></table>';

	print $table;

	}
	$page->output_footer();



	} else {

	$page->output_header($lang->naoardonate_stats_guests);
    $page->output_nav_tabs($sub_tabs, 'guests');


	$query = $db->simple_select('naoardonate', '*', 'real_amount > 0 AND uid = 0 AND confirmed =1',array('order_by' => 'real_amount', 'order_dir'=> 'DESC', 'limit' => 21));

			$table =<<<TABLE_HEAD
		<table cellspacing="0" class="general">
    <thead>
	<tr>
		<th class="align_center">$lang->naoardonate_global_name</th>
		<th class="align_center">$lang->naoardonate_global_amount</th>
		<th class="align_center">$lang->naoardonate_global_payment_method</th>
		<th class="align_center">$lang->naoardonate_global_ip</th>
		<th class="align_center">$lang->naoardonate_global_extra</th>
		<th class="align_center">$lang->naoardonate_global_date</th>
	</tr>
    </thead>
    <tbody>
TABLE_HEAD;
		 while($donor = $db->fetch_array($query)){

		 $donor['name']?$donor['name'] :$donor['name'] = 'Guest' ;
		 $donor['dateline']= my_date($mybb->settings['dateformat'], $donor['dateline']).", ".my_date($mybb->settings['timeformat'], $donor['dateline']);


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

		 $table .= <<<TABLE_BODY
	<tr>
		<td class="align_center">$donor[name]</td>
		<td class="align_center">$donor[real_amount] $donor[currency]</td>
		<td class="align_center">$donor[payment_method]</td>
		<td class="align_center">$donor[ip]</td>
		<td class="align_center"><div class="coderme_info"><div>$email</div><div>$note</div></div></td>
		<td class="align_center">$donor[dateline]</td>
			</tr>
TABLE_BODY;
		}
	if(strpos($table, 'align_center') !== false){
		$table .='</tbody></table>';

	print $table;

	} else {

	$table .= '<tr><td colspan="6" align="center">' . $lang->naoardonate_global_nothing . '</td></tr></tbody></table>';

	print $table;

	}
	$page->output_footer();


	}


