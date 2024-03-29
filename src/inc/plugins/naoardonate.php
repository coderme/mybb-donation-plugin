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


$plugins->add_hook("admin_page_output_footer", "naoar_showhide");
$plugins->add_hook("admin_config_settings_change", "naoar_fixit");
$plugins->add_hook("global_start", "naoar_showdonatelinks");
$plugins->add_hook("naoardonate_alert_admin", "naoar_alert");
$plugins->add_hook("build_friendly_wol_location_end", "naoar_donationpage_online");
$plugins->add_hook("admin_config_plugins_begin", "naoardonate_getid");
$plugins->add_hook("admin_config_plugins_activate_commit", "naoar_post_install");

function naoardonate_info(){
  global $lang;
  $lang->load('naoardonate_plugin');

    return array(
        "name"      => "CoderMe Donation FREE",
        "description"   => $lang->naoardonate_plugin_description,
        "website"   => "https://coderme.com/mybb-donation-plugin",
        "author"    => "CoderMe.com",
        "authorsite"    => "https://coderme.com?src=pluginslist",
        "version"   => "6.0.11",
        "guid"      => "a60331204b57399c66a958398b08e6df",
        // this shouldn't be in the 1st place
        // "codename"  => "naoardonate",
        "compatibility" => "18*"
    );
}


 #   ADDITIONAL PLUGIN INSTALL/UNINSTALL ROUTINES
 #
 #   _install():
 #   Called whenever a plugin is installed by clicking the "Install" button in the plugin manager.
 #   If no install routine exists, the install button is not shown and it assumed any work will be
 #   performed in the _activate() routine.


