<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author bruno
 */

define('SM_PATH','../../');
require_once(SM_PATH . 'functions/global.php');
require_once('DatabaseCon.php');

class User extends DatabaseCon{
    public function selectAll($json = false){
        $this->sql = "select distinct \"user\" from userprefs where \"user\" is not null and \"user\" <> ''";        
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);        
        if(!$json){            
            return $res;
        }else{
            return json_encode($res);
        }
    }
    public function selectMembers($id){
        $this->sql = "select username,response from members where fk_event = $id";
        //$this->sql = 'select * from members where ';
        $res = $this->db->getAll($this->sql,array(),DB_FETCHMODE_ASSOC);
        echo json_encode($res);
    }
}

?>
