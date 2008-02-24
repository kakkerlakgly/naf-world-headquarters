<?php
  include '../includes/pnAPI.php';
  pnInit();

	$query = "select gameid, homecoachid, racehome, rephome, awaycoachid, raceaway, repaway from naf_game order by date, hour, gameid";
	
	$res = $dbconn->execute($query);
	
	$coaches = array();
	for ( ; !$res->EOF; $res->moveNext() ) {
		$home = $res->Fields('homecoachid')."-".$res->Fields('racehome');
		$away = $res->Fields('awaycoachid')."-".$res->Fields('raceaway');
		
		if (!$coaches[$home]) {
			$coaches[$home] = true;
			if ($res->Fields('rephome') != 15000)
				echo "Game ".$res->Fields('gameid')." erronous HOME. First game with race not CR 150.<br />\n";
		}
		if (!$coaches[$away]) {
			$coaches[$away] = true;
			if ($res->Fields('repaway') != 15000)
				echo "Game ".$res->Fields('gameid')." erronous HOME. First game with race not CR 150.<br />\n";
		}
	}

?>