<?php

require_once "User.php";
require_once "Role.php";

class PrivilegedUser extends User{

    private $roles;

    public function __constructor(){
        parent:: __constructor();
        $this->roles = array();
    }

    public static function getByUsername($username){

        $sql = "SELECT * FROM users WHERE username = '$username' ";
        $result = queryMysql($sql);
        if($result->num_rows){
            $row = $result->fetch_assoc();
            $privilegedUser = new PrivilegedUser();
            $privilegedUser->id = $row['id'];
            $privilegedUser->username = $row['username'];
            $privilegedUser->password = $row['password'];
            $privilegedUser->initRoles($row['id']);
            return $privilegedUser;
        } else {
            return false;
        }
    }

    public function initRoles($user_id){
        $sql = "SELECT t1.role_id, t2.role_name FROM user_roles AS t1
                LEFT JOIN roles AS t2
                ON t1.role_id = t2.id
                WHERE t1.user_id = $user_id";
        $result = queryMysql($sql);
        while($row = $result->fetch_assoc()){
            $this->roles[$row['role_name']] = Role::getRolePerms($row['role_id']);
        }
    }

    public function hasPrivilege($perm){
        
        if($this->roles){
        foreach($this->roles as $role){
            if($role->hasPermission($perm)){
                return true;
            }
        }
        }
        return false;
         }

}






