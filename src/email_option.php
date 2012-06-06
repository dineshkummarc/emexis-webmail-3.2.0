<?php
/* 
 *
 * Checa se há um email cadastrado para o remetente, responde a uma requisão ajax,
 * caso não haja um email cadastrado para o remetente ele abre uma caixa de dialogo
 * para cadastrar.
 *
 */


define('SM_PATH','../');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/utils.php');
require_once("DB.php");
global $dsn_pear;


$user = $_POST['user'];
$action = $_POST['action'];

DEFINE(COUNT,1);
DEFINE(EMAIL,2);

        //returnEmailOptions($dsn_pear,'');

switch($action){
    case COUNT:
        echo returnCountEmailOptions($dsn_pear,$user);
        break;
    case EMAIL:
        echo returnEmailOptions($dsn_pear,$user);
        break;
}



?>
