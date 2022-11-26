<?php

require('../fpdf/fpdf.php');

$pdf = new FPDF();

$pdf->AddPage('Portrait', 'Legal');
$pdf->AliasNbPages();

// HEADER DE LA TABLA
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 9, 'HOLA MUNDO', 1, 0, 'C');

$pdf->Output();