<?php
use Omnipay\Common\CreditCard;
use Omnipay\Omnipay;
Route::get('/test',function(){
	$gateway = Omnipay::create('Iyzico');
	$formInputData = array(
	    'name' => 'Bobby',
	    'lastname' => 'Tables',
	    'number' => '4131111111111117',
	    'expiryMonth' => '08',
	    'expiryYear' => '2023',
	    'cvv' => '001',
	    'billingCity'	=> 'Izmir',
	    'billingAddress1'	=> 'test address',
		'billingCountry' => 'Turkiye',
		'email'	=> 'bl4cksta@gmail.com',
		'postCode'	=> '35530',
	);
	$card = new CreditCard($formInputData);
	$request = $gateway->authorize(['identityNumber'=>'35476978256']);
	$request->setCard($card);
	$request->setApiKey('sandbox-DvaeegvTrPBMmxIstCWQzIbcDxQExKwQ');
	$request->setSecretKey('sandbox-p9CUz4nCowOVtl4EXdpUUt5XqJqWtqhr');
	$basket = new \Omnipay\Common\ItemBag();
	$item = new \Omnipay\Common\Item(['name' => 'item name 1', 'price'=> '40.00']);
	$basket->add($item);

	$request->setItems($basket);
	dd($request->send());
});