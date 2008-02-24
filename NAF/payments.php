<?
  require_once 'NAF/include/db.php';
  require_once 'NAF/include/payments.php';

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
        echo "<td><input type=\"checkbox\" name=\"coach[".($row++)."]\" value=\"$value\" /></td>";
        echo "<td><a href=\"?page=payments&amp;op=log&amp;view=$value\">Log</a></td>";
      }
    }

    if (strlen($value) == 0) {
      echo "<td>&nbsp;</td>";
    }
    else {
      if ($count == 8 && strcmp($op, "latest") != 0) {
        echo "<td><a href=\"?constraint=coachnation='$value'&amp;ordercol=$ordercol\">$value</a></td>";
      }
      else {
        echo "<td>$value</td>";
      }
    }

    if ($count == $max - 1) {
      echo "</tr>\n";
    }
    $count = ++$count % $max;
  }

  function displayControlbuttons() {
      echo "<select name=\"action\">";
      echo "<option value=0>[ Choose Action ]</option>";
      echo "<option value=\"PACKAGE\">Send Package</option>";
      echo "<option value=\"UNPACKAGE\">Undo Package</option>";
      echo "<option value=\"COMMUNICATION\">Communication</option>";
      echo "<option value=\"REFUND\">Refund</option>";
      echo "<option value=\"PAYMENT\">Payment</option>";
      echo "<option value=\"DICE\">Dice</option>";
      echo "</select> ";
      echo "Comment: <input type=\"text\" name=\"info\" />";
      echo "<input type=\"submit\" value=\"Perform Action\" />";
      echo "<input type=\"hidden\" name=\"op\" value=\"action\" />";
      echo "<input type=\"hidden\" name=\"page\" value=\"payments\" />";
  }

  $db = new DB("bloodbo_Rouge");

  // addAdminEvent($db, 2, 2, 'PAYMENT', 'IPN');
  if ($db->getLastError() != 0) {
    echo "Error: ".$db->getErrorMessage();
  }
  else {
    switch($op) {
    case 'comma':
      list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
      $arr = getNewPayments($ordercol, $constraint);

      $count = 0;
      header("Content-type: text/plain");
      echo "#id; firstname; lastname; address1; address2; city; state; zip; nation; phone; email; Exp. Date; has dice";
      array_map("dispText", $arr);
      break;
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
    case 'latest':
      include 'header.php';

      $arr = getLatestEvents();

      OpenTable();

      echo "<form action=\"naf.php\" method=\"post\">";

      echo "<table border=\"1\">";
      echo "<tr><th>&nbsp;</th><th>Log</th><th>ID</th><th>User</th><th>First Name</th><th>Last Name</th><th>Date</th><th>Action</th><th>Info</th></tr>\n";
      $rowcount = 0;
      array_map("dispHTML", $arr);
      echo "</table>";

      echo "<input type=\"hidden\" name=\"count\" value=\"$rowcount\" />";

      displayControlbuttons();
      echo "</form>";
      echo "<a href=\"naf.php\">Back</a>";

      CloseTable();

      include 'footer.php';
      break;
    case 'action';
      list($action, $count, $coaches, $info) = pnVarCleanFromInput('action', 'count', 'coach', 'info');
      $count += 0;
      if (!isset($action) || strlen($action) == 0) {
        //pnRedirect("naf.php");
        //return;
      }
      $staff_coachid = pnUserGetVar('uid');

      for ($i=0; $i<$count; $i++) {
        $coaches[$i] += 0;
        if ($coaches[$i] > 0) {
          //echo "addAdminEvent($db, $coaches[$i], $staff_coachid, $action, $info);";
          addAdminEvent($db, $coaches[$i], $staff_coachid, $action, $info);
        }
      }
      pnRedirect("naf.php?page=payments");
      break;
    default:
      list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
      if (strlen($ordercol) == 0) {
        $ordercol = "coachid";
      }
      $arr = getNewPayments($ordercol, $constraint);

      include 'header.php';

      OpenTable();

      echo "<form action=\"naf.php\" method=\"post\">";

      echo "<table border=\"1\">";
      echo "<tr><th colspan=\"15\">New Payments".(strlen($constraint)>0?"<a href=\"?ordercol=$ordercol\">(Show All)</a>":"")."</th></tr>";
      echo "<tr><th>&nbsp;</th><th>Log</th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachid\">ID</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachfirstname\">First Name</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachlastname\">Last Name</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachaddress1\">Address 1</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachaddress2\">Address 2</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachcity\">City</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachstate\">State</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachzip\">Zip</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachnation\">Nation</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachphone\">Phone</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=pn_email\">Email</a></th>"
          ."<th><a href=\"?constraint=$constraint&amp;ordercol=coachid\">Exp. Date</a></th>"
          ."<th>Got Dice?</th>"
          ."</tr>";
      $rowcount = 0;
      $max = $db->fields + 1;
      array_map("dispHTML", $arr);
      echo "</table>";

      echo "<input type=\"hidden\" name=\"count\" value=\"$rowcount\" />";

      displayControlbuttons();
      echo "</form>";

      echo "<a href=\"?page=payments&amp;op=comma&amp;ordercol=$ordercol&amp;constraint=$constraint\">Comma Separated File</a><br />";
      echo "<a href=\"?page=payments&amp;op=latest\">Latest events for all coaches</a>";

      CloseTable();

      include 'footer.php';
      break;
    }
  }

?>
