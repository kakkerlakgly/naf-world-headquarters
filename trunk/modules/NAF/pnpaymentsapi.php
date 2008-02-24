<?
function addAdminEvent($coachid, $staff_coachid, $action, $info) {
  $dbconn =& pnDBGetConn(true);
  // First, we secure the variables
  $coachid += 0;
  $staff_coachid += 0;
  $action = addslashes($action);
  $info = addslashes($info);

  $qry = "insert into naf_administration (coachid, staff_coachid, action, admin_date, admin_info) "
  ."values (".pnVarPrepForStore($coachid).", ".pnVarPrepForStore($staff_coachid).", '".pnVarPrepForStore($action)."', now(), '".pnVarPrepForStore($info)."')";

  $dbconn->Execute($qry);
  // Set up a new expiry date
  if (strcmp($action, 'PAYMENT')==0) {
    // First, find the new expiry date.
    // At least 1 year from today, but can be further ahead
    // if the user is paying ahead of time
    $qry = "SELECT if (coachexpirydate > curdate() AND coachexpirydate IS NOT NULL, "
    ."           date_add(coachexpirydate, interval 1 year), "
    ."           date_add(curdate(), interval 1 year) "
    ."          ) as newdate "
    ."FROM naf_coach "
    ."WHERE coachid=".pnVarPrepForStore($coachid);
    $result = $dbconn->Execute($qry);

    if (!$result->EOF) {
      // And now we update the coach info with the new expiry date
      $qry = "UPDATE naf_coach "
      ."SET coachexpirydate='".pnVarPrepForStore($result->Fields('newdate'))."' "
      ."WHERE coachid=".pnVarPrepForStore($coachid);
      $dbconn->Execute($qry);
    }
  }
}

function getPaymentLog($view) {
  $dbconn =& pnDBGetConn(true);

  $view += 0;

  $qry = "select nuke_users.pn_uname, action, admin_date, admin_info from naf_administration, nuke_users "
  ."where coachid=".pnVarPrepForStore($view)." and nuke_users.pn_uid=staff_coachid order by id desc";

  $result = $dbconn->Execute($qry);

  $resultarr = array();

  for (; !$result->EOF; $result->MoveNext()) {
      array_push($resultarr, $result->Fields('pn_uname'), $result->Fields('action'), $result->Fields('admin_date'), $result->Fields('admin_info'));
  }

  return $resultarr;
}

function getCoachName($id) {
  $dbconn =& pnDBGetConn(true);

  $id += 0;

  $qry = "select coachlastname, coachfirstname from naf_coach where coachid=".pnVarPrepForStore($id);
  $result = $dbconn->Execute($qry);

  if ($result->EOF) {
    return "[Noone]";
  }
  return $result->Fields('coachfirstname') . ' ' .$result->Fields('coachlastname');
}

function appendToList($list, $item) {
  if (strlen($list) > 0) {
    $list .= ",";
  }
  $list .= $item;

  return $list;
}

function getLatestEvents() {
  $dbconn =& pnDBGetConn(true);

  $qry = "select coachid, max(id) as id from naf_administration group by coachid order by coachid asc";
  $result = $dbconn->Execute($qry);

  $ids = "";

  for (; !$result->EOF; $result->MoveNext()) {
    $ids = appendToList($ids, $result->Fields('id'));
  }

  $qry = "select pn_uid, coachfirstname as FirstName, coachlastname as LastName, n.pn_uname, admin_date as date, action, admin_info "
  ."from naf_administration, nuke_users n, naf_coach "
  ."where id in ($ids) and n.pn_uid=naf_administration.coachid and pn_uid=naf_coach.coachid order by id desc";

  $resultarr = array();

  $result = $dbconn->Execute($qry);
  for (; !$result->EOF; $result->MoveNext()) {
    array_push($resultarr, $result->Fields('pn_uid'), $result->Fields('pn_uname'), $result->Fields('FirstName'),
     $result->Fields('LastName'),
     $result->Fields('date'), $result->Fields('action'), $result->Fields('admin_info'));
  }

  return $resultarr;
}

