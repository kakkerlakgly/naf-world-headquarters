<?
require_once 'NAF/include/payments.php';

function NAF_newusers_submit($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    pnRedirect(pnGetBaseURL());
    return;
  }
  $dbconn =& pnDBGetConn(true);

  list($comment, $coach, $count) = pnVarCleanFromInput('comment', 'coach', 'count');
  $user = pnUserGetVar('uid');
  $count += 0;
  $list = "";
  for ($i=0; $i<$count; $i++) {
    $coach[$i]+=0;
    if ($coach[$i] > 0) {
      $qry = "insert into naf_administration (coachid, staff_coachid, action, admin_date, admin_info)"
      ."values (".pnVarPrepForStore($coach[$i]).", ".pnVarPrepForStore($user).", 'PAYMENT', now(), '".pnVarPrepForStore($comment)."')";
      $dbconn->Execute($qry);

      if (strlen($list) > 0) {
        $list .= ",";
      }
      $list .= pnVarPrepForStore($coach[$i]);
    }
  }

  if (strlen($list) > 0) {
    $qry = "update naf_coach set coachactivationcode='' where coachid in (".$list.")";
    $dbconn->Execute($qry);
  }

  pnRedirect(pnModURL('NAF', 'newusers'));
  return true;
}

function NAF_newusers_main($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    pnRedirect(pnGetBaseURL());
    return;
  }
  $dbconn =& pnDBGetConn(true);

  include 'header.php';
  OpenTable();

  list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
  if (strlen($ordercol) == 0) {
    $ordercol = "c.coachid";
  }

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', 'submit')).'" method="post">';

  echo '<table border="1">';

  echo '<tr><th colspan="8">Unactivated coaches</th></tr>';

  echo '<tr><th>&nbsp;</th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', '', array('ordercol' => 'c.coachid'))).'">ID</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', '', array('ordercol' => 'pn_uname'))).'">Username</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', '', array('ordercol' => 'coachfirstname'))).'">First Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', '', array('ordercol' => 'coachlastname'))).'">Last Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', '', array('ordercol' => 'pn_email'))).'">Email</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', '', array('ordercol' => 'coachnation'))).'">Nation</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'newusers', '', array('ordercol' => 'regdate'))).'">Regdate</a></th>'
  .'</tr>';

  if (strlen($ordercol)==0) {
    $ordercol="c.coachid";
  }

  $qry = "SELECT c.*, nu.*, cr.coachid, from_unixtime(pn_user_regdate) as regdate, cr.coachid as rankedcoach, unix_timestamp(now())-nu.pn_user_regdate as age, na.coachid as nacoach "
  ."FROM naf_coach c, nuke_users nu "
  ."left join naf_coachranking cr on cr.coachid=c.coachid "
  ."left join naf_administration na on c.coachid=na.coachid "
  ."where pn_uid=c.coachid and coachactivationcode<>'' and na.coachid IS NULL order by ".pnVarPrepForStore($ordercol).", c.coachid";
  $res = $dbconn->Execute($qry);

  $count=0;
  $oldUsers = array();
  for ( ; !$res->EOF; $res->MoveNext()) {
    $old="";
    if ($res->Fields('age') > 60*60*24*70 && $res->Fields('rankedcoach') == 0 &&
    $res->Fields('nacoach') == 0) {
      $old = "*";
      $oldUsers[] = $res->Fields('pn_uid');
    }
    echo '<tr><td><input type="checkbox" name="coach['.$count.']" value="'.$res->Fields('pn_uid').'" /></td>'
    .'<td>'.$res->Fields('pn_uid').($res->Fields('rankedcoach')>0?"*":"").'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('pn_uname')).'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('coachfirstname')).'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('coachlastname')).'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('pn_email')).'</td>'
    .'<td>'.$res->Fields('coachnation').'</td>'
    .'<td>'.$res->Fields('regdate').$old.'</td>'
    .'</tr>'."\n";
    $count++;
  }

  if (count($oldUsers) > 0) {
    $query = "DELETE FROM naf_coach WHERE coachid in (".implode(', ', $oldUsers).")";
    $dbconn->Execute($query);

    $query = "DELETE FROM nuke_users WHERE pn_uid in (".implode(', ', $oldUsers).")";
    $dbconn->Execute($query);
  }

  echo '</table><br />';

  echo '<input type="hidden" name="count" value="'.$count.'" />';

  echo 'Comment: <input type="text" name="comment" /> <input type="submit" value="Activate" />';

  echo '</form>';

  echo 'Note that this action will be registered as a PAYMENT for the activated users so '
  .'please supply a useful comment.';

  CloseTable();
  include 'footer.php';
  return true;
}
?>
