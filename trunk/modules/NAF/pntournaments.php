<?
require_once 'modules/NAF/xml.php';
require_once 'modules/NAF/pntournamentsapi.php';


function NAF_tournaments_add($args) {
  $dbconn =& pnDBGetConn(true);
  $op = pnVarCleanFromInput('op');
  include 'header.php';
  if(strcmp($op, 'edit') != 0) $op = 'add';
  OpenTable();

  if (strcmp($op, "add")==0) {
    list($tName, $tAddress1, $tAddress2, $tCity, $tState, $tZip, $tNation, $tUrl, $tNotesurl, $tStartdate, $tEnddate,
    $tType, $tStyle, $tScoring, $tCost, $tNaffee, $tNafdiscount, $tInformation, $tEmail, $tOrg, $tStatus, $tMajor ) =
    pnVarCleanFromInput('name', 'address1', 'address2', 'city', 'state', 'zip', 'nation', 'url', 'notesurl', 'startdate',
    'enddate', 'type', 'style', 'scoring', 'cost', 'naffee', 'nafdiscount', 'information',
    'email', 'org', 'status', 'major');
  }
  else {
    $tournamentid = pnVarCleanFromInput('id');

    $res = $dbconn->Execute("SELECT * FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($tournamentid));

    list($tName, $tAddress1, $tAddress2, $tCity, $tState, $tZip, $tNation, $tUrl, $tNotesurl, $tStartdate, $tEnddate,
    $tType, $tStyle, $tScoring, $tCost, $tNaffee, $tNafdiscount, $tInformation, $tEmail, $tOrg, $tStatus, $tMajor ) =
    array($res->Fields('tournamentname'), $res->Fields('tournamentaddress1'), $res->Fields('tournamentaddress2'),
    $res->Fields('tournamentcity'), $res->Fields('tournamentstate'), $res->Fields('tournamentzip'),
    $res->Fields('tournamentnation'), $res->Fields('tournamenturl'), $res->Fields('tournamentnotesurl'),
    $res->Fields('tournamentstartdate'),
    $res->Fields('tournamentenddate'), $res->Fields('tournamenttype'), $res->Fields('tournamentstyle'),
    $res->Fields('tournamentscoring'), $res->Fields('tournamentcost'), $res->Fields('tournamentnaffee'),
    $res->Fields('tournamentnafdiscount'), $res->Fields('tournamentinformation'),
    $res->Fields('tournamentemail'), $res->Fields('tournamentorg'),
    $res->Fields('tournamentstatus'), $res->Fields('tournamentmajor'));
  }

  echo '<h4>'.(strcmp($op, "add")==0?"Add":"Edit").' Tournament</h4>'."\n";

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'insert', array('op' => (strcmp($op, 'add')==0?'insert':'update')))).'" method="post">'."\n";
  echo '<input type="hidden" name="id" value="'.$tournamentid.'" />'."\n";

  if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    echo '<b>Status</b><select name="status">'
    .'<option value="NEW"'.(strcmp($tStatus, 'NEW')==0?' selected="selected"':'').'>New</option>'
    .'<option value="PENDING"'.(strcmp($tStatus, 'PENDING')==0?' selected="selected"':'').'>Pending</option>'
    .'<option value="APPROVED"'.(strcmp($tStatus, 'APPROVED')==0?' selected="selected"':'').'>Approved</option>'
    .'<option value="NOTAPPROVED"'.(strcmp($tStatus, 'NOTAPPROVED')==0?' selected="selected"':'').'>Not Approved</option>'
    .'</select>';
    echo '<br /><b>Major Tournament</b><select name="major">'
    .'<option value="no"'.(strcmp($tMajor, "no")==0?' selected="selected"':'').'>No</option>'
    .'<option value="yes"'.(strcmp($tMajor, "yes")==0?' selected="selected"':'').'>Yes</option>'
    .'</select>';
  }

  echo '<table border="0" cellpadding="8"><tr><td>';

  echo '<table border="0"><tr><td valign="top">';
  echo 'Tournament Name</td><td><input type="text" name="name" value="'.pnVarPrepForDisplay($tName).'" /></td></tr>'."\n";
  echo '<tr><td>Organizer</td><td><input type="text" name="org" value="'.$tOrg.'" /></td></tr>'."\n";
  echo '<tr><td>Start Date (YYYY-MM-DD)</td><td><input type="text" name="startdate" value="'.$tStartdate.'" /></td></tr>'."\n";
  echo '<tr><td>End Date (YYYY-MM-DD)</td><td><input type="text" name="enddate" value="'.$tEnddate.'" /></td></tr>'."\n";
  echo '<tr><td>Type</td><td><select name="tournament_type">'
  .'<option value="OPEN"'.(strcmp($tType, 'OPEN')==0?' selected="selected"':'').'>Open</option>'
  .'<option value="INVITATIONAL"'.(strcmp($tType, 'INVITATIONAL')==0?' selected="selected"':'').'>Invitational</option></select></td></tr>'."\n";
  echo '<tr><td>Style</td><td><input type="text" name="style" value="'.$tStyle.'" /></td></tr>'."\n";
  echo '<tr><td>Scoring</td><td><input type="text" name="scoring" value="'.$tScoring.'" /></td></tr>'."\n";
  echo '<tr><td>Cost</td><td><input type="text" name="cost" value="'.$tCost.'" /></td></tr>'."\n";
  echo '<tr><td>NAF Fee Included</td><td><input type="checkbox" name="naffee" '.((strcmp($tNaffee, "yes")==0)?'checked':'').'/></td></tr>'."\n";
  echo '<tr><td>NAF Member Discount</td><td><input type="checkbox" name="nafdiscount" '.((strcmp($tNafdiscount, "yes")==0)?'checked':'').'/></td></tr>'."\n";
  echo '<tr><td>Email</td><td><input type="text" name="email" value="'.pnVarPrepForDisplay($tEmail).'" /></td></tr>'."\n";
  echo '<tr><td>Webpage URL</td><td><input type="text" name="url" value="'.pnVarPrepForDisplay($tUrl).'" /></td></tr>'."\n";
  echo '<tr><td>Webpage Name</td><td><input type="text" name="notesurl" value="'.pnVarPrepForDisplay($tNotesurl).'" /></td></tr>'."\n";

  echo '</table></td><td valign="top"><table border="0">';

  echo '<tr><th colspan="2">Tournament Location</th></tr>'."\n";
  echo '<tr><td>Address 1</td><td><input type="text" name="address1" value="'.$tAddress1.'" /></td></tr>'."\n";
  echo '<tr><td>Address 2</td><td><input type="text" name="address2" value="'.$tAddress2.'" /></td></tr>'."\n";
  echo '<tr><td>City</td><td><input type="text" name="city" value="'.$tCity.'" /></td></tr>'."\n";
  echo '<tr><td>State</td><td><input type="text" name="state" value="'.$tState.'" /></td></tr>'."\n";
  echo '<tr><td>Zip</td><td><input type="text" name="zip" value="'.$tZip.'" /></td></tr>'."\n";

  echo '<tr><td>Nation</td><td><select name="nation">';
  require 'NAF/include/countries.php';
  foreach($countries as $nation) {
    if (strlen($nation) > 0) {
      echo '<option value="'.$nation.'"'.(strcmp($nation, $tNation)==0?' selected="selected"':'').'>'.$nation.'</option>';
    }
    else {
      echo '<option value="">---</option>';
    }
  }
  echo '</select></td></tr>'."\n";

  echo '<tr><th colspan="2">Information</th></tr><tr>'
  .'<td colspan="2"><textarea rows="10" cols="40" name="information">'.pnVarPrepForDisplay($tInformation).'</textarea></td></tr>'."\n";

  echo '</table></td></tr>'."\n";

  echo '<tr><td colspan="2"><input type="submit" value="Submit" /></td></tr>'."\n";

  echo '</table>';

  echo '</form>';

  CloseTable();
  include 'footer.php';
  return true;
}
function NAF_tournaments_delete($args) {
  $dbconn =& pnDBGetConn(true);

  list($id, $confirm) = pnVarCleanFromInput('id', 'confirm');

  $res = $dbconn->Execute("SELECT tournamentorganizerid, tournamentname FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($id));

  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    if ($res->EOF || strcmp($res->fields[0], pnUserGetVar('uid'))!=0) {
      include 'header.php';
      OpenTable();
      echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
      CloseTable();
      include 'footer.php';
    }
  }

  if ($confirm == 1) {
    $dbconn->Execute("DELETE FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($id));
    pnRedirect(pnModURL('NAF', 'tournaments'));
  }
  else {
    include 'header.php';
    OpenTable();
    echo '<b>Are you sure you want to delete the \''.$res->fields[1].'\' tournament?</b><br /><br />';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'delete', array('id' => $id, 'confirm' => '1'))).'">Yes</a> &nbsp; &nbsp; '
    .'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments')).'">No</a>';
    CloseTable();
    include 'footer.php';
  }

  $res->Close();
  return true;
}

