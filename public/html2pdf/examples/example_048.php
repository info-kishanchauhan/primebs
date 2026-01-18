<?php
require_once('../config/lang/eng.php');
require_once('../tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Mahendran P');
$pdf->SetTitle('Tayva');
$pdf->SetSubject('PDF Report');
$pdf->SetKeywords('Export to PDF');

// set default header data
$pdf->SetHeaderData("logo.jpg", PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.'SYARIKAT LOGAM ANTA SDN BHD', '#address goes here');

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


<table width="640" align="center">
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>	
	<tr>
		<td colspan="2" align="center"><strong style="font-size:32px;">TAX INVOICE</strong></td>
	</tr>
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><hr></td>
	</tr>
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td width="320">
			<table width="80%">
				<tr>
					<td width="65">Sold To,</td>
					<td> Address here.</td>
				</tr>
			</table>
		</td>
		<td width="320">
			<table width="80%" style="float:right;">
				<tr>
					<td width="65">Bill No.</td>
					<td>0152</td>
				</tr>
				<tr>
					<td width="65">Date :</td>
					<td>5/2/2015</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" style="border: 1px solid #333333; line-height:15px; text-align:center; font-size:25px;" border="1" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<td width="50">SL No.</td>
						<td width="340">PERTICULARS</td>
						<td width="80">QTY</td>
						<td width="80">RATE</td>
						<td width="90">AMOUNT</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>					
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2" style="border:none;"></td>
						<td colspan="2">TOTAL Excluding GST	</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="2" style="border:none;"></td>
						<td colspan="2">GST Payable @6%</td>
						<td></td>
					</tr>
					<tr>
						<td colspan="2" style="border:none;"></td>
						<td colspan="2">Total Amount Payable</td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</td>
	</tr>
	
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%">
				<tr>
					<td style="border-bottom:1px solid #666666; width:30%;"></td>
					<td style="width:20%;"></td>
					<td style="border-bottom:1px solid #666666; width:50%; float:right;"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2">
				<table width="100%">
					<tr>
						<td style="width:30%; text-align:center; line-height:15px;">Buyer Signature</td>
						<td style="width:20%;"></td>
						<td style="width:50%; text-align:center; float:right; line-height:15px;">Syarikat Logam Anta Sdn. Bhd.</td>
				</tr>
			</table>
		</td>
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