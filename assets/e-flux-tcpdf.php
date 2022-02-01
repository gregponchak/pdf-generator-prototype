<?php

/**
 * Extend TCPDF to work with e-flux style guidelines
 */
class E_FLUX_TCPDF extends TCPDF {

  /**
	 * This is the class constructor.
	 * It allows to set up the page format, the orientation and the measure unit used in all the methods (except for the font sizes).
	 *
	 * @param string $orientation page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li><li>'' (empty string) for automatic orientation</li></ul>
	 * @param string $unit User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
	 * @param mixed $format The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param boolean $unicode TRUE means that the input text is unicode (default = true)
	 * @param string $encoding Charset encoding (used only when converting back html entities); default is UTF-8.
	 * @param boolean $diskcache DEPRECATED FEATURE
	 * @param integer $pdfa If not false, set the document to PDF/A mode and the good version (1 or 3).
	 * @public
	 * @see getPageSizeFromFormat(), setPageFormat()
	 */
  public function __construct( $orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false ) {
    parent::__construct( $orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false );
    $this->EssayTitle = '';
    $this->EssayAuthor = '';
    $this->JournalNumber = '';
    $this->JournalDate = '';
    $this->JournalTitle = '';
    $this->Footnotes = array();
    $this->HTML = '';
    $this->font = TCPDF_FONTS::addTTFfont('./assets/fonts/atto.ttf', 'TrueTypeUnicode', '', 96);
  }

  // Overwrite Default Header
  public function Header() {

    // Set style
    $this->SetTextColor( 0 );
    $this->SetFont( 'helvetica', '', 8 );

    // Start Transformation
    $this->StartTransform();

    // Start rotation and output text
    $this->Rotate( 90, PDF_PAGE_WIDTH/2, PDF_PAGE_HEIGHT/2 );
    $this->Translate( PDF_PAGE_HEIGHT/-3, -3 );
    $this->WriteHTMLCell( PDF_PAGE_HEIGHT/2, 30, PDF_PAGE_WIDTH/2, PDF_PAGE_HEIGHT/2, $html='<strong>e-flux</strong> Journal ' . $this->JournalNumber . ' &mdash; ' . $this->JournalDate . ' <u>' . $this->EssayAuthor . '</u><br><strong>' . $this->EssayTitle . '</strong>' );

    // Stop Transformation
    $this->StopTransform();

  }

  // Overwrite Default Footer
  public function Footer() {

    // Set style
    $this->SetTextColor( 0 );
    $this->SetFont( 'helvetica', '', 8 );

    // Start Transformation
    $this->StartTransform();

    // Start rotation and output text
    $this->Rotate( 90, PDF_PAGE_WIDTH/2, PDF_PAGE_HEIGHT/2 );
    $this->Translate( PDF_PAGE_HEIGHT/3, 0 );
    $this->Text( PDF_PAGE_WIDTH/2, PDF_PAGE_HEIGHT/2, $this->getAliasNumPage() . '/' . $this->getAliasNbPages() );

    // Stop Transformation
    $this->StopTransform();

  }

