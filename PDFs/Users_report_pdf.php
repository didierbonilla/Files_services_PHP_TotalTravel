<?php

require('../fpdf/fpdf.php');
require('../API.class.php');
require('../Functions.class.php');

$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IkFuZ2VsIGpob2phbiBNYWNlYSIsImh0dHA6Ly9zY2hlbWFzLnhtbHNvYXAub3JnL3dzLzIwMDUvMDUvaWRlbnRpdHkvY2xhaW1zL2VtYWlsYWRkcmVzcyI6ImFuZ2Vsam1hY2VhQGdtYWlsLmNvbSIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkFkbWluaXN0cmFkb3IiLCJVc3VhcmlvSUQiOiIyIiwiUm9sSUQiOiIxIiwiZXhwIjoxNjcxOTEyNTcwLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo0NDM3NS9BcGkiLCJhdWQiOiJodHRwczovL2xvY2FsaG9zdDo0NDM3NS9BcGkifQ.Ae1R_-wEewkWZ9IGbjKOkCkO1ghDAXsMYk6vaEDc5Ew";
$_API = new API($token);
$_Functions = new Functions();

$hotelsList = json_decode($_API->HTTPRequest("/API/Hotels/List", "GET", null),true);

$filterCount = count($_GET);  
$filterKeys = array_keys($_GET);// obtiene los nombres de las varibles  
$filterValues = array_values($_GET);// obtiene los valores de las varibles 

$data = $hotelsList["data"];

$dataFilter = $_Functions->getDataFilter($data,$filterCount,$filterKeys,$filterValues);

//echo var_dump($dataFilter);
class PDF extends FPDF
{
    var $tablewidths;
    var $footerset;

    function morepagestable($datas, $lineheight = 13)
    {
        // Algunas cosas para establecer y ' recuerdan '
        $l = $this->lMargin;
        $startheight = $h = $this->GetY();
        $startpage = $currpage = $maxpage = $this->page;

        // Calcular todo el ancho
        $fullwidth = 0;
        foreach ($this->tablewidths as $width) {
            $fullwidth += $width;
        }

        // Ahora vamos a empezar a escribir la tabla
        foreach ($datas as $row => $data) {
            $this->page = $currpage;

            // Escribir los bordes horizontales
            $this->Line($l, $h, $fullwidth + $l, $h);

            // Escribir el contenido y recordar la altura de la más alta columna
            foreach ($data as $col => $txt) {
                $this->page = $currpage;
                $this->SetXY($l, $h);
                $this->MultiCell($this->tablewidths[$col], $lineheight, $txt,0,"C");
                $l += $this->tablewidths[$col];

                if (!isset($tmpheight[$row . '-' . $this->page]))
                    $tmpheight[$row . '-' . $this->page] = 0;
                if ($tmpheight[$row . '-' . $this->page] < $this->GetY()) {
                    $tmpheight[$row . '-' . $this->page] = $this->GetY();
                }
                if ($this->page > $maxpage)
                    $maxpage = $this->page;
            }

            // Obtener la altura estábamos en la última página utilizada
            $h = $tmpheight[$row . '-' . $maxpage];

            //Establecer el "puntero " al margen izquierdo
            $l = $this->lMargin;

            // Establecer el "$currpage en la ultima pagina
            $currpage = $maxpage;
        }

        // Dibujar las fronteras
        // Empezamos a añadir una línea horizontal en la última página
        $this->page = $maxpage;
        $this->Line($l, $h, $fullwidth + $l, $h);
        // Ahora empezamos en la parte superior del documento
        for ($i = $startpage; $i <= $maxpage; $i++) {
            $this->page = $i;
            $l = $this->lMargin;
            $t = ($i == $startpage) ? $startheight : $this->tMargin;
            $lh = ($i == $maxpage) ? $h : $this->h - $this->bMargin;
            $this->Line($l, $t, $l, $lh);
            foreach ($this->tablewidths as $width) {
                $l += $width;
                $this->Line($l, $t, $l, $lh);
            }
        }
        // Establecerlo en la última página , si no que va a causar algunos problemas
        $this->page = $maxpage;
    }
}

$pdf = new PDF();

$pdf->AddPage('Portrait', 'Legal');
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(205, 30, utf8_decode('REPORTE DE HOTELES'), 0, 0, 'C');
$pdf->Ln(30);

$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(100, 9, count($dataFilter).' registros filtrados de '.count($hotelsList["data"])." registros en total", 0, 0, 'L');
$pdf->Ln(5);
$pdf->Cell(100, 9, $filterCount." filtros aplicados", 0, 0, 'L');
$pdf->Ln(9);

// HEADER DE LA TABLA
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(8, 9, 'No.', 1, 0, 'C');
$pdf->Cell(20, 9, 'COD.', 1, 0, 'C');
$pdf->Cell(35, 9, 'Nombre de hotel', 1, 0, 'C');
$pdf->Cell(40, 9, 'Partner', 1, 0, 'C');
$pdf->Cell(70, 9, 'Direccion', 1, 0, 'C');
$pdf->Ln(9);


if(count($dataFilter) > 0){
    $pdf->SetFont('Arial', '', 9);
    // ESTABLECE EL TAMAÑO DE CADA CELDA
    $pdf->tablewidths = array(8,20,35,40,70);

    $item = 0;

    for ($i=0; $i < count($dataFilter); $i++) { 
        $item = $item+1;
        $key = $dataFilter[$i];

        $id_hotel = $key["id"];
        $hotel = $key["hotel"];;
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
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(254,38,25);
    $pdf->tablewidths = array(173);
    $row[] = array(
        utf8_decode("NO SE ENCONTRARON DATOS COINCIDENTES CON LA BUSQUEDA")
    );
    $pdf->morepagestable($row,10);
}

$pdf->Output();
