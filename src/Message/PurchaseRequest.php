<?php
namespace Omnipay\Iyzico\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Item;
use Omnipay\Common\Message\AbstractRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Iyzico Purchase Request
 */
class PurchaseRequest extends AbstractRequest{
    /**
     * @var array
     */
    protected $endpoints = array(
    	'production' => 'https://api.iyzipay.com',
    	'test' => 'https://sandbox-api.iyzipay.com',
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
	public function getID(){
		return $this->getParameter('id');
	}
	/**
	 * @param $value
	 * @return AbstractRequest
	 */
	public function setID($value){
		return $this->setParameter('id',$value);
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
	public function getCallbackUrl(){
		return $this->getParameter('callbackUrl');
	}
	/**
	 * @param $value
	 * @return AbstractRequest
	 */
	public function setCallbackUrl($value){
		return $this->setParameter('callbackUrl',$value);
	}
		/**
	 * The date when the order is initiated in the system, in YYYY-MM-DD HH:MM:SS format (e.g.: "2012-05-01 21:15:45")
	 * Important: Date should be UTC standard +/-10 minutes
	 * @return mixed
	 */
		public function getClientIp(){
			return $this->getParameter('clientIP');
		}
	/**
	 * @param $value
	 * @return AbstractRequest
	 */
	public function setClientIp($value){
		return $this->setParameter('clientIP',$value);
	}
	public function getOptions(){
		$options = new \Iyzipay\Options();
		$options->setApiKey($this->getApiKey());
		$options->setSecretKey($this->getSecretKey());
		$options->setBaseUrl($this->getEndpoint('test'));
		return $options;
	}
	public function buildTransactionID($card){
		$data = array(
			"name"	=> $card->getName(),
			"city"	=> $card->getBillingCity(),
			"country" => $card->getBillingCountry(),
			"address" => $card->getBillingAddress1(),
			"zipcode" => $card->getBillingPostcode(),
			"ip"	=> $this->getClientIp(),
			"id"	=> uniqid(),
			"timestamp"	=> date("YmdHis")
		);
		$data = serialize($data);
		return hash_hmac("sha1",$data,$this->getSecretKey());
	}

	/**
	* @return array
	* @throws \Omnipay\Common\Exception\InvalidCreditCardException
	*/
	public function getData(){
		$options = $this->getOptions();
		// If card is not valid then throw InvalidCreditCardException.
		$creditCard = new \Iyzipay\Model\PaymentCard();
		$card = $this->getCard();
		$card->validate();
		$creditCard->setCardHolderName($card->getName());
		$creditCard->setCardNumber($card->getNumber());
		$creditCard->setExpireMonth($card->getExpiryMonth());
		$creditCard->setExpireYear($card->getExpiryYear());
		$creditCard->setCvc($card->getCvv());
//		$creditCard->setRegisterCard(false); // todo
		$billingAddress = new \Iyzipay\Model\Address();
		$billingAddress->setContactName($card->getName());
		$billingAddress->setCity($card->getBillingCity());
		$billingAddress->setCountry($card->getBillingCountry());
		$billingAddress->setAddress($card->getBillingAddress1());
		$billingAddress->setZipCode($card->getBillingPostcode());
		$buyer = new \Iyzipay\Model\Buyer();
		$buyer->setId($card->getFirstname());
		$buyer->setRegistrationDate(date('Y-m-d H:i:s'));
		$buyer->setRegistrationAddress($card->getBillingAddress1());
		$buyer->setZipCode($card->getBillingPostcode());
		$buyer->setIp($this->getClientIp());
		$buyer->setName($card->getFirstname());
		$buyer->setSurname($card->getLastname());
		$buyer->setEmail($card->getEmail());
		$buyer->setIdentityNumber($this->getIdentityNumber());
		$buyer->setCity($card->getCity());
		$buyer->setCountry($card->getCountry());
		$basket = new \Iyzipay\Model\BasketItem();
		$items = $this->getItems();
		$basketItems = array();
		$amount = 0;
		if( !empty($items)){
			foreach ($items as $key => $item) {
				$basket->setId($key);
				$basket->setName($item->getName());
				$basket->setCategory1($item->getName());
				$basket->setItemType("VIRTUAL");
				$basket->setPrice($item->getPrice());
				$basketItems[] = $basket;
				$amount += $item->getPrice();
			}
		}
        // todo
		$data = new \Iyzipay\Request\CreatePaymentRequest();
		$data->setPaidPrice($amount);
		$data->setPrice($amount);
		$data->setLocale(\Iyzipay\Model\Locale::TR);
		$data->setCurrency(\Iyzipay\Model\Currency::TL);
		$data->setConversationId($this->buildTransactionID($card));
		$data->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
		$data->setPaymentGroup(\Iyzipay\Model\PaymentGroup::SUBSCRIPTION);
		$data->setPaymentCard($creditCard);
		$data->setInstallment($this->getInstallment());
		$data->setBuyer($buyer);
		$data->setBillingAddress($billingAddress);
		$data->setBasketItems($basketItems);
		$data->setCallbackUrl($this->getcallbackUrl());
		$payment = \Iyzipay\Model\ThreedsInitialize::create($data, $options);
		return $payment;
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
    	return new PurchaseResponse($this,$data);
    }
}