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
$reservation_id = isset($_GET["id"]) ? $_GET["id"] : 0;
$reservationList = json_decode($_API->HTTPRequest("/API/Reservation/Details?Id=$reservation_id", "GET", null),true);
$data = $reservationList["data"];

// GET DEFAULT DATA
$dataFilter =  $data;
$user_name = "NO USER NAME";

$pdf = new PDF();
$pdf->user_name = $user_name;
$pdf->report_tittle = "REPORTE A DETALLE DE RESERVACION - COD-00$reservation_id";

$pdf->AddPage('Portrait', 'Legal');
$pdf->AliasNbPages();
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(20,10,"CODIGO",1,0,"C");
$pdf->Cell(25,10,"COD-000",1,0,"C");

$pdf->Cell(20,10,"CLIENTE",1,0,"C");
$pdf->Cell(80,10,"Didier Isaac Bonilla Chavez",1,0,"C");

$pdf->Cell(10,10,"DNI",1,0,"C");
$pdf->Cell(40,10,"0501200313138",1,0,"C");
$pdf->Ln(15);


// HOTEL INFO TABLE
$pdf->Cell(0,8,"INFORMACION DE HOTEL",1,0,"C");
$pdf->Ln(8);

$pdf->tablewidths = array(35,40,40,81);
$row[] = array(
    utf8_decode("NOMBRE DE HOTEL-B-9"),
    utf8_decode("Hotel Hyatte"),
    utf8_decode("DIRECCION DE HOTEL-B-9"),
    utf8_decode("Ciudad san pedro sula, colonia san jose de sula, calle 7, avenida 22"),
);
$pdf->morepagestable($row,6);
$pdf->Ln(10);

$pdf->Output();

/*
// CONSTRUCT PDF DOCUMENT
if($dataFilter["reservacionDetalle"] != null){

    $pdf = new PDF();
    $pdf->user_name = $user_name;
    $pdf->report_tittle = "REPORTE DE SOCIOS";

    $pdf->AddPage('Portrait', 'Legal');
    $pdf->AliasNbPages();

    $pdf->Cell(20,10,"codigo",0,0,"C");
    $pdf->Output();

}
else{
   
    

}*/
