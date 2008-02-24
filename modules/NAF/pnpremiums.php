<?
function NAF_premiums_add($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect(pnGetBaseURL());
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  list($name, $start, $image) = pnVarCleanFromInput('name', 'start', 'image');
  list($name, $start, $image) = pnVarPrepForStore($name, $start, $image);
  if (strlen($start) > 0) {
    $query = "INSERT INTO naf_gift (name, start, image) VALUES ('$name', '$start', '$image')";
    $dbconn->Execute($query);
  }
  pnRedirect(pnModURL('NAF', 'premiums'));
  return true;
}
function NAF_premiums_remove($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect(pnGetBaseURL());
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  $id = pnVarCleanFromInput('id');
  $id = pnVarPrepForStore($id);
  $query = "DELETE from naf_gift where gift_id=$id";
  $dbconn->Execute($query);
  pnRedirect(pnModURL('NAF', 'premiums'));
  return true;
}
function NAF_premiums_main($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect(pnGetBaseURL());
    exit;
  }
  $dbconn =& pnDBGetConn(true);
  include 'header.php';
  OpenTable();
  echo '<div style="font-size: 2em;">NAF Membership Premiums</div>';
  $query = "SELECT * FROM naf_gift order by start";
  $premiums = $dbconn->Execute($query);

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'premiums', 'add')).'" method="post">';
  echo '<b>Name:</b> <input type="text" name="name" maxlength="250" /><br />';
  echo '<b>Start:</b> <input type="text" name="start" size="10" maxlength="10" /> (YYYY-MM-DD)<br />';
  echo '<b>Image:</b> http://www.bloodbowl.net/<input type="text" name="image" maxlength="250" /><br />';
  echo '<br /><input type="submit" value="Add Premium" />'."\n";
  echo '</form>';
  echo '<br />';

  echo '<table border="1" cellspacing="0">';
  echo '<tr><th>Premium</th><th>Start Date</th><th>Image</th><th>&nbsp;</th></tr>';
  for ( ; !$premiums->EOF; $premiums->moveNext() ) {
    echo '<tr valign="top"><td>'.$premiums->Fields('name').'</td>'
    .'<td>'.$premiums->Fields('start').'</td>'
    .'<td><a href="'.pnVarPrepForDisplay(pnGetBaseURL().$premiums->Fields('image')).'">'.pnVarPrepForDisplay($premiums->Fields('image')).'</a><br /><img src="'.pnVarPrepForDisplay(pnGetBaseURL().$premiums->Fields('image')).'" alt="'.pnVarPrepForDisplay(pnGetBaseURL().$premiums->Fields('image')).'"/></td>'
    .'<td>(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'premiums', 'remove', array('id' => $premiums->Fields('gift_id')))).'">Del</a>)</td></tr>'."\n";
  }
  echo '</table>';

  CloseTable();
  include 'footer.php';
  return true;
}

?>
