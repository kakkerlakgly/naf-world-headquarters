<?
include_once 'NAF/include/CR.php';

$tournamentK = array();
$tournamentKCached = 0;

function findEarliestDirtyGame() {
  $dbconn =& pnDBGetConn(true);

  $query = "SELECT gameid, tournamentid, newdate, homecoachid, awaycoachid, trhome, traway, rephome, repaway, racehome, raceaway, goalshome, goalsaway "
  ."FROM naf_game "
  //.", naf_tournamentcoach c1, naf_tournamentcoach c2 "
  ."WHERE dirty='TRUE' "
  //."AND c1.naftournament=ng.tournamentid "
  //."AND c1.nafcoach=ng.homecoachid "
  //."AND c2.naftournament=ng.tournamentid "
  //."AND c2.nafcoach=ng.awaycoachid "
  ."ORDER BY newdate, gameid "
  ."LIMIT 1";
  $game = $dbconn->Execute($query);

  return $game;
}

function findNextGame($coach, $race, $minId, $minDate) {
  $dbconn =& pnDBGetConn(true);

  $query =
  "(SELECT gameid, homecoachid, awaycoachid, rephome, repaway, newdate "
  ."FROM naf_game "
  ."WHERE homecoachid=$coach "
  ."AND racehome=$race "
  ."AND newdate='$minDate' "
  ."AND gameid > $minId) "
  ."UNION ALL "
  ."(SELECT gameid, homecoachid, awaycoachid, rephome, repaway, newdate "
  ."FROM naf_game "
  ."WHERE awaycoachid=$coach "
  ."AND raceaway=$race "
  ."AND newdate='$minDate' "
  ."AND gameid > $minId) "
  ."UNION ALL "
  ."(SELECT gameid, homecoachid, awaycoachid, rephome, repaway, newdate "
  ."FROM naf_game "
  ."WHERE homecoachid=$coach "
  ."AND racehome=$race "
  ."AND newdate>'$minDate') "
  ."UNION ALL "
  ."(SELECT gameid, homecoachid, awaycoachid, rephome, repaway, newdate "
  ."FROM naf_game "
  ."WHERE awaycoachid=$coach "
  ."AND raceaway=$race "
  ."AND newdate>'$minDate') "
  ."ORDER BY newdate, gameid "
  ."LIMIT 1";
  $game = $dbconn->Execute($query);

  /*if (!$game->EOF) {
    return $game;
  }

  $query =
  "(SELECT gameid, homecoachid, awaycoachid, rephome, repaway, newdate "
  ."FROM naf_game "
  ."WHERE homecoachid=$coach "
  ."AND racehome=$race "
  ."AND newdate>'$minDate') "
  ."UNION ALL "
  ."(SELECT gameid, homecoachid, awaycoachid, rephome, repaway, newdate "
  ."FROM naf_game "
  ."WHERE awaycoachid=$coach "
  ."AND raceaway=$race "
  ."AND newdate>'$minDate') "
  ."ORDER BY newdate, gameid "
  ."LIMIT 1";
  $game = $dbconn->Execute($query);*/

  return $game;
}

function findPreviousRanking($coach, $race, $minId, $minDate) {
  $dbconn =& pnDBGetConn(true);

  $query =
  "(SELECT homecoachid, rephome, repaway, trhome, traway, tournamentid, goalshome, goalsaway, newdate, gameid "
  ."FROM naf_game "
  ."WHERE homecoachid=$coach "
  ."AND racehome=$race "
  ."AND newdate='$minDate' "
  ."AND gameid < $minId) "
  ."UNION ALL "
  ."(SELECT homecoachid, rephome, repaway, trhome, traway, tournamentid, goalshome, goalsaway, newdate, gameid "
  ."FROM naf_game "
  ."WHERE awaycoachid=$coach "
  ."AND raceaway=$race "
  ."AND newdate='$minDate' "
  ."AND gameid < $minId) "
  ."UNION ALL "
  ."(SELECT homecoachid, rephome, repaway, trhome, traway, tournamentid, goalshome, goalsaway, newdate, gameid "
  ."FROM naf_game "
  ."WHERE homecoachid=$coach "
  ."AND racehome=$race "
  ."AND newdate<'$minDate') "
  ."UNION ALL "
  ."(SELECT homecoachid, rephome, repaway, trhome, traway, tournamentid, goalshome, goalsaway, newdate, gameid "
  ."FROM naf_game "
  ."WHERE awaycoachid=$coach "
  ."AND raceaway=$race "
  ."AND newdate<'$minDate') "
  ."ORDER BY newdate desc, gameid desc "
  ."LIMIT 1";
  $game = $dbconn->Execute($query);

  /*if ($game->EOF) {
    $query =
    "(SELECT homecoachid, rephome, repaway, trhome, traway, tournamentid, goalshome, goalsaway, newdate, gameid "
    ."FROM naf_game "
    ."WHERE homecoachid=$coach "
    ."AND racehome=$race "
    ."AND newdate<'$minDate') "
    ."UNION ALL "
    ."(SELECT homecoachid, rephome, repaway, trhome, traway, tournamentid, goalshome, goalsaway, newdate, gameid "
    ."FROM naf_game "
    ."WHERE awaycoachid=$coach "
    ."AND raceaway=$race "
    ."AND newdate<'$minDate') "
    ."ORDER BY newdate desc, gameid desc "
    ."LIMIT 1";
    $game = $dbconn->Execute($query);
  }*/

  if ($game->EOF) {
    $ranking=15000;
  }
  else {
    $tid = $game->Fields('tournamentid');
    $k = getTournamentK($tid);
    $homeTR = $game->Fields('trhome');
    $awayTR = $game->Fields('traway');
    $homeCR = $game->Fields('rephome');
    $awayCR = $game->Fields('repaway');
    $sd = $game->Fields('goalshome') - $game->Fields('goalsaway');

    if ($game->Fields('homecoachid') == $coach) {
      //        $ranking = $game->Fields('rephome');
      $ranking = calculateNewReputation($homeCR, $awayCR, $homeTR, $awayTR, $sd, $k);
    }
    else {
      //        $ranking = $game->Fields('repaway');
      $ranking = calculateNewReputation($awayCR, $homeCR, $awayTR, $homeTR, -$sd, $k);
    }
  }

  return $ranking;
}

