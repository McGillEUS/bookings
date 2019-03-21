<?php
// $Id$

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require 'vendor/autoload.php';

// Handles actions on bookings awaiting approval

require "defaultincludes.inc";
require_once "mrbs_sql.inc";
require_once "functions_mail.inc";

// Get non-standard form variables
$action = get_form_var('action', 'string');
$id = get_form_var('id', 'int');
$series = get_form_var('series', 'int');
$returl = get_form_var('returl', 'string');
$note = get_form_var('note', 'string');


// Check the user is authorised for this page
checkAuthorised();
$user = getUserName();

// Retrieve the booking details
$data = get_booking_info($id, $series);
$room_id = $data['room_id'];

// Initialise $mail_previous so that we can use it as a parameter for notifyAdminOnBooking
$mail_previous = array();
$start_times = array();

// Give the return URL a query string if it doesn't already have one
if (strpos($returl, '?') === FALSE)
{
  $returl .= "?year=$year&month=$month&day=$day&area=$area&room=$room";
}

                  
if (isset($action))
{                     
  if ($need_to_send_mail)
  { 
    $is_new_entry = TRUE;  // Treat it as a new entry unless told otherwise    
  }
  
  // If we have to approve or reject a booking, check that we have rights to do so
  // for this room
  if ((($action == "approve") || ($action == "reject")) 
       && !auth_book_admin($user, $room_id))
  {
    showAccessDenied($day, $month, $year, $area, isset($room) ? $room : "");
    exit;
  }
  
  switch ($action)
  {
    // ACTION = "APPROVE"
    case 'approve':
      if ($need_to_send_mail)
      {
        $is_new_entry = FALSE;
        // Get the current booking data, before we change anything, for use in emails
        $mail_previous = get_booking_info($id, $series);
      }
      $start_times = mrbsApproveEntry($id, $series);
      $result = ($start_times !== FALSE);
      if ($result === FALSE)
      {
        $returl .= "&error=approve_failed";
      }

      // ADDITION FROM EUS IT DIRECTOR - OCTOBER 2017
      /* This sends an email to the email address that made the
         booking, confirming that their booking is approved and
         adding details relevant to the specific room. Note that
         this does not use the built-in MRBS email system.
      */
       // get details of this booking
       $booking_details = get_booking_info($id, $series);

      try {
  			$mail = new PHPMailer(true); 

  			$mail->isSMTP();                           // Set mailer to use SMTP
  			// $mail->SMTPDebug = 2;                      // enable verbose debugging (uncomment when debugging)
  			$mail->SMTPSecure = 'tls';
  			$mail->Port = '587';
  			$mail->Host = 'smtp.gmail.com';
  			$mail->SMTPAuth = true;                               // Enable SMTP authentication
  			$mail->Username = 'booking.approval@mcgilleus.ca';
  			// NOTE: if this password gets changed (e.g. in yearly password reset), it also needs to be changed here!
  			$mail->Password = '';
  			
  			$mail->SetFrom('booking.approval@mcgilleus.ca', 'EUS Booking Approval');
        	// $mail->addAddress($booking_details['Email']);   // Name of person is optional => left out
  			$mail->addAddress('it.director@mcgilleus.ca');   // all email to here for testing, remove when ready to go

  			$mail->isHTML(true);                                  // Set email format to HTML

  			$mail->Subject = "[EUS Booking System] - Booking Approved";

        	// determine which email the person should be sent
  			/*switch($booking_details['room_id']) {
  				case 1:

  					break;
  			}*/

  			$mail->Body    = file_get_contents('emails/pipeline.html');

  			// $mail->send();       // TODO: uncomment when ready to send mail
		  } catch (Exception $e) {
			 // don't do anything (yet...)
		  }

      break;
    
      
    // ACTION = "MORE_INFO"  
    case 'more_info':
      // update the last reminded time (the ball is back in the 
      // originator's court, so the clock gets reset)
      update_last_reminded($id, $series);
      // update the more info fields
      update_more_info($id, $series, $user, $note);
      $result = TRUE;  // We'll assume success and end an email anyway
      break;
    
      
    // ACTION = "REMIND"
    case 'remind':
      // update the last reminded time
      update_last_reminded($id, $series);
      $result = TRUE;  // We'll assume success and end an email anyway
      break;

      
    default:
      $result = FALSE;  // should not get here
      break;
      
  }  // switch ($action)
  
  
  
  // Now send an email if required and the operation was successful
  if ($result && $need_to_send_mail)
  {
    // Get the area settings for this area (we will need to know if periods are enabled
    // so that we will kniow whether to include iCalendar information in the email)
    get_area_settings($data['area_id']);
    // Send the email
    $result = notifyAdminOnBooking($data, $mail_previous, $is_new_entry, $series, $start_times, $action, $note);
  }
}

// Now it's all done go back to the previous view
header("Location: $returl");
exit;

