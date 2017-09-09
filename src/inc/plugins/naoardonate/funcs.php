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
if(!defined("IN_MYBB")){
    exit("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

define('CODERME_XXX', '000');
define('CODERME_2CHECKOUT', '2CHECKOUT');
define('CODERME_PAYPAL', 'PAYPAL');
define('CODERME_BANK_WIRE', 'BANK_WIRE');
define('CODERME_WESTERN_UNION', 'WESTERN_UNION');
define('CODERME_BANNED_GROUP', 6);




function getCurrenciesOf($index) {

    # currencies supported by LibertyReserve
    $xcurrencies = array();

    $xcurrencies[CODERME_XXX] = array('EUR','USD');

    # currencies supported by 2checkout
    $xcurrencies[CODERME_2CHECKOUT] = array('AFN', 'ALL', 'DZD', 'ARS', 'AUD', 'AZN', 'BSD', 'BDT', 'BBD', 'BZD', 'BMD', 'BOB', 'BWP', 'BRL', 'GBP', 'BND', 'BGN', 'CAD', 'CLP', 'CNY', 'COP', 'CRC', 'HRK', 'CZK', 'DKK', 'DOP', 'XCD', 'EGP', 'EUR', 'FJD', 'GTQ', 'HKD', 'HNL', 'HUF', 'INR', 'IDR', 'ILS', 'JMD', 'JPY', 'KZT', 'KES', 'LAK', 'MMK', 'LBP', 'LRD', 'MOP', 'MYR', 'MVR', 'MRO', 'MUR', 'MXN', 'MAD', 'NPR', 'TWD', 'NZD', 'NIO', 'NOK', 'PKR', 'PGK', 'PEN', 'PHP', 'PLN', 'QAR', 'RON', 'RUB', 'WST', 'SAR', 'SCR', 'SGD', 'SBD', 'ZAR', 'KRW', 'LKR', 'SEK', 'CHF', 'SYP', 'THB', 'TOP', 'TTD', 'TRY', 'UAH', 'AED', 'USD', 'VUV', 'VND', 'XOF', 'YER');

    # currencies supported by PayPal
    $xcurrencies[CODERME_PAYPAL] = array('AUD','BRL','CAD','CHF','CZK','DKK','EUR','GBP','HKD','HUF','ILS','JPY','MXN','MYR','NOK','NZD','PHP','PLN','RUB','SEK','SGD','THB','TWD','USD');


    # currencies supported by Western Union
    $xcurrencies[CODERME_WESTERN_UNION] = array ('AED',    'ALL',    'AMD',    'ANG',    'AOA',    'ARS',    'AUD',    'AWG',    'AZN',    'BBD',    'BDT',    'BGN',    'BHD',    'BIF',    'BMD',    'BND',    'BOB',    'BRL',    'BSD',    'BTN',    'BWP',    'BZD',    'CAD',       'CDF',    'CHF',    'CLP',    'CNH',    'CNY',    'COP',    'CRC',    'CVE',    'CZK',    'DJF',    'DKK',    'DOP',    'DZD',    'EGP',    'ETB',    'EUR',    'FJD',    'FKP',    'GBP',    'GEL',    'GHS',    'GIP',    'GMD',    'GNF',    'GTQ',    'GYD',    'HKD',    'HNL',    'HRK',    'HTG',    'HUF',    'IDR',    'ILS',    'INR',    'JMD',    'JOD',    'JPY',    'KES',    'KGS',    'KHR',    'KMF',    'KRW',    'KWD',    'KYD',    'KZT',    'LAK',    'LBP',    'LKR',    'LSL',    'LTL',    'LVL',    'MAD',    'MDL',    'MGA',    'MKD',    'MNT',    'MOP',    'MRO',    'MUR',    'MVR',    'MWK',    'MXN',    'MYR',    'MZN',    'NAD',    'NGN',    'NIO',    'NOK',    'NPR',    'NZD',    'OMR',    'PAB',    'PEN',    'PGK',    'PHP',    'PKR',    'PLN',    'PYG',    'QAR',    'RON',    'RUB',    'RWF',    'SAR',    'SBD',    'SCR',    'SEK',    'SGD',    'SRD',    'STD',    'SZL',    'THB',    'TND',    'TOP',    'TRY',    'TTD',    'TWD',    'TZS',    'UAH',    'UGX',    'USD',    'UYU',    'UZS',    'VND',    'VUV',    'WST',    'XAF',    'XCD',    'XOF',    'XPF',    'YER',    'ZAR',    'ZMW');

    $xcurrencies[CODERME_BANK_WIRE] = array('AED',    'AFN',    'ALL',    'AMD',    'ANG',    'AOA',    'ARS',    'AUD',    'AWG',    'AZN',    'BAM',    'BBD',    'BDT',    'BGN',    'BHD',    'BIF',    'BMD',    'BND',    'BOB',    'BOV',    'BRL',    'BSD',    'BTN',    'BWP',    'BYR',    'BZD',    'CAD',    'CDF',    'CHE',    'CHF',    'CHW',    'CLF',    'CLP',    'CNH',    'CNY',    'COP',    'COU',    'CRC',    'CUC',    'CUP',    'CVE',    'CZK',    'DJF',    'DKK',    'DOP',    'DZD',    'EGP',    'ERN',    'ETB',    'EUR',    'FJD',    'FKP',    'GBP',    'GEL',    'GHS',    'GIP',    'GMD',    'GNF',    'GTQ',    'GYD',    'HKD',    'HNL',    'HRK',    'HTG',    'HUF',    'IDR',    'ILS',    'INR',    'IQD',    'IRR',    'ISK',    'JMD',    'JOD',    'JPY',    'KES',    'KGS',    'KHR',    'KMF',    'KPW',    'KRW',    'KWD',    'KYD',    'KZT',    'LAK',    'LBP',    'LKR',    'LRD',    'LSL',    'LTL',    'LVL',    'LYD',    'MAD',    'MDL',    'MGA',    'MKD',    'MMK',    'MNT',    'MOP',    'MRO',    'MUR',    'MVR',    'MWK',    'MXN',    'MXV',    'MYR',    'MZN',    'NAD',    'NGN',    'NIO',    'NOK',    'NPR',    'NZD',    'OMR',    'PAB',    'PEN',    'PGK',    'PHP',    'PKR',    'PLN',    'PYG',    'QAR',    'RON',    'RSD',    'RUB',    'RWF',    'SAR',    'SBD',    'SCR',    'SDG',    'SEK',    'SGD',    'SHP',    'SLL',    'SOS',    'SRD',    'SSP',    'STD',    'SYP',    'SZL',    'THB',    'TJS',    'TMT',    'TND',    'TOP',    'TRY',    'TTD',    'TWD',    'TZS',    'UAH',    'UGX',    'USD',    'UYI',    'UYU',    'UZS',    'VEF',    'VND',    'VUV',    'WST',    'XAF',    'XCD',    'XOF',    'XPF',    'YER',    'ZAR',    'ZMW',    'ZWL');

    
    $keys = array();
    if ('string' === gettype($index)) {
        if (strpos(',', $index) !== false) {
            $keys = explose(',', $index);
        } else {
            $keys = array($index);
        }

    } else {
        $keys = $index;
    }
    

    
    $ret = array();

    foreach($keys as $k) {
        if (array_key_exists($k, $xcurrencies)) {
            $ret  = array_merge($ret, $xcurrencies[$k]);
                  
        }
    }

    $ret = array_unique($ret);
    sort($ret);
    return $ret;

}

function getCommonCurrenciesFor() {
    $common = array();
    $diff = array();
    $all = array();
    
    $opts = array(
        CODERME_2CHECKOUT,
        CODERME_PAYPAL, CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

    );
    
    foreach( $opts as $o ){
        $all[] = getCurrenciesOf($o);
    }
    
    $num_args = func_num_args();

    if($num_args == 0){
        $common = call_user_func_array('array_merge', $all);
    } else {
        $arrays = array();
        $args = func_get_args();
        $others = array_values(
                          array_diff($opts, $args)
                    );
        if ($num_args == 1) {
            $common = getCurrenciesOf(func_get_args(0));
        } else {
          foreach($args as $v){
              $arrays[] = getCurrenciesOf($v);
           }
          
           $common = call_user_func_array('array_intersect', $arrays);
        }
        $xothers  = getCurrenciesOf($others);
        $common = call_user_func_array('array_diff',
                                       array($common,
                                             $xothers,
                                       ));
        
    }

    $common = array_unique(array_values($common));
    sort($common);
    
    return $common;
}


// mayDonate checks if the user allowed to donate
function mayDonate(array $user, $allowed_groups) {
    global $mybb;
      $donate_groups = explode(',', $allowed_groups);
  
      $gids = array();
      if(isset($user['usergroup'])) {
        $gids[] = $user['usergroup'];
      }

     if(isset($user['additionalgroups'])) {
       foreach(explode(',',$user['additionalgroups'] ) as $v) {
         $v = trim_blank_chrs($v);
         $gids[] = $v;
        }
     }

   foreach($donate_groups as $g){
      if(in_array($g, $gids)) {
        return true;
      }
    }

   // banned users maybe allowed too who knows
   if (isset($user['usergroup']) &&
       $user['usergroup'] == 6 &&
       $mybb->settings['naoardonate_unban'] > 0
    ) {
       return true;
   }
  
   return false;

  }


function isBanned(array $user){
    if (isset($user['usergroup']) and 
        $user['usergroup'] == CODERME_BANNED_GROUP) {
        return true;
    }
    return false;
}
    