function naoardonate_install()
{
    global $mybb, $db, $gid, $sep, $no, $lang, $cache;

    $lang->load('naoardonate_plugin');
    $lang->load('naoardonate_settings');
    $lang->load('naoardonate_global');


    // try to delete old paths
    $old_donors = MYBB_ROOT . $mybb->config['admin_dir']
                . '/modules/naoar_donors';
    my_rmdir_recursive($old_donors);
    @rmdir($old_donors);



 if($db->table_exists('teradonate'))
 {

      $db->update_query('teradonate', array('ebank' => 'Paypal'), "ebank = 'PayPal'");
      $db->update_query('teradonate', array('ebank' => 'Payza'), "ebank = 'AlertPay'");

    if (!$db->field_exists('ogid', 'teradonate') )
    {
        $db->add_column('teradonate', 'ogid', "smallint NOT NULL DEFAULT '1'");
        if($query = $db->query("SELECT t.uid AS id, o.usergroup AS group
                            FROM " . TABLE_PREFIX . "teradonate t
                            LEFT JOIN " . TABLE_PREFIX . "users o ON(t.uid = o.uid)"))
        {
            while($row = $db->fetch_array($query))
            {
                $db->write_query("UPDATE " . TABLE_PREFIX . "teradonate SET ogid = $row[group] WHERE uid = $row[id]");
            }

        }

    }
 }
 elseif ($db->table_exists('naoardonate') ) {

  if ( $db->field_exists('ebank', 'naoardonate') ) {
          $db->update_query('naoardonate', array('ebank' => 'Paypal'), "ebank = 'PayPal'");
          $db->update_query('naoardonate', array('ebank' => 'Payza'), "ebank = 'AlertPay'");
  }
  elseif ( $db->field_exists('payment_method', 'naoardonate') ) {
      $db->update_query('naoardonate', array('payment_method' => 'Paypal'), "payment_method = 'PayPal'");
      $db->update_query('naoardonate', array('payment_method' => 'Payza'), "payment_method = 'AlertPay'");

  }
}


   # delete old settings
    // naoardonate_payment_method_pz remove
    // naoardonate_ebank_ap
    // teradonate_ebank_ap

   $db->delete_query("settings", "name = 'naoardonate_payment_method_pz' OR name = 'naoardonate_ebank_ap' OR  name = 'teradonate_ebank_ap'");


    # handle upgrading..
    if(!$db->table_exists('naoardonate') and $db->table_exists('teradonate'))
    {
        # rename table
        switch($mybb->config['database']['type']){
            case 'pgsql':
            case 'sqlite':
                $query = 'ALTER TABLE ' . TABLE_PREFIX . 'teradonate RENAME TO ' . TABLE_PREFIX . 'naoardonate';
            break;

            default:
                $query = 'RENAME TABLE ' . TABLE_PREFIX . 'teradonate TO ' . TABLE_PREFIX . 'naoardonate';
            }

            $db->query($query) or exit('DATABASE ERROR: teradonate table could not be renamed, error number: ' . $db->error_number());
    }
    elseif( ! $db->table_exists('naoardonate')) {
        switch($mybb->config['database']['type']){
            case 'pgsql':
                $query = "CREATE TABLE " . TABLE_PREFIX . "naoardonate
                (did SERIAL PRIMARY KEY,
                uid int NOT NULL DEFAULT '0',
                ogid int NOT NULL DEFAULT '1',
                name varchar(20) NOT NULL DEFAULT '',
                email varchar(120) NOT NULL DEFAULT '',
                invoice_id varchar(120) NOT NULL DEFAULT '',
                payment_method varchar(100) NOT NULL DEFAULT '',
                real_amount FLOAT NOT NULL DEFAULT '0',
                currency char(3) NOT NULL DEFAULT '' ,
                note varchar(100) DEFAULT '',
                ip inet,
                dateline numeric(30,0) NOT NULL DEFAULT '0',
                confirmed smallint DEFAULT '0',
                isbanned smallint DEFAULT '0'

                )";
            break;

            case 'sqlite':
                $query = "CREATE TABLE " . TABLE_PREFIX . "naoardonate
                (did int AUTOINCREMENT PRIMARY KEY,
                uid int NOT NULL DEFAULT '0',
                ogid int NOT NULL DEFAULT '1',
                name varchar(20) NOT NULL DEFAULT '',
                email varchar(120) NOT NULL DEFAULT '',
                invoice_id varchar(120) NOT NULL DEFAULT '',
                payment_method varchar(100) NOT NULL DEFAULT '',
                real_amount FLOAT NOT NULL DEFAULT '0',
                currency char(3) NOT NULL DEFAULT '' ,
                note varchar(100) DEFAULT '',
                ip varchar(39) DEFAULT '',
                dateline bigint(30) NOT NULL DEFAULT '0',
                confirmed tinyint(1) NOT NULL DEFAULT '0',
                isbanned tinyint(1) NOT NULL DEFAULT '0'
                )";
            break;

            default:
                $query = "CREATE TABLE " . TABLE_PREFIX . "naoardonate
                (did INT UNSIGNED NOT NULL AUTO_INCREMENT,
                uid INT UNSIGNED NOT NULL DEFAULT '0',
                ogid INT UNSIGNED NOT NULL DEFAULT '1',
                name VARCHAR(20) NOT NULL DEFAULT '',
                email VARCHAR(120) NOT NULL DEFAULT '',
                invoice_id VARCHAR(120) NOT NULL DEFAULT '',
                payment_method VARCHAR(100) NOT NULL DEFAULT '',
                real_amount FLOAT UNSIGNED NOT NULL DEFAULT '0',
                currency CHAR(3) NOT NULL DEFAULT '' ,
                note VARCHAR(100) DEFAULT '',
                ip VARCHAR(39) DEFAULT '',
                dateline BIGINT(30) UNSIGNED NOT NULL DEFAULT '0',
                confirmed TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                isbanned TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY did (did),
                KEY uid (uid),
                KEY ogid (ogid)) ENGINE=MyISAM;";

        }

        $db->query($query) or exit('CoderMe Donation plugin Couldn\'t be installed, database error number' . $db->error_number());

    } 

   if (!$db->field_exists('invoice_id', 'naoardonate')) {
        $db->add_column('naoardonate', 'invoice_id', "VARCHAR(120) NOT NULL DEFAULT ''");
    } 

   if (!$db->field_exists('isbanned', 'naoardonate')) {
        $db->add_column('naoardonate', 'isbanned', "SMALLINT NOT NULL DEFAULT '0'");
    }

    #####################
    # rename columns
    #####################
    if ($db->field_exists('ebank', 'naoardonate')) {
        switch($mybb->config['database']['type']){
        case 'pgsql':
            $db->rename_column('naoardonate', 'ebank', 'payment_method', "VARCHAR", True,  '');
        break;

        default:
            $db->rename_column('naoardonate', 'ebank', 'payment_method', "VARCHAR(100) NOT NULL DEFAULT ''");
        }

     }

    if ($db->field_exists('amount', 'naoardonate')) {
      switch($mybb->config['database']['type']){
          case 'pgsql':
          $db->rename_column('naoardonate', 'amount', 'real_amount', "FLOAT", True,  '0.00');
          break;

          default:
          $db->rename_column('naoardonate', 'amount', 'real_amount', "FLOAT UNSIGNED NOT NULL DEFAULT '0.00'");

      }

    }

    # check for previous versions
    $query = $db->simple_select('settinggroups', 'gid', "name='naoardonate' or name='teradonate'");

    if($db->num_rows($query) > 0):
        require_once  MYBB_ROOT . $mybb->config['admin_dir'] .
                     '/inc/functions.php';
        change_admin_permission('coderme_donors', "", -1);
        $gid = (int)$db->fetch_field($query, 'gid');
        $db->update_query('settinggroups', array('title' => 'CoderMe Donation FREE', 'name' => 'naoardonate'), "gid='{$gid}'");
    else:
        $query = $db->simple_select("settinggroups", "COUNT(*) as rose");
        $rows = $db->fetch_field($query, "rose");
        $insertarray = array(
            'name' => 'naoardonate',
            'title' => 'CoderMe Donation FREE',
            'description' => $db->escape_string($lang->naoardonate_settings_intro),
            'disporder' => $rows+1,
            'isdefault' => 0
        );
        $gid = $db->insert_query("settinggroups", $insertarray);
    endif;

    $settingsarray = array();


    if( array_key_exists('naoardonate_onoff', $mybb->settings) and $mybb->settings['naoardonate_onoff'] == 1
        or array_key_exists('teradonate_onoff', $mybb->settings) and $mybb->settings['teradonate_onoff'] == 1 )  {
        $naoardonate_onoff = 1;
    }
    else{
        $naoardonate_onoff = 0;

    }

    $c = 0;

    $settingsarray[] = array(
        'name' => 'naoardonate_onoff',
        'title' => $db->escape_string($lang->naoardonate_settings_onoff),
        'description' => $db->escape_string($lang->naoardonate_settings_onoff_desc),
        'optionscode' => $db->escape_string('php
<label onclick=\"t_load();\" for=\"naoardonate_on\" class=\"label_radio_on naoardonate_settings_onoff\">
<input type=\"radio\" name=\"upsetting[{$setting[\'name\']}]\" value=\"1\" class=\"radio_input radio_on naoardonate_settings_onoff\" id=\"naoardonate_on\"  " . ($setting[\'value\'] == 1 ? "checked=\"checked\"" : "" ) . "/>' . $lang->yes . '</label>
<label onclick=\"t_load();\" for=\"naoardonate_off\" class=\"label_radio_off naoardonate_settings_onoff\">
<input type=\"radio\" name=\"upsetting[{$setting[\'name\']}]\" value=\"0\" class=\"radio_input radio_off naoardonate_settings_onoff\" id=\"naoardonate_off\"  " . ($setting[\'value\'] == 0 ? "checked=\"checked\"" : "" ) . " />' . $lang->no . '</label>'),
        'value' => $db->escape_string("$naoardonate_onoff"),
        'disporder' => $c++,
        'gid' => $gid
    );
  
    $naoardonate_payment_method = '';
    if ($mybb->settings['naoardonate_payment_method']){
        $naoardonate_payment_method = $mybb->settings['naoardonate_payment_method'];
    }

    elseif ($mybb->settings['naoardonate_ebank'])
    {
        $naoardonate_payment_method = $mybb->settings['naoardonate_ebank'];
    }
    elseif ($mybb->settings['teradonate_ebank']) {
        $naoardonate_payment_method = $mybb->settings['teradonate_ebank'];

    }
    if ($naoardonate_payment_method){
      $naoardonate_payment_method = str_ireplace(
          array('AlertPay', 'Payza', ',,'),  array('', '', ','),  $naoardonate_payment_method );
      $naoardonate_payment_method = str_replace('PayPal', 'Paypal', $naoardonate_payment_method );
      $naoardonate_payment_method = trim($naoardonate_payment_method, ',');
    }
                                   
    $settingsarray[] = array(
        'name' => 'naoardonate_payment_method',
        'title' => $db->escape_string($lang->naoardonate_settings_payment_method),
        'description' => $db->escape_string($lang->naoardonate_settings_payment_method_desc),
        'optionscode' => $db->escape_string('php
<label onclick=\"t_onchange(\'naoardonate_2c\',\'payment_method_2c\');\" for=\"naoardonate_2c\"><input type=\"checkbox\" name=\"upsetting[naoardonate_payment_method][]\" id=\"naoardonate_2c\" value=\"2checkout\"   ".(strpos($setting[\'value\'],\'2checkout\') !== false? "checked=\"checked\"" : "" ) . "> 2checkout <a href=\"https://www.2checkout.com/signup\" title=\"'
. $lang->sprintf($lang->naoardonate_settings_get_payment_method_account, '2checkout') . '\" target=\"_blank\" rel=\"noopener\"><img src=\"./../images/naoar/oh.png\"  alt=\"'
. $lang->sprintf($lang->naoardonate_settings_get_payment_method_account, '2checkout') . '\" style=\"vertical-align:middle;border:0;width:13px;height:13px\"/></a></label>

<br /><label onclick=\"t_onchange(\'naoardonate_pp\',\'payment_method_pp\');\" for=\"naoardonate_pp\"><input type=\"checkbox\" name=\"upsetting[naoardonate_payment_method][]\" id=\"naoardonate_pp\" value=\"Paypal\"  ".(strpos($setting[\'value\'],\'Paypal\') !== false? "checked=\"checked\"" : "" ). "> Paypal <a href=\"https://www.paypal.com/us/cgi-bin/webscr?cmd=_registration-run\" title=\"'
. $lang->sprintf($lang->naoardonate_settings_get_payment_method_account, 'Paypal') . '\" target=\"_blank\" rel=\"noopener\"><img src=\"./../images/naoar/oh.png\" alt=\"'
. $lang->sprintf($lang->naoardonate_settings_get_payment_method_account, 'Paypal') . '\" style=\"vertical-align:middle;border:0;width:13px;height:13px\"/></a></label>
 

<br /><label onclick=\"t_onchange(\'naoardonate_bk\',\'payment_method_bk\');\" for=\"naoardonate_bk\"><input type=\"checkbox\" name=\"upsetting[naoardonate_payment_method][]\" id=\"naoardonate_bk\" value=\"Bank/Wire transfer\"  ".(strpos($setting[\'value\'],\'Bank/Wire transfer\') !== false? "checked=\"checked\"" : "" ). "> Bank/Wire transfer</label>


<br /><label onclick=\"t_onchange(\'naoardonate_wu\',\'payment_method_wu\');\" for=\"naoardonate_wu\"><input type=\"checkbox\" name=\"upsetting[naoardonate_payment_method][]\" id=\"naoardonate_wu\" value=\"Western Union\"  ".(strpos($setting[\'value\'],\'Western Union\') !== false? "checked=\"checked\"" : "" ). "> Western Union</label>



'),
        'value' => $db->escape_string($naoardonate_payment_method),
        'disporder' => $c++,
        'gid' => $gid
    );

    
    if ($mybb->settings['naoardonate_payment_method_2c']) {
        $naoardonate_payment_method_2c = $mybb->settings['naoardonate_payment_method_2c'];

        }
        elseif($mybb->settings['naoardonate_ebank_2c'])
        {
        $naoardonate_payment_method_2c = $mybb->settings['naoardonate_ebank_2c'];
        }
        elseif ($mybb->settings['teradonate_ebank_2c']) {
        $naoardonate_payment_method_2c = $mybb->settings['teradonate_ebank_2c'];

        }
        else {
        $naoardonate_payment_method_2c = '';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_payment_method_2c',
        'title' => $db->escape_string($lang->naoardonate_settings_payment_method_2C),
        'description' => $db->escape_string($lang->naoardonate_settings_payment_method_2C_desc),
        'optionscode' => 'text',
        'value' => $db->escape_string($naoardonate_payment_method_2c),
        'disporder' => $c++,
        'gid' => $gid
    );


     if($mybb->settings['naoardonate_payment_method_pp'])
        {
        $naoardonate_payment_method_pp = $mybb->settings['naoardonate_payment_method_pp'];
        }
        elseif($mybb->settings['naoardonate_ebank_pp'])
        {
        $naoardonate_payment_method_pp = $mybb->settings['naoardonate_ebank_pp'];
        }
        elseif ($mybb->settings['teradonate_ebank_pp']) {
        $naoardonate_payment_method_pp = $mybb->settings['teradonate_ebank_pp'];

        }
        else {
        $naoardonate_payment_method_pp = '';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_payment_method_pp',
        'title' => $db->escape_string($lang->naoardonate_settings_payment_method_PP),
        'description' => $db->escape_string($lang->naoardonate_settings_payment_method_PP_desc),
        'optionscode' => 'text',
        'value' => $db->escape_string($naoardonate_payment_method_pp),
        'disporder' => $c++,
        'gid' => $gid
    );

    if($mybb->settings['naoardonate_payment_method_bk']){
        $payment_method_bk = $mybb->settings['naoardonate_payment_method_bk'];
    }
    else {
        $payment_method_bk = "Account no: \nAccount holder: \nBank SWIFT code: \n----------\nBank Details: \nBank Address: \nBank contact address:\n";
    }


    $settingsarray[] = array(
        'name' => 'naoardonate_payment_method_bk',
        'title' => $db->escape_string($lang->naoardonate_settings_payment_method_bank),
        'description' => $db->escape_string($lang->naoardonate_settings_payment_method_bank_desc),
        'optionscode' => 'textarea',
        'value' => $db->escape_string($payment_method_bk),
        'disporder' => $c++,
        'gid' => $gid
    );

    if($mybb->settings['naoardonate_payment_method_wu']){
        $payment_method_wu = $mybb->settings['naoardonate_payment_method_wu'];
    }
    else {
        $payment_method_wu = "Full Name: \nAddress:\n ";
    }



    $settingsarray[] = array(
        'name' => 'naoardonate_payment_method_wu',
        'title' => $db->escape_string($lang->naoardonate_settings_payment_method_WU),
        'description' => $db->escape_string($lang->naoardonate_settings_payment_method_WU_desc),
        'optionscode' => 'textarea',
        'value' =>   $db->escape_string($payment_method_wu),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_from'])
        {
        $naoardonate_from = $mybb->settings['naoardonate_from'];
        }
        elseif ($mybb->settings['teradonate_from']) {
        $naoardonate_from = $mybb->settings['teradonate_from'];

        }
        else {
        $naoardonate_from = '1,2,3,4,6';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_from',
        'title' => $db->escape_string($lang->naoardonate_settings_from),
        'description' => $db->escape_string($lang->naoardonate_settings_from_desc),
        'optionscode' => $db->escape_string('php
" . $naoardonate_fromgroups . "'),
        'value' => $db->escape_string($naoardonate_from),
        'disporder' => $c++,
        'gid' => $gid
    );



    if($mybb->settings['naoardonate_alert'])
        {
        $naoardonate_alert = $mybb->settings['naoardonate_alert'];
        }
        elseif ($mybb->settings['teradonate_alert']) {
        $naoardonate_alert = $mybb->settings['teradonate_alert'];

        }
        else {
        $naoardonate_alert = 'notice';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_alert',
        'title' => $db->escape_string($lang->naoardonate_settings_unconfirmednotice),
        'description' => $db->escape_string($lang->naoardonate_settings_unconfirmednotice_desc),
        'optionscode' => "radio
notice=$lang->naoardonate_settings_notice
email=$lang->naoardonate_settings_email
disabled=$lang->naoardonate_settings_disabled
",
        'value' => $db->escape_string($naoardonate_alert),
        'disporder' => $c++,
        'gid' => $gid
    );



    
    if($mybb->settings['naoardonate_unban'])   {
         $naoardonate_unban = $mybb->settings['naoardonate_unban'];
          }     else {
        $naoardonate_unban = '0';
        }


    

    $settingsarray[] = array(
        'name' => 'naoardonate_unban',
        'title' => $db->escape_string($lang->naoardonate_settings_unban),
        'description' => $db->escape_string($lang->naoardonate_settings_unban_desc),
        'optionscode' => 'text',
        'value' => $db->escape_string($naoardonate_unban),
        'disporder' => $c++,
        'gid' => $gid
    );



    
    if($mybb->settings['naoardonate_enablebar'])
        {
        $naoardonate_enablebar = $mybb->settings['naoardonate_enablebar'];
        }
        elseif ($mybb->settings['teradonate_enablebar']) {
        $naoardonate_enablebar = $mybb->settings['teradonate_enablebar'];

        }
        else {
        $naoardonate_enablebar = '0';
        }




    $settingsarray[] = array(
        'name' => 'naoardonate_enablebar',
        'title' => $db->escape_string($lang->naoardonate_settings_enablebar),
        'description' => $db->escape_string($lang->naoardonate_settings_enablebar_desc),
        'optionscode' => $db->escape_string('php
<label onclick=\"t_enablebar();\" for=\"naoardonate_enablebar_on\" class=\"label_radio_yes naoardonate_settings_enablebar\"><input type=\"radio\" name=\"upsetting[naoardonate_enablebar]\" value=\"1\" class=\"radio_input radio_yes naoardonate_settings_enablebar\" id=\"naoardonate_enablebar_on\" " . ($setting[\'value\'] == 1 ? "checked=\"checked\"" : "" ) . "/>' . $lang->yes . '</label>
<label onclick=\"t_enablebar();\" for=\"naoardonate_enablebar_off\" class=\"label_radio_no naoardonate_settings_enablebar\"><input type=\"radio\" name=\"upsetting[naoardonate_enablebar]\" value=\"0\" class=\"radio_input radio_no naoardonate_settings_enablebar\" id=\"naoardonate_enablebar_off\" " . ($setting[\'value\'] == 0 ? "checked=\"checked\"" : "" ) . " />' . $lang->no . '</label>'),
        'value' => $db->escape_string($naoardonate_enablebar),
        'disporder' => $c++,
        'gid' => $gid
    );


    $settingsarray[] = array(
        'name' => 'naoardonate_newgoal',
        'title' => $db->escape_string($lang->naoardonate_settings_newgoal),
        'description' => $db->escape_string($lang->naoardonate_settings_newgoal_desc),
        'optionscode' => $db->escape_string('php
<label for=\"naoardonate_newgoal_on\" class=\"label_radio_yes naoardonate_settings_newgoal\"><input type=\"radio\" name=\"upsetting[naoardonate_newgoal]\" value=\"1\" class=\"radio_input radio_yes naoardonate_settings_newgoal\" id=\"naoardonate_newgoal_on\" />' . $lang->yes . '</label>
<label for=\"naoardonate_newgoal_off\" class=\"label_radio_no naoardonate_settings_newgoal\"><input type=\"radio\" name=\"upsetting[naoardonate_newgoal]\" value=\"0\" class=\"radio_input radio_no naoardonate_settings_newgoal\" id=\"naoardonate_newgoal_off\" checked=\"checked\" />' . $lang->no . '</label>'),
        'value' => 0,
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_reason'])
        {
        $naoardonate_reason = $mybb->settings['naoardonate_reason'];
        }
        elseif ($mybb->settings['teradonate_reason']) {
        $naoardonate_reason = $mybb->settings['teradonate_reason'];

        }
        else {
        $naoardonate_reason = '';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_reason',
        'title' => $db->escape_string($lang->naoardonate_settings_reason),
        'description' => $db->escape_string($lang->naoardonate_settings_reason_desc),
        'optionscode' => 'textarea',
        'value' => $db->escape_string($naoardonate_reason),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_target'])
        {
        $naoardonate_target = $mybb->settings['naoardonate_target'];
        }
        elseif ($mybb->settings['teradonate_target']) {
        $naoardonate_target = $mybb->settings['teradonate_target'];

        }
        else {
        $naoardonate_target = '';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_target',
        'title' => $db->escape_string($lang->naoardonate_settings_target),
        'description' => $db->escape_string($lang->naoardonate_settings_target_desc),
        'optionscode' => 'text',
        'value' => $db->escape_string($naoardonate_target),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_duration'])
        {
        $naoardonate_duration = $mybb->settings['naoardonate_duration'];
        }
        elseif ($mybb->settings['teradonate_duration']) {
        $naoardonate_duration = $mybb->settings['teradonate_duration'];

        }
        else {
        $naoardonate_duration = '';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_duration',
        'title' => $db->escape_string($lang->naoardonate_settings_duration),
        'description' => $db->escape_string($lang->naoardonate_settings_duration_desc),
        'optionscode' => $db->escape_string('php
<input type=\"text\" size=\"7\" maxlength=\"3\" name=\"upsetting[{$setting[\'name\']}]\" value=\"" . (string)((($v = ( (int)$setting[\'value\'] - time()) / 86400) <= 0 ) ? 0: round($v) ) ."\" /> Days'),

        'value' => $db->escape_string($naoardonate_duration),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_ifreached'])
        {
        $naoardonate_ifreached = $mybb->settings['naoardonate_ifreached'];
        }
        elseif ($mybb->settings['teradonate_ifreached']) {
        $naoardonate_ifreached = $mybb->settings['teradonate_ifreached'];

        }
        else {
            $naoardonate_ifreached = '1';
        }




    $settingsarray[] = array(
        'name' => 'naoardonate_ifreached',
        'title' => $db->escape_string($lang->naoardonate_settings_ifreached),
        'description' => $db->escape_string($lang->naoardonate_settings_ifreached_desc),
        'optionscode' => 'yesno',
        'value' => $db->escape_string($naoardonate_ifreached),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_bar_width'])
        {
        $naoardonate_bar_width  = $mybb->settings['naoardonate_bar_width'];
        }
        elseif ($mybb->settings['teradonate_bar_width']) {
        $naoardonate_bar_width  = $mybb->settings['teradonate_bar_width'];

        }
        else {
        $naoardonate_bar_width = '851/605';
        }


    $settingsarray[] = array(
        'name' => 'naoardonate_bar_width',
        'title' => $db->escape_string($lang->naoardonate_settings_bar_width),
        'description' => $db->escape_string($lang->naoardonate_settings_bar_width_desc),
        'optionscode' => 'text',
        'value' => $db->escape_string($naoardonate_bar_width),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_recievedmsg'])
        {
        $naoardonate_recievedmsg  = $mybb->settings['naoardonate_recievedmsg'];
        }
        elseif ($mybb->settings['teradonate_recievedmsg']) {
        $naoardonate_recievedmsg  = $mybb->settings['teradonate_recievedmsg'];

        }
        else {
        $naoardonate_recievedmsg = 'We have recieved <span style="color:#C30000">{1}</span> of our goal ..';
        }




    $settingsarray[] = array(
        'name' => 'naoardonate_recievedmsg',
        'title' => $db->escape_string($lang->naoardonate_settings_recievedmsg),
        'description' => $db->escape_string($lang->naoardonate_settings_recievedmsg_desc),
        'optionscode' => 'textarea',
        'value' => $db->escape_string($naoardonate_recievedmsg),
        'disporder' => $c++,
        'gid' => $gid
    );



    if($mybb->settings['naoardonate_recievedmsg_100'])
        {
        $naoardonate_recievedmsg_100  = $mybb->settings['naoardonate_recievedmsg_100'];
        }
        elseif ($mybb->settings['teradonate_recievedmsg_100']) {
        $naoardonate_recievedmsg_100  = $mybb->settings['teradonate_recievedmsg_100'];

        }
        else {
        $naoardonate_recievedmsg_100 = 'Woooow! goal achieved .. Thanks for all donors for their support';
        }




    $settingsarray[] = array(
        'name' => 'naoardonate_recievedmsg_100',
        'title' => $db->escape_string($lang->naoardonate_settings_recievedmsg_100),
        'description' => $db->escape_string($lang->naoardonate_settings_recievedmsg_100_desc),
        'optionscode' => 'textarea',
        'value' => $db->escape_string($naoardonate_recievedmsg_100),
        'disporder' => $c++,
        'gid' => $gid
    );



    if($mybb->settings['naoardonate_amount'])
        {
        $naoardonate_amount  = $mybb->settings['naoardonate_amount'];
        }
        elseif ($mybb->settings['teradonate_amount']) {
        $naoardonate_amount  = $mybb->settings['teradonate_amount'];

        }
        else {
        $naoardonate_amount = '0';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_amount',
        'title' => $db->escape_string($lang->naoardonate_settings_amount),
        'description' => $db->escape_string($lang->naoardonate_settings_amount_desc),
        'optionscode' => 'textarea',
        'value' => $db->escape_string($naoardonate_amount),
        'disporder' => $c++,
        'gid' => $gid
    );



    if($mybb->settings['naoardonate_currency'])
        {
        $naoardonate_currency  = $mybb->settings['naoardonate_currency'];
        }
        elseif ($mybb->settings['teradonate_currency']) {
        $naoardonate_currency  = $mybb->settings['teradonate_currency'];

        }
        else {
        $naoardonate_currency = 'Any';
        }
    
     require_once  MYBB_ROOT . 'inc/plugins'.
                                   '/naoardonate/funcs.php';
    $currencies = array (
    array(
    $lang->naoardonate_global_currency_all_supported =>
        array(
        CODERME_2CHECKOUT,
        CODERME_PAYPAL, CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),
    array(
    $lang->naoardonate_global_currency_2c_pp_bk =>
        array(
        CODERME_2CHECKOUT,
        CODERME_PAYPAL,
        CODERME_BANK_WIRE,

            )),                                                     
    array(
    $lang->naoardonate_global_currency_2c_wu_bk =>
        array(
        CODERME_2CHECKOUT,
        CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),
    array(
    $lang->naoardonate_global_currency_2c_bk =>
        array(
        CODERME_2CHECKOUT,
        CODERME_BANK_WIRE,

            )),
        
    array(
    $lang->naoardonate_global_currency_pp_wu_bk =>
        array(
        CODERME_PAYPAL, CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),
    array(
    $lang->naoardonate_global_currency_pp_bk =>
        array(
        CODERME_PAYPAL,
        CODERME_BANK_WIRE,

            )),
    array(
    $lang->naoardonate_global_currency_2c_pp_wu_bk =>
        array(
        CODERME_2CHECKOUT,
        CODERME_PAYPAL, CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),

    array(
    $lang->naoardonate_global_currency_2c_pp_bk =>
        array(
        CODERME_2CHECKOUT,
        CODERME_PAYPAL,
        CODERME_BANK_WIRE,

            )),

    array(
    $lang->naoardonate_global_currency_2c_wu_bk =>
        array(
        CODERME_2CHECKOUT,
        CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),

    array(
    $lang->naoardonate_global_currency_2c_bk =>
        array(
        CODERME_2CHECKOUT,
        CODERME_BANK_WIRE,
            )),
    array(
    $lang->naoardonate_global_currency_pp_wu_bk =>
        array(
        CODERME_PAYPAL, CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),


    array(
    $lang->naoardonate_global_currency_pp_bk =>
        array(
        CODERME_PAYPAL,
        CODERME_BANK_WIRE,

            )),        

    array(
    $lang->naoardonate_global_currency_wu_bk =>
        array(
        CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),        

    array(
    $lang->naoardonate_global_currency_bk =>
        array(
        CODERME_BANK_WIRE,

            )),        

    array(
    $lang->naoardonate_global_currency_wu_bk =>
        array(
        CODERME_WESTERN_UNION,
        CODERME_BANK_WIRE,

            )),        

    array(
    $lang->naoardonate_global_currency_bk =>
        array(
        CODERME_BANK_WIRE,
            )),
                                                                
    );
    
    $currenciesOptions = 'php
<select name=\"upsetting[{$setting[\'name\']}]\">
<option value=\"Any\" ".($setting[\'value\'] == \'Any\' ? "selected=\"selected\"" : "" ). ">' . $lang->naoardonate_settings_currency_any . '</option>
<option value=\"000\" ".($setting[\'value\'] == \'000\' ? "selected=\"selected\"" : "" ). ">Euro and USD</option>';

    foreach($currencies as $x){
      foreach($x as $k => $v){
        $list = call_user_func_array('getCommonCurrenciesFor', $v);
        if (count($list) == 0) {
          // :P
          continue;
        }
        $currenciesOptions .= '<optgroup label=\"' . $k . '\">';
        foreach($list as $y ){
             $name = 'naoardonate_global_currency_' . strtolower($y);
             $currenciesOptions .= '<option value=\"' . $y  .
                       '\"  ".($setting[\'value\'] == \'' . $y . '\' ? "selected=\"selected\"" : "" ). ">' . $lang->$name . '</option>';
        }
        $currenciesOptions .= '</optgroup>';
        
     }
    }
   $currenciesOptions .= '</select>';

   
    $settingsarray[] = array(
        'name' => 'naoardonate_currency',
        'title' => $db->escape_string($lang->naoardonate_settings_currency),
        'description' => $db->escape_string($lang->naoardonate_settings_currency_desc),
        'optionscode' => $db->escape_string($currenciesOptions),
        'value' => $db->escape_string($naoardonate_currency),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_info'])
        {
        $naoardonate_info  = $mybb->settings['naoardonate_info'];
        }
        elseif ($mybb->settings['teradonate_info']) {
        $naoardonate_info  = $mybb->settings['teradonate_info'];

        }
        else {
        $naoardonate_info = '1';
        }


    $settingsarray[] = array(
        'name' => 'naoardonate_info',
        'title' => $db->escape_string($lang->naoardonate_settings_info),
        'description' => $db->escape_string($lang->naoardonate_settings_info_desc),
        'optionscode' => "select
0=$lang->naoardonate_settings_disabled
1=$lang->naoardonate_settings_guestonly
2=$lang->naoardonate_settings_memberonly
3=$lang->naoardonate_settings_always
",
        'value' => $db->escape_string($naoardonate_info),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_info_required'])
        {
        $naoardonate_info_required  = $mybb->settings['naoardonate_info_required'];
        }
        elseif ($mybb->settings['teradonate_info_required']) {
        $naoardonate_info_required  = $mybb->settings['teradonate_info_required'];

        }
        else {
        $naoardonate_info_required = '0';
        }


    $settingsarray[] = array(
        'name' => 'naoardonate_info_required',
        'title' => $db->escape_string($lang->naoardonate_settings_info_required),
        'description' => $db->escape_string($lang->naoardonate_settings_info_required_desc),
        'optionscode' => "yesno",
        'value' => $db->escape_string($naoardonate_info_required),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_donorsgroup'])
        {
        $naoardonate_donorsgroup  = $mybb->settings['naoardonate_donorsgroup'];
        }
        elseif ($mybb->settings['teradonate_donorsgroup']) {
        $naoardonate_donorsgroup  = $mybb->settings['teradonate_donorsgroup'];

        }
        else {
        $naoardonate_donorsgroup = 'nochange';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_donorsgroup',
        'title' => $db->escape_string($lang->naoardonate_settings_donorsgroup),
        'description' => $db->escape_string($lang->naoardonate_settings_donorsgroup_desc),
        'optionscode' => $db->escape_string('php
<select name=\"upsetting[naoardonate_donorsgroup]\">
<option value=\"nochange\" " . ($setting[\'value\'] == \'nochange\' ?  "selected=\"selected\"" : "" ) . " >' . $lang->naoardonate_settings_donors_nochange .  '</option><option disabled=\"disabled\"> ............</option>" .
$naoardonate_groups . " </select>'),
        'value' => $db->escape_string($naoardonate_donorsgroup),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_unmovable'])
        {
        $naoardonate_unmovable  = $mybb->settings['naoardonate_unmovable'];
        }
        elseif ($mybb->settings['teradonate_unmovable']) {
        $naoardonate_unmovable  = $mybb->settings['teradonate_unmovable'];

        }
        else {
        $naoardonate_unmovable = '1,3,4,6';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_unmovable',
        'title' => $db->escape_string($lang->naoardonate_settings_unmovable),
        'description' => $db->escape_string($lang->naoardonate_settings_unmovable_desc),
        'optionscode' => $db->escape_string('php
" . $naoardonate_unmovablegroups . "'),
        'value' => $db->escape_string($naoardonate_unmovable),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_donormsg'])
        {
        $naoardonate_donormsg  = $mybb->settings['naoardonate_donormsg'];
        }
        elseif ($mybb->settings['teradonate_donormsg']) {
        $naoardonate_donormsg  = $mybb->settings['teradonate_donormsg'];

        }
        else {
        $naoardonate_donormsg = '0';
        }


    $settingsarray[] = array(
        'name' => 'naoardonate_donormsg',
        'title' => $db->escape_string($lang->naoardonate_settings_donormsg),
        'description' => $db->escape_string($lang->naoardonate_settings_donormsg_desc),
        'optionscode' => 'yesno',
        'value' => $db->escape_string($naoardonate_donormsg),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_captcha'])
        {
        $naoardonate_captcha  = $mybb->settings['naoardonate_captcha'];
        }
        elseif ($mybb->settings['teradonate_captcha']) {
        $naoardonate_captcha  = $mybb->settings['teradonate_captcha'];

        }
        else {
        $naoardonate_captcha = '1';
        }



    $settingsarray[] = array(
        'name' => 'naoardonate_captcha',
        'title' => $db->escape_string($lang->naoardonate_settings_captcha),
        'description' => $db->escape_string($lang->naoardonate_settings_captcha_desc),
        'optionscode' => "select
0=$lang->naoardonate_settings_disabled
1=$lang->naoardonate_settings_guestonly
2=$lang->naoardonate_settings_memberonly
3=$lang->naoardonate_settings_always
",
        'value' => $db->escape_string($naoardonate_captcha),
        'disporder' => $c++,
        'gid' => $gid
    );


    if($mybb->settings['naoardonate_cannotviewtop'])
        {
        $naoardonate_cannotviewtop  = $mybb->settings['naoardonate_cannotviewtop'];
        }
        elseif ($mybb->settings['teradonate_cannotviewtop']) {
        $naoardonate_cannotviewtop  = $mybb->settings['teradonate_cannotviewtop'];

        }
        else {
        $naoardonate_cannotviewtop = '1,2,5,7';
        }




    $settingsarray[] = array(
        'name' => 'naoardonate_cannotviewtop',
        'title' => $db->escape_string($lang->naoardonate_settings_cannotviewtop),
        'description' => $db->escape_string($lang->naoardonate_settings_cannotviewtop_desc),
        'optionscode' => $db->escape_string('php
" . $naoardonate_blockedgroups . "'),
        'value' => $db->escape_string($naoardonate_cannotviewtop),
        'disporder' => $c++,
        'gid' => $gid
    );



    $settingsarray[] = array(
        'name' => 'naoardonate_hidetopemails',
        'title' => $db->escape_string($lang->naoardonate_settings_hidetopemails),
        'description' => $db->escape_string($lang->naoardonate_settings_hidetopemails_desc),
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => $c++,
        'gid' => $gid
    );


    

    if($mybb->settings['naoardonate_googleanalytics'])
        {
        $naoardonate_googleanalytics  = $mybb->settings['naoardonate_googleanalytics'];
        }
        elseif ($mybb->settings['teradonate_googleanalytics']) {
        $naoardonate_googleanalytics  = $mybb->settings['teradonate_googleanalytics'];

        }
        else {
        $naoardonate_googleanalytics = '';
        }

                                   
    $settingsarray[] = array(
        'name' => 'naoardonate_googleanalytics',
        'title' => $db->escape_string($lang->naoardonate_settings_googleanalytics),
        'description' => $db->escape_string($lang->naoardonate_settings_googleanalytics_dec),
        'optionscode' => "textarea",
        'value' => $db->escape_string($naoardonate_googleanalytics),
        'disporder' => $c++,
        'gid' => $gid
    );


    $settingsarray[] = array(
        'name' => 'naoardonate_premium',
        'title' => '',
        'description' => $db->escape_string(<<<'DOC'
<h3 style="color:blue">Thank You</h3>
<span style="color:darkred;font-size:.9rem">Thank you for using my plugin, 
I hope you like it : ), 
you really do?! Great news for you is that the GOLD version of this plugin is in the <a href="https://coderme.com/mybb-donation-gold" target="_blank" rel="noopener">WILD</a>,<br />

If you like to contact me about anything other than support please use *contact link <a href="https://coderme.com?src=mybbc" target="_blank" rel="noopener">on this page</a><br />For support please use the release thread  <a href="https://community.mybb.com/thread-84084.html" target="_blank" rel="noopener">HERE</a>.</span>
DOC
        ),
        'optionscode' => 'php',
        'value' => '',
        'disporder' => $c++,
        'gid' => $gid
    );
    
    # clean old setups
    if(array_key_exists('naoardonate_onoff', $mybb->settings))
    {
        $db->delete_query("settings", "name LIKE 'naoardonate%'");
    }
    elseif(array_key_exists('teradonate_supportme', $mybb->settings))
        {
        $db->update_query("datacache", "title = REPLACE(title, 'tera', 'naoar')", "title LIKE 'tera%'");
        if(is_object($cache->handler)):

            # copy value to new cache
            $cache->update('naoardonate_goal', $cache->read('teradonate_goal'));
            $cache->update('naoardonate_unconfirmed', $cache->read('teradonate_unconfirmed'));

            # remove old cache
            $cache->handler->delete("teradonate_goal");
            $cache->handler->delete("teradonate_unconfirmed");
        endif;


      naoardonate_uninstall('teradonate');
    }

     # insert new values
    foreach($settingsarray as $v):
        $db->insert_query('settings', $v);
    endforeach;



    rebuild_settings();

  # Mybb 1.4
  sprintf('%.1f', $mybb->version) == 1.4 ? $sep = '/' : $sep = '-';
   $no = 'index.php?module=config%ssettings&action=change&gid=%d' ;
    @sleep(3);
}

function naoar_post_install()
{
    global $mybb, $sep, $gid, $no, $message, $installed;
    if ( $mybb->input['plugin'] == "naoardonate" and !$installed ) {
        flash_message($message, 'success');
        admin_redirect( sprintf($no, $sep, $gid) );
    }
}

 #   _is_installed():
 #   Called on the plugin management page to establish if a plugin is already installed or not.
 #   This should return TRUE if the plugin is installed (by checking tables, fields etc) or FALSE
 #   if the plugin is not installed.


function naoardonate_is_installed()
{
    // testing
    //return False;

    global $db;
    $query = $db->simple_select('settings', 'name', "name='naoardonate_unban'");

    if($db->num_rows($query) > 0){
        return True;
    }
    return False;

}

 #    _uninstall():
 #    Called whenever a plugin is to be uninstalled. This should remove ALL traces of the plugin
 #    from the installation (tables etc). If it does not exist, uninstall button is not shown.


function naoardonate_uninstall($clean=null)
{
    global $mybb, $db, $cache;


        if($mybb->request_method != 'post')
        {
                global $page, $lang;
                $lang->load('naoardonate_settings');
                $page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=naoardonate', $lang->naoardonate_settings_uninstall_message, $lang->naoardonate_settings_uninstall);
        }


   if(!isset($mybb->input['no'])){

    if($clean == 'teradonate')  {
        $tname = 'teradonate';
        $perm = 'tera_donors';
    }
    else {
        $tname = 'naoardonate';
        $perm = 'coderme_donors';
        
        # drop main plugin table
        $db->drop_table($tname);
    }


    # remove traces
    $db->delete_query("settings", "name LIKE '$tname%'");
    $db->delete_query("settinggroups", "name = '$tname'");
    $db->delete_query("datacache", "title = '{$tname}_goal'");
    $db->delete_query("datacache", "title = '{$tname}_unconfirmed'");
    if(is_object($cache->handler)):
        $cache->handler->delete("{$tname}_goal");
        $cache->handler->delete("{$tname}_unconfirmed");
    endif;

    require_once  MYBB_ROOT . $mybb->config['admin_dir'] 
            . '/inc/functions.php';
    
    change_admin_permission($perm, "", -1);


    rebuild_settings();
  }
}

 #     _activate():
 #    Called whenever a plugin is activated via the Admin CP. This should essentially make a plugin
 #    "visible" by adding templates/template changes, language changes etc.



function naoardonate_activate()
{
    global $db;

    include_once MYBB_ROOT . "inc/adminfunctions_templates.php";

    find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$pm_notice}{$naoardonate_notice}{$naoardonate_bar}');

    find_replace_templatesets("header", "#".preg_quote('{$menu_portal}')."#i", '{$naoardonate_donatelink}{$menu_portal}');

    find_replace_templatesets("footer", "#".preg_quote('{$task_image}')."#i", '{$task_image}{$naoar_copyright}');

    $templates_array = array();
    $templates_array[] = array(
        'title' => 'naoardonate_bar_v5',
        'template' => $db->escape_string('<br class="clear" />
<table style="width:{$container_width}px; margin:auto">
    <tr>
        <td style="padding-left:23%;" colspan="2">
        <span style="font-weight:bold;font-size:small;text-align:left;">&nbsp;{$werecieved_msg}</span>
        </td>
    </tr>


    <tr>
        <td>
        <table style=" border:0;" cellspacing="0" cellpadding="0">
        <tr>

            <td style="background: url(\'{$mybb->settings[\'bburl\']}/images/naoar/tail.gif\') no-repeat; width:12px;"> </td>

            <!-- bar started -->

            <td style="background: url(\'{$mybb->settings[\'bburl\']}/images/naoar/body.gif\') repeat-x; width:{$progress_value}px;"> </td>


                 {$tip_tail}
                 {$left_div}
                {$tail_tip}

        <td styke="padding:0"><a href="{$mybb->settings[\'bburl\']}/donate.php"><img alt="" src="{$mybb->settings[\'bburl\']}/images/naoar/donate_now.gif" width="100" height="21" style="vertical-align:baseline" border="0" /></a>{$naoardonate_top}</td>
        </tr>
        </table>
        </td>

    </tr>



    <tr>
        <td colspan="2">
            <!-- reason  started-->
            {$naoardonate_reason}
            <!-- reason ended -->
        </td>
    </tr>

</table>
<br class="clear" />'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );


        $templates_array[] = array(
        'title' => 'naoardonate_links_donate_v5',
        'template' => $db->escape_string('<li><a href="{$mybb->settings[\'bburl\']}/donate.php" style="background-image: url(\'{$mybb->settings[\'bburl\']}/images/naoar/donate.png\')">{$lang->naoardonate_front_donate_title}</a></li>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_links_topdonors_v5',
        'template' => $db->escape_string('<li><a href="{$mybb->settings[\'bburl\']}/donate.php?action=top_donors" style="background-image: url(\'{$mybb->settings[\'bburl\']}/images/naoar/top.png\')">{$lang->naoardonate_front_top_title}</a></li>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );




        $templates_array[] = array(
        'title' => 'naoardonate_links_unban_v7',
        'template' => $db->escape_string('<li><a href="{$mybb->settings[\'bburl\']}/donate.php" style="background-image: url(\'{$mybb->settings[\'bburl\']}/images/naoar/unban.png\')">{$lang->naoardonate_front_unban_title}</a></li>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_reason_v5',
        'template' => $db->escape_string('{$mybb->settings[\'naoardonate_reason\']}'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_img_topdonors_v5',
        'template' => $db->escape_string('<a href="{$mybb->settings[\'bburl\']}/donate.php?action=top_donors"><img src="{$mybb->settings[\'bburl\']}/images/naoar/topdonors.gif" style="border:0" alt="" /></a>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_bar_tailtip_v5',
        'template' => $db->escape_string('<td style="width:12px; background: url(\'{$mybb->settings[\'bburl\']}/images/naoar/ftail.gif\') no-repeat; "></td>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_leftdiv_v5',
        'template' => $db->escape_string('<td style="width:{$left_value}px;
            background: url(\'{$mybb->settings[\'bburl\']}/images/naoar/lbody.gif\') repeat-x;"></td>
            <td style="width:12px; background: url(\'{$mybb->settings[\'bburl\']}/images/naoar/ltail.gif\') no-repeat;"></td>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_tiptail_v5',
        'template' => $db->escape_string('<td style="width:8px; background: url(\'{$mybb->settings[\'bburl\']}/images/naoar/tip.gif\') no-repeat;"></td>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );


    $templates_array[] = array(
        'title' => 'naoardonate_donate_aboutu_v5',
        'template' => $db->escape_string('<fieldset class="w50 tleft" >
<legend><strong>{$lang->naoardonate_front_aboutu}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
    <tr>
        <td><strong>{$lang->naoardonate_global_name}:</strong>
        </td>
            <td class="w70"><input type="text" name="name" value="{$name}" class="w80" /> <em>{$optional_required}</em>
        </td>

    </tr>
        <tr>
        <td><strong>{$lang->naoardonate_front_email}:</strong>
        </td>
            <td class="w70"><input type="email" name="email" value="{$email}" class="w80" /> <em>{$optional_required}</em>
        </td>

    </tr>

</table>
</fieldset>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_donate_currencies_row_v5',
        'template' => $db->escape_string('<tr>
        <td><strong>{$lang->naoardonate_global_currency}:</strong>
        </td>
        <td class="w70"><div id="currency">{$currencyselect}</div>
        </td>

    </tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );




        $templates_array[] = array(
        'title' => 'naoardonate_donate_offline_v5',
        'template' => $db->escape_string('<fieldset class="w50 tleft" style="display: none;" id="{$payment_offline_id}">
<legend><strong>{$payment_method_offline}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
        <tr>
    <td colspan="2">
    {$payment_offline}

    </td>
</tr>

</table>
</fieldset>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );




    $templates_array[] = array(
        'title' => 'naoardonate_donate_offline_bw_v5',
        'template' => $db->escape_string('<fieldset class="w50 tleft" style="display: none;" id="{$payment_offline_id}">
<legend><strong>{$payment_method_offline}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
        <tr>
    <td colspan="2">
    {$payment_offline}
    </td>
</tr>
</table>
</fieldset>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );




    $templates_array[] = array(
        'title' => 'naoardonate_donate_offline_wu_v5',
        'template' => $db->escape_string('<fieldset class="w50 tleft" style="display: none;" id="{$payment_offline_id}">
<legend><strong>{$payment_method_offline}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
        <tr>
    <td colspan="2">
    {$payment_offline}

    </td>
</tr>
<tr id="coderme-mtcn">
 <td><b>{$lang->naoardonate_front_mtcn}</b></td>
 <td class="w70">
<input type="number" step="1" name="mtcn" value="$mtcn" />
</td>

</tr>

</table>
</fieldset>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );


        


        $templates_array[] = array(
        'title' => 'naoardonate_donate_note_v5',
        'template' => $db->escape_string('<fieldset class="w50 tleft">
<legend><strong>{$lang->naoardonate_front_donationnote}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
        <tr>
    <td valign="top" align="left"><strong>
                    {$lang->naoardonate_front_note}:
                    </strong>

    </td>
    <td class="w70" valign="top">
    <script type="text/javascript">
    <!--
    document.write(\'<div id="noteintro" style="text-align:center"><a href="javascript:shownote();">{$lang->naoardonate_front_writenote}<\/a> <em> {$lang->naoardonate_front_optional}<\/em><\/div>\');
    //-->
    </script>
    <div style="display:none" id="divnote"><textarea class="w100" cols="33" rows="5" name="note" onkeyup="limit()" onkeypress="limit()">{$note}</textarea><br/><em><span id="max">100</span>{$lang->naoardonate_front_charsleft}</em></div>

    <noscript><textarea cols="33" rows="5" name="note" class="w100">{$note}</textarea></noscript>

    </td>
</tr>

</table>
</fieldset>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



        $templates_array[] = array(
        'title' => 'naoardonate_donate_captcha_v5',
        'template' => $db->escape_string('<fieldset class="w50 tleft" >
<script type="text/javascript" src="jscripts/captcha.js?ver=1400"></script>
<legend><strong>Image verification</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
    <tr>
        <td  style="padding-left:30%"><img src="captcha.php?action=regimage&amp;imagehash={$imagehash}" alt="{$lang->image_verification}" title="{$lang->naoardonate_front_refresh}" id="captcha_img" onmouseover="this.style.cursor=\'help\';"  onclick="return captcha.refresh();"  width="231" />
        </td>

    </tr>
        <tr>
<td  style="padding-left:30%"><input type="text" name="imgstr" value="" style="width:227px" />  <input type="hidden" name="imagehash" value="{$imagehash}" id="imagehash" />    </td>

    </tr>

</table>
</fieldset>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



    $templates_array[] = array(
        'title' => 'naoardonate_donate_v7',
        'template' => $db->escape_string('<html>
<head>
{$googleanalytics}
<title>{$mybb->settings[\'bbname\']} - {$donation_page_title} </title>
{$headerinclude}
<style type="text/css">
.w70 {
    width:70%
}
.w50 {

width:50%
}
.w100 {

width:100%
}
.w80 {
width:80%
}
.tleft {

text-align:left

}
em {
color:gray;
font-size:x-small
}
</style>
</head>
<body onload="load()">
{$header}
{$errors}
<form action="donate.php" method="post" name="naoar" {$submit_ifvalid}>
<table class="tborder" border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" >
<tr>
<td colspan="2" class="thead">
<strong> {$donationform_title} </strong>
</td></tr>

<tr>
<td  class="trow1 w100"  align="center">
{$aboutyou}
<fieldset class="w50 tleft">
<legend><strong>{$donationdetails_title}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" class="w100">
    <tr>
        <td><strong>{$lang->naoardonate_global_payment_method}:</strong>
        </td>
            <td class="w70"><select onchange="change_payment_method()" name="payment_method" class="w100">
                {$payment_methodselect}
                </select>
        </td>

    </tr>
    <tr>
        <td valign="top"><strong>{$lang->naoardonate_global_amount}:</strong>
        </td>
            <td class="w70">{$p_amount}{$c_amount}
        </td>

    </tr>
    {$currencies_row}

</table>
</fieldset>
{$offline_options}
{$note_fieldset}
{$captcha}

    </td></tr>
        <tr>
        <td align="center">
        <input type="hidden" name="coderme_post_key" value="{$coderme_post_key}">
        <input type="submit" name ="submit" value="   {$lang->naoardonate_global_go}   " />
        </td>

    </tr>
</table>
</form>
<div class="modal" id="coderme_alert" style="display: none;">
<table width="100%" cellspacing="0" cellpadding="5" border="0" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->naoardonate_front_msg}</strong></td>
                    </tr>
                    <tr>
                <td class="trow1" colspan="2" id="coderme_msg"></td>
    </tr>
</table>
</div>
<script>
a=document.naoar;f=a.p_amount;d=document;function load(){change_payment_method();{$js_load}}
{$js_updatelist}{$js_funcs}
function coderme_alert(msg){
jQuery("#coderme_msg").html(msg);
jQuery("#coderme_alert").modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== "undefined" ? modal_zindex : 9999) });
}</script>

<br style="clear: both" />
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );




    $templates_array[] = array(
        'title' => 'naoardonate_top_donation_v5',
        'template' => $db->escape_string('<tr>
    <td class="trow1" align="center">
        {$top_donors[\'name\']}
    </td>

    <td align="center" class="trow2">
        {$top_donors[\'real_amount\']}
    </td>

    <td align="center" class="trow1">
    {$top_donors[\'payment_method\']}
    </td>

    <td class="trow2" align="center">
    {$top_donors[\'email\']}
    </td>

    <td align="center" class="trow1">
        {$top_donors[\'dateline\']}
    </td>

</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );


    $templates_array[] = array(
        'title' => 'naoardonate_notice_v5',
        'template' => $db->escape_string('<div style="background-color:#EFDFF5;border:thin #D88CF4 solid;text-align:center;padding:1px">
<span style="color:red;font-weight:bolder;font-size:larger;background-color:yellow;padding:3px;border:thin red solid">{$unconfirmed_donors}</span>
<span style="font-weight:bolder">
{$lang->naoardonate_front_waitingyouraction}
</span>,
{$lang->naoardonate_front_formoreinfo}
<a href="{$pathtoadmin}" target="_blank" title="new page" style="color:blue;text-decoration:underline">
{$lang->naoardonate_front_clickhere}
</a>
</div><br />'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );



    $templates_array[] = array(
        'title' => 'naoardonate_redirect_v5',
        'template' => $db->escape_string('
<!DOCTYPE html> <head>
 <meta charset="utf-8">
<title>$lang->naoardonate_front_redirect</title> </head> <body onload="document.naoardonate.submit()">
<form name="naoardonate" action="$url" method="$method">
 <div> 
   <input type="hidden" name="$merchant_name" value="$merchant_value" />
   <input type="hidden" name="$amount_name" value="$amount" />
   <input type="hidden" name="$currency_name" value="$currency" />
   $cancel_url
   <input type="hidden" name="$return_name" value="{$mybb->settings[\'bburl\']}/donate.php?action=thank_you" />
 $additional
   <noscript><div style="padding-top:23%;text-align:center"><button type="submit">{$lang->naoardonate_front_continuebutton}</button></div></noscript>
   </div>
    </form>
     </body>
     </html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );


    $templates_array[] = array(
        'title' => 'naoardonate_top_v5',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->naoardonate_front_top_title} </title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="clear: both;">
    <tr>
        <td class="thead" colspan="5">
            <div>
                <strong>{$lang->naoardonate_front_top_title}</strong>
            </div>
        </td>
    </tr>
    <tr>
        <th align="center" class="tcat" width="15%" ><span class="smalltext"><strong>{$lang->naoardonate_global_name}</strong></span>
        </th>
        <th align="center" class="tcat"  width="15%"><span class="smalltext"><strong>{$lang->naoardonate_global_amount}</strong></span>
        </th>
        <th align="center" class="tcat"  width="15%"><span class="smalltext"><strong>{$lang->naoardonate_global_payment_method}</strong></span>
        </th>

        <th align="center" class="tcat"  width="30%" ><span class="smalltext"><strong>{$lang->naoardonate_front_email}</strong></span>
        </th>

        <th align="center" class="tcat"  width="25%"><span class="smalltext"><strong>{$lang->naoardonate_global_date}</strong></span>
        </th>
    </tr>
    {$donations}
    <tr>
        <td class="tfoot" colspan="5">
            </td>
    </tr>
</table>
<br style="clear: both" />

{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );

    foreach($templates_array as $template):
        $db->insert_query("templates", $template);
    endforeach;
}

 #    _deactivate():
 #    Called whenever a plugin is deactivated. This should essentially "hide" the plugin from view
 #    by removing templates/template changes etc. It should not, however, remove any information
 #    such as tables, fields etc - that should be handled by an _uninstall routine. When a plugin is
 #    uninstalled, this routine will also be called before _uninstall() if the plugin is active.


function naoardonate_deactivate()
{
    global $db;

    include_once MYBB_ROOT . "inc/adminfunctions_templates.php";

    find_replace_templatesets("header", "#".preg_quote('{$naoardonate_notice}')."#i", '',0);
    find_replace_templatesets("header", "#".preg_quote('{$naoardonate_bar}')."#i", '',0);
    find_replace_templatesets("header", "#".preg_quote('{$naoardonate_donatelink}')."#i", '',0);
    find_replace_templatesets("footer", "#".preg_quote('{$naoar_copyright}')."#i", '', 0);

    $db->write_query("DELETE FROM ".TABLE_PREFIX."templates WHERE title LIKE 'naoardonate%'");
}


function naoar_showhide(){
global $naoardonate_id;

 sprintf('%.1f', $GLOBALS['mybb']->version) == 1.4 ? $sep = '/' : $sep = '-';
 $j = '<script type="text/javascript">
        <!--
';
 if(stripos($_SERVER['QUERY_STRING'],"module=config{$sep}settings&action=change") !== false)
 {
    $j .= <<<NAOARDONATE_SHOWHIDE
$(document).ready(function() {
t_load();
});
function t_load(){
  if(t_ischecked('naoardonate_off')) {  t_hide('payment_method', 1); t_hide('payment_method_2c', 1); t_hide('payment_method_pp', 1);t_hide('payment_method_bk', 1); t_hide('payment_method_wu', 1);t_hide('enablebar', 1); t_hide('reason', 1); t_hide('target', 1); t_hide('duration', 1); t_hide('ifreached', 1); t_hide('amount', 1); t_hide('from', 1); t_hide('alert', 1); t_hide('info', 1); t_hide('info_required', 1); t_hide('bar_width', 1); t_hide('newgoal', 1); t_hide('recievedmsg', 1);
t_hide('recievedmsg_100', 1); t_hide('currency', 1); t_hide('donorsgroup', 1); t_hide('unmovable', 1); t_hide('cannotviewtop', 1);t_hide('hidetopemails', 1); t_hide('donormsg', 1); t_hide('captcha', 1); t_hide('googleanalytics', 1); t_hide('premium', 1)}  else { t_onchange('naoardonate_bk','payment_method_bk');  t_onchange('naoardonate_wu','payment_method_wu'); t_onchange('naoardonate_2c','payment_method_2c'); t_onchange('naoardonate_pp','payment_method_pp'); t_enablebar(); t_hide('payment_method'); t_hide('enablebar'); t_hide('amount'); t_hide('from'); t_hide('alert'); t_hide('info'); t_hide('info_required'); t_hide('currency'); t_hide('donorsgroup'); t_hide('unmovable'); t_hide('cannotviewtop');t_hide('hidetopemails'); t_hide('donormsg'); t_hide('captcha'); t_hide('googleanalytics');
t_hide('premium')} };
function t_hide(id, hide) {
id = 'row_setting_naoardonate_' + id; var t_el = document.getElementById(id); if(hide) { t_el.style.display = 'none'}  else {t_el.style.display = ''} }  function t_onchange(id,h)  { if(t_ischecked(id)) {  t_hide(h, 0) } else {  t_hide(h, 1) } }  function t_ischecked(id) {  return document.getElementById(id).checked  }  function t_enablebar() {  if(t_ischecked('naoardonate_enablebar_off')) {  t_hide('reason',1); t_hide('target',1);
t_hide('duration',1); t_hide('bar_width', 1); t_hide('newgoal', 1); t_hide('recievedmsg', 1); t_hide('recievedmsg_100', 1); t_hide('ifreached',1)}  else {  t_hide('reason'); t_hide('target'); t_hide('duration'); t_hide('bar_width'); t_hide('newgoal'); t_hide('recievedmsg'); t_hide('recievedmsg_100'); t_hide('ifreached')}}
NAOARDONATE_SHOWHIDE;
    }

    if($naoardonate_id)
    {
        $j .= 'document.getElementById("naoardonate").innerHTML = "<b><a href=\'index.php?module=config' . $sep .'settings&amp;action=change&amp;gid=' . $naoardonate_id . '\' style=\'padding:3px 9px; background-color: #656161;border-radius: 7px;color: white;\'>Edit Settings</a></b>";';

    }

    $j .= "\n//-->\n" . '</script>';

    print $j;
}


function naoar_showdonatelinks()
{
    global $mybb, $db, $theme, $templates, $cache, $lang, $naoardonate_bar, $naoardonate_donatelink, $naoar_copyright, $googleanalytics, $naoardonate_notice;

    # work around not ready template
    $theme['templateset'] = 1;

    $lang->load('naoardonate_front');

    $unconfirmed_donors = (int)$cache->read('naoardonate_unconfirmed');

    $naoardonate_notice = $left_div = $naoardonate_top = $naoardonate_reason = $naoardonate_donatelink = $naoardonate_bar = $naoar_copyright = '';

    if($mybb->user['usergroup'] == 4 and $unconfirmed_donors > 0 and $mybb->settings['naoardonate_alert'] == 'notice') {
        require_once  MYBB_ROOT . $mybb->config['admin_dir']
                                . '/inc/functions.php';
        
        $permissions = get_admin_permissions($mybb->user['uid'], 4);

        if($mybb->user['uid'] == 1 || isset($permissions['coderme_donors'])) {
            sprintf('%.1f', $mybb->version) == 1.4 ? $sep = '/' :  $sep = '-';
        $pathtoadmin = $mybb->settings['bburl'] . '/' . $mybb->config['admin_dir'] . '/index.php?module=coderme_donors' . $sep . 'browse&amp;action=unconfirmed';
            eval("\$naoardonate_notice = \"" . $templates->get('naoardonate_notice_v5') . "\";");
        } 
    }
    
    

    $googleanalytics = $mybb->settings['naoardonate_googleanalytics'];
    $naoardonate_from = explode(',',$mybb->settings['naoardonate_from']);


    if(!in_array($mybb->user['usergroup'], $naoardonate_from) or !$db->table_exists('naoardonate') or $mybb->settings['naoardonate_onoff'] == 0 or (!$mybb->settings['naoardonate_payment_method_2c'] and !$mybb->settings['naoardonate_payment_method_pp']) or strlen($mybb->settings['naoardonate_payment_method']) < 5) return; # yeah better now than later ..b
    $amount = intval($cache->read('naoardonate_goal'));
    eval('$naoardonate_donatelink = "'. $templates->get('naoardonate_links_donate_v5') . '";');
    $blocked_groups = explode(',',$mybb->settings['naoardonate_cannotviewtop']);

    if(!in_array($mybb->user['usergroup'],$blocked_groups))
    {
        eval('$naoardonate_donatelink .= "' . $templates->get('naoardonate_links_topdonors_v5') . '";');
    }

    if($mybb->settings['naoardonate_enablebar'] == 1 and !($mybb->settings['naoardonate_ifreached'] == 1 and ($amount >= $mybb->settings['naoardonate_target'] or $mybb->settings['naoardonate_duration'] <= time() and $mybb->settings['naoardonate_duration'] != 0) or $mybb->settings['naoardonate_target'] == 0))
    {
        if($mybb->settings['naoardonate_reason']){
            eval('$naoardonate_reason = "' . $templates->get('naoardonate_reason_v5'). '";');
        }
        if(!in_array($mybb->user['usergroup'],$blocked_groups)){
            eval('$naoardonate_top = "' . $templates->get('naoardonate_img_topdonors_v5') . '";');
        }
        $widths = explode('/', $mybb->settings['naoardonate_bar_width']);
        $container_width = $widths[0];
        $bar_width = $widths[1];
        if ($amount >= (int)$mybb->settings['naoardonate_target'])
        {
            $werecieved_msg = $mybb->settings['naoardonate_recievedmsg_100'];
            eval('$tail_tip = "' . $templates->get('naoardonate_bar_tailtip_v5') . '";');

            $progress_value = $bar_width - 12 - 12; # 705 - 13 - 13 # 687
        }
        else
        {
            $werecieved_msg = $lang->sprintf($mybb->settings['naoardonate_recievedmsg'], '' . intval($amount ? $amount/$mybb->settings['naoardonate_target'] * 100 : 0 ) . '%');

            eval('$tip_tail = "' . $templates->get('naoardonate_tiptail_v5') . '";');

            $progress_value = intval(($bar_width - 12 - 8 -12)  * $amount / $mybb->settings['naoardonate_target']); # 705 - 12 - 8 - 12  | 674
            $left_value = $bar_width - 12 - 8 -12 - $progress_value;
            eval('$left_div = "' . $templates->get('naoardonate_leftdiv_v5') . '";');
        }
        eval("\$naoardonate_bar = \"".$templates->get('naoardonate_bar_v5')."\";");
    }


    if(stripos($_SERVER['SCRIPT_NAME'],'donate.php') !== false or $naoardonate_bar) {
        $naoar_copyright = '<!-- CoderMe Copyright -- Keeping this copyright notice intact is REQUIRED for legal usage -->
<div class="coderme-copyright" style="text-align: center; padding: 1rem 0 2rem 0">
 Donations by <a href="https://coderme.com/mybb-donation-gold" target="_blank" rel="noopener">CoderMe</a>
</div>
<!-- CoderMe Copyright -->';
    }

}


function naoar_fixit()
{
    global $mybb, $db, $cache, $naoardonate_groups, $naoardonate_blockedgroups,$naoardonate_unmovablegroups, $naoardonate_fromgroups;
    $select = $db->simple_select('settinggroups', 'gid' , "name = 'naoardonate'", array('limit'=>1));
    $gid = $db->fetch_field($select, 'gid');

    if($gid == $mybb->input['gid'] and $mybb->request_method == "get")
    {
    $naoar_groups = $cache->read('usergroups');

    $naoar_block = explode(',', $mybb->settings['naoardonate_cannotviewtop']);
    $naoar_unmovable = explode(',', $mybb->settings['naoardonate_unmovable']);
    $naoar_from = explode(',', $mybb->settings['naoardonate_from']);

    foreach($naoar_groups as $k => $v)
    {
        if($mybb->settings['naoardonate_donorsgroup'] == $k)
        {
            $naoardonate_groups .= "<option value=\"$k\" selected=\"selected\">{$naoar_groups[$k]['title']}</option>";

        }
        else
        {
            $naoardonate_groups .= "<option value=\"$k\">{$naoar_groups[$k]['title']}</option>";

        }

        if(in_array($k, $naoar_block))
        {
            $naoardonate_blockedgroups .= "<label for=\"naoardonate_cannotviewtop_$k\"><input type=\"checkbox\" checked=\"checked\" id=\"naoardonate_cannotviewtop_$k\" name=\"upsetting[naoardonate_cannotviewtop][]\" value=\"$k\" />{$naoar_groups[$k]['title']}</label><br />";
        }
        else
        {
            $naoardonate_blockedgroups .= "<label for=\"naoardonate_cannotviewtop_$k\"><input type=\"checkbox\" name=\"upsetting[naoardonate_cannotviewtop][]\" id=\"naoardonate_cannotviewtop_$k\" value=\"$k\" />{$naoar_groups[$k]['title']}</label><br />";

        }


        if(in_array($k, $naoar_unmovable))
        {
            $naoardonate_unmovablegroups .= "<label for=\"naoardonate_unmovable_$k\"><input type=\"checkbox\" checked=\"checked\" id=\"naoardonate_unmovable_$k\" name=\"upsetting[naoardonate_unmovable][]\" value=\"$k\" />{$naoar_groups[$k]['title']}</label><br />";
        }
        else
        {
            $naoardonate_unmovablegroups .= "<label for=\"naoardonate_unmovable_$k\"><input type=\"checkbox\" name=\"upsetting[naoardonate_unmovable][]\" id=\"naoardonate_unmovable_$k\" value=\"$k\" />{$naoar_groups[$k]['title']}</label><br />";

        }


        if(in_array($k, $naoar_from))
        {
            $naoardonate_fromgroups .= "<label for=\"naoardonate_from_$k\"><input type=\"checkbox\" checked=\"checked\" id=\"naoardonate_from_$k\" name=\"upsetting[naoardonate_from][]\" value=\"$k\" />{$naoar_groups[$k]['title']}</label><br />";
        }
        else
        {
            $naoardonate_fromgroups .= "<label for=\"naoardonate_from_$k\"><input type=\"checkbox\" name=\"upsetting[naoardonate_from][]\" id=\"naoardonate_from_$k\" value=\"$k\" />{$naoar_groups[$k]['title']}</label><br />";

        }



    }


    }
    if($mybb->request_method == "post" and $gid == $mybb->input['gid'])
    {

        $mybb->input['upsetting']['naoardonate_payment_method'] = @implode(',', $mybb->input['upsetting']['naoardonate_payment_method']);

        $mybb->input['upsetting']['naoardonate_cannotviewtop'] = @implode(',', $mybb->input['upsetting']['naoardonate_cannotviewtop']);

        $mybb->input['upsetting']['naoardonate_unmovable'] = @implode(',', $mybb->input['upsetting']['naoardonate_unmovable']);

        $mybb->input['upsetting']['naoardonate_from'] = @implode(',', $mybb->input['upsetting']['naoardonate_from']);


        # reset the counter
        if($mybb->input['upsetting']['naoardonate_newgoal'] == 1){
            $cache->update('naoardonate_goal', 0);
            $cache->update('naoardonate_unconfirmed', 0);
        }

        if($mybb->input['upsetting']['naoardonate_duration'] > 0)
        {
            $mybb->input['upsetting']['naoardonate_duration'] = '+' . (int) $mybb->input['upsetting']['naoardonate_duration'] . ' days';
            $mybb->input['upsetting']['naoardonate_duration'] = strtotime($mybb->input['upsetting']['naoardonate_duration']);
        }
        elseif($mybb->input['upsetting']['naoardonate_duration'] <= 0)
        { # string will evaluate to zero if not numeric
            $mybb->input['upsetting']['naoardonate_duration'] = 0;
        }
    }
}


function naoar_alert()
{
    global $mybb, $cache, $db, $lang;

    if($mybb->settings['naoardonate_alert'] == 'disabled')
    {
        # clean every thing
        if($cache->cache['naoardonate_unconfirmed'])
        {
            if(is_object($cache->handler)) $cache->handler->delete('naoardonate_unconfirmed');
            $db->delete_query("datacache", "title = 'naoardonate_unconfirmed'");
        }

        return; # save some unneeded work
    }

    $unconfirmed = (int)$cache->read('naoardonate_unconfirmed');
    $cache->update('naoardonate_unconfirmed',++$unconfirmed);
    if($mybb->settings['naoardonate_alert'] == 'email' and $mybb->settings['adminemail'] and $unconfirmed > 0)
    {
        sprintf('%.1f', $mybb->version) == 1.4 ? $sep = '/' : $sep = '-';
        my_mail($mybb->settings['adminemail'], $lang->sprintf($lang->naoardonate_front_unconfirmed_emailsubject,$unconfirmed), $lang->sprintf($lang->naoardonate_front_unconfirmed_emailhtmlmessage,$unconfirmed, $mybb->settings['bburl'] . '/' . $mybb->config['admin_dir'] . "/index.php?module=coderme_donors" . $sep . "browse&mp;action=unconfirmed"), '', '', '', false , 'html' ,$lang->sprintf($lang->naoardonate_front_unconfirmed_emailtextmessage,$unconfirmed, $mybb->settings['bburl'] . '/' . $mybb->config['admin_dir'] . "/index.php?module=coderme_donors" . $sep . "browse&mp;action=unconfirmed"));
    }
}


# get plugin settings number
function naoardonate_getid()
{
    global $db, $naoardonate_id;

    $select = $db->simple_select('settinggroups', 'gid' , "name = 'naoardonate'", array('limit'=>1));
    $naoardonate_id = $db->fetch_field($select, 'gid');
    if(!$naoardonate_id) $naoardonate_id = 0;
}


function naoar_donationpage_online(&$plugin_array)
{
    global $mybb, $lang;
    $lang->load('naoardonate_front');

    if (preg_match('/donate\.php/',$plugin_array['user_activity']['location']))
    {
        $plugin_array['location_name'] = $lang->sprintf($lang->naoardonate_front_online,'donate.php');
    }

    return $plugin_array;
}

