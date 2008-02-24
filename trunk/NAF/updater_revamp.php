<?php
	include_once('include/class.tournamentManager.php');
	include_once('include/class.Timer.php');
	
	$timer = new Timer();
	$timer->start();
	
	echo '<h1>Ranking update</h1>';
	echo '<span style="color:red;">Version 0.3.2 (March, 1st, 2007) - no database data will be changed! <br />';
	echo '<br />v0.3.2: Added ob_flush methods to overcome timeout problems, changed game loading to sql queries because they are faster then iterating over huge arrays.';
	echo '<br />v0.3.1: Added flush methods to overcome timeout problems';
	echo '<br />v0.3.0: Tournament games will be read into memory with one sql query and added to the according rounds instead of loading the games with one sql each round. </span>';
	if (function_exists('memory_get_usage')) {
		echo '<p>Memory usage: ' . memory_get_usage() / 1000 .' kB</p>';
	}
        
	ob_flush();flush();
	$tourneyManager = new TournamentManager();
	$tourneyManager->setGenerateOutput(true);
	$tourneyManager->loadTournaments();

	$numberOfTournaments = $tourneyManager->getNumberOfTournaments();

	if ($numberOfTournaments > 0) {
		echo '<p>Updating ' . $numberOfTournaments . ' tournaments!</p>';
		echo '<ol>';
		ob_flush();flush();
		$tournaments = $tourneyManager->getTournaments();
	
		while(list(,$v) = each($tournaments)) {
			echo '<li>' . $v->getName() . ' (' . $v->getID() .')';

			if ($v->isMajor()) {
				echo '<span style="color:red"> (Major) </span>';
			}
			
			echo '<br />Coaches: ' . $v->getNumberOfCoaches() . ' -> kValue: ' . $v->getKValue();
			echo '</li>'; 
			ob_flush();flush();   
			
			$rounds = $v->getRounds();
			if (is_array($rounds)) {
				while(list(,$vr) = each($rounds)) {
					echo '<br />Round ' . $vr->getDate() . ' @ ' . $vr->getHour();
					ob_flush();flush();
					
					$games = $vr->getGames();
					if (is_array($games)) {
						while(list(,$vg) = each($games)) {
							//echo '<br />Game' . $vg->getGameID();
							//ob_flush();flush();  
						}
					}
				}
			} else {
				//echo '<br /><span style="color:red"> no games entered! </span>';
			}
		}

		echo '</ol>';
	}
        
        $timer->end();

   echo '<p>Time elapsed: ' . $timer->getElapsedTime() . ' seconds!</p>';
	if (function_exists('memory_get_usage')) {	
		echo '<p>Memory usage: ' . memory_get_usage() / 1000 . ' kB</p>';
	}
	ob_flush();flush();
?>	