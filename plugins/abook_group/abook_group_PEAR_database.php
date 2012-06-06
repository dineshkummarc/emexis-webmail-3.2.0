<?php
/**
 * abook_group_database.php
 *
 * Backend for personal addressbook stored in a database,
 * accessed using the DB-classes in PEAR.
 *
 * IMPORTANT:  The PEAR modules must be in the include path
 * for this class to work.
 *
 * An array with the following elements must be passed to
 * the class constructor (elements marked ? are optional):
 *
 *    dsn       => database DNS (see PEAR for syntax)
 *    table     => table to store addresses in (must exist)
 *    owner     => current user (owner of address data)
 *  ? writeable => set writeable flag (true/false)
 *
 * The table used should have the following columns:
 *  Attribute   | Type | Modifier
 *--------------+------+----------
 * owner        | text |
 * nickname     | text |
 * addressgroup | text |
 * type         | text |
 *
 * The pair (owner,nickname, addressgroup, type) should be unique.
 *
 * Type comes in when you have two different types of address book:
 * abook_database.php
 * abook_global_database.php
 *
 * The (owner,nickname) pair is the unique reference to the addressbook
 * of the owner.
 * @copyright (c) 2002 Kelvin Ho
 * @copyright (c) 2002-2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: abook_group_PEAR_database.php,v 1.6 2006/09/13 16:36:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */
require_once(SM_PATH . 'functions/utils.php');

if(verifiedClass("DB.php")){
    include_once('DB.php');
}else{
    include_once(SM_PATH . 'pear/DB.php');
}

/**
 * @package sm-plugins
 * @subpackage abook_group
 */
class abook_group_database {
    var $btype = 'local';
    var $bname = 'global_database';

    var $dsn       = '';
    var $table     = '';
    var $owner     = '';
    var $dbh       = false;
    var $error     = '';
    var $writeable = true;

    /* ========================== Private ======================= */

    /* Constructor */
    function abook_group_database($param) {
        bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');
        // FIXME: use own string
        $this->sname = _("Global address book");
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');

        if (is_array($param)) {
            if (empty($param['dsn']) ||
                empty($param['table']) ||
                empty($param['owner'])) {
                return $this->set_error('Invalid parameters');
            }

            $this->dsn   = $param['dsn'];
            $this->table = $param['table'];
            $this->owner = $param['owner'];

            if (!empty($param['name'])) {
               $this->sname = $param['name'];
            }

            if (isset($param['writeable'])) {
               $this->writeable = $param['writeable'];
            }

            $this->open(true);
        }
        else {
            return $this->set_error('Invalid argument to constructor');
        }
    }


    /* Open the database. New connection if $new is true */
    function open($new = false) {
        $this->error = '';

        /* Return true is file is open and $new is unset */
        if ($this->dbh && !$new) {
            return true;
        }

        /* Close old file, if any */
        if ($this->dbh) {
            $this->close();
        }

        $dbh = DB::connect($this->dsn, true);

        if (DB::isError($dbh)) {
            bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
            textdomain('abook_group');
            return $this->set_error(sprintf(_("Database error: %s"),
                                            DB::errorMessage($dbh)));
            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
        }

        $this->dbh = $dbh;
        return true;
    }

    /* Close the file and forget the filehandle */
    function close() {
        $this->dbh->disconnect();
        $this->dbh = false;
    }

    /* ========================== Public ======================== */
	/* Delete contacts */
	//Deleta contatos que estão nos grupos mas não estão no catálogo de endereços pessoal
	function deleteContacts(){	
		$query = "delete from addressgroups where owner = owner and nickname <> '' and nickname not in (select address.nickname from address,addressgroups where address.owner = addressgroups.owner and address.nickname = addressgroups.nickname)";
		$res = $this->dbh->query($query);	
		return;
	}

