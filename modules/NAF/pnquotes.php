<?

function NAF_quotes_add ($args) {
  if (!pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN)) {
    pnRedirect('/');
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  $quote = pnVarCleanFromInput('quote');
  if (strlen($quote) > 0) {
    $query = "INSERT INTO naf_quote (quote) VALUES ('".pnVarPrepForStore($quote)."')";
    $dbconn->Execute($query);
  }
  pnRedirect(pnModURL('NAF', 'quotes'));
  return true;
}
function NAF_quotes_remove ($args) {
  if (!pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN)) {
    pnRedirect('/');
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  $id = pnVarCleanFromInput('id');
  $query = "DELETE from naf_quote where quoteid=".pnVarPrepForStore($id);
  $dbconn->Execute($query);
  pnRedirect(pnModURL('NAF', 'quotes'));
  return true;
}
function NAF_quotes_main ($args) {
  if (!pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN)) {
    pnRedirect('/');
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  include 'header.php';
  OpenTable();
  echo '<div style="font-size: 2em;">NAF Quotes</div>';
  $query = "SELECT * FROM naf_quote order by quote";
  $quotes = $dbconn->Execute($query);

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'quotes', 'add')).'" method="post">';
  echo '<input type="text" name="quote" size="55" maxlength="250" />';
  echo '<br /><input type="submit" value="Add Quote" />'."\n";
  echo '</form>';
  echo '<br />';

  echo '<table border="1" cellspacing="0">';
  echo '<tr><th colspan="2">Quote</th></tr>';
  for ( ; !$quotes->EOF; $quotes->moveNext() ) {
    echo '<tr><td>'.$quotes->Fields('quote').'</td><td>(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'quotes', 'remove', array('id' => $quotes->Fields('quoteid')))).'">Del</a>)</td></tr>'."\n";
  }
  echo '</table>';

  CloseTable();
  include 'footer.php';
  return true;
}
?>
