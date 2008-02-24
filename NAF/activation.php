<?
  require_once 'NAF/include/db.php';
  require_once 'NAF/include/payments.php';
  require_once 'NAF/include/welcomeMessage.php';

  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }

  function dispText($value) {
    global $count;
    if ($count > 0) {
      echo ";";
    }
    else {
      echo "\n";
    }
    if ($count == 0) {
      $value = sprintf("%06d", $value);
      echo $value;
    }
    else {
      echo $value;
    }
    $count = ++$count % 13;
  }

  $row = 0;
  $max = 0;
  function dispHTML($value) {
    global $count, $op, $db, $row, $constraint, $ordercol, $rowcount, $max;

    if ($max == 0) {
      $max = $db->fields;
    }

    if ($count == 0) {
      $rowcount++;
      echo "<tr>";
      if (strcmp($op, "log") != 0) {
        echo "<td><input type=\"checkbox\" name=\"coach[".($row++)."]\" value=\"$value\"></td>";
        echo "<td><a href=\"?page=payments&op=log&view=$value\">Log</a></td>";
      }
    }

    if (strlen($value) == 0) {
      echo "<td>&nbsp;</td>";
    }
    else {
      if ($count == 8 && strcmp($op, "latest") != 0) {
        echo "<td><a href=\"?constraint=coachnation='$value'&ordercol=$ordercol\">$value</a></td>";
      }
      else {
        echo "<td>$value</td>";
      }
    }

    if ($count == $max - 1) {
      echo "</tr>";
    }   
    $count = ++$count % $max;
  }

  function displayControlbuttons() {
      echo "<input type=\"submit\" name=\"submit\" value=\"Resend Activation Code\">";
      echo " &nbsp; &nbsp; ";
      echo "<input type=\"submit\" name=\"submit\" value=\"Activate Account\"> ";
      echo "<input type=\"hidden\" name=\"op\" value=\"action\">";
      echo "<input type=\"hidden\" name=\"page\" value=\"activation\">";
  }

  $db = new DB("bloodbo_Rouge");

  // addAdminEvent($db, 2, 2, 'PAYMENT', 'IPN');
  if ($db->getLastError() != 0) {
    echo "Error: ".$db->getErrorMessage();
  }
  else {
    switch($op) {
    case 'log':
      $coach = getCoachName($view);
      $arr = getPaymentLog($view);

      include 'header.php';
      Opentable();
      echo "<table border=\"1\">";
      echo "<tr><th colspan=\"4\">Log for $coach</th></tr>";
      echo "<tr><th>Staff</th><th>Action</th><th>Date</th><th>Info</th></tr>";
      array_map("dispHTML", $arr);
      echo "</table>";
      echo "<a href=\"naf.php\">Back</a>";
      CloseTable();
      include 'footer.php';
      break;
    case 'action';
      list($action, $count, $coaches, $info, $submit) = pnVarCleanFromInput('action', 'count', 'coach', 'info', 'submit');

      $count += 0;
      if (!isset($action) || strlen($action) == 0) {
        //pnRedirect("naf.php");
        //return;
      }
      $staff_coachid = pnUserGetVar('uid');

      for ($i=0; $i<$count; $i++) {
        $coaches[$i] += 0;
        if ($coaches[$i] > 0) {
          if ($submit == "Resend Activation Code") {
            $query = "SELECT pn_uname, coachactivationcode from nuke_users nu, naf_coach c "
                    ."WHERE nu.pn_uid = c.coachid AND c.coachid = ".$coaches[$i];
            $nukeCoach = $dbconn->Execute($query);
            if ($nukeCoach) {
              $message = generateWelcomeMessage($nukeCoach->Fields('pn_uname'), $nukeCoach->Fields('coachactivationcode'), '');
              mail($email, $subject, $message, "From: $from\nX-Mailer: PHP/" . phpversion());
            }
            else {
              echo "Error while querying for coach: ".$dbconn->errorMsg();
              exit;
            }
          }
          else if ($submit == "Activate Account") {
            $query = "UPDATE naf_coach SET coachactivationcode='' WHERE coachid=".$coaches[$i];
            $result = $dbconn->Execute($query);
            if (!$result) {
              echo "Error while activating coach: ".$dbconn->errorMsg();
              exit;
            }
          }
          else {
            echo "Command not supported: ".pnVarPrepForDispaly($submit);
            exit;
          }
        }
      }
      pnRedirect("naf.php?page=activation");
      break;
    default:
      list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
      if (strlen($ordercol) == 0) {
        $ordercol = "coachid";
      }
      $arr = getInactiveCoaches($ordercol, $constraint);

      include 'header.php';

      OpenTable();

      echo "<form action=\"naf.php\" method=\"post\">";

      echo "<table border=\"1\">";
      echo "<tr><th colspan=\"15\">Paid inactive coaches".(strlen($constraint)>0?"<a href=\"?ordercol=$ordercol\">(Show All)</a>":"")."</th></tr>";
      echo "<tr><th>&nbsp;</th><th>Log</th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachid\">ID</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachfirstname\">First Name</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachlastname\">Last Name</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachaddress1\">Address 1</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachaddress2\">Address 2</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachcity\">City</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachstate\">State</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachzip\">Zip</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachnation\">Nation</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachphone\">Phone</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=pn_email\">Email</a></th>"
          ."<th><a href=\"?constraint=$constraint&ordercol=coachid\">Exp. Date</a></th>"
          ."<th>Got Dice?</th>"
          ."</tr>";
      $rowcount = 0;
      $max = $db->fields + 2;
      array_map("dispHTML", $arr);
      echo "</table>";

      echo "<input type=\"hidden\" name=\"count\" value=\"$rowcount\">";

      displayControlbuttons();
      echo "</form>";

      CloseTable();

      include 'footer.php';
      break;
    }
  }

?>
