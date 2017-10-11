<?php

/**
 *
 * CoderMe Donation plugin
 * Copyright 2017 CoderMe.com, All Rights Reserved
 *
 * Website: https://coderme.com
 * Home:    https://red.coderme.com/mybb-donation-plugin
 * License: https://red.coderme.com/mybb-donation-plugin#license
 * Version: 4.0.1
 *
 **/


define("IN_MYBB", 1);
define('THIS_SCRIPT', 'donate.php');

$templatelist = "naoardonate_redirect_v4,naoardonate_donate_aboutu_v4,naoardonate_donate_captcha_v4,naoardonate_donate_offline_v4,naoardonate_donate_currencies_row_v4,naoardonate_donate_currencies_row_v4,naoardonate_donate_note_v4,naoardonate_donate_v4,naoardonate_top_donation_v4,naoardonate_top_v4";

require_once "./global.php";

# Load language phrases
$lang->load("naoardonate_front");
$lang->load("naoardonate_global");

# can you accept donations?
$naoar_from = explode(',', $mybb->settings['naoardonate_from']);


if(!$db->table_exists('naoardonate')):
	error($lang->naoardonate_front_error_notinstalled);
elseif($mybb->settings['naoardonate_onoff'] == 0):
	error($lang->naoardonate_front_error_disabled);
elseif((!$mybb->settings['naoardonate_payment_method_pz'] and !$mybb->settings['naoardonate_payment_method_lr'] and !$mybb->settings['naoardonate_payment_method_sk'] and !$mybb->settings['naoardonate_payment_method_pp']) or strlen($mybb->settings['naoardonate_payment_method']) < 5):
	error($lang->naoardonate_front_error_notready);
elseif(!in_array($mybb->user['usergroup'], $naoar_from)):
	$mybb->user['uid'] != 0 ? error($lang->naoardonate_front_error_blockedgroups) : error($lang->naoardonate_front_error_noguests);
endif;



# resetting some variables
$name = $email = $amount = $currency = $currencies_row = $payment_method = $note = $errors = $js_updatelist = $js_load = $js_funcs = $captcha_valid = $submit_ifvalid = $isvalid_ = $single_currency_text = '';

# All available currencies
 $currencies_bk = array('AED',    'AFN',    'ALL',    'AMD',    'ANG',    'AOA',    'ARS',    'AUD',    'AWG',    'AZN',    'BAM',    'BBD',    'BDT',    'BGN',    'BHD',    'BIF',    'BMD',    'BND',    'BOB',    'BOV',    'BRL',    'BSD',    'BTN',    'BWP',    'BYR',    'BZD',    'CAD',    'CDF',    'CHE',    'CHF',    'CHW',    'CLF',    'CLP',    'CNH',    'CNY',    'COP',    'COU',    'CRC',    'CUC',    'CUP',    'CVE',    'CZK',    'DJF',    'DKK',    'DOP',    'DZD',    'EGP',    'ERN',    'ETB',    'EUR',    'FJD',    'FKP',    'GBP',    'GEL',    'GHS',    'GIP',    'GMD',    'GNF',    'GTQ',    'GYD',    'HKD',    'HNL',    'HRK',    'HTG',    'HUF',    'IDR',    'ILS',    'INR',    'IQD',    'IRR',    'ISK',    'JMD',    'JOD',    'JPY',    'KES',    'KGS',    'KHR',    'KMF',    'KPW',    'KRW',    'KWD',    'KYD',    'KZT',    'LAK',    'LBP',    'LKR',    'LRD',    'LSL',    'LTL',    'LVL',    'LYD',    'MAD',    'MDL',    'MGA',    'MKD',    'MMK',    'MNT',    'MOP',    'MRO',    'MUR',    'MVR',    'MWK',    'MXN',    'MXV',    'MYR',    'MZN',    'NAD',    'NGN',    'NIO',    'NOK',    'NPR',    'NZD',    'OMR',    'PAB',    'PEN',    'PGK',    'PHP',    'PKR',    'PLN',    'PYG',    'QAR',    'RON',    'RSD',    'RUB',    'RWF',    'SAR',    'SBD',    'SCR',    'SDG',    'SEK',    'SGD',    'SHP',    'SLL',    'SOS',    'SRD',    'SSP',    'STD',    'SYP',    'SZL',    'THB',    'TJS',    'TMT',    'TND',    'TOP',    'TRY',    'TTD',    'TWD',    'TZS',    'UAH',    'UGX',    'USD',    'UYI',    'UYU',    'UZS',    'VEF',    'VND',    'VUV',    'WST',    'XAF',    'XCD',    'XOF',    'XPF',    'YER',    'ZAR',    'ZMW',    'ZWL');

