<?
pnRedirect(pnModURL('NAF', 'tournaments'));
exit;
//require_once 'NAF/include/db.php';
require_once 'NAF/include/util.php';
require_once 'NAF/include/output_functions.php';

require_once 'NAF/include/countries.php';

if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
  $secAdmin=true;
}
else {
  $secAdmin=false;
}

$user = pnUserGetVar('uid');

function makeNationOption($nation) {
  global $tNation;
  if (strlen($nation) > 0) {
    echo "<option value=\"$nation\"".(strcmp($nation, $tNation)==0?" selected":"").">$nation</option>";
  }
  else {
    echo "<option value=\"\">---</option>";
  }

}

switch($op) {
  case 'edit':
  case 'add':
    include 'header.php';
    OpenTable();

    if (strcmp($op, "add")==0) {
      list($tName, $tAddress1, $tAddress2, $tCity, $tState, $tZip, $tNation, $tUrl, $tNotesurl, $tStartdate, $tEnddate,
      $tType, $tStyle, $tScoring, $tCost, $tNaffee, $tNafdiscount, $tInformation, $tEmail, $tOrg, $tStatus, $tMajor ) =
      pnVarCleanFromInput('name', 'address1', 'address2', 'city', 'state', 'zip', 'nation', 'url', 'notesurl', 'startdate',
                             'enddate', 'type', 'style', 'scoring', 'cost', 'naffee', 'nafdiscount', 'information',
                             'email', 'org', 'status', 'major');
    }
    else {
      list($dbconn) = pnDBGetConn();
      $pntable = pnDBGetTables();
      $tournamentid = pnVarCleanFromInput('id');

      $res = $dbconn->Execute("select * from naf_tournament where tournamentid=$tournamentid");

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

    echo "<h4>".(strcmp($op, "add")==0?"Add":"Edit")." Tournament</h4>";

    echo makeFormHeader('tournaments', (strcmp($op, "add")==0?"insert":"update"));
    echo "<input type=\"hidden\" name=\"id\" value=\"$tournamentid\" />";

    if ($secAdmin) {
      echo "<b>Status</b><select name=\"status\">"
      ."<option value=\"NEW\"".(strcmp($tStatus, "NEW")==0?" selected":"").">New</option>"
      ."<option value=\"PENDING\"".(strcmp($tStatus, "PENDING")==0?" selected":"").">Pending</option>"
      ."<option value=\"APPROVED\"".(strcmp($tStatus, "APPROVED")==0?" selected":"").">Approved</option>"
      ."<option value=\"NOTAPPROVED\"".(strcmp($tStatus, "NOTAPPROVED")==0?" selected":"").">Not Approved</option>"
      ."</select>";
      echo "<br /><b>Major Tournament</b><select name=\"major\">"
      ."<option value=\"no\"".(strcmp($tMajor, "no")==0?" selected":"").">No</option>"
      ."<option value=\"yes\"".(strcmp($tMajor, "yes")==0?" selected":"").">Yes</option>"
      ."</select>";
    }

    echo "<table border=\"0\" cellpadding=\"8\"><tr><td>";

    echo "<table border=\"0\"><tr><td valign=\"top\">";
    echo "<tr><td>Tournament Name</td><td>".makeFormInput('name', $tName)."</td></tr>";
    echo "<tr><td>Organizer</td><td>".makeFormInput('org', $tOrg)."</td></tr>";
    echo "<tr><td>Start Date (YYYY-MM-DD)</td><td>".makeFormInput('startdate', $tStartdate)."</td></tr>";
    echo "<tr><td>End Date (YYYY-MM-DD)</td><td>".makeFormInput('enddate', $tEnddate)."</td></tr>";
    echo "<tr><td>Type</td><td><select name=\"type\">"
    ."<option value=\"OPEN\"".(strcmp($tType, "OPEN")==0?" selected":"").">Open</option>"
    ."<option value=\"INVITATIONAL\"".(strcmp($tType, "INVITATIONAL")==0?" selected":"").">Invitational</option></td></tr>";
    echo "<tr><td>Style</td><td>".makeFormInput('style', $tStyle)."</td></tr>";
    echo "<tr><td>Scoring</td><td>".makeFormInput('scoring', $tScoring)."</td></tr>";
    echo "<tr><td>Cost</td><td>".makeFormInput('cost', $tCost)."</td></tr>";
    echo "<tr><td>NAF Fee Included</td><td>".makeFormInput('naffee', '', 'checkbox', '', (strcmp($tNaffee, "yes")==0)?'checked':'')."</td></tr>";
    echo "<tr><td>NAF Member Discount</td><td>".makeFormInput('nafdiscount', '', 'checkbox', '', (strcmp($tNafdiscount, "yes")==0)?'checked':'')."</td></tr>";
    echo "<tr><td>Email</td><td>".makeFormInput('email', $tEmail)."</td></tr>";
    echo "<tr><td>Webpage URL</td><td>".makeFormInput('url', $tUrl)."</td></tr>";
    echo "<tr><td>Webpage Name</td><td>".makeFormInput('notesurl', $tNotesurl)."</td></tr>";

    echo "</table></td><td valign=\"top\"><table border=\"0\">";

    echo "<tr><th colspan=\"2\">Tournament Location</th></tr>";
    echo "<tr><td>Address 1</td><td>".makeFormInput('address1', $tAddress1)."</td></tr>";
    echo "<tr><td>Address 2</td><td>".makeFormInput('address2', $tAddress2)."</td></tr>";
    echo "<tr><td>City</td><td>".makeFormInput('city', $tCity)."</td></tr>";
    echo "<tr><td>State</td><td>".makeFormInput('state', $tState)."</td></tr>";
    echo "<tr><td>Zip</td><td>".makeFormInput('zip', $tZip)."</td></tr>";

    echo "<tr><td>Nation</td><td><select name=\"nation\">";
    array_map("makeNationOption", $countries);
    echo "</select></td></tr>";

    echo "<tr><th colspan=\"2\">Information</th></tr><tr>"
    ."<td colspan=\"2\"><textarea rows=\"10\" cols=\"40\" name=\"information\">".pnVarPrepForDisplay($tInformation)."</textarea></td></tr>";

    echo "</table></td></tr>";

    echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Submit\" /></td></tr>";

    echo "</table>";

    echo makeFormFooter();

    CloseTable();
    include 'footer.php';
    break;
  case 'delete':
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($id, $confirm) = pnVarCleanFromInput('id', 'confirm');

    $res = $dbconn->Execute("select tournamentorganizerid, tournamentname from naf_tournament where tournamentid=$id");

    if (!$secAdmin) {
      if ($res->EOF || strcmp($res->fields[0], $user)!=0) {
        $res->Close();
        pnRedirect("naf.php?page=tournaments");
      }
    }

    if ($confirm == 1) {
      $dbconn->Execute("delete from naf_tournament where tournamentid=$id");
      pnRedirect("naf.php?page=tournaments");
    }
    else {
      include 'header.php';
      OpenTable();
      echo "<b>Are you sure you want to delete the '".$res->fields[1]."' tounament?</b><br /><br />";
      echo "<a href=\"naf.php?page=tournaments&amp;op=delete&amp;id=$id&amp;confirm=1\">Yes</a> &nbsp; &nbsp; "
      ."<a href=\"naf.php?page=tournaments\">No</a>";
      CloseTable();
      include 'footer.php';
    }

    $res->Close();

    break;

  case 'delete_match':

    $game_id = pnVarCleanFromInput('game_id');
    $tournament_id = pnVarCleanFromInput('id');
    deleteFromUnverified($game_id, $tournament_id);
    pnRedirect("naf.php?page=tournaments&op=report3&id=$tournament_id");
    break;

  case 'finalize_tournament':

    $tournament_id = pnVarCleanFromInput('id');
    outputFinal($tournament_id);
    // Confirmation needs to be added here. Also, the updater should be called and whatever needs to be done to set a tournament as completed


    pnRedirect("naf.php?page=tournaments&op=report3&id=$tournament_id");
    break;

  case 'update':
  case 'insert':
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($tName, $tAddress1, $tAddress2, $tCity, $tState, $tZip, $tNation, $tUrl, $tNotesurl, $tStartdate, $tEnddate,
    $tType, $tStyle, $tScoring, $tCost, $tNaffee, $tNafdiscount, $tInformation, $tEmail, $tOrg, $id, $tStatus, $tMajor ) =
    pnVarCleanFromInput('name', 'address1', 'address2', 'city', 'state', 'zip', 'nation', 'url', 'notesurl', 'startdate',
                             'enddate', 'type', 'style', 'scoring', 'cost', 'naffee', 'nafdiscount', 'information',
                             'email', 'org', 'id', 'status', 'major');

                             $user = pnUserGetVar('uid');

                             if (!$user || $user == 0) {
                               pnRedirect("/");
                               exit;
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
                               if (!$secAdmin)
                               $tMajor = 'no';
                               $res = $dbconn->Execute("insert into naf_tournament (tournamentorganizerid, tournamentname, tournamentaddress1, "
                               ."tournamentaddress2, tournamentcity, tournamentstate, tournamentzip, tournamentnation, "
                               ."tournamenturl, tournamentnotesurl, tournamentstartdate, tournamentenddate, tournamenttype, "
                               ."tournamentstyle, tournamentscoring, tournamentcost, tournamentnaffee, tournamentnafdiscount, "
                               ."tournamentinformation, tournamentemail, tournamentorg, tournamentmajor ) "
                               ."values ( $user, '".pnVarPrepForStore($tName)."', '".pnVarPrepForStore($tAddress1)."', "
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
                               if (!$secAdmin) {
                                 $res = $dbconn->Execute("select tournamentorganizerid from naf_tournament where tournamentid=$id");
                                 if ($res->fields[0] != $user) {
                                   pnRedirect("naf.php?page=tournaments");
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
                               if ($secAdmin) {
                                 $qry .= ", tournamentstatus='".pnVarPrepForStore($tStatus)."'"
                                 .", tournamentmajor='".pnVarPrepForStore($tMajor)."'";
                               }
                               $qry .= " where tournamentid=$id";
                               $res = $dbconn->Execute($qry);
                             }

                             $err = $dbconn->ErrorMsg();

                             if (strlen($err) == 0) {
                               pnRedirect('naf.php?page=tournaments');
                             }
                             else {
                               include 'header.php';
                               OpenTable();
                               echo "Error in database query:<br />"
                               ."$err<br /><br />Query:<br />$qry<br /><br />"
                               ."<a href=\"naf.php?page=tournaments\">Back to tournaments page</a>";
                               CloseTable();
                               include 'footer.php';
                             }

                             break;
  case 'view':
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $tournamentid = pnVarCleanFromInput('id');

    $res = $dbconn->Execute("select * from naf_tournament where tournamentid=".pnVarPrepForStore($tournamentid));

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

    echo "<table border=\"0\" cellpadding=\"8\"><tr><td>";

    echo "<table border=\"0\"><tr><td valign=\"top\">";
    echo "<tr><td><b>Tournament Name</b></td><td>$tName</td></tr>";
    echo "<tr><td><b>Organizer</b></td><td>$tOrg</td></tr>";
    echo "<tr><td><b>Start Date (YYYY-MM-DD)</b></td><td>$tStartdate</td></tr>";
    echo "<tr><td><b>End Date (YYYY-MM-DD)</b></td><td>$tEnddate</td></tr>";
    echo "<tr><td><b>Type</b></td><td>$tType</td></tr>";
    echo "<tr><td><b>Style</b></td><td>$tStyle</td></tr>";
    echo "<tr><td><b>Scoring</b></td><td>$tScoring</td></tr>";
    echo "<tr><td><b>Cost</b></td><td>$tCost</td></tr>";
    echo "<tr><td><b>NAF Fee Included</b></td><td>$tNaffee</td></tr>";
    echo "<tr><td><b>NAF Member Discount</b></td><td>$tNafdiscount</td></tr>";
    echo "<tr><td><b>Email</b></td><td>$tEmail</td></tr>";
    echo "<tr><td><b>Webpage</b></td><td><a href=\"$tUrl\">$tNotesurl</a></td></tr>";

    echo "</table></td><td width=\"20\">&nbsp;</td><td valign=\"top\"><table border=\"0\">";

    echo "<tr><th colspan=\"2\">Tournament Location</th></tr>";
    echo "<tr><td><b>Address</b></td><td>$tAddress1</td></tr>";
    echo "<tr><td>&nbsp;</td><td>$tAddress2</td></tr>";
    echo "<tr><td><b>City</b></td><td>$tCity</td></tr>";
    echo "<tr><td><b>State</b></td><td>$tState</td></tr>";
    echo "<tr><td><b>Zip</b></td><td>$tZip</td></tr>";

    echo "<tr><td><b>Nation</b></td><td>$tNation</td></tr>";

    echo "<tr><th colspan=\"2\"><br />Information</th></tr><tr>"
    ."<td colspan=\"2\">".nl2br(pnVarPrepHTMLDisplay($tInformation))."</td></tr>";

    echo "</table></td></tr>";
    echo "</table>";

    echo "<a href=\"naf.php?page=view&amp;id=$tournamentid\">View Matches</a><br />";

    echo "<a href=\"naf.php?page=tournaments\">Back to tournament list</a>";

    CloseTable();
    include 'footer.php';
    break;
  case 'report':
    list($tId, $addCoaches, $delCoach, $coachcount, $coaches, $add) = pnVarCleanFromInput('id', 'addcoaches', 'delcoach', 'coachcount', 'coaches', 'add');

    $qry = "select * from naf_tournament where tournamentid=$tId";
    $tourney = $dbconn->Execute($qry);
    if ($tourney->EOF) {
      pnRedirect("naf.php?page=tournaments");
    }
    else {
      if ($addCoaches == 1) {
        for ($i=0; $i<$coachcount; $i++) {
          $coach = pnVarPrepForStore($coaches[$i]);
          if ($coach != "") {
            $coach = trim($coach);
            if (strlen($list) > 0) {
              $list .= ",";
            }
            $list .= "'$coach'";
          }
        }
        $qry = "insert into naf_tournamentcoach (nafcoach, naftournament) select coachid, $tId from naf_coach c, nuke_users nu where nu.pn_uid=c.coachid and (nu.pn_uname in ($list) or nu.pn_uid in ($list))";
        $dbconn->Execute($qry);
      }
      if ($delCoach > 0) {
        $qry = "delete from naf_tournamentcoach where naftournament=$tId and nafcoach=$delCoach";
        $dbconn->Execute($qry);
      }

      $qry = "select raceid, name from naf_race order by name";
      $races = $dbconn->Execute($qry);
      $raceArr[0] = "[ None ]";
      $raceSel = "<option value=\"\">[ Select Race ]</option>";
      for ( ; !$races->EOF; $races->MoveNext() ) {
        $raceArr[$races->Fields('raceid')] = $races->Fields('name');
        $raceSel .= "<option value=\"".$races->Fields('raceid')."\">".$races->Fields('name')."</option>";
      }

      include 'header.php';
      OpenTable();

      $tourneyName = pnVarPrepForDisplay($tourney->Fields('tournamentname'));

      echo "<h3>Attending NAF coaches in '$tourneyName'</h3>";

      if (pnSecAuthAction(0, "Users::", "::", ACCESS_ADMIN)) {
        echo "First, make sure all users are <a href=\"http://www.bloodbowl.net/admin.php?module=User&amp;op=main\"> "
        ."added to the site</a>. You can also <a href=\"/naf.php?page=expired\">renew accounts</a>.<br />";
      }

      $qry = "select coachid, pn_uname, coachfirstname, coachlastname, race from nuke_users u, naf_coach c, naf_tournamentcoach tc "
      ."where tc.nafcoach=c.coachid and tc.naftournament=$tId and pn_uid=coachid";
      $coaches = $dbconn->Execute($qry);

      if (!$coaches->EOF) {
        echo "<table border=\"1\">"
        ."<tr><th colspan=\"5\">Tournament NAF attendees</th></tr>"
        ."<tr><th>Op</th><th>Coachid</th><th>Login name</th><th>Real Name</th><th>Race</th></tr>";

        $hasCoaches = false;
        for ( ; !$coaches->EOF; $coaches->MoveNext() ) {
          list($cId, $cFirst, $cLast, $cUname, $cRace) = pnVarPrepForDisplay( $coaches->Fields('coachid'), $coaches->Fields('coachfirstname'), $coaches->Fields('coachlastname'), $coaches->Fields('pn_uname'), $coaches->Fields('race') );
          echo "<tr><td>(<a href=\"naf.php?page=tournaments&amp;op=report&amp;id=$tId&amp;delcoach=$cId\">Del</a>)</td>"
          ."<td>$cId</td>"
          ."<td>$cUname</td>"
          ."<td>$cFirst $cLast</td>"
          ."<td>".$raceArr[$cRace]."</td>"
          ."</tr>";
          $hasCoaches = true;
        }

        echo "</table>";
      }

      if ($hasCoaches == false || isset($add)) {
        echo "<br /><div style=\"font-size: 1.4em;\">Type in the usernames or membership numbers of the coaches you want to add:</div>";
        echo "<form action=\"naf.php\" method=\"post\">";
        $coachcount = 0;
        for ($rows=0; $rows<4; $rows++) {
          for ($cols=0; $cols<10; $cols++) {
            echo "<input size=\"6\" type=\"text\" name=\"coaches[".($coachcount++)."]\"> ";
          }
          echo "<br />";
        }
        echo "<input type=\"hidden\" name=\"page\" value=\"tournaments\" />";
        echo "<input type=\"hidden\" name=\"op\" value=\"report\" />";
        echo "<input type=\"hidden\" name=\"id\" value=\"$tId\" />";
        echo "<input type=\"hidden\" name=\"addcoaches\" value=\"1\" />";
        echo "<input type=\"hidden\" name=\"coachcount\" value=\"$coachcount\" />";
        echo "<input type=\"submit\" value=\"Add Coaches\" /><br />";
        echo "</form>";
      }
      else {
        echo "<a href=\"naf.php?page=tournaments&amp;op=report&amp;id=$tId&amp;add=1\">Add Coaches</a><br /><br />";

        echo "<form action=\"naf.php\" method=\"get\">"
        ."<input type=\"hidden\" name=\"page\" value=\"tournaments\" />"
        ."<input type=\"hidden\" name=\"op\" value=\"report2\" />"
        ."<input type=\"hidden\" name=\"id\" value=\"$tId\" />"
        ."<input type=\"submit\" value=\"Next >>\" />"
        ."</form>";

      }

      CloseTable();
      include 'footer.php';
    }
    break;
  case 'report2':  // Set default races for the participants
    list($tId, $edit, $submit, $coachcount, $race, $coach) = pnVarCleanFromInput('id', 'edit', 'submit', 'coachcount', 'race', 'coach');

    function makeRaceOptions($selected) {
      global $raceArr;

      $raceSel = "<option value=\"\">[ Select Race ]</option>";
      for ($i=1; $i<=count($raceArr); $i++) {
        $raceSel .= "<option value=\"$i\"".($selected==$i?" selected=\"1\"":"").">".$raceArr[$i]."</option>";
      }
      return $raceSel;
    }

    if ($submit == 1) {
      for ($i=0; $i<$coachcount; $i++) {
        $rid = $race[$i];
        $cid = $coach[$i];
        list($rid, $cid) = pnVarPrepForStore($rid, $cid);
        $query = "UPDATE naf_tournamentcoach SET race=$rid WHERE nafcoach=$cid and naftournament=$tId";
        $dbconn->Execute($query);
      }
      pnRedirect("naf.php?page=tournaments&op=report2&id=$tId");
      exit;
    }

    $qry = "select * from naf_tournament where tournamentid=$tId";
    $tourney = $dbconn->Execute($qry);
    if ($tourney->EOF) {
      pnRedirect("naf.php?page=tournaments");
      exit;
    }
    $tourneyName = $tourney->Fields('tournamentname');

    $qry = "SELECT coachid, pn_uname, coachfirstname, coachlastname, race "
    ."FROM nuke_users u, naf_coach c, naf_tournamentcoach tc "
    ."WHERE tc.nafcoach=c.coachid "
    ."  AND tc.naftournament=$tId "
    ."  AND pn_uid=coachid";
    $coaches = $dbconn->Execute($qry);

    $qry = "select raceid, name from naf_race order by name";
    $races = $dbconn->Execute($qry);
    $raceArr[0] = "[ None ]";
    for ( ; !$races->EOF; $races->MoveNext() ) {
      $raceArr[$races->Fields('raceid')] = $races->Fields('name');
    }

    include 'header.php';
    OpenTable();

    echo "<h3>Race selection in '$tourneyName'</h3>";

    if ($edit==1) {
      echo "<form method=\"post\" action=\"naf.php\">";
    }

    echo "<table border=\"1\">";
    echo "<tr><th colspan=\"3\">Races</th></tr>";
    echo "<tr><th>Username</th><th>Coach</th><th>Race</th></tr>";

    $count=0;
    for ( ; !$coaches->EOF; $coaches->MoveNext() ) {
      echo "<tr><td>".$coaches->Fields('pn_uname')."</td><td>"
      .$coaches->Fields('coachfirstname')." ".$coaches->Fields('coachlastname')."</td>";

      if ($edit == 1) {
        echo "<td><input type=\"hidden\" name=\"coach[$count]\" value=\"".$coaches->Fields('coachid')."\" />";
        echo "<select name=\"race[".($count++)."]\">";
        echo makeRaceOptions($coaches->Fields('race'));
        echo "</select></td>";
      }
      else {
        echo "<td>".$raceArr[$coaches->Fields('race')]."</td>";
      }

      echo "</tr>";
    }
    echo "</table>";

    if ($edit==1) {
      echo "<input type=\"submit\" value=\"Update Races\" />";
      echo "<input type=\"hidden\" name=\"page\" value=\"tournaments\" />";
      echo "<input type=\"hidden\" name=\"op\" value=\"report2\" />";
      echo "<input type=\"hidden\" name=\"id\" value=\"$tId\" />";
      echo "<input type=\"hidden\" name=\"coachcount\" value=\"$count\" />";
      echo "<input type=\"hidden\" name=\"submit\" value=\"1\" />";
      echo "</form>";
    }
    else {
      echo "<a href=\"naf.php?page=tournaments&amp;op=report2&amp;id=$tId&edit=1\">Edit races</a><br />";
    }

    echo "<br /><form method=\"post\" action=\"naf.php\">";
    if ($edit != 1) {
      echo "<input type=\"submit\" value=\"Report Matches\" />";
    }
    echo "<input type=\"hidden\" name=\"page\" value=\"tournaments\" />";
    echo "<input type=\"hidden\" name=\"op\" value=\"report3\" />";
    echo "<input type=\"hidden\" name=\"id\" value=\"$tId\" />";
    echo "</form>";

    CloseTable();
    include 'footer.php';

    break;
  case 'report3':
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
                                  exit;
                                }

                                for ($i=0; $i<$NUM_REPORTS; $i++) {
                                  if (($gtr+0) > 0) {
                                    if (($tr1[$i]+0) == 0) { $tr1[$i]=$gtr+0; }
                                    if (($tr2[$i]+0) == 0) { $tr2[$i]=$gtr+0; }
                                  }
                                  if ($c1[$i]+0 < 1 && $c2[$i]+0 < 1)
                                  continue;

                                  //Check row: This needs to be expanded
                                  if ($insert_match){
                                    insertIntoUnverified($c1[$i], $r1[$i], $tr1[$i], $bh1[$i], $si1[$i], $rip1[$i], $w1[$i], $s1[$i],
                                    $s2[$i], $c2[$i], $r2[$i], $tr2[$i], $bh2[$i], $si2[$i], $rip2[$i], $w2[$i], $gate[$i], $tId, $insert_match, $date, $hour);
                                    $insert_match = "";
                                  }else if ($tr1[$i] > 0){
                                    // Output to naf_unverified_game
                                    appendToUnverified($c1[$i], $r1[$i], $tr1[$i], $bh1[$i], $si1[$i], $rip1[$i], $w1[$i], $s1[$i],
                                    $s2[$i], $c2[$i], $r2[$i], $tr2[$i], $bh2[$i], $si2[$i], $rip2[$i], $w2[$i], $gate[$i], $tId, $date, $hour);

                                  }else{
                                    //Need to do something here in case of errors
                                  }
                                }

    }

    $qry = "select * from naf_tournament where tournamentid=$tId";
    $tourney = $dbconn->Execute($qry);
    if ($tourney->EOF) {
      pnRedirect("naf.php?page=tournaments");
    }
    else {
      include 'header.php';
      OpenTable();

      $tourneyName = pnVarPrepForDisplay($tourney->Fields('tournamentname'));
      $startdate = pnVarPrepForDisplay($tourney->Fields('tournamentstartdate'));

      echo "<h3>Match reporting for '$tourneyName'</h3>";

      $qry = "select pn_uid, pn_uname from nuke_users u, naf_tournamentcoach tc where tc.nafcoach=u.pn_uid and "
      ."tc.naftournament=$tId order by pn_uname";
      $coaches = $dbconn->Execute($qry);
      $coachSel = "<option value=\"\">[ Select Coach ]</option>";
      for ( ; !$coaches->EOF; $coaches->MoveNext() ) {
        $coachSel .= "<option value=\"".$coaches->Fields('pn_uid')."\">".$coaches->Fields('pn_uname')."</option>";
      }

      if ($advanced == 1) {
        $qry = "select raceid, name from naf_race order by name";
        $races = $dbconn->Execute($qry);
        $raceSel = "<option value=\"0\">[Keep Default]</option>";
        for ( ; !$races->EOF; $races->MoveNext() ) {
          $raceSel .= "<option value=\"".$races->Fields('raceid')."\">".$races->Fields('name')."</option>";
        }
      }

      $req = "<span style=\"vertical-align: top; font-size: 0.9em; color: red;\">*</span>";

      echo "<form method=\"post\" action=\"naf.php\">";

      if ($advanced != 1) {
        echo "<a href=\"naf.php?page=tournaments&amp;op=report3&amp;id=$tId&advanced=1\">Switch to Advanced mode</a>";
      }
      else {
        echo "<a href=\"naf.php?page=tournaments&amp;op=report3&amp;id=$tId\">Switch to Simple mode</a>";
      }

      echo "<table border=\"0\">";
      echo "<tr><td>Global Team Rating:</td><td><input type=\"text\" name=\"gtr\" value=\"100\" /></td></tr>";
      echo "<tr><td>Date:</td><td><input type=\"text\" name=\"date\" value=\"$startdate\" /> (yyyy-mm-dd)</td></tr>";
      echo "<tr><td>Hour:</td><td>";
      echo "<select name=\"hour\">";
      for ($i=0; $i<24; $i++) {
        $str = $i;
        if (strlen($str) < 2)
        $str = "0" . $str;
        echo "<option value=\"$i\">$str</option>\n";
      }
      echo "</td></tr>";
      echo "</table>";

      if ($advanced == 1) {
        echo "<table border=\"1\">"
        ."<tr><th colspan=\"5\">Home</th><th>Score$req</th><th colspan=\"5\">Away</th><th>Gate</th></tr>"
        ."<tr align=\"center\"><td>Coach$req</td><td>Race$req</td><td>Team<br />Rating$req</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</th>"
        ."<td>Coach$req</td><td>Race$req</td><td>Team<br />Rating$req</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</td></tr>";
      }
      else {
        echo "<table border=\"1\">"
        ."<tr><th colspan=\"2\">Home</th><th>Score$req</th><th colspan=\"2\">Away</th></tr>"
        ."<tr align=\"center\"><td>Coach$req</td><td>Team<br />Rating$req</td><th>&nbsp;</th>"
        ."<td>Coach$req</td><td>Team<br />Rating$req</td></tr>";
      }

      for ($i=0; $i<$NUM_REPORTS; $i++) {
        echo "<tr>"
        ."<td><select name=\"c1[$i]\">$coachSel</select></td>"
        .($advanced==1?"<td><select name=\"r1[$i]\">$raceSel</select></td>":"")
        ."<td><input size=\"2\" type=\"text\" name=\"tr1[$i]\"></td>"
        .($advanced==1?"<td><input size=\"1\" type=\"text\" name=\"bh1[$i]\"><input size=\"1\" type=\"text\" name=\"si1[$i]\"><input size=\"1\" type=\"text\" name=\"rip1[$i]\"></td>":"")
        .($advanced==1?"<td><input size=\"5\" type=\"text\" name=\"w1[$i]\"></td>":"")
        ."<td><table border=\"0\"><tr><td><input size=\"1\" type=\"text\" name=\"s1[$i]\"></td><td>-</td><td><input size=\"1\" type=\"text\" name=\"s2[$i]\"></td></tr></table></td>"
        ."<td><select name=\"c2[$i]\">$coachSel</select></td>"
        .($advanced==1?"<td><select name=\"r2[$i]\">$raceSel</select></td>":"")
        ."<td><input size=\"2\" type=\"text\" name=\"tr2[$i]\"></td>"
        .($advanced==1?"<td><input size=\"1\" type=\"text\" name=\"bh2[$i]\"><input size=\"1\" type=\"text\" name=\"si2[$i]\"><input size=\"1\" type=\"text\" name=\"rip2[$i]\"></td>":"")
        .($advanced==1?"<td><input size=\"5\" type=\"text\" name=\"w2[$i]\"></td>":"")
        .($advanced==1?"<td><input size=\"5\" type=\"text\" name=\"gate[$i]\"></td></tr>":"");
      }
      echo "</table>";
      echo "Columns marked with $req are required.<br />";

      echo "<input type=\"hidden\" name=\"page\" value=\"tournaments\" />"
      ."<input type=\"hidden\" name=\"op\" value=\"report3\" />"
      ."<input type=\"hidden\" name=\"id\" value=\"$tId\" />"
      ."<input type=\"hidden\" name=\"insert_done\" value=\"1\" />"
      ."<input type=\"hidden\" name=\"insert_match\" value=\"$insert_match\" />"
      ."<input type=\"hidden\" name=\"submit\" value=\"1\" />";

      echo "<br /><input type=\"submit\" value=\"Add Matches\" /><br />";

      echo "</form>";

      // Get list of games in temp table for this tournament
      $query = "SELECT * "
      ."FROM naf_unverified_game "
      ."WHERE tournamentid=$tId "
      ."ORDER BY game_order ";
      $games = $dbconn->Execute($query);
      if (!$games->EOF){
        $numGames = $games->RecordCount();
        echo "$numGames matches reported.<br />";
        // Output table header
        if ($advanced == 1) {
          echo "<table border=\"1\">"
          ."<tr><th>Time</th><th colspan=\"5\">Home</th><th>Score</th><th colspan=\"5\">Away</th><th>Gate</th><th>Op</th></tr>"
          ."<tr align=\"center\"><td>&nbsp;</td><td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</th>"
          ."<td>Coach</td><td>Race</td><td>Team<br />Rating</td><td>Cas Inflicted<br />BH | SI | RIP</td><td>Winnings</td><th>&nbsp;</td><td>&nbsp</td></tr>";
        }else {
          echo "<table border=\"1\">"
          ."<tr><th>Time</th><th colspan=\"2\">Home</th><th>Score</th><th colspan=\"2\">Away</th><th>Op</th></tr>"
          ."<tr align=\"center\"><td>&nbsp;</td><td>Coach</td><td>Team<br />Rating</td><th>&nbsp;</th>"
          ."<td>Coach</td><td>Team<br />Rating</td><td>&nbsp</td></tr>";
        }

        // Output games currently in temp table
        for ( ; !$games->EOF; $games->MoveNext() ) {
          // Get coach 1 name
          $query = "SELECT pn_uname, coachid "
          ."FROM naf_coach, nuke_users "
          ."WHERE coachid=pn_uid "
          ."AND pn_uid = ".$games->fields("homecoachid");
          $result = $dbconn->Execute($query);
          $coach_1 = $result->fields[0];

          // Get coach 2 name
          $query = "SELECT pn_uname, coachid "
          ."FROM naf_coach, nuke_users "
          ."WHERE coachid=pn_uid "
          ."AND pn_uid = ".$games->fields("awaycoachid");
          $result = $dbconn->Execute($query);
          $coach_2 = $result->fields[0];

          // Get race 1 name
          $query = "SELECT name "
          ."FROM naf_race "
          ."WHERE raceid = ".$games->fields("racehome");
          $result = $dbconn->Execute($query);
          $race_1 = $result->fields[0];

          // Get race 2 name
          $query = "SELECT name "
          ."FROM naf_race "
          ."WHERE raceid = ".$games->fields("raceaway");
          $result = $dbconn->Execute($query);
          $race_2 = $result->fields[0];

          //Output table row

          echo "<tr>"
          ."<td>".$games->Fields('date')." ".$games->Fields('hour').":00</td>"
          ."<td>$coach_1</td>"
          .($advanced==1?"<td>$race_1</td>":"")
          ."<td>".$games->fields("trhome")."</td>"
          .($advanced==1?"<td>".$games->fields("badlyhurthome")."|".$games->fields("serioushome")."|".$games->fields("killshome")."</td>":"")
          .($advanced==1?"<td>".$games->fields("winningshome")."</td>":"")
          ."<td><table border=\"0\"><tr><td>".$games->fields("goalshome")."</td><td>-</td><td>".$games->fields("goalsaway")."</td></tr></table></td>"
          ."<td>$coach_2</td>"
          .($advanced==1?"<td>$race_2</td>":"")
          ."<td>".$games->fields("traway")."</td>"
          .($advanced==1?"<td>".$games->fields("badlyhurtaway")."|".$games->fields("seriousaway")."|".$games->fields("killsaway")."</td>":"")
          .($advanced==1?"<td>".$games->fields("winningsaway")."</td>":"")
          .($advanced==1?"<td>".$games->fields("gate")."</td></tr>":"")
          //Output links to insert or delete a game
          ."<td>(<a href=\"naf.php?page=tournaments&amp;op=delete_match&amp;id=$tId&game_id=".$games->fields("gameid")."\">Delete</a>)"
          ."(<a href=\"naf.php?page=tournaments&amp;op=report3&amp;id=$tId&insert_match=".$games->fields("game_order")."\">Insert</a>)</td>";
        }
        echo "</table> <br />";
        echo "<a href=\"naf.php?page=tournaments&amp;op=finalize_tournament&amp;id=$tId&insert_match=".$games->fields("game_order")."\">Finalize Tournament</a>";
      }


      CloseTable();
      include 'footer.php';
    }
    break;
  default:
    list($order, $dir, $showall) = pnVarCleanFromInput('ordercolumn', 'dir', 'showall');
    include 'header.php';
    OpenTable();

    if (strlen($order) == 0) {
      $order = "tournamentstartdate";
    }

    if ($showall!=1) {
      $showall=0;
    }

    echo "<h3>NAF Tourneys</h3>";

    echo makeLink('Add Tournament', 'add') . "<br />";

    if ($showall == 1) {
      echo "<a href=\"naf.php?page=tournaments&amp;ordercolumn=$order&amp;showall=0\">Hide past tournaments</a><br /><br />";
      $qry = "select * from naf_tournament where tournamentorganizerid=$user order by ".$order.(strlen($dir)>0?" $dir":"");
    }
    else {
      echo "<a href=\"naf.php?page=tournaments&amp;ordercolumn=$order&amp;showall=1\">Show past tournaments</a><br /><br />";
      $qry = "select * from naf_tournament where tournamentorganizerid=$user and tournamentenddate>now() "
      ."order by ".$order.(strlen($dir)>0?" $dir":"");
    }


    $res = $dbconn->Execute($qry);

    if (!$res->EOF && $user > 0) {
      echo '<table width="100%" bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
      echo '<tr><th bgcolor="#D9D8D0" colspan="6">Your Tournaments</th></tr>';
      echo '<th bgcolor="#D9D8D0">Op</th>';
      echo '<th bgcolor="#D9D8D0">Tournament</th><th bgcolor="#D9D8D0">Location</th><th bgcolor="#D9D8D0">Start Date</th>'
      .'<th bgcolor="#D9D8D0">End Date</th>';
      echo '<th bgcolor="#D9D8D0">Status</th>';
      echo "</tr>\n";

      for ( ; !$res->EOF; $res->MoveNext() ) {
        echo "<tr>";
        echo "<td bgcolor=\"#f8f7ee\">(<a href=\"naf.php?page=tournaments&amp;op=edit&amp;id=".$res->Fields('tournamentid')."\">Edit</a>)"
        ." (<a href=\"naf.php?page=tournaments&amp;op=delete&amp;id=".$res->Fields('tournamentid')."\">Delete</a>)";
        if (strcmp($res->Fields('tournamentstatus'), "APPROVED") == 0) {
          echo "<br />(<a href=\"naf.php?page=tournaments&amp;op=report&amp;id=".$res->Fields('tournamentid')."\">Report Results</a>)";
        }
        echo "</td>";
        echo "<td bgcolor=\"#f8f7ee\"><a href=\"naf.php?page=tournaments&amp;op=view&amp;id=".$res->Fields('tournamentid')."\">".$res->Fields('tournamentname')."</a>"
        ."</td><td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentnation')
        ."</td><td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentstartdate')
        ."</td><td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentenddate')."</td>";
        echo "<td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentstatus')."</td>";
        echo "</tr>\n";
      }
      echo "</table><br /><br />";
      $res->Close();
    }

    $query = "SELECT naf_tournament.*, pn_uname as organizer FROM naf_tournament, nuke_users"
    ." WHERE pn_uid=tournamentorganizerid".($user>0?" and tournamentorganizerid<>$user":"")." "
    ." AND tournamentenddate >= date_sub(now(), interval 1 month) "
    ." AND tournamentenddate <= now()"
    ." order by $order".(strlen($dir)>0?" $dir":"").", tournamentname";
    $res = $dbconn->Execute($query);
    if ($res->numRows() > 0) {
      echo '<table width="100%" bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
      echo '<tr><th bgcolor="#D9D8D0" colspan="'.($secAdmin?"7":"4").'">Recent Tournaments</th></tr>'."\n";
      echo '<tr>';
      if ($secAdmin) {
        echo '<th bgcolor="#D9D8D0">Op</th>';
      }
      echo '<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentname&amp;showall='.$showall.($dir==""&&$order=="tournamentname"?"&amp;dir=desc":"").'">Tournament</a></th>';
      if ($secAdmin) {
        echo '<th bgcolor="#D9D8D0">Submitted By</th>';
      }
      echo '<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentnation&amp;showall='.$showall.($dir==""&&$order=="tournamentnation"?"&amp;dir=desc":"").'">Location</a></th>'
      .'<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentstartdate&amp;dir='.($dir=="desc"&&$order=="tournamentstartdate"?"asc":"desc").'&amp;showall='.$showall.'">Start Date</a></th>'
      .'<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentenddate&amp;dir='.($dir=="desc"&&$order=="tournamentenddate"?"asc":"desc").'&amp;showall='.$showall.'">End Date</a></th>';
      if ($secAdmin) {
        echo '<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentstatus&amp;showall='.$showall.($dir==""&&$order=="tournamentstatus"?"&amp;dir=desc":"").'">Status</a></th>';
      }
      echo '</tr>'."\n";

      for ( ; !$res->EOF; $res->moveNext() ) {
        echo "<tr>";
        if ($secAdmin) {
          echo "<td bgcolor=\"#f8f7ee\">(<a href=\"naf.php?page=tournaments&amp;op=edit&amp;id=".$res->Fields('tournamentid')."\">Edit</a>)"
          ." (<a href=\"naf.php?page=tournaments&amp;op=delete&amp;id=".$res->Fields('tournamentid')."\">Delete</a>)";
          if ($secAdmin == true && strcmp($res->Fields('tournamentstatus'), "APPROVED") == 0) {
            echo "<br />(<a href=\"naf.php?page=tournaments&amp;op=report&amp;id=".$res->Fields('tournamentid')."\">Report Results</a>)";
          }
          echo "</td>";
        }
        echo "<td bgcolor=\"#f8f7ee\"><a href=\"naf.php?page=tournaments&amp;op=view&amp;id=".$res->Fields('tournamentid')."\">".$res->Fields('tournamentname')."</a></td>";
        if ($secAdmin) {
          echo "<td bgcolor=\"#f8f7ee\">".$res->Fields('organizer')."</td>";
        }
        echo "<td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentnation')
        ."</td><td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentstartdate')
        ."</td><td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentenddate')."</td>";
        if ($secAdmin) {
          echo "<td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentstatus')."</td>";
        }
        echo "</tr>\n";
      }

      echo '</table>';
      echo "<br /><br />";
    }
    $res->close();


    $qry = "select naf_tournament.*, pn_uname as organizer from naf_tournament, nuke_users ";
    $qry .= "where pn_uid=tournamentorganizerid".($user>0?" and tournamentorganizerid<>$user":"");
    if ($showall != 1) {
      $qry .= " and tournamentenddate>now()";
    }
    if (!$secAdmin) {
      $qry .= " and tournamentstatus='APPROVED'";
    }
    $qry .= " order by $order".(strlen($dir)>0?" $dir":"").", tournamentname";
    $res = $dbconn->Execute($qry);

    echo '<table width="100%" bgcolor="#858390" cellpadding="2" cellspacing="1" border="0">';
    echo '<tr><th bgcolor="#D9D8D0" colspan="'.($secAdmin?"7":"4").'">Upcoming Tournaments</th></tr>'."\n";
    echo "<tr>";
    if ($secAdmin) {
      echo '<th bgcolor="#D9D8D0">Op</th>';
    }
    echo '<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentname&amp;showall='.$showall.($dir==""&&$order=="tournamentname"?"&amp;dir=desc":"").'">Tournament</a></th>';
    if ($secAdmin) {
      echo '<th bgcolor="#D9D8D0">Submitted By</th>';
    }
    echo '<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentnation&amp;showall='.$showall.($dir==""&&$order=="tournamentnation"?"&amp;dir=desc":"").'">Location</a></th>'
    .'<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentstartdate&amp;dir='.($dir=="desc"&&$order=="tournamentstartdate"?"asc":"desc").'&amp;showall='.$showall.'">Start Date</a></th>'
    .'<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentenddate&amp;dir='.($dir=="desc"&&$order=="tournamentenddate"?"asc":"desc").'&amp;showall='.$showall.'">End Date</a></th>';
    if ($secAdmin) {
      echo '<th bgcolor="#D9D8D0"><a href="naf.php?page=tournaments&amp;ordercolumn=tournamentstatus&amp;showall='.$showall.($dir==""&&$order=="tournamentstatus"?"&amp;dir=desc":"").'">Status</a></th>';
    }
    echo '</tr>'."\n";
    for ( ; !$res->EOF; $res->MoveNext() ) {
      echo "<tr>";
      if ($secAdmin) {
        echo "<td bgcolor=\"#f8f7ee\">(<a href=\"naf.php?page=tournaments&amp;op=edit&amp;id=".$res->Fields('tournamentid')."\">Edit</a>)"
        ." (<a href=\"naf.php?page=tournaments&amp;op=delete&amp;id=".$res->Fields('tournamentid')."\">Delete</a>)";
        if ($secAdmin == true && strcmp($res->Fields('tournamentstatus'), "APPROVED") == 0) {
          echo "<br />(<a href=\"naf.php?page=tournaments&amp;op=report&amp;id=".$res->Fields('tournamentid')."\">Report Results</a>)";
        }
        echo "</td>";
      }
      echo "<td bgcolor=\"#f8f7ee\"><a href=\"naf.php?page=tournaments&amp;op=view&amp;id=".$res->Fields('tournamentid')."\">".$res->Fields('tournamentname')."</a></td>";
      if ($secAdmin) {
        echo "<td bgcolor=\"#f8f7ee\">".$res->Fields('organizer')."</td>";
      }
      echo "<td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentnation')
      ."</td><td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentstartdate')
      ."</td><td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentenddate')."</td>";
      if ($secAdmin) {
        echo "<td bgcolor=\"#f8f7ee\">".$res->Fields('tournamentstatus')."</td>";
      }
      echo "</tr>\n";
    }
    $res->Close();
    echo '</table>';

    CloseTable();
    include 'footer.php';
}

?>
