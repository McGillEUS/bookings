<?php
// $Id$

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require 'vendor/autoload.php';

// Deletes an entry, or a series.    The $id is always the id of
// an individual entry.   If $series is set then the entire series
// of wich $id is a member should be deleted. [Note - this use of
// $series is inconsistent with use in the rest of MRBS where it
// means that $id is the id of an entry in the repeat table.   This
// should be fixed sometime.]

function checkGoogleLogin ($info) {

    set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ .'/gplus/vendor/google/apiclient/src');

    require_once __DIR__.'/gplus/vendor/autoload.php';

    $CLIENT_ID = '';
    $CLIENT_SECRET = '';
    $APPLICATION_NAME = "McGill EUS MRBS";
//$redirect_uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $state = "$_SERVER[REQUEST_URI]";

    if (!isset($_GET['code'])) {
        $state = str_replace("/booking/del_entry.php?", "", $state);
        $state = str_replace("del_entry.php?", "", $state);
        $state = base64_encode($state);
    }

    $redirect_uri = "http://" . $_SERVER[HTTP_HOST] . "/del_entry.php";

    $client = new Google_Client();
    $client->setApplicationName($APPLICATION_NAME);
    $client->setClientId($CLIENT_ID);
    $client->setClientSecret($CLIENT_SECRET);
    $client->setRedirectUri($redirect_uri);
    $client->addScope("https://www.googleapis.com/auth/userinfo.email");
    $client->addScope(array('https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/userinfo.profile'));
    $client->setState($state);

    $plus = new Google_Service_Plus($client);

    //Authenticate code from Google OAuth Flow
    //Add Access Token to Session
    if (isset($_GET['code'])) {
        $client->authenticate($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        //header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    }

    //Set Access Token to make Request
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
    }

    //Get User Data from Google Plus
    //If New, Insert to Database
    if ($client->getAccessToken()) {
        //$userData = $objOAuthService->userinfo->get();
        try {
            $me = $plus->people->get('me'); # HOW TO SPECIFY FIELDS?
            $userData = $plus->people->get('me');
            if(!empty($userData)) {
                $userEmail = ($me['emails'][0]['value']);
                if ($userEmail != $info['Email']) {
                    header("Location: " . "http://" . $_SERVER[HTTP_HOST] . "/need_auth_to_edit.php"); /* Redirect browser */
                    exit();
                }
            }
        } catch (Google_Exception $e) {
            //$token = json_decode($app['session']->get('token'))->access_token;
            $token = json_decode($_SESSION['access_token'])->access_token;
            $client->revokeToken($token);
            // Remove the credentials from the user's session.
            //unset($_SESSION['access_token']);
            $_SESSION = array();

            header("Location: " . $_SERVER[HTTP_HOST]); /* Redirect browser */
            exit();
        }

        //$_SESSION['access_token'] = $client->getAccessToken();
    } else {
        $authUrl = $client->createAuthUrl();
        header("Location: " . $authUrl); /* Redirect browser */
        exit();
    }
}

