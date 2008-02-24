<?
  require_once 'NAF/include/db.php';
  require_once 'NAF/include/payments.php';

  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) &&
      !pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)
     ) {
    pnRedirect("index.php");
    return;
  }

  $db = new DB('bloodbo_Rouge');

  switch($op) {

  case 'submit':
    list($comment, $coach, $count, $manual, $hasDice) = pnVarCleanFromInput('comment', 'coach', 'count', 'manual', 'hasdice');
    $user = pnUserGetVar('uid');
    $count += 0;
    $list = "";
    $renewed = array();
    if ($hasDice==1)
      $hasDice = true;
    else
      $hasDice = false;

// List of expired coaches that have been renewed
    for ($i=0; $i<$count; $i++) {
      $coach[$i]+=0;
      if ($coach[$i] > 0) {
        addAdminEvent($db, $coach[$i], $user, 'PAYMENT', $comment);
	  if ($hasDice)
          addAdminEvent($db, $coach[$i], $user, 'DICE', 'Premium given to coach');

        $renewed[] = $coach[$i];
      }
    }

// List of unexpired coached that have been renewed
    for ($i=0; $i<30; $i++) {
      if ($manual[$i] == 0 && strlen($manual[$i]) > 0) {
        $db->query("SELECT pn_uid FROM nuke_users WHERE pn_uname='".addslashes($manual[$i])."'");
        $ob = $db->getNextObject();
        $id = $ob->pn_uid;
      }
      else
        $id = $manual[$i] + 0;

      if ($id > 0) {
        addAdminEvent($db, $id, $user, 'PAYMENT', $comment);

        if ($hasDice)
          addAdminEvent($db, $id, $user, 'DICE', 'Premium given to coach');
        $renewed[] = $id;
      }
    }

    if (count($renewed) > 0) {
      include 'header.php';
      OpenTable();

      $db->query("SELECT pn_uid, pn_uname FROM nuke_users WHERE pn_uid IN (".implode(',', $renewed).")");
      echo "<b>Renewed Coaches:</b><br />\n";
      while ($coach = $db->getNextObject()) {
        echo "&nbsp; ".$coach->pn_uid.": ".$coach->pn_uname."<br />\n";
      }

      echo "<a href=\"/naf.php?page=expired\">Back to list of expired coaches</a>";

      CloseTable();
      include 'footer.php';
    }
    else
      pnRedirect('/naf.php?page=expired');

    break;

  default:
    include 'header.php';
    OpenTable();

    list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
    if (strlen($ordercol) == 0) {
      $ordercol = "c.coachid";
    }

    echo '<form action="naf.php" method="post">';
    echo '<input type="hidden" name="op" value="submit">';
    echo '<input type="hidden" name="page" value="expired">';

    echo '<table border="1">';

    echo '<tr><th colspan="8">Expired coaches</th></tr>';

    echo "<tr><th>&nbsp;</th>"
        ."<th><a href=\"naf.php?page=expired&ordercol=c.coachid\">ID</a></th>"
        ."<th><a href=\"naf.php?page=expired&ordercol=pn_uname\">Username</a></th>"
        ."<th><a href=\"naf.php?page=expired&ordercol=coachfirstname\">First Name</a></th>"
        ."<th><a href=\"naf.php?page=expired&ordercol=coachlastname\">Last Name</a></th>"
        ."<th><a href=\"naf.php?page=expired&ordercol=pn_email\">Email</a></th>"
        ."<th><a href=\"naf.php?page=expired&ordercol=coachnation\">Nation</a></th>"
        ."<th><a href=\"naf.php?page=expired&ordercol=coachexpirydate\">Exp. date</a></th>"
        ."</tr>";

    if (strlen($ordercol)==0) {
      $ordercol="c.coachid";
    }

    $qry = "SELECT c.*, nu.*, coachexpirydate "
          ."FROM naf_coach c, nuke_users nu "
          ."where pn_uid=c.coachid AND coachexpirydate < curdate() order by $ordercol, c.coachid";
    $res = $dbconn->Execute($qry);

    $count=0;
    for ( ; !$res->EOF; $res->MoveNext()) {
      echo "<td><input type=\"checkbox\" name=\"coach[$count]\" value=\"".$res->Fields('pn_uid')."\"></td>"
          ."<td>".$res->Fields('pn_uid')."</td>"
          ."<td>".$res->Fields('pn_uname')."</td>"
          ."<td>".$res->Fields('coachfirstname')."</td>"
          ."<td>".$res->Fields('coachlastname')."</td>"
          ."<td>".$res->Fields('pn_email')."</td>"
          ."<td>".$res->Fields('coachnation')."</td>"
          ."<td>".$res->Fields('coachexpirydate')."</td>"
          ."</tr>";
      $count++;
    }

    echo "<input type=\"hidden\" name=\"count\" value=\"$count\">";

    echo '</table><br />';

    echo "<b>Renewals for unexpired coaches</b><br />Type in the user names or membership IDs of the coaches to be renewed.<br />\n";
    for ($i=0; $i<30; $i++) {
      echo "<input name=\"manual[$i]\" size=\"15\" /> ";
      if (($i%5) == 4) echo "<br />\n";
    }

    echo '<br /><b>Premium given:</b> <input type="checkbox" name="hasdice" value="1"> (This is a global flag for all coaches that are renewed here)<br />';

    echo '<br /><b>Comment:</b> <input type="text" name="comment" size=\"40\"><br /><input type="submit" value="Renew">';

    echo '</form>';

    echo 'Note that this action will be registered as a PAYMENT for the renewed users so '
        .'please supply a useful comment.';

    CloseTable();
    include 'footer.php';
    break;
  }

?>
