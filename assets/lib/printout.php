<?php
 include 'fpdf.php';
 include 'exfpdf.php';
 include 'easyTable.php';

$pdf=new exFPDF();
 $pdf->AddPage(); 
 $pdf->SetFont('helvetica','',10);

 $pdf->AddFont('lato','','Lato-Regular.php');

 $pdf->Write(6, 'Some writing...');

 $pdf->Ln(5);

 $pdf->Write(6, 'Integer eget risus non dui scelerisque consectetur. Integer eleifend in nibh in mattis. Aenean eu justo quis mauris tempus eleifend. Praesent malesuada turpis ut justo semper tempor. Integer varius, nisi non elementum molestie, leo arcu euismod velit, eu tempor ligula diam convallis sem. Sed ultrices hendrerit suscipit. Pellentesque volutpat a urna nec placerat. Etiam auctor dapibus leo nec ullamcorper. Nullam id placerat elit. Vivamus ut quam a metus tincidunt laoreet sit amet a ligula. Sed rutrum felis ipsum, sit amet finibus magna tincidunt id. Suspendisse vel urna interdum lacus luctus ornare. Curabitur ultricies nunc est, eget rhoncus orci vestibulum eleifend. In in consequat mi. Curabitur sodales magna at consequat molestie. Aliquam vulputate, neque varius maximus imperdiet, nisi orci accumsan risus, sit amet placerat augue ipsum eget elit. Quisque sodales orci non est tincidunt tincidunt. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In ut diam in dolor ultricies accumsan sit amet eu ex. Pellentesque aliquet scelerisque ullamcorper. Aenean porta enim eget nisl viverra euismod sed non eros. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque at imperdiet sem, non volutpat metus. Phasellus sed velit sed orci iaculis venenatis ac id risus.');
 
 $pdf->Ln(10);

 $pdf->AddFont('FontUTF8','','Arimo-Regular.php'); 
 $pdf->AddFont('FontUTF8','B','Arimo-Bold.php'); 
 $pdf->AddFont('FontUTF8','BI','Arimo-BoldItalic.php'); 
 $pdf->AddFont('FontUTF8','I','Arimo-Italic.php');

 for ($i = 1; $i <= 10; $i++) {
   $table=new easyTable($pdf, 3, 'width:100%; border: 1;');
   $table->easyCell('Text 1', 'rowspan:2; valign:T'); 
   $table->easyCell('Text 2', 'bgcolor:#b3ccff; rowspan:2');
   $table->easyCell('Text 3');
   $table->printRow();
 
   $table->rowStyle('min-height:20');
   $table->easyCell('Text 4', 'bgcolor:#3377ff; rowspan:2');
   $table->printRow();

   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
   $table->easyCell('Text 5', 'bgcolor:#99bbff;colspan:2');
   $table->printRow();
 
   $table->endTable();
}
 
//-----------------------------------------

 $pdf->Output(); 
?>