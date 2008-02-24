<?
require_once 'modules/NAF/pnupdaterapi.php';

function NAF_view_delete($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    include 'header.php';
    OpenTable();
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    CloseTable();
    include 'footer.php';
    return true;
  }
  $game_id = pnVarCleanFromInput('game_id');
  deleteGame($game_id);
  pnRedirect(pnModURL('NAF', 'view', '', array('id' => pnVarCleanFromInput('id'), 'advanced' => pnVarCleanFromInput('advanced'))));
  return true;
}

function NAF_view_save($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    include 'header.php';
    OpenTable();
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    CloseTable();
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  $initial_reputation = 15000;
  $tournament_id = pnVarCleanFromInput('id');
  list($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1,
  $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $s2, $gate, $game_id) =
  pnVarCleanFromInput('c1', 'r1', 'tr1', 'bh1', 'si1', 'rip1', 'w1', 's1',
  'c2', 'r2', 'tr2', 'bh2', 'si2', 'rip2', 'w2', 's2', 'gate', 'game_id');
  //Update changed match

  //Get race for coach 1
  if (!$r1){
    $query = "SELECT race "
    ."FROM naf_tournamentcoach "
    ."WHERE naftournament=".pnVarPrepForStore($tournament_id)
    ." AND nafcoach=".pnVarPrepForStore($c1);
    $race = $dbconn->Execute($query);
    $r1 = $race->fields[0];
  }
  //Get race for coach 2
  if (!$r2){
    $query = "SELECT race "
    ."FROM naf_tournamentcoach "
    ."WHERE naftournament=".pnVarPrepForStore($tournament_id)
    ." AND nafcoach=".pnVarPrepForStore($c2);
    $race = $dbconn->Execute($query);
    $r2 = $race->fields[0];
  }

  //Check for reputation for coach/race #1
  $query = "SELECT ranking "
  ."FROM naf_coachranking "
  ."WHERE coachid=".pnVarPrepForStore($c1)
  ." AND raceid=".pnVarPrepForStore($r1);
  $repcheck = $dbconn->Execute($query);
  if ($repcheck->EOF) {
    //No reputation for this coach/race. Insert new record
    $sqlupdate = "INSERT INTO naf_coachranking "
    ."(coachid,raceid,ranking) "
    ."VALUES ("
    .pnVarPrepForStore($c1).","
    .pnVarPrepForStore($r1).","
    .pnVarPrepForStore($initial_reputation)
    .")";
    $update = $dbconn->Execute($sqlupdate);
    $rep1=$initial_reputation;
  }
  else{
    $rep1 = $repcheck->fields[0];
  }

  //Check for reputation for coach/race #2
  $query = "SELECT ranking "
  ."FROM naf_coachranking "
  ."WHERE coachid=".pnVarPrepForStore($c2)
  ." AND raceid=".pnVarPrepForStore($r2);
  $repcheck = $dbconn->Execute($query);
  if ($repcheck->EOF) {
    //No reputation for this coach/race. Insert new record
    $sqlupdate = "INSERT INTO naf_coachranking "
    ."(coachid,raceid,ranking) "
    ."VALUES ("
    .pnVarPrepForStore($c2).","
    .pnVarPrepForStore($r2).","
    .pnVarPrepForStore($initial_reputation)
    .")";
    $update = $dbconn->Execute($sqlupdate);
    $rep2=$initial_reputation;
  }
  else{
    $rep2 = $repcheck->fields[0];
  }

  $query = "UPDATE naf_game SET "

  ."homecoachid = ".pnVarPrepForStore($c1)." , "
  ."awaycoachid = ".pnVarPrepForStore($c2)." , "
  ."racehome = ".pnVarPrepForStore($r1)." , "
  ."raceaway = ".pnVarPrepForStore($r2)." , "
  ."trhome = ".pnVarPrepForStore($tr1)." , "
  ."traway = ".pnVarPrepForStore($tr2)." , "
  ."rephome = ".pnVarPrepForStore($rep1)." , "
  ."repaway = ".pnVarPrepForStore($rep2)." , "
  ."goalshome = ".pnVarPrepForStore($s1)." , "
  ."goalsaway = ".pnVarPrepForStore($s2)." , "
  ."badlyhurthome = ".pnVarPrepForStore($bh1 + 0).", "
  ."badlyhurtaway = ".pnVarPrepForStore($bh2 + 0)." , "
  ."serioushome = ".pnVarPrepForStore($si1 + 0)." , "
  ."seriousaway = ".pnVarPrepForStore($si2 + 0)." , "
  ."killshome = ".pnVarPrepForStore($rip1 + 0)." , "
  ."killsaway = ".pnVarPrepForStore($rip2 + 0)." , "
  ."gate = ".pnVarPrepForStore($gate + 0)." , "
  ."winningshome = ".pnVarPrepForStore($w1 + 0)." , "
  ."winningsaway = ".pnVarPrepForStore($w2 + 0)." , "
  ."dirty = 'TRUE' "
  ."WHERE gameid=".pnVarPrepForStore($game_id);
  //echo $query;
  $dbconn->Execute($query);
  pnRedirect(pnModURL('NAF', 'view', '', array('id' => $tournament_id, 'advanced' => pnVarCleanFromInput('advanced'))));
  return true;
}

