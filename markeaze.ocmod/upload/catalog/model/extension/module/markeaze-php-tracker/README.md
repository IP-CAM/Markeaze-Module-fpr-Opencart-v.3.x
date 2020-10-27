# Markeaze php-tracker

With this tracker you can collect user event data from the client-side tier of your websites and web apps.

## Example html code for the site

```php
include_once('mkz.php');
$app_key = 'xxxxxxxxxxxxxxxxxxxx';
$uid = '1';
$mkz = new Mkz($app_key);
$mkz->set_visitor_info(array(
  'client_id' => $uid
));
$mkz->track('cart_update', array(
  'items' => array(
    array(
      'variant_id' => 'bb1',
      'qnt' => '2',
      'price' => '50'
    )
  )
));

```

## Initializarion

```php
include_once('mkz.php');
$app_key = 'yyyyyyyyyyyyyyyyyyyy@us-1';
$mkz = new Mkz($app_key);

```

## Options

### Set tracker url

```php
$mkz->endpoint('tracker-stage2.markeaze.com');
```

### Set app key

```php
$mkz->set_app_key('yyyyyyyyyyyyyyyyyyyyyyy');
```

### Set device uid (It is inserted automatically from cookies)

```php
$mkz->set_device_uid('xxxxxyyyyy');
```

### Debug mode

```php
$mkz->debug(true);
```

### Set visitor info

```php
$mkz->set_visitor_info(array(
  'first_name' => 'Kong',
  'last_name' => 'Qiu'
));
```

## Events

### Cart update

```php
$mkz->track('cart_update', array(
  'items' => array(
    array(
      'variant_id' => 'bb1', // required
      'qnt' => 2.0, // required
      'price' => 100.0, // required
      'name' => 'Test product',
      'main_image_url' => 'http://example.net/product.jpg',
      'url' => 'http://example.net/product'
    )
  )
));
```

### Cart add item

```php
$mkz->track('cart_add_item', array(
  'item' => array(
    'variant_id' => 'bb1', // required
    'qnt' => 2.0, // required
    'price' => 100.0, // required
    'name' => 'Test product',
    'main_image_url' => 'http://example.net/product.jpg',
    'url' => 'http://example.net/product'
  )
));
```

### Cart remove item

```php
$mkz->track('cart_remove_item', array(
  'item' => array(
    'variant_id' => 'bb1', // required
    'qnt' => 1.0 // required
  )
));
```

### Order create

```php
$mkz->track('order_create', array(
  'order_uid' => '123', // required
  'external_id' => 1234,
  'total' => 200.0,
  'items' => array( // required
    array(
      'variant_id' => 'bb1', // required
      'qnt' => 2.0, // required
      'price' => 100.0, // required
      'name' => 'Test product',
      'main_image_url' => 'http://example.net/product.jpg',
      'url' => 'http://example.net/product'
    )
  ),
  'trigger_value' => 'some-trigger',
  'tracking_number' => '123',
  'fulfillment_status' => 'Delivered',
  'financial_status' => 'Paid',
  'payment_method' => 'Card',
  'shipping_method' => 'Mail'
));
```

### Order update

```php
$mkz->track('cart_update', array(
  'order_uid' => '123', // required
  'external_id' => 1234,
  'total' => 200.0,
  'items' => array(
    array(
      'variant_id' => 'bb1',
      'qnt' => 2.0,
      'price' => 100.0,
      'name' => 'Test product',
      'main_image_url' => 'http://example.net/product.jpg',
      'url' => 'http://example.net/product'
    )
  ),
  'trigger_value' => 'some-trigger',
  'tracking_number' => '123',
  'fulfillment_status' => 'Delivered',
  'financial_status' => 'Paid',
  'payment_method' => 'Card',
  'shipping_method' => 'Mail'
));
```

### Order create

```php
$mkz->track('order_cancel', array(
  'order_uid' => '123' // required
));
```

### Update visitor info

```php
$mkz->track('visitor_update', array(
  'client_id' => '123',
  'first_name' => 'Kong',
  'last_name' => 'Qiu'
));
```
