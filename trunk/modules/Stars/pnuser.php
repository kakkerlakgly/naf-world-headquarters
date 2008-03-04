<?php
function goBack($t, $err=false) {
  if($err) pnRedirect(pnModURL('Stars', '', 'view', array('t' => $t, 'err' => $err)));
  else pnRedirect(pnModURL('Stars', '', 'view', array('t' => $t)));
  exit;
}

function poss($name) {
  return ($name{strlen($name)-1}=='s') ? $name."'" : $name."'s";
}

function fixStat($val, $m, $rules_version) {
  $m += 0; $val += 0;
  if ($m < -2) $m = -2;
  if ($m > 2) $m = 2;
  $val += $m;
  if ($val < 1) $val = 1;
  if ($rules_version >= 5 && $val > 10) $val = 10;
  return ($m == 0) ? $val : (($m < 0) ? ('-<b>'.pnVarPrepForDisplay($val).'</b>-') : ('+<b>'.pnVarPrepForDisplay($val).'</b>+'));
}

function fixSPP($team) {
  $dbconn =& pnDBGetConn(true);

  $sql = "SELECT p.playerid, sum(completions) as cp, sum(interceptions) as i, sum(touchdowns) as td, sum(casualties) as cs, "
  ."sum(mvps) as vp FROM nuke_stars_player p LEFT JOIN nuke_stars_playergame pg ON p.playerid=pg.playerid WHERE teamid=".pnVarPrepForStore($team)." group by playerid";
  $result = $dbconn->Execute($sql);
  for ( ; !$result->EOF; $result->moveNext() ) {
    $sql = "UPDATE nuke_stars_player SET "
    ."playercp=".pnVarPrepForStore(0+$result->Fields('cp')).", "
    ."playerint=".pnVarPrepForStore(0+$result->Fields('i')).", "
    ."playertd=".pnVarPrepForStore(0+$result->Fields('td')).", "
    ."playercas=".pnVarPrepForStore(0+$result->Fields('cs')).", "
    ."playermvp=".pnVarPrepForStore(0+$result->Fields('vp')).", "
    ."playerspp=".pnVarPrepForStore($result->Fields('cp')+$result->Fields('i')*2+$result->Fields('td')*3+$result->Fields('cs')*2+$result->Fields('vp')*5)." "
    ."WHERE playerid=".pnVarPrepForStore($result->Fields('playerid'));
    $dbconn->Execute($sql);
    echo $dbconn->errorMsg();
  }
}

function f($num) {
  return ($num>0) ? (pnVarPrepForDisplay($num)) : '&nbsp;';
}

function f2($num) {
  return $num>0?$num:'';
}

function displayError($message) {
  include 'header.php';
  OpenTable();
  echo '<div style="font-size: 2em;">'.pnVarPrepForDisplay($message).'</div>';
  CloseTable();
  include 'footer.php';
  exit;
}

function checkLogin() {
  if (pnUserLoggedIn())
  return;

  displayError('You must be logged in to manage teams.');
}

function Stars_user_fixspp($args) {
  checkLogin();
  $t = pnVarCleanFromInput('t');
  fixSpp($t + 0);
  goBack($t);
  return true;
}