function NAF_tournaments_delete_match($args) {
  $dbconn =& pnDBGetConn(true);
  $game_id = pnVarCleanFromInput('game_id');
  $tournament_id = pnVarCleanFromInput('id');
  // Expects $gameid and $tournament_id, removes game with id matching gameid and corrects the game_order of any subsequent games
  // Get count from game to be deleted
  $query = "SELECT count(1) FROM naf_unverified_game WHERE gameid=".pnVarPrepForStore($game_id);
  $result = $dbconn->Execute($query);
  $game_order = $result->fields[0];
  // Delete match
  $sqlupdate = "DELETE FROM naf_unverified_game where gameid = ".pnVarPrepForStore($game_id);
  $result = $dbconn->Execute($sqlupdate);
  // Re-order current matches
  $sqlupdate = "UPDATE naf_unverified_game SET game_order=game_order - 1 WHERE game_order > ".pnVarPrepForStore($game_order)
  ." AND tournamentid = ".pnVarPrepForStore($tournament_id);
  $result = $dbconn->Execute($sqlupdate);
  pnRedirect(pnModURL('NAF', 'tournaments', 'report3', array('id' => $tournament_id)));
  return true;
}

function NAF_tournaments_finalize_tournament($args) {

  $dbconn =& pnDBGetConn(true);
  $tournament_id = pnVarCleanFromInput('id');
  $sqlupdate = "INSERT INTO naf_game "
  ."(tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
  ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour) "
  ."SELECT  "
  ."tournamentid,homecoachid,awaycoachid,racehome,raceaway,trhome,traway,rephome,repaway,goalshome,goalsaway,"
  ."badlyhurthome,badlyhurtaway,serioushome,seriousaway,killshome,killsaway, gate, winningshome, winningsaway, date, hour "
  ."FROM naf_unverified_game "
  ."WHERE tournamentid = ".pnVarPrepForStore($tournament_id)
  ." ORDER BY game_order";
  $result = $dbconn->Execute($sqlupdate);

  //Delete rows from temp table
  $sqlupdate = "DELETE FROM naf_unverified_game "
  ."WHERE tournamentid = ".pnVarPrepForStore($tournament_id);
  $result = $dbconn->Execute($sqlupdate);
  // Confirmation needs to be added here. Also, the updater should be called and whatever needs to be done to set a tournament as completed


  pnRedirect(pnModURL('NAF', 'tournaments', 'report3', array('id' => $tournament_id)));
  return true;
}

function NAF_tournaments_insert($args) {
  $dbconn =& pnDBGetConn(true);

  list($tName, $tAddress1, $tAddress2, $tCity, $tState, $tZip, $tNation, $tUrl, $tNotesurl, $tStartdate, $tEnddate,
  $tType, $tStyle, $tScoring, $tCost, $tNaffee, $tNafdiscount, $tInformation, $tEmail, $tOrg, $id, $tStatus, $tMajor, $op) =
  pnVarCleanFromInput('name', 'address1', 'address2', 'city', 'state', 'zip', 'nation', 'url', 'notesurl', 'startdate',
  'enddate', 'tournament_type', 'style', 'scoring', 'cost', 'naffee', 'nafdiscount', 'information',
  'email', 'org', 'id', 'status', 'major', 'op');

  if (!pnUserGetVar('uid') || pnUserGetVar('uid') == 0) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
    return true;
  }


  $id += 0;
  if (strcmp($tNaffee, "on")==0) {
    $tNaffee="yes";
  }
  if (strcmp($tNafdiscount, "on")==0) {
    $tNafdiscount="yes";
  }

  if (!empty($tUrl)) {
    if (!eregi('^http://[\-\.0-9a-z]+', $tUrl)) {
      $tUrl = 'http://' . $tUrl;
    }
    $tUrl = preg_replace('/[^a-zA-Z0-9_@.&#?;:\/-~-]/','',$tUrl);
  }
  if (strcmp($op, "insert")==0) {
    if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN))
    $tMajor = 'no';
    $res = $dbconn->Execute("INSERT INTO naf_tournament (tournamentorganizerid, tournamentname, tournamentaddress1, "
    ."tournamentaddress2, tournamentcity, tournamentstate, tournamentzip, tournamentnation, "
    ."tournamenturl, tournamentnotesurl, tournamentstartdate, tournamentenddate, tournamenttype, "
    ."tournamentstyle, tournamentscoring, tournamentcost, tournamentnaffee, tournamentnafdiscount, "
    ."tournamentinformation, tournamentemail, tournamentorg, tournamentmajor ) "
    ."values ( ".pnVarPrepForStore(pnUserGetVar('uid')).", '".pnVarPrepForStore($tName)."', '".pnVarPrepForStore($tAddress1)."', "
    ."'".pnVarPrepForStore($tAddress2)."', '".pnVarPrepForStore($tCity)."', "
    ."'".pnVarPrepForStore($tState)."', '".pnVarPrepForStore($tZip)."', '".pnVarPrepForStore($tNation)."', "
    ."'".pnVarPrepForStore($tUrl)."', '".pnVarPrepForStore($tNotesurl)."', "
    ."'".pnVarPrepForStore($tStartdate)."', '".pnVarPrepForStore($tEnddate)."', "
    ."'".pnVarPrepForStore($tType)."', '".pnVarPrepForStore($tStyle)."', '".pnVarPrepForStore($tScoring)."', "
    ."'".pnVarPrepForStore($tCost)."', '".pnVarPrepForStore($tNaffee)."', "
    ."'".pnVarPrepForStore($tNafdiscount)."', '".pnVarPrepForStore($tInformation)."', "
    ."'".pnVarPrepForStore($tEmail)."', "
    ."'".pnVarPrepForStore($tOrg)."', "
    ."'".pnVarPrepForStore($tMajor)."' )");
  }
  else {
    if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      $res = $dbconn->Execute("SELECT tournamentorganizerid FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($id));
      if ($res->fields[0] != pnUserGetVar('uid')) {
        include 'header.php';
        OpenTable();
        echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
        CloseTable();
        include 'footer.php';
      }
    }

    $qry = "update naf_tournament set "
    ."tournamentname='".pnVarPrepForStore($tName)."', "
    ."tournamentaddress1='".pnVarPrepForStore($tAddress1)."', "
    ."tournamentaddress2='".pnVarPrepForStore($tAddress2)."', "
    ."tournamentcity='".pnVarPrepForStore($tCity)."', "
    ."tournamentstate='".pnVarPrepForStore($tState)."', "
    ."tournamentzip='".pnVarPrepForStore($tZip)."', "
    ."tournamentnation='".pnVarPrepForStore($tNation)."', "
    ."tournamenturl='".pnVarPrepForStore($tUrl)."', "
    ."tournamentnotesurl='".pnVarPrepForStore($tNotesurl)."', "
    ."tournamentstartdate='".pnVarPrepForStore($tStartdate)."', "
    ."tournamentenddate='".pnVarPrepForStore($tEnddate)."', "
    ."tournamenttype='".pnVarPrepForStore($tType)."', "
    ."tournamentstyle='".pnVarPrepForStore($tStyle)."', "
    ."tournamentscoring='".pnVarPrepForStore($tScoring)."', "
    ."tournamentcost='".pnVarPrepForStore($tCost)."', "
    ."tournamentnaffee='".pnVarPrepForStore($tNaffee)."', "
    ."tournamentnafdiscount='".pnVarPrepForStore($tNafdiscount)."', "
    ."tournamentinformation='".pnVarPrepForStore($tInformation)."', "
    ."tournamentemail='".pnVarPrepForStore($tEmail)."', "
    ."tournamentorg='".pnVarPrepForStore($tOrg)."'";
    if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      $qry .= ", tournamentstatus='".pnVarPrepForStore($tStatus)."'"
      .", tournamentmajor='".pnVarPrepForStore($tMajor)."'";
    }
    $qry .= " WHERE tournamentid=".pnVarPrepForStore($id);
    $res = $dbconn->Execute($qry);
  }

  $err = $dbconn->ErrorMsg();

  if (strlen($err) == 0) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
  }
  else {
    include 'header.php';
    OpenTable();
    echo 'Error in database query:<br />'
    .$err.'<br /><br />Query:<br />'.$qry.'<br /><br />'
    .'<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments')).'>Back to tournaments page</a>';
    CloseTable();
    include 'footer.php';
  }
  return true;
}

