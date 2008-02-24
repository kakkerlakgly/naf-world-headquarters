<?
require_once 'modules/NAF/pnupdaterapi.php';
/*function findNextGame($coach, $race, $minId, $minDate, $minHour) {
  $dbconn =& pnDBGetConn(true);

  $minHour += 0; // Workaround for tournaments lacking hour info

  $query = "SELECT gameid, homecoachid, awaycoachid, rephome, repaway "
          ."FROM naf_game "
          ."WHERE ((homecoachid=".pnVarPrepForStore($coach)." AND racehome=".pnVarPrepForStore($race).") "
          ."        OR (awaycoachid=".pnVarPrepForStore($coach)." AND raceaway=".pnVarPrepForStore($race).")) "
          ."  AND (date > '".pnVarPrepForStore($minDate)."' OR (date='".pnVarPrepForStore($minDate)."' AND hour>".pnVarPrepForStore($minHour).") OR (date='".pnVarPrepForStore($minDate)."' AND hour=".pnVarPrepForStore($minHour)." AND gameid > ".pnVarPrepForStore($minId).")) "
          ."ORDER BY date, hour, gameid "
          ."LIMIT 1";
  $game = $dbconn->Execute($query);

  if (!$game) {
    echo $query;
    echo $dbconn->errorMsg();
  }

  return $game;
}*/

/*function findCoachReputation($coach, $race, $minId, $minDate, $minHour) {
  $dbconn =& pnDBGetConn(true);

  $game = findNextGame($coach, $race, $minId, $minDate, $minHour);

  if ($game->EOF) {
    $query = "SELECT ranking FROM naf_coachranking WHERE coachid=".pnVarPrepForStore($coach)." AND raceid=".pnVarPrepForStore($race);
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
}*/

function appendToUnverified($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1,
$s2, $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $gate, $tournament_id, $date, $hour, $order = 0) {

  $dbconn =& pnDBGetConn(true);
  $initial_reputation= 15000;

  if ($order == 0){
    //Get current number of matches for this tournament
    $query = "SELECT count(1) FROM naf_unverified_game WHERE tournamentid=".pnVarPrepForStore($tournament_id);
    $count = $dbconn->Execute($query);
    $order = $count->fields[0] + 1;
  }
  //Get race for coach 1
  if (!$r1){
    $query = "SELECT race FROM naf_tournamentcoach WHERE naftournament=".pnVarPrepForStore($tournament_id)." AND nafcoach=".pnVarPrepForStore($c1);
    $race = $dbconn->Execute($query);
    $r1 = $race->fields[0];
  }
  //Get race for coach 2
  if (!$r2){
    $query = "SELECT race FROM naf_tournamentcoach WHERE naftournament=".pnVarPrepForStore($tournament_id)." AND nafcoach=".pnVarPrepForStore($c2);
    $race = $dbconn->Execute($query);
    $r2 = $race->fields[0];
  }

  $rep1 = findCoachReputation($c1, $r1, 0, $date.' '.($hour<10 ? '0' : '').$hour.':00:00');
  if ($rep1 == false) {
    //No reputation for this coach/race. Insert new record
    $qry = "INSERT INTO naf_coachranking "
    ."(coachid,raceid,ranking) "
    ."VALUES ("
    .pnVarPrepForStore($c1).","
    .pnVarPrepForStore($r1).","
    .pnVarPrepForStore($initial_reputation).")";
    $result = $dbconn->Execute($qry);
    if (!$result) {
      echo $qry;
      echo $dbconn->errorMsg();
    }
    $rep1=$initial_reputation;
  }

  //Check for reputation for coach/race #2
  $rep2 = findCoachReputation($c2, $r2, 0, $date, $hour);
  if ($rep2 == false) {
    //No reputation for this coach/race. Insert new record
    $qry = "INSERT INTO naf_coachranking "
    ."(coachid,raceid,ranking) "
    ."VALUES ("
    .pnVarPrepForStore($c2).","
    .pnVarPrepForStore($r2).","
    .pnVarPrepForStore($initial_reputation).")";
    $result = $dbconn->Execute($qry);
    if (!$result) {
      echo $qry;
      echo $dbconn->errorMsg();
    }
    $rep2=$initial_reputation;
  }

  // Enter new report
  $qry = "INSERT INTO naf_unverified_game "
  ."(tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
  ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour, game_order) "
  ."VALUES (".pnVarPrepForStore($tournament_id).","
  .pnVarPrepForStore($c1).","
  .pnVarPrepForStore($c2).","
  .pnVarPrepForStore($r1).","
  .pnVarPrepForStore($r2).","
  .pnVarPrepForStore($tr1).","
  .pnVarPrepForStore($tr2).","
  .pnVarPrepForStore($rep1).","
  .pnVarPrepForStore($rep2).","
  .pnVarPrepForStore($s1).","
  .pnVarPrepForStore($s2).","
  .pnVarPrepForStore($bh1 + 0).","
  .pnVarPrepForStore($bh2 + 0).","
  .pnVarPrepForStore($si1 + 0).","
  .pnVarPrepForStore($si2 + 0).","
  .pnVarPrepForStore($rip1 + 0).","
  .pnVarPrepForStore($rip2 + 0).","
  .pnVarPrepForStore($gate + 0).","
  .pnVarPrepForStore($w1 + 0).","
  .pnVarPrepForStore($w2 + 0).","
  ."'".pnVarPrepForStore($date)."' ,"
  .pnVarPrepForStore($hour).","
  .pnVarPrepForStore($order).")";
  $result = $dbconn->Execute($qry);
  if (!$result) {
    echo $qry;
    echo $dbconn->errorMsg();
  }
}

function insertIntoUnverified($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1, 
							$s2, $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $gate, $tournament_id,$game_to_insert_after, $date, $hour){
	$dbconn =& pnDBGetConn(true);
    $game_to_insert_after = $game_to_insert_after - 1;
    //Re-order current matches
	$sqlupdate = "UPDATE naf_unverified_game "
                 ."SET game_order=game_order + 1 "
                 ."WHERE game_order > ".pnVarPrepForStore($game_to_insert_after)
                 ."   AND tournamentid = ".pnVarPrepForStore($tournament_id);
    $result = $dbconn->Execute($sqlupdate);
	//Insert new match
	appendToUnverified($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1, 
					   $s2, $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $gate, $tournament_id, $date, $hour, ($game_to_insert_after + 1));
}

?>
