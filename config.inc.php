<?php

// $Id: config.inc.php 2211 2011-12-24 09:27:00Z cimorrison $

/**************************************************************************
 *   MRBS Configuration File
 *   Configure this file for your site.
 *   You shouldn't have to modify anything outside this file.
 *
 *   This file has already been populated with the minimum set of configuration
 *   variables that you will need to change to get your system up and running.
 *   If you want to change any of the other settings in systemdefaults.inc.php
 *   or areadefaults.inc.php, then copy the relevant lines into this file
 *   and edit them here.   This file will override the default settings and
 *   when you upgrade to a new version of MRBS the config file is preserved.
 **************************************************************************/

/**********
 * Timezone
 **********/
 
// The timezone your meeting rooms run in. It is especially important
// to set this if you're using PHP 5 on Linux. In this configuration
// if you don't, meetings in a different DST than you are currently
// in are offset by the DST offset incorrectly.
//
// Note that timezones can be set on a per-area basis, so strictly speaking this
// setting should be in areadefaults.inc.php, but as it is so important to set
// the right timezone it is included here.
//
// When upgrading an existing installation, this should be set to the
// timezone the web server runs in.  See the INSTALL document for more information.
//
// A list of valid timezones can be found at http://php.net/manual/timezones.php
// The following line must be uncommented by removing the '//' at the beginning
$timezone = "America/Toronto";


/*******************
 * Database settings
 ******************/
// Which database system: "pgsql"=PostgreSQL, "mysql"=MySQL,
// "mysqli"=MySQL via the mysqli PHP extension
$dbsys = "mysql";
// Hostname of database server. For pgsql, can use "" instead of localhost
// to use Unix Domain Sockets instead of TCP/IP.
$db_host = "localhost";
// Database name:
$db_database = "booking";
// Database login user name:
$db_login = "potato";
// Database login password:
$db_password = 'potato_pw';
// Prefix for table names.  This will allow multiple installations where only
$db_tbl_prefix = "mrbs_";
// Uncomment this to NOT use PHP persistent (pooled) database connections:
// $db_nopersist = 1;


/* Add lines from systemdefaults.inc.php and areadefaults.inc.php below here
   to change the default configuration. Do _NOT_ modify systemdefaults.inc.php
   or areadefaults.inc.php.  */
$max_duration_enabled = TRUE;
$max_duration_secs = 10800;
$vocab_override["en"]["type.I"] = "McConnell Engineering Building";
$vocab_override["en"]["type.E"] = "Building";
$vocab_override['en']["room.alcohol"] = "Will Alcohol be served? (Common Room only)";
$vocab_override['en']["room.audio"] = "Do you need a projector?";
$auth["session"] = "php";
$auth["type"] = "db";
$mrbs_company = "The McGill EUS";
$approval_enabled = FALSE;
$reminders_enabled = FALSE;
$confirmation_enabled = FALSE;
/*
$auth["session"] = "php";
$auth["type"] = "db";
$mrbs_company = "";
$auth['only_admin_can_book_repeat'] = TRUE;
$auth['only_admin_can_select_multiroom'] = TRUE;
$vocab_override["en"]["type.I"] = "McConnell Engineering Building";
$vocab_override["en"]["type.E"] = "Building";
$is_mandatory_field['entry.Email'] = true;
//$hidden_days = array(0,6);
$max_duration_enabled = TRUE;
$max_duration_secs = 10800;
if (isset($_COOKIE['lang'])){
	$disable_automatic_language_changing = 1;
	$default_language_tokens = $_COOKIE['lang'];
	  if ($_COOKIE['lang']=="en") {
	  $override_locale = "en_US.utf-8";
	  }
	  elseif ($_COOKIE['lang']=="fr") {
	  $override_locale = "fr_FR.utf-8";
	  }
	  elseif ($_COOKIE['lang']=="ja") {
	  $override_locale = "ja_JP.utf-8";
	  }
	  else {
	  $override_locale = "zh_CN.utf-8";
	  }
}
?>
*/
