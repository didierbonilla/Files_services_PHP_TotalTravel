<?php

require('PDF.class.php');
require('../API.class.php');
require('../Functions.class.php');

// INSTANCE CLASS --------------------------------------------------------------------
$_API = new API(null);
$_Functions = new Functions();

// get API TOKEN
$login_response = json_decode($_API->getToken(),true);
$token = $login_response["data"]["token"];
$_API->_API_TOKEN = $token;

// SAVE REPORT DATA 
$hotelsList = json_decode($_API->HTTPRequest("/API/Transports/List", "GET", null),true); // CAMBIO #1
$data = $hotelsList["data"];

// GET DEFAULT DATA
$dataFilter =  $data;
$user_name = "NO USER NAME";

// FILTER DATA
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

// CREATE PDF DOCUMENT --------------------------------------------------------------------
$pdf = new PDF();
$pdf->user_name = $user_name;
$pdf->report_tittle = "REPORTE DE TRANSPORTES";

$pdf->AddPage('Portrait', 'Legal');
$pdf->AliasNbPages();

// HEADER DE LA TABLA
$pdf->table_headers = array( // CAMBIO #2
    'No.',
    'Nombre',
    'Tipo Transporte',
    'Socio',
    "Dirección",
);

if(count($dataFilter) > 0){
    $pdf->SetFont('Arial', '', 9);
    // ESTABLECE EL TAMAÑO DE CADA CELDA
    $pdf->tablewidths = array(8,40,30,40,70); // TAMAÑOS EN MM DE CADA COLUMNA  // CAMBIO #3

    $item = 0;

    for ($i=0; $i < count($dataFilter); $i++) { 
        $item = $item+1;
        $key = $dataFilter[$i];

        $id_transport = $key["id"];
        $nombre = $key["nombre"];
        $tipo_transporte = $key["tipoTransporte"];
        $partners = $key["nombrePartner"];
        $direccion = "Ciudad {$key["ciudad"]}, Colonia {$key["colonia"]}, Calle {$key["calle"]} Y Ave. {$key["avenida"]} ";

        // este array se rellena en orden de las columnas
        //ejemplo $item es el valor de la columna #1 y asi  // CAMBIO #4
        $row[] = array(
            utf8_decode($item."."),
            utf8_decode($nombre),
            utf8_decode($tipo_transporte),
            utf8_decode($partners),
            utf8_decode($direccion)
        );
    }

    $pdf->morepagestable($row,5);
}else{
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(254,38,25);
    $pdf->tablewidths = array(0);
    $row[] = array(
        utf8_decode("NO SE ENCONTRARON DATOS COINCIDENTES CON LA BUSQUEDA")
    );
    $pdf->morepagestable($row,13);
}

$pdf->Output();
