<?
  require_once 'include/ipn.php';
  require_once 'include/db.php';
  require_once 'include/admin.php';
  require_once 'include/welcomeMessage.php';

$postvars = array();
while (list ($key, $value) = each ($_POST)) {
$postvars[] = $key;
}
$req = '';
for ($var = 0; $var < count ($postvars); $var++) {
$postvar_key = $postvars[$var];
$postvar_value = $$postvars[$var];
if ($var > 0) { $req .= "&"; }
$req .= $postvar_key . "=" . $postvar_value;
}

$file = fopen("/home/bloodbo/IPN.log", "a+");
fputs($file, $req."\n\n");
fclose($file);

  // Create an instance of the IPN class
  $ipn = new IPN();

  // Setup the variables
  $ipn->setup($req);

  // Open up a connection to the database
  $db = new DB("bloodbo_Rouge");

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

              if (strlen($activationcode) > 0) {
                $subject = "NAF Activation Code";
                $message = generateWelcomeMessage($nukeCoach->pn_uname, $activationcode, '');
              }
              else {
                $subject ="NAF Membership Renewal";
                $message = generateRenewalMessage($nukeCoach->pn_uname);
              }

              mail($email, $subject, $message, "From: $from\nX-Mailer: PHP/" . phpversion());

              $nuffle_coachid = 2; // The coach id for the Nuffle User.

              addAdminEvent($db, $coachid, $nuffle_coachid, 'PAYMENT', 'IPN');

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

  $logresult = $ipn->logQuery($db, $code);

  if (strlen($logresult)!=0) {
    $error .= "<br />Error while logging transaction: " . $logresult;
  }

  // Although not required, let's just display a simple page
  echo "<html><head><title>IPN</title></head><body><b>IPN processed:</b> ($code) ".$ipn->curl_error."<br />".$result;

  if (strlen($error) > 0) {
    echo "<br /><b>Error Message:</b>" . $error;
  }

  echo "</body></html>";

?>
