<?
  require_once 'NAF/include/db.php';
  require_once 'NAF/include/payments.php';

  // TODO: Fix this to reflect correct permission components...
  // For now we only allow admins to access this.
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }

function getPaidUsers() {
  global $db;

  $qry = "select coachid, max(id) as id from naf_administration WHERE action='PAYMENT' "
	      ."group by coachid";
  $db->query($qry);

  $ids = "";

  while ($ob = $db->getNextObject()) {
    $ids = appendToList($ids, $ob->id);
  }

  $qry = "select pn_uid, pn_uname, admin_date as date "
        ."from naf_administration, nuke_users n, naf_coach "
        ."where id in ($ids) and n.pn_uid=naf_administration.coachid and pn_uid=naf_coach.coachid order by id";

  if ($db->query($qry) == 0) {
    echo $db->getErrorMessage();
  }

  $result = array();

  while ($ob = $db->getNextObject()) {
    array_push($result, $ob->pn_uid, $ob->pn_uname, $ob->date);
  }

  return $result;
}

  $db = new DB("bloodbo_Rouge");

$arr = getPaidUsers();

header("Content-type: text/plain");
echo "#id, username, Exp. Date\n";
for ($i=0, $sz=count($arr); $i<$sz; $i+=3)
	echo $arr[$i+0].", ".$arr[$i+1].", ".$arr[$i+2]."\n";

?>
