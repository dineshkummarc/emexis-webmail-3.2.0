<?php
/** 
 * This file simply selects the correct database php code to use based
 * on the orignal squirrelmail configuration for the $addrbook_dsn setting
 * in the case that we don't have a database the $addrbook_dsn variable
 * is empty thus use flat file.  If it's set to anything else then
 * we must assume that there is a database used to simply manage
 * groups...
 *
 * Author: Jason Naughton  April 25, 2004
 * @copyright (c) 2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: abook_group_database.php,v 1.9 2006/09/13 16:36:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */
  
//printf("Addressbook_dsn setting: >%s< <br> \n",$addrbook_dsn);

if ( $addrbook_dsn == "" ) {
  include_once(SM_PATH . 'plugins/abook_group/abook_group_flatfile_database.php');
} else {
  include_once(SM_PATH . 'plugins/abook_group/abook_group_PEAR_database.php');
}
