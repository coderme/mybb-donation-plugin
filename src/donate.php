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





define("IN_MYBB", 1);
define('THIS_SCRIPT', 'donate.php');



$templatelist = "naoardonate_redirect_v5,naoardonate_donate_aboutu_v5,naoardonate_donate_captcha_v5,naoardonate_donate_offline_wu_v5,naoardonate_donate_offline_bw_v5,naoardonate_donate_currencies_row_v5,naoardonate_donate_currencies_row_v5,naoardonate_donate_note_v5,naoardonate_donate_v5,naoardonate_top_donation_v5,naoardonate_top_v5,naoardonate_links_unban_v7";

require_once "./global.php";
require_once  MYBB_ROOT . 'inc/plugins/naoardonate/funcs.php';



# Load language phrases
$lang->load("naoardonate_front");
$lang->load("naoardonate_global");

if(!$db->table_exists('naoardonate')){
	error($lang->naoardonate_front_error_notinstalled);
} elseif($mybb->settings['naoardonate_onoff'] == 0) {
	error($lang->naoardonate_front_error_disabled);
} elseif((!$mybb->settings['naoardonate_payment_method_2c'] and !$mybb->settings['naoardonate_payment_method_pp']) or strlen($mybb->settings['naoardonate_payment_method']) < 5) {
	error($lang->naoardonate_front_error_notready);
} elseif(!mayDonate($mybb->user, $mybb->settings['naoardonate_from'])){
  	$mybb->user['uid'] != 0 ?
                       error($lang->naoardonate_front_error_blockedgroups)
                       : error($lang->naoardonate_front_error_noguests);
}



# resetting some variables
$name = $email = $amount = $currency = $currencies_row = $payment_method = $note = $errors = $js_updatelist = $js_load = $js_funcs = $captcha_valid = $submit_ifvalid = $isvalid_ = $single_currency_text = '';
 

# accepted payment processor
$accepted_payment_methods = explode(',',$mybb->settings['naoardonate_payment_method']);
$payment_methods_count = count($accepted_payment_methods);

# doonation page text
if (isBanned($mybb->user)) {
    $donation_page_title = $lang->naoardonate_front_unban_title;
    $donationdetails_title = $lang->naoardonate_front_unbandetails;
} else {
    $donation_page_title = $lang->naoardonate_front_donate_title;
    $donationdetails_title = $lang->naoardonate_front_donationdetails;
}

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

// currency
$currencies_lr = array('EUR', 'USD');
$currencies_2c = getCurrenciesOf(CODERME_2CHECKOUT);
$currencies_bk = getCurrenciesOf(CODERME_BANK_WIRE);
$currencies_wu = getCurrenciesOf(CODERME_WESTERN_UNION);
$currencies_pp = getCurrenciesOf(CODERME_PAYPAL);
$coderme_post_key = generate_post_check();


