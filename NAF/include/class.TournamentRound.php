<?
include_once('class.TournamentGame.php');

class TournamentRound {
        var $tournamentid;
        var $date;
        var $hour;
        var $games;
        
        function TournamentRound($tournamentid, $date, $hour) {
                $this->setTournamentId($tournamentid);
                $this->setHour($hour);
                $this->setDate($date);
        } // end constructor
        
        function loadGames() {
                global $dbconn;

                $query = 'SELECT gameid, tournamentid, homecoachid, racehome, rephome, awaycoachid, raceaway, repaway, date, hour' .
                         ' FROM naf_game' .
                         ' WHERE tournamentid=' . $this->tournamentid . ' AND date="'. $this->date . '" AND hour="'. $this->hour . '"';

                $gamesList = $dbconn->Execute($query);               

                if (!$gamesList) return;
                
                while(!$gamesList->EOF) {
								$g = new TournamentGame();
                        $g->setGameID($gamesList->fields('gameid'));
                        $g->setTournamentID($gamesList->fields('tournamentid'));
								$g->setDate($gamesList->fields('date'));
								$g->setHour($gamesList->fields('hour'));                        
                        $this->addGame($g);
								
                        $gamesList->moveNext();
                }
 	
        }
        
        function toString() {
                return "Round date: " . $this->getDate() . " hour: " . $this->getHour() . ' Games: ' .$this->getGames();
        }
        /*****************
         * GETTER / SETTER
         *****************/
         function setTournamentId($id) {
                 $this->tournamentid = $id;
         }
         function getTournamentId() {
                 return $this->tournamentid;
         }
         function setHour($i) {
                 $this->hour = $i;
         }
         function getHour() {
                 return $this->hour;
         }
         function setDate($d) {
                 $this->date = $d;
         }
         function getDate() {
                 return $this->date;
         }
         function setGames($games) {
                 $this->games = $games;
         }
         function addGame($game) {
                 $this->games[] = $game;
         }
         function getGames() {
                 return $this->games;
         }
} // end class
?>