# currencies supported by LibertyReserve
$currencies_lr = array('EUR','USD');

# currencies supported by Payza
$currencies_pz = array('AUD','BGN','CAD','CHF','CZK','DKK','EUR','GBP','HKD','HUF','INR','LTL','MKD','MYR','NOK','NZD','PLN','RON','SEK','SGD','USD','ZAR');

# currencies supported by Skrill
$currencies_sk = array('AED','AUD','BGN','CAD','CHF','CZK','DKK','EUR','GBP','HKD','HRK','HUF','ILS','INR','ISK','JOD','JPY','KRW','LTL','LVL','MAD','MYR','NOK','NZD','OMR','PLN','QAR','RON','RSD','SAR','SEK','SGD','THB','TND','TRY','TWD','USD','ZAR');

# currencies supported by PayPal
$currencies_pp = array('AUD','BRL','CAD','CHF','CZK','DKK','EUR','GBP','HKD','HUF','ILS','JPY','MXN','MYR','NOK','NZD','PHP','PLN','SEK','SGD','THB','TWD','USD');

# currencies supported by Western Union
$currencies_wu = array ('AED',    'ALL',    'AMD',    'ANG',    'AOA',    'ARS',    'AUD',    'AWG',    'AZN',    'BBD',    'BDT',    'BGN',    'BHD',    'BIF',    'BMD',    'BND',    'BOB',    'BRL',    'BSD',    'BTN',    'BWP',    'BZD',    'CAD',       'CDF',    'CHF',    'CLP',    'CNH',    'CNY',    'COP',    'CRC',    'CVE',    'CZK',    'DJF',    'DKK',    'DOP',    'DZD',    'EGP',    'ETB',    'EUR',    'FJD',    'FKP',    'GBP',    'GEL',    'GHS',    'GIP',    'GMD',    'GNF',    'GTQ',    'GYD',    'HKD',    'HNL',    'HRK',    'HTG',    'HUF',    'IDR',    'ILS',    'INR',    'JMD',    'JOD',    'JPY',    'KES',    'KGS',    'KHR',    'KMF',    'KRW',    'KWD',    'KYD',    'KZT',    'LAK',    'LBP',    'LKR',    'LSL',    'LTL',    'LVL',    'MAD',    'MDL',    'MGA',    'MKD',    'MNT',    'MOP',    'MRO',    'MUR',    'MVR',    'MWK',    'MXN',    'MYR',    'MZN',    'NAD',    'NGN',    'NIO',    'NOK',    'NPR',    'NZD',    'OMR',    'PAB',    'PEN',    'PGK',    'PHP',    'PKR',    'PLN',    'PYG',    'QAR',    'RON',    'RUB',    'RWF',    'SAR',    'SBD',    'SCR',    'SEK',    'SGD',    'SRD',    'STD',    'SZL',    'THB',    'TND',    'TOP',    'TRY',    'TTD',    'TWD',    'TZS',    'UAH',    'UGX',    'USD',    'UYU',    'UZS',    'VND',    'VUV',    'WST',    'XAF',    'XCD',    'XOF',    'XPF',    'YER',    'ZAR',    'ZMW');

# accepted Ebanks
$accepted_payment_methods = explode(',',$mybb->settings['naoardonate_payment_method']);
$payment_methods_count = count($accepted_payment_methods);

# amounts array
$amount_settings = explode(',',$mybb->settings['naoardonate_amount']);
$amount_array = array();
foreach($amount_settings as $v)
{
    $v = trim($v);
    if(empty($v) and $v != 0 ) continue;
    if ( preg_match('#\[(.*?)\]#', $v, $matches)) {
        $text = trim( htmlspecialchars_uni( $matches[1] ) );
        if ( preg_match( '#(?:\[.*\])?\s*(\d*\.?\d*)#', $v, $float)) {
            $v = strpos( $float[1], '.') === False ? (int) $float[1] : number_format( ( float ) $float[1], 2, '.', '');
        }
    }
    else {
        $text = '';
        $v = strpos( $v, '.') === False ? (int) $v : number_format( ( float ) $v, 2, '.', '');
    }

    if(False !== strpos("$v", '.00'))
	  $v = str_replace('.00', '', (string) $v);


	if( ! array_key_exists($v, $amount_array))
	{
		$amount_array[ $v ] = $text;
		$amount_indeces[] = $v;
	}
}

ksort($amount_array);
$amount_0 = array_slice($amount_array, 0, 1 ,1);
$index_0 = $amount_indeces[0];
$amount_1 = array_slice($amount_array, 1, 1, 1 );
$index_1 = $amount_indeces[1];