function Stars_user_treasury($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  list($t, $treasury) = pnVarCleanFromInput('t', 'treasury');
  $t+=0;
  $result = $dbconn->Execute("SELECT coachid, teamtreasury FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  if (isset($treasury)) {
    $treasury += 0;
    $dbconn->Execute("UPDATE nuke_stars_team SET teamtreasury=".pnVarPrepForStore($treasury)." WHERE teamid=".pnVarPrepForStore($t));
    goBack($t);
  }
  else {
    include 'header.php';
    OpenTable();
    echo '<div style="font-size: 200%;">Edit Treasury</div>';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'view', array('t' => $t))).'">Back to team</a><br />';

    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'treasury', array('t' => $t))).'" method="post">';

    echo '<input type="text" name="treasury" value="'.$result->Fields('teamtreasury').'" /><br />'
    .'<input type="submit" value="Update Treasury" />'
    .'</form>';
    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_user_addreroll($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT t.coachid, r.reroll_cost FROM nuke_stars_team t, nuke_stars_race r WHERE t.teamrace=r.raceid AND t.teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $numGames = $dbconn->getOne("SELECT count(1) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  $cost = $result->Fields('reroll_cost');
  if ($numGames > 0)
  $cost *= 2;

  $dbconn->Execute("UPDATE nuke_stars_team SET teamrerolls=teamrerolls+1, teamtreasury=teamtreasury-".pnVarPrepForStore($cost)." WHERE teamid=".pnVarPrepForStore($t)." AND teamtreasury>=".pnVarPrepForStore($cost));
  goBack($t);
  return true;
}

function Stars_user_delreroll($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT t.coachid, r.reroll_cost FROM nuke_stars_team t, nuke_stars_race r WHERE t.teamrace=r.raceid AND t.teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $numGames = $dbconn->getOne("SELECT count(1) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  $cost = $result->Fields('reroll_cost');
  if ($numGames > 0)
  $cost = 0;

  $dbconn->Execute("UPDATE nuke_stars_team SET teamrerolls=teamrerolls-1, teamtreasury=teamtreasury+".pnVarPrepForStore($cost)." WHERE teamid=".pnVarPrepForStore($t)." AND teamrerolls>0");
  goBack($t);
  return true;
}

function Stars_user_addff($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT coachid FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $numGames = $dbconn->getOne("SELECT count(1) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  if ($numGames == 0)
  $dbconn->Execute("UPDATE nuke_stars_team SET teamfanfactor=teamfanfactor+1, teamtreasury=teamtreasury-10000 WHERE teamid=".pnVarPrepForStore($t)." AND teamtreasury>=10000");

  goBack($t);
  return true;
}

function Stars_user_delff($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT coachid FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $numGames = $dbconn->getOne("SELECT count(1) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  if ($numGames == 0)
  $dbconn->Execute("UPDATE nuke_stars_team SET teamfanfactor=teamfanfactor-1, teamtreasury=teamtreasury+10000 WHERE teamid=".pnVarPrepForStore($t)." AND ((rules_version <= 4 && teamfanfactor>1) OR (rules_version > 4 && teamfanfactor>0))");

  goBack($t);
  return true;
}

function Stars_user_addcoach($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT coachid FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $dbconn->Execute("UPDATE nuke_stars_team SET teamcoaches=teamcoaches+1, teamtreasury=teamtreasury-10000 WHERE teamid=".pnVarPrepForStore($t)." AND teamtreasury>=10000");

  goBack($t);
  return true;
}

function Stars_user_delcoach($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT coachid FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $numGames = $dbconn->getOne("SELECT count(1) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  if ($numGames > 0)
  $cost=0;
  else
  $cost=10000;

  $dbconn->Execute("UPDATE nuke_stars_team SET teamcoaches=teamcoaches-1, teamtreasury=teamtreasury+".pnVarPrepForStore($cost)." WHERE teamid=".pnVarPrepForStore($t)." AND teamcoaches>0");

  goBack($t);
  return true;
}

function Stars_user_addcheer($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT coachid FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $dbconn->Execute("UPDATE nuke_stars_team SET teamcheerleaders=teamcheerleaders+1, teamtreasury=teamtreasury-10000 WHERE teamid=".pnVarPrepForStore($t)." AND teamtreasury>=10000");

  goBack($t);
  return true;
}

function Stars_user_delcheer($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT coachid FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $numGames = $dbconn->getOne("SELECT count(1) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  if ($numGames > 0)
  $cost=0;
  else
  $cost=10000;

  $dbconn->Execute("UPDATE nuke_stars_team SET teamcheerleaders=teamcheerleaders-1, teamtreasury=teamtreasury+".pnVarPrepForStore($cost)." WHERE teamid=".pnVarPrepForStore($t)." AND teamcheerleaders>0");

  goBack($t);
  return true;
}

function Stars_user_addapoth($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT t.*, r.name as race, r.reroll_cost, r.apoth FROM nuke_stars_team t, nuke_stars_race r WHERE t.teamrace=r.raceid "
  ."AND teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  if ($result->Fields('apoth') == 'n')
  goBack($t, "This team can not hire an apothecary");

  $dbconn->Execute("UPDATE nuke_stars_team SET teamapoth=1, teamtreasury=teamtreasury-50000 WHERE teamid=".pnVarPrepForStore($t)." AND teamtreasury>=50000 AND teamapoth=0");
  goBack($t);
  return true;
}

function Stars_user_delapoth($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $t+=0;
  $result = $dbconn->Execute("SELECT t.*, r.name as race, r.reroll_cost FROM nuke_stars_team t, nuke_stars_race r "
  ."WHERE t.teamrace=r.raceid AND teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $numGames = $dbconn->getOne("SELECT count(1) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  if ($numGames > 0)
  $cost=0;
  else
  $cost=50000;
  $dbconn->Execute("UPDATE nuke_stars_team SET teamapoth=teamapoth-1, teamtreasury=teamtreasury+".pnVarPrepForStore($cost)." WHERE teamid=".pnVarPrepForStore($t)." AND teamapoth>0");

  goBack($t);
  return true;
}

function Stars_user_delete($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  list($t, $confirm) = pnVarCleanFromInput('t', 'confirm');
  $t+=0; $confirm+=0;
  $result = $dbconn->Execute("SELECT t.*, r.name as race, r.reroll_cost FROM nuke_stars_team t, nuke_stars_race r "
  ."WHERE t.teamrace=r.raceid AND teamid=".pnVarPrepForStore($t));
  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  if ($confirm==1) {
    $dbconn->Execute("DELETE FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
    $dbconn->Execute("DELETE FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));
    $players = $dbconn->Execute("SELECT playerid FROM nuke_stars_player WHERE teamid=".pnVarPrepForStore($t));
    $p = array();
    for ( ; !$players->EOF; $players->moveNext() ) {
      $p[] = pnVarPrepForStore($players->fields[0]);
    }

    if (count($p) > 0)
    $dbconn->Execute("DELETE FROM nuke_stars_playerskill WHERE playerid IN (".implode(',', $p).")");

    $dbconn->Execute("DELETE FROM nuke_stars_player WHERE teamid=".pnVarPrepForStore($t));
    pnRedirect(pnModURL('Stars'));
  }
  else {
    include 'header.php';
    OpenTable();

    echo '<div style="font-size: 2em;">Are you sure you want to <span style="color: red;">Delete</span> \''.pnVarPrepForDisplay($result->Fields('teamname')).'\'?</div>'
    .'<br />'
    .'Deleting the team will also delete all game records, players and notes that have been written. This is an '
    .'irreversible action.<br />'
    .'<br />'
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'view', array('t' => $t))).'">No, do not delete this team</a><br /><br />'
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delete', array('t' => $t, 'confirm' => '1'))).'">Yes, delete this team</a>';

    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_user_create($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);

  list($name, $race, $version) = pnVarCleanFromInput('teamname', 'race', 'rules_version');
  $name = trim($name);
  if (strlen($race)==0 || strlen($name)==0) {
    include 'header.php';
    OpenTable();
    echo '<div class="title">Create Team</div>';
    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'create')).'" method="post">';
    $races = $dbconn->Execute("SELECT distinct name FROM nuke_stars_race order by name");
    echo '<br />Race<br /><select name="race">';
    for ( ; !$races->EOF; $races->moveNext() ) {
      echo '<option value="'.pnVarPrepForDisplay($races->Fields('name')).'">'.pnVarPrepForDisplay($races->Fields('name')).'</option>';
    }
    echo '</select><br />';
    $versions = $dbconn->Execute("SELECT historic_version, title FROM nuke_stars_rules_versions WHERE historic_version >= 4.0 order by historic_version");
    echo '<br />Rules Version<br /><select name="rules_version">';
    for ( ; !$versions->EOF; $versions->moveNext() ) {
      echo '<option value="'.pnVarPrepForDisplay($versions->Fields('historic_version')).'"'.($versions->Fields('historic_version')==5?' selected="selected"':'').'>'.pnVarPrepForDisplay($versions->Fields('title')).'</option>';
    }
    echo '</select><br />';
    echo '<br />Name<br /><input type="text" name="teamname" /><br />';
    echo '<br /><input type="submit" value="Create Team" />';
    echo '</form>';
    CloseTable();
    include 'footer.php';
  }
  else {
    $name = trim($name);
    $existing = $dbconn->Execute("SELECT * FROM nuke_stars_team WHERE coachid=".pnVarPrepForStore(pnUserGetVar('uid'))." AND teamname='".pnVarPrepForStore($name)."'");
    if (!$existing->EOF) {
      include 'header.php';
      OpenTable();
      echo 'You can not have two teams with the same name.<br /><a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'create')).'">Back</a>';
      CloseTable();
      include 'footer.php';
      exit;
    }
    $sql = "INSERT INTO nuke_stars_team (coachid, teamname, teamrace, teamtreasury, teamfanfactor, rules_version, teamrating) "
    ."SELECT ".pnVarPrepForStore(pnUserGetVar('uid')).", '".pnVarPrepForStore($name)."', raceid, ".
    ($version <= 4.0 ? "990000" : "1000000").", ".
    ($version <= 4.0 ? "1" : "0").", ".pnVarPrepForStore($version).", ".($version <= 4.0 ? "99" : "0")." FROM nuke_stars_race WHERE name = '".pnVarPrepForStore($race)."' AND ".pnVarPrepForStore($version)." BETWEEN from_rules_version AND to_rules_version";
    $dbconn->Execute($sql);
    pnRedirect(pnModURL('Stars'));
  }
  return true;
}

function Stars_user_delreport($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  list($t, $id) = pnVarCleanFromInput('t', 'id');
  $t += 0;
  $id += 0;

  $result = $dbconn->Execute("SELECT t.*, r.name as race FROM nuke_stars_team t , nuke_stars_race r WHERE t.teamrace=r.raceid AND teamid=".pnVarPrepForStore($t));

  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $oldgame = $dbconn->Execute("SELECT * FROM nuke_stars_teamgame WHERE gameid=".pnVarPrepForStore($id)." AND teamid=".pnVarPrepForStore($t));
  if (!$oldgame || $oldgame->EOF)
  goBack($t, "No such game");

  $dbconn->Execute("UPDATE nuke_stars_team SET teamtreasury=teamtreasury-".pnVarPrepForStore($oldgame->Fields('winnings')).", teamfanfactor=teamfanfactor-".pnVarPrepForStore($oldgame->Fields('fanfactor'))." WHERE teamid=".pnVarPrepForStore($t));

  $deaths = $dbconn->Execute("SELECT playerid FROM nuke_stars_playergame WHERE gameid=".pnVarPrepForStore($id)." AND injury=7");

  $arr =array();
  for ( ; !$deaths->EOF; $deaths->moveNext() ) {
    $arr[] = pnVarPrepForStore($deaths->fields[0]);
  }
  if (count($arr) > 0) {
    $dbconn->Execute("UPDATE nuke_stars_player SET playerstatus='ACTIVE' WHERE playerid IN (".implode(",", $arr).")");
  }

  $dbconn->Execute("DELETE FROM nuke_stars_playergame WHERE gameid=".pnVarPrepForStore($id)." AND injury<>7");

  $dbconn->Execute("DELETE FROM nuke_stars_teamgame WHERE gameid=".pnVarPrepForStore($id));

  fixSpp($id);

  goBack($t);
  return true;
}

function Stars_user_report($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  list($t, $submit, $id) = pnVarCleanFromInput('t', 'submit', 'id');
  $t += 0;
  $id += 0;

  $sql = "SELECT t.*, r.name as race FROM nuke_stars_team t , nuke_stars_race r WHERE t.teamrace=r.raceid AND teamid=".pnVarPrepForStore($t);
  $result = $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() <> 0) {
    echo $sql;
    echo $dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />';
    error_log ($dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />');
    exit;
  }

  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t, "Not your team");

  $sql = "SELECT * FROM nuke_stars_player WHERE teamid=".pnVarPrepForStore($t)." AND playerstatus='ACTIVE' ORDER BY playernumber";
  $players = $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() <> 0) {
    echo $sql;
    echo $dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />';
    error_log ($dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />');
    exit;
  }
  $numPlayers = $players->numRows();

  if ($submit == 1) {
    list($opponentteam, $opponentrace, $teamrating, $opponentrating, $teamtd, $opponenttd, $teamcas, $opponentcas,
    $fanfactor, $winnings, $gate, $notes, $pid, $inj, $cp, $td, $int, $cas, $mvp, $id, $decayinj) = pnVarCleanFromInput('opponentname',
    'opponentrace', 'teamrating', 'opponentrating', 'teamtd', 'opponenttd', 'teamcas',
    'opponentcas', 'fanfactor', 'winnings', 'gate', 'notes', 'pid', 'inj', 'cp', 'td', 'int', 'cas',
    'mvp', 'id', 'decay');

    if ($id > 0) {
      $oldgame = $dbconn->Execute("SELECT * FROM nuke_stars_teamgame WHERE gameid=".pnVarPrepForStore($id));

      $gold = $winnings - $oldgame->Fields('winnings');
      $ff = $fanfactor - $oldgame->Fields('fanfactor');

      $dbconn->Execute("UPDATE nuke_stars_team SET teamtreasury=teamtreasury+".pnVarPrepForStore($gold).", teamfanfactor=teamfanfactor+".pnVarPrepForStore($ff)." WHERE teamid=".pnVarPrepForStore($t));

      $dbconn->Execute("UPDATE nuke_stars_teamgame SET opponentname='".pnVarPrepForStore($opponentteam)
      ."', opponentrace=".pnVarPrepForStore($opponentrace+0)
      .", teamtr=".pnVarPrepForStore($teamrating+0)
      .", opponenttr=".pnVarPrepForStore($opponentrating+0)
      .", teamtd=".pnVarPrepForStore($teamtd+0)
      .", opponenttd=".pnVarPrepForStore($opponenttd+0)
      .", teamcas=".pnVarPrepForStore($teamcas+0)
      .", opponentcas=".pnVarPrepForStore($opponentcas+0)
      .", gate=".pnVarPrepForStore($gate+0)
      .", winnings=".pnVarPrepForStore($winnings+0)
      .", fanfactor=".pnVarPrepForStore($fanfactor+0)
      .", notes='".pnVarPrepForStore($notes)."' "
      ."WHERE gameid=".pnVarPrepForStore($id));
      $dbconn->Execute("DELETE FROM nuke_stars_playergame WHERE gameid=".pnVarPrepForStore($id)." AND injury<>7");
      $update=true;
    }
    else {
      $sql = "INSERT INTO nuke_stars_teamgame (teamid, opponentname, opponentrace, teamtr, opponenttr, teamtd, opponenttd, "
      ."teamcas, opponentcas, gate, winnings, fanfactor, notes) "
      ."VALUES (".pnVarPrepForStore($t).", '".pnVarPrepForStore($opponentteam)."', ".pnVarPrepForStore($opponentrace).", ".pnVarPrepForStore($teamrating).", ".pnVarPrepForStore($opponentrating+0).", ".pnVarPrepForStore($teamtd+0).", ".pnVarPrepForStore($opponenttd+0).", "
      .pnVarPrepForStore($teamcas+0).", ".pnVarPrepForStore($opponentcas+0).", ".pnVarPrepForStore($gate+0).", ".pnVarPrepForStore($winnings+0).", ".pnVarPrepForStore($fanfactor+0).", '".pnVarPrepForStore($notes)."')";
      $res = $dbconn->Execute($sql);
      if ($dbconn->ErrorNo() <> 0) {
        echo $sql;
        echo $dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />';
        error_log ($dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />');
        exit;
      }
      $id = $dbconn->insert_id();

      $sql = "UPDATE nuke_stars_team SET teamtreasury=teamtreasury+".pnVarPrepForStore($winnings+0).", teamfanfactor=teamfanfactor+".pnVarPrepForStore($fanfactor+0)." "
      ."WHERE teamid=".pnVarPrepForStore($t);
      $dbconn->Execute($sql);

      $update=false;
    }

    $prepare_sql = "INSERT INTO nuke_stars_playergame (playerid, gameid, injury, completions, interceptions, touchdowns, casualties, mvps, decay_injury) VALUES ";
    $list = array();

    for ($i=0; $i<16; $i++) {

      $injury = $inj[$i]+0;
      $completions = $cp[$i]+0;
      $interceptions = $int[$i]+0;
      $touchdowns = $td[$i]+0;
      $casualties = $cas[$i]+0;
      $mvps = $mvp[$i]+0;
      $decayinjury = $decayinj[$i]+0;
      $spps = $completions + 2*$interceptions + 3*$touchdowns + 2*$casualties + 5*$mvps;

      if ($injury+$completions+$interceptions+$touchdowns+$casualties+$mvps+$decayinjury > 0) {
        if (!$update) {
          $sql = "UPDATE nuke_stars_player SET playercp=playercp+".pnVarPrepForStore($completions)
          .", playerint=playerint+".pnVarPrepForStore($interceptions)
          .", playertd=playertd+".pnVarPrepForStore($touchdowns)
          .", playercas=playercas+".pnVarPrepForStore($casualties)
          .", playermvp=playermvp+".pnVarPrepForStore($mvps)
          .", playerspp=playerspp+".pnVarPrepForStore($spps)
          ." WHERE playerid=".pnVarPrepForStore($pid[$i]);
          $dbconn->Execute($sql);
          if ($dbconn->ErrorNo() <> 0) {
            echo $sql;
            echo $dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />';
            error_log ($dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />');
            exit;
          }
        }
        $list[] = "(".pnVarPrepForStore($pid[$i]).", "
        .pnVarPrepForStore($id).", "
        .pnVarPrepForStore($injury).", "
        .pnVarPrepForStore($completions).", "
        .pnVarPrepForStore($interceptions).", "
        .pnVarPrepForStore($touchdowns).", "
        .pnVarPrepForStore($casualties).", "
        .pnVarPrepForStore($mvps).", "
        .pnVarPrepForStore($decayinjury).")";
      }
    }
    if (count($list) > 0) {
      $dbconn->Execute($prepare_sql.implode(", ", $list));
    }

    if ($update) {
      $perf = $dbconn->Execute("SELECT p.playerid, sum(completions) as cp, sum(interceptions) as inter, sum(touchdowns) as td, "
      ."sum(casualties) as cas, sum(mvps) as mvp FROM nuke_stars_player p, "
      ."nuke_stars_playergame pg WHERE p.playerid=pg.playerid AND p.teamid=".pnVarPrepForStore($t)." GROUP BY p.playerid");
      for ( ; !$perf->EOF; $perf->moveNext() ) {
        $cp = $perf->Fields('cp')+0;
        $int = $perf->Fields('inter')+0;
        $td = $perf->Fields('td')+0;
        $cas = $perf->Fields('cas')+0;
        $mvp = $perf->Fields('mvp')+0;
        $spp = $cp+2*$int+3*$td+2*$cas+5*$mvp;
        $dbconn->Execute("UPDATE nuke_stars_player SET playercp=".pnVarPrepForStore($cp).", playertd=".pnVarPrepForStore($td).", playercas=".pnVarPrepForStore($cas).", playerint=".pnVarPrepForStore($int).", "
        ."playermvp=".pnVarPrepForStore($mvp).", playerspp=".pnVarPrepForStore($spp)." WHERE playerid=".pnVarPrepForStore($perf->Fields('playerid')));
      }
    }

    pnRedirect(pnModURL('Stars', '', 'view', array('t' => $t)));
    exit;
  }
  else {
    if ($id > 0) {
      $match = $dbconn->Execute("SELECT * FROM nuke_stars_teamgame WHERE gameid=".pnVarPrepForStore($id));
      if ($match->Fields('teamid') != $t)
      goBack($t, "Not your game");

      $opponentteam = $match->Fields('opponentname');
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
      $notes = $match->Fields('notes');

      $performances = $dbconn->Execute("SELECT * FROM nuke_stars_playergame WHERE gameid=".pnVarPrepForStore($id));
      $pid=array();
      $inj=array();
      $cp=array();
      $td=array();
      $int=array();
      $cas=array();
      $mvp=array();
      $decayinj=array();
      for ( ; !$performances->EOF; $performances->moveNext() ) {
        $pid[] = $performances->Fields('playerid');
        $inj[] = $performances->Fields('injury');
        $cp[] = $performances->Fields('completions');
        $td[] = $performances->Fields('touchdowns');
        $int[] = $performances->Fields('interceptions');
        $cas[] = $performances->Fields('casualties');
        $mvp[] = $performances->Fields('mvps');
        $decayinj[] = $performances->Fields('decay_injury');
      }
    }

    include 'header.php';
    OpenTable();

    echo '<div style="font-size: 200%;">Report Match</div>';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'view', array('t' => $t))).'">Back to team</a><br />';

    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'report', array('t' => $t))).'" method="post">';
    echo '<input type="hidden" name="submit" value="1" />';

    if ($id > 0)
    echo '<input type="hidden" name="id" value="'.$id.'" />';

    echo '<div style="float: left;">';

    echo '<table border="0" cellspacing="5">';
    echo '<tr ><th>&nbsp;</th><th>Your team</th><th>Opponent\'s team</th></tr>';
    echo '<tr valign="top"><th align="left">Team</th><td>';
    echo pnVarPrepForDisplay($result->Fields('teamname')).'<br />';
    echo '</td><td>';
    echo '<input type="text" name="opponentname" value="'.pnVarPrepForDisplay($opponentteam).'"/></td></tr>';

    $races = $dbconn->Execute("SELECT raceid, name FROM nuke_stars_race order by name");
    echo '<tr><th align="left">Race</th><td>'.$result->Fields('race').'</td><td>';
    echo '<select name="opponentrace">';
    for ( ; !$races->EOF; $races->moveNext() ) {
      echo '<option value="'.$races->Fields('raceid').'"'.($races->Fields('raceid')==$opponentrace?' selected="selected"':'').'>'.$races->Fields('name').'</option>';
    }
    echo '</select></td></tr>';

    echo '<tr valign="top"><th align="left">Team Rating</th><td>';
    echo '<input type="text" size="4" name="teamrating" value="'.($id>0?$teamrating:$result->Fields('teamrating')).'" />';
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

    echo '<tr valign="top"><th colspan="3" align="left">Notes<br /><textarea name="notes" rows="5" cols="40" style="width: 100%;">'.pnVarPrepForDisplay($notes).'</textarea></th></tr>';


    echo '<tr><td colspan="3" align="center">';
    echo '</td></tr></table>';

    echo '</div>';
    $sql = "SELECT * FROM nuke_stars_positionskills ps, nuke_stars_player WHERE skillid = 80 AND positionid = playertypeid and teamid = ".pnVarPrepForStore($t);
    $decay_team = $dbconn->Execute($sql);



    echo '<table border="0" cellspacing="1" bgcolor="#000000">';
    echo '<tr bgcolor="#ffffff"><th colspan="'.($decay_team->EOF ? '8' : '9').'">Performance</th></tr>';
    echo '<tr bgcolor="#ffffff"><th>#</th><th>Name</th><th>Inj</th>';
    if (!$decay_team->EOF) echo '<th>Decay Inj</th>';
    echo '<th>Cp</th><th>Td</th><th>Int</th><th>Cas</th><th>MVP</th></tr>';

    for ($c=0; !$players->EOF; $c++, $players->moveNext() ) {
      $injury = $comp = $touch = $inter = $casual = $mvps = $decayinjury = '';
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
          $decayinjury = f2($decayinj[$pos]);
        }
      }
      echo '<tr bgcolor="#ffffff">'
      .'<td align="right">'.$players->Fields('playernumber').'</td>'
      .'<td>'.pnVarPrepForDisplay($players->Fields('playername')).'</td>'
      .'<td><select name="inj['.$c.']">'
      .'<option value="0"'.($injury==0?' selected="selected"':'').'>&nbsp;</option>'
      .'<option value="1"'.($injury==1?' selected="selected"':'').'>Miss</option>'
      .'<option value="2"'.($injury==2?' selected="selected"':'').'>Nig</option>'
      .'<option value="3"'.($injury==3?' selected="selected"':'').'>-MA</option>'
      .'<option value="4"'.($injury==4?' selected="selected"':'').'>-ST</option>'
      .'<option value="5"'.($injury==5?' selected="selected"':'').'>-AG</option>'
      .'<option value="6"'.($injury==6?' selected="selected"':'').'>-AV</option>'
      .'<option value="7"'.($injury==7?' selected="selected"':'').'>Dead</option>'
      .'</select></td>';
      $sql = "SELECT * FROM nuke_stars_positionskills WHERE skillid = 80 AND positionid = ".pnVarPrepForStore($players->Fields('playertypeid'));
      $decay = $dbconn->Execute($sql);
      if(!$decay->EOF) {
        echo '<td><select name="decay['.$c.']">'
        .'<option value="0"'.($decayinjury==0?' selected="selected"':'').'>&nbsp;</option>'
        .'<option value="1"'.($decayinjury==1?' selected="selected"':'').'>Miss</option>'
        .'<option value="2"'.($decayinjury==2?' selected="selected"':'').'>Nig</option>'
        .'<option value="3"'.($decayinjury==3?' selected="selected"':'').'>-MA</option>'
        .'<option value="4"'.($decayinjury==4?' selected="selected"':'').'>-ST</option>'
        .'<option value="5"'.($decayinjury==5?' selected="selected"':'').'>-AG</option>'
        .'<option value="6"'.($decayinjury==6?' selected="selected"':'').'>-AV</option>'
        .'<option value="7"'.($decayinjury==7?' selected="selected"':'').'>Dead</option>'
        .'</select></td>';
      }
      else if (!$decay_team->EOF) echo '<td>&nbsp;</td>';
      echo '<td><input type="text" size="3" name="cp['.$c.']" value="'.$comp.'" /></td>'
      .'<td><input type="text" size="3" name="td['.$c.']" value="'.$touch.'" /></td>'
      .'<td><input type="text" size="3" name="int['.$c.']" value="'.$inter.'" /></td>'
      .'<td><input type="text" size="3" name="cas['.$c.']" value="'.$casual.'" /></td>'
      .'<td><input type="text" size="3" name="mvp['.$c.']" value="'.$mvps.'" />'
      .'<input type="hidden" name="pid['.$c.']" value="'.$players->Fields('playerid').'" /></td>'
      .'</tr>';
    }
    echo '</table>';

    echo '<div style="clear: both;"><input type="submit" value="Submit Game" /></div>';
    echo '</form>';

    if ($id > 0) {
      echo '<br /><br />';
      echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delreport', array('t' => $t, 'id' => $id))).'">Delete this report</a> (Make sure you mean it as there is no confirmation screen.)';
    }

    CloseTable();
    include 'footer.php';
  }

  return true;
}

function Stars_user_buyplayer($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  list($number, $name, $pos, $t) = pnVarCleanFromInput('number', 'playername', 'position', 't');

  $t += 0;
  $number += 0;
  $position += 0;

  $result = $dbconn->Execute("SELECT * FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));

  if ($result->fields('coachid') != pnUserGetVar('uid'))
  goBack($t);

  $sql = "SELECT pla.playernumber, pla.playertypeid, pos.big_guy FROM nuke_stars_player pla, nuke_stars_position pos WHERE pla.teamid=".pnVarPrepForStore($t)." AND pla.playerstatus='ACTIVE' AND pla.playertypeid = pos.playertypeid";
  $players = $dbconn->Execute($sql);
  $numPlayers = $players->numRows();

  $numHash = array();
  $posHash = array();
  $hasBigGuy = 0;
  for ( ; !$players->EOF; $players->moveNext()) {
    $numHash[$players->Fields('playernumber')] = true;
    $posHash[$players->Fields('playertypeid')]++;
    if ($players->Fields('big_guy') == 'y')
    $hasBigGuy++;
  }

  if ($number > 0 && $number < 17) {
    if ($pos+0 == 0)
    goBack($t);
    $test = $dbconn->Execute("SELECT 1 FROM nuke_stars_player WHERE playernumber=".pnVarPrepForStore($number)." AND teamid=".pnVarPrepForStore($t)." AND playerstatus='ACTIVE'");
    if (!$test->EOF)
    goBack($t, "Player Number is taken");

    $position = $dbconn->Execute("SELECT * FROM nuke_stars_position WHERE playertypeid=".pnVarPrepForStore($pos));

    if ($result->Fields('teamrace') != $position->Fields('raceid'))
    goBack($t, "Bogus position");

    if ($result->Fields('teamtreasury') < $position->Fields('cost'))
    goBack($t, "You can't afford it!");

    if ($position->Fields('qty') <= $posHash[$pos])
    goBack($t, "You can't have more of those");

    if ($position->Fields('big_guy') == 'y' && (($position->Fields('qty')==1 && $hasBigGuy > 0) || ($position->Fields('qty')==2 && $hasBigGuy > 1)))
    goBack($t, "You can't have more big guys on this team.");

    $ma = $position->Fields('ma');
    $st = $position->Fields('st');
    $ag = $position->Fields('ag');
    $av = $position->Fields('av');
    $res = $dbconn->Execute("INSERT INTO nuke_stars_player (teamid, playernumber, playername, playertypeid, playerma, playerst, "
    ." playerag, playerav) VALUES (".pnVarPrepForStore($t).", ".pnVarPrepForStore($number).", '".pnVarPrepForStore($name)."', ".pnVarPrepForStore($pos).", ".pnVarPrepForStore($ma).", ".pnVarPrepForStore($st).", ".pnVarPrepForStore($ag).", ".pnVarPrepForStore($av).")");
    if ($res)
    $dbconn->Execute("UPDATE nuke_stars_team SET teamtreasury=teamtreasury-".pnVarPrepForStore($position->Fields('cost'))." WHERE teamid=".pnVarPrepForStore($t));

    pnRedirect(pnModURL('Stars', '', 'buyplayer', array('t' => $t)));
  }
  else {
    include 'header.php';
    OpenTable();
    $treasury = $result->Fields('teamtreasury');

    echo '<div class="title">Buy Player for '.pnVarPrepForDisplay($result->Fields('teamname')).'</div>';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'view', array('t' => $t))).'">Back to team</a><br />';
    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'buyplayer', array('t' => $t))).'" method="post">';

    echo '<br />Treasury: '.pnVarPrepForDisplay($treasury).'<br />';
    echo 'Number<br /><select name="number">';
    for ($i=1; $i<=16; $i++) {
      if (!$numHash[$i])
      echo '<option>'.$i.'</option>';
    }
    echo '</select>';

    $sql = "SELECT * FROM nuke_stars_position WHERE raceid=".pnVarPrepForStore($result->Fields('teamrace'))." AND ".pnVarPrepForStore($result->Fields('rules_version'))." BETWEEN from_rules_version AND to_rules_version ORDER BY cost";
    $positions = $dbconn->Execute($sql);
    echo '<br />Position<br /><select name="position">';
    for ( ; !$positions->EOF; $positions->moveNext() ) {
      if ($positions->Fields('big_guy') == 'y' && (($positions->Fields('qty')==1 && $hasBigGuy > 0) || ($positions->Fields('qty')==2 && $hasBigGuy > 1)))
      continue;

      if ($posHash[$positions->Fields('playertypeid')]+0 < $positions->Fields('qty') &&
      $result->Fields('teamtreasury') >= $positions->Fields('cost'))
      echo '<option value="'.$positions->Fields('playertypeid').'">'
      .($posHash[$positions->Fields('playertypeid')]+0).'/'.$positions->Fields('qty')." "
      .$positions->Fields('position')
      .' ('.($positions->Fields('cost')/1000).'k)</option>';
    }
    echo '</select><br />';
    echo 'Name<br /><input type="text" name="playername" /><br />';
    echo '<input type="submit" value="Buy Player" />';
    echo '</form>';
    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_user_player($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  list($p, $skill, $delskill, $revive, $renumber, $edit, $rename, $age, $delage, $retire, $t) =
  pnVarCleanFromInput('p', 'skill', 'delskill', 'revive', 'renumber', 'edit', 'rename', 'age', 'delage', 'retire', 't');
  $p += 0;
  $skill += 0;
  $delskill += 0;
  $renumber += 0;
  $edit += 0;
  $age += 0;
  $delage += 0;
  $sql = "SELECT p.*, pos.position, pos.cost, t.coachid, t.rules_version, pos.general, pos.agility, pos.strength, pos.passing, pos.mutation, pos.st FROM nuke_stars_player p, nuke_stars_position pos, nuke_stars_team t WHERE p.playertypeid = pos.playertypeid AND p.teamid=t.teamid AND t.rules_version BETWEEN pos.from_rules_version AND pos.to_rules_version AND playerid=".pnVarPrepForStore($p);
  $player = $dbconn->Execute($sql);
  if ($player->Fields('coachid') != pnUserGetVar('uid')) {
    goBack($t, "Not your team.");
  }
  $t = $player->Fields('teamid');
  $rules_version = $player->Fields('rules_version');

  $maxgame = $dbconn->getOne("SELECT max(gameid) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  if ($age > 0 && $age < 6 && $rules_version > 0 && $rules_version < 5) {
    $dbconn->Execute("INSERT INTO nuke_stars_playerage (playerid, ageid) VALUES (".pnVarPrepForStore($p).", ".pnVarPrepForStore($age).")");
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  if ($delage > 0) {
    $dbconn->Execute("DELETE FROM nuke_stars_playerage WHERE playerageid=".pnVarPrepForStore($delage)." AND playerid=".pnVarPrepForStore($p));
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  if ($retire > 0) {
    if ($maxgame > 0) {
      $dbconn->Execute("UPDATE nuke_stars_player SET playerstatus='RETIRED' WHERE playerid=".pnVarPrepForStore($p));
    }
    else if (!$player->EOF) {
      $dbconn->Execute("DELETE FROM nuke_stars_player WHERE playerid=".pnVarPrepForStore($p));
      $dbconn->Execute("UPDATE nuke_stars_team SET teamtreasury=teamtreasury+".pnVarPrepForStore($player->Fields('cost'))." WHERE teamid=".pnVarPrepForStore($t));
    }
    pnRedirect(pnModURL('Stars', '', 'view', array('t' => $t)));
    exit;
  }

  if ($unretire > 0) {
    $dbconn->Execute("UPDATE nuke_stars_player SET playerstatus='ACTIVE' WHERE playerid=".pnVarPrepForStore($p));
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  if ($skill > 0) {
    $dbconn->Execute("INSERT INTO nuke_stars_playerskills (playerid, skillid) VALUES (".pnVarPrepForStore($p).", ".pnVarPrepForStore($skill).")");
    if ($rules_version >=5 ) {
      $sql = "SELECT skillgroupid FROM nuke_stars_skills WHERE skillid = ".pnVarPrepForStore($skill);
      $skillgroup = $dbconn->getOne($sql);
      switch($skillgroup) {
        case 0:
        switch($skill) {
          case 1:
          case 4:
          $value = 30000;
          break;
          case 2:
          $value = 50000;
          break;
          case 3:
          $value = 40000;
          break;
          default:
          break;
        }
        break;
        case 1:
        if($player->Fields('general') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 2:
        if($player->Fields('passing') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 3:
        if($player->Fields('strength') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 4:
        if($player->Fields('agility') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 5:
        if($player->Fields('mutation') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        default:
        break;
      }
      $dbconn->Execute("UPDATE nuke_stars_player SET additional_value=additional_value+".pnVarPrepForStore($value)." WHERE playerid=".pnVarPrepForStore($p));
    }
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  if ($delskill > 0) {
    $dbconn->Execute("DELETE FROM nuke_stars_playerskills WHERE playerid=".pnVarPrepForStore($p)." AND skillid=".pnVarPrepForStore($delskill)." LIMIT 1");
    if ($rules_version >=5 ) {
      $sql = "SELECT skillgroupid FROM nuke_stars_skills WHERE skillid = ".pnVarPrepForStore($skill);
      $skillgroup = $dbconn->getOne($sql);
      switch($skillgroup) {
        case 0:
        switch($skill) {
          case 1:
          case 4:
          $value = 30000;
          break;
          case 2:
          $value = 50000;
          break;
          case 3:
          $value = 40000;
          break;
          default:
          break;
        }
        break;
        case 1:
        if($player->Fields('general') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 2:
        if($player->Fields('passing') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 3:
        if($player->Fields('strength') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 4:
        if($player->Fields('agility') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        case 5:
        if($player->Fields('mutation') == 'NORMAL') $value = 20000;
        else $value = 30000;
        break;
        default:
        break;
      }
      $dbconn->Execute("UPDATE nuke_stars_player SET additional_value=additional_value-".pnVarPrepForStore($value)." WHERE playerid=".pnVarPrepForStore($p));
    }
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  if ($revive > 0) {
    $dbconn->Execute("UPDATE nuke_stars_playergame SET injury=0 WHERE playerid=".pnVarPrepForStore($p)." AND injury=7");
    $dbconn->Execute("UPDATE nuke_stars_player SET playerstatus='ACTIVE' WHERE playerid=".pnVarPrepForStore($p));
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  if ($renumber > 0 && $renumber<17) {
    $dbconn->Execute("UPDATE nuke_stars_player SET playernumber=$renumber WHERE playerid=".pnVarPrepForStore($p));
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  if (strlen($rename) > 0) {
    $dbconn->Execute("UPDATE nuke_stars_player SET playername='".pnVarPrepForStore(trim($rename))."' WHERE playerid=".pnVarPrepForStore($p));
    pnRedirect(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p)));
    exit;
  }

  $pos = $player->Fields('playertypeid');

  include 'header.php';
  OpenTable();

  $statMod = array(0, 0, 0, 0, 0);

  $posSkills = $dbconn->Execute("SELECT name FROM nuke_stars_positionskills p, nuke_stars_skills s WHERE p.skillid=s.skillid AND positionid=".pnVarPrepForStore($pos)." ORDER BY name");
  $posSkillNames = array();
  for ( ; !$posSkills->EOF; $posSkills->moveNext() ) {
    $posSkillNames[] = $posSkills->Fields('name');
  }

  $sql = "SELECT s.skillid, name FROM nuke_stars_playerskills p, nuke_stars_skills s WHERE p.skillid=s.skillid AND p.playerid=".pnVarPrepForStore($p)." ORDER BY name";
  $skills = $dbconn->Execute($sql);
  $skillNames = array();
  $skillId = array();
  for ( ; !$skills->EOF; $skills->moveNext() ) {
    $s = $skills->Fields('skillid');
    if ($s < 5)
    $statMod[$s]++;

    if ($s == 51 && $rules_version <= 4) // Very Long Legs
    $statMod[1]++;

    if ($s == 48 && $rules_version <= 4) // Spikes
    $statMod[4]++;

    $skillNames[] = $skills->Fields('name');
    $skillId[$skills->Fields('name')] = $s;
  }

  $skills = array_merge($posSkillNames, $skillNames);

  $sql = "SELECT injury, pg.gameid FROM nuke_stars_playergame pg "
  ."WHERE pg.playerid=".pnVarPrepForStore($p)." AND injury>0 "
  ."UNION ALL "
  ."SELECT decay_injury, pg.gameid FROM nuke_stars_playergame pg "
  ."WHERE pg.playerid=".pnVarPrepForStore($p)." AND decay_injury>0 "
  ."ORDER BY injury";
  $inj = $dbconn->Execute($sql);
  /*$inj = $dbconn->Execute("SELECT injury, pg.gameid FROM nuke_stars_playergame pg "
  ."WHERE pg.playerid=".pnVarPrepForStore($p)." AND injury>0 ORDER BY injury");*/
  $injArray = array();
  $injNames = array( 'None', 'm', 'n', '-MA', '-ST', '-AG', '-AV', 'd' );
  for ( ; !$inj->EOF; $inj->moveNext() ) {
    $i = $inj->Fields('injury');

    if ($i != 1 || $inj->Fields('gameid') == $maxgame) {
      if ($i!=7)
      $injArray[] = $injNames[$i];
      else
      $injArray[] = $injNames[$i] . ' (<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p, 'revive' => $p))).'">Revive</a>)';
    }

    if ($i>2 && $i<7) {
      $statMod[$i-2]--;
    }

    if ($i > 1 && $i != 7 && $inj->Fields('gameid') == $maxgame && !in_array('m', $injArray)) {
      array_unshift($injArray, 'm');
    }
  }
  if($rules_version > 0 && $rules_version < 5) {
    $age = $dbconn->Execute("SELECT pa.*, a.name FROM nuke_stars_playerage pa, nuke_stars_age a WHERE pa.ageid=a.ageid AND playerid=".pnVarPrepForStore($p)." ORDER BY pa.ageid");

    for ( ; !$age->EOF; $age->moveNext() ) {
      $a = $age->Fields('ageid');
      $injArray[] = $age->Fields('name') . ' (<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p, 'delage' => $age->Fields('playerageid')))).'">Remove</a>)';
      if ($a < 5)
      $statMod[$a]--;
    }
  }

  echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'view', array('t' => $t))).'">Back to team</a><br />';
  echo '<table width="300" border="0" cellspacing="1" bgcolor="#000000" style="margin-top: 3px;">';
  $n = pnVarPrepForDisplay($player->Fields('playernumber'));
  echo '<tr bgcolor="#ffffff"><th colspan="5" style="font-size: 2em;">'
  .'<div style="float: left; font-size: 50%;">'.$n.'</div>'
  .'<div style="float: right; font-size: 50%;">'.$n.'</div>'
  .pnVarPrepForDisplay($player->Fields('playername'))
  .'<br /><span style="font-size: 0.5em;">'.pnVarPrepForDisplay($player->Fields('position')).'</span></th></tr>';

  echo '<tr bgcolor="#ffffff"><th colspan="5">Stats</th></tr>';
  echo '<tr bgcolor="#ffffff"><th>MA</th><th>ST</th><th>AG</th><th>AV</th><th>Cost</th></tr>';
  echo '<tr align="center" bgcolor="#ffffff">'
  .'<td>'.fixStat($player->Fields('playerma'), $statMod[1], $rules_version).'</td>'
  .'<td>'.fixStat($player->Fields('playerst'), $statMod[2], $rules_version).'</td>'
  .'<td>'.fixStat($player->Fields('playerag'), $statMod[3], $rules_version).'</td>'
  .'<td>'.fixStat($player->Fields('playerav'), $statMod[4], $rules_version).'</td>'
  .'<td>'.pnVarPrepForDisplay(($player->Fields('cost')+$player->Fields('additional_value'))/1000).'k</td></tr>';

  echo '<tr bgcolor="#ffffff"><th colspan="5">Performance ('.pnVarPrepForDisplay($player->Fields('playerspp')).' SPP)</th></tr>';
  echo '<tr bgcolor="#ffffff"><th>Cp</th><th>TD</th><th>Int</th><th>Cas</th><th>MVP</th></tr>';
  echo '<tr align="center" bgcolor="#ffffff"><td>'.pnVarPrepForDisplay($player->Fields('playercp')).'</td><td>'.pnVarPrepForDisplay($player->Fields('playertd')).'</td><td>'
  .pnVarPrepForDisplay($player->Fields('playerint')).'</td><td>'.pnVarPrepForDisplay($player->Fields('playercas')).'</td><td>'.pnVarPrepForDisplay($player->Fields('playermvp')).'</td></tr>';

  echo '<tr bgcolor="#ffffff"><th colspan="3">Skills</th><th colspan="2">Injuries</th></tr>';
  echo '<tr valign="top" bgcolor="#ffffff"><td colspan="3">';
  if (count($skills) > 0) {
    foreach ($posSkillNames as $skill) {
      echo "$skill<br />\n";
    }
    foreach ($skillNames as $skill) {
      echo $skill.' (<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p, 'delskill' => $skillId[$skill]))).'">Del</a>)<br />'."\n";
    }
  }
  else
  echo 'None<br />';

  $spp = $player->Fields('playerspp');
  $numSkills = 0;
  $sql = "SELECT * FROM nuke_stars_rules_versions WHERE historic_version = ".pnVarPrepForStore($rules_version);
  $skilllevels = $dbconn->Execute($sql);

  if ($spp >= $skilllevels->Fields('1_skilllevel')) $numSkills++;
  if ($spp >= $skilllevels->Fields('2_skilllevel')) $numSkills++;
  if ($spp >= $skilllevels->Fields('3_skilllevel')) $numSkills++;
  if ($spp >= $skilllevels->Fields('4_skilllevel')) $numSkills++;
  if ($spp >= $skilllevels->Fields('5_skilllevel')) $numSkills++;
  if ($spp >= $skilllevels->Fields('6_skilllevel')) $numSkills++;
  if ($spp >= $skilllevels->Fields('7_skilllevel') && $skilllevels->Fields('7_skilllevel') > 0) $numSkills++;

  if (count($skillNames) < $numSkills) {
    $sql = "SELECT skillid, name FROM nuke_stars_skills WHERE ".pnVarPrepForStore($rules_version)." BETWEEN from_rules_version and to_rules_version"
    ." AND ((SKILLGROUPID = 0)"
    .($player->Fields('general') != 'NEVER' ? " OR (SKILLGROUPID = 1".($player->Fields('general') == 'DOUBLE' ? " AND TYPE='SKILL')": ")") : "")
    .($player->Fields('agility') != 'NEVER' ? " OR (SKILLGROUPID = 4".($player->Fields('agility') == 'DOUBLE' ? " AND TYPE='SKILL')": ")") : "")
    .($player->Fields('strength') != 'NEVER' ? " OR (SKILLGROUPID = 3".($player->Fields('strength') == 'DOUBLE' ? " AND TYPE='SKILL')": ")") : "")
    .($player->Fields('passing') != 'NEVER' ? " OR (SKILLGROUPID = 2".($player->Fields('passing') == 'DOUBLE' ? " AND TYPE='SKILL')": ")") : "")
    .($player->Fields('mutation') != 'NEVER' ? " OR (SKILLGROUPID = 5".($player->Fields('mutation') == 'DOUBLE' ? " AND TYPE='SKILL')": ")") : "")
    .") ORDER BY name";
    $s = $dbconn->Execute($sql);

    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p))).'" method="post">';
    echo '<select name="skill">';
    for ( ; !$s->EOF; $s->moveNext() ) {
      if($rules_version <= 4 && $s->Fields('skillid') == 26 && $player->Fields('st') <= 2) continue; // Mighty Blow
      // TODO not starting strength but real strength when $rules_version < 4, fixStat can't help, it returns html
      $name = $s->Fields('name');
      if (array_search($name, $skills) === false || $s->Fields('skillid') < 5)
      echo '<option value="'.pnVarPrepForDisplay($s->Fields('skillid')).'">'.pnVarPrepForDisplay($name).'</option>'."\n";
    }
    echo '</select>';
    echo '<input type="submit" value="Add" />';
    echo '</form>';
  }

  echo '</td>';
  echo '<td colspan="2">';

  /************ Injuries **************/
  if (count($injArray) > 0) {
    foreach($injArray as $inj)
    echo $inj.'<br />';
  }
  else
  echo '&nbsp;';

  echo '</td></tr>';
  echo '</table>';

  if ($edit==0) {
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p, 'edit' => '1'))).'">Modify Player</a>';
  }
  else {
    echo '<div class="title">Modification</div>';

    if($rules_version > 0 && $rules_version < 5) {
      $age = $dbconn->Execute("SELECT * FROM nuke_stars_age order by ageid");
      $ageSelect = '<select name="age">';
      for ( ; !$age->EOF; $age->moveNext() ) {
        $ageSelect .= '<option value="'.pnVarPrepForDisplay($age->Fields('ageid')).'">'.pnVarPrepForDisplay($age->Fields('name')).'</option>';
      }
      $ageSelect .= '</select>';

      echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p))).'" method="post">'
      .'Age: '.$ageSelect
      .'<input type="submit" value="Add" />'
      .'</form>';
    }

    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p))).'" method="post">'
    .'Number: <input size="3" type="text" name="renumber" value="'.pnVarPrepForDisplay($player->Fields('playernumber')).'" />'
    .'<input type="submit" value="Renumber" />'
    .'</form>';

    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p))).'" method="post">'
    .'Name: <input type="text" name="rename" value="'.pnVarPrepForDisplay($player->Fields('playername')).'" />'
    .'<input type="submit" value="Rename" />'
    .'</form>';

    if ($player->Fields('playerstatus') == 'ACTIVE') {
      echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p))).'" method="post">'
      .'<input type="hidden" name="retire" value="'.pnVarPrepForDisplay($p).'" />'
      .'<input type="submit" value="Retire Player" />'
      .'</form>';
    }
    else if ($player->Fields('playerstatus') == 'RETIRED' || $player->Fields('playerstatus') == 'DEAD') {
      echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $p))).'" method="post">'
      .'<input type="hidden" name="unretire" value="'.pnVarPrepForDisplay($p).'" />'
      .'<input type="submit" value="Unretire Player" />'
      .'</form>';
    }
  }

  CloseTable();
  include 'footer.php';

  return true;
}

