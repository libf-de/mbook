<?php
include 'assets/lib/fpdf.php';
include 'assets/lib/exfpdf.php';
include 'assets/lib/easyTable.php';

/*
* 10 heading
* + 8,4 per line
* + 10,4 last line
*/

function printTable($pdf, $lines, $title) {
   $pdf->SetFont('helvetica','',16);
   $rowCounter = $lines;
   
   
   if($lines >= 8) {
      //Two column mode
      $tblFmt = '{13, 81.8, 0.3, 13, 81.8}';
      $colPerLine = 2;
   } else {
      //Single column mode
      $tblFmt =  '{13, 177}';
      $colPerLine = 1;
   }

   $rowCounter /= $colPerLine;
   $tblHeight = (20.4 + (8.4*($rowCounter-1)));

   if($pdf->getY() + $tblHeight > 272) {
      //$pdf->Ln(272 - $pdf->getY());
      $pdf->AddPage();
   }

   $pdf->SetFont('helvetica','',20);
   $pdf->Write(10, $title);
   $pdf->Ln();

   $table=new easyTable($pdf, $tblFmt, 'width:100%; border: 1; split-row:false;');

   for ($i = 1; $i <= $lines; $i+=$colPerLine) {
      //  $table->easyCell('Text 1', 'rowspan:2; valign:T'); 
      //  $table->easyCell('Text 2', 'bgcolor:#b3ccff; rowspan:2');
      $table->easyCell($i . ".");
      //$table->easyCell($pdf->getY());
      $table->easyCell("");
      $table->easyCell("", "bgcolor: #000000;");
      $table->easyCell($i+1 . ".", "min-width: 700;");
      //$table->easyCell($pdf->getY());
      $table->easyCell("");
      $table->printRow();
   }

   $table->endTable();
}

/*$pdf=new exFPDF('P','mm','A4');
$pdf->AddFont('lato','','Lato-Regular.php');
$pdf->AddPage();
$pdf->SetFont('lato','',26);



$pdf->Write(10, 'Herbstferien 2022');

$pdf->SetFont('lato','',16);

$pdf->Ln();

printTable($pdf, 40, "Wanderritt");
printTable($pdf, 10, "Wanderritt");
printTable($pdf, 10, "Wanderritt");
printTable($pdf, 10, "Wanderritt");
printTable($pdf, 10, "Wanderritt");
printTable($pdf, 10, "Wanderritt");
printTable($pdf, 10, "Wanderritt");
printTable($pdf, 10, "Wanderritt");
printTable($pdf, 10, "Wanderritt");

//-----------------------------------------

$pdf->Output();*/
?>