function findCoachReputation($coach, $race, $minId, $minDate) {
  $dbconn =& pnDBGetConn(true);

  $game = findNextGame($coach, $race, $minId, $minDate);

  if ($game->EOF) {
    $query = "SELECT ranking FROM naf_coachranking WHERE coachid=$coach AND raceid=$race";
    $res = $dbconn->Execute($query);

    if ($res->EOF) {
      return false;
    }

    return $res->fields[0];
  }

  if ($game->Fields('homecoachid')==$coach) {
    return $game->Fields('rephome');
  }
  else {
    return $game->Fields('repaway');
  }
}

function getTournamentK($tournament) {
  $dbconn =& pnDBGetConn(true);

  // Added by juergen March 3rd, 2007
  // Tournament K Cache
  global $tournamentK;

  if (array_key_exists($tournament,$tournamentK)) {
    return $tournamentK[$tournament];
  }

  // Get the number of participants in the tournament
  $query = "SELECT count(1) from naf_tournamentcoach where naftournament=$tournament";
  $res = $dbconn->Execute($query);
  $participants = $res->fields[0];

  // Check if the tournament is a major
  $query = "SELECT tournamentmajor FROM naf_tournament WHERE tournamentid=$tournament";
  $major = $dbconn->getOne($query);

  // Limit the participant count to 60 or 30, depending on if it's a major or not
  if ($major == 'yes')
  //$participants = min($participants, 60);
  // Majors are always calculated as if 60 coaches are attending
  // see: http://bloodbowl.net/bugtracker/view.php?id=11
  $participants = 60;
  else
  $participants = min($participants, 30);


  $k = calculateKValue($participants);


  // Add the tournament K to the cache
  $tournamentK[$tournament] = $k;

  return $k;
}

function updateNextGame($game, $coach, $race, $newCR) {
  $dbconn =& pnDBGetConn(true);

  if ($game->EOF) {
    $query = "UPDATE naf_coachranking "
    ."SET ranking = $newCR "
    ."WHERE coachid=$coach "
    ."  AND raceid=$race";
    $dbconn->Execute($query);
    if ($dbconn->Affected_Rows() == 0) {
      $query = "INSERT INTO naf_coachranking "
      ."(ranking, coachid, raceid) VALUES ($newCR, $coach, $race)";
      $dbconn->Execute($query);
    }
  }
  else if ($game->Fields('homecoachid') == $coach) {
    if ($game->Fields('rephome') != $newCR) {
      $query = "UPDATE naf_game "
      ."SET rephome=$newCR, "
      ."    dirty='TRUE' "
      ."WHERE gameid=".$game->Fields('gameid');
      $dbconn->Execute($query);
    }
  }
  else {
    if ($game->Fields('repaway') != $newCR) {
      $query = "UPDATE naf_game "
      ."SET repaway=$newCR, "
      ."    dirty='TRUE' "
      ."WHERE gameid=".$game->Fields('gameid');
      $dbconn->Execute($query);
    }
  }
}

