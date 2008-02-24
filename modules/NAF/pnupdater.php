<?
include 'modules/NAF/pnupdaterapi.php';

function NAF_updater_full($args) {
  $dbconn =& pnDBGetConn(true);
  // Clear the current coach ranking table
  $dbconn->execute("TRUNCATE TABLE naf_coachranking");

  // Mark all games as dirty and in need of update
  $dbconn->execute("UPDATE naf_game SET dirty='TRUE', rephome=15000, repaway=15000");
  pnRedirect(pnModURL('NAF', 'updater', '', array('all' => '1')));
  return true;
}

function NAF_updater_main($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    pnRedirect("/");
  }
  $dbconn =& pnDBGetConn(true);
  $tId = pnVarCleanFromInput('tournament');

  include 'header.php';
  OpenTable();
  $all = pnVarCleanFromInput('all')+0;

  $start = mktime();
  updateRaces();
  $end = mktime();
  echo 'updateRaces took '.($end-$start).' seconds<br />';

  $start = mktime();
  updateNewDate();
  $end = mktime();
  echo 'updateNewDate took '.($end-$start).' seconds<br />';

  if ($all == 0) {
    $start = mktime();
    updatePreviousRankings();
    $end = mktime();
    echo 'updatePreviousRankings took '.($end-$start).' seconds<br />';
  }

  $max = 4000;
  $start = mktime();
  $i = 0;
  for (; $i< $max && updateRankings(); $i++) ;
  $end = mktime();
  echo 'updateRankings * '.($i).' took '.($end-$start).' seconds<br />';

  $query = "select count(*) from naf_game where dirty = 'TRUE'";
  $result = $dbconn->Execute($query);
  $dirty = $result->fields[0];
  if ($dirty > 0){
    echo 'Dirty games left: '.$dirty.'<br />';
    echo '<a href="'.pnVarPrepForDisplay($all ? pnModURL('NAF', 'updater', '', array('all' => '1')) : pnModURL('NAF', 'updater')).'">Click here to continue</a> or refresh this page<br />';
  }
  else {
    echo 'Done.<br /><a href="'.pnVarPrepForDisplay(pnGetBaseURL()).'">Back to main page</a>';
    if ($tId > 0) {
      echo '<br /><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report3', array('id' => $tournament))).'">Back to tournament match reporting</a>';
    }
    echo '<br /><br />'
        .'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'updater', 'full')).'">Recalculate all rankings</a><br /><b>Note:</b> This will update all '
        .'rankings for everyone and will take some time';
  }
  CloseTable();
  include 'footer.php';
  return true;
}
?>
