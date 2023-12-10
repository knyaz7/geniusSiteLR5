<?php
class DBManager
{
    protected $connection;

    public function __construct($dbParams)
    {
        $this->connection = new mysqli(
            $dbParams['servername'],
            $dbParams['username'],
            $dbParams['password'],
            $dbParams['database']
        );

        if ($this->connection->connect_error) {
            die("Ошибка подключения к базе данных: " . $this->connection->connect_error);
        }
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

