<?
include 'modules/NAF/pnupdaterapi.php';
function NAF_tournamentinfo_main($args) {
  $dbconn =& pnDBGetConn(true);
  include 'header.php';
  OpenTable();

  $uid = pnVarCleanFromInput('uid') + 0;
  $uname = pnVarCleanFromInput('uname');
  $tournament = pnVarCleanFromInput('id')+0;

  if ($uid > 0) {
    //$query = "SELECT pn_uname FROM nuke_users where pn_uid=".pnVarPrepForStore($uid);
    //$dbconn->getOne($query);
    $username = pnUserGetVar('uname', $uid);
  }
  else {
    //$query = "SELECT pn_uid FROM nuke_users where pn_uname='".pnVarPrepForStore($uname)."'";
    //$uid = $dbconn->getOne($query);
    $uid = pnUserGetIDFromName($uname);
    $username=$uname;
  }

  if (strlen($username) == 0 || $uid==0)
    echo 'No such user';
  else {
    echo '<h3>Tournament info for '.pnVarPrepForDisplay($username).'</h3>';

    echo '<table border="0"><tr valign="top"><td>';

    $query = "SELECT tc.naftournament as tournamentid, t.tournamentstartdate, t.tournamentname as tournament, r.name as race FROM naf_tournamentcoach tc "
            ."LEFT JOIN naf_tournament t on tc.naftournament=t.tournamentid "
            ."LEFT JOIN naf_race r on tc.race=r.raceid "
            ."WHERE tc.nafcoach=".pnVarPrepForStore($uid)." "
            ."ORDER BY tournamentstartdate";
    $tournaments = $dbconn->Execute($query);

    $results = array();
    $raceresults = array();
    for ( ; !$tournaments->EOF; $tournaments->moveNext() ) {
      $tid = $tournaments->Fields('tournamentid');
      if ($tid == $tournament) {
        $tName = $tournaments->Fields('tournament');
        $tRace = $tournaments->Fields('race');
        $tLink = '<b>'.$tournaments->Fields('tournament').'</b>';
      }
      else
        $tLink = '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournamentinfo', '', array('uid' => $uid, 'id' => $tid))).'">'.pnVarPrepForDisplay($tournaments->Fields('tournament')).'</a>';

      $results[$tid] = array();
      $results[$tid][0] = '<td bgcolor="#f8f7ee">'.$tournaments->Fields('tournamentstartdate').'</td>'
                         .'<td bgcolor="#f8f7ee">'.$tLink.'</td>'
                         .'<td bgcolor="#f8f7ee">'.$tournaments->Fields('race').'</td>';
    }
    if ($tournaments->numRows() > 0) {
      $tdScored = $tdAllowed = 0;

      $query = "SELECT tournamentid, homecoachid, racehome, awaycoachid, goalshome, goalsaway FROM naf_game WHERE homecoachid=".pnVarPrepForStore($uid);
      $games = $dbconn->Execute($query);

      for ( ; !$games->EOF; $games->moveNext() ) {
        $tid = $games->Fields('tournamentid');
        $home = $games->Fields('goalshome');
        $away = $games->Fields('goalsaway');
        $race = $games->Fields('racehome');
        $tdScored += $home;
        $tdAllowed += $away;

        if (!is_array($raceresults[$race]))
          $raceresults[$race] = array();

        if ($home > $away) {
          $results[$tid][1]++;
          $raceresults[$race][1]++;
        }
        else if ($home == $away) {
          $results[$tid][2]++;
          $raceresults[$race][2]++;
        }
        else {
          $results[$tid][3]++;
          $raceresults[$race][3]++;
        }
        $results[$tid][4] += $home+0;
        $results[$tid][5] += $away+0;
        $raceresults[$race][4] += $home+0;
        $raceresults[$race][5] += $away+0;
      }

      $query = "SELECT tournamentid, awaycoachid, homecoachid, raceaway, goalsaway, goalshome FROM naf_game WHERE awaycoachid=".pnVarPrepForStore($uid);
      $games = $dbconn->Execute($query);

      for ( ; !$games->EOF; $games->moveNext() ) {
        $tid = $games->Fields('tournamentid');
        $home = $games->Fields('goalsaway');
        $away = $games->Fields('goalshome');
        $race = $games->Fields('raceaway');
        $tdScored += $home;
        $tdAllowed += $away;

        if (!is_array($raceresults[$race]))
          $raceresults[$race] = array();

        if ($home > $away) {
          $results[$tid][1]++;
          $raceresults[$race][1]++;
        }
        else if ($home == $away) {
          $results[$tid][2]++;
          $raceresults[$race][2]++;
        }
        else {
          $results[$tid][3]++;
          $raceresults[$race][3]++;
        }
        $results[$tid][4] += $home+0;
        $results[$tid][5] += $away+0;
        $raceresults[$race][4] += $home+0;
        $raceresults[$race][5] += $away+0;
      }

      echo '<table bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
      echo '<tr><th bgcolor="#D9D8D0" colspan="6">Tournaments</th></tr>';
      echo '<tr><th bgcolor="#D9D8D0">Date</th><th bgcolor="#D9D8D0">Tournament</th>'
          .'<th bgcolor="#D9D8D0">Race</th><th bgcolor="#D9D8D0">Record<br /><span style="font-size: 80%">(W/T/L)</span></th>'
          .'<th bgcolor="#D9D8D0" colspan="2">TDs<br /><span style="font-size: 80%">Diff (for-agnst)</span></th></tr>';

      $wins = $ties = $losses;
      foreach ($results as $tid=>$arr) {
        echo '<tr>'
            .$arr[0]
            .'<td bgcolor="#f8f7ee" align="center">'.($arr[1]+0).'/'.($arr[2]+0).'/'.($arr[3]+0).'</td>'
            .'<td bgcolor="#f8f7ee" align="right">'.($arr[4]-$arr[5]).'</td>'
            .'<td bgcolor="#f8f7ee" align="left">('.($arr[4]+0).'-'.($arr[5]+0).')</td>'
            .'</tr>';
        $wins += $arr[1];
        $ties += $arr[2];
        $losses += $arr[3];
      }
      echo '</table>';
    }

    echo '</td><td>';

    $query = "SELECT name, ranking, naf_race.raceid FROM naf_coachranking LEFT JOIN naf_race USING (raceid) "
            ."WHERE coachid=".pnVarPrepForStore($uid)." order by name";
    $rankings = $dbconn->Execute($query);

    if ($rankings->numRows() > 0) {
      echo '<table bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
      echo '<tr><th bgcolor="#D9D8D0" colspan="5">Rankings</th></tr>';
      echo '<tr><th bgcolor="#D9D8D0">Race</th><th bgcolor="#D9D8D0">Ranking</th>'
          .'<th bgcolor="#D9D8D0">Record<br /><span style="font-size: 80%">(W/T/L)</span></th>'
          .'<th bgcolor="#D9D8D0" colspan="2">TDs<br /><span style="font-size: 80%">Diff (for-agnst)</span></th>';
      echo '</tr>';
      for ( ; !$rankings->EOF; $rankings->moveNext() ) {
        $race = $rankings->Fields('raceid');
        echo '<tr>'
            .'<td bgcolor="#f8f7ee">'.$rankings->Fields('name').'</td>'
            .'<td bgcolor="#f8f7ee">'.($rankings->Fields('ranking')/100).'</td>'
            .'<td bgcolor="#f8f7ee" align="center">'.($raceresults[$race][1]+0).'/'.($raceresults[$race][2]+0).'/'.($raceresults[$race][3]+0).'</td>'
            .'<td bgcolor="#f8f7ee" align="right">'.($raceresults[$race][4]-$raceresults[$race][5]).'</td>'
            .'<td bgcolor="#f8f7ee">('.($raceresults[$race][4]+0).'-'.($raceresults[$race][5]+0).')</td>'
            .'</tr>';
      }
      echo '<tr><td bgcolor="#f8f7ee" colspan="2" align="right">Total: </td>'
          .'<td align="center" bgcolor="#f8f7ee">'.$wins.'/'.$ties.'/'.$losses.'</td>'
          .'<td align="right" bgcolor="#f8f7ee">'.($tdScored-$tdAllowed).'</td>'
          .'<td align="left" bgcolor="#f8f7ee">('.($tdScored+0).'-'.($tdAllowed+0).')</td></tr>';
      echo '</table>';
    }
   echo '</td></tr></table>';
  }

  if ($tournament > 0) {

    $query = "SELECT naf_game.*, "
            ."r1.name as homeracename, r2.name as awayracename "
            ."FROM naf_game "
            ."LEFT JOIN naf_race r1 ON (racehome = r1.raceid) "
            ."LEFT JOIN naf_race r2 ON (raceaway = r2.raceid) "
            ."WHERE tournamentid=".pnVarPrepForStore($tournament)." order by naf_game.date, naf_game.hour, naf_game.gameid";
    $games = $dbconn->execute($query);

    $startCR = 0;
    if ($games->numRows() > 0) {
      $k = getTournamentK($tournament);

      echo '<table bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
      echo '<tr><th bgcolor="#D9D8D0" colspan="6">'.pnVarPrepForDisplay($tName).'</th>'
          .'<th bgcolor="#D9D8D0" colspan="2">'.$tRace.'</th></tr>';
      echo '<tr><th bgcolor="#D9D8D0" colspan="8">&nbsp;</th></tr>';
      echo '<tr><th bgcolor="#D9D8D0">Game</th>'
          .'<th bgcolor="#D9D8D0">CR</th>'
          .'<th bgcolor="#D9D8D0">TD+</th>'
          .'<th bgcolor="#D9D8D0">TD-</th>'
          .'<th bgcolor="#D9D8D0">Opponent</th>'
          .'<th bgcolor="#D9D8D0">Opponent\'s Race</th>'
          .'<th bgcolor="#D9D8D0">CR</th>'
          .'<th bgcolor="#D9D8D0">CR Adjust</th></tr>';

      $gameNum = 0;
      $tdFor = 0;
      $tdAgainst = 0;
      for ( ; !$games->EOF; $games->moveNext() ) {
        if ($games->Fields('homecoachid') != $uid && $games->Fields('awaycoachid') != $uid)
          continue;

        if ($startCR == 0) {
          if ($games->Fields('homecoachid') == $uid)
            $startCR = $games->Fields('rephome') / 100;
          else
            $startCR = $games->Fields('repaway') / 100;
        }

        $gameNum++;
        if ($games->Fields('homecoachid') == $uid) {
          $myCR = $games->Fields('rephome') / 100;
          $oppCR = $games->Fields('repaway') / 100;
          $myTR = $games->Fields('trhome');
          $oppTR = $games->Fields('traway');
          $myTD = $games->Fields('goalshome');
          $oppTD = $games->Fields('goalsaway');
          $opponent = pnUserGetVar('uname', $games->Fields('awaycoachid'));
          $oppRace = $games->Fields('awayracename');
        }
        else {
          $oppCR = $games->Fields('rephome') / 100;
          $myCR = $games->Fields('repaway') / 100;
          $oppTR = $games->Fields('trhome');
          $myTR = $games->Fields('traway');
          $oppTD = $games->Fields('goalshome');
          $myTD = $games->Fields('goalsaway');
          $opponent = pnUserGetVar('uname', $games->Fields('homecoachid'));
          $oppRace = $games->Fields('homeracename');
        }

        $newCR = calculateNewReputation( $myCR*100, $oppCR*100, $myTR, $oppTR, $myTD-$oppTD, $k) / 100;
        $crAdjust = round(100*($newCR - $myCR))/100;
        $tdFor += $myTD;
        $tdAgainst += $oppTD;
        $endCR = round(100*($myCR + $crAdjust))/100;

        echo '<tr>'
            .'<td bgcolor="#f8f7ee" align="center">'.$gameNum.'</td>'
            .'<td bgcolor="#f8f7ee">'.$myCR.'</td>'
            .'<td bgcolor="#f8f7ee" align="right">'.$myTD.'</td>'
            .'<td bgcolor="#f8f7ee">'.$oppTD.'</td>'
            .'<td bgcolor="#f8f7ee">'.$opponent.'</td>'
            .'<td bgcolor="#f8f7ee">'.$oppRace.'</td>'
            .'<td bgcolor="#f8f7ee">'.$oppCR.'</td>'
            .'<td bgcolor="#f8f7ee">'.$crAdjust.'</td>'
            .'</tr>';
      }
      echo '<tr>'
          .'<td bgcolor="#f8f7ee" align="center">End</td>'
          .'<td bgcolor="#f8f7ee">'.$endCR.'</td>'
          .'<td bgcolor="#f8f7ee" align="right">'.$tdFor.'</td>'
          .'<td bgcolor="#f8f7ee">'.$tdAgainst.'</td>'
          .'<td bgcolor="#f8f7ee" colspan="3" align="right">Net CR Adjust</td>'
          .'<td bgcolor="#f8f7ee">'.(round(100*($endCR - $startCR))/100).'</td>'
          .'</tr>';

      echo '</table>';
    }
  }

  CloseTable();
  include 'footer.php';
  return true;
}
?>
