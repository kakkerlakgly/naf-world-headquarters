<?
function addStyle($num) {
  return '<span style="display: inline; background: #cccccc; border: solid gray 1px; padding-left: 1em; padding-right: 1em;">'.pnVarPrepForDisplay($num).'</span>';
}

function poss($name) {
  return ($name{strlen($name)-1}=='s') ? ($name."'") : ($name."'s");
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

  displayError('You must be logged in to manage leagues.');
}

function generateRaceOptions($race) {
  $dbconn =& pnDBGetConn(true);
  $ret = '<select name="race">';
  $result = $dbconn->Execute("SELECT raceid, name FROM nuke_stars_race ORDER BY name");
  for ( ; !$result->EOF; $result->moveNext() ) {
    $ret .= '<option value="'.pnVarPrepForDisplay($result->Fields('raceid')).'"'.($result->Fields('raceid')==$race?' selected="selected"':'').'>'.pnVarPrepForDisplay($result->Fields('name')).'</option>';
  }
  $ret .= '</select>';
  return $ret;
}

function Stars_league_advanced($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  include 'header.php';
  OpenTable();
  echo '<div><a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename')))).'">Back to league</a></div>';

  echo '<div style="font-size: 2em;">Advanced QUILTing</div>';

  echo '
    <p>
    The QUILT system allows you to include the league table on your own site
    by providing the league information in XML format.
    </p>

    <p>
    The XML data for '.pnVarPrepForDisplay($result->Fields('leaguename')).' can be found at
    <a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'print', array('l' => $result->Fields('leaguename'), 'xml' => '1', 'u' => pnUserGetVar('uname')))).'">'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'print', array('l' => $result->Fields('leaguename'), 'xml' => '1', 'u' => pnUserGetVar('uname')))).'</a>.
    </p>

    <p>
    For an example of how to use it we have created an example written in PHP which you may
    use and modify for your own purposes.<br />
    <a href="files/LeagueParser.php">Download the League Parser example here (Modified November 2007)</a>.
    </p>
    ';

  CloseTable();
  include 'footer.php';
  return true;
}

