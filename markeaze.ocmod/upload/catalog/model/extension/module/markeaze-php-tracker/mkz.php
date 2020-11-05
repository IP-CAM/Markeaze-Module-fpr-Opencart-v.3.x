<?php

/*
MIT License

Copyright (c) Markeaze Inc. https://markeaze.com

This file is part of the markeaze-php-tracker library created by Markeaze.

Repository: https://github.com/markeaze/markeaze-php-tracker
Documentation: https://github.com/markeaze/markeaze-php-tracker/blob/master/README.md
*/

class Mkz {
  public $version = '1.1.0';
  public $debug = false;
  public $post_data = null;
  private $tracker_name = 'markeaze-php';
  private $endpoint = null;
  private $app_key;
  private $region;
  private $uid;
  private $visitor = array();
  private $cookie_name = '_mkz_dvc_uid';
  private $use_cookie = true;

  public function __construct($app_key) {
    $this->set_app_key($app_key);
  }

  public function set_app_key($app_key) {
    $parts = explode('@', $app_key);
    if (count($parts) !== 2) return;

    $this->app_key = (string) $app_key;
    $this->region = end($parts);
    $this->endpoint = "tracker-{$this->region}.markeaze.com";
  }

  public function set_device_uid($uid) {
    if (!$uid) $this->uid = null;
    else $this->uid = (string) stripslashes($uid);
  }

  public function use_cookie_uid($boolean = true) {
    $this->use_cookie = (boolean) $boolean;
  }

  public function debug($value) {
    $this->debug = (bool) $value;
  }

  public function set_visitor_info($visitor) {
    foreach ($visitor as $key => $value) $this->visitor[$key] = $value;
    return $this->visitor;
  }

  public function track_visitor_update($event_name, $properties = []) {
    $this->send($event_name, [], $properties);
  }

  public function track($event_name, $properties = [], $visitor = []) {
    $method = "track_{$event_name}";
    if (method_exists($this, $method)) return $this->$method($event_name, $properties);
    else return $this->send($event_name, $properties);
  }

  private function send($event_name, $properties = [], $visitor = []) {
    $cookie_uid = $this->use_cookie && !empty($_COOKIE[$this->cookie_name]) ? stripslashes($_COOKIE[$this->cookie_name]) : null;
    $uid = $this->uid ? $this->uid : $cookie_uid;
    $merged_visitor = array_merge($this->visitor, $visitor);

    if (empty($uid) && empty($merged_visitor['client_id'])) throw new Exception('No user id specified');

    $data = array();
    $data['app_key'] = $this->app_key;
    $data['type'] = $event_name;
    $data['tracker_ver'] = $this->version;
    $data['tracker_name'] = $this->tracker_name;
    $data['performed_at'] = time();

    $data['visitor'] = $merged_visitor;
    if ($uid) $data['visitor']['device_uid'] = $uid;

    if (count($properties) > 0) $data['properties'] = $properties;

    $this->post_data = $data;

    include_once('mkz_sender.php');
    $sender = new MkzSender("https://{$this->endpoint}/event");
    $headers = array('Content-Type: application/x-www-form-urlencoded; charset=utf-8');
    $response = $sender->send(http_build_query(array('data' => json_encode($data))), 'POST', $headers);

    include_once('mkz_logger.php');
    $logger = new MkzLogger($this->debug);
    $logger->put(array('headers' => $headers, 'body' => $data), $response);

    return $response;
  }
}