	/**
	 * e_flux_print_essay
	 * @param int $num chapter number
	 * @param string $title chapter title
	 * @param string $file name of the file containing the chapter body
	 * @param boolean $mode if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function e_flux_print_essay( $file, $mode=false ) {

		// add a new page
		$this->AddPage();
		// disable existing columns
		$this->resetColumns();
		// set columns
		$this->setEqualColumns( 2, 82 );
    // print chapter title
		$this->e_flux_set_title( $this->EssayAuthor, $this->EssayTitle );
    // parse footnotes
    $this->e_flux_parse_footnotes( $file );
    // break for full screen items (images,idk)
    /*
    $chunks    = explode($delimiter, $html);
    $cnt       = count($chunks);

    for ($i = 0; $i < $cnt; $i++) {
      $pdf->writeHTML($delimiter . $chunks[$i], true, 0, true, 0);

      if ($i < $cnt - 1) {
          $pdf->AddPage();
      }
    }
    // https://stackoverflow.com/questions/1605860/manual-page-break-in-tcpdf
    // https://www.enovision.net/replace-text-brackets-preg_replace-php
    */
		// print chapter body
		$this->e_flux_set_body( $this->HTML, $mode );
    // print footnotes
    if ( $this->Footnotes != null ) {
      $this->e_flux_set_footnotes();
    }

	}

  /**
	 * Parse footnotes
   * @param string $source essay author
	 * @param string $title essay title
	 * @public
	 */
	public function e_flux_parse_footnotes( $source ) {

    $source = file_get_contents( $source, false );

    // Split content based on [footnote] shortcode
    $sections = preg_split( "/\[footnote\s(.*?)\]/i", $source, -1, PREG_SPLIT_DELIM_CAPTURE );

    // Loop through sections
    foreach( $sections as $i => $section ) {

      if ( $i % 2 == 0 ) { // If section is not footnote content
        $this->HTML .= $section;
      } else { // If section is footnote content
        $this->HTML .= "<sup>" . ( count( $this->Footnotes ) + 1 ) . "</sup>";
        $this->Footnotes[] = $section;
      }

    }

  }

	/**
	 * Set essay title
   * @param string $author essay author
	 * @param string $title essay title
	 * @public
	 */
	public function e_flux_set_title( $author, $title ) {

    // Select first column
    $this->selectColumn();

    // Set font style
    $this->SetTextColor( 50, 50, 50 );

    // Output Title
    $this->SetFont( 'helvetica', '', 18 );
    $this->writeHTML( $author, true, false, true, true, 'L' );
    $this->SetFont( 'helvetica', 'B', 30 );
    $this->writeHTML( $title, true, false, true, true, 'L' );

	}

	/**
	 * Print body copy
	 * @param string $content
	 * @param boolean $mode if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function e_flux_set_body( $content, $mode=false ) {

    // Select second column in order to make space for title
		$this->selectColumn( 1 );

		// get external file content
		// $content = file_get_contents( $file, false );

		// set font
		$this->SetFont( 'helvetica', '', 9 );
		$this->SetTextColor( 50, 50, 50 );

		// print content
		if ( $mode ) {
			// ------ HTML MODE ------
			$this->writeHTML( $content, true, false, true, false, 'L' );
		} else {
			// ------ TEXT MODE ------
			$this->Write( 0, $content, '', 0, 'J', true, 0, false, true, 0 );
		}
		$this->Ln();

	}

  /**
	 * Print footnotes
	 * @public
	 */
   public function e_flux_set_footnotes() {

     // add a new page
 		$this->AddPage();
 		// disable existing columns
 		$this->resetColumns();
 		// set columns
 		$this->setEqualColumns( 4, 40 );
    // set font
		$this->SetFont( $this->font, '', 7, '', false );

    // Organize footnotes
    $footnotes_content = "";
    foreach( $this->Footnotes as $i => $footnote ) {
      $footnotes_content .= "<p>         " . ( $i + 1 ) . ".<br>" . $footnote . "</p>";
    }

    $this->writeHTML( $footnotes_content, true, false, true, false, 'L' );

   }

  /**
	 * Set Essay Data
	 * @param string $essayTitle title of the essay
	 * @param string $essayAuthor author
   * @param string $journalNumber the volume number
   * @param string $journalDate date the journal was published
   * @param string $journalTitle the title of the journal
	 * @public
	 */
  public function e_flux_set_essay_data( $essayTitle, $essayAuthor, $journalNumber, $journalDate, $journalTitle ) {
    $this->EssayTitle = $essayTitle;
    $this->EssayAuthor = $essayAuthor;
    $this->JournalNumber = $journalNumber;
    $this->JournalDate = $journalDate;
    $this->JournalTitle = $journalTitle;
    $this->Footnotes = array();
    $this->HTML = '';
  }

} // end of extended class
