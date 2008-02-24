<?
require_once 'NAF/include/db.php';

function addAdminEvent($db, $coachid, $staff_coachid, $action, $info) {
  // First, we secure the variables
  $coachid += 0;
  $staff_coachid += 0;
  $action = addslashes($action);
  $info = addslashes($info);

  $qry = "insert into naf_administration (coachid, staff_coachid, action, admin_date, admin_info) "
        ."values ($coachid, $staff_coachid, '$action', now(), '$info')";

  $db->query($qry);
  // Set up a new expiry date
  if (strcmp($action, 'PAYMENT')==0) {
    // First, find the new expiry date.
    // At least 1 year from today, but can be further ahead
    // if the user is paying ahead of time
    $query = "SELECT if (coachexpirydate > curdate() AND coachexpirydate IS NOT NULL, "
            ."           date_add(coachexpirydate, interval 1 year), "
            ."           date_add(curdate(), interval 1 year) "
            ."          ) as newdate "
            ."FROM naf_coach "
            ."WHERE coachid=$coachid";
    $db->query($query);
    $info = $db->getNextObject();

    if ($info) {
      // And now we update the coach info with the new expiry date
      $query = "UPDATE naf_coach "
              ."SET coachexpirydate='".$info->newdate."' "
              ."WHERE coachid=$coachid";
      $db->query($query);
    }
  }
}

function getPaymentLog($view) {
  global $db;

  $view += 0;

  $qry = "select nuke_users.pn_uname, action, admin_date, admin_info from naf_administration, nuke_users "
        ."where coachid=$view and nuke_users.pn_uid=staff_coachid order by id desc";

  $db->query($qry);

  $result = array();

  while ($ob = $db->getNextObject()) {
    array_push($result, $ob->pn_uname, $ob->action, $ob->admin_date, $ob->admin_info);
  }

  return $result;
}

function getCoachName($id) {
  global $db;

  $id += 0;

  $qry = "select coachlastname, coachfirstname from naf_coach where coachid=$id";
  $db->query($qry);

  $ob = $db->getNextObject();
  if ($ob == false) {
    return "[Noone]";
  }
  return $ob->coachfirstname . " " .$ob->coachlastname;
}

function appendToList($list, $item) {
  if (strlen($list) > 0) {
    $list .= ",";
  }
  $list .= $item;

  return $list;
}

function getLatestEvents() {
  global $db;

  $qry = "select coachid, max(id) as id from naf_administration group by coachid order by coachid asc";
  $db->query($qry);
  
  $ids = "";        
  
  while ($ob = $db->getNextObject()) {
    $ids = appendToList($ids, $ob->id);
  }
  
  $qry = "select pn_uid, coachfirstname as FirstName, coachlastname as LastName, n.pn_uname, admin_date as date, action, admin_info "
        ."from naf_administration, nuke_users n, naf_coach "
        ."where id in ($ids) and n.pn_uid=naf_administration.coachid and pn_uid=naf_coach.coachid order by id desc";

  if ($db->query($qry) == 0) {
    echo $db->getErrorMessage();
  }

  $result = array();

  while ($ob = $db->getNextObject()) {
    array_push($result, $ob->pn_uid, $ob->pn_uname, $ob->FirstName, $ob->LastName, $ob->date, $ob->action, $ob->admin_info);
  }

  return $result;
}

