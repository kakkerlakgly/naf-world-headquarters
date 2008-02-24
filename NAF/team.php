<?
pnRedirect(pnModURL('Stars'));

function goBack($err=false) {
  global $t;
  pnRedirect("naf.php?page=team&op=view&t=$t".($err?'&err='.urlencode($err):''));
  exit;
}

function poss($name) {
  if ($name{strlen($name)-1}=='s')
    return $name."'";
  else
    return $name."'s";
}

function fixStat($val, $m) {
  $m += 0; $val += 0;
  if ($m < -2) $m = -2;
  if ($m > 2) $m = 2;
  $val += $m;
  if ($val < 1) $val = 1;
  if ($m == 0)
    return $val;
  else if ($m < 0)
    return "-<b>$val</b>-";
  else
    return "+<b>$val</b>+";

}

function fixSPP($team) {
  global $dbconn;

  $query = "SELECT p.playerid, sum(completions) as cp, sum(interceptions) as i, sum(touchdowns) as td, sum(casualties) as cs, "
          ."sum(mvps) as vp FROM naf_player p left join naf_playergame pg using (playerid) where teamid=$team group by playerid";
  $s = $dbconn->execute($query);
  for ( ; !$s->EOF; $s->moveNext() ) {
    $query = "UPDATE naf_player SET "
            ."playercp=".(0+$s->Fields('cp')).", "
            ."playerint=".(0+$s->Fields('i')).", "
            ."playertd=".(0+$s->Fields('td')).", "
            ."playercas=".(0+$s->Fields('cs')).", "
            ."playermvp=".(0+$s->Fields('vp')).", "
            ."playerspp=".($s->Fields('cp')+$s->Fields('i')*2+$s->Fields('td')*3+$s->Fields('cs')*2+$s->Fields('vp')*5)." "
            ."WHERE playerid=".$s->Fields('playerid');
    $dbconn->execute($query);
echo $dbconn->errorMsg();
  }
}

function f($num) {
  if ($num>0)
    return $num;
  return "&nbsp;";
}

