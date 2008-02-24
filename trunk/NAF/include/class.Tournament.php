<?
include_once('class.TournamentRound.php');

class Tournament {
	var $id = 0;
	var $name = "";
	var $major = false;
	var $kValue = 0;
        var $numberOfCoaches = 0;
        var $rounds;
        
	function Tournament($id, $name, $isMajor) { 
		$this->setID($id);
		$this->setName($name);
		$this->setMajor($isMajor);
	}

	/**
	 * Returns the Major Status of the tournament
	 * Basically this is identicall to the getter, but for 
	 * better code readability this alias function was added
	 * getMajor could imply that some status is returned
	 * isMajor imlies true/false
	 * @see getName()
	 */
	function isMajor() {
		return $this->major;
	}


	/** 
	 * Calculates the tournaments k-value
	 */
	function calculateKValue() {
                if ($this->isMajor()) {
                        //$coaches = min(60, $this->getNumberOfCoaches());
                        $coaches = 60;
                } else {
                        $coaches = min(30, $this->getNumberOfCoaches());
                }
                
                $this->setKValue(pow($coaches, 0.5) * 2 );
	}
        
	/*
	* Loads the list of game rounds for the tournament
	* 
	* TODO: use this function only if loading data for a specific tournament
	*       To improve performance (if needed) for the whole tournament list
	*       do a query in the manager for all tourneys, iterate over the results
	*       and set the rounds with the set methods.
	*/
	function loadRounds() {
		global $dbconn;
			 
		$query = 'SELECT distinct(tournamentid), date, hour ' .
			'FROM naf_game ' .
			'WHERE tournamentid = ' . $this->id . 
			' ORDER BY tournamentid, date, hour';
	
	
			 $roundList = $dbconn->Execute($query);               
	
	
			 if (!$roundList) return;
			 
			 
			 while(!$roundList->EOF) {
						$r = new TournamentRound(
								  $roundList->fields('date'),
								  $roundList->fields('hour')
						);
						
						$this->addRound($r);
						$roundList->moveNext();
			 }
			 
        }

	/**
	 * GETTER/SETTER
	 */
	function getID() {
		return $this->id;
	}
	function setID($id) {
		$this->id = $id;
	}

	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function setMajor($b) {
		if ($b == "yes") $this->major = true;
	}
	function getMajor() {
		return $this->major;
	}
	function setKValue($k) {
		$this->kValue = $k;
	}
	function getKValue() {
		return $this->kValue;
	}
        function getNumberOfCoaches() {
                return $this->numberOfCoaches;
        }
        function setNumberOfCoaches($i) {
                $this->numberOfCoaches = $i;
        }
	function getRounds() {
		return $this->rounds;
	}
	function addRound($r) {
		$this->rounds[] = $r;
	}

	function toString() {
		return 'ID: ' . $id .
			'Name: ' . $name .
			'Major: ' . $major . 
			'Coaches #: ' . $numberOfCoaches .
                        'KValue: ' . $kValue;
	}
} // end class

?>