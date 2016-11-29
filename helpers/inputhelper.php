<?php

class XSSHelper {

  public function sanitise($text, $options) {

    if(in_array('html', $options)) {
      $text = $this->sanitise_html($text);
    }
      return $text;
  }

  function sanitise_html($text) {
    return htmlspecialchars($text);
  }

}

?>
