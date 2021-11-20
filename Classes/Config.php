<?php

class Config
{    
    /**
     * @var array $arr
     * Pretty print array
     */
    public function print_pr($arr){
        echo '<pre>'.print_r($arr, true).'</pre>';
    }

    /**
     * Basic output sanitize
     * @param string $sanitize
     * @param string $filter
     */
    public function sanitizeOutput($sanitize, $filter)
    {
        $options = [
            'string' => FILTER_SANITIZE_STRING,
            'email' => FILTER_SANITIZE_EMAIL,
            'int' => FILTER_SANITIZE_NUMBER_INT
        ];

        echo filter_var($sanitize, $options[$filter]);
    }

    /**
     * Api request
     * @return string $result
     */
    public function apiCall($chunk)
    {
        $api_prepared = ''; // API DOC: https://api.genderize.io/?name[]=peter&name[]=lois&name[]=stevie
        foreach ($chunk as $name_string) {
            $api_prepared .= 'name[]='.$name_string.'&';
        }
        
        $api_endpoint = "https://api.genderize.io?$api_prepared"; // SINGLE $api_endpoint = "https://api.genderize.io?name=$api_prepared";
        $curl = curl_init($api_endpoint);
        
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        
        return $result;
    }
}