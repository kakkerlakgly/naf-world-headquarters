<?php
/**
 * the main user function
 *
 * This function is the default function, and is called whenever the module is
 * initiated without defining arguments.  As such it can be used for a number
 * of things, but most commonly it either just shows the module menu and
 * returns or calls whatever the module designer feels should be the default
 * function (often this is the view() function)
 *
 * @author       Kristian Rastrup (slup)
 * @return       output       The main module page
 */
require_once 'modules/NAF/xml.php';
require_once 'modules/NAF/pntournamentsapi.php';

function NAF_upload_submit()
{
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    include 'header.php';
    OpenTable();
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    CloseTable();
    include 'footer.php';
    return true;
  }

  $dbconn =& pnDBGetConn(true);
  include 'header.php';
  OpenTable();
  if($_FILES['result']) {
    $file = file_get_contents($_FILES['result']['tmp_name']);
    //echo htmlspecialchars($file);
    $data = XML_unserialize(($file));
    $tournament_id = 557;
    $error = false;
    if($data) {
      $coaches = array();
      foreach ($data['nafreport']['coaches']['coach'] as $coach_data) {
        $qry = "SELECT c.coachfirstname, c.coachlastname FROM naf_coach c, nuke_users nu WHERE nu.pn_uid=c.coachid AND nu.pn_uid = ".pnVarPrepForStore($coach_data['number']);
        $result = $dbconn->Execute($qry);
        if($res->EOF) {
          $error = true;
          echo 'There is no valid user with user id '.pnVarPrepForDisplay($coach_data['number']).'<br/>';
          echo 'All new users must be created before submitting results as XML<br/>';
        }
        else if(strcasecmp($result->Fields('coachfirstname').' '.$result->Fields('coachlastname'), $coach_data['name']) != 0) {
          $error = true;
          echo 'An incorrect name for user '.pnVarPrepForDisplay($coach_data['number']).' is reported<br/>';
          echo 'Real name: '.pnVarPrepForDisplay($result->Fields('coachfirstname')).' '.pnVarPrepForDisplay($result->Fields('coachlastname')).'<br/>';
          echo 'Reported name: '.pnVarPrepForDisplay($coach_data['name']).'<br/>';
        }
        else {
          $coaches[$coach_data['number']] = $coach_data['name'];
        }
        $qry = "SELECT raceid FROM naf_race WHERE name = '".pnVarPrepForStore($coach_data['team'])."'";
        $result = $dbconn->Execute($qry);
        if($result->EOF) {
          $error = true;
          echo 'There is no valid race with name '.pnVarPrepForDisplay($coach_data['team']).'<br/>';
          echo 'Enter a valid race for user '.pnVarPrepForDisplay($coach_data['number']).'<br/>';
        }
      }
      if(!$error) {
        foreach ($data['nafreport']['game'] as $game_data) {
          if(!array_key_exists($game_data['playerRecord'][0]['number'], $coaches)) {
            $error = true;
            echo 'Coach '.$game_data['playerRecord'][0]['number'].' is not reported as an attendant at the tournament<br/>';
          }
          if(!array_key_exists($game_data['playerRecord'][1]['number'], $coaches)) {
            $error = true;
            echo 'Coach '.$game_data['playerRecord'][1]['number'].' is not reported as an attendant at the tournament<br/>';
          }
        }
      }
      if(!$error) {
        $qry = "DELETE FROM naf_tournamentcoach WHERE naftournament = ".pnVarPrepForStore($tournament_id);
        $result = $dbconn->Execute($qry);
        $qry = "DELETE FROM naf_unverified_game WHERE naftournament = ".pnVarPrepForStore($tournament_id);
        $result = $dbconn->Execute($qry);
        $qry = "DELETE FROM naf_game WHERE naftournament = ".pnVarPrepForStore($tournament_id);
        $result = $dbconn->Execute($qry);
        foreach ($data['nafreport']['coaches']['coach'] as $coach_data) {
          $qry = "INSERT INTO naf_tournamentcoach (nafcoach, naftournament, race) VALUES (".
          pnVarPrepForStore($coach_data['number']).", ".
          pnVarPrepForStore($tournament_id).", ".
          pnVarPrepForStore($coach_data['team']).")";
          $dbconn->Execute($qry);
          echo $qry;
        }
        foreach ($data['nafreport']['game'] as $game_data) {
          echo $game_data['playerRecord'][0]['winnings'];
          appendToUnverified($game_data['playerRecord'][0]['number'],
                             NULL,
                             $game_data['playerRecord'][0]['rating'],
                             $game_data['playerRecord'][0]['badlyHurt'],
                             $game_data['playerRecord'][0]['seriouslyInjured'],
                             $game_data['playerRecord'][0]['dead'],
                             $game_data['playerRecord'][0]['rating'],
                             $game_data['playerRecord'][0]['winnings'],
                             $game_data['playerRecord'][0]['touchDowns'],
                             $game_data['playerRecord'][1]['number'],
                             NULL,
                             $game_data['playerRecord'][1]['touchDowns'],
                             $game_data['playerRecord'][1]['rating'],
                             $game_data['playerRecord'][1]['badlyHurt'],
                             $game_data['playerRecord'][1]['seriouslyInjured'],
                             $game_data['playerRecord'][1]['dead'],
                             $game_data['playerRecord'][1]['rating'],
                             $game_data['playerRecord'][1]['winnings'],
                             $game_data['gate'],
                             $tournament_id,
                             $game_data['time_stamp_date'],
                             $game_data['time_stamp_hour']);
        }
        $qry = "INSERT INTO naf_game "
                       ."(tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
                       ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour) "
                       ."SELECT  "
        			 ."tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
                       ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour "
        			 ."FROM naf_unverified_game "
        			 ."WHERE tournamentid = ".pnVarPrepForStore($tournament_id)
        			 ." ORDER BY game_order";
        $result = $dbconn->Execute($qry);

        //Delete rows from temp table
        $qry = "DELETE FROM naf_unverified_game WHERE tournamentid = ".pnVarPrepForStore($tournament_id);
        $result = $dbconn->Execute($qry);
        echo 'Tournament information is now inserted in the database';
      }
      else {
        echo '<br/>Reading the XML failed, please correct the above errors and try again<br/>';
        echo 'No data has been added or deleted for the tournament<br/>';
      }
    }
    else {
      echo '<br/>Document is not valid XML, correct and try again<br/>';
    }
  }
  CloseTable();
  include 'footer.php';
  return true;
}

function NAF_upload_main()
{
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    include 'header.php';
    OpenTable();
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    CloseTable();
    include 'footer.php';
    return true;
  }

  include 'header.php';
  OpenTable();
  echo 'When submitting data it will delete all previously entered data for the tournament<br/>';
  echo '<form enctype="multipart/form-data" action="'.pnVarPrepForDisplay(pnModURL('NAF', 'upload', 'submit')).'" method="POST">'
  //.'<input type="hidden" name="MAX_FILE_SIZE" value="100000" />'
  .'Choose an XML file to upload: <input name="result" type="file" /><br />'
  .'<input type="submit" value="Upload Tournament Result" />'
  .'</form>';
  CloseTable();
  include 'footer.php';
  return true;
}
?>