function Stars_league_editteam($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  list($t, $win, $tie, $loss, $tdfor, $tdagainst, $casfor, $casagainst) = pnVarCleanFromInput('t', 'win', 'tie', 'loss', 'tdfor', 'tdagainst', 'casfor', 'casagainst');
  $t+=0;
  $lm = $dbconn->Execute("SELECT t.*, lm.* FROM nuke_stars_leaguemember lm, nuke_stars_team t WHERE lm.teamid = t.teamid AND lm.leagueid=".pnVarPrepForStore($result->Fields('leagueid'))." AND lm.teamid=".pnVarPrepForStore($t));

  if ($lm->EOF)
  displayError('The team is not part of the league');

  if (strlen($win)>0) {
    $win+=0; $tie+=0; $loss+=0; $tdfor+=0; $tdagainst+=0; $casfor+=0; $casagainst+=0;

    $win = $lm->Fields('teamwins')-$win;
    $tie = $lm->Fields('teamties')-$tie;
    $loss = $lm->Fields('teamlosses')-$loss;
    $tdfor = $lm->Fields('teamtdfor')-$tdfor;
    $tdagainst = $lm->Fields('teamtdagainst')-$tdagainst;
    $casfor = $lm->Fields('teamcasfor')-$casfor;
    $casagainst = $lm->Fields('teamcasagainst')-$casagainst;

    $sql = "UPDATE nuke_stars_leaguemember lm, nuke_stars_team t SET prewin=".pnVarPrepForStore($win).", pretie=".pnVarPrepForStore($tie).", preloss=".pnVarPrepForStore($loss).", "
      ."pretdfor=".pnVarPrepForStore($tdfor).", pretdagainst=".pnVarPrepForStore($tdagainst).", precasfor=".pnVarPrepForStore($casfor).", precasagainst=".pnVarPrepForStore($casagainst)." "
      ."WHERE lm.leagueid=".pnVarPrepForStore($result->Fields('leagueid'))." AND lm.teamid=".pnVarPrepForStore($t);

    $dbconn->Execute($sql);

    pnRedirect(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid'))));
  }
  else {
    include 'header.php';
    OpenTable();
    echo '<div><a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid')))).'">Back to member list</a></div>';

    $wins = $lm->Fields('teamwins') - $lm->Fields('prewin');
    $ties = $lm->Fields('teamties') - $lm->Fields('pretie');
    $losses = $lm->Fields('teamlosses') - $lm->Fields('preloss');
    $tdfor = $lm->Fields('teamtdfor') - $lm->Fields('pretdfor');
    $tdagainst = $lm->Fields('teamtdagainst') - $lm->Fields('pretdagainst');
    $casfor = $lm->Fields('teamcasfor') - $lm->Fields('precasfor');
    $casagainst = $lm->Fields('teamcasagainst') - $lm->Fields('precasagainst');


    echo '<div style="font-size: 2em;">Lifetime record for '.pnVarPrepForDisplay($lm->Fields('teamname')).'</div>';
    echo '<table border="0">'
      .'<tr align="right"><th align="left">Record</th><td>Wins '.addStyle($lm->Fields('teamwins')).'</td>'
      .'<td>Ties '.addStyle($lm->Fields('teamties')).'</td>'
      .'<td>Losses '.addStyle($lm->Fields('teamlosses')).'</td></tr>'
      .'<tr align="right"><th align="left">Touchdowns</th><td>For '.addStyle($lm->Fields('teamtdfor')).'</td><td colspan="2">Against '.addStyle($lm->Fields('teamtdagainst')).'</td></tr>'
      .'<tr align="right"><th align="left">Casualties</th><td>For '.addStyle($lm->Fields('teamcasfor')).'</td><td colspan="2">Against '.addStyle($lm->Fields('teamcasagainst')).'</td></tr>'
      .'</table><br />';

    echo '<div style="font-size: 2em;">Seasonal record</div>';
    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'editteam')).'" method="post">'
      .'<input type="hidden" name="l" value="'.$result->Fields('leagueid').'" />'
      .'<input type="hidden" name="t" value="'.$t.'" />'
      .'<table border="0">'
      .'<tr align="right"><th align="left">Record</th><td>Wins <input type="text" size="3" name="win" value="'.$wins.'" /></td>'
      .'<td>Ties <input type="text" size="3" name="tie" value="'.$ties.'" /></td>'
      .'<td>Losses <input type="text" size="3" name="loss" value="'.$losses.'" /></td></tr>'
      .'<tr align="right"><th align="left">Touchdowns</th><td>For <input size="3" type="text" name="tdfor" value="'.$tdfor.'" /></td><td colspan="2">Against <input size="3" type="text" name="tdagainst" value="'.$tdagainst.'" /></td></tr>'
      .'<tr align="right"><th align="left">Casualties</th><td>For <input size="3" type="text" name="casfor" value="'.$casfor.'" /></td><td colspan="2">Against <input size="3" type="text" name="casagainst" value="'.$casagainst.'" /></td></tr>'
      .'</table>'
      .'<input type="submit" value="Update" />'
      .'</form>';

    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_league_clearrecords($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  if ($confirm==1) {
    $dbconn->Execute("UPDATE nuke_stars_externalteam SET teamwins=0, teamties=0, teamlosses=0, teamtdfor=0, teamtdagainst=0, "
      ."teamcasfor=0, teamcasagainst=0 WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid')));
    $dbconn->Execute("UPDATE nuke_stars_leaguemember lm, nuke_stars_team t SET prewin=teamwins, pretie=teamties, preloss=teamlosses, "
      ."pretdfor=teamtdfor, pretdagainst=teamtdagainst, precasfor=teamcasfor, precasagainst=teamcasagainst "
      ."WHERE lm.teamid=t.teamid AND lm.leagueid=".pnVarPrepForStore($lid));
    pnRedirect(pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename'))));
  }
  else {
    include 'header.php';
    OpenTable();

    echo '<div style="font-size: 2em;">Are you sure you want to clear the seasonal records for \''.pnVarPrepForDisplay($result->Fields('leaguename')).'\'?</div>'
      .'<br />'
      .'Doing so will reset the seasonal record of the teams in the league, showing them as having played no games. '
      .'No actual game records will be affected. While this is not an irreversible action, you will need to set up the previous seasonal records manually '
      .'should you decide to go ahead and clear the current ones.<br />'
      .'<br />'
      .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid')))).'">No, do not clear the seasonal records</a><br /><br />'
      .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'clearrecords', array('l' => $result->Fields('leagueid'), 'confirm' => '1'))).'">Yes, clear the seasonal records</a>';

    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_league_config($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  list($name, $win, $tie, $loss, $tdfor, $tdagainst, $casfor, $casagainst) =
  pnVarCleanFromInput('leaguename', 'win', 'tie', 'loss', 'tdfor', 'tdagainst', 'casfor', 'casagainst');
  $win+=0; $tie+=0; $loss+=0;
  $tdfor+=0; $tdagainst+=0; $casfor+=0; $casagainst+=0;
  if (strlen($name) > 0) {
    $dbconn->Execute("UPDATE nuke_stars_league SET leaguename='".pnVarPrepForStore($name)."', leaguescorewin=".pnVarPrepForStore($win).", leaguescoretie=".pnVarPrepForStore($tie).", leaguescoreloss=".pnVarPrepForStore($loss).", "
      ."leaguescoretdfor=".pnVarPrepForStore($tdfor).", leaguescoretdagainst=".pnVarPrepForStore($tdagainst).", leaguescorecasfor=".pnVarPrepForStore($casfor).", leaguescorecasagainst=".pnVarPrepForStore($casagainst)." "
      ."WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid')));
    pnRedirect(pnModURL('Stars', 'league', 'view', array('l' => $name)));
  }
  else {
    include 'header.php';
    OpenTable();
    echo '<div><a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename')))).'">Back to league</a></div>';

    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'config')).'" method="post">'
      .'<input type="hidden" name="l" value="'.pnVarPrepForDisplay($l).'" />'
      .'<table border="0">'
      .'<tr><th colspan="4" align="left">League Name</th></tr>'
      .'<tr><td colspan="4"><input type="text" name="leaguename" value="'.pnVarPrepForDisplay($result->Fields('leaguename')).'" /></td></tr>'
      .'<tr><th colspan="4">Scoring</th></tr>'
      .'<tr align="right"><th align="left">Result</th><td>Win <input type="text" size="3" name="win" value="'.$result->Fields('leaguescorewin').'" /></td>'
      .'<td>Tie <input type="text" size="3" name="tie" value="'.$result->Fields('leaguescoretie').'" /></td>'
      .'<td>Loss <input type="text" size="3" name="loss" value="'.$result->Fields('leaguescoreloss').'" /></td></tr>'
      .'<tr align="right"><th align="left">Touchdowns</th><td>For <input size="3" type="text" name="tdfor" value="'.$result->Fields('leaguescoretdfor').'" /></td><td colspan="2">Against <input size="3" type="text" name="tdagainst" value="'.$result->Fields('leaguescoretdagainst').'" /></td></tr>'
      .'<tr align="right"><th align="left">Causalties</th><td>For <input size="3" type="text" name="casfor" value="'.$result->Fields('leaguescorecasfor').'" /></td><td colspan="2">Against <input size="3" type="text" name="casagainst" value="'.$result->Fields('leaguescorecasagainst').'" /></td></tr>'
      .'</table>'
      .'<input type="submit" value="Update" />'
      .'</form>';

    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_league_addmember($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  list($coach, $t) = pnVarCleanFromInput('c', 't');
  $t = trim($t);
  if ($t+0 == 0)
  $tid = $dbconn->getOne("SELECT teamid FROM nuke_stars_team t WHERE coachid='".pnVarPrepForStore(pnUserGetIDFromName($coach))."' AND teamname='".pnVarPrepForStore($t)."'");
  else {
    $tid = $t+0;
  }
  if ($tid+0 > 0)
  $dbconn->Execute("INSERT INTO nuke_stars_leaguemember (leagueid, teamid) VALUES (".pnVarPrepForStore($result->Fields('leagueid')).", ".pnVarPrepForStore($tid).")");
  pnRedirect(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid'))));
  return true;
}

function Stars_league_delmember($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  $t = pnVarCleanFromInput('t');
  $t+=0;
  if ($t > 0)
  $dbconn->Execute("DELETE FROM nuke_stars_leaguemember WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid'))." AND teamid=".pnVarPrepForStore($t));
  pnRedirect(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid'))));
  return true;
}

function Stars_league_addexternal($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  list($t, $coach, $wins, $ties, $losses, $tdfor, $tdagainst, $casfor, $casagainst, $name, $url, $rating, $race) =
  pnVarCleanFromInput('t', 'c', 'wins', 'ties', 'losses', 'tdfor', 'tdagainst', 'casfor', 'casagainst', 'teamname', 'url', 'rating', 'race');
  $t+=0;
  $wins+=0; $ties+=0; $losses+=0;
  $tdfor+=0; $tdagainst+=0; $casfor+=0; $casagainst+=0; $rating+=0;
  if (strlen($name) > 0) {
    if ($t > 0) {
      $sql = "UPDATE nuke_stars_externalteam SET coachname='".pnVarPrepForStore(trim($coach))."', teamname='".pnVarPrepForStore(trim($name))."', teamrace=".pnVarPrepForStore($race).", teamrating=".pnVarPrepForStore($rating).", "
        ."teamwins=".pnVarPrepForStore($wins).", teamlosses=".pnVarPrepForStore($losses).", teamties=".pnVarPrepForStore($ties).", teamtdfor=".pnVarPrepForStore($tdfor).", teamtdagainst=".pnVarPrepForStore($tdagainst).", "
        ."teamcasfor=".pnVarPrepForStore($casfor).", teamcasagainst=".pnVarPrepForStore($casagainst).", teamurl='".pnVarPrepForStore(trim($url))."' WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid'))." AND teamid=".pnVarPrepForStore($t);
      $dbconn->Execute($sql);
    }
    else {
      $sql = "INSERT INTO nuke_stars_externalteam (leagueid, coachname, teamname, teamrace, teamrating, teamwins, teamties, teamlosses, "
        ."teamtdfor, teamtdagainst, teamcasfor, teamcasagainst, teamurl) VALUES (".pnVarPrepForStore($result->Fields('leagueid')).", '".pnVarPrepForStore(trim($coach))."', '".pnVarPrepForStore(trim($name))."', ".pnVarPrepForStore($race).", ".pnVarPrepForStore($rating).", "
        .pnVarPrepForStore($wins).", ".pnVarPrepForStore($ties).", ".pnVarPrepForStore($losses).", ".pnVarPrepForStore($tdfor).", ".pnVarPrepForStore($tdagainst).", ".pnVarPrepForStore($casfor).", ".pnVarPrepForStore($casagainst).", '".pnVarPrepForStore(trim($url))."')";
      $dbconn->Execute($sql);
    }
    pnRedirect(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid'))));
  }
  else {
    include 'header.php';
    OpenTable();

    echo '<div><a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename')))).'">Back to league</a></div>';

    if ($t > 0) {
      $team = $dbconn->Execute("SELECT * FROM nuke_stars_externalteam WHERE teamid=".pnVarPrepForStore($t));
      if ($team->Fields('leagueid') != $result->Fields('leagueid'))
      displayError("This team is not a part of the league");

      $name = $team->Fields('teamname');
      $coach = $team->Fields('coachname');
      $wins = $team->Fields('teamwins');
      $ties = $team->Fields('teamties');
      $losses = $team->Fields('teamlosses');
      $tdfor = $team->Fields('teamtdfor');
      $tdagainst = $team->Fields('teamtdagainst');
      $casfor = $team->Fields('teamcasfor');
      $casagainst = $team->Fields('teamcasagainst');
      $url = $team->Fields('teamurl');
      $race = $team->Fields('teamrace');
      $rating = $team->Fields('teamrating');
    }
    else {
      $name=$coach=$url='';
      $wins=$ties=$losses=$tdfor=$tdagainst=$casfor=$casagainst=$race=0;
      $rating=100;
    }


    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'addexternal')).'" method="post">'
      .'<input type="hidden" name="l" value="'.$result->Fields('leagueid').'" />'
      .'<input type="hidden" name="t" value="'.$t.'" />'
      .'<table border="0">'
      .'<tr><th colspan="4" align="left">Team Name</th></tr>'
      .'<tr><td colspan="4"><input type="text" name="teamname" value="'.pnVarPrepForDisplay($name).'" /></td></tr>'
      .'<tr><th colspan="4" align="left">Race</th></tr>'
      .'<tr><td colspan="4">'.generateRaceOptions($race).'</td></tr>'
      .'<tr><th colspan="4" align="left">Team Rating</th></tr>'
      .'<tr><td colspan="4"><input type="text" name="rating" size="3" value="'.pnVarPrepForDisplay($rating).'" /></td></tr>'
      .'<tr><th colspan="4" align="left">Coach Name</th></tr>'
      .'<tr><td colspan="4"><input type="text" name="c" value="'.pnVarPrepForDisplay($coach).'" /></td></tr>'
      .'<tr><th colspan="4" align="left">Team URL</th></tr>'
      .'<tr><td colspan="4">http://<input type="text" name="url" value="'.pnVarPrepForDisplay($url).'" /></td></tr>'
      .'<tr align="right"><th align="left">Results</th><td>Wins <input type="text" size="3" name="wins" value="'.$wins.'" /></td>'
      .'<td>Ties <input type="text" size="3" name="ties" value="'.$ties.'" /></td>'
      .'<td>Losses <input type="text" size="3" name="losses" value="'.$losses.'" /></td></tr>'
      .'<tr align="right"><th align="left">Touchdowns</th><td>For <input size="3" type="text" name="tdfor" value="'.$tdfor.'" /></td><td colspan="2">Against <input size="3" type="text" name="tdagainst" value="'.$tdagainst.'" /></td></tr>'
      .'<tr align="right"><th align="left">Causalties</th><td>For <input size="3" type="text" name="casfor" value="'.$casfor.'" /></td><td colspan="2">Against <input size="3" type="text" name="casagainst" value="'.$casagainst.'" /></td></tr>'
      .'</table>'
      .'<input type="submit" value="'.($t>0?'Update':'Add').'" />'
      .'</form>';

    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_league_delexternal($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  $t = pnVarCleanFromInput('t');
  if (is_numeric($t))
  $sql = "DELETE FROM nuke_stars_externalteam WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid'))." AND teamid=".pnVarPrepForStore($t);
  $dbconn->Execute($sql);
  pnRedirect(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid'))));
  return true;
}

function Stars_league_members($args) {
  checkLogin();
  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  $teams = $dbconn->Execute("SELECT lm.*, t.*, lm.teamid as teamid, r.name as race FROM nuke_stars_leaguemember lm, nuke_stars_team t, "
    ."nuke_stars_race r "
    ."WHERE lm.teamid=t.teamid AND t.teamrace=r.raceid AND lm.leagueid=".pnVarPrepForStore($l)." order by teamname");


  include 'header.php';
  OpenTable();
  echo '<div><a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename')))).'">Back to league</a></div>';
  echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
    .'<tr><th bgcolor="#D9D8D0" colspan="7">Edit members in '.pnVarPrepForDisplay($result->Fields('leaguename')).'</th></tr>'
    .'<tr>'
    .'<th bgcolor="#D9D8D0">Coach</th>'
    .'<th bgcolor="#D9D8D0">Team Name</th>'
    .'<th bgcolor="#D9D8D0">Race</th>'
    .'<th bgcolor="#D9D8D0">Record<br /><span style="font-size: 0.8em;">(W/T/L)</span></th>'
    .'<th bgcolor="#D9D8D0">TD Diff</th>'
    .'<th bgcolor="#D9D8D0">Cas Diff</th>'
    .'<th bgcolor="#D9D8D0">Op</th>'
    .'</tr>';

  for ( ; !$teams->EOF; $teams->moveNext() ) {
    $tdDiff = ($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')) - ($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst'));
    $casDiff = ($teams->Fields('teamcasfor')-$teams->Fields('precasfor')) - ($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst'));
    if ($tdDiff>0) $tdDiff="+$tdDiff";
    if ($casDiff>0) $casDiff="+$casDiff";
    echo '<tr bgcolor="#ffffff">'
      .'<td>'.pnVarPrepForDisplay(pnUserGetVar('uname', $teams->Fields('coachid'))).'</td>'
      .'<td>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</td>'
      .'<td>'.$teams->Fields('race').'</td>'
      .'<td>'.($teams->Fields('teamwins')-$teams->Fields('prewin')).'/'.($teams->Fields('teamties')-$teams->Fields('pretie')).'/'.($teams->Fields('teamlosses')-$teams->Fields('preloss')).'</td>'
      .'<td>'.$tdDiff.' ('.($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')).'-'.($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).')</td>'
      .'<td>'.$casDiff.' ('.($teams->Fields('teamcasfor')-$teams->Fields('precasfor')).'-'.($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).')</td>'
      .'<td>'
      .'(<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'delmember', array('l' => $result->Fields('leagueid'), 't' => $teams->Fields('teamid')))).'">Kick</a>) '
      .'(<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'editteam', array('l' => $result->Fields('leagueid'), 't' => $teams->Fields('teamid')))).'">Edit</a>) '
      .'</td>'
      .'</tr>';
  }

  echo '</table>';
  echo '<div align="center">'
    .'Type in the coach and team names OR the team id of the team to add<br />'
    .'<form action="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'addmember')).'" method="post">'
    .'<input type="hidden" name="l" value="'.$result->Fields('leagueid').'" />'
    .'Coach: <input type="text" name="c" /> '
    .'Team: <input type="text" name="t" /> '
    .'<input type="submit" value="Add team" />'
    .'</form>'
    .'</div>';

  echo '<br />';

  echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
    .'<tr><th bgcolor="#D9D8D0" colspan="7">Edit external teams in '.pnVarPrepForDisplay($result->Fields('leaguename')).'</th></tr>'
    .'<tr>'
    .'<th bgcolor="#D9D8D0">Coach</th>'
    .'<th bgcolor="#D9D8D0">Team Name</th>'
    .'<th bgcolor="#D9D8D0">Race</th>'
    .'<th bgcolor="#D9D8D0">Record<br /><span style="font-size: 0.8em;">(W/T/L)</span></th>'
    .'<th bgcolor="#D9D8D0">TD Diff</th>'
    .'<th bgcolor="#D9D8D0">Cas Diff</th>'
    .'<th bgcolor="#D9D8D0">Op</th>'
    .'</tr>';

  $teams = $dbconn->Execute("SELECT t.*, r.name as race FROM nuke_stars_externalteam t, nuke_stars_race r WHERE teamrace=raceid AND leagueid=".pnVarPrepForStore($result->Fields('leagueid'))." ORDER BY teamname");
  for ( ; !$teams->EOF; $teams->moveNext() ) {
    $tdDiff = $teams->Fields('teamtdfor') - $teams->Fields('teamtdagainst');
    $casDiff = $teams->Fields('teamcasfor') - $teams->Fields('teamcasagainst');
    if ($tdDiff>0) $tdDiff="+$tdDiff";
    if ($casDiff>0) $casDiff="+$casDiff";
    echo '<tr bgcolor="#ffffff">'
      .'<td>'.pnVarPrepForDisplay($teams->Fields('coachname')).'</td>'
      .'<td>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</td>'
      .'<td>'.$teams->Fields('race').'</td>'
      .'<td>'.($teams->Fields('teamwins')-$teams->Fields('prewin')).'/'.($teams->Fields('teamties')-$teams->Fields('pretie')).'/'.($teams->Fields('teamlosses')-$teams->Fields('preloss')).'</td>'
      .'<td>'.$tdDiff.' ('.($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')).'-'.($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).')</td>'
      .'<td>'.$casDiff.' ('.($teams->Fields('teamcasfor')-$teams->Fields('precasfor')).'-'.($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).')</td>'
      .'<td>(<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'delexternal', array('l' => $result->Fields('leagueid'), 't' => $teams->Fields('teamid')))).'">Del</a>) '
      .'(<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'addexternal', array('l' => $result->Fields('leagueid'), 't' => $teams->Fields('teamid')))).'">Edit</a>)</td>'
      .'</tr>';
  }
  echo '</table>';
  echo '<div align="center">'
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'addexternal', array('l' => $result->Fields('leagueid')))).'">Add external team</a>'
    .'<br />External teams are teams which are not tracked by STARS';

  echo '<br /><br />'
    .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'clearrecords', array('l' => $result->Fields('leagueid')))).'">Clear seasonal records</a>';

  echo '</div>';

  CloseTable();
  include 'footer.php';
  return true;
}

