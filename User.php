<?php
class User
{
    protected $password, $email;

    public function __construct($login, $password, $email)
    {
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
        return "E-mail: {$this->email}\nПароль: {$this->password}";
    }
}
