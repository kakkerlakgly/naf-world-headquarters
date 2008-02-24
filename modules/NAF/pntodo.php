<?
/*function createOption($current, $value, $caption) {
  if (strcmp($current, $value) == 0) {
    echo '<option value="'.$value.'" selected="selected">'.$caption.'</option>';
  }
  else {
    echo '<option value="'.$value.'">'.$caption.'</option>';
  }
}*/

function createForm($isNew, $name, $status, $priority, $comment, $id, $staff) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  $dbconn =& pnDBGetConn(true);
  $res = $dbconn->Execute("select pn_uid, pn_uname from nuke_users order by pn_uname");

  $myhtml = new pnHTML();
  $myhtml->FormStart(pnModURL('NAF', 'todo', 'submit'));

  $myhtml->TableStart('', '', 0, '');

  $myhtml->TableRowStart();
  $myhtml->TableColStart(1, 'left');
  $myhtml->Text('Name');
  $myhtml->TableColEnd();
  $myhtml->TableColStart(1, 'left');
  $myhtml->FormText('name', $name);
  $myhtml->TableColEnd();
  $myhtml->TableColStart(1, 'left');
  $myhtml->Text('Status');
  $myhtml->TableColEnd();
  $myhtml->TableColStart(1, 'left');
  $myhtml->FormSelectMultiple('status', array(
    array('id' => "NEW", 'name' => "New"),
    array('id' => "INPROGRESS", 'name' => "In Progress"),
    array('id' => "DONE", 'name' => "Done")
    ), 0, 1, $status);
  $myhtml->TableColEnd();
  $myhtml->TableRowEnd();

  $myhtml->TableRowStart();
  $myhtml->TableColStart(1, 'left');
  $myhtml->Text('Owner');
  $myhtml->TableColEnd();
  $myhtml->TableColStart(1, 'left');

  $data = array();
  for ( ; !$res->EOF; $res->MoveNext() ) {
    $data[] = array('id' => $res->Fields('pn_uid'), 'name' => $res->Fields('pn_uname'));
  }
  $myhtml->FormSelectMultiple('staff', $data, 0, 1, $staff);

  $myhtml->TableColEnd();
  $myhtml->TableColStart(1, 'left');
  $myhtml->Text('Priority');
  $myhtml->TableColEnd();
  $myhtml->TableColStart(1, 'left');

  $myhtml->FormSelectMultiple('priority', array(
    array('id' => "CRITICAL", 'name' => "Critical"),
    array('id' => "IMPORTANT", 'name' => "Important"),
    array('id' => "NORMAL", 'name' => "Normal"),
    array('id' => "LOW", 'name' => "Low")
    ), 0, 1, $priority);
  $myhtml->TableColEnd();
  $myhtml->TableRowEnd();

  $myhtml->TableRowStart();
  $myhtml->TableColStart(4, 'left');
  $myhtml->Text('Comment');
  $myhtml->TableColEnd();
  $myhtml->TableRowEnd();

  $myhtml->TableRowStart();
  $myhtml->TableColStart(4, 'left');
  $myhtml->FormTextArea('comment', $comment, 20, 80);
  $myhtml->TableColEnd();
  $myhtml->TableRowEnd();

  $myhtml->TableEnd();

  $myhtml->FormHidden('id', $id);
  $myhtml->FormSubmit($isNew==true?"Add":"Update");

  $myhtml->FormEnd();
  $myhtml->PrintPage();
}

