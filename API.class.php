<?php 

class API{

    public string $_API_URL;
    public ?string $_API_TOKEN;

    function __construct($API_TOKEN) {
        $this->_API_TOKEN = $API_TOKEN;
        $this->_API_URL = "https://apitotaltravel.azurewebsites.net";
    }

    function HTTPRequest($url, $method, $data){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->_API_URL.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer $this->_API_TOKEN"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "HTTP REQUEST ERROR: " . $err;
        } else {
            return $response;
        }
    }
}


?>