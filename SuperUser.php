<?php
include "User.php";

class SuperUser extends User {
    protected $role;

    public function __construct($role) {
        $this->role = $role;
    }
}

?>