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

    public function getLogin()
    {
        return $this->login;
    }

    public function getPasswrord()
    {
        return $this->password;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function showAttr()
    {
        return "Логин: {$this->login}\nПароль: {$this->password}\nE-mail: {$this->email}";
    }
}