# validate input
if($mybb->request_method == 'post')
{
	# set working enviroment
	$name = trim($mybb->input['name']);
	$email = trim($mybb->input['email']);
	$payment_method = $mybb->input['payment_method'];
	$amount = str_replace('.00', '', number_format(floatval(($mybb->input['p_amount'] == 'custom' or !isset($mybb->input['p_amount'])) ? trim($mybb->input['c_amount']) : $mybb->input['p_amount']), 2 , '.', '') );
	$currency = ( preg_match('@[A-Z]{3}@', $mybb->settings['naoardonate_currency']) ? $mybb->settings['naoardonate_currency'] : $mybb->input['currency'] );
	$note = trim($mybb->input['note']);
	$imgstr = trim($mybb->input['imgstr']);
	$imghash = $mybb->input['imagehash'];



	# check name and email only if they are required
	if($mybb->settings['naoardonate_info_required'] and ($mybb->settings['naoardonate_info'] == 3 or $mybb->settings['naoardonate_info'] == 2 and $mybb->user['uid'] or $mybb->settings['naoardonate_info'] == 1 and !$mybb->user['uid'])){
		if(empty($name)){
			$errors[] = $lang->naoardonate_front_error_namerequired;
		}
		elseif(strlen($name) < $mybb->settings['minnamelength']){
			$errors[] = $lang->sprintf($lang->naoardonate_front_error_nametooshort, $mybb->settings['minnamelength']);
		}
		if(empty($email)){
			$errors[] = $lang->naoardonate_front_error_emailrequired;
		}
		elseif(!validate_email_format($email)){
			$errors[] = $lang->naoardonate_front_error_bademail;
		}
	}


	if ($amount == 0)
	{
		$errors[] = $lang->naoardonate_front_error_minimumzero;
	}

	elseif ($index_0 == 0 and $amount < $index_1 )
	{
		$errors[] = $lang->sprintf($lang->naoardonate_front_error_minimum, $index_1 );
	}

	elseif ($amount <  $index_0 )
	{
		$errors[] = $lang->sprintf($lang->naoardonate_front_error_minimum, $index_0 );
	}

	if(empty($payment_method))
	{
		$errors[] = $lang->naoardonate_front_error_nopayment_method;
	}

	elseif(!in_array($payment_method, $accepted_payment_methods))
	{
		$errors[] = $lang->sprintf($lang->naoardonate_front_error_notsupportedpayment_method,$payment_method);
	}
	elseif($mybb->settings['naoardonate_currency'] == '000' and !in_array($currency, array('EUR', 'USD')))
	{
		$errors[] = $lang->naoardonate_front_error_onlyusdoreuro;
	}
	elseif(!(($currency == $mybb->settings['naoardonate_currency']
	    or $mybb->settings['naoardonate_currency'] == 'Any')
	    and (($payment_method == 'LibertyReserve' and in_array($currency, $currencies_lr))
	    or ($payment_method == 'Payza' and in_array($currency, $currencies_pz))
	    or ($payment_method == 'Skrill' and in_array($currency, $currencies_sk))
	    or ($payment_method == 'Bank/Wire transfer' and in_array($currency, $currencies_bk))
	    or ($payment_method == 'Western Union' and in_array($currency, $currencies_wu))
	    or ($payment_method == 'Paypal' and in_array($currency, $currencies_pp)))) and $mybb->settings['naoardonate_currency'] != '000')
	{
		$errors[] = $lang->sprintf($lang->naoardonate_front_error_unsupportedcurency, $payment_method);
	}

	# check for valid captcha
	if(($mybb->settings['naoardonate_captcha'] == 3  or ($mybb->settings['naoardonate_captcha'] == 2 and $mybb->user['uid']) or ($mybb->settings['naoardonate_captcha'] == 1 and !$mybb->user['uid'])) and function_exists("imagepng"))
	{
		$imghash = $db->escape_string($imghash);
		$imgstr = $db->escape_string(my_strtolower($imgstr));
		$query = $db->simple_select("captcha", "*", "imagehash='$imghash' AND LOWER(imagestring)='$imgstr'");
		$imgcheck = $db->fetch_array($query);
		if(!$imgcheck['dateline'])
		{
			$errors[]  = $lang->naoardonate_front_error_invalidcaptcha;
		}
		$db->delete_query("captcha", "imagehash='$imghash'");
	}
	else
	{
		$captcha_valid = True;
	}


	# further manipulation of post data :)
	if(!empty($name)){
		$name = substr($name,0,$mybb->settings['maxnamelength']);
	}else{
		$mybb->user['uid'] ? $name = $mybb->user['username'] : 	$name = $lang->naoardonate_global_guest;
	}
	!empty($note) ? $note = substr($note,0 , 100) : false;

	# is everything ok?
	if(empty($errors))
	{
		if(!$email and $mybb->user['uid']) $email = $mybb->user['email'];

		# prepare data for database insertion
		if($mybb->settings['naoardonate_info'] == 3 or $mybb->settings['naoardonate_info'] == 2 and $mybb->user['uid'] or $mybb->settings['naoardonate_info'] == 1 and !$mybb->user['uid']){
			$name = $db->escape_string(htmlspecialchars_uni($name));
			$email = $db->escape_string(htmlspecialchars_uni($email));
		}

		$currency = $db->escape_string($currency);
		$payment_method = $db->escape_string($payment_method);
		$note ? $note = $db->escape_string(htmlspecialchars_uni($note)):false;

		$ip = $db->escape_string($_SERVER['REMOTE_ADDR']);
		$uid = intval($mybb->user['uid']);
		$gid = (int)$mybb->user['usergroup'];

		# very well .. lets do it
		$insert_id = $db->insert_query('naoardonate',array(
								'uid' => $uid,
								'ogid' => $gid,
								'name' => $name,
								'email' => $email,
								'payment_method' => $payment_method,
								'real_amount' => $amount,
								'currency' => $currency,
								'note' => $note,
								'ip' => $ip,
								'dateline' => time()
							));

		# now prepare payment_method specific data  : )
		switch($payment_method)
		{
			case 'Payza':

				$method = 'post';
				$url = 'https://secure.payza.com/checkout';
				$currency_name = 'ap_currency';
				$merchant_name =  'ap_merchant';
				$merchant_value = $mybb->settings['naoardonate_payment_method_pz'];
				$amount_name = 'ap_amount';
				$return_name = 'ap_returnurl';
				$cancel_name ='ap_cancelurl';
				$additional = "<input type=\"hidden\" name=\"ap_purchasetype\" value=\"service\" /><input type=\"hidden\" name=\"ap_itemname\" value=\"{$lang->naoardonate_front_donation}#$insert_id:$uid | $name\" />";

			break;

			case 'Skrill':
            
				$method = 'post';
				$url = 'https://www.skrill.com/app/payment.pl';
				$currency_name = 'currency';
				$merchant_name =  'pay_to_email';
				$merchant_value = $mybb->settings['naoardonate_payment_method_sk'];
				$amount_name = 'amount';
				$return_name = 'return_url';
				$cancel_name ='cancel_url';
				$additional = "<input type=\"hidden\" name=\"recipient_description\" value=\"{$mybb->settings['bburl']}\" /><input type=\"hidden\" name=\"return_url_text\" value=\"{$lang->naoardonate_front_returnto}{$mybb->settings['bburl']}\" /><input type=\"hidden\" name=\"return_url_target\" value=\"3\" /><input type=\"hidden\" name=\"cancel_url_target\" value=\"3\" /><input type=\"hidden\" name=\"language\" value=\"EN\" /><input type=\"hidden\" name=\"rid\" value=\"19686949\" /><input type=\"hidden\" name=\"detail1_description\" value=\"{$lang->naoardonate_front_donation}\" /><input type=\"hidden\" name=\"detail1_text\" value=\"#$insert_id:$uid | $name\" />";

			break;

			case 'Paypal':

				$method = 'post';
				$url = 'https://www.paypal.com/cgi-bin/webscr';
				$currency_name = 'currency_code';
				$merchant_name =  'business';
				$merchant_value = $mybb->settings['naoardonate_payment_method_pp'];
				$amount_name = 'amount';
				$return_name = 'return';
				$cancel_name ='cancel_return';
				$additional = "<input type=\"hidden\" name=\"cmd\" value=\"_donations\" /><input type=\"hidden\" name=\"item_name\" value=\"{$lang->naoardonate_front_donation}#$insert_id:$uid | $name\" />";
			break;

		}

		# this is a good time to run plugins
		$plugins->run_hooks('naoardonate_alert_admin');


		# offline donations finish here
		if ( in_array($payment_method, array('Western Union', 'Bank/Wire transfer'))) {
		    error($lang->naoardonate_front_thanku, $lang->naoardonate_front_thanku_title);
		    exit;
		}

		# give a user a cookie :)
		my_setcookie('naoardonate', 'd_ip'.$_SERVER['REMOTE_ADDR'],'86400');

		# everything is ready? I hope so ..
		eval('$naoardonate_redirect = "' . $templates->get('naoardonate_redirect_v4') . '";');
		print $naoardonate_redirect;
		exit;
	}
	else
	{
		$errors = inline_error($errors);
	}
}

