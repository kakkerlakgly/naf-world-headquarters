<?
require_once 'modules/NAF/pnpaymentsapi.php';

function NAF_expired_submit($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) &&
  !pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)
  ) {
    pnRedirect('index.php');
    return true;
  }
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
      addAdminEvent($coach[$i], $user, 'PAYMENT', $comment);
      if ($hasDice)
      addAdminEvent($coach[$i], $user, 'DICE', 'Premium given to coach');

      $renewed[] = $coach[$i];
    }
  }

  // List of unexpired coached that have been renewed
  for ($i=0; $i<30; $i++) {
    if ($manual[$i] == 0 && strlen($manual[$i]) > 0) {
      $id = pnUserGetIDFromName($manual[$i]);
    }
    else
    $id = $manual[$i] + 0;

    if ($id > 0) {
      addAdminEvent($id, $user, 'PAYMENT', $comment);

      if ($hasDice)
      addAdminEvent($id, $user, 'DICE', 'Premium given to coach');
      $renewed[] = $id;
    }
  }

  if (count($renewed) > 0) {
    include 'header.php';
    OpenTable();
    echo '<b>Renewed Coaches:</b><br />'."\n";
    foreach ($renewed as $coachid) {
      echo '&nbsp; '.$coachid.": ".pnUserGetVar('name', $coachid).'<br />'."\n";
    }

    echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired')).'">Back to list of expired coaches</a>';

    CloseTable();
    include 'footer.php';
  }
  else
  pnRedirect(pnModURL('NAF', 'expired'));

  return true;
}

function NAF_expired_main($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) &&
  !pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)
  ) {
    pnRedirect('index.php');
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  include 'header.php';
  OpenTable();

  list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
  if (strlen($ordercol) == 0) {
    $ordercol = "c.coachid";
  }

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', 'submit')).'" method="post">';

  echo '<table border="1">';

  echo '<tr><th colspan="8">Expired coaches</th></tr>';

  echo '<tr><th>&nbsp;</th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', '', array('ordercol' => 'c.coachid'))).'">ID</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', '', array('ordercol' => 'pn_uname'))).'">Username</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', '', array('ordercol' => 'coachfirstname'))).'">First Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', '', array('ordercol' => 'coachlastname'))).'">Last Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', '', array('ordercol' => 'pn_email'))).'">Email</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', '', array('ordercol' => 'coachnation'))).'">Nation</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired', '', array('ordercol' => 'coachexpirydate'))).'">Exp. date</a></th>'
  .'</tr>';

  if (strlen($ordercol)==0) {
    $ordercol="c.coachid";
  }

  $qry = "SELECT pn_uid, pn_uname, pn_email, coachfirstname, coachlastname, coachnation, coachexpirydate "
  ."FROM naf_coach c, nuke_users nu "
  ."where pn_uid=c.coachid AND coachexpirydate < curdate() order by ".pnVarPrepForStore($ordercol).", c.coachid";
  $res = $dbconn->Execute($qry);

  $count=0;
  for ( ; !$res->EOF; $res->MoveNext()) {
    echo '<tr><td><input type="checkbox" name="coach['.$count.']" value="'.$res->Fields('pn_uid').'" /></td>'
    .'<td>'.$res->Fields('pn_uid').'</td>'
    .'<td>'.$res->Fields('pn_uname').'</td>'
    .'<td>'.$res->Fields('coachfirstname').'</td>'
    .'<td>'.$res->Fields('coachlastname').'</td>'
    .'<td>'.$res->Fields('pn_email').'</td>'
    .'<td>'.$res->Fields('coachnation').'</td>'
    .'<td>'.$res->Fields('coachexpirydate').'</td>'
    .'</tr>'."\n";
    $count++;
  }


  echo '</table><br />';

  echo '<b>Renewals for unexpired coaches</b><br />Type in the user names or membership IDs of the coaches to be renewed.<br />'."\n";
  for ($i=0; $i<30; $i++) {
    echo '<input name="manual['.$i.']" size="15" /> ';
    if (($i%5) == 4) echo '<br />'."\n";
  }

  echo '<input type="hidden" name="count" value="'.$count.'" />';
  echo '<br /><b>Premium given:</b> <input type="checkbox" name="hasdice" value="1" /> (This is a global flag for all coaches that are renewed here)<br />';

  echo '<br /><b>Comment:</b> <input type="text" name="comment" size="40" /><br /><input type="submit" value="Renew" />';

  echo '</form>';

  echo 'Note that this action will be registered as a PAYMENT for the renewed users so '
  .'please supply a useful comment.';

  CloseTable();
  include 'footer.php';
  return true;
}

?>