function NAF_tournaments_view($args) {
  $dbconn =& pnDBGetConn(true);
  $tournamentid = pnVarCleanFromInput('id');

  if (!$tournamentid) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
    return true;
  }

  $res = $dbconn->Execute("SELECT * FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($tournamentid));

  list($tName, $tAddress1, $tAddress2, $tCity, $tState, $tZip, $tNation, $tUrl, $tNotesurl, $tStartdate, $tEnddate,
  $tType, $tStyle, $tScoring, $tCost, $tNaffee, $tNafdiscount, $tInformation, $tEmail, $tOrg ) =
  array($res->Fields('tournamentname'), $res->Fields('tournamentaddress1'), $res->Fields('tournamentaddress2'),
  $res->Fields('tournamentcity'), $res->Fields('tournamentstate'), $res->Fields('tournamentzip'),
  $res->Fields('tournamentnation'), $res->Fields('tournamenturl'), $res->Fields('tournamentnotesurl'),
  $res->Fields('tournamentstartdate'),
  $res->Fields('tournamentenddate'), $res->Fields('tournamenttype'), $res->Fields('tournamentstyle'),
  $res->Fields('tournamentscoring'), $res->Fields('tournamentcost'), $res->Fields('tournamentnaffee'),
  $res->Fields('tournamentnafdiscount'), $res->Fields('tournamentinformation'),
  $res->Fields('tournamentemail'), $res->Fields('tournamentorg'));

  if (strlen($tNaffee)==0)
  $tNaffee = "no";
  if (strlen($tNafdiscount)==0)
  $tNafdiscount = "no";

  include 'header.php';
  OpenTable();

  echo '<table border="0" cellpadding="8"><tr><td>';

  echo '<table border="0"><tr><td valign="top">';
  echo '<b>Tournament Name</b></td><td>'.$tName.'</td></tr>'."\n";
  echo '<tr><td><b>Organizer</b></td><td>'.$tOrg.'</td></tr>'."\n";
  echo '<tr><td><b>Start Date (YYYY-MM-DD)</b></td><td>'.$tStartdate.'</td></tr>'."\n";
  echo '<tr><td><b>End Date (YYYY-MM-DD)</b></td><td>'.$tEnddate.'</td></tr>'."\n";
  echo '<tr><td><b>Type</b></td><td>'.$tType.'</td></tr>'."\n";
  echo '<tr><td><b>Style</b></td><td>'.$tStyle.'</td></tr>'."\n";
  echo '<tr><td><b>Scoring</b></td><td>'.$tScoring.'</td></tr>'."\n";
  echo '<tr><td><b>Cost</b></td><td>'.$tCost.'</td></tr>'."\n";
  echo '<tr><td><b>NAF Fee Included</b></td><td>'.$tNaffee.'</td></tr>'."\n";
  echo '<tr><td><b>NAF Member Discount</b></td><td>'.$tNafdiscount.'</td></tr>'."\n";
  echo '<tr><td><b>Email</b></td><td>'.$tEmail.'</td></tr>'."\n";
  echo '<tr><td><b>Webpage</b></td><td><a href="'.pnVarPrepForDisplay($tUrl).'">'.pnVarPrepForDisplay($tNotesurl).'</a></td></tr>'."\n";

  echo '</table></td><td width="20">&nbsp;</td><td valign="top"><table border="0">';

  echo '<tr><th colspan="2">Tournament Location</th></tr>'."\n";
  echo '<tr><td><b>Address</b></td><td>'.$tAddress1.'</td></tr>'."\n";
  echo '<tr><td>&nbsp;</td><td>'.$tAddress2.'</td></tr>'."\n";
  echo '<tr><td><b>City</b></td><td>'.$tCity.'</td></tr>'."\n";
  echo '<tr><td><b>State</b></td><td>'.$tState.'</td></tr>'."\n";
  echo '<tr><td><b>Zip</b></td><td>'.$tZip.'</td></tr>'."\n";

  echo '<tr><td><b>Nation</b></td><td>'.$tNation.'</td></tr>'."\n";

  echo '<tr><th colspan="2"><br />Information</th></tr><tr>'
  .'<td colspan="2">'.nl2br(pnVarPrepHTMLDisplay($tInformation)).'</td></tr>'."\n";

  echo '</table></td></tr>'."\n";
  echo '</table>';

  echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'view', '', array('id' => $tournamentid))).'">View Matches</a><br />';

  echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments')).'">Back to tournament list</a>';

  CloseTable();
  include 'footer.php';
  return true;
}