/*function createForm($isNew, $name, $status, $priority, $comment, $id, $staff) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  $dbconn =& pnDBGetConn(true);
  $res = $dbconn->Execute("select pn_uid, pn_uname from nuke_users order by pn_uname");

  echo '<form method="post" action="'.pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'submit')).'">';

  echo '<table border="0">';

  echo '<tr><td>Name</td><td><input type="text" name="name" value="'.pnVarPrepForDisplay($name).'" /></td>';
  echo '<td>Status</td><td><select name="status">';
  createOption($status, "NEW", "New");
  createOption($status, "INPROGRESS", "In Progress");
  createOption($status, "DONE", "Done");
  echo '</select></td></tr>';

  echo '<tr><td>Owner</td><td><select name="staff">';


  for ( ; !$res->EOF; $res->MoveNext() ) {
    createOption($staff, $res->Fields('pn_uid'), $res->Fields('pn_uname'));
  }

  echo '</select></td>';

  echo '<td>Priority</td><td><select name="priority">';
  createOption($priority, "CRITICAL", "Critical");
  createOption($priority, "IMPORTANT", "Important");
  createOption($priority, "NORMAL", "Normal");
  createOption($priority, "LOW", "Low");
  echo '</select></td></tr>';

  echo '<tr><td colspan="4">Comment</td></tr>'
  .'<tr><td colspan="4"><textarea rows="20" cols="80" name="comment">'
  .pnVarPrepForDisplay($comment).'</textarea></td></tr>';

  echo '</table>';

  echo '<input type="hidden" name="id" value="'.$id.'" />';
  echo '<input type="submit" value="'.($isNew==true?"Add":"Update").'" />';

  echo '</form>';
}*/

function NAF_todo_add($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  include 'header.php';
  OpenTable();

  createForm(true, '', '', 'NORMAL', '', 0, pnUserGetVar('uid'));

  CloseTable();
  include 'footer.php';
  return true;
}

function NAF_todo_edit($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  $dbconn =& pnDBGetConn(true);
  include 'header.php';
  OpenTable();

  $id = pnVarCleanFromInput('id');

  $id += 0;

  $res = $dbconn->Execute("select * from naf_todo where id=".pnVarPrepForStore($id));

  if (!$res->EOF) {
    createForm(false, $res->Fields('name'), $res->Fields('status'), $res->Fields('priority'),
    $res->Fields('comment'), $res->Fields('id'), $res->Fields('staff'));
  }
  else {
    $myhtml = new pnHTML();
    $myhtml->Text('Error while finding Todo item.');
    $myhtml->Linebreak(2);
    $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo')), 'Back');
    $myhtml->PrintPage();
  }

  CloseTable();
  include 'footer.php';
  return true;
}

function NAF_todo_submit($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  $dbconn =& pnDBGetConn(true);
  list($id, $name, $status, $priority, $comment, $staff) = pnVarCleanFromInput('id', 'name', 'status', 'priority', 'comment', 'staff');

  $id += 0;

  if ($id > 0) {
    $qry = "update naf_todo set name='".pnVarPrepForStore($name)."', staff=".pnVarPrepForStore($staff).", status='".pnVarPrepForStore($status)."', priority='".pnVarPrepForStore($priority)."', comment='".pnVarPrepForStore($comment)."' where id=".pnVarPrepForStore($id);
  }
  else {
    $qry = "insert into naf_todo (name, comment, status, priority, staff) values "
    ."('".pnVarPrepForStore($name)."', '".pnVarPrepForStore($comment)."', '".pnVarPrepForStore($status)."', '".pnVarPrepForStore($priority)."', ".pnVarPrepForStore($staff).")";
  }

  $dbconn->Execute($qry);

  pnRedirect(pnModURL('NAF', 'todo'));

  return true;
}

function NAF_todo_delete($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  $dbconn =& pnDBGetConn(true);
  list($id, $confirm) = pnVarCleanFromInput('id', 'confirm');

  if ($confirm != 1) {
    include 'header.php';
    OpenTable();

    $res = $dbconn->Execute("select name from naf_todo where id=".pnVarPrepForStore($id));

    $myhtml = new pnHTML();
    $myhtml->Text('Are you sure you want to delete the following Todo item:');
    $myhtml->Linebreak(2);
    $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'delete', array('id' => $id, 'confirm' => '1'))), 'Yes');
    $myhtml->Linebreak(2);
    $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo')), 'No');
    $myhtml->PrintPage();

    CloseTable();
    include 'footer.php';
  }
  else {
    $qry = "delete from naf_todo where id=".pnVarPrepForStore($id);
    $dbconn->Execute($qry);
    pnRedirect(pnModURL('NAF', 'todo'));
  }

  return true;
}

