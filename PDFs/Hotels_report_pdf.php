<?php

require('PDF.class.php');
require('../API.class.php');
require('../Functions.class.php');

// INSTANCE CLASS 
$_API = new API(null);
$_Functions = new Functions();

// get API TOKEN
$login_response = json_decode($_API->getToken(),true);
$token = $login_response["data"]["token"];
$_API->_API_TOKEN = $token;

// SAVE REPORT DATA 
$hotelsList = json_decode($_API->HTTPRequest("/API/Hotels/List", "GET", null),true);
$data = $hotelsList["data"];

// GET DEFAULT DATA
$dataFilter =  $data;
$user_name = "NO USER NAME";

if(isset($_GET["query"])){

    $query = json_decode($_GET["query"]);

    // GETS PARAMETERS
    $parameters = isset($query->parameters) ? $query->parameters : null;
    $user_name = isset($query->user_name) ? $query->user_name : "";

    // count the total of parameters
    $parametersCount=0;
    if($parameters != null){
        foreach ($parameters as $key => $value)
            $parametersCount++;
    }
    
    // FILTER DATA
    if($parametersCount > 0)
        $dataFilter = $_Functions->getDataFilter($data,$parameters); 
}


$pdf = new PDF();
$pdf->user_name = $user_name;
$pdf->report_tittle = "REPORTE DE HOTELES";

$pdf->AddPage('Portrait', 'Legal');
$pdf->AliasNbPages();

// HEADER DE LA TABLA
$pdf->table_headers = array(
    'No.',
    'COD.',
    'Hotel',
    'Socio',
    "Dirección",
);

if(count($dataFilter) > 0){
    $pdf->SetFont('Arial', '', 9);
    // ESTABLECE EL TAMAÑO DE CADA CELDA
    $pdf->tablewidths = array(10,25,40,45,70);

    $item = 0;

    for ($i=0; $i < count($dataFilter); $i++) { 
        $item = $item+1;
        $key = $dataFilter[$i];

        $id_hotel = $key["id"];
        $hotel = $key["hotel"];
        $partners = $key["partners"];
        $ciudad = "Ciudad {$key["ciudad"]}, Colonia {$key["colonia"]}, Ave. {$key["avenida"]}, Calle {$key["calle"]}";

        // este array se rellena en orden de las columnas
        //ejemplo $item es el valor de la columna #1 y asi
        $row[] = array(
            utf8_decode($item."."),
            utf8_decode("COD-00".$id_hotel),
            utf8_decode($hotel),
            utf8_decode($partners),
            utf8_decode($ciudad)
        );
    }

    $pdf->morepagestable($row,5);
}else{
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(254,38,25);
    $pdf->tablewidths = array(190);
    $row[] = array(
        utf8_decode("NO SE ENCONTRARON DATOS COINCIDENTES CON LA BUSQUEDA")
    );
    $pdf->morepagestable($row,13);
}

$pdf->Output();
