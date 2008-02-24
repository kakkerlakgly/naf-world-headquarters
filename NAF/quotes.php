<?

  if (!pnSecAuthAction(0, "NAF::", "::", ACCESS_ADMIN)) {
    pnRedirect("/");
    exit;
  }

  switch($op) {
    case 'add':
      $quote = pnVarCleanFromInput('quote');
      $quote = pnVarPrepForStore($quote);
      if (strlen($quote) > 0) {
        $query = "INSERT INTO naf_quote (quote) VALUES ('$quote')";
        $dbconn->Execute($query);
      }
      pnRedirect("naf.php?page=quotes");
      break;
    case 'remove':
      $id = pnVarCleanFromInput('id');
      $id = pnVarPrepForStore($id);
      $query = "DELETE from naf_quote where quoteid=$id";
      $dbconn->Execute($query);
      pnRedirect("naf.php?page=quotes");
      exit;
      break;
    default:
      include 'header.php';
      OpenTable();
      echo "<div style=\"font-size: 2em;\">NAF Quotes</div>";
      $query = "SELECT * FROM naf_quote order by quote";
      $quotes = $dbconn->Execute($query);

      echo "<form action=\"naf.php\" method=\"post\">";
      echo "<input type=\"hidden\" name=\"page\" value=\"quotes\" />\n";
      echo "<input type=\"hidden\" name=\"op\" value=\"add\" />\n";
      echo "<input type=\"text\" name=\"quote\" size=\"55\" maxLength=\"250\" />";
      echo "<br /><input type=\"submit\" value=\"Add Quote\" />\n";
      echo "</form>";
      echo "<br />";

      echo "<table border=\"1\" cellspacing=\"0\">";
      echo "<tr><th colspan=\"2\">Quote</th></tr>";
      for ( ; !$quotes->EOF; $quotes->moveNext() ) {
        echo "<tr><td>".$quotes->Fields('quote')."</td><td>(<a href=\"naf.php?page=quotes&op=remove&id=".$quotes->Fields('quoteid')."\">Del</a>)</td></tr>\n";
      }
      echo "</table>";

      CloseTable();
      include 'footer.php';
      break;
  }

?>
