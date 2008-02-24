<?

function isExpired() {
  list($dbconn) = pnDBGetConn();

  if (!pnUserLoggedIn())
    return true;

  $uid = pnUserGetVar('uid');

  $isExpired = $dbconn->getOne("SELECT coachexpirydate<curdate() FROM naf_coach WHERE coachid=$uid");

  if ($isExpired > 0)
    return true;

  return false;
}

?>
