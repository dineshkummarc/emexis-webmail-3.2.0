<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Conexão com banco de dados
 *
 * @author bruno
 */

class DatabaseCon {
    protected $db;
    protected $sql;
    
    function __construct($dsn_pear){    
        $this->db =& DB::connect($dsn_pear);
        if (DB::isError($this->db)) {
            die ($this->db->getMessage());
        }
    }
 
}
?>
