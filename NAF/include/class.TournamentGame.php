<?php
class TournamentGame {
        var $gameid;
        var $tournamentid;
        var $coachHome;
        var $coachAway;
		  var $date;
		  var $hour;
        
        function TournamentGame() {
                
        }
        
        function toString() {
                return 'GameID: ' . $this->gameid .
                        ' TournamentID: ' . $this->tournamentid .
                        ' Coach Home: ' . $this->coachHome . 
                        ' Coach Away: ' . $this->coachAway .
								' Date: ' . $this->date .
								' Hour: ' . $this->hour;
        }
        
        /**
         * GETTER/SETTER
         */ 
        function getGameID() {
                return $this->gameid;
        }
        function setGameID($id) {
                $this->gameid = $id;
        }
        
        function getTournamentID() {
                return $this->tournamentid;
        }
        function setTournamentID($id) {
                $this->tournamentid = $id;
        }
        
        function getCoachHome() {
                return $this->coachHome;
        }
        function setCoachHome($coach) {
                $this->coachHome = $coach;
        }
        
        function getCoachAway() {
                return $this->coachAway;
        }
        function setCoachAway($coach) {
                $this->coachAway = $coach;
        }
		  
		  function getDate() {
			  return $this->date;
		  }
		  function setDate($date) {
			  $this->date = $date;
		  }
		  
		  function getHour() {
			  return $this->hour;
		  }
		  function setHour($hour) {
			  $this->hour = $hour;
		  }
} // end class
?>