function NAF_tournaments_report($args) {
  $dbconn =& pnDBGetConn(true);
  list($tId, $addCoaches, $delCoach, $coachcount, $coaches, $add) = pnVarCleanFromInput('id', 'addcoaches', 'delcoach', 'coachcount', 'coaches', 'add');

  $qry = "SELECT * FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($tId);
  $tourney = $dbconn->Execute($qry);
  if ($tourney->EOF) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
  }
  else {
    if ($addCoaches == 1) {
      for ($i=0; $i<$coachcount; $i++) {
        $coach = pnVarPrepForStore($coaches[$i]);
        if ($coach != '') {
          $coach = trim($coach);
          if (strlen($list) > 0) {
            $list .= ",";
          }
          $list .= "'".pnVarPrepForStore($coach)."'";
        }
      }
      $qry = "INSERT INTO naf_tournamentcoach (nafcoach, naftournament) SELECT coachid, ".pnVarPrepForStore($tId)." FROM naf_coach c, nuke_users nu WHERE nu.pn_uid=c.coachid AND (nu.pn_uname in ($list) or nu.pn_uid in ($list))";
      $dbconn->Execute($qry);
    }
    if ($delCoach > 0) {
      $qry = "DELETE FROM naf_tournamentcoach WHERE naftournament=".pnVarPrepForStore($tId)." AND nafcoach=".pnVarPrepForStore($delCoach);
      $dbconn->Execute($qry);
    }

    $qry = "SELECT raceid, name FROM naf_race ORDER BY name";
    $races = $dbconn->Execute($qry);
    $raceArr[0] = '[ None ]';
    $raceSel = '<option value="">[ Select Race ]</option>';
    for ( ; !$races->EOF; $races->MoveNext() ) {
      $raceArr[$races->Fields('raceid')] = $races->Fields('name');
      $raceSel .= '<option value="'.$races->Fields('raceid').'">'.$races->Fields('name').'</option>';
    }

    include 'header.php';
    OpenTable();

    echo '<h3>Attending NAF coaches in \''.pnVarPrepForDisplay($tourney->Fields('tournamentname')).'\'</h3>';

    if (pnSecAuthAction(0, 'Users::', '::', ACCESS_ADMIN)) {
      echo 'First, make sure all users are <a href="'.pnVarPrepForDisplay('admin.php?module=User').'"> '
      .'added to the site</a>. You can also <a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired')).'">renew accounts</a>.<br />';
    }

    $qry = "SELECT coachid, coachfirstname, coachlastname, race FROM naf_coach c, naf_tournamentcoach tc "
    ."WHERE tc.nafcoach=c.coachid AND tc.naftournament=".pnVarPrepForStore($tId);
    $coaches = $dbconn->Execute($qry);

    if (!$coaches->EOF) {
      echo '<table border="1">'
      .'<tr><th colspan="5">Tournament NAF attendees</th></tr>'
      .'<tr><th>Op</th><th>Coachid</th><th>Login name</th><th>Real Name</th><th>Race</th></tr>'."\n";

      $hasCoaches = false;
      for ( ; !$coaches->EOF; $coaches->MoveNext() ) {
        echo '<tr><td>(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report', array('id' => $tId, 'delcoach' => $coaches->Fields('coachid')))).'">Del</a>)</td>'
        .'<td>'.pnVarPrepForDisplay($coaches->Fields('coachid')).'</td>'
        .'<td>'.pnVarPrepForDisplay(pnUserGetVar('uname', $coaches->Fields('coachid'))).'</td>'
        .'<td>'.pnVarPrepForDisplay($coaches->Fields('coachfirstname')).' '.pnVarPrepForDisplay($coaches->Fields('coachlastname')).'</td>'
        .'<td>'.pnVarPrepForDisplay($raceArr[$coaches->Fields('race')]).'</td>'
        .'</tr>'."\n";
        $hasCoaches = true;
      }

      echo '</table>';
    }

    if ($hasCoaches == false || isset($add)) {
      echo '<br /><div style="font-size: 1.4em;">Type in the usernames or membership numbers of the coaches you want to add:</div>';
      echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report')).'" method="post">';
      $coachcount = 0;
      for ($rows=0; $rows<4; $rows++) {
        for ($cols=0; $cols<10; $cols++) {
          echo '<input size="6" type="text" name="coaches['.($coachcount++).']" />';
        }
        echo '<br />';
      }
      echo '<input type="hidden" name="id" value="'.pnVarPrepForDisplay($tId).'" />';
      echo '<input type="hidden" name="addcoaches" value="1" />';
      echo '<input type="hidden" name="coachcount" value="'.$coachcount.'" />';
      echo '<input type="submit" value="Add Coaches" /><br />';
      echo '</form>';
    }
    else {
      echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report', array('id' => $tId, 'add' => '1'))).'">Add Coaches</a><br /><br />';

      echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report2')).'" method="post">'
      .'<input type="hidden" name="id" value="'.pnVarPrepForDisplay($tId).'" />'
      .'<input type="submit" value="Next >>" />'
      .'</form>';

    }

    CloseTable();
    OpenTable();
    echo '<h3>Submit data as XML</h3>'.
    'When submitting data it will delete all previously entered data for the tournament<br/>'.
    'When submitting data as XML there must be at least 2 coaches and 2 matches registered<br/>';
    if (pnSecAuthAction(0, 'Users::', '::', ACCESS_ADMIN)) {
      echo 'First, make sure all users are <a href="'.pnVarPrepForDisplay('admin.php?module=User').'"> '
      .'added to the site</a>. You can also <a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired')).'">renew accounts</a>.<br />';
    }
    echo '<form enctype="multipart/form-data" action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'submit')).'" method="post">'
    .'Choose an XML file to upload: <input name="result" type="file" /><br />'
    .'<input type="hidden" name="id" value="'.pnVarPrepForDisplay($tId).'" />'
    .'<input type="submit" value="Upload Tournament Result" />'
    .'</form>';
    CloseTable();
    include 'footer.php';
  }
  return true;
}
function NAF_tournaments_report2($args) {
  $dbconn =& pnDBGetConn(true);
  list($tId, $edit, $submit, $coachcount, $race, $coach) = pnVarCleanFromInput('id', 'edit', 'submit', 'coachcount', 'race', 'coach');

  if ($submit == 1) {
    for ($i=0; $i<$coachcount; $i++) {
      $query = "UPDATE naf_tournamentcoach SET race=".pnVarPrepForStore($race[$i])." WHERE nafcoach=".pnVarPrepForStore($coach[$i])." AND naftournament=".pnVarPrepForStore($tId);
      $dbconn->Execute($query);
    }
    pnRedirect(pnModURL('NAF', 'tournaments', 'report2', array('id' => $tId)));
    return true;
  }

  $qry = "SELECT * FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($tId);
  $tourney = $dbconn->Execute($qry);
  if ($tourney->EOF) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
    return true;
  }
  $qry = "SELECT coachid, coachfirstname, coachlastname, race"
  ." FROM naf_coach c, naf_tournamentcoach tc"
  ." WHERE tc.nafcoach=c.coachid"
  ." AND tc.naftournament=".pnVarPrepForStore($tId);
  $coaches = $dbconn->Execute($qry);

  $qry = "SELECT raceid, name FROM naf_race ORDER BY name";
  $races = $dbconn->Execute($qry);
  $raceArr[0] = "[ None ]";
  for ( ; !$races->EOF; $races->MoveNext() ) {
    $raceArr[$races->Fields('raceid')] = $races->Fields('name');
  }

  include 'header.php';
  OpenTable();

  echo '<h3>Race selection in \''.pnVarPrepForDisplay($tourney->Fields('tournamentname')).'\'</h3>';

  if ($edit==1) {
    echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report2')).'" method="post">';
  }

  echo '<table border="1">';
  echo '<tr><th colspan="3">Races</th></tr>'."\n";
  echo '<tr><th>Username</th><th>Coach</th><th>Race</th></tr>'."\n";

  $count=0;
  for ( ; !$coaches->EOF; $coaches->MoveNext() ) {
    echo '<tr><td>'.pnUserGetVar('uname', $coaches->Fields('coachid')).'</td><td>'
    .$coaches->Fields('coachfirstname').' '.$coaches->Fields('coachlastname').'</td>';

    if ($edit == 1) {
      echo '<td><input type="hidden" name="coach['.$count.']" value="'.$coaches->Fields('coachid').'" />';
      echo '<select name="race['.($count++).']">';
      echo '<option value="">[ Select Race ]</option>';
      for ($i=1; $i<=count($raceArr); $i++) {
        echo '<option value="'.$i.'"'.($coaches->Fields('race')==$i?' selected="selected"':'').'>'.$raceArr[$i].'</option>';
      }
      echo '</select></td>';
    }
    else {
      echo '<td>'.$raceArr[$coaches->Fields('race')].'</td>';
    }

    echo '</tr>'."\n";
  }
  echo '</table>';

  if ($edit==1) {
    echo '<input type="submit" value="Update Races" />';
    echo '<input type="hidden" name="id" value="'.pnVarPrepForDisplay($tId).'" />';
    echo '<input type="hidden" name="coachcount" value="'.$count.'" />';
    echo '<input type="hidden" name="submit" value="1" />';
    echo '</form>';
  }
  else {
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report2', array('id' => $tId, 'edit' => '1'))).'">Edit races</a><br />';
  }
  echo '<br />';
  echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report3')).'" method="post">';
  if ($edit != 1) {
    echo '<input type="submit" value="Report Matches" />';
  }
  echo '<input type="hidden" name="id" value="'.pnVarPrepForDisplay($tId).'" />';
  echo '</form>';

  CloseTable();
  include 'footer.php';
  return true;
}
function NAF_tournaments_report3($args) {
  $dbconn =& pnDBGetConn(true);
  list($advanced, $submit, $tId, $addCoaches, $delCoach, $coachcount, $coaches, $insert_match, $insert_done) = pnVarCleanFromInput('advanced', 'submit', 'id', 'addcoaches', 'delcoach', 'coachcount', 'coaches', 'insert_match', 'insert_done');

  $NUM_REPORTS = 10;
  if ($insert_match && !$insert_done){
    $NUM_REPORTS = 1;
  }
  // Check to see if form has already been submitted
  if ($submit == 1) {
    list($c1, $r1, $tr1, $bh1, $si1, $rip1, $w1, $s1,
    $c2, $r2, $tr2, $bh2, $si2, $rip2, $w2, $s2, $gate, $gtr, $date, $hour) =
    pnVarCleanFromInput('c1', 'r1', 'tr1', 'bh1', 'si1', 'rip1', 'w1', 's1',
    'c2', 'r2', 'tr2', 'bh2', 'si2', 'rip2', 'w2', 's2', 'gate', 'gtr', 'date', 'hour');

    if (strlen($date)==0 || strlen($hour)==0) {
      echo "You must set hour and date for the games. Press the Back button on your browser.";
      return true;
    }

    for ($i=0; $i<$NUM_REPORTS; $i++) {
      if (($gtr+0) > 0) {
        if (($tr1[$i]+0) == 0) {
          $tr1[$i]=$gtr+0;
        }
        if (($tr2[$i]+0) == 0) {
          $tr2[$i]=$gtr+0;
        }
      }
      if ($c1[$i]+0 < 1 && $c2[$i]+0 < 1)
      continue;

      //Check row: This needs to be expanded
      if ($insert_match){
        insertIntoUnverified($c1[$i], $r1[$i], $tr1[$i], $bh1[$i], $si1[$i], $rip1[$i], $w1[$i], $s1[$i],
        $s2[$i], $c2[$i], $r2[$i], $tr2[$i], $bh2[$i], $si2[$i], $rip2[$i], $w2[$i], $gate[$i], $tId, $insert_match, $date, $hour);
        $insert_match = '';
      }
      else if ($tr1[$i] > 0){
        // Output to naf_unverified_game
        appendToUnverified($c1[$i], $r1[$i], $tr1[$i], $bh1[$i], $si1[$i], $rip1[$i], $w1[$i], $s1[$i],
        $s2[$i], $c2[$i], $r2[$i], $tr2[$i], $bh2[$i], $si2[$i], $rip2[$i], $w2[$i], $gate[$i], $tId, $date, $hour);

      }
      else{
        //Need to do something here in case of errors
      }
    }

  }

  $qry = "SELECT * FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($tId);
  $tourney = $dbconn->Execute($qry);
  if ($tourney->EOF) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
  }
  else {
    include 'header.php';
    OpenTable();

    echo '<h3>Match reporting for \''.pnVarPrepForDisplay($tourney->Fields('tournamentname')).'\'</h3>';

    $qry = "SELECT pn_uid, pn_uname FROM nuke_users u, naf_tournamentcoach tc WHERE tc.nafcoach=u.pn_uid AND "
    ."tc.naftournament=".pnVarPrepForStore($tId)." ORDER BY pn_uname";
    $coaches = $dbconn->Execute($qry);
    $coachSel = '<option value="">[ Select Coach ]</option>';
    for ( ; !$coaches->EOF; $coaches->MoveNext() ) {
      $coachSel .= '<option value="'.$coaches->Fields('pn_uid').'">'.$coaches->Fields('pn_uname').'</option>';
    }

    if ($advanced == 1) {
      $qry = "SELECT raceid, name FROM naf_race ORDER BY name";
      $races = $dbconn->Execute($qry);
      $raceSel = '<option value="0">[Keep Default]</option>';
      for ( ; !$races->EOF; $races->MoveNext() ) {
        $raceSel .= '<option value="'.$races->Fields('raceid').'">'.$races->Fields('name').'</option>';
      }
    }

    $req = '<span style="vertical-align: top; font-size: 0.9em; color: red;">*</span>';

    echo '<form action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report3')).'" method="post">';

    if ($advanced != 1) {
      echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report3', array('id' => $tId, 'advanced' => '1'))).'">Switch to Advanced mode</a>';
    }
    else {
      echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report3', array('id' => $tId))).'">Switch to Simple mode</a>';
    }

    echo '<table border="0">';
    echo '<tr><td>Global Team Rating:</td><td><input type="text" name="gtr" value="100" /></td></tr>'."\n";
    echo '<tr><td>Date:</td><td><input type="text" name="date" value="'.pnVarPrepForDisplay($tourney->Fields('tournamentstartdate')).'" /> (yyyy-mm-dd)</td></tr>'."\n";
    echo '<tr><td>Hour:</td><td>';
    echo '<select name="hour">';
    for ($i=0; $i<24; $i++) {
      $str = $i;
      if (strlen($str) < 2)
      $str = "0" . $str;
      echo '<option value="'.$i.'">'.$str.'</option>'."\n";
    }
    echo '</select></td></tr>'."\n";
    echo '</table>';

    if ($advanced == 1) {
      echo '<table border="1">'
      .'<tr><th colspan="5">Home</th><th>Score'.$req.'</th><th colspan="5">Away</th><th>Gate</th></tr>'
      .'<tr align="center"><td>Coach'.$req.'</td><td>Race'.$req.'</td><td>Team<br />Rating'.$req.'</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</th>'
      .'<td>Coach'.$req.'</td><td>Race'.$req.'</td><td>Team<br />Rating'.$req.'</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><td>&nbsp;</td></tr>'."\n";
    }
    else {
      echo '<table border="1">'
      .'<tr><th colspan="2">Home</th><th>Score'.$req.'</th><th colspan="2">Away</th></tr>'
      .'<tr align="center"><td>Coach'.$req.'</td><td>Team<br />Rating'.$req.'</td><th>&nbsp;</th>'
      .'<td>Coach'.$req.'</td><td>Team<br />Rating'.$req.'</td></tr>'."\n";
    }

    for ($i=0; $i<$NUM_REPORTS; $i++) {
      echo '<tr>'
      .'<td><select name="c1['.$i.']">'.$coachSel.'</select></td>'
      .($advanced==1?'<td><select name="r1['.$i.']">'.$raceSel.'</select></td>':'')
      .'<td><input size="2" type="text" name="tr1['.$i.']" /></td>'
      .($advanced==1?'<td><input size="1" type="text" name="bh1['.$i.']" /><input size="1" type="text" name="si1['.$i.']" /><input size="1" type="text" name="rip1['.$i.']" /></td>':'')
      .($advanced==1?'<td><input size="5" type="text" name="w1['.$i.']" /></td>':'')
      .'<td><table border="0"><tr><td><input size="1" type="text" name="s1['.$i.']" /></td><td>-</td><td><input size="1" type="text" name="s2['.$i.']" /></td></tr></table></td>'
      .'<td><select name="c2['.$i.']">'.$coachSel.'</select></td>'
      .($advanced==1?'<td><select name="r2['.$i.']">'.$raceSel.'</select></td>':'')
      .'<td><input size="2" type="text" name="tr2['.$i.']" /></td>'
      .($advanced==1?'<td><input size="1" type="text" name="bh2['.$i.']" /><input size="1" type="text" name="si2['.$i.']" /><input size="1" type="text" name="rip2['.$i.']" /></td>':'')
      .($advanced==1?'<td><input size="5" type="text" name="w2['.$i.']" /></td>':'')
      .($advanced==1?'<td><input size="5" type="text" name="gate['.$i.']" /></td>':'').'</tr>'."\n";
    }
    echo '</table>';
    echo 'Columns marked with '.$req.' are required.<br />';

    echo '<input type="hidden" name="id" value="'.pnVarPrepForDisplay($tId).'" />'
    .'<input type="hidden" name="insert_done" value="1" />'
    .'<input type="hidden" name="insert_match" value="'.$insert_match.'" />'
    .'<input type="hidden" name="submit" value="1" />';

    echo '<br /><input type="submit" value="Add Matches" /><br />';

    echo '</form>';

    // Get list of games in temp table for this tournament
    $query = "SELECT g.*, r1.name as racehomename, r2.name as raceawayname"
    ." FROM naf_unverified_game g, naf_race r1, naf_race r2"
    ." WHERE g.tournamentid=".pnVarPrepForStore($tId).' '
    ." AND g.racehome = r1.raceid"
    ." AND g.raceaway = r2.raceid"
    ." ORDER BY g.game_order ";
    $games = $dbconn->Execute($query);
    if (!$games->EOF){
      $numGames = $games->RecordCount();
      echo $numGames.' matches reported.<br />';
      // Output table header
      if ($advanced == 1) {
        echo '<table border="1">'
        .'<tr><th>Time</th><th colspan="5">Home</th><th>Score</th><th colspan="5">Away</th><th>Gate</th><th>Op</th></tr>'
        .'<tr align="center"><td>&nbsp;</td><td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</th>'
        .'<td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><td>&nbsp;</td><td>&nbsp;</td></tr>'."\n";
      }
      else {
        echo '<table border="1">'
        .'<tr><th>Time</th><th colspan="2">Home</th><th>Score</th><th colspan="2">Away</th><th>Op</th></tr>'
        .'<tr align="center"><td>&nbsp;</td><td>Coach</td><td>Team<br />Rating</td><th>&nbsp;</th>'
        .'<td>Coach</td><td>Team<br />Rating</td><td>&nbsp;</td></tr>'."\n";
      }

      // Output games currently in temp table
      for ( ; !$games->EOF; $games->MoveNext() ) {
        // Get coach 1 name
        /*$query = "SELECT pn_uname, coachid "
        ."FROM naf_coach, nuke_users "
        ."WHERE coachid=pn_uid "
        ."AND pn_uid = ".pnVarPrepForStore($games->fields('homecoachid'));
        $result = $dbconn->Execute($query);
        $coach_1 = $result->fields[0];

        // Get coach 2 name
        $query = "SELECT pn_uname, coachid "
        ."FROM naf_coach, nuke_users "
        ."WHERE coachid=pn_uid "
        ."AND pn_uid = ".pnVarPrepForStore($games->fields('awaycoachid'));
        $result = $dbconn->Execute($query);
        $coach_2 = $result->fields[0];

        // Get race 1 name
        $query = "SELECT name "
        ."FROM naf_race "
        ."WHERE raceid = ".pnVarPrepForStore($games->fields('racehome'));
        $result = $dbconn->Execute($query);
        $race_1 = $result->fields[0];

        // Get race 2 name
        $query = "SELECT name "
        ."FROM naf_race "
        ."WHERE raceid = ".pnVarPrepForStore($games->fields('raceaway'));
        $result = $dbconn->Execute($query);
        $race_2 = $result->fields[0];*/

        //Output table row

        echo '<tr>'
        .'<td>'.$games->Fields('date').' '.$games->Fields('hour').':00</td>'
        //.'<td>'.$coach_1.'</td>'
        .'<td>'.pnUserGetVar('uname', $games->fields('homecoachid')).'</td>'
        //.($advanced==1?'<td>'.$race_1.'</td>':'')
        .($advanced==1?'<td>'.$games->fields('racehomename').'</td>':'')
        .'<td>'.$games->fields('trhome').'</td>'
        .($advanced==1?'<td>'.$games->fields('badlyhurthome').'|'.$games->fields('serioushome').'|'.$games->fields('killshome').'</td>':'')
        .($advanced==1?'<td>'.$games->fields('winningshome').'</td>':'')
        .'<td><table border="0"><tr><td>'.$games->fields('goalshome').'</td><td>-</td><td>'.$games->fields('goalsaway').'</td></tr></table></td>'
        //.'<td>'.$coach_2.'</td>'
        .'<td>'.pnUserGetVar('uname', $games->fields('awaycoachid')).'</td>'
        //.($advanced==1?'<td>'.$race_2.'</td>':'')
        .($advanced==1?'<td>'.$games->fields('raceawayname').'</td>':'')
        .'<td>'.$games->fields('traway').'</td>'
        .($advanced==1?'<td>'.$games->fields('badlyhurtaway').'|'.$games->fields('seriousaway').'|'.$games->fields('killsaway').'</td>':'')
        .($advanced==1?'<td>'.$games->fields('winningsaway').'</td>':'')
        .($advanced==1?'<td>'.$games->fields('gate').'</td>':'')
        //Output links to insert or delete a game
        .'<td>(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'delete_match', array('id' => $tId, 'game_id' => $games->fields('gameid')))).'">Delete</a>)'
        .'(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report3', array('id' => $tId, 'insert_match' => $games->fields('game_order')))).'">Insert</a>)</td></tr>';
      }
      echo '</table> <br />';
      echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'finalize_tournament', array('id' => $tId))).'">Finalize Tournament</a>';
    }


    CloseTable();
    include 'footer.php';
  }
  return true;
}

