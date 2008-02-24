<?

  if (!pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN)) {
    pnRedirect("/");
    exit;
  }

  switch($op) {
    case 'add':
      list($name, $start, $image) = pnVarCleanFromInput('name', 'start', 'image');
      list($name, $start, $image) = pnVarPrepForStore($name, $start, $image);
      if (strlen($start) > 0) {
        $query = "INSERT INTO naf_gift (name, start, image) VALUES ('$name', '$start', '$image')";
        $dbconn->Execute($query);
      }
      pnRedirect("naf.php?page=premiums");
      break;
    case 'remove':
      $id = pnVarCleanFromInput('id');
      $id = pnVarPrepForStore($id);
      $query = "DELETE from naf_gift where gift_id=$id";
      $dbconn->Execute($query);
      pnRedirect("naf.php?page=premiums");
      exit;
      break;
    default:
      include 'header.php';
      OpenTable();
      echo "<div style=\"font-size: 2em;\">NAF Membership Premiums</div>";
      $query = "SELECT * FROM naf_gift order by start";
      $premiums = $dbconn->Execute($query);

      echo "<form action=\"naf.php\" method=\"post\">";
      echo "<input type=\"hidden\" name=\"page\" value=\"premiums\" />\n";
      echo "<input type=\"hidden\" name=\"op\" value=\"add\" />\n";
      echo "<b>Name:</b> <input type=\"text\" name=\"name\" maxLength=\"250\" /><br />";
      echo "<b>Start:</b> <input type=\"text\" name=\"start\" size=\"10\" maxLength=\"10\" /> (YYYY-MM-DD)<br />";
      echo "<b>Image:</b> http://www.bloodbowl.net/<input type=\"text\" name=\"image\" maxLength=\"250\" /><br />";
      echo "<br /><input type=\"submit\" value=\"Add Premium\" />\n";
      echo "</form>";
      echo "<br />";

      echo "<table border=\"1\" cellspacing=\"0\">";
      echo "<tr><th>Premium</th><th>Start Date</th><th>Image</th><th>&nbsp;</th></tr>";
      for ( ; !$premiums->EOF; $premiums->moveNext() ) {
        echo "<tr valign=\"top\"><td>".$premiums->Fields('name')."</td>"
            ."<td>".$premiums->Fields('start')."</td>"
            ."<td><a href=\"/".$premiums->Fields('image')."\">".$premiums->Fields('image')."</a><br /><img src=\"/".$premiums->Fields('image')."\" /></td>"
            ."<td>(<a href=\"naf.php?page=premiums&op=remove&id=".$premiums->Fields('gift_id')."\">Del</a>)</td></tr>\n";
      }
      echo "</table>";

      CloseTable();
      include 'footer.php';
      break;
  }

?>
