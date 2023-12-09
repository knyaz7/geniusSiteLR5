<?php
class User
{
    protected $login, $password, $email;

    public function __construct($login, $password, $email)
    {
        $this->login = $login;
        $this->password = $password;
        $this->email = $email;
    }
}
