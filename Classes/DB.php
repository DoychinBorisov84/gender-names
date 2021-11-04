<?php

class DB
{
    private $dsn;
    private $user;
    private $password;

    private $connection;

    private $sourceDBTable;
    private $filteredDBTable;
    private $testDBTable;

    public function __construct()
    {
        $this->dsn = 'mysql:host=localhost;dbname=people_names';
        $this->user = 'root';
        $this->password = '';

        $this->setConnection();
    }

    public function setSourceDBTable($table)
    {
        $available = $this->getAvailableTables();

        if(!in_array($table, $available)){
            throw new Exception("Incorrect database table", 1);
        }

        return $this->sourceDBTable = $table;
    }

    public function getSourceDBTable()
    {
        return $this->sourceDBTable;
    }

    /**
     * Return arr of available db-tables for access
     * @return array
     */
    public function getAvailableTables()
    {
        return [
            'people',
            'people_limit_50',
            'filtered_names'
        ];
    }


    /**
     * Connect to the dsn
     * 
    */
    public function setConnection()
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING
        ];

        try{
            $this->connection = new PDO($this->dsn, $this->user, $this->password, $options);
            return $this->connection;
        }catch(PDOException $ex){
            echo $ex->getMessage();
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Return all records from the filtered db-table
     * @return array
     */
    public function getFilteredRecords()
    {
        $dBSource = $this->setSourceDBTable('filtered_names');
        // var_dump($db_source);die;
        $sqlSelectFiltered = "SELECT id, firstName, gender, probability, counter FROM $dBSource";

        $result = $this->getConnection()->query($sqlSelectFiltered);

        return $result;
    }

    /**
     * Get the last record ID from the data source table
     */
    public function getSourceLastRecordId()
    {
        $dbSource = $this->setSourceDBTable('people_limit_50');

        $dbLastIDSql = "SELECT id FROM $dbSource ORDER BY id DESC LIMIT 1";
        $dbLastID = $this->getConnection()->query($dbLastIDSql)->fetch(PDO::FETCH_COLUMN, 0);

        return $dbLastID;
    }

    /**
     * Get the last record ID from the filtered table
     */
    public function getFilteredLastRecordId()
    {
        $dbSource = $this->setSourceDBTable('filtered_names');

        $filteredDbLastIDSql = "SELECT person_id FROM $dbSource ORDER BY id DESC LIMIT 1";
        $filteredDbLastID = $this->getConnection()->query($filteredDbLastIDSql)->fetch(PDO::FETCH_COLUMN, 0);

        return $filteredDbLastID;
    }

}