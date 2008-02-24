<?
include 'modules/NAF/pnupdaterapitemp.php';

function NAF_updatertemp_main($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    pnRedirect("/");
  }
  $dbconn =& pnDBGetConn(true);
  $tId = pnVarCleanFromInput('tournament');

  include 'header.php';
  OpenTable();

  if (pnVarCleanFromInput('all')+0 == 1) {
    // Clear the current coach ranking table
    $dbconn->execute("TRUNCATE TABLE naf_coachranking_temp");

    // Mark all games as dirty and in need of update
    $dbconn->execute("UPDATE naf_game_temp SET dirty='TRUE'");
    pnRedirect(pnModURL('NAF', 'updatertemp'));
    return true;
  }

  updateRaces();
  updateNewDate();
  // echo '<br />Updating previous rankings <br />';

  // 13.7.2007 - juergen:
  // temporary disabled to do a full recalc
  // see: http://www.bloodbowl.net/index.php?name=PNphpBB2&file=viewtopic&p=58597#58597
  //updatePreviousRankings();

  echo '<br />Updating rankings <br />';
  $max = 4000;
  $start = mktime();
  $i = 0;
  for (; $i< $max && updateRankings(); $i++) ;
  $end = mktime();
  echo 'updateRankings * '.($i).' took '.($end-$start).' seconds<br />';

  $query = "select count(*) from naf_game_temp where dirty = 'TRUE'";
  $result = $dbconn->Execute($query);
  $dirty = $result->fields[0];
  if ($dirty > 0){
    echo 'Dirty games left: '.$dirty.'<br />';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'updatertemp')).'">Click here to continue</a> or refresh this page<br />';
  }
  else {
    echo 'Done.<br /><a href="'.pnVarPrepForDisplay(pnGetBaseURL()).'">Back to main page</a>';
    if ($tId > 0) {
      echo '<br /><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report3', array('id' => $tournament))).'">Back to tournament match reporting</a>';
    }
    echo '<br /><br />'
        .'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'updatertemp', '', array('all' => '1'))).'">Recalculate all rankings</a><br /><b>Note:</b> This will update all '
        .'rankings for everyone and will take some time';
  }
  CloseTable();
  include 'footer.php';
  return true;
}
?>
