<?php
/**
 *
 * Purpose of file:  Provide version and credit information about the module
 *
 * @package      NAF_modules
 * @subpackage   NAF
 * @author       Kristian Rastrup (slup)
 * @link         http://www.bloodbowl.net
 * @copyright    Copyright (C) 2006 by the NAF
 */

// The following information is used by the Modules module
// for display and upgrade purposes
$modversion['name']           = pnVarPrepForDisplay(_NAF_NAME); //'NAF';
// the version string must not exceed 10 characters!
$modversion['version']        = '1.0';
$modversion['description']    = pnVarPrepForDisplay(_NAF_DESCRIPTION);
$modversion['displayname']    = pnVarPrepForDisplay(_NAF_DISPLAYNAME);

// The following in formation is used by the credits module
// to display the correct credits
//$modversion['changelog']      = 'pndocs/changelog.txt';
//$modversion['credits']        = 'pndocs/credits.txt';
//$modversion['help']           = 'pndocs/help.txt';
//$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 0;
$modversion['author']         = 'NAF';
$modversion['contact']        = 'http://www.bloodbowl.net/';

// The following information tells the PostNuke core that this
// module has an admin option.
$modversion['admin']          = 0;

// This one adds the info to the DB, so that users can click on the
// headings in the permission module
$modversion['securityschema'] = array('NAF::' => 'NAF item name::NAF item ID');

?>