function Stars_league_create($args) {
  checkLogin();

  $dbconn =& pnDBGetConn(true);
  $name = trim(pnVarCleanFromInput('leaguename'));

  if (strlen($name)==0) {
    include 'header.php';
    OpenTable();
    echo '<div class="title">Create League</div>';
    echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'create')).'" method="post">';

    echo 'Name<br /><input type="text" name="leaguename" /><br />';
    echo '<input type="submit" value="Create League" />';
    echo '</form>';
    CloseTable();
    include 'footer.php';
  }
  else {
    $existing = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leaguecommissionerid=".pnVarPrepForStore(pnUserGetVar('uid'))." AND leaguename='".pnVarPrepForStore($name)."'");
    if (!$existing->EOF) {
      displayError('You can not have two leagues with the same name.<br />'
        .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'create')).'">Back</a>');
    }
    $dbconn->Execute("INSERT INTO nuke_stars_league (leaguecommissionerid, leaguename) "
      ."VALUES (".pnVarPrepForStore(pnUserGetVar('uid')).", '".pnVarPrepForStore($name)."')");
    pnRedirect(pnModURL('Stars', 'league'));
  }
  return true;
}

function Stars_league_delete($args) {
  checkLogin();

  $l = pnVarCleanFromInput('l');
  $dbconn =& pnDBGetConn(true);
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($l));

  if ($result->EOF)
  displayError('No such league');

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
  if (!$isOwner)
  displayError('You do not own this league');

  $confirm = pnVarCleanFromInput('confirm');
  if ($confirm==1) {
    $dbconn->Execute("DELETE FROM nuke_stars_externalteam WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid')));
    $dbconn->Execute("DELETE FROM nuke_stars_leaguemember WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid')));
    $dbconn->Execute("DELETE FROM nuke_stars_league WHERE leagueid=".pnVarPrepForStore($result->Fields('leagueid')));
    pnRedirect(pnModURL('Stars', 'league'));
  }
  else {
    include 'header.php';
    OpenTable();

    echo '<div style="font-size: 2em;">Are you sure you want to <span style="color: red;">Delete</span> \''.pnVarPrepForDisplay($result->Fields('leaguename')).'\'?</div>'
      .'<br />'
      .'Deleting the league will also delete all external teams and seasonal data connected to the league. This is an '
      .'irreversible action.<br />'
      .'<br />'
      .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename')))).'">No, do not delete this league</a><br /><br />'
      .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'delete', array('l' => $result->Fields('leagueid'), 'confirm' => '1'))).'">Yes, delete this league</a>';

    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_league_print($args) {
  list($l, $u, $xml) = pnVarCleanFromInput('l', 'u', 'xml');
  $xml ?
    pnRedirect(pnModURL('Stars', 'league', 'view', array('l' => $l, 'u' => $u, 'xml' => $xml))) :
    pnRedirect(pnModURL('Stars', 'league', 'view', array('l' => $l, 'u' => $u, 'print' => '1', 'theme' => 'Printer')));
  return true;
}

function Stars_league_view($args) {

  $dbconn =& pnDBGetConn(true);
  list($l, $u, $print, $xml) = pnVarCleanFromInput('l', 'u', 'print', 'xml');

  if (strlen($u) > 0) {
  	$owner = $u;
    $u = pnUserGetIDFromName($u);
    if (!$u) {
      displayError('No such user');
    }
  }
  else {
    checkLogin();
    $u = pnUserGetVar('uid');
    $owner = pnUserGetVar('uname');
  }
  $result = $dbconn->Execute("SELECT * FROM nuke_stars_league WHERE leaguename='".pnVarPrepForStore($l)."' AND leaguecommissionerid=".pnVarPrepForStore($u));

  if ($result->EOF) {
    displayError('No such league.<br />');
  }

  $isOwner = $result->Fields('leaguecommissionerid') == pnUserGetVar('uid') || pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);

  if ($xml) {
    header("Content-type: text/xml");
    echo '<?xml version="1.0"?><league generator="QUILT" version="1.0"><name>'.pnVarPrepForDisplay($result->Fields('leaguename')).'</name>';
  }
  else if ($print) {
    include 'header.php';
    OpenTable();
  }
  else {
    include 'header.php';
    OpenTable();
    echo '<div><a href="'.($u!=pnUserGetVar('uid') ? pnVarPrepForDisplay(pnModURL('Stars', 'league', 'main', array('u' => $u))) : pnVarPrepForDisplay(pnModURL('Stars', 'league'))).'">Back to league list</a></div>';
  }
  if (!$xml) {
    echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
      .'<tr><th bgcolor="#D9D8D0" colspan="8">'.pnVarPrepForDisplay($result->Fields('leaguename')).'</th></tr>'
      .'<tr>'
      .'<th bgcolor="#D9D8D0">Coach</th>'
      .'<th bgcolor="#D9D8D0">Team Name</th>'
      .'<th bgcolor="#D9D8D0">Race</th>'
      .'<th bgcolor="#D9D8D0">Rating</th>'
      .'<th bgcolor="#D9D8D0">Record<br /><span style="font-size: 0.8em;">(W/T/L)</span></th>'
      .'<th bgcolor="#D9D8D0">TD Diff</th>'
      .'<th bgcolor="#D9D8D0">Cas Diff</th>'
      .'<th bgcolor="#D9D8D0">Score</th>'
      .'</tr>';
  }
  $teams = $dbconn->Execute("SELECT lm.*, t.*, r.name as race FROM nuke_stars_leaguemember lm, nuke_stars_team t, "
    ."nuke_stars_race r "
    ."WHERE lm.teamid=t.teamid AND teamrace=r.raceid AND lm.leagueid=".pnVarPrepForStore($result->Fields('leagueid')));
  $teamArray = array();
  $scores = array();
  for (; !$teams->EOF; $teams->moveNext()) {
    if ($teams->Fields('coachid') == pnUserGetVar('uid'))
    $link = pnModURL('Stars', '', 'view', array('t' => $teams->Fields('teamid')));
    else
    $link = pnModURL('Stars', '', 'print', array('t' => $teams->Fields('teamid')));
    $tdDiff = ($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')) - ($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst'));
    $casDiff = ($teams->Fields('teamcasfor')-$teams->Fields('precasfor')) - ($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst'));
    if ($tdDiff>0) $tdDiff="+$tdDiff";
    if ($casDiff>0) $casDiff="+$casDiff";
    $score = $result->Fields('leaguescorewin')*($teams->Fields('teamwins')-$teams->Fields('prewin')) +
    $result->Fields('leaguescoretie')*($teams->Fields('teamties')-$teams->Fields('pretie')) +
    $result->Fields('leaguescoreloss')*($teams->Fields('teamlosses')-$teams->Fields('preloss')) +
    $result->Fields('leaguescoretdfor')*($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')) +
    $result->Fields('leaguescoretdagainst')*($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')) +
    $result->Fields('leaguescorecasfor')*($teams->Fields('teamcasfor')-$teams->Fields('precasfor')) +
    $result->Fields('leaguescorecasagainst')*($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst'));
    if (!$xml) {
      $teamArray[$teams->Fields('teamid')] =
      '<tr bgcolor="#ffffff">'
        .'<td>'.pnVarPrepForDisplay(pnUserGetVar('uname', $teams->Fields('coachid'))).'</td>'
        .'<td><a href="'.pnVarPrepForDisplay($link).'">'.pnVarPrepForDisplay($teams->Fields('teamname')).'</a></td>'
        .'<td>'.$teams->Fields('race').'</td>'
        .'<td>'.$teams->Fields('teamrating').'</td>'
        .'<td>'.($teams->Fields('teamwins')-$teams->Fields('prewin')).'/'.($teams->Fields('teamties')-$teams->Fields('pretie')).'/'.($teams->Fields('teamlosses')-$teams->Fields('preloss')).'</td>'
        .'<td>'.$tdDiff.' ('.($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')).'-'.($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).')</td>'
        .'<td>'.$casDiff.' ('.($teams->Fields('teamcasfor')-$teams->Fields('precasfor')).'-'.($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).')</td>'
        .'<td>'.$score.'</td>'
        .'</tr>'."\n";
    }
    else {
      $teamArray[$teams->Fields('teamid')] = '
        <team>
        <type>STARS</type>
        <name>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</name>
        <url>'.pnVarPrepForDisplay($link).'</url>
        <coach>'.pnVarPrepForDisplay(pnUserGetVar('uname', $teams->Fields('coachid'))).'</coach>
        <race>'.$teams->Fields('race').'</race>
        <rating>'.$teams->Fields('teamrating').'</rating>
        <wins>'.$teams->Fields('teamwins').'</wins>
        <ties>'.$teams->Fields('teamties').'</ties>
        <losses>'.$teams->Fields('teamlosses').'</losses>
        <seasonwins>'.($teams->Fields('teamwins')-$teams->Fields('prewin')).'</seasonwins>
        <seasonties>'.($teams->Fields('teamties')-$teams->Fields('pretie')).'</seasonties>
        <seasonlosses>'.($teams->Fields('teamlosses')-$teams->Fields('preloss')).'</seasonlosses>
        <tdfor>'.$teams->Fields('teamtdfor').'</tdfor>
        <tdagainst>'.$teams->Fields('teamtdagainst').'</tdagainst>
        <seasontdfor>'.($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')).'</seasontdfor>
        <seasontdagainst>'.($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).'</seasontdagainst>
        <casfor>'.$teams->Fields('teamcasfor').'</casfor>
        <casagainst>'.$teams->Fields('teamcasagainst').'</casagainst>
        <seasoncasfor>'.($teams->Fields('teamcasfor')-$teams->Fields('precasfor')).'</seasoncasfor>
        <seasoncasagainst>'.($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).'</seasoncasagainst>
        <score>'.$score.'</score>
        </team>
        ';
    }
    $scores[$teams->Fields('teamid')] = $score * 10000 + ($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')) - ($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst'));
  }

  $teams = $dbconn->Execute("SELECT et.*, r.name as race FROM nuke_stars_externalteam et, "
    ."nuke_stars_race r "
    ."WHERE et.teamrace=r.raceid AND et.leagueid=".pnVarPrepForStore($result->Fields('leagueid')));
  for (; !$teams->EOF; $teams->moveNext()) {
    $teamURL = trim($teams->Fields('teamurl'));
    if (strlen($teamURL) > 0)
    $teamlink = '(Ext) <a href="http://'.pnVarPrepForDisplay($teamURL).'">'. pnVarPrepForDisplay($teams->Fields('teamname')).'</a>';
    else
    $teamlink = pnVarPrepForDisplay($teams->Fields('teamname'));

    $tdDiff = $teams->Fields('teamtdfor') - $teams->Fields('teamtdagainst');
    $casDiff = $teams->Fields('teamcasfor') - $teams->Fields('teamcasagainst');
    if ($tdDiff>0) $tdDiff="+$tdDiff";
    if ($casDiff>0) $casDiff="+$casDiff";
    $score = $result->Fields('leaguescorewin')*$teams->Fields('teamwins') +
    $result->Fields('leaguescoretie')*$teams->Fields('teamties') +
    $result->Fields('leaguescoreloss')*$teams->Fields('teamlosses') +
    $result->Fields('leaguescoretdfor')*$teams->Fields('teamtdfor') +
    $result->Fields('leaguescoretdagainst')*$teams->Fields('teamtdagainst') +
    $result->Fields('leaguescorecasfor')*$teams->Fields('teamcasfor') +
    $result->Fields('leaguescorecasagainst')*$teams->Fields('teamcasagainst');
    if (!$xml) {
      $teamArray['x'.$teams->Fields('teamid')] =
      '<tr bgcolor="#ffffff">'
        .'<td>'.pnVarPrepForDisplay($teams->Fields('coachname')).'</td>'
        .'<td>'.$teamlink.'</td>'
        .'<td>'.pnVarPrepForDisplay($teams->Fields('race')).'</td>'
        .'<td>'.pnVarPrepForDisplay($teams->Fields('teamrating')).'</td>'
        .'<td>'.pnVarPrepForDisplay($teams->Fields('teamwins')).'/'.$teams->Fields('teamties').'/'.$teams->Fields('teamlosses').'</td>'
        .'<td>'.pnVarPrepForDisplay($tdDiff).' ('.$teams->Fields('teamtdfor').'-'.$teams->Fields('teamtdagainst').')</td>'
        .'<td>'.pnVarPrepForDisplay($casDiff).' ('.$teams->Fields('teamcasfor').'-'.$teams->Fields('teamcasagainst').')</td>'
        .'<td>'.pnVarPrepForDisplay($score).'</td>'
        .'</tr>'."\n";
    }
    else {
      $teamArray['x'.$teams->Fields('teamid')] = '
        <team>
        <type>External</type>
        <name>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</name>
        <url>'.pnVarPrepForDisplay($teams->Fields('teamurl')).'</url>
        <coach>'.pnVarPrepForDisplay($teams->Fields('coachname')).'</coach>
        <race>'.$teams->Fields('race').'</race>
        <rating>'.$teams->Fields('teamrating').'</rating>
        <wins>'.$teams->Fields('teamwins').'</wins>
        <ties>'.$teams->Fields('teamties').'</ties>
        <losses>'.$teams->Fields('teamlosses').'</losses>
        <seasonwins>'.($teams->Fields('teamwins')-$teams->Fields('prewin')).'</seasonwins>
        <seasonties>'.($teams->Fields('teamties')-$teams->Fields('pretie')).'</seasonties>
        <seasonlosses>'.($teams->Fields('teamlosses')-$teams->Fields('preloss')).'</seasonlosses>
        <tdfor>'.$teams->Fields('teamtdfor').'</tdfor>
        <tdagainst>'.$teams->Fields('teamtdagainst').'</tdagainst>
        <seasontdfor>'.($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')).'</seasontdfor>
        <seasontdagainst>'.($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).'</seasontdagainst>
        <casfor>'.$teams->Fields('teamcasfor').'</casfor>
        <casagainst>'.$teams->Fields('teamcasagainst').'</casagainst>
        <seasoncasfor>'.($teams->Fields('teamcasfor')-$teams->Fields('precasfor')).'</seasoncasfor>
        <seasoncasagainst>'.($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).'</seasoncasagainst>
        <score>'.$score.'</score>
        </team>
        ';
    }
    $scores['x'.$teams->Fields('teamid')] = $score * 10000 + $teams->Fields('teamtdfor') - $teams->Fields('teamtdagainst');
  }

  arsort($scores);

  foreach ($scores as $team=>$score) {
    echo $teamArray[$team];
  }

  if (!$xml)
  echo '</table>';

  if ($xml) {
    echo '</league>';
  }
  else if ($print) {
  }
  else {
    if ($isOwner) {
      echo '<div align="center">'
        .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'print', array('l' => $result->Fields('leaguename'), 'u' => $owner))).'">Printable</a> &bull; '
        .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'config', array('l' => $result->Fields('leagueid')))).'">Configuration</a> &bull; '
        .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'members', array('l' => $result->Fields('leagueid')))).'">Edit Members</a> &bull; '
        .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'delete', array('l' => $result->Fields('leagueid')))).'">Delete League</a> &bull; '
        .'<a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'advanced', array('l' => $result->Fields('leagueid')))).'">Advanced</a>'
        .'</div>';
    }
    CloseTable();
    include 'footer.php';
  }
  return true;
}

