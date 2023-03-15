<?php
global $plugin_root;
require_once($plugin_root . 'assets/lib/fpdf.php');
require_once($plugin_root . 'assets/lib/exfpdf.php');
require_once($plugin_root . 'assets/lib/easyTable.php');

/*
* 10 heading
* + 8,4 per line
* + 10,4 last line
*/

function printTable($pdf, $lines, $title, $notes = '') {
   $pdf->SetFont('helvetica','',16);
   $rowCounter = $lines;

   $wrmode = str_contains(strtolower($title), 'wanderritt'); //TODO: Store properly in database
   
   if($wrmode) {
      $tblFmt = '{13, 40.9, 40.9, 0.3, 13, 40.9, 40.9}';
      $colPerLine = 2;
   } elseif($lines >= 8) {
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
   $pdf->writeHTML(10, "<b>" . iconv('UTF-8', 'windows-1252', $title) . "</b>");
   $pdf->SetFont('helvetica','',11);
   $pdf->Write(10, " [" . iconv('UTF-8', 'windows-1252', $notes) . "]");
   $pdf->SetFont('helvetica','',20);
   $pdf->Ln();

   $table=new easyTable($pdf, $tblFmt, 'width:100%; border: 1; split-row:false;');

   for ($i = 1; $i <= $lines; $i+=$colPerLine) {
      if($wrmode) {
         $table->easyCell($i . ".");
         $table->easyCell("");
         $table->easyCell("");
         $table->easyCell("", "bgcolor: #000000;");
         $table->easyCell($i+1 . ".", "min-width: 700;");
         $table->easyCell("");
         $table->easyCell("");
         $table->printRow();
      } else {
         $table->easyCell($i . ".");
         $table->easyCell("");
         $table->easyCell("", "bgcolor: #000000;");
         $table->easyCell($i+1 . ".", "min-width: 700;");
         $table->easyCell("");
         $table->printRow();
      }
   }

   $table->endTable();
}
?>