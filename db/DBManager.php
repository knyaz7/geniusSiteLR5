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
    /**
     * Пример вызова
     * select(
     *       ['id', 'email', 'password', 'accessright'],
     *       'users',
     *       ['email' => $email]
     * )
     */
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

    /**
     * Пример вызова
     * update(
     *       'users',
     *       [
     *          'code' => 5,
     *          'email' => 'lol@ya.ru'
     *       ],
     *       ['id' => 4]
     * )
     */
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

    /**
     * Пример вызова
     * insert(
     *       'users',
     *       ['email', 'password', 'name'],
     *       ['lol@ya.ru', 1234, 'Борис']
     * )
     */
    public function insert(string $table, array $insertingFields, array $values)
    {
        $insertingFields = substr(
            implode(', ', $insertingFields), 0, -2
        );
        $values = substr(
            implode(', ', $values), 0, -5
        );
        $query = "INSERT INTO {$table} ({$insertingFields}) VALUES ({$values})";
        return $this->connection->query($query);
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
