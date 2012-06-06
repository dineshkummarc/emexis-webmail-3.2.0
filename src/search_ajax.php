<?php

/**
 * search_ajax.php
 *
 * 	 P�gina que retorna a busca de contatos atrav�s de requisi��es ajax.
 *
 * 	 This file is included from compose.php
 * 	 Retornas a busca de contatos da p�gina de contatos via ajax
 * 	 @package squirrelmail
 * 	 @subpackage addressbook
 * 	 @author Bruno Borges da Silva <bborges@brc.com.br>
 *
 */
if (!defined('PAGE_NAME')) {
    define('PAGE_NAME', 'search_ajax');
}

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
if (!defined('SM_PATH')) {
    define('SM_PATH', '../');
}

/** SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/addressbook.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'functions/html.php');

sqgetGlobalVar('addrquery', $addrquery, SQ_POST);
sqgetGlobalVar('backend', $backend, SQ_POST);


if (is_null($addrquery) && !isset($_POST['send_to'])) {
    $flag = true;
    sqgetGlobalVar('carrega', $addrquery, SQ_POST);
}
$abook = addressbook_init();
if (empty($addrquery) || $addquery == _("Search contact")) {
    $addrquery = '*';
} else {
    $addrquery = $addrquery;
}

if (is_null($backend) or empty($backend))
    $res = $abook->s_search($addrquery);
else
    $res = $abook->s_search($addrquery, $backend);
$cont = 0;
$strNomes = array();
if (is_array($res)) {
    foreach ($res as $ind => $row) {
        $cont++;
        if (!is_null($limit_show_contacts) && is_numeric($limit_show_contacts)) {
            if ($cont == $limit_show_contacts)
                break;
        }
        $intIni = strpos($row['name'], $addrquery);
        $intLen = strlen($addrquery);
        $intFin = $intIni + $intLen;
        $strRetorno = strtolower(utf8_decode($row['name']));
        $strNomes[] = $strRetorno;
        $strAddq = $addrquery;
        $strSub = strtolower(substr($strAddq, 0, $intFin));
        $strRetorno = str_replace($strSub, "<span>$strSub</span>", strtolower($strRetorno));
        $strRetorno = ucwords($strRetorno);
        $email = $row['email'];

        if((count($res) - 1) == $ind){
            $strJsonNome .= '"' . ucwords($strRetorno) . '"';
            $strJsonMail .= '"' . $email . '"';
        }else{
            $strJsonNome .= '"' . ucwords($strRetorno) . '", ';
            $strJsonMail .= '"' . $email . '", ';
        }
        
    }
 
  
    $json = sprintf('{"nome": [%s], "email": [%s]}',$strJsonNome,$strJsonMail);
}

echo $json;

