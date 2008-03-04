<?php

function Stars_admin_addposition($args) {
  if (!pnSecAuthAction(0, 'Stars::', "::", ACCESS_ADMIN)) {
    include 'header.php';
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  list($raceid, $rules_version) = pnVarCleanFromInput('raceid', 'rules_version');
  include 'header.php';
  OpenTable();
  echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars','admin', 'submitposition')).'" method="post">';
  echo '<table>';
  echo '<tr><td><b>Qty</b></td>';
  echo '<td><b>Position</b></td>';
  echo '<td><b>Cost</b></td>';
  echo '<td><b>MA</b></td>';
  echo '<td><b>ST</b></td>';
  echo '<td><b>AG</b></td>';
  echo '<td><b>AV</b></td>';
  echo '<td><b>General</b></td>';
  echo '<td><b>Agility</b></td>';
  echo '<td><b>Strength</b></td>';
  echo '<td><b>Passing</b></td>';
  echo '<td><b>Mutation</b></td>';
  echo '<td><b>From Rules Version</b></td>';
  echo '<td><b>To Rules Version</b></td></tr>'."\n";
  echo '<tr><td><input type="text" size="3" name="qty" /></td>';
  echo '<td><input type="text" size="20" name="position" /></td>';
  echo '<td><input type="text" size="10" name="cost" /></td>';
  echo '<td><select name="ma">';
  for($i = 2; $i<10;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==6?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="st">';
  for($i = 1; $i<8;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==3?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="ag">';
  for($i = 1; $i<5;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==3?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="av">';
  for($i = 5; $i<11;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==8?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="general">';
  echo '<option value="NORMAL" selected="selected">NORMAL</option>';
  echo '<option value="DOUBLE">DOUBLE</option>';
  echo '<option value="NEVER">NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="agility">';
  echo '<option value="NORMAL">NORMAL</option>';
  echo '<option value="DOUBLE" selected="selected">DOUBLE</option>';
  echo '<option value="NEVER">NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="strength">';
  echo '<option value="NORMAL">NORMAL</option>';
  echo '<option value="DOUBLE" selected="selected">DOUBLE</option>';
  echo '<option value="NEVER">NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="passing">';
  echo '<option value="NORMAL">NORMAL</option>';
  echo '<option value="DOUBLE" selected="selected">DOUBLE</option>';
  echo '<option value="NEVER">NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="mutation">';
  echo '<option value="NORMAL">NORMAL</option>';
  echo '<option value="DOUBLE">DOUBLE</option>';
  echo '<option value="NEVER" selected="selected">NEVER</option>';
  echo '</select></td>';
  $sql = "SELECT title, historic_version from nuke_stars_rules_versions order by historic_version";
  $result = $dbconn->Execute($sql);
  echo '<td><select name="from_version">';
  for ( ; !$result->EOF; $result->MoveNext() ) {
    echo '<option value="'.pnVarPrepForDisplay($result->Fields('historic_version')).'"'.($rules_version==$result->Fields('historic_version')?' selected="selected"':'').'>'.pnVarPrepForDisplay($result->Fields('title')).'</option>'."\n";
  }
  echo '</select></td>';
  $sql = "SELECT title, historic_version from nuke_stars_rules_versions order by historic_version";
  $result = $dbconn->Execute($sql);
  echo '<td><select name="to_version">';
  for ( ; !$result->EOF; $result->MoveNext() ) {
    echo '<option value="'.pnVarPrepForDisplay($result->Fields('historic_version')).'">'.pnVarPrepForDisplay($result->Fields('title')).'</option>'."\n";
  }
  echo '<option value="999" selected="selected">Forever</option>';
  echo '</select></td>';
  echo '</tr>'."\n";
  echo '</table>';
  echo '<input type="hidden" name="raceid" value="'.$raceid.'" />';
  echo '<input type="submit" value="Submit" />';
  echo '</form>';
  CloseTable();
  include 'footer.php';
  return true;
}

function Stars_admin_editposition($args) {
  if (!pnSecAuthAction(0, 'Stars::', "::", ACCESS_ADMIN)) {
    include 'header.php';
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  $id = pnVarCleanFromInput('playertypeid');
  include 'header.php';
  OpenTable();
  $sql = "SELECT playertypeid, qty, position, ma, st, ag, av, cost, big_guy, from_rules_version, to_rules_version, "
  ."general, agility, strength, passing, mutation "
  ."FROM nuke_stars_position WHERE playertypeid = ".pnVarPrepForStore($id);
  $result = $dbconn->Execute($sql);
  echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars','admin', 'submitposition')).'" method="post">';
  echo '<table>';
  echo '<tr><td><b>Qty</b></td>';
  echo '<td><b>Position</b></td>';
  echo '<td><b>Cost</b></td>';
  echo '<td><b>MA</b></td>';
  echo '<td><b>ST</b></td>';
  echo '<td><b>AG</b></td>';
  echo '<td><b>AV</b></td>';
  echo '<td><b>General</b></td>';
  echo '<td><b>Agility</b></td>';
  echo '<td><b>Strength</b></td>';
  echo '<td><b>Passing</b></td>';
  echo '<td><b>Mutation</b></td>';
  echo '<td><b>From Rules Version</b></td>';
  echo '<td><b>To Rules Version</b></td></tr>'."\n";
  echo '<tr><td><input type="text" size="3" name="qty" value="'.pnVarPrepForDisplay($result->Fields('qty')).'"/></td>';
  echo '<td><input type="text" size="20" name="position" value="'.pnVarPrepForDisplay($result->Fields('position')).'"/></td>';
  echo '<td><input type="text" size="10" name="cost" value="'.pnVarPrepForDisplay($result->Fields('cost')).'"/></td>';
  echo '<td><select name="ma">';
  for($i = 2; $i<10;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==$result->Fields('ma')?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="st">';
  for($i = 1; $i<8;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==$result->Fields('st')?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="ag">';
  for($i = 1; $i<5;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==$result->Fields('ag')?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="av">';
  for($i = 5; $i<11;$i++){
    echo '<option value="'.pnVarPrepForDisplay($i).'"'.($i==$result->Fields('av')?' selected="selected"':'').'>'.pnVarPrepForDisplay($i).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><select name="general">';
  echo '<option value="NORMAL"'.($result->Fields('general')=='NORMAL'?' selected="selected"':'').'>NORMAL</option>';
  echo '<option value="DOUBLE"'.($result->Fields('general')=='DOUBLE'?' selected="selected"':'').'>DOUBLE</option>';
  echo '<option value="NEVER"'.($result->Fields('general')=='NEVER'?' selected="selected"':'').'>NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="agility">';
  echo '<option value="NORMAL"'.($result->Fields('agility')=='NORMAL'?' selected="selected"':'').'>NORMAL</option>';
  echo '<option value="DOUBLE"'.($result->Fields('agility')=='DOUBLE'?' selected="selected"':'').'>DOUBLE</option>';
  echo '<option value="NEVER"'.($result->Fields('agility')=='NEVER'?' selected="selected"':'').'>NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="strength">';
  echo '<option value="NORMAL"'.($result->Fields('strength')=='NORMAL'?' selected="selected"':'').'>NORMAL</option>';
  echo '<option value="DOUBLE"'.($result->Fields('strength')=='DOUBLE'?' selected="selected"':'').'>DOUBLE</option>';
  echo '<option value="NEVER"'.($result->Fields('strength')=='NEVER'?' selected="selected"':'').'>NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="passing">';
  echo '<option value="NORMAL"'.($result->Fields('passing')=='NORMAL'?' selected="selected"':'').'>NORMAL</option>';
  echo '<option value="DOUBLE"'.($result->Fields('passing')=='DOUBLE'?' selected="selected"':'').'>DOUBLE</option>';
  echo '<option value="NEVER"'.($result->Fields('passing')=='NEVER'?' selected="selected"':'').'>NEVER</option>';
  echo '</select></td>';
  echo '<td><select name="mutation">';
  echo '<option value="NORMAL"'.($result->Fields('mutation')=='NORMAL'?' selected="selected"':'').'>NORMAL</option>';
  echo '<option value="DOUBLE"'.($result->Fields('mutation')=='DOUBLE'?' selected="selected"':'').'>DOUBLE</option>';
  echo '<option value="NEVER"'.($result->Fields('mutation')=='NEVER'?' selected="selected"':'').'>NEVER</option>';
  echo '</select></td>';
  $sql = "SELECT title, historic_version from nuke_stars_rules_versions order by historic_version";
  $result_rules = $dbconn->Execute($sql);
  echo '<td><select name="from_version">';
  for ( ; !$result_rules->EOF; $result_rules->MoveNext() ) {
    echo '<option value="'.pnVarPrepForDisplay($result_rules->Fields('historic_version')).'"'.($result->Fields('from_rules_version')==$result_rules->Fields('historic_version')?' selected="selected"':'').'>'.$result_rules->Fields('title').'</option>'."\n";
  }
  echo '</select></td>';
  $sql = "SELECT title, historic_version from nuke_stars_rules_versions order by historic_version";
  $result_rules = $dbconn->Execute($sql);
  echo '<td><select name="to_version">';
  for ( ; !$result_rules->EOF; $result_rules->MoveNext() ) {
    echo '<option value="'.pnVarPrepForDisplay($result_rules->Fields('historic_version')).'"'.($result->Fields('to_rules_version')==$result_rules->Fields('historic_version')?' selected="selected"':'').'>'.$result_rules->Fields('title').'</option>'."\n";
  }
  echo '<option value="999"'.($result->Fields('to_rules_version')==999?' selected="selected"':'').'>Forever</option>';
  echo '</select></td>';
  echo '</tr>'."\n";
  echo '</table>';
  echo '<input type="hidden" name="positionid" value="'.$id.'" />';
  echo '<input type="submit" value="Submit" />';
  echo '</form>';

  echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars','admin', 'addskill')).'" method="post">';
  echo '<input type="hidden" name="positionid" value="'.$id.'" />';
  echo '<table>';
  $sql = "SELECT s.name, s.skillid FROM nuke_stars_positionskills ps, nuke_stars_skills s WHERE s.skillid = ps.skillid AND ps.positionid = ".$result->Fields('playertypeid')." order by s.name";
  $result2 = $dbconn->Execute($sql);
  for ( ; !$result2->EOF; $result2->MoveNext() ) {
    echo '<tr><td>';
    echo pnVarPrepForDisplay($result2->Fields('name'));
    echo '</td><td><a href="'.pnVarPrepForDisplay(pnModURL('Stars','admin', 'deleteskill', array('positionid' => $result->Fields('playertypeid'), 'skillid' => $result2->Fields('skillid')))).'">Delete</a></td></tr>';
  }

  echo '<tr><td><select name="skillid">';
  $sql = "SELECT s.name, s.skillid FROM nuke_stars_skills s WHERE skillgroupid NOT IN (0, 7) AND ".pnVarPrepForStore($result->Fields('from_rules_version'))." BETWEEN s.from_rules_version AND s.to_rules_version order by s.skillgroupid, s.name";
  $result2 = $dbconn->Execute($sql);
  for ( ; !$result2->EOF; $result2->MoveNext() ) {
    echo '<option value="'.pnVarPrepForDisplay($result2->Fields('skillid')).'">'.pnVarPrepForDisplay($result2->Fields('name')).'</option>'."\n";
  }
  echo '</select></td>';
  echo '<td><input type="submit" value="Submit" /></td></tr>';
  echo '</table>';
  echo '</form>';
  echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars','admin', '', array('version' => $result->Fields('from_rules_version')))).'">Return to teamlist</a>';

  CloseTable();
  include 'footer.php';
  return true;
}

function Stars_admin_submitposition($args) {
  if (!pnSecAuthAction(0, 'Stars::', "::", ACCESS_ADMIN)) {
    include 'header.php';
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  list($raceid, $qty, $position, $cost, $ma, $st, $ag, $av, $general, $agility, $strength, $passing, $mutation, $positionid, $from_version, $to_version)
  = pnVarCleanFromInput('raceid', 'qty', 'position', 'cost', 'ma', 'st', 'ag', 'av', 'general', 'agility', 'strength', 'passing', 'mutation', 'positionid', 'from_version', 'to_version');
  if($positionid + 0) {
    $sql = "UPDATE nuke_stars_position SET "
    ."qty=".pnVarPrepForStore($qty).", "
    ."position="."'".pnVarPrepForStore($position)."', "
    ."cost=".pnVarPrepForStore($cost).", "
    ."ma=".pnVarPrepForStore($ma).", "
    ."st=".pnVarPrepForStore($st).", "
    ."ag=".pnVarPrepForStore($ag).", "
    ."av=".pnVarPrepForStore($av).", "
    ."general="."'".pnVarPrepForStore($general)."', "
    ."agility="."'".pnVarPrepForStore($agility)."', "
    ."strength="."'".pnVarPrepForStore($strength)."', "
    ."passing="."'".pnVarPrepForStore($passing)."', "
    ."mutation="."'".pnVarPrepForStore($mutation)."', "
    ."from_rules_version=".pnVarPrepForStore($from_version).", "
    ."to_rules_version=".pnVarPrepForStore($to_version)." "
    ."WHERE playertypeid = ".pnVarPrepForStore($positionid);
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
      echo $sql."\n";
      pnSessionSetVar('errormsg', _GETFAILED);
      return false;
    }
  }
  else {
    $sql = "INSERT INTO nuke_stars_position (raceid, qty, position, cost, ma, st, ag, av, general, agility, strength, passing, mutation, from_rules_version, to_rules_version) "
    ."VALUES ("
    .pnVarPrepForStore($raceid).", "
    .pnVarPrepForStore($qty).", "
    ."'".pnVarPrepForStore($position)."', "
    .pnVarPrepForStore($cost).", "
    .pnVarPrepForStore($ma).", "
    .pnVarPrepForStore($st).", "
    .pnVarPrepForStore($ag).", "
    .pnVarPrepForStore($av).", "
    ."'".pnVarPrepForStore($general)."', "
    ."'".pnVarPrepForStore($agility)."', "
    ."'".pnVarPrepForStore($strength)."', "
    ."'".pnVarPrepForStore($passing)."', "
    ."'".pnVarPrepForStore($mutation)."', "
    .pnVarPrepForStore($from_version).", "
    .pnVarPrepForStore($to_version).")";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
      echo $sql."\n";
      pnSessionSetVar('errormsg', _GETFAILED);
      return false;
    }
  }
  pnRedirect(pnModURL('Stars','admin', '', array('version' => $from_version)));
  return true;
}

function Stars_admin_deleteskill($args) {
  if (!pnSecAuthAction(0, 'Stars::', "::", ACCESS_ADMIN)) {
    include 'header.php';
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  list($id, $skillid) = pnVarCleanFromInput('positionid', 'skillid');
  $sql = "DELETE FROM nuke_stars_positionskills WHERE positionid=".pnVarPrepForStore($id)." AND skillid=".pnVarPrepForStore($skillid);
  $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo $sql."\n";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }
  pnRedirect(pnModURL('Stars','admin', 'editposition', array('playertypeid' => $id)));
  return true;
}

function Stars_admin_addskill($args) {
  if (!pnSecAuthAction(0, 'Stars::', "::", ACCESS_ADMIN)) {
    include 'header.php';
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);
  list($id, $skillid) = pnVarCleanFromInput('positionid', 'skillid');
  $sql = "INSERT INTO nuke_stars_positionskills (positionid, skillid) VALUES (".pnVarPrepForStore($id).", ".pnVarPrepForStore($skillid).")";
  $dbconn->Execute($sql);
  if ($dbconn->ErrorNo() != 0) {
    echo $sql."\n";
    pnSessionSetVar('errormsg', _GETFAILED);
    return false;
  }
  pnRedirect(pnModURL('Stars','admin', 'editposition', array('playertypeid' => $id)));
  return true;
}

function Stars_admin_main($args) {
  if (!pnSecAuthAction(0, 'Stars::', "::", ACCESS_ADMIN)) {
    include 'header.php';
    echo pnVarPrepHTMLDisplay(_MODULENOAUTH);
    include 'footer.php';
    return true;
  }
  $dbconn =& pnDBGetConn(true);

  $version = pnVarCleanFromInput('version');
  if(!isset($version)) $version = 5;

  include 'header.php';
  OpenTable();
  $sql = "SELECT title, historic_version from nuke_stars_rules_versions order by historic_version";
  $result = $dbconn->Execute($sql);
  echo '<h1>Select Version</h1>';
  echo '<form action="'.pnVarPrepForDisplay(pnModURL('Stars','admin')).'" method="post">';
  echo '<select name="version">';
  for ( ; !$result->EOF; $result->MoveNext() ) {
    echo '<option value="'.pnVarPrepForDisplay($result->Fields('historic_version')).'"'.($result->Fields('historic_version')==$version?' selected="selected"':'').'>'.pnVarPrepForDisplay($result->Fields('title')).'</option>'."\n";
  }
  echo '</select>';
  echo '<input type="submit" value="Submit" />';
  echo '</form>';

  CloseTable();
  $sql = "SELECT name, raceid, apoth, reroll_cost FROM nuke_stars_race WHERE ".$version." BETWEEN from_rules_version AND to_rules_version Order by name";
  $result = $dbconn->Execute($sql);
  for ( ; !$result->EOF; $result->MoveNext() ) {
    OpenTable();
    echo '<h2>'.pnVarPrepForDisplay($result->Fields('name')).'</h2>';
    echo 'Re-roll Price: '.pnVarPrepForDisplay($result->Fields('reroll_cost')).'<br />';
    echo 'Apothecary: '.pnVarPrepForDisplay($result->Fields('apoth'));
    echo '<table>';
    $sql = "SELECT playertypeid, qty, position, ma, st, ag, av, cost, big_guy, "
    ."concat(if(general='NORMAL', 'G', ''),if(agility='NORMAL', 'A', ''),if(strength='NORMAL', 'S', ''),if(passing='NORMAL', 'P', ''),if(mutation='NORMAL', 'M', '')) as NORMAL, "
    ."concat(if(general='DOUBLE', 'G', ''),if(agility='DOUBLE', 'A', ''),if(strength='DOUBLE', 'S', ''),if(passing='DOUBLE', 'P', ''),if(mutation='DOUBLE', 'M', '')) as DOUBLES "
    ."FROM nuke_stars_position WHERE ".$version." BETWEEN from_rules_version AND to_rules_version AND raceid = ".$result->Fields('raceid')." order by cost";
    $result2 = $dbconn->Execute($sql);
    for ( ; !$result2->EOF; $result2->MoveNext() ) {
      echo '<tr><td>'.'0-'.pnVarPrepForDisplay($result2->Fields('qty')).'</td>';
      echo '<td><a href="'.pnVarPrepForDisplay(pnModUrl('Stars', 'admin', 'editposition', array('playertypeid' => $result2->Fields('playertypeid')))).'">'.pnVarPrepForDisplay($result2->Fields('position')).'</a></td>';
      echo '<td>'.pnVarPrepForDisplay($result2->Fields('cost')).'</td>';
      echo '<td>'.pnVarPrepForDisplay($result2->Fields('ma')).'</td>';
      echo '<td>'.pnVarPrepForDisplay($result2->Fields('st')).'</td>';
      echo '<td>'.pnVarPrepForDisplay($result2->Fields('ag')).'</td>';
      echo '<td>'.pnVarPrepForDisplay($result2->Fields('av')).'</td>';
      $sql = "SELECT s.name FROM nuke_stars_positionskills ps, nuke_stars_skills s WHERE s.skillid = ps.skillid AND ps.positionid = ".$result2->Fields('playertypeid')." order by s.name";
      $result3 = $dbconn->Execute($sql);
      $first = true;
      echo '<td>';
      for ( ; !$result3->EOF; $result3->MoveNext() ) {
        if(!$first) echo ', ';
        echo pnVarPrepForDisplay($result3->Fields('name'));
        $first = false;
      }
      if($first) echo '&nbsp;';
      echo '</td>';
      echo '<td>'.pnVarPrepForDisplay($result2->Fields('normal')).'</td>';
      echo '<td>'.pnVarPrepForDisplay($result2->Fields('doubles')).'</td>';
      echo '</tr>'."\n";

    }
    echo '</table>';
    echo '<a href="'.pnVarPrepForDisplay(pnModURL('Stars','admin', 'addposition', array('raceid' => $result->Fields('raceid'), 'rules_version' => $version))).'">Add position</a>';
    CloseTable();

  }

  include 'footer.php';
  return true;
}
?>