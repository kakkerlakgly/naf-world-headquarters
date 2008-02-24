<?
  pnRedirect(pnModURL('NAF'));
  exit();
  if (!pnUserLoggedIn() ||
       ( !pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN) ||
         !pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) ||
         !pnSecAuthAction(0, 'NAF::', 'Admin::', ACCESS_ADMIN) ||
         !pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN))
     ) {
    pnRedirect("/");
    exit;
  }

  function createLink($text, $page, $sec) {
    if (pnSecAuthAction(0, 'NAF::', "$sec::", ACCESS_ADMIN)) {
      return "<td><a href=\"naf.php?page=$page\">$text</a></td>";
    }
    else {
      return "&nbsp;";
    }
  }

  include 'header.php';
  OpenTable();
  echo '<center>';

  echo '<div style="font-size: 2em;">NAF Admin Menu</div><br />'
      .'<table border="0" width="50%">'
      .'<tr align="center">'
      .createLink("Tournaments", "tournaments", "Tournaments")
      .createLink("Update Rankings", "updater", "Tournaments")
      .'</tr>'
      .'<tr align="center">'
      .createLink("Payments", "payments", "Membership")
      .createLink("Todo", "todo", "Todo")
      .'</tr>'
      .'<tr align="center">'
      .createLink("New Users", "newusers", "Admin")
      .createLink("Quotes", "quotes", "Admin")
      .'</tr>'
      .'<tr align="center">'
      .createLink("Unactivated Coaches", "activation", "Admin")
      .createLink("Membership Premiums", "premiums", "Membership")
      .'</tr>'
      .'<tr align="center">'
      .createLink("Membership renewal", "expired", "Tournaments")
      .createLink("Membership status list", "coachStatusList", "Tournaments")
      .'</tr>'
      .'</table>';

   echo '</center>';

  CloseTable();
  include 'footer.php';
?>
