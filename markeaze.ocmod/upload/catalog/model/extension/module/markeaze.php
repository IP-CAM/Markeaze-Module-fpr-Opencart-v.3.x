<?php

class ModelExtensionModuleMarkeaze extends Model {

  public function updateCart() {
    if (!($mkz = $this->getTracker())) return;

    $this->load->model('tool/image');

    $items = array();
    if ($this->cart->hasProducts()) {
      $products = $this->cart->getProducts();
      foreach ($products as $product) {
        $url = $this->url->link('product/product', 'product_id=' . $product['product_id']);
        $item = array(
          'variant_id' => (string) $product['product_id'],
          'name' => (string) strip_tags($product['name']),
          'price' => (float) $product['price'],
          'qnt' => (float) $product['quantity'],
          'url' => (string) $url
        );

        if ($product['image']) $item['main_image_url'] = (string) $this->getImageUrl($product['image']);;

        $items[] = $item;
      }
    }
    $mkz->track('cart_update', array('items' => $items));
  }

  public function productViewed($product_id) {
    if ($product_id == 0) return

    $this->load->model('tool/image');
    $this->load->model('catalog/product');

    $product = $this->model_catalog_product->getProduct($product_id);
    $url = $this->url->link('product/product', 'product_id=' . $product_id);
    $item = array(
      'variant_id' => (string) $product['product_id'],
      'name' => (string) strip_tags($product['name']),
      'price' => (float) $product['price'],
      'qnt' => (float) $product['quantity'],
      'url' => (string) $url
    );

    if ($product['image']) $item['main_image_url'] = (string) $this->getImageUrl($product['image']);

    $this->session->mkz_view_product = $item;
  }

  public function orderState($order_id) {
    if (!($mkz_webhook = $this->getWebhook())) return;

    $this->load->model('checkout/order');
    $this->load->model('account/order');
    $this->load->model('catalog/product');

    $order = $this->model_checkout_order->getOrder($order_id);
    $products = $this->model_account_order->getOrderProducts($order_id);
    $data = $this->getOrderData($order, $products);
    $data['customer'] = $this->getCustomerInfo($order);

    // Fix multiple order updates
    if (isset($this->session->data['cnv_last_order_update'])) {
      if ($this->session->data['cnv_last_order_update'] === $data) return;
    }
    $this->session->data['cnv_last_order_update'] = $data;

    $mkz_webhook->send('order/update', $data);
  }

  public function orderAdd($order_id) {
    if (!($mkz = $this->getTracker())) return;

    if ($order_id == 0) return false;

    $this->load->model('checkout/order');
    $this->load->model('account/order');
    $this->load->model('catalog/product');

    // Fix for Moneymaker2 - buy one click
    if (is_array($order_id) and !empty($order_id[0])) {
      $order = $order_id[0];
      if (!isset($order['products'])) return true;
      $products = $order['products'];
      $order_id = uniqid();
      $order['order_status_id'] = 0;
    } else {
      $order = $this->model_checkout_order->getOrder($order_id);
      $products = $this->model_account_order->getOrderProducts($order_id);
    }

    $visitor = $this->getCustomerInfo($order);

    if ($order['order_status_id'] == 0 or $order['order_status_id'] == $this->config->get('config_order_status_id')) {
      if (isset($this->session->data['cnv_last_order_id']) and $this->session->data['cnv_last_order_id'] == $order_id) return false;
      else $this->session->data['cnv_last_order_id'] = $order_id;
      $mkz->set_visitor_info($visitor);
      $mkz->track('order_create', $this->getOrderData($order, $products));
    } else if ($order['order_status_id'] > 0) {
      if ($order['order_status_id'] === 'cancelled') {
        $this->orderDelete($order_id);
      } else {
        if (!($mkz_webhook = $this->getWebhook())) return;

        $data = $this->getOrderData($order, $products);
        $data['customer'] = $visitor;
        $mkz_webhook->send('order/update', $data);
      }
    }
  }

