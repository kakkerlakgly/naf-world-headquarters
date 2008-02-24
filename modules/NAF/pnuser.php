<?php
/**
 * NAF Module
 *
 * The NAF module shows how to make a PostNuke module.
 * It can be copied over to get a basic file structure.
 *
 * Purpose of file:  user display functions --
 *                   This file contains all user GUI functions for the module
 *
 * @package      NAF_modules
 * @subpackage   NAF
 * @author       Kristian Rastrup (slup)
 * @link         http://www.bloodbowl.net
 * @copyright    Copyright (C) 2006 by the NAF
 */


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
function createLink($text, $page, $sec) {
  if (pnSecAuthAction(0, 'NAF::', "$sec::", ACCESS_ADMIN)) {
    return '<td><a href="'.pnVarPrepForDisplay(pnModURL('NAF', $page)).'">'.pnVarPrepForDisplay($text).'</a></td>';
  }
  else {
    return '<td>&nbsp;</td>';
  }
}

function NAF_user_main()
{
  if (!pnUserLoggedIn() ||
       ( !pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN) ||
         !pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN) ||
         !pnSecAuthAction(0, 'NAF::', 'Admin::', ACCESS_ADMIN) ||
         !pnSecAuthAction(0, 'NAF::', 'Membership::', ACCESS_ADMIN))
     ) {
    pnRedirect('index.php');
    exit;
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
  return true;
}
?>