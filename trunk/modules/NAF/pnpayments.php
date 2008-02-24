<?
require_once 'modules/NAF/pnpaymentsapi.php';


function displayControlbuttons() {
  echo '<select name="action">';
  echo '<option value="0">[ Choose Action ]</option>';
  echo '<option value="PACKAGE">Send Package</option>';
  echo '<option value="UNPACKAGE">Undo Package</option>';
  echo '<option value="COMMUNICATION">Communication</option>';
  echo '<option value="REFUND">Refund</option>';
  echo '<option value="PAYMENT">Payment</option>';
  echo '<option value="DICE">Dice</option>';
  echo '</select>';
  echo 'Comment: <input type="text" name="info" />';
  echo '<input type="submit" value="Perform Action" />';
}

// addAdminEvent($db, 2, 2, 'PAYMENT', 'IPN');
/*if ($db->getLastError() != 0) {
echo "Error: ".$db->getErrorMessage();
}
else {*/
function NAF_payments_comma($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }
  list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
  $arr = getNewPayments($ordercol, $constraint);
  $arrsize = count($arr);

  header("Content-type: text/plain");
  echo '#id; firstname; lastname; address1; address2; city; state; zip; nation; phone; email; Exp. Date; has dice'."\n";
  for($i=0;$i < $arrsize;$i+=13) {
    echo sprintf("%06d", $arr[$i]).';'
    .$arr[$i+1].';'
    .$arr[$i+2].';'
    .$arr[$i+3].';'
    .$arr[$i+4].';'
    .$arr[$i+5].';'
    .$arr[$i+6].';'
    .$arr[$i+7].';'
    .$arr[$i+8].';'
    .$arr[$i+9].';'
    .$arr[$i+10].';'
    .$arr[$i+11].';'
    .$arr[$i+12]."\n";
  }
  return true;
}
function NAF_payments_log($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }
  $view = pnVarCleanFromInput('view');
  $coach = getCoachName($view);
  $arr = getPaymentLog($view);
  $arrsize = count($arr);
  
  include 'header.php';
  Opentable();
  echo '<table border="1">';
  echo '<tr><th colspan="4">Log for '.$coach.'</th></tr>';
  echo '<tr><th>Staff</th><th>Action</th><th>Date</th><th>Info</th></tr>';
  for($i=0;$i < $arrsize;$i+=4) {
    echo '<tr><td>'.$arr[$i+0].'</td>'
    .'<td>'.$arr[$i+1].'</td>'
    .'<td>'.$arr[$i+2].'</td>'
    .'<td>'.$arr[$i+3].'</td></tr>'."\n";
  }
  echo '</table>';
  echo '<a href="'.pnModURL('NAF').'">Back</a>';
  CloseTable();
  include 'footer.php';
  return true;
}
function NAF_payments_latest($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }
  include 'header.php';

  $arr = getLatestEvents();
  $arrsize = count($arr);

  OpenTable();

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', 'action')).'" method="post">';

  echo '<table border="1">';
  echo '<tr><th>&nbsp;</th><th>Log</th><th>ID</th><th>User</th><th>First Name</th><th>Last Name</th><th>Date</th><th>Action</th><th>Info</th></tr>'."\n";
  for($i=0;$i < $arrsize;$i+=7) {
    echo '<tr><td><input type="checkbox" name="coach['.($i/7).']" value="'.$arr[$i].'" /></td>';
    echo '<td><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', 'log', array('view' => $arr[$i]))).'">Log</a></td>';
    echo '<td>'.$arr[$i+0].'</td>'
    .'<td>'.$arr[$i+1].'</td>'
    .'<td>'.$arr[$i+2].'</td>'
    .'<td>'.$arr[$i+3].'</td>'
    .'<td>'.$arr[$i+4].'</td>'
    .'<td>'.$arr[$i+5].'</td>'
    .'<td>'.$arr[$i+6].'</td></tr>'."\n";
  }
  echo '</table>';

  echo '<input type="hidden" name="count" value="'.($arr/7).'" />';

  displayControlbuttons();
  echo '</form>';
  echo '<a href="'.pnModURL('NAF').'">Back</a>';

  CloseTable();

  include 'footer.php';
  return true;
}
function NAF_payments_action($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }
  list($action, $count, $coaches, $info) = pnVarCleanFromInput('action', 'count', 'coach', 'info');
  $count += 0;
  if (!isset($action) || strlen($action) == 0) {
    //pnRedirect(pnModURL('NAF'));
    //return;
  }
  $staff_coachid = pnUserGetVar('uid');

  for ($i=0; $i<$count; $i++) {
    $coaches[$i] += 0;
    if ($coaches[$i] > 0) {
      addAdminEvent($coaches[$i], $staff_coachid, $action, $info);
    }
  }
  pnRedirect(pnModURL('NAF', 'payments'));
  return true;
}
function NAF_payments_main($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }
  list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
  if (strlen($ordercol) == 0) {
    $ordercol = 'coachid';
  }
  $arr = getNewPayments($ordercol, $constraint);
  $arrsize = count($arr);
  include 'header.php';

  OpenTable();

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', 'action')).'" method="post">';

  echo '<table border="1">';
  echo '<tr><th colspan="15">New Payments'.(strlen($constraint)>0?'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('ordercol' => $ordercol))).'">(Show All)</a>':'').'</th></tr>';
  echo '<tr><th>&nbsp;</th><th>Log</th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachid'))).'">ID</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachfirstname'))).'">First Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachlastname'))).'">Last Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachaddress1'))).'">Address 1</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachaddress2'))).'">Address 2</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachcity'))).'">City</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachstate'))).'">State</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachzip'))).'">Zip</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachnation'))).'">Nation</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachphone'))).'">Phone</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'pn_email'))).'">Email</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => $constraint, 'ordercol' => 'coachid'))).'">Exp. Date</a></th>'
  .'<th>Got Dice?</th>'
  .'</tr>'."\n";
  for($i=0;$i < $arrsize;$i+=13) {
    echo '<tr><td><input type="checkbox" name="coach['.($i/13).']" value="'.$arr[$i].'" /></td>';
    echo '<td><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', 'log', array('view' => $arr[$i]))).'">Log</a></td>';
    echo '<td>'.$arr[$i+0].'</td>'
    .'<td>'.$arr[$i+1].'</td>'
    .'<td>'.$arr[$i+2].'</td>'
    .'<td>'.$arr[$i+3].'</td>'
    .'<td>'.$arr[$i+4].'</td>'
    .'<td>'.$arr[$i+5].'</td>'
    .'<td>'.$arr[$i+6].'</td>'
    .'<td>'.$arr[$i+7].'</td>';
    echo '<td><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', '', array('constraint' => 'coachnation=\''.$arr[$i+8].'\'', 'ordercol' => $ordercol))).'">'.$arr[$i+8].'</a></td>';
    echo '<td>'.$arr[$i+9].'</td>'
    .'<td>'.$arr[$i+10].'</td>'
    .'<td>'.$arr[$i+11].'</td>'
    .'<td>'.$arr[$i+12].'</td></tr>'."\n";
  }
  echo '</table>';

  echo '<input type="hidden" name="count" value="'.($arrsize/13).'" />';

  displayControlbuttons();
  echo '</form>';

  echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', 'comma', array('constraint' => $constraint, 'ordercol' => $ordercol))).'">Comma Separated File</a><br />';
  echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'payments', 'latest')).'">Latest events for all coaches</a>';

  CloseTable();

  include 'footer.php';
  return true;
}
/*}*/

?>
