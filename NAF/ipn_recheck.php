<?
  require_once 'include/ipn.php';
  require_once 'include/db.php';

  // Create an instance of the IPN class
  $ipn = new IPN();

  // Open up a connection to the database
  $db = new DB("bloodbo_Rouge");

  // reload the IPN request

  $naf_txn = $_GET['id'] + 0;
  if ($naf_txn == 0) {
    echo "Error in txn id";
    return;
  }

  $ipn->restoreIPN($db, $naf_txn);

  // Verify the transaction
  $result = $ipn->verify();

  $error = "";

  if ($result != 0) {
    // Transaction Verified

    $status = $ipn->payment_status;
    $txn_id = $ipn->txn_id;
    $receiver_email = $ipn->receiver_email;

    if (strcmp($status, "Completed") == 0) {
      // Only check the completed transactions for duplicates.
      $qry = "select * from naf_transactions where txn_id='$txn_id' and naf_status='PROCESSED'";
      if ($db->query($qry) ) {
        if ($db->numRows() == 0) {
          if (strcmp($receiver_email, "nuffle@bloodbowl.net") == 0) {
            // Ok, the user has shown us the money!
            if (strcmp($ipn->payment_gross, "10.00") == 0) {
              $coachid = $ipn->item_number;

              $qry = "select * from naf_coach where coachid=$coachid";
              if (!$db->query($qry)) {
                echo $db->getErrorMessage()."<br>";
              }
              $coach = $db->getNextObject();

             $qry = "select * from nuke_users where pn_uid=$coachid";
              if (!$db->query($qry)) {
                echo $db->getErrorMessage()."<br>";
              }
              $nukeCoach = $db->getNextObject();

              $activationcode = $coach->coachactivationcode;
              $email = $nukeCoach->pn_email;

              $from = "nuffle@bloodbowl.net";
              $subject = "NAF Activation Code";
              $message = "Welcome to the NAF!\n\n"
                        ."Your activation code is: $activationcode\n";

              mail($email, $subject, $message, "From: $from\nX-Mailer: PHP/" . phpversion());
              $code = 0; // Set the result code
            }
            else {
              // They paid some sum other than the $10 specified.
              $code = -7;
            }
          }
          else {
            // Receiver email was set to something other than "nuffle@bloodbowl.net"

            $code = -1;
          }
        }
        else {
          // Duplicate transaction
          $code = -2;
        }
      }
      else {
        // Error while selecting from transaction table
        $code = -3;
        $error .= "<br />" . $db->getErrorMessage();
      }
    }
    else {
      // Payment status isn't "completed"
      $code = -4;
    }
  }
  else {
    // Transaction failed
    $result = $ipn->getResult();

    if (strcmp($result, "INVALID") == 0) {
      // The notification was INVALID. Log for manual investigation.
      $code = -5;
    }
    else {
      // Not INVALID, but the request did not go through. Most likely we were not able to
      // access the PayPal site. Nevertheless, log for manual investigation.
      $code = -6;
    }
  }

//  $logresult = $ipn->logQuery($db, $code);

//  if (strlen($logresult)!=0) {
//    $error .= "<br />Error while logging transaction: " . $logresult;
//  }

  // Although not required, let's just display a simple page
  echo "<html><head><title>IPN</title></head><body><b>IPN processed:</b> ($code) ".$ipn->curl_error."<br />".$result;

  if (strlen($error) > 0) {
    echo "<br /><b>Error Message:</b>" . $error;
  }

  echo "</body></html>";

?>