if( !in_array($mybb->input['action'], array('thank_you', 'top_donors'))){

# show name and email fields ONLY when enabled
if($mybb->settings['naoardonate_info'] == 3 or $mybb->settings['naoardonate_info'] == 2 and $mybb->user['uid'] or $mybb->settings['naoardonate_info'] == 1 and !$mybb->user['uid']) {
	if(empty($name) and empty($email) and $mybb->user['uid']){
		$name = $mybb->user['username'];
		$email = $mybb->user['email'];
	}
	if($mybb->settings['naoardonate_info_required']){
		$optional_required = $lang->naoardonate_front_required;
	}
	else {
		$optional_required = $lang->naoardonate_front_optional;
	}
	($name == $lang->naoardonate_global_guest) ? $name='' : false;
	eval('$aboutyou = "' . $templates->get('naoardonate_donate_aboutu_v4') . '";');
} else {
	$aboutyou ='';
}

			if($mybb->input['imagestring'])
			{
				$imagehash = $db->escape_string($mybb->input['imagehash']);
				$imagestring = $db->escape_string($mybb->input['imagestring']);
				$query = $db->simple_select("captcha", "*", "imagehash='{$imagehash}' AND imagestring='{$imagestring}'");
				$imgcheck = $db->fetch_array($query);
				if($imgcheck['dateline'] > 0)
				{
					my_setcookie('naoardonate_captcha', md5($_SERVER['REMOTE_ADDR']),'86400');

				}
				else
				{
					$db->delete_query("captcha", "imagehash='{$imagehash}'");
				}
			}

	if(($mybb->settings['naoardonate_captcha'] == 3  or ($mybb->settings['naoardonate_captcha'] == 2 and $mybb->user['uid']) or ($mybb->settings['naoardonate_captcha'] == 1 and !$mybb->user['uid'])) and function_exists("imagepng") and !$captcha_valid)
		{
	$randomstr = random_str(5);
		$imagehash = md5(random_str(12));
		$imagearray = array(
			"imagehash" => $imagehash,
			"imagestring" => $randomstr,
			"dateline" => TIME_NOW
		);
		$db->insert_query("captcha", $imagearray);


eval('$captcha = "' . $templates->get('naoardonate_donate_captcha_v4') . '";');
} elseif($captcha_valid){
my_setcookie('imgstr', $mybb->input['imgstr'],'159');
$captcha ='';

}else {
$captcha ='';

}


add_breadcrumb($lang->naoardonate_front_donate_title);

# payment options
$payment_methodselect = $offline_options = '';

/* <fieldset class="w50 tleft" style="display: none;" id="{$payment_offline_id}">
<legend><strong>{$lang->naoardonate_front_offline_payment_methods}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
		<tr>
	<td valign="top" align="left"><strong>
			 		{$pay_to}:
					</strong>

	</td>
	<td class="w70" valign="top">
	{$payment_offline}

	</td>
</tr>

</table>
</fieldset> */
if ( in_array('Western Union', $accepted_payment_methods) ) {
    $payment_method_offline =  $lang->sprintf($lang->naoardonate_front_offline_payment_methods, 'Western Union');
    $payment_offline_id = 'offline_wu';
    $payment_offline = nl2br( htmlspecialchars_uni( $mybb->settings['naoardonate_payment_method_wu']) );
    $pay_to = $lang->sprintf( $lang->naoardonate_front_payfor, 'Western Union');
    eval('$offline_options = "' . $templates->get('naoardonate_donate_offline_v4') . '";');
}

if ( in_array('Bank/Wire transfer', $accepted_payment_methods) ) {
    $payment_method_offline = $lang->sprintf($lang->naoardonate_front_offline_payment_methods, 'Bank/Wire transfer');
    $payment_offline_id = 'offline_bk';
    $payment_offline =  nl2br( htmlspecialchars_uni( $mybb->settings['naoardonate_payment_method_bk']) );
    $pay_to = $lang->sprintf( $lang->naoardonate_front_payfor, 'Bank/Wire transfer');
    eval('$offline_options .= "' . $templates->get('naoardonate_donate_offline_v4') . '";');

}


# currency dropdown list
$currencyselect ='<select name="currency" class="w100">';

foreach($accepted_payment_methods as $e)
{
	$payment_methodselect .= "<option value=\"$e\"" . ( $payment_method == $e ? 'selected="selected"' : '' ) . "> " . $e . "</option>";

}
	foreach($currencies_bk as $c)
	{
		$lang_var = 'naoardonate_global_currency_' . strtolower($c);

		if(in_array($c, $currencies_lr))
		{
			$lr_currencies .=  "<option value=\"$c\">" . $lang->$lang_var . "</option>";
		}
		if(in_array($c, $currencies_pz))
		{
			$pz_currencies .=  "<option value=\"$c\">" . $lang->$lang_var . "</option>";
		}
		if(in_array($c, $currencies_sk))
		{
			$sk_currencies .=  "<option value=\"$c\">" . $lang->$lang_var . "</option>";
		}
		if(in_array($c, $currencies_pp))
		{
			$pp_currencies .=  "<option value=\"$c\">" . $lang->$lang_var . "</option>";
		}
		if(in_array($c, $currencies_wu))
		{
			$wu_currencies .=  "<option value=\"$c\">" . $lang->$lang_var . "</option>";
		}
		$bk_currencies .=  "<option value=\"$c\">" . $lang->$lang_var . "</option>";

	}

# wrapper function
$js_updatelist = 'function change_payment_method(){
try{
_payment_method();
}
catch(e){}
} ';

