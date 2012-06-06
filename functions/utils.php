<?php
/**
 * utils.php
 *
 * Arquivo de funções úteis do Emexis
 *
 * @copyright BRconnection
 * @author Bruno Borges <bborges@brc.com.br>
 * @package Emexis-Webmail
 */



/**
 * Carrega css
 * @author Bruno Boges <bborges@brc.com.br>
 * @param <String> $page Nome da página
 * @return void
 */
function loadCss($page){
    $css = sprintf("../themes/default/%s.css",$page);
    echo "<link rel='stylesheet' href='$css' type='text/css'>";
    return;
}

/*
 * Verifica se o layout é vertical
 * @author Bruno Borges <bborges@brc.com.br>
 * @return boolean
 */
function is_vertical(){
    global $data_dir, $username;
    if( strcmp(getPref($data_dir, $username, 'layout') ,'vertical') == 0){
        return true;
    }else{
        return false;
    }
}

/*
 * Verifica se o layout está em nova janela
 * @author Bruno Borges <bborges@brc.com.br>
 * @return boolean
 */
function is_new_window(){
    global $data_dir, $username;
    if( strcmp(getPref($data_dir, $username, 'layout') ,'window') == 0){
        return true;
    }else{
        return false;
    }
}
/*
 * Verifica se o browser usado é o Internet explorer
 * @author Bruno Borges <bborges@brc.com.br>
 * @return boolean
 */
function isIE(){
    $browser = $_SERVER['HTTP_USER_AGENT'];
    if(stripos($browser,'MSIE') != -1 && is_numeric(stripos($browser,'MSIE')))
        return true;
}

/*
 * Imprime página de erro
 * @author Bruno Borges <bborges@brc.com.br>
 * @params $msg Mensagem que será impressa na tela de rro
 * @return string
 */
function msgErrorPage($msg){
    global $version;

    $str = '
    <script>
        try{
            errorPage();
        }catch(err){}
    </script>
    <link rel="stylesheet" href="../themes/default/login.css" type="text/css">
    <div class="topRedirect">
    <img class="logoTop" src="../images/mail.png"><div class="messageLogin">'
    . ('Welcome to Emexis-Webmail') . 
    '</div>
    </div>
    <center style="height:100%">
        <div class="screenRedirect">
            <div>'
             .    $msg . '<br><br>'
             .   '<a href="login.php">' .  _("Go to the login page") . '</a>'
             . '</div>
        </div>
    </center>';
 

    return $str;
}

/**
 * Recupera o html da página de dashboard
 *
 * @return string
 */
function dashBoard(){    
    global $dsn_pear,$username;
    $db =& DB::connect($dsn_pear);
    if (PEAR::isError($db)) {
        die($db->getMessage());
    }
    $res =& $db->query('SELECT * FROM dashboard');
    while ($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
       $str .= $row['html'] . "\n";
    }

    $str = str_replace('$username', $username, $str);
    
    return $str;
}

/**
 * Função quer verifica se o módulo do PEAR está instalado
 *
 * @param String $class Nome da classe
 * @return bool
 */
 function verifiedClass($class){
    $path =  get_include_path();
    $rpath =  substr($path,strpos($path,':') + 1 ,strpos($path,'php') + 1);//substr($path,strpos($path,':') + 1 ,strrpos($path,':') - 2);
    if(file_exists($rpath . '/' . $class))
        return true;
    else
        return false;
 }

 function listAgenda(){
        $str = '<div id="list">
            <table width=100% cellspacing=0>
                <th colspan=3 class="labelListAgenda">' .
                    _('Commitments') .
                '</th>                
            </table>
        </div>';
        return $str;
 }


 function returnCountEmailOptions($dsn_pear,$user){
    $db =& DB::connect($dsn_pear);
        if (DB::isError($db)) {
            die ($db->getMessage());
        }
        $sql = "select count(prefval) from userprefs where prefkey = 'email_address'"
                . "and \"user\" = '$user'";

    $ret = $db->getAll($sql,array(),DB_FETCHMODE_ASSOC);
    return $ret[0]['count'];
}
function returnEmailOptions($dsn_pear,$user){
    $db =& DB::connect($dsn_pear);
        if (DB::isError($db)) {
            die ($db->getMessage());
        }
        $sql = "select prefval from userprefs where prefkey = 'email_address'"
                . "and \"user\" = '$user'";
    
    $ret = $db->getAll($sql,array(),DB_FETCHMODE_ASSOC);
    return $ret[0]['prefval'];
}
?>
