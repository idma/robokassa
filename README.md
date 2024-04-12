## PHP library for Robokassa payment system

[![Latest Unstable Version](https://poser.pugx.org/idma/robokassa/v/unstable.svg)](https://packagist.org/packages/idma/robokassa)
[![Latest Stable Version](https://poser.pugx.org/idma/robokassa/v/stable.svg)](https://packagist.org/packages/idma/robokassa)
[![Total Downloads](https://poser.pugx.org/idma/robokassa/downloads.svg)](https://packagist.org/packages/idma/robokassa)
[![License](https://poser.pugx.org/idma/robokassa/license.svg)](https://packagist.org/packages/idma/robokassa)

## Installation

Install this package through Composer. To your `composer.json` file, add:
```js
{
    "require": {
        "idma/robokassa": "dev-master"
    }
}
```

## Examples

Create payment:
```php
$payment = new \Idma\Robokassa\Payment(
    'john_doe', 'password1', 'password2', true
);

$payment
    ->setInvoiceId($order->id)
    ->setSum($order->amount)
    ->setDescription('Payment for some goods');

// redirect to payment url
$user->redirect($payment->getPaymentUrl());
```
For pointining nomenclatures data:
```php
// for details - https://docs.robokassa.ru/fiscalization/
$receiptData = array(
    'items' => array([
        'sum' => $sum,
        'name' => 'name of order',
        'quantity' => 1,
        'tax' => 'none',
    ])
);
$payment
    ->setInvoiceId($order->id)
    ->setSum($order->amount)
    ->setDescription('Payment for some goods')
    ->addReceiptData($receiptData);

...

Check payment result:
```php
// somewere in result url handler...
...
$payment = new \Idma\Robokassa\Payment(
    'john_doe', 'password1', 'password2', true
);

if ($payment->validateResult($_GET) {
    $order = Orders::find($payment->getInvoiceId());

    if ($payment->getSum() == $order->sum) {

    }

    // send answer
    echo $payment->getSuccessAnswer(); // "OK1254487\n"
}
...
```

Check payment on Success page:
```php
...
$payment = new \Idma\Robokassa\Payment(
    'john_doe', 'password1', 'password2', true
);

if ($payment->validateSuccess($_GET) {
    $order = Orders::find($payment->getInvoiceId());

    if ($payment->getSum() == $order->sum) {
        // payment is valid
    }

}
...
```
