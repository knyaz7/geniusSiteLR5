<?php
class User
{
    protected $password, $email;

    public function __construct($password, $email)
    {
        $this->password = $password;
        $this->email = $email;
    }

    public function __clone()
    {
        $this->email = "Guest";
        $this->password = "qwerty";
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
