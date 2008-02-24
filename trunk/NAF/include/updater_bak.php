<?
  include_once 'NAF/include/CR.php';

  $debugFlag = true;

  function enableDebug() {
    global $debugFlag;
    $debugFlag = true;
  }

  function debug($output) {
    global $debugFlag;

    if ($debugFlag) {
      echo $output."<br />\n";
    }
  }

  function findEarliestDirtyGame() {
    global $dbconn;

    $query = "SELECT ng.*, c1.race as c1race, c2.race as c2race "
            ."FROM naf_game ng "
            ."LEFT JOIN naf_tournamentcoach c1 ON (c1.naftournament=ng.tournamentid AND c1.nafcoach=ng.homecoachid) "
            ."LEFT JOIN naf_tournamentcoach c2 ON (c2.naftournament=ng.tournamentid AND c2.nafcoach=ng.awaycoachid) "
            ."WHERE dirty='TRUE' "
            ."AND ng.racehome<>0 AND ng.raceaway<>0 "
            ."ORDER BY date, hour, gameid "
            ."LIMIT 1";
    $game = $dbconn->Execute($query);

    debug("findEarliestDirtyGame: ".($game->EOF?"EOF":$game->Fields('gameid')." : ".$game->Fields('homecoachid')." "
         .$game->Fields('goalshome')." vs ".$game->Fields('goalsaway')." "
         .$game->Fields('awaycoachid')));

    return $game;
  }

  function findNextGame($coach, $race, $minId, $minDate, $minHour) {
    global $dbconn;

    $minHour += 0; // Workaround for tournaments lacking hour info

    $query = "SELECT gameid, homecoachid, awaycoachid, rephome, repaway "
            ."FROM naf_game "
            ."WHERE ((homecoachid=$coach AND racehome=$race) "
            ."        OR (awaycoachid=$coach AND raceaway=$race)) "
            ."  AND (date > '$minDate' OR (date='$minDate' AND hour>$minHour) OR (date='$minDate' AND hour=$minHour AND gameid > $minId)) "
            ."ORDER BY date, hour, gameid "
            ."LIMIT 1";
    $game = $dbconn->Execute($query);

    if (!$game) {
      echo $dbconn->errorMsg();
    }
    else {
      debug("findNextGame($coach, $race, $minId, $minDate, $minHour): ".($game->EOF?"EOF":$game->Fields('gameid')));
    }

    return $game;
  }

  function findPreviousRanking($coach, $race, $minId, $minDate, $minHour) {
    global $dbconn;

    $minHour += 0; // Workaround for tournaments lacking hour info

    $query = "SELECT gameid, homecoachid, awaycoachid, rephome, repaway, trhome, traway, tournamentid, goalshome, goalsaway "
            ."FROM naf_game "
            ."WHERE ((homecoachid=$coach AND racehome=$race) "
            ."        OR (awaycoachid=$coach AND raceaway=$race)) "
            ."  AND (date < '$minDate' OR (date='$minDate' AND hour<$minHour) OR (date='$minDate' AND hour=$minHour AND gameid < $minId)) "
            ."ORDER BY date desc, hour desc, gameid desc "
            ."LIMIT 1";
    $game = $dbconn->Execute($query);

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
        debug("calculateNewReputation($homeCR, $awayCR, $homeTR, $awayTR, $sd, $k)");
        $ranking = calculateNewReputation($homeCR, $awayCR, $homeTR, $awayTR, $sd, $k);
      }
      else {
//        $ranking = $game->Fields('repaway');
        debug("calculateNewReputation($homeCR, $awayCR, $homeTR, $awayTR, $sd, $k)");
        $ranking = calculateNewReputation($awayCR, $homeCR, $awayTR, $homeTR, -$sd, $k);
      }
    }

    debug("findPreviousRanking($coach, $race, $minId, $minDate, $minHour): ".$ranking);

    return $ranking;
  }

  function findCoachReputation($coach, $race, $minId, $minDate, $minHour) {
    global $dbconn;

    debug("findCoachReputation($coach, $race, $minId, $minDate, $minHour)");

    $game = findNextGame($coach, $race, $minId, $minDate, $minHour);

    if ($game->EOF) {
      $query = "SELECT ranking FROM naf_coachranking WHERE coachid=$coach AND raceid=$race";
      $res = $dbconn->Execute($query);

      if ($res->EOF) {
        debug("  Found no ranking");
        return false;
      }

      debug("  Found current ranking: ".$res->fields[0]);
      return $res->fields[0];
    }

    if ($game->Fields('homecoachid')==$coach) {
      debug("  Found ranking from game: ".$game->Fields('rephome'));
      return $game->Fields('rephome');
    }
    else {
      debug("  Found ranking from game: ".$game->Fields('repaway'));
      return $game->Fields('repaway');
    }
  }

  function getTournamentK($tournament) {
    global $dbconn;

    // Get the number of participants in the tournament
    $query = "SELECT count(1) from naf_tournamentcoach where naftournament=$tournament";
    $res = $dbconn->Execute($query);
    $participants = $res->fields[0];

    // Check if the tournament is a major
    $query = "SELECT tournamentmajor FROM naf_tournament WHERE tournamentid=$tournament";
    $major = $dbconn->getOne($query);

    // Limit the participant count to 60 or 30, depending on if it's a major or not
    if ($major == 'yes')
        $participants = min($participants, 60);
    else
        $participants = min($participants, 30);


    $k = calculateKValue($participants);

    debug("getTournamentK($tournament): calculateKValue(".$participants.") = $k");

    return $k;
  }

  function updateNextGame($game, $coach, $race, $newCR) {
    global $dbconn;

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
      debug("updateNextGame($game, $coach, $race, $newCR): Changed naf_coachranking");
    }
    else if ($game->Fields('homecoachid') == $coach) {
      if ($game->Fields('rephome') != $newCR) {
        $query = "UPDATE naf_game "
                ."SET rephome=$newCR, "
                ."    dirty='TRUE' "
                ."WHERE gameid=".$game->Fields('gameid');
        $dbconn->Execute($query);
      debug("updateNextGame(".$game->Fields('gameid').", $coach, $race, $newCR): Changed homecoach (".$game->Fields('homecoachid').").");
      }
    }
    else {
      if ($game->Fields('repaway') != $newCR) {
        $query = "UPDATE naf_game "
                ."SET repaway=$newCR, "
                ."    dirty='TRUE' "
                ."WHERE gameid=".$game->Fields('gameid');
        $dbconn->Execute($query);
      debug("updateNextGame(".$game->Fields('gameid').", $coach, $race, $newCR): Changed awaycoach (".$game->Fields('awaycoachid').").");
      }
    }
  }

  function deleteGame($gameid) {
    global $dbconn;

    $query = "SELECT * FROM naf_game WHERE gameid=$gameid";
    $game = $dbconn->Execute($query);

    $homeCoach = $game->Fields('homecoachid');
    $awayCoach = $game->Fields('awaycoachid');
    $homeCR = $game->Fields('rephome');
    $awayCR = $game->Fields('repaway');
    $homeRace = $game->Fields('racehome');
    $awayRace = $game->Fields('raceaway');
    $date = $game->Fields('date');
    $hour = $game->Fields('hour');

    $homeNext = findNextGame($homeCoach, $homeRace, $gameid, $date, $hour);
    $awayNext = findNextGame($awayCoach, $awayRace, $gameid, $date, $hour);

    updateNextGame($homeNext, $homeCoach, $homeRace, $homeCR);
    updateNextGame($awayNext, $awayCoach, $awayRace, $awayCR);

    $query = "DELETE FROM naf_game WHERE gameid=$gameid";
    $dbconn->Execute($query);
  }

  function updatePreviousRankings() {
    global $dbconn;

    $query = "SELECT * FROM naf_game WHERE dirty='TRUE'";
    $res = $dbconn->Execute($query);
echo $dbconn->errorMsg();

    for ( ; !$res->EOF; $res->moveNext() ) {
      $gameid = $res->Fields('gameid');
      $prevRank1 = findPreviousRanking($res->Fields('homecoachid'), $res->Fields('racehome'),
                                       $gameid, $res->Fields('date'), $res->Fields('hour'));
      $prevRank2 = findPreviousRanking($res->Fields('awaycoachid'), $res->Fields('raceaway'),
                                       $gameid, $res->Fields('date'), $res->Fields('hour'));

      $query = "UPDATE naf_game SET rephome=$prevRank1, repaway=$prevRank2 WHERE gameid=$gameid";
      $dbconn->Execute($query);
echo $dbconn->errorMsg();
    }
  }

  function updateRaces() {
    global $dbconn;

    $query = "SELECT g.*, c1.race as hrace, c2.race as arace "
            ."FROM naf_game g "
            ."LEFT JOIN naf_tournamentcoach c1 on (g.tournamentid=c1.naftournament AND g.homecoachid=c1.nafcoach) "
            ."LEFT JOIN naf_tournamentcoach c2 on (g.tournamentid=c2.naftournament AND g.awaycoachid=c2.nafcoach) "
            ."WHERE racehome=0 or raceaway=0";
    $res = $dbconn->Execute($query);
echo $dbconn->errorMsg();


    for ( ; !$res->EOF; $res->moveNext() ) {
      if ($res->Fields('hrace')==0 || $res->Fields('arace')==0)
        echo "<div style=\"color: red;\">Game #".$res->Fields('gameid')." is lacking race information!</div>";
      $query = "UPDATE naf_game SET racehome=".$res->Fields('hrace').", raceaway=".$res->Fields('arace')." "
              ."WHERE gameid=".$res->Fields('gameid');
      $dbconn->Execute($query);
echo $dbconn->errorMsg();
    }
  }

  function updateRankings() {
    global $dbconn;

    debug("<br>");
    $game = findEarliestDirtyGame();
    if ($game->EOF) {
      return false;
    }
    $gameid=$game->Fields('gameid');
    $id = $game->Fields('tournamentid');
    $gamedate = $game->Fields('date');
    $gamehour = $game->Fields('hour');

    $homeCoach = $game->Fields('homecoachid');
    $awayCoach = $game->Fields('awaycoachid');

    $homeTR = $game->Fields('trhome');
    $awayTR = $game->Fields('traway');
    $homeCR = $game->Fields('rephome');
    $awayCR = $game->Fields('repaway');
    $homeRace = $game->Fields('racehome');
    if ($homeRace == 0)
      $homeRace = $game->Fields('c1race');
    $awayRace = $game->Fields('raceaway');
    if ($awayRace == 0)
      $awayRace = $game->Fields('c2race');

    if ($homeRace == 0 || $awayRace == 0) {
      debug("Empty race detected!");
      return false;
    }

    $sd = $game->Fields('goalshome') - $game->Fields('goalsaway');

    $k = getTournamentK($id);

    $homeNewCR = calculateNewReputation($homeCR, $awayCR, $homeTR, $awayTR, $sd, $k);
    $awayNewCR = calculateNewReputation($awayCR, $homeCR, $awayTR, $homeTR, -$sd, $k);

    debug("Rating change for $homeCoach: $homeCR -> $homeNewCR");
    debug("Rating change for $awayCoach: $awayCR -> $awayNewCR");

    $homeNext = findNextGame($homeCoach, $homeRace, $gameid, $gamedate, $gamehour);
    $awayNext = findNextGame($awayCoach, $awayRace, $gameid, $gamedate, $gamehour);

    updateNextGame($homeNext, $homeCoach, $homeRace, $homeNewCR);
    updateNextGame($awayNext, $awayCoach, $awayRace, $awayNewCR);

    $query = "UPDATE naf_game SET dirty='FALSE' WHERE gameid=$gameid";
    $dbconn->Execute($query);
    debug("Marked game $gameid as clean");

    return true;
  }

?>
