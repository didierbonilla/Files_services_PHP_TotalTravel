<?php
//query={"parameters":{"id":2,"edad":"18-20"},"user_name":"user_name_example"}
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
$partnersList = json_decode($_API->HTTPRequest("/API/Partners/List", "GET", null),true);
$data = $partnersList["data"];

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
$pdf->report_tittle = "REPORTE DE SOCIOS";

$pdf->AddPage('Portrait', 'Legal');
$pdf->AliasNbPages();

// HEADER DE LA TABLA
$pdf->table_headers = array(
    'No.',
    'COD.',
    'Nombre',
    'E-mail',
    "Telefono",
    "Rubro"
);

if(count($dataFilter) > 0){
    $pdf->SetFont('Arial', '', 9);
    // ESTABLECE EL TAMAÑO DE CADA CELDA
    $pdf->tablewidths = array(10,25,40,50,30,40);

    $item = 0;

    for ($i=0; $i < count($dataFilter); $i++) { 
        $item = $item+1;
        $key = $dataFilter[$i];

        $id_partner = $key["id"];
        $partner_name = $key["nombre"];;
        $E_mail = $key["email"];
        $telefono = $key["telefono"];
        $partner_type = $key["tipoPartner"];

        // este array se rellena en orden de las columnas
        //ejemplo $item es el valor de la columna #1 y asi
        $row[] = array(
            utf8_decode($item."."),
            utf8_decode("COD-00".$id_partner),
            utf8_decode($partner_name),
            utf8_decode($E_mail),
            utf8_decode($telefono),
            utf8_decode($partner_type),
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
