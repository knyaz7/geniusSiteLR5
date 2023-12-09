<?php
class User
{
    protected $password, $email;

    public function __construct($email = '', $password = '')
    {
        $this->email = $email;
        $this->password = $password;
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
