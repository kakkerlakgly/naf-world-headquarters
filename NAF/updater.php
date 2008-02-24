<?
//  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
//    pnRedirect("/");
//  }
  $tId = pnVarCleanFromInput('tournament');

  if (pnVarCleanFromInput('all')+0 == 1) {
    // Clear the current coach ranking table
    $dbconn->execute("TRUNCATE TABLE naf_coachranking");

    // Mark all games as dirty and in need of update
    $dbconn->execute("UPDATE naf_game SET dirty='TRUE'");
  }

  include 'NAF/include/updater.php';

  set_time_limit(0);

  //enableDebug();

  include 'header.php';
  OpenTable();
  echo "Updating Rankings. This might take a while...";
  echo "<br /><br />Updating Races <br />";
  ob_flush();flush();
sleep(1);
  updateRaces();

  echo "<br /><br />Updating previous rankings <br />";
  ob_flush();flush();

  // 13.7.2007 - juergen:
  // temporary disabled to do a full recalc
  // see: http://www.bloodbowl.net/index.php?name=PNphpBB2&file=viewtopic&sid=b087cfc9aee4d7744755e558b71c9589&p=58597#58597
  //updatePreviousRankings();

  echo "<br /><br />Updating rankings <br />";
  ob_flush();flush();

  //while (updateRankings()) ;
   $count = 0;
   while ($count < 5000 && updateRankings())
   $count++;

  echo "Done.<br /><a href=\"/\">Back to main page</a>";
  if ($tId > 0) {
    echo "<br /><a href=\"naf.php?page=tournaments&op=report3&id=$tournament\">Back to tournament match reporting</a>";
  }

  /*echo "<br /><br />"
      ."<a href=\"naf.php?page=updater&all=1\">Recalculate all rankings</a><br /><b>Note:</b> This will update all "
      ."rankings for everyone and will take a lot of time";*/

  CloseTable();
  include 'footer.php';
?>