function NAF_todo_main($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  $dbconn =& pnDBGetConn(true);

  switch(pnVarCleanFromInput('view')) {
    case 'all':
    $where = "status <> 'DONE'";
    break;
    case 'done':
    $where = "status = 'DONE'";
    break;
    default:
    $where = "pn_uid=".pnVarPrepForStore(pnUserGetVar('uid'))." and status <> 'DONE'";
    break;
  }

  $qry = "select * from naf_todo, nuke_users where pn_uid=staff and $where order by priority, name";

  $res = $dbconn->Execute($qry);

  include 'header.php';
  OpenTable();
  $myhtml = new pnHTML();
  $myhtml->TableStart('Todo List', array('Op', 'Item', 'Status', 'Staff', 'Priority'), 1, '');
  for ( ; !$res->EOF; $res->MoveNext()) {
    $myhtml->TableRowStart();
    $myhtml->TableColStart();
    $myhtml->Text('(');
    $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'delete', array('id' => $res->Fields('id')))), 'Delete');
    $myhtml->Text(')');
    $myhtml->TableColEnd();
    $myhtml->TableColStart();
    $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'edit', array('id' => $res->Fields('id')))), $res->Fields('name'));
    $myhtml->TableColEnd();
    $myhtml->TableColStart();
    $myhtml->Text($res->Fields('status'));
    $myhtml->TableColEnd();
    $myhtml->TableColStart();
    $myhtml->Text($res->Fields('pn_uname'));
    $myhtml->TableColEnd();
    $myhtml->TableColStart();
    $myhtml->Text($res->Fields('priority'));
    $myhtml->TableColEnd();
    $myhtml->TableRowEnd();
  }
  $myhtml->TableEnd();
  $myhtml->Linebreak();
  $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'add')), 'Add Todo Item');
  $myhtml->Linebreak(2);
  $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo')), 'View your pending items');
  $myhtml->Linebreak();
  $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo', '', array('view' => 'all'))), 'View all pending items');
  $myhtml->Linebreak();
  $myhtml->URL(pnVarPrepForDisplay(pnModURL('NAF', 'todo', '', array('view' => 'done'))), 'View all completed items');
  $myhtml->PrintPage();

  CloseTable();
  include 'footer.php';
  return true;
}

/*function NAF_todo_main($args) {
  if (!pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    pnRedirect('index.php');
    return;
  }
  $dbconn =& pnDBGetConn(true);
  include 'header.php';
  OpenTable();

  echo '<table border="1">';

  echo '<tr><th colspan="5">Todo List</th></tr>';
  echo '<tr><th>Op</th><th>Item</th><th>Status</th><th>Staff</th><th>Priority</th></tr>';

  switch(pnVarCleanFromInput('view')) {
    case 'all':
    $where = "status <> 'DONE'";
    break;
    case 'done':
    $where = "status = 'DONE'";
    break;
    default:
    $where = "pn_uid=".pnVarPrepForStore(pnUserGetVar('uid'))." and status <> 'DONE'";
    break;
  }

  $qry = "select * from naf_todo, nuke_users where pn_uid=staff and $where order by priority, name";

  $res = $dbconn->Execute($qry);

  for ( ; !$res->EOF; $res->MoveNext()) {
    echo '<tr>'
    .'<td>(<a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'delete', array('id' => $res->Fields('id')))).'">Delete</a>)</td>'
    .'<td><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'edit', array('id' => $res->Fields('id')))).'">'.$res->Fields('name').'</a></td>'
    .'<td>'.$res->Fields('status').'</td>'
    .'<td>'.$res->Fields('pn_uname').'</td>'
    .'<td>'.$res->Fields('priority').'</td>'
    .'</tr>';
  }

  echo '</table>';
  echo '<br /><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'todo', 'add')).'">Add Todo Item</a><br />';
  echo '<br /><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'todo')).'">View your pending items</a>';
  echo '<br /><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'todo', '', array('view' => 'all'))).'">View all pending items</a>';
  echo '<br /><a href="'.pnVarPrepForDisplay(pnModURL('NAF', 'todo', '', array('view' => 'done'))).'">View all completed items</a>';

  CloseTable();
  include 'footer.php';
  return true;
}*/
?>
