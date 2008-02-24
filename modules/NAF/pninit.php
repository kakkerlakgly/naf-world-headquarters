<?php
/**
 * NAF Module
 *
 * The NAF module shows how to make a PostNuke module.
 * It can be copied over to get a basic file structure.
 *
 * Purpose of file:  Initialisation functions for NAF
 *
 * @package      NAF_modules
 * @subpackage   NAF
 * @author       Kristian Rastrup (slup)
 * @link         http://www.bloodbowl.net
 * @copyright    Copyright (C) 2006 by the NAF
 */


/**
 * initialise the NAF module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance.
 * This function MUST exist in the pninit file for a module
 *
 * @author       Kristian Rastrup (slup)
 * @return       bool       true on success, false otherwise
 */
function NAF_init()
{
    return true;
}


/**
 * upgrade the NAF module from an old version
 *
 * This function can be called multiple times
 * This function MUST exist in the pninit file for a module
 *
 * @author       Kristian Rastrup (slup)
 * @return       bool       true on success, false otherwise
 */
function NAF_upgrade($oldversion)
{
    return true;
}


/**
 * delete the NAF module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * This function MUST exist in the pninit file for a module
 *
 * @author       Kristian Rastrup (slup)
 * @return       bool       true on success, false otherwise
 */
function NAF_delete()
{
    return true;
}

?>