<?

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

function getLatestEvents() {
  global $db;

  $qry = "select coachid, max(id) from naf_administration group by coachid order by coachid asc";
  $db->query($qry);

  $ids = "";

  $ob = $db->getNextObject();
  if ($ob) {
    $ids .= $ob->id;
  }

  while ($ob = $db->getNextObject()) {
    $ids .= ",".$ob->id;
  }



  $qry = "select coachid, n.pn_uname, max(admin_date) as date, action, admin_info from naf_administration, nuke_users n "
        ."where n.pn_uid=coachid group by coachid, pn_uname, admin_info order by id desc";

  $db->query($qry);

  $result = array();

  while ($ob = $db->getNextObject()) {
    array_push($result, $ob->coachid, $ob->pn_uname, $ob->date, $ob->action, $ob->admin_info);
  }

  return $result;
}

function getNewPayments() {
  global $db;
  $lastPaymentQry = "select max(admin_date) as payDate, c.coachid, c.coachfirstname, c.coachlastname, c.coachaddress1, "
                   ."c.coachaddress2, c.coachcity, c.coachstate, c.coachzip, c.coachnation, c.coachphone, n.pn_email "
                   ."from naf_administration a, naf_coach c, nuke_users n "
                   ."where action='PAYMENT' and c.coachid=a.coachid and n.pn_uid=c.coachid "
                   ."group by coachid order by coachid asc";
  $lastPackageQuery = "select coachid, max(admin_date) as packageDate from naf_administration "
                     ."where action='PACKAGE' group by coachid order by coachid asc";
  $lastUnpackageQuery = "select coachid, max(admin_date) as unpackageDate from naf_administration "
                       ."where action='UNPACKAGE' group by coachid order by coachid asc";


  $result = array();

  $db->query($lastPackageQuery);
  $package = $db->getResult();

  $db->query($lastUnpackageQuery);
  $unpackage = $db->getResult();

  $db->query($lastPaymentQry);
  $payment = $db->getResult();

  $packCoach = -1;
  $unpackCoach = -1;

  while ($pay = mysql_fetch_object($payment)) {
    $payCoach = $pay->coachid;
    while ($packCoach != -2 && ($packCoach < $payCoach || $pack->packDate < $pay->payDate)) {
      $pack = mysql_fetch_object($package);
      if ($pack == false) {
        $packCoach = -2;
      }
      else {
        $packCoach = $pack->coachid;
      }
    }
    while (($unpackCoach < $payCoach || $unpack->unpackDate >= $pack->packDate) && $unpackCoach != -2) {
      $unpack = mysql_fetch_object($unpackage);
      if ($unpack == false) {
        $unpackCoach = -2;
      }
      else {
        $unpackCoach = $unpack->coachid;
      }
    }

//echo $packCoach . " " . $payCoach . " " . $unpackCoach . "<br>";
//echo strtotime($pack->packageDate) . " " . strtotime($pay->payDate) . " " . strtotime($unpack->unpackageDate) . "<br>";
    if ($packCoach != $payCoach || strtotime($pack->packageDate) < strtotime($pay->payDate) ||
        ($unpackCoach == $packCoach && strtotime($unpack->unpackageDate) >= strtotime($pack->packageDate))) {
      array_push($result, $pay->coachid, $pay->coachfirstname, $pay->coachlastname,
                 $pay->coachaddress1, $pay->coachaddress2, $pay->coachcity, $pay->coachstate,
                 $pay->coachzip, $pay->coachnation, $pay->coachphone, $pay->pn_email);
    }    
  }

  return $result;
}
?>