$js_updatelist .= "\nj=d.getElementById('currency');\n";
$js_updatelist .= "\nfunction _payment_method() { \n\tvar offline =  false; \n if(false) { var x = 1; } \n";

if ( in_array('Western Union', $accepted_payment_methods) ){

    $offline_js_wu = "	offline = document.getElementById('offline_wu');
	    try {
		document.getElementById('offline_bk').style.display = 'none';
	    } catch(e) {}
    ";
}
    if ( in_array('Bank/Wire transfer', $accepted_payment_methods) ){
$offline_js_bk = " offline = document.getElementById('offline_bk');
	try {
	    document.getElementById('offline_wu').style.display = 'none';
	} catch(e) {}
	";
}
if ($offline_js_bk  or $offline_js_wu) {
  $offline_js_submit =  "if (offline){
      a.submit.value = '{$lang->naoardonate_front_finiishbutton}';
      offline.style.display='block';
      }
      else {
      try {
	      document.getElementById('offline_wu').style.display = 'none';
	      document.getElementById('offline_bk').style.display = 'none';
	  } catch(e) {}

      a.submit.value = '{$lang->naoardonate_front_goto} ' + a.payment_method.value;
      }
";
}


# special case to allow only euro and usd
if($mybb->settings['naoardonate_currency'] == '000')
{
		foreach($currencies_lr as $s)
		{

				$lang_var = 'naoardonate_global_currency_' . strtolower($s);
				$currencyselect .= "<option value=\"$s\">" . $lang->$lang_var . "</option>";

		}
    $currencyselect .= '</select>';
    eval('$currencies_row ="' . $templates->get('naoardonate_donate_currencies_row_v4') . '";');

}
elseif ($mybb->settings['naoardonate_currency'] == 'Any')
{
    if ( in_array('Payza', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'Payza') . '">'
			. $pz_currencies
			. '</optgroup>';
	$js_updatelist  .= " else if(a.payment_method.value == 'Payza'){ j.innerHTML = '<select name=\"currency\" class=\"w100\">$pz_currencies</select>'}";
    }

    if ( in_array('Skrill', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'Skrill') . '">'
			. $sk_currencies
			. '</optgroup>';
	$js_updatelist  .= " else if(a.payment_method.value == 'Skrill'){j.innerHTML = '<select name=\"currency\" class=\"w100\">$sk_currencies</select>'} ";
    }

    if ( in_array('Paypal', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'Paypal') . '">'
			. $pp_currencies
			. '</optgroup>';
	$js_updatelist  .= " else if(a.payment_method.value == 'Paypal') {j.innerHTML = '<select name=\"currency\" class=\"w100\">$pp_currencies</select>'}";
    }

    if ( in_array('LibertyReserve', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'LibertyReserve') . '">'
			. $lr_currencies
			. '</optgroup>';
	$js_updatelist  .= "else if(a.payment_method.value == 'LibertyReserve') {j.innerHTML ='<select name=\"currency\" class=\"w100\">$lr_currencies</select>'}\n";
    }

    if ( in_array('Western Union', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'Western Union') . '">'
			. $wu_currencies
			. '</optgroup>';
	$js_updatelist  .= " else if(a.payment_method.value == 'Western Union'){ j.innerHTML = '<select name=\"currency\" class=\"w100\">$wu_currencies</select>';
	$offline_js_wu
	}";
    }

    if ( in_array('Bank/Wire transfer', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'Bank/Wire transfer') . '">'
			. $bk_currencies
			. '</optgroup>';
	$js_updatelist  .= " else if(a.payment_method.value == 'Bank/Wire transfer'){ j.innerHTML = '<select name=\"currency\" class=\"w100\">$bk_currencies</select>';
	$offline_js_bk
	} ";
    }


    $currencyselect .= '</select>';
    eval('$currencies_row ="' . $templates->get('naoardonate_donate_currencies_row_v4') . '";');

}
else
{
      $single_currency = 1;
      $single_currency_text = $mybb->settings['naoardonate_currency'];
}

