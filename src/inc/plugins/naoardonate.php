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