function NAF_view_edit($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    include 'header.php';
    OpenTable();
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    CloseTable();
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  $game_id = pnVarCleanFromInput('game_id');
  $advanced = pnVarCleanFromInput('advanced');
  $tournament_id = pnVarCleanFromInput('id');
  $query = "SELECT * "
  ."FROM naf_game "
  ."WHERE gameid=".pnVarPrepForStore($game_id);
  $game = $dbconn->Execute($query);

  $qry = "select pn_uid, pn_uname from nuke_users u, naf_tournamentcoach tc where tc.nafcoach=u.pn_uid and "
  ."tc.naftournament=".pnVarPrepForStore($tournament_id)." order by pn_uname";
  $coaches = $dbconn->Execute($qry);
  for ($i=0; !$coaches->EOF; $i++, $coaches->MoveNext() ) {
    $coachNameArr[$i] = $coaches->Fields('pn_uname');
    $coachIdArr[$i] = $coaches->Fields('pn_uid');
  }

  if ($advanced == 1) {
    $qry = "select raceid, name from naf_race order by name";
    $races = $dbconn->Execute($qry);
    $raceSel = '<option value="0">[Keep Default]</option>';
    for ( ; !$races->EOF; $races->MoveNext() ) {
      $raceSel .= '<option value="'.$races->Fields('raceid').'">'.$races->Fields('name').'</option>';
    }
  }

  $req = '<span style="vertical-align: top; font-size: 0.9em; color: red;">*</span>';

  include 'header.php';
  OpenTable();
  echo '<form method="post" action="'.pnVarPrepForDisplay(pnModURL('NAF', 'view', 'save', array('id' => $tournament_id, 'game_id' => $game_id))).'">';

  if ($advanced == 1) {
    echo '<table border="1">'
    .'<tr><th colspan="5">Home</th><th>Score'.$req.'</th><th colspan="5">Away</th><th>Gate</th></tr>'
    .'<tr align="center"><td>Coach'.$req.'</td><td>Race'.$req.'</td><td>Team<br />Rating'.$req.'</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><td>&nbsp;</td>'
    .'<td>Coach'.$req.'</td><td>Race'.$req.'</td><td>Team<br />Rating'.$req.'</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><td>&nbsp;</td></tr>';
  }
  else {
    echo '<table border="1">'
    .'<tr><th colspan="2">Home</th><th>Score'.$req.'</th><th colspan="2">Away</th></tr>'
    .'<tr align="center"><td>Coach'.$req.'</td><td>Team<br />Rating'.$req.'</td><td>&nbsp;</td>'
    .'<td>Coach'.$req.'</td><td>Team<br />Rating'.$req.'</td></tr>';
  }

  echo '<tr>'
  .'<td><select name="c1">';
  echo '<option value="">[ Select Coach ]</option>';
  for ($i=0; $i<count($coachNameArr); $i++) {
    echo '<option value="'.pnVarPrepForDisplay($coachIdArr[$i]).'"'.($game->Fields('homecoachid')==$coachIdArr[$i]?' selected="selected"':'').'>'.pnVarPrepForDisplay($coachNameArr[$i]).'</option>';
  }
  echo ($advanced==1?'<td><select name="r1">'.$raceSel.'</select></td>':'')
  .'<td><input size="2" type="text" name="tr1" value="'.$game->fields('trhome').'" /></td>'
  .($advanced==1?'<td><input size="1" type="text" name="bh1" value="'.$game->fields('badlyhurthome').'" /><input size="1" type="text" name="si1" value="'.$game->fields('serioushome').'" /><input size="1" type="text" name="rip1" value="'.$game->fields('killshome').'" /></td>':'')
  .($advanced==1?'<td><input size="5" type="text" name="w1" value="'.$game->fields('winningshome').'" /></td>':'')
  .'<td><table border="0"><tr><td><input size="1" type="text" name="s1" value="'.$game->fields('goalshome').'" /></td><td>-</td><td><input size="1" type="text" name="s2" value="'.$game->fields('goalsaway').'" /></td></tr></table></td>'
  .'<td><select name="c2">';
  echo '<option value="">[ Select Coach ]</option>';
  for ($i=0; $i<count($coachNameArr); $i++) {
    echo '<option value="'.pnVarPrepForDisplay($coachIdArr[$i]).'"'.($game->Fields('awaycoachid')==$coachIdArr[$i]?' selected="selected"':'').'>'.pnVarPrepForDisplay($coachNameArr[$i]).'</option>';
  }
  echo ($advanced==1?'<td><select name="r2">'.$raceSel.'</select></td>':'')
  .'<td><input size="2" type="text" name="tr2" value="'.$game->fields('traway').'" /></td>'
  .($advanced==1?'<td><input size="1" type="text" name="bh2" value="'.$game->fields('badlyhurtaway').'" /><input size="1" type="text" name="si2" value="'.$game->fields('seriousaway').'" /><input size="1" type="text" name="rip2" value="'.$game->fields('killsaway').'" /></td>':'')
  .($advanced==1?'<td><input size="5" type="text" name="w2" value="'.$game->fields('winningsaway').'" /></td>':'')
  .($advanced==1?'<td><input size="5" type="text" name="gate" value="'.$game->fields('gate').'" /></td>':'').'</tr>'."\n";

  echo '</table>';
  echo 'Columns marked with '.$req.' are required.<br />';

  //echo '<input type="hidden" name="op" value="save" />'
  //.'<input type="hidden" name="game_id" value="'.$game_id.'" />'
  //.'<input type="hidden" name="id" value="'.$tournament_id.'" />'
  //.'<input type="hidden" name="submit" value="1" />';

  echo '<br /><input type="submit" value="Save Changes" /><br />';

  echo '</form>';

  CloseTable();
  include 'footer.php';
  return true;
}

