<?
pnRedirect(pnModURL('Stars', 'league'));
exit;

$isAdmin = pnSecAuthAction(0, 'NAF::', '::', ACCESS_ADMIN);
$isLoggedIn = pnUserLoggedIn();
$uid = pnUserGetVar('uid');
$uname = pnUserGetVar('uname');

function poss($name) {
  if ($name{strlen($name)-1}=='s')
  return $name."'";
  else
  return $name."'s";
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
  global $isLoggedIn;

  if ($isLoggedIn)
  return;

  displayError('You must be logged in to manage leagues.');
}



list($op, $l, $u) = pnVarCleanFromInput('op', 'l', 'u');

if (strlen($u) > 0) {
  $user = $dbconn->execute("SELECT pn_uid, pn_uname FROM nuke_users WHERE pn_uname='".pnVarPrepForStore($u)."'");
  if ($user->EOF) {
    echo "<html><head><title>Oops</title></head><body bgcolor=\"#ffffff\">No such user</body></html>";
    exit;
  }
  $u = $user->Fields('pn_uid');
  $owner = $user->Fields('pn_uname');
}
else {
  $u = $uid;
  $owner = $uname;
}

switch ($op) {
  case 'advanced': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    include 'header.php';
    OpenTable();
    echo "<div><a href=\"/naf.php?page=league&op=view&l=".pnVarPrepForDisplay($league->Fields('leaguename'))."\">Back to league</a></div>";

    echo '<div style="font-size: 2em;">Advanced QUILTing</div>';

    echo '
    <p>
    The QUILT system allows you to include the league table on your own site
    by providing the league information in XML format.
    </p>

    <p>
    The XML data for '.pnVarPrepForDisplay($league->Fields('leaguename')).' can be found at
    <a href="naf.php?page=league&amp;op=print&amp;l='.pnVarPrepForDisplay($league->Fields('leaguename')).'&amp;xml=1&amp;u='.pnVarPrepForDisplay($uname).'">naf.php?page=league&amp;op=print&amp;l='.pnVarPrepForDisplay($league->Fields('leaguename')).'&amp;xml=1&amp;u='.pnVarPrepForDisplay($uname).'</a>.
    </p>

    <p>
    For an example of how to use it we have created an example written in PHP which you may
    use and modify for your own purposes.<br />
    <a href="/files/LeagueParser.php">Download the League Parser example here</a>.
    </p>
    ';

    CloseTable();
    include 'footer.php';
    break;
  }
  case 'editteam': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    list($t, $win, $tie, $loss, $tdfor, $tdagainst, $casfor, $casagainst) = pnVarCleanFromInput('t', 'win', 'tie', 'loss', 'tdfor', 'tdagainst', 'casfor', 'casagainst');
    $t+=0;
    $lm = $dbconn->execute("SELECT t.*, lm.* FROM naf_leaguemember lm LEFT JOIN naf_team t USING (teamid) WHERE lm.leagueid=".pnVarPrepForStore($lid)." AND lm.teamid=".pnVarPrepForStore($t));

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

      $query = "UPDATE naf_leaguemember lm, naf_team t SET prewin=".pnVarPrepForStore($win).", pretie=".pnVarPrepForStore($tie).", preloss=".pnVarPrepForStore($loss).", "
      ."pretdfor=".pnVarPrepForStore($tdfor).", pretdagainst=".pnVarPrepForStore($tdagainst).", precasfor=".pnVarPrepForStore($casfor).", precasagainst=".pnVarPrepForStore($casagainst)." "
      ."WHERE lm.leagueid=".pnVarPrepForStore($lid)." AND lm.teamid=".pnVarPrepForStore($t);

      $dbconn->execute($query);

      pnRedirect("/naf.php?page=league&op=members&l=".$lid);
    }
    else {
      include 'header.php';
      OpenTable();
      echo "<div><a href=\"/naf.php?page=league&op=members&l=".$lid."\">Back to member list</a></div>";

      $wins = $lm->Fields('teamwins') - $lm->Fields('prewin');
      $ties = $lm->Fields('teamties') - $lm->Fields('pretie');
      $losses = $lm->Fields('teamlosses') - $lm->Fields('preloss');
      $tdfor = $lm->Fields('teamtdfor') - $lm->Fields('pretdfor');
      $tdagainst = $lm->Fields('teamtdagainst') - $lm->Fields('pretdagainst');
      $casfor = $lm->Fields('teamcasfor') - $lm->Fields('precasfor');
      $casagainst = $lm->Fields('teamcasagainst') - $lm->Fields('precasagainst');

      function b($num) {
        return '<span style="display: inline; background: #cccccc; border: solid gray 1px; padding-left: 1em; padding-right: 1em;">'.pnVarPrepForDisplay($num).'</span>';
      }

      echo '<div style="font-size: 2em;">Lifetime record for '.pnVarPrepForDisplay($lm->Fields('teamname')).'</div>';
      echo '<table border="0">'
      .'<tr align="right"><th align="left">Record</th><td>Wins '.b($lm->Fields('teamwins')).'</td>'
      .'<td>Ties '.b($lm->Fields('teamties')).'</td>'
      .'<td>Losses '.b($lm->Fields('teamlosses')).'</td></tr>'
      .'<tr align="right"><th align="left">Touchdowns</th><td>For '.b($lm->Fields('teamtdfor')).'</td><td colspan="2">Against '.b($lm->Fields('teamtdagainst')).'</td></tr>'
      .'<tr align="right"><th align="left">Casualties</th><td>For '.b($lm->Fields('teamcasfor')).'</td><td colspan="2">Against '.b($lm->Fields('teamcasagainst')).'</td></tr>'
      .'</table><br />';

      echo '<div style="font-size: 2em;">Seasonal record</div>';
      echo '<form action="naf.php" method="post">'
      .'<input type="hidden" name="page" value="league" />'
      .'<input type="hidden" name="op" value="editteam" />'
      .'<input type="hidden" name="l" value="'.$lid.'" />'
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

    break;
  }
  case 'clearrecords': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    if ($confirm==1) {
      $dbconn->execute("UPDATE naf_externalteam SET teamwins=0, teamties=0, teamlosses=0, teamtdfor=0, teamtdagainst=0, "
      ."teamcasfor=0, teamcasagainst=0 WHERE leagueid=".pnVarPrepForStore($lid));
      $dbconn->execute("UPDATE naf_leaguemember lm, naf_team t SET prewin=teamwins, pretie=teamties, preloss=teamlosses, "
      ."pretdfor=teamtdfor, pretdagainst=teamtdagainst, precasfor=teamcasfor, precasagainst=teamcasagainst "
      ."WHERE lm.teamid=t.teamid AND lm.leagueid=".pnVarPrepForStore($lid));
      pnRedirect("/naf.php?page=league&op=view&l=".pnVarPrepForDisplay($league->Fields('leaguename')));
    }
    else {
      include 'header.php';
      OpenTable();

      echo '<div style="font-size: 2em;">Are you sure you want to clear the seasonal records for \''.pnVarPrepForDisplay($league->Fields('leaguename')).'\'?</div>'
      .'<br />'
      .'Doing so will reset the seasonal record of the teams in the league, showing them as having played no games. '
      .'No actual game records will be affected. While this is not an irreversible action, you will need to set up the previous seasonal records manually '
      .'should you decide to go ahead and clear the current ones.<br />'
      .'<br />'
      .'<a href="naf.php?page=league&op=members&l='.pnVarPrepForDisplay($league->Fields('leaguename')).'">No, do not clear the seasonal records</a><br /><br />'
      .'<a href="naf.php?page=league&op=clearrecords&l='.$lid.'&confirm=1">Yes, clear the seasonal records</a>';

      CloseTable();
      include 'footer.php';
    }

    break;
  }
  case 'config': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    list($name, $win, $tie, $loss, $tdfor, $tdagainst, $casfor, $casagainst) =
    pnVarCleanFromInput('name', 'win', 'tie', 'loss', 'tdfor', 'tdagainst', 'casfor', 'casagainst');
    $win+=0; $tie+=0; $loss+=0;
    $tdfor+=0; $tdagainst+=0; $casfor+=0; $casagainst+=0;
    if (strlen($name) > 0) {
      $dbconn->execute("UPDATE naf_league SET leaguename='".pnVarPrepForStore($name)."', leaguescorewin=".pnVarPrepForStore($win).", leaguescoretie=".pnVarPrepForStore($tie).", leaguescoreloss=".pnVarPrepForStore($loss).", "
      ."leaguescoretdfor=".pnVarPrepForStore($tdfor).", leaguescoretdagainst=".pnVarPrepForStore($tdagainst).", leaguescorecasfor=".pnVarPrepForStore($casfor).", leaguescorecasagainst=".pnVarPrepForStore($casagainst)." "
      ."WHERE leagueid=".pnVarPrepForStore($lid));
      pnRedirect("/naf.php?page=league&op=view&l=".pnVarPrepForDisplay($name));
    }
    else {
      include 'header.php';
      OpenTable();
      echo "<div><a href=\"/naf.php?page=league&op=view&l=".pnVarPrepForDisplay($league->Fields('leaguename'))."\">Back to league</a></div>";

      echo '<form method="post" action="naf.php">'
      .'<input type="hidden" name="page" value="league" />'
      .'<input type="hidden" name="op" value="config" />'
      .'<input type="hidden" name="l" value="'.pnVarPrepForDisplay($l).'" />'
      .'<table border="0">'
      .'<tr><th colspan="4" align="left">League Name</th></tr>'
      .'<tr><td colspan="4"><input type="text" name="name" value="'.pnVarPrepForDisplay($league->Fields('leaguename')).'" /></td></tr>'
      .'<tr><th colspan="4">Scoring</th></tr>'
      .'<tr align="right"><th align="left">Result</th><td>Win <input type="text" size="3" name="win" value="'.$league->Fields('leaguescorewin').'" /></td>'
      .'<td>Tie <input type="text" size="3" name="tie" value="'.$league->Fields('leaguescoretie').'" /></td>'
      .'<td>Loss <input type="text" size="3" name="loss" value="'.$league->Fields('leaguescoreloss').'" /></td></tr>'
      .'<tr align="right"><th align="left">Touchdowns</th><td>For <input size="3" type="text" name="tdfor" value="'.$league->Fields('leaguescoretdfor').'" /></td><td colspan="2">Against <input size="3" type="text" name="tdagainst" value="'.$league->Fields('leaguescoretdagainst').'" /></td></tr>'
      .'<tr align="right"><th align="left">Causalties</th><td>For <input size="3" type="text" name="casfor" value="'.$league->Fields('leaguescorecasfor').'" /></td><td colspan="2">Against <input size="3" type="text" name="casagainst" value="'.$league->Fields('leaguescorecasagainst').'" /></td></tr>'
      .'</table>'
      .'<input type="submit" value="Update" />'
      .'</form>';

      CloseTable();
      include 'footer.php';
    }

    break;
  }
  case 'addmember': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    list($coach, $t) = pnVarCleanFromInput('c', 't');
    $t = trim($t);
    if ($t+0 == 0)
    $tid = $dbconn->getOne("SELECT teamid FROM naf_team t LEFT JOIN nuke_users nu ON (coachid=pn_uid) WHERE pn_uname='".pnVarPrepForStore($coach)."' AND teamname='".pnVarPrepForStore($t)."'");
    else {
      $tid = $t+0;
    }
    if ($tid+0 > 0)
    $dbconn->execute("INSERT INTO naf_leaguemember (leagueid, teamid) VALUES (".pnVarPrepForStore($lid).", ".pnVarPrepForStore($tid).")");
    pnRedirect("naf.php?page=league&op=members&l=$lid");
    break;
  }
  case 'delmember': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    $t = pnVarCleanFromInput('t');
    $t+=0;
    if ($t > 0)
    $dbconn->execute("DELETE FROM naf_leaguemember WHERE leagueid=".pnVarPrepForStore($lid)." AND teamid=".pnVarPrepForStore($t));
    pnRedirect("naf.php?page=league&op=members&l=$lid");
    break;
  }
  case 'addexternal': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    list($t, $coach, $wins, $ties, $losses, $tdfor, $tdagainst, $casfor, $casagainst, $name, $url, $rating, $race) =
    pnVarCleanFromInput('t', 'c', 'wins', 'ties', 'losses', 'tdfor', 'tdagainst', 'casfor', 'casagainst', 'name', 'url', 'rating', 'race');
    $t+=0;
    $wins+=0; $ties+=0; $losses+=0;
    $tdfor+=0; $tdagainst+=0; $casfor+=0; $casagainst+=0; $rating+=0;
    if (strlen($name) > 0) {
      if ($t > 0) {
        $sql = "UPDATE naf_externalteam SET coachname='".pnVarPrepForStore(trim($coach))."', teamname='".pnVarPrepForStore(trim($name))."', teamrace=".pnVarPrepForStore($race).", teamrating=".pnVarPrepForStore($rating).", "
        ."teamwins=".pnVarPrepForStore($wins).", teamlosses=".pnVarPrepForStore($losses).", teamties=".pnVarPrepForStore($ties).", teamtdfor=".pnVarPrepForStore($tdfor).", teamtdagainst=".pnVarPrepForStore($tdagainst).", "
        ."teamcasfor=".pnVarPrepForStore($casfor).", teamcasagainst=".pnVarPrepForStore($casagainst).", teamurl='".pnVarPrepForStore(trim($url))."' WHERE leagueid=".pnVarPrepForStore($lid)." AND teamid=".pnVarPrepForStore($t);
        $dbconn->execute($sql);
      }
      else {
        $sql = "INSERT INTO naf_externalteam (leagueid, coachname, teamname, teamrace, teamrating, teamwins, teamties, teamlosses, "
        ."teamtdfor, teamtdagainst, teamcasfor, teamcasagainst, teamurl) VALUES (".pnVarPrepForStore($lid).", '".pnVarPrepForStore(trim($coach))."', '".pnVarPrepForStore(trim($name))."', ".pnVarPrepForStore($race).", ".pnVarPrepForStore($rating).", "
        .pnVarPrepForStore($wins).", ".pnVarPrepForStore($ties).", ".pnVarPrepForStore($losses).", ".pnVarPrepForStore($tdfor).", ".pnVarPrepForStore($tdagainst).", ".pnVarPrepForStore($casfor).", ".pnVarPrepForStore($casagainst).", '".pnVarPrepForStore(trim($url))."')";
        $dbconn->execute($sql);
      }
      pnRedirect("naf.php?page=league&op=members&l=$lid");
    }
    else {
      include 'header.php';
      OpenTable();

      echo "<div><a href=\"/naf.php?page=league&op=view&l=".pnVarPrepForDisplay($league->Fields('leaguename'))."\">Back to league</a></div>";

      if ($t > 0) {
        $team = $dbconn->execute("SELECT * FROM naf_externalteam WHERE teamid=".pnVarPrepForStore($t));
        if ($team->Fields('leagueid') != $lid)
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

      function generateRaceOptions($race) {
        global $dbconn;
        $ret = '<select name="race">';
        $races = $dbconn->execute("SELECT raceid, name FROM naf_race ORDER BY name");
        for ( ; !$races->EOF; $races->moveNext() ) {
          $ret .= '<option value="'.pnVarPrepForDisplay($races->Fields('raceid')).'"'.($races->Fields('raceid')==$race?' selected="1"':'').'>'.pnVarPrepForDisplay($races->Fields('name')).'</option>';
        }
        $ret .= '</select>';
        return $ret;
      }

      echo '<form method="post" action="naf.php">'
      .'<input type="hidden" name="page" value="league" />'
      .'<input type="hidden" name="op" value="addexternal" />'
      .'<input type="hidden" name="l" value="'.$lid.'" />'
      .'<input type="hidden" name="t" value="'.$t.'" />'
      .'<table border="0">'
      .'<tr><th colspan="4" align="left">Team Name</th></tr>'
      .'<tr><td colspan="4"><input type="text" name="name" value="'.pnVarPrepForDisplay($name).'" /></td></tr>'
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
    break;
  }
  case 'delexternal': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    $t = pnVarCleanFromInput('t');
    if (is_numeric($t))
    $sql = "DELETE FROM naf_externalteam WHERE leagueid=".pnVarPrepForStore($lid)." AND teamid=".pnVarPrepForStore($t);
    $dbconn->execute($sql);
    pnRedirect("naf.php?page=league&op=members&l=$lid");
    break;
  }
  case 'members': {
    checkLogin();
    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    $teams = $dbconn->execute("SELECT lm.*, t.*, lm.teamid as teamid, r.name as race, pn_uname as coach FROM naf_leaguemember lm LEFT JOIN naf_team t USING(teamid) "
    ."LEFT JOIN naf_race r ON (teamrace=raceid) LEFT JOIN nuke_users nu ON (pn_uid=t.coachid) "
    ."WHERE lm.leagueid=".pnVarPrepForStore($l)." order by teamname");

    include 'header.php';
    OpenTable();
    echo "<div><a href=\"/naf.php?page=league&op=view&l=".pnVarPrepForDisplay($league->Fields('leaguename'))."\">Back to league</a></div>";
    echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
    .'<tr><th bgcolor="#D9D8D0" colspan="7">Edit members in '.pnVarPrepForDisplay($league->Fields('leaguename')).'</th></tr>'
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
      .'<td>'.pnVarPrepForDisplay($teams->Fields('coach')).'</td>'
      .'<td>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</td>'
      .'<td>'.$teams->Fields('race').'</td>'
      ."<td>".($teams->Fields('teamwins')-$teams->Fields('prewin')).'/'.($teams->Fields('teamties')-$teams->Fields('pretie')).'/'.($teams->Fields('teamlosses')-$teams->Fields('preloss'))."</td>"
      ."<td>".$tdDiff." (".($teams->Fields('teamtdfor')-$teams->Fields('pretdfor'))."-".($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).")</td>"
      ."<td>".$casDiff." (".($teams->Fields('teamcasfor')-$teams->Fields('precasfor'))."-".($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).")</td>"
      .'<td>'
      .'(<a href="naf.php?page=league&op=delmember&l='.$lid.'&t='.$teams->Fields('teamid').'">Kick</a>) '
      .'(<a href="naf.php?page=league&op=editteam&l='.$lid.'&t='.$teams->Fields('teamid').'">Edit</a>) '
      .'</td>'
      .'</tr>';
    }

    echo '</table>';
    echo '<div align="center">'
    .'Type in the coach and team names OR the team id of the team to add<br />'
    .'<form method="post" action="naf.php">'
    .'<input type="hidden" name="page" value="league" />'
    .'<input type="hidden" name="op" value="addmember" />'
    .'<input type="hidden" name="l" value="'.$lid.'" />'
    .'Coach: <input type="text" name="c" /> '
    .'Team: <input type="text" name="t" /> '
    .'<input type="submit" value="Add team" />'
    .'</form>'
    .'</div>';

    echo '<br />';

    echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
    .'<tr><th bgcolor="#D9D8D0" colspan="7">Edit external teams in '.pnVarPrepForDisplay($league->Fields('leaguename')).'</th></tr>'
    .'<tr>'
    .'<th bgcolor="#D9D8D0">Coach</th>'
    .'<th bgcolor="#D9D8D0">Team Name</th>'
    .'<th bgcolor="#D9D8D0">Race</th>'
    .'<th bgcolor="#D9D8D0">Record<br /><span style="font-size: 0.8em;">(W/T/L)</span></th>'
    .'<th bgcolor="#D9D8D0">TD Diff</th>'
    .'<th bgcolor="#D9D8D0">Cas Diff</th>'
    .'<th bgcolor="#D9D8D0">Op</th>'
    .'</tr>';

    $teams = $dbconn->execute("SELECT t.*, r.name as race FROM naf_externalteam t LEFT JOIN naf_race r ON (teamrace=raceid) WHERE leagueid=".pnVarPrepForStore($lid)." ORDER BY teamname");
    for ( ; !$teams->EOF; $teams->moveNext() ) {
      $tdDiff = $teams->Fields('teamtdfor') - $teams->Fields('teamtdagainst');
      $casDiff = $teams->Fields('teamcasfor') - $teams->Fields('teamcasagainst');
      if ($tdDiff>0) $tdDiff="+$tdDiff";
      if ($casDiff>0) $casDiff="+$casDiff";
      echo '<tr bgcolor="#ffffff">'
      .'<td>'.pnVarPrepForDisplay($teams->Fields('coachname')).'</td>'
      .'<td>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</td>'
      .'<td>'.$teams->Fields('race').'</td>'
      ."<td>".($teams->Fields('teamwins')-$teams->Fields('prewin')).'/'.($teams->Fields('teamties')-$teams->Fields('pretie')).'/'.($teams->Fields('teamlosses')-$teams->Fields('preloss'))."</td>"
      ."<td>".$tdDiff." (".($teams->Fields('teamtdfor')-$teams->Fields('pretdfor'))."-".($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).")</td>"
      ."<td>".$casDiff." (".($teams->Fields('teamcasfor')-$teams->Fields('precasfor'))."-".($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).")</td>"
      .'<td>(<a href="naf.php?page=league&op=delexternal&l='.$lid.'&t='.$teams->Fields('teamid').'">Del</a>) '
      .'(<a href="naf.php?page=league&op=addexternal&l='.$lid.'&t='.$teams->Fields('teamid').'">Edit</a>)</td>'
      .'</tr>';
    }
    echo '</table>';
    echo '<div align="center">'
    .'<a href="naf.php?page=league&op=addexternal&l='.$lid.'">Add external team</a>'
    .'<br />External teams are teams which are not tracked by STARS';

    echo '<br /><br />'
    .'<a href="naf.php?page=league&op=clearrecords&l='.$lid.'">Clear seasonal records</a>';

    echo '</div>';

    CloseTable();
    include 'footer.php';
    break;
  }
  case 'create': {
    checkLogin();

    $name = pnVarCleanFromInput('name');
    $name = trim($name);

    if (strlen($name)==0) {
      include 'header.php';
      OpenTable();
      echo '<div class="title">Create League</div>';
      echo '<form action="naf.php" method="post">';
      echo '<input type="hidden" name="page" value="league" />'
      .'<input type="hidden" name="op" value="create" />';

      echo 'Name<br /><input type="text" name="name" /><br />';
      echo '<input type="submit" value="Create League" />';
      echo '</form>';
      CloseTable();
      include 'footer.php';
    }
    else {
      $name = trim($name);
      $existing = $dbconn->execute("SELECT * FROM naf_league WHERE leaguecommissionerid=".pnVarPrepForStore($u)." AND leaguename='".pnVarPrepForStore($name)."'");
      if (!$existing->EOF) {
        echo "<html><head><title>Oops</title></head><body bgcolor=\"#ffffff\">"
        ."You can not have two leagues with the same name.<br />"
        ."<a href=\"naf.php?page=league&op=create\">Back</a>"
        ."</body></html>";
        exit;
      }
      $dbconn->execute("INSERT INTO naf_league (leaguecommissionerid, leaguename) "
      ."VALUES (".pnVarPrepForStore($u).", '".pnVarPrepForStore($name)."')");
      pnRedirect('/naf.php?page=league');
    }
    break;
  }
  case 'delete': {
    checkLogin();

    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leagueid=".pnVarPrepForStore($l));

    if ($league->EOF)
    displayError('No such league');

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;
    if (!$isOwner)
    displayError('You do not own this league');

    $confirm = pnVarCleanFromInput('confirm');
    if ($confirm==1) {
      $dbconn->execute("DELETE FROM naf_externalteam WHERE leagueid=".pnVarPrepForStore($lid));
      $dbconn->execute("DELETE FROM naf_leaguemember WHERE leagueid=".pnVarPrepForStore($lid));
      $dbconn->execute("DELETE FROM naf_league WHERE leagueid=".pnVarPrepForStore($lid));
      header('Location: naf.php?page=league');
    }
    else {
      include 'header.php';
      OpenTable();

      echo '<div style="font-size: 2em;">Are you sure you want to <span style="color: red;">Delete</span> \''.pnVarPrepForDisplay($league->Fields('leaguename')).'\'?</div>'
      .'<br />'
      .'Deleting the league will also delete all external teams and seasonal data connected to the league. This is an '
      .'irreversible action.<br />'
      .'<br />'
      .'<a href="naf.php?page=league&op=view&l='.pnVarPrepForDisplay($league->Fields('leaguename')).'">No, do not delete this league</a><br /><br />'
      .'<a href="naf.php?page=league&op=delete&l='.$lid.'&confirm=1">Yes, delete this league</a>';

      CloseTable();
      include 'footer.php';
    }
    break;
  }
  case 'print': {
    $xml = (pnVarCleanFromInput('xml') + 0)==1;
    $print = true;
  }
  case 'view': {

    $league = $dbconn->execute("SELECT * FROM naf_league WHERE leaguename='".pnVarPrepForStore($l)."' AND leaguecommissionerid=".pnVarPrepForStore($u));

    if ($league->EOF) {
      echo "<html><head><title>Oops</title></head><body bgcolor=\"#ffffff\">"
      ."No such league.<br />"
      ."</body></html>";
      exit;
    }

    $lid = $league->Fields('leagueid');

    $isOwner = $league->Fields('leaguecommissionerid') == $uid || $isAdmin;

    if ($xml) {
      header("Content-type: text/xml");
      echo '<?xml version="1.0"?>
      <league generator="QUILT" version="1.0">
      <name>'.pnVarPrepForDisplay($league->Fields('leaguename')).'</name>
      ';
    }
    else if ($print) {
      include 'header.php';
      OpenTable();
      //echo "<html><head><title>".pnVarPrepForDisplay($league->Fields('leaguename'))."</title></head><body bgcolor=\"#ffffff\">";
    }
    else {
      include 'header.php';
      OpenTable();
      echo "<div><a href=\"/naf.php?page=league".($u!=$uid?"&amp;u=$u":"")."\">Back to league list</a></div>";
    }
    if (!$xml) {
      echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
      .'<tr><th bgcolor="#D9D8D0" colspan="8">'.pnVarPrepForDisplay($league->Fields('leaguename')).'</th></tr>'
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
    $teams = $dbconn->execute("SELECT lm.*, t.*, r.name as race, pn_uname as coach FROM naf_leaguemember lm LEFT JOIN naf_team t USING(teamid) "
    ."LEFT JOIN naf_race r ON (teamrace=raceid) LEFT JOIN nuke_users nu ON (pn_uid=t.coachid) "
    ."WHERE lm.leagueid=".pnVarPrepForStore($lid));
    $teamArray = array();
    $scores = array();
    for (; !$teams->EOF; $teams->moveNext()) {
      if ($teams->Fields('coachid') == $uid)
      $link = "http://www.bloodbowl.net/naf.php?page=team&amp;op=view&amp;t=".pnVarPrepForDisplay($teams->Fields('teamid'));
      else
      $link = "http://www.bloodbowl.net/naf.php?page=team&amp;op=print&amp;t=".pnVarPrepForDisplay($teams->Fields('teamid'))."&amp;theme=Printer";
      //$link = "http://www.bloodbowl.net/teams/".urlencode($teams->Fields('coach'))."/".urlencode($teams->Fields('teamname')).".html";
      $tdDiff = ($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')) - ($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst'));
      $casDiff = ($teams->Fields('teamcasfor')-$teams->Fields('precasfor')) - ($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst'));
      if ($tdDiff>0) $tdDiff="+$tdDiff";
      if ($casDiff>0) $casDiff="+$casDiff";
      $score = $league->Fields('leaguescorewin')*($teams->Fields('teamwins')-$teams->Fields('prewin')) +
      $league->Fields('leaguescoretie')*($teams->Fields('teamties')-$teams->Fields('pretie')) +
      $league->Fields('leaguescoreloss')*($teams->Fields('teamlosses')-$teams->Fields('preloss')) +
      $league->Fields('leaguescoretdfor')*($teams->Fields('teamtdfor')-$teams->Fields('pretdfor')) +
      $league->Fields('leaguescoretdagainst')*($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')) +
      $league->Fields('leaguescorecasfor')*($teams->Fields('teamcasfor')-$teams->Fields('precasfor')) +
      $league->Fields('leaguescorecasagainst')*($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst'));
      if (!$xml) {
        $teamArray[$teams->Fields('teamid')] =
        "<tr bgcolor=\"#ffffff\">"
        ."<td>".pnVarPrepForDisplay($teams->Fields('coach'))."</td>"
        ."<td><a href=\"$link\">".pnVarPrepForDisplay($teams->Fields('teamname'))."</a></td>"
        ."<td>".$teams->Fields('race')."</td>"
        ."<td>".$teams->Fields('teamrating')."</td>"
        ."<td>".($teams->Fields('teamwins')-$teams->Fields('prewin')).'/'.($teams->Fields('teamties')-$teams->Fields('pretie')).'/'.($teams->Fields('teamlosses')-$teams->Fields('preloss'))."</td>"
        ."<td>".$tdDiff." (".($teams->Fields('teamtdfor')-$teams->Fields('pretdfor'))."-".($teams->Fields('teamtdagainst')-$teams->Fields('pretdagainst')).")</td>"
        ."<td>".$casDiff." (".($teams->Fields('teamcasfor')-$teams->Fields('precasfor'))."-".($teams->Fields('teamcasagainst')-$teams->Fields('precasagainst')).")</td>"
        ."<td>".$score."</td>"
        ."</tr>\n";
      }
      else {
        $teamArray[$teams->Fields('teamid')] = '
        <team>
        <type>STARS</type>
        <name>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</name>
        <url>'.$link.'</url>
        <coach>'.pnVarPrepForDisplay($teams->Fields('coach')).'</coach>
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

    $teams = $dbconn->execute("SELECT et.*, r.name as race FROM naf_externalteam et "
    ."LEFT JOIN naf_race r ON (teamrace=raceid) "
    ."WHERE et.leagueid=".pnVarPrepForStore($lid));
    for (; !$teams->EOF; $teams->moveNext()) {
      $teamURL = trim($teams->Fields('teamurl'));
      if (strlen($teamURL) > 0)
      $teamlink = '(Ext) <a href="http://'.$teamURL.'">'. pnVarPrepForDisplay($teams->Fields('teamname')).'</a>';
      else
      $teamlink = pnVarPrepForDisplay($teams->Fields('teamname'));

      $tdDiff = $teams->Fields('teamtdfor') - $teams->Fields('teamtdagainst');
      $casDiff = $teams->Fields('teamcasfor') - $teams->Fields('teamcasagainst');
      if ($tdDiff>0) $tdDiff="+$tdDiff";
      if ($casDiff>0) $casDiff="+$casDiff";
      $score = $league->Fields('leaguescorewin')*$teams->Fields('teamwins') +
      $league->Fields('leaguescoretie')*$teams->Fields('teamties') +
      $league->Fields('leaguescoreloss')*$teams->Fields('teamlosses') +
      $league->Fields('leaguescoretdfor')*$teams->Fields('teamtdfor') +
      $league->Fields('leaguescoretdagainst')*$teams->Fields('teamtdagainst') +
      $league->Fields('leaguescorecasfor')*$teams->Fields('teamcasfor') +
      $league->Fields('leaguescorecasagainst')*$teams->Fields('teamcasagainst');
      if (!$xml) {
        $teamArray['x'.$teams->Fields('teamid')] =
        "<tr bgcolor=\"#ffffff\">"
        ."<td>".pnVarPrepForDisplay($teams->Fields('coachname'))."</td>"
        ."<td>$teamlink</td>"
        ."<td>".$teams->Fields('race')."</td>"
        ."<td>".$teams->Fields('teamrating')."</td>"
        ."<td>".$teams->Fields('teamwins').'/'.$teams->Fields('teamties').'/'.$teams->Fields('teamlosses')."</td>"
        ."<td>".$tdDiff." (".$teams->Fields('teamtdfor')."-".$teams->Fields('teamtdagainst').")</td>"
        ."<td>".$casDiff." (".$teams->Fields('teamcasfor')."-".$teams->Fields('teamcasagainst').")</td>"
        ."<td>".$score."</td>"
        ."</tr>\n";
      }
      else {
        $teamArray['x'.$teams->Fields('teamid')] = '
        <team>
        <type>External</type>
        <name>'.pnVarPrepForDisplay($teams->Fields('teamname')).'</name>
        <url>'.$teams->Fields('teamurl').'</url>
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
      //        $scores['x'.$teams->Fields('teamid')] = $score;
    }

    arsort($scores);

    foreach ($scores as $team=>$score) {
      echo $teamArray[$team];
    }

    if (!$xml)
    echo "</table>";

    if ($xml) {
      echo '</league>';
    }
    else if ($print) {
      //echo "</body></html>";
    }
    else {
      if ($isOwner) {
        echo '<div align="center">'
        //.'<a href="/leagues/'.urlencode($uname).'/'.urlencode($league->Fields('leaguename')).'">Printable</a> &bull; '
        .'<a href="naf.php?page=league&amp;op=print&amp;l='.pnVarPrepForDisplay($league->Fields('leaguename')).'&amp;theme=Printer">Printable</a> &bull; '
        .'<a href="naf.php?page=league&amp;op=config&amp;l='.$lid.'">Configuration</a> &bull; '
        .'<a href="naf.php?page=league&amp;op=members&amp;l='.$lid.'">Edit Members</a> &bull; '
        .'<a href="naf.php?page=league&amp;op=delete&amp;l='.$lid.'">Delete League</a> &bull; '
        .'<a href="naf.php?page=league&amp;op=advanced&amp;l='.$lid.'">Advanced</a>'
        .'</div>';
      }
      CloseTable();
      include 'footer.php';
    }
    break;
  }
  default: {
    include 'header.php';
    OpenTable();

    echo '<div style="font-size: 2em;">Quick League Tracker (QUILT)</div>';

    echo '<table cellpadding="2" cellspacing="1" bgcolor="#858390" border="0" align="center">'
    .'<tr><th bgcolor="#D9D8D0" colspan="2">'.pnVarPrepForDisplay(poss($owner)).' leagues</th></tr>'
    .'<tr>'
    .'<th bgcolor="#D9D8D0">League Name</th>'
    .'<th bgcolor="#D9D8D0">Members</th>'
    .'</tr>';

    $leagues = $dbconn->execute("SELECT leaguename, sum(if(naf_team.teamid is not null,1,0)) as teams "
    ."FROM naf_league l LEFT JOIN naf_leaguemember lm USING (leagueid) LEFT JOIN naf_team USING (teamid) "
    ."WHERE leaguecommissionerid=".pnVarPrepForStore($u)." GROUP BY leaguename order by leaguename");
    for ( ; !$leagues->EOF; $leagues->moveNext() ) {
      $link = "naf.php?page=league&amp;op=view&amp;l=".pnVarPrepForDisplay($leagues->Fields('leaguename'));

      echo "<tr>"
      ."<td bgcolor=\"#f8f7ee\"><a href=\"$link\">".pnVarPrepForDisplay($leagues->Fields('leaguename'))."</a></td>"
      ."<td bgcolor=\"#f8f7ee\" align=\"center\">".pnVarPrepForDisplay($leagues->Fields('teams')+0)."</td>"
      ."</tr>";
    }

    echo '</table>';
    if ($u==$uid)
    echo '<div align="center"><a href="/naf.php?page=league&amp;op=create">Create League</a></div>';

    CloseTable();
    include 'footer.php';
    break;
  }
}
?>
