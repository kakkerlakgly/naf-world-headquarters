<?php
//require_once 'NAF/include/payments.php';
function NAF_coachStatusList_main($args) {

  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) &&
  !pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)
  ) {
    pnRedirect("index.php");
    return;
  }
  if (pnVarCleanFromInput('export') != "") {
    exportCSV();
    exit;
  }

  if (pnVarCleanFromInput('export') != "")
  exportCSV();

  $country = pnVarCleanFromInput('country');
  $activation = pnVarCleanFromInput('activation');

  include 'header.php';
  OpenTable();
  echo  "<h1>Coaches Status List</h1>";
  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'coachStatusList')).'" method="post">';
  echo '<input type="hidden" name="op" value="submit" />';
  echo '<input type="hidden" name="country" value="' . $country . '" />';
  echo '<input type="hidden" name="activation" value="' . $activation . '" />';
  echo '<div id="filter" style="background-color: #CCCCCC; padding:5px;">';
  echo '<h2>Filter options </h2>';
  echo '<h3>Nation</h3>';
  echo showCountryList();
  echo '<h3>Activation status </h3>';
  echo showActivationFilter();

  $activationFilter = pnVarCleanFromInput('activation');

  echo '</div>';
  echo '<div id="export" style="background-color: #888888; padding: 5px;">';
  echo '<input type="submit" name="export" value="Export to CSV" />';

  echo '</div>';
  echo '<br />';
  showCoachesList();
  echo '</form>';

  CloseTable();
  include 'footer.php';
  return true;
}


function showCountryList() {
  $dbconn =& pnDBGetConn(true);
  $country = pnVarCleanFromInput('country');

  $qry = 	'SELECT DISTINCT coachnation ' .
  ' FROM naf_coach ' .
  ' ORDER BY coachnation';
  $res = $dbconn->Execute($qry);
  echo '<select size="1" name="country" onchange="this.form.submit()">';

  if ($country == "--All--" || $country == '') { $sel = 'selected="selected"'; }
  echo '<option ' . $sel . '>--All--</option>';

  $sel = $country == "--Without--" ? 'selected="selected"' : '';
  echo '<option ' . $sel . '>--Without--</option>';

  for ( ; !$res->EOF; $res->MoveNext()) {
    if ($res->Fields('coachnation') == "") continue;
    $sel = $res->Fields('coachnation') == $country ? 'selected="selected"' : '';
    echo "<option $sel>";
    echo $res->Fields('coachnation') . '</option>';
  }
  echo  '</select>';
}

function showActivationFilter() {
  $activationFilter = pnVarCleanFromInput('activation');

  $chkAll = $activationFilter == "activationAll" ? 'checked="checked"' : '';
  $chkAct = $activationFilter == "activationActivated" ? 'checked="checked"' : '';
  $chkNot = $activationFilter == "activationNotActivated" ? 'checked="checked"' : '';

  if ($chkAll == "" && $chkAct == "" && $chkNot == "") {
    $chkAll = 'checked="checked"';
  }

  echo '<input type="radio" name="activation" onclick="this.form.submit()" value="activationAll" ' . $chkAll . ' /> all ';
  echo '<input type="radio" name="activation" onclick="this.form.submit()" value="activationActivated" ' . $chkAct . ' /> only activated ';
  echo '<input type="radio" name="activation" onclick="this.form.submit()" value="activationNotActivated" ' . $chkNot . ' /> only not activated ';
  echo 'coaches ';
}

function getCoachesListQuery() {
  list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
  if (strlen($ordercol) == 0) {
    $ordercol = "c.coachid";
  }

  // where clause for country
  $country = pnVarCleanFromInput('country');
  if ($country == "--All--" || $country == '')
  $where_nation = '';
  elseif ($country == "--Without--")
  $where_nation = ' AND c.coachnation IS NULL ';
  else
  $where_nation = ' AND c.coachnation = "' . pnVarPrepForStore($country) . '"';

  // where clause for activation filter
  $activationFilter = pnVarCleanFromInput('activation');
  if ($activationFilter == "activationActivated") { $where_act = ' AND c.coachexpirydate <> "" '; }
  else if ($activationFilter == "activationNotActivated") { $where_act = ' AND c.coachexpirydate IS NULL '; }

  $qry = 	'SELECT c.coachfirstname, c.coachlastname,c.coachexpirydate , c.coachnation, nu.pn_uname, nu.pn_email, nu.pn_uid ' .
  ' FROM naf_coach c, nuke_users nu ' .
  ' WHERE pn_uid = c.coachid ' .
  $where_nation .
  $where_act .
  ' ORDER BY ' .pnVarPrepForStore($ordercol);
  return $qry;
}

function showCoachesList() {
  $dbconn =& pnDBGetConn(true);


  $qry = getCoachesListQuery();
  //echo $qry;
  $res = $dbconn->Execute($qry);

  echo '<h2>'.$res->_numOfRows.' coaches found!</h2>';
  echo '<table border="1">';
  echo '<tr><th colspan="7">Coaches expiry status</th></tr>';
  echo '<tr>'
  .'<th>ID</th>'
  .'<th>Username</th>'
  .'<th>First Name</th>'
  .'<th>Last Name</th>'
  .'<th>Email</th>'
  .'<th>Nation</th>'
  .'<th>Exp. date</th>'
  .'</tr>'."\n";

  for ( ; !$res->EOF; $res->MoveNext()) {
    echo '<tr><td>'.$res->Fields('pn_uid').'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('pn_uname')).'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('coachfirstname')).'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('coachlastname')).'</td>'
    .'<td>'.pnVarPrepForDisplay($res->Fields('pn_email')).'</td>'
    .'<td>'.$res->Fields('coachnation').'</td>'
    .'<td>'.$res->Fields('coachexpirydate').'</td>'
    .'</tr>'."\n";
  }
  echo '</table>';
}

function exportCSV() {
  $dbconn =& pnDBGetConn(true);

  $qry = getCoachesListQuery();
  $res = $dbconn->Execute($qry);

  header('Content-Type: text/x-csv');
  header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
  header('Content-Disposition: attachment; filename=coachStatusList.csv');
  header('Pragma: no-cache');

  echo "CoachId,Nickname,First Name,Last Name,e-mail,Nation,Expiry date\r\n";
  for ( ; !$res->EOF; $res->MoveNext()) {
    echo $res->Fields('pn_uid') . ',' .
    $res->Fields('pn_uname') . ',' .
    $res->Fields('coachfirstname') . ',' .
    $res->Fields('coachlastname') . ',' .
    $res->Fields('pn_email') . ',' .
    $res->Fields('coachnation') . ',' .
    $res->Fields('coachexpirydate') .
    "\r\n";
  }
}

?>
