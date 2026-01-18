<?php
require_once('config/lang/eng.php');
require_once('tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Mahendran P');
$pdf->SetTitle('Tayva');
$pdf->SetSubject('PDF Report');
$pdf->SetKeywords('Export to PDF');

// set default header data
$pdf->SetHeaderData("logo.jpg", PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.'PARAMEDIA GROUP', '#34,CA,USA-44');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', 'B', 20);

// add a page
$pdf->AddPage();

//$pdf->Write(0, 'Client Details', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 8);

// -----------------------------------------------------------------------------


// NON-BREAKING ROWS (nobr='true')

$tbl = <<<EOD


<h2 align="center"><u>Client Details</u></h2>
<table style="border: 1px solid #FFFFFF;">
  <tr style="background-color:#8DBDD8;font-weight:300;">
    <th height="20">Title</th>
    <th height="20">Title</th>
    <th height="20">Title</th>
    <th height="20">Title</th>
  </tr>
  
  <tr style="background-color: #F0F0F6;">
    <td >Data</td>
    <td >Data</td>
    <td>Data</td>
    <td>Data</td>
  </tr>
  <tr>
    <td >Data</td>
    <td >Data</td>
    <td>Data</td>
    <td>Data</td>
  </tr>
  <tr style="background-color: #F0F0F6;">
    <td >Data</td>
    <td >Data</td>
    <td>Data</td>
    <td>Data</td>
  </tr>
  <tr>
    <td >Data</td>
    <td >Data</td>
    <td>Data</td>
    <td>Data</td>
  </tr>

</table>


EOD;



$pdf->writeHTML($tbl, true, false, false, false, '');

// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_048.pdf', 'I');

//============================================================+
// END OF FILE                                                
//============================================================+ 