<?
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect("index.php");
    return;
  }

  function createOption($current, $value, $caption) {
    if (strcmp($current, $value) == 0) {
      echo "<option value=\"$value\" selected=\"1\">$caption</option>";
    }
    else {
      echo "<option value=\"$value\">$caption</option>";
    }
  }

  function createForm($isNew, $name, $status, $priority, $comment, $id, $staff) {
      global $dbconn;

      echo "<form method=\"POST\" action=\"naf.php\">";

      echo "<table border=\"0\">";

      echo "<tr><td>Name</td><td><input type=\"text\" name=\"name\" value=\"".pnVarPrepForDisplay($name)."\"></td>";
      echo "<td>Status</td><td><select name=\"status\">";
      createOption($status, "NEW", "New");
      createOption($status, "INPROGRESS", "In Progress");
      createOption($status, "DONE", "Done");
      echo "</select></td></tr>";

      echo "<tr><td>Owner</td><td><select name=\"staff\">";

      $res = $dbconn->Execute("select pn_uid, pn_uname from nuke_users order by pn_uname");

      for ( ; !$res->EOF; $res->MoveNext() ) {
        createOption($staff, $res->Fields('pn_uid'), $res->Fields('pn_uname'));
      }

      echo "</td>";

      echo "<td>Priority</td><td><select name=\"priority\">";
      createOption($priority, "CRITICAL", "Critical");
      createOption($priority, "IMPORTANT", "Important");
      createOption($priority, "NORMAL", "Normal");
      createOption($priority, "LOW", "Low");
      echo "</select></td></tr>";

      echo "<tr><td colspan=\"4\">Comment</td></tr>"
          ."<tr><td colspan=\"4\"><textarea rows=\"20\" cols=\"80\" name=\"comment\">"
          .pnVarPrepForDisplay($comment)."</textarea></td></tr>";

      echo "</table>";

      echo "<input type=\"hidden\" name=\"page\" value=\"todo\">";
      echo "<input type=\"hidden\" name=\"op\" value=\"submit\">";
      echo "<input type=\"hidden\" name=\"id\" value=\"$id\">";
      echo "<input type=\"Submit\" value=\"".($isNew==true?"Add":"Update")."\">";

      echo "</form>";
  }

  list($dbconn) = pnDBGetConn();

  switch($op) {
    case 'add':
      include 'header.php';
      OpenTable();

      createForm(true, '', '', 'NORMAL', '', 0, pnUserGetVar('uid'));

      CloseTable();
      include 'footer.php';
      break;
    case 'edit':
      include 'header.php';
      OpenTable();

      $id = pnVarCleanFromInput('id');

      $id += 0;

      $res = $dbconn->Execute("select * from naf_todo where id=$id");

      if (!$res->EOF) {
        createForm(false, $res->Fields('name'), $res->Fields('status'), $res->Fields('priority'), 
                   $res->Fields('comment'), $res->Fields('id'), $res->Fields('staff'));
      }
      else {
        echo "Error while finding Todo item.<br /><br /><a href=\"naf.php?page=todo\">Back</a>";
      }

      CloseTable();
      include 'footer.php';
      break;
    case 'submit':

      list($id, $name, $status, $priority, $comment, $staff) = pnVarCleanFromInput('id', 'name', 'status', 'priority', 'comment', 'staff');

      $id += 0;

      list($id, $name, $status, $priority, $comment, $staff) = pnVarPrepForStore($id, $name, $status, $priority, $comment, $staff);

      if ($id > 0) {
        $qry = "update naf_todo set name='$name', staff=$staff, status='$status', priority='$priority', comment='$comment' where id=$id";
      }
      else {
        $qry = "insert into naf_todo (name, comment, status, priority, staff) values "
              ."('$name', '$comment', '$status', '$priority', $staff)";
      }

      $dbconn->Execute($qry);

      pnRedirect("naf.php?page=todo");

      break;
    case 'delete':
      list($id, $confirm) = pnVarCleanFromInput('id', 'confirm');

      if ($confirm != 1) {
        include 'header.php';
        OpenTable();

        $res = $dbconn->Execute("select name from naf_todo where id=$id");

        echo "<center>Are you sure you want to delete the following Todo item:<br /><br />"
            .$res->Fields('name')."<br /><br />"
            ."<a href=\"naf.php?page=todo&op=delete&id=$id&confirm=1\">Yes</a> "
            ."<a href=\"naf.php?page=todo\">No</a>";

        CloseTable();
        include 'footer.php';
      }
      else {
        $qry = "delete from naf_todo where id=$id";
        $dbconn->Execute($qry);
        pnRedirect("naf.php?page=todo");
      }

      break;
    default:
      include 'header.php';
      OpenTable();

      $view = pnVarCleanFromInput('view');

      echo "<table border=\"1\">";

      echo "<tr><th colspan=\"5\">Todo List</th></tr>";
      echo "<tr><th>Op</th><th>Item</th><th>Status</th><th>Staff</th><th>Priority</th></tr>";

      switch($view) {
        case 'all':
          $where = "status <> 'DONE'";
          break;
        case 'done':
          $where = "status = 'DONE'";
          break;
        default:
          $where = "pn_uid=".pnUserGetVar('uid')." and status <> 'DONE'";
          break;
      }

      $qry = "select * from naf_todo, nuke_users where pn_uid=staff and $where order by priority, name";

      $res = $dbconn->Execute($qry);

      for ( ; !$res->EOF; $res->MoveNext()) {
        echo "<tr>"
            ."<td>(<a href=\"naf.php?page=todo&op=delete&id=".$res->Fields('id')."\">Delete</a>)</td>"
            ."<td><a href=\"naf.php?page=todo&op=edit&id=".$res->Fields('id')."\">".$res->Fields('name')."</a></td>"
            ."<td>".$res->Fields('status')."</td>"
            ."<td>".$res->Fields('pn_uname')."</td>"
            ."<td>".$res->Fields('priority')."</td>"
            ."</tr>";
      }

      echo "</table>";
      echo "<br /><a href=\"naf.php?page=todo&op=add\">Add Todo Item</a><br />";
      echo "<br /><a href=\"naf.php?page=todo\">View your pending items</a>";
      echo "<br /><a href=\"naf.php?page=todo&view=all\">View all pending items</a>";
      echo "<br /><a href=\"naf.php?page=todo&view=done\">View all completed items</a>";

      CloseTable();
      include 'footer.php';
      break;
  }

?>