function f2($num) {
  return $num>0?$num:'';
}

  $isAdmin = pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);

  list($op, $team, $u) = pnVarCleanFromInput('op', 'team', 'u');

  // Must be logged in to edit rosters
  if ($op != 'print' && !pnUserLoggedIn()) {
    include 'header.php';
    OpenTable();
    echo '<div style="font-size: 2em;">You must be logged in to manage your teams.</div>';
    CloseTable();
    include 'footer.php';
    return;
  }

  $uid = pnUserGetVar('uid');

  $team += 0;
  $u = pnVarPrepForStore($u);

  switch ($op) {
    case 'fixspp':
      $t = pnVarCleanFromInput('t');
      $t += 0;
      fixSpp($t);
      goBack();
      break;
    case 'treasury':
      list($t, $treasury) = pnVarCleanFromInput('t', 'treasury');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      if (isset($treasury)) {
        $treasury += 0;
        $dbconn->execute("UPDATE naf_team SET teamtreasury=$treasury WHERE teamid=$t");
        goBack();
      }
      else {
        include 'header.php';
        OpenTable();
        echo '<div style="font-size: 200%;">Edit Treasury</div>';
        echo '<a href="naf.php?page=team&op=view&t='.$t.'">Back to team</a><br />';

        echo '<form action="naf.php" method="post">';
        echo '<input type="hidden" name="page" value="team" />'
            .'<input type="hidden" name="op" value="treasury" />'
            .'<input type="hidden" name="t" value="'.$t.'" />';

        echo '<input type="text" name="treasury" value="'.$team->Fields('teamtreasury').'" /><br />'
            .'<input type="submit" value="Update Treasury">'
            .'</form>';
        CloseTable();
        include 'footer.php';
      }
      break;
    case 'addreroll':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $numGames = $dbconn->getOne("SELECT count(1) FROM naf_teamgame WHERE teamid=$t");

      $cost = $team->Fields('reroll_cost');
      if ($numGames > 0)
        $cost *= 2;

      $dbconn->execute("UPDATE naf_team SET teamrerolls=teamrerolls+1, teamtreasury=teamtreasury-$cost WHERE teamid=$t AND teamtreasury>=$cost");
      goBack();
      break;
    case 'delreroll':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $numGames = $dbconn->getOne("SELECT count(1) FROM naf_teamgame WHERE teamid=$t");

      $cost = $team->Fields('reroll_cost');
      if ($numGames > 0)
        $cost = 0;

      $dbconn->execute("UPDATE naf_team SET teamrerolls=teamrerolls-1, teamtreasury=teamtreasury+$cost WHERE teamid=$t AND teamrerolls>0");
      goBack();
      break;
    case 'addff':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                              ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $numGames = $dbconn->getOne("SELECT count(1) FROM naf_teamgame WHERE teamid=$t");

      if ($numGames == 0)
        $dbconn->execute("UPDATE naf_team SET teamfanfactor=teamfanfactor+1, teamtreasury=teamtreasury-10000 WHERE teamid=$t AND teamtreasury>=10000");

      goBack();
      break;
    case 'delff':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $numGames = $dbconn->getOne("SELECT count(1) FROM naf_teamgame WHERE teamid=$t");

      if ($numGames == 0)
        $dbconn->execute("UPDATE naf_team SET teamfanfactor=teamfanfactor-1, teamtreasury=teamtreasury+10000 WHERE teamid=$t AND teamfanfactor>0");

      goBack();
      break;
    case 'addcoach':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $dbconn->execute("UPDATE naf_team SET teamcoaches=teamcoaches+1, teamtreasury=teamtreasury-10000 WHERE teamid=$t AND teamtreasury>=10000");

      goBack();
      break;
    case 'delcoach':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $numGames = $dbconn->getOne("SELECT count(1) FROM naf_teamgame WHERE teamid=$t");

      if ($numGames > 0)
        $cost=0;
      else
        $cost=10000;

      $dbconn->execute("UPDATE naf_team SET teamcoaches=teamcoaches-1, teamtreasury=teamtreasury+$cost WHERE teamid=$t AND teamcoaches>0");

      goBack();
      break;
    case 'addcheer':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $dbconn->execute("UPDATE naf_team SET teamcheerleaders=teamcheerleaders+1, teamtreasury=teamtreasury-10000 WHERE teamid=$t AND teamtreasury>=10000");

      goBack();
      break;
    case 'delcheer':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $numGames = $dbconn->getOne("SELECT count(1) FROM naf_teamgame WHERE teamid=$t");

      if ($numGames > 0)
        $cost=0;
      else
        $cost=10000;

      $dbconn->execute("UPDATE naf_team SET teamcheerleaders=teamcheerleaders-1, teamtreasury=teamtreasury+$cost WHERE teamid=$t AND teamcoaches>0");

      goBack();
      break;
    case 'addapoth':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost, r.apoth FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      if ($team->Fields('apoth') == 'n')
        goBack("This team can not hire an apothecary");

      $dbconn->execute("UPDATE naf_team SET teamapoth=1, teamtreasury=teamtreasury-50000 WHERE teamid=$t AND teamtreasury>=50000 AND teamapoth=0");
      goBack();
      break;
    case 'delapoth':
      $t = pnVarCleanFromInput('t');
      $t+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $numGames = $dbconn->getOne("SELECT count(1) FROM naf_teamgame WHERE teamid=$t");

      if ($numGames > 0)
        $cost=0;
      else
        $cost=50000;
      $dbconn->execute("UPDATE naf_team SET teamapoth=teamapoth-1, teamtreasury=teamtreasury+$cost WHERE teamid=$t AND teamapoth>0");

      goBack();
      break;
    case 'delete':
      list($t, $confirm) = pnVarCleanFromInput('t', 'confirm');
      $t+=0; $confirm+=0;
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");
      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      if ($confirm==1) {
        $dbconn->execute("DELETE FROM naf_team WHERE teamid=$t");
        $dbconn->execute("DELETE FROM naf_teamgame WHERE teamid=$t");
        $players = $dbconn->execute("SELECT playerid FROM naf_player WHERE teamid=$t");
        $p = array();
        for ( ; !$players->EOF; $players->moveNext() ) {
          $p[] = $players->fields[0];
        }

        if (count($p) > 0)
          $dbconn->execute("DELETE FROM naf_playerskill WHERE playerid IN (".implode(',', $p).")");

        $dbconn->execute("DELETE FROM naf_player WHERE teamid=$t");
        header('Location: naf.php?page=team');
      }
      else {
        include 'header.php';
        OpenTable();

        echo '<div style="font-size: 2em;">Are you sure you want to <span style="color: red;">Delete</span> \''.pnVarPrepForDisplay($team->Fields('teamname')).'\'?</div>'
            .'<br />'
            .'Deleting the team will also delete all game records, players and notes that have been written. This is an '
            .'irreversible action.<br />'
            .'<br />'
            .'<a href="naf.php?page=team&op=view&t='.$t.'">No, do not delete this team</a><br /><br />'
            .'<a href="naf.php?page=team&op=delete&t='.$t.'&confirm=1">Yes, delete this team</a>';

        CloseTable();
        include 'footer.php';
      }
      break;
    case 'create':
      $u = pnUserGetVar('uid');

      list($name, $race) = pnVarCleanFromInput('name', 'race');
      $name = trim($name);
      $race += 0;
      if ($race==0 || strlen($name)==0) {
        include 'header.php';
        OpenTable();
        echo '<div class="title">Create Team</div>';
        echo '<form action="naf.php" method="post">';
        echo '<input type="hidden" name="page" value="team" />'
            .'<input type="hidden" name="op" value="create" />';
        $races = $dbconn->execute("SELECT raceid, name FROM naf_race order by name");
        echo '<br />Race<br /><select name="race">';
        for ( ; !$races->EOF; $races->moveNext() ) {
          echo '<option value="'.$races->Fields('raceid').'">'.$races->Fields('name').'</option>';
        }
        echo '</select><br />';
        echo 'Name<br /><input type="text" name="name" /><br />';
        echo '<input type="submit" value="Create Team" />';
        echo '</form>';
        CloseTable();
        include 'footer.php';
      }
      else {
        $name = trim($name);
        $existing = $dbconn->execute("SELECT * FROM naf_team WHERE coachid=$u AND teamname='".pnVarPrepForStore($name)."'");
        if (!$existing->EOF) {
          echo "<html><head><title>Oops</title></head><body bgcolor=\"#ffffff\">"
              ."You can not have two teams with the same name.<br />"
              ."<a href=\"naf.php?page=team&op=create\">Back</a>"
              ."</body></html>";
          exit;
        }
        $dbconn->execute("INSERT INTO naf_team (coachid, teamname, teamrace, teamtreasury) "
                        ."VALUES ($u, '".pnVarPrepForStore($name)."', $race, 1000000)");
        pnRedirect('/naf.php?page=team');
      }
      break;
    case 'delreport':
      list($t, $id) = pnVarCleanFromInput('t', 'id');
      $t += 0;
      $id += 0;

      $team = $dbconn->execute("SELECT t.*, r.name as race FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) WHERE teamid=$t");
      $teamName = $team->Fields('teamname');

      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $oldgame = $dbconn->execute("SELECT * FROM naf_teamgame WHERE gameid=$id AND teamid=$t");
      if (!$oldgame || $oldgame->EOF)
        goBack("No such game");

      $gold = $oldgame->Fields('winnings');
      $ff = $oldgame->Fields('fanfactor');

      $dbconn->execute("UPDATE naf_team SET teamtreasury=teamtreasury-".$gold.", teamfanfactor=teamfanfactor-".$ff." WHERE teamid=$t");

      $deaths = $dbconn->execute("SELECT playerid FROM naf_playergame WHERE gameid=$id AND injury=7");

      $arr =array();
      for ( ; !$deaths->EOF; $deaths->moveNext() ) {
        $arr[] = $deaths->fields[0];
      }
      if (count($arr) > 0) {
        $dbconn->execute("UPDATE naf_player SET playerstatus='ACTIVE' WHERE playerid IN (".implode(",", $arr).")");
      }

      $dbconn->execute("DELETE FROM naf_playergame WHERE gameid=$id AND injury<>7");

      $dbconn->execute("DELETE FROM naf_teamgame WHERE gameid=$id");

      fixSpp($id);

      goBack();
      break;
    case 'report':
      list($t, $submit, $id) = pnVarCleanFromInput('t', 'submit', 'id');
      $t += 0;
      $id += 0;

      $team = $dbconn->execute("SELECT t.*, r.name as race FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) WHERE teamid=$t");
      $teamName = $team->Fields('teamname');

      if ($team->fields('coachid') != $uid)
        goBack("Not your team");

      $players = $dbconn->execute("SELECT * FROM naf_player WHERE teamid=$t AND playerstatus='ACTIVE' ORDER BY playernumber");
      $numPlayers = $players->numRows();

      if ($submit == 1) {
        list($opponentteam, $opponentrace, $teamrating, $opponentrating, $teamtd, $opponenttd, $teamcas, $opponentcas,
             $fanfactor, $winnings, $gate, $notes, $pid, $inj, $cp, $td, $int, $cas, $mvp, $id) =
        pnVarCleanFromInput('name', 'opponentrace', 'teamrating', 'opponentrating', 'teamtd', 'opponenttd', 'teamcas',
                            'opponentcas', 'fanfactor', 'winnings', 'gate', 'notes', 'pid', 'inj', 'cp', 'td', 'int', 'cas',
                            'mvp', 'id');

        list($opponentteam, $opponentrace, $teamrating, $opponentrating, $teamtd, $opponenttd, $teamcas, $opponentcas,
             $fanfactor, $winnings, $gate, $notes) =
        pnVarPrepForStore($opponentteam, $opponentrace, $teamrating, $opponentrating, $teamtd, $opponenttd, $teamcas, $opponentcas,
             $fanfactor, $winnings, $gate, $notes);

        $t+=0; $opponentrace+=0; $teamrating+=0; $opponentrating+=0; $teamtd+=0; $opponenttd+=0; $teamcas+=0;
        $opponentcas+=0; $gate+=0; $winnings+=0; $fanfactor+=0;

        if ($id > 0) {
          $oldgame = $dbconn->execute("SELECT * FROM naf_teamgame WHERE gameid=$id");

          $gold = $winnings - $oldgame->Fields('winnings');
          $ff = $fanfactor - $oldgame->Fields('fanfactor');

          $dbconn->execute("UPDATE naf_team SET teamtreasury=teamtreasury+$gold, teamfanfactor=teamfanfactor+$ff WHERE teamid=$t");

          $dbconn->execute("UPDATE naf_teamgame SET opponentname='$opponentteam', opponentrace=$opponentrace, teamtr=$teamrating, "
                          ."opponenttr=$opponentrating, teamtd=$teamtd, opponenttd=$opponenttd, teamcas=$teamcas, "
                          ."opponentcas=$opponentcas, gate=$gate, winnings=$winnings, fanfactor=$fanfactor, notes='$notes' "
                          ."WHERE gameid=$id");
          $dbconn->execute("DELETE FROM naf_playergame WHERE gameid=$id AND injury<>7");
          $update=true;
        }
        else {
          $res = $dbconn->execute("INSERT INTO naf_teamgame (teamid, opponentname, opponentrace, teamtr, opponenttr, teamtd, opponenttd, "
                           ."teamcas, opponentcas, gate, winnings, fanfactor, notes) "
                           ."VALUES ($t, '$opponentteam', $opponentrace, $teamrating, $opponentrating, $teamtd, $opponenttd, "
                           ."$teamcas, $opponentcas, $gate, $winnings, $fanfactor, '$notes')");
          $id = $dbconn->insert_id();

          $dbconn->execute("UPDATE naf_team SET teamtreasury=teamtreasury+$winnings, teamfanfactor=teamfanfactor+$fanfactor "
                          ."WHERE teamid=$t");

          $update=false;
        }

        $query = "INSERT INTO naf_playergame (playerid, gameid, injury, completions, interceptions, touchdowns, casualties, mvps) VALUES ";
        $list = array();

        for ($i=0; $i<16; $i++) {

          $injury = $inj[$i]+0;
          $completions = $cp[$i]+0;
          $interceptions = $int[$i]+0;
          $touchdowns = $td[$i]+0;
          $casualties = $cas[$i]+0;
          $mvps = $mvp[$i]+0;
          $spps = $completions + 2*$interceptions + 3*$touchdowns + 2*$casualties + 5*$mvps;

          if ($injury+$completions+$interceptions+$touchdowns+$casualties+$mvps > 0) {
            if (!$update) {
              $dbconn->execute("UPDATE naf_player SET playercp=playercp+$completions, playerint=playerint+$interceptions, "
                              ."playertd=playertd+$touchdowns, playercas=playercas+$casualties, playermvp=playermvp+$mvps, "
                              ."playerspp=playerspp+$spps WHERE playerid=".$pid[$i]);
              echo $dbconn->errorMsg();
            }
            $list[] = "($pid[$i], $id, $injury, $completions, $interceptions, $touchdowns, $casualties, $mvps)";
          }
        }
        if (count($list) > 0) {
          $dbconn->execute($query.implode(", ", $list));
        }

        if ($update) {
          $perf = $dbconn->execute("SELECT p.playerid, sum(completions) as cp, sum(interceptions) as inter, sum(touchdowns) as td, "
                                  ."sum(casualties) as cas, sum(mvps) as mvp FROM naf_player p "
                                  ."LEFT JOIN naf_playergame pg USING (playerid) WHERE p.teamid=$t GROUP BY p.playerid");
          for ( ; !$perf->EOF; $perf->moveNext() ) {
            $cp = $perf->Fields('cp')+0;
            $int = $perf->Fields('inter')+0;
            $td = $perf->Fields('td')+0;
            $cas = $perf->Fields('cas')+0;
            $mvp = $perf->Fields('mvp')+0;
            $spp = $cp+2*$int+3*$td+2*$cas+5*$mvp;
            $dbconn->execute("UPDATE naf_player SET playercp=$cp, playertd=$td, playercas=$cas, playerint=$int, "
                            ."playermvp=$mvp, playerspp=$spp WHERE playerid=".$perf->Fields('playerid'));
          }
        }

        header("Location: naf.php?page=team&op=view&t=$t");
        exit;
      }
      else {
        if ($id > 0) {
          $match = $dbconn->execute("SELECT * FROM naf_teamgame WHERE gameid=$id");
          if ($match->Fields('teamid') != $t)
            goBack("Not your game");

          $opponentteam = pnVarPrepForDisplay($match->Fields('opponentname'));
          $opponentrace = $match->Fields('opponentrace');
          $teamrating = $match->Fields('teamtr');
          $opponentrating = $match->Fields('opponenttr');
          $teamtd = $match->Fields('teamtd');
          $opponenttd = $match->Fields('opponenttd');
          $teamcas = $match->Fields('teamcas');
          $opponentcas = $match->Fields('opponentcas');
          $gate = $match->Fields('gate');
          $winnings = $match->Fields('winnings');
          $fanfactor = $match->Fields('fanfactor');
          $notes = pnVarPrepForDisplay($match->Fields('notes'));

          $performances = $dbconn->execute("SELECT * FROM naf_playergame WHERE gameid=$id");
          $pid=array();
          $inj=array();
          $cp=array();
          $td=array();
          $int=array();
          $cas=array();
          $mvp=array();
          for ( ; !$performances->EOF; $performances->moveNext() ) {
            $pid[] = $performances->Fields('playerid');
            $inj[] = $performances->Fields('injury');
            $cp[] = $performances->Fields('completions');
            $td[] = $performances->Fields('touchdowns');
            $int[] = $performances->Fields('interceptions');
            $cas[] = $performances->Fields('casualties');
            $mvp[] = $performances->Fields('mvps');
          }
        }

        include 'header.php';
        OpenTable();

        echo '<div style="font-size: 200%;">Report Match</div>';
        echo '<a href="naf.php?page=team&op=view&t='.$t.'">Back to team</a><br />';

        echo '<form action="naf.php" method="post">';
        echo '<input type="hidden" name="page" value="team" />'
            .'<input type="hidden" name="op" value="report" />'
            .'<input type="hidden" name="t" value="'.$t.'" />'
            .'<input type="hidden" name="submit" value="1" />';

        if ($id > 0)
          echo '<input type="hidden" name="id" value="'.$id.'" />';

        echo "<div style=\"float: left;\">";

        echo '<table border="0" cellspacing="5">';
        echo '<tr ><th>&nbsp;</th><th>Your team</th><th>Opponent\'s team</th></tr>';
        echo '<tr valign="top"><th align="left">Team</th><td>';
        echo pnVarPrepForDisplay($teamName)."<br />";
        echo '</td><td>';
        echo '<input type="text" name="name" value="'.$opponentteam.'"/></td></tr>';

        $races = $dbconn->execute("SELECT raceid, name FROM naf_race order by name");
        echo '<tr><th align="left">Race</th><td>'.$team->Fields('race').'</td><td>';
        echo '<select name="opponentrace">';
        for ( ; !$races->EOF; $races->moveNext() ) {
          echo '<option value="'.$races->Fields('raceid').'"'.($races->Fields('raceid')==$opponentrace?' selected="1"':'').'>'.$races->Fields('name').'</option>';
        }
        echo '</select></td></tr>';

        echo '<tr valign="top"><th align="left">Team Rating</th><td>';
        echo '<input type="text" size="4" name="teamrating" value="'.($id>0?$teamrating:$team->Fields('teamrating')).'" />';
        echo '</td><td>';
        echo '<input type="text" size="4" name="opponentrating" value="'.$opponentrating.'" /></td></tr>';

        echo '<tr valign="top"><th align="left">Touchdowns</th><td>';
        echo '<input type="text" size="4" name="teamtd" value="'.$teamtd.'"/>';
        echo '</td><td>';
        echo '<input type="text" size="4" name="opponenttd" value="'.$opponenttd.'" /></td></tr>';

        echo '<tr valign="top"><th align="left">Casualties<br /><span style="font-weight: normal; font-size: 80%;">(Caused)</span></th><td>';
        echo '<input type="text" size="4" name="teamcas" value="'.$teamcas.'" />';
        echo '</td><td>';
        echo '<input type="text" size="4" name="opponentcas" value="'.$opponentcas.'"/></td></tr>';

        echo '<tr valign="top"><th align="left">Fan factor<br /><span style="font-weight: normal; font-size: 80%;">(Change)</span></th><td>';
        echo '<input type="text" size="4" name="fanfactor" value="'.($fanfactor+0).'"/>';
        echo '</td><td>';
        echo '&nbsp;</td></tr>';

        echo '<tr valign="top"><th align="left">Winnings</th><td>';
        echo '<input type="text" size="7" name="winnings" value="'.$winnings.'"/>';
        echo '</td><td>';
        echo '&nbsp;</td></tr>';

        echo '<tr valign="top"><th align="left">Gate</th><td>';
        echo '<input type="text" size="7" name="gate" value="'.$gate.'"/>';
        echo '</td><td>&nbsp;</td></tr>';

        echo '<tr valign="top"><th colspan="3" align="left">Notes<br /><textarea name="notes" rows="5" style="width: 100%;">'.$notes.'</textarea></th></tr>';


        echo '<tr><td colspan="3" align="center">';
        echo '</td></tr></table>';

        echo "</div>";

        echo '<table border="0" cellspacing="1" bgcolor="#000000">';
        echo '<tr bgcolor="#ffffff"><th colspan="8">Performance</th></tr>';
        echo '<tr bgcolor="#ffffff"><th>#</th><th>Name</th><th>Inj</th><th>Cp</th><th>Td</th><th>Int</th><th>Cas</th><th>MVP</th></tr>';

        for ($c=0; !$players->EOF; $c++, $players->moveNext() ) {
          $injury = $comp = $touch = $inter = $casual = $mvps = '';
          if ($id>0) {
            $playerid = $players->Fields('playerid');
            $pos = array_search($playerid, $pid);
             if ($pos!==false) {
              $injury = f2($inj[$pos]);
              $comp = f2($cp[$pos]);
              $touch = f2($td[$pos]);
              $inter = f2($int[$pos]);
              $casual = f2($cas[$pos]);
              $mvps = f2($mvp[$pos]);
            }
          }
          echo '<input type="hidden" name="pid['.$c.']" value="'.$players->Fields('playerid').'" />';
          echo '<tr bgcolor="#ffffff">'
              .'<td align="right">'.$players->Fields('playernumber').'</td>'
              .'<td>'.pnVarPrepForDisplay($players->Fields('playername')).'</td>'
              .'<td><select name="inj['.$c.']">'
              .'<option value="0"'.($injury==0?' selected="1"':'').'>&nbsp;</option>'
              .'<option value="1"'.($injury==1?' selected="1"':'').'>Miss</option>'
              .'<option value="2"'.($injury==2?' selected="1"':'').'>Nig</option>'
              .'<option value="3"'.($injury==3?' selected="1"':'').'>-MA</option>'
              .'<option value="4"'.($injury==4?' selected="1"':'').'>-ST</option>'
              .'<option value="5"'.($injury==5?' selected="1"':'').'>-AG</option>'
              .'<option value="6"'.($injury==6?' selected="1"':'').'>-AV</option>'
              .'<option value="7"'.($injury==7?' selected="1"':'').'>Dead</option>'
              .'</td>'
              .'<td><input type="text" size="3" name="cp['.$c.']" value="'.$comp.'" /></td>'
              .'<td><input type="text" size="3" name="td['.$c.']" value="'.$touch.'" /></td>'
              .'<td><input type="text" size="3" name="int['.$c.']" value="'.$inter.'" /></td>'
              .'<td><input type="text" size="3" name="cas['.$c.']" value="'.$casual.'" /></td>'
              .'<td><input type="text" size="3" name="mvp['.$c.']" value="'.$mvps.'" /></td>'
              .'</tr>';
        }
        echo '</table>';

        echo '<div style="clear: both;"><input type="submit" value="Submit Game" /></div>';
        echo '</form>';

        if ($id > 0) {
          echo "<br /><br />";
          echo "<a href=\"/naf.php?page=team&op=delreport&t=$t&id=$id\">Delete this report</a> (Make sure you mean it as there is no confirmation screen.)";
        }

        CloseTable();
        include 'footer.php';
      }

      break;
    case 'buyplayer':
      list($number, $name, $pos, $t) = pnVarCleanFromInput('number', 'name', 'position', 't');

      $t += 0;
      $number += 0;
      $position += 0;

      $team = $dbconn->execute("SELECT * FROM naf_team WHERE teamid=$t");

      if ($team->fields('coachid') != $uid)
        goBack();

      $players = $dbconn->execute("SELECT * FROM naf_player WHERE teamid=$t AND playerstatus='ACTIVE'");
      $numPlayers = $players->numRows();

      $numHash = array();
      $posHash = array();
      $hasBigGuy = false;
      for ( ; !$players->EOF; $players->moveNext()) {
        $numHash[$players->Fields('playernumber')] = true;
        $posHash[$players->Fields('playertypeid')]++;
        if ($players->Fields('big_guy') == 'y')
          $hasBigGuy = true;
      }

      if ($number > 0 && $number < 17) {
        if ($pos+0 == 0)
          goBack();
        $test = $dbconn->execute("SELECT 1 FROM naf_player WHERE playernumber=$number AND teamid=$t AND playerstatus='ACTIVE'");
        if (!$test->EOF)
          goBack("Player Number is taken");

        $position = $dbconn->execute("SELECT * FROM naf_position WHERE playertypeid=$pos");

        if ($team->Fields('teamrace') != $position->Fields('raceid'))
          goBack("Bogus position");

        if ($team->Fields('teamtreasury') < $position->Fields('cost'))
          goBack("You can't afford it!");

        if ($position->Fields('qty') <= $posHash[$pos])
          goBack("You can't have more of those");

        if ($position->Fields('big_guy') == 'y' && $position->Fields('qty')==1 && $hasBugGuy)
          goBack("You can't have more than one big guy on this team.");

        $ma = $position->Fields('ma');
        $st = $position->Fields('st');
        $ag = $position->Fields('ag');
        $av = $position->Fields('av');
        $res = $dbconn->execute("INSERT INTO naf_player (teamid, playernumber, playername, playertypeid, playerma, playerst, "
                        ." playerag, playerav) VALUES ($t, $number, '".pnVarPrepForStore($name)."', $pos, $ma, $st, $ag, $av)");
        if ($res)
          $dbconn->execute("UPDATE naf_team SET teamtreasury=teamtreasury-".$position->Fields('cost')." WHERE teamid=$t");

        pnRedirect("naf.php?page=team&op=buyplayer&t=$t");
      }
      else {
        include 'header.php';
        OpenTable();
        $treasury = $team->Fields('teamtreasury');
        $teamName = $team->Fields('teamname');

        echo '<div class="title">Buy Player for '.pnVarPrepForDisplay($teamName).'</div>';
        echo '<a href="naf.php?page=team&op=view&t='.$t.'">Back to team</a><br />';
        echo '<form action="naf.php" method="post">';
        echo '<input type="hidden" name="page" value="team" />'
            .'<input type="hidden" name="op" value="buyplayer" />'
            .'<input type="hidden" name="t" value="'.$t.'" />';

        echo "<br />Treasury: $treasury<br />";
        echo 'Number<br /><select name="number">';
        for ($i=1; $i<=16; $i++) {
          if (!$numHash[$i])
            echo '<option>'.$i.'</option>';
        }
        echo '</select>';

        $positions = $dbconn->execute("SELECT * FROM naf_position WHERE raceid=".$team->Fields('teamrace')." ORDER BY position");
        echo '<br />Position<br /><select name="position">';
        for ( ; !$positions->EOF; $positions->moveNext() ) {
          if ($hasBigGuy && $positions->Fields('big_guy') == 'y' && $positions->Fields('qty')==1)
            continue;

          if ($posHash[$positions->Fields('playertypeid')]+0 < $positions->Fields('qty') &&
              $team->Fields('teamtreasury') >= $positions->Fields('cost'))
            echo '<option value="'.$positions->Fields('playertypeid').'">'
                .($posHash[$positions->Fields('playertypeid')]+0).'/'.$positions->Fields('qty')." "
                .$positions->Fields('position')
                .' ('.($positions->Fields('cost')/1000).'k)</option>';
        }
        echo '</select><br />';
        echo 'Name<br /><input type="text" name="name" /><br />';
        echo '<input type="submit" value="Buy Player" />';
        echo '</form>';
        CloseTable();
        include 'footer.php';
      }
      break;
    case 'player':
      list($p, $skill, $delskill, $revive, $renumber, $edit, $rename, $age, $delage, $retire, $t) =
           pnVarCleanFromInput('p', 'skill', 'delskill', 'revive', 'renumber', 'edit', 'rename', 'age', 'delage', 'retire', 't');
      $p += 0;
      $skill += 0;
      $delskill += 0;
      $renumber += 0;
      $edit += 0;
      $age += 0;
      $delage += 0;

      $player = $dbconn->execute("SELECT p.*, pos.position, pos.cost, coachid FROM naf_player p LEFT JOIN naf_position pos USING (playertypeid) LEFT JOIN naf_team t ON (p.teamid=t.teamid) WHERE playerid=$p");
      if ($player->Fields('coachid') != $uid)
        goBack("Not your team.");
      $t = $player->Fields('teamid');

      $maxgame = $dbconn->getOne("SELECT max(gameid) FROM naf_teamgame WHERE teamid=$t");

      if ($age > 0 && $age < 6) {
        $dbconn->execute("INSERT INTO naf_playerage (playerid, ageid) VALUES ($p, $age)");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      if ($delage > 0) {
        $dbconn->execute("DELETE FROM naf_playerage WHERE playerageid=$delage AND playerid=$p");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      if ($retire > 0) {
        if ($maxgame > 0) {
          $dbconn->execute("UPDATE naf_player SET playerstatus='RETIRED' WHERE playerid=$p");
        }
        else if (!$player->EOF) {
          $dbconn->execute("DELETE FROM naf_player WHERE playerid=$p");
          $dbconn->execute("UPDATE naf_team SET teamtreasury=teamtreasury+".$player->Fields('cost')." WHERE teamid=$t");
        }
        pnRedirect("naf.php?page=team&op=view&t=$t");
        exit;
      }

      if ($unretire > 0) {
        $dbconn->execute("UPDATE naf_player SET playerstatus='ACTIVE' WHERE playerid=$p");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      if ($skill > 0) {
        $dbconn->execute("INSERT INTO naf_playerskills (playerid, skillid) VALUES ($p, $skill)");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      if ($delskill > 0) {
        $dbconn->execute("DELETE FROM naf_playerskills WHERE playerid=$p AND skillid=$delskill LIMIT 1");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      if ($revive > 0) {
        $dbconn->execute("UPDATE naf_playergame SET injury=0 WHERE playerid=$p AND injury=7");
        $dbconn->execute("UPDATE naf_player SET playerstatus='ACTIVE' WHERE playerid=$p");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      if ($renumber > 0 && $renumber<17) {
        $dbconn->execute("UPDATE naf_player SET playernumber=$renumber WHERE playerid=$p");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      if (strlen($rename) > 0) {
        $dbconn->execute("UPDATE naf_player SET playername='".pnVarPrepForStore(trim($rename))."' WHERE playerid=$p");
        pnRedirect("naf.php?page=team&op=player&t=$t&p=$p");
        exit;
      }

      $pos = $player->Fields('playertypeid');

      include 'header.php';
      OpenTable();

      $statMod = array(0, 0, 0, 0, 0);

      $posSkills = $dbconn->execute("SELECT name FROM naf_positionskills LEFT JOIN naf_skills USING (skillid) WHERE positionid=$pos ORDER BY name");
      $posSkillNames = array();
      for ( ; !$posSkills->EOF; $posSkills->moveNext() ) {
        $posSkillNames[] = $posSkills->Fields('name');
      }

      $skills = $dbconn->execute("SELECT naf_skills.skillid, name FROM naf_playerskills LEFT JOIN naf_skills USING (skillid) WHERE playerid=$p ORDER BY name");
      $skillNames = array();
      $skillId = array();
      for ( ; !$skills->EOF; $skills->moveNext() ) {
        $s = $skills->Fields('skillid');
        if ($s < 5)
          $statMod[$s]++;

        if ($s == 51) // Very Long Legs
          $statMod[1]++;

        if ($s == 48) // Spikes
          $statMod[4]++;

        $skillNames[] = $skills->Fields('name');
        $skillId[$skills->Fields('name')] = $s;
      }

      $skills = array_merge($posSkillNames, $skillNames);

      $inj = $dbconn->execute("SELECT injury, pg.gameid FROM naf_playergame pg "
                             ."WHERE pg.playerid=$p AND injury>0 ORDER BY injury");
      $injArray = array();
      $injNames = array( 'None', 'm', 'n', '-MA', '-ST', '-AG', '-AV', 'd' );
      for ( ; !$inj->EOF; $inj->moveNext() ) {
        $i = $inj->Fields('injury');

        if ($i != 1 || $inj->Fields('gameid') == $maxgame) {
          if ($i!=7)
            $injArray[] = $injNames[$i];
          else
            $injArray[] = $injNames[$i] . " (<a href=\"naf.php?page=team&op=player&t=$t&p=$p&revive=".$p."\">Revive</a>)";
        }

        if ($i>2 && $i<7) {
          $statMod[$i-2]--;
        }

        if ($i > 1 && $i != 7 && $inj->Fields('gameid') == $maxgame) {
          array_unshift($injArray, 'm');
        }
      }

      $age = $dbconn->execute("SELECT pa.*, a.name FROM naf_playerage pa LEFT JOIN naf_age a USING (ageid) WHERE playerid=$p ORDER BY pa.ageid");

      for ( ; !$age->EOF; $age->moveNext() ) {
        $a = $age->Fields('ageid');
        $injArray[] = $age->Fields('name') . " (<a href=\"naf.php?page=team&op=player&t=$t&p=$p&delage=".$age->Fields('playerageid')."\">Remove</a>)";
        if ($a < 5)
          $statMod[$a]--;
      }

      echo '<a href="naf.php?page=team&op=view&t='.$t.'">Back to team</a><br />';
      echo "<table width=\"300\" border=\"0\" cellspacing=\"1\" bgcolor=\"#000000\" style=\"margin-top: 3px;\">";
      $n = $player->Fields('playernumber');
      echo "<tr bgcolor=\"#ffffff\"><th colspan=\"5\" style=\"font-size: 2em;\">"
          ."<div style=\"float: left; font-size: 50%;\">$n</div>"
          ."<div style=\"float: right; font-size: 50%;\">$n</div>"
          .pnVarPrepForDisplay($player->Fields('playername'))
          ."<br /><span style=\"font-size: 0.5em;\">".$player->Fields('position')."</span></th></tr>";

      echo "<tr bgcolor=\"#ffffff\"><th colspan=\"5\">Stats</th></tr>";
      echo "<tr bgcolor=\"#ffffff\"><th>MA</th><th>ST</th><th>AG</th><th>AV</th><th>Cost</th></tr>";
      echo "<tr align=\"center\" bgcolor=\"#ffffff\">"
          ."<td>".fixStat($player->Fields('playerma'), $statMod[1])."</td>"
          ."<td>".fixStat($player->Fields('playerst'), $statMod[2])."</td>"
          ."<td>".fixStat($player->Fields('playerag'), $statMod[3])."</td>"
          ."<td>".fixStat($player->Fields('playerav'), $statMod[4])."</td>"
          ."<td>".($player->Fields('cost')/1000)."k</td></tr>";

      echo "<tr bgcolor=\"#ffffff\"><th colspan=\"5\">Performance (".$player->Fields('playerspp')." SPP)</th></tr>";
      echo "<tr bgcolor=\"#ffffff\"><th>Cp</th><th>TD</th><th>Int</th><th>Cas</th><th>MVP</th></tr>";
      echo "<tr align=\"center\" bgcolor=\"#ffffff\"><td>".$player->Fields('playercp')."</td><td>".$player->Fields('playertd')."</td><td>"
          .$player->Fields('playerint')."</td><td>".$player->Fields('playercas')."</td><td>".$player->Fields('playermvp')."</td></tr>";

      echo "<tr bgcolor=\"#ffffff\"><th colspan=\"3\">Skills</th><th colspan=\"2\">Injuries</th></tr>";
      echo "<tr valign=\"top\" bgcolor=\"#ffffff\"><td colspan=\"3\">";
      if (count($skills) > 0) {
        foreach ($posSkillNames as $skill) {
          echo "$skill<br />\n";
        }
        foreach ($skillNames as $skill) {
          echo "$skill (<a href=\"naf.php?page=team&op=player&t=$t&p=$p&delskill=".$skillId[$skill]."\">Del</a>)<br />\n";
        }
      }
      else
        echo "None<br />";

      $spp = $player->Fields('playerspp');
      $numSkills = 0;

      if ($spp >= 6) $numSkills++;
      if ($spp >= 16) $numSkills++;
      if ($spp >= 31) $numSkills++;
      if ($spp >= 51) $numSkills++;
      if ($spp >= 76) $numSkills++;
      if ($spp >= 126) $numSkills++;
      if ($spp >= 176) $numSkills++;

      if (count($skillNames) < $numSkills) {
        $s = $dbconn->execute("SELECT skillid, name FROM naf_skills WHERE type='SKILL' OR type='TRAIT' OR type='STAT' ORDER BY name");

        echo "<form action=\"naf.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"page\" value=\"team\" />"
            ."<input type=\"hidden\" name=\"op\" value=\"player\" />"
            ."<input type=\"hidden\" name=\"t\" value=\"$t\" />"
            ."<input type=\"hidden\" name=\"p\" value=\"$p\" />";
        echo "<select name=\"skill\">";
        for ( ; !$s->EOF; $s->moveNext() ) {
          $name = $s->Fields('name');
          if (array_search($name, $skills) === false)
            echo "<option value=\"".$s->Fields('skillid')."\">$name</option>\n";
        }
        echo "</select> ";
        echo "<input type=\"submit\" value=\"Add\" />";
        echo "</form>";
      }

      echo "</td>";
      echo "<td colspan=\"2\">";

      /************ Injuries **************/
      if (count($injArray) > 0) {
        foreach($injArray as $inj)
          echo $inj."<br />";
      }
      else
        echo "&nbsp;";

      echo "</td></tr>";
      echo "</table>";

      if ($edit==0) {
        echo "<a href=\"naf.php?page=team&op=player&t=$t&p=$p&edit=1\">Modify Player</a>";
      }
      else {
        echo "<div class=\"title\">Modification</div>";

        $age = $dbconn->execute("SELECT * FROM naf_age order by ageid");
        $ageSelect = "<select name=\"age\">";
        for ( ; !$age->EOF; $age->moveNext() ) {
          $ageSelect .= "<option value=\"".$age->Fields('ageid')."\">".$age->Fields('name')."</option>";
        }
        $ageSelect .= "</select>";

        echo "<form action=\"naf.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"page\" value=\"team\" />"
            ."<input type=\"hidden\" name=\"op\" value=\"player\" />"
            ."<input type=\"hidden\" name=\"t\" value=\"$t\" />"
            ."<input type=\"hidden\" name=\"p\" value=\"$p\" />"
            ."Age: ".$ageSelect
            ."<input type=\"submit\" value=\"Add\" />"
            ."</form>";

        echo "<form action=\"naf.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"page\" value=\"team\" />"
            ."<input type=\"hidden\" name=\"op\" value=\"player\" />"
            ."<input type=\"hidden\" name=\"t\" value=\"$t\" />"
            ."<input type=\"hidden\" name=\"p\" value=\"$p\" />"
            ."Number: <input size=\"3\" type=\"text\" name=\"renumber\" value=\"".$player->Fields('playernumber')."\" /> "
            ."<input type=\"submit\" value=\"Renumber\" />"
            ."</form>";

        echo "<form action=\"naf.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"page\" value=\"team\" />"
            ."<input type=\"hidden\" name=\"op\" value=\"player\" />"
            ."<input type=\"hidden\" name=\"t\" value=\"$t\" />"
            ."<input type=\"hidden\" name=\"p\" value=\"$p\" />"
            ."Name: <input type=\"text\" name=\"rename\" value=\"".urlencode($player->Fields('playername'))."\" /> "
            ."<input type=\"submit\" value=\"Rename\" />"
            ."</form>";

        if ($player->Fields('playerstatus') == 'ACTIVE') {
          echo "<form action=\"naf.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"page\" value=\"team\" />"
            ."<input type=\"hidden\" name=\"op\" value=\"player\" />"
              ."<input type=\"hidden\" name=\"t\" value=\"$t\" />"
              ."<input type=\"hidden\" name=\"p\" value=\"$p\" />"
              ."<input type=\"hidden\" name=\"retire\" value=\"$p\" />"
              ."<input type=\"submit\" value=\"Retire Player\" />"
              ."</form>";
        }
        else if ($player->Fields('playerstatus') == 'RETIRED' || $player->Fields('playerstatus') == 'DEAD') {
          echo "<form action=\"naf.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"page\" value=\"team\" />"
            ."<input type=\"hidden\" name=\"op\" value=\"player\" />"
              ."<input type=\"hidden\" name=\"t\" value=\"$t\" />"
              ."<input type=\"hidden\" name=\"p\" value=\"$p\" />"
              ."<input type=\"hidden\" name=\"unretire\" value=\"$p\" />"
              ."<input type=\"submit\" value=\"Unretire Player\" />"
              ."</form>";
        }
      }

      CloseTable();
      include 'footer.php';

      break;
    case 'pastplayers':
      $t = pnVarCleanFromInput('t');
      $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                             ."WHERE teamid=$t");

      $statMod = array();

      $posSkills = $dbconn->execute("SELECT positionid, name FROM naf_positionskills LEFT JOIN naf_skills USING (skillid) ORDER BY name");
      $posSkillNames = array();
      for ( ; !$posSkills->EOF; $posSkills->moveNext() ) {
        if (!is_array($posSkillNames[$posSkills->Fields('positionid')]))
          $posSkillNames[$posSkills->Fields('positionid')] = array();
        $posSkillNames[$posSkills->Fields('positionid')][] = $posSkills->Fields('name');
      }

      $skills = $dbconn->execute("SELECT playerid, naf_skills.skillid, name FROM naf_playerskills LEFT JOIN naf_skills USING (skillid) ORDER BY name");
      $skillNames = array();
      for ( ; !$skills->EOF; $skills->moveNext() ) {
        if (!is_array($skillNames[$skills->Fields('playerid')]))
          $skillNames[$skills->Fields('playerid')] = array();
        if (!is_array($statMod[$skills->Fields('playerid')]))
          $statMod[$skills->Fields('playerid')] = array(0, 0, 0, 0, 0);

        $skillNames[$skills->Fields('playerid')][] = $skills->Fields('name');

        if ($skills->Fields('skillid') < 5)
          $statMod[$skills->Fields('playerid')][$skills->Fields('skillid')]++;

        if ($skills->Fields('skillid') == 51) // Very Long Legs
          $statMod[$skills->Fields('playerid')][1]++;

        if ($skills->Fields('skillid') == 48) // Spikes
          $statMod[$skills->Fields('playerid')][4]++;
      }

      $inj = $dbconn->execute("SELECT p.playerid, injury, pg.gameid FROM naf_playergame pg, naf_player p "
                             ."WHERE pg.playerid=p.playerid AND playerstatus<>'ACTIVE' AND injury>0 ORDER BY injury");
      $injArray = array();
      $injNames = array( 'None', 'm', 'n', '-MA', '-ST', '-AG', '-AV', 'd' );
      for ( ; !$inj->EOF; $inj->moveNext() ) {
        $p = $inj->Fields('playerid');
        $i = $inj->Fields('injury');
        if (!is_array($injArray[$p]))
          $injArray[$p] = array();

        if ($i != 1)
          $injArray[$p][] = $injNames[$i];

        if ($i>2) {
          if (!is_array($statMod[$p]))
            $statMod[$p] = array(0, 0, 0, 0, 0);
          $statMod[$p][$i-2]--;
        }
      }

       $query = "SELECT p.*, pos.position, pos.cost, pos.ma, pos.st, pos.ag, pos.av FROM naf_player p LEFT JOIN naf_position pos USING (playertypeid) where teamid=$t AND playerstatus<>'ACTIVE' ORDER BY playernumber, playername";
      $players = $dbconn->execute($query);


      $playerHTML = "";
      for ($count=0; !$players->EOF; $players->moveNext(), $count++) {
        $warn=false;
        $spp = $players->Fields('playerspp');
        $numSkills = 0;

        if ($spp >= 6) $numSkills++;
        if ($spp >= 16) $numSkills++;
        if ($spp >= 31) $numSkills++;
        if ($spp >= 51) $numSkills++;
        if ($spp >= 76) $numSkills++;
        if ($spp >= 126) $numSkills++;
        if ($spp >= 176) $numSkills++;

        $pSkills = count($skillNames[$players->Fields('playerid')]);
        if ($pSkills < $numSkills)
          $warn = "<b>Needs another skill</b>";
        else if ($pSkills > $numSkills)
          $warn = "<b>Too many skills!</b>";

        if (count($posSkillNames[$players->Fields('playertypeid')])+
            count($skillNames[$players->Fields('playerid')]) > 0)
          $skillList = implode(", ", array_merge($posSkillNames[$players->Fields('playertypeid')], $skillNames[$players->Fields('playerid')]));
        else
          $skillList = "&nbsp;";

        if (count($injArray[$players->Fields('playerid')]) > 0)
          $injuryList = implode(", ", $injArray[$players->Fields('playerid')]);
        else
          $injuryList = "&nbsp;";
        $playerHTML .= "<tr align=\"right\"><td align=\"right\">".$players->Fields('playernumber')."</td>"
            ."<td align=\"left\" style=\"white-space: nowrap;\">"
            ."<a href=\"naf.php?page=team&op=player&t=$t&p=".$players->Fields('playerid')."\">".pnVarPrepForDisplay($players->Fields('playername'))."</a>"
            ."</td>"
            ."<td align=\"left\">".$players->Fields('position')."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('ma'),$statMod[$players->Fields('playerid')][1])."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('st'),$statMod[$players->Fields('playerid')][2])."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('ag'),$statMod[$players->Fields('playerid')][3])."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('av'),$statMod[$players->Fields('playerid')][4])."</td>"
            ."<td align=\"left\">$skillList".($warn?"<br />$warn":'')."</td>"
            ."<td align=\"left\">$injuryList</td>"
            ."<td>".f($players->fields('playercp'))."</td>"
            ."<td>".f($players->fields('playertd'))."</td>"
            ."<td>".f($players->fields('playerint'))."</td>"
            ."<td>".f($players->fields('playercas'))."</td>"
            ."<td>".f($players->fields('playermvp'))."</td>"
            ."<td>".f($players->fields('playerspp'))."</td>"
            ."<td align=\"right\">".($players->Fields('cost')/1000)."k</td>"
            ."</tr>\n";
      }

      $additional_header = array('<style type="text/css"><!-- .roster a { text-decoration: none; } .roster th { border-bottom: solid black 1px; border-right: solid black 1px; padding: 2px; background: #cccccc; } .roster td { padding: 2px; background: transparent; border-bottom: solid black 1px; border-right: solid black 1px;} --></style>');

      include 'header.php';
      OpenTable();

      echo '<div style="text-align: center; margin-bottom: 3px;"><a href="naf.php?page=team&op=view&t='.$t.'">Back to team</a></div>';
      echo '<div class="roster" style="background: white; border: solid black 1px; border-top: solid black 2px; border-left: solid black 2px;">';
      echo '<table width="100%" cellspacing="0" border="0" align="center">'
          .'<tr>'
          .'<th colspan="16" style="font-size: 200%;">'.$team->Fields('teamname').'<br /><span style="font-size: 50%">Past Players</span></th>'
          ."</tr>\n";
      echo '<tr>'
          .'<th width="1%">#</th>'
          .'<th width="5%">Name</th>'
          .'<th width="10%">Position</th>'
          .'<th width="1%">MA</th>'
          .'<th width="1%">ST</th>'
          .'<th width="1%">AG</th>'
          .'<th width="1%">AV</th>'
          .'<th>Skills</th>'
          .'<th width="5%">Inj</th>'
          .'<th width="1%">Cp</th>'
          .'<th width="1%">TD</th>'
          .'<th width="1%">Int</th>'
          .'<th width="1%">Cas</th>'
          .'<th width="1%">MVP</th>'
          .'<th width="1%">SPP</th>'
          .'<th width="1%">Cost</th>'
          ."</tr>\n";

      echo $playerHTML;

      echo '</table>';

      CloseTable();
      include 'footer.php';

      break;
    case 'print':
      $print = true;
    case 'view':
      list($t, $user) = pnVarCleanFromInput('t', 'u');

      if (is_numeric($t))
        $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost, r.apoth FROM naf_team t LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                               ."WHERE teamid=".pnVarPrepForStore($t));
      else {
        //$u = $dbconn->getOne("SELECT pn_uid FROM nuke_users WHERE pn_uname='".pnVarPrepForStore($user)."'");
        //$u+=0;
        $team = $dbconn->execute("SELECT t.*, r.name as race, r.reroll_cost FROM naf_team t, nuke_users LEFT JOIN naf_race r ON (t.teamrace=r.raceid) "
                               ."WHERE pn_uname = '".pnVarPrepForStore($user)."' and teamname='".pnVarPrepForStore($t)."' AND coachid=pn_uid");
        $t = $team->Fields('teamid');

      }

      if ($print && $team->EOF) {
        echo "<html><head><title>Oops</title><body>No such team.</body></html>";
        exit;
      }

      $maxgame = $dbconn->getOne("SELECT max(gameid) FROM naf_teamgame WHERE teamid=".pnVarPrepForStore($t));

      $raceid = $team->Fields('teamrace');

      $statMod = array();
      $sql = "SELECT positionid, name FROM naf_positionskills LEFT JOIN naf_skills USING (skillid) LEFT JOIN naf_position ON (naf_positionskills.positionid = naf_position.playertypeid) WHERE raceid=$raceid ORDER BY name";
      $posSkills = $dbconn->execute($sql);
      $posSkillNames = array();
      for ( ; !$posSkills->EOF; $posSkills->moveNext() ) {
        if (!is_array($posSkillNames[$posSkills->Fields('positionid')]))
          $posSkillNames[$posSkills->Fields('positionid')] = array();
        $posSkillNames[$posSkills->Fields('positionid')][] = $posSkills->Fields('name');
      }

      $skills = $dbconn->execute("SELECT playerid, naf_skills.skillid, name FROM naf_playerskills LEFT JOIN naf_skills USING (skillid) ORDER BY name");
      $skillNames = array();
      for ( ; !$skills->EOF; $skills->moveNext() ) {
        if (!is_array($skillNames[$skills->Fields('playerid')]))
          $skillNames[$skills->Fields('playerid')] = array();
        if (!is_array($statMod[$skills->Fields('playerid')]))
          $statMod[$skills->Fields('playerid')] = array(0, 0, 0, 0, 0);

        $skillNames[$skills->Fields('playerid')][] = $skills->Fields('name');

        if ($skills->Fields('skillid') < 5)  // MA ST AG AV
          $statMod[$skills->Fields('playerid')][$skills->Fields('skillid')]++;

        if ($skills->Fields('skillid') == 51) // Very Long Legs
          $statMod[$skills->Fields('playerid')][1]++;

        if ($skills->Fields('skillid') == 48) // Spikes
          $statMod[$skills->Fields('playerid')][4]++;
      }

      $inj = $dbconn->execute("SELECT p.playerid, injury, pg.gameid FROM naf_playergame pg, naf_player p "
                             ."WHERE pg.playerid=p.playerid AND p.teamid=$t AND playerstatus='ACTIVE' AND injury>0 ORDER BY injury");

      $injArray = array();
      $injNames = array( 'None', 'm', 'n', '-MA', '-ST', '-AG', '-AV', 'd' );
      for ( ; !$inj->EOF; $inj->moveNext() ) {
        $p = $inj->Fields('playerid');
        $i = $inj->Fields('injury');
        if (!is_array($injArray[$p]))
          $injArray[$p] = array();

        if ($i != 1 || $inj->Fields('gameid') == $maxgame)
          $injArray[$p][] = $injNames[$i];

        if ($i>2 && $i<7) {
          if (!is_array($statMod[$p]))
            $statMod[$p] = array(0, 0, 0, 0, 0);
          $statMod[$p][$i-2]--;
        }

        if ($i == 7) {
          $dbconn->execute("UPDATE naf_player SET playerstatus='DEAD' WHERE playerid=".$inj->Fields('playerid'));
        }
        else if ($i > 1 && $inj->Fields('gameid') == $maxgame) {
          array_unshift($injArray[$inj->Fields('playerid')], 'm');
        }
      }

      $age = $dbconn->execute("SELECT pa.*, a.name FROM naf_playerage pa LEFT JOIN naf_age a USING (ageid) LEFT JOIN naf_player p ON (pa.playerid=p.playerid) WHERE teamid=$t ORDER BY pa.ageid");
      for ( ; !$age->EOF; $age->moveNext() ) {
        $a = $age->Fields('ageid');
        $p = $age->Fields('playerid');
        $injArray[$p][] = $age->Fields('name');
        if ($a < 5)
          $statMod[$p][$a]--;
      }

      $totalCp=0;
      $totalTD=0;
      $totalInt=0;
      $totalCas=0;
      $totalMVP=0;
      $totalSPP=0;
      $totalCost=0;
      $teamRating = $team->Fields('teamtreasury') / 10000 +
                    $team->Fields('teamfanfactor') +
                    $team->Fields('teamrerolls') * $team->Fields('reroll_cost')/10000 +
                    $team->Fields('teamcoaches') +
                    $team->Fields('teamcheerleaders') +
                    $team->Fields('teamapoth')*5;

      $query = "SELECT p.*, pos.position, pos.cost, pos.ma, pos.st, pos.ag, pos.av FROM naf_player p LEFT JOIN naf_position pos USING (playertypeid) where teamid=$t AND playerstatus='ACTIVE' ORDER BY playernumber, playername";
      $players = $dbconn->execute($query);


      $playerHTML = "";
      for ($count=0; !$players->EOF; $players->moveNext(), $count++) {
        $warn=false;
        if (!$print) {
          $spp = $players->Fields('playerspp');
          $numSkills = 0;

          if ($spp >= 6) $numSkills++;
          if ($spp >= 16) $numSkills++;
          if ($spp >= 31) $numSkills++;
          if ($spp >= 51) $numSkills++;
          if ($spp >= 76) $numSkills++;
          if ($spp >= 126) $numSkills++;
          if ($spp >= 176) $numSkills++;

          $pSkills = count($skillNames[$players->Fields('playerid')]);
          if ($pSkills < $numSkills)
            $warn = "<b>Needs another skill</b>";
          else if ($pSkills > $numSkills)
            $warn = "<b>Too many skills!</b>";

        }
        while ($count+1 < $players->Fields('playernumber')) {
          $count++;
          $playerHTML .= '<tr>'
            .'<td align="right">'.$count.'</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'</tr>';
        }
        $totalCp += $players->Fields('playercp');
        $totalTD += $players->Fields('playertd');
        $totalInt += $players->Fields('playerint');
        $totalCas += $players->Fields('playercas');
        $totalMVP += $players->Fields('playermvp');
        $totalSPP += $players->Fields('playerspp');
        $totalCost += $players->Fields('cost');
        $teamRating += $players->Fields('cost') / 10000;
        if (count($posSkillNames[$players->Fields('playertypeid')])+
            count($skillNames[$players->Fields('playerid')]) > 0)
          $skillList = implode(", ", array_merge($posSkillNames[$players->Fields('playertypeid')], $skillNames[$players->Fields('playerid')]));
        else
          $skillList = "&nbsp;";

        if (count($injArray[$players->Fields('playerid')]) > 0)
          $injuryList = implode(", ", $injArray[$players->Fields('playerid')]);
        else
          $injuryList = "&nbsp;";
        $pname = pnVarPrepForDisplay($players->fields('playername'));
        if (strlen($pname)==0)
          $pname=$print?"&nbsp;":"{Unnamed}";

        $playerHTML .= "<tr align=\"right\"><td align=\"right\">".$players->Fields('playernumber')."</td>"
            ."<td align=\"left\" style=\"white-space: nowrap;\">"
            .($print?$pname:"<a href=\"naf.php?page=team&op=player&t=$t&p=".$players->Fields('playerid')."\">".$pname."</a>")
            ."</td>"
            ."<td align=\"left\">".$players->Fields('position')."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('ma'),$statMod[$players->Fields('playerid')][1])."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('st'),$statMod[$players->Fields('playerid')][2])."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('ag'),$statMod[$players->Fields('playerid')][3])."</td>"
            ."<td align=\"center\">".fixStat($players->Fields('av'),$statMod[$players->Fields('playerid')][4])."</td>"
            ."<td align=\"left\">$skillList".($warn?"<br />$warn":'')."</td>"
            ."<td align=\"left\">$injuryList</td>"
            ."<td>".f($players->fields('playercp'))."</td>"
            ."<td>".f($players->fields('playertd'))."</td>"
            ."<td>".f($players->fields('playerint'))."</td>"
            ."<td>".f($players->fields('playercas'))."</td>"
            ."<td>".f($players->fields('playermvp'))."</td>"
            ."<td>".f($players->fields('playerspp'))."</td>"
            ."<td align=\"right\">".($players->Fields('cost')/1000)."k</td>"
            ."</tr>\n";
      }
      $teamRating += floor($totalSPP/5);
      for ( ; $count<16; $count++) {
        $playerHTML .= '<tr>'
            .'<td align="right">'.($count+1).'</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'<td>&nbsp;</td>'
            .'</tr>';
      }

      if ($print) {
        /*echo '<html><head><title>'.$team->Fields('teamname').'</title>'
            .'<style type="text/css"><!-- th { border-bottom: solid black 1px; border-right: solid black 1px; padding: 2px; background: #cccccc; } td { padding: 2px; background: transparent; border-bottom: solid black 1px; border-right: solid black 1px;} --></style></head>'."\n";
        echo '<body text="#000000" bgcolor="#ffffff">';*/
            $additional_header = array('<style type="text/css"><!-- th { border-bottom: solid black 1px; border-right: solid black 1px; padding: 2px; background: #cccccc; } td { padding: 2px; background: transparent; border-bottom: solid black 1px; border-right: solid black 1px;} --></style>');
      }
      else {
        $additional_header = array('<style type="text/css"><!-- .roster a { text-decoration: none; } .roster th { border-bottom: solid black 1px; border-right: solid black 1px; padding: 2px; background: #cccccc; } .roster td { padding: 2px; background: transparent; border-bottom: solid black 1px; border-right: solid black 1px;} --></style>');
      }
        include 'header.php';
        OpenTable();

      $tr = $dbconn->getOne("SELECT teamrating FROM naf_team where teamid=$t");
      if ($teamRating != $tr) {
        $dbconn->execute("UPDATE naf_team SET teamrating=$teamRating WHERE teamid=$t");
      }

      if (!$print) {
        echo '<div style="text-align: center; margin-bottom: 3px;">'
            .'<a href="naf.php?page=team">Back to team list</a> &bull; '
            .'<a href="naf.php?page=team&op=pastplayers&t='.$t.'">View Past Players</a> &bull; '
            //.'<a href="/teams/'.urlencode(pnUserGetVar('uname')).'/'.urlencode($team->Fields('teamname')).'.html">View Printable Roster</a></div>';
            .'<a href="naf.php?page=team&op=print&t='.$t.'&theme=Printer">View Printable Roster</a></div>';
      }

      echo '<div class="roster" style="background: white URL(/images/NAFwatermark.jpg) center no-repeat; border: solid black 1px; border-top: solid black 2px; border-left: solid black 2px;">';
      echo '<table width="100%" cellspacing="0" border="0" align="center">'
          .'<tr>'
          .'<th colspan="16" style="font-size: 200%;">'.$team->Fields('teamname').'<br /><span style="font-size: 50%">'.$team->Fields('race').' (TR '.$teamRating.')</span></th>'
          ."</tr>\n";
      echo '<tr>'
          .'<th width="1%">#</th>'
          .'<th width="5%">Name</th>'
          .'<th width="10%">Position</th>'
          .'<th width="1%">MA</th>'
          .'<th width="1%">ST</th>'
          .'<th width="1%">AG</th>'
          .'<th width="1%">AV</th>'
          .'<th>Skills</th>'
          .'<th width="7%">Inj</th>'
          .'<th width="1%">Cp</th>'
          .'<th width="1%">TD</th>'
          .'<th width="1%">Int</th>'
          .'<th width="1%">Cas</th>'
          .'<th width="1%">MVP</th>'
          .'<th width="1%">SPP</th>'
          .'<th width="1%">Cost</th>'
          ."</tr>\n";

      echo $playerHTML;

      echo '<tr align="center"><td colspan="9" align="right"><b>Total: </b></td>'
          .'<td>'.$totalCp.'</td>'
          .'<td>'.$totalTD.'</td>'
          .'<td>'.$totalInt.'</td>'
          .'<td>'.$totalCas.'</td>'
          .'<td>'.$totalMVP.'</td>'
          .'<td>'.$totalSPP.'</td>'
          .'<td align="right">'.($totalCost/1000).'k</td>'
          .'</tr>';
      echo '</table>';
//      echo '<tr><td colspan="16" style="padding: 0px;">';
      echo '<table width="100%" border="0" cellspacing="0">'
          .'<tr valign="top">'
          .'<td rowspan="6" width="70%">';
      if ($print || $uid != $team->Fields('coachid')) {
        echo '&nbsp;';
      }
      else {
        echo '<div style="float: right;"><a href="naf.php?page=team&op=delete&t='.$t.'">Delete Team</a></div>';
        echo '<a href="naf.php?page=team&op=buyplayer&t='.$t.'">Buy player</a><br /><br />';
        echo '<a href="naf.php?page=team&op=report&t='.$t.'">Report Match</a><br /><br />';
        echo '<a href="naf.php?page=team&op=treasury&t='.$t.'">Edit Treasury</a><br /><br />';
        echo '<a href="naf.php?page=team&op=fixspp&t='.$t.'">Recalculate SPPs</a><br /><br />';
      }

      $matches = $dbconn->Execute("SELECT g.*, r.name as race FROM naf_teamgame g LEFT JOIN naf_race r ON (g.opponentrace=r.raceid) "
                                 ."WHERE teamid=$t ORDER BY gameid");
      $played = $matches->numRows() > 0;
      $treasury = $team->Fields('teamtreasury');
      $allowApoth = $team->Fields('apoth') == 'y';
      echo '</td>'
          .'<th>Rerolls:</th>'
          .'<td align="center">'
          .($print || $team->Fields('teamrerolls')==0?'':'<a href="naf.php?page=team&op=delreroll&t='.$t.'">-</a> ')
          .(0+$team->Fields('teamrerolls'))
          .($print || $treasury < $team->Fields('reroll_cost')*($played?2:1)?'':' <a href="naf.php?page=team&op=addreroll&t='.$t.'">+</a>')
          .'</td>'
          .'<th>x '.($team->Fields('reroll_cost')/1000).'k =</th>'
          .'<td align="right">'.($team->Fields('teamrerolls') * $team->Fields('reroll_cost') / 1000).'k</td>'
          .'</tr>'
          .'<tr>'
          .'<th>Fan&nbsp;Factor:</th>'
          .'<td align="center">'
          .($print || $played || $team->Fields('teamfanfactor')==0?'':'<a href="naf.php?page=team&op=delff&t='.$t.'">-</a> ')
          .(0+$team->Fields('teamfanfactor'))
          .($print || $played || $treasury < 10000 || $team->Fields('teamfanfactor')>=9?'':' <a href="naf.php?page=team&op=addff&t='.$t.'">+</a>')
          .'</td>'
          .'<th>x 10k =</th>'
          .'<td align="right">'.($team->Fields('teamfanfactor') * 10).'k</td>'
          .'</tr>'
          .'<tr>'
          .'<th>Assistant Coaches:</th>'
          .'<td align="center">'
          .($print || $team->Fields('teamcoaches')==0?'':'<a href="naf.php?page=team&op=delcoach&t='.$t.'">-</a> ')
          .(0+$team->Fields('teamcoaches'))
          .($print || $treasury < 10000?'':' <a href="naf.php?page=team&op=addcoach&t='.$t.'">+</a>')
          .'</td>'
          .'<th>x 10k =</th>'
          .'<td align="right">'.($team->Fields('teamcoaches') * 10).'k</td>'
          .'</tr>'
          .'<tr>'
          .'<th>Cheerleaders:</th>'
          .'<td align="center">'
          .($print || $team->Fields('teamcheerleaders')==0?'':'<a href="naf.php?page=team&op=delcheer&t='.$t.'">-</a> ')
          .(0+$team->Fields('teamcheerleaders'))
          .($print || $treasury < 10000?'':' <a href="naf.php?page=team&op=addcheer&t='.$t.'">+</a>')
          .'</td>'
          .'<th>x 10k =</th>'
          .'<td align="right">'.($team->Fields('teamcheerleaders') * 10).'k</td>'
          .'</tr>'
          .'<tr>'
          .'<th>Apothecary:</th>'
          .'<td align="center">'
          .($print || $team->Fields('teamapoth')==0?'':'<a href="naf.php?page=team&op=delapoth&t='.$t.'">-</a> ')
          .((0+$team->Fields('teamapoth')==0?"No":"Yes"))
          .($print || $treasury < 50000 || $team->Fields('teamapoth')==1 || !$allowApoth?'':' <a href="naf.php?page=team&op=addapoth&t='.$t.'">+</a>')
          .'</td>'
          .'<th>@ 50k =</th>'
          .'<td align="right">'.($team->Fields('teamapoth') * 50).'k</td>'
          .'</tr>'
          .'<tr>'
          .'<th colspan="3">Treasury:</th>'
          .'<td align="right">'.($team->Fields('teamtreasury')/1000).'k</td>'
          .'</tr>'
          .'</table>';
      echo '</div>';

      echo '<div class="roster" style="page-break-before: always; margin-top: 1em; background: white; border-top: solid black 1px; border-left: solid black 1px;">';
      echo '<table width="100%" cellspacing="0" border="0" align="center">'
          .'<tr>'
          .'<th colspan="9">Match Record</th>'
          ."</tr>\n";
      echo '<tr>'
          .'<th>Team Rating</th>'
          .'<th>Opponent</th>'
          .'<th>Race</th>'
          .'<th>Result</th>'
          .'<th>Score</th>'
          .'<th>Cas</th>'
          .'<th>FF</th>'
          .'<th>Winnings</th>'
          .'<th>Gate</th>'
          .'</tr>';

      $wins=$ties=$losses=$tdfor=$tdagainst=$casfor=$casagainst=0;
      for ( ; !$matches->EOF; $matches->moveNext() ) {
        $td = $matches->Fields('teamtd');
        $oppTd = $matches->Fields('opponenttd');
        $tdfor += $td;
        $tdagainst += $oppTd;
        $casfor += $matches->Fields('teamcas');
        $casagainst += $matches->Fields('opponentcas');

        if ($td > $oppTd)
          $wins++;
        else if ($td==$oppTd)
          $ties++;
        else
          $losses++;

        echo "<tr align=\"center\">"
            ."<td align=\"left\">".$matches->Fields('teamtr')."</td>"
            ."<td>".(!$print?"<a href=\"naf.php?page=team&op=report&t=$t&id=".$matches->Fields('gameid')."\">":"").$matches->Fields('opponentname')." (TR ".$matches->Fields('opponenttr').")".(!$print?"</a>":"")."</td>"
            ."<td align=\"left\">".$matches->Fields('race')."</td>"
            ."<td>".($td>$oppTd?"Win":($td==$oppTd?"Tie":"Loss"))."</td>"
            ."<td>$td - $oppTd</td>"
            ."<td>".$matches->Fields('teamcas')." - ".$matches->Fields('opponentcas')."</td>"
            ."<td>".$matches->Fields('fanfactor')."</td>"
            ."<td align=\"right\">".$matches->Fields('winnings')."</td>"
            ."<td align=\"right\">".$matches->Fields('gate')."</td>"
            ."</tr>\n";
      }
      echo '</table>';

      if ( $wins != $team->Fields('teamwins') ||
           $ties != $team->Fields('teamties') ||
           $losses != $team->Fields('teamlosses') ||
           $tdfor != $team->Fields('teamtdfor') ||
           $tdagainst != $team->Fields('teamtdagainst') ||
           $casfor != $team->Fields('teamcasfor') ||
           $casagainst != $team->Fields('teamcasagainst') ) {
        $dbconn->execute("UPDATE naf_team SET teamwins=$wins, teamties=$ties, teamlosses=$losses, teamtdfor=$tdfor, "
                        ."teamtdagainst=$tdagainst, teamcasfor=$casfor, teamcasagainst=$casagainst WHERE teamid=$t");
      }

      echo '</div>';

      /*if ($print) {
        echo '</body></html>';
      }
      else {*/
        CloseTable();
        include 'footer.php';
      //}
      break;
    default:
      $uid = pnUserGetVar('uid');

      if ($u == 0)
        $u = $uid;

      $uname = $dbconn->getOne("SELECT pn_uname FROM nuke_users WHERE pn_uid=$u");
      include 'header.php';
      OpenTable();

      echo '<div style="font-size: 2em;">Simple Team And Roster System (STARS)</div>';

      echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
          .'<tr><th bgcolor="#D9D8D0" colspan="6">'.poss($uname).' teams</th></tr>'
          .'<tr>'
          .'<th bgcolor="#D9D8D0">Team Name</th>'
          .'<th bgcolor="#D9D8D0">Race</th>'
          .'<th bgcolor="#D9D8D0">Rating</th>'
          .'<th bgcolor="#D9D8D0">Record<br /><span style="font-size: 0.8em;">(W/T/L)</span></th>'
          .'<th bgcolor="#D9D8D0">TD Diff</th>'
          .'<th bgcolor="#D9D8D0">Cas Diff</th>'
          .'</tr>';

      $teams = $dbconn->execute("SELECT naf_team.*, naf_race.name as race "
                               ."FROM naf_team LEFT JOIN naf_race ON (teamrace=raceid) WHERE coachid=$u order by teamname");

      for ( ; !$teams->EOF; $teams->moveNext() ) {
        if ($u == $uid)
          $link = "naf.php?page=team&op=view&t=".$teams->Fields('teamid');
        else
          //$link = '/teams/'.urlencode($uname).'/'.urlencode($teams->Fields('teamname')).'.html';
          $link = 'naf.php?page=team&op=print&t='.$t.'&theme=Printer';

        $tdDiff = $teams->Fields('teamtdfor')-$teams->Fields('teamtdagainst');
        if ($tdDiff > 0) $tdDiff = "+$tdDiff";
        $casDiff = $teams->Fields('teamcasfor')-$teams->Fields('teamcasagainst');
        if ($casDiff > 0) $casDiff = "+$casDiff";
        echo "<tr>"
            ."<td bgcolor=\"#f8f7ee\"><a href=\"$link\">".$teams->Fields('teamname')."</a></td>"
            ."<td bgcolor=\"#f8f7ee\">".$teams->Fields('race')."</td>"
            ."<td bgcolor=\"#f8f7ee\" align=\"center\">".$teams->Fields('teamrating')."</td>"
            ."<td bgcolor=\"#f8f7ee\" align=\"center\">".$teams->Fields('teamwins')."/".$teams->Fields('teamties')."/".$teams->Fields('teamlosses')."</td>"
            ."<td bgcolor=\"#f8f7ee\" align=\"center\">$tdDiff</td>"
            ."<td bgcolor=\"#f8f7ee\" align=\"center\">$casDiff</td>"
            ."</tr>";
      }

      echo '</table>';
      echo '<div align="center"><a href="/naf.php?page=team&op=create">Create Team</a></div>';

      CloseTable();
      include 'footer.php';
      break;
  }
?>
