<?php
/**
 * functions.php - Select Language functions
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
 * @version $Id: functions.php,v 1.5 2005/11/29 18:36:31 tokul Exp $
 * @package sm-plugins
 * @subpackage select_language
 */

/** @ignore */
if (!defined('SM_PATH')) define('SM_PATH','../../');

/** load sqgetGlobalVar() */
include_once(SM_PATH.'functions/global.php');
/** check_sm_version() */
include_once(SM_PATH.'functions/strings.php');

/** set configuration globals */
global $select_language_altnames, $select_language_detect_limits, 
    $select_language_detect_preferred, $select_language_patched;

/** load default configuration*/
if (file_exists(SM_PATH . 'plugins/select_language/config_default.php')) {
    include_once(SM_PATH . 'plugins/select_language/config_default.php');
} else {
    // set default config values inside script, if file is removed.
    $select_language_altnames=false;
    $select_language_detect_limits=true;
    $select_language_detect_preferred=true;
    $select_language_patched=false;
}

/** site configuration */
if (file_exists(SM_PATH . 'config/select_language_config.php')) {
    include_once(SM_PATH . 'config/select_language_config.php');
} elseif (file_exists(SM_PATH . 'plugins/select_language/config.php')) {
    include_once(SM_PATH . 'plugins/select_language/config.php');
}

/**
 * Show language selection form
 */
function select_language_form_function() {
    global $languages, $select_language_altnames, $select_language_detect_limits, 
        $select_language_patched, $plugins, $color;

    unset($only_langs);

    if ($select_language_detect_limits) {
        // Limit languages plugin
        if (in_array('limit_languages',$plugins)) {
            unset($limit_languages);
            if (file_exists(SM_PATH . 'config/limit_languages_config.php')) {
                include_once(SM_PATH . 'config/limit_languages_config.php');
            } elseif (file_exists(SM_PATH . 'plugins/limit_languages/config.php')) {
                include_once(SM_PATH . 'plugins/limit_languages/config.php');
            }
            if (isset($limit_languages) && is_array($limit_languages) && ! empty($limit_languages)) {
                $only_langs=array();
                foreach ($languages as $lang_code => $lang_data) {
                    // detect language code ($languages['en_US']) that is present in $limit_languages 
                    // ($limit_languages=array('en_US');)
                    if (in_array($lang_code, $limit_languages)) {
                        $only_langs[]=$lang_code;
                    }
                    // detect language alias that is used in plugins configuration 
                    // ($limit_languages=array('en');)
                    if (isset($lang_data['ALIAS']) && in_array($lang_code, $limit_languages)){
                        $only_langs[]=$lang_data['ALIAS'];
                    }                   
                }
                // custom charset plugin is enabled and custom language is present in language limits
                if (in_array('custom', $limit_languages) && in_array('custom_charset',$plugins)) {
                    $only_langs[]='custom';
                }
            }
        }

        if(!isset($only_langs)) {
            // language limits are not available
            $select_language_detect_limits=false;
            $only_langs = array();
        }
    } else {
        // language limits are not used in select_language plugin
        $only_langs=array();
    }

    $language_values = array();
    foreach ($languages as $lang_key => $lang_attributes) {
        if (isset($lang_attributes['NAME']) &&
            (! $select_language_detect_limits || in_array($lang_key,$only_langs) )) {
            if ($select_language_altnames && isset($lang_attributes['ALTNAME'])) {
                $language_values[$lang_key] = $lang_attributes['ALTNAME'];
            } else {
                $language_values[$lang_key] = $lang_attributes['NAME'];
            }
        }
    }

    // switch domain
    bindtextdomain('select_language',SM_PATH . 'plugins/select_language/locale');
    textdomain('select_language');

    if (in_array('custom_charset',$plugins) && 
        (! $select_language_detect_limits || in_array('custom',$only_langs))) {
        // i18n: translated string for custom_charset plugin.
        $language_values['custom']=_("English (custom charset)");
    }

    // ALTNAME uses html codes and they are not sorted correctly
    if ($select_language_altnames) {
        ksort($language_values);
    } else {
        asort($language_values);
    }

    // get preferred language
    $preferred=select_language_detect($language_values);

    // i18n: 'Default' as in 'Default Language'. Already configured language or default interface language
    $language_values = array_merge(array('0' => _("Default")), $language_values);

    if (count($language_values)>2) {
        $ret = '<td>' . _("Language:") . '</td><td>' .  select_language_select_option('select_language',$language_values,$preferred) . '</td>';
    } else {
        $ret = '';
    }

    // switch domain
    bindtextdomain('squirrelmail',SM_PATH . 'locale');
    textdomain('squirrelmail');

    // handle older login_form hook
    if (check_sm_version(1,5,1) || $select_language_patched) {
        return $ret;
    } elseif (!empty($ret)) {
            echo $ret;
        return '';
    }
}

/**
 * @param string $name
 * @param array $values
 * @param string $default
 * @return string html formated language selection box
 */
function select_language_select_option($name,$values,$default) {
    $ret = '<select name="'.htmlspecialchars($name) . "\">\n";
    foreach ($values as $key => $val) {
        $ret .= '<option value="' .
            htmlspecialchars( $key ) . '"' .
            (($default == $key) ? ' selected="selected"' : '') .
            '>' .$val ."</option>\n";
    }
    $ret .= "</select>\n";

    return $ret;
}

/**
 * Detect preferred language
 *
 * Uses SquirrelMail cookie and HTTP_ACCEPT_LANGUAGE for detection.
 * @param array $langs list of available languages
 * @return string preferred language code
 */
function select_language_detect($langs) {
    global $languages, $select_language_detect_preferred;

    // don't do detection, if configuration does not permit it.
    if (! $select_language_detect_preferred) return null;

    // Get language code from cookie (return null in order to avoid saving language option)
    if (sqgetGlobalVar('squirrelmail_language',$cookie_lang,SQ_COOKIE) && 
        isset($langs[$cookie_lang]))
        return null;

    // don't do detection if header is not available
    if (! sqgetGlobalVar('HTTP_ACCEPT_LANGUAGE',$remote_lang,SQ_SERVER))
        return null;

    $lang_array=explode(',',$remote_lang);

    $new_array='';
    foreach ($lang_array as $lang_key) {
        if (! preg_match("';'",$lang_key)) {
            // key does not have language options. assume preferred one.
            $new_array[str_replace('-','_',$lang_key)]=1;
        } else {
            // key has language pref options.
            $options=explode(';',$lang_key);
            $new_array[str_replace('-','_',$options[0])]=substr($options[1],2);
        }
    }

    arsort($new_array);

    foreach ($new_array as $lang => $pref) {
        if (! isset($preferred_language)) {
            foreach ($langs as $key => $value) {
                if (strtolower($key)==strtolower($lang)) {
                    $preferred_language=$key;
                } elseif (isset($languages[$lang]['ALIAS']) &&
                          isset($langs[$languages[$lang]['ALIAS']])) {
                    $preferred_language=$languages[$lang]['ALIAS'];
                }
            }
        }
    }
    if (! isset($preferred_language))
        $preferred_language=null;

    return $preferred_language;
}
/**
 * Set language after login is verified
 */
function select_language_set_function() {
    global $languages, $data_dir, $username, $plugins;
    if (sqgetGlobalVar('select_language',$selected_language,SQ_FORM) && 
        (isset($languages[$selected_language]) || 
         (in_array('custom_charset',$plugins) && $selected_language=='custom'))) {
        setPref($data_dir,$username,'language',$selected_language);
    }
}
?>