function Stars_user_pastplayers($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $t = pnVarCleanFromInput('t');
  $sql = "SELECT t.teamname, t.rules_version FROM nuke_stars_team t WHERE teamid=".pnVarPrepForStore($t);
  $result = $dbconn->Execute($sql);

  $statMod = array();
  $posSkills = $dbconn->Execute("SELECT positionid, name FROM nuke_stars_positionskills p, nuke_stars_skills s WHERE p.skillid=s.skillid ORDER BY name");
  $posSkillNames = array();
  for ( ; !$posSkills->EOF; $posSkills->moveNext() ) {
    if (!is_array($posSkillNames[$posSkills->Fields('positionid')]))
    $posSkillNames[$posSkills->Fields('positionid')] = array();
    $posSkillNames[$posSkills->Fields('positionid')][] = $posSkills->Fields('name');
  }

  $skills = $dbconn->Execute("SELECT p.playerid, s.skillid, name FROM nuke_stars_playerskills p, nuke_stars_skills s WHERE p.skillid=s.skillid ORDER BY name");
  $skillNames = array();
  for ( ; !$skills->EOF; $skills->moveNext() ) {
    if (!is_array($skillNames[$skills->Fields('playerid')]))
    $skillNames[$skills->Fields('playerid')] = array();
    if (!is_array($statMod[$skills->Fields('playerid')]))
    $statMod[$skills->Fields('playerid')] = array(0, 0, 0, 0, 0);

    $skillNames[$skills->Fields('playerid')][] = $skills->Fields('name');

    if ($skills->Fields('skillid') < 5)
    $statMod[$skills->Fields('playerid')][$skills->Fields('skillid')]++;

    if ($skills->Fields('skillid') == 51 && $result->Fields('rules_version') <= 4) // Very Long Legs
    $statMod[$skills->Fields('playerid')][1]++;

    if ($skills->Fields('skillid') == 48 && $result->Fields('rules_version') <= 4) // Spikes
    $statMod[$skills->Fields('playerid')][4]++;
  }

  $sql = "SELECT p.playerid, injury, pg.gameid FROM nuke_stars_playergame pg, nuke_stars_player p "
  ."WHERE pg.playerid=p.playerid AND p.teamid=".pnVarPrepForStore($t)." AND playerstatus<>'ACTIVE' AND injury>0 "
  ."UNION ALL "
  ."SELECT p.playerid, decay_injury, pg.gameid FROM nuke_stars_playergame pg, nuke_stars_player p "
  ."WHERE pg.playerid=p.playerid AND p.teamid=".pnVarPrepForStore($t)." AND playerstatus<>'ACTIVE' AND decay_injury>0 "
  ."ORDER BY injury";
  $inj = $dbconn->Execute($sql);
  /*$inj = $dbconn->Execute("SELECT p.playerid, injury, pg.gameid FROM nuke_stars_playergame pg, nuke_stars_player p "
  ."WHERE pg.playerid=p.playerid AND playerstatus<>'ACTIVE' AND injury>0 ORDER BY injury");*/
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

  $sql = "SELECT p.*, pos.position, pos.cost, pos.ma, pos.st, pos.ag, pos.av FROM nuke_stars_player p, nuke_stars_position pos WHERE p.playertypeid=pos.playertypeid AND teamid=".pnVarPrepForStore($t)." AND playerstatus<>'ACTIVE' ORDER BY playernumber, playername";
  $players = $dbconn->Execute($sql);


  $playerHTML = "";
  $skilllevels = $dbconn->Execute("SELECT * FROM nuke_stars_rules_versions WHERE historic_version = ".pnVarPrepForStore($result->Fields('rules_version')));
  for ($count=0; !$players->EOF; $players->moveNext(), $count++) {
    $warn=false;
    $spp = $players->Fields('playerspp');
    $numSkills = 0;

    if ($spp >= $skilllevels->Fields('1_skilllevel')) $numSkills++;
    if ($spp >= $skilllevels->Fields('2_skilllevel')) $numSkills++;
    if ($spp >= $skilllevels->Fields('3_skilllevel')) $numSkills++;
    if ($spp >= $skilllevels->Fields('4_skilllevel')) $numSkills++;
    if ($spp >= $skilllevels->Fields('5_skilllevel')) $numSkills++;
    if ($spp >= $skilllevels->Fields('6_skilllevel')) $numSkills++;
    if ($spp >= $skilllevels->Fields('7_skilllevel') && $skilllevels->Fields('7_skilllevel') > 0) $numSkills++;

    $pSkills = count($skillNames[$players->Fields('playerid')]);
    if ($pSkills < $numSkills)
    $warn = '<b>Needs another skill</b>';
    else if ($pSkills > $numSkills)
    $warn = '<b>Too many skills!</b>';

    if (count($posSkillNames[$players->Fields('playertypeid')])+
    count($skillNames[$players->Fields('playerid')]) > 0)
    $skillList = implode(", ", array_merge($posSkillNames[$players->Fields('playertypeid')], $skillNames[$players->Fields('playerid')]));
    else
    $skillList = '&nbsp;';

    if (count($injArray[$players->Fields('playerid')]) > 0)
    $injuryList = implode(", ", $injArray[$players->Fields('playerid')]);
    else
    $injuryList = '&nbsp;';
    $playerHTML .= '<tr align="right"><td align="right">'.pnVarPrepForDisplay($players->Fields('playernumber')).'</td>'
    .'<td align="left" style="white-space: nowrap;">'
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $players->Fields('playerid')))).'">'.(strlen($players->Fields('playername')) > 0 ? pnVarPrepForDisplay($players->Fields('playername')) : '{unnamed}').'</a>'
    .'</td>'
    .'<td align="left">'.pnVarPrepForDisplay($players->Fields('position')).'</td>'
    .'<td align="center">'.fixStat($players->Fields('ma'),$statMod[$players->Fields('playerid')][1]).'</td>'
    .'<td align="center">'.fixStat($players->Fields('st'),$statMod[$players->Fields('playerid')][2]).'</td>'
    .'<td align="center">'.fixStat($players->Fields('ag'),$statMod[$players->Fields('playerid')][3]).'</td>'
    .'<td align="center">'.fixStat($players->Fields('av'),$statMod[$players->Fields('playerid')][4]).'</td>'
    .'<td align="left">'.$skillList.($warn?'<br />'.$warn:'').'</td>'
    .'<td align="left">'.$injuryList.'</td>'
    .'<td>'.f($players->fields('playercp')).'</td>'
    .'<td>'.f($players->fields('playertd')).'</td>'
    .'<td>'.f($players->fields('playerint')).'</td>'
    .'<td>'.f($players->fields('playercas')).'</td>'
    .'<td>'.f($players->fields('playermvp')).'</td>'
    .'<td>'.f($players->fields('playerspp')).'</td>'
    .'<td align="right">'.pnVarPrepForDisplay(($players->Fields('cost')+$players->Fields('additional_value'))/1000).'k</td>'
    .'</tr>'."\n";
  }

  global $additional_header;
  $additional_header[] = '<link rel="StyleSheet" href="modules/Stars/pnstyle/roster.css" type="text/css" />'."\n";

  include 'header.php';
  OpenTable();

  echo '<div style="text-align: center; margin-bottom: 3px;"><a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'view', array('t' => $t))).'">Back to team</a></div>';
  echo '<div class="roster" style="background: white; border: solid black 1px; border-top: solid black 2px; border-left: solid black 2px;">';
  echo '<table width="100%" cellspacing="0" border="0" align="center">'
  .'<tr>'
  .'<th colspan="16" style="font-size: 200%;">'.pnVarPrepForDisplay($result->Fields('teamname')).'<br /><span style="font-size: 50%">Past Players</span></th>'
  .'</tr>'."\n";
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
  .'</tr>'."\n";

  echo $playerHTML;

  echo '</table></div>';

  CloseTable();
  include 'footer.php';

  return true;
}

