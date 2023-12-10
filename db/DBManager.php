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

    public function closeConnection() {
        $this->connection->close();
    }

    public function select(array $selectedFields, string $table, array $filterConditions)
    {
        $selectedFields = substr(
            implode(', ', $selectedFields), 0, -2
        );
        $filterConditions = substr(
            implode(
                array_map(
                    function($key, $value){
                        return $key . '=' . $value . ' AND ';
                    },
                    array_keys($filterConditions),
                    $filterConditions
                )
            ),
            0, -5
        );
        $query = "SELECT {$selectedFields} FROM {$table} WHERE {$filterConditions}";
        return $this->connection->query($query);
        // $query = $this->connection->prepare($queryString);
        // $query->bind_param($this->determinateTypes(array_values($filterConditions)), $filterConditions);
        // $query->execute();
        // return $query->get_result();
    }

    public function update(string $table, array $updatingFields, array $filterConditions)
    {
        $updatingFields = substr(
            implode(
                array_map(
                    function($key, $value){
                        return $key . '=' . $value . ', ';
                    },
                    array_keys($updatingFields),
                    $updatingFields
                )
            ),
            0, -2
        );
        $filterConditions = substr(
            implode(
                array_map(
                    function($key, $value){
                        return $key . '=' . $value . ' AND ';
                    },
                    array_keys($filterConditions),
                    $filterConditions
                )
            ),
            0, -5
        );
        $query = "UPDATE {$table} SET {$updatingFields} WHERE {$filterConditions}";
        return $this->connection->query($query);
    }

    public function insert()
    {
        
    }

    // protected function determinateTypes($fileds)
    // {
    //     $arrTypes = '';
    //     foreach ($fileds as $field) {
    //         $arrTypes .= gettype($field)[0];
    //     }
    //     return $arrTypes;
    // }
}