# validate input
if($mybb->request_method == 'post') {
    // post_key check
    verify_post_check($mybb->input['coderme_post_key']);

	# set working enviroment
	$name = trim($mybb->input['name']);
	$email = trim($mybb->input['email']);
	$payment_method = $mybb->input['payment_method'];
	$amount = str_replace('.00', '', number_format(floatval(($mybb->input['p_amount'] == 'custom' or !isset($mybb->input['p_amount'])) ? trim($mybb->input['c_amount']) : $mybb->input['p_amount']), 2 , '.', '') );
	$currency = ( preg_match('@[A-Z]{3}@', $mybb->settings['naoardonate_currency']) ? $mybb->settings['naoardonate_currency'] : $mybb->input['currency'] );
	$note = trim($mybb->input['note']);
	$imgstr = trim($mybb->input['imgstr']);
	$imghash = $mybb->input['imagehash'];
    $mtcn = trim($mybb->input['mtcn']);



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
	    and (($payment_method == '2checkout' and in_array($currency, $currencies_2c))
	    or ($payment_method == 'Bank/Wire transfer' and in_array($currency, $currencies_bk))
	    or ($payment_method == 'Western Union' and in_array($currency, $currencies_wu))
	    or ($payment_method == 'Paypal' and in_array($currency, $currencies_pp)))) and $mybb->settings['naoardonate_currency'] != '000')
	{
		$errors[] = $lang->sprintf($lang->naoardonate_front_error_unsupportedcurency, $payment_method);
	}


    // wu
    if($payment_method == 'Western Union') {
        if (!$mtcn) {
            $errors[] = $lang->naoardonate_front_error_empty_mtcn; 
        } elseif(!ctype_digit($mtcn) or $mtcn < 5e9) {
         $errors[] = $lang->naoardonate_front_error_invalid_mtcn; 
        }
    } else {
        // no other payment methods needs it for now
        $mtcn = '';
    }
    

	# check for valid captcha
	if(($mybb->settings['naoardonate_captcha'] == 3  or ($mybb->settings['naoardonate_captcha'] == 2 and $mybb->user['uid']) or ($mybb->settings['naoardonate_captcha'] == 1 and !$mybb->user['uid'])) and function_exists("imagepng"))	{
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
	else {
		$captcha_valid = True;
	}


	# further manipulation of post data :)
	if (!empty($name)) {
		$name = substr($name,0,$mybb->settings['maxnamelength']);
	} else {
		$mybb->user['uid'] ? $name = $mybb->user['username'] : 	$name = $lang->naoardonate_global_guest;
	}
    
	!empty($note) ? $note = substr($note,0 , 100) : false;

	# is everything ok?
	if(empty($errors))	{
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
                                'invoice_id' => $mtcn,
								'payment_method' => $payment_method,
								'real_amount' => $amount,
								'currency' => $currency,
								'note' => $note,
								'ip' => $ip,
								'dateline' => time()
							));

		# now prepare payment_method specific data  : )
		switch($payment_method)	{

        case '2checkout':
            
				$method = 'post';
				$url = 'https://www.2checkout.com/checkout/purchase';
				$currency_name = 'currency_code';
				$merchant_name =  'sid';
                $merchant_value = $mybb->settings['naoardonate_payment_method_2c'];
				$amount_name = 'li_0_price';
				$return_name = 'x_receipt_link_url';
				$cancel_name = '';
				$additional = <<<DOC
<input type="hidden" name="li_0_type" value="product" />
<input type="hidden" name="li_0_name" value="{$lang->naoardonate_front_donation} #$insert_id:$uid | $name" />
<input type="hidden" name="li_0_tangible" value="N" />
<input type="hidden" name="li_0_quantity" value="1" />
<input type="hidden" name="mode" value="2CO" />
DOC;

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

        if($cancel_name) {
            $cancel_url =<<<DOC
<input type="hidden" name="$cancel_name" value="{$mybb->settings['bburl']}/donate.php" />
DOC;
        } else {
          $cancel_url = '';
        }

		# this is a good time to run plugins
		$plugins->run_hooks('naoardonate_alert_admin');


		# give a user a cookie :)
		my_setcookie('naoardonate', 'd_ip'.$_SERVER['REMOTE_ADDR'],'86400');        

		# offline donations finish here
		if ( in_array($payment_method, array('Western Union', 'Bank/Wire transfer'))) {
           // rdr to prevent form resubmit
           redirect($mybb->settings['bburl'] . '/donate.php?action=thank_you',
                  $lang->naoardonate_front_thanku,
                  $lang->naoardonate_front_thanku_title);
            
            
		    exit;
		}


		# everything is ready? I hope so ..
		eval('$naoardonate_redirect = "' . $templates->get('naoardonate_redirect_v5') . '";');
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
	eval('$aboutyou = "' . $templates->get('naoardonate_donate_aboutu_v5') . '";');
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


eval('$captcha = "' . $templates->get('naoardonate_donate_captcha_v5') . '";');
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
    $payment_method_offline = $lang->sprintf($lang->naoardonate_front_offline_payment_methods, 'Western Union');
        $payment_offline_id = 'offline_wu';
        $payment_offline = nl2br(htmlspecialchars_uni($mybb->settings['naoardonate_payment_method_wu']));
        $pay_to = $lang->sprintf($lang->naoardonate_front_payfor, 'Western Union');
        eval('$offline_options = "' . $templates->get('naoardonate_donate_offline_wu_v5') . '";');
    
}

