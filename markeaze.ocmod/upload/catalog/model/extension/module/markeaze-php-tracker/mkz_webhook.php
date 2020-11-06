<?php

/*
MIT License

Copyright (c) Markeaze Inc. https://markeaze.com

This file is part of the markeaze-php-tracker library created by Markeaze.

Repository: https://github.com/markeaze/markeaze-php-tracker
Documentation: https://github.com/markeaze/markeaze-php-tracker/blob/master/README.md
*/

class MkzWebhook {
  public $debug = false;
  private $endpoint = null;
  private $app_key = null;
  private $app_secret = null;
  private $cms = null;

  public function __construct($app_key, $app_secret, $cms) {
    $this->app_key = (string) $app_key;
    $this->app_secret = (string) $app_secret;
    $this->cms = (string) $cms;
    $this->endpoint = strrpos($app_key, '@stage') === false ? 'apps.markeaze.com' : 'apps-stage.markeaze.com';
  }

  public function send($topic, $payload) {
    include_once('mkz_sender.php');
    $url = "https://{$this->endpoint}/i/cms/{$this->cms}/webhook";
    $sender = new MkzSender($url);
    $data = array(
      'app_key' => (string) $this->app_key,
      'topic' => (string) $topic,
      'payload' => $payload
    );
    $json = json_encode($data);
    $signature = $this->get_webhook_signature($json);
    $headers = array(
      "x-markeaze-webhook-signature: {$signature}",
      'Content-Type: application/json'
    );
    $response = $sender->send($json, 'POST', $headers);

    include_once('mkz_logger.php');
    $logger = new MkzLogger($this->debug);
    $logger->put(array('headers' => $headers, 'body' => $data), $response, $url);
  }

  private function get_webhook_signature($data) {
    return base64_encode(hash_hmac('sha256', $data, $this->app_secret, true));
  }

}
