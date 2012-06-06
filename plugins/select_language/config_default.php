<?php
/**
 * config_default.php - Default configuration file
 *
 * Copyright (c) 2005 The SquirrelMail Project Team
 * This file is part of SquirrelMail Select Language plugin.
 *
 * Select Language plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Select Language plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Select Language plugin; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id: config_default.php,v 1.4 2005/11/29 18:36:31 tokul Exp $
 * @package sm-plugins
 * @subpackage select_language
 */

/**
 * Controls use of ALTNAME key in $languages array.
 *
 * ALTNAME can display localized translation name.
 * 
 * WARNING: Due to ALTNAME formating specifics, selection box can be sorted 
 * only by language code. This issue can't be solved without enforcing same Unicode
 * character set in all SquirrelMail translations.
 * @global boolean $select_language_altnames
 */
$select_language_altnames=false;

/**
 * Controls detection of preferred language from HTTP_ACCEPT_LANGUAGE header.
 * 
 * Detection is not used, if user has squirrelmail_language cookie and selected
 * language is available.
 * @global boolean $select_language_detect_preferred
 */
$select_language_detect_preferred=true;

/**
 * Controls detection of language limits in SquirrelMail
 *
 * Limits can be set by limit_languages plugin.
 * @global boolean $select_language_detect_limits
 */
$select_language_detect_limits=true;

/**
 * Controls used hook format.
 * Older SquirrelMail versions use do_hook function in login_form hook. Hook
 * places language selection box after login button. SquirrelMail 1.5.1 
 * contains modified hook version and plugin can place language selection box
 * before login button. If you want to see language selection box before
 * login button, you must apply login.php.diff to src/login.php script and set 
 * this options to true. See INSTALL doc for more details about this patch.
 * @global boolean $select_language_patched
 */
$select_language_patched=false;

?>