function deleteGame($gameid) {
  $dbconn =& pnDBGetConn(true);

  $query = "SELECT * FROM naf_game WHERE gameid=$gameid";
  $game = $dbconn->Execute($query);

  $homeCoach = $game->Fields('homecoachid');
  $awayCoach = $game->Fields('awaycoachid');
  $homeCR = $game->Fields('rephome');
  $awayCR = $game->Fields('repaway');
  $homeRace = $game->Fields('racehome');
  $awayRace = $game->Fields('raceaway');
  $date = $game->Fields('newdate');

  $homeNext = findNextGame($homeCoach, $homeRace, $gameid, $date);

  $awayNext = findNextGame($awayCoach, $awayRace, $gameid, $date);

  updateNextGame($homeNext, $homeCoach, $homeRace, $homeCR);

  updateNextGame($awayNext, $awayCoach, $awayRace, $awayCR);

  $query = "DELETE FROM naf_game WHERE gameid=$gameid";
  $dbconn->Execute($query);
}

function updatePreviousRankings() {
  $dbconn =& pnDBGetConn(true);

  $query = "SELECT * FROM naf_game WHERE dirty='TRUE'";
  $res = $dbconn->Execute($query);
  echo $dbconn->errorMsg();

  for ( ; !$res->EOF; $res->moveNext() ) {
    $gameid = $res->Fields('gameid');
    $prevRank1 = findPreviousRanking($res->Fields('homecoachid'), $res->Fields('racehome'),
    $gameid, $res->Fields('newdate'));
    $prevRank2 = findPreviousRanking($res->Fields('awaycoachid'), $res->Fields('raceaway'),
    $gameid, $res->Fields('newdate'));

    $query = "UPDATE naf_game SET rephome=$prevRank1, repaway=$prevRank2 WHERE gameid=$gameid";
    $dbconn->Execute($query);
  }
}

function updateRaces() {
  $dbconn =& pnDBGetConn(true);

  $query = "UPDATE naf_game ng, naf_tournamentcoach c SET ng.racehome = c.race WHERE ng.tournamentid=c.naftournament AND ng.homecoachid=c.nafcoach AND ng.racehome=0";
  $dbconn->Execute($query);

  $query = "UPDATE naf_game ng, naf_tournamentcoach c SET ng.raceaway = c.race WHERE ng.tournamentid=c.naftournament AND ng.awaycoachid=c.nafcoach AND ng.raceaway=0";
  $dbconn->Execute($query);

  $query = "SELECT gameid "
  ."FROM naf_game "
  ."WHERE racehome=0 or raceaway=0";
  $res = $dbconn->Execute($query);
  for ( ; !$res->EOF; $res->moveNext() ) {
    echo '<div style="color: red;">Game #'.$res->Fields('gameid').' is lacking race information!</div>';
  }

}

function updateRankings() {
  $dbconn =& pnDBGetConn(true);
  $game = findEarliestDirtyGame();
  if ($game->EOF) {
    return false;
  }
  $gameid=$game->Fields('gameid');
  $id = $game->Fields('tournamentid');
  $gamedate = $game->Fields('newdate');

  $homeCoach = $game->Fields('homecoachid');
  $awayCoach = $game->Fields('awaycoachid');

  $homeTR = $game->Fields('trhome');
  $awayTR = $game->Fields('traway');
  $homeCR = $game->Fields('rephome');
  $awayCR = $game->Fields('repaway');
  $homeRace = $game->Fields('racehome');
  if ($homeRace == 0)
  $homeRace = findrace($id, $homeCoach);
  $awayRace = $game->Fields('raceaway');
  if ($awayRace == 0)
  $homeRace = findrace($id, $awayCoach);

  if ($homeRace == 0 || $awayRace == 0) {
    return false;
  }

  $sd = $game->Fields('goalshome') - $game->Fields('goalsaway');

  $k = getTournamentK($id);


  $homeNewCR = calculateNewReputation($homeCR, $awayCR, $homeTR, $awayTR, $sd, $k);

  $awayNewCR = calculateNewReputation($awayCR, $homeCR, $awayTR, $homeTR, -$sd, $k);

  $homeNext = findNextGame($homeCoach, $homeRace, $gameid, $gamedate);

  $awayNext = findNextGame($awayCoach, $awayRace, $gameid, $gamedate);

  updateNextGame($homeNext, $homeCoach, $homeRace, $homeNewCR);

  updateNextGame($awayNext, $awayCoach, $awayRace, $awayNewCR);

  $query = "UPDATE naf_game SET dirty='FALSE' WHERE gameid=$gameid";
  $dbconn->Execute($query);

  $game->Free();
  $homeNext->Free();
  $awayNext->Free();

  return true;
}

function findrace($tournamentid, $coachid) {
  $dbconn =& pnDBGetConn(true);
  $query = "select race from naf_tournamentcoach where naftournament = $tournamentid and nafcoach = $coachid";
  $result = $dbconn->Execute($query);
  return $result->Fields('race');
}

function updateNewDate() {
  $dbconn =& pnDBGetConn(true);
  $query = "UPDATE naf_game SET newdate = concat( date, ' ', if( HOUR <10, '0', '' ) , HOUR , ':00:00' ) WHERE newdate = '0000-00-00 00:00:00'";
  $dbconn->Execute($query);
}
?>
