<?php
/**
* NAF Module
*
* Purpose of file:  administration display functions --
*                   This file contains all administrative GUI functions
*                   for the module
*
* @package      NAF_modules
* @subpackage   NAF
* @author       Kristian Rastrup (slup)
* @link         http://www.bloodbowl.net
* @copyright    Copyright (C) 2006 by the NAF
*/


/**
* initialise block
*
* @author       Kristian Rastrup (slup)
*/
function NAF_NAFmenublock_init() {
  // Security
  pnSecAddSchema('NAF:NAFmenublock:', 'Block title::');
}

/**
* get information on block
*
* @author       Kristian Rastrup (slup)
* @return       array       The block information
*/
function NAF_NAFmenublock_info() {
  return array('text_type'      => 'NAF menu',
  'module'         => 'NAF',
  'text_type_long' => 'Show NAF menu',
  'allow_multiple' => false,
  'form_content'   => false,
  'form_refresh'   => true,
  'show_preview'   => true);
}

/**
* display block
*
* @author       Kristian Rastrup (slup)
* @param        array       $blockinfo     a blockinfo structure
* @return       output      the rendered bock
*/
function NAF_NAFmenublock_display($blockinfo) {
  // Security check - important to do this as early as possible to avoid
  // potential security holes or just too much wasted processing.
  // Note that we have NAF:NAFmenublock: as the component.
  if (!pnSecAuthAction(0,
  'NAF:NAFmenublock:',
  "$blockinfo[title]::",
  ACCESS_READ)) {
    return false;
  }

  // Get variables from content block
  $vars = pnBlockVarsFromContent($blockinfo['content']);

  // Check if the NAF module is available.
  if (!pnModAvailable('NAF')) {
    return false;
  }

  // Create output object
  // Note that for a block the corresponding module must be passed.
  $pnRender =& new pnRender('NAF');
  $pnRender->caching = false;

  if (pnSecAuthAction(0, "NAF::", "Membership::", ACCESS_ADMIN)) {
    $fnum = pnModAPIFunc('NAF', 'user', 'getNewPayments');
    if ($fnum > 0) {
      $NAFitems[] = array('url' => pnModUrl('NAF', 'payments'), 'title' => "New Payments: $fnum");
    }

    $fnum = pnModAPIFunc('NAF', 'user', 'getInactiveCoaches');
    if ($fnum > 0) {
      $NAFitems[] = array('url' => pnModUrl('NAF', 'activation'), 'title' => "Inactive Users: $fnum");
    }
  }

  if (pnSecAuthAction(0, 'NAF::', 'Tournaments::', ACCESS_ADMIN)) {
    $fnum = pnModAPIFunc('NAF', 'user', 'getNewTourneys');
    if ($fnum > 0) {
      $NAFitems[] = array('url' => pnModUrl('NAF', 'tournaments'), 'title' => "New Tourneys: $fnum");
    }
	  $NAFitems[] = array('url' => pnModUrl('NAF', 'coachStatusList'), 'title' => "Coaches status list");
  }

  if (pnSecAuthAction(0, 'NAF::', 'Todo::', ACCESS_ADMIN)) {
    $fnum = pnModAPIFunc('NAF', 'user', 'getTodo');
    if ($fnum > 0) {
      $NAFitems[] = array('url' => pnModUrl('NAF', 'todo'), 'title' => "Todo: $fnum");
    }
  }

  if (pnSecAuthAction(0, 'NAF::', 'Admin::', ACCESS_ADMIN)) {
    $fnum = pnModAPIFunc('NAF', 'user', 'getDirtyGames');
    if ($fnum > 0) {
      $NAFitems[] = array('url' => pnModUrl('NAF', 'updater'), 'title' => "Dirty Games: $fnum");
    }
  }

  $pnRender->assign('items', $NAFitems);

  // Populate block info and pass to theme
  $blockinfo['content'] = $pnRender->fetch('NAF_block_NAFmenu.htm');

  return themesideblock($blockinfo);
}
?>