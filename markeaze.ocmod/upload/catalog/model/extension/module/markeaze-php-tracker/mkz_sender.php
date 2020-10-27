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

  public function send($data) {
    $class_name = function_exists('curl_version') ? 'MkzCurlSender' : 'MkzNativeSender';
    $sender = new $class_name($this->url);
    return $sender->send($data);
  }

}

class MkzNativeSender {

  public function __construct($url) {
    $this->url = (string) $url;
  }

  public function send($data) {
    $post = http_build_query($data);
    $opts = stream_context_create(array(
      'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
        'content' => $post
      )
    ));
    return @file_get_contents($this->url, false, $opts);
  }

}

class MkzCurlSender {

  public function __construct($url) {
    $this->url = (string) $url;
  }

  public function send($data) {
    $method = 'POST';
    $timeout = 1;
    $connect_timeout = 1;

    $headers = array(
      'Accept: application/json, text/javascript, */*; q=0.01',
      'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
    );

    $curl = curl_init($this->url);

    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    if ($data) {
      if ($method == 'POST') curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

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
