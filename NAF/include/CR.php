<?
	/**
	 *  Calculates win probability. This function is used internally in the CR calculation.
	 *
	 * @param myCR The reputation of the coach's team
	 * @param oppCR The reputation of the opposing coach's team
	 * @param myTR The team rating of the coach's team
	 * @param oppTR The team rating of the opposing coach's team
	 */
	function calculateWinProbability( $myCR, $oppCR, $myTR, $oppTR ) {

		// Note that the 15000.0 really means 150.0
		// It is scaled due to the CR format

		return 1.0 / (pow(10.0, ($oppCR-$myCR) / 15000.0 + ($oppTR-$myTR) / 70.0) + 1.0);
	}

	/**
	 *  Calculates the K value. This function is used internally in the CR calculation.
	 *
	 * @param numCoaches The number of coaches in the tournament.
	 */
	function calculateKValue( $numCoaches ) {
		$k = round( 2 * sqrt($numCoaches) );
		return $k;
	}

	/**
	 * Calculates the new reputation after a game.
	 *
	 * @param myCR The reputation of the coach's team
	 * @param oppCR The reputation of the opposing coach's team
	 * @param myTR The team rating of the coach's team
	 * @param oppTR The team rating of the opposing coach's team
	 * @param scoreDifference The score result (TD's for - TD's against)
	 * @param k The K value for the game
	 */
	function calculateNewReputation( $myCR, $oppCR, $myTR, $oppTR, $scoreDifference, $k ) {
		$s = $scoreDifference > 0 ? 1.0 : ( $scoreDifference==0 ? 0.5 : 0.0 ) ;

		$pWin = calculateWinProbability ( $myCR, $oppCR, $myTR, $oppTR );

		// Note the multiplication by 100
		// This is due to the format

		return round($myCR + $k * ($s - $pWin) * 100);
	}
?>
