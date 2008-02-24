<?
require_once 'NAF/include/updater.php';

function appendToUnverified($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1, 
							$s2, $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $gate, $tournament_id, $date, $hour, $order = 0) {
 
	global $dbconn;
	$initial_reputation= 15000;
	
	if ($order == 0){
		//Get current number of matches for this tournament
		$query = "SELECT count(1) "
		."FROM naf_unverified_game "
		."WHERE tournamentid=$tournament_id ";
		$count = $dbconn->Execute($query);
		$order = $count->fields[0] + 1;
	}
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

        $rep1 = findCoachReputation($c1, $r1, 0, $date, $hour);
        if ($rep1 == false) {
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
        }

	//Check for reputation for coach/race #2
        $rep2 = findCoachReputation($c2, $r2, 0, $date, $hour);
        if ($rep2 == false) {
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
	}

	// Enter new report
     $sqlupdate = "INSERT INTO naf_unverified_game "
                  ."(tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
                  ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour, game_order) "
                  ."VALUES ("
                             .$tournament_id.","
                             .$c1.","
                             .$c2.","
                             .$r1.","
                             .$r2.","
                             .$tr1.","
                             .$tr2.","
                             .$rep1.","
                             .$rep2.","
                             .$s1.","
                             .$s2.","
                             .($bh1 + 0).","
                             .($bh2 + 0).","
                             .($si1 + 0).","
                             .($si2 + 0).","
                             .($rip1 + 0).","
                             .($rip2 + 0).","
                             .($gate + 0).","
                             .($w1 + 0).","
                             .($w2 + 0).","
                             ."'$date' ,"
                             ."$hour ,"
							 .$order
							 .")";
      $result = mysql_query($sqlupdate);
}

function deleteFromUnverified($gameid, $tournament_id){
	// Expects $gameid and $tournament_id, removes game with id matching gameid and corrects the game_order of any subsequent games
	global $dbconn;
	// Get count from game to be deleted
	$query = "SELECT count(1) "
    ."FROM naf_unverified_game "
	."WHERE gameid=$gameid ";
	$result = $dbconn->Execute($query);
	$game_order = $result->fields[0];
	// Delete match
	$sqlupdate = "DELETE FROM naf_unverified_game where gameid = $gameid";
    $result = $dbconn->Execute($sqlupdate);
	// Re-order current matches
	$sqlupdate = "UPDATE naf_unverified_game "
                 ."SET game_order=game_order - 1 "
                 ."WHERE game_order > $game_order "
                 ."   AND tournamentid = $tournament_id";
    $result = $dbconn->Execute($sqlupdate);
}

function insertIntoUnverified($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1, 
							$s2, $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $gate, $tournament_id,$game_to_insert_after, $date, $hour){
	global $dbconn;
    $game_to_insert_after = $game_to_insert_after - 1;
    //Re-order current matches
	$sqlupdate = "UPDATE naf_unverified_game "
                 ."SET game_order=game_order + 1 "
                 ."WHERE game_order > $game_to_insert_after "
                 ."   AND tournamentid = $tournament_id";
    $result = $dbconn->Execute($sqlupdate);
	//Insert new match
	appendToUnverified($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1, 
					   $s2, $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $gate, $tournament_id, $date, $hour, ($game_to_insert_after + 1));
}

function outputFinal($tournament_id){
    global $dbconn;
	
	//Copy rows for this tournament to final game table.
	$sqlupdate = "INSERT INTO naf_game "
                 ."(tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
                 ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour) "
                 ."SELECT  "
				 ."tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
                 ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour "
				 ."FROM naf_unverified_game "
				 ."WHERE tournamentid = $tournament_id "
				 ."ORDER BY game_order";
	$result = $dbconn->Execute($sqlupdate);
	
	//Delete rows from temp table
	$sqlupdate = "DELETE FROM naf_unverified_game "
				 ."WHERE tournamentid = $tournament_id ";
	$result = $dbconn->Execute($sqlupdate);

}
?>
