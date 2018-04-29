<?php

namespace Omnipay\PayU\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Item;
use Omnipay\Common\Message\AbstractRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * PayU Purchase Request
 */
class PurchaseRequest extends AbstractRequest{

    /**
     * @var array
     */
    protected $endpoints = array(
        'production' => 'https://api.iyzipay.com',
        'test' => '"https://sandbox-api.iyzipay.com',
    );
	/**
	 * @return mixed
	 */
	public function getApiKey()
	{
		return $this->getParameter('apiKey');
	}
	/**
	 * @param $value
	 * @return mixed
	 */
	public function setApiKey($value)
	{
		return $this->setParameter('apiKey', $value);
	}
	/**
	 * @return mixed
	 */
	public function getSecretKey()
	{
		return $this->getParameter('secretKey');
	}
	/**
	 * @param $value
	 * @return mixed
	 */
	public function setSecretKey($value)
	{
		return $this->setParameter('secretKey', $value);
	}
    /**
     * @param $endpoint
     * @return mixed
     */
    public function getEndpoint($endpoint)
    {
        return $this->getTestMode() ? $this->endpoints['test'] : $this->endpoints[$endpoint];
    }
	/**
	 * @return mixed
	 */
	public function getIdentityNumber(){
		return $this->getParameter('identityNumber');
	}
	/**
	 * @param $value
	 * @return AbstractRequest
	 */
	public function setIdentityNumber($value){
		return $this->setParameter('identityNumber',$value);
	}
	/**
	 * @return int
	 * @throws \Exception
	 */
	public function getInstallment(){
		$installment = $this->getParameter('installment');
		if(false == $installment){
			return 1;
		}
		if( $installment <1 || $installment >12){
			throw new \Exception('Invalid installment number');
		}
		return $installment;
	}
	/**
	 * @param $value
	 * @return AbstractRequest
	 */
	public function setInstallment($value){
		return $this->setParameter('installment',$value);
	}
		/**
	 * The date when the order is initiated in the system, in YYYY-MM-DD HH:MM:SS format (e.g.: "2012-05-01 21:15:45")
	 * Important: Date should be UTC standard +/-10 minutes
	 * @return mixed
	 */
		public function getOrderDate(){
			return $this->getParameter('orderDate');
		}
	/**
	 * @param $value
	 * @return AbstractRequest
	 */
	public function setOrderDate($value){
		return $this->setParameter('orderDate',$value);
	}
	/**
	* @return array
	* @throws \Omnipay\Common\Exception\InvalidCreditCardException
	*/
	public function getData(){
        $options = new \Iyzipay\Options();
        $options->setApiKey($this->getApiKey());
		$options->setSecretKey($this->getSecretKey());
		$options->setBaseUrl($this->getEndpoint('test'));
		// If card is not valid then throw InvalidCreditCardException.
        $creditCard = new \Iyzipay\Model\PaymentCard();
        $card->validate();
        $creditCard->setCardHolderName($this->getName());
		$creditCard->setCardNumber($this->getNumber());
		$creditCard->setExpireMonth($this->getExpiryMonth());
		$creditCard->setExpireYear($this->getExpiryYear());
		$creditCard->setCvc($this->getCvv());
		$creditCard->setRegisterCard(false); // todo
        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($this->getBillingName());
        $billingAddress->setCity($this->getBillingCity());
        $billingAddress->setCountry($this->getBillingCountry());
        $billingAddress->setAddress($this->getBillingAddress());
        $billingAddress->setZipCode($this->getBillingZip());
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($this->getId());
        $buyer->setName($this->getFirstname());
        $buyer->setSurname($this->getLastname());
        $buyer->setEmail($this->getEmail());
        $buyer->setIdentityNumber($this->getIdentityNumber());
        $buyer->setRegistrationDate($this->getRegistrationDate());
        $buyer->setRegistrationAddress($this->getAddress());
        $buyer->setCity($this->getCity());
        $buyer->setCountry($this->getCountry());
        $buyer->setIp($this->getClientIp());
        $basket = new \Iyzipay\Model\BasketItem();
        $items = $this->getItems();
        $basketItems = array();
        foreach ($items as $item) {
	        $basket->setId($item->getName());
	        $basket->setName($item->getName());
	        $basket->setCategory1($item->getName());
	        $basket->setCategory2($item->getName());
	        $basket->setItemType($item->getName());
	        $basket->setPrice($item->getPrice());
	        basketItems[] = $basket;
        }
        // todo
		$data = new \Iyzipay\Request\CreatePaymentRequest();
		$data->setPaymentCard($creditCard);
        $data->setShippingAddress($billingAddress);
        $data->setBillingAddress($billingAddress);
        $data->setBasketItems($basketItems);
		$payment = \Iyzipay\Model\ThreedsInitialize::create($data, $options);
		dd($payment);
	}
	/**
     * @param $data
     * @return Response
     */
    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }
    /**
     * @param $data
     * @return PurchaseResponse
     */
    public function sendData($data)
    {
        $httpRequest = $this->httpClient->post($this->getEndpoint('test'), null, http_build_query($data));
        //$httpRequest->getCurlOptions()->set(CURLOPT_SSLVERSION, 6); // CURL_SSLVERSION_TLSv1_2 for libcurl < 7.35
        $response = $httpRequest->send();
        $xmlData = json_decode(json_encode($response->xml()),1);
        $data = array();
        foreach($xmlData as $key => $value){
            $data[$key] = empty($value) ? null: $value;
        }
        return new PurchaseResponse($this,$data);
    }
    /**
     * HMAC_MD5 signature applied on all parameters from the request.
     * Source string for HMAC_MD5 will be calculated by adding the length
     * of each field value at the beginning of field value. A common key
     * shared between PayU and the merchant is used for the signature.
     * @param array $data
     * @return string
     */
    public function generateHash(array $data)
    {
        if ($this->getSecretKey()) {
            //begin HASH calculation
            ksort($data);
            $hashString = "";
            foreach ($data as $key => $val) {
                $hashString .= strlen($val) . $val;
            }
            return hash_hmac("md5", $hashString, $this->getSecretKey());
        }
    }
}