function Stars_league_main($args) {
  $dbconn =& pnDBGetConn(true);
  $u = pnVarCleanFromInput('u');

  if (strlen($u) > 0) {
    $u = pnUserGetIDFromName($u);
    if (!$u) {
      displayError('No such user');
    }
    $owner = pnUserGetVar('uname', $u);
  }
  else {
    checkLogin();
    $u = pnUserGetVar('uid');
    $owner = pnUserGetVar('uname');
  }
  include 'header.php';
  OpenTable();

  echo '<div style="font-size: 2em;">Quick League Tracker (QUILT)</div>';

  echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
    .'<tr><th bgcolor="#D9D8D0" colspan="2">'.pnVarPrepForDisplay(poss($owner)).' leagues</th></tr>'
    .'<tr>'
    .'<th bgcolor="#D9D8D0">League Name</th>'
    .'<th bgcolor="#D9D8D0">Members</th>'
    .'</tr>';
  $sql = "SELECT leaguename, leagueid FROM nuke_stars_league WHERE leaguecommissionerid=".pnVarPrepForStore($u)." ORDER BY leaguename";
  $result = $dbconn->Execute($sql);
  for ( ; !$result->EOF; $result->moveNext() ) {
    $sql = "SELECT count(*) as count FROM nuke_stars_leaguemember lm, nuke_stars_team WHERE ".pnVarPrepForStore($result->Fields('leagueid'))."=lm.leagueid AND lm.teamid=nuke_stars_team.teamid";
    $count = $dbconn->Execute($sql);
    /*if($u == pnUserGetVar('uid'))
      $link = pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename')));
    else*/
      $link = pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename'), 'u' => pnUserGetVar('uname', $u)));

    echo '<tr>'
      .'<td bgcolor="#f8f7ee"><a href="'.pnVarPrepForDisplay($link).'">'.pnVarPrepForDisplay($result->Fields('leaguename')).'</a></td>'
      .'<td bgcolor="#f8f7ee" align="center">'.pnVarPrepForDisplay($count->Fields('count')+0).'</td>'
      .'</tr>';
  }

  echo '</table>';
  if ($u==pnUserGetVar('uid'))
  echo '<div align="center"><a href="'.pnVarPrepForDisplay(pnModURL('Stars', 'league', 'create')).'">Create League</a></div>';

  CloseTable();
  OpenTable();

  echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
    .'<tr><th bgcolor="#D9D8D0" colspan="2">Leagues in which '.pnVarPrepForDisplay($owner).' is a member</th></tr>'
    .'<tr>'
    .'<th bgcolor="#D9D8D0">League Name</th>'
    .'<th bgcolor="#D9D8D0">Members</th>'
    .'</tr>';
  $sql = "SELECT l.leaguename, l.leagueid, l.leaguecommissionerid FROM nuke_stars_league l, nuke_stars_team t, nuke_stars_leaguemember lm WHERE l.leagueid=lm.leagueid and lm.teamid = t.teamid and t.coachid = ".pnVarPrepForStore($u)." ORDER BY leaguename";
  $result = $dbconn->Execute($sql);
  for ( ; !$result->EOF; $result->moveNext() ) {
    $sql = "SELECT count(*) as count FROM nuke_stars_leaguemember lm, nuke_stars_team WHERE ".pnVarPrepForStore($result->Fields('leagueid'))."=lm.leagueid AND lm.teamid=nuke_stars_team.teamid";
    $count = $dbconn->Execute($sql);
    $link = pnModURL('Stars', 'league', 'view', array('l' => $result->Fields('leaguename'), 'u' => pnUserGetVar('uname', $result->Fields('leaguecommissionerid'))));

    echo '<tr>'
      .'<td bgcolor="#f8f7ee"><a href="'.pnVarPrepForDisplay($link).'">'.pnVarPrepForDisplay($result->Fields('leaguename')).'</a></td>'
      .'<td bgcolor="#f8f7ee" align="center">'.pnVarPrepForDisplay($count->Fields('count')+0).'</td>'
      .'</tr>';
  }

  echo '</table>';

  CloseTable();
  include 'footer.php';
  return true;
}
?>
