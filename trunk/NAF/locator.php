<?
pnRedirect(pnModURL('Stars', 'locator'));
exit;

  if (!pnUserLoggedIn()) {
    include 'header.php';

    OpenTable();

    echo "<div style=\"font-size: 2em; text-align: center;\">"
        ."Sorry. This feature is only available to registered users."
        ."</div>";

    CloseTable();

    include 'footer.php';
    exit;
  }

  $isAdmin = pnSecAuthAction(0, "NAF::", "Membership::", ACCESS_ADMIN) ||
             pnSecAuthAction(0, "NAF::", "Tournaments::", ACCESS_ADMIN);

  switch($op) {
    case 'optin':
      $uid = pnUserGetVar('uid');
      $query = "SELECT * from nuke_user_data WHERE pn_uda_uid=$uid AND pn_uda_propid=18";
      $res = $dbconn->Execute($query);

      if ($res->RecordCount() == 0) {
        $dbconn->Execute("INSERT into nuke_user_data (pn_uda_propid, pn_uda_uid, pn_uda_value) "
                        ."VALUES (18, $uid, 1)");
        echo $dbconn->errorMsg();
      }
      else {
        pnUserSetVar('_NAF_NATIONDISPLAY', 1);
      }
      pnRedirect("naf.php?page=locator");
      break;
    case 'optout':
      pnUserSetVar('_NAF_NATIONDISPLAY', 0);
      pnRedirect("naf.php?page=locator");
      break;
    case 'list':
      include 'header.php';

      OpenTable();

      list($nation, $displayAll) = pnVarCleanFromInput('nation', 'all');
      $displayAll += 0;

      echo "<div align=\"center\" style=\"font-size: 2em;\">Coaches in ".pnVarPrepForDisplay($nation)."</div>\n";

      $nation = pnVarPrepForStore($nation);

      $stateNations = array("United States", "Canada", "Spain", "Italy", "Australia");
      $showState = array_search($nation, $stateNations) !== false;

      if ($isAdmin && $displayAll==1) {
        $query = "SELECT pn_uid, pn_uname, coachnation, coachstate, coachcity "
            ."FROM naf_coach c, nuke_users nu "
            ."WHERE (length(coachactivationcode) = 0 OR coachactivationcode is NULL) "
            ."  AND coachnation='$nation' "
            ."  AND coachid = pn_uid "
            ."ORDER BY ".($showState==1?"coachstate, ":"")."coachcity, pn_uname";
      }
      else {
        $query = "SELECT pn_uid, pn_uname, coachnation, coachstate, coachcity "
            ."FROM naf_coach c, nuke_users nu, nuke_user_data nud "
            ."WHERE (length(coachactivationcode) = 0 OR coachactivationcode is NULL) "
            ."  AND coachnation='$nation' "
            ."  AND coachid = pn_uid "
            ."  AND pn_uda_uid=coachid "
            ."  AND pn_uda_propid=18 "
            ."  AND pn_uda_value=1 "
            ."ORDER BY ".($showState==1?"coachstate, ":"")."coachcity, pn_uname";
      }

      $res = $dbconn->Execute($query);

      echo "<div align=\"center\"><a href=\"naf.php?page=locator".($all==1?"&all=1":"")."\">Back</a></div>\n";

      echo "<table align=\"center\" border=\"1\" cellspacing=\"0\">";
      echo "<tr bgcolor=\"#D9D8D0\"><th>Username</th>".($showState==1?"<th>State</th>":"")."<th>City</th></tr>";
      for ( ; !$res->EOF; $res->moveNext() ) {
        $user = "<a href=\"user.php?op=userinfo&uname=".$res->FieldS('pn_uname')."\">"
               .pnVarPrepForStore($res->Fields('pn_uname'))
               ."</a>";
        $city = pnVarPrepForDisplay($res->Fields('coachcity'));
        $state = pnVarPrepForDisplay($res->Fields('coachstate'));
        if (strlen($city) == 0)
          $city = "&nbsp;";
        if (strlen($state) == 0)
          $state = "&nbsp;";
        echo "<tr><td>$user</td>"
            .($showState==1?"<td>$state</td>":"")
            ."<td>$city</td></tr>\n";
      }
      echo "</table>";

      CloseTable();

      include 'footer.php';
      break;
    default:
      include 'header.php';

      OpenTable();

      echo "<div align=\"left\">"
      ."<h3>NAF Coach Locator</h3>"
      ."</div>";

      $uid = pnUserGetVar('uid');

      $val = pnUserGetVar('_NAF_NATIONDISPLAY');

      if ($val == 0) {
        echo "<div align=\"center\" style=\"margin: 0.5em;\">"
            ."This page will allow you to find NAF coaches in various parts of the world.<br />"
            ."For privacy reasons, you have to <a href=\"naf.php?page=locator&op=optin\">opt in</a> "
            ."to show up in these lists."
            ."</div>";
      }
      else {
        echo "<div align=\"center\" style=\"margin: 0.5em;\">"
            ."This page will allow you to find NAF coaches in various parts of the world.<br />"
            ."If you do not want to be shown here anymore, you can "
            ."<a href=\"naf.php?page=locator&op=optout\">opt out</a> at any time."
            ."</div>";
      }

      $displayAll = pnVarCleanFromInput('all') + 0;


        $query = "SELECT coachnation, COUNT(1) AS num "
            ."FROM naf_coach c, nuke_user_data nud "
            ."WHERE pn_uda_uid = c.coachid "
            ."  AND (length(coachactivationcode) = 0 OR coachactivationcode is NULL) "
            ."  AND length(coachnation) > 0 "
            ."  AND pn_uda_propid=18 "
            ."  AND pn_uda_value=1 "
            ."GROUP BY coachnation "
            ."ORDER BY coachnation";

      if ($isAdmin) {
        echo "<div style=\"margin: 0.5em; text-align: center;\">(Admin: "
            .($all==1?"<a href=\"naf.php?page=locator\">Show only opted in coaches</a>":
                      "<a href=\"naf.php?page=locator&all=1\">Show all coaches</a>")
            .")</div>";
        if ($displayAll==1) {
          $query = "SELECT coachnation, COUNT(1) AS num "
              ."FROM naf_coach c "
              ."WHERE (length(coachactivationcode) = 0 OR coachactivationcode IS NULL) "
              ."  AND length(coachnation) > 0 "
              ."GROUP BY coachnation "
              ."ORDER BY coachnation";
        }
      }

      $res = $dbconn->Execute($query);

      echo "<table width=\"30%\" cellpadding=\"2\" cellspacing=\"1\" bgcolor=\"#858390\" border=\"0\" align=\"center\">";
      echo "<tr bgcolor=\"#D9D8D0\"><th>Nation</th><th>Coaches</th></tr>";
      $total = 0;
      for ( ; !$res->EOF; $res->moveNext() ) {
        $nation = $res->Fields('coachnation');
        $num = $res->Fields('num');
        $total += $num;
        echo "<tr><td bgcolor=\"#f8f7ee\"><a href=\"naf.php?page=locator&op=list&nation=".urlencode($nation).($all==1?"&all=1":"")."\">".$nation."</a></td>"
            ."<td bgcolor=\"#f8f7ee\" align=\"center\">".$num."</td></tr>\n";
      }
      echo "</table>";
      echo "<div align=\"center\">$total coach".($total!=1?"es":"")." listed.</div>";

      CloseTable();

      include 'footer.php';
      break;
  }

?>
