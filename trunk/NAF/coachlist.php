<?
  include 'header.php';
  OpenTable();
  
  $showInactive = pnVarCleanFromInput('showinactive');
  
  echo '<center>';
  
  $query = "SELECT pn_uname, coachlastname, coachfirstname from nuke_users nu, naf_coach c "
          ."WHERE pn_uid=coachid "
          .($showInactive!=1?"and (coachactivationcode is NULL or coachactivationcode='') ":"")
          ."ORDER BY coachlastname, coachfirstname, pn_uname";          
  $res = $dbconn->Execute($query);
  
  echo "<table border=\"1\" cellspacing=\"0\">";
  
  echo "<tr><th>Real Name</th><th>Username</th></tr>";
  
  for ( ; !$res->EOF; $res->moveNext() ) {
    echo "<tr><td>".$res->fields[1].", ".$res->fields[2]."</td><td>".$res->fields[0]."</td></tr>";
  }
   
  echo "</table>";
  
  echo '</center>';
  
  CloseTable();
  include 'footer.php';
?>
