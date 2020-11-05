<?php

/*
MIT License

Copyright (c) Markeaze Inc. https://markeaze.com

This file is part of the markeaze-php-tracker library created by Markeaze.

Repository: https://github.com/markeaze/markeaze-php-tracker
Documentation: https://github.com/markeaze/markeaze-php-tracker/blob/master/README.md
*/

class MkzSender {
  public function __construct($url) {
    $this->url = (string) $url;
  }

  public function send($data, $method = 'POST', $headers = []) {
    $class_name = function_exists('curl_version') ? 'MkzCurlSender' : 'MkzNativeSender';
    array_push($headers, 'Accept: application/json, text/javascript, */*; q=0.01');
    $sender = new $class_name($this->url);
    return $sender->send($data, strtoupper($method), $headers);
  }

}

class MkzNativeSender {

  public function __construct($url) {
    $this->url = (string) $url;
  }

  public function send($data, $method = 'POST', $headers = []) {
    $opts = stream_context_create(array(
      'http' => array(
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'content' => $data
      )
    ));
    return @file_get_contents($this->url, false, $opts);
  }

}

class MkzCurlSender {

  public function __construct($url) {
    $this->url = (string) $url;
  }

  public function send($data, $method = 'POST', $headers = []) {
    $timeout = 1;
    $connect_timeout = 1;
    $curl = curl_init($this->url);

    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    if ($data) {
      if ($method == 'POST') curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    } else {
      curl_setopt($curl, CURLOPT_POST, false);
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    curl_exec($curl);

    $response = curl_error($curl);

    curl_close($curl);

    return $response;
  }

}
