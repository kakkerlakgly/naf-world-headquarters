<?php
// ----------------------------------------------------------------------
/**
 * NAF Module
 *
 * Purpose of file:  User API --
 *                   The file that contains all user operational
 *                   functions for the module
 *
 * @package      NAF_modules
 * @subpackage   NAF
 * @author       Kristian Rastrup (slup)
 * @link         http://www.bloodbowl.net
 * @copyright    Copyright (C) 2006 by the NAF
 */

function NAF_userapi_getNewPayments() {
  $dbconn =& pnDBGetConn(true);
  // for reference and to prevent mistyping
  $payment = "PAYMENT";
  $package = "PACKAGE";
  $unpackage = "UNPACKAGE";

  $sql = "SELECT a.coachid, a.action FROM naf_administration a, naf_coach c, nuke_users n WHERE a.action IN('$payment', '$package', '$unpackage') AND c.coachid = a.coachid AND n.pn_uid = a.coachid ORDER BY a.coachid, a.id";
  $result =& $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo "SELECT payment";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }

  $sum = 0;
  //create default safe coach
  $currentcoach = 0;
  $currentaction = $package;

  for (; !$result->EOF; $result->MoveNext()) {
    //$id is only to be used for sorting in the SQL
    list($coach, $action) = $result->fields;
    //see if we reached the last action for $currentcoach and if the last action is PAYMENT or UNPACKAGE (ie. not PACKAGE)
    $sum += ($coach != $currentcoach && $currentaction != $package) ? 1 : 0;

    $currentcoach = $coach;
    $currentaction = $action;
  }
  //also do the check for the last coach, allthough we are sure that this coach has reached the last action
  $sum += ($currentaction != $package) ? 1 : 0;
  $result->Close();
  return $sum;
}

/*function NAF_userapi_getNewPayments() {
  $dbconn =& pnDBGetConn(true);

  $sql = "SELECT max(a.id), c.coachid FROM naf_administration a, naf_coach c, nuke_users n WHERE a.action = 'PAYMENT' AND c.coachid = a.coachid AND n.pn_uid = c.coachid GROUP BY c.coachid";
  $payment =& $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo "SELECT payment";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }

  $sum = 0;

  for (; !$payment->EOF; $payment->MoveNext()) {
    list($payDate, $payCoach) = $payment->fields;

    $sql = "SELECT id FROM naf_administration WHERE action = 'PACKAGE' AND coachid = $payCoach AND id >= $payDate order by 1 desc";
    $result =& $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
      echo "SELECT package";
      pnSessionSetVar('errormsg', _GETFAILED);
      return false;
    }
    if ($result->EOF) {
      $sum++;
    }
    else {
      list($packDate) = $result->fields;
      $sql = "SELECT 1 FROM naf_administration WHERE action = 'UNPACKAGE' AND coachid = $payCoach AND id >= $packDate";
      $result2 =& $dbconn->Execute($sql);
      if ($dbconn->ErrorNo() != 0) {
        echo "SELECT unpackage";
        pnSessionSetVar('errormsg', _GETFAILED);
        return false;
      }
      if (!$result2->EOF) {
        $sum++;
      }
      $result2->Close();
    }
    $result->Close();
  }
  $payment->Close();
  return $sum;
}*/

function NAF_userapi_getInactiveCoaches() {
  $dbconn =& pnDBGetConn(true);
  $sql = "SELECT COUNT(DISTINCT c.coachid) FROM naf_administration a, naf_coach c, nuke_users n WHERE a.action='PAYMENT' AND c.coachid = a.coachid AND a.coachid = n.pn_uid AND LENGTH(c.coachactivationcode) > 0";
  $result =& $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo "SELECT inactive coaches";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }

  list($sum) = $result->fields;
  $result->Close();
  return $sum;
}

function NAF_userapi_getNewTourneys() {
  $dbconn =& pnDBGetConn(true);
  $sql = "SELECT COUNT(1) FROM naf_tournament WHERE tournamentstatus = 'NEW'";
  $result =& $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo "Select new tournaments";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }

  list($sum) = $result->fields;
  $result->Close();
  return $sum;
}

function NAF_userapi_getTodo() {
  $dbconn =& pnDBGetConn(true);
  $sql = "SELECT COUNT(1) FROM naf_todo WHERE status <> 'DONE' AND staff = ".pnUserGetVar('uid');
  $result =& $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo "Select todo";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }

  list($sum) = $result->fields;
  $result->Close();
  return $sum;
}

function NAF_userapi_getDirtyGames() {
  $dbconn =& pnDBGetConn(true);
  $sql = "SELECT COUNT(1) FROM naf_game WHERE dirty = 'TRUE'";
  $result =& $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo "Select dirty games";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }

  list($sum) = $result->fields;
  $result->Close();
  return $sum;
}
?>