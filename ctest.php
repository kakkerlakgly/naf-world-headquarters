<?php
  include 'includes/pnAPI.php';
  pnInit();

	list($dbconn) = pnDBGetConn();
	
	
	function checkFirstGameCR() {
		global $dbconn;
		echo "Checking CR of the first matches for each coach...<br />\n";
		$query = "select gameid, homecoachid, racehome, rephome, awaycoachid, raceaway, repaway from naf_game order by date, hour, gameid";
		
		$res = $dbconn->execute($query);
		
		$coaches = array();
		$okGames = 0;
		$failGames = 0;
		for ( ; !$res->EOF; $res->moveNext() ) {
			$home = $res->Fields('homecoachid')."-".$res->Fields('racehome');
			$away = $res->Fields('awaycoachid')."-".$res->Fields('raceaway');

			if ($coaches[$home] && $coaches[$away])
				continue;
			
			$ok=true;
			if (!$coaches[$home]) {
				$coaches[$home] = true;
				if ($res->Fields('rephome') != 15000) {
					echo "Game ".$res->Fields('gameid')." erronous HOME. First game with race not CR 150.<br />\n";
					$ok=false;
					$failGames++;
				}
			}
			if (!$coaches[$away]) {
				$coaches[$away] = true;
				if ($res->Fields('repaway') != 15000) {
					echo "Game ".$res->Fields('gameid')." erronous HOME. First game with race not CR 150.<br />\n";
					if ($ok)
						$failGames++;
					$ok=false;
				}
			}
			
			if ($ok)
				$okGames++;
		}

		echo "<br />\nNumber of OK first matches: ".$okGames."<br />\n";
		echo "Number of Bad first matches: ".$failGames."<br />\n";
		echo "-------------------------------------<br />\n";
	}
	
	function checkTournamentRaces() {
		global $dbconn;
		
		echo "Checking if races match between tournaments and games...<br />\n";
		
		$query = "(
		SELECT g.tournamentid, g.gameid, c.nafcoach, racehome, c.race, 1
		FROM naf_game g
		LEFT JOIN naf_tournamentcoach c ON ( g.tournamentid = c.naftournament
		AND g.homecoachid = c.nafcoach )
		WHERE racehome <> c.race
		)
		UNION (
		
		SELECT g.tournamentid, g.gameid, c.nafcoach, raceaway, c.race, 2
		FROM naf_game g
		LEFT JOIN naf_tournamentcoach c ON ( g.tournamentid = c.naftournament
		AND g.awaycoachid = c.nafcoach )
		WHERE raceaway <> c.race
		)";
		
		$res = $dbconn->execute($query);
		
		for ( ; !$res->EOF; $res->moveNext() ) {
			echo "Mismatch in tournament ".$res->Fields('tournamentid').", Gameid "
			.$res->Fields('gameid').": ".($res->fields[5]==1?"Home":"Away")." coach (".$res->Fields('nafcoach')
			.") mismatch (g".$res->Fields('racehome').", t".$res->Fields('race').")<br />\n";
		}
		echo "-------------------------------------<br />\n";
	}
	
	checkFirstGameCR();
	checkTournamentRaces();
?>