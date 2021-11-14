<?php

class DB
{
    private $dsn;
    private $user;
    private $password;

    private $connection;

    private $sourceDBTable;
    private $recordsLimit; //TODO: implement getter/setter

    public function __construct()
    {
        $this->dsn = 'mysql:host=localhost;dbname=people_names';
        $this->user = 'root';
        $this->password = '';
        $this->recordsLimit = 10;

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
     * @return array|object
     */
    public function getFilteredRecords($lastPersonId=false)
    {
        // No records
        if(!$lastPersonId){
            $lastPersonId = 0;
        }
        $dBSource = $this->setSourceDBTable('filtered_names');
        
        $sqlSelectFiltered = "SELECT id, firstName, gender, probability, counter FROM $dBSource WHERE person_id > $lastPersonId";

        $result = $this->getConnection()->query($sqlSelectFiltered);

        return $result;
    }

    /**
     * Get the last record ID from the data source table
     * @return int|string
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
     * @return int|string
     */
    public function getFilteredLastRecordId()
    {
        $dbSource = $this->setSourceDBTable('filtered_names');

        $filteredDBLastIDSql = "SELECT person_id FROM $dbSource ORDER BY id DESC LIMIT 1";
        $filteredDBLastID = $this->getConnection()->query($filteredDBLastIDSql)->fetch(PDO::FETCH_COLUMN, 0);

        return $filteredDBLastID;
    }

    /**
     * Get data to be displayed
     * @return object|array
     */
    public function getSourceData()
    {
        $filteredDBLastSourceID = $this->getFilteredLastRecordId();
        $dbSource = $this->setSourceDBTable('people_limit_50');

        // Set the start-offset if the table is empty
        if($filteredDBLastSourceID != false){
            $offsetSql = "SELECT COUNT(id) as counter FROM $dbSource WHERE id <= $filteredDBLastSourceID";
            $offset = $this->connection->query($offsetSql)->fetch(PDO::FETCH_COLUMN);
        }else{
            $offset = 0;
        }
                
        $recordsWithOffset = $this->getOffsetFromSourceTable($dbSource, $offset);

        return $recordsWithOffset;
    }

    /**
     * Get records for processing based on limit / offset
     * @return object|array
     */
    public function getOffsetFromSourceTable($table, $offset)
    {
        $dbSource = $this->setSourceDBTable($table);

        // The result of the query with offset/limit
        $records_limit_offset_sql = "SELECT id, firstName FROM $dbSource LIMIT $this->recordsLimit OFFSET $offset";
        $records_limit_offset = $this->connection->query($records_limit_offset_sql)->fetchAll(PDO::FETCH_ASSOC);

        return $records_limit_offset;
    }

    /**
     * Check if the string-name exists into filtered table
     * @param string $name
     * @return array|object 
     */
    public function checkFilterdExists($name)
    {
        $dbSource = $this->setSourceDBTable('filtered_names');

        $existSql = "SELECT firstName FROM $dbSource WHERE firstName LIKE '%$name%' ";
        $exist = $this->connection->query($existSql)->fetchAll();

        return $exist;
    }

    /**
     * Save filtered records to db
     * @param array $filteredNames
     * @return bool $dataSaved flag
     */
    public function saveFilteredResults($filteredNames)
    {
        $dbSource = $this->setSourceDBTable('filtered_names');

        foreach ($filteredNames as $key => $value) {
            $name = $value['name'];
            $person_id = $value['person_id'];
            $gender = $value['gender'];
            $probability = $value['probability'];
            $count = $value['count'];
        
            $insertFilteredSql = "INSERT INTO $dbSource(firstName, person_id, gender, probability, counter, timestamp) VALUES(:name, :person_id, :gender, :probability, :count, now())";
            
            $insertFiltered = $this->connection->prepare($insertFilteredSql);
            
            $dataSaved = $insertFiltered->execute([':name' => $name, 'person_id' => $person_id, ':gender' => $gender, ':probability' => $probability, ':count' => $count]);
          }
        
         return $dataSaved;
    }

}