function getNewPayments($ordercol="coachid", $constraint="") {
  $dbconn =& pnDBGetConn(true);
  $payment = "PAYMENT";
  $package = "PACKAGE";
  $unpackage = "UNPACKAGE";

  $sql = "SELECT a.id, a.action, c.*, n.pn_email FROM naf_administration a, naf_coach c, nuke_users n WHERE a.action IN('".pnVarPrepForStore($payment)."', '".pnVarPrepForStore($package)."', '".pnVarPrepForStore($unpackage)."') AND c.coachid = a.coachid AND n.pn_uid = c.coachid ";
  if($constraint != '') $sql .= " AND ".$constraint." ";
  $sql .= "ORDER BY ".($ordercol=='coachid'?'':$ordercol.', ')."a.coachid, a.id";
  $result = $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo "SELECT payment";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }

  //create default safe coach
  $currentcoach = 0;
  $currentaction = $package;
  $currentid = 0;
  $arr = array();
  $temparr = array();

  for (; !$result->EOF; $result->MoveNext()) {
    //$id is only to be used for sorting in the SQL
    $coach = $result->Fields('coachid');
    $action = $result->Fields('action');
    $id = $result->Fields('id');
    //see if we reached the last action for $currentcoach and if the last action is PAYMENT or UNPACKAGE (ie. not PACKAGE)
    if($coach != $currentcoach && $currentaction != $package) {
      $sql = "SELECT * FROM naf_administration WHERE coachid = ".pnVarPrepForStore($currentcoach)." AND action='DICE' AND id > ".pnVarPrepForStore($currentid);
      $dice = $dbconn->Execute($sql);
      $arr = array_merge($arr, $temparr);
      array_push($arr, $dice->EOF ? 'No' : 'Yes');
    }
    $temparr = array();
    array_push($temparr, $coach, $result->Fields('coachfirstname'), $result->Fields('coachlastname'),
    $result->Fields('coachaddress1'), $result->Fields('coachaddress2'), $result->Fields('coachcity'), $result->Fields('coachstate'),
    $result->Fields('coachzip'), $result->Fields('coachnation'), $result->Fields('coachphone'), $result->Fields('pn_email'), //pnUserGetVar('email', $result->Fields('coachid')),
    $result->Fields('coachexpirydate'));

    $currentcoach = $coach;
    $currentaction = $action;
    $currentid = $id;
  }
  //also do the check for the last coach, allthough we are sure that this coach has reached the last action
  if($currentaction != $package){
    $arr = array_merge($arr, $temparr);
    $sql = "SELECT * FROM naf_administration WHERE coachid = ".pnVarPrepForStore($temparr[0])." AND action='DICE' AND id > ".pnVarPrepForStore($id);
    $dice = $dbconn->Execute($sql);
    array_push($arr, $dice->EOF ? 'No' : 'Yes');
  }
  $result->Close();
  return $arr;
}

function getInactiveCoaches($ordercol="coachid", $constraint="") {
  $dbconn =& pnDBGetConn(true);
  $qry = "select max(id) as payDate, c.coachid, c.coachfirstname, c.coachlastname, c.coachaddress1, "
  ."c.coachaddress2, c.coachcity, c.coachstate, c.coachzip, c.coachnation, c.coachphone, n.pn_email, DATE_FORMAT(DATE_ADD(a.admin_date, INTERVAL 1 YEAR), '%b %e %Y') as admin_date "
  ."from naf_administration a, naf_coach c, nuke_users n "
  ."where action='PAYMENT' and c.coachid=a.coachid and n.pn_uid=c.coachid "
  ."  AND length(coachactivationcode)>0 "
  ."group by coachid, coachfirstname, coachlastname, coachaddress1, coachaddress2, "
  ."         coachcity, coachstate, coachnation, coachphone, pn_email, admin_date "
  ."order by coachid asc";

  $result = $dbconn->Execute($qry);

  $coachlist = "";

  for (; !$result->EOF; $result->MoveNext()) {
    $payCoach = $result->Fields('coachid');
    $payments_list[$payCoach] = $result->Fields('admin_date');

    $coachlist = appendToList($coachlist, $payCoach);
  }

  $qry = "select coachid, coachfirstname, coachlastname, coachaddress1, coachaddress2, "
  ."coachcity, coachstate, coachzip, coachnation, coachphone, nuke_users.pn_email "
  ."from naf_coach, nuke_users "
  ."where coachid=pn_uid and coachid in (".pnVarPrepForStore($coachlist).") ";
  if (strlen($constraint) > 0) {
    $qry .= "and ($constraint) ";
  }
  $qry.= "order by ".pnVarPrepForStore($ordercol);
  $result = $dbconn->Execute($qry);

  $resultarr = array();

  for (; !$result->EOF; $result->MoveNext()) {
    array_push($resultarr, $result->Fields('coachid'), $result->Fields('coachfirstname'), $result->Fields('coachlastname'),
    $result->Fields('coachaddress1'), $result->Fields('coachaddress2'), $result->Fields('coachcity'), $result->Fields('coachstate'),
    $result->Fields('coachzip'), $result->Fields('coachnation'), $result->Fields('coachphone'), $result->Fields('pn_email'),
    $payments_list[$result->Fields('coachid')], $dicedCoaches[$result->Fields('coachid')]==true?"Yes":"No");
  }

  return $resultarr;
}
?>
