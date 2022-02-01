<?php

  // Get content
  $content = file_get_contents( './data/chapter_demo_1.txt', false );

  // Split content based on [footnote] shortcode
  $sections = preg_split( "/\[footnote\s(.*?)\]/i", $content, -1, PREG_SPLIT_DELIM_CAPTURE );

  // Setup $html and $footnotes
  $html = "";
  $footnotes = array();

  // Loop through sections
  foreach( $sections as $i => $section ) {

    if ( $i % 2 == 0 ) { // If section is not footnote content
      $html .= $section;
    } else { // If section is footnote content
      $html .= "<sup>" . ( count( $footnotes ) + 1 ) . "</sup>";
      $footnotes[] = $section;
    }

  }

  // Print content
  echo $html;

  // Print footnotes
  foreach( $footnotes as $i => $footnote ) {
    echo "<p>" . ( $i + 1 ) . ". " . $footnote . "</p>";
  }

?>
