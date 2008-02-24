<?
  include 'includes/pnAPI.php';
  pnInit();
  $page = pnVarCleanFromInput('page');
  if (strlen($page) == 0) {
    pnRedirect(pnModURL('NAF'));
    exit;
  }
  if ($page == 'league') {
    pnRedirect(pnModURL('Stars', $page));
    exit;
  }
  if ($page == 'team') {
    pnRedirect(pnModURL('Stars'));
    exit;
  }

  pnRedirect(pnModURL('NAF', $page));
  exit;

  /*// Get variables
  list($page, $op, $view, $action) = pnVarCleanFromInput('page', 'op', 'view', 'action');

  list($dbconn) = pnDBGetConn();

  if (strlen($page) == 0) {
    $page = "menu";
  }

  $file = 'NAF/'.pnVarPrepForOS($page).'.php';

  if (file_exists($file)) {
    include $file;
  }
  else {
    pnRedirect('index.php');
  }*/
?>
