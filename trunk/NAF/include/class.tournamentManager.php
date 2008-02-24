<?
include_once('class.Tournament.php');
include_once('class.TournamentRound.php');
include_once('class.TournamentGame.php');

class TournamentManager {
	var $tournaments;
	var $numberOfTournaments = 0;
	var $generateOutput = false;

	function TournamentManager() {
		$this->generateOutput = false;
	}

	function loadTournaments() {
		global $dbconn;
		
		
		$tourneyList = $this->getTournamentList();
		$roundsList = $this->getTournamentRounds();       
		$gamesList = $this->getTournamentGames();

		if (!$tourneyList) return;
	
		set_time_limit(300);	
		$this->setNumberOfTournaments($tourneyList->_numOfRows );
		while(!$tourneyList->EOF) {
			$t = new Tournament(
				$tourneyList->fields('tournamentid'),
				$tourneyList->fields('tournamentname'),
				$tourneyList->fields('tournamentmajor')
			);
			
			$t->setNumberOfCoaches($tourneyList->fields('numberofcoaches'));
			$t->calculateKValue();
			
			//$t->loadRounds(); // Slow because it's at least one select per tourney
			if (is_array($roundsList)) {
				reset($roundsList);
				while(list(,$r) = each($roundsList)) {
					if ($t->getID() == $r->getTournamentId()) {
						//$r->loadGames(); // Slow because at least one query per game
						//$this->output('<br />size:' . count($gamesList));
						
						if (is_array($gamesList)) {
							reset($gamesList);
							/*
							while(list(,$g) = each($gamesList)) {
								if ($t->getID() == $g->getTournamentID() &&
									 $r->getDate() == $g->getDate() && 
									 $r->getHour() == $g->getHour()) {
										 $r->addGame($g);
									 }
								
							}
							*/
							
							foreach($gamesList as $g) {
//							$this->output("<br />".$t->getID()."=".$g->getGameID());
								if (($t->getID() == $g->getTournamentID()) &&
									 ($r->getDate() == $g->getDate()) && 
									 ($r->getHour() == $g->getHour())) {
										 $r->addGame($g);										 
								}
							}
							
							
						}
						$t->addRound($r);
					}
				}
			}
			
			
			$this->addTournament($t);
			$tourneyList->moveNext();
		} // end while
	} // end function
		
	/**
	 * Returns the Tournament List as result of a database query
	 *
	 * @returns object	The result of the database query
	 */ 
	function getTournamentList() {
		global $dbconn;
		
		$this->output('Loading tournament list...');
		$query = "SELECT t.tournamentid, t.tournamentname, t.tournamentmajor, count(c.nafcoach) as 'numberofcoaches' ".
			'FROM naf_tournament t, naf_tournamentcoach c ' .
			'WHERE c.naftournament = t.tournamentid ' .
			'GROUP BY t.tournamentid ' . 
			'ORDER by t.tournamentstartdate';
			
		$this->output('done.');
		return $dbconn->Execute($query);
	} // end function

	/**
	 * Returns the Tournament rounds as array of tournamentRound objects
	 *
	 * @returns array of tournamentRound Objects
	 */ 
	function getTournamentRounds() {
		global $dbconn;
		
		$this->output('<br />Loading tournament rounds...');
		$query = 'SELECT distinct(tournamentid), date, hour ' .
			'FROM naf_game ' .
			'ORDER BY tournamentid, date, hour';
		
		$roundList = $dbconn->Execute($query);               
		
		if (!$roundList) return;
		
		while(!$roundList->EOF) {
			$r[] = new TournamentRound(
				$roundList->fields('tournamentid'),
				$roundList->fields('date'),
				$roundList->fields('hour')
			);
			
			$roundList->moveNext();
		} // end while
		
		$this->output('done.');
		return $r;
	} // end function
	
	function getTournamentGames() {
		global $dbconn;
		
		$this->output('<br />Loading games list...');
		$query = 'SELECT gameid, tournamentid, homecoachid, racehome, rephome, awaycoachid, raceaway, repaway, date, hour' .
			' FROM naf_game';

			$gamesList = $dbconn->Execute($query); 
	
			if (!$gamesList) return;
			
			
			while(!$gamesList->EOF) {
				$g = new TournamentGame();
				$g->setGameID($gamesList->fields('gameid'));
				$g->setTournamentID($gamesList->fields('tournamentid'));
				$g->setCoachHome($gamesList->fields('homecoachid'));
				$g->setCoachAway($gamesList->fields('awaycoachid'));
				$g->setDate($gamesList->fields('date'));
				$g->setHour($gamesList->fields('hour'));
				$games[] = $g;
				$gamesList->moveNext();
				$i++;
			} // end while
			
			$this->output('done.');

			return $games;
	} // end function

	function output($s) {
		if ($this->generateOutput() == false || $s == null || $s == "") return;
		
		echo $s;
		ob_flush();
		flush();
	}        
	/**
	 * GETTER / SETTER
	 */ 
	function getTournaments() {
		return $this->tournaments;
	}
	function addTournament($t) {
		$this->tournaments[] = $t;
	}

	function setNumberOfTournaments($number) {
		$this->numberOfTournaments = $number;
	}
	function getNumberOfTournaments() {
		return $this->numberOfTournaments;
	}
	
	function generateOutput() {
		return $this->generateOutput;
	}
	function setGenerateOutput($b) {
		$this->generateOutput = $b;
	}
}
?>