function NAF_tournaments_main($args) {
  $dbconn =& pnDBGetConn(true);
  list($order, $dir, $showall) = pnVarCleanFromInput('ordercolumn', 'dir', 'showall');

  include 'header.php';
  OpenTable();

  if (strlen($order) == 0) {
    $order = 'tournamentstartdate';
  }

  if ($showall!=1) {
    $showall=0;
  }

  echo '<h3>NAF Tourneys</h3>';

  echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'add')).'">Add Tournament</a><br /><br />'."\n";

  if ($showall == 1) {
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => $order, 'showall' => '0'))).'">Hide past tournaments</a><br /><br />'."\n";
    $qry = "SELECT * FROM naf_tournament WHERE tournamentorganizerid=".pnVarPrepForStore(pnUserGetVar('uid'))." ORDER BY ".pnVarPrepForStore($order.(strlen($dir)>0?" $dir":''));
  }
  else {
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => $order, 'showall' => '1'))).'">Show past tournaments</a><br /><br />'."\n";
    $qry = "SELECT * FROM naf_tournament WHERE tournamentorganizerid=".pnVarPrepForStore(pnUserGetVar('uid'))." AND tournamentenddate>now() "
    ."ORDER BY ".pnVarPrepForStore($order.(strlen($dir)>0?" $dir":''));
  }

  $res = $dbconn->Execute($qry);

  if (!$res->EOF && pnUserGetVar('uid') > 0) {
    echo '<table width="100%" bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
    echo '<tr><th bgcolor="#D9D8D0" colspan="6">Your Tournaments</th></tr>'."\n";
    echo '<tr><th bgcolor="#D9D8D0">Op</th>';
    echo '<th bgcolor="#D9D8D0">Tournament</th><th bgcolor="#D9D8D0">Location</th><th bgcolor="#D9D8D0">Start Date</th>'
    .'<th bgcolor="#D9D8D0">End Date</th>';
    echo '<th bgcolor="#D9D8D0">Status</th>';
    echo '</tr>'."\n";

    for ( ; !$res->EOF; $res->MoveNext() ) {
      echo '<tr>';
      echo '<td bgcolor="#f8f7ee">(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'add', array('id' => $res->Fields('tournamentid'), 'op' => 'edit'))).'">Edit</a>)'
      .' (<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'delete', array('id' => $res->Fields('tournamentid')))).'">Delete</a>)';
      if (strcmp($res->Fields('tournamentstatus'), 'APPROVED') == 0) {
        echo '<br />(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report', array('id' => $res->Fields('tournamentid')))).'">Report Results</a>)';
      }
      echo '</td>';
      echo '<td bgcolor="#f8f7ee"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'view', array('id' => $res->Fields('tournamentid')))).'">'.pnVarPrepForDisplay($res->Fields('tournamentname')).'</a>'
      .'</td><td bgcolor="#f8f7ee">'.$res->Fields('tournamentnation')
      .'</td><td bgcolor="#f8f7ee">'.$res->Fields('tournamentstartdate')
      .'</td><td bgcolor="#f8f7ee">'.$res->Fields('tournamentenddate').'</td>';
      echo '<td bgcolor="#f8f7ee">'.$res->Fields('tournamentstatus').'</td>';
      echo '</tr>'."\n";
    }
    echo '</table><br /><br />';
    $res->Close();
  }

  $query = "SELECT naf_tournament.* FROM naf_tournament"
  ." WHERE "
  ." tournamentenddate >= date_sub(now(), interval 1 month) "
  .(pnUserGetVar('uid')>0?" AND tournamentorganizerid<>".pnVarPrepForStore(pnUserGetVar('uid')):'').' '
  ." AND tournamentenddate <= now()"
  ." ORDER BY ".pnVarPrepForStore($order.(strlen($dir)>0?" $dir":'')).", tournamentname";
  $res = $dbconn->Execute($query);
  if ($res->numRows() > 0) {
    echo '<table width="100%" bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
    echo '<tr><th bgcolor="#D9D8D0" colspan="'.(pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)?"7":"4").'">Recent Tournaments</th></tr>'."\n";
    echo '<tr>';
    if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      echo '<th bgcolor="#D9D8D0">Op</th>';
    }
    echo '<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentname', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentname') ? 'desc' : 'asc'))).'">Tournament</a></th>';
    if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      echo '<th bgcolor="#D9D8D0">Submitted By</th>';
    }
    echo '<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentnation', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentnation') ? 'desc' : 'asc'))).'">Location</a></th>'
    .'<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentstartdate', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentstartdate') ? 'desc' : 'asc'))).'">Start Date</a></th>'
    .'<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentenddate', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentenddate') ? 'desc' : 'asc'))).'">End Date</a></th>';
    if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      echo '<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentstatus', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentstatus') ? 'desc' : 'asc'))).'">Status</a></th>';
    }
    echo '</tr>'."\n";

    for ( ; !$res->EOF; $res->moveNext() ) {
      echo '<tr>';
      if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
        echo '<td bgcolor="#f8f7ee">(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'add', array('id' => $res->Fields('tournamentid'), 'op' => 'edit'))).'">Edit</a>)'
        .' (<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'delete', array('id' => $res->Fields('tournamentid')))).'">Delete</a>)';
        if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) && strcmp($res->Fields('tournamentstatus'), "APPROVED") == 0) {
          echo '<br />(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report', array('id' => $res->Fields('tournamentid')))).'">Report Results</a>)';
        }
        echo '</td>';
      }
      echo '<td bgcolor="#f8f7ee"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'view', array('id' => $res->Fields('tournamentid')))).'">'.pnVarPrepForDisplay($res->Fields('tournamentname')).'</a></td>';
      if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
        echo '<td bgcolor="#f8f7ee">'.pnUserGetVar('uname', $res->Fields('tournamentorganizerid')).'</td>';
      }
      echo '<td bgcolor="#f8f7ee">'.$res->Fields('tournamentnation')
      .'</td><td bgcolor="#f8f7ee">'.$res->Fields('tournamentstartdate')
      .'</td><td bgcolor="#f8f7ee">'.$res->Fields('tournamentenddate').'</td>';
      if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
        echo '<td bgcolor="#f8f7ee">'.$res->Fields('tournamentstatus').'</td>';
      }
      echo '</tr>'."\n";
    }

    echo '</table>';
    echo '<br /><br />';
  }
  $res->close();


  $qry = "SELECT naf_tournament.* as organizer FROM naf_tournament ";
  $qry .= "WHERE 1".(pnUserGetVar('uid')>0?" AND tournamentorganizerid<>".pnVarPrepForStore(pnUserGetVar('uid')):'');
  if ($showall != 1) {
    $qry .= " AND tournamentenddate>now()";
  }
  if (!pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    $qry .= " AND tournamentstatus='APPROVED'";
  }
  $qry .= " ORDER BY $order".(strlen($dir)>0?" $dir":'').", tournamentname";
  $res = $dbconn->Execute($qry);

  echo '<table width="100%" bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
  echo '<tr><th bgcolor="#D9D8D0" colspan="'.(pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)?"7":"4").'">Upcoming Tournaments</th></tr>'."\n";
  echo '<tr>';
  if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    echo '<th bgcolor="#D9D8D0">Op</th>';
  }
  echo '<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentname', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentname') ? 'desc' : 'asc'))).'">Tournament</a></th>';
  if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    echo '<th bgcolor="#D9D8D0">Submitted By</th>';
  }
  echo '<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentnation', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentnation') ? 'desc' : 'asc'))).'">Location</a></th>'
  .'<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentstartdate', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentstartdate') ? 'desc' : 'asc'))).'">Start Date</a></th>'
  .'<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentenddate', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentenddate') ? 'desc' : 'asc'))).'">End Date</a></th>';
  if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    echo '<th bgcolor="#D9D8D0"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', '', array('ordercolumn' => 'tournamentstatus', 'showall' => $showall, 'dir' => ($dir=='asc'&&$order=='tournamentstatus') ? 'desc' : 'asc'))).'">Status</a></th>';
  }
  echo '</tr>'."\n";
  for ( ; !$res->EOF; $res->MoveNext() ) {
    echo '<tr>';
    if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      echo '<td bgcolor="#f8f7ee">(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'add', array('id' => $res->Fields('tournamentid'), 'op' => 'edit'))).'">Edit</a>)'
      .' (<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'delete', array('id' => $res->Fields('tournamentid')))).'">Delete</a>)';
      if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) && strcmp($res->Fields('tournamentstatus'), "APPROVED") == 0) {
        echo '<br />(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'report', array('id' => $res->Fields('tournamentid')))).'">Report Results</a>)';
      }
      echo '</td>';
    }
    echo '<td bgcolor="#f8f7ee"><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'view', array('id' => $res->Fields('tournamentid')))).'">'.pnVarPrepForDisplay($res->Fields('tournamentname')).'</a></td>';
    if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      echo '<td bgcolor="#f8f7ee">'.pnUserGetVar('uname', $res->Fields('tournamentorganizerid')).'</td>';
    }
    echo '<td bgcolor="#f8f7ee">'.$res->Fields('tournamentnation')
    .'</td><td bgcolor="#f8f7ee">'.$res->Fields('tournamentstartdate')
    .'</td><td bgcolor="#f8f7ee">'.$res->Fields('tournamentenddate').'</td>';
    if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
      echo '<td bgcolor="#f8f7ee">'.$res->Fields('tournamentstatus').'</td>';
    }
    echo '</tr>'."\n";
  }
  $res->Close();
  echo '</table>';

  CloseTable();
  include 'footer.php';
  return true;
}