function getNewPayments($ordercol="coachid", $constraint="") {
  global $db;
  $lastPaymentQry = "select max(id) as payDate, c.coachid, c.coachfirstname, c.coachlastname, c.coachaddress1, "
                   ."c.coachaddress2, c.coachcity, c.coachstate, c.coachzip, c.coachnation, c.coachphone, n.pn_email, "
                   ."DATE_FORMAT(DATE_ADD(a.admin_date, INTERVAL 1 YEAR), '%b %e %Y') as admin_date, c.coachexpirydate "
                   ."from naf_administration a, naf_coach c, nuke_users n "
                   ."where action='PAYMENT' and c.coachid=a.coachid and n.pn_uid=c.coachid "
                   ."group by coachid, coachfirstname, coachlastname, coachaddress1, coachaddress2, "
                   ."         coachcity, coachstate, coachzip, coachnation, coachphone, pn_email, coachexpirydate "
                   ."order by coachid asc, payDate desc";
  $lastPackageQuery = "select coachid, max(id) as packDate from naf_administration "
                     ."where action='PACKAGE' group by coachid order by coachid asc";
  $lastUnpackageQuery = "select coachid, max(id) as unpackDate from naf_administration "
                       ."where action='UNPACKAGE' group by coachid order by coachid asc";
  $lastDiceQuery = "select coachid, max(id) as diceDate from naf_administration "
                  ."where action='DICE' group by coachid order by coachid asc";

  $coachlist = "";

  $result = array();

  $dicedCoaches = array();

  $db->query($lastPackageQuery);
  $package = $db->getResult();

  $db->query($lastUnpackageQuery);
  $unpackage = $db->getResult();

  $db->query($lastPaymentQry);
  $payment = $db->getResult();

  $db->query($lastDiceQuery);
  $dice = $db->getResult();

  $packCoach = -1;
  $unpackCoach = -1;
  $diceCoach = -1;

  while ($pay = mysql_fetch_object($payment)) {
    $payCoach = $pay->coachid;
//echo $payCoach." ".$pay->admin_date."<br />";
    $payments_list[$payCoach] = $pay->admin_date;

    while ($diceCoach != -2 && ($diceCoach < $payCoach || ($diceCoach == $payCoach && $die->diceDate < $pay->payDate))) {
      $die = mysql_fetch_object($dice);
      if ($die == false) {
        $diceCoach = -2;
      }
      else {
        $diceCoach = $die->coachid;
      }
    }

    if ($diceCoach == $payCoach && $die->diceDate > $pay->payDate) {
      $dicedCoaches[$payCoach] = true;
    }

    while ($packCoach != -2 && ($packCoach < $payCoach || ($packCoach == $payCoach && $pack->packDate < $pay->payDate))) {
      $pack = mysql_fetch_object($package);
      if ($pack == false) {
        $packCoach = -2;
      }
      else {
        $packCoach = $pack->coachid;
      }
    }
    while ($unpackCoach != -2 && ($unpackCoach < $packCoach || ($unpackCoach == $packCoach && $unpack->unpackDate < $pack->packDate))) {
      $unpack = mysql_fetch_object($unpackage);
      if ($unpack == false) {
        $unpackCoach = -2;
      }
      else {
        $unpackCoach = $unpack->coachid;
      }
    }

//    if ($packCoach != $payCoach || strtotime($pack->packageDate) < strtotime($pay->payDate) ||
//        ($unpackCoach == $packCoach && strtotime($unpack->unpackageDate) >= strtotime($pack->packageDate))) {
    if ($packCoach != $payCoach || $packCoach == $unpackCoach) {
      $coachlist = appendToList($coachlist, $pay->coachid);
    }
  }

  $qry = "select coachid, coachfirstname, coachlastname, coachaddress1, coachaddress2, "
        ."coachcity, coachstate, coachzip, coachnation, coachphone, nuke_users.pn_email, DATE_FORMAT(coachexpirydate, '%b %e %Y') as coachexpirydate "
        ."from naf_coach, nuke_users "
        ."where coachid=pn_uid and coachid in ($coachlist) ";
  if (strlen($constraint) > 0) {
    $qry .= "and ($constraint) ";
  }    
  $qry.= "order by $ordercol";
  if (!$db->query($qry)) {
    echo $db->getErrorMessage();
  }

  while ($ob = $db->getNextObject()) {
//    array_push($result, $ob->coachid, $ob->coachfirstname, $ob->coachlastname,
//               $ob->coachaddress1, $ob->coachaddress2, $ob->coachcity, $ob->coachstate,
//               $ob->coachzip, $ob->coachnation, $ob->coachphone, $ob->pn_email, 
//               $payments_list[$ob->coachid], $dicedCoaches[$ob->coachid]==true?"Yes":"No");
    array_push($result, $ob->coachid, $ob->coachfirstname, $ob->coachlastname,
               $ob->coachaddress1, $ob->coachaddress2, $ob->coachcity, $ob->coachstate,
               $ob->coachzip, $ob->coachnation, $ob->coachphone, $ob->pn_email, 
               $ob->coachexpirydate, $dicedCoaches[$ob->coachid]==true?"Yes":"No");
  }    

  return $result;
}

function getInactiveCoaches($ordercol="coachid", $constraint="") {
  global $db;
  $lastPaymentQry = "select max(id) as payDate, c.coachid, c.coachfirstname, c.coachlastname, c.coachaddress1, "
                   ."c.coachaddress2, c.coachcity, c.coachstate, c.coachzip, c.coachnation, c.coachphone, n.pn_email, DATE_FORMAT(DATE_ADD(a.admin_date, INTERVAL 1 YEAR), '%b %e %Y') as admin_date "
                   ."from naf_administration a, naf_coach c, nuke_users n "
                   ."where action='PAYMENT' and c.coachid=a.coachid and n.pn_uid=c.coachid "
                   ."  AND length(coachactivationcode)>0 "
                   ."group by coachid, coachfirstname, coachlastname, coachaddress1, coachaddress2, "
                   ."         coachcity, coachstate, coachnation, coachphone, pn_email, admin_date "
                   ."order by coachid asc";

  $coachlist = "";

  $result = array();

  $db->query($lastPaymentQry);
  $payment = $db->getResult();

  while ($pay = mysql_fetch_object($payment)) {
    $payCoach = $pay->coachid;
//echo $payCoach." ".$pay->admin_date."<br />";
    $payments_list[$payCoach] = $pay->admin_date;

    $coachlist = appendToList($coachlist, $payCoach);
  }

  $qry = "select coachid, coachfirstname, coachlastname, coachaddress1, coachaddress2, "
        ."coachcity, coachstate, coachzip, coachnation, coachphone, nuke_users.pn_email "
        ."from naf_coach, nuke_users "
        ."where coachid=pn_uid and coachid in ($coachlist) ";
  if (strlen($constraint) > 0) {
    $qry .= "and ($constraint) ";
  }    
  $qry.= "order by $ordercol";
  if (!$db->query($qry)) {
    echo $db->getErrorMessage();
  }

  while ($ob = $db->getNextObject()) {
    array_push($result, $ob->coachid, $ob->coachfirstname, $ob->coachlastname,
               $ob->coachaddress1, $ob->coachaddress2, $ob->coachcity, $ob->coachstate,
               $ob->coachzip, $ob->coachnation, $ob->coachphone, $ob->pn_email, 
               $payments_list[$ob->coachid], $dicedCoaches[$ob->coachid]==true?"Yes":"No");
  }    

  return $result;
}
?>
