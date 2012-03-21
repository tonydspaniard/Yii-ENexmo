<?php

/**
 * ENexmoReceipt Class
 * 
 * Delivery receipt sent by Nexmo on the CallBack URL set on their API.
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
class ENexmoReceipt extends CComponent {
	/** Status parameters will be of the following possible values * */
	/**
	 * Message arrived to handset.
	 */
	const STATUS_DELIVERED = 'DELIVERED';
	/**
	 * Message timed out after we waited 48h to receive status from mobile operator.
	 */
	const STATUS_EXPIRED = 'EXPIRED';
	/**
	 * Message failed to be delivered.
	 */
	const STATUS_FAILED = 'FAILED';
	/**
	 * Message is being delivered.
	 */
	const STATUS_BUFFERED = 'BUFFERED';

	/**
	 * The request parameters sent sent via a GET (default) to your URL 
	 * include the following parameters names
	 * @var array the expected parameters
	 */
	protected $attributes = array(
		'username' => null,
		'password' => null,
		'to' => null,
		'network-code' => null,
		'messageId' => null,
		'msisdn' => null,
		'status' => null,
		'err-code' => null,
		'scts' => null,
		'client-ref' => null
	);

	/**
	 * Holds the possible error codes
	 * @var array 
	 */
	protected $error_codes;

	/**
	 *
	 * @var integer the UNIXTIME
	 */
	protected $time_received;

	/**
	 * class constructor
	 * @param array $data the data to fill the attributes with
	 */
	public function __construct(array $data = array())
	{

		$this->error_codes = array(
			Yii::t('ENexmo', 'Delivered'),
			Yii::t('ENexmo', 'Unknown'),
			Yii::t('ENexmo', 'Absent Subscriber - Temporary'),
			Yii::t('ENexmo', 'Absent Subscriber - Permenant'),
			Yii::t('ENexmo', 'Call barred by user'),
			Yii::t('ENexmo', 'Portability Error'),
			Yii::t('ENexmo', 'Anti-Spam Rejection'),
			Yii::t('ENexmo', 'Handset Busy'),
			Yii::t('ENexmo', 'Network Error'),
			Yii::t('ENexmo', 'Illegal Number'),
			Yii::t('ENexmo', 'Invalid Message'),
			Yii::t('ENexmo', 'Unroutable'),
			99 => Yii::t('ENexmo', 'General Error')
		);

		$this->setAttributes($data);
	}

	/**
	 * Sets the attributes of the class by checking its 
	 * @param array $attr the array to extract the parameters from
	 * @return void
	 */
	protected function setAttributes(array $attr)
	{
		if (!empty($attr) && !isset($attr['msisdn'], $attr['network-code'], $attr['messageId']))
		{
			return;
		}

		foreach (array_keys($this->attributes) as $key)
			$this->attributes[$key] = isset($data[$key]) ? $data[$key] : null;

		$this->onReceiptFound(new CEvent($this, $this->attributes));
	}

	/**
	 * Static helper function for class initialization
	 * @param array $data the array to extract the parameters from
	 * @return self 
	 */
	public static function process(array $data = array())
	{
		if (empty($data))
			$data = array_merge($_GET, $_POST);

		return new self($data);
	}

	/**
	 * Raises a general event when receipt is being successfully initialized
	 * @param void $event 
	 */
	public function onReceiptFound($event)
	{
		$this->raiseEvent('onReceiptFound', $event);
	}

	/**
	 * Optional username for authentication. Contact support@nexmo.com to enable
	 * @return string 
	 */
	public function getUsername()
	{
		return $this->attributes['username'];
	}

	/**
	 * Optional password for authentication. Contact support@nexmo.com to enable.
	 * @return string 
	 */
	public function getPassword()
	{
		return $this->attributes['password'];
	}

	/**
	 * Sender Id of the message.
	 * @return string
	 */
	public function getFrom()
	{
		return $this->attributes['to'];
	}

	/**
	 * Number message was delivered to.
	 * @return string
	 */
	public function getTo()
	{
		return $this->attributes['msisdn'];
	}

	/**
	 * Optional identifier of a mobile network MCCMNC. 
	 * @return string
	 */
	public function getNetwork()
	{
		return $this->attributes['network-code'];
	}

	/**
	 * Message ID.
	 * @return type 
	 */
	public function getMessageId()
	{
		return $this->attributes['messageId'];
	}

	/**
	 * Status of message.
	 * @return string
	 */
	public function getStatus()
	{
		return $this->attributes['status'];
	}

	/**
	 * Status related error code.
	 * @return integer
	 */
	public function getError()
	{
		return $this->attributes['err-code'];
	}

	/**
	 * Returns received message time in UNIXTIME
	 * @return integer
	 */
	public function getReceivedTime()
	{
		if (null === $this->time_received && !is_null($this->attributes['scts']))
		{
			$dp = date_parse_from_format('ymdGi', $this->attributes['scts']);
			$this->received_time = mktime($dp['hour'], $dp['minute'], $dp['second'], $dp['month'], $dp['day'], $dp['year']);
		}
		return $this->time_received;
	}

	/**
	 * Returns the error message based on the error code returned
	 * @return string 
	 */
	public function getErrorMessage()
	{
		return null !== $this->getError() && array_key_exists((int) $this->getError(), $this->error_codes) ? $this->error_codes[(int) $this->getError()] : '';
	}

	/**
	 * If you set a custom reference during your send request, this will return that value.
	 * @return string 
	 */
	public function getCustom()
	{
		return $this->attributes['client-ref'];
	}

}