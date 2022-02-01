<?php

// Include the main TCPDF library
require_once('./lib/TCPDF/tcpdf.php');

// Include e-flux TCPDF
require_once('./assets/e-flux-tcpdf.php');

// create new PDF document
$pdf = new E_FLUX_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('E-Flux');
$pdf->SetTitle('E-Flux PDF Generator Test');
$pdf->SetSubject('Subject Here');
$pdf->SetKeywords('Key, Words, Here');

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

// Set vertical spacing per tag
$tagvs = array(
  'p' => array(
    0 => array('h' => 0, 'n' => 0),
    1 => array('h' => 0, 'n' => .5)
  ),
  'blockquote' => array(
    0 => array('h' => 0, 'n' => 0),
    1 => array('h' => 0, 'n' => .5)
  )
);
$pdf->setHtmlVSpace( $tagvs );

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// print TEXT
$pdf->e_flux_set_essay_data( "Twenty-One Art Worlds: A Game Map", "Hito Steyerl, Department of Decentralization, and GPT-3", 567, "October 2021", "Journal Name Goes Here" );
$pdf->e_flux_print_essay( 'data/chapter_demo_1.txt', true );
$pdf->e_flux_set_essay_data( "Another Essay Title Could Go Here", "Hito Steyerl, Department of Decentralization, and GPT-3", 999, "March 2020", "Journal Name Goes Here" );
$pdf->e_flux_print_essay( 'data/chapter_demo_2.txt', true );

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output( 'article-name-here.pdf', 'I' );


?>
