<?php

class DBManager
{
    protected $env;

    public function __construct()
    {
        $this->env = parse_ini_file('.env');
    }

    public function dbConnect()
    {
        $params = $this->getConnParam();
        return new mysqli(
            $params['servername'],
            $params['username'],
            $params['password'],
            $params['database']
        );
    }

    protected function getConnParam()
    {
        return [
            'servername' => $this->env['DB_HOST'],
            'username' => $this->env['DB_USERNAME'],
            'password' => $this->env['DB_PASSWORD'],
            'database' => $this->env['DB_DATABASE'],
        ];
    }
}