    /* Count members */
	function countMembers($group){
		$ret = array();
		if (!$this->open()) {
            return false;
        }
		$this->deleteContacts();
		$query = sprintf("select count(*) from addressgroups where owner='%s' and nickname <> '' and addressgroup = '%s' and type = 'Personal address book'",$this->owner,$group);
		$res = $this->dbh->query($query);
		 while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            array_push($ret, array('count' => $row['count']));
        }
		$int = $ret[0]['count'];
		return $int;
	}

	/*List all groups*/
    function list_group() {
        $ret = array();
        if (!$this->open()) {
            return false;
        }

        $query = sprintf("SELECT distinct(addressgroup) FROM %s WHERE owner='%s' ORDER BY addressgroup asc",
                         $this->table, $this->owner);

        $res = $this->dbh->query($query);

        if (DB::isError($res)) {
            bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
            textdomain('abook_group');
            return $this->set_error(sprintf(_("Database error: %s"),
                                            DB::errorMessage($res)));
            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
        }

        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            array_push($ret, array('addressgroup' => $row['addressgroup']));
        }
        return $ret;
    }
    
    /* List all groupmembers */
    function list_groupMembers($group) {
        $ret = array();
        if (!$this->open()) {
            return false;
        }

        /*
         * don't enable ldap (remote backends)
         * code uses internal db backend var (table), which is not present in ldap backend. 
         */
        $abook = addressbook_init(true,true);
        $alist = $abook->get_backend_list();

        foreach ($alist as $backend) {
            $bnum=$backend->bnum;
            /* Added for il8n */
            $thisTable = $this->table;
            $abookBackendsTable = $abook->backends[$bnum]->table;
            $thisOwner = $this->owner;
            $backendOwner = $backend->owner;
            // Use backend string according to backend number
            if ($backend->bnum == $abook->localbackend) {
                $abookBackendsSname = "Personal address book";
            } else {
                $abookBackendsSname = "Global address book";
            }

            $query = sprintf("SELECT b.nickname,b.firstname,b.lastname,b.email FROM %s a, %s b WHERE a.owner='%s' AND b.owner='%s' AND a.nickname=b.nickname AND a.addressgroup ='%s' AND a.type='%s' ORDER BY a.nickname asc",
                             $this->dbh->quoteString($thisTable),
                             $this->dbh->quoteString($abookBackendsTable),
                             $this->dbh->quoteString($thisOwner),
                             $this->dbh->quoteString($backendOwner),
                             $this->dbh->quoteString($group),
                             $this->dbh->quoteString($abookBackendsSname));
            $res = $this->dbh->query($query);
                
            if (DB::isError($res)) {
                bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
                textdomain('abook_group');
                return $this->set_error(sprintf(_("Database error: %s"), DB::errorMessage($res)));
                bindtextdomain('squirrelmail', SM_PATH . 'locale');
                textdomain('squirrelmail');
            }

            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                array_push($ret, array('nickname'  => $row['nickname'],
                    'name'      => $row['firstname']." ".$row['lastname'],
                    'firstname' => $row['firstname'],
                    'lastname'  => $row['lastname'],
                    'email'     => $row['email'],
                    'backend'   => $bnum));
            }
        }
        
        return $ret;
    }

	function addGroup($groupName,$user){
		if (!$this->open()) {
            return false;
        }

		$groupName=str_replace(array("<", ">", "\\", "/", "=", "'", "?"), "", $groupName);
		$query = "insert into addressgroups values('$user','','$groupName','Personal address book')";

    	$res = $this->dbh->query($query);

        if (DB::isError($res)) {
            bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
            textdomain('abook_group');
            return $this->set_error(sprintf(_("Database error: %s"),
                                            DB::errorMessage($res)));
            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
			return false;
        }else{
			return true;
		}        
	}
    /* Add array of users to new group */
    function addToGroup($arrayOfUsers, $groupName, $new=false) {
        $groupName = htmlspecialchars($groupName);
        
        if (!$this->writeable) {
            bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
            textdomain('abook_group');

            return $this->set_error(_("Addressbook is read-only"));

            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
        }

        if (!$this->open()) {
            return false;
        }

        if ($new){
                //* See if GROUP exist already /
                $query = sprintf("SELECT distinct(addressgroup) FROM %s WHERE owner='%s'AND addressgroup='%s'",
                                 $this->table,
                                 $this->owner,
                                 $groupName);
                $res = $this->dbh->query($query);
                $ret = array();
                if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                   array_push($ret, array('addressgroup' => $row['addressgroup']));
                }
                if (!empty($ret)) {
                   bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
                   textdomain('abook_group');
                   return $this->error = sprintf(_("Group %s already exists"),$groupName);
                   bindtextdomain('squirrelmail', SM_PATH . 'locale');
                   textdomain('squirrelmail');
                }
        }

        for ($i=0;$i<count($arrayOfUsers);$i++){
            $type = $arrayOfUsers[$i]['backEndSName'];
            $nickName = $arrayOfUsers[$i]['nickName'];

            // FIXME: hardcoded local backend number
            if ($type == 1 ) {
                $sType = "Personal address book";
            } else {
                $sType = "Global address book";
            }
            $this->addUser($nickName, $groupName, $this->owner, $sType);
        }
    }

    /* Add New User into Group */
    function addUser($nickName, $groupName, $owner, $type) {
        $groupName = htmlspecialchars($groupName);

        // FIXME:
        if ($type != "Global address book") {
            $type = "Personal address book";
        }

        //* See if GROUP exist already /
        $type = addslashes ($type);
        $nickName = addslashes ($nickName);
        $groupName = addslashes ($groupName);
        $owner = addslashes ($owner);
        $query = sprintf ("SELECT distinct(nickname) FROM %s WHERE owner='%s'AND addressgroup='%s' AND type='%s' AND nickname='%s'",
                          $this->table,
                          $owner,
                          $groupName,
                          $type,
                          $nickName);
        $type = stripslashes ($type);
        $nickName = stripslashes ($nickName);
        $groupName = stripslashes ($groupName);
        $owner = stripslashes ($owner);
        $res = $this->dbh->query($query);
        $ret = array();
        if ($row = $res->fetchRow(DB_FETCHMODE_ORDERED)) {
           array_push($ret, array('nickname' => $row[0]));
        }
        if (!empty($ret)) {
           bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
           textdomain('abook_group');
           return $this->error = _("User Already Exists in The Group");
           bindtextdomain('squirrelmail', SM_PATH . 'locale');
           textdomain('squirrelmail');
        }

        //* Create query /
        $query = sprintf("INSERT INTO %s (owner, nickname, addressgroup, type) VALUES('%s','%s','%s','%s')",
                         $this->table, $this->owner,
                         $this->dbh->quoteString($nickName),
                         $this->dbh->quoteString($groupName),
                         $this->dbh->quoteString($type));

         // Do the insert /
         $r = $this->dbh->simpleQuery($query);
         if ($r == DB_OK) {
             return true;
         }

         //* Fail /
         bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
         textdomain('abook_group');
         return $this->set_error(sprintf(_("Database error: %s"),
                                         DB::errorMessage($r)));
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
         textdomain('squirrelmail');
    }
    
    function set_error($string) {
        $this->error = '[' . $this->sname . '] ' . $string;
        return false;
    }
    
    /* Remove From Group */
    function removeFromGroup($arrayOfUsers, $groupName) {
        if (!$this->writeable) {
         bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
         textdomain('abook_group');
            return $this->set_error(_("Addressbook is read-only"));
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
         textdomain('squirrelmail');
        }

        if (!$this->open()) {
            return false;
        }
        for ($i=0;$i<count($arrayOfUsers);$i++){
            $type = $arrayOfUsers[$i]['backEndSName'];
            $nickName = $arrayOfUsers[$i]['nickName'];

            // FIXME: hardcoded backend number
            if ($type == 1) {
                $sType = "Personal address book";
            } else {
                $sType = "Global address book";
            }
            $this->removeUser($nickName, $groupName, $this->owner, $sType);
        }
    }

    /* Remove New User from Group */
    function removeUser($nickName, $groupName, $owner, $type) {
        if ($type != "Global address book") {
            $type = "Personal address book";
        }

        /* See if user exist in group */
        $query = sprintf("SELECT distinct(nickname) FROM %s WHERE owner='%s'AND addressgroup='%s' AND type='%s' AND nickname='%s'",
                         $this->table,
                         $owner, $groupName,
                         $type,
                         $nickName);
        $res = $this->dbh->query($query);
        $ret = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ORDERED)) {
           array_push($ret, array('nickname' => $row[0]));
        }
        if (empty($ret)) {
           bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
           textdomain('abook_group');
           return $this->error = _("User Does Not Exist in The Group");
           bindtextdomain('squirrelmail', SM_PATH . 'locale');
           textdomain('squirrelmail');
        }

        //* Create query /
        $query = sprintf("DELETE FROM  %s WHERE owner='%s' AND nickname='%s' AND addressgroup='%s' AND type='%s'",
                         $this->table, $this->owner,
                         $this->dbh->quoteString($nickName),
                         $this->dbh->quoteString($groupName),
                         $this->dbh->quoteString($type));

         // Do the insert /
         $r = $this->dbh->simpleQuery($query);
         if ($r == DB_OK) {
             return true;
         }

         //* Fail /
         bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
         textdomain('abook_group');
         return $this->set_error(sprintf(_("Database error: %s"),
                                         DB::errorMessage($r)));
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
         textdomain('squirrelmail');
    }
    
    /* Remove Group */
    function deleteGroup($groupName) {

        if (!$this->writeable) {
            bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
            textdomain('abook_group');
            return $this->set_error(_("Addressbook is read-only"));
            bindtextdomain('squirrelmail', SM_PATH . 'locale');
            textdomain('squirrelmail');
        }

        if (!$this->open()) {
            return false;
        }

        //* See if GROUP exist already /
        $query = sprintf("SELECT distinct(addressgroup) FROM %s WHERE owner='%s'AND addressgroup='%s'",
                         $this->table,
                         $this->owner,
                         $groupName);
        $res = $this->dbh->query($query);
        $ret = array();
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
           array_push($ret, array('addressgroup' => $row['addressgroup']));
        }
        if (empty($ret)) {
           bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
           textdomain('abook_group');
           return $this->error = sprintf(_("Group %s does not exist"), $groupName);
           bindtextdomain('squirrelmail', SM_PATH . 'locale');
           textdomain('squirrelmail');
        }
        
        //* Create query /
        $query = sprintf("DELETE FROM  %s WHERE owner='%s' AND addressgroup='%s'",
                         $this->table,
                         $this->owner,
                         $this->dbh->quoteString($groupName));

         // Do the insert /
         $r = $this->dbh->simpleQuery($query);
         if ($r == DB_OK) {
             return true;
         }

         //* Fail /
         bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
         textdomain('abook_group');
         return $this->set_error(sprintf(_("Database error: %s"),
                                         DB::errorMessage($r)));
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
         textdomain('squirrelmail');

    }

    /* Modify address group*/
    function modifyGroup($groupName, $newGroupName) {
        $newGroupName = htmlspecialchars($newGroupName);

        bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');
        if ($groupName == $newGroupName) {
            $this->set_error(_("Group name is unchanged."));
        } elseif (!$newGroupName) {
            $this->set_error(_("Group name was empty."));
        } elseif (!$this->writeable) {
            $this->set_error(_("Addressbook is read-only"));
        }
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');

        if ($this->error!='') {
            return $this->error;
        }

        if (!$this->open()) {
            return false;
        }

        //* See if current GROUP exist already /
        $query = sprintf("SELECT distinct(addressgroup) FROM %s WHERE owner='%s'AND addressgroup='%s'",
                         $this->table,
                         $this->owner,
                         $this->dbh->quoteString($groupName));
        $res = $this->dbh->query($query);
        $ret = array();
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
           array_push($ret, array('addressgroup' => $row['addressgroup']));
        }
        if (empty($ret)) {
           bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
           textdomain('abook_group');
           return $this->error = sprintf(_("Group %s does not exist"),$groupName);
           bindtextdomain('squirrelmail', SM_PATH . 'locale');
           textdomain('squirrelmail');
        }

        //* See if another GROUP with same name exist already /
        $query = sprintf("SELECT distinct(addressgroup) FROM %s WHERE owner='%s'AND addressgroup='%s'",
                         $this->table,
                         $this->owner,
                         $this->dbh->quoteString($newGroupName));
        $res = $this->dbh->query($query);
        $ret = array();
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
           array_push($ret, array('addressgroup' => $row['addressgroup']));
        }
        if (!empty($ret)) {
           bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
           textdomain('abook_group');
           return $this->error = sprintf(_("Another group %s already exists"),$newGroupName);
           bindtextdomain('squirrelmail', SM_PATH . 'locale');
           textdomain('squirrelmail');
        }

        //* Create query /
        $query = sprintf("UPDATE %s SET addressgroup='%s' WHERE owner='%s' AND addressgroup='%s'",
                         $this->table,
                         $this->dbh->quoteString($newGroupName),
                         $this->owner,
                         $this->dbh->quoteString($groupName));

        //* Do the insert /
        $r = $this->dbh->simpleQuery($query);
        if ($r == DB_OK) {
            return true;
        }

        //* Fail /
        bindtextdomain('abook_group', SM_PATH . 'plugins/abook_group/locale');
        textdomain('abook_group');
        return $this->set_error(sprintf(_("Database error: %s"),
                                        DB::errorMessage($r)));
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
        textdomain('squirrelmail');
    }

} /* End of class abook_group_database */