function NAF_tournaments_submit() {
  $dbconn =& pnDBGetConn(true);
  $file = pnVarCleanFromInput('result');
  $xml = file_get_contents($file['tmp_name']);
  $tournament_id = pnVarCleanFromInput('id');
  $qry = "SELECT * FROM naf_tournament WHERE tournamentid=".pnVarPrepForStore($tournament_id);
  $tourney = $dbconn->Execute($qry);
  if ($tourney->EOF) {
    pnRedirect(pnModURL('NAF', 'tournaments'));
  }
  include 'header.php';
  OpenTable();
  if($xml) {
    $data = XML_unserialize($xml);
    $error = false;
    $qry = "SELECT * FROM naf_game naf_game WHERE tournamentid = ".pnVarPrepForStore($tournament_id);
    $result = $dbconn->Execute($qry);
    if(!$result->EOF) {
      $error = true;
      echo 'This tournament has been finalized, data can no longer be submitted by XML, you must edit this tournament manually<br/>';
    }
    if($data) {
      $coaches = array();
      foreach ($data['nafReport']['coaches']['coach'] as $coach_data) {
        if(!array_key_exists('number', $coach_data)) {
          $userid = pnUserGetIDFromName($coach_data['name']);
          if($userid) {
            $coach_data['number'] = $userid;
          }
        }
        if(!array_key_exists('number', $coach_data)) {
          $error = true;
          echo 'User id missing and username '.$coach_data['name'].' is not known by NAF<br/>';
        }
        else if(!$name = pnUserGetVar('uname', $coach_data['number'])) {
          $error = true;
          echo 'There is no valid user with user id '.pnVarPrepForDisplay($coach_data['number']).'<br/>';
          echo 'All new users must be created before submitting results as XML<br/>';
        }
        else if(strcasecmp($name, $coach_data['name']) != 0) {
          $error = true;
          echo 'An incorrect name for user '.pnVarPrepForDisplay($coach_data['number']).' is reported<br/>';
          echo 'Real name: '.$name.'<br/>';
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
        foreach ($data['nafReport']['game'] as $game_data) {
          if(!array_key_exists($game_data['playerRecord'][0]['number'], $coaches) && !in_array($game_data['playerRecord'][0]['name'], $coaches)) {
            $error = true;
            echo 'Coach '.pnVarPrepForDisplay($game_data['playerRecord'][0]['number']).', '.$game_data['playerRecord'][0]['name'].' is not reported as an attendant at the tournament<br/>';
          }
          if(!array_key_exists($game_data['playerRecord'][1]['number'], $coaches) && !in_array($game_data['playerRecord'][1]['name'], $coaches)) {
            $error = true;
            echo 'Coach '.pnVarPrepForDisplay($game_data['playerRecord'][1]['number']).', '.$game_data['playerRecord'][1]['name'].' is not reported as an attendant at the tournament<br/>';
          }
          /*php 4.0-5.0 returns -1 on error, php 5.1-> returns false*/
          if(strtotime($game_data['timeStamp']) === -1 || strtotime($game_data['timeStamp']) === false) {
            $error = true;
            echo 'Timestamp: '.pnVarPrepForDisplay($game_data['timeStamp']).' is not a valid time/date format';
          }
        }
      }
      if(!$error) {
        $qry = "DELETE FROM naf_tournamentcoach WHERE naftournament = ".pnVarPrepForStore($tournament_id);
        $result = $dbconn->Execute($qry);
        if (!$result) {
          echo $qry;
          echo $dbconn->errorMsg();
        }
        $qry = "DELETE FROM naf_unverified_game WHERE tournamentid = ".pnVarPrepForStore($tournament_id);
        $result = $dbconn->Execute($qry);
        if (!$result) {
          echo $qry;
          echo $dbconn->errorMsg();
        }
        foreach ($data['nafReport']['coaches']['coach'] as $coach_data) {
          $qry = "INSERT INTO naf_tournamentcoach (nafcoach, naftournament, race) SELECT ".
          pnVarPrepForStore($coach_data['number'] ? $coach_data['number'] : pnUserGetIDFromName($coach_data['name'])).", ".
          pnVarPrepForStore($tournament_id).", raceid from naf_race where name = '".
          pnVarPrepForStore($coach_data['team'])."'";
          $result = $dbconn->Execute($qry);
          if (!$result) {
            echo $qry;
            echo $dbconn->errorMsg();
          }
        }
        foreach ($data['nafReport']['game'] as $game_data) {
          $date = strtotime($game_data['timeStamp']);
          appendToUnverified(($game_data['playerRecord'][0]['number'] ? $game_data['playerRecord'][0]['number'] : pnUserGetIDFromName($game_data['playerRecord'][0]['name'])),
          0,
          $game_data['playerRecord'][0]['teamRating'],
          $game_data['playerRecord'][0]['badlyHurt'],
          $game_data['playerRecord'][0]['seriouslyInjured'],
          $game_data['playerRecord'][0]['dead'],
          $game_data['playerRecord'][0]['winnings'],
          $game_data['playerRecord'][0]['touchDowns'],
          $game_data['playerRecord'][1]['touchDowns'],
          ($game_data['playerRecord'][1]['number'] ? $game_data['playerRecord'][1]['number'] : pnUserGetIDFromName($game_data['playerRecord'][1]['name'])),
          0,
          $game_data['playerRecord'][1]['teamRating'],
          $game_data['playerRecord'][1]['badlyHurt'],
          $game_data['playerRecord'][1]['seriouslyInjured'],
          $game_data['playerRecord'][1]['dead'],
          $game_data['playerRecord'][1]['winnings'],
          $game_data['gate'],
          $tournament_id,
          date('Y-m-d', $date),
          date('G', $date));
        }
        $qry = "SELECT coachid, coachfirstname, coachlastname, r.name FROM naf_coach c, naf_tournamentcoach tc, naf_race r "
        ."WHERE tc.nafcoach=c.coachid AND tc.naftournament=".pnVarPrepForStore($tournament_id)." AND tc.race=r.raceid";
        $coaches = $dbconn->Execute($qry);
          if (!$coaches) {
            echo $qry;
            echo $dbconn->errorMsg();
          }

        if (!$coaches->EOF) {
          echo '<table border="1">'
          .'<tr><th colspan="4">Tournament NAF attendees</th></tr>'
          .'<tr><th>Coachid</th><th>Login name</th><th>Real Name</th><th>Race</th></tr>'."\n";

          for ( ; !$coaches->EOF; $coaches->MoveNext() ) {
            echo '<tr><td>'.pnVarPrepForDisplay($coaches->Fields('coachid')).'</td>'
            .'<td>'.pnVarPrepForDisplay(pnUserGetVar('uname', $coaches->Fields('coachid'))).'</td>'
            .'<td>'.pnVarPrepForDisplay($coaches->Fields('coachfirstname')).' '.pnVarPrepForDisplay($coaches->Fields('coachlastname')).'</td>'
            .'<td>'.pnVarPrepForDisplay($coaches->Fields('name')).'</td>'
            .'</tr>'."\n";
          }

          echo '</table>';
        }
        // Get list of games in temp table for this tournament
        $query = "SELECT g.*, r1.name as racehomename, r2.name as raceawayname"
        ." FROM naf_unverified_game g, naf_race r1, naf_race r2"
        ." WHERE g.tournamentid=".pnVarPrepForStore($tournament_id)
        ." AND g.racehome = r1.raceid"
        ." AND g.raceaway = r2.raceid"
        ." ORDER BY g.game_order ";
        /*$query = "SELECT * "
        ."FROM naf_unverified_game "
        ."WHERE tournamentid=".pnVarPrepForStore($tournament_id).' '
        ."ORDER BY game_order ";*/
        $games = $dbconn->Execute($query);
        if (!$games->EOF){
          $numGames = $games->RecordCount();
          echo $numGames.' matches reported.<br />';
          // Output table header
          echo '<table border="1">'
          .'<tr><th>Time</th><th colspan="5">Home</th><th>Score</th><th colspan="5">Away</th><th>Gate</th></tr>'
          .'<tr align="center"><td>&nbsp;</td><td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</th>'
          .'<td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><td>&nbsp;</td></tr>'."\n";

          // Output games currently in temp table
          for ( ; !$games->EOF; $games->MoveNext() ) {
            // Get coach 1 name
            /*$query = "SELECT pn_uname, coachid "
            ."FROM naf_coach, nuke_users "
            ."WHERE coachid=pn_uid "
            ."AND pn_uid = ".pnVarPrepForStore($games->fields("homecoachid"));
            $result = $dbconn->Execute($query);
            $coach_1 = $result->fields[0];

            // Get coach 2 name
            $query = "SELECT pn_uname, coachid "
            ."FROM naf_coach, nuke_users "
            ."WHERE coachid=pn_uid "
            ."AND pn_uid = ".pnVarPrepForStore($games->fields("awaycoachid"));
            $result = $dbconn->Execute($query);
            $coach_2 = $result->fields[0];

            // Get race 1 name
            $query = "SELECT name "
            ."FROM naf_race "
            ."WHERE raceid = ".pnVarPrepForStore($games->fields("racehome"));
            $result = $dbconn->Execute($query);
            $race_1 = $result->fields[0];

            // Get race 2 name
            $query = "SELECT name "
            ."FROM naf_race "
            ."WHERE raceid = ".pnVarPrepForStore($games->fields("raceaway"));
            $result = $dbconn->Execute($query);
            $race_2 = $result->fields[0];*/

            //Output table row

            echo '<tr>'
            .'<td>'.pnVarPrepForDisplay($games->Fields('date')).' '.pnVarPrepForDisplay($games->Fields('hour')).':00</td>'
            //.'<td>'.pnVarPrepForDisplay($coach_1).'</td>'
            .'<td>'.pnUserGetVar('uname', $games->fields('homecoachid')).'</td>'
            //.'<td>'.pnVarPrepForDisplay($race_1).'</td>'
            .'<td>'.$games->fields('racehomename').'</td>'
            .'<td>'.pnVarPrepForDisplay($games->fields('trhome')).'</td>'
            .'<td>'.pnVarPrepForDisplay($games->fields('badlyhurthome')).'|'.pnVarPrepForDisplay($games->fields('serioushome')).'|'.pnVarPrepForDisplay($games->fields('killshome')).'</td>'
            .'<td>'.pnVarPrepForDisplay($games->fields('winningshome')).'</td>'
            .'<td><table border="0"><tr><td>'.pnVarPrepForDisplay($games->fields('goalshome')).'</td><td>-</td><td>'.pnVarPrepForDisplay($games->fields('goalsaway')).'</td></tr></table></td>'
            //.'<td>'.pnVarPrepForDisplay($coach_2).'</td>'
            .'<td>'.pnUserGetVar('uname', $games->fields('awaycoachid')).'</td>'
            //.'<td>'.pnVarPrepForDisplay($race_2).'</td>'
            .'<td>'.$games->fields('raceawayname').'</td>'
            .'<td>'.pnVarPrepForDisplay($games->fields('traway')).'</td>'
            .'<td>'.pnVarPrepForDisplay($games->fields('badlyhurtaway')).'|'.pnVarPrepForDisplay($games->fields('seriousaway')).'|'.pnVarPrepForDisplay($games->fields('killsaway')).'</td>'
            .'<td>'.pnVarPrepForDisplay($games->fields('winningsaway')).'</td>'
            .'<td>'.pnVarPrepForDisplay($games->fields('gate')).'</td></tr>'."\n";
          }
          echo '</table><br />';
          echo '<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'finalize_tournament', array('id' => $tournament_id))).'">Finalize Tournament</a>, if the tournament is finalized it is no longer possible to submit data via XML';
        }
        CloseTable();
        OpenTable();
        echo '<h3>Resubmit data as XML</h3>';
        echo 'When submitting data it will delete all previously entered data for the tournament<br/>';
        echo 'When submitting data as XML there must be at least 2 coaches and 2 matches registered<br/>';
        if (pnSecAuthAction(0, 'Users::', '::', ACCESS_ADMIN)) {
          echo 'First, make sure all users are <a href="'.pnVarPrepForDisplay('admin.php?module=User').'"> '
          .'added to the site</a>. You can also <a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'expired')).'">renew accounts</a>.<br />';
        }
        echo '<form enctype="multipart/form-data" action="'.pnVarPrepForDisplay(pnModURL('NAF', 'tournaments', 'submit')).'" method="post">'
        .'Choose an XML file to upload: <input name="result" type="file" /><br />'
        .'<input type="hidden" name="id" value="'.pnVarPrepForDisplay($tournament_id).'" />'
        .'<input type="submit" value="Upload Tournament Result" />'
        .'</form>';
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
  else {
    echo '<br/>No file submitted, correct and try again<br/>';
  }
  CloseTable();
  include 'footer.php';
  return true;
}
?>