if ( in_array('Bank/Wire transfer', $accepted_payment_methods) ) {
    
    $payment_method_offline = $lang->sprintf($lang->naoardonate_front_offline_payment_methods, 'Bank/Wire transfer');
        $payment_offline_id = 'offline_bk';
        $payment_offline = nl2br(htmlspecialchars_uni($mybb->settings['naoardonate_payment_method_bk']));
        $pay_to = $lang->sprintf($lang->naoardonate_front_payfor, 'Bank/Wire transfer');
        eval('$offline_options .= "' . $templates->get('naoardonate_donate_offline_bw_v5') . '";');






    

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
		if(in_array($c, $currencies_2c))
		{
			$tc_currencies .=  "<option value=\"$c\">" . $lang->$lang_var . "</option>";
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
$js_updatelist = <<<'DOC'
 function change_payment_method(){
 try{
 _payment_method();
 }
 catch(e){
  console.error("Err _payment_method()", e);
 }
}

// noop
function check_amount(){
  return;
}

function mtcnSwitch(on){
  var t = $('#coderme-mtcn'), i = t.find('input')[0];
  if(on) {
    t.show();
    i.required = true;
    return;
  }

  t.hide();
  i.required = false;
}
j=d.getElementById('currency');
function _payment_method() { 
var offline =  false; 
if(false) { var x = 1; }
               
DOC;

$js_updatelist .= "\n\n";
$js_updatelist .= "\n \n";


    if (in_array('Western Union', $accepted_payment_methods)) {

        $offline_js_wu = " offline = document.getElementById('offline_wu');
	    try {
		   document.getElementById('offline_bk').style.display = 'none';
	    } catch(e) {console.log(e)}  mtcnSwitch(1);
    ";
    }
    
    if (in_array('Bank/Wire transfer', $accepted_payment_methods))  {
        $offline_js_bk = " offline = document.getElementById('offline_bk');
	try {
	    document.getElementById('offline_wu').style.display = 'none';
	} catch(e) {console.log(e)} mtcnSwitch();
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
	  } catch(e) {console.log(e)} mtcnSwitch();

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
    eval('$currencies_row ="' . $templates->get('naoardonate_donate_currencies_row_v5') . '";');

}
elseif ($mybb->settings['naoardonate_currency'] == 'Any')
{

    if ( in_array('2checkout', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , '2checkout') . '">'
			. $tc_currencies
			. '</optgroup>';
	$js_updatelist  .= " else if(a.payment_method.value == '2checkout'){j.innerHTML = '<select name=\"currency\" class=\"w100\">$tc_currencies</select>'} ";
    }

    if ( in_array('Paypal', $accepted_payment_methods) )
    {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'Paypal') . '">'
			. $pp_currencies
			. '</optgroup>';
	$js_updatelist  .= " else if(a.payment_method.value == 'Paypal') {j.innerHTML = '<select name=\"currency\" class=\"w100\">$pp_currencies</select>'}";
    }

    if ( in_array('Western Union', $accepted_payment_methods) ) {
	$currencyselect .='<optgroup label="' . $lang->sprintf( $lang->naoardonate_front_currencies_supported_by , 'Western Union') . '">'
			. $wu_currencies
			. '</optgroup>';
   $js_updatelist .= " else if(a.payment_method.value == 'Western Union'){ j.innerHTML = '<select onchange=\"check_amount();\" name=\"currency\" class=\"w100\">$wu_currencies</select>'; mtcnSwitch(1);
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
    eval('$currencies_row ="' . $templates->get('naoardonate_donate_currencies_row_v5') . '";');

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

eval('$note_fieldset ="' . $templates->get('naoardonate_donate_note_v5') . '";');

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

eval('$naoardonate_donate = "' . $templates->get('naoardonate_donate_v5') . '";');

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
            
             if ($mybb->settings['naoardonate_hidetopemails'] == '0' and
                $top_donors['email']) {
                $top_donors['email'] = "<a href=\"mailto:$top_donors[email]\" title=\"$lang->naoardonate_global_email_donor\">$top_donors[email]</a>";
            } else {
                $top_donors['email'] = '-------';
            }


			$top_donors['dateline'] = my_date($mybb->settings['dateformat'],$top_donors['dateline']) . ', ' . my_date($mybb->settings['timeformat'], $top_donors['dateline']);
			eval("\$donations .= \"".$templates->get('naoardonate_top_donation_v5')."\";");
		}

		empty($donations) ? $donations = "<tr><td align=\"center\" class=\"trow1\" colspan=\"5\">{$lang->naoardonate_global_nothing}</td></tr>" : false;
		eval("\$naoardonate_top =\"".$templates->get('naoardonate_top_v5')."\";");
		output_page($naoardonate_top);
	}
}




