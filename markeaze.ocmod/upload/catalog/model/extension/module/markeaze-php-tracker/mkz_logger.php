<?php

/*
MIT License

Copyright (c) Markeaze Inc. https://markeaze.com

This file is part of the markeaze-php-tracker library created by Markeaze.

Repository: https://github.com/markeaze/markeaze-php-tracker
Documentation: https://github.com/markeaze/markeaze-php-tracker/blob/master/README.md
*/

class MkzLogger {
  public $debug = false;

  public function __construct($debug) {
    $this->debug = $debug;
  }

  public function put($request, $response, $url) {
    if (!$this->debug) return;

    ob_start();
    echo "REQUEST:\n";
    echo "{$url}\n";
    print_r($request);
    echo "RESPONSE:\n";
    print_r($response);
    $str_post = ob_get_clean();
    $date = date('Y.m.d H:i:s');
    $row = "{$date}\n{$str_post}\n\n";
    $filename = dirname(__FILE__) . '/debug.log';
    file_put_contents($filename, $row, FILE_APPEND);
  }

}