if (isset($_GET['state'])) {
    $state_values = $_GET['state'];
    $state_values = str_replace("=", "", $state_values);
    $state_values = base64_decode($state_values);
    $state_values = str_replace("/", "", $state_values);

    $temp = parse_url("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    parse_str(str_replace("%3D", "", $temp["query"]));

    $state_values = $state_values . "&code=" . str_replace("#", "",$code);

    $temp_url = "http://$_SERVER[HTTP_HOST]" . "/del_entry.php?" . $state_values . "&test=post-state";
    $state_values = null;
    $temp = null;
    header("Location: " . $temp_url); /* Redirect browser */
    $temp_url = null;
    exit();

}

require "defaultincludes.inc";
require_once "mrbs_sql.inc";

// Get non-standard form variables
$id = get_form_var('id', 'int');
$series = get_form_var('series', 'int');
$returl = get_form_var('returl', 'string');
$action = get_form_var('action', 'string');
$note = get_form_var('note', 'string', '');

// Check the user is authorised for this page
//heckAuthorised();

if (empty($returl))
{
  switch ($default_view)
  {
    case "month":
      $returl = "month.php";
      break;
    case "week":
      $returl = "week.php";
      break;
    default:
      $returl = "day.php";
  }
  $returl .= "?year=$year&month=$month&day=$day&area=$area";
}

if ($info = get_booking_info($id, FALSE, TRUE))
{
    $user = getUserName();
    if (!(authGetUserLevel($user) >= 2)) {
      checkGoogleLogin($info);
  }
  // check that the user is allowed to delete this entry
  if (isset($action) && ($action="reject"))
  {
    $authorised = auth_book_admin($user, $info['room_id']);
  }
  else
  {
    $authorised = getWritable($info['create_by'], $user, $info['room_id']);
  }
  if ($authorised)
  {
    $day   = strftime("%d", $info["start_time"]);
    $month = strftime("%m", $info["start_time"]);
    $year  = strftime("%Y", $info["start_time"]);
    $area  = mrbsGetRoomArea($info["room_id"]);
    // Get the settings for this area (they will be needed for policy checking)
    get_area_settings($area);

    // EUS IT - send an email if the booking was deleted (or rejected) - NOVEMBER 2017
    // ADDITION FROM EUS IT DIRECTOR - NOVEMBER 2017
      /* This sends an email to the email address that made the
         booking, explaining that their booking was rejected. Note that
         this does not use the built-in MRBS email system.
      */

      try {
        $mail = new PHPMailer(true); 

        $mail->isSMTP();                           // Set mailer to use SMTP
        $mail->SMTPDebug = 2;                      // enable verbose debugging (uncomment when debugging)
        $mail->SMTPSecure = 'tls';
        $mail->Port = '587';
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'booking.approval@mcgilleus.ca';
        // NOTE: if this password gets changed (e.g. in yearly password reset), it also needs to be changed here!
        $mail->Password = '';
        
        $mail->SetFrom('booking.approval@mcgilleus.ca', 'EUS Booking Approval System');
        $mail->addAddress($info['Email']);   // Name of person is optional => left out

        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = "[EUS Booking System] - Booking Deleted or Rejected";

        // TODO - change this to a rejection email in an HTML file
        // $mail->Body = file_get_contents('emails/pipeline.html');
        $mail->Body = "Your booking has been rejected or deleted. This action was performed by you or an administrator. Contact space.manager@mcgilleus.ca with any questions.";

        $mail->send();       // TODO: uncomment when ready to send mail
      } catch (Exception $e) {
       // don't do anything (yet...)
      }
      // END OF EUS IT ADDITION

    
    $notify_by_email = $mail_settings['on_delete'] && $need_to_send_mail;

    if ($notify_by_email)
    {
      require_once "functions_mail.inc";
      // Gather all fields values for use in emails.
      $mail_previous = get_booking_info($id, FALSE);
      // If this is an individual entry of a series then force the entry_type
      // to be a changed entry, so that when we create the iCalendar object we know that
      // we only want to delete the individual entry
      if (!$series && ($mail_previous['rep_type'] != REP_NONE))
      {
        $mail_previous['entry_type'] = ENTRY_RPT_CHANGED;
      }
    }
    sql_begin();
    $start_times = mrbsDelEntry(getUserName(), $id, $series, 1);
    sql_commit();

    // [At the moment MRBS does not inform the user if it was not able to delete
    // an entry, or, for a series, some entries in a series.  This could happen for
    // example if a booking policy is in force that prevents the deletion of entries
    // in the past.   It would be better to inform the user that the operation has
    // been unsuccessful or only partially successful]
    if (($start_times !== FALSE) && (count($start_times) > 0))
    {
      // Send a mail to the Administrator
      if ($notify_by_email)
      {
        // Now that we've finished with mrbsDelEntry, change the id so that it's
        // the repeat_id if we're looking at a series.   (This is a complete hack, 
        // but brings us back into line with the rest of MRBS until the anomaly
        // of del_entry is fixed) 
        if ($series)
        {
          $mail_previous['id'] = $mail_previous['repeat_id'];
        }
        if (isset($action) && ($action == "reject"))
        {
          $result = notifyAdminOnDelete($mail_previous, $series, $start_times, $action, $note);
        }
        else
        {
          $result = notifyAdminOnDelete($mail_previous, $series, $start_times);
        }
      }

    }
    Header("Location: $returl");
    exit();
  }
}

// If you got this far then we got an access denied.
showAccessDenied($day, $month, $year, $area, "");

