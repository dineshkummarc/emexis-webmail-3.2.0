<?php
/**
 * abook_group_database_flatfile.php
 * Orignal Code Based on: Kelvin Ho. v0.1 2002-07-01.
 *
 * Backend for personal addressbook stored in a flat file database,
 *
 * This code essentially is a drop in replacement for the existing
 * PEAR database access.  As all of the orignal code is left unchanged
 * I've left the syntax of the calling routines the same.  Items
 * such as dsn, table and so forth can be ignored as we're dealing with
 * a simple flat file format.
 *
 * The (owner,nickname) pair is the unique reference to the addressbook
 * of the owner.
 * @copyright (c) 2004 Jon Nelson <quincy at linuxnotes.net>
 * @copyright (c) 2004-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: abook_group_flatfile_database.php,v 1.5 2006/09/13 16:36:42 tokul Exp $
 * @package sm-plugins
 * @subpackage abook_group
 */

/**
 * @package sm-plugins
 * @subpackage abook_group
 */
class abook_group_database {
    var $btype = 'local';
    var $bname = 'global_database';

    var $table     = 'foobar';
    var $owner     = 'simple';

    var $error     = '';

    var $AGROUPFILE = '';
    var $file_discriptor; // General filedescriptor to use

    /* Constructor */
    function abook_group_database($param) {
        global $data_dir;

        $this->dsn   = $param['dsn'];
        $this->owner = $param['owner'];       
        $this->table = $param['table'];

        $this->AGROUPFILE = $data_dir . $this->owner . ".agroup";
        //sprintf("%s%s.agroup",$data_dir,$this->owner);
    }


    /* Public functions follow */
    /* List all groups */
    function list_group() {
        $ret = array();
        if(file_exists($this->AGROUPFILE)) {
            $filecontents = file($this->AGROUPFILE);

            foreach ($filecontents as $rownum => $line)  {
                $res = explode(":",$line);
                array_push($ret, array('addressgroup' => $res[0]));
            }
        }
        return $ret;
    }
    
    /* List all groupmembers */
    function list_groupMembers($group) {
        $res = array();
        $filecontents = file($this->AGROUPFILE);            
        // Fetch a copy of all the users in a given group
        // essentially all I'm fetching is a list of nicknames
        // given group $group
        $Aagroup = $this->fetch_Aagroup();
        $users = explode(",",$Aagroup["$group"]);

        /*
       Addressbook assoiate values:
        $line['nickname'] nickname field
        $line['name']     Full name
        $line['firstname'] obvious firstname
        $line['lastname']  obvious lastname
        $line['email']     obvious emaill address
        $line['backend']   God knows set to 1
        $line['source']    Personal adddress book
        $line['label']     I can only assume the note field
        */        
        $res = array();
        $abook = addressbook_init(true,true);
        foreach ($abook->list_addr() as $rownum => $line) {            
            if(in_array($line['nickname'],$users)) {                
                array_push($res,array('nickname' => $line['nickname'],
                                      'name'     => $line['name'],
                                      'firstname'=> $line['firstname'],
                                      'lastname' => $line['lastname'],
                                      'email'    => $line['email'],
                                      'backend'     => $line['backend']));
            }
        }
                
        return $res;
    }

    /* Add array of users to new group */
    function addToGroup($arrayOfUsers, $groupName, $new=false) {
        $groupName = htmlspecialchars($groupName);

        $Aagroup = $this->fetch_Aagroup();

        //
        // If the group is new then we might as well just add it to the 
        // Aagroup list.  If the user is foolish enough to store a bunch
        // of nicknames to an existing group but delares it new then
        // we're just going to clobber the existing.
        //
        if($new) {
            foreach($arrayOfUsers as $index => $row) {
                if( $index == 0 ) {
                    $string = $row['nickName'];
                } else {
                    $string = $string . "," . $row['nickName'];
                }
            }
            $Aagroup["$groupName"] = $string . ",\n";
        } else {
            $addusers = array();
            $users = explode(",",$Aagroup["$groupName"]);
            foreach($arrayOfUsers as $index => $row) {
                if(in_array($row['nickName'],$users) == false) {
                    $Aagroup["$groupName"]
                        = $row['nickName'] . "," . $Aagroup["$groupName"];
                }
            }
        }
        $this->Update_file($Aagroup);
    }
  
    /* Remove a user from a Group */
    function removeFromGroup($userData, $groupName) {
        $groupName = htmlspecialchars($groupName);

        //
        // First let's fetch the complete agroup file contents
        // and build the Aagroup assosiate array
        //
        $Aagroup = $this->fetch_Aagroup();

        //
        // We need to remove just the users selected
        //
        $users = explode(",",$Aagroup["$groupName"]);

        // Remove \n
        $users = array_slice($users,0,count($users)-1);

        // Go through all the Users that we need to delete and blast
        // them out of the $users array
        //
        foreach($userData as $index => $value) {
            $users[array_search($value['nickName'],$users)] = "";
        }

        //
        // rebuild the Aagroup line 
        //
        $Aagroup["$groupName"] = "";
        foreach($users as $index => $value) {
            if($value != "") {
                if($Aagroup["$groupName"] == "") {
                    $Aagroup["$groupName"] = $value;
                } else {
                    $Aagroup["$groupName"] 
                        = $Aagroup["$groupName"] . "," . $value;
                }
            }
        }

        $Aagroup["$groupName"] = $Aagroup["$groupName"] . ",\n";
        $this->Update_file($Aagroup);
    }

    /* Remove Group */
    function deleteGroup($groupName) {
        $groupName = htmlspecialchars($groupName);
        $Aagroup = $this->fetch_Aagroup();
        $Aagroup["$groupName"] = "";
        $this->Update_file($Aagroup);
    }

    /* Modify address group*/
    function modifyGroup($groupName, $newGroupName) {
        $newGroupName = htmlspecialchars($newGroupName);
        $groupName = htmlspecialchars($groupName);
        if (($groupName == $newGroupName)||(!$newGroupName)) {
            return;
        }
        $Aagroup = $this->fetch_Aagroup();
        $Aagroup["$newGroupName"] = $Aagroup["$groupName"];
        $Aagroup["$groupName"] = "";
        $this->Update_file($Aagroup);
    }

    /*
     * Update_file($Aagroup)
     *
     * This function will update the flat text file in the following
     * format:
     * groupname:nickname,nickname,nickname,
     *
     * If there are no nicknames in the line Update_file will drop
     * the group.
     */
    function Update_file($Aagroup) {
        $fd = fopen($this->AGROUPFILE,"w+");
        foreach($Aagroup as $group => $groupline) {
            if($groupline != "") {
                fwrite($fd,$group . ":" . $groupline);
            }
        }
        fclose($fd);
    }

    /*
     * Function fetch_Aagroup()
     *
     * This function simply creates an associate list with the group
     * name being the index.  Each element in the array will be a simple
     * string consisting of nicknames separated by ',''s
     */
    function fetch_Aagroup() {
        $res = array();
        $Aagroup = array();
        if(file_exists($this->AGROUPFILE)) {
            $filecontents = file($this->AGROUPFILE);

            foreach($filecontents as $rownum => $line) {
                $res = explode(":",$line);
                $Aagroup["$res[0]"] = $res[1];
            } 
        }
        return $Aagroup;
    }
} /* End of class abook_group_database_flatfile */

