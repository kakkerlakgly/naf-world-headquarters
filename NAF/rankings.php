<?
pnRedirect(pnModURL('Stars', 'rankings'));
exit;
  $ROWS_PER_PAGE = 50;
  $start = pnVarCleanFromInput('start');

  if ($start > 0)
    $start -= 1;

  include 'header.php';
  OpenTable();

  list($raceid, $nation) = pnVarCleanFromInput('race', 'nation');
  $nation = urldecode($nation);

  $query = "SELECT DISTINCT coachnation FROM naf_coach c, naf_coachranking cr "
          ."WHERE (coachactivationcode IS NOT NULL "
          ."       OR  length(coachactivationcode)=0) "
          ."  AND length(coachnation) > 0 "
          ."  AND c.coachid=cr.coachid "
          ."ORDER BY coachnation";
  $res = $dbconn->Execute($query);
  $nationOptions = "<option value=\"\">All Nations</option>\n";
  for ( ; !$res->EOF; $res->moveNext() ) {
    $val = $res->fields[0];
    $enc = urlencode($val);
    $nationOptions .= "<option".($nation == $val?" selected=\"1\"":"")." value=\"$enc\">".$val."</option>\n";
  }

  $query = "SELECT raceid, name from naf_race order by name";
  $race = $dbconn->Execute($query);

  echo "<div style=\"float: right;\">";

  $raceOptions = "<option value=\"0\">All Races</option>\n";
  for ( ; !$race->EOF; $race->moveNext() ) {
    $name = $race->fields[1];
    $rid = $race->fields[0];
    if ($rid == $raceid)
      $racename = $name;
    $raceOptions .= "<option value=\"".$race->fields[0]."\""
                           .($raceid==$race->fields[0]?" selected=\"1\"":"").">".$race->fields[1]."</option>\n";
  }
  echo "<br /><form method=\"get\" action=\"naf.php\">"
      ."<input type=\"hidden\" name=\"page\" value=\"rankings\">\n"
      ."<input type=\"hidden\" name=\"race\" value=\"$race\">\n"
      ."<input type=\"hidden\" name=\"page\" value=\"rankings\">\n"
      ."<b>Nation</b><br />"
      ."<select name=\"nation\">\n$nationOptions</select>\n<br />"
      ."<b>Race</b><br />"
      ."<select name=\"race\">$raceOptions</select>"
      ."<div align=\"center\" style=\"margin-top: 3px;\"><input type=\"submit\" value=\"View\" /></div>"
      ."</form>";
  echo "</div>";

  echo "<div align=\"left\">"
      ."<h3>NAF Coach Rankings</h3>"
      ."</div>";

  if ($raceid > 0) {
    if (strlen($nation) == 0)
      $query = "SELECT count(1) FROM naf_coachranking WHERE raceid=$raceid";
    else
      $query = "SELECT count(1) FROM naf_coachranking cr LEFT JOIN naf_coach using (coachid) "
              ."WHERE raceid=$raceid AND coachnation='$nation'";

    $count = $dbconn->Execute($query);
    $count = $count->fields[0];

    $pages = floor($count / $ROWS_PER_PAGE) + ($count%$ROWS_PER_PAGE > 0 ? 1 : 0);

    if ($pages > 1) {
      echo "<div align=\"center\">";
      if ($start > 0)
        echo " <a href=\"PHP_SELF?nation=".urlencode($nation)."&page=rankings&race=$raceid&start=".($start)."\">&lt;&lt;</a>";

      for ($i=0; $i<$pages; $i++) {
        if ($start == $i)
          echo " ".($i+1);
        else
          echo " <a href=\"$PHP_SELF?nation=".urlencode($nation)."&page=rankings&race=$raceid&start=".($i+1)."\">".($i+1)."</a>";
      }
      if ($start < $pages-1)
        echo " <a href=\"PHP_SELF?nation=".urlencode($nation)."&page=rankings&race=$raceid&start=".($start+2)."\">&gt;&gt;</a>";

      echo " </div><br>";
    }

    $query = "SELECT coachfirstname, coachlastname, ranking, pn_uname, coachnation "
            ."FROM naf_coach, naf_coachranking, nuke_users "
            ."WHERE naf_coach.coachid = naf_coachranking.coachid and naf_coach.coachid=pn_uid "
            ."  AND raceid=$raceid "
            .($nation!=''?"  AND coachnation='$nation' ":"")
            ."ORDER BY ranking desc, coachfirstname, coachlastname "
            ."LIMIT ".($start * $ROWS_PER_PAGE).",$ROWS_PER_PAGE";
    $rankings = $dbconn->Execute($query);

    if ($rankings->EOF) {
    echo "<div align=\"center\">"
        ."No rankings have been registered for $racename."
        ."</div>";
    }
    else {
      echo "<table width=\"80%\" cellpadding=\"2\" cellspacing=\"1\" bgcolor=\"#858390\" border=\"0\" align=\"center\">";
      echo "<tr><th bgcolor=\"#D9D8D0\" colspan=\"5\">Rankings for $racename</th></tr>";
      echo "<tr><th bgcolor=\"#D9D8D0\">#</th><th bgcolor=\"#D9D8D0\">Real Name</th><th bgcolor=\"#D9D8D0\">Username</th><th bgcolor=\"#D9D8D0\">Nation</th><th bgcolor=\"#D9D8D0\">Rating</th></tr>";
      for ($count=$start*$ROWS_PER_PAGE+1; !$rankings->EOF; $count++, $rankings->MoveNext() ) {
        echo "<tr>";
        echo "<td bgcolor=\"#D9D8D0\" align=\"right\">$count</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$rankings->fields[0]." ".$rankings->fields[1]."</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$rankings->fields[3]."</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$rankings->fields[4]."</td>";
        echo "<td bgcolor=\"#f8f7ee\" align=\"right\"><a href=\"naf.php?page=tournamentinfo&uname=".urlencode($rankings->fields[3])."\">".($rankings->fields[2]/100)."</a></td>";
        echo "</tr>";
      }
      echo "</table><br />";
    }
  }
  else {
    echo "<div align=\"center\">"
        ."Use the dropdowns on the right to view subsets of the data."
        ."</div><br />";

    if (strlen($nation) == 0)
      $query = "SELECT count(1) FROM naf_coachranking";
    else
      $query = "SELECT count(1) FROM naf_coachranking cr LEFT JOIN naf_coach using (coachid) "
              ."WHERE coachnation='$nation'";

    $count = $dbconn->Execute($query);
    $count = $count->fields[0];

    $pages = floor($count / $ROWS_PER_PAGE) + ($count%$ROWS_PER_PAGE > 0 ? 1 : 0);

    if ($pages > 1) {
      echo "<div align=\"center\">";
      if ($start > 0)
        echo " <a href=\"$PHP_SELF?nation=".urlencode($nation)."&page=rankings&start=".($start)."\">&lt;&lt;</a>";

      for ($i=0; $i<$pages; $i++) {
        if ($start == $i)
          echo " ".($i+1);
        else
          echo " <a href=\"$PHP_SELF?nation=".urlencode($nation)."&page=rankings&start=".($i+1)."\">".($i+1)."</a>";
      }
      if ($start < $pages-1)
        echo " <a href=\"$PHP_SELF?nation=".urlencode($nation)."&page=rankings&start=".($start+2)."\">&gt;&gt;</a>";

      echo " </div><br>";
    }

    $query = "SELECT coachfirstname, coachlastname, ranking, pn_uname, coachnation, naf_race.name "
            ."FROM naf_coach, naf_coachranking, nuke_users, naf_race "
            ."WHERE naf_coach.coachid = naf_coachranking.coachid and naf_coach.coachid=pn_uid "
            ."  AND naf_race.raceid = naf_coachranking.raceid "
            .($nation!=''?"  AND coachnation='$nation' ":"")
            ."ORDER BY ranking desc, coachfirstname, coachlastname "
            ."LIMIT ".($start * $ROWS_PER_PAGE).",$ROWS_PER_PAGE";
    $rankings = $dbconn->Execute($query);

      echo "<table width=\"80%\" cellpadding=\"2\" cellspacing=\"1\" bgcolor=\"#858390\" border=\"0\" align=\"center\">";
      echo "<tr><th bgcolor=\"#D9D8D0\" colspan=\"6\">Overall Rankings</th></tr>";
      echo "<tr><th bgcolor=\"#D9D8D0\">#</th><th bgcolor=\"#D9D8D0\">Real Name</th><th bgcolor=\"#D9D8D0\">Username</th><th bgcolor=\"#D9D8D0\">Nation</th><th bgcolor=\"#D9D8D0\">Race</th><th bgcolor=\"#D9D8D0\">Rating</th></tr>";
      for ($count=$start*$ROWS_PER_PAGE+1; !$rankings->EOF; $count++, $rankings->MoveNext() ) {
        echo "<tr>";
        echo "<td bgcolor=\"#D9D8D0\" align=\"right\">$count</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$rankings->fields[0]." ".$rankings->fields[1]."</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$rankings->fields[3]."</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$rankings->fields[4]."</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$rankings->fields[5]."</td>";
        echo "<td bgcolor=\"#f8f7ee\" align=\"right\"><a href=\"naf.php?page=tournamentinfo&uname=".urlencode($rankings->fields[3])."\">".($rankings->fields[2]/100)."</a></td>";
        echo "</tr>";
      }
      echo "</table><br />";

  }

  CloseTable();
  include 'footer.php';
?>