function Stars_user_print($args) {
  pnRedirect(pnModURL('Stars', '', 'view', array('t' => pnVarCleanFromInput('t'), 'u' => pnVarCleanFromInput('u'), 'print' => '1', 'theme' => 'Printer')));
  return true;
}

function Stars_user_view($args) {
  list($t, $user, $print) = pnVarCleanFromInput('t', 'u', 'print');
  if(!$print)checkLogin();

  $dbconn =& pnDBGetConn(true);
  if (is_numeric($t)) {
    $sql = "SELECT t.*, r.name as race, r.reroll_cost, r.apoth FROM nuke_stars_team t, nuke_stars_race r "
    ."WHERE t.teamrace=r.raceid AND teamid=".pnVarPrepForStore($t);
    $result = $dbconn->Execute($sql);
  }
  else {
    $sql = "SELECT t.*, r.name as race, r.reroll_cost FROM nuke_stars_team t, nuke_stars_race r "
    ."WHERE t.teamrace=r.raceid AND teamname='".pnVarPrepForStore($t)."' AND coachid=".pnVarPrepForStore(pnUserGetIDFromName($user));
    $result = $dbconn->Execute($sql);
    $t = $result->Fields('teamid');

  }

  if ($print && $result->EOF) {
    include 'header.php';
    OpenTable();
    echo 'No such team.';
    CloseTable();
    include 'footer.php';
    exit;
  }

  $maxgame = $dbconn->getOne("SELECT max(gameid) FROM nuke_stars_teamgame WHERE teamid=".pnVarPrepForStore($t));

  $raceid = $result->Fields('teamrace');

  $statMod = array();

  $sql = "SELECT positionid, name FROM nuke_stars_positionskills ps, nuke_stars_skills s, nuke_stars_position p WHERE ps.skillid=s.skillid AND ps.positionid = p.playertypeid AND raceid=".pnVarPrepForStore($raceid)." ORDER BY name";
  $posSkills = $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() <> 0) {
    echo $dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />';
    error_log ($dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br />');
    exit;
  }
  $posSkillNames = array();
  for ( ; !$posSkills->EOF; $posSkills->moveNext() ) {
    if (!is_array($posSkillNames[$posSkills->Fields('positionid')]))
    $posSkillNames[$posSkills->Fields('positionid')] = array();
    $posSkillNames[$posSkills->Fields('positionid')][] = $posSkills->Fields('name');
  }

  $skills = $dbconn->Execute("SELECT playerid, s.skillid, name FROM nuke_stars_playerskills p, nuke_stars_skills s WHERE p.skillid=s.skillid ORDER BY name");
  $skillNames = array();
  for ( ; !$skills->EOF; $skills->moveNext() ) {
    if (!is_array($skillNames[$skills->Fields('playerid')]))
    $skillNames[$skills->Fields('playerid')] = array();
    if (!is_array($statMod[$skills->Fields('playerid')]))
    $statMod[$skills->Fields('playerid')] = array(0, 0, 0, 0, 0);

    $skillNames[$skills->Fields('playerid')][] = $skills->Fields('name');

    if ($skills->Fields('skillid') < 5)  // MA ST AG AV
    $statMod[$skills->Fields('playerid')][$skills->Fields('skillid')]++;

    if ($skills->Fields('skillid') == 51 && $result->Fields('rules_version') <= 4) // Very Long Legs
    $statMod[$skills->Fields('playerid')][1]++;

    if ($skills->Fields('skillid') == 48 && $result->Fields('rules_version') <= 4) // Spikes
    $statMod[$skills->Fields('playerid')][4]++;
  }
  $sql = "SELECT p.playerid, injury, pg.gameid FROM nuke_stars_playergame pg, nuke_stars_player p "
  ."WHERE pg.playerid=p.playerid AND p.teamid=".pnVarPrepForStore($t)." AND playerstatus='ACTIVE' AND injury>0 "
  ."UNION ALL "
  ."SELECT p.playerid, decay_injury, pg.gameid FROM nuke_stars_playergame pg, nuke_stars_player p "
  ."WHERE pg.playerid=p.playerid AND p.teamid=".pnVarPrepForStore($t)." AND playerstatus='ACTIVE' AND decay_injury>0 "
  ."ORDER BY injury";
  $inj = $dbconn->Execute($sql);

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
      $dbconn->Execute("UPDATE nuke_stars_player SET playerstatus='DEAD' WHERE playerid=".pnVarPrepForStore($inj->Fields('playerid')));
    }
    else if ($i > 1 && $inj->Fields('gameid') == $maxgame && !in_array('m', $injArray[$p])) {
      array_unshift($injArray[$inj->Fields('playerid')], 'm');
    }
  }

  if($result->Fields('rules_version') > 0 && $result->Fields('rules_version') < 5) {
    $age = $dbconn->Execute("SELECT pa.*, a.name FROM nuke_stars_playerage pa, nuke_stars_age a, nuke_stars_player p WHERE pa.ageid=a.ageid AND pa.playerid=p.playerid AND teamid=".pnVarPrepForStore($t)." ORDER BY pa.ageid");
    for ( ; !$age->EOF; $age->moveNext() ) {
      $a = $age->Fields('ageid');
      $p = $age->Fields('playerid');
      $injArray[$p][] = $age->Fields('name');
      if ($a < 5)
      $statMod[$p][$a]--;
    }
  }

  $totalCp=0;
  $totalTD=0;
  $totalInt=0;
  $totalCas=0;
  $totalMVP=0;
  $totalSPP=0;
  $totalCost=0;
  if ($result->Fields('rules_version') < 5) {
    $teamRating = $result->Fields('teamtreasury') / 10000;
  }
  $teamRating += $result->Fields('teamfanfactor') +
  $result->Fields('teamrerolls') * $result->Fields('reroll_cost')/10000 +
  $result->Fields('teamcoaches') +
  $result->Fields('teamcheerleaders') +
  $result->Fields('teamapoth')*5;

  $sql = "SELECT p.*, pos.position, pos.cost, pos.ma, pos.st, pos.ag, pos.av FROM nuke_stars_player p, nuke_stars_position pos WHERE p.playertypeid=pos.playertypeid AND teamid=".pnVarPrepForStore($t)." AND playerstatus='ACTIVE' ORDER BY playernumber, playername";
  $players = $dbconn->Execute($sql);


  $playerHTML = "";
  $sql = "SELECT * FROM nuke_stars_rules_versions WHERE historic_version = ".pnVarPrepForStore($result->Fields('rules_version'));
  $skilllevels = $dbconn->Execute($sql);
  for ($count=0; !$players->EOF; $players->moveNext(), $count++) {
    $warn=false;
    if (!$print) {
      $spp = $players->Fields('playerspp');
      $numSkills = 0;

      if ($spp >= $skilllevels->Fields('1_skilllevel')) $numSkills++;
      if ($spp >= $skilllevels->Fields('2_skilllevel')) $numSkills++;
      if ($spp >= $skilllevels->Fields('3_skilllevel')) $numSkills++;
      if ($spp >= $skilllevels->Fields('4_skilllevel')) $numSkills++;
      if ($spp >= $skilllevels->Fields('5_skilllevel')) $numSkills++;
      if ($spp >= $skilllevels->Fields('6_skilllevel')) $numSkills++;
      if ($spp >= $skilllevels->Fields('7_skilllevel') && $skilllevels->Fields('7_skilllevel') > 0) $numSkills++;

      $pSkills = count($skillNames[$players->Fields('playerid')]);
      if ($pSkills < $numSkills)
      $warn = '<b>Needs another skill</b>';
      else if ($pSkills > $numSkills)
      $warn = '<b>Too many skills!</b>';

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
    if($result->Fields('rules_version') < 5 || !in_array('m', $injArray[$players->Fields('playerid')])) {
      $totalCost += $players->Fields('cost');
      $teamRating += $players->Fields('cost') / 10000;
    }
    if (count($posSkillNames[$players->Fields('playertypeid')])+
    count($skillNames[$players->Fields('playerid')]) > 0)
    $skillList = implode(", ", array_merge($posSkillNames[$players->Fields('playertypeid')], $skillNames[$players->Fields('playerid')]));
    else
    $skillList = '&nbsp;';

    if (count($injArray[$players->Fields('playerid')]) > 0)
    $injuryList = implode(", ", $injArray[$players->Fields('playerid')]);
    else
    $injuryList = '&nbsp;';

    $i_start = (in_array('m', $injArray[$players->Fields('playerid')]) ? '<i>' : '');
    $i_slut = (in_array('m', $injArray[$players->Fields('playerid')]) ? '</i>' : '');

    $pname = pnVarPrepForDisplay($players->fields('playername'));
    if (strlen($pname)==0)
    $pname=$print?'&nbsp;':'{Unnamed}';


    $playerHTML .= '<tr align="right"><td align="right">'.pnVarPrepForDisplay($players->Fields('playernumber')).'</td>'
    .'<td align="left" style="white-space: nowrap;">'
    .($print?$pname:'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'player', array('t' => $t, 'p' => $players->Fields('playerid')))).'">'.$i_start.$pname.$i_slut.'</a>')
    .'</td>'
    .'<td align="left">'.$i_start.pnVarPrepForDisplay($players->Fields('position')).$i_slut.'</td>'
    .'<td align="center">'.$i_start.fixStat($players->Fields('ma'),$statMod[$players->Fields('playerid')][1]).$i_slut.'</td>'
    .'<td align="center">'.$i_start.fixStat($players->Fields('st'),$statMod[$players->Fields('playerid')][2]).$i_slut.'</td>'
    .'<td align="center">'.$i_start.fixStat($players->Fields('ag'),$statMod[$players->Fields('playerid')][3]).$i_slut.'</td>'
    .'<td align="center">'.$i_start.fixStat($players->Fields('av'),$statMod[$players->Fields('playerid')][4]).$i_slut.'</td>'
    .'<td align="left">'.$i_start.$skillList.($warn?'<br />'.$warn:'').$i_slut.'</td>'
    .'<td align="left">'.$i_start.$injuryList.$i_slut.'</td>'
    .'<td>'.f($players->fields('playercp')).'</td>'
    .'<td>'.f($players->fields('playertd')).'</td>'
    .'<td>'.f($players->fields('playerint')).'</td>'
    .'<td>'.f($players->fields('playercas')).'</td>'
    .'<td>'.f($players->fields('playermvp')).'</td>'
    .'<td>'.f($players->fields('playerspp')).'</td>'
    .'<td align="right">'.($result->Fields('rules_version') >= 5 && in_array('m', $injArray[$players->Fields('playerid')]) ? '0' : pnVarPrepForDisplay(($players->Fields('cost')+$players->Fields('additional_value'))/1000).'k').'</td>'
    .'</tr>'."\n";
    if($result->Fields('rules_version') < 5 || !in_array('m', $injArray[$players->Fields('playerid')])) {
      $totalCost += $players->Fields('additional_value');
      $teamRating += ($players->Fields('additional_value') / 10000);
    }

  }
  if ($result->Fields('rules_version') < 5) {
    $teamRating += floor($totalSPP/5);
  }
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
  global $additional_header;
  if ($print) {
    $additional_header[] = '<link rel="StyleSheet" href="modules/Stars/pnstyle/print_roster.css" type="text/css" />'."\n";
  }
  else {
    $additional_header[] = '<link rel="StyleSheet" href="modules/Stars/pnstyle/roster.css" type="text/css" />'."\n";
  }
  include 'header.php';
  OpenTable();

  $tr = $dbconn->getOne("SELECT teamrating FROM nuke_stars_team WHERE teamid=".pnVarPrepForStore($t));
  if ($teamRating != $tr) {
    $dbconn->Execute("UPDATE nuke_stars_team SET teamrating=".pnVarPrepForStore($teamRating)." WHERE teamid=".pnVarPrepForStore($t));
  }

  if (!$print) {
    echo '<div style="text-align: center; margin-bottom: 3px;">'
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars')).'">Back to team list</a> &bull; '
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'pastplayers', array('t' => $t))).'">View Past Players</a> &bull; '
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'print', array('t' => $t))).'">View Printable Roster</a></div>';
  }

  echo '<div class="roster" style="background: white URL(/images/NAFwatermark.jpg) center no-repeat; border: solid black 1px; border-top: solid black 2px; border-left: solid black 2px;">';
  echo '<table width="100%" cellspacing="0" border="0" align="center">'
  .'<tr>'
  .'<th colspan="16" style="font-size: 200%;">'.$result->Fields('teamname').'<br /><span style="font-size: 50%">'.$result->Fields('race').($result->Fields('rules_version') < 5 ? ' (TR '.$teamRating : ' (TV '.($teamRating*10).'K').')</span></th>'
  .'</tr>'."\n";
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
  .'</tr>'."\n";

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
  echo '<table width="100%" border="0" cellspacing="0">'
  .'<tr valign="top">'
  .'<td rowspan="'.($result->Fields('rules_version') >= 5 ? '7' : '6').'" width="70%">';
  if ($print || pnUserGetVar('uid') != $result->Fields('coachid')) {
    echo '&nbsp;';
  }
  else {
    echo '<div style="float: right;"><a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delete', array('t' => $t))).'">Delete Team</a></div>';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'buyplayer', array('t' => $t))).'">Buy player</a><br /><br />';
    //echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'report', array('t' => $t))).'">Report Match</a><br /><br />';
    //echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'treasury', array('t' => $t))).'">Edit Treasury</a><br /><br />';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'fixspp', array('t' => $t))).'">Recalculate SPPs</a>';
  }

  $matches = $dbconn->Execute("SELECT g.*, r.name as race FROM nuke_stars_teamgame g, nuke_stars_race r "
  ."WHERE g.opponentrace=r.raceid AND teamid=".pnVarPrepForStore($t)." ORDER BY gameid");
  $played = $matches->numRows() > 0;
  $treasury = $result->Fields('teamtreasury');
  $allowApoth = $result->Fields('apoth') == 'y';
  echo '</td>'
  .'<th>Rerolls:</th>'
  .'<td align="center">'
  .($print || $result->Fields('teamrerolls')==0?'':'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delreroll', array('t' => $t))).'">-</a> ')
  .(0+$result->Fields('teamrerolls'))
  .($print || $treasury < $result->Fields('reroll_cost')*($played?2:1)?'':' <a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'addreroll', array('t' => $t))).'">+</a>')
  .'</td>'
  .'<th>x '.($result->Fields('reroll_cost')/1000).'k =</th>'
  .'<td align="right">'.($result->Fields('teamrerolls') * $result->Fields('reroll_cost') / 1000).'k</td>'
  .'</tr>'
  .'<tr>'
  .'<th>Fan&nbsp;Factor:</th>'
  .'<td align="center">'
  .($print || $played || $result->Fields('teamfanfactor')<=1?'':'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delff', array('t' => $t))).'">-</a> ')
  .(0+$result->Fields('teamfanfactor'))
  .($print || $played || $treasury < 10000 || $result->Fields('teamfanfactor')>=9?'':' <a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'addff', array('t' => $t))).'">+</a>')
  .'</td>'
  .'<th>x 10k =</th>'
  .'<td align="right">'.($result->Fields('teamfanfactor') * 10).'k</td>'
  .'</tr>'
  .'<tr>'
  .'<th>Assistant Coaches:</th>'
  .'<td align="center">'
  .($print || $result->Fields('teamcoaches')==0?'':'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delcoach', array('t' => $t))).'">-</a> ')
  .(0+$result->Fields('teamcoaches'))
  .($print || $treasury < 10000?'':' <a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'addcoach', array('t' => $t))).'">+</a>')
  .'</td>'
  .'<th>x 10k =</th>'
  .'<td align="right">'.($result->Fields('teamcoaches') * 10).'k</td>'
  .'</tr>'
  .'<tr>'
  .'<th>Cheerleaders:</th>'
  .'<td align="center">'
  .($print || $result->Fields('teamcheerleaders')==0?'':'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delcheer', array('t' => $t))).'">-</a> ')
  .(0+$result->Fields('teamcheerleaders'))
  .($print || $treasury < 10000?'':' <a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'addcheer', array('t' => $t))).'">+</a>')
  .'</td>'
  .'<th>x 10k =</th>'
  .'<td align="right">'.($result->Fields('teamcheerleaders') * 10).'k</td>'
  .'</tr>'
  .'<tr>'
  .'<th>Apothecary:</th>'
  .'<td align="center">'
  .($print || $result->Fields('teamapoth')==0?'':'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'delapoth', array('t' => $t))).'">-</a> ')
  .((0+$result->Fields('teamapoth')==0?"No":"Yes"))
  .($print || $treasury < 50000 || $result->Fields('teamapoth')==1 || !$allowApoth?'':' <a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'addapoth', array('t' => $t))).'">+</a>')
  .'</td>'
  .'<th>@ 50k =</th>'
  .'<td align="right">'.($result->Fields('teamapoth') * 50).'k</td>'
  .'</tr>'
  .'<tr>';
  if ($print || pnUserGetVar('uid') != $result->Fields('coachid')) {
    echo '<th colspan="3">Treasury:</th>';
  }
  else {
    echo '<th colspan="3"><a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'treasury', array('t' => $t))).'">Treasury (edit):</a></th>';
  }
  echo '<td align="right">'.($result->Fields('teamtreasury')/1000).'k</td>'
  .'</tr>';
  if ($result->Fields('rules_version') >= 5) {
    if ($teamRating < 175)$spiralling = 0;
    else $spiralling = floor(($teamRating - 175)/15 + 1) * 10000;
    echo '<tr>'
    .'<th colspan="3">Spiralling expenses:</th>'
    .'<td align="right">'.$spiralling.($spiralling > 0 ? 'k' : '').'</td>'
    .'</tr>';
  }
  echo '</table>';
  echo '</div>';

  echo '<div class="roster" style="page-break-before: always; margin-top: 1em; background: white; border-top: solid black 1px; border-left: solid black 1px;">';
  echo '<table width="100%" cellspacing="0" border="0" align="center">'
  .'<tr>';
  if ($print || pnUserGetVar('uid') != $result->Fields('coachid')) {
    echo '<th colspan="9">Match Record</th>';
  }
  else {
    echo '<th colspan="9"><a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'report', array('t' => $t))).'">Match Record (Report):</a></th>';
  }
  echo '</tr>'."\n";
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

    echo '<tr align="center">'
    .'<td align="left">'.pnVarPrepForDisplay($matches->Fields('teamtr')).'</td>'
    .'<td>'.(!$print?'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'report', array('t' => $t, 'id' => $matches->Fields('gameid')))).'">':'').pnVarPrepForDisplay($matches->Fields('opponentname')).' (TR '.pnVarPrepForDisplay($matches->Fields('opponenttr')).')'.(!$print?'</a>':'').'</td>'
    .'<td align="left">'.pnVarPrepForDisplay($matches->Fields('race')).'</td>'
    .'<td>'.($td>$oppTd?'Win':($td==$oppTd?'Tie':'Loss')).'</td>'
    .'<td>'.pnVarPrepForDisplay($td).' - '.pnVarPrepForDisplay($oppTd).'</td>'
    .'<td>'.pnVarPrepForDisplay($matches->Fields('teamcas')).' - '.pnVarPrepForDisplay($matches->Fields('opponentcas')).'</td>'
    .'<td>'.pnVarPrepForDisplay($matches->Fields('fanfactor')).'</td>'
    .'<td align="right">'.pnVarPrepForDisplay($matches->Fields('winnings')).'</td>'
    .'<td align="right">'.pnVarPrepForDisplay($matches->Fields('gate')).'</td>'
    .'</tr>'."\n";
  }
  echo '</table>';

  if ( $wins != $result->Fields('teamwins') ||
  $ties != $result->Fields('teamties') ||
  $losses != $result->Fields('teamlosses') ||
  $tdfor != $result->Fields('teamtdfor') ||
  $tdagainst != $result->Fields('teamtdagainst') ||
  $casfor != $result->Fields('teamcasfor') ||
  $casagainst != $result->Fields('teamcasagainst') ) {
    $dbconn->Execute("UPDATE nuke_stars_team SET teamwins=".pnVarPrepForStore($wins).", teamties=".pnVarPrepForStore($ties).", teamlosses=".pnVarPrepForStore($losses).", teamtdfor=".pnVarPrepForStore($tdfor).", "
    ."teamtdagainst=".pnVarPrepForStore($tdagainst).", teamcasfor=".pnVarPrepForStore($casfor).", teamcasagainst=".pnVarPrepForStore($casagainst)." WHERE teamid=".pnVarPrepForStore($t));
  }

  echo '</div>';

  CloseTable();
  include 'footer.php';
  return true;
}

function Stars_user_main($args) {
  checkLogin();
  $dbconn =& pnDBGetConn(true);
  $u = pnVarCleanFromInput('u');
  if ($u == 0)
  $u = pnUserGetVar('uid');

  include 'header.php';
  OpenTable();

  echo '<div style="font-size: 2em;">Simple Team And Roster System (STARS)</div>';

  echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
  .'<tr><th bgcolor="#D9D8D0" colspan="7">'.pnVarPrepForDisplay(poss(pnUserGetVar('uname', $u))).' teams</th></tr>'
  .'<tr>'
  .'<th bgcolor="#D9D8D0">Team Name</th>'
  .'<th bgcolor="#D9D8D0">Race</th>'
  .'<th bgcolor="#D9D8D0">Value /<br />Rating</th>'
  .'<th bgcolor="#D9D8D0">Record<br /><span style="font-size: 0.8em;">(W/T/L)</span></th>'
  .'<th bgcolor="#D9D8D0">TD Diff</th>'
  .'<th bgcolor="#D9D8D0">Cas Diff</th>'
  .'<th bgcolor="#D9D8D0">Rules Version</th>'
  .'</tr>';

  $result = $dbconn->Execute("SELECT nuke_stars_team.*, nuke_stars_race.name as race, title "
  ."FROM nuke_stars_team, nuke_stars_race, nuke_stars_rules_versions WHERE teamrace=raceid AND coachid=".pnVarPrepForStore($u)." AND rules_version = historic_version order by rules_version, teamname");

  for ( ; !$result->EOF; $result->moveNext() ) {
    //if ($u == pnUserGetVar('uid'))
    $link = pnModURL('Stars', '', 'view', array('t' => $result->Fields('teamid')));
    //else
    //$link = pnModURL('Stars', '', 'view', array('u' => $u, 't' => $result->Fields('teamid')));
    $tdDiff = $result->Fields('teamtdfor')-$result->Fields('teamtdagainst');
    if ($tdDiff > 0) $tdDiff = "+$tdDiff";
    $casDiff = $result->Fields('teamcasfor')-$result->Fields('teamcasagainst');
    if ($casDiff > 0) $casDiff = "+$casDiff";
    echo '<tr>'
    .'<td bgcolor="#f8f7ee"><a href="'.pnVarPrepForDisplay($link).'">'.pnVarPrepForDisplay($result->Fields('teamname')).'</a></td>'
    .'<td bgcolor="#f8f7ee">'.pnVarPrepForDisplay($result->Fields('race')).'</td>'
    .'<td bgcolor="#f8f7ee" align="center">'.pnVarPrepForDisplay(($result->Fields('rules_version')< 5 ? $result->Fields('teamrating') : ($result->Fields('teamrating')*10).'K')).'</td>'
    .'<td bgcolor="#f8f7ee" align="center">'.pnVarPrepForDisplay($result->Fields('teamwins')).'/'.pnVarPrepForDisplay($result->Fields('teamties')).'/'.pnVarPrepForDisplay($result->Fields('teamlosses')).'</td>'
    .'<td bgcolor="#f8f7ee" align="center">'.pnVarPrepForDisplay($tdDiff).'</td>'
    .'<td bgcolor="#f8f7ee" align="center">'.pnVarPrepForDisplay($casDiff).'</td>'
    .'<td bgcolor="#f8f7ee" align="center">'.pnVarPrepForDisplay($result->Fields('title')).'</td>'
    .'</tr>';
  }

  echo '</table>';
  echo '<div align="center"><a href="'.pnVarPrepForDisplay(pnModURL('Stars', '', 'create')).'">Create Team</a></div>';

  CloseTable();
  include 'footer.php';
  return true;
}
?>
