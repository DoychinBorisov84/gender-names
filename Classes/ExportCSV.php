<?php
require_once __DIR__.'/DB.php';

/**
 * Export to CSV class
 * 
 */
class ExportCSV extends DB
{
    private $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->fileName = 'export.csv';
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function export()
    {
        $selectFiltered = $this->getFilteredRecords();
        
        $header = [];
        foreach ($selectFiltered as $key => $value) {
            $header[] = array_keys($value);
            break;
        }
        
        $filename = $this->getFileName();
        $outputStream = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);        
        
        fputcsv($outputStream, $header[0]); // header row

        foreach ($selectFiltered as $arr) {
            fputcsv($outputStream, $arr);
        }
        
        fclose($outputStream);
    }

}
