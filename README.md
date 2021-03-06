# Omnipay: Iyzico

**Iyzico driver for the Omnipay payment processing library**

<p align="center">
	<a href="https://travis-ci.org/kaankilic/omnipay-iyzico">
		<img src="https://travis-ci.org/kaankilic/omnipay-iyzico.svg" alt="Build Status">
	</a>	
	<a href="https://packagist.org/packages/kaankilic/omnipay-iyzico">
		<img src="https://poser.pugx.org/kaankilic/omnipay-iyzico/d/total.svg" alt="Total Downloads">
	</a>		
	<a href="https://packagist.org/packages/kaankilic/omnipay-iyzico">
		<img src="https://poser.pugx.org/kaankilic/omnipay-iyzico/v/stable.svg" alt="Latest Stable Version">
	</a>		 
	<a href="https://packagist.org/packages/kaankilic/omnipay-iyzico">
		<img src="https://poser.pugx.org/kaankilic/omnipay-iyzico/license.svg" alt="License">
	</a>
</p>

## Introduction
`omnipay-iyzico` provides an expressive and fluent way to use iyzico with omnipay framework agnostic, multi-gateway payment processing. 

## License

Laravel `omnipay-iyzico` is open-sourced software licensed under the [MIT](http://opensource.org/licenses/MIT)

### Installation
To get started with `omnipay-iyzico`, use Composer to add the package to your project's dependencies:

```php
composer require kaankilic/omnipay-iyzico
```
For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay) repository.

### Basic Usage
```php
	$gateway = Omnipay::create('Iyzico');
	$formInputData = array(
	    'name' => 'Bobby',
	    'lastname' => 'Tables',
	    'number' => '4131111111111117',
	    'expiryMonth' => '08',
	    'expiryYear' => '2023',
	    'cvv' => '001',
	    'billingCity'	=> 'Amsterdam',
	    'billingAddress1'	=> 'test address',
		'billingCountry' => 'Nederlands',
		'email'	=> 'bl4cksta@gmail.com',
		'postCode'	=> 'NL11100'
	);
	$card = new CreditCard($formInputData);
	$request = $gateway->authorize(['identityNumber'=>'IDENTITY_NUMBER','callbackUrl' => 'https://pos.app/']);
	$request->setCard($card);
	$request->setApiKey('sandbox-API-KEY');
	$request->setSecretKey('sandbox-SECRET-KEY');
	$basket = new \Omnipay\Common\ItemBag();
	$item = new \Omnipay\Common\Item(['name' => 'item name 1', 'price'=> '40.00']);
	$basket->add($item);

	$request->setItems($basket);
	$response = $request->send();
	if($response->isSuccessful()){
		...
	}
```
### Support
If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/kaankilic/omnipay-iyzico/issues),
or better yet, fork the library and submit a pull request.


## Contributions
I am the creator and single contributor of the project. So, Feel free to contribute something useful.
Please use Github for reporting bugs, and making comments or suggestions
