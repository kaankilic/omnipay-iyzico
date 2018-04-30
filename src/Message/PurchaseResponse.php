
<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Item;
use Omnipay\Common\Message\AbstractRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Iyzico Purchase Request
 */
class PurchaseResponse extends AbstractRequest
{
	public function isResponse(){
		return true;
	}
}