function NAF_view_main($args) {
  if (pnSecAuthAction(0, 'NAF::', 'TournamentAdmin::', ACCESS_ADMIN)) {
    $secAdmin=true;
  }
  else {
    $secAdmin=false;
  }
  $dbconn =& pnDBGetConn(true);

  $advanced = pnVarCleanFromInput('advanced');
  $tournament_id = pnVarCleanFromInput('id');

  if ($tournament_id+0 == 0) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
    return true;
  }
  $query = "SELECT tournamentname FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($tournament_id);
  $tourney = $dbconn->Execute($query);
  include 'header.php';
  OpenTable();

  echo '<div align="center"><h2>Matches played during '.$tourney->Fields('tournamentname').'</h2></div>'."\n";

  // Get list of games in temp table for this tournament
  $query = "SELECT g.*, r1.name as racehomename, r2.name as raceawayname"
  ." FROM naf_game g, naf_race r1, naf_race r2"
  ." WHERE tournamentid=".pnVarPrepForStore($tournament_id)
  ." AND g.racehome = r1.raceid"
  ." AND g.raceaway = r2.raceid"
  ." ORDER BY date, hour, gameid ";
  $games = $dbconn->Execute($query);
  if (!$games->EOF){
    if ($advanced != 1) {
      echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'view', '', array('id' => $tournament_id, 'advanced' => '1'))).'">Switch to Advanced mode</a>';
    }
    else {
      echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'view', '', array('id' => $tournament_id))).'">Switch to Simple mode</a>';
    }
    // Output table header
    if ($advanced == 1) {
      echo '<table border="1">'
      .'<tr><th>&nbsp;</th><th colspan="5">Home</th><th>Score</th><th colspan="5">Away</th><th>Gate</th>'.($secAdmin?'<th>id</th><th>Op</th>':'').'</tr>'
      .'<tr align="center"><td>&nbsp;</td><td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><td>&nbsp;</td>'
      .'<td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><td>&nbsp;</td>'.($secAdmin?'<td colspan="2">&nbsp;</td>':'').'</tr>';
    }
    else {
      echo '<table border="1">'
      .'<tr><th>&nbsp;</th><th colspan="2">Home</th><th>Score</th><th colspan="2">Away</th>'.($secAdmin?'<th>id</th><th>Op</th>':'').'</tr>'
      .'<tr align="center"><td>&nbsp;</td><td>Coach</td><td>Team<br />Rating</td><td>&nbsp;</td>'
      .'<td>Coach</td><td>Team<br />Rating</td>'.($secAdmin?'<td colspan="2">&nbsp;</td>':'').'</tr>';
    }

    // Output games currently in temp table
    for ( ; !$games->EOF; $games->MoveNext() ) {
      // Get coach 1 name
      /*$query = "SELECT pn_uname, coachid "
      ."FROM naf_coach, nuke_users "
      ."WHERE coachid=pn_uid "
      ."AND pn_uid = ".pnVarPrepForStore($games->fields('homecoachid'));
      $result = $dbconn->Execute($query);
      $coach_1 = $result->fields[0];

      // Get coach 2 name
      $query = "SELECT pn_uname, coachid "
      ."FROM naf_coach, nuke_users "
      ."WHERE coachid=pn_uid "
      ."AND pn_uid = ".pnVarPrepForStore($games->fields('awaycoachid'));
      $result = $dbconn->Execute($query);
      $coach_2 = $result->fields[0];

      // Get race 1 name
      $query = "SELECT name "
      ."FROM naf_race "
      ."WHERE raceid = ".pnVarPrepForStore($games->fields('racehome'));
      $result = $dbconn->Execute($query);
      $race_1 = $result->fields[0];

      // Get race 2 name
      $query = "SELECT name "
      ."FROM naf_race "
      ."WHERE raceid = ".pnVarPrepForStore($games->fields('raceaway'));
      $result = $dbconn->Execute($query);
      $race_2 = $result->fields[0];*/

      //Output table row

      $currentDate = $games->fields('date');
      $currentHour = $games->fields('hour');
      $span = 6+7*$advanced+($secAdmin?2:0);
      if ($lastDate != $currentDate) {
        echo '<tr><th align="center" colspan="$span">'.$currentDate.'</th></tr>';
        $lastDate = $currentDate;
      }
      if ($lastHour != $currentHour) {
        echo '<tr><td align="center" colspan="$span"><b>'.$currentHour.':00</b></td></tr>';
        $lastHour = $currentHour;
      }

      echo '<tr>'
      .'<td>'.(++$rowCount).'</td>'
      //.'<td>'.$coach_1.'</td>'
      .'<td>'.pnUserGetVar('uname', $games->fields('homecoachid')).'</td>'
      //.($advanced==1?'<td>'.$race_1.'</td>':'')
      .($advanced==1?'<td>'.$games->fields('racehomename').'</td>':'')
      .'<td>'.$games->fields('trhome').'</td>'
      .($advanced==1?'<td>'.$games->fields('badlyhurthome').'|'.$games->fields('serioushome').'|'.$games->fields('killshome').'</td>':'')
      .($advanced==1?'<td>'.$games->fields('winningshome').'</td>':'')
      .'<td><table border="0"><tr><td>'.$games->fields('goalshome').'</td><td>-</td><td>'.$games->fields('goalsaway').'</td></tr></table></td>'
      //.'<td>'.$coach_2.'</td>'
      .'<td>'.pnUserGetVar('uname', $games->fields('awaycoachid')).'</td>'
      //.($advanced==1?'<td>'.$race_2.'</td>':'')
      .($advanced==1?'<td>'.$games->fields('raceawayname').'</td>':'')
      .'<td>'.$games->fields('traway').'</td>'
      .($advanced==1?'<td>'.$games->fields('badlyhurtaway').'|'.$games->fields('seriousaway').'|'.$games->fields('killsaway').'</td>':'')
      .($advanced==1?'<td>'.$games->fields('winningsaway').'</td>':'')
      .($advanced==1?'<td>'.$games->fields('gate').'</td>':'');
      if ($secAdmin) {
        echo '<td>'.$games->Fields('gameid').'</td>';
        //Output links to insert or delete a game
        if($advanced != 1)$advanced = 0;
        echo '<td>(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'view', 'delete', array('id' => $tournament_id, 'advanced' => $advanced, 'game_id' => $games->fields('gameid')))).'">Delete</a>)'
        .'(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'view', 'edit', array('id' => $tournament_id, 'advanced' => $advanced, 'game_id' => $games->fields('gameid')))).'">Edit</a>)</td>';
      }
      echo '</tr>';
    }
    echo '</table> <br />';
  }
  else{
    echo 'There are no games to view for this tournament.';
  }

  echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'view', array('id' => $tournament_id))).'">Back to Tournament Page</a>';

  CloseTable();
  include 'footer.php';
  return true;
}

?>
