<?php

class ConfigManager
{
    protected $env;

    public function __construct()
    {
        $this->env = parse_ini_file('.env');
    }

    public function getDBParam()
    {
        return [
            'servername' => $this->env['DB_HOST'],
            'username' => $this->env['DB_USERNAME'],
            'password' => $this->env['DB_PASSWORD'],
            'database' => $this->env['DB_DATABASE'],
        ];
    }
}

?>
