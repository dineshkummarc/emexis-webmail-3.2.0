<?php

class HandlerGroup {

    private $userOwner;
    public $sql;
    private $db;

    function __construct($username, $dsn_pear) {
        $this->userOwner = $username;
        $this->db = & DB::connect($dsn_pear);
        if (DB::isError($this->db)) {
            die($this->db->getMessage());
        }
    }

    public function addGroup($user, $group) {
        if(isIE())
            $user = utf8_encode($user);        
        $this->sql = "insert into addressgroups values ('$this->userOwner','$user','$group','Personal address book')";
        $res = $this->db->query($this->sql);        
        if (PEAR::isError($res)) {
           die($res->getMessage());
        }
    }

    public function remGroup($user, $group) {
        if(isIE())
            $user = utf8_encode($user);        
        $this->sql = "delete from addressgroups where owner = '$this->userOwner' and nickname = '$user' and addressgroup = '$group'";
        $res = $this->db->query($this->sql);
        if (PEAR::isError($res)) {
            die($res->getMessage());
        }
    }

    public function deleteGroup($group) {
        $this->sql = "delete from addressgroups where owner = '$this->userOwner' and addressgroup = '$group'";
        $res = $this->db->query($this->sql);
        if (PEAR::isError($res)) {
            die($res->getMessage());
        }
    }

    public function renameGroup($newName, $group) {
        $this->sql = "update addressgroups set addressgroup = '$newName' where addressgroup = '$group' and owner = '$this->userOwner'";
        $res = $this->db->query($this->sql);
        if (PEAR::isError($res)) {
           die($res->getMessage());
        }        
        
    }
    public function selectMails($group) {
        $this->sql = "select a.email from address as a left join addressgroups as b on b.nickname = a.nickname where b.addressgroup = '$group' and b.owner ='" . $this->userOwner . "'";
        $res = & $this->db->query($this->sql);

        $id = 0;        
        $total = $res->numRows();
        while ($row = & $res->fetchRow()) {
            $id++;
            if ($id == $total)
                $emails .= '"' . $row[0] . '"';
            else
                $emails .= '"' . $row[0] . '", ';
        }
        $json = sprintf('{"email":[%s]}', $emails);        
        return $json;
    }

}

?>
