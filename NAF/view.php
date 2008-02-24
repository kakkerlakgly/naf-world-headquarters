<?
pnRedirect(pnModURL('NAF', 'view'));
exit;
require_once 'NAF/include/updater.php';
$tournament_id = pnVarCleanFromInput('id');
$advanced = pnVarCleanFromInput('advanced');
$game_id = pnVarCleanFromInput('game_id');
$initial_reputation = 15000;


if (pnSecAuthAction(0, 'NAF::', 'TournamentAdmin::', ACCESS_ADMIN)) {
  $secAdmin=true;
}
else {
  $secAdmin=false;
}

include 'header.php';
OpenTable();

switch($op) {
  case 'delete':
    deleteGame($game_id);
    break;
  case 'save':
    list($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1,
    $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $s2, $gate, $game_id) =
    pnVarCleanFromInput('c1', 'r1', 'tr1', 'bh1', 'si1', 'rip1', 'w1', 's1',
                            'c2', 'r2', 'tr2', 'bh2', 'si2', 'rip2', 'w2', 's2', 'gate', 'game_id');
                            //Update changed match



                            //Get race for coach 1
                            if (!$r1){
                              $query = "SELECT race "
                              ."FROM naf_tournamentcoach "
                              ."WHERE naftournament=$tournament_id "
                              ."  AND nafcoach=$c1";
                              $race = $dbconn->Execute($query);
                              $r1 = $race->fields[0];
                            }
                            //Get race for coach 2
                            if (!$r2){
                              $query = "SELECT race "
                              ."FROM naf_tournamentcoach "
                              ."WHERE naftournament=$tournament_id "
                              ."  AND nafcoach=$c2";
                              $race = $dbconn->Execute($query);
                              $r2 = $race->fields[0];
                            }

                            //Check for reputation for coach/race #1
                            $query = "SELECT ranking "
                            ."FROM naf_coachranking "
                            ."WHERE coachid=$c1 "
                            ."  AND raceid=$r1";
                            $repcheck = $dbconn->Execute($query);
                            if ($repcheck->EOF) {
                              //No reputation for this coach/race. Insert new record
                              $sqlupdate = "INSERT INTO naf_coachranking "
                              ."(coachid,raceid,ranking) "
                              ."VALUES ("
                              .$c1.","
                              .$r1.","
                              .$initial_reputation
                              .")";
                              $update = $dbconn->Execute($sqlupdate);
                              $rep1=$initial_reputation;
                            }else{
                              $rep1 = $repcheck->fields[0];
                            }

                            //Check for reputation for coach/race #2
                            $query = "SELECT ranking "
                            ."FROM naf_coachranking "
                            ."WHERE coachid=$c2 "
                            ."  AND raceid=$r2";
                            $repcheck = $dbconn->Execute($query);
                            if ($repcheck->EOF) {
                              //No reputation for this coach/race. Insert new record
                              $sqlupdate = "INSERT INTO naf_coachranking "
                              ."(coachid,raceid,ranking) "
                              ."VALUES ("
                              .$c2.","
                              .$r2.","
                              .$initial_reputation
                              .")";
                              $update = $dbconn->Execute($sqlupdate);
                              $rep2=$initial_reputation;
                            }else{
                              $rep2 = $repcheck->fields[0];
                            }




                            $query = "UPDATE naf_game SET "

                            ."homecoachid = $c1 , "
                            ."awaycoachid = $c2 , "
                            ."racehome = $r1 , "
                            ."raceaway = $r2 , "
                            ."trhome = $tr1 , "
                            ."traway = $tr2 , "
                            ."rephome = $rep1 , "
                            ."repaway = $rep2 , "
                            ."goalshome = $s1 , "
                            ."goalsaway = $s2 , "
                            ."badlyhurthome = ".($bh1 + 0).", "
                            ."badlyhurtaway = ".($bh2 + 0)." , "
                            ."serioushome = ".($si1 + 0)." , "
                            ."seriousaway = ".($si2 + 0)." , "
                            ."killshome = ".($rip1 + 0)." , "
                            ."killsaway = ".($rip2 + 0)." , "
                            ."gate = ".($gate + 0)." , "
                            ."winningshome = ".($w1 + 0)." , "
                            ."winningsaway = ".($w2 + 0)." , "
                            ."dirty = \"TRUE\" "
                            ."WHERE gameid=$game_id";
                            $dbconn->Execute($query);
                            break;
  case 'edit':
    function createCoachSelection($selected) {
      global $coachNameArr, $coachIdArr;

      $coachSel = "<option value=\"\">[ Select Coach ]</option>";
      for ($i=0; $i<count($coachNameArr); $i++) {
        $coachSel .= "<option value=\"".$coachIdArr[$i]."\"".($selected==$coachIdArr[$i]?" selected=\"1\"":"").">".$coachNameArr[$i]."</option>";
      }
      return $coachSel;
    }
    $query = "SELECT * "
				."FROM naf_game "
				."WHERE gameid=$game_id ";
				echo $query."\n";
				$game = $dbconn->Execute($query);

				$qry = "select pn_uid, pn_uname from nuke_users u, naf_tournamentcoach tc where tc.nafcoach=u.pn_uid and "
				."tc.naftournament=$tournament_id order by pn_uname";
				$coaches = $dbconn->Execute($qry);
				for ($i=0; !$coaches->EOF; $i++, $coaches->MoveNext() ) {
				  $coachNameArr[$i] = $coaches->Fields('pn_uname');
				  $coachIdArr[$i] = $coaches->Fields('pn_uid');
				}

				if ($advanced == 1) {
				  $qry = "select raceid, name from naf_race order by name";
				  $races = $dbconn->Execute($qry);
				  $raceSel = "<option value=\"0\">[Keep Default]</option>";
				  for ( ; !$races->EOF; $races->MoveNext() ) {
				    $raceSel .= "<option value=\"".$races->Fields('raceid')."\">".$races->Fields('name')."</option>";
				  }
				}

				$req = "<span style=\"vertical-align: top; font-size: 0.9em; color: red;\">*</span>";

				echo "<form method=\"post\" action=\"naf.php\">";

				if ($advanced == 1) {
				  echo "<table border=\"1\">"
				  ."<tr><th colspan=\"5\">Home</th><th>Score$req</th><th colspan=\"5\">Away</th><th>Gate</th></tr>"
				  ."<tr align=\"center\"><td>Coach$req</td><td>Race$req</td><td>Team<br />Rating$req</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</th>"
				  ."<td>Coach$req</td><td>Race$req</td><td>Team<br />Rating$req</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</td></tr>";
				}
				else {
				  echo "<table border=\"1\">"
				  ."<tr><th colspan=\"2\">Home</th><th>Score$req</th><th colspan=\"2\">Away</th></tr>"
				  ."<tr align=\"center\"><td>Coach$req</td><td>Team<br />Rating$req</td><th>&nbsp;</th>"
				  ."<td>Coach$req</td><td>Team<br />Rating$req</td></tr>";
				}


		  echo "<tr>"
		  ."<td><select name=\"c1\">".createCoachSelection($game->Fields('homecoachid'))."</select></td>"
		  .($advanced==1?"<td><select name=\"r1\">$raceSel</select></td>":"")
		  ."<td><input size=\"2\" type=\"text\" name=\"tr1\" value=\"".$game->fields("trhome")."\"></td>"
		  .($advanced==1?"<td><input size=\"1\" type=\"text\" name=\"bh1\" value=\"".$game->fields("badlyhurthome")."\"><input size=\"1\" type=\"text\" name=\"si1\" value=\"".$game->fields("serioushome")."\"><input size=\"1\" type=\"text\" name=\"rip1\" value=\"".$game->fields("killshome")."\"></td>":"")
		  .($advanced==1?"<td><input size=\"5\" type=\"text\" name=\"w1\" value=\"".$game->fields("winningshome")."\"></td>":"")
		  ."<td><table border=\"0\"><tr><td><input size=\"1\" type=\"text\" name=\"s1\" value=\"".$game->fields("goalshome")."\"></td><td>-</td><td><input size=\"1\" type=\"text\" name=\"s2\" value=\"".$game->fields("goalsaway")."\"></td></tr></table></td>"
		  ."<td><select name=\"c2\">".createCoachSelection($game->Fields('awaycoachid'))."</select></td>"
		  .($advanced==1?"<td><select name=\"r2\">$raceSel</select></td>":"")
		  ."<td><input size=\"2\" type=\"text\" name=\"tr2\" value=\"".$game->fields("traway")."\"></td>"
		  .($advanced==1?"<td><input size=\"1\" type=\"text\" name=\"bh2\" value=\"".$game->fields("badlyhurtaway")."\"><input size=\"1\" type=\"text\" name=\"si2\" value=\"".$game->fields("seriousaway")."\"><input size=\"1\" type=\"text\" name=\"rip2\" value=\"".$game->fields("killsaway")."\"></td>":"")
		  .($advanced==1?"<td><input size=\"5\" type=\"text\" name=\"w2\" value=\"".$game->fields("winningsaway")."\"></td>":"")
		  .($advanced==1?"<td><input size=\"5\" type=\"text\" name=\"gate\" value=\"".$game->fields("gate")."\"></td></tr>":"");

		  echo "</table>";
		  echo "Columns marked with $req are required.<br />";

		  echo "<input type=\"hidden\" name=\"page\" value=\"view\">"
		  ."<input type=\"hidden\" name=\"op\" value=\"save\">"
		  ."<input type=\"hidden\" name=\"game_id\" value=\"$game_id\">"
		  ."<input type=\"hidden\" name=\"id\" value=\"$tournament_id\">"
		  ."<input type=\"hidden\" name=\"submit\" value=\"1\">";

		  echo "<br /><input type=\"submit\" value=\"Save Changes\"><br />";

		  echo "</form>";




		  break;
}

