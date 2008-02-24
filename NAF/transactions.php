<?

  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }

include 'NAF/include/ipn.php';

$uid = pnVarCleanFromInput('uid')+0;
$op = pnVarCleanFromInput('op');

if ($uid==0) {
  include 'header.php';
  OpenTable();
  echo "<form action=\"naf.php\" method=\"get\">";
  echo "<input type=\"hidden\" name=\"page\" value=\"transactions\" />";
  echo "Userid: <input type=\"text\" name=\"uid\" /><br />";
  echo "<input type=\"submit\" value=\"View transactions\" />";
  echo "</form>";
  CloseTable();
  include 'footer.php';
}
else {
  switch($op) {
  case 'reverify':
    $db = new DB('bloodbo_Rouge');
    $ipn = new IPN();
    $txn = pnVarCleanFromInput('txn');
    $txn = pnVarPrepForStore($txn);
    $ipn->restoreIPN($db, $txn);
    echo $ipn->verify();
    break;
  default:
    $query = "SELECT * FROM naf_transactions WHERE item_number=$uid";
    $res = $dbconn->execute($query);
    if (!$res->EOF) {
      include 'header.php';
      OpenTable();
      echo "<table cellspacing=\"0\" border=\"1\">";
      echo "<tr><th>Txn id</th><th>date</th><th>Status</th><th>Error</th></tr>";
      for ( ; !$res->EOF; $res->moveNext()) {
        echo "<tr><td>".$res->fields('naf_txn')."</td><td>".$res->Fields('payment_date')."</td><td>".$res->Fields('payment_status')."</td><td>".$res->Fields('errorCode')."</td>";
      }
      echo "</table>";
      echo "<a href=\"naf.php?page=transactions\">Back</a>";
      CloseTable();
      include 'footer.php';
    }
    else {
      include 'header.php';
      OpenTable();
      echo "No transactions recorded for that user.<br />";
      echo "<a href=\"naf.php?page=transactions\">Back</a>";
      CloseTable();
      include 'footer.php';
    }
    break;
  }
}
  

?>
