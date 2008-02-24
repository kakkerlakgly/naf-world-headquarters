<?
function NAF_locator_optin($args) {
  if (!pnUserLoggedIn()) {
    include 'header.php';

    OpenTable();

    echo '<div style="font-size: 2em; text-align: center;">'
    .'Sorry. This feature is only available to registered users.'
    .'</div>';

    CloseTable();

    include 'footer.php';
    exit;
  }
  pnUserSetVar('_NAF_NATIONDISPLAY', 1);
  pnRedirect(pnModURL('NAF', 'locator'));
  return true;
}

function NAF_locator_optout($args) {
  if (!pnUserLoggedIn()) {
    include 'header.php';

    OpenTable();

    echo '<div style="font-size: 2em; text-align: center;">'
    .'Sorry. This feature is only available to registered users.'
    .'</div>';

    CloseTable();

    include 'footer.php';
    exit;
  }
  pnUserSetVar('_NAF_NATIONDISPLAY', 0);
  pnRedirect(pnModURL('NAF', 'locator'));
  return true;
}

function NAF_locator_list($args) {
  if (!pnUserLoggedIn()) {
    include 'header.php';

    OpenTable();

    echo '<div style="font-size: 2em; text-align: center;">'
    .'Sorry. This feature is only available to registered users.'
    .'</div>';

    CloseTable();

    include 'footer.php';
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  include 'header.php';

  OpenTable();

  list($nation, $displayAll) = pnVarCleanFromInput('nation', 'all');
  $displayAll += 0;

  echo '<div align="center" style="font-size: 2em;">Coaches in '.pnVarPrepForDisplay($nation).'</div>'."\n";

  $stateNations = array("United States", "Canada", "Spain", "Italy", "Australia");
  $showState = array_search($nation, $stateNations) !== false;

  if ((pnSecAuthAction(0, "NAF::", "Membership::", ACCESS_ADMIN) ||
  pnSecAuthAction(0, "NAF::", "Tournaments::", ACCESS_ADMIN)) && $displayAll==1) {
    $query = "SELECT c.coachid, pn_uname, coachnation, coachstate, coachcity "
    ."FROM naf_coach c, nuke_users nu "
    ."WHERE (length(coachactivationcode) = 0 OR coachactivationcode is NULL) "
    ."  AND coachnation='".pnVarPrepForStore($nation)."' "
    ."  AND coachid = pn_uid "
    ."ORDER BY ".($showState==1?"coachstate, ":'')."coachcity, pn_uname";
  }
  else {
    $query = "SELECT c.coachid, pn_uname, coachnation, coachstate, coachcity "
    ."FROM naf_coach c, nuke_users nu, nuke_user_data nud "
    ."WHERE (length(coachactivationcode) = 0 OR coachactivationcode is NULL) "
    ."  AND coachnation='".pnVarPrepForStore($nation)."' "
    ."  AND coachid = pn_uid "
    ."  AND pn_uda_uid=coachid "
    ."  AND pn_uda_propid=18 "
    ."  AND pn_uda_value=1 "
    ."ORDER BY ".($showState==1?"coachstate, ":'')."coachcity, pn_uname";
  }

  $res = $dbconn->Execute($query);

  echo '<div align="center"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'locator', '', array('all' => $displayAll))).'">Back</a></div>'."\n";

  echo '<table align="center" border="1" cellspacing="0">';
  echo '<tr bgcolor="#D9D8D0"><th>Username</th>'.($showState==1?'<th>State</th>':'').'<th>City</th></tr>';
  for ( ; !$res->EOF; $res->moveNext() ) {
    $user = '<a href="'.pnVarPrepForDisplay('user.php?op=userinfo&uname='.$res->FieldS('pn_uname')).'">'
    .pnVarPrepForStore($res->Fields('pn_uname'))
    .'</a>';
    $city = pnVarPrepForDisplay($res->Fields('coachcity'));
    $state = pnVarPrepForDisplay($res->Fields('coachstate'));
    if (strlen($city) == 0)
    $city = "&nbsp;";
    if (strlen($state) == 0)
    $state = "&nbsp;";
    echo '<tr><td>'.$user.'</td>'
    .($showState==1?'<td>'.$state.'</td>':'')
    .'<td>'.$city.'</td></tr>'."\n";
  }
  echo '</table>';

  CloseTable();

  include 'footer.php';
  return true;
}

function NAF_locator_main($args) {
  if (!pnUserLoggedIn()) {
    include 'header.php';

    OpenTable();

    echo '<div style="font-size: 2em; text-align: center;">'
    .'Sorry. This feature is only available to registered users.'
    .'</div>';

    CloseTable();

    include 'footer.php';
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  include 'header.php';

  OpenTable();

  echo '<div align="left">'
  .'<h3>NAF Coach Locator</h3>'
  .'</div>';

  $uid = pnUserGetVar('uid');

  $val = pnUserGetVar('_NAF_NATIONDISPLAY');

  if ($val == 0) {
    echo '<div align="center" style="margin: 0.5em;">'
    .'This page will allow you to find NAF coaches in various parts of the world.<br />'
    .'For privacy reasons, you have to <a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'locator', 'optin')).'">opt in</a> '
    .'to show up in these lists.'
    .'</div>';
  }
  else {
    echo '<div align="center" style="margin: 0.5em;">'
    .'This page will allow you to find NAF coaches in various parts of the world.<br />'
    .'If you do not want to be shown here anymore, you can '
    .'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'locator', 'optout')).'">opt out</a> at any time.'
    .'</div>';
  }

  $displayAll = pnVarCleanFromInput('all') + 0;


  $query = "SELECT coachnation, COUNT(1) AS num "
  ."FROM naf_coach c, nuke_user_data nud "
  ."WHERE pn_uda_uid = c.coachid "
  ."  AND (length(coachactivationcode) = 0 OR coachactivationcode is NULL) "
  ."  AND length(coachnation) > 0 "
  ."  AND pn_uda_propid=18 "
  ."  AND pn_uda_value=1 "
  ."GROUP BY coachnation "
  ."ORDER BY coachnation";

  if (pnSecAuthAction(0, "NAF::", "Membership::", ACCESS_ADMIN) ||
  pnSecAuthAction(0, "NAF::", "Tournaments::", ACCESS_ADMIN)) {
    echo '<div style="margin: 0.5em; text-align: center;">(Admin: '
    .($displayAll==1?'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'locator')).'">Show only opted in coaches</a>':
    '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'locator', '', array('all' => '1'))).'">Show all coaches</a>')
    .')</div>';
    if ($displayAll==1) {
      $query = "SELECT coachnation, COUNT(1) AS num "
      ."FROM naf_coach c "
      ."WHERE (length(coachactivationcode) = 0 OR coachactivationcode IS NULL) "
      ."  AND length(coachnation) > 0 "
      ."GROUP BY coachnation "
      ."ORDER BY coachnation";
    }
  }

  $res = $dbconn->Execute($query);

  echo '<table width="30%" cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">';
  echo '<tr bgcolor="#D9D8D0"><th>Nation</th><th>Coaches</th></tr>';
  $total = 0;
  for ( ; !$res->EOF; $res->moveNext() ) {
    $nation = $res->Fields('coachnation');
    $num = $res->Fields('num');
    $total += $num;
    echo '<tr><td bgcolor="#f8f7ee"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'locator', 'list', array('nation' => $nation, 'all' => $displayAll))).'">'.$nation.'</a></td>'
    .'<td bgcolor="#f8f7ee" align="center">'.$num.'</td></tr>'."\n";
  }
  echo '</table>';
  echo '<div align="center">'.$total.' coach'.($total!=1?"es":'').' listed.</div>';

  CloseTable();

  include 'footer.php';
  return true;
}

?>