$currencyselect .='</select>';

if ($offline_js_submit and  $mybb->settings['naoardonate_currency'] != 'Any') {
    if($offline_js_wu)
      $js_updatelist .= "else if(a.payment_method.value == 'Western Union'){
      $offline_js_wu
      }";

    if($offline_js_bk)
      $js_updatelist .= "else if(a.payment_method.value == 'Bank/Wire transfer'){
      $offline_js_bk
      }";
}
else {
    $js_updatelist .=  " a.submit.value = '{$lang->naoardonate_front_goto} ' + a.payment_method.value;";
}


$js_updatelist  .= $offline_js_submit  . '}';


$countofamount = count($amount_array);
$minimum_amount = ( $index_1  ? $index_1  : 1 );

$minimum = $lang->sprintf($lang->naoardonate_front_minimum,"$minimum_amount" . ($single_currency ? ' ' . $single_currency_text : ''));


if($index_0  == 0 and $countofamount <= 2){
  $c_amount = "<input type=\"number\" step=\"0.01\" min=\"$minimum_amount\" name=\"c_amount\" value=\"" . ($amount >=  $index_1 ? $amount : '') . "\" /> $single_currency_text <em>" . $minimum . " </em>";


  $p_amount = '';
}
elseif($index_0  == 0 and $countofamount > 2){
  # copy 0 to last element so not showing as the 1st option in dropdown list
//   $amount_array[] = $amount_array[0];
  # we don't need it anymore
//   unset($amount_array[0]);

  $p_amount = '<select name="p_amount" onchange="custom()" class="w100">';
  foreach ($amount_array as $k => $v){
    if ($k == 0) {
        continue;
    }

    $p_amount .= "<option value=\"$k\">";
    if( $v )
	$p_amount .= $v;
    elseif ( !$v )
	$p_amount .= "$k $single_currency_text" ;

    $p_amount .= " </option>";
  }

  $p_amount .= "<option value=\"custom\">" .  ($amount_array[0] ? $amount_array[0] : $lang->naoardonate_front_custom ) . " </option></select>";
  $c_amount = "<div id=\"custom\"><input type=\"number\" step=\"0.01\" name=\"c_amount\" min=\"$minimum_amount\" value=\"" . ($amount >= $minimum_amount ? $amount : $minimum_amount ). "\" /> $single_currency_text <em>" . $minimum . " </em></div>";

}
else {
  $c_amount = '';
  $p_amount .= '<select name="p_amount" class="w100">';
  foreach($amount_array as $k => $v ){

  $p_amount .=  "<option value=\"$k\">" .  ( $v ? $v : "$k $single_currency_text" ).  " </option>";

  }
  $p_amount .= '</select>';
  $js_validate ='';
}

