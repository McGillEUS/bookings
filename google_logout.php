<?php
/**
 * Created by PhpStorm.
 * User: Lou
 * Date: 2017-02-03
 * Time: 4:57 PM
 */

//the session variable doesn't load correctly if this is not included - McGill EUS MOD
require "defaultincludes.inc";

//this wipes out the current users session data - McGill EUS Mod
$_SESSION = array();

header("Location: http://" . $_SERVER[HTTP_HOST]); /* Redirect browser */

exit();