$id = pnVarCleanFromInput('id');

$query = "SELECT * FROM naf_tournament WHERE tournamentid=$id";
$tourney = $dbconn->Execute($query);

echo "<div align=\"center\"><h2>Matches played during ".$tourney->Fields('tournamentname')."</h2></div>\n";

// Get list of games in temp table for this tournament
$query = "SELECT * "
."FROM naf_game "
."WHERE tournamentid=$id "
."ORDER BY date, hour, gameid ";
$games = $dbconn->Execute($query);
if (!$games->EOF){
  if ($advanced != 1) {
    echo "<a href=\"naf.php?page=view&id=$tournament_id&advanced=1\">Switch to Advanced mode</a>";
  }
  else {
    echo "<a href=\"naf.php?page=view&id=$tournament_id\">Switch to Simple mode</a>";
  }
  // Output table header
  if ($advanced == 1) {
    echo "<table border=\"1\">"
    ."<tr><th>&nbsp;</th><th colspan=\"5\">Home</th><th>Score</th><th colspan=\"5\">Away</th><th>Gate</th>".($secAdmin?"<th>id</th><th>Op</th>":"")."</tr>"
    ."<tr align=\"center\"><td>&nbsp;</td><td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</th>"
    ."<td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</td>".($secAdmin?"<td colspan=\"2\">&nbsp</td>":"")."</tr>";
  }else {
    echo "<table border=\"1\">"
    ."<tr><th>&nbsp;</th><th colspan=\"2\">Home</th><th>Score</th><th colspan=\"2\">Away</th>".($secAdmin?"<th>id</th><th>Op</th>":"")."</tr>"
    ."<tr align=\"center\"><td>&nbsp;</td><td>Coach</td><td>Team<br />Rating</td><th>&nbsp;</th>"
    ."<td>Coach</td><td>Team<br />Rating</td>".($secAdmin?"<td colspan=\"2\">&nbsp</td>":"")."</tr>";
  }

  // Output games currently in temp table
  for ( ; !$games->EOF; $games->MoveNext() ) {
    // Get coach 1 name
    $query = "SELECT pn_uname, coachid "
    ."FROM naf_coach, nuke_users "
    ."WHERE coachid=pn_uid "
    ."AND pn_uid = ".$games->fields("homecoachid");
    $result = $dbconn->Execute($query);
    $coach_1 = $result->fields[0];

    // Get coach 2 name
    $query = "SELECT pn_uname, coachid "
    ."FROM naf_coach, nuke_users "
    ."WHERE coachid=pn_uid "
    ."AND pn_uid = ".$games->fields("awaycoachid");
    $result = $dbconn->Execute($query);
    $coach_2 = $result->fields[0];

    // Get race 1 name
    $query = "SELECT name "
    ."FROM naf_race "
    ."WHERE raceid = ".$games->fields("racehome");
    $result = $dbconn->Execute($query);
    $race_1 = $result->fields[0];

    // Get race 2 name
    $query = "SELECT name "
    ."FROM naf_race "
    ."WHERE raceid = ".$games->fields("raceaway");
    $result = $dbconn->Execute($query);
    $race_2 = $result->fields[0];

    //Output table row

    $currentDate = $games->fields('date');
    $currentHour = $games->fields('hour');
    $span = 6+7*$advanced+($secAdmin?2:0);
    if ($lastDate != $currentDate) {
						echo "<tr><th align=\"center\" colspan=\"$span\">$currentDate</th></tr>";
						$lastDate = $currentDate;
    }
    if ($lastHour != $currentHour) {
						echo "<tr><td align=\"center\" colspan=\"$span\"><b>$currentHour:00</b></td></tr>";
						$lastHour = $currentHour;
    }

    echo "<tr>"
    ."<td>".(++$rowCount)."</td>"
    ."<td>$coach_1</td>"
    .($advanced==1?"<td>$race_1</td>":"")
    ."<td>".$games->fields("trhome")."</td>"
    .($advanced==1?"<td>".$games->fields("badlyhurthome")."|".$games->fields("serioushome")."|".$games->fields("killshome")."</td>":"")
    .($advanced==1?"<td>".$games->fields("winningshome")."</td>":"")
    ."<td><table border=\"0\"><tr><td>".$games->fields("goalshome")."</td><td>-</td><td>".$games->fields("goalsaway")."</td></tr></table></td>"
    ."<td>$coach_2</td>"
    .($advanced==1?"<td>$race_2</td>":"")
    ."<td>".$games->fields("traway")."</td>"
    .($advanced==1?"<td>".$games->fields("badlyhurtaway")."|".$games->fields("seriousaway")."|".$games->fields("killsaway")."</td>":"")
    .($advanced==1?"<td>".$games->fields("winningsaway")."</td>":"")
    .($advanced==1?"<td>".$games->fields("gate")."</td>":"");
    if ($secAdmin) {
      echo "<td>".$games->Fields('gameid')."</td>";
						//Output links to insert or delete a game
						echo "<td>(<a href=\"naf.php?page=view&op=delete&id=$tournament_id&advanced=$advanced&game_id=".$games->fields("gameid")."\">Delete</a>)"
						."(<a href=\"naf.php?page=view&op=edit&id=$tournament_id&advanced=$advanced&game_id=".$games->fields("gameid")."\">Edit</a>)</td>";
    }
    echo "</tr>";
  }
  echo "</table> <br />";
}
else{
  echo "There are no games to view for this tournament.";
}

echo "<a href=\"naf.php?page=tournaments&op=view&id=$id\">Back to Tournament Page</a>";

CloseTable();
include 'footer.php';

?>