if($mybb->settings['naoardonate_donormsg'] == 1){
# add js function shownote()
$js_load .= "t=a.note;if(t.value != ''){shownote();limit()}";
$js_funcs .= "function shownote(){r=d.getElementById('divnote');r.style.display = 'block';d.getElementById('noteintro').innerHTML =''}function limit(){if(t.value.length > 100){t.value=t.value.substring(0,100)}d.getElementById('max').innerHTML=100 - t.value.length}";

eval('$note_fieldset ="' . $templates->get('naoardonate_donate_note_v4') . '";');

}else {

$note_fieldset = '';

}
# playing with some javascript :)
if($mybb->input['p_amount'] == 'custom'){
  $js_load .= 'checkcustom();';
  $js_funcs .= "function checkcustom(){f.value = 'custom'; f.value.checked=1}";
}

if($c_amount and $p_amount or empty($p_amount) or $captcha or $mybb->settings['naoardonate_info_required'] and $aboutyou):
	$isvalid_ = 'function isvalid(){';
	$submit_ifvalid ='onsubmit="return isvalid()"';
endif;

if($mybb->settings['naoardonate_info_required'] and $aboutyou):
	$isvalid_ .= " var un = a.name;
	if(un.value == ''){
	coderme_alert('{$lang->naoardonate_front_error_namerequired}');
	un.focus();
	return false;}
	else if(un.value.length < " . $mybb->settings['minnamelength'] . " ){
	coderme_alert('" . $lang->sprintf($lang->naoardonate_front_error_nametooshort, $mybb->settings['minnamelength']) . "');
	un.focus();
	return false;}
	var ue = a.email;
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	if(ue.value == ''){
	coderme_alert('{$lang->naoardonate_front_error_emailrequired}');
	ue.focus();
	return false;}
	else if(re.test(ue.value) == false){
	coderme_alert('{$lang->naoardonate_front_error_bademail}');
	ue.focus();
	return false;}";
