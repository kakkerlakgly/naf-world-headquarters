<?
require_once 'modules/NAF/pnpaymentsapi.php';
require_once 'NAF/include/welcomeMessage.php';

function NAF_activation_log($args) {
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
function NAF_activation_action($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }
  $dbconn =& pnDBGetConn(true);
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
        ."WHERE nu.pn_uid = c.coachid AND c.coachid = ".pnVarPrepForStore($coaches[$i]);
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
        $query = "UPDATE naf_coach SET coachactivationcode='' WHERE coachid=".pnVarPrepForStore($coaches[$i]);
        $result = $dbconn->Execute($query);
        if (!$result) {
          echo "Error while activating coach: ".$dbconn->errorMsg();
          exit;
        }
      }
      else {
        echo "Command not supported: ".pnVarPrepForDisplay($submit);
        exit;
      }
    }
  }
  pnRedirect(pnModURL('NAF', 'activation'));
  return true;
}
function NAF_activation_main($args) {
  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }
  list($ordercol, $constraint) = pnVarCleanFromInput('ordercol', 'constraint');
  if (strlen($ordercol) == 0) {
    $ordercol = "coachid";
  }
  $arr = getInactiveCoaches($ordercol, $constraint);
  $arrsize = count($arr);

  include 'header.php';

  OpenTable();

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', 'action')).'" method="post">';

  echo '<table border="1">';
  echo '<tr><th colspan="15">Paid inactive coaches'.(strlen($constraint)>0?'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('ordercol' => $ordercol))).'">(Show All)</a>':'').'</th></tr>';
  echo '<tr><th>&nbsp;</th><th>Log</th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachid'))).'">ID</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachfirstname'))).'">First Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachlastname'))).'">Last Name</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachaddress1'))).'">Address 1</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachaddress2'))).'">Address 2</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachcity'))).'">City</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachstate'))).'">State</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachzip'))).'">Zip</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachnation'))).'">Nation</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachphone'))).'">Phone</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'pn_email'))).'">Email</a></th>'
  .'<th><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => $constraint, 'ordercol' => 'coachid'))).'">Exp. Date</a></th>'
  .'<th>Got Dice?</th>'
  .'</tr>'."\n";
  for($i=0;$i < $arrsize;$i+=13) {
    echo '<tr><td><input type="checkbox" name="coach['.($i/13).']" value="'.$arr[$i].'" /></td>';
    echo '<td><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', 'log', array('view' => $arr[$i]))).'">Log</a></td>';
    echo '<td>'.$arr[$i+0].'</td>'
    .'<td>'.$arr[$i+1].'</td>'
    .'<td>'.$arr[$i+2].'</td>'
    .'<td>'.$arr[$i+3].'</td>'
    .'<td>'.$arr[$i+4].'</td>'
    .'<td>'.$arr[$i+5].'</td>'
    .'<td>'.$arr[$i+6].'</td>'
    .'<td>'.$arr[$i+7].'</td>';
    echo '<td><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'activation', '', array('constraint' => 'coachnation=\''.$arr[$i+8].'\'', 'ordercol' => $ordercol))).'">'.$arr[$i+8].'</a></td>';
    echo '<td>'.$arr[$i+9].'</td>'
    .'<td>'.$arr[$i+10].'</td>'
    .'<td>'.$arr[$i+11].'</td>'
    .'<td>'.$arr[$i+12].'</td></tr>'."\n";
  }
  echo '</table>';

  echo '<input type="hidden" name="count" value="'.($arrsize/13).'" />';

  echo '<input type="submit" name="submit" value="Resend Activation Code" />';
  echo ' &nbsp; &nbsp; ';
  echo '<input type="submit" name="submit" value="Activate Account" /> ';
  echo '</form>';

  CloseTable();

  include 'footer.php';
  return true;
}

?>
