<?php

class Functions{
    
    function getDataFilter($data,$parameters){

        $arrayFilter = array();
        foreach ($parameters as $key => $value){

            if(count($arrayFilter) == 0){

                for ($j = 0; $j < count($data); $j++){
                
                    $element = $data[$j];
                    
                    if($element[$key] == intval($value)){
                        array_push($arrayFilter, $element);
                    }
                }
            }
            else{

                $temporalFilter = $arrayFilter;
                $arrayFilter = array();
                for ($j = 0; $j < count($temporalFilter); $j++){
                
                    $element = $temporalFilter[$j];
                    
                    if($element[$key] == intval($value)){
                        array_push($arrayFilter, $element);
                    }
                }
            }
        }

        return $arrayFilter;
    }
}

?>