endif;


if($c_amount and $p_amount){
  $isvalid_ .= " var e= a.c_amount;
  if((parseFloat(e.value) != e.value) && f.value == 'custom'){
  e.value='';e.focus();
  coderme_alert('{$lang->naoardonate_front_error_invalidamount}');
  return false;}else if(e.value < $minimum_amount && f.value == 'custom'){
  e.value='';
  e.focus();
  coderme_alert('" . $lang->sprintf($lang->naoardonate_front_error_toosmallamount,"$minimum_amount $single_currency_text") . "');
  return false;}";

  $js_funcs .= "function custom(){
  c=d.getElementById('custom');
  if(f.value == 'custom'){
  c.style.display='block'}
  else c.style.display='none';}";
  $js_load .= 'custom();';

}
elseif(empty($p_amount)){
  $isvalid_ .= " var e= a.c_amount;
  if(parseFloat(e.value) != e.value){
  coderme_alert('{$lang->naoardonate_front_error_invalidamount}');
  e.value='';
  e.focus();
  return false;}
  else if(e.value < $minimum_amount){
  e.value='';
  e.focus();
  coderme_alert('" . $lang->sprintf($lang->naoardonate_front_error_toosmallamount,"$minimum_amount $single_currency_text") . "');
  return false;}";
}

if($captcha):
	$isvalid_ .= " var captcha = a.imgstr;
	if(captcha.value == ''){
	coderme_alert('{$lang->naoardonate_front_error_emptycaptcha}');
	captcha.focus();
	return false;}
	else if(captcha.value.length < 5){
	coderme_alert('{$lang->naoardonate_front_error_captchatooshort}');
	captcha.focus();
	return false;}";
endif;

if($isvalid_):
	$isvalid_ .= '}';
	$js_funcs .= $isvalid_;
endif;

eval('$naoardonate_donate = "' . $templates->get('naoardonate_donate_v4') . '";');

output_page($naoardonate_donate);


} elseif($mybb->input['action'] == 'thank_you'){

if($_COOKIE['naoardonate'] == 'd_ip'.$_SERVER['REMOTE_ADDR']){


	error($lang->naoardonate_front_thanku, $lang->naoardonate_front_thanku_title);

} else {

	error($lang->naoardonate_front_thanku_error);


}

}
elseif($mybb->input['action'] == 'top_donors') {

	$blocked_groups = explode(',',$mybb->settings['naoardonate_cannotviewtop']);

	if(in_array($mybb->user['usergroup'],$blocked_groups)){

		error($lang->naoardonate_front_error_cannotviewtop);


	} else {

		add_breadcrumb($lang->naoardonate_front_donate_title,'donate.php');
		add_breadcrumb($lang->naoardonate_front_top_title);

		$query =$db->simple_select('naoardonate','*', 'real_amount > 0 AND confirmed = 1', array('order_by' =>'real_amount', 'order_dir' => 'DESC', 'limit' => 11));

		while($top_donors = $db->fetch_array($query)){

			$top_donors['uid'] ? $top_donors['name'] = "<a href=\"member.php?action=profile&amp;uid=$top_donors[uid]\">$top_donors[name]</a>" : false;

			$top_donors['name'] ? True : $top_donors['name'] = $lang->naoardonate_global_guest;
			$top_donors['email'] ? $top_donors['email'] = "<a href=\"mailto:$top_donors[email]\" title=\"$lang->naoardonate_global_email_donor\">$top_donors[email]</a>" : $top_donors['email'] = '-----';
			$top_donors['dateline'] = my_date($mybb->settings['dateformat'],$top_donors['dateline']) . ', ' . my_date($mybb->settings['timeformat'], $top_donors['dateline']);
			eval("\$donations .= \"".$templates->get('naoardonate_top_donation_v4')."\";");
		}

		empty($donations) ? $donations = "<tr><td align=\"center\" class=\"trow1\" colspan=\"5\">{$lang->naoardonate_global_nothing}</td></tr>" : false;
		eval("\$naoardonate_top =\"".$templates->get('naoardonate_top_v4')."\";");
		output_page($naoardonate_top);
	}
}