  public function orderDelete($order_id) {
    if (!($mkz_webhook = $this->getWebhook())) return;

    $mkz_webhook->send('order/update', array(
      'order_uid' => $order_id,
      'fulfillment_status' => 'Cancelled'
    ));
  }

  private function getCustomerInfo($order) {
    if ($order['customer_id']) $this->visitor_info['client_id'] = (string) $order['customer_id'];
    if (trim($order['firstname'])) $this->visitor_info['first_name'] = trim($order['firstname']);
    if (trim($order['lastname'])) $this->visitor_info['last_name'] = trim($order['lastname']);
    if (trim($order['email'])) $this->visitor_info['email'] = trim($order['email']);
    if (trim($order['telephone'])) $this->visitor_info['phone'] = trim($order['telephone']);

    return $this->visitor_info;
  }

  private function getOrderData($order, $products) {
    $items = array();
    foreach($products as $product) {
      $url = $this->url->link('product/product', 'product_id=' . $product['product_id']);
      $item = array(
        'variant_id' => (string) $product['product_id'],
        'name' => (string) strip_tags($product['name']),
        'qnt' => (float) $product['quantity'],
        'price' => (float) $product['price'],
        'url' => (string) $url
      );

      $product_images = $this->model_catalog_product->getProductImages($product['product_id']);
      if (count($product_images) > 0) $item['main_image_url'] = (string) $this->getImageUrl($product_images[0]['image']);

      $items[] = $item;
    }
    return array(
      'order_uid' => (string) $order['order_id'],
      'total' => (float) $order['total'],
      'items' => $items,
      'fulfillment_status' => (string) $order['order_status'],
      // 'financial_status' => (string) 'Paid',
      'payment_method' => (string) $order['payment_method'],
      'shipping_method' => (string) $order['shipping_method']
    );
  }

  public function getAppKey() {
    $this->load->model('setting/setting');
    $app_key = $this->config->get('markeaze_app_key');
    if ($app_key) return $app_key;
  }

  public function getSecretKey() {
    $this->load->model('setting/setting');
    $secret_key = $this->config->get('markeaze_secret_key');
    if ($secret_key) return $secret_key;
  }

  private function getTracker() {
    $this->load->model('account/customer');
    $app_key = $this->getAppKey();
    if (!$app_key) return false;

    include_once('markeaze-php-tracker/mkz.php');
    $mkz = new Mkz($app_key);
    return $mkz;
  }

  private function getWebhook() {
    $this->load->model('account/customer');
    $app_key = $this->getAppKey();
    $secret_key = $this->getSecretKey();
    if (!$app_key || !$secret_key) return false;

    include_once('markeaze-php-tracker/mkz_webhook.php');
    $mkz_webhook = new MkzWebhook($app_key, $secret_key, 'opencart_v3');
    return $mkz_webhook;
  }

  protected function getImageUrl($path) {
    $prefix = 'image/';
    if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
      return $this->config->get('config_ssl') . $prefix . $path;
    } else {
      return $this->config->get('config_url') . $prefix . $path;
    }
  }

  public $visitor_info = array();

  public function getVisitorInfo() {
    if ($this->customer->isLogged()) {
      $id = $this->customer->getId();
      $customer_info = $this->model_account_customer->getCustomer($id);
      $this->visitor_info['client_id'] = (string) $id;
      if (!empty($customer_info['firstname'])) $this->visitor_info['first_name'] = $customer_info['firstname'];
      if (!empty($customer_info['lastname'])) $this->visitor_info['last_name'] = $customer_info['lastname'];
      if (!empty($customer_info['email'])) $this->visitor_info['email'] = $customer_info['email'];
      if (!empty($customer_info['telephone'])) $this->visitor_info['phone'] = $customer_info['telephone'];
    }
    return $this->visitor_info;
  }
}
