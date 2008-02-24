<?
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    pnRedirect("/");
  }
  $tId = pnVarCleanFromInput('tournament');

  if (pnVarCleanFromInput('all')+0 == 1) {
    // Clear the current coach ranking table
    $dbconn->execute("TRUNCATE TABLE naf_coachranking");

    // Mark all games as dirty and in need of update
    $dbconn->execute("UPDATE naf_game SET dirty='TRUE'");
  }

  include 'NAF/include/updater.php';

  set_time_limit(0);

  enableDebug();

//  include 'header.php';
//  OpenTable();

echo "<h2>ranking update temporarily disabled while trying to find the problem. </h2><p>Sorry Juergen</p>";

  echo "Updating Rankings. This might take a while...";

  updateRaces();

  updatePreviousRankings();
  while (updateRankings()) ;

  echo "Done.<br /><a href=\"/\">Back to main page</a>";
  if ($tId > 0) {
    echo "<br /><a href=\"naf.php?page=tournaments&op=report3&id=$tournament\">Back to tournament match reporting</a>";
  }

  echo "<br /><br />"
      ."<a href=\"naf.php?page=updater&all=1\">Recalculate all rankings</a><br /><b>Note:</b> This will update all "
      ."rankings for everyone and will take a lot of time";

  CloseTable();
  include 'footer.php';
?>
