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
