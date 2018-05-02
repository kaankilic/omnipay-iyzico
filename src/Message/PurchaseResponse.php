<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

/**
 * Iyzico Purchase Request
 */
class PurchaseResponse extends AbstractResponse
{
	public function isResponse(){
		return true;
	}
	public function isSuccessful()
    {
        //$this->validateResponse();
        if(!isset($this->data['status'])){
            return false;
        }
        return $this->data['status'] == 'success';
    }

    public function getRedirectData(){
        return $this->data;
    }
}