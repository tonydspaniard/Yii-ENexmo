<?php

/**
 * ENexmoDeliveryCallback class
 * 
 * Checks whether an inbound message has arrived and fires an event
 * 
 * @author Antonio Ramirez Cobos
 * @link www.ramirezcobos.com
 * 
 * 
 * @copyright 
 * 
 * Copyright (c) 2012 Antonio Ramirez
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software 
 * and associated documentation files (the "Software"), to deal in the Software without restriction, 
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, 
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial 
 * portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
 * NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE 
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */
class ENexmoDeliveryCallback extends CAction {

	/**
	 * Process a message delivery request
	 * @return void
	 */
	public function run()
	{

		$data = array_merge($_GET, $_POST);

		if (isset($data['msisdn'], $data['network-code'], $data['messageId']))
		{

			$receipt = new ENexmoReceipt($data);

			$event = new ENexmoDeliveryEvent($this, $receipt);

			$this->onDelivery($event);
		}
		else
			Yii::log(Yii::t('ENexmo', 'Invalid nexmo delivery call.'));
	}

	/**
	 *
	 * @param ENexmoDeliveryEvent $event 
	 */
	public function onDelivery($event)
	{
		$this->raiseEvent('onDelivery', $event);
	}

}

/**
 * ENexmoDeliveryEvent class
 *
 * @author Antonio Ramirez <antonio@ramirezcobos.com>
 */
class ENexmoDeliveryEvent extends CEvent {

	/**
	 *
	 * @var ENexmoReceipt
	 */
	protected $_receipt;

	/**
	 *
	 * @param mixed $sender
	 * @param ENexmoReceipt $receipt
	 * @param array $params 
	 */
	public function __construct($sender = null, $receipt = null, $params = null)
	{
		$this->_receipt = $receipt;
		parent::__construct($sender, $params);
	}

	/**
	 * @return ENexmoReceipt
	 */
	public function getReceipt()
	{
		return $this->receipt;
	}

}